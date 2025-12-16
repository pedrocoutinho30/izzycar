@extends('layouts.admin-v2')

@section('title', 'Fornecedores')

@section('content')
<div class="admin-content">
    <!-- HEADER -->
    <div class="content-header">
        <div>
            <h1 class="content-title">Fornecedores</h1>
            <p class="content-subtitle">Gestão de fornecedores de veículos</p>
        </div>
        <div class="content-actions">
            <a href="{{ route('admin.v2.suppliers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Novo Fornecedor
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
                'placeholder' => 'Pesquisar por nome, contacto ou NIF...',
                'value' => request('search')
            ],
            [
                'type' => 'select',
                'name' => 'type',
                'label' => 'Tipo',
                'placeholder' => 'Tipo',
                'value' => request('type'),
                'options' => [
                    '' => 'Todos os Tipos',
                    'Stand' => 'Stand',
                    'Particular' => 'Particular',
                    'Leilão' => 'Leilão',
                    'Outro' => 'Outro'
                ]
            ]
        ]
    ])

    <!-- LISTA DE FORNECEDORES -->
    <div class="content-grid">
        @forelse($suppliers as $supplier)
            @include('components.admin.item-card', [
                'image' => 'https://ui-avatars.com/api/?name=' . urlencode($supplier->company_name) . '&background=6e0707&color=fff&bold=true',
                'title' => $supplier->company_name,
                'subtitle' => $supplier->contact_name ?? 'Sem contacto definido',
                'badges' => array_filter([
                    $supplier->type ? [
                        'text' => $supplier->type,
                        'color' => 'primary'
                    ] : null,
                    $supplier->country ? [
                        'text' => $supplier->country,
                        'color' => $supplier->country === 'Portugal' ? 'success' : 'info'
                    ] : null,
                    $supplier->vat ? [
                        'text' => 'NIF: ' . $supplier->vat,
                        'color' => 'secondary'
                    ] : null
                ]),
                'meta' => array_filter([
                    $supplier->phone ? [
                        'icon' => 'bi-telephone',
                        'text' => $supplier->phone
                    ] : null,
                    $supplier->email ? [
                        'icon' => 'bi-envelope',
                        'text' => $supplier->email
                    ] : null,
                    $supplier->city ? [
                        'icon' => 'bi-geo-alt',
                        'text' => $supplier->city
                    ] : null
                ]),
                'actions' => [
                    [
                        'href' => route('admin.v2.suppliers.edit', $supplier->id),
                        'icon' => 'bi-pencil',
                        'label' => 'Editar',
                        'color' => 'primary'
                    ],
                    [
                        'href' => route('admin.v2.suppliers.destroy', $supplier->id),
                        'icon' => 'bi-trash',
                        'label' => 'Eliminar',
                        'color' => 'danger',
                        'method' => 'delete',
                        'confirm' => 'Tem a certeza que pretende eliminar este fornecedor?'
                    ]
                ]
            ])
        @empty
            @include('components.admin.empty-state', [
                'icon' => 'bi-truck',
                'title' => 'Nenhum fornecedor encontrado',
                'description' => 'Ainda não existem fornecedores registados ou não há resultados para os filtros aplicados.',
                'actionUrl' => route('admin.v2.suppliers.create'),
                'actionText' => 'Adicionar Primeiro Fornecedor'
            ])
        @endforelse
    </div>

    <!-- PAGINAÇÃO -->
    @if($suppliers->hasPages())
        <div class="pagination-wrapper">
            {{ $suppliers->links() }}
        </div>
    @endif
</div>
@endsection
