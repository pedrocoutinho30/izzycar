<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FormProposal;
use App\Models\Legalization;
use App\Models\Proposal;
use App\Models\Sale;
use App\Models\V3Vehicle;
use App\Permissions\PermissionService;
use App\Services\GlobalSearchService;
use Illuminate\Http\Request;

/**
 * Pesquisa global do backoffice — procura por aproximação (LIKE) nos
 * principais campos de Leads, Clientes, Cotações, Formulários,
 * Legalizações, Viaturas e Vendas, devolvendo os resultados agrupados
 * por tipo. As consultas estão no GlobalSearchService (partilhado com a
 * pesquisa mobile); aqui ficam as secções, os limites e os links.
 */
class GlobalSearchController extends Controller
{
    private const LIMIT_DROPDOWN = 8;
    private const LIMIT_FULL_PAGE = 50;

    public function __construct(private GlobalSearchService $search)
    {
    }

    public function index(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < GlobalSearchService::MIN_LENGTH) {
            return response()->json(['groups' => []]);
        }

        return response()->json(['groups' => $this->searchAll($term, self::LIMIT_DROPDOWN)]);
    }

    public function results(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $groups = mb_strlen($term) >= GlobalSearchService::MIN_LENGTH ? $this->searchAll($term, self::LIMIT_FULL_PAGE) : [];

        return view('admin.v2.search.results', [
            'term' => $term,
            'groups' => $groups,
            'total' => array_sum(array_column($groups, 'count')),
        ]);
    }

    /**
     * Cada secção só entra se o utilizador puder abrir os resultados (a rota
     * de destino) — as permissões e o âmbito ficam garantidos pela mesma
     * regra do middleware.
     */
    private const SECTIONS = [
        'searchLeads' => 'admin.v2.leads.show',
        'searchClients' => 'admin.v2.clients.show',
        'searchProposals' => 'admin.v2.proposals.edit',
        'searchFormProposals' => 'admin.v2.form-proposals.show',
        'searchLegalizations' => 'admin.legalizations.show',
        'searchVehicles' => 'admin.v3.vehicles.edit',
        'searchSales' => 'admin.v2.sales.edit',
    ];

    private function searchAll(string $term, int $limit): array
    {
        $user = auth()->user();
        $permissions = app(PermissionService::class);

        return array_values(array_filter(array_map(
            fn (string $method, string $route) => $permissions->canAccessRoute($user, $route) ? $this->{$method}($term, $limit) : null,
            array_keys(self::SECTIONS),
            self::SECTIONS,
        )));
    }

    private function searchLeads(string $term, int $limit): ?array
    {
        $leads = $this->search->leads($term)->limit($limit)->get();

        return $this->toGroup('Leads', 'bi-funnel', 'primary', $leads, fn (Client $lead) => [
            'title' => $lead->name,
            'subtitle' => implode(' · ', array_filter([$lead->email, $lead->phone])),
            'url' => route('admin.v2.leads.show', $lead->id),
        ]);
    }

    private function searchClients(string $term, int $limit): ?array
    {
        $clients = $this->search->clients($term)->limit($limit)->get();

        return $this->toGroup('Clientes', 'bi-people', 'info', $clients, fn (Client $client) => [
            'title' => $client->name,
            'subtitle' => implode(' · ', array_filter([$client->email, $client->phone])),
            'url' => route('admin.v2.clients.show', $client->id),
        ]);
    }

    private function searchProposals(string $term, int $limit): ?array
    {
        $proposals = $this->search->proposals($term)->limit($limit)->get();

        return $this->toGroup('Cotações', 'bi-file-earmark-text', 'warning', $proposals, fn (Proposal $proposal) => [
            'title' => trim(($proposal->brand ?? '') . ' ' . ($proposal->model ?? '')) ?: ('Cotação #' . $proposal->id),
            'subtitle' => $proposal->client->name ?? '—',
            'url' => route('admin.v2.proposals.edit', $proposal->id),
        ]);
    }

    private function searchFormProposals(string $term, int $limit): ?array
    {
        $formProposals = $this->search->formProposals($term)->limit($limit)->get();

        return $this->toGroup('Formulários', 'bi-envelope-open', 'secondary', $formProposals, fn (FormProposal $formProposal) => [
            'title' => $formProposal->name ?: trim(($formProposal->brand ?? '') . ' ' . ($formProposal->model ?? '')),
            'subtitle' => implode(' · ', array_filter([$formProposal->email, $formProposal->phone])),
            'url' => route('admin.v2.form-proposals.show', $formProposal->id),
        ]);
    }

    private function searchLegalizations(string $term, int $limit): ?array
    {
        $legalizations = $this->search->legalizations($term)->limit($limit)->get();

        return $this->toGroup('Legalizações', 'bi-file-earmark-check', 'dark', $legalizations, fn (Legalization $legalization) => [
            'title' => trim(($legalization->marca ?? '') . ' ' . ($legalization->modelo ?? '')) ?: ('Legalização #' . $legalization->id),
            'subtitle' => implode(' · ', array_filter([$legalization->matricula, $legalization->client->name ?? null])),
            'url' => route('admin.legalizations.show', $legalization->id),
        ]);
    }

    private function searchVehicles(string $term, int $limit): ?array
    {
        $vehicles = $this->search->vehicles($term)->limit($limit)->get();

        return $this->toGroup('Viaturas', 'bi-car-front-fill', 'success', $vehicles, fn (V3Vehicle $vehicle) => [
            'title' => trim(($vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '')),
            'subtitle' => implode(' · ', array_filter([$vehicle->reference, $vehicle->registration])),
            'url' => route('admin.v3.vehicles.edit', $vehicle->id),
        ]);
    }

    private function searchSales(string $term, int $limit): ?array
    {
        $sales = $this->search->sales($term)->limit($limit)->get();

        return $this->toGroup('Vendas', 'bi-cash-coin', 'danger', $sales, function (Sale $sale) {
            $vehicleLabel = trim((($sale->v3Vehicle ?: $sale->vehicle)?->brand ?? '') . ' ' . (($sale->v3Vehicle ?: $sale->vehicle)?->model ?? ''));

            return [
                'title' => $vehicleLabel ?: ('Venda #' . $sale->id),
                'subtitle' => $sale->client->name ?? '—',
                'url' => route('admin.v2.sales.edit', $sale->id),
            ];
        });
    }

    private function toGroup(string $label, string $icon, string $color, $items, callable $map): ?array
    {
        if ($items->isEmpty()) {
            return null;
        }

        return [
            'label' => $label,
            'icon' => $icon,
            'color' => $color,
            'count' => $items->count(),
            'items' => $items->map($map)->values()->all(),
        ];
    }
}
