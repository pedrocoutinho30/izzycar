<?php

namespace App\Support;

/**
 * Neutraliza a injeção de fórmulas em ficheiros CSV (CSV injection, SEG-011).
 *
 * O Excel/LibreOffice avaliam como fórmula qualquer célula de um CSV que comece
 * por = + - @ (ou tab/CR). Esses valores levam um apóstrofo à frente, que o
 * Excel esconde e trata como "texto". Números (-1, 12,50) e telefones
 * (+351 912 345 678) são legítimos e ficam como estão.
 *
 * Só se usa em CSV. No XLSX o texto vai sempre como célula de texto (nunca
 * como fórmula), por isso não precisa de prefixo (ver ExportService).
 */
final class CsvSanitizer
{
    public static function cell(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        $first = $value[0];
        if ($first === "\t" || $first === "\r" || $first === "\n") {
            return "'" . $value;
        }

        $trimmed = ltrim($value);
        if ($trimmed === '' || !in_array($trimmed[0], ['=', '+', '-', '@'], true)) {
            return $value;
        }

        // Número simples (-1, -12,50, +3.5) ou telefone (+351 912 345 678).
        if (preg_match('/^[+-]?\d+(?:[.,]\d+)?$/', $trimmed) || preg_match('/^\+\d[\d ]*$/', $trimmed)) {
            return $value;
        }

        return "'" . $value;
    }

    /** @param  array<int|string, mixed>  $row */
    public static function row(array $row): array
    {
        return array_map([self::class, 'cell'], $row);
    }
}
