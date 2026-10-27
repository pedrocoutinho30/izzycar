<?php

namespace Tests\Feature\Mobile;

use App\Mail\ProposalStatusUpdatedMail;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\Proposal;
use App\Models\StatusProposalHistory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MobileConvertedProposalsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function client(array $attributes = []): Client
    {
        return Client::create($attributes + ['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678', 'is_lead' => false, 'lead_status' => 'nova']);
    }

    private function converted(Client $client, array $attributes = []): ConvertedProposal
    {
        return ConvertedProposal::create($attributes + [
            'client_id' => $client->id, 'status' => 'Iniciada', 'brand' => 'BMW', 'modelCar' => 'i4', 'version' => 'eDrive40',
        ]);
    }

    /** Utilizador não admin, com acesso mobile, com apenas as permissões dadas. */
    private function userWith(array $permissions): User
    {
        $role = Role::findOrCreate('mobile-' . md5(implode(',', $permissions)), 'web');
        foreach ($permissions as $nome) {
            $role->givePermissionTo(Permission::findOrCreate($nome, 'web'));
        }
        $user = $this->backofficeUser($role->name);
        $user->forceFill(['access_mobile' => true])->save();

        return $user;
    }

    public function test_lista_filtra_por_etapa_cancelada_inclui_cancelado_e_pesquisa(): void
    {
        $ana = $this->client();
        $rui = $this->client(['name' => 'Rui Costa', 'email' => 'rui@example.com']);
        $this->converted($ana, ['modelCar' => 'ModeloIpo', 'status' => 'IPO']);
        $this->converted($rui, ['brand' => 'Audi', 'modelCar' => 'ModeloTransporte', 'status' => 'Transporte']);
        $this->converted($ana, ['modelCar' => 'ModeloCancelada', 'status' => 'Cancelada']);
        $this->converted($ana, ['modelCar' => 'ModeloCancelado', 'status' => 'Cancelado']);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.index'))
            ->assertOk()->assertSee('ModeloIpo')->assertSee('ModeloTransporte')->assertSee('ModeloCancelado')
            ->assertSee('href="' . route('admin.mobile.converted-proposals.index', ['status' => 'Matriculação']) . '"', false)
            ->assertDontSee('<table', false);

        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.index', ['status' => 'IPO']))
            ->assertOk()->assertSee('ModeloIpo')->assertDontSee('ModeloTransporte')->assertDontSee('ModeloCancelad');

        // "Cancelada" junta a grafia antiga "Cancelado".
        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.index', ['status' => 'Cancelada']))
            ->assertOk()->assertSee('ModeloCancelada')->assertSee('ModeloCancelado')->assertDontSee('ModeloIpo');

        // Pesquisa por cliente e por viatura.
        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.index', ['search' => 'Rui']))
            ->assertOk()->assertSee('ModeloTransporte')->assertDontSee('ModeloIpo');
        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.index', ['search' => 'ModeloIpo']))
            ->assertOk()->assertSee('ModeloIpo')->assertDontSee('ModeloTransporte');

        // Etapa desconhecida é ignorada (mostra todas).
        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.index', ['status' => 'Inventado']))
            ->assertOk()->assertSee('ModeloIpo')->assertSee('ModeloTransporte');
    }

    public function test_lista_pagina_com_carregar_mais(): void
    {
        $ana = $this->client();
        foreach (range(1, 16) as $i) {
            $this->converted($ana, ['modelCar' => "Modelo{$i}"]);
        }

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.converted-proposals.index'))
            ->assertOk()->assertSee('Carregar mais')->assertSee('id="m-load-more"', false);
    }

    public function test_ficha_mostra_pipeline_historico_pagamentos_e_ligacoes(): void
    {
        $lead = Client::create(['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678', 'is_lead' => true, 'lead_status' => 'em_contacto']);
        $proposal = Proposal::create(['client_id' => $lead->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 1000, 'proposal_code' => 'BI00000001', 'status' => 'Aprovada']);
        $converted = $this->converted($lead, [
            'proposal_id' => $proposal->id, 'matricula_destino' => 'AA-00-BB', 'vin' => 'wba 123',
            'valor_primeira_tranche' => 750, 'primeira_tranche_pago' => true, 'valor_segunda_tranche' => 750,
            'custo_transporte' => 1200, 'transporte_pago' => true, 'valor_carro' => 30000,
            'observacoes' => 'Cliente quer entrega em Braga.', 'url' => 'javascript:alert(1)',
        ]);
        $converted->update(['status' => 'Transporte']);
        $converted->update(['status' => 'IPO']);
        $user = $this->mobileUser();

        $response = $this->actingAs($user)->get(route('admin.mobile.converted-proposals.show', $converted->id))->assertOk();
        $response->assertSee('Estado do processo')->assertSee('aria-current="step"', false)
            ->assertSee('Registo automóvel')
            ->assertSeeInOrder(['Transporte', 'IPO'])
            ->assertSee('Histórico')
            ->assertSee('WBA123')
            ->assertSee('AA-00-BB')
            ->assertSee('1.ª tranche')->assertSee('Recebida a ' . now()->format('d/m/Y'))
            ->assertSee('Total do serviço')->assertSee('Cliente quer entrega em Braga.')
            ->assertSee('Mudar estado')->assertSee('name="notify"', false)
            ->assertSee('href="' . route('admin.mobile.proposals.show', $proposal->id) . '"', false)
            ->assertSee('href="' . route('admin.mobile.leads.show', $lead->id) . '"', false)
            ->assertSee('href="tel:', false)->assertSee('wa.me', false)
            ->assertSee('href="' . route('admin.v2.converted-proposals.edit', $converted->id) . '"', false)
            ->assertDontSee('href="javascript:', false)
            ->assertDontSee('<table', false);

        // O acompanhamento público usa ids sequenciais no passado: o mobile não o expõe.
        $response->assertDontSee($converted->tracking_code)->assertDontSee('/timeline', false);
        $this->assertSame(2, $response->viewData('history')->count());
    }

    public function test_mudar_estado_grava_historico_e_avisa_o_cliente(): void
    {
        Mail::fake();
        $converted = $this->converted($this->client());
        $user = $this->mobileUser();

        $this->actingAs($user)->patch(route('admin.mobile.converted-proposals.status', $converted->id), ['status' => 'Transporte', 'notify' => '1'])
            ->assertRedirect(route('admin.mobile.converted-proposals.show', $converted->id))
            ->assertSessionHas('success', 'Estado: Transporte. O cliente foi avisado por email.');

        $this->assertSame('Transporte', $converted->fresh()->status);
        $history = StatusProposalHistory::where('converted_proposal_id', $converted->id)->sole();
        $this->assertSame(['Iniciada', 'Transporte'], [$history->old_status, $history->new_status]);
        Mail::assertSent(ProposalStatusUpdatedMail::class, fn ($mail) => $mail->hasTo('ana@example.com'));

        // Sem email: o checkbox desmarcado envia notify=0.
        $this->actingAs($user)->patch(route('admin.mobile.converted-proposals.status', $converted->id), ['status' => 'IPO', 'notify' => '0'])
            ->assertSessionHas('success', 'Estado: IPO.');
        Mail::assertSent(ProposalStatusUpdatedMail::class, 1);

        // O mesmo estado não grava nem envia.
        $this->actingAs($user)->patch(route('admin.mobile.converted-proposals.status', $converted->id), ['status' => 'IPO', 'notify' => '1'])
            ->assertSessionHas('info', 'O estado já era este.');
        $this->assertSame(2, StatusProposalHistory::count());
        Mail::assertSent(ProposalStatusUpdatedMail::class, 1);

        // Visível no desktop.
        $this->actingAs($user)->get(route('admin.v2.converted-proposals.edit', $converted->id))->assertOk()->assertSee('IPO');
    }

    public function test_estado_invalido_e_rejeitado(): void
    {
        Mail::fake();
        $converted = $this->converted($this->client());

        $this->actingAs($this->mobileUser())->from(route('admin.mobile.converted-proposals.show', $converted->id))
            ->patch(route('admin.mobile.converted-proposals.status', $converted->id), ['status' => 'Inventado'])
            ->assertRedirect(route('admin.mobile.converted-proposals.show', $converted->id))
            ->assertSessionHasErrors(['status' => 'Estado inválido.']);

        $this->assertSame('Iniciada', $converted->fresh()->status);
        $this->assertSame(0, StatusProposalHistory::count());
        Mail::assertNothingSent();
    }

    public function test_falha_no_email_nao_impede_a_mudanca(): void
    {
        Exceptions::fake();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP em baixo'));
        $converted = $this->converted($this->client());

        $this->actingAs($this->mobileUser())->patch(route('admin.mobile.converted-proposals.status', $converted->id), ['status' => 'Entrega', 'notify' => '1'])
            ->assertRedirect(route('admin.mobile.converted-proposals.show', $converted->id))
            ->assertSessionHas('success', 'Estado: Entrega.');

        $this->assertSame('Entrega', $converted->fresh()->status);
        $this->assertSame(1, StatusProposalHistory::count());
        Exceptions::assertReported(\RuntimeException::class);
    }

    public function test_cliente_sem_email_nao_oferece_aviso(): void
    {
        Mail::fake();
        $converted = $this->converted($this->client(['email' => null]));
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.show', $converted->id))
            ->assertSee('O cliente não tem email: não é avisado.')->assertDontSee('Enviar email ao cliente');
        $this->actingAs($user)->patch(route('admin.mobile.converted-proposals.status', $converted->id), ['status' => 'DAV', 'notify' => '1'])
            ->assertSessionHas('success', 'Estado: DAV.');
        Mail::assertNothingSent();
    }

    public function test_cancelar_e_grafia_antiga(): void
    {
        Mail::fake();
        $converted = $this->converted($this->client(), ['status' => 'Transporte']);
        $antiga = $this->converted($this->client(['name' => 'Rui', 'email' => 'rui@example.com']), ['status' => 'Cancelado']);
        $user = $this->mobileUser();

        $this->actingAs($user)->patch(route('admin.mobile.converted-proposals.status', $converted->id), ['status' => 'Cancelada', 'notify' => '0'])
            ->assertSessionHas('success', 'Estado: Cancelada.');
        $this->assertTrue($converted->fresh()->isCancelled());

        foreach ([$converted, $antiga] as $cancelada) {
            $this->actingAs($user)->get(route('admin.mobile.converted-proposals.show', $cancelada->id))
                ->assertOk()->assertSee('Esta cotação convertida foi cancelada.')->assertDontSee('aria-current="step"', false);
        }
        // A grafia antiga também se pode corrigir para uma etapa.
        $this->actingAs($user)->patch(route('admin.mobile.converted-proposals.status', $antiga->id), ['status' => 'Iniciada', 'notify' => '0'])
            ->assertSessionHas('success');
        $this->assertSame('Iniciada', $antiga->fresh()->status);
    }

    public function test_aceitar_cotacao_no_mobile_cria_convertida_visivel_nas_listas(): void
    {
        Mail::fake();
        $lead = Client::create(['name' => 'Beatriz', 'email' => 'bea@example.com', 'is_lead' => true, 'lead_status' => 'em_contacto']);
        $proposal = Proposal::create([
            'client_id' => $lead->id, 'brand' => 'Volvo', 'model' => 'XC40', 'transport_cost' => 1000,
            'commission_cost' => 500, 'proposed_car_value' => 20000, 'proposal_code' => 'VX00000001', 'status' => 'Pendente',
        ]);
        FormProposal::create([
            'name' => 'Beatriz', 'email' => 'bea@example.com', 'phone' => '912345678', 'client_id' => $lead->id,
            'brand' => 'Volvo', 'model' => 'XC40', 'status' => 'em_analise', 'proposal_id' => $proposal->id,
        ]);
        $user = $this->mobileUser();

        $this->actingAs($user)->post(route('admin.mobile.proposals.accept', $proposal->id))->assertRedirect();
        $converted = ConvertedProposal::where('proposal_id', $proposal->id)->sole();

        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.index'))
            ->assertOk()->assertSee('XC40')->assertSee('Beatriz')
            ->assertSee('href="' . route('admin.mobile.converted-proposals.show', $converted->id) . '"', false);
        $this->actingAs($user)->get(route('admin.v2.converted-proposals.index'))->assertOk()->assertSee('Beatriz');
        $this->actingAs($user)->get(route('admin.mobile.proposals.show', $proposal->id))
            ->assertSee('href="' . route('admin.mobile.converted-proposals.show', $converted->id) . '"', false);
        // A lead passou a cliente: a ficha liga à cotação de origem e ao cliente (desktop).
        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.show', $converted->id))
            ->assertOk()->assertSee('Iniciada')
            ->assertSee('href="' . route('admin.mobile.proposals.show', $proposal->id) . '"', false)
            ->assertSee('href="' . route('admin.v2.clients.show', $lead->id) . '"', false);
    }

    public function test_ficha_da_lead_liga_as_convertidas_e_menu_mais(): void
    {
        $lead = Client::create(['name' => 'Ana', 'email' => 'ana@example.com', 'is_lead' => true, 'lead_status' => 'em_contacto']);
        $converted = $this->converted($lead);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.leads.show', $lead->id))
            ->assertOk()->assertSee('href="' . route('admin.mobile.converted-proposals.show', $converted->id) . '"', false);
        $this->actingAs($user)->get(route('admin.mobile.dashboard'))
            ->assertSee('href="' . route('admin.mobile.converted-proposals.index') . '"', false)->assertSee('Convertidas');
    }

    public function test_so_ver_proprias_ve_as_convertidas_dos_seus_clientes_e_nao_muda_estado(): void
    {
        config(['permissions.enforce' => true]);
        Mail::fake();
        $user = $this->userWith(['proposals.view.own', 'leads.view.own']);
        $meu = Client::create(['name' => 'Cliente meu', 'email' => 'meu@example.com', 'is_lead' => true, 'lead_status' => 'em_contacto', 'owner_id' => $user->id]);
        $alheio = $this->client(['name' => 'Cliente alheio', 'email' => 'alheio@example.com']);
        $minha = $this->converted($meu, ['modelCar' => 'Minha', 'valor_primeira_tranche' => 750, 'observacoes' => 'Nota interna']);
        $alheia = $this->converted($alheio, ['modelCar' => 'Alheia']);

        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.index'))
            ->assertOk()->assertSee('Cliente meu')->assertDontSee('Cliente alheio');
        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.index', ['search' => 'Alheia']))
            ->assertOk()->assertDontSee('Cliente alheio');

        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.show', $minha->id))
            ->assertOk()->assertSee('Minha')->assertSee('Estado do processo')
            ->assertDontSee('Mudar estado')->assertDontSee('Pagamentos e custos')->assertDontSee('1.ª tranche')
            ->assertDontSee('Nota interna')->assertDontSee('Abrir no desktop');
        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.show', $alheia->id))->assertForbidden();

        $this->actingAs($user)->patch(route('admin.mobile.converted-proposals.status', $minha->id), ['status' => 'Transporte'])->assertForbidden();
        $this->actingAs($user)->patch(route('admin.mobile.converted-proposals.status', $alheia->id), ['status' => 'Transporte'])->assertForbidden();
        $this->assertSame('Iniciada', $minha->fresh()->status);
        $this->assertSame(0, StatusProposalHistory::count());
        Mail::assertNothingSent();

        // O desktop continua a exigir a permissão das convertidas.
        $this->actingAs($user)->get(route('admin.v2.converted-proposals.index'))->assertForbidden();

        // A ficha da lead e o menu ligam às convertidas que pode ver.
        $this->actingAs($user)->get(route('admin.mobile.leads.show', $meu->id))
            ->assertSee('href="' . route('admin.mobile.converted-proposals.show', $minha->id) . '"', false);
        $this->actingAs($user)->get(route('admin.mobile.leads.index'))
            ->assertOk()->assertSee('href="' . route('admin.mobile.converted-proposals.index') . '"', false);
    }

    public function test_sem_enforce_o_controller_bloqueia_quem_nao_pode(): void
    {
        Mail::fake();
        $converted = $this->converted($this->client());

        // Sem permissões de cotações: não vê nada e o menu não mostra "Convertidas".
        $semAcesso = $this->userWith(['leads.view.own']);
        $this->actingAs($semAcesso)->get(route('admin.mobile.converted-proposals.index'))->assertForbidden();
        $this->actingAs($semAcesso)->get(route('admin.mobile.converted-proposals.show', $converted->id))->assertForbidden();
        $this->actingAs($semAcesso)->get(route('admin.mobile.dashboard'))
            ->assertDontSee('href="' . route('admin.mobile.converted-proposals.index') . '"', false);

        // Ver todas sem editar: consulta, mas não muda o estado nem vê os pagamentos.
        $leitor = $this->userWith(['converted-proposals.view']);
        $this->actingAs($leitor)->get(route('admin.mobile.converted-proposals.index'))->assertOk()->assertSee('i4');
        $this->actingAs($leitor)->get(route('admin.mobile.converted-proposals.show', $converted->id))
            ->assertOk()->assertDontSee('Mudar estado')->assertDontSee('Pagamentos e custos');
        $this->actingAs($leitor)->patch(route('admin.mobile.converted-proposals.status', $converted->id), ['status' => 'IPO'])->assertForbidden();

        // Próprias sem enforce: a alheia continua bloqueada no controller.
        $proprio = $this->userWith(['proposals.view.own']);
        $this->actingAs($proprio)->get(route('admin.mobile.converted-proposals.show', $converted->id))->assertForbidden();
        $this->actingAs($proprio)->get(route('admin.mobile.converted-proposals.index'))->assertOk()->assertDontSee('i4');

        $this->assertSame('Iniciada', $converted->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_comissao_do_angariador_so_para_quem_ve_comissoes(): void
    {
        $angariador = $this->backofficeUser('angariador', ['commission_fixed_value' => 300]);
        $converted = $this->converted($this->client(), ['owner_id' => $angariador->id]);
        $this->assertSame(300.0, $converted->fresh()->angariadorCommissionAmount());

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.converted-proposals.show', $converted->id))
            ->assertSee('Comissão do angariador')->assertSee('300,00 €');

        $gestor = $this->userWith(['converted-proposals.view', 'converted-proposals.update']);
        $this->actingAs($gestor)->get(route('admin.mobile.converted-proposals.show', $converted->id))
            ->assertOk()->assertSee('Pagamentos e custos')->assertSee('Mudar estado')
            ->assertDontSee('Comissão do angariador')->assertDontSee('300,00 €');

        $comissoes = $this->userWith(['converted-proposals.view', 'commissions.view.all']);
        $this->actingAs($comissoes)->get(route('admin.mobile.converted-proposals.show', $converted->id))
            ->assertSee('Comissão do angariador');
    }
}
