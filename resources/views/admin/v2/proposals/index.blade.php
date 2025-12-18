{{--
    ==================================================================
    PROPOSTAS - LISTAGEM V2
    ==================================================================
    
    View moderna de listagem de propostas com:
    - Stats cards no topo
    - Filtros avançados
    - Cards adaptativos mobile-first
    - Paginação
    - Actions rápidas
    
    @extends layouts.admin-v2
    ==================================================================
--}}

@extends('layouts.admin-v2')

@section('title', 'Propostas')

@section('content')

<!-- Page Header -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-file-earmark-text', 'label' => 'Propostas', 'href' => ''],
],
'title' => 'Propostas',
'subtitle' => 'Gerir todas as propostas de importação',
'actionHref' => route('admin.v2.proposals.create'),
'actionLabel' => 'Nova Proposta'
])
<!-- Stats Cards -->
@include('components.admin.stats-cards', ['stats' => $stats])

<!-- Filter Bar -->
@include('components.admin.filter-bar', [
'action' => route('admin.v2.proposals.index'),
'filters' => [
[
'name' => 'search',
'label' => 'Pesquisar',
'type' => 'text',
'placeholder' => 'Marca, modelo ou versão...',
'value' => request('search'),
'col' => 12
],
[
'name' => 'status',
'label' => 'Estado',
'type' => 'select',
'options' => ['Pendente', 'Aprovada', 'Reprovada', 'Enviado', 'Sem resposta'],
'value' => request('status'),
'col' => 4
],
[
'name' => 'client_id',
'label' => 'Cliente',
'type' => 'select',
'options' => $clients->mapWithKeys(function($client) {
return [$client->id => $client->name];
})->toArray(),
'value' => request('client_id'),
'col' => 4
],
[
'name' => 'date_from',
'label' => 'Data Inicial',
'type' => 'date',
'value' => request('date_from'),
'col' => 2
],
[
'name' => 'date_to',
'label' => 'Data Final',
'type' => 'date',
'value' => request('date_to'),
'col' => 2
]
]
])

<!-- Proposals List -->
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Propostas
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $proposals->total() }} total</span>
    </div>

    @if($proposals->count() > 0)
    <div class="proposals-list">
        @foreach($proposals as $proposal)
        @php
        // Definir cor do badge de status
        $statusColors = [
        'Pendente' => 'warning',
        'Aprovada' => 'success',
        'Reprovada' => 'danger',
        'Enviado' => 'info',
        'Sem resposta' => 'secondary'
        ];
        $statusColor = $statusColors[$proposal->status] ?? 'secondary';

        // Pegar imagem se existir
        $proposalImage = $proposal->images ? asset('storage/' . $proposal->images) : null;
        @endphp

        @include('components.admin.item-card', [
        'item' => $proposal,
        'title' => $proposal->brand . ' ' . $proposal->model,
        'subtitle' => $proposal->version,
        'image' => $proposalImage,
        'badges' => [
        ['text' => $proposal->status, 'color' => $statusColor],
        ['text' => $proposal->proposed_car_year_month ?? 'N/A', 'color' => 'secondary', 'icon' => 'calendar'],
        ['text' => $proposal->proposed_car_mileage ?? 'N/A', 'color' => 'secondary', 'icon' => 'speedometer'],
        ['text' => $proposal->fuel ?? 'N/A', 'color' => 'secondary', 'icon' => 'fuel-pump']
        ],
        'meta' => [
        ['icon' => 'person', 'text' => $proposal->client ? $proposal->client->name : 'Sem cliente'],
        ['icon' => 'calendar-event', 'text' => 'Criada em ' . $proposal->created_at->format('d/m/Y')],
        ['icon' => 'currency-euro', 'text' => number_format($proposal->proposed_car_value ?? 0, 0, ',', '.') . '€']
        ],
        'actions' => [
        [
        'icon' => 'bi-eye',
        'href' => route('proposals.detail', ['proposal_code' => $proposal->proposal_code]),
        'color' => 'success',
        'label' => 'Ver Detalhes',
        'target' => '_blank'
        ],
        [
        'icon' => 'bi-pencil',
        'href' => route('admin.v2.proposals.edit', $proposal->id),
        'color' => 'primary',
        'label' => 'Editar'
        ],
        [
        'icon' => 'bi-trash',
        'href' => route('admin.v2.proposals.destroy', $proposal->id),
        'color' => 'danger',
        'label' => 'Eliminar',
        'method' => 'DELETE',
        'confirm' => 'Tem certeza que deseja eliminar esta proposta?'
        ]
        ]
        ])
        @endforeach
    </div>

    <!-- Pagination -->

    @include('components.admin.pagination-footer', [
    'items' => $proposals,
    'label' => 'propostas'
    ])
    @else
    <!-- Estado vazio -->
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
        </div>
        <h4 class="text-muted mb-2">Nenhuma proposta encontrada</h4>
        <p class="text-muted mb-4">
            @if(request()->hasAny(['status', 'client_id', 'search', 'date_from', 'date_to']))
            Não foram encontradas propostas com os filtros aplicados.
            @else
            Comece por criar a sua primeira proposta.
            @endif
        </p>
        @if(request()->hasAny(['status', 'client_id', 'search', 'date_from', 'date_to']))
        <a href="{{ route('admin.v2.proposals.index') }}" class="btn btn-secondary-modern">
            <i class="bi bi-x-circle"></i>
            Limpar Filtros
        </a>
        @else
        <a href="{{ route('admin.v2.proposals.create') }}" class="btn btn-primary-modern">
            <i class="bi bi-plus-lg"></i>
            Criar Primeira Proposta
        </a>
        @endif
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    /**
     * ESTILOS ESPECÍFICOS DA LISTAGEM
     */

    .proposals-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* Animação de entrada dos cards */
    .item-card {
        animation: fadeInUp 0.3s ease forwards;
        opacity: 0;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Stagger animation para múltiplos cards */
    .item-card:nth-child(1) {
        animation-delay: 0.05s;
    }

    .item-card:nth-child(2) {
        animation-delay: 0.1s;
    }

    .item-card:nth-child(3) {
        animation-delay: 0.15s;
    }

    .item-card:nth-child(4) {
        animation-delay: 0.2s;
    }

    .item-card:nth-child(5) {
        animation-delay: 0.25s;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.75rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    /**
     * ==============================================================
     * JAVASCRIPT DA LISTAGEM
     * ==============================================================
     */

    document.addEventListener('DOMContentLoaded', function() {

        /**
         * CONFIRMAÇÃO DE DELETE
         * Já implementado no componente item-card via onsubmit
         */

        /**
         * TOAST DE SUCESSO (se existir mensagem na sessão)
         */
        @if(session('success'))
        showToast('{{ session('
            success ') }}', 'success');
        @endif

        @if(session('error'))
        showToast('{{ session('
            error ') }}', 'error');
        @endif

        /**
         * QUICK STATUS CHANGE (funcionalidade futura)
         * Permitir mudar status diretamente da listagem sem abrir form
         */
        document.querySelectorAll('.quick-status-change').forEach(select => {
            select.addEventListener('change', function() {
                const proposalId = this.dataset.proposalId;
                const newStatus = this.value;

                // TODO: Implementar AJAX request para mudar status
                console.log(`Mudar status da proposta ${proposalId} para ${newStatus}`);
            });
        });
    });
</script>
@endpush