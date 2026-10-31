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
use App\Models\V3Vehicle;
use App\Models\Vehicle;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
        
        $inicioMes = now()->startOfMonth();
        $fimMes = now()->endOfMonth();
        $estadosLeadAtiva = ['nova', 'em_contacto', 'qualificada'];

        // Formulários de proposta pendentes (NOVOS - precisa atenção!)
        $newFormProposals = FormProposal::where('status', 'novo')
            ->orWhereNull('status')
            ->count();

        // Propostas: pendentes e "sem resposta há mais de 7 dias" numa só query
        $propostasAgg = Proposal::selectRaw(
            "COALESCE(SUM(CASE WHEN status = 'Pendente' THEN 1 ELSE 0 END), 0) as pendentes,
             COALESCE(SUM(CASE WHEN status = 'Sem resposta' AND created_at < ? THEN 1 ELSE 0 END), 0) as sem_resposta_antigas",
            [now()->subDays(7)]
        )->first();
        $proposalsPending = (int) $propostasAgg->pendentes;
        $proposalsNoResponse = (int) $propostasAgg->sem_resposta_antigas;

        // Clientes: totais, novos este mês e leads activas numa só query
        $placeholdersLead = implode(',', array_fill(0, count($estadosLeadAtiva), '?'));
        $clientesAgg = Client::selectRaw(
            "COUNT(*) as total,
             COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as novos_mes,
             COALESCE(SUM(CASE WHEN is_lead = 1 AND lead_status IN ({$placeholdersLead}) THEN 1 ELSE 0 END), 0) as leads_ativas",
            array_merge([$inicioMes, $fimMes], $estadosLeadAtiva)
        )->first();
        $totalClients = (int) $clientesAgg->total;
        $newClientsThisMonth = (int) $clientesAgg->novos_mes;
        // Leads activas hoje (para o badge do menu)
        $totalLeadsActivas = (int) $clientesAgg->leads_ativas;

        // Vendas este mês (contagem e receita numa só query)
        $vendasAgg = Sale::whereBetween('created_at', [$inicioMes, $fimMes])
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(sale_price), 0) as receita')
            ->first();
        $salesThisMonth = (int) $vendasAgg->total;
        $salesRevenueThisMonth = (float) $vendasAgg->receita;
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
        // MÉTRICAS ACCIONÁVEIS — O QUE PRECISA DE ATENÇÃO HOJE
        // ============================================================

        // Leads activas sem qualquer actividade registada há mais de 3 dias
        $leadsSemActividadeQuery = fn () => Client::where('is_lead', true)
            ->whereIn('lead_status', $estadosLeadAtiva)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('lead_activities')
                    ->whereColumn('lead_activities.client_id', 'clients.id')
                    ->where('lead_activities.created_at', '>=', now()->subDays(3));
            });

        $leadsSemActividadeCount = $leadsSemActividadeQuery()->count();

        // Leads sem actividade — lista para mostrar (máx 10: as 10 primeiras por id, mais recentes primeiro)
        $leadsSemActividadeLista = $leadsSemActividadeQuery()
            ->orderBy('id')
            ->limit(10)
            ->get()
            ->sortByDesc('created_at')
            ->values();

        // Follow-ups: atrasados, de hoje e de amanhã numa só query (intervalos em vez de whereDate)
        $inicioHoje = today();
        $fimAmanha = today()->addDay()->endOfDay();
        $followups = Client::where('is_lead', true)
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<=', $fimAmanha)
            ->with('latestActivity')
            ->orderBy('next_followup_at')
            ->get();

        $followupsAtrasados = $followups->filter(fn ($c) => $c->next_followup_at < now())->values();
        $followupsHoje = $followups->filter(fn ($c) => $c->next_followup_at >= $inicioHoje && $c->next_followup_at <= today()->endOfDay())->values();
        $followupsAmanha = $followups->filter(fn ($c) => $c->next_followup_at >= today()->addDay() && $c->next_followup_at <= $fimAmanha)->values();

        // Cotações pendentes ou sem resposta há mais de 5 dias
        $cotacoesSemResposta = Proposal::whereIn('status', ['Pendente', 'Sem resposta'])
            ->where('updated_at', '<', now()->subDays(5))
            ->with('client')
            ->orderBy('updated_at')
            ->take(10)
            ->get();

        // Viaturas em stock sem preço de venda definido
        $viaturasEmStockSemPreco = V3Vehicle::where('status', 'em_stock')
            ->where(function ($q) {
                $q->whereNull('asking_price')->orWhere('asking_price', 0);
            })
            ->count();

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
        $mesesGrafico = [];
        for ($i = 5; $i >= 0; $i--) {
            $mesesGrafico[] = now()->startOfMonth()->subMonths($i);
        }
        $contagemPropostas = $this->monthlyCounts(Proposal::query(), $mesesGrafico);
        $proposalsChart = [];
        foreach ($mesesGrafico as $i => $month) {
            $proposalsChart[] = ['month' => $month->format('M Y'), 'count' => $contagemPropostas[$i]];
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

        // ============================================================
        // TAREFAS DA SEMANA
        // ============================================================
        $weekTasks = Task::whereNotIn('status', ['concluida', 'cancelada'])
            ->where('due_date', '<=', now()->endOfWeek())
            ->orderBy('due_date')
            ->get();

        return view('admin.v2.dashboard', compact(
            'followupsHoje',
            'followupsAmanha',
            'stats',
            'recentProposals',
            'recentFormProposals',
            'recentSales',
            'proposalsChart',
            'alerts',
            'weekTasks',
            // Métricas accionáveis
            'leadsSemActividadeCount',
            'leadsSemActividadeLista',
            'followupsAtrasados',
            'cotacoesSemResposta',
            'viaturasEmStockSemPreco',
            'totalLeadsActivas'
        ));
    }

    /**
     * API de polling para notificações — devolve leads criadas após `since` (timestamp Unix)
     */
    public function newLeadsApi(Request $request)
    {
        $since = $request->query('since')
            ? Carbon::createFromTimestampMs((int) $request->query('since'))
            : now()->subMinute();

        $newLeads = Client::where('is_lead', true)
            ->where('created_at', '>', $since)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'created_at']);

        $totalActive = Client::where('is_lead', true)
            ->whereNotIn('lead_status', ['fria', 'perdida'])
            ->count();

        return response()->json([
            'count'        => $newLeads->count(),
            'total_active' => $totalActive,
            'leads'        => $newLeads->map(fn ($l) => [
                'id'   => $l->id,
                'name' => $l->name,
                'url'  => route('admin.v2.leads.show', $l->id),
            ]),
            'timestamp' => now()->timestampMs,
        ]);
    }

    /**
     * Retorna dados para os gráficos do dashboard
     */
    public function getChartData(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $compareStartDate = $request->input('compare_start_date');
        $compareEndDate = $request->input('compare_end_date');
        $mode = $request->input('mode', 'range'); // 'range' ou 'compare'

        if ($mode === 'compare' && $compareStartDate && $compareEndDate) {
            // Modo comparação de 2 períodos
            return $this->getComparisonData($startDate, $endDate, $compareStartDate, $compareEndDate);
        } else {
            // Modo intervalo de datas
            return $this->getRangeData($startDate, $endDate);
        }
    }

    /**
     * Dados para modo de intervalo de datas
     */
    private function getRangeData($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $months = [];
        $starts = [];

        $current = $start->copy()->startOfMonth();
        while ($current->lte($end->endOfMonth())) {
            $months[] = $current->format('M Y');
            $starts[] = $current->copy();
            $current->addMonth();
        }

        // Uma query por modelo (contagens por mês em colunas) em vez de uma por mês
        $clients = $this->monthlyCounts(Client::query(), $starts);
        $proposals = $this->monthlyCounts(Proposal::query(), $starts);
        $costSimulators = $this->monthlyCounts(\App\Models\CostSimulator::query(), $starts);
        $convertedProposals = $this->monthlyCounts(ConvertedProposal::notCancelled(), $starts);

        return response()->json([
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Período Selecionado',
                    'clients' => $clients,
                    'proposals' => $proposals,
                    'costSimulators' => $costSimulators,
                    'convertedProposals' => $convertedProposals,
                ]
            ]
        ]);
    }

    /**
     * Dados para modo de comparação
     */
    private function getComparisonData($startDate1, $endDate1, $startDate2, $endDate2)
    {
        $period1Start = Carbon::parse($startDate1);
        $period1End = Carbon::parse($endDate1);
        $period2Start = Carbon::parse($startDate2);
        $period2End = Carbon::parse($endDate2);

        // Para comparação, vamos usar apenas 1 mês de cada período
        $month1Label = $period1Start->format('M Y');
        $month2Label = $period2Start->format('M Y');

        // Uma query por modelo para os dois meses (período 1 e 2)
        $meses = [$period1Start->copy()->startOfMonth(), $period2Start->copy()->startOfMonth()];
        [$clients1, $clients2] = $this->monthlyCounts(Client::query(), $meses);
        [$proposals1, $proposals2] = $this->monthlyCounts(Proposal::query(), $meses);
        [$costSimulators1, $costSimulators2] = $this->monthlyCounts(\App\Models\CostSimulator::query(), $meses);
        [$convertedProposals1, $convertedProposals2] = $this->monthlyCounts(ConvertedProposal::notCancelled(), $meses);

        return response()->json([
            'labels' => [$month1Label, $month2Label],
            'datasets' => [
                [
                    'label' => $month1Label,
                    'clients' => [$clients1],
                    'proposals' => [$proposals1],
                    'costSimulators' => [$costSimulators1],
                    'convertedProposals' => [$convertedProposals1],
                ],
                [
                    'label' => $month2Label,
                    'clients' => [$clients2],
                    'proposals' => [$proposals2],
                    'costSimulators' => [$costSimulators2],
                    'convertedProposals' => [$convertedProposals2],
                ]
            ]
        ]);
    }

    /**
     * Contagens de created_at por mês numa só query: uma coluna SUM(CASE ...) por mês, com
     * intervalos [início do mês, início do mês seguinte[ (sargável, ao contrário de
     * whereYear/whereMonth) e SQL portável (MySQL e sqlite). Devolve um array de inteiros
     * na mesma ordem de $inicios (Carbon no 1.º dia de cada mês).
     *
     * @param  Builder  $query
     * @param  array<int, Carbon>  $inicios
     * @return array<int, int>
     */
    private function monthlyCounts($query, array $inicios): array
    {
        if ($inicios === []) {
            return [];
        }

        $colunas = [];
        $bindings = [];
        foreach ($inicios as $i => $inicio) {
            $colunas[] = "COALESCE(SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END), 0) as m{$i}";
            $bindings[] = $inicio->copy()->startOfMonth();
            $bindings[] = $inicio->copy()->startOfMonth()->addMonth();
        }

        // Limita a leitura ao intervalo total pedido (usa índice em created_at, se existir)
        $min = collect($inicios)->min()->copy()->startOfMonth();
        $max = collect($inicios)->max()->copy()->startOfMonth()->addMonth();

        $row = $query->where('created_at', '>=', $min)
            ->where('created_at', '<', $max)
            ->selectRaw(implode(', ', $colunas), $bindings)
            ->first();

        return array_map(fn ($i) => (int) $row->{"m{$i}"}, array_keys($inicios));
    }
}
