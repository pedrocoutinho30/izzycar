<?php

namespace Tests\Feature;

use App\Models\RadarListing;
use App\Models\RadarSearch;
use App\Models\Supplier;
use App\Models\TransportQuote;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Radar (listagem, detalhe, filtros e ações simples; sem scraper), transportes e comparador de carros.
 * Não se testa criar/editar/correr/apagar do radar: escrevem YAML em scarperAutoscout/searches e
 * lançam o scraper (processo externo).
 */
class RadarTransportesComparadorCoberturaTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Http::preventStrayRequests();
    }

    private function admin()
    {
        return $this->actingAs($this->backofficeUser('admin'));
    }

    private function search(array $over = []): RadarSearch
    {
        return RadarSearch::create($over + ['name' => 'bmw-serie-3', 'make' => 'BMW', 'model' => 'Serie 3', 'base_url' => 'https://www.autoscout24.test/lst']);
    }

    private function listing(RadarSearch $search, array $over = []): RadarListing
    {
        static $n = 0;
        $n++;

        return RadarListing::create($over + [
            'external_id' => 'ext-' . $n, 'source' => 'autoscout24', 'radar_search_id' => $search->id, 'make' => 'BMW', 'model' => 'Serie 3',
            'version' => 'Versao ' . $n, 'first_registration_year' => 2019, 'mileage_km' => 80000, 'price_eur' => 20000,
            'url' => 'https://www.autoscout24.test/anuncio/' . $n, 'first_seen_at' => now()->subDay(), 'last_seen_at' => now(),
        ]);
    }

    // ── Radar: acesso ────────────────────────────────────────────────────────

    public function test_radar_requires_login_and_blocks_angariador(): void
    {
        $search = $this->search();
        $listing = $this->listing($search);

        $this->get(route('admin.v2.radar.index'))->assertRedirect(route('login'));
        $this->get(route('admin.v2.radar.show', $search))->assertRedirect(route('login'));
        $this->patch(route('admin.v2.radar.toggle-active', $search), ['active' => 0])->assertRedirect(route('login'));

        $angariador = $this->backofficeUser('angariador');
        $this->actingAs($angariador)->get(route('admin.v2.radar.index'))->assertForbidden();
        $this->actingAs($angariador)->get(route('admin.v2.radar.show', $search))->assertForbidden();
        $this->actingAs($angariador)->patch(route('admin.v2.radar.toggle-active', $search), ['active' => 0])->assertForbidden();
        $this->actingAs($angariador)->patch(route('admin.v2.radar.toggle-average', $listing), ['include' => 0])->assertForbidden();
        $this->actingAs($angariador)->delete(route('admin.v2.radar.destroy', $search))->assertForbidden();

        $this->assertNotNull(RadarSearch::find($search->id));
    }

    // ── Radar: listagem e detalhe ────────────────────────────────────────────

    public function test_index_lists_searches_with_active_listing_counts_only(): void
    {
        $search = $this->search(['new_listings_seen_at' => now()->subDays(2)]);
        $this->search(['name' => 'audi-a4', 'make' => 'Audi', 'model' => 'A4']);
        $this->listing($search);
        $this->listing($search);
        $this->listing($search, ['removed_at' => now()]); // removido: não conta
        $this->listing($search, ['source' => 'standvirtual']); // PT: conta à parte

        $response = $this->admin()->get(route('admin.v2.radar.index'))->assertOk()->assertSee('bmw-serie-3')->assertSee('audi-a4');

        $bmw = $response->viewData('searches')->firstWhere('name', 'bmw-serie-3');
        $this->assertSame(2, (int) $bmw->listings_count);
        $this->assertSame(1, (int) $bmw->pt_listings_count);
        $this->assertSame(3, (int) $bmw->new_listings_count);
    }

    public function test_index_without_searches_renders(): void
    {
        $this->admin()->get(route('admin.v2.radar.index'))->assertOk();
    }

    public function test_show_lists_active_listings_and_hides_removed_and_duplicates(): void
    {
        $search = $this->search();
        $this->listing($search, ['version' => 'VISIVEL-ATIVO']);
        $this->listing($search, ['version' => 'OCULTO-REMOVIDO', 'removed_at' => now()]);
        $original = $this->listing($search, ['version' => 'OCULTO-ORIGINAL-PT', 'source' => 'standvirtual']);
        $this->listing($search, ['version' => 'OCULTO-DUPLICADO', 'source' => 'carmine', 'duplicate_of_listing_id' => $original->id]);

        $response = $this->admin()->get(route('admin.v2.radar.show', $search))
            ->assertOk()
            ->assertSee('VISIVEL-ATIVO')
            ->assertDontSee('OCULTO-REMOVIDO')
            ->assertDontSee('OCULTO-DUPLICADO');

        $this->assertSame(1, $response->viewData('listings')->total());
    }

    public function test_show_filters_by_km_and_price_ranges(): void
    {
        $search = $this->search();
        $this->listing($search, ['version' => 'BARATO-POUCOS-KM', 'price_eur' => 10000, 'mileage_km' => 30000]);
        $this->listing($search, ['version' => 'CARO-MUITOS-KM', 'price_eur' => 40000, 'mileage_km' => 150000]);

        $this->admin()->get(route('admin.v2.radar.show', ['radarSearch' => $search, 'price_max' => 20000]))
            ->assertOk()->assertSee('BARATO-POUCOS-KM')->assertDontSee('CARO-MUITOS-KM');
        $this->admin()->get(route('admin.v2.radar.show', ['radarSearch' => $search, 'km_min' => 100000]))
            ->assertOk()->assertSee('CARO-MUITOS-KM')->assertDontSee('BARATO-POUCOS-KM');
        $this->admin()->get(route('admin.v2.radar.show', ['radarSearch' => $search, 'price_min' => 5000, 'price_max' => 50000, 'km_max' => 200000]))
            ->assertOk()->assertSee('BARATO-POUCOS-KM')->assertSee('CARO-MUITOS-KM');
    }

    public function test_show_sorting_is_whitelisted_and_defaults_apply(): void
    {
        $search = $this->search();
        $this->listing($search, ['version' => 'A', 'price_eur' => 30000]);
        $this->listing($search, ['version' => 'B', 'price_eur' => 10000]);

        // "sort" arbitrário volta ao preço (lista fechada), direção desc respeitada.
        $r = $this->admin()->get(route('admin.v2.radar.show', ['radarSearch' => $search, 'sort' => 'password; drop table', 'dir' => 'desc']))->assertOk();
        $this->assertSame('price_eur', $r->viewData('sort'));
        $this->assertSame('desc', $r->viewData('dir'));

        $r = $this->admin()->get(route('admin.v2.radar.show', ['radarSearch' => $search, 'sort' => 'price_eur', 'dir' => 'asc']))->assertOk();
        $this->assertSame([10000, 30000], $r->viewData('listings')->pluck('price_eur')->map(fn ($p) => (int) $p)->all());

        $this->admin()->get(route('admin.v2.radar.show', ['radarSearch' => $search, 'sort' => 'rank']))->assertOk();
    }

    public function test_show_computes_price_stats_including_import_cost_and_marks_visit(): void
    {
        $search = $this->search(['import_cost_eur' => 1000, 'new_listings_seen_at' => now()->subDays(5)]);
        $this->listing($search, ['price_eur' => 10000]);
        $this->listing($search, ['price_eur' => 20000]);
        $this->listing($search, ['price_eur' => 30000]);
        $this->listing($search, ['price_eur' => 99999, 'removed_at' => now()]); // não entra nas estatísticas

        $response = $this->admin()->get(route('admin.v2.radar.show', $search))->assertOk();

        $stats = $response->viewData('deStats');
        $this->assertSame(3, $stats['count']);
        $this->assertEquals(21000, $stats['avg']);    // (10+20+30)/3 + 1000 de custo de importação
        $this->assertEquals(21000, $stats['median']);
        $this->assertEquals(11000, $stats['min']);
        $this->assertEquals(31000, $stats['max']);
        $this->assertNull($response->viewData('ptStats')); // sem fontes PT configuradas

        // O valor anterior chega à vista; a base de dados fica com "agora" (badge de novidades limpa).
        $this->assertTrue($response->viewData('newSince')->lt(now()->subDays(4)));
        $this->assertTrue($search->fresh()->new_listings_seen_at->gt(now()->subMinute()));
    }

    public function test_show_without_listings_has_empty_stats(): void
    {
        $search = $this->search();

        $response = $this->admin()->get(route('admin.v2.radar.show', $search))->assertOk();

        $this->assertSame(0, $response->viewData('deStats')['count']);
        $this->assertNull($response->viewData('deStats')['avg']);
    }

    public function test_show_unknown_search_is_404(): void
    {
        $this->admin()->get(route('admin.v2.radar.show', 9999))->assertNotFound();
    }

    // ── Radar: ações simples ─────────────────────────────────────────────────

    public function test_toggle_active_and_update_import_cost(): void
    {
        $search = $this->search();

        $this->admin()->patchJson(route('admin.v2.radar.toggle-active', $search), ['active' => 1])->assertOk()->assertJson(['is_active' => true]);
        $this->assertTrue($search->fresh()->is_active);
        $this->admin()->patchJson(route('admin.v2.radar.toggle-active', $search), ['active' => 0])->assertOk()->assertJson(['is_active' => false]);

        $this->admin()->patch(route('admin.v2.radar.update-import-cost', $search), ['import_cost_eur' => 1500.5])
            ->assertRedirect(route('admin.v2.radar.show', $search))->assertSessionHas('success');
        $this->assertEquals(1500.5, $search->fresh()->import_cost_eur);

        $this->admin()->patch(route('admin.v2.radar.update-import-cost', $search), ['import_cost_eur' => ''])->assertRedirect();
        $this->assertNull($search->fresh()->import_cost_eur);

        $this->admin()->patch(route('admin.v2.radar.update-import-cost', $search), ['import_cost_eur' => -5])->assertSessionHasErrors('import_cost_eur');
        $this->admin()->patch(route('admin.v2.radar.update-import-cost', $search), ['import_cost_eur' => 'abc'])->assertSessionHasErrors('import_cost_eur');
    }

    public function test_toggle_average_recalculates_portuguese_stats(): void
    {
        $search = $this->search(['standvirtual_base_url' => 'https://www.standvirtual.test/lst']);
        $a = $this->listing($search, ['source' => 'standvirtual', 'price_eur' => 10000]);
        $this->listing($search, ['source' => 'carmine', 'price_eur' => 30000]);

        $this->admin()->patchJson(route('admin.v2.radar.toggle-average', $a), ['include' => 0])
            ->assertOk()->assertJsonPath('count', 1)->assertJsonPath('avg', 30000);
        $this->assertFalse($a->fresh()->include_in_average);

        $this->admin()->patchJson(route('admin.v2.radar.toggle-average', $a), ['include' => 1])
            ->assertOk()->assertJsonPath('count', 2)->assertJsonPath('avg', 20000);
    }

    // ── Radar: equipamento ───────────────────────────────────────────────────

    public function test_equipment_page_requires_login_and_renders_for_admin(): void
    {
        $this->get(route('admin.v2.radar-equipment.index'))->assertRedirect(route('login'));
        $this->actingAs($this->backofficeUser('angariador'))->get(route('admin.v2.radar-equipment.index'))->assertForbidden();
        $this->admin()->get(route('admin.v2.radar-equipment.index'))->assertOk();
    }

    // ── Transportes ──────────────────────────────────────────────────────────

    private function supplier(string $name = 'Transportes Rapidos'): Supplier
    {
        static $n = 0;
        $n++;

        return Supplier::create(['type' => 'Particular', 'company_name' => $name, 'contact_name' => 'Contacto', 'email' => "t{$n}@teste.test"]);
    }

    private function quote(Supplier $supplier, array $over = []): TransportQuote
    {
        return TransportQuote::create($over + [
            'brand' => 'BMW', 'model' => 'i4', 'origin_location' => 'Munique, Alemanha', 'supplier_id' => $supplier->id, 'price' => 650, 'quote_date' => '2026-10-01',
            'destination_city' => 'Oliveira de Azeméis', 'destination_country' => 'Portugal',
        ]);
    }

    public function test_transport_quotes_require_login_and_block_angariador(): void
    {
        $quote = $this->quote($this->supplier());

        $this->get(route('admin.transport-quotes.index'))->assertRedirect(route('login'));
        $this->post(route('admin.transport-quotes.store'), [])->assertRedirect(route('login'));
        $this->get(route('admin.transport-quotes.map-data'))->assertRedirect(route('login'));
        $this->delete(route('admin.transport-quotes.destroy', $quote->id))->assertRedirect(route('login'));

        $angariador = $this->backofficeUser('angariador');
        $this->actingAs($angariador)->get(route('admin.transport-quotes.index'))->assertForbidden();
        $this->actingAs($angariador)->get(route('admin.transport-quotes.map-data'))->assertForbidden();
        $this->actingAs($angariador)->delete(route('admin.transport-quotes.destroy', $quote->id))->assertForbidden();

        $this->assertSame(1, TransportQuote::count());
    }

    public function test_transport_quote_crud_sets_fixed_destination(): void
    {
        $supplier = $this->supplier();
        $this->admin()->get(route('admin.transport-quotes.create'))->assertOk();

        $this->admin()->post(route('admin.transport-quotes.store'), [
            'brand' => 'Audi', 'model' => 'A3', 'origin_location' => 'Berlim', 'supplier_id' => $supplier->id, 'price' => 700, 'quote_date' => '2026-10-05',
            'origin_latitude' => 52.52, 'origin_longitude' => 13.4, 'destination_city' => 'Lisboa',
        ])->assertRedirect(route('admin.transport-quotes.index'))->assertSessionHas('success');

        $quote = TransportQuote::sole();
        // O destino é sempre Oliveira de Azeméis, mesmo que o pedido traga outro.
        $this->assertSame('Oliveira de Azeméis', $quote->destination_city);
        $this->assertEquals(40.8397, $quote->destination_latitude);

        $this->admin()->get(route('admin.transport-quotes.edit', $quote->id))->assertOk()->assertSee('Audi');
        $this->admin()->put(route('admin.transport-quotes.update', $quote->id), [
            'brand' => 'Audi', 'model' => 'A4', 'origin_location' => 'Hamburgo', 'supplier_id' => $supplier->id, 'price' => 720, 'quote_date' => '2026-10-06',
        ])->assertRedirect(route('admin.transport-quotes.index'));
        $this->assertSame('A4', $quote->fresh()->model);

        $this->admin()->delete(route('admin.transport-quotes.destroy', $quote->id))->assertRedirect(route('admin.transport-quotes.index'));
        $this->assertSame(0, TransportQuote::count());
    }

    public function test_transport_quote_validation(): void
    {
        $url = route('admin.transport-quotes.store');

        $this->admin()->post($url, [])->assertSessionHasErrors(['brand', 'model', 'origin_location', 'supplier_id', 'price', 'quote_date']);
        $this->admin()->post($url, [
            'brand' => 'A', 'model' => 'B', 'origin_location' => 'C', 'supplier_id' => 9999, 'price' => -1, 'quote_date' => 'ontem',
            'origin_latitude' => 120, 'origin_longitude' => 200,
        ])->assertSessionHasErrors(['supplier_id', 'price', 'quote_date', 'origin_latitude', 'origin_longitude']);

        $this->assertSame(0, TransportQuote::count());
        $this->admin()->get(route('admin.transport-quotes.edit', 9999))->assertNotFound();
        $this->admin()->delete(route('admin.transport-quotes.destroy', 9999))->assertNotFound();
    }

    public function test_transport_index_filters(): void
    {
        $rapidos = $this->supplier('Transportes Rapidos');
        $lentos = $this->supplier('Transportes Lentos');
        $this->quote($rapidos, ['brand' => 'MARCA-RAPIDA', 'origin_location' => 'Munique', 'car_type' => 'SUV', 'quote_date' => '2026-10-10']);
        $this->quote($lentos, ['brand' => 'MARCA-LENTA', 'origin_location' => 'Hamburgo', 'car_type' => 'Ligeiro', 'quote_date' => '2026-01-10']);

        $this->admin()->get(route('admin.transport-quotes.index', ['supplier_id' => $rapidos->id]))->assertOk()->assertSee('MARCA-RAPIDA')->assertDontSee('MARCA-LENTA');
        $this->admin()->get(route('admin.transport-quotes.index', ['car_type' => 'Ligeiro']))->assertOk()->assertSee('MARCA-LENTA')->assertDontSee('MARCA-RAPIDA');
        $this->admin()->get(route('admin.transport-quotes.index', ['origin_location' => 'Muniq']))->assertOk()->assertSee('MARCA-RAPIDA')->assertDontSee('MARCA-LENTA');
        $this->admin()->get(route('admin.transport-quotes.index', ['date_from' => '2026-06-01']))->assertOk()->assertSee('MARCA-RAPIDA')->assertDontSee('MARCA-LENTA');
        $this->admin()->get(route('admin.transport-quotes.index', ['date_to' => '2026-06-01']))->assertOk()->assertSee('MARCA-LENTA')->assertDontSee('MARCA-RAPIDA');
    }

    public function test_transport_map_data_only_returns_quotes_with_coordinates_and_filters(): void
    {
        $supplier = $this->supplier();
        $this->quote($supplier, ['brand' => 'COM-COORD', 'origin_latitude' => 48.1, 'origin_longitude' => 11.5, 'price' => 650]);
        $this->quote($supplier, ['brand' => 'SEM-COORD']);

        $dados = $this->admin()->getJson(route('admin.transport-quotes.map-data'))->assertOk()->json();
        $this->assertCount(1, $dados);
        $this->assertSame('COM-COORD', $dados[0]['brand']);
        $this->assertEquals(650, $dados[0]['price']);
        $this->assertEquals(48.1, $dados[0]['lat']);
        $this->assertSame('Transportes Rapidos', $dados[0]['supplier']);
        $this->assertSame('01/10/2026', $dados[0]['quote_date']);

        $this->assertCount(0, $this->admin()->getJson(route('admin.transport-quotes.map-data', ['brand' => 'OUTRA']))->json());
        $this->admin()->get(route('admin.transport-quotes.map'))->assertOk();
    }

    public function test_geocode_requires_location(): void
    {
        $this->admin()->postJson(route('admin.transport-quotes.geocode'), [])->assertStatus(422)->assertJsonValidationErrors('location');
    }

    // ── Comparador de carros ─────────────────────────────────────────────────

    private function cars(int $n = 2): array
    {
        return array_map(fn ($i) => ['brand' => 'BMW', 'model' => "Serie $i", 'year' => 2020, 'km' => 50000, 'fuel' => 'Diesel', 'price' => 20000], range(1, $n));
    }

    public function test_comparator_requires_login_and_blocks_angariador(): void
    {
        $this->get(route('admin.v2.comparator.index'))->assertRedirect(route('login'));
        $this->postJson(route('admin.v2.comparator.analyze'), ['cars' => $this->cars()])->assertUnauthorized();

        $angariador = $this->backofficeUser('angariador');
        $this->actingAs($angariador)->get(route('admin.v2.comparator.index'))->assertForbidden();
        $this->actingAs($angariador)->postJson(route('admin.v2.comparator.analyze'), ['cars' => $this->cars()])->assertForbidden();
    }

    public function test_comparator_page_renders(): void
    {
        $this->admin()->get(route('admin.v2.comparator.index'))->assertOk();
    }

    public function test_comparator_validates_number_of_cars_and_fields(): void
    {
        $this->admin()->postJson(route('admin.v2.comparator.analyze'), [])->assertStatus(422)->assertJsonValidationErrors('cars');
        $this->admin()->postJson(route('admin.v2.comparator.analyze'), ['cars' => $this->cars(1)])->assertStatus(422)->assertJsonValidationErrors('cars');
        $this->admin()->postJson(route('admin.v2.comparator.analyze'), ['cars' => $this->cars(6)])->assertStatus(422)->assertJsonValidationErrors('cars');

        $cars = $this->cars();
        $cars[0]['year'] = 1950;
        $cars[1]['price'] = -3;
        unset($cars[1]['brand']);
        $this->admin()->postJson(route('admin.v2.comparator.analyze'), ['cars' => $cars])->assertStatus(422)
            ->assertJsonValidationErrors(['cars.0.year', 'cars.1.price', 'cars.1.brand']);
    }

    public function test_comparator_without_openai_key_answers_500_json_without_external_request(): void
    {
        config(['services.openai.key' => null]);

        $this->admin()->postJson(route('admin.v2.comparator.analyze'), ['cars' => $this->cars()])
            ->assertStatus(500)
            ->assertJsonPath('error', 'OPENAI_API_KEY não configurada. Adicione-a ao ficheiro .env.');

        Http::assertNothingSent();
    }
}
