<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\V3Vehicle;
use App\Models\V3VehiclePhoto;
use App\Support\VehicleCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Cache da listagem pública: chave só com filtros permitidos e só a foto de capa em cache. */
class PublicVehicleListingCacheTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        foreach (['address', 'email', 'facebook', 'insta', 'phone', 'vat_number'] as $label) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => '+351928459346']);
        }
        Setting::create(['title' => 'logo', 'label' => 'logo', 'type' => 'image', 'value' => 'settings/logo.png']);
    }

    private function vehicle(string $brand, array $fotos): V3Vehicle
    {
        $v = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => $brand, 'model' => 'M', 'status' => 'em_stock', 'show_online' => true]);
        foreach ($fotos as $i => [$path, $cover]) {
            V3VehiclePhoto::create(['v3_vehicle_id' => $v->id, 'path' => $path, 'order_position' => $i + 1, 'is_cover' => $cover]);
        }

        return $v;
    }

    private function vehicleQueries(callable $fn): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $fn();
        $n = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'v3_vehicle'))->count();
        DB::disableQueryLog();

        return $n;
    }

    public function test_so_a_foto_de_capa_fica_em_cache_e_e_a_que_se_mostra(): void
    {
        $this->vehicle('BMW', [['v3-vehicles/1/a.jpg', false], ['v3-vehicles/1/capa.jpg', true], ['v3-vehicles/1/c.jpg', false]]);
        $this->vehicle('Audi', [['v3-vehicles/2/primeira.jpg', false], ['v3-vehicles/2/segunda.jpg', false]]); // sem capa: cai na 1.ª

        $html = $this->get(route('vehicles.list'))->assertOk()->getContent();

        $this->assertStringContainsString('v3-vehicles/1/capa.jpg', $html);
        $this->assertStringNotContainsString('v3-vehicles/1/a.jpg', $html);
        $this->assertStringContainsString('v3-vehicles/2/primeira.jpg', $html);

        $emCache = Cache::get(VehicleCache::key('vehicles_' . md5(json_encode([]))));
        $this->assertCount(2, $emCache);
        foreach ($emCache as $v) {
            $this->assertCount(1, $v->photos);
        }
    }

    public function test_parametros_desconhecidos_nao_geram_novas_chaves_de_cache(): void
    {
        $this->vehicle('BMW', [['v3-vehicles/1/capa.jpg', true]]);

        $primeira = $this->vehicleQueries(fn () => $this->get(route('vehicles.list'))->assertOk());
        $comLixo = $this->vehicleQueries(fn () => $this->get(route('vehicles.list', ['utm_source' => 'x', 'fbclid' => 'abc', 'page' => 3, 'brand' => '']))->assertOk());
        fwrite(STDERR, "\n[listagem pública] queries a v3_vehicle*: 1.ª visita={$primeira}, com utm/fbclid/page (cache hit)={$comLixo}\n");

        $this->assertGreaterThan(0, $primeira);
        $this->assertSame(0, $comLixo);
    }

    public function test_filtros_permitidos_filtram_e_ordem_dos_parametros_nao_importa(): void
    {
        $this->vehicle('BMW', [['v3-vehicles/1/bmw.jpg', true]]);
        $this->vehicle('Audi', [['v3-vehicles/2/audi.jpg', true]]);

        $html = $this->get(route('vehicles.list', ['brand' => 'BMW']))->assertOk()->getContent();
        $this->assertStringContainsString('v3-vehicles/1/bmw.jpg', $html);
        $this->assertStringNotContainsString('v3-vehicles/2/audi.jpg', $html);

        // Mesmos filtros, outra ordem e parâmetros alheios: continua a ser cache hit.
        $n = $this->vehicleQueries(fn () => $this->get(route('vehicles.list', ['fuel' => '', 'utm' => '1', 'brand' => 'BMW']))->assertOk());
        $this->assertSame(0, $n);
    }

    public function test_alterar_uma_viatura_invalida_a_listagem(): void
    {
        $v = $this->vehicle('BMW', [['v3-vehicles/1/capa.jpg', true]]);
        $this->get(route('vehicles.list'))->assertOk();

        $v->update(['show_online' => false]);

        $this->assertStringNotContainsString('v3-vehicles/1/capa.jpg', $this->get(route('vehicles.list'))->assertOk()->getContent());
    }
}
