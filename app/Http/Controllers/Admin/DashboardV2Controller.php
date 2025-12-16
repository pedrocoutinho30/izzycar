<?php

/**
 * ==================================================================
 * DASHBOARD CONTROLLER V2
 * ==================================================================
 * 
 * Controller para o dashboard principal do backoffice
 * 
 * FUNCIONALIDADES:
 * - Estatísticas gerais
 * - Gráficos de vendas e propostas
 * - Alertas e notificações
 * - Quick links para ações rápidas
 * 
 * @author Izzycar Team
 * @version 2.0
 * ==================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\FormProposal;
use App\Models\ConvertedProposal;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Sale;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardV2Controller extends Controller
{
    /**
     * Exibe o dashboard principal
     */
    public function index()
    {
        // ============================================================
        // ESTATÍSTICAS GERAIS
        // ============================================================
        
        // Formulários de proposta pendentes (NOVOS - precisa atenção!)
        $newFormProposals = FormProposal::where('status', 'novo')
            ->orWhereNull('status')
            ->count();

        // Propostas por estado
        $proposalsPending = Proposal::where('status', 'Pendente')->count();
        $proposalsApproved = Proposal::where('status', 'Aprovada')->count();
        
        // Clientes totais
        $totalClients = Client::count();
        $newClientsThisMonth = Client::whereMonth('created_at', now()->month)->count();

        // Veículos
        $totalVehicles = Vehicle::count();
        $vehiclesAvailable = Vehicle::where('show_online', true)->count();

        // Vendas este mês
        $salesThisMonth = Sale::whereMonth('created_at', now()->month)->count();
        $salesRevenueThisMonth = Sale::whereMonth('created_at', now()->month)->sum('sale_price');
        // ============================================================
        // CARDS DE ESTATÍSTICAS
        // ============================================================
        $stats = [
            [
                'title' => 'Formulários Novos',
                'value' => $newFormProposals,
                'icon' => 'envelope-exclamation',
                'color' => 'warning',
                'change' => $newFormProposals > 0 ? 'Requer atenção!' : 'Tudo OK',
                'changeType' => $newFormProposals > 0 ? 'negative' : 'positive',
                'link' => route('admin.v2.form-proposals.index')
            ],
            [
                'title' => 'Propostas Pendentes',
                'value' => $proposalsPending,
                'icon' => 'clock-history',
                'color' => 'info',
                'link' => route('admin.v2.proposals.index', ['status' => 'Pendente'])
            ],
            [
                'title' => 'Clientes',
                'value' => $totalClients,
                'icon' => 'people',
                'color' => 'primary',
                'change' => "+{$newClientsThisMonth} este mês",
                'changeType' => 'positive',
                'link' => route('admin.v2.clients.index')
            ],
            [
                'title' => 'Vendas Mês',
                'value' => $salesThisMonth,
                'icon' => 'cash-coin',
                'color' => 'success',
                'change' => number_format($salesRevenueThisMonth, 0, ',', '.') . '€',
                'changeType' => 'positive',
                'link' => route('admin.v2.sales.index')
            ]
        ];

        // ============================================================
        // ATIVIDADE RECENTE
        // ============================================================
        
        // Últimas propostas criadas
        $recentProposals = Proposal::with('client')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Últimos formulários recebidos
        $recentFormProposals = FormProposal::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Últimas vendas
        $recentSales = Sale::with('client', 'vehicle')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // ============================================================
        // GRÁFICO: Propostas por mês (últimos 6 meses)
        // ============================================================
        $proposalsChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $proposalsChart[] = [
                'month' => $month->format('M Y'),
                'count' => Proposal::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count()
            ];
        }

        // ============================================================
        // ALERTAS E NOTIFICAÇÕES
        // ============================================================
        $alerts = [];

        // Alerta: Formulários novos
        if ($newFormProposals > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'envelope-exclamation',
                'title' => 'Formulários de Proposta Pendentes',
                'message' => "Tem {$newFormProposals} formulário(s) de proposta que precisam de atenção.",
                'action' => [
                    'text' => 'Ver Formulários',
                    'href' => route('admin.v2.form-proposals.index')
                ]
            ];
        }

        // Alerta: Propostas sem resposta
        $proposalsNoResponse = Proposal::where('status', 'Sem resposta')
            ->where('created_at', '<', now()->subDays(7))
            ->count();
        
        if ($proposalsNoResponse > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'clock',
                'title' => 'Propostas Antigas Sem Resposta',
                'message' => "{$proposalsNoResponse} proposta(s) há mais de 7 dias sem resposta.",
                'action' => [
                    'text' => 'Ver Propostas',
                    'href' => route('admin.v2.proposals.index', ['status' => 'Sem resposta'])
                ]
            ];
        }

        return view('admin.v2.dashboard', compact(
            'stats',
            'recentProposals',
            'recentFormProposals',
            'recentSales',
            'proposalsChart',
            'alerts'
        ));
    }
}
