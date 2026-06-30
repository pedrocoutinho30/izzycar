<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsBlock extends Model
{
    protected $fillable = [
        'cms_page_id', 'type', 'name',
        'title', 'subtitle', 'body',
        'data', 'button_text', 'button_url',
        'button2_text', 'button2_url',
        'image', 'active', 'order',
    ];

    protected $casts = [
        'data'   => 'array',
        'active' => 'boolean',
    ];

    // Human labels for each block type
    public const TYPES = [
        'hero'   => 'Hero (Banner Principal)',
        'badges' => 'Badges de Confiança',
        'text'   => 'Texto / Destaque',
        'cards'  => 'Cards / Serviços',
        'steps'  => 'Passos / Processo',
        'cta'    => 'Call to Action',
        'costs'  => 'Custos / Tabela de Preços',
        'faq'    => 'FAQ — Perguntas Frequentes',
    ];

    // Fields in data[] per type, used to render the repeater editor
    public const DATA_SCHEMA = [
        'badges' => [
            ['key' => 'icon',  'label' => 'Ícone Bootstrap (ex: bi-shield-check)', 'type' => 'text'],
            ['key' => 'title', 'label' => 'Título',   'type' => 'text'],
            ['key' => 'text',  'label' => 'Subtexto', 'type' => 'text'],
        ],
        'cards' => [
            ['key' => 'icon',        'label' => 'Ícone Bootstrap', 'type' => 'text'],
            ['key' => 'title',       'label' => 'Título',          'type' => 'text'],
            ['key' => 'text',        'label' => 'Descrição',       'type' => 'textarea'],
            ['key' => 'link',        'label' => 'Link (opcional)', 'type' => 'text'],
            ['key' => 'button_text', 'label' => 'Texto do Botão',  'type' => 'text'],
        ],
        'steps' => [
            ['key' => 'number', 'label' => 'Número (ex: 01)',  'type' => 'text'],
            ['key' => 'title',  'label' => 'Título do Passo',  'type' => 'text'],
            ['key' => 'text',   'label' => 'Descrição',        'type' => 'textarea'],
        ],
        'costs' => [
            ['key' => 'icon',  'label' => 'Ícone Bootstrap', 'type' => 'text'],
            ['key' => 'title', 'label' => 'Item de Custo',   'type' => 'text'],
            ['key' => 'text',  'label' => 'Descrição',       'type' => 'textarea'],
            ['key' => 'badge', 'label' => 'Badge (opcional)', 'type' => 'text'],
        ],
        'faq' => [
            ['key' => 'question', 'label' => 'Pergunta', 'type' => 'text'],
            ['key' => 'answer',   'label' => 'Resposta', 'type' => 'textarea'],
        ],
    ];

    public function page()
    {
        return $this->belongsTo(CmsPage::class, 'cms_page_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
