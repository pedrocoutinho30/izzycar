@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="mt-4">Resultados Anuais</h1>

            @php
            use Carbon\Carbon;


            $year = now()->year;
            $lastYear = $year - 1;

            // Filtra vendas por ano atual e ano anterior
            $filteredSales = $sales->filter(fn ($sale) => Carbon::parse($sale->sale_date)->year == $year);
            $filteredSalesLastYear = $sales->filter(fn ($sale) => Carbon::parse($sale->sale_date)->year == $lastYear);

            // Dados atuais
            $totalSales = $filteredSales->count();
            $totalValue = $filteredSales->sum('sale_price');
            $netMargin = $filteredSales->sum('net_margin');
            $avgProfit = $filteredSales->avg('net_profitability');

            // Dados anteriores
            $totalSalesLast = $filteredSalesLastYear->count();
            $totalValueLast = $filteredSalesLastYear->sum('sale_price');
            $netMarginLast = $filteredSalesLastYear->sum('net_margin');
            $avgProfitLast = $filteredSalesLastYear->avg('net_profitability');

            // Classes de cor e ícones para comparação
            function compareIcon($current, $previous) {
            if ($current > $previous) return '<span class="text-success ms-1">&#9650;</span>'; // seta para cima
            if ($current < $previous) return '<span class="text-danger ms-1">&#9660;</span>' ; // seta para baixo
                return '' ; // igual
                }

                $netMarginClass=$netMargin>= 0 ? 'text-success' : 'text-danger';
                @endphp

                <div class="row g-4 mb-4">

                    <!-- Total de Vendas -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="text-muted">Total de Vendas</h6>
                                <small class="text-muted">{{ $year }}</small>
                            </div>
                            <h4 class="fw-bold text-primary">{{ $totalSales }}</h4>
                            <small class="text-muted">
                                {{ $lastYear }}: {{ $totalSalesLast }}
                                {!! compareIcon($totalSales, $totalSalesLast) !!}
                            </small>
                        </div>
                    </div>

                    <!-- Valor Total -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="text-muted">Valor Total</h6>
                                <small class="text-muted">{{ $year }}</small>
                            </div>
                            <h4 class="fw-bold text-success">{{ number_format($totalValue, 2, ',', '.') }}€</h4>
                            <small class="text-muted">
                                {{ $lastYear }}: {{ number_format($totalValueLast, 2, ',', '.') }}€
                                {!! compareIcon($totalValue, $totalValueLast) !!}
                            </small>
                        </div>
                    </div>

                    <!-- Margem Líquida -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="text-muted">Margem Líquida</h6>
                                <small class="text-muted">{{ $year }}</small>
                            </div>
                            <h4 class="fw-bold {{ $netMarginClass }}">{{ number_format($netMargin, 2, ',', '.') }}€</h4>
                            <small class="text-muted">
                                {{ $lastYear }}: {{ number_format($netMarginLast, 2, ',', '.') }}€
                                {!! compareIcon($netMargin, $netMarginLast) !!}
                            </small>
                        </div>
                    </div>

                    <!-- Margem de Lucro Média -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="text-muted">Margem de Lucro Média</h6>
                                <small class="text-muted">{{ $year }}</small>
                            </div>
                            <h4 class="fw-bold text-info">{{ number_format($avgProfit, 2, ',', '.') }}%</h4>
                            <small class="text-muted">
                                {{ $lastYear }}: {{ number_format($avgProfitLast, 2, ',', '.') }}%
                                {!! compareIcon($avgProfit, $avgProfitLast) !!}
                            </small>
                        </div>
                    </div>

                </div>




                @php

                $currentMonth = now()->month;
                $currentYear = now()->year;
                Carbon::setLocale('pt');
                $currentMonthName = now()->translatedFormat('F');

                // Mês anterior e ano do mês anterior
                $previousMonthDate = now()->subMonth();
                $previousMonth = $previousMonthDate->month;
                $previousYear = $previousMonthDate->year;
                $previousMonthName = $previousMonthDate->translatedFormat('F');

                // Filtra vendas do mês atual
                $filteredSales = $sales->filter(function ($sale) use ($currentMonth, $currentYear) {
                $date = Carbon::parse($sale->sale_date);
                return $date->year == $currentYear && $date->month == $currentMonth;
                });

                // Filtra vendas do mês anterior
                $filteredSalesPrev = $sales->filter(function ($sale) use ($previousMonth, $previousYear) {
                $date = Carbon::parse($sale->sale_date);
                return $date->year == $previousYear && $date->month == $previousMonth;
                });

                // Dados mês atual
                $totalSales = $filteredSales->count();
                $totalValue = $filteredSales->sum('sale_price');
                $netMargin = $filteredSales->sum('net_margin');
                $avgProfit = $filteredSales->avg('net_profitability');
                $netMarginClass = $netMargin >= 0 ? 'text-success' : 'text-danger';

                // Dados mês anterior
                $totalSalesPrev = $filteredSalesPrev->count();
                $totalValuePrev = $filteredSalesPrev->sum('sale_price');
                $netMarginPrev = $filteredSalesPrev->sum('net_margin');
                $avgProfitPrev = $filteredSalesPrev->avg('net_profitability');


                @endphp

                <h1 class="mt-4">Resultados Mensais</h1>

                <div class="row g-4 mb-4">
                    <!-- Total de Vendas -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="text-muted">Total de Vendas</h6>
                                <small class="text-muted">{{ $currentMonthName }} {{ $currentYear }}</small>
                            </div>
                            <h4 class="fw-bold text-primary">{{ $totalSales }}</h4>
                            <small class="text-muted">
                                {{ $previousMonthName }} {{ $previousYear }}: {{ $totalSalesPrev }}
                                {!! compareIcon($totalSales, $totalSalesPrev) !!}
                            </small>
                        </div>
                    </div>

                    <!-- Valor Total -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="text-muted">Valor Total</h6>
                                <small class="text-muted">{{ $currentMonthName }} {{ $currentYear }}</small>
                            </div>
                            <h4 class="fw-bold text-success">
                                {{ number_format($totalValue, 2, ',', '.') }}€
                            </h4>
                            <small class="text-muted">
                                {{ $previousMonthName }} {{ $previousYear }}: {{ number_format($totalValuePrev, 2, ',', '.') }}€
                                {!! compareIcon($totalValue, $totalValuePrev) !!}
                            </small>
                        </div>
                    </div>

                    <!-- Margem Líquida -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="text-muted">Margem Líquida</h6>
                                <small class="text-muted">{{ $currentMonthName }} {{ $currentYear }}</small>
                            </div>
                            <h4 class="fw-bold {{ $netMarginClass }}">
                                {{ number_format($netMargin, 2, ',', '.') }}€
                            </h4>
                            <small class="text-muted">
                                {{ $previousMonthName }} {{ $previousYear }}: {{ number_format($netMarginPrev, 2, ',', '.') }}€
                                {!! compareIcon($netMargin, $netMarginPrev) !!}
                            </small>
                        </div>
                    </div>

                    <!-- Margem de Lucro Média -->
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100 p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="text-muted">Margem de Lucro Média</h6>
                                <small class="text-muted">{{ $currentMonthName }} {{ $currentYear }}</small>
                            </div>
                            <h4 class="fw-bold text-info">
                                {{ number_format($avgProfit, 2, ',', '.') }}%
                            </h4>
                            <small class="text-muted">
                                {{ $previousMonthName }} {{ $previousYear }}: {{ number_format($avgProfitPrev, 2, ',', '.') }}%
                                {!! compareIcon($avgProfit, $avgProfitPrev) !!}
                            </small>
                        </div>
                    </div>
                </div>



                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 text-primary mb-0">Vendas</h1>
                    <a href="{{ route('sales.create') }}" class="btn btn-outline-primary shadow-sm">
                        <i class="fas fa-plus me-1"></i> Adicionar Venda
                    </a>
                </div>
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Veículo</th>
                                <th>Compra</th>
                                <th>Cliente</th>
                                <th>Data de compra</th>
                                <th>Data de venda</th>
                                <th>Rentabilidade (€)</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sales as $sale)
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td>{{ ($sale->vehicle->brand ?? 'N/A') . ' ' . ($sale->vehicle->model ?? '') }}</td>
                                <td>{{ $sale->vehicle->purchase_type ?? 'N/A' }}</td>
                                <td>{{ $sale->client->name ?? 'N/A' }}</td>
                                <td>{{ $sale->vehicle->purchase_date ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                                <td>
                                    {{ number_format($sale->net_margin, 2, ',', '.') }}€
                                    ({{ number_format($sale->net_profitability, 2, ',', '.') }}%)
                                </td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Ações">
                                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('Tem certeza?')" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $sales->links() }}
                </div>
        </div>
    </div>
</div>


@endsection