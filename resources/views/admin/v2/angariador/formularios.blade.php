@extends('layouts.admin-v2')

@section('title', 'Formulários Recebidos')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Início', 'href' => route('admin.angariador.dashboard')],
        ['icon' => '', 'label' => 'Formulários'],
    ],
    'title' => 'Formulários Recebidos',
    'subtitle' => 'Pedidos de importação submetidos através do seu código de angariador',
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-envelope-open"></i> Formulários</h5>
        <span class="badge bg-secondary rounded-pill">{{ $formularios->count() }} total</span>
    </div>

    @forelse($formularios as $fp)
    <div class="lead-activity-item align-items-center">
        <div class="lad-icon" style="background:rgba(13,110,253,.1);color:#0d6efd;">
            <i class="bi bi-envelope-open"></i>
        </div>
        <div class="lad-body">
            <div class="lad-title">{{ trim(($fp->brand ?? '') . ' ' . ($fp->model ?? '')) ?: 'Pedido de importação' }}</div>
            <div class="lad-meta">{{ $fp->created_at->format('d/m/Y H:i') }}</div>
        </div>
    </div>
    @empty
    @include('components.admin.empty-state', [
        'icon' => 'bi-envelope-open',
        'title' => 'Ainda sem formulários',
        'message' => 'Quando alguém submeter o formulário de importação com o seu código, aparecerá aqui.',
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
</style>
@endpush
