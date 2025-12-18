<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Vehicle;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\Expense;

/**
 * SaleV2Controller
 * 
 * Controlador para gestão de vendas
 * Regista a venda de veículos a clientes
 * Inclui informações sobre:
 * - Preço de venda
 * - Método de pagamento
 * - Margens (bruta e líquida)
 * - IVA
 * - Rentabilidade
 */
class SaleV2Controller extends Controller
{
    /**
     * Lista todas as vendas com filtros
     */
    public function index(Request $request)
    {
        // Query base com relacionamentos
        $query = Sale::with(['vehicle', 'client']);

        // Filtro de pesquisa (cliente ou veículo)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('vehicle', function ($q) use ($search) {
                        $q->where('reference', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
            });
        }

        // Filtro por período
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        // Filtro por método de pagamento
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Paginação
        $sales = $query->orderBy('sale_date', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Estatísticas
        $totalSales = Sale::sum('sale_price');
        $thisMonthSales = Sale::whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->sum('sale_price');
        $thisMonthCount = Sale::whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->count();
        $avgMargin = Sale::whereNotNull('gross_margin')->avg('gross_margin');

        $stats = [
            [
                'title' => 'Total Vendido',
                'value' => number_format($totalSales, 0, ',', '.') . '€',
                'color' => 'primary',
                'icon' => 'bi-cash-coin'
            ],
            [
                'title' => 'Vendas este Mês',
                'value' => number_format($thisMonthSales, 0, ',', '.') . '€',
                'color' => 'success',
                'icon' => 'bi-calendar-check'
            ],
            [
                'title' => 'Nº Vendas este Mês',
                'value' => $thisMonthCount,
                'color' => 'info',
                'icon' => 'bi-cart-check'
            ],
            [
                'title' => 'Margem Média',
                'value' => number_format($avgMargin ?? 0, 1, ',', '.') . '€',
                'color' => 'warning',
                'icon' => 'bi-graph-up'
            ]
        ];

        return view('admin.v2.sales.index', compact('sales', 'stats'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        // Apenas veículos que não foram vendidos
        $vehicles = Vehicle::doesntHave('sale')
            ->select('id', 'reference', 'brand', 'model', 'year')
            ->orderBy('reference')
            ->get();

        $clients = Client::orderBy('name')->get();

        return view('admin.v2.sales.form', compact('vehicles', 'clients'));
    }

    /**
     * Guarda nova venda
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'client_id' => 'required|exists:clients,id',
            'sale_date' => 'required|date',
            'sale_price' => 'required|numeric|min:0',
            'vat_rate' => 'nullable',
            'payment_method' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
            'gross_margin' => 'nullable|numeric',
            'net_margin' => 'nullable|numeric',
            'vat_paid' => 'nullable|numeric',
            'vat_deducible_purchase' => 'nullable|numeric',
            'vat_settle_sale' => 'nullable|numeric',
            'totalCost' => 'nullable|numeric',
            'totalExpenses' => 'nullable|numeric',
            'net_profitability' => 'nullable|numeric',
            'gross_profitability' => 'nullable|numeric',
            'has_trade_in' => 'nullable|boolean',
            'trade_in_vehicle_id' => 'nullable|exists:vehicles,id',
            'trade_in_value' => 'nullable|numeric|min:0',
        ]);
        $sale = Sale::create($validated);
        $this->calculate($sale, $validated);
        return redirect()->route('admin.v2.sales.index')
            ->with('success', 'Venda criada com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $sale = Sale::with(['vehicle', 'client'])->findOrFail($id);

        // Veículos disponíveis (incluindo o da venda atual)
        $vehicles = Vehicle::where(function ($query) use ($sale) {
            $query->doesntHave('sale')
                ->orWhere('id', $sale->vehicle_id);
        })
            ->select('id', 'reference', 'brand', 'model', 'year')
            ->orderBy('reference')
            ->get();

        $clients = Client::orderBy('name')->get();
        $expenses = Expense::where('vehicle_id', $sale->vehicle_id)->get();
        return view('admin.v2.sales.form', compact('sale', 'vehicles', 'clients', 'expenses'));
    }

    /**
     * Atualiza venda
     */
    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'client_id' => 'required|exists:clients,id',
            'sale_date' => 'required|date',
            'sale_price' => 'required|numeric|min:0',
            'vat_rate' => 'nullable',
            'payment_method' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
            'gross_margin' => 'nullable|numeric',
            'net_margin' => 'nullable|numeric',
            'vat_paid' => 'nullable|numeric',
            'vat_deducible_purchase' => 'nullable|numeric',
            'vat_settle_sale' => 'nullable|numeric',
            'totalCost' => 'nullable|numeric',
            'totalExpenses' => 'nullable|numeric',
            'net_profitability' => 'nullable|numeric',
            'gross_profitability' => 'nullable|numeric',
            'has_trade_in' => 'nullable|boolean',
            'trade_in_vehicle_id' => 'nullable|exists:vehicles,id',
            'trade_in_value' => 'nullable|numeric|min:0',
        ]);
        $sale->update($validated);
        $this->calculate($sale, $validated );
        return redirect()->route('admin.v2.sales.index')
            ->with('success', 'Venda atualizada com sucesso!');
    }

    /**
     * Elimina venda
     */
    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->delete();

        return redirect()->route('admin.v2.sales.index')
            ->with('success', 'Venda eliminada com sucesso!');
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
            $validatedData['net_margin'] = $net_margin - $totalExpenses;;
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
}
