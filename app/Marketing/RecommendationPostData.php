<?php

namespace App\Marketing;

/**
 * Instantâneo de um post de recomendação (carro de fora, entrada manual).
 * Campos vazios ficam de fora; preço e poupança nunca são calculados.
 */
class RecommendationPostData
{
    public const MAX_EQUIPMENT = 4;

    /**
     * @param  array  $v           campos do formulário (ou do post antigo)
     * @param  string[]  $photoPaths  caminhos no disco "public", a principal primeiro
     */
    public static function fromInput(array $v, array $photoPaths): array
    {
        $data = [];
        foreach (['brand', 'model', 'version', 'fuel', 'url'] as $k) {
            $val = trim((string) ($v[$k] ?? ''));
            if ($val !== '') {
                $data[$k] = $val;
            }
        }
        foreach (['year', 'mileage', 'power'] as $k) {
            if (isset($v[$k]) && $v[$k] !== '' && (int) $v[$k] > 0) {
                $data[$k] = (int) $v[$k];
            }
        }
        foreach (['price', 'savings'] as $k) {
            if (isset($v[$k]) && $v[$k] !== '' && (float) $v[$k] > 0) {
                $data[$k] = (float) $v[$k];
            }
        }

        $equipment = $v['equipment'] ?? null;
        if ($equipment === null) {
            $equipment = preg_split('/\R/', (string) ($v['equipment_raw'] ?? ''));
        }
        $data['equipment'] = collect($equipment)
            ->map(fn ($l) => trim((string) $l))
            ->filter()
            ->take(self::MAX_EQUIPMENT)
            ->values()
            ->all();

        $data['photos'] = array_map(fn (string $path) => ['path' => $path, 'focal_x' => null, 'focal_y' => null], array_values($photoPaths));
        $data['captured_at'] = now()->toIso8601String();

        return $data;
    }
}
