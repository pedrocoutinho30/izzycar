@extends('layouts.admin-v2')

@section('title', 'Marcas')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-patch-check', 'label' => 'Marcas', 'href' => ''],
    ],
    'title'    => 'Marcas',
    'subtitle' => 'Logos das marcas do catálogo de viaturas',
])

@include('components.admin.stats-cards', [
    'stats' => [
        ['icon' => 'patch-check', 'title' => 'Marcas',    'value' => $stats['total'],    'color' => 'primary'],
        ['icon' => 'image',       'title' => 'Com logo',  'value' => $stats['com_logo'], 'color' => 'success'],
        ['icon' => 'image-alt',   'title' => 'Sem logo',  'value' => $stats['sem_logo'], 'color' => 'warning'],
    ]
])

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="modern-card">
    <div class="modern-card-header">
        <form method="GET" class="d-flex align-items-center flex-wrap gap-2">
            <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm" style="max-width:240px" placeholder="Pesquisar marca...">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="sem_logo" value="1" id="semLogo" @checked($onlyMissing) onchange="this.form.submit()">
                <label class="form-check-label small" for="semLogo">Só sem logo</label>
            </div>
            <button class="btn btn-sm btn-primary">Filtrar</button>
            @if($search !== '' || $onlyMissing)
                <a href="{{ route('admin.v2.brands.index') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
            @endif
        </form>
    </div>
    <div class="modern-card-body p-0">
        @if($brands->isEmpty())
            <div class="text-center py-5 text-muted">Nenhuma marca encontrada.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:110px">Logo</th>
                            <th>Marca</th>
                            <th>Modelos</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brands as $brand)
                        <tr>
                            <td>
                                @if($brand->logo_url)
                                    <img src="{{ $brand->logo_url }}" alt="Logo {{ $brand->name }}" loading="lazy" style="height:44px;max-width:90px;object-fit:contain">
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Sem logo</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $brand->name }}</td>
                            <td class="text-muted">{{ $brand->models_count }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <form method="POST" action="{{ route('admin.v2.brands.logo.update', $brand) }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="file" name="logo" id="logo-{{ $brand->id }}" accept="image/png,image/jpeg,image/webp" class="d-none" onchange="this.form.submit()">
                                        <label for="logo-{{ $brand->id }}" class="btn btn-sm btn-outline-primary mb-0">
                                            <i class="bi bi-upload me-1"></i>{{ $brand->logo_path ? 'Substituir' : 'Adicionar' }}
                                        </label>
                                    </form>
                                    @if($brand->logo_path)
                                    <form method="POST" action="{{ route('admin.v2.brands.logo.destroy', $brand) }}" onsubmit="return confirm('Remover o logo de {{ e($brand->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Remover logo"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
