@extends('layouts.admin-v2')

@section('title', isset($quote) ? 'Editar Orçamento' : 'Novo Orçamento')

@section('content')

<!-- PAGE HEADER -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-truck', 'label' => 'Transportes', 'href' => route('admin.transport-quotes.index')],
        ['icon' => 'bi bi-' . (isset($quote) ? 'pencil' : 'plus'), 'label' => isset($quote) ? 'Editar' : 'Novo', 'href' => ''],
    ],
    'title' => isset($quote) ? 'Editar Orçamento' : 'Novo Orçamento',
    'subtitle' => 'Preencha os dados do orçamento de transporte',
    'actionHref' => '',
    'actionLabel' => ''
])

<form action="{{ isset($quote) ? route('admin.transport-quotes.update', $quote->id) : route('admin.transport-quotes.store') }}" 
      method="POST">
    @csrf
    @if(isset($quote))
        @method('PUT')
    @endif

    <div class="row">
        <!-- Dados do Veículo -->
        <div class="col-lg-6">
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-car-front"></i>
                        Dados do Veículo
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div class="mb-3">
                        <label class="form-label">Marca <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="brand" 
                               class="form-control @error('brand') is-invalid @enderror" 
                               value="{{ old('brand', $quote->brand ?? '') }}" 
                               required>
                        @error('brand')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Modelo <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="model" 
                               class="form-control @error('model') is-invalid @enderror" 
                               value="{{ old('model', $quote->model ?? '') }}" 
                               required>
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Dados da Origem -->
        <div class="col-lg-6">
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-geo-alt"></i>
                        Origem
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div class="mb-3">
                        <label class="form-label">Cidade <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="origin_city" 
                               class="form-control @error('origin_city') is-invalid @enderror" 
                               value="{{ old('origin_city', $quote->origin_city ?? '') }}" 
                               required>
                        @error('origin_city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">País <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="origin_country" 
                               class="form-control @error('origin_country') is-invalid @enderror" 
                               value="{{ old('origin_country', $quote->origin_country ?? '') }}" 
                               required>
                        @error('origin_country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Código Postal</label>
                        <input type="text" 
                               name="origin_postal_code" 
                               class="form-control @error('origin_postal_code') is-invalid @enderror" 
                               value="{{ old('origin_postal_code', $quote->origin_postal_code ?? '') }}">
                        @error('origin_postal_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="number" 
                                       step="0.0000001"
                                       name="origin_latitude" 
                                       class="form-control @error('origin_latitude') is-invalid @enderror" 
                                       value="{{ old('origin_latitude', $quote->origin_latitude ?? '') }}">
                                @error('origin_latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="number" 
                                       step="0.0000001"
                                       name="origin_longitude" 
                                       class="form-control @error('origin_longitude') is-invalid @enderror" 
                                       value="{{ old('origin_longitude', $quote->origin_longitude ?? '') }}">
                                @error('origin_longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dados do Orçamento -->
        <div class="col-lg-6">
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-currency-euro"></i>
                        Dados do Orçamento
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div class="mb-3">
                        <label class="form-label">Transportadora <span class="text-danger">*</span></label>
                        <select name="supplier_id" 
                                class="form-select @error('supplier_id') is-invalid @enderror" 
                                required>
                            <option value="">Selecione...</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" 
                                        {{ old('supplier_id', $quote->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->company_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Preço (€) <span class="text-danger">*</span></label>
                        <input type="number" 
                               step="0.01"
                               name="price" 
                               class="form-control @error('price') is-invalid @enderror" 
                               value="{{ old('price', $quote->price ?? '') }}" 
                               required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Data do Orçamento <span class="text-danger">*</span></label>
                        <input type="date" 
                               name="quote_date" 
                               class="form-control @error('quote_date') is-invalid @enderror" 
                               value="{{ old('quote_date', isset($quote) ? $quote->quote_date->format('Y-m-d') : date('Y-m-d')) }}" 
                               required>
                        @error('quote_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Prazo de Entrega (dias)</label>
                        <input type="number" 
                               name="estimated_delivery_days" 
                               class="form-control @error('estimated_delivery_days') is-invalid @enderror" 
                               value="{{ old('estimated_delivery_days', $quote->estimated_delivery_days ?? '') }}">
                        @error('estimated_delivery_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Observações -->
        <div class="col-lg-6">
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-chat-left-text"></i>
                        Informações Adicionais
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div class="mb-3">
                        <label class="form-label">Destino</label>
                        <input type="text" 
                               class="form-control" 
                               value="Oliveira de Azeméis, Portugal" 
                               disabled>
                        <small class="text-muted">O destino é fixo para todos os orçamentos</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea name="observations" 
                                  rows="5" 
                                  class="form-control @error('observations') is-invalid @enderror">{{ old('observations', $quote->observations ?? '') }}</textarea>
                        @error('observations')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="d-flex gap-2 justify-content-end">
        <a href="{{ route('admin.transport-quotes.index') }}" class="btn btn-secondary">
            <i class="bi bi-x-lg"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> {{ isset($quote) ? 'Atualizar' : 'Criar' }} Orçamento
        </button>
    </div>
</form>

@endsection
