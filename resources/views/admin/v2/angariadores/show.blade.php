@extends('layouts.admin-v2')

@section('title', 'Angariador — ' . $angariador->name)

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Angariadores', 'href' => route('admin.v2.angariadores.index')],
        ['icon' => '', 'label' => $angariador->name],
    ],
    'title' => $angariador->name . ' ' . $angariador->last_name,
    'subtitle' => 'Detalhe de leads, propostas e comissões — sem impersonation',
    'extraActions' => [
        ['href' => route('admin.v2.users.edit', $angariador->id), 'label' => 'Editar Utilizador', 'icon' => 'bi-pencil'],
    ],
])

@include('components.admin.stats-cards', [
    'stats' => [
        ['title' => 'Leads Geradas', 'value' => $leadsCount, 'icon' => 'bi-funnel', 'color' => 'primary'],
        ['title' => 'Leads Convertidas', 'value' => $convertedCount, 'icon' => 'bi-person-check', 'color' => 'success'],
        ['title' => 'Taxa de Conversão', 'value' => $conversionRate . '%', 'icon' => 'bi-graph-up', 'color' => 'info'],
        ['title' => 'Comissão (paga / pendente)', 'value' => number_format($comissaoRecebida, 2, ',', '.') . ' € / ' . number_format($comissaoPendente, 2, ',', '.') . ' €', 'icon' => 'bi-cash-coin', 'color' => 'warning'],
    ]
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-funnel"></i> Leads</h5>
        <span class="badge bg-secondary rounded-pill">{{ $leads->count() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Contacto</th>
                    <th>Registada em</th>
                    <th>Propostas</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr>
                    <td>{{ $lead->name }}</td>
                    <td>{{ $lead->email ?? $lead->phone ?? '—' }}</td>
                    <td>{{ $lead->created_at->format('d/m/Y') }}</td>
                    <td>{{ $lead->proposals->count() }}</td>
                    <td>{{ $lead->is_lead ? 'Lead' : 'Convertido' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.v2.leads.show', $lead->id) }}" class="btn btn-icon btn-primary-modern" title="Ver lead (vista admin)">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted p-4">Este angariador ainda não gerou leads.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-clock-history"></i> Atividade Recente</h5>
        <span class="badge bg-secondary rounded-pill">{{ $activity->count() }}</span>
    </div>
    @if($activity->isNotEmpty())
    <div class="timeline-list">
        @foreach($activity as $act)
        <div class="timeline-item">
            <div class="timeline-icon bg-{{ $act->color }}">
                <i class="{{ $act->icon }}"></i>
            </div>
            <div class="timeline-body">
                <div class="timeline-title">{{ $act->title }}</div>
                @if($act->body)
                <div class="timeline-text">{{ $act->body }}</div>
                @endif
                <div class="timeline-meta">
                    {{ $act->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="p-4 text-center text-muted small">
        <i class="bi bi-clock-history d-block fs-4 mb-1"></i>
        Ainda não há atividade registada por este angariador (login ou apontamentos em leads).
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    .timeline-list { padding: .5rem 0; }
    .timeline-item { display: flex; gap: 1rem; padding: .85rem 1.25rem; position: relative; }
    .timeline-item + .timeline-item::before {
        content: ''; position: absolute; top: 0; left: 2.35rem;
        width: 1px; height: 100%; background: #f0f0f0;
    }
    .timeline-icon {
        width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem; color: #fff; opacity: .9;
    }
    .timeline-body { flex: 1; min-width: 0; }
    .timeline-title { font-size: .88rem; font-weight: 600; color: #111; }
    .timeline-text { font-size: .83rem; color: #555; margin-top: .2rem; white-space: pre-line; }
    .timeline-meta { font-size: .72rem; color: #aaa; margin-top: .3rem; }
</style>
@endpush
