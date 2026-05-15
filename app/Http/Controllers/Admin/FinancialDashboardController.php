<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialMovement;
use App\Models\Sale;
use App\Models\Vehicle;
use App\Models\Expense;
use Illuminate\Http\Request;

class FinancialDashboardController extends Controller
{
    public function index(Request $request)
    {
        $year      = $request->input('year', now()->year); // 'all' = desde o início
        $month     = $request->input('month');
        $type      = $request->input('type');

        // When year = 'all', month filter makes no sense – ignore it
        if ($year === 'all') {
            $month = null;
        }

        // ----------------------------------------------------------------
        // Build query
        // ----------------------------------------------------------------
        $query = FinancialMovement::query();

        if ($year !== 'all') {
            $query->whereYear('movement_date', $year);
        }

        if ($month) {
            $query->whereMonth('movement_date', $month);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $movements = $query->orderByDesc('movement_date')->get();

        // ----------------------------------------------------------------
        // Totals for current filter period
        // ----------------------------------------------------------------
        $totalIncome   = $movements->where('type', 'income')->sum('amount_gross');
        $totalExpenses = $movements->where('type', 'expense')->sum('amount_gross');
        $netBalance    = $totalIncome - $totalExpenses;

        $totalVatCharged  = $movements->where('type', 'income')->sum('vat_amount');
        $totalVatDeducted = $movements->where('type', 'expense')->sum('vat_amount');
        $netVat           = $totalVatCharged - $totalVatDeducted;

        // ----------------------------------------------------------------
        // Monthly chart data (always for the selected year, ignore month filter)
        // For 'all' years, show per-year totals instead of per-month
        // ----------------------------------------------------------------
        $chartByYear = $year === 'all';
        $monthlyData = FinancialMovement::selectRaw(
                ($chartByYear ? 'YEAR(movement_date) as m' : 'MONTH(movement_date) as m') . ',
                 type,
                 SUM(amount_gross) as total'
            )
            ->when($year !== 'all', fn ($q) => $q->whereYear('movement_date', $year))
            ->groupBy('m', 'type')
            ->orderBy('m')
            ->get()
            ->groupBy('m');

        if ($chartByYear) {
            // Build dynamic year-based chart labels
            $chartLabels   = [];
            $chartIncome   = [];
            $chartExpenses = [];
            foreach ($monthlyData as $y => $rows) {
                $chartLabels[] = (string) $y;
                $chartIncome[]   = 0;
                $chartExpenses[] = 0;
                $idx = count($chartLabels) - 1;
                foreach ($rows as $row) {
                    if ($row->type === 'income') {
                        $chartIncome[$idx] = (float) $row->total;
                    } else {
                        $chartExpenses[$idx] = (float) $row->total;
                    }
                }
            }
        } else {
            $chartLabels   = null; // view will use month names
            $chartIncome   = array_fill(1, 12, 0);
            $chartExpenses = array_fill(1, 12, 0);
            foreach ($monthlyData as $m => $rows) {
                foreach ($rows as $row) {
                    if ($row->type === 'income') {
                        $chartIncome[(int)$m] = (float) $row->total;
                    } else {
                        $chartExpenses[(int)$m] = (float) $row->total;
                    }
                }
            }
        }

        // ----------------------------------------------------------------
        // Available years for filter
        // ----------------------------------------------------------------
        $availableYears = FinancialMovement::selectRaw('YEAR(movement_date) as y')
            ->groupBy('y')
            ->orderByDesc('y')
            ->pluck('y');

        if ($availableYears->isEmpty()) {
            $availableYears = collect([now()->year]);
        }

        // Resultado líquido real: rendimentos líquidos - custos líquidos (sem IVA de lado a lado)
        $netResult = ($totalIncome - $totalVatCharged) - ($totalExpenses - $totalVatDeducted);

        // ----------------------------------------------------------------
        // Stats cards
        // ----------------------------------------------------------------
        $stats = [
            [
                'title' => 'Entradas Brutas',
                'value' => '€ ' . number_format($totalIncome, 2, ',', '.'),
                'icon'  => 'arrow-down-circle-fill',
                'color' => 'success',
            ],
            [
                'title' => 'Saídas Brutas',
                'value' => '€ ' . number_format($totalExpenses, 2, ',', '.'),
                'icon'  => 'arrow-up-circle-fill',
                'color' => 'danger',
            ],
            [
                'title' => 'Resultado Bruto',
                'value' => '€ ' . number_format($netBalance, 2, ',', '.'),
                'icon'  => 'calculator',
                'color' => $netBalance >= 0 ? 'info' : 'warning',
            ],
            [
                'title' => 'Resultado Líquido',
                'value' => '€ ' . number_format($netResult, 2, ',', '.'),
                'icon'  => 'graph-up-arrow',
                'color' => $netResult >= 0 ? 'primary' : 'danger',
            ],
        ];

        return view('admin.v2.financial.dashboard', compact(
            'movements',
            'stats',
            'totalIncome',
            'totalExpenses',
            'netBalance',
            'netResult',
            'totalVatCharged',
            'totalVatDeducted',
            'netVat',
            'chartIncome',
            'chartExpenses',
            'chartLabels',
            'availableYears',
            'year',
            'month',
            'type'
        ));
    }

    /**
     * Seed existing data into financial_movements via Expense records.
     * Safe to run multiple times (uses updateOrCreate).
     */
    public function seedExisting()
    {
        // 1. Remove stale FinancialMovement entries that were created directly from Sale/Vehicle
        //    (they are now replaced by Expense-based entries)
        \App\Models\FinancialMovement::whereIn('movable_type', [Sale::class, Vehicle::class])->delete();

        // 2. Create/update Expense records for all Sales → ExpenseObserver syncs to financial_movements
        Sale::with(['vehicle', 'client'])->get()->each(function ($sale) {
            Expense::syncFromSale($sale);
        });

        // 3. Create/update Expense records for all Vehicles → ExpenseObserver syncs to financial_movements
        Vehicle::whereNotNull('purchase_price')->get()->each(function ($vehicle) {
            Expense::syncFromVehicle($vehicle);
        });

        // 4. Re-sync all Expense records to financial_movements (covers manual entries)
        Expense::all()->each(function ($expense) {
            \App\Models\FinancialMovement::syncFromExpense($expense);
        });

        return redirect()->route('admin.v2.financial.dashboard')
            ->with('success', 'Dados financeiros sincronizados com sucesso!');
    }
}
