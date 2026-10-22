<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Divisão do serviço Izzycar em duas tranches, pelas percentagens das
 * Definições (init_payment na adjudicação, final_payment na entrega; 50/50
 * por omissão). É a mesma conta da página da cotação que o cliente vê.
 */
class ServiceTranches
{
    /** @return array<string, mixed> */
    public static function settings(): array
    {
        return Setting::whereIn('label', ['init_payment', 'final_payment'])->pluck('value', 'label')->all();
    }

    /**
     * @param  array<string, mixed>|null  $settings  por omissão lê as Definições
     * @return array{tranche1: float, tranche2: float, pct1: float, pct2: float}
     */
    public static function split(float $total, ?array $settings = null): array
    {
        $settings ??= self::settings();

        $pct1 = self::percentagem($settings['init_payment'] ?? null) ?? 50.0;
        $pct2 = self::percentagem($settings['final_payment'] ?? null) ?? (100.0 - $pct1);

        $tranche1 = round($total * $pct1 / 100, 2);

        return [
            'tranche1' => $tranche1,
            'tranche2' => round($total - $tranche1, 2),
            'pct1' => $pct1,
            'pct2' => $pct2,
        ];
    }

    /** "60%" → 60.0; vazio ou fora de 0–100 → null. */
    private static function percentagem(mixed $valor): ?float
    {
        $numero = (float) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', (string) $valor));

        return $numero > 0 && $numero <= 100 ? $numero : null;
    }
}
