<?php

namespace Tests\Feature\Mobile;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\CostSimulator;
use App\Models\Proposal;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MobileDashboardTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private int $codes = 0;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        // Meio do mês e do dia: as fronteiras testam-se com datas explícitas.
        Carbon::setTestNow('2026-03-15 12:00:00');
        // Criar cotações envia avisos por email (ProposalObserver).
        Mail::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function lead(array $attributes = []): Client
    {
        return Client::create($attributes + ['name' => 'Lead', 'is_lead' => true, 'lead_status' => 'nova']);
    }

    private function cliente(array $attributes = [], ?string $criadoEm = null): Client
    {
        $client = Client::create($attributes + ['name' => 'Cliente', 'is_lead' => false]);
        if ($criadoEm) {
            DB::table('clients')->where('id', $client->id)->update(['created_at' => $criadoEm]);
        }

        return $client;
    }

    private function pedido(Client $client, string $status = 'novo'): FormProposal
    {
        return FormProposal::create([
            'name' => $client->name, 'email' => 'x@example.com', 'phone' => '912345678', 'client_id' => $client->id,
            'brand' => 'Tesla', 'model' => 'Model 3', 'status' => $status,
        ]);
    }

    private function cotacao(Client $client, string $status = 'Pendente'): Proposal
    {
        return Proposal::create([
            'client_id' => $client->id, 'brand' => 'Volvo', 'model' => 'XC40', 'transport_cost' => 1000,
            'commission_cost' => 500, 'proposed_car_value' => 20000,
            'proposal_code' => 'VX' . str_pad((string) ++$this->codes, 8, '0', STR_PAD_LEFT), 'status' => $status,
        ]);
    }

    private function convertida(Client $client, ?string $status = 'Iniciada'): ConvertedProposal
    {
        return ConvertedProposal::create(['client_id' => $client->id, 'status' => $status, 'brand' => 'BMW', 'modelCar' => 'i4', 'version' => 'eDrive40']);
    }

    private function userWith(array $permissions): User
    {
        $role = Role::findOrCreate('dash-' . md5(implode(',', $permissions)), 'web');
        foreach ($permissions as $nome) {
            $role->givePermissionTo(Permission::findOrCreate($nome, 'web'));
        }
        $user = $this->backofficeUser($role->name);
        $user->forceFill(['access_mobile' => true])->save();

        return $user;
    }

    /** Valor do cartão com este rótulo, lido do HTML ("valor" imediatamente antes do ícone, rótulo depois). */
    private function valor(string $html, string $rotulo): int
    {
        $ok = preg_match('/m-stat__value[^>]*>(\d+)<\/span>\s*<i[^>]*><\/i>\s*<\/span>\s*<span class="m-stat__label">\s*' . preg_quote($rotulo, '/') . '\s*<\/span>/', $html, $m);
        $this->assertSame(1, $ok, "Cartão '{$rotulo}' não encontrado");

        return (int) $m[1];
    }

    /** Valor do cartão da faixa "Para tratar" com este rótulo. */
    private function topo(string $html, string $rotulo): int
    {
        $ok = preg_match('/m-todo__value">(\d+)<\/span>\s*<i[^>]*><\/i>\s*<\/span>\s*<span class="m-todo__label">' . preg_quote($rotulo, '/') . '<\/span>/', $html, $m);
        $this->assertSame(1, $ok, "Cartão '{$rotulo}' não encontrado na faixa Para tratar");

        return (int) $m[1];
    }

    private function simulacao(bool $lida, ?Client $client = null): CostSimulator
    {
        return CostSimulator::create([
            'client_id' => ($client ?? $this->cliente(['email' => uniqid() . '@example.com']))->id, 'brand' => 'Audi', 'model' => 'A3',
            'car_value' => 20000, 'commission_cost' => 1000, 'inspection_commission_cost' => 150, 'transport' => 600, 'ipo_cost' => 40,
            'imt_cost' => 65, 'registration_cost' => 55, 'plates_cost' => 30, 'isv_cost' => 1500, 'total_cost' => 23440, 'read' => $lida,
        ]);
    }

    private function tarefa(array $attributes = []): Task
    {
        return Task::create($attributes + ['title' => 'Tarefa', 'due_date' => '2026-03-15', 'status' => 'pendente']);
    }

    public function test_logo_no_topo_do_inicio_e_de_outra_pagina_ligado_ao_inicio(): void
    {
        $user = $this->mobileUser();
        $logo = 'src="' . asset('img/mobile/logo-redondo.png') . '"';
        $link = 'href="' . route('admin.mobile.dashboard') . '" class="m-topbar-logo"';

        $this->actingAs($user)->get(route('admin.mobile.dashboard'))
            ->assertOk()->assertSee($logo, false)->assertSee($link, false)->assertSee('alt="IzzyCar', false);

        $this->actingAs($user)->get(route('admin.mobile.leads.index'))
            ->assertOk()->assertSee($logo, false)->assertSee($link, false)->assertSee('<h1>Leads</h1>', false);

        $lead = $this->lead();
        $this->actingAs($user)->get(route('admin.mobile.leads.show', $lead->id))
            ->assertOk()->assertSee($logo, false)->assertSee('m-topbar-back', false);
    }

    public function test_numeros_corretos_para_admin(): void
    {
        $hoje = now()->startOfDay();
        $this->lead(['lead_status' => 'nova', 'next_followup_at' => $hoje->copy()->setTime(18, 0)]);               // novas, a tratar, hoje
        $this->lead(['lead_status' => 'em_contacto', 'next_followup_at' => $hoje->copy()->subSecond()]);          // a tratar, atraso (ontem 23:59:59)
        $this->lead(['lead_status' => 'qualificada', 'next_followup_at' => $hoje->copy()->addDay()]);             // a tratar, futuro (amanhã 00:00)
        $this->lead(['lead_status' => 'fria', 'next_followup_at' => $hoje->copy()->subDays(3)]);                  // atraso
        $this->lead(['lead_status' => 'perdida']);
        $this->cliente(['name' => 'Cliente com is_lead falso', 'lead_status' => 'nova', 'next_followup_at' => $hoje->copy()->subDay()]); // não é lead

        $c = $this->cliente(['email' => 'a@example.com']);
        $this->pedido($c, 'novo');
        $this->pedido($c, 'novo');
        $this->pedido($c, 'convertido');
        $this->cotacao($c, 'Pendente');
        $this->cotacao($c, 'Enviado');
        $this->cotacao($c, 'Enviado');
        $this->cotacao($c, 'Aprovada');
        $this->convertida($c, 'Iniciada');
        $this->convertida($c, 'IPO');
        $this->convertida($c, 'Concluido');
        $this->convertida($c, 'Cancelada');
        $this->convertida($c, 'Cancelado');

        $html = $this->actingAs($this->mobileUser())->get(route('admin.mobile.dashboard'))->assertOk()->getContent();

        $this->assertSame(1, $this->topo($html, 'Leads novas'));
        $this->assertSame(3, $this->valor($html, 'A tratar'));
        $this->assertSame(1, $this->topo($html, 'Follow-ups hoje'));
        $this->assertSame(2, $this->valor($html, 'Follow-ups em atraso'));
        $this->assertSame(2, $this->topo($html, 'Formulários novos'));
        $this->assertSame(1, $this->valor($html, 'Pendentes'));
        $this->assertSame(2, $this->valor($html, 'Enviadas (a aguardar)'));
        $this->assertSame(2, $this->valor($html, 'Em processo'));
        $this->assertSame(2, $this->valor($html, 'Total'));      // $c e o cliente com is_lead falso
        $this->assertSame(2, $this->valor($html, 'Novos este mês'));
    }

    public function test_faixa_para_tratar_cinco_numeros_para_admin(): void
    {
        $admin = $this->mobileUser();
        $outro = $this->backofficeUser('admin', ['email' => 'outro@example.com']);
        $this->lead(['next_followup_at' => now()->setTime(9, 0)]);
        $this->pedido($this->cliente(['email' => 'a@example.com']));

        $this->simulacao(false);
        $this->simulacao(false);
        $this->simulacao(true);

        $this->tarefa(['user_id' => $admin->id, 'due_date' => '2026-03-15']);                      // hoje
        $this->tarefa(['user_id' => $admin->id, 'due_date' => '2026-03-10', 'status' => 'em_progresso']); // atraso
        $this->tarefa(['user_id' => $admin->id, 'due_date' => '2026-03-16']);                      // amanhã: fora
        $this->tarefa(['user_id' => $admin->id, 'due_date' => '2026-03-01', 'status' => 'concluida']);
        $this->tarefa(['user_id' => $admin->id, 'due_date' => '2026-03-01', 'status' => 'cancelada']);
        $this->tarefa(['user_id' => $outro->id, 'due_date' => '2026-03-01']);                      // de outro
        $this->tarefa(['user_id' => null, 'due_date' => '2026-03-01']);                            // sem dono

        $html = $this->actingAs($admin)->get(route('admin.mobile.dashboard'))->assertOk()->getContent();

        $this->assertSame(1, $this->topo($html, 'Leads novas'));
        $this->assertSame(1, $this->topo($html, 'Follow-ups hoje'));
        $this->assertSame(1, $this->topo($html, 'Formulários novos'));
        $this->assertSame(2, $this->topo($html, 'Simulações novas'));
        $this->assertSame(2, $this->topo($html, 'Tarefas'));
        $this->assertStringContainsString('1 em atraso', $html);
        $this->assertStringContainsString('m-todo is-alert', $html);
        $this->assertStringContainsString(route('admin.mobile.tasks.index', ['filtro' => 'para_tratar']), html_entity_decode($html));
        $this->assertStringContainsString(route('admin.mobile.cost-simulators.index'), html_entity_decode($html));
    }

    /** Segue a ligação do cartão e devolve o HTML da lista. */
    private function listaDoCartao(User $user, string $html, string $rotulo): string
    {
        $ok = preg_match('/<a href="([^"]+)" class="m-(?:stat|todo)(?: is-alert)?">(?:(?!<\/a>).)*?' . preg_quote($rotulo, '/') . '/s', $html, $m);
        $this->assertSame(1, $ok, "Ligação do cartão '{$rotulo}' não encontrada");

        return $this->actingAs($user)->get(html_entity_decode($m[1]))->assertOk()->getContent();
    }

    private function montarFollowups(?User $dono, ?User $outro): void
    {
        foreach ([[now()->setTime(8, 0), 2], [now()->subDay()->setTime(23, 59), 3], [now()->addDay()->setTime(0, 0), 1]] as [$quando, $n]) {
            for ($i = 0; $i < $n; $i++) {
                $this->lead(['name' => 'Minha', 'owner_id' => $dono?->id, 'next_followup_at' => $quando]);
            }
        }
        $this->lead(['name' => 'Alheia', 'owner_id' => $outro?->id, 'next_followup_at' => now()->setTime(9, 0)]);
        $this->lead(['name' => 'Alheia', 'owner_id' => $outro?->id, 'next_followup_at' => now()->subDays(3)]);
        $this->lead(['name' => 'Alheia', 'owner_id' => $outro?->id, 'lead_status' => 'perdida', 'next_followup_at' => now()->subDays(2)]);
    }

    public function test_cartoes_de_followups_e_simulacoes_abrem_listas_com_o_mesmo_numero_para_admin(): void
    {
        $admin = $this->mobileUser();
        $this->montarFollowups($admin, null);
        $this->simulacao(false);
        $this->simulacao(false);
        $this->simulacao(true);

        $html = $this->actingAs($admin)->get(route('admin.mobile.dashboard'))->assertOk()->getContent();

        $hoje = $this->topo($html, 'Follow-ups hoje');
        $this->assertSame(3, $hoje);
        $this->assertSame($hoje, substr_count($this->listaDoCartao($admin, $html, 'Follow-ups hoje'), 'm-lead-card"'));

        $atraso = $this->valor($html, 'Follow-ups em atraso');
        $this->assertSame(5, $atraso);
        $this->assertSame($atraso, substr_count($this->listaDoCartao($admin, $html, 'Follow-ups em atraso'), 'm-lead-card"'));

        $novas = $this->topo($html, 'Simulações novas');
        $this->assertSame(2, $novas);
        $this->assertSame($novas, substr_count($this->listaDoCartao($admin, $html, 'Simulações novas'), 'm-lead-card"'));
    }

    public function test_cartoes_de_followups_abrem_listas_com_o_mesmo_numero_para_quem_so_ve_as_suas(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['leads.view.own']);
        $outro = $this->backofficeUser('admin', ['email' => 'outro@example.com']);
        $this->montarFollowups($user, $outro);

        $html = $this->actingAs($user)->get(route('admin.mobile.dashboard'))->assertOk()->getContent();

        $this->assertSame(2, $this->topo($html, 'Follow-ups hoje'));
        $lista = $this->listaDoCartao($user, $html, 'Follow-ups hoje');
        $this->assertSame(2, substr_count($lista, 'm-lead-card"'));
        $this->assertStringNotContainsString('Alheia', $lista);

        $this->assertSame(3, $this->valor($html, 'Follow-ups em atraso'));
        $lista = $this->listaDoCartao($user, $html, 'Follow-ups em atraso');
        $this->assertSame(3, substr_count($lista, 'm-lead-card"'));
        $this->assertStringNotContainsString('Alheia', $lista);
    }

    public function test_filtros_de_followup_e_nao_lidas_ignoram_valores_invalidos_ou_em_array(): void
    {
        $admin = $this->mobileUser();
        $this->montarFollowups($admin, null);
        $this->simulacao(true);

        $this->actingAs($admin)->get(route('admin.mobile.leads.index', ['followup' => ['hoje']]))->assertOk();
        $this->actingAs($admin)->get(route('admin.mobile.leads.index', ['followup' => 'xpto']))->assertOk();
        $this->actingAs($admin)->get(route('admin.mobile.cost-simulators.index', ['nao_lidas' => ['1']]))->assertOk()->assertSee('Cliente');
        // Combina com a pesquisa: só as de hoje com este nome.
        $this->assertSame(1, substr_count($this->actingAs($admin)->get(route('admin.mobile.leads.index', ['followup' => 'hoje', 'search' => 'Alheia']))->getContent(), 'm-lead-card"'));
        $this->assertSame(2, substr_count($this->actingAs($admin)->get(route('admin.mobile.leads.index', ['followup' => 'hoje', 'search' => 'Minha']))->getContent(), 'm-lead-card"'));
    }

    public function test_simulacoes_novas_usam_a_mesma_definicao_da_lista(): void
    {
        $this->simulacao(true);
        $this->simulacao(false);
        $this->simulacao(false);

        $user = $this->mobileUser();
        $html = $this->actingAs($user)->get(route('admin.mobile.dashboard'))->getContent();
        $this->assertSame(2, $this->topo($html, 'Simulações novas'));

        // A lista mostra exatamente 2 badges "Nova".
        $lista = $this->actingAs($user)->get(route('admin.mobile.cost-simulators.index'))->getContent();
        $this->assertSame(2, substr_count($lista, '>Nova</span>'));

        // Abrir a lista não as marca como lidas; o contador mantém-se.
        $this->assertSame(2, $this->topo($this->actingAs($user)->get(route('admin.mobile.dashboard'))->getContent(), 'Simulações novas'));
    }

    public function test_tarefas_sem_atraso_nao_realcam(): void
    {
        $admin = $this->mobileUser();
        $this->tarefa(['user_id' => $admin->id, 'due_date' => '2026-03-15']);

        $html = $this->actingAs($admin)->get(route('admin.mobile.dashboard'))->getContent();

        $this->assertSame(1, $this->topo($html, 'Tarefas'));
        $this->assertStringNotContainsString('em atraso</span>', str_replace('Follow-ups em atraso', '', $html));
        $this->assertStringNotContainsString('m-todo is-alert', $html);
    }

    public function test_faixa_esconde_cartoes_sem_permissao(): void
    {
        config(['permissions.enforce' => true]);
        $this->lead();
        $this->simulacao(false);
        $user = $this->userWith(['cost-simulators.view']);

        $html = $this->actingAs($user)->get(route('admin.mobile.dashboard'))->assertOk()
            ->assertDontSee('Leads novas')->assertDontSee('Follow-ups hoje')->assertDontSee('Formulários novos')->assertDontSee('Tarefas')
            ->getContent();
        $this->assertSame(1, $this->topo($html, 'Simulações novas'));

        $user = $this->userWith(['tasks.view']);
        $this->tarefa(['user_id' => $user->id]);
        $html = $this->actingAs($user)->get(route('admin.mobile.dashboard'))->assertOk()
            ->assertDontSee('Simulações novas')->assertDontSee('Leads novas')
            ->getContent();
        $this->assertSame(1, $this->topo($html, 'Tarefas'));
    }

    public function test_faixa_com_so_proprias_conta_formularios_e_leads_do_utilizador(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['leads.view.own', 'form-proposals.view.own']);
        $outro = $this->backofficeUser('admin', ['email' => 'outro@example.com']);

        $minha = $this->lead(['owner_id' => $user->id, 'next_followup_at' => now()->setTime(10, 0)]);
        $this->lead(['owner_id' => $outro->id, 'next_followup_at' => now()->setTime(10, 0)]);
        $this->pedido($minha);
        $this->pedido($this->lead(['owner_id' => $outro->id]));

        $html = $this->actingAs($user)->get(route('admin.mobile.dashboard'))->assertOk()->getContent();

        $this->assertSame(1, $this->topo($html, 'Leads novas'));
        $this->assertSame(1, $this->topo($html, 'Follow-ups hoje'));
        $this->assertSame(1, $this->topo($html, 'Formulários novos'));
    }


    public function test_clientes_novos_respeitam_as_fronteiras_do_mes(): void
    {
        $this->cliente(['email' => '1@example.com'], '2026-02-28 23:59:59');  // mês anterior
        $this->cliente(['email' => '2@example.com'], '2026-03-01 00:00:00');  // primeiro instante
        $this->cliente(['email' => '3@example.com'], '2026-03-31 23:59:59');  // último instante
        $this->cliente(['email' => '4@example.com'], '2026-04-01 00:00:00');  // mês seguinte

        $html = $this->actingAs($this->mobileUser())->get(route('admin.mobile.dashboard'))->getContent();

        $this->assertSame(4, $this->valor($html, 'Total'));
        $this->assertSame(2, $this->valor($html, 'Novos este mês'));
    }

    public function test_grupos_escondidos_sem_permissao(): void
    {
        $this->lead();
        $user = $this->userWith(['leads.view.all']);
        config(['permissions.enforce' => true]);

        $this->actingAs($user)->get(route('admin.mobile.dashboard'))
            ->assertOk()->assertSee('Follow-ups hoje')
            ->assertDontSee('Formulários novos')->assertDontSee('Pendentes')->assertDontSee('Em processo')->assertDontSee('Novos este mês');
    }

    public function test_so_proprias_conta_apenas_os_registos_do_utilizador(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['leads.view.own', 'form-proposals.view.own', 'proposals.view.own', 'clients.view.own']);
        $outro = $this->backofficeUser('admin', ['email' => 'outro@example.com']);

        $minhaLead = $this->lead(['owner_id' => $user->id, 'next_followup_at' => now()->setTime(10, 0)]);
        $this->lead(['owner_id' => $outro->id, 'next_followup_at' => now()->setTime(10, 0)]);
        $this->lead(['owner_id' => $user->id, 'lead_status' => 'perdida']);
        $meuCliente = $this->cliente(['owner_id' => $user->id, 'email' => 'm@example.com']);
        $alheio = $this->cliente(['owner_id' => $outro->id, 'email' => 'o@example.com']);

        $this->pedido($minhaLead);
        $this->pedido($alheio);
        $this->cotacao($meuCliente, 'Pendente');
        $this->cotacao($alheio, 'Pendente');
        $this->cotacao($alheio, 'Enviado');
        $this->convertida($meuCliente, 'Iniciada');
        $this->convertida($alheio, 'Iniciada');
        $this->convertida($alheio, 'IPO');

        $html = $this->actingAs($user)->get(route('admin.mobile.dashboard'))->assertOk()->getContent();

        $this->assertSame(1, $this->topo($html, 'Leads novas'));
        $this->assertSame(1, $this->valor($html, 'A tratar'));
        $this->assertSame(1, $this->topo($html, 'Follow-ups hoje'));
        $this->assertSame(1, $this->topo($html, 'Formulários novos'));
        $this->assertSame(1, $this->valor($html, 'Pendentes'));
        $this->assertSame(0, $this->valor($html, 'Enviadas (a aguardar)'));
        $this->assertSame(1, $this->valor($html, 'Em processo'));
        $this->assertSame(1, $this->valor($html, 'Total'));
    }

    public function test_convertidas_para_quem_so_ve_cotacoes_proprias_sem_converted_proposals(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['proposals.view.own']);
        $meu = $this->cliente(['owner_id' => $user->id, 'email' => 'm@example.com']);
        $this->convertida($meu);
        $this->convertida($this->cliente(['email' => 'o@example.com']));

        $html = $this->actingAs($user)->get(route('admin.mobile.dashboard'))->assertOk()->assertDontSee('Leads novas')->getContent();

        $this->assertSame(1, $this->valor($html, 'Em processo'));
    }

    public function test_sem_permissoes_relevantes_mostra_mensagem_amigavel(): void
    {
        config(['permissions.enforce' => true]);
        $this->lead();
        $user = $this->userWith(['movements.view']);

        $this->actingAs($user)->get(route('admin.mobile.dashboard'))
            ->assertOk()->assertSee('Ainda não tem números para mostrar')
            ->assertDontSee('class="m-stat"', false);
    }

    public function test_todas_as_ligacoes_dos_cartoes_abrem_para_o_admin(): void
    {
        $user = $this->mobileUser();
        $html = $this->actingAs($user)->get(route('admin.mobile.dashboard'))->getContent();

        preg_match_all('/<a href="([^"]+)" class="m-(?:stat|todo)(?: is-alert)?">/', $html, $m);
        $this->assertCount(12, $m[1]); // 5 da faixa + 7 dos grupos (sem os duplicados)
        foreach (array_unique($m[1]) as $href) {
            $this->actingAs($user)->get(html_entity_decode($href))->assertOk();
        }

        // Os filtros usados existem mesmo nas listas.
        $this->assertContains(route('admin.mobile.leads.index', ['lead_status' => 'nova']), array_map('html_entity_decode', $m[1]));
        $this->assertContains(route('admin.mobile.form-proposals.index', ['status' => 'novo']), array_map('html_entity_decode', $m[1]));
    }
}
