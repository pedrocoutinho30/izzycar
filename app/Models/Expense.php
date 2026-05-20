<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        // Movement classification
        'movement_type',     // income | expense | transfer | salary | commission | tax | refund | other
        'expense_category',  // legacy field (Empresa / Veiculo / Outros)
        'category',          // new taxonomy: vehicle_purchase, repair, transport, tax, etc.
        // References
        'vehicle_id',
        'v3_vehicle_id',
        'client_id',
        'partner_id',
        // Description & financials
        'title',
        'amount',            // gross amount entered by user
        'amount_gross',      // = amount (stored explicitly)
        'vat_rate',          // % (e.g. 23)
        'vat_amount',        // computed VAT portion
        'amount_net',        // amount_gross - vat_amount
        // Date & extras
        'expense_date',
        'observations',
        'attachment_path',
        // Payment info
        'payment_method',    // cash | bank_transfer | mbway | card | financing | other
        'status',            // pending | paid | cancelled | partially_paid
        // Source tracking (auto-created from sale/vehicle)
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'expense_date'  => 'date',
        'amount'        => 'float',
        'amount_gross'  => 'float',
        'vat_rate'      => 'float',
        'vat_amount'    => 'float',
        'amount_net'    => 'float',
    ];

    // ── Type helpers ────────────────────────────────────────────────────────

    public static function movementTypes(): array
    {
        return [
            'expense'    => 'Despesa',
            'income'     => 'Receita',
            'transfer'   => 'Transferência',
            'salary'     => 'Salário',
            'commission' => 'Comissão',
            'tax'        => 'Imposto',
            'refund'     => 'Reembolso',
            'other'      => 'Outro',
        ];
    }

    public static function categories(): array
    {
        return [
            'vehicle_purchase' => 'Compra de Veículo',
            'vehicle_sale'     => 'Venda de Veículo',
            'transport'        => 'Transporte',
            'repair'           => 'Reparação',
            'legalization'     => 'Legalização',
            'insurance'        => 'Seguro',
            'fuel'             => 'Combustível',
            'marketing'        => 'Marketing',
            'salary'           => 'Salário',
            'rent'             => 'Renda',
            'commission'       => 'Comissão',
            'tax'              => 'Imposto',
            'office'           => 'Escritório',
            'other'            => 'Outro',
        ];
    }

    public static function paymentMethods(): array
    {
        return [
            'cash'             => 'Numerário',
            'bank_transfer'    => 'Transferência Bancária',
            'mbway'            => 'MBWay',
            'card'             => 'Cartão',
            'financing'        => 'Financiamento',
            'other'            => 'Outro',
        ];
    }

    public static function statuses(): array
    {
        return [
            'paid'             => 'Pago',
            'pending'          => 'Pendente',
            'partially_paid'   => 'Parcialmente Pago',
            'cancelled'        => 'Cancelado',
        ];
    }

    public function getMovementTypeLabelAttribute(): string
    {
        return self::movementTypes()[$this->movement_type] ?? $this->movement_type;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categories()[$this->category] ?? ($this->expense_category ?? '—');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function isIncome(): bool
    {
        return $this->movement_type === 'income';
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function v3Vehicle()
    {
        return $this->belongsTo(V3Vehicle::class, 'v3_vehicle_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    // ── Auto-sync from Sale ──────────────────────────────────────────────────

    /**
     * Create or update the auto-generated Expense record for a Sale (income entry).
     * The ExpenseObserver will then sync it to financial_movements.
     */
    public static function syncFromSale(Sale $sale): void
    {
        $sale->loadMissing(['vehicle', 'v3Vehicle', 'client']);

        $vehicle   = $sale->vehicle;
        $v3Vehicle = $sale->v3Vehicle;

        if (!$vehicle && !$v3Vehicle) return;

        if ($v3Vehicle) {
            // ── V3 sale ───────────────────────────────────────────────────
            $brand = $v3Vehicle->brand;
            $model = $v3Vehicle->model;
            $year  = $v3Vehicle->year;
        } else {
            // ── V2 sale ───────────────────────────────────────────────────
            $brand = $vehicle->brand;
            $model = $vehicle->model;
            $year  = $vehicle->year;
        }

        $title = 'Venda ' . $brand . ' ' . $model
            . ($year ? ' (' . $year . ')' : '');
        if ($sale->client) {
            $title .= ' – ' . $sale->client->name;
        }

        $amountGross = (float) $sale->sale_price;
        $vatAmount   = (float) ($sale->vat_settle_sale ?? 0);
        $amountNet   = $amountGross - $vatAmount;
        $vatRate     = $vatAmount > 0 && $amountGross > 0
            ? round($vatAmount / ($amountGross - $vatAmount) * 100, 2)
            : 0;

        $paymentMethodMap = [
            'Financiamento'    => 'financing',
            'Pronto pagamento' => 'cash',
        ];
        $paymentMethod = $paymentMethodMap[$sale->payment_method] ?? 'other';

        self::updateOrCreate(
            ['source_type' => Sale::class, 'source_id' => $sale->id],
            [
                'movement_type'  => 'income',
                'category'       => 'vehicle_sale',
                'title'          => $title,
                'vehicle_id'     => $vehicle ? $vehicle->id : null,
                'v3_vehicle_id'  => $v3Vehicle ? $v3Vehicle->id : null,
                'client_id'      => $sale->client_id,
                'amount'         => $amountGross,
                'amount_gross'   => $amountGross,
                'vat_rate'       => $vatRate,
                'vat_amount'     => $vatAmount,
                'amount_net'     => $amountNet,
                'expense_date'   => $sale->sale_date ?? now()->toDateString(),
                'payment_method' => $paymentMethod,
                'status'         => 'paid',
                'observations'   => $sale->observation,
            ]
        );
    }

    // ── Auto-sync from V3Vehicle ─────────────────────────────────────────────

    /**
     * Create or update the auto-generated Expense record for a V3Vehicle purchase.
     */
    public static function syncFromV3Vehicle(V3Vehicle $vehicle): void
    {
        if (!$vehicle->purchase_price) {
            self::where('source_type', V3Vehicle::class)
                ->where('source_id', $vehicle->id)
                ->each(fn ($e) => $e->delete());
            return;
        }

        $title = 'Compra ' . $vehicle->brand . ' ' . $vehicle->model
            . ($vehicle->year ? ' (' . $vehicle->year . ')' : '')
            . ($vehicle->reference ? ' – Ref. ' . $vehicle->reference : '');

        $amountNet   = (float) $vehicle->purchase_price;
        $vatAmount   = (float) ($vehicle->purchase_vat_paid ?? 0);
        $amountGross = $amountNet + $vatAmount;
        $vatRate     = $vatAmount > 0 && $amountNet > 0
            ? round($vatAmount / $amountNet * 100, 2)
            : 0;

        $movementDate = $vehicle->purchase_date
            ?? ($vehicle->created_at ? $vehicle->created_at->toDateString() : now()->toDateString());

        self::updateOrCreate(
            ['source_type' => V3Vehicle::class, 'source_id' => $vehicle->id],
            [
                'movement_type'  => 'expense',
                'category'       => 'vehicle_purchase',
                'title'          => $title,
                'v3_vehicle_id'  => $vehicle->id,
                'amount'         => $amountGross,
                'amount_gross'   => $amountGross,
                'vat_rate'       => $vatRate,
                'vat_amount'     => $vatAmount,
                'amount_net'     => $amountNet,
                'expense_date'   => $movementDate,
                'payment_method' => 'other',
                'status'         => 'paid',
            ]
        );
    }

    // ── Auto-sync from Vehicle ───────────────────────────────────────────────

    /**
     * Create or update the auto-generated Expense record for a Vehicle purchase (expense entry).
     * The ExpenseObserver will then sync it to financial_movements.
     */
    public static function syncFromVehicle(Vehicle $vehicle): void
    {
        if (!$vehicle->purchase_price) {
            // Purchase price removed — delete the auto entry
            self::where('source_type', Vehicle::class)
                ->where('source_id', $vehicle->id)
                ->each(fn ($e) => $e->delete());
            return;
        }

        $title = 'Compra ' . $vehicle->brand . ' ' . $vehicle->model
            . ($vehicle->year ? ' (' . $vehicle->year . ')' : '')
            . ($vehicle->reference ? ' – Ref. ' . $vehicle->reference : '');

        $amountNet   = (float) $vehicle->purchase_price;
        $vatAmount   = (float) ($vehicle->purchase_vat_paid ?? 0);
        $amountGross = $amountNet + $vatAmount;
        $vatRate     = $vatAmount > 0 && $amountNet > 0
            ? round($vatAmount / $amountNet * 100, 2)
            : 0;

        $movementDate = $vehicle->purchase_date
            ?? ($vehicle->created_at ? $vehicle->created_at->toDateString() : now()->toDateString());

        self::updateOrCreate(
            ['source_type' => Vehicle::class, 'source_id' => $vehicle->id],
            [
                'movement_type'  => 'expense',
                'category'       => 'vehicle_purchase',
                'title'          => $title,
                'vehicle_id'     => $vehicle->id,
                'amount'         => $amountGross,
                'amount_gross'   => $amountGross,
                'vat_rate'       => $vatRate,
                'vat_amount'     => $vatAmount,
                'amount_net'     => $amountNet,
                'expense_date'   => $movementDate,
                'payment_method' => 'other',
                'status'         => 'paid',
            ]
        );
    }
}
