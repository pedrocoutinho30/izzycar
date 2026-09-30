<?php

namespace Tests\Feature;

use App\Enums\ContactType;
use App\Enums\OpportunityStatus;
use App\Models\Brand;
use App\Models\Client;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ProposalFromOpportunityTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;


    private User $user;

    private FormProposal $formProposal;

    private ImportOpportunity $opportunity;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $this->user = $this->backofficeUser();
        $client = Client::create(['name' => 'João Silva', 'email' => 'joao@example.com', 'phone' => '912345678']);

        $this->formProposal = FormProposal::create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'phone' => '912345678',
            'client_id' => $client->id,
            'brand' => 'Tesla',
            'model' => 'Model 3',
            'message' => 'Procuro um Model 3 até 25 mil.',
        ]);

        $this->opportunity = $this->formProposal->opportunities()->create([
            'brand' => 'BMW',
            'model' => 'i4',
            'version' => 'eDrive40',
            'year' => 2022,
            'mileage' => 68400,
            'price' => 29500,
            'fuel' => 'eletrico',
            'listing_url' => 'https://suchen.mobile.de/fahrzeuge/details.html?id=1',
            'vehicle_notes' => 'Bateria com SOH 94%.',
        ]);
    }

    private function storePayload(array $overrides = []): array
    {
        return $overrides + [
            'form_proposal_id' => $this->formProposal->id,
            'import_opportunity_id' => $this->opportunity->id,
            'client_id' => $this->formProposal->client_id,
            'brand' => 'BMW',
            'model' => 'i4',
            'fuel' => 'Elétrico',
            'transport_cost' => 1350,
            'proposed_car_value' => 29500,
        ];
    }

    public function test_create_form_is_prefilled_from_the_opportunity(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.v2.proposals.createFromOpportunity', $this->opportunity->id))
            ->assertOk()
            ->assertSee('Cotação a partir da oportunidade BMW i4 2022')
            ->assertSee('name="import_opportunity_id" value="' . $this->opportunity->id . '"', false)
            ->assertSee('value="eDrive40"', false)
            ->assertSee('value="68400"', false)
            ->assertSee('https://suchen.mobile.de/fahrzeuge/details.html?id=1')
            ->assertSee('Bateria com SOH 94%.')
            ->assertSee('<option value="Elétrico" selected', false);
    }

    public function test_brand_and_model_are_matched_to_the_catalog(): void
    {
        $brand = Brand::create(['name' => 'BMW']);
        $brand->models()->create(['name' => 'i4']);
        $this->opportunity->update(['brand' => 'bmw', 'model' => 'I4']);

        $this->actingAs($this->user)
            ->get(route('admin.v2.proposals.createFromOpportunity', $this->opportunity->id))
            ->assertOk()
            ->assertSee('value="BMW"' . "\n" . '                                selected', false)
            ->assertDontSee('não corresponde a uma marca/modelo do catálogo');

        $this->opportunity->update(['brand' => 'Polestar', 'model' => '2']);
        $this->actingAs($this->user)
            ->get(route('admin.v2.proposals.createFromOpportunity', $this->opportunity->id))
            ->assertSee('não corresponde a uma marca/modelo do catálogo');
    }

    public function test_storing_links_proposal_to_opportunity_and_request(): void
    {
        $this->actingAs($this->user)
            ->post(route('admin.v2.proposals.store'), $this->storePayload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.v2.proposals.index'));

        $proposal = Proposal::sole();
        $this->opportunity->refresh();

        $this->assertSame($proposal->id, $this->opportunity->proposal_id);
        $this->assertSame(OpportunityStatus::Selected, $this->opportunity->status);
        $this->assertSame($proposal->id, $this->formProposal->fresh()->proposal_id);
        $this->assertSame('convertido', $this->formProposal->fresh()->status);

        $note = $this->opportunity->contacts()->sole();
        $this->assertSame(ContactType::Note, $note->type);
        $this->assertStringContainsString("Cotação #{$proposal->id}", $note->message);
    }

    public function test_request_keeps_first_proposal_when_several_opportunities_are_quoted(): void
    {
        $second = $this->formProposal->opportunities()->create(['brand' => 'Tesla', 'model' => 'Model 3']);

        $this->actingAs($this->user)->post(route('admin.v2.proposals.store'), $this->storePayload());
        $this->actingAs($this->user)->post(route('admin.v2.proposals.store'), $this->storePayload([
            'import_opportunity_id' => $second->id, 'brand' => 'Tesla', 'model' => 'Model 3',
        ]));

        [$first, $other] = Proposal::orderBy('id')->get();
        $this->assertSame($first->id, $this->formProposal->fresh()->proposal_id);
        $this->assertSame($other->id, $second->fresh()->proposal_id);
    }

    public function test_only_one_proposal_per_opportunity(): void
    {
        $this->actingAs($this->user)->post(route('admin.v2.proposals.store'), $this->storePayload());
        $proposalId = $this->opportunity->fresh()->proposal_id;

        $this->actingAs($this->user)
            ->get(route('admin.v2.proposals.createFromOpportunity', $this->opportunity->id))
            ->assertRedirect(route('admin.v2.proposals.edit', $proposalId));

        $this->actingAs($this->user)
            ->post(route('admin.v2.proposals.store'), $this->storePayload())
            ->assertRedirect(route('admin.v2.proposals.edit', $proposalId));

        $this->assertSame(1, Proposal::count());
    }

    public function test_opportunity_photo_is_used_when_no_image_is_uploaded(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('car.jpg', 800, 600)->store('import-opportunities/' . $this->formProposal->id, 'public');
        $this->opportunity->update(['photo_path' => $path]);

        $this->actingAs($this->user)
            ->post(route('admin.v2.proposals.store'), $this->storePayload())
            ->assertSessionHasNoErrors();

        $images = Proposal::sole()->images;
        $this->assertNotEmpty($images);
        Storage::disk('public')->assertExists(is_array($images) ? $images[0] : $images);
        // A foto original da oportunidade mantém-se.
        Storage::disk('public')->assertExists($path);
    }

    public function test_deleting_the_proposal_unlinks_the_opportunity(): void
    {
        $this->actingAs($this->user)->post(route('admin.v2.proposals.store'), $this->storePayload());

        Proposal::sole()->delete();

        $this->assertNull($this->opportunity->fresh()->proposal_id);
    }

    public function test_request_page_shows_proposal_buttons(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.v2.form-proposals.show', $this->formProposal->id))
            ->assertOk()
            ->assertSee(route('admin.v2.proposals.createFromOpportunity', $this->opportunity->id));

        $this->actingAs($this->user)->post(route('admin.v2.proposals.store'), $this->storePayload());
        $proposalId = $this->opportunity->fresh()->proposal_id;

        $this->actingAs($this->user)
            ->get(route('admin.v2.form-proposals.opportunities.show', [$this->formProposal->id, $this->opportunity->id]))
            ->assertOk()
            ->assertSee('Ver cotação #' . $proposalId);
    }

    public function test_existing_create_from_form_flow_still_works(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.v2.proposals.createFromForm', $this->formProposal->id))
            ->assertOk()
            ->assertSee('Cotação a partir do formulário de João Silva')
            ->assertDontSee('name="import_opportunity_id"', false);

        // Sem oportunidade, o pedido passa a apontar para a cotação mais recente (comportamento atual).
        $this->formProposal->update(['proposal_id' => null]);
        $payload = collect($this->storePayload())->except('import_opportunity_id')->all();
        $this->actingAs($this->user)->post(route('admin.v2.proposals.store'), $payload);
        $this->actingAs($this->user)->post(route('admin.v2.proposals.store'), $payload);

        $this->assertSame(Proposal::max('id'), $this->formProposal->fresh()->proposal_id);
        $this->assertNull($this->opportunity->fresh()->proposal_id);
    }
}
