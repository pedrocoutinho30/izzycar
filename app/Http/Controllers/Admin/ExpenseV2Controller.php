<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\Partner;
use Illuminate\Http\Request;

/**
 * ExpenseV2Controller
 * 
 * Controlador para gestão de despesas associadas a veículos
 * Despesas podem incluir:
 * - Reparações
 * - Inspeções
 * - Seguros
 * - Transportes
 * - Outros custos operacionais
 */
class ExpenseV2Controller extends Controller
{
    /**
     * Lista todas as despesas com filtros
     */
    public function index(Request $request)
    {
        // Query base com relacionamentos
        $query = Expense::with(['vehicle', 'partner']);

        // Filtro de pesquisa (título ou observações)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('observations', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtro por veículo
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Paginação
        $expenses = $query->orderBy('expense_date', 'desc')
                         ->paginate(12)
                         ->withQueryString();

        // Estatísticas
        $totalExpenses = Expense::sum('amount');
        $thisMonthExpenses = Expense::whereMonth('expense_date', now()->month)
                                   ->whereYear('expense_date', now()->year)
                                   ->sum('amount');
        
        $stats = [
            [
                'title' => 'Total de Despesas',
                'value' => number_format($totalExpenses, 2, ',', '.') . '€',
                'color' => 'primary',
                'icon' => 'bi-receipt'
            ],
            [
                'title' => 'Despesas este Mês',
                'value' => number_format($thisMonthExpenses, 2, ',', '.') . '€',
                'color' => 'warning',
                'icon' => 'bi-calendar-check'
            ],
            [
                'title' => 'Número de Despesas',
                'value' => Expense::count(),
                'color' => 'info',
                'icon' => 'bi-list-ul'
            ]
        ];

        // Lista de veículos para filtro
        $vehicles = Vehicle::select('id', 'reference', 'brand', 'model')->orderBy('reference')->get();

        return view('admin.v2.expenses.index', compact('expenses', 'stats', 'vehicles'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $vehicles = Vehicle::select('id', 'reference', 'brand', 'model')->orderBy('reference')->get();
        $partners = Partner::orderBy('name')->get();
        
        return view('admin.v2.expenses.form', compact('vehicles', 'partners'));
    }

    /**
     * Guarda nova despesa
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'nullable|string|max:255',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'expense_date' => 'required|date',
            'partner_id' => 'nullable|exists:partners,id',
            'observations' => 'nullable|string',
        ]);

        Expense::create($validated);

        return redirect()->route('admin.v2.expenses.index')
                        ->with('success', 'Despesa criada com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $expense = Expense::with(['vehicle', 'partner'])->findOrFail($id);
        $vehicles = Vehicle::select('id', 'reference', 'brand', 'model')->orderBy('reference')->get();
        $partners = Partner::orderBy('name')->get();
        
        return view('admin.v2.expenses.form', compact('expense', 'vehicles', 'partners'));
    }

    /**
     * Atualiza despesa
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'type' => 'nullable|string|max:255',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'expense_date' => 'required|date',
            'partner_id' => 'nullable|exists:partners,id',
            'observations' => 'nullable|string',
        ]);

        $expense->update($validated);

        return redirect()->route('admin.v2.expenses.index')
                        ->with('success', 'Despesa atualizada com sucesso!');
    }

    /**
     * Elimina despesa
     */
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return redirect()->route('admin.v2.expenses.index')
                        ->with('success', 'Despesa eliminada com sucesso!');
    }
}
