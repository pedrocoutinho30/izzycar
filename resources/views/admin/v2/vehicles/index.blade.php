@extends('layouts.admin-v2')

@section('title', 'Veículos')

@section('content')

<!-- Page Header -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
    ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => ''],
    ['icon' => 'bi bi-car-front', 'label' => 'Veículos', 'href' => ''],
    ],
'title' => 'Veículos',
'subtitle' => 'Gestão de stock de veículos',
'actionHref' => route('admin.v2.vehicles.create'),
'actionLabel' => 'Novo Veículo'
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
'placeholder' => 'Pesquisar por referência, marca, modelo, matrícula...',
'value' => request('search')
],
[
'type' => 'select',
'name' => 'brand',
'label' => 'Marca',
'placeholder' => 'Marca',
'value' => request('brand'),
'options' => ['' => 'Todas as Marcas'] + $brands->mapWithKeys(fn($brand) => [$brand => $brand])->toArray()
],
[
'type' => 'select',
'name' => 'fuel',
'label' => 'Combustível',
'placeholder' => 'Combustível',
'value' => request('fuel'),
'options' => [
'' => 'Todos',
'Gasolina' => 'Gasolina',
'Gasóleo' => 'Gasóleo',
'Híbrido' => 'Híbrido',
'Elétrico' => 'Elétrico',
'GPL' => 'GPL'
]
],
[
'type' => 'select',
'name' => 'availability',
'label' => 'Disponibilidade',
'placeholder' => 'Disponibilidade',
'value' => request('availability'),
'options' => [
'' => 'Todos',
'available' => 'Disponível',
'sold' => 'Vendido'
]
]
]
])

<!-- LISTA DE VEÍCULOS -->
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Veículos
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $vehicles->total() }} total</span>
    </div>

    @forelse($vehicles as $vehicle)
    @php
    $mainImage = $vehicle->images->first();
    $imageUrl = $mainImage
    ? asset('storage/' . $mainImage->path)
    : 'https://via.placeholder.com/400x300/6e0707/ffffff?text=' . urlencode($vehicle->brand);

    $isSold = $vehicle->sale()->exists();
    @endphp

    @include('components.admin.item-card', [
    'image' => $imageUrl,
    'title' => $vehicle->brand . ' ' . $vehicle->model,
    'subtitle' => $vehicle->reference,
    'badges' => array_filter([
    $isSold ? [
    'text' => 'VENDIDO',
    'color' => 'danger'
    ] : [
    'text' => 'DISPONÍVEL',
    'color' => 'success'
    ],
    $vehicle->year ? [
    'text' => $vehicle->year,
    'color' => 'secondary'
    ] : null,
    $vehicle->fuel ? [
    'text' => $vehicle->fuel,
    'color' => 'info'
    ] : null,
    $vehicle->show_online ? [
    'text' => 'ONLINE',
    'color' => 'warning'
    ] : null
    ]),
    'meta' => array_filter([
    $vehicle->kilometers ? [
    'icon' => 'bi-speedometer2',
    'text' => number_format($vehicle->kilometers, 0, ',', '.') . ' km'
    ] : null,
    $vehicle->purchase_price ? [
    'icon' => 'bi-tag',
    'text' => 'Compra: ' . number_format($vehicle->purchase_price, 0, ',', '.') . '€'
    ] : null,
    $vehicle->sell_price ? [
    'icon' => 'bi-cash',
    'text' => 'Venda: ' . number_format($vehicle->sell_price, 0, ',', '.') . '€'
    ] : null,
    $vehicle->registration ? [
    'icon' => 'bi-credit-card-2-front',
    'text' => $vehicle->registration
    ] : null
    ]),
    'actions' => [
    [
    'href' => route('admin.v2.vehicles.edit', $vehicle->id),
    'icon' => 'bi-pencil',
    'label' => 'Editar',
    'color' => 'primary'
    ],
    [
    'href' => route('admin.v2.vehicles.destroy', $vehicle->id),
    'icon' => 'bi-trash',
    'label' => 'Eliminar',
    'color' => 'danger',
    'method' => 'delete',
    'confirm' => 'Tem a certeza que pretende eliminar este veículo?'
    ]
    ]
    ])
    @empty
    @include('components.admin.empty-state', [
    'icon' => 'bi-car-front',
    'title' => 'Nenhum veículo encontrado',
    'description' => 'Ainda não existem veículos registados ou não há resultados para os filtros aplicados.',
    'actionUrl' => route('admin.v2.vehicles.create'),
    'actionText' => 'Adicionar Primeiro Veículo'
    ])
    @endforelse
</div>

<!-- PAGINAÇÃO -->
@if($vehicles->hasPages())
<div class="pagination-wrapper">
    {{ $vehicles->links() }}
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