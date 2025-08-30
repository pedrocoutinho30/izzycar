@extends('layouts.admin')

@section('main-content')
<div class="container my-4">
    <h2 class="mb-4">🚗 Detalhes da Venda</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📌 Informações do Veículo</h5>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-4"><strong>Marca:</strong> {{ $vehicle->brand }}</div>
                <div class="col-md-4"><strong>Modelo:</strong> {{ $vehicle->model }}</div>
                <div class="col-md-4"></div>
                <div class="col-md-4"><strong>Data de Compra:</strong> {{ $vehicle->purchase_date }}</div>
                <div class="col-md-4"><strong>Preço de Compra:</strong> €{{ number_format($vehicle->purchase_price, 2) }}</div>
                <div class="col-md-4"><strong>Regime de IVA:</strong> {{ $vehicle->purchase_type }}</div>
            </div>


        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">💰 Despesas do Veículo</h5>
        </div>
        <div class="card-body">
            @if($expenses->count())
            <ul class="list-group mb-3">
                @foreach($expenses as $expense)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $expense->title }}
                    <span>€{{ number_format($expense->amount, 2) }}</span>
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-muted">Sem despesas registradas.</p>
            @endif
            <p class="fw-bold">Total de Despesas: €{{ number_format($sale->totalExpenses, 2) }}</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">📊 Resumo Financeiro</h5>
        </div>
        <div class="card-body">
            <ul class="list-group">

                <li class="list-group-item d-flex justify-content-between">
                    <span><strong>Preço de Compra (sem IVA):</strong></span>
                    <span>€{{ number_format($vehicle->purchase_price, 2) }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><strong>Total de Despesas:</strong></span>
                    <span>€{{ number_format($sale->totalExpenses, 2) }}</span>
                </li>

                <li class="list-group-item d-flex justify-content-between">
                    <span><strong>Gasto Total:</strong></span>
                    <span>€{{ number_format($sale->totalCost, 2) }}</span>
                </li>

                <li class="list-group-item d-flex justify-content-between">
                    <span><strong>Preço de Venda (IVA incluído):</strong></span>
                    <span>€{{ number_format($sale->sale_price, 2) }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><strong>Diferença:</strong></span>
                    <span>€{{ number_format(($sale->sale_price - $sale->totalCost), 2) }}</span>
                </li>
                @if($vehicle->purchase_type == 'Geral')
                <!-- <li class="list-group-item d-flex justify-content-between">
                    <span><strong>IVA Dedutível na Compra:</strong></span>
                    <span>€{{ number_format($sale->vat_deducible_purchase, 2, ',', '.') }}</span>
                </li> -->
                 <li class="list-group-item d-flex justify-content-between">
                    <span><strong>IVA Dedutível das Despesas:</strong></span>
                    <span>€{{ number_format($sale->vat_deducible_expenses, 2, ',', '.') }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><strong>IVA a Liquidar na Venda:</strong></span>
                    <span>€{{ number_format($sale->vat_settle_sale, 2, ',', '.') }}</span>
                </li>
                @endif
                @if(in_array($vehicle->purchase_type, ['Geral']))
                <li class="list-group-item d-flex justify-content-between">
                    <span>
                        <strong class="d-block">Iva a Pagar:</strong>
                        <small class="text-muted">Este valor é ilustrativo com base no iva que pode ser deduzido nas despesas</small>
                    </span>
                    <span>€{{ number_format($sale->vat_paid, 2, ',', '.') }}</span>

                </li>
                @endif
                <li class="list-group-item d-flex justify-content-between">
                    <span><strong>Margem Bruta:</strong></span>
                    <span>{{ number_format($sale->gross_margin, 2, ',', '.') }} ({{ number_format($sale->gross_profitability, 0) }}%)</span>
                </li>
                <li class="list-group-item d-flex justify-content-between" style="color:
                    {{ $sale->net_margin > 1000 ? 'green' : ($sale->net_margin >= 0 && $sale->net_margin <= 100 ? 'orange' : 'red') }}">
                    <span><strong>Margem Líquida:</strong></span>
                    <span>{{ number_format($sale->net_margin, 2, ',', '.') }} ({{ number_format($sale->net_profitability, 0) }}%)</span>
                </li>
            </ul>
        </div>
    </div>

    <a href="{{ route('sales.index') }}" class="btn btn-outline-primary mt-3">
        ⬅️ Voltar para a listagem
    </a>
</div>
@endsection