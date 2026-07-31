<?php

namespace App\Services;

use Carbon\Carbon;

class MonthlyReportService
{
    public function __construct(private ReportDataService $data) {}

    public function generate(Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $pmStart = $month->copy()->subMonthNoOverflow()->startOfMonth();
        $pmEnd   = $month->copy()->subMonthNoOverflow()->endOfMonth();

        $slyStart = $month->copy()->subYear()->startOfMonth();
        $slyEnd   = $month->copy()->subYear()->endOfMonth();

        return [
            'period'              => $start,
            'current'             => $this->data->periodData($start, $end),
            'prev_month'          => $this->data->periodData($pmStart, $pmEnd),
            'same_last_year'      => $this->data->periodData($slyStart, $slyEnd),
            'ytd_avg'             => $this->ytdAverage($month),
            'lead_origins'        => $this->data->leadOrigins($start, $end),
            'proposal_funnel'     => $this->data->proposalFunnel($start, $end),
            'lead_statuses'       => $this->data->leadStatuses($end),
            'activity_types'      => $this->data->activityTypeBreakdown($start, $end),
            'movement_categories' => $this->data->movementCategories($start, $end),
            'legalizations'       => $this->data->legalizationsSnapshot(),
        ];
    }

    private function ytdAverage(Carbon $month): array
    {
        $yearStart     = Carbon::create($month->year, 1, 1)->startOfDay();
        $monthsElapsed = $month->month;

        $totals = $this->data->periodData($yearStart, $month->copy()->endOfMonth());

        $avg = [];
        foreach ($totals as $key => $value) {
            $avg[$key] = in_array($key, ReportDataService::RATE_FIELDS)
                ? $value
                : ($monthsElapsed > 0 ? round($value / $monthsElapsed, 2) : 0);
        }

        return $avg;
    }
}
