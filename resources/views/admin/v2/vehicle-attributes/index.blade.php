@extends('layouts.admin-v2')

@section('title', 'Atributos de Veículos')

@section('content')

<!-- Page Header -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Atributos de Veículos']
    ],
    'title' => 'Atributos de Veículos',
    'subtitle' => 'Gerir atributos dos veículos',
    'actionHref' => route('admin.v2.vehicle-attributes.create'),
    'actionLabel' => 'Novo Atributo'
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
            'placeholder' => 'Pesquisar por nome ou chave...',
            'value' => request('search')
        ],
        [
            'type' => 'select',
            'name' => 'group',
            'label' => 'Grupo',
            'placeholder' => 'Todos os grupos',
            'value' => request('group'),
            'options' => $groups->pluck('name', 'name')->toArray()
        ],
        [
            'type' => 'select',
            'name' => 'type',
            'label' => 'Tipo',
            'placeholder' => 'Todos os tipos',
            'value' => request('type'),
            'options' => [
                'text' => 'Texto',
                'number' => 'Número',
                'boolean' => 'Boolean',
                'select' => 'Select'
            ]
        ]
    ]
])

<!-- Lista de Atributos -->
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Atributos
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $attributes->total() }} total</span>
    </div>
    @forelse($attributes as $attribute)
        @php
            $typeColors = [
                'text' => 'info',
                'number' => 'success',
                'boolean' => 'warning',
                'select' => 'primary'
            ];
        @endphp
        @include('components.admin.item-card', [
            'title' => $attribute->name,
            'subtitle' => 'Chave: ' . $attribute->key,
            'image' => null,
            'badges' => [
                [
                    'text' => ucfirst($attribute->type),
                    'color' => $typeColors[$attribute->type] ?? 'secondary'
                ],
                $attribute->attribute_group ? [
                    'text' => $attribute->attribute_group,
                    'color' => 'secondary'
                ] : null
            ],
            'meta' => [
                [
                    'icon' => 'bi-hash',
                    'text' => 'Ordem ' . $attribute->order
                ]
            ],
            'actions' => [
                [
                    'href' => route('admin.v2.vehicle-attributes.edit', $attribute->id),
                    'icon' => 'bi-pencil',
                    'label' => 'Editar',
                    'color' => 'primary'
                ],
                [
                    'href' => route('admin.v2.vehicle-attributes.destroy', $attribute->id),
                    'icon' => 'bi-trash',
                    'label' => 'Eliminar',
                    'color' => 'danger',
                    'method' => 'delete',
                    'confirm' => 'Tem certeza que deseja eliminar este atributo?'
                ]
            ]
        ])
    @empty
        @include('components.admin.empty-state', [
            'icon' => 'bi-tags',
            'title' => 'Nenhum atributo encontrado',
            'description' => 'Crie o primeiro atributo de veículo.',
            'actionUrl' => route('admin.v2.vehicle-attributes.create'),
            'actionText' => 'Criar Atributo'
        ])
    @endforelse
</div>

<!-- Paginação -->
@if($attributes->hasPages())
<div class="pagination-wrapper">
    {{ $attributes->links() }}
</div>
@endif

@endsection
