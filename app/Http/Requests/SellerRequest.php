<?php

namespace App\Http\Requests;

use App\Models\ImportOpportunity;
use App\Services\ContactNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Criar/editar um vendedor, opcionalmente com o primeiro contacto. Com
 * existing_seller_id (o utilizador escolheu "Adicionar a este vendedor" numa
 * correspondência por domínio) só o contacto é criado, nesse vendedor.
 */
class SellerRequest extends FormRequest
{
    use SellerContactRules;

    public function authorize(): bool
    {
        // O acesso é controlado pelo grupo de rotas "gestao" (auth + restrictAngariador).
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (filled($this->input('country'))) {
            $this->merge(['country' => strtoupper(trim($this->input('country')))]);
        }
    }

    public function rules(): array
    {
        return [
            'existing_seller_id' => 'nullable|integer|exists:sellers,id',
            'name' => 'required_without:existing_seller_id|nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'country' => ['nullable', Rule::in(array_keys(ImportOpportunity::COUNTRIES))],
            'address' => 'nullable|string|max:2000',
            'domains' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:20000',
        ] + $this->contactRules($this->filled('existing_seller_id')
            ? 'required'
            : 'required_with:contact.role,contact.email,contact.phone,contact.whatsapp,contact.notes');
    }

    public function messages(): array
    {
        return [
            'name.required_without' => 'O nome do vendedor é obrigatório.',
            'country.in' => 'País inválido.',
            'existing_seller_id.exists' => 'O vendedor escolhido já não existe.',
        ] + $this->contactMessages();
    }

    public function existingSellerId(): ?int
    {
        return $this->filled('existing_seller_id') ? (int) $this->input('existing_seller_id') : null;
    }

    /** @return list<string> */
    public function domainList(): array
    {
        return app(ContactNormalizer::class)->domains($this->validated('domains'));
    }

    public function sellerData(): array
    {
        return collect($this->validated())
            ->only(['name', 'website', 'country', 'address', 'notes'])
            ->put('domains', $this->domainList())
            ->all();
    }
}
