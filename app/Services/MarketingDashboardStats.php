<?php

namespace App\Services;

use App\Enums\MarketingPostStatus;
use App\Enums\MarketingPostType;
use App\Models\MarketingPost;

/** Números reais do Marketing Studio (contagens e listas). Não há métricas de alcance nem estimativas. */
class MarketingDashboardStats
{
    public function build(): array
    {
        $byStatus = MarketingPost::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $byType = MarketingPost::selectRaw('type, count(*) as n')->groupBy('type')->pluck('n', 'type');

        return [
            'total' => (int) $byStatus->sum(),
            'by_status' => collect(MarketingPostStatus::cases())->mapWithKeys(fn ($s) => [$s->value => (int) ($byStatus[$s->value] ?? 0)])->all(),
            'by_type' => collect(MarketingPostType::cases())->mapWithKeys(fn ($t) => [$t->value => (int) ($byType[$t->value] ?? 0)])->all(),
            'published_this_month' => MarketingPost::where('status', MarketingPostStatus::Publicado->value)
                ->whereBetween('published_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            // Fila de saída: aprovados e agendados; com data primeiro (mais próxima), sem data no fim.
            'ready' => MarketingPost::whereIn('status', [MarketingPostStatus::Aprovado->value, MarketingPostStatus::Agendado->value])
                ->orderByRaw('scheduled_for is null')->orderBy('scheduled_for')->orderByDesc('updated_at')->limit(8)->get(),
            'review' => MarketingPost::where('status', MarketingPostStatus::EmRevisao->value)->latest('updated_at')->limit(8)->get(),
            'recent' => MarketingPost::latest('updated_at')->limit(6)->get(),
        ];
    }
}
