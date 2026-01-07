{{--
    ==================================================================
    PROPOSTAS - FORM (Criar/Editar) V2
    ==================================================================
    
    Formulário moderno e organizado para criar ou editar propostas
    
    FUNCIONALIDADES:
    - Secções colapsáveis
    - Validação client-side
    - Auto-save draft (futura)
    - Upload de imagens
    - Cálculo automático de custos
    
    @extends layouts.admin-v2
    ==================================================================
--}}

@extends('layouts.admin-v2')

@section('title', isset($proposal) ? 'Editar Proposta' : 'Nova Proposta')

@section('content')

<!-- Page Header -->
@php
$existAction = isset($proposal) ? 'Editar' : 'Criar';
@endphp
<!-- Page Header -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-file-earmark-text', 'label' => 'Propostas', 'href' => route('admin.v2.proposals.index')],
['icon' => '', 'label' => $existAction]
],
'title' => $existAction . ' Proposta',
'subtitle' => "",
'actionHref' => "",
'actionLabel' => ''
])

<!-- Formulário -->
<form
    action="{{ isset($proposal) ? route('admin.v2.proposals.update', $proposal->id) : route('admin.v2.proposals.store') }}"
    method="POST"
    enctype="multipart/form-data"
    id="proposalForm">
    @csrf
    @if(isset($proposal))
    @method('PUT')
    @endif

    <div class="row g-4">
        <!-- Coluna Principal (Esquerda) -->
        <div class="col-lg-8">

            {{-- SECÇÃO 1: Informações Básicas --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-info-circle"></i>
                        Informações Básicas
                    </h5>
                </div>

                <div class="row g-3">
                    <!-- Cliente -->
                    <div class="col-md-6">
                        <label for="client_id" class="form-label">
                            Cliente <span class="text-danger">*</span>
                        </label>
                        <select
                            name="client_id"
                            id="client_id"
                            class="form-select @error('client_id') is-invalid @enderror"
                            required>
                            <option value="">Selecione um cliente</option>
                            @foreach($clients as $client)
                            <option
                                value="{{ $client->id }}"
                                {{ (old('client_id', $proposal->client_id ?? '') == $client->id) ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('client_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label for="status" class="form-label">Estado</label>
                        <select
                            name="status"
                            id="status"
                            class="form-select @error('status') is-invalid @enderror">
                            <option value="Pendente" {{ old('status', $proposal->status ?? 'Pendente') == 'Pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="Aprovada" {{ old('status', $proposal->status ?? '') == 'Aprovada' ? 'selected' : '' }}>Aprovada</option>
                            <option value="Reprovada" {{ old('status', $proposal->status ?? '') == 'Reprovada' ? 'selected' : '' }}>Reprovada</option>
                            <option value="Enviado" {{ old('status', $proposal->status ?? '') == 'Enviado' ? 'selected' : '' }}>Enviado</option>
                            <option value="Sem resposta" {{ old('status', $proposal->status ?? '') == 'Sem resposta' ? 'selected' : '' }}>Sem resposta</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- URL do Anúncio -->
                    <div class="col-12">
                        <label for="url" class="form-label">
                            <i class="bi bi-link-45deg"></i>
                            URL do Anúncio
                        </label>
                        <input
                            type="url"
                            name="url"
                            id="url"
                            class="form-control @error('url') is-invalid @enderror"
                            placeholder="https://exemplo.com/anuncio"
                            value="{{ old('url', $proposal->url ?? '') }}">
                        @error('url')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- SECÇÃO 2: Detalhes do Veículo --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-car-front"></i>
                        Detalhes do Veículo
                    </h5>
                </div>

                <div class="row g-3">
                    <!-- Marca -->
                    <div class="col-md-4">
                        <label for="brand" class="form-label">
                            Marca <span class="text-danger">*</span>
                        </label>
                        <select
                            name="brand"
                            id="brand"
                            class="form-select @error('brand') is-invalid @enderror"
                            required>
                            <option value="">Selecione</option>
                            @foreach($brands as $brand)
                            <option
                                value="{{ $brand->name }}"
                                {{ (old('brand', $proposal->brand ?? '') == $brand->name) ? 'selected' : '' }}
                                data-models="{{ json_encode($brand->models->pluck('name')) }}">
                                {{ $brand->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('brand')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Modelo -->
                    <div class="col-md-4">
                        <label for="model" class="form-label">
                            Modelo <span class="text-danger">*</span>
                        </label>
                        <select
                            name="model"
                            id="model"
                            class="form-select @error('model') is-invalid @enderror"
                            required
                            disabled>
                            <option value="">Primeiro selecione a marca</option>
                        </select>
                        @error('model')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Versão -->
                    <div class="col-md-4">
                        <label for="version" class="form-label">Versão</label>
                        <input
                            type="text"
                            name="version"
                            id="version"
                            class="form-control @error('version') is-invalid @enderror"
                            value="{{ old('version', $proposal->version ?? '') }}"
                            placeholder="Ex: AMG Line">
                        @error('version')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ano -->
                    <div class="col-md-3">
                        <label for="year" class="form-label">Ano</label>
                        <input
                            type="text"
                            name="proposed_car_year_month"
                            id="proposed_car_year_month"
                            class="form-control @error('proposed_car_year_month') is-invalid @enderror"
                            value="{{ old('proposed_car_year_month', $proposal->proposed_car_year_month ?? '') }}"
                            min="1900"
                            max="{{ date('Y') + 1 }}"
                            placeholder="{{ date('Y') }}">
                        @error('proposed_car_year_month')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Quilometragem -->
                    <div class="col-md-3">
                        <label for="proposed_car_mileage" class="form-label">Quilómetros</label>
                        <input
                            type="number"
                            name="proposed_car_mileage"
                            id="proposed_car_mileage"
                            class="form-control @error('proposed_car_mileage') is-invalid @enderror"
                            value="{{ old('proposed_car_mileage', $proposal->proposed_car_mileage ?? '') }}"
                            min="0"
                            placeholder="0">
                        @error('proposed_car_mileage')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Combustível -->
                    <div class="col-md-3">
                        <label for="fuel" class="form-label">Combustível</label>
                        <select
                            name="fuel"
                            id="fuel"
                            class="form-select @error('fuel') is-invalid @enderror">
                            <option value="">Selecione</option>
                            <option value="Gasolina" {{ old('fuel', $proposal->fuel ?? '') == 'Gasolina' ? 'selected' : '' }}>Gasolina</option>
                            <option value="Diesel" {{ old('fuel', $proposal->fuel ?? '') == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                            <option value="Elétrico" {{ old('fuel', $proposal->fuel ?? '') == 'Elétrico' ? 'selected' : '' }}>Elétrico</option>
                            <option value="Híbrido Plug-in/Gasolina" {{ old('fuel', $proposal->fuel ?? '') == 'Híbrido Plug-in/Gasolina' ? 'selected' : '' }}>Híbrido Plug-in/Gasolina</option>
                            <option value="Híbrido Plug-in/Diesel" {{ old('fuel', $proposal->fuel ?? '') == 'Híbrido Plug-in/Diesel' ? 'selected' : '' }}>Híbrido Plug-in/Diesel</option>
                        </select>
                        @error('fuel')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Valor do Carro -->
                    <div class="col-md-3">
                        <label for="proposed_car_value" class="form-label">Valor do Carro (€)</label>
                        <input
                            type="number"
                            name="proposed_car_value"
                            id="proposed_car_value"
                            class="form-control @error('proposed_car_value') is-invalid @enderror"
                            value="{{ old('proposed_car_value', $proposal->proposed_car_value ?? '') }}"
                            min="0"
                            step="0.01"
                            placeholder="0.00">
                        @error('proposed_car_value')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Cilindrada -->
                    <div class="col-md-4">
                        <label for="engine_capacity" class="form-label">Cilindrada (cc)</label>
                        <input
                            type="number"
                            name="engine_capacity"
                            id="engine_capacity"
                            class="form-control @error('engine_capacity') is-invalid @enderror"
                            value="{{ old('engine_capacity', $proposal->engine_capacity ?? '') }}"
                            min="0"
                            placeholder="1500">
                        @error('engine_capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- CO2 -->
                    <div class="col-md-4">
                        <label for="co2" class="form-label">CO₂ (g/km)</label>
                        <input
                            type="number"
                            name="co2"
                            id="co2"
                            class="form-control @error('co2') is-invalid @enderror"
                            value="{{ old('co2', $proposal->co2 ?? '') }}"
                            min="0"
                            step="0.01"
                            placeholder="120">
                        @error('co2')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Notas -->
                    <div class="col-12">
                        <label for="notes" class="form-label">Notas</label>
                        <textarea
                            name="notes"
                            id="notes"
                            class="form-control @error('notes') is-invalid @enderror"
                            rows="3"
                            placeholder="Observações adicionais sobre o veículo...">{{ old('notes', $proposal->notes ?? '') }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- SECÇÃO 3: Custos de Importação --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-currency-euro"></i>
                        Custos de Importação
                    </h5>
                    <button type="button" class="btn btn-sm btn-secondary-modern" onclick="calculateTotal()">
                        <i class="bi bi-calculator"></i>
                        Calcular Total
                    </button>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="transport_cost" class="form-label">
                            Transporte (€) <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="transport_cost"
                            id="transport_cost"
                            class="form-control cost-input @error('transport_cost') is-invalid @enderror"
                            value="{{ old('transport_cost', $proposal->transport_cost ?? $defaults['transport_cost'] ?? 1250) }}"
                            min="0"
                            step="0.01"
                            required>
                    </div>

                    <div class="col-md-4">
                        <label for="ipo_cost" class="form-label">IPO (€)</label>
                        <input
                            type="number"
                            name="ipo_cost"
                            id="ipo_cost"
                            class="form-control cost-input @error('ipo_cost') is-invalid @enderror"
                            value="{{ old('ipo_cost', $proposal->ipo_cost ?? $defaults['ipo_cost'] ?? 100) }}"
                            min="0"
                            step="0.01">
                    </div>

                    <div class="col-md-4">
                        <label for="imt_cost" class="form-label">IMT (€)</label>
                        <input
                            type="number"
                            name="imt_cost"
                            id="imt_cost"
                            class="form-control cost-input @error('imt_cost') is-invalid @enderror"
                            value="{{ old('imt_cost', $proposal->imt_cost ?? $defaults['imt_cost'] ?? 65) }}"
                            min="0"
                            step="0.01">
                    </div>

                    <div class="col-md-4">
                        <label for="registration_cost" class="form-label">Registo Automóvel (€)</label>
                        <input
                            type="number"
                            name="registration_cost"
                            id="registration_cost"
                            class="form-control cost-input @error('registration_cost') is-invalid @enderror"
                            value="{{ old('registration_cost', $proposal->registration_cost ?? $defaults['registration_cost'] ?? 55) }}"
                            min="0"
                            step="0.01">
                    </div>

                    <div class="col-md-4">
                        <label for="isv_cost" class="form-label">ISV (€)</label>
                        <input
                            type="number"
                            name="isv_cost"
                            id="isv_cost"
                            class="form-control cost-input @error('isv_cost') is-invalid @enderror"
                            value="{{ old('isv_cost', $proposal->isv_cost ?? $defaults['isv_cost'] ?? 0) }}"
                            min="0"
                            step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label for="iuc_cost" class="form-label">IUC (€)</label>
                        <input
                            type="number"
                            name="iuc_cost"
                            id="iuc_cost"
                            class="form-control cost-input @error('iuc_cost') is-invalid @enderror"
                            value="{{ old('iuc_cost', $proposal->iuc_cost ?? $defaults['iuc_cost'] ?? 0) }}"
                            min="0"
                            step="0.01">
                    </div>

                    <div class="col-md-4">
                        <label for="license_plate_cost" class="form-label">Matrícula (€)</label>
                        <input
                            type="number"
                            name="license_plate_cost"
                            id="license_plate_cost"
                            class="form-control cost-input @error('license_plate_cost') is-invalid @enderror"
                            value="{{ old('license_plate_cost', $proposal->license_plate_cost ?? $defaults['license_plate_cost'] ?? 40) }}"
                            min="0"
                            step="0.01">
                    </div>

                    <div class="col-md-4">
                        <label for="inspection_commission_cost" class="form-label">Inspeção na Origem (€)</label>
                        <input
                            type="number"
                            name="inspection_commission_cost"
                            id="inspection_commission_cost"
                            class="form-control cost-input @error('inspection_commission_cost') is-invalid @enderror"
                            value="{{ old('inspection_commission_cost', $proposal->inspection_commission_cost ?? $defaults['inspection_commission_cost'] ?? 350) }}"
                            min="0"
                            step="0.01">
                    </div>

                    <div class="col-md-4">
                        <label for="commission_cost" class="form-label">Comissão Izzycar (€)</label>
                        <input
                            type="number"
                            name="commission_cost"
                            id="commission_cost"
                            class="form-control cost-input @error('commission_cost') is-invalid @enderror"
                            value="{{ old('commission_cost', $proposal->commission_cost ?? $defaults['commission_cost'] ?? 861) }}"
                            min="0"
                            step="0.01">
                    </div>

                    <!-- Total -->
                    <div class="col-12">
                        <div class="alert alert-info d-flex align-items-center justify-content-between">
                            <div>
                                <strong>Total dos Custos de Importação:</strong>
                            </div>
                            <div>
                                <h4 class="mb-0 text-primary-admin" id="totalCost">0,00 €</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECÇÃO 4: Extras e Características --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-star"></i>
                        Extras e Características
                    </h5>
                    <button type="button" class="btn btn-sm btn-secondary-modern" data-bs-toggle="collapse" data-bs-target="#extrasCollapse">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>

                <div class="collapse show" id="extrasCollapse">
                    <div class="row g-3">
                        @if(isset($attributes) && count($attributes) > 0)
                        @foreach($attributes as $groupName => $groupAttributes)
                        <!-- Grupo de Atributos -->
                        <div class="col-12">
                            <div class="attribute-group-header">
                                <i class="bi bi-folder"></i>
                                <strong>{{ $groupName }}</strong>
                            </div>
                        </div>

                        @foreach($groupAttributes as $attr)
                        @php
                        $fieldName = 'attributes[' . $attr->id . ']';
                        $existingValue = old($fieldName, isset($attributeValues) && isset($attributeValues[$attr->id]) ? $attributeValues[$attr->id]->value : null);
                        @endphp

                        <div class="col-md-4">
                            @if($attr->type === 'boolean')
                            <!-- Checkbox Switch -->
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="attr_{{ $attr->id }}"
                                    name="{{ $fieldName }}"
                                    value="1"
                                    {{ $existingValue ? 'checked' : '' }}>
                                <label class="form-check-label" for="attr_{{ $attr->id }}">
                                    {{ $attr->name }}
                                </label>
                            </div>

                            @elseif($attr->type === 'select')
                            <!-- Select -->
                            <label for="attr_{{ $attr->id }}" class="form-label">{{ $attr->name }}</label>
                            <select
                                name="{{ $fieldName }}"
                                id="attr_{{ $attr->id }}"
                                class="form-select">
                                <option value="">Selecione</option>
                                @if(isset($attr->options) && is_array($attr->options))
                                @foreach($attr->options as $option)
                                <option value="{{ $option }}" {{ $existingValue == $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                                @endforeach
                                @endif
                            </select>

                            @elseif($attr->type === 'text' || $attr->type === 'number')
                            <!-- Text/Number Input -->
                            <label for="attr_{{ $attr->id }}" class="form-label">{{ $attr->name }}</label>
                            <input
                                type="{{ $attr->type }}"
                                name="{{ $fieldName }}"
                                id="attr_{{ $attr->id }}"
                                class="form-control"
                                value="{{ $existingValue }}"
                                placeholder="{{ $attr->name }}">
                            @endif
                        </div>
                        @endforeach
                        @endforeach
                        @else
                        <div class="col-12">
                            <p class="text-muted mb-0">Nenhum atributo disponível</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Coluna Lateral (Direita) -->
        <div class="col-lg-4">

            {{-- SECÇÃO: Preço Total --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-calculator"></i>
                        Resumo Financeiro
                    </h5>
                </div>

                <div class="price-summary">
                    <div class="price-row">
                        <span class="price-label">Valor do Veículo:</span>
                        <span class="price-value" id="carValueDisplay">0,00 €</span>
                    </div>
                    <div class="price-row">
                        <span class="price-label">Custos de Importação:</span>
                        <span class="price-value" id="importCostDisplay">0,00 €</span>
                    </div>
                    <hr>
                    <div class="price-row total">
                        <span class="price-label"><strong>TOTAL:</strong></span>
                        <span class="price-value"><strong id="grandTotal">0,00 €</strong></span>
                    </div>
                </div>

                <div class="alert alert-light mt-3 mb-0">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Valor indicativo. Sujeito a confirmação.
                    </small>
                </div>
            </div>

            {{-- SECÇÃO: Ações --}}
            @include('components.admin.action-card', [
            'cancelButtonHref' => route('admin.v2.proposals.index'),
            'submitButtonLabel' => isset($proposal) ? 'Atualizar Proposta' : 'Criar Proposta',
            'timestamps' => isset($proposal) ? [
            'created_at' => $proposal->created_at,
            'updated_at' => $proposal->updated_at
            ] : null
            ])

            {{-- SECÇÃO: Upload de Imagem --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-image"></i>
                        Imagem do Veículo
                    </h5>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Imagem do veículo</label>
                    <input
                        type="file"
                        name="image"
                        id="image"
                        class="form-control @error('image') is-invalid @enderror"
                        accept="image/*">
                    <small class="text-muted">Formatos aceites: JPG, PNG, WEBP, GIF, SVG, AVIF (máx 16MB). Se adicionar uma nova imagem, substitui a anterior.</small>
                    @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Preview de imagem existente -->
                @if(isset($proposal) && $proposal->images)
                <div class="existing-image">
                    <label class="form-label">Imagem atual:</label>
                    <div class="image-preview">
                        <img src="{{ asset('storage/' . $proposal->images) }}" alt="Imagem" style="max-width: 300px; border-radius: 8px;">
                    </div>
                </div>
                @endif
            </div>

            {{-- SECÇÃO: Quick Info --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-lightbulb"></i>
                        Dicas
                    </h5>
                </div>

                <ul class="list-unstyled mb-0 small text-muted">
                    <li class="mb-2">
                        <i class="bi bi-check2 text-success me-2"></i>
                        Preencha todos os campos obrigatórios
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check2 text-success me-2"></i>
                        Adicione o máximo de informação possível
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check2 text-success me-2"></i>
                        Use o botão "Calcular Total" para verificar custos
                    </li>
                    <li>
                        <i class="bi bi-check2 text-success me-2"></i>
                        Adicione uma imagem de qualidade do veículo
                    </li>
                </ul>
            </div>

        </div>
    </div>

</form>

@endsection

@push('styles')
<style>
    /* Image preview */
    .image-preview {
        width: 100%;
        height: 100px;
        border-radius: 8px;
        overflow: hidden;
        background: #f8f9fa;
        border: 2px solid #dee2e6;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Form enhancements */
    .form-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 0.25rem rgba(110, 7, 7, 0.1);
    }

    /* Required indicator */
    .text-danger {
        color: var(--admin-primary) !important;
    }

    /* Attribute Groups */
    .attribute-group-header {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--admin-secondary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .attribute-group-header i {
        color: var(--admin-primary);
    }

    /* Form switches modernos */
    .form-check-input:checked {
        background-color: var(--admin-primary);
        border-color: var(--admin-primary);
    }

    .form-check-input:focus {
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 0.25rem rgba(110, 7, 7, 0.1);
    }

    /* Price Summary Card */
    .price-summary {
        padding: 1rem 0;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .price-row:last-child {
        border-bottom: none;
    }

    .price-row.total {
        padding-top: 1rem;
        font-size: 1.25rem;
        color: var(--admin-primary);
    }

    .price-label {
        color: #666;
        font-size: 0.95rem;
    }

    .price-value {
        font-weight: 600;
        color: var(--admin-secondary);
        font-size: 1rem;
    }

    .price-row.total .price-value {
        color: var(--admin-primary);
        font-size: 1.5rem;
    }

    /* Collapse button animation */
    [data-bs-toggle="collapse"] i {
        transition: transform 0.3s ease;
    }

    [data-bs-toggle="collapse"][aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    /* Mobile adjustments */
    @media (max-width: 768px) {
        .attribute-group-header {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }

        .price-row {
            padding: 0.5rem 0;
        }

        .price-row.total {
            font-size: 1.1rem;
        }

        .price-row.total .price-value {
            font-size: 1.25rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    /**
     * ==============================================================
     * FORM JAVASCRIPT
     * ==============================================================
     */

    document.addEventListener('DOMContentLoaded', function() {

        /**
         * MARCA/MODELO CASCADE
         * Quando marca é selecionada, carrega modelos correspondentes
         */
        const brandSelect = document.getElementById('brand');
        const modelSelect = document.getElementById('model');

        brandSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const models = JSON.parse(selectedOption.dataset.models || '[]');

            // Limpar modelos existentes
            modelSelect.innerHTML = '<option value="">Selecione o modelo</option>';

            // Adicionar novos modelos
            models.forEach(model => {
                const option = document.createElement('option');
                option.value = model;
                option.textContent = model;
                modelSelect.appendChild(option);
            });

            // Habilitar select de modelo
            modelSelect.disabled = models.length === 0;
        });

        // Se estiver em modo de edição, trigger change para carregar modelos
        @if(isset($proposal) && $proposal->brand)
        brandSelect.dispatchEvent(new Event('change'));
        modelSelect.value = '{{ $proposal->model }}';
        @endif

        /**
         * CÁLCULO AUTOMÁTICO DE CUSTOS
         * Soma todos os custos de importação
         */
        function calculateTotal() {
            const costInputs = document.querySelectorAll('.cost-input');
            let totalImport = 0;

            costInputs.forEach(input => {
                const value = parseFloat(input.value) || 0;
                totalImport += value;
            });

            // Obter valor do carro
            const carValue = parseFloat(document.getElementById('proposed_car_value').value) || 0;

            // Exibir custos de importação
            document.getElementById('totalCost').textContent =
                totalImport.toLocaleString('pt-PT', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' €';

            // Atualizar resumo financeiro (sidebar)
            document.getElementById('carValueDisplay').textContent =
                carValue.toLocaleString('pt-PT', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' €';

            document.getElementById('importCostDisplay').textContent =
                totalImport.toLocaleString('pt-PT', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' €';

            // Calcular e exibir total geral
            const grandTotal = carValue + totalImport;
            document.getElementById('grandTotal').textContent =
                grandTotal.toLocaleString('pt-PT', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' €';
        }

        // Expor função globalmente
        window.calculateTotal = calculateTotal;

        // Calcular ao carregar página
        calculateTotal();

        // Recalcular quando qualquer input mudar
        document.querySelectorAll('.cost-input').forEach(input => {
            input.addEventListener('input', calculateTotal);
        });

        // Recalcular quando valor do carro mudar
        const carValueInput = document.getElementById('proposed_car_value');
        if (carValueInput) {
            carValueInput.addEventListener('input', calculateTotal);
        }

        /**
         * PREVIEW DE IMAGENS
         * Mostra preview das imagens antes de upload
         */
        const imageInput = document.getElementById('images');

        imageInput.addEventListener('change', function(e) {
            // TODO: Implementar preview de imagens selecionadas
            const files = Array.from(e.target.files);
            console.log(`${files.length} imagens selecionadas`);
        });

        /**
         * VALIDAÇÃO ANTES DE SUBMIT
         */
        const form = document.getElementById('proposalForm');

        form.addEventListener('submit', function(e) {
            // Validações adicionais podem ser adicionadas aqui
            // Por enquanto, deixar browser fazer validação nativa
        });
    });
</script>
@endpush