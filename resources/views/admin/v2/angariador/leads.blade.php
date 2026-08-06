@extends('layouts.admin-v2')

@section('title', 'As Minhas Leads')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Início', 'href' => route('admin.angariador.dashboard')],
        ['icon' => '', 'label' => 'As Minhas Leads'],
    ],
    'title' => 'As Minhas Leads',
    'subtitle' => 'Contactos que registou através do seu código de angariador',
])

<div class="d-flex justify-content-end mb-3 gap-2">
    <a href="{{ route('admin.angariador.leads') }}" class="btn btn-primary-modern btn-sm">
        <i class="bi bi-list-ul me-1"></i> Vista de Lista
    </a>
    <a href="{{ route('admin.angariador.leads.kanban') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-kanban me-1"></i> Pipeline Kanban
    </a>
</div>

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-funnel"></i> Leads</h5>
        <span class="badge bg-secondary rounded-pill">{{ $leads->count() }} total</span>
    </div>

    @forelse($leads as $lead)
    <div class="lead-card">
        <div class="lead-card__avatar">{{ strtoupper(substr($lead->name, 0, 1)) }}</div>
        <div class="lead-card__info">
            <div class="lead-card__name">{{ $lead->name }}</div>
            <div class="lead-card__meta">
                @if($lead->email)<span><i class="bi bi-envelope"></i> {{ $lead->email }}</span>@endif
                @if($lead->phone)<span><i class="bi bi-telephone"></i> {{ $lead->phone }}</span>@endif
                <span><i class="bi bi-clock"></i> {{ $lead->created_at->diffForHumans() }}</span>
            </div>
        </div>
        <div class="lead-card__badges">
            @if($lead->proposals_count > 0)
                <span class="badge bg-info">Proposta enviada</span>
            @else
                <span class="badge bg-secondary">Em tratamento pela equipa</span>
            @endif
        </div>
        <div class="item-actions">
            <a href="{{ route('admin.angariador.leads.show', $lead->id) }}" class="btn btn-icon btn-primary-modern" title="Ver detalhes">
                <i class="bi bi-eye"></i>
            </a>
        </div>
    </div>
    @empty
    @include('components.admin.empty-state', [
        'icon' => 'bi-funnel',
        'title' => 'Ainda sem leads',
        'message' => 'Partilhe o seu link de angariador para começar a gerar leads.',
    ])
    @endforelse
</div>

@endsection

@push('styles')
<style>
.lead-card { display: flex; align-items: center; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--admin-border); }
.lead-card:last-child { border-bottom: none; }
.lead-card__avatar { width: 42px; height: 42px; flex-shrink: 0; border-radius: 50%; background: linear-gradient(135deg, var(--admin-primary), #990000); color: #fff; font-size: .95rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
.lead-card__info { flex: 1; min-width: 0; }
.lead-card__name { font-weight: 600; color: #111; font-size: .95rem; }
.lead-card__meta { display: flex; flex-wrap: wrap; gap: .75rem; font-size: .78rem; color: #6c757d; margin-top: .2rem; }
.lead-card__meta span { display: flex; align-items: center; gap: .3rem; }
.lead-card__badges { flex-shrink: 0; }
</style>
@endpush
