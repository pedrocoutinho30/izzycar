<?php

namespace App\Models;

use App\Services\ContactNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Vendedor: a empresa/stand (nunca uma pessoa — as pessoas são
 * SellerContact). Oportunidades e veículos apontam para aqui, para que os
 * dados do vendedor existam num só sítio.
 */
class Seller extends Model
{
    protected $fillable = [
        'name',
        'website',
        'country',
        'address',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(SellerDomain::class)->orderBy('domain');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(SellerContact::class)
            ->orderByDesc('is_active')
            ->orderByDesc('is_primary')
            ->orderBy('name');
    }

    public function activeContacts(): HasMany
    {
        return $this->contacts()->where('is_active', true);
    }

    public function primaryContact(): HasOne
    {
        return $this->hasOne(SellerContact::class)->where('is_primary', true)->where('is_active', true);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(V3Vehicle::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(ImportOpportunity::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Pesquisa por nome, domínio ou dados de qualquer contacto (nome, email,
     * telefone, WhatsApp — os números também em dígitos normalizados).
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = "%{$term}%";
        $digits = preg_replace('/\D/', '', $term);

        return $query->where(function (Builder $q) use ($like, $digits) {
            $q->where('name', 'like', $like)
                ->orWhere('website', 'like', $like)
                ->orWhereHas('domains', fn (Builder $d) => $d->where('domain', 'like', $like))
                ->orWhereHas('contacts', function (Builder $c) use ($like, $digits) {
                    $c->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('whatsapp', 'like', $like);

                    if (strlen($digits) >= 4) {
                        $c->orWhere('phone_normalized', 'like', "%{$digits}%")
                            ->orWhere('whatsapp_normalized', 'like', "%{$digits}%");
                    }
                });
        });
    }

    public function getCountryLabelAttribute(): ?string
    {
        return $this->country ? (ImportOpportunity::COUNTRIES[$this->country] ?? $this->country) : null;
    }

    public function getWebsiteUrlAttribute(): ?string
    {
        if (blank($this->website)) {
            return null;
        }

        return preg_match('#^https?://#i', $this->website) ? $this->website : 'https://' . $this->website;
    }

    /** Domínios em texto, para o campo do formulário. */
    public function getDomainsTextAttribute(): string
    {
        return $this->domains->pluck('domain')->implode(', ');
    }

    /** @param list<string>|string|null $domains */
    public function syncDomains(array|string|null $domains): void
    {
        $domains = is_array($domains) ? $domains : app(ContactNormalizer::class)->domains($domains);

        $this->domains()->whereNotIn('domain', $domains)->delete();

        foreach ($domains as $domain) {
            $this->domains()->firstOrCreate(['domain' => $domain]);
        }
    }

    public function hasHistory(): bool
    {
        return $this->vehicles()->exists() || $this->opportunities()->exists() || $this->contacts()->exists();
    }
}
