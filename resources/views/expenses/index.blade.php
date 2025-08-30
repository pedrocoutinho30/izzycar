@extends('layouts.admin')

@section('main-content')

<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Despesas</h1>
                <a href="{{ route('expenses.create') }}" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Adicionar Despesa
                </a>
            </div>
            <!-- Filtros -->
            <form method="GET" action="{{ route('expenses.index') }}" class="mb-4">
                <div class="row">
                    <!-- Filtro por Tipo de Despesa -->
                    <div class="col-md-4">
                        <label for="type">Tipo de Despesa</label>
                        <select name="type" id="type" class="form-control">
                            <option value="">Todos</option>
                            @foreach($expenseTypes as $expenseType)
                            <option value="{{ $expenseType }}" {{ request('type') == $expenseType ? 'selected' : '' }}>
                                {{ $expenseType }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Veículo -->
                    <div class="col-md-4" id="vehicle-filter" style="display: none;">

                        <label for="vehicle_id">Veículo</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                ({{ $vehicle->reference }}) {{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->version }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botão de Filtrar -->
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </div>
                </div>
            </form>


            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Título</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Valor</th>
                        <th scope="col">Taxa de IVA</th>
                        <th scope="col">Data da Despesa</th>
                        <th scope="col">Parceiro</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)

                    <tr>
                        <th scope="row">{{ $expense->id }}</th>
                        <td>{{ $expense->title }}</td>
                        <td>
                            {{ $expense->type }}

                            @if($expense->type === 'Veiculo' && $expense->vehicle)
                            -
                            {{ $expense->vehicle->brand }} {{ $expense->vehicle->model }} {{ $expense->vehicle->version }} ({{ $expense->vehicle->reference }})

                            <a href="{{ route('vehicles.edit', $expense->vehicle->id) }}" title="Editar Veículo">
                                <i class="bi bi-car-front" style="font-size: 1.2em;"></i>
                            </a>
                            @else
                            
                            {{ $expense->vehicle ? $expense->vehicle->brand . ' ' . $expense->vehicle->model .' ' . $expense->vehicle->version . ' (' . $expense->vehicle->reference . ')' : ''}}
                            @endif
                        </td>
                        <td>{{ number_format($expense->amount, 2, ',', '.') }}€</td>
                        <td>{{ $expense->vat_rate }}</td>
                        <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
                        <td>{{ $expense->partner ? $expense->partner->name : 'N/A' }}</td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Ações">
                                <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir?')">
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
            <div class="d-flex justify-content-center">
                {{ $expenses->withQueryString()->links() }}
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const vehicleFilter = document.getElementById('vehicle-filter');
        const vehicleSelect = document.getElementById('vehicle_id');

        // Função para mostrar ou ocultar o filtro de veículo
        function toggleVehicleFilter() {
            if (typeSelect.value === 'Veiculo') {
                vehicleFilter.style.display = 'block';
            } else {
                vehicleFilter.style.display = 'none';
                // Redefinir o valor do filtro de veículo para "Todos" (vazio)
                vehicleSelect.value = '';
            }
        }

        // Verificar o estado inicial
        toggleVehicleFilter();

        // Adicionar evento de mudança no filtro de tipo
        typeSelect.addEventListener('change', toggleVehicleFilter);
    });
</script>
@endsection