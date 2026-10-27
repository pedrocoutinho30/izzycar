<?php

namespace Tests\Feature\Mobile;

use App\Enums\OpportunityStatus;
use App\Mail\ProposalSentMail;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Models\ProposalAttributeValue;
use App\Models\User;
use App\Models\VehicleAttribute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MobileProposalsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private int $codes = 0;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();
    }

    private function lead(array $attributes = []): Client
    {
        return Client::create($attributes + ['name' => 'Ana', 'email' => 'ana@example.com', 'is_lead' => true, 'lead_status' => 'em_contacto']);
    }

    private function request(Client $client, array $attributes = []): FormProposal
    {
        return FormProposal::create($attributes + [
            'name' => $client->name, 'email' => $client->email ?? 'x@example.com', 'phone' => '912345678',
            'client_id' => $client->id, 'brand' => 'Tesla', 'model' => 'Model 3', 'version' => 'Long Range',
            'budget' => 32000, 'km_max' => 60000, 'message' => 'Quero o carro até ao verão.', 'status' => 'novo',
        ]);
    }

    private function opportunity(FormProposal $request, array $attributes = []): ImportOpportunity
    {
        return $request->opportunities()->create($attributes + [
            'brand' => 'BMW', 'model' => 'i4', 'version' => 'eDrive40', 'year' => 2022, 'mileage' => 68400,
            'price' => 29500, 'fuel' => 'eletrico', 'listing_url' => 'https://anuncio.example/i4',
        ]);
    }

    private function proposal(Client $client, array $attributes = []): Proposal
    {
        return Proposal::create($attributes + [
            'client_id' => $client->id, 'brand' => 'Volvo', 'model' => 'XC40', 'transport_cost' => 1000,
            'commission_cost' => 500, 'proposed_car_value' => 20000,
            'proposal_code' => 'VX' . str_pad((string) ++$this->codes, 8, '0', STR_PAD_LEFT), 'status' => 'Pendente',
        ]);
    }

    private function payload(Client $client, array $overrides = []): array
    {
        return $overrides + [
            'client_id' => $client->id, 'brand' => 'BMW', 'model' => 'Série 3', 'version' => '320d',
            'year' => 2021, 'fuel' => 'Diesel', 'transport_cost' => 800, 'status' => 'Pendente',
        ];
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

    public function test_cotacao_criada_no_mobile_fica_associada_a_lead_e_visivel_no_desktop(): void
    {
        $lead = $this->lead();
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.proposals.create', ['client_id' => $lead->id]))
            ->assertOk()->assertSee('Ana')
            ->assertSee('name="client_id" value="' . $lead->id . '"', false)
            ->assertSee('inputmode="decimal"', false)
            ->assertSee('value="1350"', false) // custo de transporte por omissão, como no desktop
            ->assertDontSee('<table', false);

        $response = $this->actingAs($user)->post(route('admin.mobile.proposals.store'), $this->payload($lead));

        $p = Proposal::where('client_id', $lead->id)->firstOrFail();
        $response->assertRedirect(route('admin.mobile.proposals.show', $p->id));
        $this->assertSame('Pendente', $p->status);
        // Inicial da marca + do modelo + 8 aleatórios (o desktop gera 10 caracteres).
        $this->assertMatchesRegularExpression('/^BS[A-Z0-9]{8}$/', $p->proposal_code);
        $this->assertTrue(LeadActivity::where('client_id', $lead->id)->where('title', "Cotação #{$p->id} criada — BMW Série 3 320d")->exists());

        // Visível no desktop, dentro da ficha da lead e na lista de cotações.
        $this->actingAs($user)->get(route('admin.v2.leads.show', $lead->id))->assertOk()->assertSee('BMW');
        $this->actingAs($user)->get(route('admin.v2.proposals.index'))->assertOk()->assertSee('BMW');

        // E no mobile: ficha da lead liga à cotação; a lista mostra-a.
        $this->actingAs($user)->get(route('admin.mobile.leads.show', $lead->id))
            ->assertSee('href="' . route('admin.mobile.proposals.show', $p->id) . '"', false)
            ->assertSee('href="' . route('admin.mobile.proposals.create', ['client_id' => $lead->id]) . '"', false);
        $this->actingAs($user)->get(route('admin.mobile.proposals.index'))->assertOk()->assertSee('Série 3')->assertSee('Ana');
    }

    public function test_criar_a_partir_do_pedido_pre_preenche_e_passa_a_em_analise(): void
    {
        $lead = $this->lead();
        $request = $this->request($lead);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.form-proposals.show', $request->id))
            ->assertSee('href="' . route('admin.mobile.proposals.create', ['form_proposal_id' => $request->id]) . '"', false);

        $this->actingAs($user)->get(route('admin.mobile.proposals.create', ['form_proposal_id' => $request->id]))
            ->assertOk()->assertSee('Ana')
            ->assertSee('name="form_proposal_id" value="' . $request->id . '"', false)
            ->assertSee('value="Long Range"', false)
            ->assertSee('value="60000"', false)
            ->assertSee('Quero o carro até ao verão.');

        $this->actingAs($user)->post(route('admin.mobile.proposals.store'), $this->payload($lead, ['form_proposal_id' => $request->id]))
            ->assertSessionHasNoErrors();

        $this->assertSame('em_analise', $request->fresh()->status);
        $this->assertSame(Proposal::sole()->id, $request->fresh()->proposal_id);
    }

    public function test_cotacao_a_partir_de_oportunidade_liga_e_marca_selecionado(): void
    {
        $lead = $this->lead();
        $request = $this->request($lead);
        $opportunity = $this->opportunity($request);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.form-proposals.opportunities.show', [$request->id, $opportunity->id]))
            ->assertSee('href="' . route('admin.mobile.proposals.create', ['opportunity_id' => $opportunity->id]) . '"', false);

        // Os mesmos valores que o desktop (createFromOpportunity) pré-preenche.
        $this->actingAs($user)->get(route('admin.mobile.proposals.create', ['opportunity_id' => $opportunity->id]))
            ->assertOk()
            ->assertSee('name="import_opportunity_id" value="' . $opportunity->id . '"', false)
            ->assertSee('name="form_proposal_id" value="' . $request->id . '"', false)
            ->assertSee('value="eDrive40"', false)
            ->assertSee('value="68400"', false)
            ->assertSee('name="proposed_car_value" value="29500', false)
            ->assertSee('https://anuncio.example/i4')
            ->assertSee('<option value="Elétrico" selected', false)
            ->assertSee('não corresponde a uma marca/modelo do catálogo');

        $this->actingAs($user)->post(route('admin.mobile.proposals.store'), $this->payload($lead, [
            'import_opportunity_id' => $opportunity->id, 'brand' => 'BMW', 'model' => 'i4', 'fuel' => 'Elétrico',
        ]))->assertSessionHasNoErrors();

        $proposal = Proposal::sole();
        $opportunity->refresh();
        $this->assertSame($proposal->id, $opportunity->proposal_id);
        $this->assertSame(OpportunityStatus::Selected, $opportunity->status);
        // O pedido da oportunidade fica ligado e "em análise", como no desktop.
        $this->assertSame($proposal->id, $request->fresh()->proposal_id);
        $this->assertSame('em_analise', $request->fresh()->status);

        // Uma cotação por oportunidade: volta a abrir a que existe.
        $this->actingAs($user)->get(route('admin.mobile.proposals.create', ['opportunity_id' => $opportunity->id]))
            ->assertRedirect(route('admin.mobile.proposals.show', $proposal->id))
            ->assertSessionHas('info', 'Esta oportunidade já tem cotação.');
        $this->actingAs($user)->post(route('admin.mobile.proposals.store'), $this->payload($lead, ['import_opportunity_id' => $opportunity->id]))
            ->assertRedirect(route('admin.mobile.proposals.show', $proposal->id));
        $this->assertSame(1, Proposal::count());

        $this->actingAs($user)->get(route('admin.mobile.form-proposals.opportunities.show', [$request->id, $opportunity->id]))
            ->assertSee('Ver cotação #' . $proposal->id)
            ->assertDontSee(route('admin.mobile.proposals.create', ['opportunity_id' => $opportunity->id]), false);
    }

    public function test_oportunidade_ou_pedido_de_outro_cliente_sao_rejeitados(): void
    {
        $lead = $this->lead();
        $outro = $this->lead(['name' => 'Rui', 'email' => 'rui@example.com']);
        $pedidoAlheio = $this->request($outro);
        $oportunidadeAlheia = $this->opportunity($pedidoAlheio);
        $user = $this->mobileUser();

        $this->actingAs($user)->post(route('admin.mobile.proposals.store'), $this->payload($lead, ['import_opportunity_id' => $oportunidadeAlheia->id]))
            ->assertSessionHasErrors('import_opportunity_id');
        $this->actingAs($user)->post(route('admin.mobile.proposals.store'), $this->payload($lead, ['form_proposal_id' => $pedidoAlheio->id]))
            ->assertSessionHasErrors('form_proposal_id');
        // Oportunidade de outro pedido do mesmo cliente também não.
        $meuPedido = $this->request($lead);
        $this->actingAs($user)->post(route('admin.mobile.proposals.store'), $this->payload($lead, [
            'form_proposal_id' => $meuPedido->id, 'import_opportunity_id' => $this->opportunity($this->request($lead))->id,
        ]))->assertSessionHasErrors('import_opportunity_id');
        $this->actingAs($user)->get(route('admin.mobile.proposals.create', ['client_id' => $lead->id, 'opportunity_id' => $oportunidadeAlheia->id]))
            ->assertSessionHasErrors('import_opportunity_id');

        $this->assertSame(0, Proposal::count());
        $this->assertNull($oportunidadeAlheia->fresh()->proposal_id);
        $this->assertSame('novo', $pedidoAlheio->fresh()->status);
    }

    public function test_transport_cost_e_obrigatorio(): void
    {
        $lead = $this->lead();

        $this->actingAs($this->mobileUser())
            ->post(route('admin.mobile.proposals.store'), collect($this->payload($lead))->except('transport_cost')->all())
            ->assertSessionHasErrors('transport_cost');
        $this->actingAs($this->mobileUser())
            ->post(route('admin.mobile.proposals.store'), $this->payload($lead, ['fuel' => 'eletrico']))
            ->assertSessionHasErrors('fuel');

        $this->assertSame(0, Proposal::count());
    }

    public function test_estado_enviado_passa_por_changeStatus(): void
    {
        $lead = $this->lead();
        $proposal = $this->proposal($lead);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.proposals.show', $proposal->id))
            ->assertSee('Marcar como Enviado (email ao cliente)')->assertSee('Marcar como Enviado sem email');

        $this->actingAs($user)->patch(route('admin.mobile.proposals.status', $proposal->id), ['status' => 'Enviado', 'notify' => '1'])
            ->assertRedirect(route('admin.mobile.proposals.show', $proposal->id))
            ->assertSessionHas('success', 'Estado: Enviado. O cliente foi avisado por email.');

        $this->assertSame('Enviado', $proposal->fresh()->status);
        Mail::assertSent(ProposalSentMail::class, fn ($mail) => $mail->hasTo('ana@example.com'));
        $this->assertTrue(now()->addDays(2)->isSameDay($lead->fresh()->next_followup_at));
        $this->assertTrue(LeadActivity::where('title', "Cotação #{$proposal->id} enviada ao cliente")->exists());

        // Sem email (partilhada por WhatsApp): o estado muda, não sai email.
        $outra = $this->proposal($lead);
        $this->actingAs($user)->patch(route('admin.mobile.proposals.status', $outra->id), ['status' => 'Enviado', 'notify' => '0'])
            ->assertSessionHas('success', 'Estado: Enviado.');
        $this->assertSame('Enviado', $outra->fresh()->status);
        Mail::assertSent(ProposalSentMail::class, 1);

        $this->actingAs($user)->patch(route('admin.mobile.proposals.status', $outra->id), ['status' => 'Enviada'])
            ->assertSessionHasErrors('status');
    }

    public function test_aceitar_pelo_backoffice_converte_lead_e_cria_convertida(): void
    {
        $lead = $this->lead();
        $proposal = $this->proposal($lead);
        $request = $this->request($lead, ['status' => 'em_analise', 'proposal_id' => $proposal->id]);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.proposals.show', $proposal->id))
            ->assertSee('Aceitar cotação')->assertSee('confirm(', false);

        $this->actingAs($user)->post(route('admin.mobile.proposals.accept', $proposal->id))
            ->assertRedirect(route('admin.mobile.proposals.show', $proposal->id));

        $converted = ConvertedProposal::where('proposal_id', $proposal->id)->sole();
        $this->assertFalse((bool) $lead->fresh()->is_lead);
        $this->assertSame('Aprovada', $proposal->fresh()->status);
        $this->assertSame('convertido', $request->fresh()->status);
        Mail::assertNothingSent();

        // A ficha liga à convertida (desktop, até haver a página mobile).
        $this->actingAs($user)->get(route('admin.mobile.proposals.show', $proposal->id))
            ->assertSee('Cotação convertida #' . $converted->id)
            ->assertSee(route('admin.v2.converted-proposals.edit', $converted->id), false)
            ->assertDontSee('Aceitar cotação')->assertDontSee('Marcar como');

        // Aceitar outra vez não duplica.
        $this->actingAs($user)->post(route('admin.mobile.proposals.accept', $proposal->id))->assertSessionHas('error', 'Esta cotação já foi aceite.');
        $this->assertSame(1, ConvertedProposal::count());
    }

    public function test_cotacao_aceite_nao_muda_de_estado(): void
    {
        $lead = $this->lead();
        $proposal = $this->proposal($lead);
        $user = $this->mobileUser();
        $this->actingAs($user)->post(route('admin.mobile.proposals.accept', $proposal->id));

        $this->actingAs($user)->patch(route('admin.mobile.proposals.status', $proposal->id), ['status' => 'Reprovada'])
            ->assertSessionHas('error', "A cotação #{$proposal->id} já foi aceite e não pode mudar de estado — para a anular, cancele a cotação convertida.");
        $this->assertSame('Aprovada', $proposal->fresh()->status);

        $this->actingAs($user)->from(route('admin.mobile.proposals.edit', $proposal->id))
            ->put(route('admin.mobile.proposals.update', $proposal->id), $this->payload($lead, ['status' => 'Reprovada']))
            ->assertSessionHasErrors('status');
        $this->assertSame('Aprovada', $proposal->fresh()->status);
        $this->assertSame('Volvo', $proposal->fresh()->brand);

        // A edição só oferece "Aprovada" e grava o resto.
        $this->actingAs($user)->get(route('admin.mobile.proposals.edit', $proposal->id))
            ->assertOk()->assertSee('name="status" value="Aprovada"', false)->assertDontSee('id="mp-status"', false);
        $this->actingAs($user)->put(route('admin.mobile.proposals.update', $proposal->id), $this->payload($lead, ['status' => 'Aprovada']))
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.mobile.proposals.show', $proposal->id));
        $this->assertSame('BMW', $proposal->fresh()->brand);
        $this->assertSame(1, ConvertedProposal::count());
    }

    public function test_editar_no_mobile_mantem_os_atributos_e_limpa_links_vazios(): void
    {
        $lead = $this->lead();
        $proposal = $this->proposal($lead, ['other_links' => json_encode(['https://a.example/1'])]);
        $attribute = VehicleAttribute::create(['name' => 'Teto', 'key' => 'teto', 'type' => 'text', 'order' => 1]);
        ProposalAttributeValue::create(['proposal_id' => $proposal->id, 'attribute_id' => $attribute->id, 'value' => 'Panorâmico']);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.proposals.edit', $proposal->id))
            ->assertOk()->assertSee('value="https://a.example/1"', false)->assertSee('name="transport_cost" value="1000', false);

        $this->actingAs($user)->put(route('admin.mobile.proposals.update', $proposal->id), $this->payload($lead, [
            'other_links' => ['https://a.example/1', '', 'https://a.example/2'], 'status' => 'Sem resposta',
        ]))->assertSessionHasNoErrors();

        $proposal->refresh();
        $this->assertSame(['https://a.example/1', 'https://a.example/2'], json_decode($proposal->other_links, true));
        $this->assertSame('Sem resposta', $proposal->status);
        $this->assertSame(1, ProposalAttributeValue::where('proposal_id', $proposal->id)->count());
        $this->assertTrue(LeadActivity::where('title', "Cotação #{$proposal->id}: estado alterado para Sem resposta")->exists());
    }

    public function test_ficha_mostra_totais_validade_e_link_publico(): void
    {
        $lead = $this->lead();
        $proposal = $this->proposal($lead, ['url' => 'javascript:alert(1)', 'other_links' => json_encode(['https://anuncio.example/x'])]);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.proposals.show', $proposal->id))
            ->assertOk()
            ->assertSee('21.500,00 €')
            ->assertSee('válida até ' . $proposal->created_at->copy()->addDays(Proposal::VALIDITY_DAYS)->format('d/m/Y'))
            ->assertSee(route('proposals.detail', $proposal->proposal_code), false)
            ->assertSee('Copiar / partilhar')
            ->assertSee('navigator.share', false)
            ->assertSee('href="https://anuncio.example/x"', false)
            ->assertDontSee('href="javascript:', false)
            ->assertDontSee('<table', false);

        $this->travel(Proposal::VALIDITY_DAYS + 2)->days();
        $this->actingAs($user)->get(route('admin.mobile.proposals.show', $proposal->id))->assertSee('Expirada');
    }

    public function test_menu_mais_tem_cotacoes_e_lista_filtra(): void
    {
        $lead = $this->lead();
        $this->proposal($lead, ['brand' => 'Volvo', 'model' => 'XC40', 'status' => 'Enviado']);
        $this->proposal($lead, ['brand' => 'Tesla', 'model' => 'Model Y']);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.dashboard'))
            ->assertSee('href="' . route('admin.mobile.proposals.index') . '"', false)->assertSee('Cotações');
        $this->actingAs($user)->get(route('admin.mobile.proposals.index', ['status' => 'Enviado']))
            ->assertOk()->assertSee('XC40')->assertDontSee('Model Y');
        $this->actingAs($user)->get(route('admin.mobile.proposals.index', ['search' => 'Tesla']))
            ->assertOk()->assertSee('Model Y')->assertDontSee('XC40');
    }

    public function test_so_ver_proprias_ve_as_cotacoes_dos_seus_clientes_e_nao_altera(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['proposals.view.own', 'leads.view.own']);
        $meu = $this->lead(['name' => 'Cliente meu', 'email' => 'meu@example.com', 'owner_id' => $user->id]);
        $alheio = $this->lead(['name' => 'Cliente alheio', 'email' => 'alheio@example.com']);
        $minha = $this->proposal($meu, ['model' => 'Minha']);
        $alheia = $this->proposal($alheio, ['model' => 'Alheia']);

        $this->actingAs($user)->get(route('admin.mobile.proposals.index'))
            ->assertOk()->assertSee('Cliente meu')->assertDontSee('Cliente alheio');
        $this->actingAs($user)->get(route('admin.mobile.proposals.index', ['search' => 'Alheia']))
            ->assertOk()->assertDontSee('Cliente alheio');

        $this->actingAs($user)->get(route('admin.mobile.proposals.show', $minha->id))
            ->assertOk()->assertSee('Minha')
            ->assertDontSee('Marcar como')->assertDontSee('Aceitar cotação')->assertDontSee('Editar cotação');
        $this->actingAs($user)->get(route('admin.mobile.proposals.show', $alheia->id))->assertForbidden();

        // A ficha da lead abre a cotação mas não oferece "Criar cotação".
        $this->actingAs($user)->get(route('admin.mobile.leads.show', $meu->id))
            ->assertSee('href="' . route('admin.mobile.proposals.show', $minha->id) . '"', false)
            ->assertDontSee(route('admin.mobile.proposals.create', ['client_id' => $meu->id]), false);

        $this->actingAs($user)->get(route('admin.mobile.proposals.create', ['client_id' => $meu->id]))->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.proposals.store'), $this->payload($meu))->assertForbidden();
        $this->actingAs($user)->get(route('admin.mobile.proposals.edit', $minha->id))->assertForbidden();
        $this->actingAs($user)->put(route('admin.mobile.proposals.update', $minha->id), $this->payload($meu))->assertForbidden();
        $this->actingAs($user)->patch(route('admin.mobile.proposals.status', $minha->id), ['status' => 'Reprovada'])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.proposals.accept', $minha->id))->assertForbidden();

        $this->assertSame(2, Proposal::count());
        $this->assertSame('Pendente', $minha->fresh()->status);
        $this->assertSame(0, ConvertedProposal::count());
    }

    public function test_sem_enforce_o_controller_continua_a_bloquear_quem_nao_pode(): void
    {
        // PERMISSIONS_ENFORCE desligado (omissão): o mobile verifica ele próprio.
        $user = $this->userWith(['proposals.view.own', 'proposals.update.own']);
        $meu = $this->lead(['owner_id' => $user->id]);
        $minha = $this->proposal($meu);
        $alheia = $this->proposal($this->lead(['name' => 'Rui', 'email' => 'rui@example.com']));

        $this->actingAs($user)->get(route('admin.mobile.proposals.show', $alheia->id))->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.proposals.store'), $this->payload($meu))->assertForbidden();
        // Editar "próprias" não chega: as rotas de editar, estado e aceitar do desktop exigem editar todas.
        $this->actingAs($user)->patch(route('admin.mobile.proposals.status', $minha->id), ['status' => 'Reprovada'])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.proposals.accept', $minha->id))->assertForbidden();
        $this->actingAs($user)->put(route('admin.mobile.proposals.update', $minha->id), $this->payload($meu))->assertForbidden();

        $this->assertSame('Pendente', $minha->fresh()->status);
        $this->assertSame(0, ConvertedProposal::count());
    }

    public function test_criar_exige_poder_ver_a_lead(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['proposals.create', 'proposals.view.own', 'leads.view.own']);
        $meu = $this->lead(['owner_id' => $user->id]);
        $alheio = $this->lead(['name' => 'Rui', 'email' => 'rui@example.com']);
        $pedidoAlheio = $this->request($alheio);

        // Um client_id forjado não liga a cotação a uma lead que não vê.
        $this->actingAs($user)->get(route('admin.mobile.proposals.create', ['client_id' => $alheio->id]))->assertForbidden();
        $this->actingAs($user)->get(route('admin.mobile.proposals.create', ['form_proposal_id' => $pedidoAlheio->id]))->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.proposals.store'), $this->payload($alheio))->assertForbidden();
        $this->assertSame(0, Proposal::count());

        $this->actingAs($user)->get(route('admin.mobile.proposals.create', ['client_id' => $meu->id]))->assertOk();
        $this->actingAs($user)->post(route('admin.mobile.proposals.store'), $this->payload($meu))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($meu->id, Proposal::sole()->client_id);
    }
}
