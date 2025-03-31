@extends('layouts.admin')

@section('main-content')
<h1 class="h3 mb-2 text-gray-800">Editar Cliente</h1>
<form action="{{ route('clients.update', $client->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="name">Nome</label>
        <input type="text" name="name" class="form-control" value="{{ $client->name }}" required>
    </div>
    <div class="form-group">
        <label for="vat_number">Número de VAT</label>
        <input type="text" name="vat_number" class="form-control" value="{{ $client->vat_number }}" required>
    </div>
    <div class="form-group">
        <label for="birth_date">Data de Nascimento</label>
        <input type="date" name="birth_date" class="form-control" value="{{ $client->birth_date }}" required>
    </div>
    <div class="form-group">
        <label for="gender">Gênero</label>
        <select name="gender" class="form-control" required>
            <option value="homem" {{ $client->gender == 'homem' ? 'selected' : '' }}>Homem</option>
            <option value="mulher" {{ $client->gender == 'mulher' ? 'selected' : '' }}>Mulher</option>
            <option value="outro" {{ $client->gender == 'outro' ? 'selected' : '' }}>Outro</option>
        </select>
    </div>
    <div class="form-group">
        <label for="phone">Telefone</label>
        <input type="text" name="phone" class="form-control" value="{{ $client->phone }}" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" value="{{ $client->email }}" required>
    </div>
    <div class="form-group">
        <label for="Identification_number">Número de Identificação</label>
        <input type="text" name="Identification_number" class="form-control" value="{{ $client->Identification_number }}" required>
    </div>
    <div class="form-group">
        <label for="validate_Identification_number">Data de Validação do Número de Identificação</label>
        <input type="date" name="validate_Identification_number" class="form-control" value="{{ $client->validate_Identification_number }}" required>
    </div>
    <div class="form-group">
        <label for="address">Endereço</label>
        <textarea name="address" class="form-control" required>{{ $client->address }}</textarea>
    </div>
    <div class="form-group">
        <label for="postal_code">Código Postal</label>
        <input type="text" name="postal_code" class="form-control" value="{{ $client->postal_code }}" required>
    </div>
    <div class="form-group">
        <label for="city">Cidade</label>
        <input type="text" name="city" class="form-control" value="{{ $client->city }}" required>
    </div>
    <div class="form-group">
        <label for="client_type">Tipo de Cliente</label>
        <select name="client_type" class="form-control" required>
            <option value="Particular" {{ $client->client_type == 'Particular' ? 'selected' : '' }}>Particular</option>
            <option value="empresa" {{ $client->client_type == 'empresa' ? 'selected' : '' }}>Empresa</option>
        </select>
    </div>
    <div class="form-group">
        <label for="origin">Origem</label>
        <select name="origin" class="form-control" required>
            <option value="Olx" {{ $client->origin == 'Olx' ? 'selected' : '' }}>Olx</option>
            <option value="StandVirtual" {{ $client->origin == 'StandVirtual' ? 'selected' : '' }}>StandVirtual</option>
            <option value="Facebook" {{ $client->origin == 'Facebook' ? 'selected' : '' }}>Facebook</option>
            <option value="Instagram" {{ $client->origin == 'Instagram' ? 'selected' : '' }}>Instagram</option>
            <option value="Amigo" {{ $client->origin == 'Amigo' ? 'selected' : '' }}>Amigo</option>
            <option value="Outro" {{ $client->origin == 'Outro' ? 'selected' : '' }}>Outro</option>
        </select>
    </div>
    <div class="form-group">
        <label for="observation">Observações</label>
        <textarea name="observation" class="form-control">{{ $client->observation }}</textarea>
    </div>

    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="{{ route('clients.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection
