<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Vehicle;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class VehicleLegadoSemDespesaTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_guardar_viatura_legada_nao_cria_despesa_de_compra(): void
    {
        $vehicle = new Vehicle();
        $vehicle->forceFill(['reference' => 'T-001', 'vin' => 'VIN-T-001', 'brand' => 'Mercedes-Benz', 'model' => 'Classe CLA', 'purchase_price' => 26500]);
        $vehicle->save();

        $this->assertSame(0, Expense::where('source_type', Vehicle::class)->count());
    }
}
