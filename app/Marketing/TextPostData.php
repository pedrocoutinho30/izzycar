<?php

namespace App\Marketing;

/** Instantâneo de um post educativo ou de serviço: sem fonte, o texto é escrito pelo utilizador. */
class TextPostData
{
    public const MAX_ITEMS = 8;

    /**
     * @param  array{title?:string,subtitle?:?string,items?:array,cta?:?string,legal_check?:bool,topic?:?string}  $v
     * @return array{title:string,subtitle?:string,items:list<array{a:string,b:string}>,cta?:string,legal_check:bool,topic?:string}
     */
    public static function fromInput(array $v): array
    {
        $out = [
            'title' => trim((string) ($v['title'] ?? '')),
            'items' => self::cleanItems($v['items'] ?? []),
            'legal_check' => (bool) ($v['legal_check'] ?? false),
        ];
        foreach (['subtitle', 'cta', 'topic'] as $k) {
            $s = trim((string) ($v[$k] ?? ''));
            if ($s !== '') {
                $out[$k] = $s;
            }
        }

        return $out;
    }

    /** Descarta pares vazios e limita o número de itens. @return list<array{a:string,b:string}> */
    public static function cleanItems(mixed $items): array
    {
        $out = [];
        foreach ((array) $items as $it) {
            $a = trim((string) ($it['a'] ?? ''));
            $b = trim((string) ($it['b'] ?? ''));
            if ($a === '' && $b === '') {
                continue;
            }
            $out[] = ['a' => $a, 'b' => $b];
        }

        return array_slice($out, 0, self::MAX_ITEMS);
    }
}
