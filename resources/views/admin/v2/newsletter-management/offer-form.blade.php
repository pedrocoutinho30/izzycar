@extends('layouts.admin-v2')

@section('title', isset($offer) ? 'Editar Oferta' : 'Nova Oferta')

@section('content')
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-newspaper', 'label' => 'Newsletter', 'href' => route('admin.v2.newsletter-management.index')],
        ['icon' => 'bi bi-eye', 'label' => $newsletter->title, 'href' => route('admin.v2.newsletter-management.show', $newsletter->id)],
        ['icon' => 'bi bi-pencil', 'label' => isset($offer) ? 'Editar Oferta' : 'Nova Oferta', 'href' => '']
    ],
    'title' => isset($offer) ? 'Editar Oferta' : 'Nova Oferta',
    'subtitle' => 'Para a newsletter: ' . $newsletter->title
])

<div class="form-container">
    <form action="{{ isset($offer) ? route('admin.v2.newsletter-management.offers.update', [$newsletter->id, $offer->id]) : route('admin.v2.newsletter-management.offers.store', $newsletter->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($offer))
            @method('PUT')
        @endif

        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-car-front"></i>
                    Informações da Oferta
                </h5>
            </div>

            <div class="modern-card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label required">Imagem</label>
                        @if(isset($offer) && $offer->image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $offer->image) }}" alt="Imagem atual" style="max-width: 200px; border-radius: 8px;">
                                <p class="text-muted small mt-1">Imagem atual</p>
                            </div>
                        @endif
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" 
                               accept="image/*" {{ isset($offer) ? '' : 'required' }}>
                        <small class="text-muted">Formatos aceitos: JPG, PNG, GIF, SVG, WebP, BMP, TIFF (máx. 5MB){{ isset($offer) ? ' - Deixe em branco para manter a imagem atual' : '' }}</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Marca</label>
                        <input type="text" name="brand" class="form-control @error('brand') is-invalid @enderror" 
                               value="{{ old('brand', isset($offer) ? $offer->brand : '') }}" required>
                        @error('brand')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Modelo</label>
                        <input type="text" name="model" class="form-control @error('model') is-invalid @enderror" 
                               value="{{ old('model', isset($offer) ? $offer->model : '') }}" required>
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Ano</label>
                        <input type="text" name="year" class="form-control @error('year') is-invalid @enderror" 
                               value="{{ old('year', isset($offer) ? $offer->year : '') }}" required>
                        @error('year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Quilómetros</label>
                        <input type="text" name="kms" class="form-control @error('kms') is-invalid @enderror" 
                               value="{{ old('kms', isset($offer) ? $offer->kms : '') }}" required>
                        @error('kms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Combustível</label>
                        <input type="text" name="combustivel" class="form-control @error('combustivel') is-invalid @enderror" 
                               value="{{ old('combustivel', isset($offer) ? $offer->combustivel : '') }}">
                        @error('combustivel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Preço</label>
                        <input type="text" name="price" class="form-control @error('price') is-invalid @enderror" 
                               value="{{ old('price', isset($offer) ? $offer->price : '') }}" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Poupança</label>
                        <input type="text" name="savings" class="form-control @error('savings') is-invalid @enderror" 
                               value="{{ old('savings', isset($offer) ? $offer->savings : '') }}" required>
                        @error('savings')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Equipamentos</label>
                        <textarea name="equipamentos" rows="6" class="form-control ckeditor @error('equipamentos') is-invalid @enderror">{{ old('equipamentos', isset($offer) ? $offer->equipamentos : '') }}</textarea>
                        @error('equipamentos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @push('scripts')
                            <script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
                            <script>
                                CKEDITOR.replace('equipamentos', {
                                    height: 150
                                });
                            </script>
                        @endpush
                    </div>

                    <div class="col-md-12">
                        <label class="form-label required">Estado</label>
                        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                            <option value="1" {{ old('is_active', isset($offer) ? $offer->is_active : 1) == 1 ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ old('is_active', isset($offer) ? $offer->is_active : 1) == 0 ? 'selected' : '' }}>Inativo</option>
                        </select>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.v2.newsletter-management.show', $newsletter->id) }}" class="btn-secondary-modern">
                <i class="bi bi-x-lg"></i>
                <span>Cancelar</span>
            </a>
            <button type="submit" class="btn-primary-modern">
                <i class="bi bi-check-lg"></i>
                <span>{{ isset($offer) ? 'Atualizar' : 'Criar' }} Oferta</span>
            </button>
        </div>
    </form>
</div>
@endsection
