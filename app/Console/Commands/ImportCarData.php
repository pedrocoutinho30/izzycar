<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Brand;
use App\Models\ModelCar;
use App\Models\SubmodelCar;

class ImportCarData extends Command
{
    protected $signature = 'import:car-data {file}';
    protected $description = 'Import all car data (brands, models, years) from OpenDataSoft API';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("Ficheiro não encontrado: $filePath");
            return 1;
        }

        $lines = array_filter(array_map('trim', file($filePath)));

        $i = 0;
        while ($i < count($lines)) {
            if (str_starts_with($lines[$i], "Marca:")) {
                $marca = trim(str_replace("Marca:", "", $lines[$i]));
                $modelos = [];

                if (isset($lines[$i + 1]) && str_starts_with($lines[$i + 1], "Modelos:")) {
                    $modelos = array_map('trim', explode(",", str_replace("Modelos:", "", $lines[$i + 1])));
                }

                // Cria ou pega a marca
                $brand = Brand::firstOrCreate(['name' => $marca]);

                // Cria os modelos
                foreach ($modelos as $modelo) {
                    if ($modelo) {
                        ModelCar::firstOrCreate([
                            'brand_id' => $brand->id,
                            'name' => $modelo
                        ]);
                    }
                }
            }
            $i++;
        }

        $this->info("✅ Marcas e modelos importados com sucesso!");
        return 0;
    }
    // public function handle()
    // {
    //     $response = Http::get('https://carapi.app/api/makes/v2?sort=Makes.name&direction=asc');

    //     if (!$response->successful()) {
    //         $this->error("❌ Erro ao buscar dados .");
    //         return;
    //     }

    //     $data = $response->json();
    //     $results = $data['data'] ?? [];
    //     for ($i = 0; $i < count($results); $i++) {
    //         $item = $results[$i];
    //         $make = $item['name'] ?? null;

    //         if (!$make) {
    //             continue;
    //         }

    //         $brand = Brand::firstOrCreate(['name' => $make]);
    //         $this->info("🔄 Importando dados da marca: " . $brand->name);
    //         $responseModel = Http::get('https://carapi.app/api/models/v2?sort=OemMakeModels.name&direction=asc&make_id=' . $item['id']);

    //         if (!$responseModel->successful()) {
    //             $this->error("❌ Erro ao buscar dados .");
    //             return;
    //         }

    //         $dataModel = $responseModel->json();
    //         $resultsModel = $dataModel['data'] ?? [];

    //         foreach ($resultsModel as $itemModel) {
    //             $modelName = $itemModel['name'] ?? null;

    //             if (!$modelName) {
    //                 continue;
    //             }

    //             $modelCar = ModelCar::firstOrCreate([
    //                 'name' => $modelName,
    //                 'brand_id' => $brand->id,
    //             ]);

    //             $this->info("🔄 Importando dados de modelos: " . $modelName);

    //         }
    //     }
    // }

    // public function handle()
    // {
    //     $startYear = 2025;
    //     $endYear = 1990; // último ano que desejas importar

    //     for ($year = $startYear; $year >= $endYear; $year--) {
    //         $this->info("🔄 Importando dados do ano $year...");

    //         $offset = 0;
    //         $limit = 100;

    //         do {
    //             $response = Http::get('https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/all-vehicles-model/records', [
    //                 'select'   => 'make,model,year',
    //                 'where'    => "year > " . ($year - 1) . " AND year < " . ($year + 1),
    //                 'order_by' => 'make',
    //                 'limit'    => $limit,
    //                 'offset'   => $offset,
    //             ]);

    //             if (!$response->successful()) {
    //                 $this->error("❌ Erro ao buscar dados para o ano $year.");
    //                 break;
    //             }

    //             $data = $response->json();
    //             $results = $data['results'] ?? [];

    //             foreach ($results as $item) {
    //                 $make = trim($item['make']);
    //                 $model = trim($item['model']);
    //                 $carYear = trim($item['year']);

    //                 if (!$make || !$model || !$carYear) {
    //                     continue;
    //                 }

    //                 $brand = Brand::firstOrCreate(['name' => $make]);

    //                 $modelCar = ModelCar::firstOrCreate([
    //                     'name' => $model,
    //                     'brand_id' => $brand->id,
    //                 ]);

    //                 ModelYear::firstOrCreate([
    //                     'model_car_id' => $modelCar->id,
    //                     'year' => $carYear,
    //                 ]);
    //             }

    //             $offset += $limit;
    //             $this->info("   → Importados: " . count($results) . " (offset $offset)");
    //         } while (count($results) === $limit);
    //     }
    // }
}
