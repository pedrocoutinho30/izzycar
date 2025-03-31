@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h2>{{ isset($sale) ? 'Editar Venda' : 'Nova Venda' }}</h2>

    <form action="{{ isset($sale) ? route('sales.update', $sale->id) : route('sales.store') }}" method="POST">
        @csrf
        @isset($sale)
        @method('PUT')
        @endisset

        <div class="mb-3">
            <label>Veículo</label>
            <select name="vehicle_id" class="form-control @error('vehicle_id') is-invalid @enderror">
                <option value="">Selecione...</option>
                @foreach ($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" {{ isset($sale) && $sale->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->id }} {{ $vehicle->brand }} {{ $vehicle->model }}
                </option>
                @endforeach
            </select>
            @error('vehicle_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label>Cliente</label>
            <select name="client_id" class="form-control @error('client_id') is-invalid @enderror">
                <option value="">Selecione...</option>
                @foreach ($clients as $client)
                <option value="{{ $client->id }}" {{ isset($sale) && $sale->client_id == $client->id ? 'selected' : '' }}>
                    {{ $client->name }}
                </option>
                @endforeach
            </select>
            @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label>Data de Venda</label>
            <input type="date" name="sale_date" class="form-control @error('sale_date') is-invalid @enderror"
                value="{{ old('sale_date', isset($sale) ? $sale->sale_date : '') }}">
            @error('sale_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label>Valor da Venda (€)</label>
            <input type="text" name="sale_price" class="form-control @error('sale_price') is-invalid @enderror"
                value="{{ old('sale_price', isset($sale) ? $sale->sale_price : '') }}">
            @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label>Taxa de IVA</label>
            <select name="vat_rate" class="form-control @error('vat_rate') is-invalid @enderror">
                <option value="">Selecione...</option>
                <option value="Sem iva" {{ isset($sale) && $sale->vat_rate == 'Sem iva' ? 'selected' : '' }}>Sem IVA</option>
                <option value="23%" {{ isset($sale) && $sale->vat_rate == '23%' ? 'selected' : '' }}>23%</option>
            </select>
            @error('vat_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label>Método de Pagamento</label>
            <select name="payment_method" class="form-control @error('payment_method') is-invalid @enderror">
                <option value="">Selecione...</option>
                <option value="Financiamento" {{ isset($sale) && $sale->payment_method == 'Financiamento' ? 'selected' : '' }}>Financiamento</option>
                <option value="Pronto pagamento" {{ isset($sale) && $sale->payment_method == 'Pronto pagamento' ? 'selected' : '' }}>Pronto Pagamento</option>
            </select>
            @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label>Observações</label>
            <textarea name="observation" class="form-control @error('observation') is-invalid @enderror" rows="3">{{ old('observation', isset($sale) ? $sale->observation : '') }}</textarea>
            @error('observation') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-success">{{ isset($sale) ? 'Atualizar' : 'Salvar' }}</button>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection