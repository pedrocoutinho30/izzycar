@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h2>Lista de Vendas</h2>

    <a href="{{ route('sales.create') }}" class="btn btn-primary mb-3">Nova Venda</a>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Veículo</th>
                <th>Compra</th>
                <th>Cliente</th>
                <th>Data de compra</th>
                <th>Data de venda</th>
                <th>Rentabilidade liquida (%)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->vehicle->brand . ' ' . $sale->vehicle->model ?? 'N/A' }}</td>
                <td>{{ $sale->vehicle->purchase_type ?? 'N/A' }}</td>
                <td>{{ $sale->client->name ?? 'N/A' }}</td>
                <td>{{ $sale->vehicle->purchase_date ?? 'N/A' }}</td>
                <td>{{ $sale->sale_date }}</td>
                <td>{{ $sale->net_profitability }}</td>
                <td>
                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza?')"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $sales->links() }}
</div>
@endsection