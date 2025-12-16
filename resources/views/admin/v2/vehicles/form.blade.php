@extends('layouts.admin-v2')

@section('title', isset($vehicle) ? 'Editar Veículo' : 'Novo Veículo')

@section('content')

<!-- Page Header -->
@php
$existAction = isset($vehicle) ? 'Editar' : 'Criar';
@endphp
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => ''],
['icon' => 'bi bi-front-car', 'label' => 'Veículos', 'href' => route('admin.v2.vehicles.index')],
['icon' => '', 'label' => $existAction]
],
'title' => $existAction . ' Veículo',
'subtitle' => "",
'actionHref' => "",
'actionLabel' => ''
])


<!-- FORMULÁRIO -->
<form action="{{ isset($vehicle) ? route('admin.v2.vehicles.update', $vehicle->id) : route('admin.v2.vehicles.store') }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($vehicle))
    @method('PUT')
    @endif
    <div class="row g-4">
        <!-- Coluna Principal (Esquerda) -->
        <div class="col-lg-8">

            <!-- Identificação -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-info-circle"></i>
                        Detalhes do Veículo
                    </h5>
                </div>

                <div class="row g-3">


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
                                {{ (old('brand', $vehicle->brand ?? '') == $brand->name) ? 'selected' : '' }}
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


                    <div class="col-md-4">
                        <label class="form-label">Versão</label>
                        <input type="text" name="version" class="form-control @error('version') is-invalid @enderror"
                            value="{{ old('version', $vehicle->version ?? '') }}">
                        @error('version')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label ">Referência</label>
                        <input type="text" readonly disabled name="reference" class="form-control @error('reference') is-invalid @enderror"
                            value="{{ old('reference', $vehicle->reference ?? '') }}">
                        @error('reference')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Matrícula</label>
                        <input type="text" name="registration" class="form-control @error('registration') is-invalid @enderror"
                            value="{{ old('registration', $vehicle->registration ?? '') }}">
                        @error('registration')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">VIN</label>
                        <input type="text" name="vin" class="form-control @error('vin') is-invalid @enderror"
                            value="{{ old('vin', $vehicle->vin ?? '') }}">
                        @error('vin')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Ano</label>
                        <input type="number" name="year" class="form-control @error('year') is-invalid @enderror"
                            value="{{ old('year', $vehicle->year ?? '') }}" min="1900" max="{{ date('Y') + 1 }}">
                        @error('year')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Cor</label>
                        <input type="text" name="color" class="form-control @error('color') is-invalid @enderror"
                            value="{{ old('color', $vehicle->color ?? '') }}">
                        @error('color')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Combustível</label>
                        <select name="fuel" class="form-select @error('fuel') is-invalid @enderror">
                            <option value="">Selecione...</option>
                            <option value="Gasolina" {{ old('fuel', $vehicle->fuel ?? '') === 'Gasolina' ? 'selected' : '' }}>Gasolina</option>
                            <option value="Diesel" {{ old('fuel', $vehicle->fuel ?? '') === 'Diesel' ? 'selected' : '' }}>Diesel</option>
                            <option value="Híbrido" {{ old('fuel', $vehicle->fuel ?? '') === 'Híbrido' ? 'selected' : '' }}>Híbrido</option>
                            <option value="Hibrido-plug-in/Gasolina" {{ old('fuel', $vehicle->fuel ?? '') === 'Hibrido-plug-in/Gasolina' ? 'selected' : '' }}>Hibrido-plug-in/Gasolina</option>
                            <option value="Hibrido-plug-in/Diesel" {{ old('fuel', $vehicle->fuel ?? '') === 'Hibrido-plug-in/Diesel' ? 'selected' : '' }}>Hibrido-plug-in/Diesel</option>
                            <option value="Elétrico" {{ old('fuel', $vehicle->fuel ?? '') === 'Elétrico' ? 'selected' : '' }}>Elétrico</option>
                            <option value="GPL" {{ old('fuel', $vehicle->fuel ?? '') === 'GPL' ? 'selected' : '' }}>GPL</option>
                            <option value="GPL" {{ old('fuel', $vehicle->fuel ?? '') === 'GPL' ? 'selected' : '' }}>GPL</option>
                            <option value="Outro" {{ old('fuel', $vehicle->fuel ?? '') === 'Outro' ? 'selected' : '' }}>Outro</option>
                        </select>
                        @error('fuel')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Quilómetros</label>
                        <input type="number" name="kilometers" class="form-control @error('kilometers') is-invalid @enderror"
                            value="{{ old('kilometers', $vehicle->kilometers ?? '') }}" min="0">
                        @error('kilometers')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Potência (cv)</label>
                        <input type="number" name="power" class="form-control @error('power') is-invalid @enderror"
                            value="{{ old('power', $vehicle->power ?? '') }}" min="0">
                        @error('power')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Cilindrada (cc)</label>
                        <input type="number" name="cylinder_capacity" class="form-control @error('cylinder_capacity') is-invalid @enderror"
                            value="{{ old('cylinder_capacity', $vehicle->cylinder_capacity ?? '') }}" min="0">
                        @error('cylinder_capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Data de Fabrico</label>
                        <input type="date" name="manufacture_date" class="form-control @error('manufacture_date') is-invalid @enderror"
                            value="{{ old('manufacture_date', $vehicle->manufacture_date ?? '') }}">
                        @error('manufacture_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Data de Registo</label>
                        <input type="date" name="register_date" class="form-control @error('register_date') is-invalid @enderror"
                            value="{{ old('register_date', $vehicle->register_date ?? '') }}">
                        @error('register_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Compra e Venda -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-cash-coin"></i>
                        Preços
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Preço de Compra (€)</label>
                        <input type="number" name="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror"
                            value="{{ old('purchase_price', $vehicle->purchase_price ?? '') }}" step="0.01" min="0">
                        @error('purchase_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Preço de Venda (€)</label>
                        <input type="number" name="sell_price" class="form-control @error('sell_price') is-invalid @enderror"
                            value="{{ old('sell_price', $vehicle->sell_price ?? '') }}" step="0.01" min="0">
                        @error('sell_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Data de Compra</label>
                        <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror"
                            value="{{ old('purchase_date', $vehicle->purchase_date ?? '') }}">
                        @error('purchase_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Fornecedor</label>
                        <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                            <option value="">Selecione...</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $vehicle->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->company_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tipo de Compra</label>
                        <select class="form-control rounded shadow-sm @error('purchase_type') is-invalid @enderror" id="purchase_type" name="purchase_type" required>
                            <option value="" {{ old('purchase_type', $vehicle->purchase_type ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                            <option value="Margem" {{ old('purchase_type', $vehicle->purchase_type ?? '') == 'Margem' ? 'selected' : '' }}>Margem</option>
                            <option value="Geral" {{ old('purchase_type', $vehicle->purchase_type ?? '') == 'Geral' ? 'selected' : '' }}>Geral</option>
                            <option value="Sem Iva" {{ old('purchase_type', $vehicle->purchase_type ?? '') == 'Sem Iva' ? 'selected' : '' }}>Sem Iva</option>
                        </select>
                        @error('purchase_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tipo de Negócio</label>
                        <select name="business_type" class="form-control rounded shadow-sm">
                            <option value="novo" {{ isset($vehicle) && $vehicle->business_type == 'novo' ? 'selected' : '' }}>Novo</option>
                            <option value="usado" {{ isset($vehicle) && $vehicle->business_type == 'usado' ? 'selected' : '' }}>Usado</option>
                            <option value="seminovo" {{ isset($vehicle) && $vehicle->business_type == 'seminovo' ? 'selected' : '' }}>Semi-novo</option>
                        </select>
                        @error('business_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Data Disponível para Venda</label>
                        <input type="date" name="available_to_sell_date" class="form-control @error('available_to_sell_date') is-invalid @enderror"
                            value="{{ old('available_to_sell_date', $vehicle->available_to_sell_date ?? '') }}">
                        @error('available_to_sell_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                                <hr>
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
        <div class="col-lg-4">
            <!-- BOTÕES DE AÇÃO -->
            @include('components.admin.action-card', [
            'cancelButtonHref' => route('admin.v2.vehicles.index'),
            'submitButtonLabel' => isset($vehicle) ? 'Atualizar Veículo' : 'Criar Veículo',
            'timestamps' => isset($vehicle) ? [
            'created_at' => $vehicle->created_at,
            'updated_at' => $vehicle->updated_at
            ] : null
            ])
            <!-- Imagens -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-image"></i>
                        Imagem do Veículo
                    </h5>
                </div>

                <div class="row g-3">
                    @if(isset($vehicle) && $vehicle->images->count() > 0)
                    <div class="col-12">
                        <label class="form-label">Imagens Atuais (Arraste para reordenar)</label>
                        <div id="existing-images" class="row g-2">
                            @foreach($vehicle->images->sortBy('order') as $image)
                            <div class="col-md-3 image-item" data-image-id="{{ $image->id }}">
                                <div class="position-relative">
                                    <img src="{{ asset('storage/' . $image->path) }}" class="img-thumbnail w-100" alt="Imagem do veículo" style="cursor: move;">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-image" data-image-id="{{ $image->id }}">
                                        <i class="bi bi-x"></i>
                                    </button>
                                    <input type="hidden" name="existing_images[]" value="{{ $image->id }}">
                                    <div class="text-center mt-1">
                                        <small class="text-muted">#{{ $loop->iteration }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <input type="hidden" id="images-order" name="images_order" value="">
                        <input type="hidden" id="images-to-remove" name="images_to_remove" value="">
                    </div>
                    @endif

                    <div class="col-12">
                        <label class="form-label">{{ isset($vehicle) ? 'Adicionar Novas Imagens' : 'Imagens do Veículo' }}</label>
                        <input type="file" name="new_images[]" id="new-images-input" class="form-control @error('new_images.*') is-invalid @enderror"
                            multiple accept="image/*">
                        <small class="form-text text-muted">
                            Pode selecionar múltiplas imagens (JPEG, PNG, WebP - máx. 5MB cada)
                        </small>
                        @error('new_images.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Opções -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-toggles"></i>
                        Opções
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="hidden" name="consigned_vehicle" value="0">
                            <input class="form-check-input" type="checkbox" name="consigned_vehicle" id="consigned_vehicle" value="1"
                                {{ old('consigned_vehicle', $vehicle->consigned_vehicle ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="consigned_vehicle">
                                Veículo à Consignação
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="hidden" name="show_online" value="0">
                            <input class="form-check-input" type="checkbox" name="show_online" id="show_online" value="1"
                                {{ old('show_online', $vehicle->show_online ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_online">
                                Mostrar Online
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-toggles"></i>
                        Garantia
                    </h5>
                </div> -->

                <!-- <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tipo de Garantia</label>
                        <select name="warranty_type" class="form-select @error('warranty_type') is-invalid @enderror">
                            <option value="">Selecione...</option>
                            <option value="Garantia marca" {{ old('warranty_type', $vehicle->warranty_type ?? '') == 'Garantia marca' ? 'selected' : '' }}>Garantia marca</option>
                            <option value="Garantia stand" {{ old('warranty_type', $vehicle->warranty_type ?? '') == 'Garantia stand' ? 'selected' : '' }}>Garantia stand</option>
                        </select>
                        @error('warranty_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Preço</label>
                        <input type="number" name="warranty_price" class="form-control @error('warranty_price') is-invalid @enderror"
                            value="{{ old('warranty_price', $vehicle->warranty_price ?? '') }}" step="0.01" min="0">
                        @error('warranty_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Data início</label>
                        <input type="date" name="warranty_start_date" class="form-control @error('warranty_start_date') is-invalid @enderror"
                            value="{{ old('warranty_start_date', $vehicle->warranty_start_date ?? '') }}">
                        @error('warranty_start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Data fim</label>
                        <input type="date" name="warranty_end_date" class="form-control @error('warranty_end_date') is-invalid @enderror"
                            value="{{ old('warranty_end_date', $vehicle->warranty_end_date ?? '') }}">
                        @error('warranty_end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                </div>
            </div>
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-toggles"></i>
                        Pagamento
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tipo de Pagamento</label>
                        <select name="payment_type" class="form-select @error('payment_type') is-invalid @enderror">
                            <option value="">Selecione...</option>
                            <option value="Pronto Pagamento" {{ old('payment_type', $vehicle->payment_type ?? '') == 'Pronto Pagamento' ? 'selected' : '' }}>Pronto Pagamento</option>
                            <option value="Financiamento" {{ old('payment_type', $vehicle->payment_type ?? '') == 'Financiamento' ? 'selected' : '' }}>Financiamento</option>
                        </select>
                        @error('payment_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</form>

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

    /* Estilos para gestão de imagens */
    .image-item {
        transition: all 0.3s ease;
    }

    .image-item img {
        cursor: move;
        transition: transform 0.2s;
    }

    .image-item:hover img {
        transform: scale(1.02);
    }

    .sortable-ghost {
        opacity: 0.4;
    }

    .remove-image {
        width: 28px;
        height: 28px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        opacity: 0.9;
    }

    .remove-image:hover {
        opacity: 1;
        transform: scale(1.1);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

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
        @if(isset($vehicle) && $vehicle->brand)
        brandSelect.dispatchEvent(new Event('change'));
        modelSelect.value = '{{ $vehicle->model }}';
        @endif

        /**
         * GESTÃO DE IMAGENS
         * Drag and drop para reordenar, botões para remover
         */
        const existingImagesContainer = document.getElementById('existing-images');
        const imagesToRemoveInput = document.getElementById('images-to-remove');
        const imagesOrderInput = document.getElementById('images-order');
        const imagesToRemove = new Set();

        if (existingImagesContainer) {
            // Inicializar Sortable para drag & drop
            new Sortable(existingImagesContainer, {
                animation: 150,
                handle: 'img',
                ghostClass: 'sortable-ghost',
                onEnd: function() {
                    updateImagesOrder();
                }
            });

            // Adicionar event listeners aos botões de remover
            document.querySelectorAll('.remove-image').forEach(button => {
                button.addEventListener('click', function() {
                    const imageId = this.dataset.imageId;
                    const imageItem = this.closest('.image-item');

                    if (confirm('Tem certeza que deseja remover esta imagem?')) {
                        imagesToRemove.add(imageId);
                        imagesToRemoveInput.value = Array.from(imagesToRemove).join(',');
                        imageItem.style.display = 'none';
                        updateImagesOrder();
                    }
                });
            });

            // Atualizar ordem das imagens
            function updateImagesOrder() {
                const visibleImages = Array.from(existingImagesContainer.querySelectorAll('.image-item'))
                    .filter(item => item.style.display !== 'none')
                    .map(item => item.dataset.imageId);

                imagesOrderInput.value = visibleImages.join(',');
            }

            // Inicializar ordem
            updateImagesOrder();
        }
    });
</script>
@endpush
@endsection