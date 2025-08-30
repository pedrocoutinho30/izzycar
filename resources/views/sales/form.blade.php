@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">{{ isset($sale) ? 'Editar Venda' : 'Nova Venda' }}</h2>

    <form action="{{ isset($sale) ? route('sales.update', $sale->id) : route('sales.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @isset($sale)
        @method('PUT')
        @endisset
        <div class="row g-3">
            <div class="col-md-4 mb-4">
                <label>Veículo</label>
                <select name="vehicle_id" class="form-control rounded shadow-sm  @error('vehicle_id') is-invalid @enderror">
                    <option value="">Selecione...</option>
                    @foreach ($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" {{ isset($sale) && $sale->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->id }} {{ $vehicle->brand }} {{ $vehicle->model }}
                    </option>
                    @endforeach
                </select>
                @error('vehicle_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-4">
                <label>Cliente</label>
                <select name="client_id" class="form-control rounded shadow-sm  @error('client_id') is-invalid @enderror">
                    <option value="">Selecione...</option>
                    @foreach ($clients as $client)
                    <option value="{{ $client->id }}" {{ isset($sale) && $sale->client_id == $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                    @endforeach
                </select>
                @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-4">
                <label>Data de Venda</label>
                <input type="date" name="sale_date" class="form-control rounded shadow-sm  @error('sale_date') is-invalid @enderror"
                    value="{{ old('sale_date', isset($sale) ? $sale->sale_date : '') }}">
                @error('sale_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-4">
                <label>Valor da Venda (€) </label>
                <input type="text" name="sale_price" class="form-control rounded shadow-sm  @error('sale_price') is-invalid @enderror"
                    value="{{ old('sale_price', isset($sale) ? $sale->sale_price : '') }}">
                @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="col-md-12">
                    Com iva geral o valor da venda deve ser colocado com o iva incluido.
                </small>
            </div>


            <div class="col-md-4 mb-4">
                <label>Taxa de IVA</label>
                <select name="vat_rate" class="form-control rounded shadow-sm  @error('vat_rate') is-invalid @enderror">
                    <option value="">Selecione...</option>
                    <option value="Sem iva" {{ isset($sale) && $sale->vat_rate == 'Sem iva' ? 'selected' : '' }}>Sem IVA</option>
                    <option value="23%" {{ isset($sale) && $sale->vat_rate == '23%' ? 'selected' : '' }}>23%</option>
                </select>
                @error('vat_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-4">
                <label>Método de Pagamento</label>
                <select name="payment_method" class="form-control rounded shadow-sm  @error('payment_method') is-invalid @enderror">
                    <option value="">Selecione...</option>
                    <option value="Financiamento" {{ isset($sale) && $sale->payment_method == 'Financiamento' ? 'selected' : '' }}>Financiamento</option>
                    <option value="Pronto pagamento" {{ isset($sale) && $sale->payment_method == 'Pronto pagamento' ? 'selected' : '' }}>Pronto Pagamento</option>
                </select>
                @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12 mb-4">
                <label>Observações</label>
                <textarea name="observation" class="form-control rounded shadow-sm  @error('observation') is-invalid @enderror" rows="3">{{ old('observation', isset($sale) ? $sale->observation : '') }}</textarea>
                @error('observation') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('sales.index') }}" class="btn btn-danger px-4 py-2 rounded-pill shadow">Voltar</a>
            <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow">
                <i class="bi bi-check-circle me-1"></i> Salvar
            </button>
        </div>
    </form>
</div>
@endsection