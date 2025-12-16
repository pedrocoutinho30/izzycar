{{--
    ==================================================================
    DASHBOARD V2
    ==================================================================
    
    Dashboard principal do backoffice com:
    - Stats cards
    - Alertas importantes
    - Atividade recente
    - Quick actions
    
    @extends layouts.admin-v2
    ==================================================================
--}}

@extends('layouts.admin-v2')

@section('title', 'Dashboard')

@section('content')

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Visão geral do seu negócio</p>
    </div>
</div>

<!-- Stats Cards -->
@include('components.admin.stats-cards', ['stats' => $stats])

<!-- Alertas Importantes -->
@if(count($alerts) > 0)
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-bell"></i>
                    Alertas e Notificações
                </h5>
            </div>

            @foreach($alerts as $alert)
            <div class="alert alert-{{ $alert['type'] }} d-flex align-items-start mb-3">
                <i class="bi {{ $alert['icon'] }} me-3" style="font-size: 1.5rem;"></i>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-1">{{ $alert['title'] }}</h6>
                    <p class="mb-2">{{ $alert['message'] }}</p>
                    @if(isset($alert['action']))
                    <a href="{{ $alert['action']['href'] }}" class="btn btn-sm btn-{{ $alert['type'] }}">
                        {{ $alert['action']['text'] }}
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Atividade Recente -->
<div class="row g-4">
    <!-- Últimas Propostas -->
    <div class="col-lg-6">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-file-earmark-text"></i>
                    Últimas Propostas
                </h5>
                <a href="{{ route('admin.v2.proposals.index') }}" class="btn btn-sm btn-secondary-modern">
                    Ver Todas
                </a>
            </div>

            @if($recentProposals->count() > 0)
                <div class="activity-list">
                    @foreach($recentProposals as $proposal)
                    <div class="activity-item">
                        <div class="activity-icon bg-primary">
                            <i class="bi bi-car-front"></i>
                        </div>
                        <div class="activity-content">
                            <h6 class="activity-title">
                                {{ $proposal->brand }} {{ $proposal->model }}
                            </h6>
                            <p class="activity-meta">
                                {{ $proposal->client ? $proposal->client->name : 'Sem cliente' }} • 
                                {{ $proposal->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="activity-status">
                            <span class="badge bg-{{ $proposal->status == 'Pendente' ? 'warning' : 'success' }}">
                                {{ $proposal->status }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted text-center py-4">Nenhuma proposta recente</p>
            @endif
        </div>
    </div>

    <!-- Últimos Formulários -->
    <div class="col-lg-6">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-envelope"></i>
                    Últimos Formulários
                </h5>
                <a href="{{ route('admin.v2.form-proposals.index') }}" class="btn btn-sm btn-secondary-modern">
                    Ver Todos
                </a>
            </div>

            @if($recentFormProposals->count() > 0)
                <div class="activity-list">
                    @foreach($recentFormProposals as $form)
                    <div class="activity-item">
                        <div class="activity-icon bg-warning">
                            <i class="bi bi-envelope-open"></i>
                        </div>
                        <div class="activity-content">
                            <h6 class="activity-title">{{ $form->name }}</h6>
                            <p class="activity-meta">
                                {{ $form->brand ?? 'Sem marca' }} {{ $form->model ?? '' }} • 
                                {{ $form->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="activity-status">
                            @if($form->proposal_id)
                                <span class="badge bg-success">Convertido</span>
                            @else
                                <span class="badge bg-warning">Novo</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted text-center py-4">Nenhum formulário recente</p>
            @endif
        </div>
    </div>
</div>

<!-- Últimas Vendas -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-cash-coin"></i>
                    Últimas Vendas
                </h5>
                <a href="{{ route('admin.v2.sales.index') }}" class="btn btn-sm btn-secondary-modern">
                    Ver Todas
                </a>
            </div>

            @if($recentSales->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Veículo</th>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSales as $sale)
                            <tr>
                                <td>
                                    <strong>{{ $sale->vehicle ? $sale->vehicle->brand . ' ' . $sale->vehicle->model : 'N/A' }}</strong>
                                </td>
                                <td>{{ $sale->client ? $sale->client->name : 'N/A' }}</td>
                                <td>{{ $sale->created_at->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <strong class="text-success">{{ number_format($sale->sell_price ?? 0, 0, ',', '.') }}€</strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">Nenhuma venda recente</p>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Activity List */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 8px;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        background: #e9ecef;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }

    .activity-content {
        flex: 1;
    }

    .activity-title {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: var(--admin-secondary);
    }

    .activity-meta {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 0;
    }

    .activity-status {
        margin-left: auto;
    }

    /* Custom alert styles */
    .alert {
        border-left: 4px solid;
    }

    .alert-warning {
        border-left-color: #ffc107;
    }

    .alert-info {
        border-left-color: #17a2b8;
    }

    .alert-success {
        border-left-color: #28a745;
    }
</style>
@endpush
