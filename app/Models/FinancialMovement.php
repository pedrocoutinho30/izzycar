<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialMovement extends Model
{
    protected $fillable = [
        'movable_type',
        'movable_id',
        'type',
        'category',
        'description',
        'amount_gross',
        'vat_rate',
        'vat_amount',
        'amount_net',
        'movement_date',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'amount_gross'  => 'float',
        'vat_rate'      => 'float',
        'vat_amount'    => 'float',
        'amount_net'    => 'float',
    ];

    public function movable()
    {
        return $this->morphTo();
    }

    // ----------------------------------------------------------------
    // Helpers to sync from source entities
    // ----------------------------------------------------------------

    /**
     * Sync (create or update) the financial movement for a Sale.
     */
    public static function syncFromSale(Sale $sale): void
    {
        $vehicle = $sale->vehicle;
        if (!$vehicle) return;

        $description = 'Venda ' . $vehicle->brand . ' ' . $vehicle->model
            . ($vehicle->year ? ' (' . $vehicle->year . ')' : '');

        if ($sale->client) {
            $description .= ' – ' . $sale->client->name;
        }

        $amountGross = (float) $sale->sale_price;
        $vatAmount   = (float) ($sale->vat_settle_sale ?? 0);
        $amountNet   = $amountGross - $vatAmount;
        $vatRate     = $vatAmount > 0 && $amountGross > 0
            ? round($vatAmount / ($amountGross - $vatAmount) * 100, 2)
            : null;

        self::updateOrCreate(
            ['movable_type' => Sale::class, 'movable_id' => $sale->id],
            [
                'type'          => 'income',
                'category'      => 'Venda',
                'description'   => $description,
                'amount_gross'  => $amountGross,
                'vat_rate'      => $vatRate,
                'vat_amount'    => $vatAmount,
                'amount_net'    => $amountNet,
                'movement_date' => $sale->sale_date ?? now()->toDateString(),
            ]
        );
    }

    /**
     * Sync (create or update) the financial movement for a Vehicle purchase.
     */
    public static function syncFromVehicle(Vehicle $vehicle): void
    {
        if (!$vehicle->purchase_price) return;

        $description = 'Compra ' . $vehicle->brand . ' ' . $vehicle->model
            . ($vehicle->year ? ' (' . $vehicle->year . ')' : '')
            . ($vehicle->reference ? ' – Ref. ' . $vehicle->reference : '');

        $purchaseType = $vehicle->purchase_type;
        $net          = (float) $vehicle->purchase_price; // always stored as net
        $vatPaid      = (float) ($vehicle->purchase_vat_paid ?? 0);
        $vatRate      = $vatPaid > 0 && $net > 0
            ? round($vatPaid / $net * 100, 2)
            : null;
        $gross        = $net + $vatPaid;

        // Use purchase_date if set, otherwise fall back to created_at
        $movementDate = $vehicle->purchase_date
            ?? ($vehicle->created_at ? $vehicle->created_at->toDateString() : now()->toDateString());

        self::updateOrCreate(
            ['movable_type' => Vehicle::class, 'movable_id' => $vehicle->id],
            [
                'type'          => 'expense',
                'category'      => 'Compra Veículo',
                'description'   => $description,
                'amount_gross'  => $gross,
                'vat_rate'      => $vatRate,
                'vat_amount'    => $vatPaid,
                'amount_net'    => $net,
                'movement_date' => $movementDate,
            ]
        );
    }

    /**
     * Sync (create or update) the financial movement for an Expense/Movement.
     * Supports both expense and income movement_type.
     */
    public static function syncFromExpense(Expense $expense): void
    {
        if (!$expense->amount) return;

        // Use pre-computed values if available, otherwise compute from amount + vat_rate
        $amountGross = (float) ($expense->amount_gross ?? $expense->amount);
        $vatRateNum  = (float) ($expense->vat_rate ?? 0);
        $vatAmount   = (float) ($expense->vat_amount
            ?? ($vatRateNum > 0 ? round($amountGross - ($amountGross / (1 + $vatRateNum / 100)), 2) : 0.0));
        $amountNet   = (float) ($expense->amount_net ?? ($amountGross - $vatAmount));

        // Use new movement_type if set, default to 'expense'
        $movableType = in_array($expense->movement_type, ['income']) ? 'income' : 'expense';

        // Use new category if available, otherwise map old type/expense_category
        $category = $expense->category
            ?? $expense->expense_category
            ?? ($expense->type ?? 'other');

        // Map to Portuguese label for the financial ledger
        $categoryLabel = \App\Models\Expense::categories()[$category] ?? ucfirst($category);

        $description = $expense->title ?? $categoryLabel;

        self::updateOrCreate(
            ['movable_type' => Expense::class, 'movable_id' => $expense->id],
            [
                'type'          => $movableType,
                'category'      => $categoryLabel,
                'description'   => $description,
                'amount_gross'  => $amountGross,
                'vat_rate'      => $vatRateNum > 0 ? $vatRateNum : null,
                'vat_amount'    => $vatAmount,
                'amount_net'    => $amountNet,
                'movement_date' => $expense->expense_date ?? now()->toDateString(),
            ]
        );
    }
}
