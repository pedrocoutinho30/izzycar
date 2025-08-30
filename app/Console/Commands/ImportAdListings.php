<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdSearch;
use App\Models\AdListing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportAdListings extends Command
{
    protected $signature = 'ads:import';
    protected $description = 'Import all ad listings from CSV files in the anuncios folder';

    public function handle()
    {
        $folderPath = base_path('AdSearch');

        if (!is_dir($folderPath)) {
            $this->error("❌ Pasta 'anuncios' não encontrada na raiz do projeto.");
            return 1;
        }

        $files = glob($folderPath . '/*.csv');

        if (empty($files)) {
            $this->info("ℹ️ Nenhum ficheiro CSV encontrado em 'anuncios/'");
            return 0;
        }

        foreach ($files as $file) {
            $this->importFile($file);
        }

        return 0;
    }

    private function importFile(string $filePath): void
    {
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $parts = explode('_', $fileName);

        if (count($parts) < 6) {
            $this->error("❌ Nome de ficheiro inválido: $fileName");
            return;
        }

        [$brand, $model, $submodel, $year_start, $year_end, $fuel, $description] = $parts;

        $searchParams = [
            'brand' => ucfirst($brand),
            'model' => $model,
            'submodel' => $submodel,
            'year_start' => $year_start,
            'year_end' => $year_end,
            'fuel' => $fuel,
            'description' => $description, // sempre true
        ];

        $search = AdSearch::firstOrCreate($searchParams);

        $csv = array_map('str_getcsv', file($filePath));
        $headers = array_map('trim', $csv[0]);
        unset($csv[0]);

        $csvExternalIds = [];
        $imported = 0;
        $updated = 0;

        DB::beginTransaction();

        try {
            foreach ($csv as $row) {
                $data = array_combine($headers, $row);
                $externalId = trim($data['ID']);
                $csvExternalIds[] = $externalId;

                [$listing, $created] = AdListing::updateOrCreate(
                    [
                        'ad_search_id' => $search->id,
                        'external_id' => $externalId,
                    ],
                    [
                        'title' => $data['Título'],
                        'price' => floatval(str_replace([' ', '€'], '', $data['Preço'])),
                        'year' => (int) $data['Ano'],
                        'mileage' => (int) str_replace([' ', 'km'], '', $data['Quilometragem']),
                        'gearbox' => $data['Transmissão'] ?? null,
                        'location' => null, //$data['Localidade'],
                        'published_time' => null, //$data['Tempo Publicação'],
                        'url' => $data['URL'],
                        'active' => true, // marca como ativo

                        // Novos campos de características
                        'teto_de_abrir' =>$this->toBoolInt($data['Teto de abrir'] ?? null),
                        'teto_panoramico' =>$this->toBoolInt($data['Teto panorâmico'] ?? null),
                        'camara_marcha_atras' =>$this->toBoolInt($data['Câmara de marcha-atrás'] ?? null),
                        'camara_360' =>$this->toBoolInt($data['Câmara 360'] ?? null),
                        'sensor_estacionamento_dianteiro' =>$this->toBoolInt($data['Sensor de estacionamento dianteiro'] ?? null),
                        'sensor_estacionamento_traseiro' =>$this->toBoolInt($data['Sensor de estacionamento traseiro'] ?? null),
                        'assistente_estacionamento' =>$this->toBoolInt($data['Assistente de estacionamento'] ?? null),
                        'sistema_estacionamento_autonomo' =>$this->toBoolInt($data['Sistema de estacionamento autónomo'] ?? null),
                        'apple_carplay' =>$this->toBoolInt($data['Apple CarPlay'] ?? null),
                        'android_auto' =>$this->toBoolInt($data['Android Auto'] ?? null),
                        'bluetooth' =>$this->toBoolInt($data['Bluetooth'] ?? null),
                        'porta_usb' =>$this->toBoolInt($data['Porta USB'] ?? null),
                        'carregador_wireless' =>$this->toBoolInt($data['Carregador de smartphone wireless'] ?? null),
                        'sistema_navegacao' =>$this->toBoolInt($data['Sistema de navegação'] ?? null),
                        'estofos_alcantara' =>$this->toBoolInt($data['Estofos em alcântara'] ?? null),
                        'estofos_pele' =>$this->toBoolInt($data['Estofos em pele'] ?? null),
                        'estofos_tecido' =>$this->toBoolInt($data['Estofos em tecido'] ?? null),
                        'banco_condutor_aquecido' =>$this->toBoolInt($data['Banco do condutor aquecido'] ?? null),
                        'banco_passageiro_aquecido' =>$this->toBoolInt($data['Banco do passageiro aquecido'] ?? null),
                        'banco_condutor_regulacao_eletrica' =>$this->toBoolInt($data['Banco do condutor com regulação eléctrica'] ?? null),
                        'bancos_memoria' =>$this->toBoolInt($data['Bancos com memória'] ?? null),
                        'fecho_central_sem_chave' =>$this->toBoolInt($data['Fecho central sem chave'] ?? null),
                        'arranque_sem_chave' =>$this->toBoolInt($data['Arranque sem chave'] ?? null),
                        'cruise_control' =>$this->toBoolInt($data['Cruise Control'] ?? null),
                        'cruise_control_adaptativo' =>$this->toBoolInt($data['Cruise Control adaptativo'] ?? null),
                        'cruise_control_predictivo' =>$this->toBoolInt($data['Cruise Control Predictivo'] ?? null),
                        'suspensao_desportiva' =>$this->toBoolInt($data['Suspensão desportiva'] ?? null),
                        'retrovisores_retrateis' =>$this->toBoolInt($data['Retrovisores exteriores eletricamente retrateis'] ?? null),
                        'assistente_angulo_morto' =>$this->toBoolInt($data['Assistente de ângulo morto'] ?? null),
                        'assistente_mudanca_faixa' =>$this->toBoolInt($data['Assistente de mudança de faixa'] ?? null),
                        'controlo_proximidade' =>$this->toBoolInt($data['Controlo de proximidade'] ?? null),
                        'conducao_autonoma_basica' =>$this->toBoolInt($data['Condução autónoma básica'] ?? null),
                    ]
                );

                $created ? $imported++ : $updated++;
            }

            // Marcar os que não estão no CSV como inativos
            $inactiveCount = AdListing::where('ad_search_id', $search->id)
                ->whereNotIn('external_id', $csvExternalIds)
                ->update(['active' => false]);

            DB::commit();

            $this->info("✅ Ficheiro importado: $fileName.csv");
            $this->info(" - Search ID: {$search->id}");
            $this->info(" - Novos anúncios: $imported");
            $this->info(" - Atualizados: $updated");
            $this->info(" - Marcados como inativos: $inactiveCount");

            // Mover o ficheiro para AdSearch/old
            $oldDir = dirname($filePath) . '/old';
            if (!is_dir($oldDir)) {
                mkdir($oldDir, 0777, true);
            }
            $newPath = $oldDir . '/' . basename($filePath);
            rename($filePath, $newPath);

            $this->info(" - Ficheiro movido para: $newPath");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Erro ao importar $fileName.csv: " . $e->getMessage());
        }
    }

    private function toBoolInt($value): ?int
    {
        if (is_null($value)) return null;
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }
}
