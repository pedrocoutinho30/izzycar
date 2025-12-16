@extends('layouts.admin-v2')

@section('title', 'Propostas Convertidas')

@section('content')
<div class="admin-content">
    <!-- HEADER -->
    <div class="content-header">
        <div>
            <h1 class="content-title">Propostas Convertidas</h1>
            <p class="content-subtitle">Propostas em processo de concretização</p>
        </div>
        <div class="content-actions">
            <a href="{{ route('admin.v2.converted-proposals.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nova Proposta Convertida
            </a>
        </div>
    </div>

    <!-- STATS CARDS -->
    @include('components.admin.stats-cards', ['stats' => $stats])

    <!-- FILTROS -->
    @include('components.admin.filter-bar', [
        'filters' => [
            [
                'type' => 'text',
                'name' => 'search',
                'label' => 'Pesquisar',
                'placeholder' => 'Pesquisar por cliente, marca ou matrícula...',
                'value' => request('search')
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => 'Estado',
                'placeholder' => 'Estado',
                'value' => request('status'),
                'options' => [
                    '' => 'Todos os Estados',
                    'Em Processo' => 'Em Processo',
                    'Concluída' => 'Concluída',
                    'Cancelada' => 'Cancelada'
                ]
            ]
        ]
    ])

    <!-- LISTA DE PROPOSTAS CONVERTIDAS -->
    <div class="content-grid">
        @forelse($convertedProposals as $converted)
            @php
                $statusColors = [
                    'Em Processo' => 'warning',
                    'Concluída' => 'success',
                    'Cancelada' => 'danger',
                ];
                $currentStatus = $converted->status ?? 'Em Processo';
            @endphp

            @include('components.admin.item-card', [
                'image' => 'https://ui-avatars.com/api/?name=' . urlencode($converted->brand ?? 'Veículo') . '&background=6e0707&color=fff&bold=true',
                'title' => ($converted->brand ?? '') . ' ' . ($converted->modelCar ?? ''),
                'subtitle' => 'Cliente: ' . ($converted->client->name ?? 'N/A'),
                'badges' => array_filter([
                    [
                        'text' => $currentStatus,
                        'color' => $statusColors[$currentStatus] ?? 'secondary'
                    ],
                    $converted->matricula_origem ? [
                        'text' => 'Origem: ' . $converted->matricula_origem,
                        'color' => 'info'
                    ] : null,
                    $converted->matricula_destino ? [
                        'text' => 'Destino: ' . $converted->matricula_destino,
                        'color' => 'primary'
                    ] : null,
                    $converted->valor_carro ? [
                        'text' => number_format($converted->valor_carro, 0, ',', '.') . '€',
                        'color' => 'success'
                    ] : null
                ]),
                'meta' => array_filter([
                    [
                        'icon' => 'bi-person',
                        'text' => $converted->client->name ?? 'Sem cliente'
                    ],
                    $converted->year ? [
                        'icon' => 'bi-calendar',
                        'text' => $converted->year
                    ] : null,
                    [
                        'icon' => 'bi-clock',
                        'text' => 'Criada ' . $converted->created_at->diffForHumans()
                    ]
                ]),
                'actions' => [
                    [
                        'href' => route('admin.v2.converted-proposals.edit', $converted->id),
                        'icon' => 'bi-pencil',
                        'label' => 'Editar',
                        'color' => 'primary'
                    ],
                    [
                        'href' => route('admin.v2.converted-proposals.destroy', $converted->id),
                        'icon' => 'bi-trash',
                        'label' => 'Eliminar',
                        'color' => 'danger',
                        'method' => 'delete',
                        'confirm' => 'Tem a certeza que pretende eliminar esta proposta convertida?'
                    ]
                ]
            ])
        @empty
            @include('components.admin.empty-state', [
                'icon' => 'bi-check2-circle',
                'title' => 'Nenhuma proposta convertida',
                'description' => 'Ainda não existem propostas convertidas ou não há resultados para os filtros aplicados.',
                'actionUrl' => route('admin.v2.converted-proposals.create'),
                'actionText' => 'Criar Primeira Proposta Convertida'
            ])
        @endforelse
    </div>

    <!-- PAGINAÇÃO -->
    @if($convertedProposals->hasPages())
        <div class="pagination-wrapper">
            {{ $convertedProposals->links() }}
        </div>
    @endif
</div>

<style>
.content-grid > * {
    animation: fadeInUp 0.4s ease-out backwards;
}

.content-grid > *:nth-child(1) { animation-delay: 0.05s; }
.content-grid > *:nth-child(2) { animation-delay: 0.1s; }
.content-grid > *:nth-child(3) { animation-delay: 0.15s; }
.content-grid > *:nth-child(4) { animation-delay: 0.2s; }
.content-grid > *:nth-child(5) { animation-delay: 0.25s; }
.content-grid > *:nth-child(6) { animation-delay: 0.3s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endsection
