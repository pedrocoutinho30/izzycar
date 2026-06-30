<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsBlockType extends Model
{
    protected $fillable = ['key', 'label', 'layout', 'fields', 'system', 'active', 'order'];

    protected $casts = [
        'fields' => 'array',
        'system' => 'boolean',
        'active' => 'boolean',
    ];

    public const LAYOUTS = [
        'hero'          => 'Hero (Banner)',
        'text'          => 'Texto livre',
        'cards-grid'    => 'Cards em grelha',
        'list'          => 'Lista com ícone',
        'steps'         => 'Passos numerados',
        'accordion'     => 'Accordion / FAQ',
        'image-gallery' => 'Galeria de imagens',
        'cta'           => 'Call to Action',
        'costs'         => 'Tabela de custos',
    ];

    public const FIELD_TYPES = [
        'text'     => 'Texto curto',
        'textarea' => 'Texto longo',
        'url'      => 'URL / Link',
        'image'    => 'Imagem (caminho)',
    ];

    public static function allActive()
    {
        return static::where('active', true)->orderBy('order')->orderBy('label')->get();
    }
}
