@extends('layouts.admin-v2')

@section('title', 'As Minhas Propostas')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Início', 'href' => route('admin.angariador.dashboard')],
        ['icon' => '', 'label' => 'Propostas'],
    ],
    'title' => 'Propostas Enviadas',
    'subtitle' => 'Propostas enviadas aos clientes das suas leads — o mesmo documento que o cliente recebeu',
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-file-earmark-text"></i> Propostas</h5>
        <span class="badge bg-secondary rounded-pill">{{ $propostas->count() }} total</span>
    </div>

    @forelse($propostas as $proposal)
    @php
        $pStatusColors = ['Pendente' => 'warning', 'Aprovada' => 'success', 'Reprovada' => 'danger', 'Enviado' => 'info', 'Sem resposta' => 'secondary'];
        $pColor = $pStatusColors[$proposal->status] ?? 'secondary';
    @endphp
    <div class="lead-activity-item align-items-center">
        <div class="lad-icon" style="background:rgba(13,110,253,.1);color:#0d6efd;">
            <i class="bi bi-file-earmark-text"></i>
        </div>
        <div class="lad-body">
            <div class="lad-title">{{ $proposal->client->name ?? '—' }} — {{ trim(($proposal->brand ?? '') . ' ' . ($proposal->model ?? '')) }}</div>
            <div class="lad-meta">{{ $proposal->created_at->format('d/m/Y') }}</div>
            <div class="lad-tags">
                <span class="lad-tag badge bg-{{ $pColor }}">{{ $proposal->status ?? 'Pendente' }}</span>
            </div>
        </div>
        @if($proposal->proposal_code)
        <a href="{{ route('proposals.detail', $proposal->proposal_code) }}" target="_blank"
           class="btn btn-icon btn-primary-modern ms-auto flex-shrink-0" title="Ver proposta">
            <i class="bi bi-arrow-up-right-square"></i>
        </a>
        @endif
    </div>
    @empty
    @include('components.admin.empty-state', [
        'icon' => 'bi-file-earmark-text',
        'title' => 'Ainda sem propostas',
        'message' => 'Quando a equipa enviar uma proposta a uma das suas leads, aparecerá aqui.',
    ])
    @endforelse
</div>

@endsection

@push('styles')
<style>
.lead-activity-item { display: flex; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--admin-border); }
.lead-activity-item:last-child { border-bottom: none; }
.lad-icon { width: 36px; height: 36px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .9rem; }
.lad-body { flex: 1; min-width: 0; }
.lad-title { font-weight: 600; font-size: .9rem; color: #111; }
.lad-meta { font-size: .75rem; color: #6c757d; margin: .15rem 0 .4rem; }
.lad-tags { display: flex; flex-wrap: wrap; gap: .35rem; }
</style>
@endpush
