<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\Partner;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Obter os valores dos filtros
        $type = $request->input('type');
        $vehicleId = $request->input('vehicle_id');

        // Consultar as despesas com base nos filtros
        $query = Expense::query();

        if ($type) {
            $query->where('type', $type);
        }

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(10);

        // Obter os tipos de despesas e veículos para os filtros
        $expenseTypes = Expense::select('type')->distinct()->pluck('type');
        $vehicles = Vehicle::all();

        return view('expenses.index', compact('expenses', 'expenseTypes', 'vehicles', 'type', 'vehicleId'));
    }
    // Mostrar o formulário de criação de despesa
    public function create()
    {

        //obter params
        if (request()->has('vehicle_id')) {
            $vehicleId = request()->input('vehicle_id'); // Padrão para null se
            $type = 'Veiculo'; // Definir o tipo como 'Veiculo' se vehicle_id estiver presente

        } else {
            $vehicleId = null; // Se não houver vehicle_id, definir como null
            $type = '';
        }
        $vehicles = Vehicle::all();  // Para carregar os veículos no formulário
        $partners = Partner::all();  // Para carregar os parceiros no formulário
        return view('expenses.form', compact('vehicles', 'partners', 'vehicleId', 'type'));
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
        } else {
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

        if (request()->has('vehicle_id')) {
            $vehicleId = request()->input('vehicle_id');
            // Redirecionar para a página de despesas do veículo específico
            return redirect()->route('vehicles.edit', $vehicleId)->with('success', 'Despesa criada com sucesso!');

        }
        return redirect()->route('expenses.index')->with('success', 'Despesa criada com sucesso!');
    }



    // Exibir formulário de edição de despesa
    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $vehicles = Vehicle::all();
        $partners = Partner::all();
        if (request()->has('vehicle_id')) {
            $vehicleId = request()->input('vehicle_id'); // Padrão para null se
            $type = 'Veiculo'; // Definir o tipo como 'Veiculo' se vehicle_id estiver presente
        } else {
            $vehicleId = $expense->vehicle_id; // Se não houver vehicle_id, definir como null
            $type = $expense->type; // Manter o tipo da despesa existente
        }
        return view('expenses.form', compact('expense', 'vehicles', 'partners', 'vehicleId', 'type'));
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
