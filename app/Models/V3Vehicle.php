<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\VehicleAttributeValue;

class V3Vehicle extends Model
{
    use HasFactory;

    protected $table = 'v3_vehicles';

    protected $fillable = [
        'reference',
        // General
        'brand', 'model', 'sub_model', 'version', 'year', 'month', 'day', 'fuel',
        'kilometers', 'power', 'cylinder_capacity',
        'color', 'vin', 'registration',
        'manufacture_date', 'register_date', 'available_to_sell_date',
        'notes', 'ad_text', 'show_online', 'is_imported', 'status',
        // Purchase
        'supplier_id',
        'purchase_price', 'purchase_date', 'purchase_type',
        'purchase_vat_rate', 'purchase_vat_paid',
        'asking_price',
    ];

    protected $casts = [
        'manufacture_date'       => 'date',
        'register_date'          => 'date',
        'available_to_sell_date' => 'date',
        'purchase_date'          => 'date',
        'show_online'            => 'boolean',
        'is_imported'            => 'boolean',
        'purchase_price'         => 'float',
        'purchase_vat_rate'      => 'float',
        'purchase_vat_paid'      => 'float',
        'asking_price'           => 'float',
        'kilometers'             => 'integer',
        'year'                   => 'integer',
        'month'                  => 'integer',
        'day'                    => 'integer',
    ];

    // ── Reference generation ────────────────────────────────────────────────

    public static function generateReference(): string
    {
        $year  = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;
        return 'V3-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    // ── Relationships ───────────────────────────────────────────────────────

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(V3VehiclePhoto::class, 'v3_vehicle_id')->orderBy('order_position');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(V3VehicleDocument::class, 'v3_vehicle_id');
    }

    public function legalization(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Legalization::class, 'v3_vehicle_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'v3_vehicle_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'v3_vehicle_id');
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(VehicleAttributeValue::class, 'v3_vehicle_id');
    }

    // ── Computed attributes ─────────────────────────────────────────────────

    public function getCoverPhotoAttribute(): ?V3VehiclePhoto
    {
        return $this->photos->where('is_cover', true)->first() ?? $this->photos->first();
    }

    public function getExpensesTotalAttribute(): float
    {
        return (float) $this->expenses
            ->where('movement_type', 'expense')
            ->where('category', '!=', 'vehicle_purchase')
            ->sum('amount_gross');
    }

    public function getTotalCostAttribute(): float
    {
        return (float) ($this->purchase_price ?? 0) + $this->expenses_total;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'em_stock'  => 'Em Stock',
            'vendido'   => 'Vendido',
            'reservado' => 'Reservado',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'em_stock'  => 'success',
            'vendido'   => 'secondary',
            'reservado' => 'warning',
            default     => 'secondary',
        };
    }

    public static function statusOptions(): array
    {
        return ['em_stock' => 'Em Stock', 'reservado' => 'Reservado', 'vendido' => 'Vendido'];
    }

    public static function fuelOptions(): array
    {
        return ['Gasolina', 'Diesel', 'Elétrico', 'Híbrido Plug-In', 'Híbrido', 'GPL', 'Hidrogénio'];
    }

    /**
     * Formatted year string: "2022", "03/2022", or "15/03/2022"
     */
    public function getYearLabelAttribute(): string
    {
        if (!$this->year) return '—';
        if ($this->month && $this->day) return str_pad($this->day, 2, '0', STR_PAD_LEFT) . '/' . str_pad($this->month, 2, '0', STR_PAD_LEFT) . '/' . $this->year;
        if ($this->month) return str_pad($this->month, 2, '0', STR_PAD_LEFT) . '/' . $this->year;
        return (string) $this->year;
    }
}
