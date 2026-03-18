@extends('layouts.admin-v2')

@section('title', isset($offer) ? 'Editar Oferta' : 'Nova Oferta')

@section('content')
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-envelope-paper', 'label' => 'Newsletter', 'href' => route('admin.v2.newsletter.index')],
        ['icon' => '', 'label' => isset($offer) ? 'Editar' : 'Nova']
    ],
    'title' => isset($offer) ? 'Editar Oferta' : 'Nova Oferta',
    'subtitle' => 'Dados do veículo para newsletter',
    'actionHref' => route('admin.v2.newsletter.index'),
    'actionLabel' => 'Voltar'
])

<form action="{{ isset($offer) ? route('admin.v2.newsletter.update', $offer->id) : route('admin.v2.newsletter.store') }}" method="POST">
    @csrf
    @if(isset($offer))
        @method('PUT')
    @endif

    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title"><i class="bi bi-pencil"></i> Informação da Oferta</h5>
        </div>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label required">Imagem (URL)</label>
                <input type="text" name="image" class="form-control" value="{{ old('image', $offer->image ?? '') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label required">Marca</label>
                <input type="text" name="brand" class="form-control" value="{{ old('brand', $offer->brand ?? '') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label required">Modelo</label>
                <input type="text" name="model" class="form-control" value="{{ old('model', $offer->model ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label required">Ano</label>
                <input type="text" name="year" class="form-control" value="{{ old('year', $offer->year ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label required">Kms</label>
                <input type="text" name="kms" class="form-control" value="{{ old('kms', $offer->kms ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label required">Preço chave na mão</label>
                <input type="text" name="price" class="form-control" value="{{ old('price', $offer->price ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label required">Poupança</label>
                <input type="text" name="savings" class="form-control" value="{{ old('savings', $offer->savings ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Combustível</label>
                <input type="text" name="combustivel" class="form-control" value="{{ old('combustivel', $offer->combustivel ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Ativo</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ old('is_active', $offer->is_active ?? true) ? 'selected' : '' }}>Sim</option>
                    <option value="0" {{ old('is_active', $offer->is_active ?? false) ? '' : 'selected' }}>Não</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Data de Publicação</label>
                <input type="date" name="published_at" class="form-control" value="{{ old('published_at', isset($offer->published_at) ? $offer->published_at->format('Y-m-d') : '') }}">
            </div>
            <div class="col-12">
                <label class="form-label">Equipamentos</label>
                <textarea name="equipamentos" class="form-control" rows="3">{{ old('equipamentos', $offer->equipamentos ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-end gap-2">
        <a href="{{ route('admin.v2.newsletter.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
</form>
@endsection
