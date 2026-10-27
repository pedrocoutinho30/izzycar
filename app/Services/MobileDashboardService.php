<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\CostSimulator;
use App\Models\FormProposal;
use App\Models\Proposal;
use App\Models\Task;
use App\Models\User;
use App\Permissions\PermissionService;

/**
 * Números do início da versão mobile: a faixa "Para tratar" (leads novas,
 * follow-ups de hoje, formulários novos, simulações novas e tarefas) e os
 * grupos por área. Cada grupo só existe se o utilizador pode
 * abrir a lista correspondente e conta exatamente o que essa lista lhe mostra
 * (PermissionService::applyScope; nas convertidas, o viewScope do
 * ConvertedProposalService). Uma consulta agrupada por grupo.
 */
class MobileDashboardService
{
    /** @var array<int, object> Números das leads por utilizador (a faixa e o grupo partilham a mesma consulta). */
    private array $leadNumbers = [];

    public function __construct(private PermissionService $permissions, private ConvertedProposalService $converted)
    {
    }

    /**
     * Faixa "Para tratar": os números que pedem atenção, só os que o utilizador
     * pode abrir e contados como a lista respetiva (âmbito incluído).
     *
     * @return list<array{rotulo: string, valor: int, icone: string, href: string, alerta?: bool, detalhe?: ?string}>
     */
    public function paraTratar(User $user): array
    {
        $cartoes = [];

        if ($this->permissions->scope($user, 'leads', 'view') !== null) {
            $n = $this->leadNumbers($user);
            $cartoes[] = ['rotulo' => 'Leads novas', 'valor' => (int) $n->novas, 'icone' => 'bi-person-plus', 'href' => route('admin.mobile.leads.index', ['lead_status' => 'nova'])];
            $cartoes[] = ['rotulo' => 'Follow-ups hoje', 'valor' => (int) $n->hoje, 'icone' => 'bi-alarm', 'href' => route('admin.mobile.leads.index')];
        }

        if ($this->permissions->scope($user, 'form-proposals', 'view') !== null) {
            $novos = $this->permissions->applyScope(FormProposal::query()->where('status', 'novo'), $user, 'form-proposals')->count();
            $cartoes[] = ['rotulo' => 'Formulários novos', 'valor' => $novos, 'icone' => 'bi-envelope-plus', 'href' => route('admin.mobile.form-proposals.index', ['status' => 'novo'])];
        }

        if ($this->permissions->allows($user, 'cost-simulators', 'view')) {
            // "Nova" é a definição da lista mobile (badge) e do desktop ("Não Lidos"): read = falso. Sem âmbito "próprios".
            $novas = CostSimulator::query()->where('read', false)->count();
            $cartoes[] = ['rotulo' => 'Simulações novas', 'valor' => $novas, 'icone' => 'bi-calculator', 'href' => route('admin.mobile.cost-simulators.index')];
        }

        if ($this->permissions->allows($user, 'tasks', 'view')) {
            $cartoes[] = $this->tarefas($user);
        }

        return $cartoes;
    }

    /**
     * Tarefas abertas atribuídas ao utilizador, com prazo hoje ou já ultrapassado.
     * (O desktop mostra todas as tarefas a quem pode ver o objeto: o número é só das suas.)
     */
    private function tarefas(User $user): array
    {
        $hoje = now()->toDateString();
        $amanha = now()->addDay()->toDateString();

        // Comparações com "< amanhã" / "< hoje": valem com a data guardada com ou sem hora (MySQL e sqlite).
        $n = Task::query()
            ->where('user_id', $user->id)
            ->whereIn('status', TaskService::STATUS_ABERTOS)
            ->where('due_date', '<', $amanha)
            ->selectRaw('COUNT(*) AS total, COALESCE(SUM(CASE WHEN due_date < ? THEN 1 ELSE 0 END), 0) AS atraso', [$hoje])
            ->first();

        $atraso = (int) $n->atraso;

        return [
            'rotulo' => 'Tarefas',
            'valor' => (int) $n->total,
            'icone' => 'bi-check2-square',
            'href' => route('admin.mobile.tasks.index', ['filtro' => 'para_tratar']),
            'alerta' => $atraso > 0,
            'detalhe' => $atraso > 0 ? $atraso . ' em atraso' : null,
        ];
    }

    /**
     * @return list<array{titulo: string, icone: string, cartoes: list<array{rotulo: string, valor: int, icone: string, href: string}>}>
     */
    public function grupos(User $user): array
    {
        $grupos = [];

        if ($this->permissions->scope($user, 'leads', 'view') !== null) {
            $grupos[] = $this->leads($user);
        }
        if ($this->permissions->scope($user, 'proposals', 'view') !== null) {
            $grupos[] = $this->cotacoes($user);
        }
        if ($this->converted->viewScope($user)) {
            $grupos[] = $this->convertidas($user);
        }
        if ($this->permissions->allows($user, 'clients', 'view')) {
            $grupos[] = $this->clientes($user);
        }

        return $grupos;
    }

    /** Uma só consulta com somas condicionais (MySQL e sqlite), no âmbito do utilizador. */
    private function leadNumbers(User $user): object
    {
        return $this->leadNumbers[$user->id] ??= (function () use ($user) {
            $hoje = now()->startOfDay();
            $amanha = $hoje->copy()->addDay();

            return $this->permissions->applyScope(Client::query()->where('is_lead', true), $user, 'leads')
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN lead_status = 'nova' THEN 1 ELSE 0 END), 0) AS novas,
                    COALESCE(SUM(CASE WHEN lead_status IS NULL OR lead_status NOT IN ('fria', 'perdida') THEN 1 ELSE 0 END), 0) AS a_tratar,
                    COALESCE(SUM(CASE WHEN next_followup_at >= ? AND next_followup_at < ? THEN 1 ELSE 0 END), 0) AS hoje,
                    COALESCE(SUM(CASE WHEN next_followup_at < ? THEN 1 ELSE 0 END), 0) AS atraso
                ", [$hoje->toDateTimeString(), $amanha->toDateTimeString(), $hoje->toDateTimeString()])
                ->first();
        })();
    }

    private function leads(User $user): array
    {
        $n = $this->leadNumbers($user);

        $lista = route('admin.mobile.leads.index');

        return [
            'titulo' => 'Leads',
            'icone' => 'bi-people',
            // "Novas" e "Follow-ups hoje" estão na faixa "Para tratar".
            'cartoes' => [
                // Sem filtro "exceto frias/perdidas" na lista: abre a lista completa.
                ['rotulo' => 'A tratar', 'valor' => (int) $n->a_tratar, 'icone' => 'bi-telephone', 'href' => $lista],
                ['rotulo' => 'Follow-ups em atraso', 'valor' => (int) $n->atraso, 'icone' => 'bi-exclamation-circle', 'href' => $lista, 'alerta' => true],
            ],
        ];
    }

    private function cotacoes(User $user): array
    {
        $n = $this->permissions->applyScope(Proposal::query(), $user, 'proposals')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status = 'Pendente' THEN 1 ELSE 0 END), 0) AS pendentes,
                COALESCE(SUM(CASE WHEN status = 'Enviado' THEN 1 ELSE 0 END), 0) AS enviadas
            ")
            ->first();

        return [
            'titulo' => 'Cotações',
            'icone' => 'bi-file-earmark-text',
            'cartoes' => [
                ['rotulo' => 'Pendentes', 'valor' => (int) $n->pendentes, 'icone' => 'bi-hourglass-split', 'href' => route('admin.mobile.proposals.index', ['status' => 'Pendente'])],
                ['rotulo' => 'Enviadas (a aguardar)', 'valor' => (int) $n->enviadas, 'icone' => 'bi-send', 'href' => route('admin.mobile.proposals.index', ['status' => 'Enviado'])],
            ],
        ];
    }

    private function convertidas(User $user): array
    {
        // Mesma regra do cartão "Em Processo" da lista desktop.
        $query = ConvertedProposal::notCancelled()
            ->where(fn ($q) => $q->where('status', '!=', 'Concluido')->orWhereNull('status'));
        $emProcesso = $this->converted->applyViewScope($query, $user)->count();

        return [
            'titulo' => 'Convertidas',
            'icone' => 'bi-arrow-repeat',
            'cartoes' => [
                // A lista só filtra por uma etapa: "em processo" abre a lista completa.
                ['rotulo' => 'Em processo', 'valor' => $emProcesso, 'icone' => 'bi-gear', 'href' => route('admin.mobile.converted-proposals.index')],
            ],
        ];
    }

    private function clientes(User $user): array
    {
        $inicio = now()->startOfMonth()->toDateTimeString();
        $fim = now()->endOfMonth()->toDateTimeString();

        $n = $this->permissions->applyScope(Client::query()->where('is_lead', false), $user, 'clients')
            ->selectRaw('COUNT(*) AS total, COALESCE(SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN 1 ELSE 0 END), 0) AS novos', [$inicio, $fim])
            ->first();

        $lista = route('admin.mobile.clients.index');

        return [
            'titulo' => 'Clientes',
            'icone' => 'bi-person-lines-fill',
            'cartoes' => [
                ['rotulo' => 'Total', 'valor' => (int) $n->total, 'icone' => 'bi-person-lines-fill', 'href' => $lista],
                ['rotulo' => 'Novos este mês', 'valor' => (int) $n->novos, 'icone' => 'bi-person-check', 'href' => $lista],
            ],
        ];
    }
}
