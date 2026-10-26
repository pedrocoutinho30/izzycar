@extends('layouts.admin-v2')

@section('title', 'Angariador — ' . $angariador->name)

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Angariadores', 'href' => route('admin.v2.angariadores.index')],
        ['icon' => '', 'label' => $angariador->name],
    ],
    'title' => $angariador->name . ' ' . $angariador->last_name,
    'subtitle' => 'Detalhe de leads, propostas e comissões — sem impersonation',
    'extraActions' => [
        ['href' => route('admin.v2.angariadores.contract', $angariador->id), 'label' => 'Descarregar contrato', 'icon' => 'bi-file-earmark-pdf', 'class' => 'btn-primary-modern'],
        ['href' => route('admin.v2.users.edit', $angariador->id), 'label' => 'Editar Utilizador', 'icon' => 'bi-pencil'],
    ],
])

@php $emFaltaContrato = \App\Services\AngariadorContractService::camposEmFalta($angariador); @endphp
@if($emFaltaContrato)
<div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
    <i class="bi bi-exclamation-triangle"></i>
    <span>Ao perfil deste angariador faltam {{ implode(', ', $emFaltaContrato) }}: o contrato é gerado com linhas em branco para preencher à mão.</span>
</div>
@endif

@include('components.admin.stats-cards', [
    'stats' => [
        ['title' => 'Leads Geradas', 'value' => $leadsCount, 'icon' => 'bi-funnel', 'color' => 'primary'],
        ['title' => 'Leads Convertidas', 'value' => $convertedCount, 'icon' => 'bi-person-check', 'color' => 'success'],
        ['title' => 'Taxa de Conversão', 'value' => $conversionRate . '%', 'icon' => 'bi-graph-up', 'color' => 'info'],
        ['title' => 'Comissão (paga / pendente)', 'value' => number_format($comissaoRecebida, 2, ',', '.') . ' € / ' . number_format($comissaoPendente, 2, ',', '.') . ' €', 'icon' => 'bi-cash-coin', 'color' => 'warning'],
    ]
])

<div class="modern-card mb-4">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-person-vcard"></i> Documento de identificação</h5>
        @if(\App\Services\AngariadorIdentityDocuments::tem($angariador))
        <span class="badge bg-success rounded-pill">Submetido</span>
        @else
        <span class="badge bg-secondary rounded-pill">Não submetido</span>
        @endif
    </div>
    <div class="p-3">
        @if(\App\Services\AngariadorIdentityDocuments::tem($angariador))
        <div class="d-flex flex-wrap gap-2">
            @foreach(['frente' => 'Ver frente', 'verso' => 'Ver verso'] as $lado => $label)
            @if($angariador->{'documento_' . $lado . '_path'})
            <a href="{{ route('admin.v2.angariadores.identity-document', [$angariador->id, $lado]) }}" target="_blank" class="btn btn-secondary-modern btn-sm">
                <i class="bi bi-eye me-1"></i> {{ $label }}
            </a>
            @endif
            @endforeach
        </div>
        <p class="text-muted small mt-2 mb-0">Dado pessoal sensível: guardado em disco privado e apagado se a candidatura for rejeitada ou a conta apagada.</p>
        @else
        <p class="text-muted mb-0">Este angariador não enviou o documento de identificação.</p>
        @endif
    </div>
</div>

<div class="modern-card mb-4">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-file-earmark-check"></i> Contrato assinado</h5>
        @if($angariador->contrato_assinado_path)
        <span class="badge bg-success rounded-pill">Carregado em {{ $angariador->contrato_assinado_em?->format('d/m/Y') }}</span>
        @else
        <span class="badge bg-secondary rounded-pill">Por carregar</span>
        @endif
    </div>
    <div class="p-3">
        <p class="text-muted small mb-3">
            Depois de o contrato ser assinado pelas duas partes, carregue-o aqui. O angariador passa a vê-lo no seu painel.
        </p>
        @if($errors->has('contrato_assinado'))
        <div class="alert alert-danger py-2">{{ $errors->first('contrato_assinado') }}</div>
        @endif
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form action="{{ route('admin.v2.angariadores.contract.signed.store', $angariador->id) }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center">
                @csrf
                <input type="file" name="contrato_assinado" accept=".pdf,.jpg,.jpeg,.png" class="form-control form-control-sm" style="max-width: 320px;" required>
                <button type="submit" class="btn btn-primary-modern btn-sm">
                    <i class="bi bi-upload me-1"></i> {{ $angariador->contrato_assinado_path ? 'Substituir' : 'Carregar' }}
                </button>
            </form>
            @if($angariador->contrato_assinado_path)
            <a href="{{ route('admin.v2.angariadores.contract.signed', $angariador->id) }}" target="_blank" class="btn btn-secondary-modern btn-sm">
                <i class="bi bi-eye me-1"></i> Ver contrato assinado
            </a>
            <form action="{{ route('admin.v2.angariadores.contract.signed.destroy', $angariador->id) }}" method="POST"
                  onsubmit="return confirm('Remover o contrato assinado? O angariador deixa de o ver.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger-modern btn-sm">
                    <i class="bi bi-trash me-1"></i> Remover
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-funnel"></i> Leads</h5>
        <span class="badge bg-secondary rounded-pill">{{ $leads->count() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Contacto</th>
                    <th>Registada em</th>
                    <th>Propostas</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr>
                    <td>{{ $lead->name }}</td>
                    <td>{{ $lead->email ?? $lead->phone ?? '—' }}</td>
                    <td>{{ $lead->created_at->format('d/m/Y') }}</td>
                    <td>{{ $lead->proposals->count() }}</td>
                    <td>{{ $lead->is_lead ? 'Lead' : 'Convertido' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.v2.leads.show', $lead->id) }}" class="btn btn-icon btn-primary-modern" title="Ver lead (vista admin)">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted p-4">Este angariador ainda não gerou leads.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-clock-history"></i> Atividade Recente</h5>
        <span class="badge bg-secondary rounded-pill">{{ $activity->count() }}</span>
    </div>
    @if($activity->isNotEmpty())
    <div class="timeline-list">
        @foreach($activity as $act)
        <div class="timeline-item">
            <div class="timeline-icon bg-{{ $act->color }}">
                <i class="{{ $act->icon }}"></i>
            </div>
            <div class="timeline-body">
                <div class="timeline-title">{{ $act->title }}</div>
                @if($act->body)
                <div class="timeline-text">{{ $act->body }}</div>
                @endif
                <div class="timeline-meta">
                    {{ $act->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="p-4 text-center text-muted small">
        <i class="bi bi-clock-history d-block fs-4 mb-1"></i>
        Ainda não há atividade registada por este angariador (login ou apontamentos em leads).
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    .timeline-list { padding: .5rem 0; }
    .timeline-item { display: flex; gap: 1rem; padding: .85rem 1.25rem; position: relative; }
    .timeline-item + .timeline-item::before {
        content: ''; position: absolute; top: 0; left: 2.35rem;
        width: 1px; height: 100%; background: #f0f0f0;
    }
    .timeline-icon {
        width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem; color: #fff; opacity: .9;
    }
    .timeline-body { flex: 1; min-width: 0; }
    .timeline-title { font-size: .88rem; font-weight: 600; color: #111; }
    .timeline-text { font-size: .83rem; color: #555; margin-top: .2rem; white-space: pre-line; }
    .timeline-meta { font-size: .72rem; color: #aaa; margin-top: .3rem; }
</style>
@endpush
