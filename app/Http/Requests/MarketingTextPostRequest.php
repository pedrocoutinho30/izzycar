<?php

namespace App\Http\Requests;

use App\Enums\MarketingFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Criação de um post educativo ou de serviço (sem fonte). O acesso é do grupo de rotas. */
class MarketingTextPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $topics = collect(config('marketing.topics'))->where('type', $this->route('type'))->pluck('key')->all();

        return [
            'topic' => ['nullable', 'string', Rule::in($topics)],
            'title' => ['nullable', 'string', 'max:120', 'required_without:topic'],
            'template' => ['required', 'string'],
            'format' => ['required', Rule::in(MarketingFormat::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required_without' => 'Escreve um título ou escolhe um tema.',
            'topic.in' => 'O tema escolhido não existe para este tipo de publicação.',
            'template.required' => 'Escolhe um template.',
            'format.required' => 'Escolhe um formato.',
        ];
    }
}
