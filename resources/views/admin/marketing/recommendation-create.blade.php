@extends('layouts.admin-v2')

@section('title', 'Nova recomendação')

@section('content')

@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-stars', 'label' => 'Marketing Studio', 'href' => route('admin.marketing.index')],
['icon' => 'bi bi-hand-thumbs-up', 'label' => 'Recomendação', 'href' => ''],
],
'title' => 'Nova recomendação',
'subtitle' => 'Recomenda um carro de fora: preenche os dados reais do anúncio e carrega as fotografias.',
])

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<form method="POST" action="{{ route('admin.marketing.recommendations.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="card mb-4">
        <div class="card-header fw-semibold">Dados do carro</div>
        <div class="card-body row g-3">
            <div class="col-md-4"><label class="form-label">Marca *</label><input name="brand" class="form-control" value="{{ old('brand') }}" required></div>
            <div class="col-md-4"><label class="form-label">Modelo *</label><input name="model" class="form-control" value="{{ old('model') }}" required></div>
            <div class="col-md-4"><label class="form-label">Versão</label><input name="version" class="form-control" value="{{ old('version') }}"></div>
            <div class="col-md-3"><label class="form-label">Ano</label><input type="number" name="year" class="form-control" value="{{ old('year') }}"></div>
            <div class="col-md-3"><label class="form-label">Quilómetros</label><input type="number" name="mileage" class="form-control" value="{{ old('mileage') }}"></div>
            <div class="col-md-3"><label class="form-label">Potência (cv)</label><input type="number" name="power" class="form-control" value="{{ old('power') }}"></div>
            <div class="col-md-3"><label class="form-label">Combustível</label><input name="fuel" class="form-control" value="{{ old('fuel') }}"></div>
            <div class="col-md-4"><label class="form-label">Preço (€)</label><input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}"></div>
            <div class="col-md-4"><label class="form-label">Poupança (€)</label><input type="number" step="0.01" name="savings" class="form-control" value="{{ old('savings') }}"><div class="form-text">Só aparece se preencheres. Indica apenas valores que consigas justificar.</div></div>
            <div class="col-md-4"><label class="form-label">Link do anúncio</label><input type="url" name="url" class="form-control" value="{{ old('url') }}"></div>
            <div class="col-12"><label class="form-label">Equipamento (uma linha por item, máx. 4)</label><textarea name="equipment_raw" rows="4" class="form-control">{{ old('equipment_raw') }}</textarea></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">Fotografias</div>
        <div class="card-body row g-3">
            <div class="col-md-6"><label class="form-label">Foto principal</label><input type="file" name="image" accept="image/*" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Mais fotos (até 12)</label><input type="file" name="gallery_photos[]" accept="image/*" multiple class="form-control"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">Template e formato</div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                @foreach($templates as $t)
                <div class="col-md-6 col-xl-4">
                    <label class="card h-100" style="cursor:pointer"><div class="card-body">
                        <input type="radio" name="template" value="{{ $t->key }}" class="form-check-input me-2" @checked(old('template', 'recomendacao') === $t->key)>
                        <span class="fw-semibold">{{ $t->name }}</span>
                        <div class="text-muted small mt-1">{{ $t->description }}</div>
                    </div></label>
                </div>
                @endforeach
            </div>
            @foreach($formats as $f)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="format" id="fmt_{{ $f->value }}" value="{{ $f->value }}" @checked(old('format', 'feed_vertical') === $f->value)>
                <label class="form-check-label" for="fmt_{{ $f->value }}">{{ $f->label() }}</label>
            </div>
            @endforeach
        </div>
    </div>

    <button class="btn btn-primary"><i class="bi bi-magic me-1"></i> Gerar e abrir editor</button>
</form>
@endsection
