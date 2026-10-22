@extends('layouts.admin-v2')

@section('title', 'As Minhas Cotações Convertidas')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Início', 'href' => route('admin.angariador.dashboard')],
        ['icon' => '', 'label' => 'Cotações convertidas'],
    ],
    'title' => 'Cotações Convertidas',
    'subtitle' => 'Cotações aceites pelos clientes das suas leads — estado do processo e valor final',
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-check2-circle"></i> Cotações convertidas</h5>
        <span class="badge bg-secondary rounded-pill">{{ $convertidas->count() }} total</span>
    </div>

    @forelse($convertidas as $cp)
    @php
        $cpStatus = $cp->status ?: 'Iniciada';
        $cpColor = $cp->isCancelled() ? 'danger' : ($cpStatus === 'Concluido' ? 'success' : 'info');
        $cpTotal = $cp->proposal?->totalCost();
    @endphp
    <div class="lead-activity-item align-items-center">
        <div class="lad-icon" style="background:rgba(25,135,84,.1);color:#198754;"><i class="bi bi-check2-circle"></i></div>
        <div class="lad-body">
            <div class="lad-title"><x-admin.brand-logo :brand="$cp->brand" :size="24" class="me-1" />{{ $cp->client->name ?? '—' }} — {{ trim(($cp->brand ?? '') . ' ' . ($cp->modelCar ?? '')) }}</div>
            <div class="lad-meta">
                @if($cpTotal) <strong>{{ number_format($cpTotal, 0, ',', '.') }} €</strong> · @endif
                Convertida em {{ $cp->created_at->format('d/m/Y') }}
            </div>
            <div class="lad-tags"><span class="lad-tag badge bg-{{ $cpColor }}">{{ $cpStatus }}</span></div>
        </div>
        <a href="{{ route('admin.angariador.convertidas.show', $cp->id) }}"
           class="btn btn-icon btn-primary-modern ms-auto flex-shrink-0" title="Ver cotação convertida">
            <i class="bi bi-arrow-up-right-square"></i>
        </a>
    </div>
    @empty
    @include('components.admin.empty-state', [
        'icon' => 'bi-check2-circle',
        'title' => 'Ainda sem cotações convertidas',
        'message' => 'Quando um cliente das suas leads aceitar uma cotação, aparecerá aqui.',
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
