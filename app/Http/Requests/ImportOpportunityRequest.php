<?php

namespace App\Http\Requests;

use App\Enums\ContactMethod;
use App\Enums\ContactStatus;
use App\Enums\OpportunityStatus;
use App\Enums\VehicleFuel;
use App\Models\ImportOpportunity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Regras partilhadas entre criar e editar uma Oportunidade. Só marca e modelo
 * são obrigatórios — o resto é completado ao longo da pesquisa.
 */
abstract class ImportOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        // O acesso é controlado pelo grupo de rotas "gestao" (auth + restrictAngariador).
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect(['vin', 'country'])
            ->filter(fn ($field) => filled($this->input($field)))
            ->mapWithKeys(fn ($field) => [$field => strtoupper(trim($this->input($field)))])
            ->all());
    }

    public function rules(): array
    {
        return [
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1950|max:' . (now()->year + 1),
            'mileage' => 'nullable|integer|min:0|max:2000000',
            'price' => 'nullable|numeric|min:0|max:10000000',
            'fuel' => ['nullable', Rule::enum(VehicleFuel::class)],
            'listing_url' => 'nullable|url|max:2048',
            'vin' => 'nullable|string|size:17|regex:/^[A-HJ-NPR-Z0-9]{17}$/',
            'country' => ['nullable', Rule::in(array_keys(ImportOpportunity::COUNTRIES))],
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_photo' => 'nullable|boolean',
            'vehicle_notes' => 'nullable|string|max:5000',
            'seller_id' => 'nullable|integer|exists:sellers,id',
            'seller_contact_id' => [
                'nullable',
                'integer',
                Rule::exists('seller_contacts', 'id')->where('seller_id', $this->input('seller_id')),
            ],
            'status' => ['nullable', Rule::enum(OpportunityStatus::class)],
            'contact_method' => ['nullable', Rule::enum(ContactMethod::class)],
            'contact_status' => ['nullable', Rule::enum(ContactStatus::class)],
            'contact_used' => 'nullable|string|max:255',
            'last_contacted_at' => 'nullable|date',
            'next_followup_at' => 'nullable|date',
            'contact_notes' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'brand.required' => 'A marca é obrigatória.',
            'model.required' => 'O modelo é obrigatório.',
            'year.integer' => 'O ano tem de ser um número.',
            'year.min' => 'Ano inválido.',
            'year.max' => 'Ano inválido.',
            'mileage.integer' => 'Os quilómetros têm de ser um número inteiro.',
            'mileage.min' => 'Os quilómetros não podem ser negativos.',
            'price.numeric' => 'O preço tem de ser um número.',
            'price.min' => 'O preço não pode ser negativo.',
            'fuel.enum' => 'Combustível inválido.',
            'listing_url.url' => 'O URL do anúncio tem de ser válido (ex.: https://...).',
            'vin.size' => 'O VIN tem de ter 17 caracteres.',
            'vin.regex' => 'O VIN só pode ter letras e números (sem I, O ou Q).',
            'country.in' => 'País inválido.',
            'seller_id.exists' => 'O vendedor escolhido já não existe.',
            'seller_contact_id.exists' => 'O contacto escolhido não pertence ao vendedor.',
            'photo.image' => 'A foto tem de ser uma imagem.',
            'photo.mimes' => 'A foto tem de ser JPG, PNG ou WEBP.',
            'photo.max' => 'A foto não pode ter mais de 5 MB.',
            'status.enum' => 'Estado inválido.',
            'contact_method.enum' => 'Método de contacto inválido.',
            'contact_status.enum' => 'Estado do contacto inválido.',
            'last_contacted_at.date' => 'Data do último contacto inválida.',
            'next_followup_at.date' => 'Data de follow-up inválida.',
        ];
    }

    /** Dados para o model (sem os campos de upload). */
    public function opportunityData(): array
    {
        return collect($this->validated())->except(['photo', 'remove_photo'])->all();
    }
}
