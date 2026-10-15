<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Seller;
use App\Models\User;
use App\Models\V3Vehicle;
use App\Support\BrandLogos;
use Illuminate\Support\Facades\Cache;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class SellerBrandsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    public function test_vehicle_with_seller_adds_its_brand_to_the_seller_without_removing_others(): void
    {
        $bmw = Brand::create(['name' => 'BMW']);
        $audi = Brand::create(['name' => 'Audi']);
        $seller = Seller::create(['name' => 'Stand']);
        $seller->brands()->attach($audi->id);

        V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'bmw', 'model' => 'i4', 'seller_id' => $seller->id]);

        $this->assertEqualsCanonicalizing([$bmw->id, $audi->id], $seller->brands()->pluck('brands.id')->all());
    }

    public function test_unknown_brand_is_ignored_and_does_not_break_saving(): void
    {
        $seller = Seller::create(['name' => 'Stand']);

        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Marca Fantasma', 'seller_id' => $seller->id]);

        $this->assertNotNull($vehicle->id);
        $this->assertSame(0, $seller->brands()->count());
    }

    public function test_form_syncs_brands_and_filter_lists_only_matching_sellers(): void
    {
        $bmw = Brand::create(['name' => 'BMW']);
        $audi = Brand::create(['name' => 'Audi']);
        $user = $this->backofficeUser();

        $this->actingAs($user)->post(route('admin.v2.sellers.store'), [
            'name' => 'Stand BMW',
            'brands_submitted' => 1,
            'brand_ids' => [$bmw->id],
        ])->assertRedirect();
        Seller::create(['name' => 'Stand Audi'])->brands()->attach($audi->id);

        $this->assertSame([$bmw->id], Seller::where('name', 'Stand BMW')->first()->brands()->pluck('brands.id')->all());

        $this->actingAs($user)->get(route('admin.v2.sellers.index', ['brand' => $bmw->id]))
            ->assertOk()->assertSee('Stand BMW')->assertDontSee('Stand Audi');

        $this->actingAs($user)->post(route('admin.v2.sellers.store'), [
            'name' => 'Stand X', 'brands_submitted' => 1, 'brand_ids' => [9999],
        ])->assertSessionHasErrors('brand_ids.0');
    }

    public function test_brand_logo_resolver_is_case_insensitive_and_uses_one_query(): void
    {
        Brand::create(['name' => 'Mercedes-Benz', 'logo_path' => 'brands/mb.png']);
        Brand::create(['name' => 'Sem Logo']);

        $logos = app(BrandLogos::class);
        $queries = 0;
        \DB::listen(function () use (&$queries) { $queries++; });

        $this->assertStringContainsString('brands/mb.png', $logos->urlFor(' mercedes-benz '));
        $this->assertNull($logos->urlFor('Sem Logo'));
        $this->assertNull($logos->urlFor(null));
        $this->assertSame(1, $queries);
    }

    public function test_brand_names_match_ignoring_accents_and_punctuation(): void
    {
        Brand::create(['name' => 'Citroën', 'logo_path' => 'brands/citroen.png']);
        Brand::create(['name' => 'Mercedes-Benz', 'logo_path' => 'brands/mb.png']);

        $logos = app(BrandLogos::class);
        $this->assertStringContainsString('brands/citroen.png', $logos->urlFor('Citroen'));
        $this->assertStringContainsString('brands/mb.png', $logos->urlFor('Mercedes Benz'));
    }

    public function test_auto_association_matches_accents_and_punctuation(): void
    {
        $citroen = Brand::create(['name' => 'Citroën']);
        $mb = Brand::create(['name' => 'Mercedes-Benz']);
        $seller = Seller::create(['name' => 'Stand']);

        V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Citroen', 'seller_id' => $seller->id]);
        V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'mercedes benz', 'seller_id' => $seller->id]);

        $this->assertEqualsCanonicalizing([$citroen->id, $mb->id], $seller->brands()->pluck('brands.id')->all());
    }

    public function test_import_opportunity_associates_brand_on_create_and_when_seller_or_brand_changes(): void
    {
        $bmw = Brand::create(['name' => 'BMW']);
        $audi = Brand::create(['name' => 'Audi']);
        $first = Seller::create(['name' => 'Stand 1']);
        $second = Seller::create(['name' => 'Stand 2']);
        $request = FormProposal::create(['name' => 'Maria', 'email' => 'm@example.com', 'phone' => '912345678']);

        $opportunity = ImportOpportunity::create([
            'form_proposal_id' => $request->id, 'brand' => 'bmw', 'model' => 'i4', 'seller_id' => $first->id,
        ]);
        $this->assertSame([$bmw->id], $first->brands()->pluck('brands.id')->all());

        $opportunity->update(['seller_id' => $second->id]);
        $this->assertSame([$bmw->id], $second->brands()->pluck('brands.id')->all());

        $opportunity->update(['brand' => 'Audi']);
        $this->assertEqualsCanonicalizing([$bmw->id, $audi->id], $second->brands()->pluck('brands.id')->all());
        // Nunca remove: o vendedor anterior mantém a marca.
        $this->assertSame([$bmw->id], $first->brands()->pluck('brands.id')->all());
    }
}
