@extends('layouts.admin-v2')

@section('title', 'Despesas')

@section('content')
<div class="admin-content">
    <!-- Page Header -->
    @include('components.admin.page-header', [
    'breadcrumbs' => [
    ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => ''],
    ['icon' => 'bi bi-receipt', 'label' => 'Despesas', 'href' => ''],
    ],
    'title' => 'Despesas',
    'subtitle' => 'Gestão de despesas operacionais',
    'actionHref' => route('admin.v2.expenses.create'),
    'actionLabel' => 'Nova Despesa'
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
    'placeholder' => 'Pesquisar por título ou observações...',
    'value' => request('search')
    ],
    [
    'type' => 'select',
    'name' => 'type',
    'label' => 'Tipo',
    'placeholder' => 'Tipo de Despesa',
    'value' => request('type'),
    'options' => [
    '' => 'Todos os Tipos',
    'Documentação' => 'Documentação',
    'IMT' => 'IMT',
    'Inspeção Técnica' => 'Inspeção Técnica',
    'IPO' => 'IPO',
    'ISV' => 'ISV',
    'IUC' => 'IUC',
    'Manutenção' => 'Manutenção',
    'Matrículas' => 'Matrículas',
    'Peças' => 'Peças',
    'Reboque' => 'Reboque',
    'Registo automóvel' => 'Registo automóvel',
    'Reparação' => 'Reparação',
    'Seguro' => 'Seguro',
    'Transporte' => 'Transporte',
    'Outros' => 'Outros',
    ]
    ],
    [
    'type' => 'select',
    'name' => 'vehicle_id',
    'label' => 'Veículo',
    'placeholder' => 'Veículo',
    'value' => request('vehicle_id'),
    'options' => ['' => 'Todos os Veículos'] + $vehicles->mapWithKeys(fn($v) => [$v->id => $v->reference . ' - ' . $v->brand . ' ' . $v->model])->toArray()
    ]
    ]
    ])

    <!-- LISTA DE DESPESAS -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title">
                <i class="bi bi-list-ul"></i>
                Lista de Despesas
            </h5>
            <span class="badge bg-secondary rounded-pill">{{ $expenses->total() }} total</span>
        </div>
        @forelse($expenses as $expense)
        @php
        $vehicle = $expense->vehicle;
        $vehicleInfo = $vehicle ? $vehicle->brand . ' ' . $vehicle->model . ' (' . $vehicle->reference . ')' : 'Sem veículo associado';
        @endphp

        @include('components.admin.item-card', [
        'image' => 'https://ui-avatars.com/api/?name=' . urlencode($expense->type ?? 'Despesa') . '&background=6e0707&color=fff&bold=true',
        'title' => $expense->title,
        'subtitle' => $vehicleInfo,
        'badges' => array_filter([
        [
        'text' => number_format($expense->amount, 2, ',', '.') . '€',
        'color' => 'danger'
        ],
        $expense->type ? [
        'text' => $expense->type,
        'color' => 'info'
        ] : null,
        $expense->partner ? [
        'text' => $expense->partner->name,
        'color' => 'secondary'
        ] : null
        ]),
        'meta' => array_filter([
        [
        'icon' => 'bi-calendar',
        'text' => \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y')
        ],
        $expense->vat_rate ? [
        'icon' => 'bi-percent',
        'text' => 'IVA: ' . $expense->vat_rate . '%'
        ] : null,
        $vehicle ? [
        'icon' => 'bi-car-front',
        'text' => $vehicle->reference
        ] : null
        ]),
        'actions' => [
        [
        'href' => route('admin.v2.expenses.edit', $expense->id),
        'icon' => 'bi-pencil',
        'label' => 'Editar',
        'color' => 'primary'
        ],
        [
        'href' => route('admin.v2.expenses.destroy', $expense->id),
        'icon' => 'bi-trash',
        'label' => 'Eliminar',
        'color' => 'danger',
        'method' => 'delete',
        'confirm' => 'Tem a certeza que pretende eliminar esta despesa?'
        ]
        ]
        ])
        @empty
        @include('components.admin.empty-state', [
        'icon' => 'bi-receipt',
        'title' => 'Nenhuma despesa encontrada',
        'description' => 'Ainda não existem despesas registadas ou não há resultados para os filtros aplicados.',
        'actionUrl' => route('admin.v2.expenses.create'),
        'actionText' => 'Registar Primeira Despesa'
        ])
        @endforelse
    </div>

    <!-- PAGINAÇÃO -->
    @if($expenses->hasPages())
    <div class="pagination-wrapper">
        {{ $expenses->links() }}
    </div>
    @endif
    @endsection