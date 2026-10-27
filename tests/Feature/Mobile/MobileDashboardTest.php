<?php

namespace Tests\Feature\Mobile;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\Proposal;
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

    public function test_logo_no_topo_do_inicio_e_de_outra_pagina_ligado_ao_inicio(): void
    {
        $user = $this->mobileUser();
        $logo = 'src="' . asset('img/logo-transparente.png') . '"';
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

        $this->assertSame(1, $this->valor($html, 'Novas'));
        $this->assertSame(3, $this->valor($html, 'A tratar'));
        $this->assertSame(1, $this->valor($html, 'Follow-ups hoje'));
        $this->assertSame(2, $this->valor($html, 'Follow-ups em atraso'));
        $this->assertSame(2, $this->valor($html, 'Pedidos novos'));
        $this->assertSame(1, $this->valor($html, 'Pendentes'));
        $this->assertSame(2, $this->valor($html, 'Enviadas (a aguardar)'));
        $this->assertSame(2, $this->valor($html, 'Em processo'));
        $this->assertSame(2, $this->valor($html, 'Total'));      // $c e o cliente com is_lead falso
        $this->assertSame(2, $this->valor($html, 'Novos este mês'));
        
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
            ->assertDontSee('Pedidos novos')->assertDontSee('Pendentes')->assertDontSee('Em processo')->assertDontSee('Novos este mês');
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

        $this->assertSame(1, $this->valor($html, 'Novas'));
        $this->assertSame(1, $this->valor($html, 'A tratar'));
        $this->assertSame(1, $this->valor($html, 'Follow-ups hoje'));
        $this->assertSame(1, $this->valor($html, 'Pedidos novos'));
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

        $html = $this->actingAs($user)->get(route('admin.mobile.dashboard'))->assertOk()->assertDontSee('Novas')->getContent();

        $this->assertSame(1, $this->valor($html, 'Em processo'));
    }

    public function test_sem_permissoes_relevantes_mostra_mensagem_amigavel(): void
    {
        config(['permissions.enforce' => true]);
        $this->lead();
        $user = $this->userWith(['cost-simulators.view']);

        $this->actingAs($user)->get(route('admin.mobile.dashboard'))
            ->assertOk()->assertSee('Ainda não tem números para mostrar')
            ->assertDontSee('class="m-stat"', false);
    }

    public function test_todas_as_ligacoes_dos_cartoes_abrem_para_o_admin(): void
    {
        $user = $this->mobileUser();
        $html = $this->actingAs($user)->get(route('admin.mobile.dashboard'))->getContent();

        preg_match_all('/<a href="([^"]+)" class="m-stat">/', $html, $m);
        $this->assertCount(10, $m[1]);
        foreach (array_unique($m[1]) as $href) {
            $this->actingAs($user)->get(html_entity_decode($href))->assertOk();
        }

        // Os filtros usados existem mesmo nas listas.
        $this->assertContains(route('admin.mobile.leads.index', ['lead_status' => 'nova']), array_map('html_entity_decode', $m[1]));
        $this->assertContains(route('admin.mobile.form-proposals.index', ['status' => 'novo']), array_map('html_entity_decode', $m[1]));
    }
}
