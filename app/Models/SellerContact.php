<?php

namespace App\Models;

use App\Services\ContactNormalizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pessoa de um vendedor. Ao guardar, preenche as colunas normalizadas usadas
 * na deteção de duplicados (o país do vendedor dá o indicativo dos números
 * nacionais).
 */
class SellerContact extends Model
{
    protected $fillable = [
        'seller_id',
        'name',
        'role',
        'email',
        'phone',
        'whatsapp',
        'notes',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_primary' => false,
        'is_active' => true,
    ];

    protected static function booted(): void
    {
        static::saving(function (SellerContact $contact) {
            $normalizer = app(ContactNormalizer::class);
            $country = $contact->seller?->country;

            $contact->email = $normalizer->email($contact->email);
            $contact->email_normalized = $contact->email;
            $contact->email_domain = $normalizer->emailDomain($contact->email);
            $contact->phone_normalized = $normalizer->phone($contact->phone, $country);
            $contact->whatsapp_normalized = $normalizer->phone($contact->whatsapp, $country);
        });
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(ImportOpportunity::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(V3Vehicle::class);
    }

    /** Número para links wa.me — o WhatsApp, ou o telefone se não houver. */
    public function getWhatsappDigitsAttribute(): ?string
    {
        return $this->whatsapp_normalized ?: $this->phone_normalized;
    }

    /** Dados para os selects/alertas em JS. */
    public function toPickerArray(): array
    {
        return [
            'id' => $this->id,
            'seller_id' => $this->seller_id,
            'name' => $this->name,
            'role' => $this->role,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'is_primary' => $this->is_primary,
            'is_active' => $this->is_active,
        ];
    }
}
