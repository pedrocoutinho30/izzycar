@extends('layouts.admin')

@section('main-content')
<h1 class="h3 mb-2 text-gray-800">Criar Novo Cliente</h1>
<form action="{{ route('clients.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="name">Nome</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="vat_number">Número de VAT</label>
        <input type="text" name="vat_number" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="birth_date">Data de Nascimento</label>
        <input type="date" name="birth_date" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="gender">Gênero</label>
        <select name="gender" class="form-control" required>
            <option value="homem">Homem</option>
            <option value="mulher">Mulher</option>
            <option value="outro">Outro</option>
        </select>
    </div>
    <div class="form-group">
        <label for="phone">Telefone</label>
        <input type="text" name="phone" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="Identification_number">Número de Identificação</label>
        <input type="text" name="Identification_number" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="validate_Identification_number">Data de Validação do Número de Identificação</label>
        <input type="date" name="validate_Identification_number" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="address">Endereço</label>
        <textarea name="address" class="form-control" required></textarea>
    </div>
    <div class="form-group">
        <label for="postal_code">Código Postal</label>
        <input type="text" name="postal_code" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="city">Cidade</label>
        <input type="text" name="city" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="client_type">Tipo de Cliente</label>
        <select name="client_type" class="form-control" required>
            <option value="Particular">Particular</option>
            <option value="empresa">Empresa</option>
        </select>
    </div>
    <div class="form-group">
        <label for="origin">Origem</label>
        <select name="origin" class="form-control" required>
            <option value="Olx">Olx</option>
            <option value="StandVirtual">StandVirtual</option>
            <option value="Facebook">Facebook</option>
            <option value="Instagram">Instagram</option>
            <option value="Amigo">Amigo</option>
            <option value="Outro">Outro</option>
        </select>
    </div>
    <div class="form-group">
        <label for="observation">Observações</label>
        <textarea name="observation" class="form-control"></textarea>
    </div>

    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="{{ route('clients.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection
