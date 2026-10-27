<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\Proposal;
use App\Models\User;
use App\Permissions\PermissionService;

/**
 * Números do início da versão mobile. Cada grupo só existe se o utilizador pode
 * abrir a lista correspondente e conta exatamente o que essa lista lhe mostra
 * (PermissionService::applyScope; nas convertidas, o viewScope do
 * ConvertedProposalService). Uma consulta agrupada por grupo.
 */
class MobileDashboardService
{
    public function __construct(private PermissionService $permissions, private ConvertedProposalService $converted)
    {
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
        if ($this->permissions->scope($user, 'form-proposals', 'view') !== null) {
            $grupos[] = $this->formularios($user);
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

    private function leads(User $user): array
    {
        $hoje = now()->startOfDay();
        $amanha = $hoje->copy()->addDay();

        // Uma só consulta com somas condicionais (MySQL e sqlite).
        $n = $this->permissions->applyScope(Client::query()->where('is_lead', true), $user, 'leads')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN lead_status = 'nova' THEN 1 ELSE 0 END), 0) AS novas,
                COALESCE(SUM(CASE WHEN lead_status IS NULL OR lead_status NOT IN ('fria', 'perdida') THEN 1 ELSE 0 END), 0) AS a_tratar,
                COALESCE(SUM(CASE WHEN next_followup_at >= ? AND next_followup_at < ? THEN 1 ELSE 0 END), 0) AS hoje,
                COALESCE(SUM(CASE WHEN next_followup_at < ? THEN 1 ELSE 0 END), 0) AS atraso
            ", [$hoje->toDateTimeString(), $amanha->toDateTimeString(), $hoje->toDateTimeString()])
            ->first();

        $lista = route('admin.mobile.leads.index');

        return [
            'titulo' => 'Leads',
            'icone' => 'bi-people',
            'cartoes' => [
                ['rotulo' => 'Novas', 'valor' => (int) $n->novas, 'icone' => 'bi-person-plus', 'href' => route('admin.mobile.leads.index', ['lead_status' => 'nova'])],
                // Sem filtro "exceto frias/perdidas" na lista: abre a lista completa.
                ['rotulo' => 'A tratar', 'valor' => (int) $n->a_tratar, 'icone' => 'bi-telephone', 'href' => $lista],
                ['rotulo' => 'Follow-ups hoje', 'valor' => (int) $n->hoje, 'icone' => 'bi-alarm', 'href' => $lista],
                ['rotulo' => 'Follow-ups em atraso', 'valor' => (int) $n->atraso, 'icone' => 'bi-exclamation-circle', 'href' => $lista, 'alerta' => true],
            ],
        ];
    }

    private function formularios(User $user): array
    {
        $novos = $this->permissions->applyScope(FormProposal::query()->where('status', 'novo'), $user, 'form-proposals')->count();

        return [
            'titulo' => 'Formulários',
            'icone' => 'bi-envelope-open',
            'cartoes' => [
                ['rotulo' => 'Pedidos novos', 'valor' => $novos, 'icone' => 'bi-envelope-plus', 'href' => route('admin.mobile.form-proposals.index', ['status' => 'novo'])],
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
