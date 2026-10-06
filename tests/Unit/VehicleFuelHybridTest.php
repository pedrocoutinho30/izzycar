<?php

namespace Tests\Unit;

use App\Enums\VehicleFuel;
use App\Models\V3Vehicle;
use PHPUnit\Framework\TestCase;

class VehicleFuelHybridTest extends TestCase
{
    public function test_hev_options_exist_with_labels(): void
    {
        $this->assertSame('Gasolina (HEV)', VehicleFuel::HybridPetrol->label());
        $this->assertSame('Diesel (HEV)', VehicleFuel::HybridDiesel->label());
        $this->assertSame('Gasolina (HEV)', VehicleFuel::HybridPetrol->proposalLabel());
    }

    public function test_hev_labels_resolve_back_to_enum(): void
    {
        $this->assertSame(VehicleFuel::HybridPetrol, VehicleFuel::fromLoose('Gasolina (HEV)'));
        $this->assertSame(VehicleFuel::HybridDiesel, VehicleFuel::fromLoose('Diesel (HEV)'));
        $this->assertSame(VehicleFuel::HybridPetrol, VehicleFuel::fromLoose('hibrido_gasolina'));
        $this->assertSame(VehicleFuel::PluginHybridDiesel, VehicleFuel::fromLoose('Híbrido Plug-in/Diesel'));
    }

    public function test_vehicle_fuel_options_include_hev(): void
    {
        $this->assertContains('Gasolina (HEV)', V3Vehicle::fuelOptions());
        $this->assertContains('Diesel (HEV)', V3Vehicle::fuelOptions());
    }
}
