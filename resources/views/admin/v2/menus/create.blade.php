@extends('layouts.admin-v2')

@section('title', 'Novo Item de Menu')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door',  'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-list-nested', 'label' => 'Menus',     'href' => route('admin.v2.menus.index')],
        ['icon' => 'bi bi-plus-circle', 'label' => 'Novo',      'href' => ''],
    ],
    'title'    => 'Novo Item de Menu',
    'subtitle' => 'Adicionar um novo item à navegação do site',
])

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('admin.v2.menus.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-link-45deg me-1"></i> Dados do Item</h5>
                </div>
                <div class="modern-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" placeholder="Ex: Importação" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">URL</label>
                            <input type="text" name="url" class="form-control @error('url') is-invalid @enderror"
                                   value="{{ old('url') }}" placeholder="Ex: /importacao">
                            <div class="form-text">Deixe em branco se for apenas um agrupador.</div>
                            @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Menu pai</label>
                            <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">— Nenhum (item principal) —</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->title }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Selecione um menu pai para criar um sub-item.</div>
                            @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ordem</label>
                            <input type="number" name="order" class="form-control @error('order') is-invalid @enderror"
                                   value="{{ old('order', 0) }}" min="0">
                            <div class="form-text">Número mais baixo aparece primeiro.</div>
                            @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-gear me-1"></i> Visibilidade</h5>
                </div>
                <div class="modern-card-body">
                    {{-- hidden ANTES do checkbox: se desmarcado, envia 0; se marcado, PHP usa o último valor (1) --}}
                    <input type="hidden" name="show_online" value="0">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="show_online" id="show_online"
                               value="1" {{ old('show_online', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="show_online">Visível no site</label>
                        <div class="form-text">Itens visíveis aparecem na navegação do frontend.</div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-check-lg me-1"></i> Guardar
                </button>
                <a href="{{ route('admin.v2.menus.index') }}" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </div>
    </div>
</form>

@endsection
