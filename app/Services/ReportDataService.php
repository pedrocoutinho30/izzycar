<?php

namespace App\Services;

use App\Models\Client;
use App\Models\CostSimulator;
use App\Models\FinancialMovement;
use App\Models\LeadActivity;
use App\Models\Legalization;
use App\Models\Proposal;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportDataService
{
    // Campos que são taxas/médias — não somar entre períodos
    public const RATE_FIELDS = ['avg_sale_price', 'avg_gross_margin', 'conversion_rate', 'lead_to_client'];

    public function periodData(Carbon $start, Carbon $end): array
    {
        $sales = Sale::whereBetween('sale_date', [$start->toDateString(), $end->toDateString()])->get();

        $proposals      = Proposal::whereBetween('created_at', [$start, $end]);
        $proposalsTotal = (clone $proposals)->count();
        $proposalsWon   = (clone $proposals)->whereIn('status', ['Aprovada', 'aprovada'])->count();

        $newLeads   = Client::where('is_lead', true)->whereBetween('created_at', [$start, $end])->count();
        $newClients = Client::where('is_lead', false)->whereBetween('converted_at', [$start, $end])->count();
        $simulators = CostSimulator::whereBetween('created_at', [$start, $end])->count();

        $activities   = LeadActivity::where('type', '!=', 'system')->whereBetween('created_at', [$start, $end])->count();
        $followupsSet = LeadActivity::where('type', 'system')
            ->where('title', 'like', 'Follow-up agendado%')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $movIncome   = (float) FinancialMovement::where('type', 'income')
            ->whereBetween('movement_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount_net');
        $movExpenses = (float) FinancialMovement::where('type', 'expense')
            ->whereBetween('movement_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount_net');

        return [
            'sales_count'       => $sales->count(),
            'sales_volume'      => (float) $sales->sum('sale_price'),
            'gross_margin'      => (float) $sales->sum('gross_margin'),
            'net_margin'        => (float) $sales->sum('net_margin'),
            'avg_sale_price'    => $sales->count() ? (float) $sales->avg('sale_price') : 0.0,
            'avg_gross_margin'  => $sales->count() ? (float) $sales->avg('gross_margin') : 0.0,
            'proposals_sent'    => $proposalsTotal,
            'proposals_won'     => $proposalsWon,
            'conversion_rate'   => $proposalsTotal > 0 ? round($proposalsWon / $proposalsTotal * 100, 1) : 0.0,
            'new_leads'         => $newLeads,
            'new_clients'       => $newClients,
            'lead_to_client'    => $newLeads > 0 ? round($newClients / $newLeads * 100, 1) : 0.0,
            'simulators'        => $simulators,
            'activities'        => $activities,
            'followups_set'     => $followupsSet,
            'mov_income'        => $movIncome,
            'mov_expenses'      => $movExpenses,
            'mov_net'           => $movIncome - $movExpenses,
            'legalizations_new' => Legalization::whereBetween('created_at', [$start, $end])->count(),
        ];
    }

    public function movementCategories(Carbon $start, Carbon $end): array
    {
        return FinancialMovement::whereBetween('movement_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('type, category, SUM(amount_net) as total, COUNT(*) as count')
            ->groupBy('type', 'category')
            ->orderBy('type')
            ->orderByDesc('total')
            ->get()
            ->groupBy('type')
            ->map(fn($rows) => $rows->map(fn($r) => [
                'category' => $r->category,
                'total'    => (float) $r->total,
                'count'    => (int) $r->count,
            ])->values()->toArray())
            ->toArray();
    }

    public function legalizationsSnapshot(): array
    {
        $all        = Legalization::all();
        $totalSteps = count(Legalization::PASSOS);
        $completed  = $all->filter(fn($l) => count(array_filter($l->steps_completed ?? [])) >= $totalSteps)->count();

        return [
            'total'       => $all->count(),
            'completed'   => $completed,
            'in_progress' => $all->count() - $completed,
            'total_steps' => $totalSteps,
        ];
    }

    public function leadOrigins(Carbon $start, Carbon $end): Collection
    {
        return Client::whereBetween('created_at', [$start, $end])
            ->whereNotNull('lead_source')
            ->selectRaw('lead_source, count(*) as total')
            ->groupBy('lead_source')
            ->orderByDesc('total')
            ->get();
    }

    public function proposalFunnel(Carbon $start, Carbon $end): array
    {
        return Proposal::whereBetween('created_at', [$start, $end])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    public function leadStatuses(Carbon $end): array
    {
        return Client::where('is_lead', true)
            ->where('created_at', '<=', $end)
            ->selectRaw('COALESCE(lead_status, "nova") as lead_status, count(*) as total')
            ->groupBy('lead_status')
            ->pluck('total', 'lead_status')
            ->toArray();
    }

    public function activityTypeBreakdown(Carbon $start, Carbon $end): array
    {
        return LeadActivity::where('type', '!=', 'system')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();
    }

    public static function delta(float $current, float $comparison): ?float
    {
        if ($comparison == 0) return null;
        return round(($current - $comparison) / abs($comparison) * 100, 1);
    }
}
