@extends('layouts.admin-v2')

@section('title', 'Vendas')

@section('content')


<!-- PAGE HEADER -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-cash-coin', 'label' => 'Vendas', 'href' => ''],
],
'title' => 'Vendas',
'subtitle' => 'Histórico de vendas de veículos',
'actionHref' => route('admin.v2.sales.create'),
'actionLabel' => 'Nova Venda'
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
'placeholder' => 'Pesquisar por cliente ou veículo...',
'value' => request('search')
],
[
'type' => 'date',
'name' => 'date_from',
'label' => 'Data Início',
'placeholder' => 'Data Início',
'value' => request('date_from')
],
[
'type' => 'date',
'name' => 'date_to',
'label' => 'Data Fim',
'placeholder' => 'Data Fim',
'value' => request('date_to')
],
[
'type' => 'select',
'name' => 'payment_method',
'label' => 'Método de Pagamento',
'placeholder' => 'Método de Pagamento',
'value' => request('payment_method'),
'options' => [
'' => 'Todos',
'Transferência' => 'Transferência',
'Numerário' => 'Numerário',
'Cheque' => 'Cheque',
'Financiamento' => 'Financiamento'
]
]
]
])

<!-- LISTA DE VENDAS -->
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Vendas
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $sales->total() }} total</span>
    </div>
    @forelse($sales as $sale)
    @php
    $vehicle = $sale->vehicle;
    $client = $sale->client;
    if (!$vehicle) {
        $v3 = $sale->v3Vehicle ?? null;
        $mainImage = null;
        $imageUrl = 'https://via.placeholder.com/400x300/6e0707/ffffff?text=' . urlencode($v3 ? $v3->brand . '+' . $v3->model : 'V3+Vehicle');
        $vehicleLabel = $v3 ? $v3->brand . ' ' . $v3->model : '—';
        $vehicleRef   = $v3->reference ?? null;
    } else {
        $v3 = null;
        $mainImage = $vehicle->images->first();
        $imageUrl = $mainImage
        ? asset('storage/' . $mainImage->path)
        : 'https://via.placeholder.com/400x300/6e0707/ffffff?text=' . urlencode($vehicle->brand);
        $vehicleLabel = $vehicle->brand . ' ' . $vehicle->model;
        $vehicleRef   = $vehicle->reference ?? null;
    }
    @endphp

    @include('components.admin.item-card', [
    'image' => $imageUrl,
    'title' => $vehicleLabel,
    'subtitle' => 'Cliente: ' . ($client->name ?? '—'),
    'badges' => array_filter([
    [
    'text' => number_format($sale->sale_price, 0, ',', '.') . '€',
    'color' => 'success'
    ],
    $sale->payment_method ? [
    'text' => $sale->payment_method,
    'color' => 'info'
    ] : null,
    $sale->gross_margin ? [
    'text' => 'Margem Bruta: ' . number_format($sale->gross_margin, 0, ',', '.') . '€',
    'color' => $sale->gross_margin > 0 ? 'success' : 'danger'
    ] : null,
    $sale->net_margin ? [
    'text' => 'Margem Líquida: ' . number_format($sale->net_margin, 0, ',', '.') . '€',
    'color' => $sale->net_margin > 0 ? 'success' : 'danger'
    ] : null,
    $sale->vat_paid ? [
    'text' => 'IVA: ' . number_format($sale->vat_paid, 0, ',', '.') . '€',
    'color' => 'secondary'
    ] : null
    ]),
    'meta' => array_filter([
    [
    'icon' => 'bi-calendar',
    'text' => \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y')
    ],
    [
    'icon' => 'bi-person',
    'text' => $client->name
    ],
    $vehicleRef ? [
    'icon' => 'bi-tag',
    'text' => 'Ref: ' . $vehicleRef
    ] : null
    ]),
    'actions' => [
    [
    'href' => route('admin.v2.sales.edit', $sale->id),
    'icon' => 'bi-pencil',
    'label' => 'Editar',
    'color' => 'primary'
    ],
    [
    'href' => route('admin.v2.sales.destroy', $sale->id),
    'icon' => 'bi-trash',
    'label' => 'Eliminar',
    'color' => 'danger',
    'method' => 'delete',
    'confirm' => 'Tem a certeza que pretende eliminar esta venda?'
    ]
    ]
    ])
    @empty
    @include('components.admin.empty-state', [
    'icon' => 'bi-cash-coin',
    'title' => 'Nenhuma venda encontrada',
    'description' => 'Ainda não existem vendas registadas ou não há resultados para os filtros aplicados.',
    'actionUrl' => route('admin.v2.sales.create'),
    'actionText' => 'Registar Primeira Venda'
    ])
    @endforelse
</div>

<!-- PAGINAÇÃO -->
@if($sales->hasPages())
<div class="pagination-wrapper">
    {{ $sales->links() }}
</div>
@endif

<style>
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