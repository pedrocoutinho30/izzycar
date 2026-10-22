<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Models\StatusProposalHistory;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class AngariadorPortalTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private $angariador;

    private $outro;

    private Client $meuCliente;

    private Client $clienteDoOutro;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $this->angariador = $this->backofficeUser('angariador', ['name' => 'Rita']);
        $this->outro = $this->backofficeUser('angariador', ['name' => 'Outro']);
        $this->meuCliente = Client::create(['name' => 'Cliente Meu', 'is_lead' => false, 'lead_status' => 'nova', 'owner_id' => $this->angariador->id]);
        $this->clienteDoOutro = Client::create(['name' => 'Cliente Alheio', 'is_lead' => false, 'lead_status' => 'nova', 'owner_id' => $this->outro->id]);
    }

    private function proposal(Client $client, string $code): Proposal
    {
        return Proposal::create([
            'client_id' => $client->id, 'brand' => 'Audi', 'model' => 'A4', 'proposal_code' => $code, 'status' => 'Aprovada',
            'commission_cost' => 1000, 'transport_cost' => 700, 'ipo_cost' => 50, 'isv_cost' => 4321, 'proposed_car_value' => 20000,
        ]);
    }

    private function converted(Client $client, Proposal $proposal, string $status = 'Transporte'): ConvertedProposal
    {
        return ConvertedProposal::forceCreate([
            'client_id' => $client->id, 'proposal_id' => $proposal->id, 'brand' => 'Audi', 'modelCar' => 'A4', 'status' => $status,
            'custo_transporte' => 777, 'isv' => 4321, 'valor_carro' => 19000, 'valor_primeira_tranche' => 1234, 'observacoes' => 'nota interna secreta',
        ]);
    }

    public function test_total_cost_is_the_same_sum_the_client_sees(): void
    {
        $this->assertSame(26071.0, $this->proposal($this->meuCliente, 'AA00000001')->totalCost());
    }

    public function test_angariador_lists_only_converted_quotes_of_own_clients(): void
    {
        $this->converted($this->meuCliente, $this->proposal($this->meuCliente, 'AA00000001'));
        $this->converted($this->clienteDoOutro, $this->proposal($this->clienteDoOutro, 'BB00000001'));

        $this->actingAs($this->angariador)->get(route('admin.angariador.convertidas'))
            ->assertOk()
            ->assertSee('Cliente Meu')
            ->assertDontSee('Cliente Alheio');
    }

    public function test_detail_shows_status_and_final_value_but_not_the_cost_breakdown(): void
    {
        $converted = $this->converted($this->meuCliente, $this->proposal($this->meuCliente, 'AA00000001'));
        StatusProposalHistory::create(['converted_proposal_id' => $converted->id, 'old_status' => 'Iniciada', 'new_status' => 'Transporte']);

        $response = $this->actingAs($this->angariador)->get(route('admin.angariador.convertidas.show', $converted->id))
            ->assertOk()
            ->assertSee('Transporte')
            ->assertSee('26.071 €', false);

        foreach (['777', '4321', '4.321', '1234', '1.234', 'nota interna secreta'] as $interno) {
            $response->assertDontSee($interno);
        }
    }

    public function test_angariador_cannot_open_the_converted_quote_of_another_angariador(): void
    {
        $converted = $this->converted($this->clienteDoOutro, $this->proposal($this->clienteDoOutro, 'BB00000001'));

        $this->actingAs($this->angariador)->get(route('admin.angariador.convertidas.show', $converted->id))->assertNotFound();
    }

    public function test_quotes_page_is_scoped_and_shows_the_total(): void
    {
        $this->proposal($this->meuCliente, 'AA00000001');
        $this->proposal($this->clienteDoOutro, 'BB00000001');

        $this->actingAs($this->angariador)->get(route('admin.angariador.cotacoes'))
            ->assertOk()
            ->assertSee('Cliente Meu')
            ->assertSee('26.071 €', false)
            ->assertDontSee('Cliente Alheio');
    }

    public function test_old_urls_redirect_to_the_new_pages(): void
    {
        $this->actingAs($this->angariador)->get(route('admin.angariador.propostas'))->assertRedirect('/gestao/angariador/cotacoes');
        $this->actingAs($this->angariador)->get(route('admin.angariador.formularios'))->assertRedirect('/gestao/angariador/leads');
    }

    public function test_lead_page_shows_import_requests_quotes_and_converted_quotes_of_that_lead_only(): void
    {
        $lead = Client::create(['name' => 'Lead Dela', 'is_lead' => true, 'lead_status' => 'qualificada', 'owner_id' => $this->angariador->id]);
        FormProposal::forceCreate(['name' => 'Lead Dela', 'phone' => '912345678', 'email' => 'lead@example.com', 'client_id' => $lead->id, 'title' => 'Pedido do BMW', 'status' => 'novo']);
        FormProposal::forceCreate(['name' => 'Cliente Alheio', 'phone' => '913333333', 'email' => 'alheio@example.com', 'client_id' => $this->clienteDoOutro->id, 'title' => 'Pedido alheio', 'status' => 'novo']);
        $proposal = $this->proposal($lead, 'AA00000001');
        $this->converted($lead, $proposal);

        $this->actingAs($this->angariador)->get(route('admin.angariador.leads.show', $lead->id))
            ->assertOk()
            ->assertSee('Pedidos de importação')
            ->assertSee('Pedido do BMW')
            ->assertDontSee('Pedido alheio')
            ->assertSee('Cotações convertidas')
            ->assertSee('26.071 €', false);
    }

    public function test_menu_uses_the_new_names(): void
    {
        $this->actingAs($this->angariador)->get(route('admin.angariador.dashboard'))
            ->assertOk()
            ->assertSee('Cotações convertidas')
            ->assertSee('/gestao/angariador/cotacoes', false)
            ->assertDontSee('/gestao/angariador/formularios', false);
    }

    public function test_timeline_shows_only_the_angariadors_own_entries(): void
    {
        $lead = Client::create(['name' => 'Lead Dela', 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $this->angariador->id]);

        // Eventos que o angariador não deve ver: sistema e equipa.
        LeadActivity::log($lead->id, 'Proprietário da lead alterado para: Josué');
        LeadActivity::create(['client_id' => $lead->id, 'user_id' => $this->outro->id, 'type' => 'note', 'title' => 'Nota da equipa', 'icon' => 'bi-sticky-fill', 'color' => 'warning']);

        // O que ele próprio faz.
        $this->actingAs($this->angariador)->post(route('admin.angariador.leads.activity', $lead->id), ['type' => 'call', 'title' => 'Liguei ao cliente'])->assertRedirect();
        $this->actingAs($this->angariador)->postJson(route('admin.angariador.leads.status', $lead->id), ['status' => 'em_contacto'])->assertOk();
        $this->actingAs($this->angariador)->post(route('admin.angariador.leads.followup', $lead->id), ['next_followup_at' => now()->addDay()->format('Y-m-d H:i')])->assertRedirect();

        $this->actingAs($this->angariador)->get(route('admin.angariador.leads.show', $lead->id))
            ->assertOk()
            ->assertSee('Liguei ao cliente')
            ->assertSee('Estado alterado: Nova → Em Contacto')
            ->assertSee('Follow-up agendado para')
            ->assertDontSee('Proprietário da lead alterado')
            ->assertDontSee('Nota da equipa');
    }

    public function test_kanban_card_shows_only_the_angariadors_last_activity(): void
    {
        $lead = Client::create(['name' => 'Lead Dela', 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $this->angariador->id]);
        LeadActivity::create(['client_id' => $lead->id, 'user_id' => $this->angariador->id, 'type' => 'note', 'title' => 'Minha nota', 'icon' => 'bi-sticky-fill', 'color' => 'warning']);
        LeadActivity::log($lead->id, 'Evento interno da equipa');

        $this->actingAs($this->angariador)->get(route('admin.angariador.leads.kanban'))
            ->assertOk()
            ->assertSee('Minha nota')
            ->assertDontSee('Evento interno da equipa');
    }
}
