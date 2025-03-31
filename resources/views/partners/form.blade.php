@extends('layouts.admin')

@section('main-content')
<h1 class="h3 mb-2 text-gray-800">
    @isset($partner)
        Editar Parceiro
    @else
        Criar Novo Parceiro
    @endisset
</h1>

<form action="{{ isset($partner) ? route('partners.update', $partner->id) : route('partners.store') }}" method="POST">
    @csrf
    @isset($partner)
        @method('PUT')
    @endisset

    <div class="form-group">
        <label for="name">Nome</label>
        <input type="text" name="name" class="form-control" value="{{ $partner->name ?? old('name') }}" required>
    </div>

    <div class="form-group">
        <label for="phone">Telemóvel</label>
        <input type="text" name="phone" class="form-control" value="{{ $partner->phone ?? old('phone') }}">
    </div>

    <div class="form-group">
        <label for="address">Morada</label>
        <textarea name="address" class="form-control">{{ $partner->address ?? old('address') }}</textarea>
    </div>

    <div class="form-group">
        <label for="postal_code">Código Postal</label>
        <input type="text" name="postal_code" class="form-control" value="{{ $partner->postal_code ?? old('postal_code') }}">
    </div>

    <div class="form-group">
        <label for="city">Cidade</label>
        <input type="text" name="city" class="form-control" value="{{ $partner->city ?? old('city') }}">
    </div>

    <div class="form-group">
        <label for="country">País</label>
        <input type="text" name="country" class="form-control" value="{{ $partner->country ?? old('country') }}">
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" value="{{ $partner->email ?? old('email') }}">
    </div>

    <div class="form-group">
        <label for="vat">NIF</label>
        <input type="text" name="vat" class="form-control" value="{{ $partner->vat ?? old('vat') }}">
    </div>

    <div class="form-group">
        <label for="contact_name">Nome de Contacto</label>
        <input type="text" name="contact_name" class="form-control" value="{{ $partner->contact_name ?? old('contact_name') }}">
    </div>

    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="{{ route('partners.index') }}" class="btn btn-danger mt-3">Voltar à listagem</a>
</form>
@endsection
