<?php

namespace App\Services;

use App\Models\Client;
use App\Models\CostSimulator;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MonthlyReportService
{
    // Métricas que são já taxas/médias — não dividir pelos meses no YTD
    private const RATE_FIELDS = ['avg_sale_price', 'avg_gross_margin', 'conversion_rate', 'lead_to_client'];

    public function generate(Carbon $month): array
    {
        $current      = $this->periodData($month->copy()->startOfMonth(), $month->copy()->endOfMonth());
        $prevMonth    = $this->periodData($month->copy()->subMonthNoOverflow()->startOfMonth(), $month->copy()->subMonthNoOverflow()->endOfMonth());
        $sameLastYear = $this->periodData($month->copy()->subYear()->startOfMonth(), $month->copy()->subYear()->endOfMonth());
        $ytdAvg       = $this->ytdAverage($month);

        return [
            'period'          => $month->copy()->startOfMonth(),
            'current'         => $current,
            'prev_month'      => $prevMonth,
            'same_last_year'  => $sameLastYear,
            'ytd_avg'         => $ytdAvg,
            'lead_origins'    => $this->leadOrigins($month),
            'proposal_funnel' => $this->proposalFunnel($month),
            'lead_statuses'   => $this->leadStatuses($month),
            'activity_types'  => $this->activityTypeBreakdown($month),
        ];
    }

    private function periodData(Carbon $start, Carbon $end): array
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

        return [
            // Vendas
            'sales_count'      => $sales->count(),
            'sales_volume'     => (float) $sales->sum('sale_price'),
            'gross_margin'     => (float) $sales->sum('gross_margin'),
            'net_margin'       => (float) $sales->sum('net_margin'),
            'avg_sale_price'   => $sales->count() ? (float) $sales->avg('sale_price') : 0.0,
            'avg_gross_margin' => $sales->count() ? (float) $sales->avg('gross_margin') : 0.0,

            // Pipeline
            'proposals_sent'  => $proposalsTotal,
            'proposals_won'   => $proposalsWon,
            'conversion_rate' => $proposalsTotal > 0 ? round($proposalsWon / $proposalsTotal * 100, 1) : 0.0,

            // Leads & clientes
            'new_leads'       => $newLeads,
            'new_clients'     => $newClients,
            'lead_to_client'  => $newLeads > 0 ? round($newClients / $newLeads * 100, 1) : 0.0,

            // Topo de funil
            'simulators'  => $simulators,

            // Atividade
            'activities'   => $activities,
            'followups_set'=> $followupsSet,
        ];
    }

    private function ytdAverage(Carbon $month): array
    {
        $yearStart     = Carbon::create($month->year, 1, 1)->startOfDay();
        $yearEnd       = $month->copy()->endOfMonth();
        $monthsElapsed = $month->month;

        $totals = $this->periodData($yearStart, $yearEnd);

        $avg = [];
        foreach ($totals as $key => $value) {
            // Taxas e médias já são calculadas sobre o total anual — mantêm-se
            if (in_array($key, self::RATE_FIELDS)) {
                $avg[$key] = $value;
            } else {
                $avg[$key] = $monthsElapsed > 0 ? round($value / $monthsElapsed, 2) : 0;
            }
        }

        return $avg;
    }

    private function leadOrigins(Carbon $month): Collection
    {
        return Client::whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->whereNotNull('lead_source')
            ->selectRaw('lead_source, count(*) as total')
            ->groupBy('lead_source')
            ->orderByDesc('total')
            ->get();
    }

    private function proposalFunnel(Carbon $month): array
    {
        return Proposal::whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    private function leadStatuses(Carbon $month): array
    {
        // Estado atual de todas as leads existentes no fim do mês
        return Client::where('is_lead', true)
            ->where('created_at', '<=', $month->copy()->endOfMonth())
            ->selectRaw('COALESCE(lead_status, "nova") as lead_status, count(*) as total')
            ->groupBy('lead_status')
            ->pluck('total', 'lead_status')
            ->toArray();
    }

    private function activityTypeBreakdown(Carbon $month): array
    {
        return LeadActivity::where('type', '!=', 'system')
            ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
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
