<?php

namespace App\Services;

use Carbon\Carbon;

class QuarterlyReportService
{
    private const QUARTER_LABELS = [
        1 => '1.º Trimestre',
        2 => '2.º Trimestre',
        3 => '3.º Trimestre',
        4 => '4.º Trimestre',
    ];

    private const QUARTER_MONTHS = [
        1 => 'Jan–Mar',
        2 => 'Abr–Jun',
        3 => 'Jul–Set',
        4 => 'Out–Dez',
    ];

    public function __construct(private ReportDataService $data) {}

    public function generate(Carbon $date): array
    {
        $quarter = (int) ceil($date->month / 3);
        $year    = $date->year;

        [$start, $end]         = $this->quarterBounds($year, $quarter);
        [$prevStart, $prevEnd] = $this->prevQuarterBounds($year, $quarter);

        $prevQ    = $quarter === 1 ? 4 : $quarter - 1;
        $prevY    = $quarter === 1 ? $year - 1 : $year;
        [$slyStart, $slyEnd] = $this->quarterBounds($year - 1, $quarter);

        return [
            'quarter'             => $quarter,
            'year'                => $year,
            'label'               => self::QUARTER_LABELS[$quarter] . ' ' . $year,
            'months_range'        => self::QUARTER_MONTHS[$quarter],
            'prev_label'          => self::QUARTER_LABELS[$prevQ] . ' ' . $prevY,
            'sly_label'           => self::QUARTER_LABELS[$quarter] . ' ' . ($year - 1),
            'current'             => $this->data->periodData($start, $end),
            'prev_quarter'        => $this->data->periodData($prevStart, $prevEnd),
            'same_last_year'      => $this->data->periodData($slyStart, $slyEnd),
            'lead_origins'        => $this->data->leadOrigins($start, $end),
            'proposal_funnel'     => $this->data->proposalFunnel($start, $end),
            'lead_statuses'       => $this->data->leadStatuses($end),
            'activity_types'      => $this->data->activityTypeBreakdown($start, $end),
            'movement_categories' => $this->data->movementCategories($start, $end),
            'legalizations'       => $this->data->legalizationsSnapshot(),
        ];
    }

    private function quarterBounds(int $year, int $quarter): array
    {
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth   = $quarter * 3;

        return [
            Carbon::create($year, $startMonth, 1)->startOfDay(),
            Carbon::create($year, $endMonth, 1)->endOfMonth()->endOfDay(),
        ];
    }

    private function prevQuarterBounds(int $year, int $quarter): array
    {
        $prevQ = $quarter === 1 ? 4 : $quarter - 1;
        $prevY = $quarter === 1 ? $year - 1 : $year;

        return $this->quarterBounds($prevY, $prevQ);
    }
}
