@extends('layouts.admin-v2')

@section('title', isset($attribute) ? 'Editar Atributo' : 'Novo Atributo')

@section('content')

<!-- Page Header -->
@php
$existAction = isset($attribute) ? 'Editar' : 'Criar';
@endphp
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-tags', 'label' => 'Atributos de Veículos', 'href' => route('admin.v2.vehicle-attributes.index')],
        ['icon' => '', 'label' => $existAction]
    ],
    'title' => $existAction . ' Atributo de Veículo',
    'subtitle' => '',
    'actionHref' => '',
    'actionLabel' => ''
])

<!-- Formulário -->
<form action="{{ isset($attribute) ? route('admin.v2.vehicle-attributes.update', $attribute->id) : route('admin.v2.vehicle-attributes.store') }}" 
      method="POST">
    @csrf
    @if(isset($attribute))
        @method('PUT')
    @endif

    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-tags"></i>
                        Informações do Atributo
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $attribute->name ?? '') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="key" class="form-label">Chave <span class="text-danger">*</span></label>
                        <input type="text" name="key" id="key" 
                               class="form-control @error('key') is-invalid @enderror" 
                               value="{{ old('key', $attribute->key ?? '') }}" required>
                        @error('key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Identificador único (ex: cor, combustivel)</small>
                    </div>

                    <div class="col-md-4">
                        <label for="type" class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">Selecione</option>
                            <option value="text" {{ old('type', $attribute->type ?? '') == 'text' ? 'selected' : '' }}>Texto</option>
                            <option value="number" {{ old('type', $attribute->type ?? '') == 'number' ? 'selected' : '' }}>Número</option>
                            <option value="boolean" {{ old('type', $attribute->type ?? '') == 'boolean' ? 'selected' : '' }}>Boolean</option>
                            <option value="select" {{ old('type', $attribute->type ?? '') == 'select' ? 'selected' : '' }}>Select</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="attribute_group" class="form-label">Grupo</label>
                        <select name="attribute_group" id="attribute_group" class="form-select">
                            <option value="">Sem grupo</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->name }}" 
                                    {{ old('attribute_group', $attribute->attribute_group ?? '') == $group->name ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="order" class="form-label">Ordem</label>
                        <input type="number" name="order" id="order" 
                               class="form-control @error('order') is-invalid @enderror" 
                               value="{{ old('order', $attribute->order ?? 0) }}">
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12" id="options-field" style="display: none;">
                        <label for="options" class="form-label">Opções (separadas por vírgula)</label>
                        <input type="text" name="options" id="options" 
                               class="form-control @error('options') is-invalid @enderror" 
                               value="{{ old('options', isset($attribute) && $attribute->options ? (is_array($attribute->options) ? implode(',', $attribute->options) : $attribute->options) : '') }}">
                        @error('options')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Ex: Vermelho, Azul, Verde</small>
                    </div>

                    <div class="col-md-6">
                        <label for="field_name_autoscout" class="form-label">Campo AutoScout</label>
                        <input type="text" name="field_name_autoscout" id="field_name_autoscout" 
                               class="form-control" 
                               value="{{ old('field_name_autoscout', $attribute->field_name_autoscout ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="field_name_mobile" class="form-label">Campo Mobile.de</label>
                        <input type="text" name="field_name_mobile" id="field_name_mobile" 
                               class="form-control" 
                               value="{{ old('field_name_mobile', $attribute->field_name_mobile ?? '') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Lateral -->
        <div class="col-lg-4">
            @include('components.admin.action-card', [
                'cancelButtonHref' => route('admin.v2.vehicle-attributes.index'),
                'submitButtonLabel' => isset($attribute) ? 'Atualizar Atributo' : 'Criar Atributo',
                'timestamps' => isset($attribute) ? [
                    'created_at' => $attribute->created_at,
                    'updated_at' => $attribute->updated_at
                ] : null
            ])
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const optionsField = document.getElementById('options-field');

    function toggleOptionsField() {
        if (typeSelect.value === 'select') {
            optionsField.style.display = 'block';
        } else {
            optionsField.style.display = 'none';
        }
    }

    typeSelect.addEventListener('change', toggleOptionsField);
    toggleOptionsField(); // Check on load
});
</script>
@endpush

@endsection
