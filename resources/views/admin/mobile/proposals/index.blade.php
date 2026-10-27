@extends('layouts.admin-mobile')

@section('title', 'Cotações')

@section('content')
    @php
        $estadoAtual = request('status');
        $estadoCores = ['Pendente' => 'warning', 'Enviado' => 'info', 'Aprovada' => 'success', 'Reprovada' => 'danger', 'Sem resposta' => 'secondary'];
        // Os chips mantêm a pesquisa e trocam só o estado.
        $chipUrl = fn (?string $estado) => route('admin.mobile.proposals.index', array_filter([
            'search' => request('search'),
            'status' => $estado,
        ], 'filled'));
    @endphp

    <div class="m-sticky-filters">
        <form method="GET" action="{{ route('admin.mobile.proposals.index') }}" role="search">
            @if($estadoAtual)
                <input type="hidden" name="status" value="{{ $estadoAtual }}">
            @endif
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="Marca, modelo, cliente ou código" aria-label="Pesquisar cotações" enterkeyhint="search">
            </div>
        </form>

        <div class="m-chips" role="group" aria-label="Filtrar por estado">
            <a href="{{ $chipUrl(null) }}" class="m-chip {{ blank($estadoAtual) ? 'active' : '' }}">Todas</a>
            @foreach($statuses as $valor)
                <a href="{{ $chipUrl($valor) }}" class="m-chip {{ $estadoAtual === $valor ? 'active' : '' }}">{{ $valor }}</a>
            @endforeach
        </div>
    </div>

    <div id="m-proposals-list">
        @forelse($proposals as $cotacao)
            @php
                $estado = $cotacao->status ?? 'Pendente';
                $expirada = in_array($estado, ['Pendente', 'Enviado'], true) && $cotacao->isExpired();
            @endphp
            <div class="m-card m-lead-card">
                <a href="{{ route('admin.mobile.proposals.show', $cotacao->id) }}" class="m-lead-card__link">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <strong class="m-lead-card__name">
                            <x-admin.brand-logo :brand="$cotacao->brand" :size="22" class="me-1" />
                            {{ trim($cotacao->brand . ' ' . $cotacao->model . ' ' . $cotacao->version) }}
                        </strong>
                        <span class="badge bg-{{ $estadoCores[$estado] ?? 'secondary' }} flex-shrink-0">{{ $estado }}</span>
                    </div>
                    <div class="small mt-1">{{ $cotacao->client?->name ?? 'Sem cliente' }}</div>
                    <div class="text-muted small">
                        {{ $cotacao->created_at->format('d/m/Y') }} · {{ number_format($cotacao->totalCost(), 0, ',', '.') }} €
                        @if($expirada) · <span class="text-danger">Expirada</span> @endif
                    </div>
                </a>
            </div>
        @empty
            <div class="m-card text-center text-muted py-4">
                <i class="bi bi-file-earmark-text d-block fs-2 mb-1"></i>
                Nenhuma cotação encontrada.
            </div>
        @endforelse
    </div>

    @if($proposals->nextPageUrl())
        <a href="{{ $proposals->nextPageUrl() }}" class="btn btn-outline-secondary w-100">Carregar mais</a>
    @endif
@endsection
