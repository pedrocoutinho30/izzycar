<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Vehicle;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\Expense;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $sales = Sale::with(['vehicle', 'client'])->paginate(10);

        $totalNetMargin = Sale::sum('net_margin');

        return view('sales.index', compact('sales', 'totalNetMargin'));
    }
    public function show(Sale $sale)
    {
        $vehicle = $sale->vehicle;

        $this->calculate($sale, $sale->toArray());
        // Buscar despesas associadas ao veículo
        $expenses = Expense::where('vehicle_id', $vehicle->id)->get();
        $vat_deducible_expenses = $expenses->filter(function ($expense) {
            return $expense->vat_rate == '23%';
        })->sum('amount');
        $vat_deducible_expenses = is_numeric($vat_deducible_expenses) ? floatval($vat_deducible_expenses) : 0;
        $sale->vat_deducible_expenses = $vat_deducible_expenses - $vat_deducible_expenses / 1.23;
        return view('sales.show', compact(
            'sale',
            'vehicle',
            'expenses'
        ));
    }



    public function create()
    {
        $vehicles = Vehicle::all();
        $clients = Client::all();
        return view('sales.form', compact('vehicles', 'clients'));
    }

    public function store(Request $request)
    {
        // $expenses = Expense::where('vehicle_id', 3)->get();
        // $this->calculateIvaGeral('43050', '25000', $expenses);
        // dd("Calculo do iva geral feito!");
        $validatedData = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'client_id' => 'required|exists:clients,id',
            'sale_date' => 'required|date',
            'sale_price' => 'required|numeric',
            'vat_rate' => 'required|in:Sem iva,23%',
            'payment_method' => 'required|in:Financiamento,Pronto pagamento',
            'observation' => 'nullable|string',
        ]);


        // Verifica se o veículo já está vendido
        $saleExist = Sale::where('vehicle_id', $validatedData['vehicle_id'])->first();
        if ($saleExist) {
            return redirect()->back()->with('error', 'Este veículo já foi vendido!');
        }
        $sale =  Sale::create($validatedData);
        $this->calculate($sale, $request->all());

        return redirect()->route('sales.show', $sale->id)->with('success', 'Venda criada com sucesso!');
    }


    public function calculate(Sale $sale, $request)
    {

        $vehicle = $sale->vehicle;
        // Buscar despesas associadas ao veículo
        $expenses = Expense::where('vehicle_id', $vehicle->id)->get();
        $totalExpenses = $expenses->sum('amount');

        // Cálculo do gasto total
        $totalCost = $vehicle->purchase_price + $totalExpenses;

        // Cálculo do IVA
        $vatAmount = $request['vat_rate'] === '23%' ? ($totalCost * 0.23) : 0;

        // Calcular o lucro do veículo
        $sellPrice = $request['sale_price'];
        $purchasePrice = $vehicle->purchase_price;
        $purchaseType = $vehicle->purchase_type;
        $gross_margin = 0;
        $vat_paid = 0;
        $net_margin = 0;
        $vat_deducible_purchase = 0;
        $vat_settle_sale = 0;


        if ($purchaseType == 'Sem Iva') {
            $gross_margin = $sellPrice - $purchasePrice;
            $net_margin = $gross_margin;
            $validatedData['net_margin'] = $net_margin - $totalExpenses; ;
        } else if ($purchaseType == 'Geral') {
            // $gross_margin = $sellPrice - $purchasePrice;
            // $vat_settle_sale = $sellPrice - $sellPrice / 1.23; //$sellPrice * (0.23 / (1 + 0.23));
            // $vat_deducible_purchase = $purchasePrice * 0.19; //$purchasePrice * (0.19 / (1 + 0.19));
            // $vat_paid = $vat_settle_sale - $vat_deducible_purchase;
            // $net_margin = $gross_margin - $vat_paid;

            $calculatedData = $this->calculateIvaGeral($sellPrice, $purchasePrice, $expenses);
            $gross_margin = $calculatedData['gross_margin'];
            $net_margin = $calculatedData['net_margin'];
            $vat_paid = $calculatedData['vat_paid'];
            $vat_settle_sale = $calculatedData['vat_settle_sale'];
            $validatedData['vat_deducible_purchase'] = 0;
            $validatedData['net_margin'] = $net_margin;
        } else if ($purchaseType == 'Margem') {
            $gross_margin = $sellPrice - $purchasePrice;
            $net_margin = $gross_margin / 1.23;
            $vat_paid = $gross_margin - $net_margin;
            $validatedData['net_margin'] = $net_margin - $totalExpenses;
        }

        $validatedData['gross_margin'] = $gross_margin - $totalExpenses;
        $validatedData['vat_paid'] = $vat_paid;
        $validatedData['vat_deducible_purchase'] = $vat_deducible_purchase;
        $validatedData['vat_settle_sale'] = $vat_settle_sale;
        $validatedData['totalCost'] = $totalCost;
        $validatedData['totalExpenses'] = $totalExpenses;
        $validatedData['vatAmount'] = $vatAmount;

        $validatedData['net_profitability'] = ($validatedData['net_margin'] / $sellPrice) * 100;
        $validatedData['gross_profitability'] = ($validatedData['gross_margin'] / $sellPrice) * 100;

        $sale->update($validatedData);
    }
    public function edit(Sale $sale)
    {
        $vehicles = Vehicle::all();
        $clients = Client::all();
        return view('sales.form', compact('sale', 'vehicles', 'clients'));
    }

    public function update(Request $request, Sale $sale)
    {
        $validatedData = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'client_id' => 'required|exists:clients,id',
            'sale_date' => 'required|date',
            'sale_price' => 'required|numeric',
            'vat_rate' => 'required|in:Sem iva,23%',
            'payment_method' => 'required|in:Financiamento,Pronto pagamento',
            'observation' => 'nullable|string',
        ]);

        $sale->update($validatedData);
        $this->calculate($sale, $request->all());

        return redirect()->route('sales.show', $sale->id)->with('success', 'Venda atualizada com sucesso!');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();
        return redirect()->route('sales.index')->with('success', 'Venda removida com sucesso!');
    }

    public function calculateIvaGeral($sellPrice, $purchasePrice, $expenses)
    {

        //verificar quais as despesas são dedutíveis e calcular o total de valor dedutivel
        $deductibleExpenses = $expenses->filter(function ($expense) {
            if ($expense->vat_rate == '23%') {
                return true;
            }
            return false;
        });


        $allDeductibleExpenses = $deductibleExpenses->map(function ($expense) {

            $amount = is_numeric($expense->amount) ? floatval($expense->amount) : 0;
            $vatRate = $expense->vat_rate == '23%' ? 23 : 0;
            $valueWithoutVat = $amount / (1 + ($vatRate / 100));
            $vatValue = $amount - $valueWithoutVat;
            return [
                'amount' => number_format($vatValue, 2),
            ];
        });
        $totalDeductibleExpenses = $allDeductibleExpenses->sum('amount'); // iva a deduzir das despesas


        //calcular o IVA a liquidar na venda
        $vat_settle_sale = $sellPrice - ($sellPrice / 1.23);
        $sell_value_without_vat = $sellPrice / 1.23;
        $vat_paid = $vat_settle_sale - $totalDeductibleExpenses;


        $allExpenses = $purchasePrice + $expenses->sum('amount') + $vat_paid; // valor total de despesas (compra + despesas + iva a pagar)
        $net_margin = $sellPrice - $allExpenses; // margem de lucro liquido
        $gross_margin = $sellPrice - $purchasePrice; // margem de lucro bruta




        return [
            'gross_margin' => $gross_margin,
            'net_margin' => $net_margin,
            'vat_paid' => $vat_paid,
            'vat_deducible_purchase' => null,
            'vat_settle_sale' => $vat_settle_sale,
        ];
    }
}
