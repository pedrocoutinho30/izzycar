@extends('layouts.admin-v2')

@section('title', 'Configurações')

@section('content')

<!-- Page Header -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Configurações']
    ],
    'title' => 'Configurações',
    'subtitle' => 'Gerir configurações do sistema',
    'actionHref' => route('admin.v2.settings.create'),
    'actionLabel' => 'Nova Configuração'
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
            'placeholder' => 'Pesquisar configurações...',
            'value' => request('search')
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
                'image' => 'Imagem'
            ]
        ]
    ]
])

<!-- Lista de Configurações -->
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Configurações
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $settings->total() }} total</span>
    </div>
    @forelse($settings as $setting)
        @php
            $typeColors = [
                'text' => 'info',
                'number' => 'success',
                'boolean' => 'warning',
                'image' => 'primary'
            ];
        @endphp
        @include('components.admin.item-card', [
            'title' => $setting->title,
            'subtitle' => 'Label: ' . $setting->label,
            'image' => $setting->type === 'image' && $setting->value ? asset('storage/' . $setting->value) : null,
            'badges' => [
                [
                    'text' => ucfirst($setting->type),
                    'color' => $typeColors[$setting->type] ?? 'secondary'
                ]
            ],
            'meta' => [
                [
                    'icon' => 'bi-tag',
                    'text' => $setting->label
                ],
                [
                    'icon' => 'bi-calendar',
                    'text' => $setting->updated_at->format('d/m/Y')
                ]
            ],
            'actions' => [
                [
                    'href' => route('admin.v2.settings.edit', $setting->id),
                    'icon' => 'bi-pencil',
                    'label' => 'Editar',
                    'color' => 'primary'
                ],
                [
                    'href' => route('admin.v2.settings.destroy', $setting->id),
                    'icon' => 'bi-trash',
                    'label' => 'Eliminar',
                    'color' => 'danger',
                    'method' => 'delete',
                    'confirm' => 'Tem certeza que deseja eliminar esta configuração?'
                ]
            ]
        ])
    @empty
        @include('components.admin.empty-state', [
            'icon' => 'bi-gear',
            'title' => 'Nenhuma configuração encontrada',
            'description' => 'Crie a primeira configuração do sistema.',
            'actionUrl' => route('admin.v2.settings.create'),
            'actionText' => 'Criar Configuração'
        ])
    @endforelse
</div>

<!-- Paginação -->
@if($settings->hasPages())
<div class="pagination-wrapper">
    {{ $settings->links() }}
</div>
@endif

@endsection
