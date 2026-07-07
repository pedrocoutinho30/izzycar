@extends('layouts.admin-v2')

@section('title', 'Leads')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Leads']
    ],
    'title' => 'Leads',
    'subtitle' => 'Potenciais clientes que ainda não foram convertidos',
    'actionHref' => route('admin.v2.leads.create'),
    'actionLabel' => 'Nova Lead'
])

@include('components.admin.stats-cards', ['stats' => $stats])

{{-- Toggle lista / kanban + exportação --}}
<div class="d-flex justify-content-end mb-3 gap-2">
    <a href="{{ route('admin.v2.leads.index') }}" class="btn btn-primary-modern btn-sm">
        <i class="bi bi-list-ul me-1"></i> Vista de Lista
    </a>
    <a href="{{ route('admin.v2.leads.kanban') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-kanban me-1"></i> Pipeline Kanban
    </a>
    <a href="{{ route('admin.v2.export.leads', request()->query()) }}"
       class="btn btn-outline-success btn-sm" title="Exportar leads visíveis para CSV (abre no Excel)">
        <i class="bi bi-download me-1"></i> Exportar CSV
    </a>
</div>

@include('components.admin.filter-bar', [
    'filters' => [
        [
            'type' => 'text',
            'name' => 'search',
            'label' => 'Pesquisar',
            'placeholder' => 'Nome, email ou telefone...',
            'value' => request('search')
        ],
        [
            'type' => 'select',
            'name' => 'lead_source',
            'label' => 'Origem',
            'value' => request('lead_source'),
            'options' => [
                '' => 'Todas as origens',
                'simulador' => 'Simulador de Custos',
                'importacao' => 'Formulário de Importação',
                'retoma' => 'Retoma',
                'manual' => 'Manual',
            ]
        ],
        [
            'type' => 'select',
            'name' => 'lead_status',
            'label' => 'Estado',
            'value' => request('lead_status'),
            'options' => [
                '' => 'Todos os estados',
                'nova' => 'Nova',
                'em_contacto' => 'Em Contacto',
                'fria' => 'Fria',
                'perdida' => 'Perdida',
            ]
        ]
    ]
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-funnel"></i>
            Lista de Leads
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $leads->total() }} total</span>
    </div>

    @forelse($leads as $lead)
    <div class="lead-card">
        <div class="lead-card__avatar">
            {{ strtoupper(substr($lead->name, 0, 1)) }}
        </div>
        <div class="lead-card__info">
            <div class="lead-card__name">{{ $lead->name }}</div>
            <div class="lead-card__meta">
                @if($lead->email)
                <span><i class="bi bi-envelope"></i> {{ $lead->email }}</span>
                @endif
                @if($lead->phone)
                <span><i class="bi bi-telephone"></i> {{ $lead->phone }}</span>
                @endif
                <span><i class="bi bi-clock"></i> {{ $lead->created_at->diffForHumans() }}</span>
            </div>
        </div>
        <div class="lead-card__badges">
            @php
                $sourceLabels = [
                    'simulador'  => ['label' => 'Simulador', 'color' => 'info'],
                    'importacao' => ['label' => 'Importação', 'color' => 'primary'],
                    'retoma'     => ['label' => 'Retoma', 'color' => 'warning'],
                    'manual'     => ['label' => 'Manual', 'color' => 'secondary'],
                ];
                $src = $sourceLabels[$lead->lead_source] ?? ['label' => 'Outro', 'color' => 'secondary'];
                $statusLabels = [
                    'nova'        => ['label' => 'Nova', 'color' => 'success'],
                    'em_contacto' => ['label' => 'Em Contacto', 'color' => 'info'],
                    'fria'        => ['label' => 'Fria', 'color' => 'secondary'],
                    'perdida'     => ['label' => 'Perdida', 'color' => 'danger'],
                ];
                $st = $statusLabels[$lead->lead_status ?? 'nova'] ?? $statusLabels['nova'];
            @endphp
            <span class="badge bg-{{ $src['color'] }}">{{ $src['label'] }}</span>
            <span class="badge bg-{{ $st['color'] }} bg-opacity-75">{{ $st['label'] }}</span>
        </div>
        <div class="item-actions">
            <a href="{{ route('admin.v2.leads.show', $lead->id) }}"
               class="btn btn-icon btn-primary-modern" title="Ver detalhes">
                <i class="bi bi-eye"></i>
            </a>
            <form action="{{ route('admin.v2.leads.convert', $lead->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Converter este lead em cliente?')">
                @csrf
                <button type="submit" class="btn btn-icon btn-success-modern" title="Converter em cliente">
                    <i class="bi bi-person-check"></i>
                </button>
            </form>
            <form action="{{ route('admin.v2.leads.destroy', $lead->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Eliminar este lead?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-icon btn-danger-modern" title="Eliminar">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    @include('components.admin.empty-state', [
        'icon' => 'bi-funnel',
        'title' => 'Nenhum lead encontrado',
        'description' => 'Ainda não existem leads ou não há resultados para os filtros aplicados.',
        'actionUrl' => '',
        'actionText' => ''
    ])
    @endforelse
</div>

@include('components.admin.pagination-footer', ['items' => $leads, 'label' => 'leads'])

@endsection

@push('styles')
<style>
.lead-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--admin-border);
    transition: background .15s;
}
.lead-card:last-child { border-bottom: none; }
.lead-card:hover { background: #fafafa; }

.lead-card__avatar {
    width: 42px; height: 42px; flex-shrink: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--admin-primary), #990000);
    color: #fff;
    font-size: .95rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}
.lead-card__info { flex: 1; min-width: 0; }
.lead-card__name { font-weight: 600; color: #111; font-size: .95rem; }
.lead-card__meta {
    display: flex; flex-wrap: wrap; gap: .75rem;
    font-size: .78rem; color: #6c757d; margin-top: .2rem;
}
.lead-card__meta span { display: flex; align-items: center; gap: .3rem; }
.lead-card__badges { flex-shrink: 0; }
.lead-card__actions { display: flex; gap: .4rem; flex-shrink: 0; }

@media(max-width: 768px) {
    .lead-card { flex-wrap: wrap; }
    .lead-card__actions { width: 100%; justify-content: flex-end; }
}
</style>
@endpush
