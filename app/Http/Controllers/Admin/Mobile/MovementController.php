<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\Expense;
use App\Models\V3Vehicle;
use App\Permissions\PermissionService;
use App\Services\ConvertedProposalService;
use App\Services\MovementService;
use App\Services\PrivateFiles;
use App\Support\DecimalInput;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Movimentos financeiros na versão mobile: lista com os filtros e os totais
 * do desktop, ficha, criar, editar e eliminar. A lógica é a do desktop
 * (MovementService). Exportações, comprovativos (envio/remoção) e gráficos
 * ficam no desktop.
 *
 * O objeto "movements" não tem âmbito "próprios": ver/criar/editar/eliminar
 * verificam-se aqui, para valerem mesmo com PERMISSIONS_ENFORCE desligado.
 * Os movimentos automáticos (source_type: venda, compra de viatura, cotação
 * convertida) são só de consulta, como no desktop — também num PUT/DELETE forjado.
 */
class MovementController extends Controller
{
    /** Caracter de escape do LIKE literal (MySQL e sqlite). */
    private const ESCAPE = '!';

    /** Campos decimais: no telemóvel escrevem-se com vírgula ("800,50"). */
    private const DECIMAL_FIELDS = ['amount', 'vat_rate'];

    public function __construct(private MovementService $movements, private PermissionService $permissions)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($this->permissions->allows($user, 'movements', 'view'), 403);

        $filters = $this->filters($request);
        $hasDates = $filters['date_from'] !== null || $filters['date_to'] !== null;

        // Os filtros do desktop (Expense::scopeFiltrar), exceto a pesquisa, que aqui é literal.
        $query = Expense::with(['v3Vehicle', 'client'])
            ->filtrar(collect($filters)->except(['search', 'convertida'])->all());

