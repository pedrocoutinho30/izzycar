@extends('layouts.admin-v2')

@section('title', 'Avaliações de Consignação')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-handshake',  'label' => 'Consignações', 'href' => ''],
    ],
    'title'    => 'Avaliações de Consignação',
    'subtitle' => 'Pedidos de avaliação submetidos pelo site',
])

@include('components.admin.stats-cards', ['stats' => $stats])

@include('components.admin.filter-bar', [
    'filters' => [
        [
            'type'        => 'text',
            'name'        => 'search',
            'label'       => 'Pesquisar',
            'placeholder' => 'Nome, email, marca, modelo, matrícula, ref...',
            'value'       => request('search'),
        ],
        [
            'type'    => 'select',
            'name'    => 'status',
            'label'   => 'Estado',
            'value'   => request('status'),
            'options' => [
                ''               => 'Todos os estados',
                'novo'           => 'Novo',
                'contactado'     => 'Contactado',
                'avaliado'       => 'Avaliado',
                'em_consignacao' => 'Em Consignação',
                'vendido'        => 'Vendido',
                'cancelado'      => 'Cancelado',
            ],
        ],
    ],
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i> Lista de Pedidos
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $evaluations->total() }} total</span>
    </div>

    @forelse($evaluations as $ev)
    @php
        $sl = \App\Models\ConsignmentEvaluation::$statusLabels[$ev->status] ?? ['label' => $ev->status, 'color' => 'secondary'];
    @endphp

    @include('components.admin.item-card', [
        'image'    => null,
        'initials' => strtoupper(substr($ev->brand, 0, 1) . substr($ev->model, 0, 1)),
        'color'    => '#6e0707',
        'title'    => $ev->brand . ' ' . $ev->model . ($ev->version ? ' · ' . $ev->version : ''),
        'subtitle' => $ev->name . ' · ' . $ev->phone,
        'badges'   => [
            ['text' => $sl['label'], 'color' => $sl['color']],
            ['text' => $ev->reference, 'color' => 'secondary'],
            ['text' => $ev->condition, 'color' => 'light text-dark'],
        ],
        'meta' => [
            ['icon' => 'bi-calendar3',    'text' => $ev->year],
            ['icon' => 'bi-speedometer2', 'text' => number_format($ev->kilometers, 0, ',', '.') . ' km'],
            ['icon' => 'bi-fuel-pump',    'text' => $ev->fuel],
            ['icon' => 'bi-tag',          'text' => $ev->price_expectation ? number_format($ev->price_expectation, 0, ',', '.') . ' €' : 'Sem valor indicado'],
            ['icon' => 'bi-clock',        'text' => $ev->created_at->diffForHumans()],
        ],
        'actions' => [
            ['label' => 'Ver', 'href' => route('admin.v2.consignment-evaluations.show', $ev->id), 'icon' => 'bi-eye', 'color' => 'primary'],
            [
                'label'   => 'Eliminar',
                'href'    => route('admin.v2.consignment-evaluations.destroy', $ev->id),
                'icon'    => 'bi-trash',
                'color'   => 'danger',
                'method'  => 'DELETE',
                'confirm' => 'Tem a certeza que quer eliminar este pedido?',
            ],
        ],
    ])
    @empty
    @include('components.admin.empty-state', [
        'icon'    => 'bi-inbox',
        'title'   => 'Ainda não existem pedidos de avaliação',
        'message' => 'Os pedidos submetidos no site aparecem aqui automaticamente.',
    ])
    @endforelse
</div>

@include('components.admin.pagination-footer', [
    'items' => $evaluations,
    'label' => 'pedidos',
])

@endsection
