@extends('layouts.admin-v2')

@section('title', 'Vendedores')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-shop', 'label' => 'Vendedores'],
    ],
    'title' => 'Vendedores',
    'subtitle' => 'Stands e empresas com quem a IzzyCar trabalha',
    'extraActions' => [
        ['href' => route('admin.v2.sellers.create'), 'label' => 'Novo vendedor', 'icon' => 'bi-plus-lg', 'class' => 'btn-primary-modern'],
    ],
])

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@include('components.admin.stats-cards', ['stats' => $stats])

@include('components.admin.filter-bar', [
    'action' => route('admin.v2.sellers.index'),
    'filters' => [
        [
            'type' => 'text',
            'name' => 'search',
            'label' => 'Pesquisar',
            'placeholder' => 'Empresa, contacto, email, telefone, WhatsApp ou domínio...',
            'value' => request('search'),
            'col' => 8,
        ],
        [
            'type' => 'select',
            'name' => 'status',
            'label' => 'Estado',
            'value' => request('status'),
            'options' => ['ativos' => 'Ativos', 'inativos' => 'Inativos'],
        ],
    ],
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Vendedores
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $sellers->total() }} total</span>
    </div>

    @forelse($sellers as $seller)
        @php $primary = $seller->primaryContact; @endphp
        @include('components.admin.item-card', [
            'image' => 'https://ui-avatars.com/api/?name=' . urlencode($seller->name) . '&background=6e0707&color=fff&bold=true',
            'title' => $seller->name,
            'subtitle' => $primary
                ? trim($primary->name . ($primary->role ? ' · ' . $primary->role : ''))
                : 'Sem contacto principal',
            'badges' => array_values(array_filter([
                $seller->is_active ? null : ['text' => 'Inativo', 'color' => 'secondary'],
                $seller->country_label ? ['text' => $seller->country_label, 'color' => 'info'] : null,
                ['text' => $seller->contacts_count . ' ' . ($seller->contacts_count === 1 ? 'contacto' : 'contactos'), 'color' => 'light text-dark', 'icon' => 'bi-people'],
                ['text' => $seller->vehicles_count . ' ' . ($seller->vehicles_count === 1 ? 'veículo' : 'veículos'), 'color' => $seller->vehicles_count ? 'success' : 'light text-dark', 'icon' => 'bi-car-front'],
                ['text' => $seller->opportunities_count . ' ' . ($seller->opportunities_count === 1 ? 'oportunidade' : 'oportunidades'), 'color' => 'light text-dark', 'icon' => 'bi-search'],
            ])),
            'meta' => array_values(array_filter([
                $seller->website_url ? ['icon' => 'bi-globe', 'text' => $seller->website, 'href' => $seller->website_url, 'target' => '_blank'] : null,
                $primary?->email ? ['icon' => 'bi-envelope', 'text' => $primary->email, 'href' => 'mailto:' . $primary->email] : null,
                $primary?->phone ? ['icon' => 'bi-telephone', 'text' => $primary->phone] : null,
            ])),
            'actions' => [
                ['href' => route('admin.v2.sellers.show', $seller->id), 'icon' => 'bi-eye', 'label' => 'Abrir', 'color' => 'secondary'],
                ['href' => route('admin.v2.sellers.edit', $seller->id), 'icon' => 'bi-pencil', 'label' => 'Editar', 'color' => 'primary'],
            ],
        ])
    @empty
        @include('components.admin.empty-state', [
            'icon' => 'bi-shop',
            'title' => 'Nenhum vendedor encontrado',
            'message' => request()->hasAny(['search', 'status'])
                ? 'Não há vendedores para os filtros aplicados.'
                : 'Ainda não existem vendedores registados.',
            'action' => ['text' => 'Novo vendedor', 'href' => route('admin.v2.sellers.create'), 'icon' => 'bi-plus-lg'],
        ])
    @endforelse
</div>

@include('components.admin.pagination-footer', ['items' => $sellers, 'label' => 'vendedores'])

@endsection
