@extends('layouts.admin-v2')

@section('title', 'As Minhas Comissões')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Início', 'href' => route('admin.angariador.dashboard')],
        ['icon' => '', 'label' => 'Comissões'],
    ],
    'title' => 'As Minhas Comissões',
    'subtitle' => 'Comissões associadas às cotações convertidas em que é o angariador',
])

@include('components.admin.stats-cards', [
    'stats' => [
        ['title' => 'Recebido', 'value' => number_format($comissaoRecebida, 2, ',', '.') . ' €', 'icon' => 'bi-check-circle', 'color' => 'success'],
        ['title' => 'Pendente', 'value' => number_format($comissaoPendente, 2, ',', '.') . ' €', 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
    ]
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-cash-coin"></i> Comissões</h5>
        <span class="badge bg-secondary rounded-pill">{{ $convertedProposals->count() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Veículo</th>
                    <th>Valor Comissão</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($convertedProposals as $proposal)
                @php $amount = $proposal->angariadorCommissionAmount(); @endphp
                <tr>
                    <td>{{ $proposal->client->name ?? '—' }}</td>
                    <td>{{ trim(($proposal->brand ?? '') . ' ' . ($proposal->modelCar ?? '')) }}</td>
                    <td>{{ $amount !== null ? number_format($amount, 2, ',', '.') . ' €' : '—' }}</td>
                    <td>
                        @if($proposal->comissao_paga)
                            <span class="badge bg-success">Pago em {{ optional($proposal->comissao_paga_em)->format('d/m/Y') }}</span>
                            @if($proposal->comprovativo_pagamento)
                            <a href="{{ asset('storage/' . $proposal->comprovativo_pagamento) }}" target="_blank" class="ms-1" title="Ver comprovativo de transferência">
                                <i class="bi bi-paperclip"></i>
                            </a>
                            @endif
                        @elseif($proposal->status === 'Entrega')
                            <span class="badge bg-warning">Pendente</span>
                        @else
                            <span class="badge bg-secondary">Aguarda entrega</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted p-4">Ainda sem comissões registadas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
