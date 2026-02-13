@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        @isset($client)
        Editar Cliente
        @else
        Criar Novo Cliente
        @endisset
    </h2>

    <form action="{{ isset($client) ? route('clients.update', $client->id) : route('clients.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @isset($client)
        @method('PUT')
        @endisset

        <div class="row g-3">
            <div class="col-md-6 mt-4">
                <label for="name" class="form-label">Nome</label>
                <input type="text" name="name" class="form-control rounded shadow-sm" value="{{ $client->name ?? '' }}" required>
            </div>

            <div class="col-md-6 mt-4">
                <label for="vat_number" class="form-label">Número de VAT</label>
                <input type="text" name="vat_number" class="form-control rounded shadow-sm" value="{{ $client->vat_number ?? '' }}">
            </div>

            <div class="col-md-4 mt-4">
                <label for="birth_date" class="form-label">Data de Nascimento</label>
                <input type="date" name="birth_date" class="form-control rounded shadow-sm" value="{{ $client->birth_date ?? '' }}">
            </div>

            <div class="col-md-4 mt-4">
                <label for="gender" class="form-label">Gênero</label>
                <select name="gender" class="form-control rounded shadow-sm">
                    <option value="homem" {{ isset($client) && $client->gender == 'homem' ? 'selected' : '' }}>Homem</option>
                    <option value="mulher" {{ isset($client) && $client->gender == 'mulher' ? 'selected' : '' }}>Mulher</option>
                    <option value="outro" {{ isset($client) && $client->gender == 'outro' ? 'selected' : '' }}>Outro</option>
                </select>
            </div>

            <div class="col-md-4 mt-4">
                <label for="phone" class="form-label">Telefone</label>
                <input type="text" name="phone" class="form-control rounded shadow-sm" value="{{ $client->phone ?? '' }}">
            </div>

            <div class="col-md-6 mt-4">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control rounded shadow-sm" value="{{ $client->email ?? '' }}">
            </div>

            <div class="col-md-6 mt-4">
                <label for="Identification_number" class="form-label">Número de Identificação</label>
                <input type="text" name="Identification_number" class="form-control rounded shadow-sm" value="{{ $client->Identification_number ?? '' }}">
            </div>

            <div class="col-md-6 mt-4">
                <label for="validate_Identification_number" class="form-label">Validação do N.º de Identificação</label>
                <input type="date" name="validate_Identification_number" class="form-control rounded shadow-sm" value="{{ $client->validate_Identification_number ?? '' }}">
            </div>

            <div class="col-md-6 mt-4">
                <label for="postal_code" class="form-label">Código Postal</label>
                <input type="text" name="postal_code" class="form-control rounded shadow-sm" value="{{ $client->postal_code ?? '' }}">
            </div>

            <div class="col-md-6 mt-4">
                <label for="city" class="form-label">Cidade</label>
                <input type="text" name="city" class="form-control rounded shadow-sm" value="{{ $client->city ?? '' }}">
            </div>

            <div class="col-md-6 mt-4">
                <label for="client_type" class="form-label">Tipo de Cliente</label>
                <select name="client_type" class="form-control rounded shadow-sm">
                    <option value="Particular" {{ isset($client) && $client->client_type == 'Particular' ? 'selected' : '' }}>Particular</option>
                    <option value="empresa" {{ isset($client) && $client->client_type == 'empresa' ? 'selected' : '' }}>Empresa</option>
                </select>
            </div>

            <div class="col-md-6 mt-4">
                <label for="origin" class="form-label">Origem</label>
                <select name="origin" class="form-control rounded shadow-sm">
                    <option value="Olx" {{ isset($client) && $client->origin == 'Olx' ? 'selected' : '' }}>Olx</option>
                    <option value="StandVirtual" {{ isset($client) && $client->origin == 'StandVirtual' ? 'selected' : '' }}>StandVirtual</option>
                    <option value="Facebook" {{ isset($client) && $client->origin == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                    <option value="Instagram" {{ isset($client) && $client->origin == 'Instagram' ? 'selected' : '' }}>Instagram</option>
                    <option value="Amigo" {{ isset($client) && $client->origin == 'Amigo' ? 'selected' : '' }}>Amigo</option>
                    <option value="Outro" {{ isset($client) && $client->origin == 'Outro' ? 'selected' : '' }}>Outro</option>
                </select>
            </div>

            <div class="col-md-6 mt-4">
                <label for="data_processing_consent" class="form-label">Tratamento de Dados</label>
                <select name="data_processing_consent" class="form-control rounded shadow-sm">
                    <option value="">Selecione...</option>
                    <option value="1" {{ isset($client) && $client->data_processing_consent ? 'selected' : '' }}>Sim</option>
                    <option value="0" {{ isset($client) && $client->data_processing_consent === false ? 'selected' : '' }}>Não</option>
                </select>
            </div>

            <div class="col-md-6 mt-4">
                <label for="newsletter_consent" class="form-label">Newsletter</label>
                <select name="newsletter_consent" class="form-control rounded shadow-sm">
                    <option value="">Selecione...</option>
                    <option value="1" {{ isset($client) && $client->newsletter_consent ? 'selected' : '' }}>Sim</option>
                    <option value="0" {{ isset($client) && $client->newsletter_consent === false ? 'selected' : '' }}>Não</option>
                </select>
            </div>

            <div class="col-12 mt-4">
                <label for="address" class="form-label">Endereço</label>
                <textarea name="address" class="form-control rounded shadow-sm">{{ $client->address ?? '' }}</textarea>
            </div>

            <div class="col-12 mt-4">
                <label for="observation" class="form-label">Observações</label>
                <textarea name="observation" class="form-control rounded shadow-sm">{{ $client->observation ?? '' }}</textarea>
            </div>
        </div>
        <div class="col-12 mt-4">
            <h4 class="border-bottom color-info">Vendas associadas</h4>
        </div>

        @if(isset($sales) && $sales->count() > 0)
        <div class="col-12 mt-4">
            <ul class="list-group">
                @foreach($sales as $sale)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>{{ $sale->vehicle->brand }} {{ $sale->vehicle->model }} {{$sale->vehicle->version}} - {{ $sale->vehicle->year }} ({{ $sale->vehicle->reference }})</span>
                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Ver Detalhes
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @else
        <div class="col-12 mt-4">
            <p class="text-muted">Nenhuma venda associada a este cliente.</p>
        </div>
        @endif


        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('clients.index') }}" class="btn btn-danger px-4 py-2 rounded-pill shadow">Voltar</a>
            <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow">
                <i class="bi bi-check-circle me-1"></i> Salvar
            </button>
        </div>
    </form>
</div>
@endsection