<?php

namespace App\Support;

/**
 * Progresso da checklist de uma Oportunidade. "Não tem / N/A" deixa de estar
 * pendente e sai do denominador da percentagem — 8 confirmados em 15 itens com
 * 3 N/A dá 8/12 = 67%, mas o resumo continua a mostrar "8/15 confirmados".
 */
final class ChecklistProgress
{
    /**
     * @param  list<string>  $pendingLabels
     */
    public function __construct(
        public readonly int $total,
        public readonly int $confirmed,
        public readonly int $notApplicable,
        public readonly array $pendingLabels,
    ) {
    }

    public function pending(): int
    {
        return $this->total - $this->confirmed - $this->notApplicable;
    }

    public function percent(): int
    {
        $relevant = $this->total - $this->notApplicable;

        return $relevant > 0 ? (int) round($this->confirmed / $relevant * 100) : 100;
    }

    /** Larguras (%) dos segmentos da barra, sobre o total de itens. */
    public function confirmedWidth(): float
    {
        return $this->total > 0 ? $this->confirmed / $this->total * 100 : 0;
    }

    public function notApplicableWidth(): float
    {
        return $this->total > 0 ? $this->notApplicable / $this->total * 100 : 0;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'confirmed' => $this->confirmed,
            'not_applicable' => $this->notApplicable,
            'pending' => $this->pending(),
            'percent' => $this->percent(),
            'confirmed_width' => $this->confirmedWidth(),
            'not_applicable_width' => $this->notApplicableWidth(),
            'pending_labels' => $this->pendingLabels,
        ];
    }
}
