<?php

namespace Tests\Feature\Mobile;

use App\Enums\ChecklistItemStatus;
use App\Enums\OpportunityStatus;
use App\Models\Client;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\LeadActivity;
use App\Models\OpportunityChecklistItem;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MobileFormProposalsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        $this->vehicleCatalog(['Volvo' => ['XC40', 'EX30'], 'Tesla' => ['Model 3']]);
    }

    /** Pedido do site com todos os campos que o formulário envia. */
    private function siteRequest(array $attributes = []): FormProposal
    {
        return FormProposal::create($attributes + [
            'name' => 'Sofia Almeida',
            'email' => 'sofia@example.com',
            'phone' => '912345678',
            'source' => 'pagina-importacao',
            'buyer_type' => 'empresa',
            'payment_type' => 'financiamento',
            'estimated_purchase_date' => '1_3_meses',
            'brand' => 'Tesla',
            'model' => 'Model 3',
            'version' => 'Long Range',
            'fuel' => 'Elétrico',
            'gearbox' => 'automatica',
            'year_min' => 2021,
            'km_max' => 60000,
            'color' => 'Preto',
            'budget' => '32000',
            'extras' => 'Teto panorâmico',
            'ad_option' => 'sim',
            'ad_links' => "https://anuncio.example/1\nhttps://anuncio.example/2",
            'retoma_option' => 'sim',
            'retoma_brand' => 'Renault',
            'retoma_model' => 'Clio',
            'retoma_year' => 2015,
            'retoma_km' => 150000,
            'retoma_fuel' => 'Diesel',
            'retoma_info' => 'Pequeno risco na porta',
            'message' => 'Quero o carro até ao verão.',
            'status' => 'novo',
        ]);
    }

    private function opportunity(FormProposal $formProposal, array $attributes = []): ImportOpportunity
    {
        return $formProposal->opportunities()->create($attributes + ['brand' => 'Tesla', 'model' => 'Model 3', 'year' => 2022, 'price' => 23900, 'fuel' => 'gasolina']);
    }

    private function checklistItem(string $slug): OpportunityChecklistItem
    {
        return OpportunityChecklistItem::whereHas('checklist', fn ($q) => $q->where('slug', 'base'))->where('slug', $slug)->firstOrFail();
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

    public function test_pedido_do_site_mostra_todos_os_campos_submetidos(): void
    {
        $lead = Client::create(['name' => 'Sofia Almeida', 'is_lead' => true, 'lead_status' => 'nova']);
        $request = $this->siteRequest(['client_id' => $lead->id]);

        $response = $this->actingAs($this->mobileUser())->get(route('admin.mobile.form-proposals.show', $request->id))->assertOk();

        foreach ([
            'Sofia Almeida', 'sofia@example.com', 'Site · pagina-importacao', 'Empresa', 'Financiamento', '1-3 meses',
            'Tesla', 'Model 3', 'Long Range', 'Elétrico', 'automatica', '2021', '60.000 km', 'Preto', '32.000 €', 'Teto panorâmico',
            'https://anuncio.example/1', 'https://anuncio.example/2',
            'Retoma', 'Renault', 'Clio', '2015', '150.000 km', 'Diesel', 'Pequeno risco na porta',
            'Quero o carro até ao verão.', 'Recebido em',
        ] as $valor) {
            $response->assertSee($valor);
        }

        $response->assertSee('href="tel:912345678"', false)
            ->assertSee('href="https://wa.me/351912345678"', false)
            ->assertSee('href="' . route('admin.mobile.leads.show', $lead->id) . '"', false)
            ->assertDontSee('<table', false);
    }

    public function test_links_do_anuncio_so_sao_ligacoes_se_forem_http_ou_https(): void
    {
        $request = $this->siteRequest(['ad_links' => "javascript:alert(1)\n  JAVASCRIPT:alert(2)\nhttps://anuncio.example/seguro"]);
        $opportunity = $this->opportunity($request, ['listing_url' => 'javascript:alert(3)']);

        $response = $this->actingAs($this->mobileUser())->get(route('admin.mobile.form-proposals.show', $request->id))->assertOk();

        $response->assertSee('href="https://anuncio.example/seguro"', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee('javascript:alert(1)', false) // visível como texto escapado, sem ser ligação
            ->assertDontSee('href="javascript:', false)
            ->assertDontSee('href="JAVASCRIPT:', false);

        $this->actingAs($this->mobileUser())
            ->get(route('admin.mobile.form-proposals.opportunities.show', [$request->id, $opportunity->id]))
            ->assertOk()
            ->assertDontSee('href="javascript:', false);
    }

    public function test_lista_filtra_por_estado_e_pesquisa(): void
    {
        $this->siteRequest(['name' => 'Ana Nova', 'brand' => 'Volvo', 'model' => 'XC40', 'status' => 'novo']);
        $this->siteRequest(['name' => 'Rui Analise', 'brand' => 'Tesla', 'model' => 'Model 3', 'status' => 'em_analise', 'email' => 'rui@example.com']);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.form-proposals.index', ['status' => 'em_analise']))
            ->assertOk()->assertSee('Rui Analise')->assertDontSee('Ana Nova')->assertDontSee('<table', false);
        $this->actingAs($user)->get(route('admin.mobile.form-proposals.index', ['search' => 'Volvo']))
            ->assertOk()->assertSee('Ana Nova')->assertDontSee('Rui Analise');
    }

    public function test_ficha_da_lead_liga_aos_pedidos_e_ao_novo_pedido(): void
    {
        $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova']);
        $request = $this->siteRequest(['client_id' => $lead->id]);

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.leads.show', $lead->id))
            ->assertOk()
            ->assertSee('href="' . route('admin.mobile.form-proposals.show', $request->id) . '"', false)
            ->assertSee('href="' . route('admin.mobile.form-proposals.create', ['client_id' => $lead->id]) . '"', false);
    }

    public function test_criar_pedido_manual_a_partir_da_lead(): void
    {
        $lead = Client::create(['name' => 'Maria', 'email' => 'maria@example.com', 'phone' => '913000000', 'is_lead' => true, 'lead_status' => 'nova']);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.form-proposals.create', ['client_id' => $lead->id]))
            ->assertOk()->assertSee('Maria')->assertSee('<option value="Volvo"', false);
        $this->actingAs($user)->get(route('admin.mobile.form-proposals.create'))->assertSessionHasErrors('client_id');

        // Marca/modelo têm de vir do catálogo.
        $this->actingAs($user)->post(route('admin.mobile.form-proposals.store'), ['client_id' => $lead->id, 'brand' => 'Volvo', 'model' => 'Inventado'])
            ->assertSessionHasErrors('model');
        $this->actingAs($user)->post(route('admin.mobile.form-proposals.store'), ['brand' => 'Volvo'])
            ->assertSessionHasErrors('client_id');
        $this->assertSame(0, FormProposal::count());

        $response = $this->actingAs($user)->post(route('admin.mobile.form-proposals.store'), [
            'client_id' => $lead->id, 'title' => 'Segundo carro', 'brand' => 'Volvo', 'model' => 'EX30', 'budget' => 35000,
        ]);

        $request = FormProposal::sole();
        $response->assertRedirect(route('admin.mobile.form-proposals.show', $request->id))
            ->assertSessionHas('success', 'Pedido criado. Já pode adicionar oportunidades.');
        $this->assertSame('em_analise', $request->status);
        $this->assertSame(FormProposal::ORIGIN_MANUAL, $request->origin);
        $this->assertSame($lead->id, (int) $request->client_id);
        $this->assertSame('Maria', $request->name);
        $this->assertSame($user->id, $request->created_by);
        $this->assertSame(1, LeadActivity::where('client_id', $lead->id)->where('title', 'Pedido de importação criado')->count());

        $this->actingAs($user)->get(route('admin.mobile.form-proposals.show', $request->id))
            ->assertOk()->assertSee('Segundo carro')->assertSee('Criado no backoffice');
    }

    public function test_mudar_estado(): void
    {
        $request = $this->siteRequest();
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.form-proposals.show', $request->id))->assertSee('Marcar como Em análise');

        $this->actingAs($user)->patch(route('admin.mobile.form-proposals.status', $request->id), ['status' => 'em_analise'])
            ->assertRedirect(route('admin.mobile.form-proposals.show', $request->id))
            ->assertSessionHas('success', 'Estado atualizado!');
        $this->assertSame('em_analise', $request->fresh()->status);

        // "Convertido" é automático.
        $this->actingAs($user)->patch(route('admin.mobile.form-proposals.status', $request->id), ['status' => 'convertido'])
            ->assertSessionHasErrors('status');
        $this->assertSame('em_analise', $request->fresh()->status);
    }

    public function test_oportunidade_mostra_checklist_e_contactos(): void
    {
        $request = $this->siteRequest();
        $seller = Seller::create(['name' => 'Autohaus XYZ', 'country' => 'DE']);
        $opportunity = $this->opportunity($request, ['seller_id' => $seller->id, 'notes' => 'Nota antiga']);
        $opportunity->contacts()->create(['contacted_at' => now(), 'method' => 'whatsapp', 'type' => 'enviado', 'message' => 'Pedi o VIN']);
        $user = $this->mobileUser();

        // A ficha do pedido lista a oportunidade.
        $this->actingAs($user)->get(route('admin.mobile.form-proposals.show', $request->id))
            ->assertSee('href="' . route('admin.mobile.form-proposals.opportunities.show', [$request->id, $opportunity->id]) . '"', false)
            ->assertSee('Nova oportunidade');

        $this->actingAs($user)->get(route('admin.mobile.form-proposals.opportunities.show', [$request->id, $opportunity->id]))
            ->assertOk()
            ->assertSee('Tesla Model 3')->assertSee('Autohaus XYZ')
            ->assertSee('VIN')->assertSee('Histórico de acidentes')->assertSee('0</strong>/10 confirmados', false)
            ->assertSee('Pedi o VIN')->assertSee('Mensagem enviada')
            ->assertSee('Nota antiga')
            ->assertDontSee('<table', false);
    }

    public function test_acoes_da_oportunidade_voltam_a_pagina_mobile(): void
    {
        $request = $this->siteRequest();
        $opportunity = $this->opportunity($request);
        $user = $this->mobileUser();
        $rota = fn (string $nome, ...$extra) => route("admin.mobile.form-proposals.opportunities.{$nome}", [$request->id, $opportunity->id, ...$extra]);
        $ficha = $rota('show');

        $this->actingAs($user)->patch($rota('status'), ['status' => 'em_negociacao'])
            ->assertRedirect($ficha)->assertSessionHas('success', 'Estado: Em negociação.');
        $this->assertSame(OpportunityStatus::Negotiating, $opportunity->fresh()->status);
        $this->actingAs($user)->patch($rota('status'), ['status' => 'xpto'])->assertSessionHasErrors(['status' => 'Estado inválido.']);

        $this->actingAs($user)->patch($rota('checklist'), ['item_id' => $this->checklistItem('vin')->id, 'status' => 'confirmed'])
            ->assertRedirect($ficha . '#checklist');
        $this->assertSame(ChecklistItemStatus::Confirmed, $opportunity->checklistEntries()->sole()->status);
        $this->actingAs($user)->get($ficha)->assertSee('1</strong>/10 confirmados', false);

        $this->actingAs($user)->patch($rota('notes'), ['notes' => 'Preço negociável'])->assertRedirect($ficha . '#notas');
        $this->assertSame('Preço negociável', $opportunity->fresh()->notes);

        $this->actingAs($user)->post($rota('contacts.store'), ['contacted_at' => now()->format('Y-m-d H:i'), 'method' => 'email', 'type' => 'enviado', 'message' => 'Olá'])
            ->assertRedirect($ficha . '#historico');
        $contacto = $opportunity->contacts()->sole();
        // Primeiro contacto "outreach" avança o estado só se ainda estava por contactar (aqui já em negociação).
        $this->assertSame(OpportunityStatus::Negotiating, $opportunity->fresh()->status);
        $this->assertNotNull($opportunity->fresh()->last_contacted_at);

        $this->actingAs($user)->delete($rota('contacts.destroy', $contacto->id))->assertRedirect($ficha . '#historico');
        $this->assertSame(0, $opportunity->contacts()->count());
    }

    public function test_criar_oportunidade_dentro_do_pedido(): void
    {
        $request = $this->siteRequest();
        $user = $this->mobileUser();

        $this->actingAs($user)->post(route('admin.mobile.form-proposals.opportunities.store', $request->id), ['brand' => 'Tesla', 'model' => 'Inventado'])
            ->assertSessionHasErrors('model');

        $response = $this->actingAs($user)->post(route('admin.mobile.form-proposals.opportunities.store', $request->id), ['brand' => 'Volvo', 'model' => 'EX30', 'price' => 30000]);

        $opportunity = ImportOpportunity::sole();
        $response->assertRedirect(route('admin.mobile.form-proposals.opportunities.show', [$request->id, $opportunity->id]));
        $this->assertSame($request->id, $opportunity->form_proposal_id);
        $this->assertSame($user->id, $opportunity->created_by);
    }

    public function test_oportunidade_de_outro_pedido_da_404(): void
    {
        $request = $this->siteRequest();
        $outro = $this->siteRequest(['name' => 'Outro']);
        $alheia = $this->opportunity($outro);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.form-proposals.opportunities.show', [$request->id, $alheia->id]))->assertNotFound();
        $this->actingAs($user)->patch(route('admin.mobile.form-proposals.opportunities.status', [$request->id, $alheia->id]), ['status' => 'rejeitado'])->assertNotFound();
        $this->actingAs($user)->patch(route('admin.mobile.form-proposals.opportunities.notes', [$request->id, $alheia->id]), ['notes' => 'x'])->assertNotFound();

        // Contacto de outra oportunidade também não.
        $contacto = $alheia->contacts()->create(['contacted_at' => now(), 'method' => 'email', 'type' => 'nota']);
        $minha = $this->opportunity($request);
        $this->actingAs($user)->delete(route('admin.mobile.form-proposals.opportunities.contacts.destroy', [$request->id, $minha->id, $contacto->id]))->assertNotFound();

        $this->assertSame(OpportunityStatus::ToContact, $alheia->fresh()->status);
        $this->assertSame(1, $alheia->contacts()->count());
    }

    public function test_so_ver_proprios_ve_os_pedidos_dos_seus_clientes_e_nao_edita(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['form-proposals.view.own', 'leads.view.own']);
        $meu = Client::create(['name' => 'Cliente meu', 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $user->id]);
        $alheio = Client::create(['name' => 'Cliente alheio', 'is_lead' => true, 'lead_status' => 'nova']);
        $meuPedido = $this->siteRequest(['name' => 'Pedido meu', 'client_id' => $meu->id]);
        $pedidoAlheio = $this->siteRequest(['name' => 'Pedido alheio', 'client_id' => $alheio->id]);
        $semCliente = $this->siteRequest(['name' => 'Pedido sem cliente']);
        $opportunity = $this->opportunity($meuPedido);

        $this->actingAs($user)->get(route('admin.mobile.form-proposals.index'))
            ->assertOk()->assertSee('Cliente meu')->assertDontSee('Cliente alheio')->assertDontSee('Pedido sem cliente');
        $this->actingAs($user)->get(route('admin.mobile.form-proposals.index', ['search' => 'Pedido']))
            ->assertOk()->assertDontSee('Pedido alheio');

        // Vê o seu, sem controlos de edição nem oportunidades (como no desktop, exigem ver todos).
        $this->actingAs($user)->get(route('admin.mobile.form-proposals.show', $meuPedido->id))
            ->assertOk()->assertSee('Pedido meu')
            ->assertDontSee('Marcar como')->assertDontSee('Nova oportunidade')
            ->assertDontSee(route('admin.mobile.form-proposals.opportunities.show', [$meuPedido->id, $opportunity->id]), false);
        $this->actingAs($user)->get(route('admin.mobile.form-proposals.show', $pedidoAlheio->id))->assertForbidden();
        $this->actingAs($user)->get(route('admin.mobile.form-proposals.show', $semCliente->id))->assertForbidden();

        // A ficha da lead abre o pedido mas não oferece "Novo pedido".
        $this->actingAs($user)->get(route('admin.mobile.leads.show', $meu->id))
            ->assertSee('href="' . route('admin.mobile.form-proposals.show', $meuPedido->id) . '"', false)
            ->assertDontSee(route('admin.mobile.form-proposals.create', ['client_id' => $meu->id]), false);

        $this->actingAs($user)->get(route('admin.mobile.form-proposals.create', ['client_id' => $meu->id]))->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.form-proposals.store'), ['client_id' => $meu->id])->assertForbidden();
        $this->actingAs($user)->patch(route('admin.mobile.form-proposals.status', $meuPedido->id), ['status' => 'arquivado'])->assertForbidden();

        $rota = fn (string $nome, ...$extra) => route("admin.mobile.form-proposals.opportunities.{$nome}", [$meuPedido->id, $opportunity->id, ...$extra]);
        $this->actingAs($user)->get($rota('show'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.form-proposals.opportunities.store', $meuPedido->id), ['brand' => 'Volvo', 'model' => 'EX30'])->assertForbidden();
        $this->actingAs($user)->patch($rota('status'), ['status' => 'rejeitado'])->assertForbidden();
        $this->actingAs($user)->patch($rota('checklist'), ['item_id' => $this->checklistItem('vin')->id, 'status' => 'confirmed'])->assertForbidden();
        $this->actingAs($user)->patch($rota('notes'), ['notes' => 'x'])->assertForbidden();
        $this->actingAs($user)->post($rota('contacts.store'), ['contacted_at' => now()->format('Y-m-d H:i'), 'method' => 'email', 'type' => 'nota'])->assertForbidden();

        $this->assertSame('novo', $meuPedido->fresh()->status);
        $this->assertSame(3, FormProposal::count());
        $this->assertSame(1, ImportOpportunity::count());
        $this->assertSame(OpportunityStatus::ToContact, $opportunity->fresh()->status);
        $this->assertNull($opportunity->fresh()->notes);
        $this->assertSame(0, $opportunity->checklistEntries()->count());
        $this->assertSame(0, $opportunity->contacts()->count());
    }

    public function test_sem_enforce_o_controller_continua_a_bloquear_quem_nao_pode(): void
    {
        // PERMISSIONS_ENFORCE desligado (omissão): o mobile verifica ele próprio.
        $user = $this->userWith(['form-proposals.view.own']);
        $meu = Client::create(['name' => 'Cliente meu', 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $user->id]);
        $pedido = $this->siteRequest(['client_id' => $meu->id]);
        $opportunity = $this->opportunity($pedido);

        $this->actingAs($user)->post(route('admin.mobile.form-proposals.store'), ['client_id' => $meu->id])->assertForbidden();
        $this->actingAs($user)->patch(route('admin.mobile.form-proposals.status', $pedido->id), ['status' => 'arquivado'])->assertForbidden();
        $this->actingAs($user)->get(route('admin.mobile.form-proposals.opportunities.show', [$pedido->id, $opportunity->id]))->assertForbidden();
        $this->actingAs($user)->patch(route('admin.mobile.form-proposals.opportunities.notes', [$pedido->id, $opportunity->id]), ['notes' => 'x'])->assertForbidden();
        $this->assertSame('novo', $pedido->fresh()->status);
    }

    public function test_ver_todos_e_editar_usa_as_oportunidades(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['form-proposals.view.all', 'form-proposals.update', 'form-proposals.create']);
        $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova']);
        $pedido = $this->siteRequest(['client_id' => $lead->id]);
        $opportunity = $this->opportunity($pedido);

        $this->actingAs($user)->get(route('admin.mobile.form-proposals.show', $pedido->id))->assertOk()->assertSee('Marcar como Arquivado')->assertSee('Nova oportunidade');
        $this->actingAs($user)->get(route('admin.mobile.form-proposals.opportunities.show', [$pedido->id, $opportunity->id]))->assertOk()->assertSee('Guardar notas');
        $this->actingAs($user)->patch(route('admin.mobile.form-proposals.status', $pedido->id), ['status' => 'arquivado'])->assertSessionHas('success');
        $this->actingAs($user)->post(route('admin.mobile.form-proposals.store'), ['client_id' => $lead->id])->assertSessionHasNoErrors();
        $this->assertSame(2, FormProposal::count());
    }
}
