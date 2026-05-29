<?php

namespace App\Services;

use App\Models\V3Vehicle;
use App\Models\V3VehicleDocument;
use App\Models\V3VehiclePhoto;
use App\Models\VehicleInspection;
use App\Models\VehicleInspectionCategory;
use App\Models\VehicleInspectionEntry;
use App\Models\VehicleInspectionItem;
use App\Models\VehicleInspectionMedia;
use App\Models\VehicleInspectionTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleInspectionService
{
    public const STATUS_OK = 'ok';
    public const STATUS_ATTENTION = 'atencao';
    public const STATUS_PROBLEM = 'problema';
    public const STATUS_UNVERIFIED = 'nao_verificado';

    public const PRIORITIES = [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
    ];

    public const STATUSES = [
        self::STATUS_OK => 'OK',
        self::STATUS_ATTENTION => 'Atenção',
        self::STATUS_PROBLEM => 'Problema',
        self::STATUS_UNVERIFIED => 'Não verificado',
    ];

    public function ensureDefaultTemplate(): VehicleInspectionTemplate
    {
        // $template = VehicleInspectionTemplate::firstOrCreate(
        //     ['slug' => 'checklist-completa'],
        //     [
        //         'name' => 'Checklist Completa',
        //         'description' => 'Checklist otimizada para análise de veículos usados antes da compra.',
        //         'is_default' => true,
        //     ]
        // );

        $template = VehicleInspectionTemplate::where('is_default', true)->first();

        foreach ($this->definition() as $categoryIndex => $categoryData) {
            $category = VehicleInspectionCategory::updateOrCreate(
                [
                    'vehicle_inspection_template_id' => $template->id,
                    'slug' => $categoryData['slug'],
                ],
                [
                    'name' => $categoryData['name'],
                    'icon' => $categoryData['icon'] ?? null,
                    'sort_order' => $categoryIndex + 1,
                    'applies_to_fuels' => $categoryData['fuels'] ?? null,
                ]
            );

            foreach ($categoryData['items'] as $itemIndex => $itemData) {
                VehicleInspectionItem::updateOrCreate(
                    [
                        'vehicle_inspection_category_id' => $category->id,
                        'slug' => $itemData['slug'],
                    ],
                    [
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                        'sort_order' => $itemIndex + 1,
                        'applies_to_fuels' => $itemData['fuels'] ?? null,
                        'is_critical' => (bool) ($itemData['critical'] ?? false),
                    ]
                );
            }
        }

        return $template->load(['categories.items']);
    }

    public function calculateSummary(VehicleInspection $inspection): array
    {
        $inspection->loadMissing(['entries.item']);

        $entries = $inspection->entries
            ->filter(fn (VehicleInspectionEntry $entry) => $entry->item && $entry->item->appliesToFuel($inspection->fuel));

        $verified = $entries->filter(fn (VehicleInspectionEntry $entry) => $entry->status !== self::STATUS_UNVERIFIED);

        $points = (float) $verified->sum(fn (VehicleInspectionEntry $entry) => $entry->score());
        $maxPoints = (float) $verified->count();

        $okCount = $verified->where('status', self::STATUS_OK)->count();
        $attentionCount = $verified->where('status', self::STATUS_ATTENTION)->count();
        $problemCount = $verified->where('status', self::STATUS_PROBLEM)->count();
        $unverifiedCount = $entries->where('status', self::STATUS_UNVERIFIED)->count();
        $criticalCount = $entries->filter(fn (VehicleInspectionEntry $entry) => $entry->status === self::STATUS_PROBLEM && ($entry->item?->is_critical || $entry->priority === 'alta'))->count();

        $percentage = $maxPoints > 0 ? round(($points / $maxPoints) * 100, 1) : 0.0;

        $result = match (true) {
            $percentage >= 90 => 'Excelente',
            $percentage >= 75 => 'Bom',
            $percentage >= 60 => 'Atenção',
            default => 'Crítico',
        };

        $problemLabels = $entries
            ->filter(fn (VehicleInspectionEntry $entry) => $entry->status === self::STATUS_PROBLEM)
            ->map(fn (VehicleInspectionEntry $entry) => $entry->item?->name)
            ->filter()
            ->values();

        $recommendations = [];
        if ($criticalCount > 0) {
            $recommendations[] = 'Priorizar os itens críticos antes da comercialização.';
        }
        if ($problemCount > 0) {
            $recommendations[] = 'Validar as anomalias assinaladas e estimar custos de correção.';
        }
        if ($attentionCount > 0) {
            $recommendations[] = 'Rever os itens com atenção para evitar surpresas após a compra.';
        }
        if ($unverifiedCount > 0) {
            $recommendations[] = 'Completar os campos ainda não verificados para fechar a inspeção.';
        }

        return [
            'total_points' => $points,
            'max_points' => $maxPoints,
            'verified_items' => $verified->count(),
            'ok_items' => $okCount,
            'attention_items' => $attentionCount,
            'problem_items' => $problemCount,
            'unverified_items' => $unverifiedCount,
            'critical_issues' => $criticalCount,
            'inspection_result' => $result,
            'recommendations' => implode(' ', $recommendations),
            'problem_labels' => $problemLabels,
            'percentage' => $percentage,
        ];
    }

    public function recalculate(VehicleInspection $inspection): VehicleInspection
    {
        $inspection->fill($this->calculateSummary($inspection));
        $inspection->save();

        return $inspection->refresh();
    }

    public function createV3Vehicle(VehicleInspection $inspection): V3Vehicle
    {
        return DB::transaction(function () use ($inspection) {
            $inspection->loadMissing(['entries.media', 'entries.item']);

            $vehicle = V3Vehicle::create([
                'reference' => V3Vehicle::generateReference(),
                'brand' => $inspection->brand,
                'model' => $inspection->model,
                'sub_model' => $inspection->sub_model,
                'version' => $inspection->version,
                'year' => $inspection->year,
                'kilometers' => $inspection->kilometers,
                'vin' => $inspection->vin,
                'registration' => $inspection->registration,
                'color' => $inspection->color,
                'fuel' => $inspection->fuel,
                'power' => $inspection->power,
                'status' => 'em_stock',
                'show_online' => false,
                'notes' => trim(($inspection->notes ?? '') . "\n\nGerado automaticamente a partir da inspeção #{$inspection->id}"),
                'generated_from_inspection_id' => $inspection->id,
            ]);

            $photoOrder = 0;

            foreach ($inspection->entries as $entry) {
                foreach ($entry->media->sortBy('sort_order') as $media) {
                    if ($media->type === 'image') {
                        $newPath = "v3-vehicles/{$vehicle->id}/photos/" . basename($media->path);
                        Storage::disk('public')->put($newPath, Storage::disk('public')->get($media->path));

                        V3VehiclePhoto::create([
                            'v3_vehicle_id' => $vehicle->id,
                            'path' => $newPath,
                            'order_position' => ++$photoOrder,
                            'is_cover' => $photoOrder === 1,
                        ]);
                    } else {
                        $newPath = "v3-vehicles/{$vehicle->id}/documents/" . basename($media->path);
                        Storage::put($newPath, Storage::disk('public')->get($media->path));

                        V3VehicleDocument::create([
                            'v3_vehicle_id' => $vehicle->id,
                            'tipo' => 'inspection-video',
                            'titulo' => 'Vídeo de inspeção',
                            'nome_original' => $media->original_name,
                            'caminho' => $newPath,
                        ]);
                    }
                }
            }

            $inspection->update([
                'v3_vehicle_id' => $vehicle->id,
                'status' => 'converted',
                'converted_at' => now(),
                'completed_at' => now(),
            ]);

            return $vehicle->fresh(['photos', 'documents']);
        });
    }

    private function definition(): array
    {
        return [
            [
                'name' => 'Exterior',
                'slug' => 'exterior',
                'icon' => 'bi-sunrise',
                'items' => [
                    ['name' => 'Pintura', 'slug' => 'pintura'],
                    ['name' => 'Diferenças de cor', 'slug' => 'diferencas-cor'],
                    ['name' => 'Riscos', 'slug' => 'riscos'],
                    ['name' => 'Mossas', 'slug' => 'mossas'],
                    ['name' => 'Ferrugem', 'slug' => 'ferrugem', 'critical' => true],
                    ['name' => 'Alinhamento de painéis', 'slug' => 'alinhamento-paineis'],
                    ['name' => 'Estado dos faróis', 'slug' => 'farois'],
                    ['name' => 'Estado dos vidros', 'slug' => 'vidros'],
                    ['name' => 'Estado das jantes', 'slug' => 'jantes'],
                    ['name' => 'Estado dos pneus', 'slug' => 'pneus', 'critical' => true],
                    ['name' => 'Data dos pneus', 'slug' => 'data-pneus'],
                    ['name' => 'Profundidade do piso', 'slug' => 'profundidade-piso', 'critical' => true],
                    ['name' => 'Estado das escovas', 'slug' => 'escovas'],
                    ['name' => 'Sensores exteriores', 'slug' => 'sensores-exteriores'],
                    ['name' => 'Câmara traseira', 'slug' => 'camara-traseira'],
                    ['name' => 'Teto panorâmico', 'slug' => 'teto-panoramico'],
                    ['name' => 'Abertura/fecho de portas', 'slug' => 'portas'],
                    ['name' => 'Porta da mala', 'slug' => 'porta-mala'],
                ],
            ],
            [
                'name' => 'Interior',
                'slug' => 'interior',
                'icon' => 'bi-car-front',
                'items' => [
                    ['name' => 'Estado dos bancos', 'slug' => 'bancos'],
                    ['name' => 'Desgaste volante', 'slug' => 'desgaste-volante'],
                    ['name' => 'Desgaste manete', 'slug' => 'desgaste-manete'],
                    ['name' => 'Estado do teto', 'slug' => 'teto-interior'],
                    ['name' => 'Estado das portas', 'slug' => 'portas-interior'],
                    ['name' => 'Tapetes', 'slug' => 'tapetes'],
                    ['name' => 'Sistema multimédia', 'slug' => 'multimedia'],
                    ['name' => 'Bluetooth', 'slug' => 'bluetooth'],
                    ['name' => 'GPS', 'slug' => 'gps'],
                    ['name' => 'Ar condicionado', 'slug' => 'ar-condicionado', 'critical' => true],
                    ['name' => 'Aquecimento', 'slug' => 'aquecimento'],
                    ['name' => 'Vidros elétricos', 'slug' => 'vidros-eletricos'],
                    ['name' => 'Fecho central', 'slug' => 'fecho-central'],
                    ['name' => 'Painel de instrumentos', 'slug' => 'painel-instrumentos', 'critical' => true],
                    ['name' => 'Luzes de erro', 'slug' => 'luzes-erro', 'critical' => true],
                    ['name' => 'Sensores interiores', 'slug' => 'sensores-interiores'],
                    ['name' => 'Bancos elétricos', 'slug' => 'bancos-eletricos'],
                    ['name' => 'Bancos aquecidos', 'slug' => 'bancos-aquecidos'],
                    ['name' => 'Câmara 360', 'slug' => 'camara-360'],
                    ['name' => 'Cheiros no interior', 'slug' => 'cheiros-interior', 'critical' => true],
                ],
            ],
            [
                'name' => 'Motor e Mecânica',
                'slug' => 'motor-mecanica',
                'icon' => 'bi-gear-wide',
                'items' => [
                    ['name' => 'Arranque a frio', 'slug' => 'arranque-frio', 'critical' => true],
                    ['name' => 'Barulhos metálicos', 'slug' => 'barulhos-metalicos', 'critical' => true],
                    ['name' => 'Fugas de óleo', 'slug' => 'fugas-oleo', 'critical' => true],
                    ['name' => 'Fugas de líquido de refrigeração', 'slug' => 'fugas-refrigeracao', 'critical' => true],
                    ['name' => 'Estado da bateria', 'slug' => 'estado-bateria'],
                    ['name' => 'Vibrações', 'slug' => 'vibracoes'],
                    ['name' => 'Turbo', 'slug' => 'turbo', 'critical' => true],
                    ['name' => 'EGR', 'slug' => 'egr', 'fuels' => ['Diesel']],
                    ['name' => 'FAP/DPF', 'slug' => 'fap-dpf', 'fuels' => ['Diesel']],
                    ['name' => 'Corrente distribuição', 'slug' => 'corrente-distribuicao'],
                    ['name' => 'Correia distribuição', 'slug' => 'correia-distribuicao', 'critical' => true],
                    ['name' => 'Injetores', 'slug' => 'injetores'],
                    ['name' => 'Nível óleo', 'slug' => 'nivel-oleo'],
                    ['name' => 'Estado líquido refrigeração', 'slug' => 'nivel-refrigeracao'],
                    ['name' => 'Fumo escape', 'slug' => 'fumo-escape', 'critical' => true],
                    ['name' => 'Suportes motor', 'slug' => 'suportes-motor'],
                ],
            ],
            [
                'name' => 'Caixa e Transmissão',
                'slug' => 'caixa-transmissao',
                'icon' => 'bi-diagram-3',
                'items' => [
                    ['name' => 'Funcionamento caixa', 'slug' => 'funcionamento-caixa', 'critical' => true],
                    ['name' => 'Passagem de mudanças', 'slug' => 'passagem-mudancas'],
                    ['name' => 'Embraiagem', 'slug' => 'embraiagem', 'critical' => true],
                    ['name' => 'Vibrações', 'slug' => 'vibracoes-transmissao'],
                    ['name' => 'Ruídos transmissão', 'slug' => 'ruidos-transmissao'],
                    ['name' => 'Diferencial', 'slug' => 'diferencial'],
                    ['name' => 'Sistema AWD/4x4', 'slug' => 'awd-4x4'],
                ],
            ],
            [
                'name' => 'Suspensão e Travagem',
                'slug' => 'suspensao-travagem',
                'icon' => 'bi-patch-check',
                'items' => [
                    ['name' => 'Amortecedores', 'slug' => 'amortecedores', 'critical' => true],
                    ['name' => 'Braços suspensão', 'slug' => 'bracos-suspensao'],
                    ['name' => 'Sinoblocos', 'slug' => 'sinoblocos'],
                    ['name' => 'Rolamentos', 'slug' => 'rolamentos'],
                    ['name' => 'Direção', 'slug' => 'direcao', 'critical' => true],
                    ['name' => 'Discos', 'slug' => 'discos', 'critical' => true],
                    ['name' => 'Pastilhas', 'slug' => 'pastilhas', 'critical' => true],
                    ['name' => 'Travagem', 'slug' => 'travagem', 'critical' => true],
                    ['name' => 'ABS', 'slug' => 'abs', 'critical' => true],
                    ['name' => 'Vibrações a travar', 'slug' => 'vibracoes-travar', 'critical' => true],
                ],
            ],
            [
                'name' => 'Diesel',
                'slug' => 'diesel',
                'icon' => 'bi-fuel-pump-diesel',
                'fuels' => ['Diesel'],
                'items' => [
                    ['name' => 'Estado FAP', 'slug' => 'diesel-fap', 'critical' => true, 'fuels' => ['Diesel']],
                    ['name' => 'Estado EGR', 'slug' => 'diesel-egr', 'critical' => true, 'fuels' => ['Diesel']],
                    ['name' => 'AdBlue', 'slug' => 'diesel-adblue', 'fuels' => ['Diesel']],
                    ['name' => 'Regenerações', 'slug' => 'diesel-regeneracoes', 'fuels' => ['Diesel']],
                    ['name' => 'Fumo excessivo', 'slug' => 'diesel-fumo-excessivo', 'critical' => true, 'fuels' => ['Diesel']],
                ],
            ],
            [
                'name' => 'Gasolina',
                'slug' => 'gasolina',
                'icon' => 'bi-fuel-pump',
                'fuels' => ['Gasolina'],
                'items' => [
                    ['name' => 'Consumo óleo', 'slug' => 'gasolina-consumo-oleo', 'critical' => true, 'fuels' => ['Gasolina']],
                    ['name' => 'Bobines', 'slug' => 'gasolina-bobines', 'fuels' => ['Gasolina']],
                    ['name' => 'Velas', 'slug' => 'gasolina-velas', 'fuels' => ['Gasolina']],
                    ['name' => 'Barulho motor', 'slug' => 'gasolina-barulho-motor', 'critical' => true, 'fuels' => ['Gasolina']],
                ],
            ],
            [
                'name' => 'Híbrido',
                'slug' => 'hibrido',
                'icon' => 'bi-ev-station',
                'fuels' => ['Híbrido'],
                'items' => [
                    ['name' => 'Estado bateria híbrida', 'slug' => 'hibrido-bateria', 'critical' => true, 'fuels' => ['Híbrido']],
                    ['name' => 'Funcionamento motor elétrico', 'slug' => 'hibrido-motor-eletrico', 'fuels' => ['Híbrido']],
                    ['name' => 'Transição elétrico/combustão', 'slug' => 'hibrido-transicao', 'fuels' => ['Híbrido']],
                    ['name' => 'Carregamento regenerativo', 'slug' => 'hibrido-regenerativo', 'fuels' => ['Híbrido']],
                ],
            ],
            [
                'name' => 'Elétrico',
                'slug' => 'eletrico',
                'icon' => 'bi-lightning-charge',
                'fuels' => ['Elétrico'],
                'items' => [
                    ['name' => 'Saúde bateria (%)', 'slug' => 'eletrico-bateria-saude', 'critical' => true, 'fuels' => ['Elétrico']],
                    ['name' => 'Autonomia real', 'slug' => 'eletrico-autonomia', 'critical' => true, 'fuels' => ['Elétrico']],
                    ['name' => 'Tempo carregamento', 'slug' => 'eletrico-tempo-carregamento', 'fuels' => ['Elétrico']],
                    ['name' => 'Cabos carregamento', 'slug' => 'eletrico-cabos', 'fuels' => ['Elétrico']],
                    ['name' => 'AC/DC charging', 'slug' => 'eletrico-ac-dc', 'fuels' => ['Elétrico']],
                    ['name' => 'Histórico de carregamentos', 'slug' => 'eletrico-historico', 'fuels' => ['Elétrico']],
                    ['name' => 'Degradação bateria', 'slug' => 'eletrico-degradacao', 'critical' => true, 'fuels' => ['Elétrico']],
                ],
            ],
        ];
    }
}
