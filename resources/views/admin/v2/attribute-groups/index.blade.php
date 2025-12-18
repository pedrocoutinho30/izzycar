@extends('layouts.admin-v2')

@section('title', 'Grupos de Atributos')

@section('content')

<!-- Page Header -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Grupos de Atributos']
    ],
    'title' => 'Grupos de Atributos',
    'subtitle' => 'Gerir grupos de atributos para organização',
    'actionHref' => route('admin.v2.attribute-groups.create'),
    'actionLabel' => 'Novo Grupo'
])

<!-- Stats Cards -->
@include('components.admin.stats-cards', ['stats' => $stats])

<!-- Filter Bar -->
@include('components.admin.filter-bar', [
    'filters' => [
        [
            'type' => 'text',
            'name' => 'search',
            'label' => 'Pesquisar',
            'placeholder' => 'Pesquisar por nome...',
            'value' => request('search')
        ]
    ]
])

<!-- Lista de Grupos -->
<div class="content-grid">
    @forelse($groups as $group)
        @include('components.admin.item-card', [
            'title' => $group->name,
            'subtitle' => 'Ordem: ' . $group->order,
            'image' => null,
            'badges' => [
                [
                    'text' => $group->attributes_count . ' atributos',
                    'color' => 'info',
                    'icon' => 'bi-tags'
                ]
            ],
            'meta' => [
                [
                    'icon' => 'bi-hash',
                    'text' => 'Ordem ' . $group->order
                ],
                [
                    'icon' => 'bi-calendar',
                    'text' => $group->created_at->format('d/m/Y')
                ]
            ],
            'actions' => [
                [
                    'href' => route('admin.v2.attribute-groups.edit', $group->id),
                    'icon' => 'bi-pencil',
                    'label' => 'Editar',
                    'color' => 'primary'
                ],
                [
                    'href' => route('admin.v2.attribute-groups.destroy', $group->id),
                    'icon' => 'bi-trash',
                    'label' => 'Eliminar',
                    'color' => 'danger',
                    'method' => 'delete',
                    'confirm' => 'Tem certeza que deseja eliminar este grupo?'
                ]
            ]
        ])
    @empty
        @include('components.admin.empty-state', [
            'icon' => 'bi-folder',
            'title' => 'Nenhum grupo encontrado',
            'description' => 'Crie o primeiro grupo de atributos.',
            'actionUrl' => route('admin.v2.attribute-groups.create'),
            'actionText' => 'Criar Grupo'
        ])
    @endforelse
</div>

<!-- Paginação -->
@if($groups->hasPages())
<div class="pagination-wrapper">
    {{ $groups->links() }}
</div>
@endif

@endsection
