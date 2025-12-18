@extends('layouts.admin-v2')

@section('title', 'Utilizadores')

@section('content')

<!-- Page Header -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Utilizadores']
    ],
    'title' => 'Utilizadores',
    'subtitle' => 'Gerir utilizadores do sistema',
    'actionHref' => route('admin.v2.users.create'),
    'actionLabel' => 'Novo Utilizador'
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
            'placeholder' => 'Pesquisar por nome ou email...',
            'value' => request('search')
        ],
        [
            'type' => 'select',
            'name' => 'role',
            'label' => 'Perfil',
            'placeholder' => 'Todos os perfis',
            'value' => request('role'),
            'options' => $roles->pluck('name', 'name')->toArray()
        ]
    ]
])

<!-- Lista de Utilizadores -->
<div class="content-grid">
    @forelse($users as $user)
        @include('components.admin.item-card', [
            'title' => $user->name . ' ' . $user->last_name,
            'subtitle' => $user->email,
            'image' => 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6e0707&color=fff&bold=true',
            'badges' => [
                [
                    'text' => $user->roles->first()?->name ?? 'Sem perfil',
                    'color' => 'primary'
                ]
            ],
            'meta' => [
                [
                    'icon' => 'bi-envelope',
                    'text' => $user->email
                ],
                [
                    'icon' => 'bi-calendar',
                    'text' => 'Criado em ' . $user->created_at->format('d/m/Y')
                ]
            ],
            'actions' => [
                [
                    'href' => route('admin.v2.users.edit', $user->id),
                    'icon' => 'bi-pencil',
                    'label' => 'Editar',
                    'color' => 'primary'
                ],
                [
                    'href' => route('admin.v2.users.destroy', $user->id),
                    'icon' => 'bi-trash',
                    'label' => 'Eliminar',
                    'color' => 'danger',
                    'method' => 'delete',
                    'confirm' => 'Tem certeza que deseja eliminar este utilizador?'
                ]
            ]
        ])
    @empty
        @include('components.admin.empty-state', [
            'icon' => 'bi-people',
            'title' => 'Nenhum utilizador encontrado',
            'description' => 'Crie o primeiro utilizador do sistema.',
            'actionUrl' => route('admin.v2.users.create'),
            'actionText' => 'Criar Utilizador'
        ])
    @endforelse
</div>

<!-- Paginação -->
@if($users->hasPages())
<div class="pagination-wrapper">
    {{ $users->links() }}
</div>
@endif

@endsection
