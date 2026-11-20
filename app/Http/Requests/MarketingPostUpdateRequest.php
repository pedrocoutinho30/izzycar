<?php

namespace App\Http\Requests;

use App\Enums\MarketingFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Edições do editor (guardar e pré-visualizar). O acesso é do grupo de rotas. */
class MarketingPostUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template' => ['sometimes', 'string', 'max:40'],
            'format' => ['sometimes', Rule::in(MarketingFormat::values())],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'caption' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'hashtags' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'cta' => ['sometimes', 'nullable', 'string', 'max:255'],
            'scheduled_for' => ['sometimes', 'nullable', 'date_format:Y-m-d\TH:i,Y-m-d H:i:s,Y-m-d H:i'],
            'channel' => ['sometimes', 'nullable', Rule::in(\App\Models\MarketingPost::CHANNELS)],
            'status' => ['sometimes', Rule::in(['rascunho', 'em_revisao', 'aprovado'])],
            'overrides' => ['sometimes', 'array'],
            'overrides.title' => ['nullable', 'string', 'max:120'],
            'overrides.subtitle' => ['nullable', 'string', 'max:160'],
            'overrides.price' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'overrides.savings' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'overrides.cta' => ['nullable', 'string', 'max:120'],
            'overrides.badge' => ['nullable', 'string', 'max:40'],
            'overrides.highlights' => ['nullable', 'array', 'max:6'],
            'overrides.highlights.*' => ['nullable', 'string', 'max:80'],
            'overrides.quote' => ['nullable', 'string', 'max:280'],
            'overrides.show_full_name' => ['nullable', 'boolean'],
            'overrides.items' => ['nullable', 'array', 'max:8'],
            'overrides.items.*' => ['array'],
            'overrides.items.*.a' => ['nullable', 'string', 'max:160'],
            'overrides.items.*.b' => ['nullable', 'string', 'max:500'],
            'photo_order' => ['sometimes', 'array', 'max:100'],
            'photo_order.*' => ['integer', 'min:0', 'max:1000'],
            'focal' => ['sometimes', 'array'],
            'focal.*' => ['array', 'size:2'],
            'focal.*.*' => ['numeric', 'between:0,100'],
            'gallery_layouts' => ['sometimes', 'array'],
            'gallery_layouts.*' => ['string', Rule::in(['full', 'duo', 'trio-left', 'trio-top', 'trio-bottom'])],
            'gallery_photos_per_slide' => ['sometimes', Rule::in([1, 3])],
        ];
    }
}
