<?php

namespace App\Observers;

use App\Models\Vehicle;
use App\Models\FinancialMovement;

class VehicleObserver
{
    public function saved(Vehicle $vehicle): void
    {
        // Only sync if purchase price is set
        if ($vehicle->purchase_price) {
            FinancialMovement::syncFromVehicle($vehicle);
        }
    }

    public function deleted(Vehicle $vehicle): void
    {
        FinancialMovement::where('movable_type', Vehicle::class)
            ->where('movable_id', $vehicle->id)
            ->delete();
    }
}
