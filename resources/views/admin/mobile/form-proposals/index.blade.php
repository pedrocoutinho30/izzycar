@extends('layouts.admin-mobile')

@section('title', 'Formulários')

@section('content')
    @php
        $estadoAtual = request('status');
        $estadoCores = ['novo' => 'warning', 'em_analise' => 'info', 'convertido' => 'success', 'rejeitado' => 'danger', 'arquivado' => 'secondary'];
        // Os chips mantêm a pesquisa e trocam só o estado.
        $chipUrl = fn (?string $estado) => route('admin.mobile.form-proposals.index', array_filter([
            'search' => request('search'),
            'status' => $estado,
        ], 'filled'));
    @endphp

    <div class="m-sticky-filters">
        <form method="GET" action="{{ route('admin.mobile.form-proposals.index') }}" role="search">
            @if($estadoAtual)
                <input type="hidden" name="status" value="{{ $estadoAtual }}">
            @endif
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="Nome, email, telefone ou marca" aria-label="Pesquisar pedidos" enterkeyhint="search">
            </div>
        </form>

        <div class="m-chips" role="group" aria-label="Filtrar por estado">
            <a href="{{ $chipUrl(null) }}" class="m-chip {{ blank($estadoAtual) ? 'active' : '' }}">Todos</a>
            @foreach($statuses as $valor => $label)
                <a href="{{ $chipUrl($valor) }}" class="m-chip {{ $estadoAtual === $valor ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div id="m-form-proposals-list">
        @forelse($formProposals as $pedido)
            <div class="m-card m-lead-card">
                <a href="{{ route('admin.mobile.form-proposals.show', $pedido->id) }}" class="m-lead-card__link">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <strong class="m-lead-card__name">{{ $pedido->label }}</strong>
                        <span class="badge bg-{{ $estadoCores[$pedido->status ?? 'novo'] ?? 'secondary' }} flex-shrink-0">{{ $statuses[$pedido->status ?? 'novo'] ?? $pedido->status }}</span>
                    </div>
                    <div class="small mt-1">{{ $pedido->client?->name ?? $pedido->name }}</div>
                    <div class="text-muted small">
                        {{ $pedido->isManual() ? 'Manual' : 'Site' }} · {{ $pedido->created_at->format('d/m/Y') }}
                        · {{ $pedido->opportunities_count }} {{ $pedido->opportunities_count === 1 ? 'oportunidade' : 'oportunidades' }}
                    </div>
                </a>
            </div>
        @empty
            <div class="m-card text-center text-muted py-4">
                <i class="bi bi-envelope-open d-block fs-2 mb-1"></i>
                Nenhum pedido encontrado.
            </div>
        @endforelse
    </div>

    @if($formProposals->nextPageUrl())
        <a href="{{ $formProposals->nextPageUrl() }}" class="btn btn-outline-secondary w-100">Carregar mais</a>
    @endif
@endsection
