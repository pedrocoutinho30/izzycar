@extends('layouts.admin-v2')

@section('title', 'Comissões de Angariadores')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Angariadores', 'href' => route('admin.v2.angariadores.index')],
        ['icon' => '', 'label' => 'Comissões'],
    ],
    'title' => 'Comissões de Angariadores',
    'subtitle' => 'Comissões devidas e pagas por cotação convertida — pagamento devido até 24h após o estado avançar para "Entrega"',
])

@include('components.admin.stats-cards', [
    'stats' => [
        ['title' => 'Total Pago', 'value' => number_format($comissaoRecebida, 2, ',', '.') . ' €', 'icon' => 'bi-check-circle', 'color' => 'success'],
        ['title' => 'Total Pendente', 'value' => number_format($comissaoPendente, 2, ',', '.') . ' €', 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
    ]
])

@include('components.admin.filter-bar', [
    'filters' => [
        ['type' => 'date', 'name' => 'date_from', 'label' => 'De', 'value' => request('date_from')],
        ['type' => 'date', 'name' => 'date_to', 'label' => 'Até', 'value' => request('date_to')],
        ['type' => 'select', 'name' => 'owner_id', 'label' => 'Angariador', 'value' => request('owner_id'),
            'options' => ['' => 'Todos'] + $angariadores->pluck('name', 'id')->toArray()],
        ['type' => 'select', 'name' => 'status', 'label' => 'Estado', 'value' => request('status'),
            'options' => ['' => 'Todos', 'pago' => 'Pago', 'pendente' => 'Pendente']],
    ],
    'action' => route('admin.v2.angariadores.comissoes'),
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
                    <th>Angariador</th>
                    <th>Cliente</th>
                    <th>Veículo</th>
                    <th>Comissão</th>
                    <th>Estado da Cotação</th>
                    <th>Pagamento</th>
                    <th>Comprovativo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($convertedProposals as $proposal)
                @php
                    $amount = $proposal->angariadorCommissionAmount();
                    $overdue = $proposal->isCommissionOverdue();
                    $statusColors = ['Cancelada' => 'danger', 'Concluido' => 'success', 'Entrega' => 'info'];
                    $statusColor = $statusColors[$proposal->status] ?? 'secondary';
                @endphp
                <tr class="{{ $overdue ? 'table-danger' : '' }}">
                    <td>{{ $proposal->owner->name ?? '—' }}</td>
                    <td>{{ $proposal->client->name ?? '—' }}</td>
                    <td>{{ trim(($proposal->brand ?? '') . ' ' . ($proposal->modelCar ?? '')) }}</td>
                    <td>{{ $amount !== null ? number_format($amount, 2, ',', '.') . ' €' : '—' }}</td>
                    <td>
                        <a href="{{ route('admin.v2.converted-proposals.edit', $proposal->id) }}" class="badge bg-{{ $statusColor }} text-decoration-none">
                            {{ $proposal->status }}
                        </a>
                    </td>
                    <td>
                        @if($proposal->comissao_paga)
                            <span class="badge bg-success">Pago em {{ optional($proposal->comissao_paga_em)->format('d/m/Y') }}</span>
                        @elseif($overdue)
                            <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Em atraso (&gt;24h)</span>
                        @elseif($proposal->status === 'Entrega')
                            <span class="badge bg-warning">Pendente</span>
                        @else
                            <span class="badge bg-secondary">Aguarda entrega</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <form action="{{ route('admin.v2.angariadores.upload-receipt', $proposal->id) }}" method="POST" enctype="multipart/form-data" class="mb-0">
                                @csrf
                                <label class="btn btn-sm btn-outline-secondary mb-0" title="{{ $proposal->comprovativo_pagamento ? 'Substituir comprovativo' : 'Anexar comprovativo' }}">
                                    <i class="bi bi-upload"></i>
                                    <input type="file" name="comprovativo" accept=".jpg,.jpeg,.png,.pdf" class="d-none" onchange="this.form.submit()">
                                </label>
                            </form>
                            @if($proposal->comprovativo_pagamento)
                            <a href="{{ asset('storage/' . $proposal->comprovativo_pagamento) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver comprovativo">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                    <td class="text-end">
                        <form action="{{ route('admin.v2.angariadores.toggle-paid', $proposal->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $proposal->comissao_paga ? 'btn-outline-secondary' : 'btn-success' }}">
                                {{ $proposal->comissao_paga ? 'Marcar pendente' : 'Marcar pago' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted p-4">Sem comissões para os filtros selecionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
