<?php

namespace App\Models;

use App\Enums\ContactMethod;
use App\Enums\ContactStatus;
use App\Enums\OpportunityStatus;
use App\Enums\VehicleFuel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Oportunidade: carro/anúncio em análise como possível solução para um
 * Pedido de Importação (FormProposal). Não existe sem pedido — a FK é
 * obrigatória e elimina em cascata.
 */
class ImportOpportunity extends Model
{
    /** Países de origem mais comuns nas importações (ISO 3166-1 alpha-2). */
    public const COUNTRIES = [
        'DE' => 'Alemanha',
        'AT' => 'Áustria',
        'BE' => 'Bélgica',
        'DK' => 'Dinamarca',
        'ES' => 'Espanha',
        'FR' => 'França',
        'NL' => 'Países Baixos',
        'IT' => 'Itália',
        'LU' => 'Luxemburgo',
        'PL' => 'Polónia',
        'CZ' => 'Chéquia',
        'SE' => 'Suécia',
        'NO' => 'Noruega',
        'CH' => 'Suíça',
        'GB' => 'Reino Unido',
        'PT' => 'Portugal',
    ];

    protected $fillable = [
        'form_proposal_id',
        'proposal_id',
        'brand',
        'model',
        'version',
        'year',
        'mileage',
        'price',
        'currency',
        'fuel',
        'listing_url',
        'vin',
        'country',
        'photo_path',
        'vehicle_notes',
        'seller_id',
        'seller_contact_id',
        'client_quote_requested_at',
        'status',
        'contact_method',
        'contact_status',
        'contact_used',
        'last_contacted_at',
        'next_followup_at',
        'contact_notes',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'mileage' => 'integer',
        'price' => 'decimal:2',
        'fuel' => VehicleFuel::class,
        'status' => OpportunityStatus::class,
        'contact_method' => ContactMethod::class,
        'contact_status' => ContactStatus::class,
        'last_contacted_at' => 'datetime',
        'next_followup_at' => 'date',
        'client_quote_requested_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'por_contactar',
        'contact_status' => 'nao_contactado',
        'currency' => 'EUR',
    ];

    public function formProposal(): BelongsTo
    {
        return $this->belongsTo(FormProposal::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function sellerContact(): BelongsTo
    {
        return $this->belongsTo(SellerContact::class);
    }

    public function checklistEntries(): HasMany
    {
        return $this->hasMany(ImportOpportunityChecklistEntry::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ImportOpportunityContact::class)->orderByDesc('contacted_at')->orderByDesc('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTitleAttribute(): string
    {
        return trim("{$this->brand} {$this->model}" . ($this->year ? " {$this->year}" : ''));
    }

    /**
     * asset() (como no resto da app) e não Storage::url(): este usa APP_URL,
     * que pode não ser o domínio por onde o backoffice está a ser aberto.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    public function getFormattedPriceAttribute(): ?string
    {
        if ($this->price === null) {
            return null;
        }

        return number_format((float) $this->price, 0, ',', '.') . ' €';
    }
}
