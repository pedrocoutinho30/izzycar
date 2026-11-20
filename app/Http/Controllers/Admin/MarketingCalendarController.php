<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketingPost;
use App\Services\MarketingPlanGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/** Calendário editorial (semana/mês) e geração do plano de 30 dias. Só planeamento interno. */
class MarketingCalendarController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->query('view') === 'week' ? 'week' : 'month';
        $channel = in_array($request->query('channel'), MarketingPost::CHANNELS, true) ? $request->query('channel') : null;
        try {
            $date = CarbonImmutable::parse((string) $request->query('date', 'today'));
        } catch (\Throwable) {
            $date = CarbonImmutable::today();
        }

        $from = $view === 'week' ? $date->startOfWeek() : $date->startOfMonth()->startOfWeek();
        $to = $view === 'week' ? $date->endOfWeek() : $date->endOfMonth()->endOfWeek();
        // No mês só contam os posts desse mês; a grelha mostra os dias vizinhos vazios.
        $posts = MarketingPost::whereBetween('scheduled_for', $view === 'week' ? [$from, $to] : [$date->startOfMonth(), $date->endOfMonth()])
            ->when($channel, fn ($q) => $q->whereIn('channel', [$channel, 'ambos']))
            ->orderBy('scheduled_for')->get()
            ->groupBy(fn ($p) => $p->scheduled_for->toDateString());

        $pending = MarketingPost::whereNotNull('scheduled_for')->where('data->pending_selection', true)->count();

        return view('admin.marketing.calendar', [
            'view' => $view, 'channel' => $channel, 'date' => $date, 'from' => $from, 'to' => $to,
            'posts' => $posts, 'pending' => $pending,
            'prev' => ($view === 'week' ? $date->subWeek() : $date->subMonthNoOverflow())->toDateString(),
            'next' => ($view === 'week' ? $date->addWeek() : $date->addMonthNoOverflow())->toDateString(),
        ]);
    }

    public function generatePlan(Request $request, MarketingPlanGenerator $generator)
    {
        $v = $request->validate(['start_date' => ['required', 'date_format:Y-m-d']]);
        $start = CarbonImmutable::createFromFormat('Y-m-d', $v['start_date'])->startOfDay();

        try {
            $n = $generator->generate($start, (int) auth()->id());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.marketing.calendar', ['date' => $start->toDateString(), 'view' => 'month'])
            ->with('success', "Criados {$n} rascunhos no calendário. Os marcados \"por selecionar\" precisam de conteúdo real (viatura, testemunho, importação…).");
    }
}
