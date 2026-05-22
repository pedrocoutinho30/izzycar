<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Partner;
use App\Models\V3Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MovementV2Controller extends Controller
{
    // ── Index ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // Year filter: default = current year, 'all' = no restriction
        $year = $request->input('year', (string) now()->year);

        $query = Expense::with(['v3Vehicle', 'partner', 'client']);

        // Only apply year filter when date_from/date_to are not set
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            if ($year !== 'all') {
                $query->whereYear('expense_date', $year);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('observations', 'like', "%{$search}%");
            });
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('v3_vehicle_id')) {
            $query->where('v3_vehicle_id', $request->v3_vehicle_id);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        $movements = $query->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Stats — scoped to same year filter for coherence
        $statsBase = Expense::query();
        if ($year !== 'all' && !$request->filled('date_from') && !$request->filled('date_to')) {
            $statsBase->whereYear('expense_date', $year);
        }
        $totalExpenses = (clone $statsBase)->where('movement_type', 'expense')->sum('amount_gross');
        $totalIncome   = (clone $statsBase)->where('movement_type', 'income')->sum('amount_gross');
        $pending       = (clone $statsBase)->where('status', 'pending')->sum('amount_gross');

        // ── Saldo (running balance) ────────────────────────────────────────
        $initialBalance = 15757.28;

        // Load ALL year movements sorted ascending for cumulative balance calculation
        $allYearMovements = Expense::query()
            ->when($year !== 'all', fn ($q) => $q->whereYear('expense_date', $year))
            ->orderBy('expense_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'movement_type', 'amount_gross', 'amount', 'status']);

        $runningBalances     = []; // inclui pendentes
        $runningBalancesReal = []; // só pagos
        $runningBalance      = $initialBalance;
        $runningBalanceReal  = $initialBalance;

        foreach ($allYearMovements as $m) {
            $amount = (float) ($m->amount_gross ?? $m->amount ?? 0);
            $isPaid = $m->status !== 'pending';

            if ($m->movement_type === 'income') {
                $runningBalance += $amount;
                if ($isPaid) $runningBalanceReal += $amount;
            } else {
                $runningBalance -= $amount;
                if ($isPaid) $runningBalanceReal -= $amount;
            }
            $runningBalances[$m->id]     = $runningBalance;
            $runningBalancesReal[$m->id] = $isPaid ? $runningBalanceReal : null;
        }
        $currentSaldo     = $runningBalance;
        $currentSaldoReal = $runningBalanceReal;

        // Available years for the year picker
        $availableYears = Expense::selectRaw('YEAR(expense_date) as y')
            ->whereNotNull('expense_date')
            ->groupBy('y')
            ->orderByDesc('y')
            ->pluck('y');
        if ($availableYears->isEmpty()) {
            $availableYears = collect([now()->year]);
        }

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
        $vehicles = V3Vehicle::select('id', 'reference', 'brand', 'model')->orderBy('reference')->get();
        $partners = Partner::orderBy('name')->get();
        $clients  = Client::select('id', 'name')->orderBy('name')->get();

        return view('admin.v2.movements.form', compact('vehicles', 'partners', 'clients'));
    }

    // ── Store ──────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $this->validateMovement($request);

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')
                ->store('movements/attachments', 'public');
        }
        unset($validated['attachment']);

        $this->computeVatFields($validated);

        Expense::create($validated);

        return redirect()->route('admin.v2.movements.index')
            ->with('success', 'Movimento criado com sucesso!');
    }

    // ── Edit ───────────────────────────────────────────────────────────────

    public function edit($id)
    {
        $movement = Expense::with(['v3Vehicle', 'partner', 'client'])->findOrFail($id);

        if ($movement->source_type) {
            return redirect()->route('admin.v2.movements.index')
                ->with('error', 'Este movimento foi gerado automaticamente e não pode ser editado aqui. Edite a venda ou o veículo correspondente.');
        }

        $vehicles = V3Vehicle::select('id', 'reference', 'brand', 'model')->orderBy('reference')->get();
        $partners = Partner::orderBy('name')->get();
        $clients  = Client::select('id', 'name')->orderBy('name')->get();

        return view('admin.v2.movements.form', compact('movement', 'vehicles', 'partners', 'clients'));
    }

    // ── Update ─────────────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        $movement = Expense::findOrFail($id);

        if ($movement->source_type) {
            return redirect()->route('admin.v2.movements.index')
                ->with('error', 'Este movimento foi gerado automaticamente e não pode ser editado aqui.');
        }

        $validated = $this->validateMovement($request, $id);

        if ($request->hasFile('attachment')) {
            if ($movement->attachment_path) {
                Storage::disk('public')->delete($movement->attachment_path);
            }
            $validated['attachment_path'] = $request->file('attachment')
                ->store('movements/attachments', 'public');
        } elseif ($request->boolean('remove_attachment')) {
            if ($movement->attachment_path) {
                Storage::disk('public')->delete($movement->attachment_path);
            }
            $validated['attachment_path'] = null;
        }

        unset($validated['attachment'], $validated['remove_attachment']);

        $this->computeVatFields($validated);

        $movement->update($validated);

        return redirect()->route('admin.v2.movements.index')
            ->with('success', 'Movimento atualizado com sucesso!');
    }

    // ── Destroy ────────────────────────────────────────────────────────────

    public function destroy($id)
    {
        $movement = Expense::findOrFail($id);

        if ($movement->source_type) {
            return redirect()->route('admin.v2.movements.index')
                ->with('error', 'Este movimento foi gerado automaticamente e não pode ser eliminado aqui. Elimine a venda ou o veículo correspondente.');
        }

        if ($movement->attachment_path) {
            Storage::disk('public')->delete($movement->attachment_path);
        }

        $movement->delete();

        return redirect()->route('admin.v2.movements.index')
            ->with('success', 'Movimento eliminado com sucesso!');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function validateMovement(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'movement_type'  => 'required|string|max:50',
            'category'       => 'nullable|string|max:100',
            'v3_vehicle_id'  => 'nullable|exists:v3_vehicles,id',
            'client_id'      => 'nullable|exists:clients,id',
            'partner_id'     => 'nullable|exists:partners,id',
            'title'          => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0',
            'vat_rate'       => 'nullable|numeric|min:0|max:100',
            'expense_date'   => 'required|date',
            'observations'   => 'nullable|string',
            'attachment'     => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            'remove_attachment' => 'nullable|boolean',
            'payment_method' => 'nullable|string|max:50',
            'status'         => 'nullable|string|max:50',
        ]);
    }

    /**
     * Auto-compute amount_gross, vat_amount, amount_net from amount + vat_rate.
     */
    private function computeVatFields(array &$data): void
    {
        $gross   = (float) ($data['amount'] ?? 0);
        $vatRate = (float) ($data['vat_rate'] ?? 0);
        $vatAmt  = $vatRate > 0 ? round($gross - ($gross / (1 + $vatRate / 100)), 2) : 0.0;

        $data['amount_gross'] = $gross;
        $data['vat_amount']   = $vatAmt;
        $data['amount_net']   = round($gross - $vatAmt, 2);
    }
}
