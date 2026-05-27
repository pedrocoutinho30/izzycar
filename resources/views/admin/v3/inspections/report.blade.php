@extends('layouts.admin-v2')

@section('title', 'Relatório de Inspeção #' . $inspection->id)

@push('styles')
<style>
    /* ── Screen layout ── */
    .report-vehicle-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a9e 100%);
        color: #fff;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 1.5rem;
    }
    .report-vehicle-header .vehicle-title {
        font-size: 1.6rem;
        font-weight: 700;
        letter-spacing: -.5px;
    }
    .report-score-circle {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.4rem;
        line-height: 1.1;
        border: 4px solid rgba(255,255,255,.35);
    }
    .report-score-circle.score-good   { background: rgba(25,135,84,.85); }
    .report-score-circle.score-warn   { background: rgba(255,193,7,.85);  color: #212529; }
    .report-score-circle.score-danger { background: rgba(220,53,69,.85); }

    .status-badge {
        display: inline-block;
        padding: .2em .65em;
        border-radius: 6px;
        font-size: .78rem;
        font-weight: 600;
        letter-spacing: .02em;
    }
    .status-badge.ok          { background: #d1fae5; color: #065f46; }
    .status-badge.atencao     { background: #fef3c7; color: #78350f; }
    .status-badge.problema    { background: #fee2e2; color: #7f1d1d; }
    .priority-badge {
        display: inline-block;
        padding: .15em .5em;
        border-radius: 5px;
        font-size: .72rem;
        font-weight: 600;
    }
    .priority-badge.baixa  { background: #d1fae5; color: #065f46; }
    .priority-badge.media  { background: #fef3c7; color: #78350f; }
    .priority-badge.alta   { background: #fee2e2; color: #7f1d1d; }

    .report-category-header {
        background: #f1f5f9;
        border-left: 4px solid #2d5a9e;
        padding: .5rem 1rem;
        border-radius: 0 6px 6px 0;
        font-weight: 600;
        font-size: .95rem;
        margin-bottom: .5rem;
        margin-top: 1.25rem;
    }
    .report-item-row {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .45rem .75rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: .875rem;
    }
    .report-item-row:last-child { border-bottom: none; }
    .report-item-name { flex: 1; color: #1e293b; }
    .report-item-notes { color: #64748b; font-size: .8rem; margin-top: .15rem; }

    /* Problems section */
    .problem-card {
        border: 2px solid #fee2e2;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .problem-card-header {
        background: #fef2f2;
        padding: .75rem 1rem;
        display: flex;
        align-items: center;
        gap: .6rem;
        border-bottom: 1px solid #fee2e2;
    }
    .problem-card-body { padding: 1rem; }
    .report-photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: .6rem;
        margin-top: .75rem;
    }
    .report-photo-grid img {
        width: 100%;
        aspect-ratio: 4/3;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    /* ── Print styles ── */
    @media print {
        .no-print { display: none !important; }
        body { background: #fff !important; }
        .report-vehicle-header { background: #1e3a5f !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .status-badge, .priority-badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .problem-card { break-inside: avoid; }
        .report-photo-grid img { break-inside: avoid; }
        .modern-card { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
        a[href]:after { content: none !important; }
    }
</style>
@endpush

@section('content')
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-clipboard-check', 'label' => 'Inspeções', 'href' => route('admin.v3.inspections.index')],
        ['icon' => 'bi bi-pencil-square', 'label' => 'Inspeção #' . $inspection->id, 'href' => route('admin.v3.inspections.edit', $inspection)],
        ['icon' => 'bi bi-file-earmark-text', 'label' => 'Relatório', 'href' => ''],
    ],
    'title' => 'Relatório de Inspeção',
    'subtitle' => 'Resumo completo de todos os itens verificados.',
])

{{-- Action bar --}}
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <a href="{{ route('admin.v3.inspections.edit', $inspection) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar à edição
    </a>
    <button class="btn btn-primary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i>Imprimir / Guardar PDF
    </button>
</div>

{{-- Vehicle header --}}
<div class="report-vehicle-header d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
        <div class="vehicle-title">
            {{ trim(implode(' ', array_filter([$inspection->brand, $inspection->model, $inspection->sub_model]))) ?: 'Veículo sem identificação' }}
        </div>
        <div class="mt-1 opacity-75" style="font-size:.95rem">
            @if($inspection->version) <span class="me-3">{{ $inspection->version }}</span> @endif
            @if($inspection->year) <span class="me-3"><i class="bi bi-calendar3 me-1"></i>{{ $inspection->year }}</span> @endif
            @if($inspection->registration) <span class="me-3"><i class="bi bi-card-text me-1"></i>{{ $inspection->registration }}</span> @endif
            @if($inspection->fuel) <span class="me-3"><i class="bi bi-fuel-pump me-1"></i>{{ $inspection->fuel }}</span> @endif
            @if($inspection->kilometers) <span class="me-3"><i class="bi bi-speedometer me-1"></i>{{ number_format($inspection->kilometers) }} km</span> @endif
        </div>
        <div class="mt-2 opacity-60" style="font-size:.82rem">
            Inspeção nº {{ $inspection->id }}
            @if($inspection->completed_at)
                &nbsp;·&nbsp; Concluída em {{ $inspection->completed_at->format('d/m/Y H:i') }}
            @endif
            @if($inspection->vin)
                &nbsp;·&nbsp; VIN: {{ $inspection->vin }}
            @endif
        </div>
    </div>
    @php
        $pct = $summary['max_points'] > 0 ? round($summary['total_points'] / $summary['max_points'] * 100) : 0;
        $scoreClass = $pct >= 80 ? 'score-good' : ($pct >= 50 ? 'score-warn' : 'score-danger');
    @endphp
    <div class="text-center">
        <div class="report-score-circle {{ $scoreClass }} mx-auto">
            <div>{{ $pct }}%</div>
            <div style="font-size:.65rem;font-weight:400;opacity:.9">pontuação</div>
        </div>
        <div class="mt-1 small opacity-75">{{ $summary['inspection_result'] ?? '' }}</div>
    </div>
</div>

{{-- Summary stats --}}
<div class="row g-2 mb-4">
    <div class="col-6 col-md-3">
        <div class="modern-card h-100 text-center">
            <div class="modern-card-body py-2">
                <div class="text-muted small">Verificados</div>
                <div class="fs-3 fw-bold text-primary">{{ $summary['verified_items'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="modern-card h-100 text-center">
            <div class="modern-card-body py-2">
                <div class="text-muted small">OK</div>
                <div class="fs-3 fw-bold text-success">{{ $summary['ok_items'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="modern-card h-100 text-center">
            <div class="modern-card-body py-2">
                <div class="text-muted small">Atenção</div>
                <div class="fs-3 fw-bold text-warning">{{ $summary['attention_items'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="modern-card h-100 text-center">
            <div class="modern-card-body py-2">
                <div class="text-muted small">Problemas</div>
                <div class="fs-3 fw-bold text-danger">{{ $summary['problem_items'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Parent inspection reference + diff --}}
@if($inspection->parent)
@php
    $statusLabelsReport = ['nao_verificado' => 'Não verificado', 'ok' => 'OK', 'atencao' => 'Atenção', 'problema' => 'Problema'];
    $statusColorsReport = ['nao_verificado' => 'secondary', 'ok' => 'success', 'atencao' => 'warning', 'problema' => 'danger'];
    $priorityLabelsReport = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta'];
@endphp
<div class="modern-card mb-4 border-start border-4 border-info">
    <div class="modern-card-header bg-info bg-opacity-10">
        <i class="bi bi-clock-history me-2 text-info"></i>
        <span class="fw-semibold">Revisão da Inspeção #{{ $inspection->parent_inspection_id }}</span>
    </div>
    <div class="modern-card-body">
        <p class="text-muted small mb-3">
            Esta inspeção é uma revisão da <strong>Inspeção #{{ $inspection->parent_inspection_id }}</strong>
            (criada em {{ $inspection->parent->created_at->format('d/m/Y') }}).
            <a href="{{ route('admin.v3.inspections.report', $inspection->parent) }}" class="ms-1 no-print">
                <i class="bi bi-arrow-up-right-square me-1"></i>Ver inspeção original
            </a>
        </p>

        @if($changedEntries->count())
            <div class="fw-semibold small text-muted mb-2">
                <i class="bi bi-arrow-left-right me-1"></i>{{ $changedEntries->count() }} {{ $changedEntries->count() === 1 ? 'ponto alterado' : 'pontos alterados' }} face à inspeção anterior:
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Categoria</th>
                            <th class="text-center">Estado anterior</th>
                            <th class="text-center">Estado actual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($changedEntries as $change)
                        <tr>
                            <td class="fw-semibold">{{ $change['item']->name ?? '—' }}</td>
                            <td class="text-muted">{{ $change['item']->category?->name }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $statusColorsReport[$change['old_status']] ?? 'secondary' }}">
                                    {{ $statusLabelsReport[$change['old_status']] ?? $change['old_status'] }}
                                </span>
                                @if($change['old_status'] !== 'ok' && $change['old_status'] !== 'nao_verificado' && $change['old_priority'])
                                    <span class="badge bg-light text-dark border ms-1">{{ $priorityLabelsReport[$change['old_priority']] ?? $change['old_priority'] }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $statusColorsReport[$change['new_status']] ?? 'secondary' }}">
                                    {{ $statusLabelsReport[$change['new_status']] ?? $change['new_status'] }}
                                </span>
                                @if($change['new_status'] !== 'ok' && $change['new_status'] !== 'nao_verificado' && $change['new_priority'])
                                    <span class="badge bg-light text-dark border ms-1">{{ $priorityLabelsReport[$change['new_priority']] ?? $change['new_priority'] }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-success mb-0 py-2 small">
                <i class="bi bi-check-circle me-1"></i>Nenhuma alteração de estado face à inspeção anterior.
            </div>
        @endif
    </div>
</div>
@endif

{{-- General vehicle photos --}}
@if($inspection->generalMedia->count())
<div class="modern-card mb-4">
    <div class="modern-card-header">
        <i class="bi bi-images me-2"></i>Fotos do Veículo
        <span class="badge bg-secondary ms-2">{{ $inspection->generalMedia->count() }}</span>
    </div>
    <div class="modern-card-body">
        <div class="report-photo-grid">
            @foreach($inspection->generalMedia as $gm)
                @if($gm->type === 'image')
                    <img src="{{ Storage::url($gm->path) }}" alt="Foto do veículo" loading="lazy">
                @else
                    <div class="d-flex align-items-center justify-content-center bg-light rounded" style="aspect-ratio:4/3">
                        <i class="bi bi-camera-video text-muted fs-3"></i>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Verified items list --}}
<div class="modern-card mb-4">
    <div class="modern-card-header">
        <i class="bi bi-list-check me-2"></i>Itens Verificados
    </div>
    <div class="modern-card-body p-0 pb-2">
        @forelse($categoriesWithEntries as $group)
            <div class="report-category-header mx-3">
                @if($group['category']->icon)<i class="{{ $group['category']->icon }} me-2"></i>@endif
                {{ $group['category']->name }}
                <span class="text-muted fw-normal ms-2" style="font-size:.8rem">{{ $group['verifiedItems']->count() }} ite{{ $group['verifiedItems']->count() === 1 ? 'm' : 'ns' }}</span>
            </div>
            @foreach($group['verifiedItems'] as $row)
                @php
                    /** @var \App\Models\VehicleInspectionEntry $entry */
                    $entry = $row['entry'];
                    $item  = $row['item'];
                    $showPriority = in_array($entry->status, ['atencao', 'problema']);
                @endphp
                <div class="report-item-row">
                    <div class="report-item-name">
                        {{ $item->name }}
                        @if($item->is_critical)
                            <span class="badge bg-danger ms-1" style="font-size:.65rem;vertical-align:middle">Crítico</span>
                        @endif
                        @if($entry->notes)
                            <div class="report-item-notes"><i class="bi bi-chat-left-text me-1"></i>{{ $entry->notes }}</div>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        @if($showPriority)
                            <span class="priority-badge {{ $entry->priority }}">
                                {{ \App\Services\VehicleInspectionService::PRIORITIES[$entry->priority] ?? $entry->priority }}
                            </span>
                        @endif
                        <span class="status-badge {{ $entry->status }}">
                            {{ \App\Services\VehicleInspectionService::STATUSES[$entry->status] ?? $entry->status }}
                        </span>
                    </div>
                </div>
            @endforeach
        @empty
            <div class="text-center text-muted py-4">Nenhum item verificado.</div>
        @endforelse
    </div>
</div>

{{-- Problems section --}}
@if($problemEntries->count())
<div class="modern-card mb-4">
    <div class="modern-card-header text-danger">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>Problemas Encontrados
        <span class="badge bg-danger ms-2">{{ $problemEntries->count() }}</span>
    </div>
    <div class="modern-card-body">
        @foreach($problemEntries as $entry)
            @php $item = $entry->item; @endphp
            <div class="problem-card">
                <div class="problem-card-header">
                    <span class="priority-badge alta d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:1rem;border-radius:50%;flex-shrink:0">
                        <i class="bi bi-exclamation{{ $entry->priority === 'alta' ? '-triangle-fill' : ($entry->priority === 'media' ? '-circle-fill' : '') }} "></i>
                    </span>
                    <div class="flex-grow-1">
                        <div class="fw-semibold" style="font-size:.95rem">
                            {{ $item?->name ?? 'Item desconhecido' }}
                            @if($item?->is_critical)
                                <span class="badge bg-danger ms-1" style="font-size:.65rem">Crítico</span>
                            @endif
                        </div>
                        <div class="small text-muted">
                            {{ $item?->category?->name ?? '' }}
                        </div>
                    </div>
                    <span class="priority-badge {{ $entry->priority }} ms-auto">
                        {{ \App\Services\VehicleInspectionService::PRIORITIES[$entry->priority] ?? $entry->priority }}
                    </span>
                </div>
                <div class="problem-card-body">
                    @if($entry->notes)
                        <p class="mb-2 text-secondary" style="font-size:.875rem">
                            <i class="bi bi-chat-left-text me-1 text-muted"></i>{{ $entry->notes }}
                        </p>
                    @endif
                    @if($entry->media->count())
                        <div class="report-photo-grid">
                            @foreach($entry->media as $media)
                                @if($media->type === 'image')
                                    <img src="{{ Storage::url($media->path) }}" alt="{{ $media->description ?: $item?->name }}" loading="lazy">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-light rounded" style="aspect-ratio:4/3">
                                        <i class="bi bi-camera-video text-muted fs-3"></i>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted small"><i class="bi bi-image me-1"></i>Sem fotografias adicionadas.</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- Observations / notes on inspection --}}
@if($inspection->notes)
<div class="modern-card mb-4">
    <div class="modern-card-header"><i class="bi bi-journal-text me-2"></i>Observações Gerais</div>
    <div class="modern-card-body">
        <p class="mb-0 text-secondary">{{ $inspection->notes }}</p>
    </div>
</div>
@endif

@endsection
