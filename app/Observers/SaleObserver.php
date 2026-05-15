<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\FinancialMovement;

class SaleObserver
{
    public function saved(Sale $sale): void
    {
        FinancialMovement::syncFromSale($sale);
    }

    public function deleted(Sale $sale): void
    {
        FinancialMovement::where('movable_type', Sale::class)
            ->where('movable_id', $sale->id)
            ->delete();
    }
}
