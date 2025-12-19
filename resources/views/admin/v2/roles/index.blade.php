@extends('layouts.admin-v2')

@section('title', 'Perfis')

@section('content')

<!-- Page Header -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Perfis']
    ],
    'title' => 'Perfis',
    'subtitle' => 'Gerir perfis e permissões',
    'actionHref' => route('admin.v2.roles.create'),
    'actionLabel' => 'Novo Perfil'
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

<!-- Lista de Perfis -->
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Perfis
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $roles->total() }} total</span>
    </div>
    @forelse($roles as $role)
        @include('components.admin.item-card', [
            'title' => $role->name,
            'subtitle' => $role->permissions_count . ' permissões atribuídas',
            'image' => null,
            'badges' => [
                [
                    'text' => $role->users_count . ' utilizadores',
                    'color' => 'info',
                    'icon' => 'bi-people'
                ],
                [
                    'text' => $role->permissions_count . ' permissões',
                    'color' => 'success',
                    'icon' => 'bi-key'
                ]
            ],
            'meta' => [
                [
                    'icon' => 'bi-calendar',
                    'text' => 'Criado em ' . $role->created_at->format('d/m/Y')
                ]
            ],
            'actions' => [
                [
                    'href' => route('admin.v2.roles.edit', $role->id),
                    'icon' => 'bi-pencil',
                    'label' => 'Editar',
                    'color' => 'primary'
                ],
                [
                    'href' => route('admin.v2.roles.destroy', $role->id),
                    'icon' => 'bi-trash',
                    'label' => 'Eliminar',
                    'color' => 'danger',
                    'method' => 'delete',
                    'confirm' => 'Tem certeza que deseja eliminar este perfil?'
                ]
            ]
        ])
    @empty
        @include('components.admin.empty-state', [
            'icon' => 'bi-person-badge',
            'title' => 'Nenhum perfil encontrado',
            'description' => 'Crie o primeiro perfil do sistema.',
            'actionUrl' => route('admin.v2.roles.create'),
            'actionText' => 'Criar Perfil'
        ])
    @endforelse
</div>

<!-- Paginação -->
@if($roles->hasPages())
<div class="pagination-wrapper">
    {{ $roles->links() }}
</div>
@endif

@endsection
