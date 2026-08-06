<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Calcula métricas de leads/conversões/comissões por angariador.
 * Usado tanto pelo dashboard do próprio angariador como pela área
 * de administração de angariadores (listagem, detalhe e totais agregados).
 */
class AngariadorMetricsService
{
    public function forUser(int $ownerId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $leadsQuery = Client::where('owner_id', $ownerId);
        $this->applyDateRange($leadsQuery, $dateFrom, $dateTo);
        $leadsCount = $leadsQuery->count();

        $convertedQuery = Client::where('owner_id', $ownerId)->where('is_lead', false);
        $this->applyDateRange($convertedQuery, $dateFrom, $dateTo, 'converted_at');
        $convertedCount = $convertedQuery->count();

        $convertedQuery2 = ConvertedProposal::with(['client', 'statusHistories'])->where('owner_id', $ownerId);
        $this->applyDateRange($convertedQuery2, $dateFrom, $dateTo);
        $convertedProposals = $convertedQuery2->get();

        [$comissaoRecebida, $comissaoPendente] = ConvertedProposal::commissionTotals($convertedProposals);

        return [
            'leadsCount' => $leadsCount,
            'convertedCount' => $convertedCount,
            'conversionRate' => $leadsCount > 0 ? round(($convertedCount / $leadsCount) * 100, 1) : 0.0,
            'comissaoRecebida' => $comissaoRecebida,
            'comissaoPendente' => $comissaoPendente,
            'convertedProposals' => $convertedProposals,
        ];
    }

    public function forAllAngariadores(?string $dateFrom = null, ?string $dateTo = null)
    {
        $angariadores = User::role('angariador')->where('status', 'aprovado')->orderBy('name')->get();

        return $angariadores->map(function (User $angariador) use ($dateFrom, $dateTo) {
            $metrics = $this->forUser($angariador->id, $dateFrom, $dateTo);

            return [
                'angariador' => $angariador,
                'leadsCount' => $metrics['leadsCount'],
                'convertedCount' => $metrics['convertedCount'],
                'conversionRate' => $metrics['conversionRate'],
                'comissaoRecebida' => $metrics['comissaoRecebida'],
                'comissaoPendente' => $metrics['comissaoPendente'],
            ];
        });
    }

    public function totals($rows): array
    {
        return [
            'leadsCount' => $rows->sum('leadsCount'),
            'convertedCount' => $rows->sum('convertedCount'),
            'comissaoRecebida' => round($rows->sum('comissaoRecebida'), 2),
            'comissaoPendente' => round($rows->sum('comissaoPendente'), 2),
        ];
    }

    private function applyDateRange($query, ?string $dateFrom, ?string $dateTo, string $column = 'created_at'): void
    {
        if ($dateFrom) {
            $query->whereDate($column, '>=', Carbon::parse($dateFrom));
        }
        if ($dateTo) {
            $query->whereDate($column, '<=', Carbon::parse($dateTo));
        }
    }
}
