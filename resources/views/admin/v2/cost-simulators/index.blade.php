@extends('layouts.admin-v2')

@section('title', 'Clientes')

@section('content')

<!-- PAGE HEADER -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-people', 'label' => 'Simulador de Custos', 'href' => ''],
],
'title' => 'Simulador de Custos',
'subtitle' => 'Gestão de todas as simulações de custos realizadas pelos clientes.',
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
]
])

<!-- LISTA DE CLIENTES -->
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Simulações
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $costSimulators->total() }} total</span>
    </div>

    @forelse($costSimulators as $costSimulator)
    @php

    $client = App\Models\Client::find($costSimulator->client_id);
    @endphp
    @include('components.admin.item-card', [
    'title' => $client ? $client->name : 'Cliente Removido',
    'subtitle' => 'Simulação realizada em ' . $costSimulator->created_at->format('d/m/Y H:i'),
    'image' => null,
    'badges' => [
    [
    'text' => 'Custo do carro: ' . number_format($costSimulator->car_value, 2, ',', '.') . '€',
    'color' => 'primary',
    'icon' => 'bi-calculator'
    ],
     [
    'text' => 'ISV: ' . number_format($costSimulator->isv_cost, 2, ',', '.') . '€',
    'color' => 'warning',
    'icon' => 'bi-calculator'
    ],
    [
    'text' => 'Custo Total: ' . number_format($costSimulator->total_cost, 2, ',', '.') . '€',
    'color' => 'success',
    'icon' => 'bi-calculator'
    ],
    [
    'text' => 'Telemóvel: ' . ($client ? $client->phone : 'N/A'),
    'color' => 'info',
    'icon' => 'bi-telephone'
    ],
    [
    'text' => 'Email: ' . ($client ? $client->email : 'N/A'),
    'color' => 'danger',
    'icon' => 'bi-envelope'
    ]

    ],
    'meta' => [
    [
    'icon' => 'bi-phone',
    'text' => 'Criada em ' . $costSimulator->created_at->format('d/m/Y H:i')
    ]
    ],
    'actions' => [
    [
    'href' => route('admin.v2.clients.edit', $costSimulator->client_id),
    'icon' => 'bi-eye',
    'label' => 'Ver Detalhe cliente',
    'color' => 'primary',

    ],
    [
    'href' => route('admin.v2.cost-simulators.destroy', $costSimulator->id),
    'icon' => 'bi-trash',
    'label' => 'Eliminar',
    'color' => 'danger',
    'method' => 'delete',
    'confirm' => 'Tem a certeza que pretende eliminar esta simulação?'
    ]

    ]
    ])


    @empty
    @include('components.admin.empty-state', [
    'icon' => 'bi-people',
    'title' => 'Nenhuma simulação encontrada',
    'description' => 'Ainda não existem simulações de custos registadas ou não há resultados para os filtros aplicados.',
    'actionUrl' => '',
    'actionText' => ''
    ])
    @endforelse
</div>
</div>

<!-- Pagination -->

@include('components.admin.pagination-footer', [
'items' => $costSimulators,
'label' => 'simulações'
])


<style>
    /* Animação de entrada para os cards */
    .content-grid>* {
        animation: fadeInUp 0.4s ease-out backwards;
    }

    .content-grid>*:nth-child(1) {
        animation-delay: 0.05s;
    }

    .content-grid>*:nth-child(2) {
        animation-delay: 0.1s;
    }

    .content-grid>*:nth-child(3) {
        animation-delay: 0.15s;
    }

    .content-grid>*:nth-child(4) {
        animation-delay: 0.2s;
    }

    .content-grid>*:nth-child(5) {
        animation-delay: 0.25s;
    }

    .content-grid>*:nth-child(6) {
        animation-delay: 0.3s;
    }

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