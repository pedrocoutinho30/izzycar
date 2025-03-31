@extends('layouts.admin')

@section('main-content')
<h1 class="h3 mb-2 text-gray-800">
    @isset($supplier)
    Editar Fornecedor
    @else
    Criar Novo Fornecedor
    @endisset
</h1>

<form action="{{ isset($supplier) ? route('suppliers.update', $supplier->id) : route('suppliers.store') }}" method="POST">
    @csrf
    @isset($supplier)
    @method('PUT')
    @endisset

    <div class="form-group">
        <label for="contact_name">Nome de Contacto</label>
        <input type="text" name="contact_name" class="form-control" value="{{ $supplier->contact_name ?? old('contact_name') }}" required>
    </div>

    <div class="form-group">
        <label for="type">Tipo</label>
        <select name="type" class="form-control">
            <option value="particular" {{ isset($supplier) && $supplier->type == 'particular' ? 'selected' : '' }}>Particular</option>
            <option value="empresa" {{ isset($supplier) && $supplier->type == 'empresa' ? 'selected' : '' }}>Empresa</option>
        </select>
    </div>

    <div class="form-group">
        <label for="company_name">Nome da Empresa</label>
        <input type="text" name="company_name" class="form-control" value="{{ $supplier->company_name ?? old('company_name') }}">
    </div>

    <div class="form-group">
        <label for="address">Morada</label>
        <textarea name="address" class="form-control">{{ $supplier->address ?? old('address') }}</textarea>
    </div>

    <div class="form-group">
        <label for="postal_code">Código Postal</label>
        <input type="text" name="postal_code" class="form-control" value="{{ $supplier->postal_code ?? old('postal_code') }}">
    </div>

    <div class="form-group">
        <label for="city">Localidade</label>
        <input type="text" name="city" class="form-control" value="{{ $supplier->city ?? old('city') }}">
    </div>

    <div class="form-group">
        <label for="country">Pais</label>
        <input type="text" name="country" class="form-control" value="{{ $supplier->country ?? old('country') }}">
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" value="{{ $supplier->email ?? old('email') }}">
    </div>

    <div class="form-group">
        <label for="phone">Telemóvel</label>
        <input type="text" name="phone" class="form-control" value="{{ $supplier->phone ?? old('phone') }}">
    </div>

    <div class="form-group">
        <label for="vat">NIF</label>
        <input type="text" name="vat" class="form-control" value="{{ $supplier->vat ?? old('vat') }}">
    </div>

    <div class="form-group">
        <label for="identification_number">Número de Identificação</label>
        <input type="text" name="identification_number" class="form-control" value="{{ $supplier->identification_number ?? old('identification_number') }}">
    </div>

    <div class="form-group">
        <label for="identification_number_validity">Validade do Número de Identificação</label>
        <input type="date" name="identification_number_validity" class="form-control" value="{{ $supplier->identification_number_validity ?? old('identification_number_validity') }}">
    </div>

    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="{{ route('suppliers.index') }}" class="btn btn-danger mt-3">Voltar à listagem</a>
</form>
@endsection