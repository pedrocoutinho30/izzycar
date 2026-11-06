<?php

namespace App\QuickEntry;

/**
 * Normalização do texto: minúsculas, sem acentos e com espaços simples.
 *
 * normalize() mantém UM caractere por caractere do original (sem colapsar
 * espaços) e só devolve bytes ASCII, para que as posições do texto normalizado
 * sirvam para recortar o texto original (ver TextCursor). O símbolo € vira
 * CURRENCY. compare() colapsa espaços e serve para comparar nomes.
 */
final class TextNormalizer
{
    public const CURRENCY = "\x01";

    private const ACCENTS = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n',
    ];

    /** Um byte ASCII por caractere do original. */
    public static function normalize(string $text): string
    {
        $out = '';
        foreach (mb_str_split($text) as $char) {
            $out .= self::char($char);
        }

        return $out;
    }

    /** Forma de comparação: normalizada e com espaços colapsados. */
    public static function compare(string $text): string
    {
        $normalized = str_replace(self::CURRENCY, '€', self::normalize($text));

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    private static function char(string $char): string
    {
        if ($char === '€') {
            return self::CURRENCY;
        }
        if (preg_match('/^\s$/u', $char) === 1) {
            return ' ';
        }

        $lower = mb_strtolower($char);
        $lower = self::ACCENTS[$lower] ?? $lower;

        // Tudo o que não seja ASCII imprimível vira "?" (continua 1 caractere).
        return strlen($lower) === 1 && ord($lower) >= 32 && ord($lower) < 127 ? $lower : '?';
    }
}
