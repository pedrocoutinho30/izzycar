@extends('layouts.admin-v2')

@section('title', 'Orçamentos de Transporte')

@section('content')

<!-- PAGE HEADER -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-truck', 'label' => 'Transportes', 'href' => ''],
],
'title' => 'Orçamentos de Transporte',
'subtitle' => 'Gestão de orçamentos de transporte de veículos',
'actionHref' => route('admin.transport-quotes.create'),
'actionLabel' => 'Novo Orçamento'
])

<!-- FILTROS -->
<div class="modern-card mb-4">
    <div class="modern-card-body">
        <form method="GET" action="{{ route('admin.transport-quotes.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Data Início</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Fim</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Transportadora</label>
                <select name="supplier_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->company_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- AÇÕES -->
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('admin.transport-quotes.map') }}" class="btn btn-outline-primary">
        <i class="bi bi-map"></i> Ver Mapa
    </a>
</div>

<!-- LISTA DE ORÇAMENTOS -->
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Orçamentos
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $quotes->total() }} total</span>
    </div>

    @forelse($quotes as $quote)
    <div class="modern-item-card mb-3">
        <div class="modern-item-card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="modern-item-title mb-1">
                        {{ $quote->brand }} {{ $quote->model }}
                    </h6>
                    <p class="modern-item-subtitle mb-2">
                        {{ $quote->supplier->company_name }}
                    </p>
                    <div class="modern-item-badges mb-2">
                        <span class="badge bg-success">
                            <i class="bi bi-currency-euro"></i> {{ number_format($quote->price, 2) }} €
                        </span>
                        <span class="badge bg-info">
                            <i class="bi bi-geo-alt"></i> {{ $quote->origin_city }}, {{ $quote->origin_country }}
                        </span>
                        <span class="badge bg-secondary">
                            <i class="bi bi-calendar"></i> {{ $quote->quote_date->format('d/m/Y') }}
                        </span>
                    </div>
                    <div class="modern-item-metas">
                        @if($quote->estimated_delivery_days)
                        <span class="modern-item-meta">
                            <i class="bi bi-clock"></i> {{ $quote->estimated_delivery_days }} dias
                        </span>
                        @endif
                        @if($quote->observations)
                        <span class="modern-item-meta">
                            <i class="bi bi-chat-left-text"></i> Com observações
                        </span>
                        @endif
                    </div>
                </div>
                <div class="modern-item-actions">
                    <a href="{{ route('admin.transport-quotes.edit', $quote->id) }}" 
                       class="btn btn-sm btn-outline-primary" 
                       title="Editar">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('admin.transport-quotes.destroy', $quote->id) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="return confirm('Tem certeza que deseja remover este orçamento?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <p class="text-muted mt-3">Nenhum orçamento encontrado.</p>
        <a href="{{ route('admin.transport-quotes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Criar Primeiro Orçamento
        </a>
    </div>
    @endforelse

    @if($quotes->hasPages())
    <div class="modern-card-footer">
        {{ $quotes->links() }}
    </div>
    @endif
</div>

@endsection