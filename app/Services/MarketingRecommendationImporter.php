<?php

namespace App\Services;

use App\Enums\MarketingFormat;
use App\Enums\MarketingPostStatus;
use App\Enums\MarketingPostType;
use App\Marketing\RecommendationPostData;
use App\Models\MarketingPost;
use App\Models\RecommendationPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Copia os posts do antigo Criador de Posts para o Marketing Studio (rascunho).
 * Idempotente e não destrutivo: não altera nem apaga registos ou ficheiros antigos.
 */
class MarketingRecommendationImporter
{
    /** @return array{imported:int, skipped:int} */
    public function import(?int $userId = null): array
    {
        $jaImportados = array_map('intval', MarketingPost::where('source_type', RecommendationPost::class)->pluck('source_id')->all());
        $imported = 0;
        $skipped = 0;

        foreach (RecommendationPost::orderBy('id')->get() as $antigo) {
            if (in_array($antigo->id, $jaImportados, true)) {
                $skipped++;
                continue;
            }

            $post = null;
            try {
                DB::transaction(function () use ($antigo, $userId, &$post) {
                $post = MarketingPost::create([
                    'type' => MarketingPostType::Recomendacao,
                    'template' => 'recomendacao',
                    'format' => MarketingFormat::FeedSquare, // o Criador antigo exportava 1080×1080
                    'status' => MarketingPostStatus::Rascunho,
                    'title' => Str::limit(trim("{$antigo->brand} {$antigo->model}"), 255, ''),
                    'source_type' => RecommendationPost::class,
                    'source_id' => $antigo->id,
                    'data' => [],
                    'config' => [],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

                $origem = array_merge([$antigo->image], $antigo->gallery_photos ?? []);
                $novos = [];
                foreach ($origem as $path) {
                    $copia = $this->copiar($path, $post->id);
                    if ($copia) {
                        $novos[] = $copia;
                    }
                }

                $data = RecommendationPostData::fromInput([
                    'brand' => $antigo->brand, 'model' => $antigo->model, 'version' => $antigo->version,
                    'year' => $antigo->year, 'mileage' => $antigo->mileage, 'power' => $antigo->power,
                    'fuel' => $antigo->fuel, 'price' => $antigo->price, 'savings' => $antigo->savings,
                    'url' => $antigo->url, 'equipment' => $antigo->equipment ?? [],
                ], $novos);
                $data['legacy_id'] = $antigo->id;

                $post->update([
                    'data' => $data,
                    'config' => [
                        'gallery_layouts' => $antigo->gallery_layouts ?: new \stdClass(),
                        'gallery_photos_per_slide' => $antigo->gallery_photos_per_slide ?: 3,
                    ],
                ]);
                });
            } catch (\Throwable $e) {
                if ($post) {
                    Storage::disk('public')->deleteDirectory("marketing-posts/{$post->id}");
                }
                throw $e;
            }
            $imported++;
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /** Só copia ficheiros que pertençam ao Criador antigo e existam. */
    protected function copiar(?string $path, int $postId): ?string
    {
        $disk = Storage::disk('public');
        if (! is_string($path) || ! str_starts_with($path, 'recommendation-posts/') || str_contains($path, '..') || ! $disk->exists($path)) {
            return null;
        }

        $novo = "marketing-posts/{$postId}/" . Str::random(24) . '.' . pathinfo($path, PATHINFO_EXTENSION);
        $disk->copy($path, $novo);

        return $novo;
    }
}
