<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
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
        $client = null;
        if ($request->filled('client_id')) {
            $clientId = $request->integer('client_id');
            $query->where('client_id', $clientId);
            $client = Client::find($clientId);
        }

        // Pesquisa por cliente (nome, email, telefone, NIF) ou viatura. Literal: % e _ contam como texto.
        if ($request->filled('search') && is_string($request->search)) {
            $search = trim($request->search);
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

        return view('admin.mobile.cost-simulators.index', [
            'costSimulators' => $query->paginate(15)->withQueryString(),
            'filterClient' => $client,
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
