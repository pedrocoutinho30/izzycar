<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Expense;
use App\Models\V3Vehicle;
use App\Services\MovementService;
use Illuminate\Http\Request;

/**
 * Movimentos financeiros no desktop. As regras (validação, IVA, comprovativo,
 * totais e saldos) estão no MovementService, partilhado com a versão mobile.
 */
class MovementV2Controller extends Controller
{
    public function __construct(private MovementService $movements)
    {
    }

    // ── Index ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // Year filter: default = current year, 'all' = no restriction
        $year = $request->input('year') ?: (string) now()->year;

        // Filtros partilhados com a exportação XLSX (Expense::scopeFiltrar)
        $query = Expense::with(['v3Vehicle', 'partner', 'client'])
            ->filtrar($request->only([
                'search', 'movement_type', 'category', 'status', 'v3_vehicle_id', 'client_id', 'date_from', 'date_to',
            ]) + ['year' => $year]);

        $movements = $query->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Totais e saldos (MovementService::summary, partilhado com o mobile)
        $summary = $this->movements->summary($year, $request->filled('date_from') || $request->filled('date_to'));
        $totalExpenses       = $summary['totalExpenses'];
        $totalIncome         = $summary['totalIncome'];
        $runningBalances     = $summary['runningBalances'];
        $runningBalancesReal = $summary['runningBalancesReal'];
        $currentSaldo        = $summary['saldo'];
        $currentSaldoReal    = $summary['saldoReal'];
        $initialBalance      = MovementService::INITIAL_BALANCE;

        // Available years for the year picker
        $availableYears = $this->movements->availableYears();

        $stats = [
            [
                'title' => 'Total Saídas',
                'value' => number_format($totalExpenses, 2, ',', '.') . '€',
                'color' => 'danger',
                'icon'  => 'bi-arrow-up-circle',
            ],
            [
                'title' => 'Total Entradas',
                'value' => number_format($totalIncome, 2, ',', '.') . '€',
                'color' => 'success',
                'icon'  => 'bi-arrow-down-circle',
            ],
            [
                'title' => 'Saldo (c/ Pendentes)',
                'value' => number_format($currentSaldo, 2, ',', '.') . '€',
                'color' => $currentSaldo >= 0 ? 'primary' : 'danger',
                'icon'  => 'bi-wallet2',
            ],
            [
                'title' => 'Saldo Real (só Pagos)',
                'value' => number_format($currentSaldoReal, 2, ',', '.') . '€',
                'color' => $currentSaldoReal >= 0 ? 'success' : 'danger',
                'icon'  => 'bi-check-circle',
            ],
        ];

        $vehicles = V3Vehicle::select('id', 'reference', 'brand', 'model')->orderBy('reference')->get();
        $clients  = Client::select('id', 'name')->orderBy('name')->get();

        return view('admin.v2.movements.index', compact(
            'movements', 'stats', 'vehicles', 'clients', 'availableYears', 'year',
            'runningBalances', 'runningBalancesReal', 'currentSaldo', 'currentSaldoReal', 'initialBalance'
        ));
    }

    // ── Create ─────────────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.v2.movements.form', $this->movements->formOptions());
    }

    // ── Store ──────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate($this->movements->rules());

        $this->movements->create($validated, $request->hasFile('attachment') ? $request->file('attachment') : null);

        return redirect()->route('admin.v2.movements.index')
            ->with('success', 'Movimento criado com sucesso!');
    }

    // ── Edit ───────────────────────────────────────────────────────────────

    public function edit($id)
    {
        $movement = Expense::with(['v3Vehicle', 'partner', 'client'])->findOrFail($id);

        if ($this->movements->isAutomatic($movement)) {
            return redirect()->route('admin.v2.movements.index')
                ->with('error', 'Este movimento foi gerado automaticamente e não pode ser editado aqui. Edite a venda, o veículo ou a cotação convertida correspondente.');
        }

        return view('admin.v2.movements.form', ['movement' => $movement] + $this->movements->formOptions());
    }

    // ── Update ─────────────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        $movement = Expense::findOrFail($id);

        if ($this->movements->isAutomatic($movement)) {
            return redirect()->route('admin.v2.movements.index')
                ->with('error', 'Este movimento foi gerado automaticamente e não pode ser editado aqui.');
        }

        $validated = $request->validate($this->movements->rules());

        $this->movements->update(
            $movement,
            $validated,
            $request->hasFile('attachment') ? $request->file('attachment') : null,
            $request->boolean('remove_attachment'),
        );

        return redirect()->route('admin.v2.movements.index')
            ->with('success', 'Movimento atualizado com sucesso!');
    }

    // ── Destroy ────────────────────────────────────────────────────────────

    public function destroy($id)
    {
        $movement = Expense::findOrFail($id);

        if ($this->movements->isAutomatic($movement)) {
            return redirect()->route('admin.v2.movements.index')
                ->with('error', 'Este movimento foi gerado automaticamente e não pode ser eliminado aqui. Elimine a venda ou o veículo correspondente.');
        }

        $this->movements->delete($movement);

        return redirect()->route('admin.v2.movements.index')
            ->with('success', 'Movimento eliminado com sucesso!');
    }
}
