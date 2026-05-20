@extends('layouts.admin-v2')

@section('title', isset($vehicle) ? 'Editar Veículo' : 'Novo Veículo')

@section('content')

<!-- Page Header -->
@php
$existAction = isset($vehicle) ? 'Editar' : 'Criar';
@endphp
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
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
                        <label class="form-label">Preço de Compra (€)
                            <i class="bi bi-info-circle text-muted ms-1" id="purchase-price-icon" style="cursor:pointer" data-bs-toggle="tooltip" title=""></i>
                        </label>
                        <input type="number" name="purchase_price" id="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror"
                            value="{{ old('purchase_price', $vehicle->purchase_price ?? '') }}" step="0.01" min="0">
                        <div id="purchase-price-hint" class="form-text mt-1"></div>
                        <div id="purchase-price-geral-calc" class="mt-1 small text-muted d-none">
                            Total com IVA (23%): <strong id="purchase-price-gross">—</strong>
                        </div>
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

                    {{-- Taxa de IVA do Regime Geral (só visível quando Tipo = Geral) --}}
                    <div class="col-md-6" id="purchase_vat_rate_group" style="display:none;">
                        <label class="form-label">Taxa de IVA (Regime Geral) <span class="text-danger">*</span></label>
                        <select class="form-control rounded shadow-sm @error('purchase_vat_rate') is-invalid @enderror" id="purchase_vat_rate" name="purchase_vat_rate">
                            <option value="">Selecione</option>
                            @foreach([6, 13, 19, 20, 23] as $rate)
                                <option value="{{ $rate }}" {{ old('purchase_vat_rate', $vehicle->purchase_vat_rate ?? '') == $rate ? 'selected' : '' }}>{{ $rate }}%</option>
                            @endforeach
                        </select>
                        @error('purchase_vat_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted">Taxa de IVA aplicada na compra (o preço inserido é líquido, sem IVA).</div>
                    </div>

                    {{-- IVA pago na compra (só visível quando Tipo = Geral) --}}
                    <div class="col-md-6" id="purchase_vat_paid_group" style="display:none;">
                        <label class="form-label">IVA Pago na Compra (€)</label>
                        <input type="number" name="purchase_vat_paid" id="purchase_vat_paid"
                            class="form-control rounded shadow-sm @error('purchase_vat_paid') is-invalid @enderror"
                            value="{{ old('purchase_vat_paid', $vehicle->purchase_vat_paid ?? '') }}"
                            step="0.01" min="0" placeholder="0.00">
                        @error('purchase_vat_paid')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted">
                            Valor de IVA efetivamente pago na compra. Se preenchido, será deduzido ao IVA a pagar na venda.
                            Deixe em branco se o IVA ainda não foi pago (ex: importação).
                        </div>
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

            @if(isset($vehicle))
            <div class="d-grid mb-3">
                <button type="button" class="btn btn-outline-success btn-sm"
                        onclick="openSimuladorVenda()">
                    <i class="bi bi-calculator me-1"></i> Simular Venda
                </button>
            </div>
            @endif
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
                                    @php
                                        $baseImagePath = preg_replace('/_(thumb|medium|large)\.(avif|webp)$/', '', $image->path);
                                    @endphp
                                    <picture>
                                        <source srcset="{{ asset('storage/' . $baseImagePath . '_medium.avif') }}" type="image/avif">
                                        <source srcset="{{ asset('storage/' . $baseImagePath . '_medium.webp') }}" type="image/webp">
                                        <img src="{{ asset('storage/' . $baseImagePath . '_medium.webp') }}" class="img-thumbnail w-100" alt="Imagem do veículo" style="cursor: move;">
                                    </picture>
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

            {{-- ── Documentos do Veículo (só na edição) ───────────────────── --}}
            @if(isset($vehicle))
            @php
                $legalizationDocs = $vehicle->legalizations->flatMap(fn($l) => $l->documents->map(fn($d) => ['doc' => $d, 'legalization' => $l]));
            @endphp
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-file-earmark-text"></i>
                        Documentos
                        @php $totalDocs = $vehicle->documents->count() + $legalizationDocs->count(); @endphp
                        @if($totalDocs > 0)
                            <span class="badge bg-secondary ms-2">{{ $totalDocs }}</span>
                        @endif
                    </h5>
                </div>
                <div class="modern-card-body">

                    {{-- Documentos do veículo --}}
                    @if($vehicle->documents->count() > 0)
                    <p class="text-muted small fw-semibold mb-1 text-uppercase" style="font-size:.65rem;letter-spacing:.05em">Documentos do veículo</p>
                    <div class="list-group list-group-flush mb-3">
                        @foreach($vehicle->documents as $doc)
                        <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                <i class="bi bi-file-earmark{{ Str::endsWith($doc->nome_original, '.pdf') ? '-pdf text-danger' : ' text-secondary' }} fs-5 flex-shrink-0"></i>
                                <div class="overflow-hidden">
                                    <div class="text-truncate small fw-semibold">{{ $doc->nome_original }}</div>
                                    @if($doc->tipo)
                                        <div class="text-muted" style="font-size:.7rem">{{ \App\Models\Legalization::DOCUMENTOS[$doc->tipo] ?? $doc->tipo }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <a href="{{ route('admin.v2.vehicles.download-document', [$vehicle->id, $doc->id]) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                <form action="{{ route('admin.v2.vehicles.delete-document', [$vehicle->id, $doc->id]) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Eliminar este documento?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Documentos das legalizações --}}
                    @if($legalizationDocs->count() > 0)
                    <p class="text-muted small fw-semibold mb-1 text-uppercase" style="font-size:.65rem;letter-spacing:.05em">
                        Da legalização
                    </p>
                    <div class="list-group list-group-flush mb-3">
                        @foreach($legalizationDocs as $entry)
                        @php $doc = $entry['doc']; $leg = $entry['legalization']; @endphp
                        <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                <i class="bi bi-file-earmark{{ Str::endsWith($doc->nome_original, '.pdf') ? '-pdf text-danger' : ' text-secondary' }} fs-5 flex-shrink-0"></i>
                                <div class="overflow-hidden">
                                    <div class="text-truncate small fw-semibold">{{ $doc->nome_original }}</div>
                                    <div class="text-muted" style="font-size:.7rem">
                                        {{ \App\Models\Legalization::DOCUMENTOS[$doc->tipo] ?? $doc->tipo }}
                                        &middot;
                                        <a href="{{ route('admin.legalizations.show', $leg->id) }}" class="text-muted text-decoration-underline">
                                            Legalização #{{ $leg->id }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('admin.legalizations.download-document', [$leg->id, $doc->id]) }}"
                               class="btn btn-sm btn-outline-secondary flex-shrink-0" title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($vehicle->documents->count() === 0 && $legalizationDocs->count() === 0)
                    <p class="text-muted small mb-0">Sem documentos associados.</p>
                    @endif

                </div>
            </div>
            @endif

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
         * TIPO DE COMPRA → HINT DO PREÇO DE COMPRA
         */
        const purchaseTypeSelect = document.getElementById('purchase_type');
        const purchasePriceInput = document.getElementById('purchase_price');
        const hintDiv            = document.getElementById('purchase-price-hint');
        const geralCalcDiv       = document.getElementById('purchase-price-geral-calc');
        const geralGrossSpan     = document.getElementById('purchase-price-gross');
        const vatRateGroup       = document.getElementById('purchase_vat_rate_group');
        const vatPaidGroup        = document.getElementById('purchase_vat_paid_group');
        const vatPaidInput        = document.getElementById('purchase_vat_paid');
        const vatRateSelect      = document.getElementById('purchase_vat_rate');

        const hints = {
            'Geral': {
                html: null, // built dynamically based on selected rate
                geral: true,
            },
            'Margem': {
                html: '<span class="text-secondary"><i class="bi bi-info-circle me-1"></i>Insira o valor <u>bruto</u> total pago pelo veículo (IVA de margem — não deduz IVA da compra).</span>',
                geral: false,
            },
            'Sem Iva': {
                html: '<span class="text-secondary"><i class="bi bi-info-circle me-1"></i>Insira o valor <u>bruto</u> total pago (compra isenta de IVA — não há IVA a deduzir).</span>',
                geral: false,
            },
        };

        function getGeralHint() {
            const rate = vatRateSelect ? vatRateSelect.value : '';
            const rateLabel = rate ? rate + '%' : '?%';
            return '<span class="text-primary fw-semibold"><i class="bi bi-info-circle me-1"></i>Insira o valor <u>líquido</u> (sem IVA). O sistema irá calcular o IVA de ' + rateLabel + ' automaticamente.</span>';
        }

        function updatePurchaseHint() {
            const type = purchaseTypeSelect.value;
            const isGeral = type === 'Geral';
            vatRateGroup.style.display = isGeral ? '' : 'none';
            vatPaidGroup.style.display = isGeral ? '' : 'none';
            if (!isGeral) {
                vatRateSelect.value = '';
                if (vatPaidInput) vatPaidInput.value = '';
            }
            const cfg = hints[type];
            if (cfg) {
                hintDiv.innerHTML = isGeral ? getGeralHint() : cfg.html;
                geralCalcDiv.classList.toggle('d-none', !cfg.geral);
                if (cfg.geral) updateGeralCalc();
            } else {
                hintDiv.innerHTML = '';
                geralCalcDiv.classList.add('d-none');
            }
        }

        function updateGeralCalc() {
            const net = parseFloat(purchasePriceInput.value);
            const rate = parseFloat(vatRateSelect.value) || 23;
            if (!isNaN(net) && net > 0) {
                const gross = net * (1 + rate / 100);
                geralGrossSpan.textContent = gross.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
            } else {
                geralGrossSpan.textContent = '—';
            }
        }

        purchaseTypeSelect.addEventListener('change', updatePurchaseHint);
        vatRateSelect.addEventListener('change', function () {
            if (purchaseTypeSelect.value === 'Geral') updatePurchaseHint();
        });
        purchasePriceInput.addEventListener('input', function () {
            if (purchaseTypeSelect.value === 'Geral') updateGeralCalc();
        });

        // Inicializar na edição
        updatePurchaseHint();

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

@if(isset($vehicle))
@push('scripts')
{{-- ══════════════════════════════════════════════════════
     MODAL: SIMULADOR DE VENDA
     ══════════════════════════════════════════════════════ --}}
@php
    // Expenses already loaded via eager-load (excludes auto-generated source entries excluded by convention)
    $expensesTotal = $vehicle->expenses
        ->where('movement_type', 'expense')
        ->whereNull('source_type')
        ->sum('amount_gross');

    // purchase cost: net if Geral (IVA deductible), gross otherwise
    $purchaseNet = 0;
    if ($vehicle->purchase_price) {
        if ($vehicle->purchase_type === 'Geral' && $vehicle->purchase_vat_rate) {
            $purchaseNet = round($vehicle->purchase_price, 2); // already net
        } else {
            $purchaseNet = round($vehicle->purchase_price, 2); // gross = cost for margin
        }
    }
    $totalCosts = round($purchaseNet + $expensesTotal, 2);
@endphp

<div class="modal fade" id="simuladorVendaModal" tabindex="-1" aria-labelledby="simuladorVendaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="simuladorVendaModalLabel">
                    <i class="bi bi-calculator me-2 text-success"></i>
                    Simulador de Venda — {{ $vehicle->brand }} {{ $vehicle->model }}
                    @if($vehicle->registration) <span class="text-muted fw-normal small">({{ $vehicle->registration }})</span> @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="row g-3 mb-4">
                    {{-- ── Tipo de IVA de Venda ──────────────── --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Regime IVA de Venda</label>
                        <select id="simVatType" class="form-select form-select-sm" onchange="simCalc()">
                            <option value="margem">Margem (IVA incluído na margem)</option>
                            <option value="geral_23" selected>Regime Geral 23%</option>
                            <option value="geral_13">Regime Geral 13%</option>
                            <option value="geral_6">Regime Geral 6%</option>
                            <option value="isento">Isento de IVA</option>
                        </select>
                    </div>

                    {{-- ── Preço de venda ────────────────────── --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small" id="simSellPriceLabel">Preço de Venda (c/ IVA)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="simSellPrice" class="form-control" step="0.01" min="0"
                                   placeholder="0.00" oninput="simCalc()">
                            <span class="input-group-text">€</span>
                        </div>
                    </div>

                    {{-- ── Custos extra / correção ───────────── --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Despesas adicionais</label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="simExtraCosts" class="form-control" step="0.01" min="0"
                                   value="0" oninput="simCalc()">
                            <span class="input-group-text">€</span>
                        </div>
                    </div>
                </div>

                {{-- ── Detalhes dos custos ───────────────────── --}}
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body py-2 px-3">
                        <p class="mb-1 small fw-semibold text-muted text-uppercase" style="font-size:.65rem;letter-spacing:.05em">Estrutura de Custos</p>
                        <div class="row g-1 small">
                            <div class="col-6 col-md-3">
                                <span class="text-muted">Preço Compra</span><br>
                                <strong id="simCostPurchase">{{ number_format($purchaseNet, 2, ',', '.') }} €</strong>
                                @if($vehicle->purchase_type)
                                    <span class="badge bg-secondary ms-1" style="font-size:.6rem">{{ $vehicle->purchase_type }}</span>
                                @endif
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted">IVA Compra recuperável</span><br>
                                <strong id="simVatPurchase">{{ $vehicle->purchase_type === 'Geral' ? number_format((float)$vehicle->purchase_vat_paid, 2, ',', '.') . ' €' : '0,00 €' }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted">Despesas ({{ $vehicle->expenses->where('movement_type','expense')->whereNull('source_type')->count() }} mov.)</span><br>
                                <strong>{{ number_format($expensesTotal, 2, ',', '.') }} €</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted">Total custos base</span><br>
                                <strong id="simTotalCostsDisplay">{{ number_format($totalCosts, 2, ',', '.') }} €</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Resultados ────────────────────────────── --}}
                <div id="simResults" style="display:none">
                    <hr class="my-3">
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-white shadow-sm h-100">
                                <div class="card-body py-2">
                                    <p class="text-muted small mb-1">Receita Líquida</p>
                                    <p class="fw-bold fs-6 mb-0" id="simNetRevenue">—</p>
                                    <small class="text-muted" style="font-size:.7rem">(venda s/ IVA)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-white shadow-sm h-100">
                                <div class="card-body py-2">
                                    <p class="text-muted small mb-1">IVA a entregar</p>
                                    <p class="fw-bold fs-6 mb-0" id="simVatOwed">—</p>
                                    <small class="text-muted" style="font-size:.7rem">(venda − compra)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm h-100" id="simGrossCard">
                                <div class="card-body py-2">
                                    <p class="text-muted small mb-1">Margem Bruta</p>
                                    <p class="fw-bold fs-6 mb-0" id="simGrossMargin">—</p>
                                    <small class="text-muted" style="font-size:.7rem" id="simGrossPct">0%</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm h-100" id="simNetCard">
                                <div class="card-body py-2">
                                    <p class="text-muted small mb-1">Margem Líquida</p>
                                    <p class="fw-bold fs-6 mb-0" id="simNetMargin">—</p>
                                    <small class="text-muted" style="font-size:.7rem" id="simNetPct">0%</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Alert with interpretation --}}
                    <div id="simAlert" class="alert mt-3 mb-0 py-2 small"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
const SIM = {
    purchaseNet:     {{ $purchaseNet }},
    purchaseVatPaid: {{ (float)($vehicle->purchase_vat_paid ?? 0) }},
    purchaseType:    '{{ $vehicle->purchase_type ?? '' }}',
    expensesTotal:   {{ $expensesTotal }},
    baseCosts:       {{ $totalCosts }},
};

function openSimuladorVenda() {
    // Update extra costs to 0
    document.getElementById('simExtraCosts').value = '0';
    simCalc();
    new bootstrap.Modal(document.getElementById('simuladorVendaModal')).show();
}

function simCalc() {
    const vatType    = document.getElementById('simVatType').value;
    const sellGross  = parseFloat(document.getElementById('simSellPrice').value) || 0;
    const extraCosts = parseFloat(document.getElementById('simExtraCosts').value) || 0;
    const totalCosts = SIM.baseCosts + extraCosts;

    // Update costs display
    document.getElementById('simTotalCostsDisplay').textContent = fmt(totalCosts) + ' €';

    if (sellGross <= 0) {
        document.getElementById('simResults').style.display = 'none';
        return;
    }

    // ── Compute net revenue (excl. IVA) and VAT owed ──
    let vatRate     = 0;
    let netRevenue  = 0;
    let vatOwed     = 0;
    let vatRecoverable = 0;

    if (vatType === 'margem') {
        // Regime de Margem: IVA is inside the margin, charged on (sell - purchase gross)
        // For display, net revenue = sell price (IVA handled internally on margin)
        netRevenue = sellGross;
        const margin = sellGross - SIM.purchaseNet - extraCosts;
        // IVA de margem = margin * 23 / 123
        vatOwed = Math.max(0, margin * 23 / 123);
        vatRecoverable = 0; // no deductible purchase VAT in this regime
    } else if (vatType === 'isento') {
        netRevenue = sellGross;
        vatOwed = 0;
        vatRecoverable = 0;
    } else {
        const rates = { 'geral_23': 23, 'geral_13': 13, 'geral_6': 6 };
        vatRate = rates[vatType] || 23;
        netRevenue = sellGross / (1 + vatRate / 100);
        const vatCollected = sellGross - netRevenue;
        vatRecoverable = SIM.purchaseType === 'Geral' ? SIM.purchaseVatPaid : 0;
        vatOwed = Math.max(0, vatCollected - vatRecoverable);
    }

    // ── Margins ──
    // Gross margin = net revenue − total costs (no tax deduction)
    const grossMargin = netRevenue - totalCosts;
    // Net margin = gross margin − VAT owed (real cash out)
    const netMargin   = grossMargin - vatOwed;

    const grossPct = netRevenue > 0 ? (grossMargin / netRevenue * 100) : 0;
    const netPct   = netRevenue > 0 ? (netMargin   / netRevenue * 100) : 0;

    // ── Update DOM ──
    document.getElementById('simNetRevenue').textContent  = fmt(netRevenue) + ' €';
    document.getElementById('simVatOwed').textContent     = fmt(vatOwed) + ' €';
    document.getElementById('simGrossMargin').textContent = fmt(grossMargin) + ' €';
    document.getElementById('simNetMargin').textContent   = fmt(netMargin) + ' €';
    document.getElementById('simGrossPct').textContent    = grossPct.toFixed(1) + '%';
    document.getElementById('simNetPct').textContent      = netPct.toFixed(1) + '%';

    const grossCard = document.getElementById('simGrossCard');
    const netCard   = document.getElementById('simNetCard');
    grossCard.className = 'card border-0 shadow-sm h-100 ' + (grossMargin >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10');
    netCard.className   = 'card border-0 shadow-sm h-100 ' + (netMargin >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10');

    // ── Alert ──
    const alert = document.getElementById('simAlert');
    if (netMargin < 0) {
        alert.className = 'alert alert-danger mt-3 mb-0 py-2 small';
        alert.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Venda com prejuízo.</strong> Para atingir break-even, o preço mínimo de venda seria <strong>' + fmtBreakeven(totalCosts, vatType, SIM.purchaseNet, extraCosts, SIM.purchaseVatPaid) + '</strong>.';
    } else if (netPct < 5) {
        alert.className = 'alert alert-warning mt-3 mb-0 py-2 small';
        alert.innerHTML = '<i class="bi bi-info-circle me-1"></i>Margem líquida muito baixa (' + netPct.toFixed(1) + '%). Reveja os custos ou o preço de venda.';
    } else {
        alert.className = 'alert alert-success mt-3 mb-0 py-2 small';
        alert.innerHTML = '<i class="bi bi-check-circle me-1"></i>Operação com margem saudável de <strong>' + netPct.toFixed(1) + '%</strong>.';
    }

    document.getElementById('simResults').style.display = '';
}

function fmtBreakeven(totalCosts, vatType, purchaseNet, extraCosts, purchaseVatPaid) {
    // Minimum sell price = break-even (net margin = 0)
    // For geral: netRevenue = grossSell/(1+r); vatOwed = vatCollected - vatRecoverable
    //   grossMargin - vatOwed = 0
    //   (netRevenue - totalCosts) - (netRevenue * r/(1+r) - vatRecoverable) = 0
    //   netRevenue * (1 - r/(1+r)) = totalCosts - vatRecoverable
    //   netRevenue = (totalCosts - vatRecoverable) / (1/(1+r))
    const rates = { 'geral_23': 23, 'geral_13': 13, 'geral_6': 6 };
    if (vatType in rates) {
        const r = rates[vatType] / 100;
        const vatRecoverable = SIM.purchaseType === 'Geral' ? purchaseVatPaid : 0;
        const netMin = (totalCosts - vatRecoverable) / (1 - r / (1 + r));
        const grossMin = netMin * (1 + r);
        return fmt(grossMin) + ' € (c/ IVA)';
    }
    // margem or isento
    return fmt(totalCosts) + ' €';
}

function fmt(v) {
    return v.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
@endpush
@endif
@endsection