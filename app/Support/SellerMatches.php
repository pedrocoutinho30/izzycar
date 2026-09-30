<?php

namespace App\Support;

use App\Models\Seller;
use App\Models\SellerContact;
use Illuminate\Support\Collection;

/**
 * Possíveis correspondências de um contacto/vendedor em criação. O email
 * exato bloqueia; telefone, WhatsApp e domínio são avisos que o utilizador
 * tem de confirmar — nunca há associação automática.
 */
final class SellerMatches
{
    /**
     * @param  Collection<int, SellerContact>  $phone
     * @param  Collection<int, SellerContact>  $whatsapp
     * @param  Collection<int, Seller>  $domain
     */
    public function __construct(
        public readonly ?SellerContact $email,
        public readonly Collection $phone,
        public readonly Collection $whatsapp,
        public readonly Collection $domain,
        public readonly ?string $domainName = null,
    ) {
    }

    public function blocking(): bool
    {
        return $this->email !== null;
    }

    /** Avisos que exigem confirmação (sem contar com o email, que bloqueia). */
    public function needsConfirmation(): bool
    {
        return $this->phone->isNotEmpty() || $this->whatsapp->isNotEmpty() || $this->domain->isNotEmpty();
    }

    public function isEmpty(): bool
    {
        return !$this->blocking() && !$this->needsConfirmation();
    }

    public function toArray(): array
    {
        $contact = fn (SellerContact $c) => $c->toPickerArray() + [
            'seller' => $this->sellerArray($c->seller),
        ];

        return [
            'email' => $this->email ? $contact($this->email) : null,
            'phone' => $this->phone->map($contact)->values()->all(),
            'whatsapp' => $this->whatsapp->map($contact)->values()->all(),
            'domain' => $this->domain->map(fn (Seller $s) => $this->sellerArray($s) + [
                'contacts' => $s->activeContacts->map->toPickerArray()->values()->all(),
            ])->values()->all(),
            'domain_name' => $this->domainName,
            'blocking' => $this->blocking(),
            'needs_confirmation' => $this->needsConfirmation(),
        ];
    }

    private function sellerArray(Seller $seller): array
    {
        return [
            'id' => $seller->id,
            'name' => $seller->name,
            'country' => $seller->country,
            'country_label' => $seller->country_label,
            'is_active' => $seller->is_active,
            'url' => route('admin.v2.sellers.show', $seller->id),
        ];
    }
}
