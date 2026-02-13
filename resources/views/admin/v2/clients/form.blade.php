@extends('layouts.admin-v2')

@section('title', isset($client) ? 'Editar Cliente' : 'Novo Cliente')

@section('content')
<div class="admin-content">
    <!-- HEADER -->
    @php
    $existAction = isset($client) ? 'Editar' : 'Criar';
    @endphp
    <!-- Page Header -->
    @include('components.admin.page-header', [
    'breadcrumbs' => [
    ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
    ['icon' => 'bi bi-people', 'label' => 'Clientes', 'href' => route('admin.v2.clients.index')],
    ['icon' => '', 'label' => $existAction]
    ],
    'title' => $existAction . ' Cliente',
    'subtitle' => "",
    'actionHref' => "",
    'actionLabel' => ''
    ])

    <!-- FORMULÁRIO -->
    <form action="{{ isset($client) ? route('admin.v2.clients.update', $client->id) : route('admin.v2.clients.store') }}"
        method="POST">
        @csrf
        @if(isset($client))
        @method('PUT')
        @endif
        <div class="row g-4">
            <!-- Coluna Principal (Esquerda) -->
            <div class="col-lg-8">
                <!-- Dados Principais -->
                <div class="modern-card">
                    <div class="modern-card-header">
                        <h5 class="modern-card-title">
                            <i class="bi bi-info-circle"></i>
                            Dados Pessoais
                        </h5>
                    </div>
                    <!-- Dados Pessoais -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Nome Completo</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $client->name ?? '') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Cliente</label>
                            <select name="client_type" class="form-select @error('client_type') is-invalid @enderror">
                                <option value="">Selecione...</option>
                                <option value="particular" {{ old('client_type', $client->client_type ?? '') === 'particular' ? 'selected' : '' }}>
                                    Particular
                                </option>
                                <option value="empresa" {{ old('client_type', $client->client_type ?? '') === 'empresa' ? 'selected' : '' }}>
                                    Empresa
                                </option>
                            </select>
                            @error('client_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Género</label>
                            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">Selecione...</option>
                                <option value="masculino" {{ old('gender', $client->gender ?? '') === 'masculino' ? 'selected' : '' }}>
                                    Masculino
                                </option>
                                <option value="feminino" {{ old('gender', $client->gender ?? '') === 'feminino' ? 'selected' : '' }}>
                                    Feminino
                                </option>
                            </select>
                            @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror"
                                value="{{ old('birth_date', $client->birth_date ?? '') }}">
                            @error('birth_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">NIF</label>
                            <input type="text" name="vat_number" class="form-control @error('vat_number') is-invalid @enderror"
                                value="{{ old('vat_number', $client->vat_number ?? '') }}" maxlength="9">
                            @error('vat_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nº Identificação</label>
                            <input type="text" name="identification_number" class="form-control @error('identification_number') is-invalid @enderror"
                                value="{{ old('identification_number', $client->identification_number ?? '') }}">
                            @error('identification_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contactos -->

                    <h3 class="form-section-title mt-4 mb-3">
                        <i class="bi bi-telephone"></i> Contactos
                    </h3>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $client->email ?? '') }}">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $client->phone ?? '') }}">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Morada -->
                    <h3 class="form-section-title mt-4 mb-3">
                        <i class="bi bi-geo-alt"></i> Morada
                    </h3>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Endereço</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                value="{{ old('address', $client->address ?? '') }}">
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Código Postal</label>
                            <input type="text" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror"
                                value="{{ old('postal_code', $client->postal_code ?? '') }}" placeholder="0000-000">
                            @error('postal_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                value="{{ old('city', $client->city ?? '') }}">
                            @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Informações Adicionais -->

                    <h3 class="form-section-title mt-4 mb-3">
                        <i class="bi bi-info-circle"></i> Informações Adicionais
                    </h3>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Origem</label>
                            <select name="origin" class="form-select @error('origin') is-invalid @enderror">
                                <option value="">Como conheceu a empresa?</option>
                                <option value="Olx" {{ isset($client) && $client->origin == 'Olx' ? 'selected' : '' }}>Olx</option>
                                <option value="StandVirtual" {{ isset($client) && $client->origin == 'StandVirtual' ? 'selected' : '' }}>StandVirtual</option>
                                <option value="Facebook" {{ isset($client) && $client->origin == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                <option value="Instagram" {{ isset($client) && $client->origin == 'Instagram' ? 'selected' : '' }}>Instagram</option>
                                <option value="Amigo" {{ isset($client) && $client->origin == 'Amigo' ? 'selected' : '' }}>Amigo</option>
                                <option value="Google" {{ isset($client) && $client->origin == 'Google' ? 'selected' : '' }}>Google</option>
                                <option value="Outro" {{ isset($client) && $client->origin == 'Outro' ? 'selected' : '' }}>Outro</option>
                            </select>
                            @error('origin')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tratamento de Dados</label>
                            <select name="data_processing_consent" class="form-select @error('data_processing_consent') is-invalid @enderror">
                                <option value="">Selecione...</option>
                                <option value="1" {{ old('data_processing_consent', $client->data_processing_consent ?? '') === 1 || old('data_processing_consent', $client->data_processing_consent ?? '') === '1' ? 'selected' : '' }}>Sim</option>
                                <option value="0" {{ old('data_processing_consent', $client->data_processing_consent ?? '') === 0 || old('data_processing_consent', $client->data_processing_consent ?? '') === '0' ? 'selected' : '' }}>Não</option>
                            </select>
                            @error('data_processing_consent')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Newsletter</label>
                            <select name="newsletter_consent" class="form-select @error('newsletter_consent') is-invalid @enderror">
                                <option value="">Selecione...</option>
                                <option value="1" {{ old('newsletter_consent', $client->newsletter_consent ?? '') === 1 || old('newsletter_consent', $client->newsletter_consent ?? '') === '1' ? 'selected' : '' }}>Sim</option>
                                <option value="0" {{ old('newsletter_consent', $client->newsletter_consent ?? '') === 0 || old('newsletter_consent', $client->newsletter_consent ?? '') === '0' ? 'selected' : '' }}>Não</option>
                            </select>
                            @error('newsletter_consent')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea name="observation" class="form-control @error('observation') is-invalid @enderror"
                                rows="4">{{ old('observation', $client->observation ?? '') }}</textarea>
                            @error('observation')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>



            </div>
            <!-- BOTÕES DE AÇÃO -->
            <div class="col-lg-4">
                @include('components.admin.action-card', [
                'cancelButtonHref' => route('admin.v2.clients.index'),
                'submitButtonLabel' => isset($client) ? 'Atualizar Cliente' : 'Criar Cliente',
                'timestamps' => isset($client) ? [
                'created_at' => $client->created_at,
                'updated_at' => $client->updated_at
                ] : null
                ])
                <div class="modern-card">
                    <div class="modern-card-header">
                        <h5 class="modern-card-title">
                            <i class="bi bi-info-circle"></i>
                            Propostas Associadas
                        </h5>
                    </div>
                    <div class="modern-card-body">
                        @if(isset($client) && $client->proposals->count() > 0)
                        <ul class="list-group">
                            @foreach($client->proposals as $proposal)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.v2.proposals.edit', $proposal->id) }}">
                                    Proposta #{{ $proposal->id }} - {{ $proposal->created_at->format('d/m/Y') }}
                                </a>
                                <span class="badge bg-primary rounded-pill">
                                    {{ $proposal->brand }} {{ $proposal->model }}
                                </span>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="mb-0">Nenhuma proposta associada a este cliente.</p>
                        @endif
                    </div>

                </div>
                <div class="modern-card">
                    <div class="modern-card-header">
                        <h5 class="modern-card-title">
                            <i class="bi bi-info-circle"></i>

                            Vendas Associadas
                        </h5>
                    </div>
                    <div class="modern-card-body">
                        @if(isset($client) && $client->sale->count() > 0)
                        <ul class="list-group">
                            @foreach($client->sale as $sale)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.v2.sales.edit', ['id' => $sale->id]) }}">
                                    Venda #{{ $sale->id }} - {{ $sale->created_at->format('d/m/Y H:i') }}
                                </a>
                                <span class="badge bg-primary rounded-pill">
                                    {{ $sale->vehicle->brand }} {{ $sale->vehicle->model }} ({{ $sale->vehicle->reference }})
                                </span>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="mb-0">Nenhuma simulação de custo associada a este cliente.</p>
                        @endif
                    </div>
                </div>
                <div class="modern-card">
                    <div class="modern-card-header">
                        <h5 class="modern-card-title">
                            <i class="bi bi-info-circle"></i>

                            Simulações de Custo Associadas
                        </h5>
                    </div>
                    <div class="modern-card-body">
                        @if(isset($client) && $client->costSimulators->count() > 0)
                        <ul class="list-group">
                            @foreach($client->costSimulators as $simulator)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.v2.cost-simulators.index', ['client_id' => $client->id]) }}">
                                    Simulação #{{ $simulator->id }} - {{ $simulator->created_at->format('d/m/Y H:i') }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="mb-0">Nenhuma simulação de custo associada a este cliente.</p>
                        @endif
                    </div>
                </div>
            </div>



    </form>
</div>

<style>
    .form-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 0;
        overflow: hidden;
    }

    .form-section {
        padding: 2rem;
        border-bottom: 1px solid #eee;
    }

    .form-section:last-of-type {
        border-bottom: none;
    }

    .form-section-header {
        margin-bottom: 1.5rem;
    }

    .form-section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-section-title i {
        color: var(--admin-primary);
    }

    .form-label.required::after {
        content: ' *';
        color: #dc3545;
    }

    .form-actions {
        padding: 2rem;
        background: #f8f9fa;
        display: flex;
        gap: 1rem;
        justify-content: flex-start;
    }

    @media (max-width: 768px) {
        .form-section {
            padding: 1.5rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
        }
    }
</style>
@endsection