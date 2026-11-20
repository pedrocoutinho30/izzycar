<?php

namespace App\Marketing;

use Carbon\CarbonImmutable;

/**
 * Plano editorial de 30 dias: uma publicação por dia, às 12:00, com rotação semanal fixa.
 * Educativos e serviços usam temas reais da biblioteca (sem texto). Viatura, testemunho,
 * importação e recomendação ficam "por selecionar": só o utilizador sabe qual o conteúdo real.
 */
class ThirtyDayPlan
{
    public const DAYS = 30;

    /** Dia da semana ISO (1 = segunda) → tipo. */
    private const ROTATION = [1 => 'educativo', 2 => 'veiculo', 3 => 'servico', 4 => 'testemunho', 5 => 'importacao', 6 => 'educativo', 7 => 'recomendacao'];

    private const PENDING = [
        'veiculo' => ['premium', 'feed_vertical', 'Viatura em destaque (escolher a viatura)'],
        'testemunho' => ['testemunho', 'feed_square', 'Testemunho (escolher um testemunho autorizado)'],
        'importacao' => ['importacao', 'feed_vertical', 'Importação realizada (escolher uma importação autorizada)'],
        'recomendacao' => ['recomendacao', 'feed_vertical', 'Recomendação (escolher o carro)'],
    ];

    /** @return list<array{date:CarbonImmutable,type:string,template:string,format:string,title:string,topic:?string,legal_check:bool,pending:bool}> */
    public static function entries(CarbonImmutable $start): array
    {
        $topics = collect(config('marketing.topics'))->groupBy('type')->map(fn ($g) => $g->values()->all())->all();
        $used = ['educativo' => 0, 'servico' => 0];
        $out = [];

        for ($i = 0; $i < self::DAYS; $i++) {
            $date = $start->addDays($i)->setTime(12, 0);
            $type = self::ROTATION[$date->dayOfWeekIso];

            if (isset(self::PENDING[$type])) {
                [$template, $format, $title] = self::PENDING[$type];
                $out[] = compact('date', 'type', 'template', 'format', 'title') + ['topic' => null, 'legal_check' => false, 'pending' => true];

                continue;
            }

            $t = $topics[$type][$used[$type]++] ?? null;
            $out[] = $t
                ? ['date' => $date, 'type' => $type, 'template' => $t['template'], 'format' => 'feed_vertical', 'title' => $t['title'], 'topic' => $t['key'], 'legal_check' => $t['legal_check'], 'pending' => false]
                : ['date' => $date, 'type' => $type, 'template' => 'dica-rapida', 'format' => 'feed_vertical', 'title' => 'Tema por escolher', 'topic' => null, 'legal_check' => false, 'pending' => false];
        }

        return $out;
    }
}
