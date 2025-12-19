@extends('layouts.admin-v2')

@section('title', 'Parceiros')

@section('content')

    <!-- Page Header -->
    @include('components.admin.page-header', [
    'breadcrumbs' => [
    ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
    ['icon' => '', 'label' => 'Parceiros']
    ],
    'title' => 'Parceiros',
    'subtitle' => 'Rede de parceiros de negócio',
    'actionHref' => route('admin.v2.partners.create'),
    'actionLabel' => 'Novo Parceiro'
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
    'placeholder' => 'Pesquisar por nome, contacto ou NIF...',
    'value' => request('search')
    ]
    ]
    ])

    <!-- LISTA DE PARCEIROS -->
    <div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Parceiros
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $partners->total() }} total</span>
    </div>
        @forelse($partners as $partner)
        @include('components.admin.item-card', [
        'image' => 'https://ui-avatars.com/api/?name=' . urlencode($partner->name) . '&background=6e0707&color=fff&bold=true',
        'title' => $partner->name,
        'subtitle' => $partner->contact_name ?? 'Sem contacto definido',
        'badges' => array_filter([
        $partner->vat ? [
        'text' => 'NIF: ' . $partner->vat,
        'color' => 'secondary'
        ] : null,
        $partner->country ? [
        'text' => $partner->country,
        'color' => 'info'
        ] : null
        ]),
        'meta' => array_filter([
        $partner->phone ? [
        'icon' => 'bi-telephone',
        'text' => $partner->phone
        ] : null,
        $partner->email ? [
        'icon' => 'bi-envelope',
        'text' => $partner->email
        ] : null,
        $partner->city ? [
        'icon' => 'bi-geo-alt',
        'text' => $partner->city
        ] : null
        ]),
        'actions' => [
        [
        'href' => route('admin.v2.partners.edit', $partner->id),
        'icon' => 'bi-pencil',
        'label' => 'Editar',
        'color' => 'primary'
        ],
        [
        'href' => route('admin.v2.partners.destroy', $partner->id),
        'icon' => 'bi-trash',
        'label' => 'Eliminar',
        'color' => 'danger',
        'method' => 'delete',
        'confirm' => 'Tem a certeza que pretende eliminar este parceiro?'
        ]
        ]
        ])
        @empty
        @include('components.admin.empty-state', [
        'icon' => 'bi-handshake',
        'title' => 'Nenhum parceiro encontrado',
        'description' => 'Ainda não existem parceiros registados ou não há resultados para os filtros aplicados.',
        'actionUrl' => route('admin.v2.partners.create'),
        'actionText' => 'Adicionar Primeiro Parceiro'
        ])
        @endforelse
    </div>

    <!-- PAGINAÇÃO -->
    @if($partners->hasPages())
    <div class="pagination-wrapper">
        {{ $partners->links() }}
    </div>
    @endif
@endsection