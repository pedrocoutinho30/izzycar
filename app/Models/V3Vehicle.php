<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Support\VehicleCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\VehicleAttributeValue;
use App\Models\Concerns\AssociatesSellerBrand;

class V3Vehicle extends Model
{
    use HasFactory, AssociatesSellerBrand;

    protected static function booted(): void
    {
        // O site público guarda a listagem em cache: qualquer alteração a
        // uma viatura (estado, preço, publicação...) invalida-a.
        static::saved(fn () => VehicleCache::flush());
        static::deleted(fn () => VehicleCache::flush());
    }

    protected $table = 'v3_vehicles';

    protected $fillable = [
        'reference',
        // General
        'brand', 'model', 'sub_model', 'version', 'year', 'month', 'day', 'fuel',
        'kilometers', 'power', 'cylinder_capacity',
        'color', 'vin', 'registration',
        'manufacture_date', 'register_date', 'available_to_sell_date',
        'notes', 'ad_text', 'show_online', 'home_featured', 'home_featured_order', 'is_imported', 'status',
        // Importação: o carro é do cliente (não há venda).
        'client_id',
        // Purchase
        'supplier_id',
        'seller_id', 'seller_contact_id',
        'purchase_price', 'purchase_date', 'purchase_type',
        'purchase_vat_rate', 'purchase_vat_paid',
        'asking_price',
        'generated_from_inspection_id',
    ];

    protected $casts = [
        'manufacture_date'       => 'date',
        'register_date'          => 'date',
        'available_to_sell_date' => 'date',
        'purchase_date'          => 'date',
        'show_online'            => 'boolean',
        'home_featured'          => 'boolean',
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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function sellerContact(): BelongsTo
    {
        return $this->belongsTo(SellerContact::class);
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

    /**
     * Legalização usada na exportação. A BD permite várias por viatura (não há
     * índice único em v3_vehicle_id) e hasOne() sem ordem não é determinista:
     * aqui fica sempre a mais antiga (menor id), que é a que o MySQL devolve
     * na prática no ecrã da viatura.
     */
    public function exportLegalization(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Legalization::class, 'v3_vehicle_id')->orderBy('id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'v3_vehicle_id');
    }

    /** Movimentos ligados à legalização desta viatura que não estão ligados diretamente à viatura. */
    public function legalizationExpenses(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Expense::class, Legalization::class, 'v3_vehicle_id', 'legalization_id')
            ->where(fn ($q) => $q->whereNull('expenses.v3_vehicle_id')->orWhere('expenses.v3_vehicle_id', '!=', $this->id))
            ->orderByDesc('expenses.expense_date');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'v3_vehicle_id');
    }

    public function generatedFromInspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'generated_from_inspection_id');
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(VehicleAttributeValue::class, 'v3_vehicle_id');
    }

    // ── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Filtros da listagem do backoffice (search, status, fuel), partilhados com a
     * exportação XLSX. Chaves vazias ou ausentes não filtram.
     *
     * @param  array{search?: mixed, status?: mixed, fuel?: mixed}  $filters
     */
    public function scopeFiltrar(Builder $query, array $filters): Builder
    {
        $filled = fn (string $key) => isset($filters[$key]) && trim((string) $filters[$key]) !== '';

        if ($filled('search')) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%$s%")
                  ->orWhere('brand', 'like', "%$s%")
                  ->orWhere('model', 'like', "%$s%")
                  ->orWhere('registration', 'like', "%$s%");
            });
        }

        if ($filled('status')) {
            $query->where('status', $filters['status']);
        }

        if ($filled('fuel')) {
            $query->where('fuel', $filters['fuel']);
        }

        return $query;
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
        return ['Gasolina', 'Diesel', 'Elétrico', 'Gasolina (HEV)', 'Diesel (HEV)', 'Híbrido Plug-In', 'Híbrido', 'GPL', 'Hidrogénio'];
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
