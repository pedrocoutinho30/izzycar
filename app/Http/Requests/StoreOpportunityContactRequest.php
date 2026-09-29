<?php

namespace App\Http\Requests;

use App\Enums\ContactMethod;
use App\Enums\ContactType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOpportunityContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contacted_at' => 'required|date',
            'method' => ['required', Rule::enum(ContactMethod::class)],
            'type' => ['required', Rule::enum(ContactType::class)],
            'message' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'contacted_at.required' => 'Indique a data/hora do contacto.',
            'contacted_at.date' => 'Data/hora inválida.',
            'method.required' => 'Escolha o método de contacto.',
            'method.enum' => 'Método de contacto inválido.',
            'type.required' => 'Escolha o tipo de contacto.',
            'type.enum' => 'Tipo de contacto inválido.',
        ];
    }
}
