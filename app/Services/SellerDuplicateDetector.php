<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\SellerContact;
use App\Support\SellerMatches;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Deteção de possíveis duplicados ao criar/editar contactos e vendedores.
 * Critérios: email completo, telefone, WhatsApp e domínio do email (este só
 * como sugestão). O nome nunca é critério — há empresas e pessoas com nomes
 * iguais.
 */
class SellerDuplicateDetector
{
    public function __construct(private ContactNormalizer $normalizer)
    {
    }

    /**
     * @param  array{email?: ?string, phone?: ?string, whatsapp?: ?string, domains?: string|array|null, country?: ?string}  $input
     * @param  int|null  $sellerId  vendedor a que o contacto pertence/vai pertencer (não se sugere ele próprio)
     * @param  int|null  $ignoreContactId  contacto em edição
     */
    public function check(array $input, ?int $sellerId = null, ?int $ignoreContactId = null): SellerMatches
    {
        $country = $input['country'] ?? ($sellerId ? Seller::whereKey($sellerId)->value('country') : null);

        $email = $this->normalizer->email($input['email'] ?? null);
        $phone = $this->normalizer->phone($input['phone'] ?? null, $country);
        $whatsapp = $this->normalizer->phone($input['whatsapp'] ?? null, $country);

        $emailMatch = $email
            ? $this->contacts($ignoreContactId)->where('email_normalized', $email)->first()
            : null;

        $phoneMatches = $this->numberMatches($phone, $ignoreContactId);
        $whatsappMatches = $whatsapp && $whatsapp !== $phone
            ? $this->numberMatches($whatsapp, $ignoreContactId)
            : collect();

        [$domainName, $domainMatches] = $emailMatch
            ? [null, collect()]
            : $this->domainMatches($email, $input['domains'] ?? null, $sellerId);

        return new SellerMatches($emailMatch, $phoneMatches, $whatsappMatches, $domainMatches, $domainName);
    }

    private function contacts(?int $ignoreContactId): Builder
    {
        return SellerContact::query()
            ->with('seller')
            ->when($ignoreContactId, fn (Builder $q) => $q->whereKeyNot($ignoreContactId));
    }

    /** Um número coincide se for o telefone OU o WhatsApp de outro contacto. */
    private function numberMatches(?string $number, ?int $ignoreContactId): Collection
    {
        if ($number === null) {
            return collect();
        }

        return $this->contacts($ignoreContactId)
            ->where(fn (Builder $q) => $q->where('phone_normalized', $number)->orWhere('whatsapp_normalized', $number))
            ->limit(5)
            ->get();
    }

    /**
     * Vendedores com o mesmo domínio (nos domínios registados ou nos emails
     * dos contactos). Ignora fornecedores de email genéricos.
     *
     * @return array{0: ?string, 1: Collection<int, Seller>}
     */
    private function domainMatches(?string $email, string|array|null $domains, ?int $sellerId): array
    {
        $candidates = collect(is_array($domains) ? $domains : $this->normalizer->domains($domains))
            ->prepend($this->normalizer->emailDomain($email))
            ->filter(fn ($domain) => $domain && !$this->normalizer->isGenericDomain($domain))
            ->unique()
            ->values();

        if ($candidates->isEmpty()) {
            return [null, collect()];
        }

        $sellers = Seller::query()
            ->with('activeContacts')
            ->when($sellerId, fn (Builder $q) => $q->whereKeyNot($sellerId))
            ->where(function (Builder $q) use ($candidates) {
                $q->whereHas('domains', fn (Builder $d) => $d->whereIn('domain', $candidates))
                    ->orWhereHas('contacts', fn (Builder $c) => $c->whereIn('email_domain', $candidates));
            })
            ->limit(5)
            ->get();

        return [$candidates->first(), $sellers];
    }
}
