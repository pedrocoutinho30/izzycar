@extends('layouts.admin-v2')

@section('title', 'Inspeções')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-clipboard-check', 'label' => 'Inspeções', 'href' => ''],
    ],
    'title'       => 'Inspeções Automóvel',
    'subtitle'    => 'Checklist, pontuação e histórico de revisões.',
    'actionHref'  => route('admin.v3.inspections.create'),
    'actionLabel' => 'Nova Inspeção',
])

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show"><i class="bi bi-info-circle me-1"></i>{{ session('info') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

@include('components.admin.stats-cards', [
    'stats' => [
        ['icon' => 'clipboard-check',  'title' => 'Total',       'value' => $stats['total'],     'color' => 'primary'],
        ['icon' => 'pencil-square',    'title' => 'Rascunhos',   'value' => $stats['draft'],     'color' => 'secondary'],
        ['icon' => 'check-circle',     'title' => 'Concluídas',  'value' => $stats['completed'], 'color' => 'success'],
        ['icon' => 'car-front-fill',   'title' => 'Convertidas', 'value' => $stats['converted'], 'color' => 'info'],
    ]
])

{{-- Filters --}}
<div class="modern-card mb-3">
    <div class="modern-card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Marca, modelo, matrícula ou VIN…" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos os estados</option>
                    @foreach(['draft' => 'Rascunho', 'in_progress' => 'Em curso', 'completed' => 'Concluída', 'converted' => 'Convertida'] as $k => $l)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="fuel" class="form-select form-select-sm">
                    <option value="">Todos os combustíveis</option>
                    @foreach(['Diesel','Gasolina','Híbrido','Elétrico'] as $f)
                        <option value="{{ $f }}" @selected(request('fuel') === $f)>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtrar</button>
                <a href="{{ route('admin.v3.inspections.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Limpar</a>
            </div>
        </form>
    </div>
</div>

@php
    $statusColors  = ['draft' => 'secondary', 'in_progress' => 'info', 'completed' => 'success', 'converted' => 'primary'];
    $statusLabels  = ['draft' => 'Rascunho', 'in_progress' => 'Em curso', 'completed' => 'Concluída', 'converted' => 'Convertida'];
@endphp

<div class="modern-card">
    <div class="modern-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="modern-card-title mb-0"><i class="bi bi-list-ul me-1"></i> Lista de Inspeções</h5>
        <span class="badge bg-secondary rounded-pill">{{ $inspections->total() }} total</span>
    </div>
    <div class="modern-card-body p-0">
        @if($inspections->isEmpty())
            @include('components.admin.empty-state', [
                'icon'       => 'bi-clipboard-check',
                'title'      => 'Nenhuma inspeção encontrada',
                'description'=> 'Ainda não existem inspeções ou não há resultados para os filtros aplicados.',
                'actionUrl'  => route('admin.v3.inspections.create'),
                'actionText' => 'Criar primeira inspeção',
            ])
        @else
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Veículo</th>
                        <th>Matrícula</th>
                        <th>Combustível</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Pontuação</th>
                        <th class="text-center">Data</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inspections as $inspection)
                    @php
                        $pct = ($inspection->max_points > 0)
                            ? round($inspection->total_points / $inspection->max_points * 100)
                            : null;
                        $pctColor = $pct === null ? 'secondary' : ($pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger'));
                        $isLocked = in_array($inspection->status, ['completed', 'converted']);
                    @endphp
                    <tr>
                        <td class="text-muted small font-monospace">{{ $inspection->id }}</td>
                        <td>
                            <div class="fw-semibold">
                                {{ trim(($inspection->brand ?? '') . ' ' . ($inspection->model ?? '')) ?: '— sem identificação' }}
                            </div>
                            <small class="text-muted">
                                {{ $inspection->sub_model }}{{ $inspection->year ? ' · ' . $inspection->year : '' }}
                                @if($inspection->parent_inspection_id)
                                    &nbsp;<span class="badge bg-light text-secondary border" style="font-size:.65rem">
                                        <i class="bi bi-arrow-return-right me-1"></i>rev. de #{{ $inspection->parent_inspection_id }}
                                    </span>
                                @endif
                            </small>
                        </td>
                        <td>{{ $inspection->registration ?: '—' }}</td>
                        <td>{{ $inspection->fuel ?: '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $statusColors[$inspection->status] ?? 'secondary' }}">
                                {{ $statusLabels[$inspection->status] ?? $inspection->status }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($pct !== null)
                                <span class="badge bg-{{ $pctColor }}-subtle text-{{ $pctColor }} border border-{{ $pctColor }} border-opacity-25 fw-semibold">
                                    {{ $pct }}%
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center text-muted small text-nowrap">
                            {{ $inspection->created_at->format('d/m/Y') }}
                        </td>
                        <td class="text-end text-nowrap">
                            @if($isLocked)
                                <a href="{{ route('admin.v3.inspections.report', $inspection) }}"
                                   class="btn btn-sm btn-outline-success" title="Ver relatório">
                                    <i class="bi bi-file-earmark-text"></i>
                                </a>
                                <form action="{{ route('admin.v3.inspections.duplicate', $inspection) }}"
                                      method="POST" class="d-inline ms-1"
                                      title="Criar nova revisão">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary" title="Nova revisão">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('admin.v3.inspections.edit', $inspection) }}"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif
                            @if($inspection->v3_vehicle_id)
                                <a href="{{ route('admin.v3.vehicles.edit', $inspection->v3_vehicle_id) }}"
                                   class="btn btn-sm btn-outline-info ms-1" title="Ver veículo V3">
                                    <i class="bi bi-car-front-fill"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@if($inspections->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-3">
        <div class="text-muted small">
            Mostrando {{ $inspections->firstItem() }} a {{ $inspections->lastItem() }} de {{ $inspections->total() }} inspeções
        </div>
        {{ $inspections->links() }}
    </div>
@endif

@endsection
