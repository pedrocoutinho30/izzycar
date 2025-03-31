@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h2>{{ isset($expense) ? 'Editar Despesa' : 'Criar Nova Despesa' }}</h2>

    <!-- Se existe uma despesa, então estamos em modo de edição -->
    <form action="{{ isset($expense) ? route('expenses.update', $expense->id) : route('expenses.store') }}" method="POST">
        @csrf
        @if(isset($expense))
        @method('PUT') <!-- Para edição -->
        @endif
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="form-group">
            <label for="title">Título</label>
            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $expense->title ?? '') }}" required>
        </div>

        <div class="form-group">
            <label for="type">Tipo</label>
            <select class="form-control" id="type" name="type" required>
                <option value="Empresa" {{ old('type', $expense->type ?? '') == 'Empresa' ? 'selected' : '' }}>Empresa</option>
                <option value="Veiculo" {{ old('type', $expense->type ?? '') == 'Veiculo' ? 'selected' : '' }}>Veículo</option>
                <option value="Outros" {{ old('type', $expense->type ?? '') == 'Outros' ? 'selected' : '' }}>Outros</option>
            </select>
        </div>
        <div class="form-group" id="vehicle_field" style="display: none;">
            <label for="vehicle_id">Veículo</label>
            <select class="form-control" id="vehicle_id" name="vehicle_id">
                <option value="0">Selecione um Veículo</option>
                @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $expense->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->brand }} - {{ $vehicle->model }}</option>
                @endforeach
            </select>

        </div>

        <div class="form-group">
            <label for="amount">Valor</label>
            <input type="number" class="form-control" step="any" id="amount" name="amount" value="{{ old('amount', $expense->amount ?? '') }}" required>
        </div>

        <div class="form-group">
            <label for="vat_rate">Taxa de IVA</label>
            <select class="form-control" id="vat_rate" name="vat_rate" required>
                <option value="sem iva" {{ old('vat_rate', $expense->vat_rate ?? '') == 'sem iva' ? 'selected' : '' }}>Sem IVA</option>
                <option value="6%" {{ old('vat_rate', $expense->vat_rate ?? '') == '6%' ? 'selected' : '' }}>6%</option>
                <option value="19%" {{ old('vat_rate', $expense->vat_rate ?? '') == '19%' ? 'selected' : '' }}>19%</option>
                <option value="23%" {{ old('vat_rate', $expense->vat_rate ?? '') == '23%' ? 'selected' : '' }}>23%</option>
                <option value="25%" {{ old('vat_rate', $expense->vat_rate ?? '') == '25%' ? 'selected' : '' }}>25%</option>
            </select>
        </div>

        <div class="form-group">
            <label for="expense_date">Data da Despesa</label>
            <input type="date" class="form-control" id="expense_date" name="expense_date" value="{{ old('expense_date', $expense->expense_date ?? '') }}" required>
        </div>

        <div class="form-group">
            <label for="partner_id">Parceiro</label>
            <select class="form-control" id="partner_id" name="partner_id">
                <option value="">Selecione um Parceiro</option>
                @foreach($partners as $partner)
                <option value="{{ $partner->id }}" {{ old('partner_id', $expense->partner_id ?? '') == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>
                @endforeach
            </select>
        </div>



        <div class="form-group">
            <label for="observation">Observação</label>
            <textarea class="form-control" id="observation" name="observation">{{ old('observation', $expense->observation ?? '') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">{{ isset($expense) ? 'Atualizar Despesa' : 'Criar Despesa' }}</button>
    </form>
</div>

<script>
    // Verifica o tipo de despesa ao carregar a página ou ao mudar o campo "Tipo"
    document.addEventListener('DOMContentLoaded', function() {
        const typeField = document.getElementById('type');
        const vehicleField = document.getElementById('vehicle_field');

        // Exibir ou esconder o campo de veículo com base no tipo de despesa
        function toggleVehicleField() {
            if (typeField.value === 'Veiculo') {
                vehicleField.style.display = 'block';
            } else {
                vehicleField.style.display = 'none';
            }
        }

        // Chama a função ao carregar a página para garantir que a visibilidade está correta
        toggleVehicleField();

        // Chama a função sempre que o tipo de despesa mudar
        typeField.addEventListener('change', toggleVehicleField);
    });
</script>
@endsection