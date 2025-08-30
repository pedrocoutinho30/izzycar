@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        @isset($supplier)
        Editar Fornecedor
        @else
        Criar Novo Fornecedor
        @endisset
    </h2>

    <form action="{{ isset($supplier) ? route('suppliers.update', $supplier->id) : route('suppliers.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @isset($supplier)
        @method('PUT')
        @endisset
        <div class="row g-3">
            <div class="form-group col-md-4 mb-4">
                <label for="contact_name">Nome de Contacto</label>
                <input type="text" name="contact_name" class="form-control rounded shadow-sm" value="{{ $supplier->contact_name ?? old('contact_name') }}" required>
            </div>

            <div class="form-group col-md-4 mb-4">
                <label for="type">Tipo</label>
                <select name="type" class="form-control rounded shadow-sm">
                    <option value="particular" {{ isset($supplier) && $supplier->type == 'particular' ? 'selected' : '' }}>Particular</option>
                    <option value="empresa" {{ isset($supplier) && $supplier->type == 'empresa' ? 'selected' : '' }}>Empresa</option>
                </select>
            </div>

            <div class="form-group col-md-4 mb-4">
                <label for="company_name">Nome da Empresa</label>
                <input type="text" name="company_name" class="form-control rounded shadow-sm" value="{{ $supplier->company_name ?? old('company_name') }}">
            </div>

            <div class="form-group col-md-4 mb-4">
                <label for="address">Morada</label>
                <textarea name="address" class="form-control rounded shadow-sm">{{ $supplier->address ?? old('address') }}</textarea>
            </div>

            <div class="form-group col-md-4 mb-4">
                <label for="postal_code">Código Postal</label>
                <input type="text" name="postal_code" class="form-control rounded shadow-sm" value="{{ $supplier->postal_code ?? old('postal_code') }}">
            </div>

            <div class="form-group col-md-4 mb-4">
                <label for="city">Localidade</label>
                <input type="text" name="city" class="form-control rounded shadow-sm" value="{{ $supplier->city ?? old('city') }}">
            </div>

            <div class="form-group col-md-4 mb-4">
                <label for="country">Pais</label>
                <input type="text" name="country" class="form-control rounded shadow-sm" value="{{ $supplier->country ?? old('country') }}">
            </div>

            <div class="form-group col-md-4 mb-4">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control rounded shadow-sm" value="{{ $supplier->email ?? old('email') }}">
            </div>

            <div class="form-group col-md-4 mb-4">
                <label for="phone">Telemóvel</label>
                <input type="text" name="phone" class="form-control rounded shadow-sm" value="{{ $supplier->phone ?? old('phone') }}">
            </div>

            <div class="form-group col-md-4 mb-4">
                <label for="vat">NIF</label>
                <input type="text" name="vat" class="form-control rounded shadow-sm" value="{{ $supplier->vat ?? old('vat') }}">
            </div>

            <div class="form-group col-md-4 mb-4">
                <label for="identification_number">Número de Identificação</label>
                <input type="text" name="identification_number" class="form-control rounded shadow-sm" value="{{ $supplier->identification_number ?? old('identification_number') }}">
            </div>

            <div class="form-group col-md-4 mb-4">
                <label for="identification_number_validity">Validade do Número de Identificação</label>
                <input type="date" name="identification_number_validity" class="form-control rounded shadow-sm" value="{{ $supplier->identification_number_validity ?? old('identification_number_validity') }}">
            </div>
        </div>
        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('suppliers.index') }}" class="btn btn-danger px-4 py-2 rounded-pill shadow">Voltar</a>
            <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow">
                <i class="bi bi-check-circle me-1"></i> Salvar
            </button>
        </div>
    </form>
</div>
@endsection