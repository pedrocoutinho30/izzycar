<?php

namespace App\Marketing;

use App\Models\V3Vehicle;

/**
 * Instantâneo de um post de importação concluída. Nunca inclui cliente,
 * matrícula nem VIN; a autorização de divulgação é obrigatória.
 */
class ImportPostData
{
    public const MAX_HIGHLIGHTS = 4;

    /** @param  array{origin_country?:?string,highlights?:array,savings?:mixed}  $extra */
    public static function fromVehicle(V3Vehicle $v, array $extra = []): array
    {
        $base = VehiclePostData::fromVehicle($v);
        $data = self::common([
            'brand' => $base['brand'], 'model' => $base['model'], 'version' => $base['version'], 'year' => $base['year'],
            'highlights' => $extra['highlights'] ?? $base['equipment'],
        ] + $extra);
        $data['photos'] = array_map(fn ($p) => array_intersect_key($p, array_flip(['id', 'path', 'focal_x', 'focal_y'])), $base['photos']);

        return $data;
    }

    /** @param  string[]  $photoPaths */
    public static function fromManual(array $v, array $photoPaths): array
    {
        $data = self::common($v);
        $data['photos'] = array_map(fn (string $p) => ['path' => $p, 'focal_x' => null, 'focal_y' => null], array_values($photoPaths));

        return $data;
    }

    private static function common(array $v): array
    {
        $data = [];
        foreach (['brand', 'model', 'version', 'origin_country'] as $k) {
            $s = trim((string) ($v[$k] ?? ''));
            if ($s !== '') {
                $data[$k] = $s;
            }
        }
        if (! empty($v['year']) && (int) $v['year'] > 0) {
            $data['year'] = (int) $v['year'];
        }
        $h = $v['highlights'] ?? [];
        if (is_string($h)) {
            $h = preg_split('/\R/', $h);
        }
        $data['highlights'] = collect($h)->map(fn ($l) => trim((string) $l))->filter()->take(self::MAX_HIGHLIGHTS)->values()->all();
        if (isset($v['savings']) && $v['savings'] !== '' && (float) $v['savings'] > 0) {
            $data['savings'] = (float) $v['savings'];
        }
        $data['authorized'] = true;
        $data['captured_at'] = now()->toIso8601String();

        return $data;
    }
}
