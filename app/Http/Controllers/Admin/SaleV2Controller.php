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
        $validatedData = [];
        $vehicle = $sale->vehicle;
        
        // Buscar despesas associadas ao veículo
        $expenses = Expense::where('vehicle_id', $vehicle->id)->get();
        $totalExpenses = $expenses->sum('amount');

        // Dados base
        $sellPrice = $request['sale_price'];
        $purchasePrice = $vehicle->purchase_price;
        $purchaseType = $vehicle->purchase_type;
        
        // Inicializar variáveis
        $gross_margin = 0;
        $net_margin = 0;
        $vat_paid = 0;
        $vat_deducible_purchase = 0;
        $vat_settle_sale = 0;

        // Calcular com base no tipo de compra
        if ($purchaseType == 'Sem Iva') {
            // Compra sem IVA, venda com IVA 23%
            $sell_value_without_vat = $sellPrice / 1.23;
            $vat_settle_sale = $sellPrice - $sell_value_without_vat;
            
            // IVA a pagar é todo o IVA da venda (não há IVA dedutível na compra)
            $vat_paid = $vat_settle_sale;
            
            // Calcular despesas sem IVA para margem líquida
            $expenses_without_vat = 0;
            foreach ($expenses as $expense) {
                if ($expense->vat_rate == '23%') {
                    $expenses_without_vat += $expense->amount / 1.23;
                } else if ($expense->vat_rate == '19%') {
                    $expenses_without_vat += $expense->amount / 1.19;
                } else if ($expense->vat_rate == '6%') {
                    $expenses_without_vat += $expense->amount / 1.06;
                } else {
                    $expenses_without_vat += $expense->amount;
                }
            }
            
            // Margens
            $gross_margin = $sellPrice - $purchasePrice - $totalExpenses;
            $net_margin = $sell_value_without_vat - $purchasePrice - $expenses_without_vat;
            
        } else if ($purchaseType == 'Geral') {
            // Regime geral: compra COM IVA 19%, venda COM IVA 23%
            // O purchase_price JÁ INCLUI IVA de 19%
            $purchase_value_without_vat = $purchasePrice / 1.19;
            $vat_on_purchase = $purchasePrice - $purchase_value_without_vat;
            
            $calculatedData = $this->calculateIvaGeral($sellPrice, $purchase_value_without_vat, $vat_on_purchase, $expenses);
            $gross_margin = $calculatedData['gross_margin'];
            $net_margin = $calculatedData['net_margin'];
            $vat_paid = $calculatedData['vat_paid'];
            $vat_settle_sale = $calculatedData['vat_settle_sale'];
            $vat_deducible_purchase = $calculatedData['vat_deducible_purchase'];
            
        } else if ($purchaseType == 'Margem') {
            // Regime de IVA de margem (usado em veículos usados)
            // IVA só incide sobre a margem (venda - compra)
            $margin = $sellPrice - $purchasePrice;
            $margin_without_vat = $margin / 1.23;
            $vat_on_margin = $margin - $margin_without_vat;
            
            // Calcular despesas sem IVA para margem líquida
            $expenses_without_vat = 0;
            foreach ($expenses as $expense) {
                if ($expense->vat_rate == '23%') {
                    $expenses_without_vat += $expense->amount / 1.23;
                } else if ($expense->vat_rate == '19%') {
                    $expenses_without_vat += $expense->amount / 1.19;
                } else if ($expense->vat_rate == '6%') {
                    $expenses_without_vat += $expense->amount / 1.06;
                } else {
                    $expenses_without_vat += $expense->amount;
                }
            }
            
            $gross_margin = $margin - $totalExpenses;
            $net_margin = $margin_without_vat - $expenses_without_vat;
            $vat_paid = $vat_on_margin;
            
            // No regime de margem, a venda é (compra + margem com IVA)
            $vat_settle_sale = $vat_on_margin;
        }

        // Calcular rentabilidades
        $net_profitability = $sellPrice > 0 ? ($net_margin / $sellPrice) * 100 : 0;
        $gross_profitability = $sellPrice > 0 ? ($gross_margin / $sellPrice) * 100 : 0;

        // Preparar dados para atualização
        $validatedData['gross_margin'] = $gross_margin;
        $validatedData['net_margin'] = $net_margin;
        $validatedData['vat_paid'] = $vat_paid;
        $validatedData['vat_deducible_purchase'] = $vat_deducible_purchase;
        $validatedData['vat_settle_sale'] = $vat_settle_sale;
        $validatedData['totalExpenses'] = $totalExpenses;
        $validatedData['net_profitability'] = $net_profitability;
        $validatedData['gross_profitability'] = $gross_profitability;

        $sale->update($validatedData);
    }

    public function calculateIvaGeral($sellPrice, $purchaseValueWithoutVat, $vatOnPurchase, $expenses)
    {
        // Venda COM IVA 23%
        $sell_value_without_vat = $sellPrice / 1.23;
        $vat_settle_sale = $sellPrice - $sell_value_without_vat;
        
        // IVA dedutível das despesas
        $vat_deducible_expenses = 0;
        foreach ($expenses as $expense) {
            if ($expense->vat_rate == '23%') {
                $expense_without_vat = $expense->amount / 1.23;
                $vat_deducible_expenses += ($expense->amount - $expense_without_vat);
            } else if ($expense->vat_rate == '19%') {
                $expense_without_vat = $expense->amount / 1.19;
                $vat_deducible_expenses += ($expense->amount - $expense_without_vat);
            } else if ($expense->vat_rate == '6%') {
                $expense_without_vat = $expense->amount / 1.06;
                $vat_deducible_expenses += ($expense->amount - $expense_without_vat);
            }
            // Se for "sem iva", não há IVA para deduzir
        }
        
        // IVA dedutível total: IVA da compra + IVA das despesas
        $vat_deducible_purchase = $vatOnPurchase;
        $total_vat_deducible = $vat_deducible_purchase + $vat_deducible_expenses;
        
        // IVA a pagar = IVA liquidado na venda - IVA dedutível
        $vat_paid = $vat_settle_sale - $total_vat_deducible;
        
        // Soma total das despesas (com IVA incluído)
        $totalExpenses = $expenses->sum('amount');
        
        // Calcular despesas sem IVA para margem líquida
        $expenses_without_vat = 0;
        foreach ($expenses as $expense) {
            if ($expense->vat_rate == '23%') {
                $expenses_without_vat += $expense->amount / 1.23;
            } else if ($expense->vat_rate == '19%') {
                $expenses_without_vat += $expense->amount / 1.19;
            } else if ($expense->vat_rate == '6%') {
                $expenses_without_vat += $expense->amount / 1.06;
            } else {
                $expenses_without_vat += $expense->amount;
            }
        }
        
        // Margens
        // Margem bruta: tudo com IVA incluído
        $gross_margin = $sellPrice - ($purchaseValueWithoutVat + $vatOnPurchase) - $totalExpenses;
        // Margem líquida: tudo sem IVA
        $net_margin = $sell_value_without_vat - $purchaseValueWithoutVat - $expenses_without_vat;

        return [
            'gross_margin' => $gross_margin,
            'net_margin' => $net_margin,
            'vat_paid' => $vat_paid,
            'vat_settle_sale' => $vat_settle_sale,
            'vat_deducible_purchase' => $vat_deducible_purchase,
        ];
    }
}
