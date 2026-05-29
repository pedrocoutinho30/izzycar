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
                        <th style="width: 3rem;"></th>
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
                    @php
                        // Group inspections by vehicle (brand|model|registration)
                        $grouped = collect();
                        foreach($inspections as $insp) {
                            $key = ($insp->brand ?? '') . '|' . ($insp->model ?? '') . '|' . ($insp->registration ?? '');
                            if (!$grouped->has($key)) {
                                $grouped[$key] = collect();
                            }
                            $grouped[$key]->push($insp);
                        }
                        
                        // Sort each group by ID DESC (newest first) and reindex
                        $grouped = $grouped->map(fn($g) => $g->sortByDesc('id')->values());
                    @endphp

                    @foreach($grouped as $vehicleKey => $inspectionGroup)
                        @php
                            // Find last completed/converted inspection, otherwise use most recent
                            $mainInspection = $inspectionGroup
                                ->first(fn($insp) => in_array($insp->status, ['completed', 'converted']))
                                ?? $inspectionGroup->first();
                            
                            // All other inspections are revisions (excluding main)
                            $revisions = $inspectionGroup->filter(fn($insp) => $insp->id !== $mainInspection->id)->values();
                            $hasRevisions = $revisions->count() > 0;
                            $collapseId = 'inspGroup' . $mainInspection->id;
                            
                            $pct = ($mainInspection->max_points > 0)
                                ? round($mainInspection->total_points / $mainInspection->max_points * 100)
                                : null;
                            $pctColor = $pct === null ? 'secondary' : ($pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger'));
                            $isLocked = in_array($mainInspection->status, ['completed', 'converted']);
                        @endphp

                        {{-- Main inspection row --}}
                        <tr>
                            {{-- Expand/collapse toggle --}}
                            <td class="text-center p-0">
                                @if($hasRevisions)
                                    <button class="btn btn-sm btn-link text-primary p-2 collapsed" 
                                            type="button"
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#{{ $collapseId }}" 
                                            aria-expanded="false"
                                            aria-controls="{{ $collapseId }}"
                                            title="Ver revisões anteriores">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                @else
                                    <span class="d-inline-block p-2"></span>
                                @endif
                            </td>

                            {{-- ID --}}
                            <td class="text-muted small font-monospace">{{ $mainInspection->id }}</td>

                            {{-- Vehicle info --}}
                            <td>
                                <div class="fw-semibold">
                                    {{ trim(($mainInspection->brand ?? '') . ' ' . ($mainInspection->model ?? '')) ?: '— sem identificação' }}
                                </div>
                                <small class="text-muted">
                                    {{ $mainInspection->sub_model }}{{ $mainInspection->year ? ' · ' . $mainInspection->year : '' }}
                                    @if($hasRevisions)
                                        <span class="badge bg-light text-secondary border" style="font-size:.65rem">
                                            <i class="bi bi-layers me-1"></i>{{ $revisions->count() }} revisões
                                        </span>
                                    @endif
                                </small>
                            </td>

                            {{-- Registration --}}
                            <td>{{ $mainInspection->registration ?: '—' }}</td>

                            {{-- Fuel --}}
                            <td>{{ $mainInspection->fuel ?: '—' }}</td>

                            {{-- Status --}}
                            <td class="text-center">
                                <span class="badge bg-{{ $statusColors[$mainInspection->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$mainInspection->status] ?? $mainInspection->status }}
                                </span>
                            </td>

                            {{-- Score --}}
                            <td class="text-center">
                                @if($pct !== null)
                                    <span class="badge bg-{{ $pctColor }}-subtle text-{{ $pctColor }} border border-{{ $pctColor }} border-opacity-25 fw-semibold">
                                        {{ $pct }}%
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="text-center text-muted small text-nowrap">
                                {{ $mainInspection->created_at->format('d/m/Y') }}
                            </td>

                            {{-- Actions --}}
                            <td class="text-end text-nowrap">
                                @if($isLocked)
                                    <a href="{{ route('admin.v3.inspections.report', $mainInspection) }}"
                                       class="btn btn-sm btn-outline-success" title="Ver relatório">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                    <form action="{{ route('admin.v3.inspections.duplicate', $mainInspection) }}"
                                          method="POST" class="d-inline ms-1">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary" title="Nova revisão">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.v3.inspections.edit', $mainInspection) }}"
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.v3.inspections.destroy', $mainInspection) }}" method="POST" class="d-inline ms-1">
                                        @csrf
                                        
                                        <button class="btn btn-sm btn-outline-secondary" title="Apagar revisão">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($mainInspection->v3_vehicle_id)
                                    <a href="{{ route('admin.v3.vehicles.edit', $mainInspection->v3_vehicle_id) }}"
                                       class="btn btn-sm btn-outline-info ms-1" title="Ver veículo V3">
                                        <i class="bi bi-car-front-fill"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>

                        {{-- Collapsed revisions section --}}
                        @if($hasRevisions)
                        <tr class="collapse" id="{{ $collapseId }}">
                            <td colspan="9" class="p-0">
                                <div style="background: #f8f9fa; padding: 1rem;">
                                    <div class="small text-muted mb-2 ms-3"><i class="bi bi-clock-history me-1"></i>Revisões anteriores:</div>
                                    <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 0.9rem;">
                                        @foreach($revisions as $revision)
                                        @php
                                            $revPct = ($revision->max_points > 0)
                                                ? round($revision->total_points / $revision->max_points * 100)
                                                : null;
                                            $revPctColor = $revPct === null ? 'secondary' : ($revPct >= 80 ? 'success' : ($revPct >= 50 ? 'warning' : 'danger'));
                                            $revIsLocked = in_array($revision->status, ['completed', 'converted']);
                                        @endphp
                                        <tr style="background: white; border: 1px solid rgba(0,0,0,0.1); margin-bottom: 0.5rem;">
                                            <td class="ps-3 pe-2 text-muted font-monospace">
                                                <small>#{{ $revision->id }}</small>
                                            </td>
                                            <td class="px-2">
                                                <div class="fw-semibold small">Revisão</div>
                                                <small class="text-muted">
                                                    @if($revision->parent_inspection_id)
                                                        de #{{ $revision->parent_inspection_id }}
                                                    @endif
                                                </small>
                                            </td>
                                            <td class="px-2">
                                                <span class="badge bg-{{ $statusColors[$revision->status] ?? 'secondary' }} small">
                                                    {{ $statusLabels[$revision->status] ?? $revision->status }}
                                                </span>
                                            </td>
                                            <td class="px-2 text-center">
                                                @if($revPct !== null)
                                                    <span class="badge bg-{{ $revPctColor }}-subtle text-{{ $revPctColor }} small">
                                                        {{ $revPct }}%
                                                    </span>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td class="px-2 text-muted small text-nowrap">
                                                {{ $revision->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="ps-2 pe-3 text-end text-nowrap">
                                                @if($revIsLocked)
                                                    <a href="{{ route('admin.v3.inspections.report', $revision) }}"
                                                       class="btn btn-sm btn-outline-success" title="Ver relatório">
                                                        <i class="bi bi-file-earmark-text"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('admin.v3.inspections.edit', $revision) }}"
                                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="{{ route('admin.v3.inspections.destroy', $revision) }}"
                                                       class="btn btn-sm btn-outline-secondary ms-1" title="Apagar revisão">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                @endif
                                               
                                            </td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @endif
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
