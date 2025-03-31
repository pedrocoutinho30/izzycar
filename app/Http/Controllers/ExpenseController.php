<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\Partner;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        // Pagina as despesas para mostrar um número limitado por página
        $expenses = Expense::with('vehicle', 'partner')->paginate(10); // Você pode alterar o número de despesas por página

        return view('expenses.index', compact('expenses'));
    }
    // Mostrar o formulário de criação de despesa
    public function create()
    {
        $vehicles = Vehicle::all();  // Para carregar os veículos no formulário
        $partners = Partner::all();  // Para carregar os parceiros no formulário
        return view('expenses.form', compact('vehicles', 'partners'));
    }

    // Armazenar a despesa
    public function store(Request $request)
    {
        if ($request->type == 'Veiculo') {
            $request->validate([
                'type' => 'required|in:Empresa,Veiculo,Outros',
                'vehicle_id' => 'exists:vehicles,id',
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'vat_rate' => 'required|in:sem iva,6%,19%,23%,25%',
                'expense_date' => 'required|date',
                'partner_id' => 'nullable|exists:partners,id',
                'observations' => 'nullable|string',
            ]);
        }else {
            $request->validate([
                'type' => 'required|in:Empresa,Veiculo,Outros',
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'vat_rate' => 'required|in:sem iva,6%,19%,23%,25%',
                'expense_date' => 'required|date',
                'partner_id' => 'nullable|exists:partners,id',
                'observations' => 'nullable|string',
            ]);
        }
        $expense = Expense::create([
            'type' => $request->type,
            'vehicle_id' => $request->type == 'Veiculo' ? $request->vehicle_id : null,
            'title' => $request->title,
            'amount' => $request->amount,
            'vat_rate' => $request->vat_rate,
            'expense_date' => $request->expense_date,
            'partner_id' => $request->partner_id,
            'observations' => $request->observations,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Despesa criada com sucesso!');
    }



    // Exibir formulário de edição de despesa
    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $vehicles = Vehicle::all();
        $partners = Partner::all();
        return view('expenses.form', compact('expense', 'vehicles', 'partners'));
    }

    // Atualizar despesa
    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:Empresa,Veiculo,Outros',
            'vehicle_id' => 'required_if:type,Veiculo|exists:vehicles,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'vat_rate' => 'required|in:sem iva,6%,19%,23%,25%',
            'expense_date' => 'required|date',
            'partner_id' => 'nullable|exists:partners,id',
            'observations' => 'nullable|string',
        ]);

        $expense = Expense::findOrFail($id);
        $expense->update([
            'type' => $request->type,
            'vehicle_id' => $request->type == 'Veiculo' ? $request->vehicle_id : null,
            'title' => $request->title,
            'amount' => $request->amount,
            'vat_rate' => $request->vat_rate,
            'expense_date' => $request->expense_date,
            'partner_id' => $request->partner_id,
            'observations' => $request->observations,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Despesa atualizada com sucesso!');
    }

    // Excluir despesa
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Despesa excluída com sucesso!');
    }
}
