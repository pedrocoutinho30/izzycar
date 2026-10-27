<?php

namespace App\Support;

/**
 * Normaliza números escritos à portuguesa ("800,50", "1.200,50") para o
 * formato que o PHP valida (800.50, 1200.50). Valores que não sejam números
 * ficam como estão, para a validação os recusar.
 *
 * Regra: com '.' e ',' ao mesmo tempo, '.' é milhares e ',' decimal; só com uma
 * vírgula, é decimal; com vários pontos, são milhares. Com um só ponto, "N.NNN"
 * (1 a 3 dígitos, não zero, antes do ponto e exactamente 3 depois) é milhares
 * ("20.000" = 20000, nunca 20.0); nos restantes casos ("1.5", "20000.00", "0.125")
 * mantém-se decimal. Quem pré-preenche campos com valores da BD deve usar a
 * vírgula como separador decimal, para nunca produzir "N.NNN".
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
        } elseif (preg_match('/^[1-9]\d{0,2}\.\d{3}$/', $limpo) === 1) {
            // "20.000": um ponto seguido de três dígitos é milhares.
            $limpo = str_replace('.', '', $limpo);
        }

        return is_numeric($limpo) ? $limpo : $value;
    }
}
