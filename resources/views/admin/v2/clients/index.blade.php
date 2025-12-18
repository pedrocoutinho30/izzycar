@extends('layouts.admin-v2')

@section('title', 'Clientes')

@section('content')

<!-- PAGE HEADER -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-people', 'label' => 'Clientes', 'href' => ''],
],
'title' => 'Clientes',
'subtitle' => 'Gestão de clientes e contactos',
'actionHref' => route('admin.v2.clients.create'),
'actionLabel' => 'Novo Cliente'
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
'name' => 'client_type',
'label' => 'Tipo de Cliente',
'placeholder' => 'Tipo de Cliente',
'value' => request('client_type'),
'options' => [
'' => 'Todos os Tipos',
'particular' => 'Particular',
'empresa' => 'Empresa'
]
]
]
])

<!-- LISTA DE CLIENTES -->
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Clientes
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $clients->total() }} total</span>
    </div>

    @forelse($clients as $client)
    @php
    $clientImage = $client->gender === 'masculino'
    ? 'https://ui-avatars.com/api/?name=' . urlencode($client->name) . '&background=6e0707&color=fff&bold=true'
    : 'https://ui-avatars.com/api/?name=' . urlencode($client->name) . '&background=e91e63&color=fff&bold=true';
    @endphp

    @include('components.admin.item-card', [
    'image' => $clientImage,
    'title' => $client->name,
    'subtitle' => $client->email ?? 'Sem email',
    'badges' => array_filter([
    $client->client_type ? [
    'text' => ucfirst($client->client_type),
    'color' => $client->client_type === 'empresa' ? 'primary' : 'info'
    ] : null,
    $client->vat_number ? [
    'text' => 'NIF: ' . $client->vat_number,
    'color' => 'secondary'
    ] : null
    ]),
    'meta' => array_filter([
    $client->phone ? [
    'icon' => 'bi-telephone',
    'text' => $client->phone
    ] : null,
    $client->city ? [
    'icon' => 'bi-geo-alt',
    'text' => $client->city
    ] : null,
    [
    'icon' => 'bi-calendar',
    'text' => 'Registado em ' . $client->created_at->format('d/m/Y')
    ]
    ]),
    'actions' => [
    [
    'href' => route('admin.v2.clients.edit', $client->id),
    'icon' => 'bi-pencil',
    'label' => 'Editar',
    'color' => 'primary'
    ],
    [
    'href' => route('admin.v2.clients.destroy', $client->id),
    'icon' => 'bi-trash',
    'label' => 'Eliminar',
    'color' => 'danger',
    'method' => 'delete',
    'confirm' => 'Tem a certeza que pretende eliminar este cliente?'
    ]
    ]
    ])
    @empty
    @include('components.admin.empty-state', [
    'icon' => 'bi-people',
    'title' => 'Nenhum cliente encontrado',
    'description' => 'Ainda não existem clientes registados ou não há resultados para os filtros aplicados.',
    'actionUrl' => route('admin.v2.clients.create'),
    'actionText' => 'Criar Primeiro Cliente'
    ])
    @endforelse
</div>
</div>

<!-- Pagination -->

@include('components.admin.pagination-footer', [
'items' => $clients,
'label' => 'clientes'
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