@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h2>Detalhes da Venda</h2>

    <div class="card">
        <div class="card-body">
            <h4>📌 Informações do Veículo</h4>
            <ul>
                <li><strong>ID:</strong> {{ $vehicle->id }}</li>
                <li><strong>Marca:</strong> {{ $vehicle->brand }}</li>
                <li><strong>Modelo:</strong> {{ $vehicle->model }}</li>
                <li><strong>Preço de Compra:</strong> €{{ number_format($vehicle->purchase_price, 2) }}</li>
                <li><strong>Data de Compra:</strong> {{ $vehicle->purchase_date }}</li>
                <li><strong>Regime de iva:</strong> {{ $vehicle->purchase_type }}</li>
            </ul>

            <h4>💰 Despesas do Veículo</h4>
            @if($expenses->count())
            <ul>
                @foreach($expenses as $expense)
                <li>{{ $expense->title }} - €{{ number_format($expense->amount, 2) }}</li>
                @endforeach
            </ul>
            @else
            <p>Sem despesas registradas.</p>
            @endif
            <strong>Total de Despesas:</strong> €{{ number_format($sale->totalExpenses, 2) }}

            <h4>📊 Resumo Financeiro</h4>
            <ul>
                <li><strong>Gasto Total:</strong> €{{ number_format($sale->totalCost, 2) }}</li>
                @if($vehicle->purchase_type == 'Geral')

                <li><strong>Iva dedutível na compra:</strong> € {{ number_format($sale->vat_deducible_purchase, 2, ',', '.') }}</li>
                <li><strong>Iva a liquidar na venda:</strong> {{ number_format($sale->vat_settle_sale, 2, ',', '.') }}</li>

                @endif
                <li><strong>Preço de Venda:</strong> €{{ number_format($sale->sale_price, 2) }}</li>
                <li><strong>Diferença:</strong> €{{ number_format(($sale->sale_price - $sale->totalCost), 2)  }}</li>
                <li><strong>Margem Bruta:</strong> {{ number_format($sale->gross_margin, 2, ',', '.') }} ({{number_format($sale->gross_profitability, 0)}}%)</li>
                <li style="color: {{ $sale->net_margin > 1000 ? 'green' : ($sale->net_margin >= 0 && $sale->net_margin <= 100 ? 'orange' : 'red') }}">
                    <strong>Margem Líquida: </strong>{{ number_format($sale->net_margin, 2, ',', '.') }} ({{number_format($sale->net_profitability, 0)}}%)
                </li>
                @if($vehicle->purchase_type == 'Geral' || $vehicle->purchase_type == 'Margem')
                <li><strong>Iva a pagar: </strong>{{ number_format($sale->vat_paid, 2, ',', '.') }} </li>
                @endif
            </ul>
        </div>
    </div>

    <a href="{{ route('sales.index') }}" class="btn btn-primary mt-3">Voltar para a listagem</a>
</div>
@endsection