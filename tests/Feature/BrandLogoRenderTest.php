<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\Proposal;
use App\Models\Seller;
use App\Models\User;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Render das páginas do backoffice que mostram o logo da marca: com logo
 * aparece a <img> (alt = marca); sem logo não aparece <img> nem erro.
 */
class BrandLogoRenderTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private User $admin;
    private Client $client;
    private Client $lead;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        Brand::create(['name' => 'Audi', 'logo_path' => 'brands/audi.png']);
        Brand::create(['name' => 'Matra']);

        $this->admin = $this->backofficeUser('admin');
        $this->client = Client::create(['name' => 'Maria', 'is_lead' => false, 'lead_status' => 'nova']);
        $this->lead = Client::create(['name' => 'Rui Lead', 'is_lead' => true, 'lead_status' => 'nova']);
    }

    private function proposal(Client $client, string $brand, string $model): Proposal
    {
        return Proposal::create(['client_id' => $client->id, 'brand' => $brand, 'model' => $model, 'transport_cost' => 0, 'proposal_code' => 'T' . $client->id . $model, 'status' => 'Pendente']);
    }

    private function assertLogoRules($response): void
    {
        $response->assertOk()
            ->assertSee('alt="Audi"', false)
            ->assertSee('brands/audi.png', false)
            ->assertDontSee('alt="Matra"', false)
            ->assertDontSee('brands/matra', false);
    }

    public function test_proposals_index_shows_logo_only_for_brands_that_have_one(): void
    {
        $this->proposal($this->client, 'Audi', 'A4X');
        $this->proposal($this->client, 'Matra', 'Murena');

        $response = $this->actingAs($this->admin)->get(route('admin.v2.proposals.index'));

        $this->assertLogoRules($response);
        $response->assertSee('Murena')->assertSee('A4X');
    }

    public function test_proposal_with_unknown_or_empty_brand_does_not_break_the_list(): void
    {
        $this->proposal($this->client, 'Marca Fantasma', 'X1');
        $this->proposal($this->client, '', 'Sem Marca');

        $this->actingAs($this->admin)->get(route('admin.v2.proposals.index'))
            ->assertOk()->assertSee('X1')->assertDontSee('<img src="" alt', false);
    }

    public function test_converted_proposals_index_shows_logo_only_for_brands_that_have_one(): void
    {
        ConvertedProposal::forceCreate(['client_id' => $this->client->id, 'brand' => 'Audi', 'modelCar' => 'A4Z', 'status' => 'Iniciada']);
        ConvertedProposal::forceCreate(['client_id' => $this->client->id, 'brand' => 'Matra', 'modelCar' => 'Bagheera', 'status' => 'Iniciada']);

        $response = $this->actingAs($this->admin)->get(route('admin.v2.converted-proposals.index'));

        $this->assertLogoRules($response);
        $response->assertSee('Bagheera');
    }

    public function test_dashboard_recent_proposals_show_logo_only_for_brands_that_have_one(): void
    {
        $this->proposal($this->client, 'Audi', 'A4D');
        $this->proposal($this->client, 'Matra', 'Djet');

        $response = $this->actingAs($this->admin)->get(route('admin.v2.dashboard'));

        $this->assertLogoRules($response);
        $response->assertSee('Djet');
    }

    public function test_client_show_shows_logo_in_proposals_and_timeline(): void
    {
        $this->proposal($this->client, 'Audi', 'A4C');
        $this->proposal($this->client, 'Matra', 'Rancho');

        $response = $this->actingAs($this->admin)->get(route('admin.v2.clients.show', $this->client->id));

        $this->assertLogoRules($response);
        $response->assertSee('Rancho');
    }

    public function test_lead_show_shows_logo_only_for_brands_that_have_one(): void
    {
        $this->proposal($this->lead, 'Audi', 'A4L');
        $this->proposal($this->lead, 'Matra', '530');

        $response = $this->actingAs($this->admin)->get(route('admin.v2.leads.show', $this->lead->id));

        $this->assertLogoRules($response);
        $response->assertSee('Rui Lead');
    }

    public function test_sellers_index_and_show_render_brand_logos_and_name_badge_fallback(): void
    {
        $seller = Seller::create(['name' => 'Stand Misto']);
        $seller->brands()->attach(Brand::whereIn('name', ['Audi', 'Matra'])->pluck('id'));
        V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Audi', 'model' => 'A4S', 'seller_id' => $seller->id]);
        V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Matra', 'model' => 'Simca', 'seller_id' => $seller->id]);

        foreach ([route('admin.v2.sellers.index'), route('admin.v2.sellers.show', $seller->id)] as $url) {
            $response = $this->actingAs($this->admin)->get($url);

            // Audi: logo; Matra (sem logo): só o nome num badge, sem <img>.
            $response->assertOk()
                ->assertSee('alt="Audi"', false)
                ->assertDontSee('alt="Matra"', false)
                ->assertSee('Matra')
                ->assertSee('Stand Misto');
        }

        $this->actingAs($this->admin)->get(route('admin.v2.sellers.show', $seller->id))
            ->assertSee('Simca')->assertSee('A4S');
    }

    public function test_sellers_without_brands_render_without_errors(): void
    {
        $seller = Seller::create(['name' => 'Stand Vazio']);

        $this->actingAs($this->admin)->get(route('admin.v2.sellers.index'))->assertOk()->assertSee('Stand Vazio');
        $this->actingAs($this->admin)->get(route('admin.v2.sellers.show', $seller->id))->assertOk()->assertSee('Marcas');
    }
}
