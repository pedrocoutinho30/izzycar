<?php

namespace Tests\Feature;

use App\Enums\OpportunityStatus;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\Legalization;
use App\Models\Proposal;
use App\Models\Seller;
use App\Models\User;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class PhaseTwoFunnelTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private User $admin;

    private Client $client;

    private FormProposal $request;

    private ImportOpportunity $opportunity;

    private Proposal $proposal;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();

        $this->admin = $this->backofficeUser('admin');
        $this->client = Client::create(['name' => 'Maria Silva', 'email' => 'maria@example.com', 'is_lead' => true, 'lead_status' => 'nova']);
        $this->request = FormProposal::create(['name' => 'Maria Silva', 'email' => 'maria@example.com', 'phone' => '912345678', 'client_id' => $this->client->id, 'status' => 'em_analise']);
        $seller = Seller::create(['name' => 'Autohaus Müller', 'country' => 'DE']);
        $this->opportunity = $this->request->opportunities()->create(['brand' => 'BMW', 'model' => 'i4', 'status' => 'selecionado', 'seller_id' => $seller->id]);
        $this->proposal = $this->makeProposal();
        $this->opportunity->update(['proposal_id' => $this->proposal->id]);
    }

    private function makeProposal(array $attributes = []): Proposal
    {
        return Proposal::create($attributes + [
            'client_id' => $this->client->id, 'brand' => 'BMW', 'model' => 'i4', 'version' => 'eDrive40',
            'proposed_car_year_month' => '2023-05', 'proposed_car_mileage' => 24000, 'fuel' => 'Elétrico',
            'transport_cost' => 1350, 'commission_cost' => 615, 'proposed_car_value' => 30000,
            'proposal_code' => 'BI' . random_int(100000, 999999), 'status' => 'Pendente',
        ]);
    }

    private function setStatus(Proposal $proposal, string $status)
    {
        return $this->actingAs($this->admin)->patchJson(route('admin.v2.proposals.updateStatus', $proposal->id), ['status' => $status]);
    }

    // ── Estados com um só significado ────────────────────────────────

    public function test_setting_approved_by_hand_goes_through_acceptance(): void
    {
        $this->setStatus($this->proposal, 'Aprovada')->assertOk()->assertJsonPath('status', 'Aprovada');

        $this->assertSame(1, ConvertedProposal::where('proposal_id', $this->proposal->id)->count());
        $this->assertFalse($this->client->fresh()->is_lead);
        $this->assertSame('convertido', $this->request->fresh()->status);
        $this->assertSame(OpportunityStatus::Selected, $this->opportunity->fresh()->status);
    }

    public function test_accepted_proposal_cannot_change_status(): void
    {
        $this->setStatus($this->proposal, 'Aprovada');

        $this->setStatus($this->proposal, 'Reprovada')->assertUnprocessable();
        $this->assertSame('Aprovada', $this->proposal->fresh()->status);

        $this->actingAs($this->admin)
            ->postJson(route('admin.v2.proposals.bulkReject'), ['ids' => [$this->proposal->id]])
            ->assertOk()
            ->assertJsonPath('count', 0);
        $this->assertSame('Aprovada', $this->proposal->fresh()->status);
    }

    public function test_rejecting_marks_the_opportunity_rejected(): void
    {
        $this->setStatus($this->proposal, 'Reprovada')->assertOk();

        $this->assertSame(OpportunityStatus::Rejected, $this->opportunity->fresh()->status);
        $this->assertSame('em_analise', $this->request->fresh()->status);
    }

    public function test_bulk_approval_creates_converted_proposals_one_by_one(): void
    {
        $other = $this->makeProposal();

        $this->actingAs($this->admin)
            ->postJson(route('admin.v2.proposals.bulkUpdateStatus'), ['ids' => [$this->proposal->id, $other->id], 'status' => 'Aprovada'])
            ->assertOk()
            ->assertJsonPath('count', 2);

        $this->assertSame(2, ConvertedProposal::count());
        $this->assertFalse($this->client->fresh()->is_lead);
    }

    public function test_deleting_the_proposal_puts_the_opportunity_back_in_analysis(): void
    {
        $this->actingAs($this->admin)->delete(route('admin.v2.proposals.destroy', $this->proposal->id));

        $opportunity = $this->opportunity->fresh();
        $this->assertNull($opportunity->proposal_id);
        $this->assertSame(OpportunityStatus::InAnalysis, $opportunity->status);
    }

    public function test_request_cannot_be_set_to_converted_by_hand(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.v2.form-proposals.update-status', $this->request->id), ['status' => 'convertido'])
            ->assertSessionHasErrors('status');

        $this->assertSame('em_analise', $this->request->fresh()->status);
    }

    // ── Comissões ────────────────────────────────────────────────────

    public function test_commission_is_fixed_at_conversion_and_cancelled_ones_do_not_count(): void
    {
        $angariador = $this->backofficeUser('angariador', ['commission_fixed_value' => 100]);
        $this->client->update(['owner_id' => $angariador->id]);

        $this->setStatus($this->proposal, 'Aprovada');
        $converted = ConvertedProposal::sole();
        $this->assertSame(100.0, $converted->angariadorCommissionAmount());

        $angariador->update(['commission_fixed_value' => 250]);
        $this->assertSame(100.0, $converted->fresh()->angariadorCommissionAmount());

        $this->assertSame([0.0, 100.0], ConvertedProposal::commissionTotals(ConvertedProposal::all()));

        $converted->update(['status' => 'Cancelado']);
        $this->assertSame([0.0, 0.0], ConvertedProposal::commissionTotals(ConvertedProposal::all()));
        $this->assertFalse($converted->fresh()->isCommissionOverdue());
    }

    // ── Viatura e legalização a partir da cotação convertida ─────────

    public function test_converted_proposal_creates_vehicle_and_legalization_with_its_data(): void
    {
        $this->setStatus($this->proposal, 'Aprovada');
        $converted = ConvertedProposal::sole();
        $converted->update(['matricula_destino' => 'AA-12-BB', 'matricula_origem' => 'M-AB 1234']);

        $this->actingAs($this->admin)
            ->post(route('admin.v2.converted-proposals.create-vehicle', $converted->id))
            ->assertRedirect();

        $vehicle = V3Vehicle::sole();
        $this->assertSame('BMW', $vehicle->brand);
        $this->assertSame('i4', $vehicle->model);
        $this->assertSame('eDrive40', $vehicle->version);
        $this->assertSame(2023, $vehicle->year);
        $this->assertSame(24000, $vehicle->kilometers);
        $this->assertSame('Elétrico', $vehicle->fuel);
        $this->assertSame('AA-12-BB', $vehicle->registration);
        $this->assertSame('reservado', $vehicle->status);
        $this->assertTrue((bool) $vehicle->is_imported);
        $this->assertSame($this->client->id, $vehicle->client_id);
        $this->assertSame($this->opportunity->seller_id, $vehicle->seller_id);
        $this->assertStringContainsString('M-AB 1234', $vehicle->notes);
        $this->assertSame($vehicle->id, $converted->fresh()->v3_vehicle_id);

        $legalization = Legalization::sole();
        $this->assertSame($vehicle->id, $legalization->v3_vehicle_id);
        $this->assertSame($this->client->id, $legalization->client_id);
        $this->assertSame('AA-12-BB', $legalization->matricula);

        // Segunda vez: não duplica.
        $this->actingAs($this->admin)->post(route('admin.v2.converted-proposals.create-vehicle', $converted->id));
        $this->assertSame(1, V3Vehicle::count());
        $this->assertSame(1, Legalization::count());

        $this->actingAs($this->admin)->get(route('admin.v2.converted-proposals.edit', $converted->id))
            ->assertOk()
            ->assertSee($vehicle->reference);
    }

    public function test_creating_the_vehicle_requires_vehicle_permission(): void
    {
        config(['permissions.enforce' => true]);
        $this->setStatus($this->proposal, 'Aprovada');
        $converted = ConvertedProposal::sole();

        $this->actingAs($this->backofficeUser('cms'))
            ->post(route('admin.v2.converted-proposals.create-vehicle', $converted->id))
            ->assertForbidden();
        $this->assertSame(0, V3Vehicle::count());

        $this->actingAs($this->admin)->get(route('admin.v2.converted-proposals.edit', $converted->id))
            ->assertSee('Criar viatura e legalização');
    }
}
