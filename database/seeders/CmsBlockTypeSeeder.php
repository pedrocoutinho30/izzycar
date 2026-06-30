<?php

namespace Database\Seeders;

use App\Models\CmsBlockType;
use Illuminate\Database\Seeder;

class CmsBlockTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'key'    => 'hero',
                'label'  => 'Hero (Banner Principal)',
                'layout' => 'hero',
                'system' => true,
                'order'  => 1,
                'fields' => null,
            ],
            [
                'key'    => 'text',
                'label'  => 'Texto / Destaque',
                'layout' => 'text',
                'system' => true,
                'order'  => 2,
                'fields' => null,
            ],
            [
                'key'    => 'cta',
                'label'  => 'Call to Action',
                'layout' => 'cta',
                'system' => true,
                'order'  => 3,
                'fields' => null,
            ],
            [
                'key'    => 'badges',
                'label'  => 'Badges de Confiança',
                'layout' => 'list',
                'system' => true,
                'order'  => 4,
                'fields' => [
                    ['key' => 'icon',  'label' => 'Ícone Bootstrap (ex: bi-shield-check)', 'type' => 'text'],
                    ['key' => 'title', 'label' => 'Título',   'type' => 'text'],
                    ['key' => 'text',  'label' => 'Subtexto', 'type' => 'text'],
                ],
            ],
            [
                'key'    => 'cards',
                'label'  => 'Cards / Serviços',
                'layout' => 'cards-grid',
                'system' => true,
                'order'  => 5,
                'fields' => [
                    ['key' => 'icon',        'label' => 'Ícone Bootstrap', 'type' => 'text'],
                    ['key' => 'title',       'label' => 'Título',          'type' => 'text'],
                    ['key' => 'text',        'label' => 'Descrição',       'type' => 'textarea'],
                    ['key' => 'link',        'label' => 'Link (opcional)', 'type' => 'url'],
                    ['key' => 'button_text', 'label' => 'Texto do Botão',  'type' => 'text'],
                ],
            ],
            [
                'key'    => 'steps',
                'label'  => 'Passos / Processo',
                'layout' => 'steps',
                'system' => true,
                'order'  => 6,
                'fields' => [
                    ['key' => 'number', 'label' => 'Número (ex: 01)',  'type' => 'text'],
                    ['key' => 'title',  'label' => 'Título do Passo',  'type' => 'text'],
                    ['key' => 'text',   'label' => 'Descrição',        'type' => 'textarea'],
                ],
            ],
            [
                'key'    => 'costs',
                'label'  => 'Custos / Tabela de Preços',
                'layout' => 'costs',
                'system' => true,
                'order'  => 7,
                'fields' => [
                    ['key' => 'icon',  'label' => 'Ícone Bootstrap', 'type' => 'text'],
                    ['key' => 'title', 'label' => 'Item de Custo',   'type' => 'text'],
                    ['key' => 'text',  'label' => 'Descrição',       'type' => 'textarea'],
                    ['key' => 'badge', 'label' => 'Badge (opcional)', 'type' => 'text'],
                ],
            ],
            [
                'key'    => 'faq',
                'label'  => 'FAQ — Perguntas Frequentes',
                'layout' => 'accordion',
                'system' => true,
                'order'  => 8,
                'fields' => [
                    ['key' => 'question', 'label' => 'Pergunta', 'type' => 'text'],
                    ['key' => 'answer',   'label' => 'Resposta', 'type' => 'textarea'],
                ],
            ],
            [
                'key'    => 'image-gallery',
                'label'  => 'Galeria de Imagens',
                'layout' => 'image-gallery',
                'system' => false,
                'order'  => 9,
                'fields' => [
                    ['key' => 'image',   'label' => 'Imagem (caminho storage/)', 'type' => 'image'],
                    ['key' => 'title',   'label' => 'Título (opcional)',          'type' => 'text'],
                    ['key' => 'caption', 'label' => 'Legenda (opcional)',         'type' => 'text'],
                ],
            ],
        ];

        foreach ($types as $data) {
            CmsBlockType::updateOrCreate(['key' => $data['key']], $data);
        }

        $this->command->info('CMS block types seeded: ' . count($types) . ' tipos.');
    }
}
