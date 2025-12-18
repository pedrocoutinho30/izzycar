@extends('layouts.admin-v2')

@section('title', 'Formulários de Proposta')

@section('content')


<!-- Page Header -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-file-earmark-text', 'label' => 'Formulários de Proposta', 'href' => ''],
],
'title' => 'Formulários de Proposta',
'subtitle' => 'Pedidos recebidos através do website',
'actionHref' => '',
'actionLabel' => ''
])
    <!-- STATS CARDS -->
    @include('components.admin.stats-cards', ['stats' => $stats])

    <!-- FILTROS -->
    @include('components.admin.filter-bar', [
        'filters' => [
            [
                'type' => 'text',
                'name' => 'search',
                'label' => 'Pesquisar',
                'placeholder' => 'Pesquisar por nome, email, telefone...',
                'value' => request('search')
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => 'Estado',
                'placeholder' => 'Estado',
                'value' => request('status'),
                'options' => [
                    'novo' => 'Novo',
                    'em_analise' => 'Em Análise',
                    'convertido' => 'Convertido',
                    'arquivado' => 'Arquivado'
                ]
            ]
        ]
    ])

    <!-- LISTA DE FORMULÁRIOS -->
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Formulários de Proposta
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $formProposals->total() }} total</span>
    </div>

        @forelse($formProposals as $form)
            @php
                $statusColors = [
                    'novo' => 'danger',
                    'em_analise' => 'warning',
                    'convertido' => 'success',
                    'arquivado' => 'secondary'
                ];
                $statusLabels = [
                    'novo' => 'Novo',
                    'em_analise' => 'Em Análise',
                    'convertido' => 'Convertido',
                    'arquivado' => 'Arquivado'
                ];
                $currentStatus = $form->status ?? 'novo';
            @endphp

            @include('components.admin.item-card', [
                'image' => 'https://ui-avatars.com/api/?name=' . urlencode($form->name) . '&background=6e0707&color=fff&bold=true',
                'title' => $form->name,
                'subtitle' => $form->email ?? $form->phone,
                'badges' => array_filter([
                    [
                        'text' => $statusLabels[$currentStatus] ?? 'Novo',
                        'color' => $statusColors[$currentStatus] ?? 'secondary'
                    ],
                    $form->brand ? [
                        'text' => $form->brand . ($form->model ? ' ' . $form->model : ''),
                        'color' => 'primary'
                    ] : null,
                    $form->budget ? [
                        'text' => 'Budget: ' . number_format($form->budget, 0, ',', '.') . '€',
                        'color' => 'info'
                    ] : null
                ]),
                'meta' => array_filter([
                    [
                        'icon' => 'bi-envelope',
                        'text' => $form->email ?? 'Sem email'
                    ],
                    [
                        'icon' => 'bi-telephone',
                        'text' => $form->phone ?? 'Sem telefone'
                    ],
                    [
                        'icon' => 'bi-calendar',
                        'text' => $form->created_at->format('d/m/Y H:i')
                    ]
                ]),
                'actions' => [
                    [
                        'href' => route('admin.v2.form-proposals.show', $form->id),
                        'icon' => 'bi-eye',
                        'label' => 'Ver Detalhes',
                        'color' => 'primary'
                    ]
                ]
            ])
        @empty
            @include('components.admin.empty-state', [
                'icon' => 'bi-envelope',
                'title' => 'Nenhum formulário encontrado',
                'description' => 'Não existem formulários de proposta ou não há resultados para os filtros aplicados.'
            ])
        @endforelse
    </div>

    <!-- PAGINAÇÃO -->
    @if($formProposals->hasPages())
        <div class="pagination-wrapper">
            {{ $formProposals->links() }}
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
