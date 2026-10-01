<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Models\User;
use App\Mail\AlternativeQuoteRequestedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ClientImportRequestsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private User $user;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        config(['permissions.enforce' => true]);

        $this->user = $this->backofficeUser('Importador');
        $this->vehicleCatalog(['Volvo' => ['XC40', 'EX30'], 'Fiat' => ['500e'], 'Kia' => ['EV3'], 'Skoda' => ['Elroq']]);
        $this->client = Client::create([
            'name' => 'Maria Antiga', 'email' => 'maria@example.com', 'phone' => '912345678',
            'is_lead' => false, 'lead_status' => 'nova',
        ]);
    }

    private function createRequest(array $data = [])
    {
        return $this->actingAs($this->user)->post(route('admin.v2.form-proposals.store'), $data + ['client_id' => $this->client->id]);
    }

    public function test_manual_request_is_created_inside_the_client(): void
    {
        $response = $this->createRequest(['title' => 'Segundo carro', 'brand' => 'Volvo', 'model' => 'XC40', 'budget' => 35000]);

        $request = FormProposal::sole();
        $response->assertRedirect(route('admin.v2.form-proposals.show', $request->id) . '#oportunidades');

        $this->assertSame(FormProposal::ORIGIN_MANUAL, $request->origin);
        $this->assertSame('Segundo carro', $request->label);
        $this->assertSame('Maria Antiga', $request->name);
        $this->assertSame('maria@example.com', $request->email);
        $this->assertSame('em_analise', $request->status);
        $this->assertSame($this->user->id, $request->created_by);
        $this->assertSame(1, LeadActivity::where('client_id', $this->client->id)->where('title', 'Pedido de importação criado')->count());
    }

    public function test_request_without_any_vehicle_data_and_client_without_contacts(): void
    {
        $client = Client::create(['name' => 'Sem Contactos', 'is_lead' => true, 'lead_status' => 'nova']);

        $this->actingAs($this->user)->post(route('admin.v2.form-proposals.store'), ['client_id' => $client->id])->assertSessionHasNoErrors();

        $request = FormProposal::sole();
        $this->assertSame('Pedido de importação', $request->label);
        $this->assertSame('', $request->email);
    }

    public function test_validation(): void
    {
        $this->actingAs($this->user)->post(route('admin.v2.form-proposals.store'), ['client_id' => 999999])->assertSessionHasErrors('client_id');
        $this->createRequest(['year_min' => 'abc', 'budget' => 'muito'])->assertSessionHasErrors(['year_min', 'budget']);
        $this->assertSame(0, FormProposal::count());
    }

    public function test_request_brand_and_model_come_from_the_catalog(): void
    {
        $this->createRequest(['brand' => 'Volvo', 'model' => 'i4'])->assertSessionHasErrors('model');
        $this->createRequest(['brand' => 'Inventada'])->assertSessionHasErrors('brand');
        $this->assertSame(0, FormProposal::count());

        $this->createRequest(['brand' => 'Volvo', 'model' => 'EX30'])->assertSessionHasNoErrors();
    }

    public function test_cms_cannot_create_requests(): void
    {
        $this->actingAs($this->backofficeUser('cms'))
            ->post(route('admin.v2.form-proposals.store'), ['client_id' => $this->client->id])
            ->assertForbidden();
    }

    public function test_lead_and_client_pages_list_requests_and_offer_new_request(): void
    {
        $this->createRequest(['title' => 'Segundo carro']);
        FormProposal::create(['name' => 'Maria Antiga', 'email' => 'maria@example.com', 'phone' => '912345678', 'client_id' => $this->client->id, 'brand' => 'BMW', 'model' => 'i4']);

        $this->actingAs($this->user)->get(route('admin.v2.clients.show', $this->client->id))
            ->assertOk()
            ->assertSee('Pedidos de importação')
            ->assertSee('Segundo carro')
            ->assertSee('BMW i4')
            ->assertSee('Manual')
            ->assertSee('id="clientRequestModal"', false);

        $lead = Client::create(['name' => 'Lead Nova', 'is_lead' => true, 'lead_status' => 'nova']);
        $this->actingAs($this->user)->get(route('admin.v2.leads.show', $lead->id))
            ->assertOk()
            ->assertSee('Sem pedidos de importação')
            ->assertSee('Novo pedido');
    }

    public function test_request_page_sits_under_the_client_and_can_be_edited(): void
    {
        $this->createRequest(['title' => 'Segundo carro']);
        $request = FormProposal::sole();

        $this->actingAs($this->user)->get(route('admin.v2.form-proposals.show', $request->id))
            ->assertOk()
            ->assertSee('Cliente: Maria Antiga')
            ->assertSee(route('admin.v2.clients.show', $this->client->id), false)
            ->assertSee('Pedido criado no backoffice')
            ->assertSee('id="editRequestModal"', false);

        $this->actingAs($this->user)
            ->put(route('admin.v2.form-proposals.update', $request->id), ['title' => 'Carro da filha', 'brand' => 'Fiat', 'model' => '500e'])
            ->assertRedirect(route('admin.v2.form-proposals.show', $request->id));
        $this->assertSame('Carro da filha', $request->fresh()->label);

        $this->actingAs($this->user)
            ->delete(route('admin.v2.form-proposals.destroy', $request->id))
            ->assertRedirect(route('admin.v2.clients.show', $this->client->id));
    }

    public function test_formularios_left_the_menu(): void
    {
        $this->actingAs($this->user)->get(route('admin.v2.leads.index'))
            ->assertOk()
            ->assertDontSee('<span>Formulários</span>', false)
            ->assertSee('Pedidos do site');
    }

    public function test_client_requests_a_quote_for_another_option_with_one_click(): void
    {
        Mail::fake();
        $this->createRequest(['title' => 'Segundo carro']);
        $request = FormProposal::sole();
        $source = $request->opportunities()->create(['brand' => 'Volvo', 'model' => 'XC40', 'price' => 32000]);
        $kia = $request->opportunities()->create(['brand' => 'Kia', 'model' => 'EV3', 'year' => 2025, 'price' => 34500, 'listing_url' => 'https://suchen.mobile.de/x/1']);
        $rejected = $request->opportunities()->create(['brand' => 'Skoda', 'model' => 'Elroq', 'status' => 'rejeitado']);
        $proposal = Proposal::create(['client_id' => $this->client->id, 'brand' => 'Volvo', 'model' => 'XC40', 'transport_cost' => 0, 'proposal_code' => 'VXTEST123', 'status' => 'Pendente']);
        $source->update(['proposal_id' => $proposal->id]);

        $url = fn ($opportunity) => route('proposals.request-alternative', ['VXTEST123', $opportunity->id]);

        $this->postJson($url($kia))->assertOk()->assertJsonPath('success', true);

        Mail::assertSent(AlternativeQuoteRequestedMail::class, function (AlternativeQuoteRequestedMail $mail) use ($kia) {
            $text = $mail->render();

            return $mail->hasTo('geral@izzycar.pt')
                && $mail->alternative->is($kia)
                && str_contains($text, 'VXTEST123')
                && str_contains($text, 'Kia EV3')
                && str_contains($text, 'https://suchen.mobile.de/x/1')
                && str_contains($text, 'maria@example.com');
        });
        $this->assertNotNull($kia->fresh()->client_quote_requested_at);
        $this->assertSame(1, LeadActivity::where('client_id', $this->client->id)->where('title', 'Pediu cotação de outra opção')->count());

        // Segundo clique: não envia de novo; a página mostra "Cotação pedida".
        $this->postJson($url($kia))->assertOk()->assertJsonPath('already', true);
        Mail::assertSentCount(1);
        $this->get(route('proposals.detail', 'VXTEST123'))->assertSee('✓ Cotação pedida');

        // Só as outras opções desta cotação.
        $this->postJson($url($rejected))->assertNotFound();
        $this->postJson($url($source))->assertNotFound();
        $this->postJson(route('proposals.request-alternative', ['NAOEXISTE', $kia->id]))->assertNotFound();

        // A equipa vê o pedido na oportunidade.
        $this->actingAs($this->user)
            ->get(route('admin.v2.form-proposals.opportunities.show', [$request->id, $kia->id]))
            ->assertOk()
            ->assertSee('O cliente pediu cotação para esta viatura');
    }

    public function test_public_proposal_shows_the_other_opportunities_of_the_request(): void
    {
        $this->createRequest(['title' => 'Segundo carro']);
        $request = FormProposal::sole();

        $source = $request->opportunities()->create(['brand' => 'Volvo', 'model' => 'XC40', 'price' => 32000]);
        $request->opportunities()->create([
            'brand' => 'Kia', 'model' => 'EV3', 'version' => 'GT-Line 81 kWh', 'year' => 2025, 'mileage' => 12000,
            'price' => 34500, 'listing_url' => 'https://suchen.mobile.de/x/1',
        ]);
        $request->opportunities()->create(['brand' => 'Skoda', 'model' => 'Elroq', 'status' => 'rejeitado', 'price' => 30000]);

        $proposal = Proposal::create([
            'client_id' => $this->client->id, 'brand' => 'Volvo', 'model' => 'XC40',
            'transport_cost' => 0, 'proposal_code' => 'VXTEST123', 'status' => 'Pendente',
        ]);
        $source->update(['proposal_id' => $proposal->id]);

        $this->get(route('proposals.detail', 'VXTEST123'))
            ->assertOk()
            ->assertSee('Outras opções encontradas para si')
            ->assertSee('Kia EV3')
            ->assertSee('GT-Line 81 kWh')
            ->assertSee('12.000 km')
            ->assertSee('34.500 €')
            ->assertSee('https://suchen.mobile.de/x/1', false)
            ->assertSee('enviamos-lhe a cotação dessa viatura')
            ->assertSee('data-alt-quote', false)
            ->assertDontSee('Skoda Elroq');

        // Cotação que não nasceu de uma oportunidade: sem a secção.
        Proposal::create(['client_id' => $this->client->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0, 'proposal_code' => 'BITEST999', 'status' => 'Pendente']);
        $this->get(route('proposals.detail', 'BITEST999'))->assertOk()->assertDontSee('Outras opções encontradas para si');
    }
}
