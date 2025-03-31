<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Vehicle;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\Expense;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['vehicle', 'client'])->paginate(10);
        return view('sales.index', compact('sales'));
    }
    public function show(Sale $sale)
    {
        $vehicle = $sale->vehicle;

        $this->calculate($sale, $sale->toArray());
        // Buscar despesas associadas ao veículo
        $expenses = Expense::where('vehicle_id', $vehicle->id)->get();



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
        $validatedData = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'client_id' => 'required|exists:clients,id',
            'sale_date' => 'required|date',
            'sale_price' => 'required|numeric',
            'vat_rate' => 'required|in:Sem iva,23%',
            'payment_method' => 'required|in:Financiamento,Pronto pagamento',
            'observation' => 'nullable|string',
        ]);

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
        } else if ($purchaseType == 'Geral') {
            $gross_margin = $sellPrice - $purchasePrice;
            $vat_settle_sale = $sellPrice * (0.23 / (1 + 0.23));
            $vat_deducible_purchase = $purchasePrice * (0.23 / (1 + 0.23));
            $vat_paid = $vat_settle_sale - $vat_deducible_purchase;
            $net_margin = $gross_margin - $vat_paid;
        } else if ($purchaseType == 'Margem') {
            $gross_margin = $sellPrice - $purchasePrice;
            $net_margin = $gross_margin / 1.23;
            $vat_paid = $gross_margin - $net_margin;
        }

        $validatedData['gross_margin'] = $gross_margin - $totalExpenses;
        $validatedData['net_margin'] = $net_margin - $totalExpenses;
        $validatedData['vat_paid'] = $vat_paid;
        $validatedData['vat_deducible_purchase'] = $vat_deducible_purchase;
        $validatedData['vat_settle_sale'] = $vat_settle_sale;
        $validatedData['totalCost'] = $totalCost;
        $validatedData['totalExpenses'] = $totalExpenses;
        $validatedData['vatAmount'] = $vatAmount;
        $validatedData['net_profitability'] = ( $validatedData['net_margin'] / $sellPrice) * 100;
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
}
