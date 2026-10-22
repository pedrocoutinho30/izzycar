@extends('layouts.admin-v2')

@php
    $veiculo = trim(implode(' ', array_filter([$converted->brand, $converted->modelCar, $converted->version])));
    $estado = $converted->status ?: 'Iniciada';
    $cancelada = $converted->isCancelled();
    $indiceAtual = array_search($estado, $etapas, true);
    $indiceAtual = $indiceAtual === false ? 0 : $indiceAtual;
    $valorFinal = $converted->proposal?->totalCost();
@endphp

@section('title', 'Cotação convertida — ' . $veiculo)

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Início', 'href' => route('admin.angariador.dashboard')],
        ['icon' => '', 'label' => 'Cotações convertidas', 'href' => route('admin.angariador.convertidas')],
        ['icon' => '', 'label' => $veiculo ?: 'Cotação convertida'],
    ],
    'title' => $veiculo ?: 'Cotação convertida',
    'titleBrandLogo' => $converted->brand,
    'subtitle' => 'Cliente: ' . ($converted->client->name ?? '—') . ' · convertida em ' . $converted->created_at->format('d/m/Y'),
])

<div class="row g-4">
    <div class="col-lg-8">
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-signpost-split"></i> Estado do processo</h5>
                <span class="badge bg-{{ $cancelada ? 'danger' : ($estado === 'Concluido' ? 'success' : 'info') }} rounded-pill">{{ $estado }}</span>
            </div>
            <div class="p-3">
                @if($cancelada)
                <div class="alert alert-danger mb-3"><i class="bi bi-x-circle me-1"></i> Esta cotação convertida foi cancelada.</div>
                @endif
                <ul class="list-unstyled mb-0">
                    @foreach($etapas as $i => $etapa)
                    @php
                        $feita = ! $cancelada && $i <= $indiceAtual;
                        $atual = ! $cancelada && $i === $indiceAtual;
                    @endphp
                    <li class="d-flex align-items-center gap-2 py-2 border-bottom">
                        <i class="bi {{ $feita ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}"></i>
                        <span class="{{ $atual ? 'fw-bold' : '' }} {{ $feita ? '' : 'text-muted' }}">{{ $etapa }}</span>
                        @if($feita && isset($datas[$etapa]))
                        <small class="text-muted ms-auto">{{ $datas[$etapa]->format('d/m/Y') }}</small>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-cash-coin"></i> Valor final</h5>
            </div>
            <div class="p-3">
                @if($valorFinal)
                <div class="fs-3 fw-bold">{{ number_format($valorFinal, 0, ',', '.') }} €</div>
                <small class="text-muted">Total da cotação aceite pelo cliente.</small>
                @else
                <span class="text-muted">Sem valor associado.</span>
                @endif
            </div>
        </div>

        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-link-45deg"></i> O que o cliente vê</h5>
            </div>
            <div class="p-3 d-grid gap-2">
                <a href="{{ route('converted-proposals.timeline', ['brand' => $converted->brand ?? 'veiculo', 'model' => $converted->modelCar ?? 'modelo', 'version' => $converted->version ?? 'versao', 'id' => $converted->id]) }}" target="_blank" class="btn btn-primary-modern">
                    <i class="bi bi-clock-history me-1"></i> Acompanhamento do cliente
                </a>
                @if($converted->proposal?->proposal_code)
                <a href="{{ route('proposals.detail', $converted->proposal->proposal_code) }}" target="_blank" class="btn btn-secondary-modern">
                    <i class="bi bi-file-earmark-text me-1"></i> Ver a cotação
                </a>
                @endif
                <a href="{{ route('admin.angariador.leads.show', $converted->client_id) }}" class="btn btn-secondary-modern">
                    <i class="bi bi-person me-1"></i> Ver a lead
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
