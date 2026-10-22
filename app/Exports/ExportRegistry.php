<?php

namespace App\Exports;

/**
 * Mapa explícito tipo → definição: só os tipos listados aqui exportam.
 * Clientes, leads, legalizações, movimentos e viaturas. Para acrescentar um módulo, criar a definição
 * e a linha aqui (e a rota, coberta pelo config/permissions.php).
 */
final class ExportRegistry
{
    /** @var array<string, class-string<ExportDefinition>> */
    private const DEFINITIONS = [
        'clients' => ClientExport::class,
        'leads' => LeadExport::class,
        'legalizations' => LegalizationExport::class,
        'movements' => MovementExport::class,
        'vehicles' => VehicleExport::class,
    ];

    public static function has(string $type): bool
    {
        return isset(self::DEFINITIONS[$type]);
    }

    public static function get(string $type): ?ExportDefinition
    {
        $class = self::DEFINITIONS[$type] ?? null;

        return $class ? new $class() : null;
    }

    /** @return list<string> */
    public static function types(): array
    {
        return array_keys(self::DEFINITIONS);
    }
}
