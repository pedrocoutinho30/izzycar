@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        @isset($partner)
        Editar Parceiro
        @else
        Criar Novo Parceiro
        @endisset
    </h2>

    <form action="{{ isset($partner) ? route('partners.update', $partner->id) : route('partners.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @isset($partner)
        @method('PUT')
        @endisset
        <div class="row g-3">
            <div class="form-group col-md-4 mt-4">
                <label for="name">Nome</label>
                <input type="text" name="name" class="form-control rounded shadow-sm" value="{{ $partner->name ?? old('name') }}" required>
            </div>

            <div class="form-group col-md-4 mt-4">
                <label for="phone">Telemóvel</label>
                <input type="text" name="phone" class="form-control rounded shadow-sm" value="{{ $partner->phone ?? old('phone') }}">
            </div>

            <div class="form-group col-md-4 mt-4">
                <label for="address">Morada</label>
                <textarea name="address" class="form-control rounded shadow-sm">{{ $partner->address ?? old('address') }}</textarea>
            </div>

            <div class="form-group col-md-4 mt-4">
                <label for="postal_code">Código Postal</label>
                <input type="text" name="postal_code" class="form-control rounded shadow-sm" value="{{ $partner->postal_code ?? old('postal_code') }}">
            </div>

            <div class="form-group col-md-4 mt-4">
                <label for="city">Cidade</label>
                <input type="text" name="city" class="form-control rounded shadow-sm" value="{{ $partner->city ?? old('city') }}">
            </div>

            <div class="form-group col-md-4 mt-4">
                <label for="country">País</label>
                <input type="text" name="country" class="form-control rounded shadow-sm" value="{{ $partner->country ?? old('country') }}">
            </div>

            <div class="form-group col-md-4 mt-4">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control rounded shadow-sm" value="{{ $partner->email ?? old('email') }}">
            </div>

            <div class="form-group col-md-4 mt-4">
                <label for="vat">NIF</label>
                <input type="text" name="vat" class="form-control rounded shadow-sm" value="{{ $partner->vat ?? old('vat') }}">
            </div>

            <div class="form-group col-md-4 mt-4">
                <label for="contact_name">Nome de Contacto</label>
                <input type="text" name="contact_name" class="form-control rounded shadow-sm" value="{{ $partner->contact_name ?? old('contact_name') }}">
            </div>
        </div>
        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('partners.index') }}" class="btn btn-danger px-4 py-2 rounded-pill shadow">Voltar</a>
            <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow">
                <i class="bi bi-check-circle me-1"></i> Salvar
            </button>
        </div>
    </form>
</div>
    @endsection