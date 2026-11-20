<?php

namespace App\Http\Requests;

use App\Enums\MarketingFormat;
use App\Enums\MarketingPostType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Criação de um post a partir de uma viatura V3. O acesso é do grupo de rotas. */
class MarketingPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([MarketingPostType::Veiculo->value])],
            'vehicle_id' => ['required', 'integer', 'exists:v3_vehicles,id'],
            'template' => ['required', 'string'],
            'format' => ['required', Rule::in(MarketingFormat::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Escolhe uma viatura.',
            'vehicle_id.exists' => 'A viatura escolhida não existe.',
            'template.required' => 'Escolhe um template.',
            'format.required' => 'Escolhe um formato.',
        ];
    }
}
