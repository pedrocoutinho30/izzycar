<?php

namespace App\Console\Commands;

use App\Models\RadarListing;
use App\Services\RadarTrimPriceAiService;
use Illuminate\Console\Command;

/**
 * Classifica, via IA, o "multiplicador de preço de trim" dos anúncios do
 * Radar de Preços ainda não classificados (ver RadarTrimPriceAiService) -
 * pensado para correr periodicamente por cron, pouco depois de
 * radar:refresh-active, para que os anúncios novos de cada scrape fiquem
 * classificados antes de o admin voltar a abrir a pesquisa.
 *
 * Só classifica uma vez por anúncio (grava trim_classified_at) - nunca
 * reclassifica um anúncio já processado, mesmo que o resultado tenha sido o
 * neutro 1.0 (versão sem informação suficiente).
 */
class ClassifyRadarListingTrims extends Command
{
    protected $signature = 'radar:classify-trims {--limit=300 : Máximo de anúncios a classificar nesta execução}';
    protected $description = 'Classifica por IA o multiplicador de preço de trim dos anúncios do radar ainda não classificados';

    private const BATCH_SIZE = 25;

    public function handle(RadarTrimPriceAiService $ai): int
    {
        $limit = (int) $this->option('limit');
        $processed = 0;

        $searchIds = RadarListing::whereNull('trim_classified_at')
            ->whereNull('removed_at')
            ->distinct()
            ->pluck('radar_search_id');

        foreach ($searchIds as $searchId) {
            if ($processed >= $limit) {
                break;
            }

            $search = \App\Models\RadarSearch::find($searchId);
            if (!$search || !$search->make || !$search->model) {
                continue;
            }

            RadarListing::where('radar_search_id', $searchId)
                ->whereNull('trim_classified_at')
                ->whereNull('removed_at')
                ->orderBy('id')
                ->chunk(self::BATCH_SIZE, function ($batch) use ($ai, $search, &$processed, $limit) {
                    if ($processed >= $limit) {
                        return false;
                    }

                    $result = $ai->classifyBatch($search->make, $search->model, $batch);

                    if (!$result['success']) {
                        $this->warn("[{$search->name}] falhou a classificar lote: {$result['error']}");

                        return; // tenta este lote de novo na próxima execução
                    }

                    foreach ($batch as $listing) {
                        $multiplier = $result['results'][$listing->id] ?? 1.0;
                        $listing->update([
                            'trim_price_multiplier' => $multiplier,
                            'trim_classified_at' => now(),
                        ]);
                    }

                    $processed += $batch->count();
                    $this->info("[{$search->name}] classificados {$batch->count()} anúncios (total: {$processed}).");
                });
        }

        $this->info("Concluído. {$processed} anúncios classificados.");

        return Command::SUCCESS;
    }
}
