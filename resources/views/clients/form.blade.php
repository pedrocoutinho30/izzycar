@extends('layouts.admin')

@section('main-content')
<h1 class="h3 mb-2 text-gray-800">
    @isset($client)
        Editar Cliente
    @else
        Criar Novo Cliente
    @endisset
</h1>

<form action="{{ isset($client) ? route('clients.update', $client->id) : route('clients.store') }}" method="POST">
    @csrf
    @isset($client)
        @method('PUT')
    @endisset

    <div class="form-group">
        <label for="name">Nome</label>
        <input type="text" name="name" class="form-control" value="{{ $client->name ?? '' }}" required>
    </div>

    <div class="form-group">
        <label for="vat_number">Número de VAT</label>
        <input type="text" name="vat_number" class="form-control" value="{{ $client->vat_number ?? '' }}">
    </div>

    <div class="form-group">
        <label for="birth_date">Data de Nascimento</label>
        <input type="date" name="birth_date" class="form-control" value="{{ $client->birth_date ?? '' }}">
    </div>

    <div class="form-group">
        <label for="gender">Gênero</label>
        <select name="gender" class="form-control">
            <option value="homem" {{ isset($client) && $client->gender == 'homem' ? 'selected' : '' }}>Homem</option>
            <option value="mulher" {{ isset($client) && $client->gender == 'mulher' ? 'selected' : '' }}>Mulher</option>
            <option value="outro" {{ isset($client) && $client->gender == 'outro' ? 'selected' : '' }}>Outro</option>
        </select>
    </div>

    <div class="form-group">
        <label for="phone">Telefone</label>
        <input type="text" name="phone" class="form-control" value="{{ $client->phone ?? '' }}">
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" value="{{ $client->email ?? '' }}">
    </div>

    <div class="form-group">
        <label for="Identification_number">Número de Identificação</label>
        <input type="text" name="Identification_number" class="form-control" value="{{ $client->Identification_number ?? '' }}">
    </div>

    <div class="form-group">
        <label for="validate_Identification_number">Data de Validação do Número de Identificação</label>
        <input type="date" name="validate_Identification_number" class="form-control" value="{{ $client->validate_Identification_number ?? '' }}">
    </div>

    <div class="form-group">
        <label for="address">Endereço</label>
        <textarea name="address" class="form-control">{{ $client->address ?? '' }}</textarea>
    </div>

    <div class="form-group">
        <label for="postal_code">Código Postal</label>
        <input type="text" name="postal_code" class="form-control" value="{{ $client->postal_code ?? '' }}">
    </div>

    <div class="form-group">
        <label for="city">Cidade</label>
        <input type="text" name="city" class="form-control" value="{{ $client->city ?? '' }}">
    </div>

    <div class="form-group">
        <label for="client_type">Tipo de Cliente</label>
        <select name="client_type" class="form-control">
            <option value="Particular" {{ isset($client) && $client->client_type == 'Particular' ? 'selected' : '' }}>Particular</option>
            <option value="empresa" {{ isset($client) && $client->client_type == 'empresa' ? 'selected' : '' }}>Empresa</option>
        </select>
    </div>

    <div class="form-group">
        <label for="origin">Origem</label>
        <select name="origin" class="form-control">
            <option value="Olx" {{ isset($client) && $client->origin == 'Olx' ? 'selected' : '' }}>Olx</option>
            <option value="StandVirtual" {{ isset($client) && $client->origin == 'StandVirtual' ? 'selected' : '' }}>StandVirtual</option>
            <option value="Facebook" {{ isset($client) && $client->origin == 'Facebook' ? 'selected' : '' }}>Facebook</option>
            <option value="Instagram" {{ isset($client) && $client->origin == 'Instagram' ? 'selected' : '' }}>Instagram</option>
            <option value="Amigo" {{ isset($client) && $client->origin == 'Amigo' ? 'selected' : '' }}>Amigo</option>
            <option value="Outro" {{ isset($client) && $client->origin == 'Outro' ? 'selected' : '' }}>Outro</option>
        </select>
    </div>

    <div class="form-group">
        <label for="observation">Observações</label>
        <textarea name="observation" class="form-control">{{ $client->observation ?? '' }}</textarea>
    </div>

    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="{{ route('clients.index') }}" class="btn btn-danger mt-3">Voltar à listagem</a>
</form>
@endsection
