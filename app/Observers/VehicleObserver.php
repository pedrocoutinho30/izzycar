<?php

namespace App\Observers;

use App\Models\Vehicle;
use App\Models\Expense;

class VehicleObserver
{
    public function saved(Vehicle $vehicle): void
    {
        Expense::syncFromVehicle($vehicle);
    }

    public function deleted(Vehicle $vehicle): void
    {
        // Delete the auto-generated Expense; ExpenseObserver cleans up FinancialMovement
        Expense::where('source_type', Vehicle::class)
            ->where('source_id', $vehicle->id)
            ->each(fn ($e) => $e->delete());
    }
}
