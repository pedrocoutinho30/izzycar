<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FormProposal;
use App\Models\Legalization;
use App\Models\Proposal;
use App\Models\Sale;
use App\Models\V3Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Pesquisa global do backoffice — procura por aproximação (LIKE) nos
 * principais campos de Leads, Clientes, Cotações, Formulários,
 * Legalizações, Viaturas e Vendas, devolvendo os resultados agrupados
 * por tipo. Quando o termo tem várias palavras (ex: "Mercedes CLA"),
 * tenta também interpretá-las como marca + modelo em campos separados.
 */
class GlobalSearchController extends Controller
{
    private const LIMIT_DROPDOWN = 8;
    private const LIMIT_FULL_PAGE = 50;

    public function index(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['groups' => []]);
        }

        return response()->json(['groups' => $this->searchAll($term, self::LIMIT_DROPDOWN)]);
    }

    public function results(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $groups = mb_strlen($term) >= 2 ? $this->searchAll($term, self::LIMIT_FULL_PAGE) : [];

        return view('admin.v2.search.results', [
            'term' => $term,
            'groups' => $groups,
            'total' => array_sum(array_column($groups, 'count')),
        ]);
    }

    private function searchAll(string $term, int $limit): array
    {
        return array_values(array_filter([
            $this->searchLeads($term, $limit),
            $this->searchClients($term, $limit),
            $this->searchProposals($term, $limit),
            $this->searchFormProposals($term, $limit),
            $this->searchLegalizations($term, $limit),
            $this->searchVehicles($term, $limit),
            $this->searchSales($term, $limit),
        ]));
    }

    private function searchLeads(string $term, int $limit): ?array
    {
        $leads = Client::where('is_lead', true)
            ->where(fn (Builder $q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('vat_number', 'like', "%{$term}%")
                ->orWhere('identification_number', 'like', "%{$term}%"))
            ->limit($limit)
            ->get();

        return $this->toGroup('Leads', 'bi-funnel', 'primary', $leads, fn (Client $lead) => [
            'title' => $lead->name,
            'subtitle' => implode(' · ', array_filter([$lead->email, $lead->phone])),
            'url' => route('admin.v2.leads.show', $lead->id),
        ]);
    }

    private function searchClients(string $term, int $limit): ?array
    {
        $clients = Client::where('is_lead', false)
            ->where(fn (Builder $q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('vat_number', 'like', "%{$term}%")
                ->orWhere('identification_number', 'like', "%{$term}%"))
            ->limit($limit)
            ->get();

        return $this->toGroup('Clientes', 'bi-people', 'info', $clients, fn (Client $client) => [
            'title' => $client->name,
            'subtitle' => implode(' · ', array_filter([$client->email, $client->phone])),
            'url' => route('admin.v2.clients.show', $client->id),
        ]);
    }

    private function searchProposals(string $term, int $limit): ?array
    {
        $proposals = Proposal::where(function (Builder $q) use ($term) {
            $q->where('brand', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%")
                ->orWhere('version', 'like', "%{$term}%")
                ->orWhere('proposal_code', 'like', "%{$term}%")
                ->orWhereHas('client', fn ($q2) => $q2->where('name', 'like', "%{$term}%"));
            $this->applyFieldComboMatch($q, $term, 'brand', 'model');
        })
            ->with('client')
            ->limit($limit)
            ->get();

        return $this->toGroup('Cotações', 'bi-file-earmark-text', 'warning', $proposals, fn (Proposal $proposal) => [
            'title' => trim(($proposal->brand ?? '') . ' ' . ($proposal->model ?? '')) ?: ('Cotação #' . $proposal->id),
            'subtitle' => $proposal->client->name ?? '—',
            'url' => route('admin.v2.proposals.edit', $proposal->id),
        ]);
    }

    private function searchFormProposals(string $term, int $limit): ?array
    {
        $formProposals = FormProposal::where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('brand', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%");
            $this->applyFieldComboMatch($q, $term, 'brand', 'model');
        })
            ->limit($limit)
            ->get();

        return $this->toGroup('Formulários', 'bi-envelope-open', 'secondary', $formProposals, fn (FormProposal $formProposal) => [
            'title' => $formProposal->name ?: trim(($formProposal->brand ?? '') . ' ' . ($formProposal->model ?? '')),
            'subtitle' => implode(' · ', array_filter([$formProposal->email, $formProposal->phone])),
            'url' => route('admin.v2.form-proposals.show', $formProposal->id),
        ]);
    }

    private function searchLegalizations(string $term, int $limit): ?array
    {
        $legalizations = Legalization::where(function (Builder $q) use ($term) {
            $q->where('marca', 'like', "%{$term}%")
                ->orWhere('modelo', 'like', "%{$term}%")
                ->orWhere('matricula', 'like', "%{$term}%")
                ->orWhere('num_processo_imt', 'like', "%{$term}%")
                ->orWhereHas('client', fn ($q2) => $q2->where('name', 'like', "%{$term}%"));
            $this->applyFieldComboMatch($q, $term, 'marca', 'modelo');
        })
            ->with('client')
            ->limit($limit)
            ->get();

        return $this->toGroup('Legalizações', 'bi-file-earmark-check', 'dark', $legalizations, fn (Legalization $legalization) => [
            'title' => trim(($legalization->marca ?? '') . ' ' . ($legalization->modelo ?? '')) ?: ('Legalização #' . $legalization->id),
            'subtitle' => implode(' · ', array_filter([$legalization->matricula, $legalization->client->name ?? null])),
            'url' => route('admin.legalizations.show', $legalization->id),
        ]);
    }

    private function searchVehicles(string $term, int $limit): ?array
    {
        $vehicles = V3Vehicle::where(function (Builder $q) use ($term) {
            $q->where('brand', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%")
                ->orWhere('reference', 'like', "%{$term}%")
                ->orWhere('vin', 'like', "%{$term}%")
                ->orWhere('registration', 'like', "%{$term}%");
            $this->applyFieldComboMatch($q, $term, 'brand', 'model');
        })
            ->limit($limit)
            ->get();

        return $this->toGroup('Viaturas', 'bi-car-front-fill', 'success', $vehicles, fn (V3Vehicle $vehicle) => [
            'title' => trim(($vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '')),
            'subtitle' => implode(' · ', array_filter([$vehicle->reference, $vehicle->registration])),
            'url' => route('admin.v3.vehicles.edit', $vehicle->id),
        ]);
    }

    private function searchSales(string $term, int $limit): ?array
    {
        $sales = Sale::where(function (Builder $q) use ($term) {
            $q->whereHas('client', fn ($q2) => $q2->where('name', 'like', "%{$term}%"))
                ->orWhereHas('vehicle', function ($q2) use ($term) {
                    $q2->where('brand', 'like', "%{$term}%")->orWhere('model', 'like', "%{$term}%");
                    $this->applyFieldComboMatch($q2, $term, 'brand', 'model');
                })
                ->orWhereHas('v3Vehicle', function ($q2) use ($term) {
                    $q2->where('brand', 'like', "%{$term}%")->orWhere('model', 'like', "%{$term}%");
                    $this->applyFieldComboMatch($q2, $term, 'brand', 'model');
                });
        })
            ->with(['client', 'vehicle', 'v3Vehicle'])
            ->limit($limit)
            ->get();

        return $this->toGroup('Vendas', 'bi-cash-coin', 'danger', $sales, function (Sale $sale) {
            $vehicleLabel = trim((($sale->v3Vehicle ?: $sale->vehicle)?->brand ?? '') . ' ' . (($sale->v3Vehicle ?: $sale->vehicle)?->model ?? ''));

            return [
                'title' => $vehicleLabel ?: ('Venda #' . $sale->id),
                'subtitle' => $sale->client->name ?? '—',
                'url' => route('admin.v2.sales.edit', $sale->id),
            ];
        });
    }

    /**
     * Quando o termo tem várias palavras (ex: "Mercedes CLA"), tenta também
     * interpretá-las como dois campos separados — a primeira palavra num
     * campo e o resto no outro, nas duas ordens possíveis. Isto permite
     * encontrar "Mercedes CLA" mesmo quando "Mercedes" está no campo marca
     * e "CLA" está no campo modelo (nenhum dos dois campos, isolado, contém
     * a frase completa).
     */
    private function applyFieldComboMatch(Builder $query, string $term, string $field1, string $field2): void
    {
        $words = preg_split('/\s+/', trim($term), -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) < 2) {
            return;
        }

        $first = $words[0];
        $rest = implode(' ', array_slice($words, 1));

        $query->orWhere(function (Builder $q) use ($field1, $field2, $first, $rest) {
            $q->where($field1, 'like', "%{$first}%")->where($field2, 'like', "%{$rest}%");
        })->orWhere(function (Builder $q) use ($field1, $field2, $first, $rest) {
            $q->where($field1, 'like', "%{$rest}%")->where($field2, 'like', "%{$first}%");
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
