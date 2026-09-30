<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\SellerContact;
use App\Models\User;
use App\Support\SellerMatches;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Regras dos Vendedores e respetivos contactos: um só contacto principal
 * (e ativo) por vendedor, desativar em vez de eliminar quando há histórico, e
 * nunca guardar por cima de uma possível correspondência sem confirmação.
 */
class SellerService
{
    public function __construct(private SellerDuplicateDetector $detector)
    {
    }

    /**
     * Bloqueia se o email já existe; exige confirmação explícita para as
     * correspondências por telefone, WhatsApp ou domínio.
     *
     * @throws ValidationException
     */
    public function ensureNoUnconfirmedMatches(SellerMatches $matches, bool $confirmed, string $emailField = 'contact.email'): void
    {
        if ($matches->email) {
            throw ValidationException::withMessages([
                $emailField => "Já existe um contacto com este email: {$matches->email->name} ({$matches->email->seller->name}).",
            ]);
        }

        if ($matches->needsConfirmation() && !$confirmed) {
            throw ValidationException::withMessages([
                'matches' => 'Encontrámos possíveis correspondências com vendedores/contactos existentes. Reveja-as antes de guardar.',
            ]);
        }
    }

    public function checkContact(array $contact, ?int $sellerId, ?string $country = null, ?int $ignoreContactId = null, string|array|null $domains = null): SellerMatches
    {
        return $this->detector->check([
            'email' => $contact['email'] ?? null,
            'phone' => $contact['phone'] ?? null,
            'whatsapp' => $contact['whatsapp'] ?? null,
            'domains' => $domains,
            'country' => $country,
        ], $sellerId, $ignoreContactId);
    }

    /**
     * Cria o vendedor e, se vier, o primeiro contacto (fica como principal).
     */
    public function create(array $sellerData, ?array $contactData, ?User $user): Seller
    {
        return DB::transaction(function () use ($sellerData, $contactData, $user) {
            $domains = $sellerData['domains'] ?? null;
            unset($sellerData['domains']);

            $seller = Seller::create($sellerData + ['created_by' => $user?->id]);
            $seller->syncDomains($domains);

            if ($contactData) {
                $this->addContact($seller, $contactData);
            }

            return $seller;
        });
    }

    public function update(Seller $seller, array $data): Seller
    {
        return DB::transaction(function () use ($seller, $data) {
            $countryChanged = array_key_exists('country', $data) && $data['country'] !== $seller->country;

            if (array_key_exists('domains', $data)) {
                $seller->syncDomains($data['domains']);
                unset($data['domains']);
            }

            $seller->update($data);

            // O indicativo dos números nacionais depende do país.
            if ($countryChanged) {
                $seller->contacts()->get()->each(fn (SellerContact $contact) => $contact->setRelation('seller', $seller)->save());
            }

            return $seller;
        });
    }

    public function addContact(Seller $seller, array $data): SellerContact
    {
        return DB::transaction(function () use ($seller, $data) {
            $makePrimary = (bool) ($data['is_primary'] ?? false) || !$seller->primaryContact()->exists();

            $contact = new SellerContact(collect($data)->except('is_primary')->all());
            $contact->seller()->associate($seller);
            $contact->save();

            if ($makePrimary) {
                $this->setPrimary($contact);
            }

            return $contact;
        });
    }

    public function updateContact(SellerContact $contact, array $data): SellerContact
    {
        return DB::transaction(function () use ($contact, $data) {
            $contact->update(collect($data)->except('is_primary')->all());

            if ((bool) ($data['is_primary'] ?? false)) {
                $this->setPrimary($contact);
            }

            return $contact;
        });
    }

    public function setPrimary(SellerContact $contact): void
    {
        DB::transaction(function () use ($contact) {
            SellerContact::where('seller_id', $contact->seller_id)
                ->whereKeyNot($contact->id)
                ->update(['is_primary' => false]);

            $contact->update(['is_primary' => true, 'is_active' => true]);
        });
    }

    /** Um contacto inativo deixa de poder ser o principal. */
    public function setContactActive(SellerContact $contact, bool $active): void
    {
        $contact->update($active ? ['is_active' => true] : ['is_active' => false, 'is_primary' => false]);
    }

    public function setActive(Seller $seller, bool $active): void
    {
        $seller->update(['is_active' => $active]);
    }

    /**
     * Só sem veículos, oportunidades nem contactos — caso contrário, desativar.
     *
     * @throws ValidationException
     */
    public function delete(Seller $seller): void
    {
        if ($seller->hasHistory()) {
            throw ValidationException::withMessages([
                'seller' => 'Este vendedor tem contactos, veículos ou oportunidades associados — desative-o em vez de o eliminar.',
            ]);
        }

        $seller->delete();
    }
}
