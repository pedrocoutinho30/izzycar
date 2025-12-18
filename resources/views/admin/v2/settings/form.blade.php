@extends('layouts.admin-v2')

@section('title', isset($setting) ? 'Editar Configuração' : 'Nova Configuração')

@section('content')

<!-- Page Header -->
@php
$existAction = isset($setting) ? 'Editar' : 'Criar';
@endphp
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-gear', 'label' => 'Configurações', 'href' => route('admin.v2.settings.index')],
        ['icon' => '', 'label' => $existAction]
    ],
    'title' => $existAction . ' Configuração',
    'subtitle' => '',
    'actionHref' => '',
    'actionLabel' => ''
])

<!-- Formulário -->
<form action="{{ isset($setting) ? route('admin.v2.settings.update', $setting->id) : route('admin.v2.settings.store') }}" 
      method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($setting))
        @method('PUT')
    @endif

    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-gear"></i>
                        Informações da Configuração
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" id="titulo" 
                               class="form-control @error('titulo') is-invalid @enderror" 
                               value="{{ old('titulo', $setting->title ?? '') }}" required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="label" class="form-label">Label/Chave <span class="text-danger">*</span></label>
                        <input type="text" name="label" id="label" 
                               class="form-control @error('label') is-invalid @enderror" 
                               value="{{ old('label', $setting->label ?? '') }}" required>
                        @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Identificador único (ex: site_name, logo)</small>
                    </div>

                    <div class="col-md-12">
                        <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                            <option value="">Selecione</option>
                            <option value="text" {{ old('tipo', $setting->type ?? '') == 'text' ? 'selected' : '' }}>Texto</option>
                            <option value="number" {{ old('tipo', $setting->type ?? '') == 'number' ? 'selected' : '' }}>Número</option>
                            <option value="boolean" {{ old('tipo', $setting->type ?? '') == 'boolean' ? 'selected' : '' }}>Boolean</option>
                            <option value="image" {{ old('tipo', $setting->type ?? '') == 'image' ? 'selected' : '' }}>Imagem</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Campo de Texto/Número -->
                    <div class="col-12" id="text-value-field">
                        <label for="valor_text" class="form-label">Valor</label>
                        <input type="text" name="valor" id="valor_text" 
                               class="form-control @error('valor') is-invalid @enderror" 
                               value="{{ old('valor', isset($setting) && in_array($setting->type, ['text', 'number']) ? $setting->value : '') }}">
                        @error('valor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Campo Boolean -->
                    <div class="col-12" id="boolean-value-field" style="display: none;">
                        <div class="form-check form-switch">
                            <input type="hidden" name="valor" value="0">
                            <input class="form-check-input" type="checkbox" name="valor" id="valor_boolean" value="1"
                                {{ old('valor', isset($setting) && $setting->type == 'boolean' && $setting->value ? 1 : 0) ? 'checked' : '' }}>
                            <label class="form-check-label" for="valor_boolean">Ativo</label>
                        </div>
                    </div>

                    <!-- Campo Imagem -->
                    <div class="col-12" id="image-value-field" style="display: none;">
                        <label for="valor_image" class="form-label">Imagem</label>
                        <input type="file" name="valor" id="valor_image" 
                               class="form-control @error('valor') is-invalid @enderror" 
                               accept="image/*">
                        @error('valor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        @if(isset($setting) && $setting->type == 'image' && $setting->value)
                        <div class="mt-3">
                            <label class="form-label">Imagem Atual:</label>
                            <div>
                                <img src="{{ asset('storage/' . $setting->value) }}" 
                                     alt="{{ $setting->title }}" 
                                     style="max-width: 300px; border-radius: 8px;">
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Lateral -->
        <div class="col-lg-4">
            @include('components.admin.action-card', [
                'cancelButtonHref' => route('admin.v2.settings.index'),
                'submitButtonLabel' => isset($setting) ? 'Atualizar Configuração' : 'Criar Configuração',
                'timestamps' => isset($setting) ? [
                    'created_at' => $setting->created_at,
                    'updated_at' => $setting->updated_at
                ] : null
            ])
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo');
    const textField = document.getElementById('text-value-field');
    const booleanField = document.getElementById('boolean-value-field');
    const imageField = document.getElementById('image-value-field');

    function toggleValueFields() {
        // Hide all
        textField.style.display = 'none';
        booleanField.style.display = 'none';
        imageField.style.display = 'none';

        // Show appropriate field
        const tipo = tipoSelect.value;
        if (tipo === 'text' || tipo === 'number') {
            textField.style.display = 'block';
            document.getElementById('valor_text').type = tipo === 'number' ? 'number' : 'text';
        } else if (tipo === 'boolean') {
            booleanField.style.display = 'block';
        } else if (tipo === 'image') {
            imageField.style.display = 'block';
        }
    }

    tipoSelect.addEventListener('change', toggleValueFields);
    toggleValueFields(); // Check on load
});
</script>
@endpush

@endsection
