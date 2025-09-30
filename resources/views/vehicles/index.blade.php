@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Veículos</h1>
                <a href="{{ route('vehicles.create') }}" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Adicionar Veículo
                </a>
            </div>

            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Referência</th>
                        <th>Veículo</th>
                        <th>Ano</th>
                        <th>Regime de IVA</th>
                        <th>Preço de Compra</th>
                        <th>Preço de Venda</th>
                        <th>Consignado</th>
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
                        <td>{{ number_format($vehicle->purchase_price, 2, ',', '.') }}€</td>
                        <td>{{ number_format($vehicle->sell_price, 2, ',', '.') }}€</td>
                        <td>{{ $vehicle->consigned_vehicle ? 'Sim' : 'Não' }}</td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Ações">
                                <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="btn btn-sm btn-outline-secondary me-2" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $vehicles->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection