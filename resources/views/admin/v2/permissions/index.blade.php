@extends('layouts.admin-v2')

@section('title', 'Permissões')

@section('content')

<!-- Page Header -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Permissões']
    ],
    'title' => 'Permissões',
    'subtitle' => 'Gerir permissões do sistema',
    'actionHref' => route('admin.v2.permissions.create'),
    'actionLabel' => 'Nova Permissão'
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

<!-- Lista de Permissões -->
<div class="content-grid">
    @forelse($permissions as $permission)
        @include('components.admin.item-card', [
            'title' => $permission->name,
            'subtitle' => 'Permissão do sistema',
            'image' => null,
            'badges' => [
                [
                    'text' => 'Permissão',
                    'color' => 'primary',
                    'icon' => 'bi-key'
                ]
            ],
            'meta' => [
                [
                    'icon' => 'bi-calendar',
                    'text' => 'Criada em ' . $permission->created_at->format('d/m/Y')
                ]
            ],
            'actions' => [
                [
                    'href' => route('admin.v2.permissions.edit', $permission->id),
                    'icon' => 'bi-pencil',
                    'label' => 'Editar',
                    'color' => 'primary'
                ],
                [
                    'href' => route('admin.v2.permissions.destroy', $permission->id),
                    'icon' => 'bi-trash',
                    'label' => 'Eliminar',
                    'color' => 'danger',
                    'method' => 'delete',
                    'confirm' => 'Tem certeza que deseja eliminar esta permissão?'
                ]
            ]
        ])
    @empty
        @include('components.admin.empty-state', [
            'icon' => 'bi-key',
            'title' => 'Nenhuma permissão encontrada',
            'description' => 'Crie a primeira permissão do sistema.',
            'actionUrl' => route('admin.v2.permissions.create'),
            'actionText' => 'Criar Permissão'
        ])
    @endforelse
</div>

<!-- Paginação -->
@if($permissions->hasPages())
<div class="pagination-wrapper">
    {{ $permissions->links() }}
</div>
@endif

@endsection
