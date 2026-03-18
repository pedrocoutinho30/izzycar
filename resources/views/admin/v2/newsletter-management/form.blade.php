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

<div class="form-container">
    <form action="{{ isset($newsletter) ? route('admin.v2.newsletter-management.update', $newsletter->id) : route('admin.v2.newsletter-management.store') }}" method="POST">
        @csrf
        @if(isset($newsletter))
            @method('PUT')
        @endif

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
                        <label class="form-label required">Título</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $newsletter->title ?? '') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.v2.newsletter-management.index') }}" class="btn-secondary-modern">
                <i class="bi bi-x-lg"></i>
                <span>Cancelar</span>
            </a>
            <button type="submit" class="btn-primary-modern">
                <i class="bi bi-check-lg"></i>
                <span>{{ isset($newsletter) ? 'Atualizar' : 'Criar' }} Newsletter</span>
            </button>
        </div>
    </form>
</div>
@endsection
