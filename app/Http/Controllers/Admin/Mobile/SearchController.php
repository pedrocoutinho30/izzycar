<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FormProposal;
use App\Models\Proposal;
use App\Permissions\PermissionService;
use App\Services\GlobalSearchService;
use Illuminate\Http\Request;

/**
 * Pesquisa da versão mobile: Leads, Clientes, Cotações e Pedidos de
 * formulário, com as consultas do desktop (GlobalSearchService). Como no
 * desktop, cada secção só entra se o utilizador puder abrir a página de
 * destino (aqui, a ficha mobile); além disso os resultados ficam no âmbito
 * do utilizador (applyScope), porque as fichas mobile aceitam "próprios".
 * O filtro é feito aqui, por isso vale também com PERMISSIONS_ENFORCE desligado.
 */
class SearchController extends Controller
{
    private const LIMIT = 20;

    /** Secção => [rota de destino, objeto de permissões, método do GlobalSearchService]. */
    private const SECTIONS = [
        'Leads' => ['admin.mobile.leads.show', 'leads', 'leads'],
        'Clientes' => ['admin.mobile.clients.show', 'clients', 'clients'],
        'Cotações' => ['admin.mobile.proposals.show', 'proposals', 'proposals'],
        'Pedidos de formulário' => ['admin.mobile.form-proposals.show', 'form-proposals', 'formProposals'],
    ];

    public function __construct(private GlobalSearchService $search, private PermissionService $permissions)
    {
    }

    public function index(Request $request)
    {
        $term = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $groups = mb_strlen($term) >= GlobalSearchService::MIN_LENGTH ? $this->searchAll($request, $term) : [];

        return view('admin.mobile.search', [
            'term' => $term,
            'groups' => $groups,
            'minLength' => GlobalSearchService::MIN_LENGTH,
        ]);
    }

    private function searchAll(Request $request, string $term): array
    {
        $user = $request->user();
        $groups = [];

        foreach (self::SECTIONS as $label => [$route, $resource, $method]) {
            if (!$this->permissions->canAccessRoute($user, $route)) {
                continue;
            }

            // Literal: no mobile, % e _ escritos pelo utilizador não são curingas.
            $query = $this->search->{$method}($term, true);
            $items = $this->permissions->applyScope($query, $user, $resource)
                ->latest()
                ->limit(self::LIMIT)
                ->get()
                ->map(fn ($record) => $this->item($record, $route));

            if ($items->isNotEmpty()) {
                $groups[] = ['label' => $label, 'items' => $items->all()];
            }
        }

        return $groups;
    }

    private function item(Client|Proposal|FormProposal $record, string $route): array
    {
        $item = match (true) {
            $record instanceof Proposal => [
                'title' => trim(($record->brand ?? '') . ' ' . ($record->model ?? '')) ?: ('Cotação #' . $record->id),
                'subtitle' => implode(' · ', array_filter([$record->proposal_code, $record->client->name ?? null])),
            ],
            $record instanceof FormProposal => [
                'title' => $record->name ?: trim(($record->brand ?? '') . ' ' . ($record->model ?? '')),
                'subtitle' => implode(' · ', array_filter([trim(($record->brand ?? '') . ' ' . ($record->model ?? '')), $record->phone])),
            ],
            default => [
                'title' => $record->name,
                'subtitle' => implode(' · ', array_filter([$record->email, $record->phone])),
            ],
        };

        return $item + ['url' => route($route, $record->id)];
    }
}