        if ($filters['search'] !== null) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $this->like($q, 'title', $search);
                $this->like($q, 'observations', $search, 'or');
            });
        }

        // Lançamentos de uma cotação convertida (ligação a partir da ficha da convertida).
        if ($filters['convertida'] !== null) {
            $query->fromConvertedProposal($filters['convertida']);
        }

        // Totais dos resultados filtrados (todas as páginas, não só a visível).
        $filteredCount = (clone $query)->count();
        // Como os cartões: valor bruto, ou o montante quando o bruto não está preenchido.
        $valorLinha = 'COALESCE(amount_gross, amount)';
        $filteredIncome = (float) (clone $query)->where('movement_type', 'income')->sum(DB::raw($valorLinha));
        $filteredExpenses = (float) (clone $query)->where('movement_type', 'expense')->sum(DB::raw($valorLinha));

        $movements = $query->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->appends(array_filter($filters, fn ($v) => $v !== null));

        return view('admin.mobile.movements.index', [
            'movements' => $movements,
            'filters' => $filters,
            'hasDates' => $hasDates,
            // Os mesmos totais e saldos dos cartões do desktop (ano escolhido; com datas, de sempre).
            'summary' => $this->movements->summary($filters['year'], $hasDates),
            'filteredCount' => $filteredCount,
            'filteredIncome' => $filteredIncome,
            'filteredExpenses' => $filteredExpenses,
            'availableYears' => $this->movements->availableYears(),
            'vehicles' => V3Vehicle::select('id', 'reference', 'brand', 'model')->orderBy('reference')->get(),
            'filterClient' => $this->filterClient($user, $filters['client_id']),
            'canCreate' => $this->permissions->allows($user, 'movements', 'create'),
        ]);
    }

    /** Nome do cliente do filtro só para quem pode ver essa ficha (senão, chip neutro "Cliente #N"). */
    private function filterClient($user, ?int $id): ?Client
    {
        if ($id === null) {
            return null;
        }

        $client = Client::select('id', 'name', 'is_lead')->find($id);

        return $client && $this->permissions->allows($user, $client->is_lead ? 'leads' : 'clients', 'view', $client) ? $client : null;
    }

    public function show(Request $request, $id)
    {
        $movement = Expense::with(['v3Vehicle', 'client', 'partner', 'legalization'])->findOrFail($id);
        $user = $request->user();
        abort_unless($this->permissions->allows($user, 'movements', 'view', $movement), 403);

        $automatic = $this->movements->isAutomatic($movement);
        $source = $this->movements->source($movement);

        // Cotação convertida de origem: só se o utilizador a pode abrir no mobile.
        $converted = $source && $source[0] === 'converted' ? ConvertedProposal::with('client')->find($source[1]) : null;
        $canViewConverted = $converted !== null && app(ConvertedProposalService::class)->canView($user, $converted);

        $client = $movement->client;

        return view('admin.mobile.movements.show', [
            'movement' => $movement,
            'automatic' => $automatic,
            'source' => $source,
            'converted' => $canViewConverted ? $converted : null,
            'sourceDesktopUrl' => $this->sourceDesktopUrl($user, $source),
            'canUpdate' => ! $automatic && $this->permissions->allows($user, 'movements', 'update', $movement),
            'canDelete' => ! $automatic && $this->permissions->allows($user, 'movements', 'delete', $movement),
            'canViewClient' => $client !== null && $this->permissions->allows($user, $client->is_lead ? 'leads' : 'clients', 'view', $client),
            // Comprovativo: servido pela rota autorizada de ficheiros privados (admin.v2.files.show).
            'attachmentUrl' => PrivateFiles::url($movement->attachment_path),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($this->permissions->allows($request->user(), 'movements', 'create'), 403);

        // Valores por omissão do formulário do desktop.
        $movement = new Expense(['movement_type' => 'expense', 'status' => 'paid', 'vat_rate' => 23, 'expense_date' => now()->toDateString()]);

        return view('admin.mobile.movements.form', ['movement' => $movement] + $this->movements->formOptions());
    }

    public function store(Request $request)
    {
        abort_unless($this->permissions->allows($request->user(), 'movements', 'create'), 403);

        $this->normalizeDecimals($request);
        // Sem comprovativo no mobile: só os campos validados chegam ao modelo.
        $data = $request->validate($this->movements->rules(false));

        $movement = $this->movements->create($data);

        return redirect()->route('admin.mobile.movements.show', $movement->id)
            ->with('success', 'Movimento criado com sucesso!');
    }

    public function edit(Request $request, $id)
    {
        $movement = Expense::findOrFail($id);
        abort_unless($this->permissions->allows($request->user(), 'movements', 'update', $movement), 403);

        if ($this->movements->isAutomatic($movement)) {
            return redirect()->route('admin.mobile.movements.show', $movement->id)
                ->with('error', 'Este movimento foi gerado automaticamente e não pode ser editado aqui. Edite a venda, o veículo ou a cotação convertida correspondente.');
        }

        return view('admin.mobile.movements.form', ['movement' => $movement] + $this->movements->formOptions());
    }

    public function update(Request $request, $id)
    {
        $movement = Expense::findOrFail($id);
        abort_unless($this->permissions->allows($request->user(), 'movements', 'update', $movement), 403);
        // Sem botão para isto na ficha: um pedido destes é forjado.
        abort_if($this->movements->isAutomatic($movement), 403, 'Este movimento foi gerado automaticamente e não pode ser editado aqui.');

        $this->normalizeDecimals($request);
        $data = $request->validate($this->movements->rules(false));

        // O comprovativo existente mantém-se (gere-se no desktop).
        $this->movements->update($movement, $data);

        return redirect()->route('admin.mobile.movements.show', $movement->id)
            ->with('success', 'Movimento atualizado com sucesso!');
    }

    public function destroy(Request $request, $id)
    {
        $movement = Expense::findOrFail($id);
        abort_unless($this->permissions->allows($request->user(), 'movements', 'delete', $movement), 403);
        abort_if($this->movements->isAutomatic($movement), 403, 'Este movimento foi gerado automaticamente e não pode ser eliminado aqui. Elimine a venda ou o veículo correspondente.');

        $this->movements->delete($movement);

        return redirect()->route('admin.mobile.movements.index')
            ->with('success', 'Movimento eliminado com sucesso!');
    }

    /**
     * Filtros do pedido, já limpos: só valores escalares e conhecidos (search[]=x,
     * year=abc ou datas inválidas contam como ausentes, nunca dão erro).
     *
     * @return array{search: ?string, movement_type: ?string, category: ?string, status: ?string, v3_vehicle_id: ?int, client_id: ?int, convertida: ?int, year: string, date_from: ?string, date_to: ?string}
     */
    private function filters(Request $request): array
    {
        $texto = function (string $chave) use ($request): ?string {
            $valor = $request->query($chave);

            return is_string($valor) && trim($valor) !== '' ? trim($valor) : null;
        };
        $opcao = fn (string $chave, array $lista) => ($v = $texto($chave)) !== null && array_key_exists($v, $lista) ? $v : null;
        $id = function (string $chave) use ($texto): ?int {
            $v = $texto($chave);

            return $v !== null && filter_var($v, FILTER_VALIDATE_INT) !== false && (int) $v > 0 ? (int) $v : null;
        };
        $data = function (string $chave) use ($texto): ?string {
            $v = $texto($chave);
            $d = $v !== null ? DateTime::createFromFormat('!Y-m-d', $v) : false;

            return $d && $d->format('Y-m-d') === $v ? $v : null;
        };

        // Ano: como no desktop, o corrente por omissão; "all" = desde o início.
        $year = $texto('year');
        if ($year !== 'all' && ($year === null || preg_match('/^\d{4}$/', $year) !== 1)) {
            $year = (string) now()->year;
        }

        $search = $texto('search');

        return [
            'search' => $search !== null ? mb_substr($search, 0, 100) : null,
            'movement_type' => $opcao('movement_type', Expense::movementTypes()),
            'category' => $opcao('category', Expense::categories()),
            'status' => $opcao('status', Expense::statuses()),
            'v3_vehicle_id' => $id('v3_vehicle_id'),
            'client_id' => $id('client_id'),
            'convertida' => $id('convertida'),
            'year' => $year,
            'date_from' => $data('date_from'),
            'date_to' => $data('date_to'),
        ];
    }

    /** "800,50" e "1.200,50" passam a 800.50 e 1200.50 antes de validar. */
    private function normalizeDecimals(Request $request): void
    {
        $normalizados = [];
        foreach (self::DECIMAL_FIELDS as $campo) {
            if ($request->exists($campo) && is_string($request->input($campo))) {
                $normalizados[$campo] = DecimalInput::normalize($request->input($campo));
            }
        }
        $request->merge($normalizados);
    }

    /** Página da origem no desktop (venda, viatura, convertida), se o utilizador a pode abrir. */
    private function sourceDesktopUrl($user, ?array $source): ?string
    {
        if ($source === null) {
            return null;
        }

        [$route, $parameters] = match ($source[0]) {
            'converted' => ['admin.v2.converted-proposals.edit', [$source[1]]],
            'sale' => ['admin.v2.sales.edit', [$source[1]]],
            'v3_vehicle' => ['admin.v3.vehicles.edit', [$source[1]]],
            'vehicle' => ['admin.v3.vehicles.index', []],
        };

        return $this->permissions->canAccessRoute($user, $route) ? route($route, $parameters) : null;
    }

    private function like(Builder $q, string $column, string $term, string $boolean = 'and'): void
    {
        $escaped = str_replace([self::ESCAPE, '%', '_'], [self::ESCAPE . self::ESCAPE, self::ESCAPE . '%', self::ESCAPE . '_'], $term);
        $wrapped = $q->getQuery()->getGrammar()->wrap($q->qualifyColumn($column));

        $q->whereRaw("{$wrapped} like ? escape '" . self::ESCAPE . "'", ["%{$escaped}%"], $boolean);
    }
}
