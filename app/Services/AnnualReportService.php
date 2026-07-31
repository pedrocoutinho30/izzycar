<?php

namespace App\Services;

use Carbon\Carbon;

class AnnualReportService
{
    public function __construct(private ReportDataService $data) {}

    public function generate(int $year): array
    {
        $start     = Carbon::create($year, 1, 1)->startOfDay();
        $end       = Carbon::create($year, 12, 31)->endOfDay();
        $prevStart = Carbon::create($year - 1, 1, 1)->startOfDay();
        $prevEnd   = Carbon::create($year - 1, 12, 31)->endOfDay();

        $monthlyBreakdown = [];
        for ($m = 1; $m <= 12; $m++) {
            $ms = Carbon::create($year, $m, 1)->startOfDay();
            $me = Carbon::create($year, $m, 1)->endOfMonth()->endOfDay();
            $monthlyBreakdown[$m] = $this->data->periodData($ms, $me);
        }

        return [
            'year'                => $year,
            'current'             => $this->data->periodData($start, $end),
            'prev_year'           => $this->data->periodData($prevStart, $prevEnd),
            'monthly_breakdown'   => $monthlyBreakdown,
            'lead_origins'        => $this->data->leadOrigins($start, $end),
            'proposal_funnel'     => $this->data->proposalFunnel($start, $end),
            'lead_statuses'       => $this->data->leadStatuses($end),
            'activity_types'      => $this->data->activityTypeBreakdown($start, $end),
            'movement_categories' => $this->data->movementCategories($start, $end),
            'legalizations'       => $this->data->legalizationsSnapshot(),
        ];
    }
}
