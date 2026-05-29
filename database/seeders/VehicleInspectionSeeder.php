<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehicleInspectionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | TEMPLATE
            |--------------------------------------------------------------------------
            */

            $templateId = DB::table('vehicle_inspection_templates')->insertGetId([
                'name' => 'Checklist Completa',
                'slug' => 'checklist-completa',
                'description' => 'Checklist otimizada para análise de veículos usados antes da compra.',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | CATEGORIES + ITEMS
            |--------------------------------------------------------------------------
            */

            $categories = [

                [
                    'name' => 'Exterior',
                    'slug' => 'exterior',
                    'icon' => 'car',
                    'sort_order' => 1,
                    'applies_to_fuels' => null,
                    'items' => [

                        [
                            'name' => 'Estado geral pintura',
                            'description' => 'Verificar riscos, verniz queimado, desgaste e qualidade da pintura.',
                            'is_critical' => false,
                        ],

                        [
                            'name' => 'Diferenças de cor e alinhamento',
                            'description' => 'Verificar sinais de acidente ou reparações.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Ferrugem',
                            'description' => 'Verificar corrosão estrutural e superficial.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Faróis e vidros',
                            'description' => 'Verificar estado geral e possíveis danos.',
                            'is_critical' => false,
                        ],

                        [
                            'name' => 'Jantes e pneus',
                            'description' => 'Verificar desgaste, danos e alinhamento.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'DOT e desgaste pneus',
                            'description' => 'Verificar idade e desgaste irregular.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Sensores e câmaras exteriores',
                            'description' => 'Validar funcionamento dos sensores e câmaras.',
                            'is_critical' => false,
                        ],

                        [
                            'name' => 'Portas, mala e teto panorâmico',
                            'description' => 'Verificar funcionamento e infiltrações.',
                            'is_critical' => false,
                        ],
                    ],
                ],

                [
                    'name' => 'Interior e Eletrónica',
                    'slug' => 'interior-eletronica',
                    'icon' => 'armchair',
                    'sort_order' => 2,
                    'applies_to_fuels' => null,
                    'items' => [

                        [
                            'name' => 'Estado geral interior',
                            'description' => 'Verificar bancos, teto, portas e tapetes.',
                            'is_critical' => false,
                        ],

                        [
                            'name' => 'Desgaste vs quilometragem',
                            'description' => 'Comparar desgaste com os quilómetros apresentados.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Painel e luzes de erro',
                            'description' => 'Verificar erros ativos no painel.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Sistema multimédia e conectividade',
                            'description' => 'Validar multimédia, GPS e Bluetooth.',
                            'is_critical' => false,
                        ],

                        [
                            'name' => 'Climatização',
                            'description' => 'Verificar ar condicionado e aquecimento.',
                            'is_critical' => false,
                        ],

                        [
                            'name' => 'Comandos elétricos',
                            'description' => 'Verificar vidros, fechos e comandos.',
                            'is_critical' => false,
                        ],

                        [
                            'name' => 'Sensores e câmaras interiores',
                            'description' => 'Validar sensores e sistemas de apoio.',
                            'is_critical' => false,
                        ],

                        [
                            'name' => 'Cheiros e infiltrações',
                            'description' => 'Verificar odores estranhos e humidade.',
                            'is_critical' => true,
                        ],
                    ],
                ],

                [
                    'name' => 'Motor e Mecânica',
                    'slug' => 'motor-mecanica',
                    'icon' => 'wrench',
                    'sort_order' => 3,
                    'applies_to_fuels' => null,
                    'items' => [

                        [
                            'name' => 'Arranque a frio',
                            'description' => 'Verificar comportamento no arranque.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Ruídos e vibrações',
                            'description' => 'Verificar ruídos metálicos e vibrações.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Fugas e níveis',
                            'description' => 'Verificar óleo e líquido de refrigeração.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Escape e fumos',
                            'description' => 'Verificar fumos e funcionamento do escape.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Turbo e admissão',
                            'description' => 'Verificar pressão e funcionamento do turbo.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Estado distribuição',
                            'description' => 'Verificar corrente/correia distribuição.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Sistema de injeção',
                            'description' => 'Verificar funcionamento dos injetores.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Diagnóstico eletrónico (OBD)',
                            'description' => 'Verificar erros eletrónicos.',
                            'is_critical' => true,
                        ],
                    ],
                ],

                [
                    'name' => 'Caixa e Transmissão',
                    'slug' => 'caixa-transmissao',
                    'icon' => 'cog',
                    'sort_order' => 4,
                    'applies_to_fuels' => null,
                    'items' => [

                        [
                            'name' => 'Funcionamento caixa',
                            'description' => 'Verificar suavidade da caixa.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Passagem de mudanças',
                            'description' => 'Validar passagem de mudanças.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Embraiagem e volante motor',
                            'description' => 'Verificar desgaste e funcionamento.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Vibrações e ruídos transmissão',
                            'description' => 'Verificar anomalias na transmissão.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'AWD/4x4 e diferencial',
                            'description' => 'Verificar sistemas de tração.',
                            'is_critical' => false,
                        ],
                    ],
                ],

                [
                    'name' => 'Suspensão e Travagem',
                    'slug' => 'suspensao-travagem',
                    'icon' => 'shield',
                    'sort_order' => 5,
                    'applies_to_fuels' => null,
                    'items' => [

                        [
                            'name' => 'Suspensão e direção',
                            'description' => 'Verificar amortecedores, braços e direção.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Rolamentos e ruídos',
                            'description' => 'Verificar rolamentos e ruídos anormais.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Discos e pastilhas',
                            'description' => 'Verificar desgaste dos travões.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Travagem e ABS',
                            'description' => 'Verificar eficiência de travagem.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Vibrações em andamento e travagem',
                            'description' => 'Verificar vibrações anormais.',
                            'is_critical' => true,
                        ],
                    ],
                ],

                [
                    'name' => 'Diesel',
                    'slug' => 'diesel',
                    'icon' => 'fuel',
                    'sort_order' => 6,
                    'applies_to_fuels' => json_encode(['diesel']),
                    'items' => [

                        [
                            'name' => 'Estado FAP/EGR',
                            'description' => 'Verificar funcionamento dos sistemas anti-poluição.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'AdBlue e regenerações',
                            'description' => 'Verificar sistema AdBlue e regenerações.',
                            'is_critical' => false,
                        ],

                        [
                            'name' => 'Fumo excessivo',
                            'description' => 'Verificar emissões anormais.',
                            'is_critical' => true,
                        ],
                    ],
                ],

                [
                    'name' => 'Gasolina',
                    'slug' => 'gasolina',
                    'icon' => 'fuel',
                    'sort_order' => 7,
                    'applies_to_fuels' => json_encode(['gasoline']),
                    'items' => [

                        [
                            'name' => 'Consumo de óleo',
                            'description' => 'Verificar consumo excessivo.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Sistema ignição',
                            'description' => 'Verificar bobines e velas.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Ruído funcionamento motor',
                            'description' => 'Verificar funcionamento do motor.',
                            'is_critical' => true,
                        ],
                    ],
                ],

                [
                    'name' => 'Híbrido',
                    'slug' => 'hibrido',
                    'icon' => 'battery-charging',
                    'sort_order' => 8,
                    'applies_to_fuels' => json_encode(['hybrid']),
                    'items' => [

                        [
                            'name' => 'Estado bateria híbrida',
                            'description' => 'Verificar degradação da bateria.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Transição elétrico/combustão',
                            'description' => 'Verificar suavidade da transição.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Regeneração e motor elétrico',
                            'description' => 'Verificar regeneração e apoio elétrico.',
                            'is_critical' => true,
                        ],
                    ],
                ],

                [
                    'name' => 'Elétrico',
                    'slug' => 'eletrico',
                    'icon' => 'zap',
                    'sort_order' => 9,
                    'applies_to_fuels' => json_encode(['electric']),
                    'items' => [

                        [
                            'name' => 'Saúde e degradação bateria',
                            'description' => 'Verificar SOH e degradação.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Autonomia real',
                            'description' => 'Comparar autonomia real vs anunciada.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Carregamento AC/DC',
                            'description' => 'Verificar velocidade e funcionamento.',
                            'is_critical' => true,
                        ],

                        [
                            'name' => 'Cabos e porta carregamento',
                            'description' => 'Verificar desgaste e funcionamento.',
                            'is_critical' => false,
                        ],

                        [
                            'name' => 'Histórico de carregamentos',
                            'description' => 'Analisar utilização da bateria.',
                            'is_critical' => false,
                        ],
                    ],
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | INSERT CATEGORIES + ITEMS
            |--------------------------------------------------------------------------
            */

            foreach ($categories as $category) {

                $categoryId = DB::table('vehicle_inspection_categories')->insertGetId([
                    'vehicle_inspection_template_id' => $templateId,
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'icon' => $category['icon'],
                    'sort_order' => $category['sort_order'],
                    'applies_to_fuels' => $category['applies_to_fuels'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($category['items'] as $index => $item) {

                    DB::table('vehicle_inspection_items')->insert([
                        'vehicle_inspection_category_id' => $categoryId,
                        'name' => $item['name'],
                        'slug' => Str::slug($item['name']),
                        'description' => $item['description'],
                        'sort_order' => $index + 1,
                        'applies_to_fuels' => $category['applies_to_fuels'],
                        'is_critical' => $item['is_critical'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}