@extends('layouts.admin-v2')

@section('title', 'Inspeções V3')

@section('content')
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-clipboard-check', 'label' => 'Inspeções', 'href' => ''],
    ],
    'title' => 'Inspeções Automóvel',
    'subtitle' => 'Checklist, media, pontuação e conversão automática para V3.',
])

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="modern-card h-100"><div class="modern-card-body"><div class="text-muted small">Total</div><div class="fs-3 fw-bold">{{ $stats['total'] }}</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="modern-card h-100"><div class="modern-card-body"><div class="text-muted small">Rascunhos</div><div class="fs-3 fw-bold">{{ $stats['draft'] }}</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="modern-card h-100"><div class="modern-card-body"><div class="text-muted small">Concluídas</div><div class="fs-3 fw-bold">{{ $stats['completed'] }}</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="modern-card h-100"><div class="modern-card-body"><div class="text-muted small">Convertidas</div><div class="fs-3 fw-bold">{{ $stats['converted'] }}</div></div></div>
    </div>
</div>

<div class="modern-card mb-3">
    <div class="modern-card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4">
                <label class="form-label small text-muted">Pesquisar</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Marca, modelo, matrícula ou VIN">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    @foreach(['draft' => 'Rascunho', 'in_progress' => 'Em curso', 'completed' => 'Concluída', 'converted' => 'Convertida'] as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Combustível</label>
                <select name="fuel" class="form-select">
                    <option value="">Todos</option>
                    @foreach(['Diesel','Gasolina','Híbrido','Elétrico'] as $fuel)
                        <option value="{{ $fuel }}" @selected(request('fuel') === $fuel)>{{ $fuel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="{{ route('admin.v3.inspections.index') }}" class="btn btn-outline-secondary">Limpar</a>
                <a href="{{ route('admin.v3.inspections.create') }}" class="btn btn-success ms-auto"><i class="bi bi-plus-lg me-1"></i>Nova Inspeção</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    @forelse($inspections as $inspection)
        <div class="col-12">
            <div class="modern-card">
                <div class="modern-card-body">
                    <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-md-center">
                        <div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                                <h5 class="mb-0">{{ trim(($inspection->brand ?? '—') . ' ' . ($inspection->model ?? '')) }}</h5>
                                <span class="badge bg-{{ match($inspection->status){'draft'=>'secondary','in_progress'=>'info','completed'=>'success','converted'=>'primary',default=>'secondary'} }} rounded-pill">{{ ucfirst(str_replace('_', ' ', $inspection->status)) }}</span>
                                @if($inspection->fuel)
                                    <span class="badge bg-light text-dark rounded-pill">{{ $inspection->fuel }}</span>
                                @endif
                            </div>
                            <div class="text-muted small">
                                {{ $inspection->registration ?: 'Sem matrícula' }} · {{ $inspection->year ?: '—' }} · {{ $inspection->created_at->format('d/m/Y H:i') }} · {{ $inspection->entries_count }} itens
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.v3.inspections.edit', $inspection) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Editar</a>
                            @if(! $inspection->v3_vehicle_id)
                                <form action="{{ route('admin.v3.inspections.convert', $inspection) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-primary btn-sm"><i class="bi bi-car-front-fill me-1"></i>Criar V3</button>
                                </form>
                            @else
                                <a href="{{ route('admin.v3.vehicles.edit', $inspection->v3_vehicle_id) }}" class="btn btn-success btn-sm"><i class="bi bi-arrow-right-circle me-1"></i>V3 gerado</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="modern-card">
                <div class="modern-card-body py-5 text-center text-muted">
                    <i class="bi bi-clipboard-check fs-1 d-block mb-2"></i>
                    Ainda não existem inspeções.
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="mt-3">{{ $inspections->links() }}</div>
@endsection
