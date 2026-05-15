<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\Expense;

class SaleObserver
{
    public function saved(Sale $sale): void
    {
        Expense::syncFromSale($sale);
    }

    public function deleted(Sale $sale): void
    {
        // Delete the auto-generated Expense; ExpenseObserver cleans up FinancialMovement
        Expense::where('source_type', Sale::class)
            ->where('source_id', $sale->id)
            ->each(fn ($e) => $e->delete());
    }
}
