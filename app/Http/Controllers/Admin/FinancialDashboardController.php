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
        $year  = $request->input('year', now()->year);
        $month = $request->input('month'); // nullable = all months
        $type  = $request->input('type');  // income | expense | null = all

        // ----------------------------------------------------------------
        // Build query
        // ----------------------------------------------------------------
        $query = FinancialMovement::query()
            ->whereYear('movement_date', $year);

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
        // ----------------------------------------------------------------
        $monthlyData = FinancialMovement::selectRaw(
                'MONTH(movement_date) as m,
                 type,
                 SUM(amount_gross) as total'
            )
            ->whereYear('movement_date', $year)
            ->groupBy('m', 'type')
            ->get()
            ->groupBy('m');

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
            'availableYears',
            'year',
            'month',
            'type'
        ));
    }

    /**
     * Seed existing data into financial_movements (called once via a route or artisan).
     */
    public function seedExisting()
    {
        // Sales
        Sale::with(['vehicle', 'client'])->get()->each(function ($sale) {
            \App\Models\FinancialMovement::syncFromSale($sale);
        });

        // Vehicles with purchase price (purchase_date optional)
        Vehicle::whereNotNull('purchase_price')->get()->each(function ($vehicle) {
            \App\Models\FinancialMovement::syncFromVehicle($vehicle);
        });

        // Expenses
        Expense::all()->each(function ($expense) {
            \App\Models\FinancialMovement::syncFromExpense($expense);
        });

        return redirect()->route('admin.v2.financial.dashboard')
            ->with('success', 'Dados financeiros sincronizados com sucesso!');
    }
}
