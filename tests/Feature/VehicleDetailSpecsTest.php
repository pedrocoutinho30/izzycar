<?php

namespace Tests\Feature;

use App\Models\V3Vehicle;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class VehicleDetailSpecsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        DB::connection()->getPdo()->sqliteCreateFunction('FIELD', fn ($value, ...$list) => ($i = array_search($value, $list, true)) === false ? 0 : $i + 1);
        foreach (['address', 'email', 'facebook', 'insta', 'phone', 'vat_number'] as $label) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => 'x']);
        }
    }

    private function detail(string $fuel): string
    {
        $vehicle = V3Vehicle::create([
            'reference' => V3Vehicle::generateReference(), 'brand' => 'Audi', 'model' => 'Q4',
            'fuel' => $fuel, 'status' => 'em_stock', 'show_online' => true,
        ]);

        return $this->get(route('vehicles.details', ['brand' => 'audi', 'model' => 'q4', 'id' => $vehicle->reference]))
            ->assertOk()
            ->getContent();
    }

    public function test_electric_vehicle_does_not_show_displacement(): void
    {
        $this->assertStringNotContainsString('Cilindrada', $this->detail('Elétrico'));
    }

    public function test_combustion_and_hybrid_vehicles_show_displacement(): void
    {
        $this->assertStringContainsString('Cilindrada', $this->detail('Gasolina'));
        $this->assertStringContainsString('Cilindrada', $this->detail('Híbrido Plug-In'));
    }
}
