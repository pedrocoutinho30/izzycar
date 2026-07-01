@extends('layouts.admin-v2')

@section('title', isset($newsletter) ? 'Editar Newsletter' : 'Nova Newsletter')

@section('content')
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-newspaper', 'label' => 'Newsletter', 'href' => route('admin.v2.newsletter-management.index')],
        ['icon' => 'bi bi-pencil', 'label' => isset($newsletter) ? 'Editar' : 'Nova', 'href' => '']
    ],
    'title' => isset($newsletter) ? 'Editar Newsletter' : 'Nova Newsletter',
    'subtitle' => 'Preencha os campos abaixo'
])

@php
    $currentTemplate = old('template', $newsletter->template ?? 'standard');
    $templates = \App\Models\Newsletter::TEMPLATES;
@endphp

<div class="form-container">
    <form action="{{ isset($newsletter) ? route('admin.v2.newsletter-management.update', $newsletter->id) : route('admin.v2.newsletter-management.store') }}" method="POST">
        @csrf
        @if(isset($newsletter))
            @method('PUT')
        @endif

        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-layout-text-window-reverse"></i>
                    Template
                </h5>
            </div>
            <div class="modern-card-body">
                <div class="row g-3">
                    @foreach($templates as $value => $label)
                        <div class="col-md-6">
                            <label class="template-option {{ $currentTemplate === $value ? 'selected' : '' }}"
                                   for="template_{{ $value }}">
                                <input type="radio" name="template" id="template_{{ $value }}"
                                       value="{{ $value }}"
                                       {{ $currentTemplate === $value ? 'checked' : '' }}
                                       class="template-radio">
                                <div class="template-option-body">
                                    @if($value === 'standard')
                                        <i class="bi bi-envelope-paper fs-3 text-primary mb-2 d-block"></i>
                                        <strong>{{ $label }}</strong>
                                        <p class="text-muted small mb-0 mt-1">Newsletter dinâmica com título, texto e ofertas de veículos.</p>
                                    @else
                                        <i class="bi bi-file-earmark-text fs-3 text-success mb-2 d-block"></i>
                                        <strong>{{ $label }}</strong>
                                        <p class="text-muted small mb-0 mt-1">Template fixo com o guia completo de custos de importação em Portugal.</p>
                                    @endif
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('template')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-info-circle"></i>
                    Informações da Newsletter
                </h5>
            </div>

            <div class="modern-card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label required">Título <span class="text-muted fw-normal">(referência interna)</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $newsletter->title ?? '') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="standardFields" class="{{ $currentTemplate !== 'standard' ? 'd-none' : '' }} col-12">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Subtítulo</label>
                                <input type="text" name="subtitle" class="form-control @error('subtitle') is-invalid @enderror"
                                       value="{{ old('subtitle', $newsletter->subtitle ?? '') }}">
                                @error('subtitle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Texto</label>
                                <textarea name="text" rows="4" class="form-control @error('text') is-invalid @enderror">{{ old('text', $newsletter->text ?? '') }}</textarea>
                                @error('text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div id="fixedTemplateNote" class="{{ $currentTemplate === 'standard' ? 'd-none' : '' }} col-12">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Este template tem conteúdo fixo. Apenas o título (referência interna) é necessário.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.v2.newsletter-management.index') }}" class="btn btn-secondary-modern">
                <i class="bi bi-x-lg"></i>
                <span>Cancelar</span>
            </a>
            <button type="submit" class="btn btn-primary-modern btn-lg">
                <i class="bi bi-check-lg"></i>
                <span>{{ isset($newsletter) ? 'Atualizar' : 'Criar' }} Newsletter</span>
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
.template-option {
    display: block;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 20px;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
    text-align: center;
    position: relative;
}
.template-option:hover {
    border-color: #990000;
}
.template-option.selected {
    border-color: #990000;
    box-shadow: 0 0 0 3px rgba(153,0,0,.12);
    background: #fff7f7;
}
.template-radio {
    position: absolute;
    top: 12px;
    right: 12px;
    accent-color: #990000;
}
.template-option-body { pointer-events: none; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const radios = document.querySelectorAll('.template-radio');
    const standardFields = document.getElementById('standardFields');
    const fixedNote = document.getElementById('fixedTemplateNote');
    const labels = document.querySelectorAll('.template-option');

    function update(value) {
        labels.forEach(l => l.classList.toggle('selected', l.querySelector('input').value === value));
        const isStandard = value === 'standard';
        standardFields.classList.toggle('d-none', !isStandard);
        fixedNote.classList.toggle('d-none', isStandard);
    }

    radios.forEach(r => r.addEventListener('change', () => update(r.value)));
    labels.forEach(l => l.addEventListener('click', () => {
        const r = l.querySelector('input');
        r.checked = true;
        update(r.value);
    }));
})();
</script>
@endpush

@endsection
