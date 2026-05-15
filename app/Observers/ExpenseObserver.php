<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\FinancialMovement;

class ExpenseObserver
{
    public function saved(Expense $expense): void
    {
        FinancialMovement::syncFromExpense($expense);
    }

    public function deleted(Expense $expense): void
    {
        FinancialMovement::where('movable_type', Expense::class)
            ->where('movable_id', $expense->id)
            ->delete();
    }
}
