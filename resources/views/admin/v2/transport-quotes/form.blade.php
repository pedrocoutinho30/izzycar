@extends('layouts.admin-v2')

@section('title', isset($quote) ? 'Editar Transporte' : 'Novo Transporte')

@section('content')

<!-- PAGE HEADER -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-truck', 'label' => 'Transportes', 'href' => route('admin.transport-quotes.index')],
        ['icon' => 'bi bi-' . (isset($quote) ? 'pencil' : 'plus'), 'label' => isset($quote) ? 'Editar' : 'Novo', 'href' => ''],
    ],
    'title' => isset($quote) ? 'Editar Transporte' : 'Novo Transporte',
    'subtitle' => 'Preencha os dados do transporte',
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
                        <select name="brand"
                                id="brand"
                                class="form-select @error('brand') is-invalid @enderror"
                                required>
                            <option value="">Selecione</option>
                            @foreach($brands as $brand)
                            <option value="{{ $brand->name }}"
                                    data-models="{{ json_encode($brand->models->pluck('name')) }}"
                                    {{ old('brand', $quote->brand ?? '') == $brand->name ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('brand')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Modelo <span class="text-danger">*</span></label>
                        <select name="model"
                                id="model"
                                class="form-select @error('model') is-invalid @enderror"
                                required
                                {{ old('brand', $quote->brand ?? '') ? '' : 'disabled' }}>
                            <option value="">{{ old('brand', $quote->brand ?? '') ? 'Selecione' : 'Primeiro selecione a marca' }}</option>
                            @if(old('brand', $quote->brand ?? ''))
                                @php
                                    $selectedBrand = $brands->firstWhere('name', old('brand', $quote->brand ?? ''));
                                @endphp
                                @if($selectedBrand)
                                    @foreach($selectedBrand->models as $modelOption)
                                    <option value="{{ $modelOption->name }}" {{ old('model', $quote->model ?? '') == $modelOption->name ? 'selected' : '' }}>
                                        {{ $modelOption->name }}
                                    </option>
                                    @endforeach
                                @endif
                            @endif
                        </select>
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de Carro</label>
                        <select name="car_type"
                                class="form-select @error('car_type') is-invalid @enderror">
                            <option value="">Selecione</option>
                            @foreach(\App\Models\TransportQuote::CAR_TYPES as $type)
                            <option value="{{ $type }}" {{ old('car_type', $quote->car_type ?? '') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                            @endforeach
                        </select>
                        @error('car_type')
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
                        <label class="form-label">Localização (cidade, código postal, país) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text"
                                   name="origin_location"
                                   id="origin_location"
                                   class="form-control @error('origin_location') is-invalid @enderror"
                                   placeholder="Ex: Munique, 80331, Alemanha"
                                   value="{{ old('origin_location', $quote->origin_location ?? '') }}"
                                   required>
                            <button type="button" class="btn btn-outline-secondary" id="geocodeBtn">
                                <i class="bi bi-geo-alt"></i> Obter Coordenadas
                            </button>
                        </div>
                        @error('origin_location')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div id="geocodeResult" class="small mt-1"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="number"
                                       step="0.0000001"
                                       name="origin_latitude"
                                       id="origin_latitude"
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
                                       id="origin_longitude"
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

        <!-- Dados do Transporte -->
        <div class="col-lg-6">
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-currency-euro"></i>
                        Dados do Transporte
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
                        <label class="form-label">Data do Transporte <span class="text-danger">*</span></label>
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
            <i class="bi bi-check-lg"></i> {{ isset($quote) ? 'Atualizar' : 'Criar' }} Transporte
        </button>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const brandSelect = document.getElementById('brand');
        const modelSelect = document.getElementById('model');

        brandSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const models = JSON.parse(selectedOption.dataset.models || '[]');

            modelSelect.innerHTML = '<option value="">Selecione</option>';
            models.forEach(model => {
                const option = document.createElement('option');
                option.value = model;
                option.textContent = model;
                modelSelect.appendChild(option);
            });

            modelSelect.disabled = models.length === 0;
        });

        const geocodeBtn = document.getElementById('geocodeBtn');
        const geocodeResult = document.getElementById('geocodeResult');

        geocodeBtn.addEventListener('click', function () {
            const location = document.getElementById('origin_location').value.trim();
            if (!location) {
                geocodeResult.innerHTML = '<span class="text-danger">Escreva primeiro a localização.</span>';
                return;
            }

            const originalHtml = geocodeBtn.innerHTML;
            geocodeBtn.disabled = true;
            geocodeBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> A procurar...';
            geocodeResult.innerHTML = '';

            fetch('{{ route('admin.transport-quotes.geocode') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                },
                body: JSON.stringify({ location: location }),
            })
            .then(res => res.json().then(data => ({ ok: res.ok, data })))
            .then(({ ok, data }) => {
                geocodeBtn.disabled = false;
                geocodeBtn.innerHTML = originalHtml;

                if (!ok || data.error) {
                    geocodeResult.innerHTML = '<span class="text-danger">' + (data.error || 'Erro ao obter coordenadas.') + '</span>';
                    return;
                }

                document.getElementById('origin_latitude').value = data.lat;
                document.getElementById('origin_longitude').value = data.lng;
                geocodeResult.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Encontrado: ' + data.display_name + '</span>';
            })
            .catch(() => {
                geocodeBtn.disabled = false;
                geocodeBtn.innerHTML = originalHtml;
                geocodeResult.innerHTML = '<span class="text-danger">Erro de rede ao obter coordenadas.</span>';
            });
        });
    });
</script>
@endpush
