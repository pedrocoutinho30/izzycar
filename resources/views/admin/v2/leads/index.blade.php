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
        ],
        [
            'type' => 'select',
            'name' => 'owner_id',
            'label' => 'Angariador',
            'value' => request('owner_id'),
            'options' => ['' => 'Todos'] + $angariadores->pluck('name', 'id')->toArray(),
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
        $leadImage = 'https://ui-avatars.com/api/?name=' . urlencode($lead->name) . '&background=6e0707&color=fff&bold=true';
    @endphp

    @include('components.admin.item-card', [
        'image' => $leadImage,
        'title' => $lead->name,
        'subtitle' => $lead->email,
        'badges' => [
            ['text' => $src['label'], 'color' => $src['color']],
            ['text' => $st['label'], 'color' => $st['color']],
        ],
        'meta' => array_filter([
            $lead->phone ? ['icon' => 'bi-telephone', 'text' => $lead->phone] : null,
            ['icon' => 'bi-clock', 'text' => $lead->created_at->diffForHumans()],
            $lead->owner ? ['icon' => 'bi-person-badge', 'text' => $lead->owner->name] : null,
        ]),
        'actions' => [
            [
                'href' => route('admin.v2.leads.show', $lead->id),
                'icon' => 'bi-eye',
                'label' => 'Ver detalhes',
                'color' => 'primary',
            ],
            [
                'href' => route('admin.v2.leads.convert', $lead->id),
                'icon' => 'bi-person-check',
                'label' => 'Converter em cliente',
                'color' => 'success',
                'method' => 'POST',
                'confirm' => 'Converter este lead em cliente?',
            ],
            [
                'href' => route('admin.v2.leads.destroy', $lead->id),
                'icon' => 'bi-trash',
                'label' => 'Eliminar',
                'color' => 'danger',
                'method' => 'DELETE',
                'confirm' => 'Eliminar este lead?',
            ],
        ],
    ])
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
