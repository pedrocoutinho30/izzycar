<?php

namespace App\Support;

/**
 * Tabela HTML do detalhe do ISV (gerada pelo ImportSimulatorController e
 * guardada em cost_simulators.isv_table) antes de ser impressa sem escape.
 * Só ficam as tags da tabela, com os atributos class e colspan: o resto
 * (script, handlers on*, style, javascript:) é removido. Mantém o aspeto igual.
 */
final class IsvTable
{
    public static function sanitize(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $limpo = strip_tags($html, '<table><thead><tbody><tr><td><th>');

        return preg_replace_callback('/<(\/?)(table|thead|tbody|tr|td|th)\b([^>]*)>/i', function (array $m): string {
            if ($m[1] === '/') {
                return '</' . strtolower($m[2]) . '>';
            }

            $atributos = '';
            if (preg_match('/\bclass\s*=\s*(["\'])([\w\s-]*)\1/i', $m[3], $c)) {
                $atributos .= ' class="' . trim($c[2]) . '"';
            }
            if (preg_match('/\bcolspan\s*=\s*(["\'])(\d{1,2})\1/i', $m[3], $c)) {
                $atributos .= ' colspan="' . $c[2] . '"';
            }

            return '<' . strtolower($m[2]) . $atributos . '>';
        }, $limpo);
    }
}
