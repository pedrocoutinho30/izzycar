@extends('layouts.admin')

@section('main-content')
<h1>Lista de Veículos</h1>
<a href="{{ route('vehicles.create') }}" class="btn btn-primary">Adicionar Novo Veículo</a>
<table class="table mt-3">
    <thead>
        <tr>
            <th>Referência</th>
            <th>Veiculo</th>
            <th>Ano</th>
            <th>Regime de iva</th>
            <th>Preço de Compra</th>
            <th>Preço de Venda</th>
            <th>Fornecedor</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($vehicles as $vehicle)
        <tr>
            <td>{{ $vehicle->reference }}</td>
            <td>{{ $vehicle->brand }} {{ $vehicle->model }}</td>
            <td>{{ $vehicle->year }}</td>
            <td>{{ $vehicle->purchase_type }}</td>
            <td>{{ $vehicle->purchase_price }}</td>
            <td>{{ $vehicle->sell_price }}</td>
            <td>$vehicle->consigned_vehicle ? 'Consignado' : {{ $vehicle->supplier ? $vehicle->supplier->name : 'Não atribuído' }} </td>
            <td>
                <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection