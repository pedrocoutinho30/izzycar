<?php

namespace App\Support;

/**
 * Texto formatado que vem da base de dados (escrito no editor do backoffice) e
 * é impresso sem escape. O editor grava as cores dentro do próprio texto
 * (style="color: rgb(153, 0, 0)" ou <font color="#900">), o que ignora o CSS do
 * site. display() troca as cores vermelhas do texto pela cor de destaque (dourado)
 * ao mostrar a página, sem alterar o que está guardado.
 *
 * Só toca na cor do TEXTO (propriedade color e <font color>): background-color,
 * border-color, etc. ficam como estão, tal como as cores que não são vermelhas.
 */
final class RichText
{
    /** Dentro de style="…": acompanha a variável do site (layout.css). */
    public const CSS_REPLACEMENT = 'var(--accent-color, #92642D)';

    /** O atributo <font color> não aceita var(): hex literal. */
    public const FONT_REPLACEMENT = '#92642D';

    private const NAMED_REDS = [
        'red' => '#ff0000', 'crimson' => '#dc143c', 'darkred' => '#8b0000', 'firebrick' => '#b22222',
        'maroon' => '#800000', 'indianred' => '#cd5c5c', 'brown' => '#a52a2a', 'tomato' => '#ff6347',
        'lightcoral' => '#f08080', 'salmon' => '#fa8072', 'darksalmon' => '#e9967a',
    ];

    public static function display(?string $html): string
    {
        if ($html === null || $html === '' || stripos($html, 'color') === false) {
            return (string) $html;
        }

        // style="…color: X…" (aspas duplas ou simples).
        $html = preg_replace_callback(
            '/\bstyle\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i',
            function (array $m): string {
                $double = $m[1] !== null;
                $css = $double ? $m[1] : $m[2];

                // (?<![\w-]) deixa de fora background-color, border-color, etc.
                $css = preg_replace_callback(
                    '/(?<![\w-])(color\s*:\s*)([^;!]*?)(\s*)(?=;|!|$)/i',
                    fn (array $c): string => self::isRed($c[2]) ? $c[1] . self::CSS_REPLACEMENT . $c[3] : $c[0],
                    $css
                );

                return $double ? 'style="' . $css . '"' : "style='" . $css . "'";
            },
            $html,
            -1,
            $count,
            PREG_UNMATCHED_AS_NULL
        ) ?? $html;

        // <font color="…">
        return preg_replace_callback(
            '/(<font\b[^>]*?\bcolor\s*=\s*)(?:"([^"]*)"|\'([^\']*)\')/i',
            function (array $m): string {
                $double = $m[2] !== null;
                $value = $double ? $m[2] : $m[3];
                $novo = self::isRed($value) ? self::FONT_REPLACEMENT : $value;

                return $m[1] . ($double ? '"' . $novo . '"' : "'" . $novo . "'");
            },
            $html,
            -1,
            $count,
            PREG_UNMATCHED_AS_NULL
        ) ?? $html;
    }

    /** Vermelho (e bordô, tijolo, coral escuro): matiz perto de 0°, com saturação. Laranja, rosa e dourado ficam de fora. */
    public static function isRed(string $value): bool
    {
        $hsl = self::toHsl(trim(strtolower($value)));
        if ($hsl === null) {
            return false;
        }

        [$h, $s, $l] = $hsl;

        return ($h <= 12 || $h >= 345) && $s >= 0.40 && $l >= 0.10 && $l <= 0.82;
    }

    /** @return array{0: float, 1: float, 2: float}|null  matiz 0-360, saturação e luminosidade 0-1 */
    private static function toHsl(string $v): ?array
    {
        if ($v === '' || str_starts_with($v, 'var(')) {
            return null;
        }

        if (isset(self::NAMED_REDS[$v])) {
            $v = self::NAMED_REDS[$v];
        }

        if (preg_match('/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/', $v, $m)) {
            $hex = $m[1];
            if (strlen($hex) <= 4) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }

            return self::rgbToHsl(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
        }

        if (preg_match('/^rgba?\(\s*([\d.]+%?)\s*[, ]\s*([\d.]+%?)\s*[, ]\s*([\d.]+%?)/', $v, $m)) {
            $c = array_map(
                fn (string $x): float => str_ends_with($x, '%') ? (float) $x * 2.55 : (float) $x,
                [$m[1], $m[2], $m[3]]
            );

            return self::rgbToHsl(...$c);
        }

        if (preg_match('/^hsla?\(\s*([\d.]+)(?:deg)?\s*[, ]\s*([\d.]+)%\s*[, ]\s*([\d.]+)%/', $v, $m)) {
            return [fmod((float) $m[1], 360.0), (float) $m[2] / 100, (float) $m[3] / 100];
        }

        return null;
    }

    /** @return array{0: float, 1: float, 2: float} */
    private static function rgbToHsl(float $r, float $g, float $b): array
    {
        [$r, $g, $b] = [min(max($r, 0), 255) / 255, min(max($g, 0), 255) / 255, min(max($b, 0), 255) / 255];
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            return [0.0, 0.0, $l];
        }

        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        $h = match ($max) {
            $r => ($g - $b) / $d + ($g < $b ? 6 : 0),
            $g => ($b - $r) / $d + 2,
            default => ($r - $g) / $d + 4,
        };

        return [$h * 60, $s, $l];
    }
}
