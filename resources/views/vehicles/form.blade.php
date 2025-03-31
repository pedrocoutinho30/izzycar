@extends('layouts.admin')

@section('main-content')
<h1>{{ isset($vehicle) ? 'Editar Veículo' : 'Criar Novo Veículo' }}</h1>

<form action="{{ isset($vehicle) ? route('vehicles.update', $vehicle->id) : route('vehicles.store') }}" id="vehicle-form" method="POST">
    @csrf
    @if (isset($vehicle))
    @method('PUT') <!-- Para quando for editar, utiliza o método PUT -->
    @endif


    <div class="form-group">
        <label for="reference">Referência</label>
        <input type="text" readonly name="reference" class="form-control" value="{{ old('reference', $vehicle->reference ?? '') }}" required>
    </div>
    <div class="form-group">
        <label for="business_type">Tipo de Negócio</label>
        <select name="business_type" class="form-control">
            <option value="novo" {{ isset($vehicle) && $vehicle->business_type == 'novo' ? 'selected' : '' }}>Novo</option>
            <option value="usado" {{ isset($vehicle) && $vehicle->business_type == 'usado' ? 'selected' : '' }}>Usado</option>
            <option value="seminovo" {{ isset($vehicle) && $vehicle->business_type == 'seminovo' ? 'selected' : '' }}>Seminovo</option>
        </select>
    </div>

    <div class="form-group">
        <label for="brand">Marca</label>
        <input type="text" name="brand" class="form-control" value="{{ old('brand', $vehicle->brand ?? '') }}" required>
    </div>

    <div class="form-group">
        <label for="model">Modelo</label>
        <input type="text" name="model" class="form-control" value="{{ old('model', $vehicle->model ?? '') }}" required>
    </div>

    <div class="form-group">
        <label for="version">Versão</label>
        <input type="text" class="form-control @error('version') is-invalid @enderror" id="version" name="version" value="{{ old('version', $vehicle->version ?? '') }}">
        @error('version')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label for="version">Ano</label>
        <input type="number" class="form-control @error('year') is-invalid @enderror" id="year" name="year" value="{{ old('year', $vehicle->year ?? '') }}">
        @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="supplier_id">Fornecedor</label>
        <select name="supplier_id" class="form-control">
            <option value="">Selecione o fornecedor</option>
            @foreach ($suppliers as $supplier)
            <option value="{{ $supplier->id }}" {{ isset($vehicle) && $vehicle->supplier_id == $supplier->id ? 'selected' : '' }}>
                {{ $supplier->company_name }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="purchase_date">Data de Compra</label>
        <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', $vehicle->purchase_date ?? '') }}">
    </div>


    <div class="row">
        <div class="form-group col-md-4">
            <label for="purchase_type">Tipo de Compra</label>
            <select class="form-control @error('purchase_type') is-invalid @enderror" id="purchase_type" name="purchase_type" onchange="calcularLucro()" required>
                <option value="" {{ old('purchase_type', $vehicle->purchase_type ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                <option value="Margem" {{ old('purchase_type', $vehicle->purchase_type ?? '') == 'Margem' ? 'selected' : '' }}>Margem</option>
                <option value="Geral" {{ old('purchase_type', $vehicle->purchase_type ?? '') == 'Geral' ? 'selected' : '' }}>Geral</option>
                <option value="Sem Iva" {{ old('purchase_type', $vehicle->purchase_type ?? '') == 'Sem Iva' ? 'selected' : '' }}>Sem Iva</option>
            </select>

            @error('purchase_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group col-md-4">
            <label for="purchase_price">Preço de Compra</label>
            <input type="number" name="purchase_price" id="purchase_price" step="any" class="form-control" value="{{ old('purchase_price', $vehicle->purchase_price ?? '') }}" oninput="calcularLucro()" required>
        </div>

        <div class="form-group col-md-4">
            <label for="sell_price">Preço de Venda Previsto</label>
            <input type="number" name="sell_price" id="sell_price" step="any" class="form-control" value="{{ old('sell_price', $vehicle->sell_price ?? '') }}" oninput="calcularLucro()">
        </div>
    </div>

    <div class="form-group">
        <label for="kilometers">Kilometragem</label>
        <input type="number" class="form-control @error('kilometers') is-invalid @enderror" id="kilometers" name="kilometers" value="{{ old('kilometers', $vehicle->kilometers ?? '') }}">
        @error('kilometers')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="power">Potência (cv)</label>
        <input type="number" class="form-control @error('power') is-invalid @enderror" id="power" name="power" value="{{ old('power', $vehicle->power ?? '') }}">
        @error('power')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="cylinder_capacity">Capacidade Cilíndrica</label>
        <input type="number" class="form-control @error('cylinder_capacity') is-invalid @enderror" id="cylinder_capacity" name="cylinder_capacity" value="{{ old('cylinder_capacity', $vehicle->cylinder_capacity ?? '') }}">
        @error('cylinder_capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="consigned_vehicle">Veículo Consignado</label>
        <select class="form-control @error('consigned_vehicle') is-invalid @enderror" id="consigned_vehicle" name="consigned_vehicle">
            <option value="" {{ old('consigned_vehicle', $vehicle->consigned_vehicle ?? '') == '' ? 'selected' : '' }}>Selecione</option>
            <option value="0" {{ old('consigned_vehicle', $vehicle->consigned_vehicle ?? '') == '0' ? 'selected' : '' }}>Não</option>
            <option value="1" {{ old('consigned_vehicle', $vehicle->consigned_vehicle ?? '') == '1' ? 'selected' : '' }}>Sim</option>
        </select>
        @error('consigned_vehicle')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <!-- <input type="checkbox" class="form-check-input" id="consigned_vehicle" name="consigned_vehicle" {{ old('consigned_vehicle', $vehicle->consigned_vehicle ?? false) ? 'checked' : '' }}> -->

    <div class="form-group">
        <label for="fuel">Combustível</label>
        <select class="form-control @error('fuel') is-invalid @enderror" id="fuel" name="fuel">
            <option value="" {{ old('fuel', $vehicle->fuel ?? '') == '' ? 'selected' : '' }}>Selecione</option>
            <option value="Diesel" {{ old('fuel', $vehicle->fuel ?? '') == 'Diesel' ? 'selected' : '' }}>Diesel</option>
            <option value="Gasolina" {{ old('fuel', $vehicle->fuel ?? '') == 'Gasolina' ? 'selected' : '' }}>Gasolina</option>
            <option value="Elétrico" {{ old('fuel', $vehicle->fuel ?? '') == 'Elétrico' ? 'selected' : '' }}>Elétrico</option>
            <option value="Hibrido-plug-in/Gasolina" {{ old('fuel', $vehicle->fuel ?? '') == 'Hibrido-plug-in/Gasolina' ? 'selected' : '' }}>Hibrido-plug-in/Gasolina</option>
            <option value="Hibrido-plug-in/Diesel" {{ old('fuel', $vehicle->fuel ?? '') == 'Hibrido-plug-in/Diesel' ? 'selected' : '' }}>Hibrido-plug-in/Diesel</option>
            <option value="Outro" {{ old('fuel', $vehicle->fuel ?? '') == 'Outro' ? 'selected' : '' }}>Outro</option>
        </select>
        @error('fuel')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>



    <div class="form-group">
        <label for="vin">VIN</label>
        <input type="text" class="form-control @error('vin') is-invalid @enderror" id="vin" name="vin" value="{{ old('vin', $vehicle->vin ?? '') }}" required>
        @error('vin')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="manufacture_date">Data de Fabrico</label>
        <input type="date" class="form-control @error('manufacture_date') is-invalid @enderror" id="manufacture_date" name="manufacture_date" value="{{ old('manufacture_date', $vehicle->manufacture_date ?? '') }}">
        @error('manufacture_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="register_date">Data de Registo</label>
        <input type="date" class="form-control @error('register_date') is-invalid @enderror" id="register_date" name="register_date" value="{{ old('register_date', $vehicle->register_date ?? '') }}">
        @error('register_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="available_to_sell_date">Data Disponível para Venda</label>
        <input type="date" class="form-control @error('available_to_sell_date') is-invalid @enderror" id="available_to_sell_date" name="available_to_sell_date" value="{{ old('available_to_sell_date', $vehicle->available_to_sell_date ?? '') }}">
        @error('available_to_sell_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="registration">Matrícula</label>
        <input type="text" class="form-control @error('registration') is-invalid @enderror" id="registration" name="registration" value="{{ old('registration', $vehicle->registration ?? '') }}">
        @error('registration')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>


    <!-- Campos adicionais de acordo com os dados do veículo -->

    <div class="form-group">
        <label for="color">Cor</label>
        <input type="text" name="color" class="form-control" value="{{ old('color', $vehicle->color ?? '') }}">
    </div>


    <div class="form-group">

        <button type="submit" class="btn btn-success mt-3">{{ isset($vehicle) ? 'Atualizar' : 'Criar' }} Veículo</button>
        <a href="{{ route('vehicles.index') }}" class="btn btn-danger mt-3">Voltar à listagem</a>
</form>
@endsection