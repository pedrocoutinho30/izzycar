<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CostSimulator;
use App\Permissions\PermissionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Simulador de custos na versão mobile: só consulta das simulações feitas no
 * site (eliminar fica no desktop). O objeto "cost-simulators" não tem âmbito
 * "próprios": quem pode ver, vê todas. A verificação fica aqui para valer
 * também com PERMISSIONS_ENFORCE desligado.
 */
class CostSimulatorController extends Controller
{
    /** Caracter de escape do LIKE literal (MySQL e sqlite). */
    private const ESCAPE = '!';

    public function __construct(private PermissionService $permissions)
    {
    }

    public function index(Request $request)
    {
        abort_unless($this->permissions->allows($request->user(), 'cost-simulators', 'view'), 403);

        $query = CostSimulator::with('client')->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        // Simulações de um cliente (ligação a partir da ficha do cliente/lead).
        // Só valores escalares: client_id[]=1 conta como ausente.
        $clientId = null;
        $rawClientId = $request->query('client_id');
        if (is_scalar($rawClientId) && filter_var($rawClientId, FILTER_VALIDATE_INT) !== false && (int) $rawClientId > 0) {
            $clientId = (int) $rawClientId;
            $query->where('client_id', $clientId);
        }

        // Pesquisa por cliente (nome, email, telefone, NIF) ou viatura. Literal: % e _ contam como texto.
        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : '';
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $this->like($q, 'brand', $search);
                $this->like($q, 'model', $search, 'or');
                $q->orWhereHas('client', function (Builder $c) use ($search) {
                    $c->where(function (Builder $c) use ($search) {
                        $this->like($c, 'name', $search);
                        $this->like($c, 'email', $search, 'or');
                        $this->like($c, 'phone', $search, 'or');
                        $this->like($c, 'vat_number', $search, 'or');
                    });
                });
            });
        }

        // Só as novas (read = falso), como o cartão "Simulações novas" do início. Só escalares.
        $naoLidas = $request->query('nao_lidas');
        $naoLidas = is_scalar($naoLidas) && filter_var($naoLidas, FILTER_VALIDATE_BOOLEAN);
        if ($naoLidas) {
            $query->where('read', false);
        }

        $costSimulators = $query->paginate(15)->withQueryString();

        // O nome só aparece se o cliente filtrado tiver simulações nos resultados;
        // caso contrário o chip é neutro (não revela nomes de clientes sem simulações).
        $filterClient = $clientId !== null
            ? $costSimulators->getCollection()->first()?->client
            : null;

        return view('admin.mobile.cost-simulators.index', [
            'costSimulators' => $costSimulators,
            'filterClientId' => $clientId,
            'filterClient' => $filterClient,
            'search' => $search,
            'naoLidas' => $naoLidas,
        ]);
    }

    public function show(Request $request, $id)
    {
        $costSimulator = CostSimulator::with('client')->findOrFail($id);
        $user = $request->user();
        abort_unless($this->permissions->allows($user, 'cost-simulators', 'view', $costSimulator), 403);

        $client = $costSimulator->client;

        return view('admin.mobile.cost-simulators.show', [
            'costSimulator' => $costSimulator,
            'client' => $client,
            'canViewClient' => $client !== null
                && $this->permissions->allows($user, $client->is_lead ? 'leads' : 'clients', 'view', $client),
        ]);
    }

    private function like(Builder $q, string $column, string $term, string $boolean = 'and'): void
    {
        $escaped = str_replace([self::ESCAPE, '%', '_'], [self::ESCAPE . self::ESCAPE, self::ESCAPE . '%', self::ESCAPE . '_'], $term);
        $wrapped = $q->getQuery()->getGrammar()->wrap($q->qualifyColumn($column));

        $q->whereRaw("{$wrapped} like ? escape '" . self::ESCAPE . "'", ["%{$escaped}%"], $boolean);
    }
}
