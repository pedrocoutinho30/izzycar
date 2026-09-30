<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SellerContactRequest extends FormRequest
{
    use SellerContactRules;

    public function authorize(): bool
    {
        // O acesso é controlado pelo grupo de rotas "gestao" (auth + restrictAngariador).
        return true;
    }

    public function rules(): array
    {
        return ['contact' => 'required|array'] + $this->contactRules();
    }

    public function messages(): array
    {
        return $this->contactMessages();
    }
}
