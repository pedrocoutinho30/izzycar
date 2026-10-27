<?php

namespace App\Support;

/**
 * Normaliza números escritos à portuguesa ("800,50", "1.200,50") para o
 * formato que o PHP valida (800.50, 1200.50). Valores que não sejam números
 * ficam como estão, para a validação os recusar.
 *
 * Regra: com '.' e ',' ao mesmo tempo, '.' é milhares e ',' decimal; só com uma
 * vírgula, é decimal; com um só ponto mantém-se (é o formato que a BD devolve);
 * com vários pontos, são milhares.
 */
final class DecimalInput
{
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Espaços normais, de não-quebra e estreitos (separador de milhares).
        $limpo = preg_replace('/[\s\x{00A0}\x{202F}]+/u', '', $value) ?? $value;
        if ($limpo === '') {
            return null;
        }

        if (str_contains($limpo, ',') && str_contains($limpo, '.')) {
            $limpo = str_replace(',', '.', str_replace('.', '', $limpo));
        } elseif (substr_count($limpo, ',') === 1) {
            $limpo = str_replace(',', '.', $limpo);
        } elseif (substr_count($limpo, '.') > 1 && ! str_contains($limpo, ',')) {
            // "1.200.500": vários pontos só podem ser milhares.
            $limpo = str_replace('.', '', $limpo);
        }

        return is_numeric($limpo) ? $limpo : $value;
    }
}
