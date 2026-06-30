@extends('layouts.admin-v2')

@section('title', isset($convertedProposal)
    ? ($convertedProposal->brand . ' ' . $convertedProposal->modelCar . ' — Gestão')
    : 'Nova Cotação Convertida')

@push('styles')
<style>
/* ============================================================
   PIPELINE DE ESTADOS
   ============================================================ */
.cp-pipeline-container {
    background: #fff;
    border-radius: 16px;
    border: 1px solid rgba(0,0,0,0.07);
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.5rem;
}

.cp-pipeline-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.cp-pipeline-title {
    font-weight: 700;
    font-size: 0.95rem;
    color: #111;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.cp-pipeline-hint {
    font-size: 0.78rem;
    color: #aaa;
    margin-left: auto;
}

.cp-pipeline-badge {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.cp-pipeline-badge.cancelled { background: #fee2e2; color: #991b1b; }

.cp-pipeline-steps {
    display: flex;
    align-items: center;
    gap: 0;
    overflow-x: auto;
    padding-bottom: 0.25rem;
}

.cp-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
    flex-shrink: 0;
    cursor: pointer;
    padding: 0.5rem 0.35rem;
    border-radius: 10px;
    transition: background 0.2s;
    min-width: 68px;
}

.cp-step:hover { background: rgba(153,0,0,0.05); }

.cp-step-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    border: 2px solid #e0e0e0;
    background: #f5f5f5;
    color: #bbb;
    transition: all 0.25s;
}

.cp-step.done .cp-step-icon {
    background: #dcfce7;
    border-color: #16a34a;
    color: #16a34a;
}

.cp-step.active .cp-step-icon {
    background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
    border-color: #990000;
    color: #fff;
    box-shadow: 0 4px 14px rgba(153,0,0,0.4), 0 0 0 3px rgba(153,0,0,0.12);
}

.cp-step.cancelled .cp-step-icon {
    background: #f5f5f5;
    border-color: #ddd;
    color: #ccc;
}

.cp-step-label {
    font-size: 0.68rem;
    font-weight: 600;
    color: #aaa;
    text-align: center;
    line-height: 1.2;
    transition: color 0.2s;
}

.cp-step.done .cp-step-label  { color: #16a34a; }
.cp-step.active .cp-step-label { color: #990000; font-weight: 700; }

.cp-step-date {
    font-size: 0.6rem;
    color: #aaa;
    background: #f5f5f5;
    border-radius: 4px;
    padding: 0.1rem 0.35rem;
}
.cp-step.done .cp-step-date  { background: #dcfce7; color: #16a34a; }
.cp-step.active .cp-step-date { background: #fee2e2; color: #990000; }

.cp-step-connector {
    flex: 1;
    min-width: 12px;
    height: 2px;
    background: #e5e7eb;
    flex-shrink: 0;
    transition: background 0.3s;
}
.cp-step-connector.done { background: #16a34a; }

.cp-cancel-btn {
    flex-shrink: 0;
    margin-left: 1rem;
    padding: 0.45rem 1rem;
    border-radius: 8px;
    border: 2px solid #fca5a5;
    background: transparent;
    color: #dc2626;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    white-space: nowrap;
}
.cp-cancel-btn:hover,
.cp-cancel-btn.active {
    background: #dc2626;
    border-color: #dc2626;
    color: #fff;
}

/* ============================================================
   FINANCIAL TRACKING TABLE
   ============================================================ */
.cp-financial-summary {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-left: auto;
}

.cp-fin-badge {
    font-size: 0.75rem;
    padding: 0.3rem 0.7rem;
    border-radius: 8px;
    font-weight: 600;
}
.cp-fin-badge.total   { background: #f1f5f9; color: #334155; }
.cp-fin-badge.paid    { background: #dcfce7; color: #166534; }
.cp-fin-badge.pending { background: #fef3c7; color: #92400e; }

.cp-cost-table { padding: 0.25rem 0; }

.cp-cost-row {
    display: grid;
    grid-template-columns: 36px 1fr 160px 160px 10px;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 1rem;
    border-bottom: 1px solid rgba(0,0,0,0.04);
    transition: background 0.15s;
}
.cp-cost-row:last-child { border-bottom: none; }
.cp-cost-row:hover      { background: #fafafa; }
.cp-cost-row.paid       { background: rgba(22,163,74,0.03); }

.cp-cost-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    background: #f1f5f9;
    color: #64748b;
    flex-shrink: 0;
}
.cp-cost-row.paid   .cp-cost-icon { background: #dcfce7; color: #16a34a; }
.cp-cost-row.pending .cp-cost-icon { background: #fef3c7; color: #d97706; }

.cp-cost-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

.cp-cost-paid { display: flex; align-items: center; }

.cp-paid-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
}
.cp-cost-row.paid   .cp-paid-label { color: #16a34a; }
.cp-cost-row.pending .cp-paid-label { color: #d97706; }

.cp-cost-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.cp-cost-status-dot.paid    { background: #16a34a; }
.cp-cost-status-dot.pending { background: #d97706; }

/* ============================================================
   STATUS HISTORY TIMELINE
   ============================================================ */
.cp-history-timeline {
    padding: 0.5rem 0;
    position: relative;
}

.cp-history-timeline::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 8px;
    bottom: 8px;
    width: 2px;
    background: linear-gradient(180deg, #990000 0%, rgba(153,0,0,0.1) 100%);
}

.cp-history-item {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
    position: relative;
}

.cp-history-item:last-child { margin-bottom: 0; }

.cp-history-dot {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px rgba(153,0,0,0.2);
    margin-top: 2px;
    z-index: 1;
}

.cp-history-content {
    flex: 1;
    background: #f8fafc;
    border-radius: 8px;
    padding: 0.55rem 0.75rem;
    border: 1px solid rgba(0,0,0,0.05);
}

.cp-history-status {
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    color: #111;
}

.cp-history-from {
    display: block;
    font-size: 0.72rem;
    color: #888;
    margin-top: 0.1rem;
}

.cp-history-date {
    display: block;
    font-size: 0.72rem;
    color: #aaa;
    margin-top: 0.2rem;
}

/* ============================================================
   STATUS TOAST / LOADING
   ============================================================ */
.cp-status-toast {
    position: fixed;
    top: 1.5rem;
    right: 1.5rem;
    z-index: 9999;
    padding: 0.85rem 1.25rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    transform: translateY(-10px);
    opacity: 0;
    transition: all 0.3s ease;
}
.cp-status-toast.show  { transform: translateY(0); opacity: 1; }
.cp-status-toast.success { background: #dcfce7; color: #166534; }
.cp-status-toast.error   { background: #fee2e2; color: #991b1b; }

/* ============================================================
   COLLAPSIBLE CARD TOGGLE
   ============================================================ */
.cp-collapse-header {
    cursor: pointer;
    user-select: none;
}
.cp-collapse-header:hover { background: #fafafa; }
.cp-collapse-icon { transition: transform 0.3s; }
.collapsed .cp-collapse-icon { transform: rotate(-90deg); }

/* ============================================================
   MISC
   ============================================================ */
.modern-card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.form-check-input:checked {
    background-color: #990000;
    border-color: #990000;
}

.form-label.required::after {
    content: ' *';
    color: #dc3545;
}

@media (max-width: 768px) {
    .cp-cost-row {
        grid-template-columns: 32px 1fr;
        grid-template-rows: auto auto auto;
    }
    .cp-cost-input  { grid-column: 2; }
    .cp-cost-paid   { grid-column: 1 / -1; }
    .cp-cost-status-dot { display: none; }

    .cp-pipeline-hint { display: none; }
    .cp-financial-summary { margin-left: 0; margin-top: 0.5rem; }
    .modern-card-header { flex-direction: column; align-items: flex-start; }
}
</style>
@endpush

@section('content')

@php
$isEdit = isset($convertedProposal);

$allStatusSteps = [
    ['key' => 'Iniciada',          'icon' => 'bi-play-circle',          'label' => 'Iniciada'],
    ['key' => 'Negociação Carro',  'icon' => 'bi-chat-dots',            'label' => 'Negociação'],
    ['key' => 'Transporte',        'icon' => 'bi-truck',                'label' => 'Transporte'],
    ['key' => 'IPO',               'icon' => 'bi-clipboard-check',      'label' => 'IPO'],
    ['key' => 'DAV',               'icon' => 'bi-file-earmark-check',   'label' => 'DAV'],
    ['key' => 'ISV',               'icon' => 'bi-cash-stack',           'label' => 'ISV'],
    ['key' => 'Matriculação',      'icon' => 'bi-card-text',            'label' => 'Matriculação'],
    ['key' => 'IMT',               'icon' => 'bi-building',             'label' => 'IMT'],
    ['key' => 'Entrega',           'icon' => 'bi-box-seam',             'label' => 'Entrega'],
    ['key' => 'Registo automóvel', 'icon' => 'bi-file-earmark',         'label' => 'Registo'],
    ['key' => 'Concluido',         'icon' => 'bi-check-circle',         'label' => 'Concluído'],
];

$currentStatus = $isEdit ? ($convertedProposal->status ?? 'Iniciada') : 'Iniciada';
$isCancelled   = $currentStatus === 'Cancelada';

$currentStepIndex = -1;
foreach ($allStatusSteps as $i => $step) {
    if ($step['key'] === $currentStatus) { $currentStepIndex = $i; break; }
}
if ($currentStepIndex === -1 && !$isCancelled) $currentStepIndex = 0;

// Map status keys to timestamps from history
$statusTimestamps = [];
if ($isEdit && isset($statusHistory)) {
    foreach ($statusHistory->reverse() as $h) {
        $statusTimestamps[$h->new_status] = $h->created_at;
    }
}

// Financial items
$financialItems = $isEdit ? [
    ['key' => 'valor_carro',             'paid_key' => 'carro_pago',              'label' => 'Valor do Carro',       'icon' => 'bi-car-front',        'value' => $convertedProposal->valor_carro ?? 0,            'paid' => (bool)($convertedProposal->carro_pago ?? false)],
    ['key' => 'custo_inspecao_origem',   'paid_key' => 'inspecao_origem_pago',    'label' => 'Inspeção de Origem',   'icon' => 'bi-search',           'value' => $convertedProposal->custo_inspecao_origem ?? 0,  'paid' => (bool)($convertedProposal->inspecao_origem_pago ?? false)],
    ['key' => 'custo_transporte',        'paid_key' => 'transporte_pago',         'label' => 'Transporte',           'icon' => 'bi-truck',            'value' => $convertedProposal->custo_transporte ?? 0,       'paid' => (bool)($convertedProposal->transporte_pago ?? false)],
    ['key' => 'custo_ipo',               'paid_key' => 'ipo_pago',               'label' => 'IPO',                  'icon' => 'bi-clipboard-check',  'value' => $convertedProposal->custo_ipo ?? 0,              'paid' => (bool)($convertedProposal->ipo_pago ?? false)],
    ['key' => 'isv',                     'paid_key' => 'isv_pago',               'label' => 'ISV',                  'icon' => 'bi-cash-stack',       'value' => $convertedProposal->isv ?? 0,                    'paid' => (bool)($convertedProposal->isv_pago ?? false)],
    ['key' => 'custo_imt',               'paid_key' => 'imt_pago',               'label' => 'IMT',                  'icon' => 'bi-building',         'value' => $convertedProposal->custo_imt ?? 0,              'paid' => (bool)($convertedProposal->imt_pago ?? false)],
    ['key' => 'custo_matricula',         'paid_key' => 'matricula_pago_impressa', 'label' => 'Matrícula',            'icon' => 'bi-card-text',        'value' => $convertedProposal->custo_matricula ?? 0,        'paid' => (bool)($convertedProposal->matricula_pago_impressa ?? false)],
    ['key' => 'custo_registo_automovel', 'paid_key' => 'registo_pago',           'label' => 'Registo Automóvel',    'icon' => 'bi-file-earmark',     'value' => $convertedProposal->custo_registo_automovel ?? 0,'paid' => (bool)($convertedProposal->registo_pago ?? false)],
] : [];

$tranches = $isEdit ? [
    ['key' => 'valor_primeira_tranche', 'paid_key' => 'primeira_tranche_pago', 'label' => '1ª Tranche do Cliente', 'value' => $convertedProposal->valor_primeira_tranche ?? 0, 'paid' => (bool)($convertedProposal->primeira_tranche_pago ?? false)],
    ['key' => 'valor_segunda_tranche',  'paid_key' => 'segunda_tranche_pago',  'label' => '2ª Tranche do Cliente', 'value' => $convertedProposal->valor_segunda_tranche ?? 0,  'paid' => (bool)($convertedProposal->segunda_tranche_pago ?? false)],
] : [];

$totalValue   = $isEdit ? array_sum(array_column($financialItems, 'value')) : 0;
$paidValue    = $isEdit ? array_sum(array_map(fn($i) => $i['paid'] ? $i['value'] : 0, $financialItems)) : 0;
$pendingValue = $totalValue - $paidValue;
@endphp

<!-- Page Header -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door',          'label' => 'Dashboard',            'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-file-earmark-text',   'label' => 'Cotações Convertidas', 'href' => route('admin.v2.converted-proposals.index')],
        ['icon' => '', 'label' => $isEdit ? (($convertedProposal->brand ?? '') . ' ' . ($convertedProposal->modelCar ?? '')) : 'Nova Cotação'],
    ],
    'title'    => $isEdit
        ? (($convertedProposal->brand ?? '') . ' ' . ($convertedProposal->modelCar ?? '') . ($convertedProposal->year ? ' · ' . $convertedProposal->year : ''))
        : 'Nova Cotação Convertida',
    'subtitle' => $isEdit
        ? 'Cliente: ' . ($convertedProposal->client->name ?? 'N/A') . ' · Gestão do processo de importação'
        : 'Preencha os dados da nova proposta convertida',
    'actionHref'  => $isEdit ? route('converted-proposals.timeline', ['brand' => $convertedProposal->brand ?? 'veiculo', 'model' => $convertedProposal->modelCar ?? 'modelo', 'version' => $convertedProposal->version ?? 'versao', 'id' => $convertedProposal->id]) : '',
    'actionLabel' => $isEdit ? 'Ver Timeline' : '',
])

@if($isEdit)
<!-- ============================================================
     PIPELINE DE ESTADOS
     ============================================================ -->
<div class="cp-pipeline-container">
    <div class="cp-pipeline-header">
        <span class="cp-pipeline-title"><i class="bi bi-diagram-3"></i> Fase do Processo</span>
        @if($isCancelled)
            <span class="cp-pipeline-badge cancelled"><i class="bi bi-x-circle"></i> Cancelada</span>
        @endif
        <span class="cp-pipeline-hint">Clique numa fase para atualizar o estado e notificar o cliente</span>
    </div>
    <div class="cp-pipeline-steps">
        @foreach($allStatusSteps as $i => $step)
        @php
            $isDone   = !$isCancelled && $i < $currentStepIndex;
            $isActive = !$isCancelled && $i === $currentStepIndex;
            $ts       = $statusTimestamps[$step['key']] ?? null;
        @endphp
        <div class="cp-step {{ $isDone ? 'done' : '' }} {{ $isActive ? 'active' : '' }} {{ $isCancelled ? 'cancelled' : '' }}"
             onclick="updateProposalStatus('{{ $step['key'] }}')"
             title="{{ $step['key'] }}{{ $ts ? ' · ' . $ts->format('d/m/Y H:i') : '' }}">
            <div class="cp-step-icon">
                @if($isDone)
                    <i class="bi bi-check-lg"></i>
                @else
                    <i class="bi {{ $step['icon'] }}"></i>
                @endif
            </div>
            <div class="cp-step-label">{{ $step['label'] }}</div>
            @if($ts)
            <div class="cp-step-date">{{ $ts->format('d/m') }}</div>
            @endif
        </div>
        @if(!$loop->last)
        <div class="cp-step-connector {{ $isDone ? 'done' : '' }}"></div>
        @endif
        @endforeach

        <button type="button"
                class="cp-cancel-btn {{ $isCancelled ? 'active' : '' }}"
                onclick="updateProposalStatus('Cancelada')">
            <i class="bi bi-x-circle"></i> Cancelar
        </button>
    </div>
</div>
@endif

<!-- ============================================================
     FORMULÁRIO PRINCIPAL
     ============================================================ -->
<form action="{{ $isEdit ? route('admin.v2.converted-proposals.update', $convertedProposal->id) : route('admin.v2.converted-proposals.store') }}"
      method="POST" id="proposalForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row g-4">

        <!-- ==================== COLUNA PRINCIPAL ==================== -->
        <div class="col-lg-8">

            @if($isEdit)
            <!-- CUSTOS & PAGAMENTOS -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-cash-coin"></i> Custos & Pagamentos</h5>
                    <div class="cp-financial-summary">
                        <span class="cp-fin-badge total">Total: <strong>{{ number_format($totalValue, 2, ',', '.') }}€</strong></span>
                        <span class="cp-fin-badge paid">Pago: <strong>{{ number_format($paidValue, 2, ',', '.') }}€</strong></span>
                        <span class="cp-fin-badge pending">Pendente: <strong>{{ number_format($pendingValue, 2, ',', '.') }}€</strong></span>
                    </div>
                </div>
                <div class="cp-cost-table">
                    @foreach($financialItems as $item)
                    <div class="cp-cost-row {{ $item['paid'] ? 'paid' : 'pending' }}">
                        <div class="cp-cost-icon"><i class="bi {{ $item['icon'] }}"></i></div>
                        <div class="cp-cost-label">{{ $item['label'] }}</div>
                        <div class="cp-cost-input">
                            <div class="input-group input-group-sm">
                                <input type="number" name="{{ $item['key'] }}" class="form-control"
                                       value="{{ old($item['key'], $item['value'] > 0 ? $item['value'] : '') }}"
                                       step="0.01" placeholder="0,00" min="0">
                                <span class="input-group-text">€</span>
                            </div>
                        </div>
                        <div class="cp-cost-paid">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox"
                                       name="{{ $item['paid_key'] }}" value="1"
                                       id="paid_{{ $item['paid_key'] }}"
                                       {{ old($item['paid_key'], $item['paid']) ? 'checked' : '' }}
                                       onchange="togglePaidRow(this)">
                                <label class="form-check-label cp-paid-label" for="paid_{{ $item['paid_key'] }}">
                                    {{ $item['paid'] ? 'Pago' : 'Pendente' }}
                                </label>
                            </div>
                        </div>
                        <div class="cp-cost-status-dot {{ $item['paid'] ? 'paid' : 'pending' }}"></div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- TRANCHES DO CLIENTE -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-wallet2"></i> Tranches do Cliente</h5>
                    @php
                        $tranchesTotalPaid    = array_sum(array_map(fn($t) => $t['paid'] ? $t['value'] : 0, $tranches));
                        $tranchesTotalPending = array_sum(array_map(fn($t) => !$t['paid'] ? $t['value'] : 0, $tranches));
                    @endphp
                    <div class="cp-financial-summary">
                        @if($tranchesTotalPaid > 0)
                        <span class="cp-fin-badge paid">Recebido: <strong>{{ number_format($tranchesTotalPaid, 2, ',', '.') }}€</strong></span>
                        @endif
                        @if($tranchesTotalPending > 0)
                        <span class="cp-fin-badge pending">A receber: <strong>{{ number_format($tranchesTotalPending, 2, ',', '.') }}€</strong></span>
                        @endif
                    </div>
                </div>
                <div class="cp-cost-table">
                    @foreach($tranches as $tranche)
                    <div class="cp-cost-row {{ $tranche['paid'] ? 'paid' : 'pending' }}">
                        <div class="cp-cost-icon"><i class="bi bi-credit-card"></i></div>
                        <div class="cp-cost-label">{{ $tranche['label'] }}</div>
                        <div class="cp-cost-input">
                            <div class="input-group input-group-sm">
                                <input type="number" name="{{ $tranche['key'] }}" class="form-control"
                                       value="{{ old($tranche['key'], $tranche['value'] > 0 ? $tranche['value'] : '') }}"
                                       step="0.01" placeholder="0,00" min="0">
                                <span class="input-group-text">€</span>
                            </div>
                        </div>
                        <div class="cp-cost-paid">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox"
                                       name="{{ $tranche['paid_key'] }}" value="1"
                                       id="paid_{{ $tranche['paid_key'] }}"
                                       {{ old($tranche['paid_key'], $tranche['paid']) ? 'checked' : '' }}
                                       onchange="togglePaidRow(this)">
                                <label class="form-check-label cp-paid-label" for="paid_{{ $tranche['paid_key'] }}">
                                    {{ $tranche['paid'] ? 'Recebido' : 'Pendente' }}
                                </label>
                            </div>
                        </div>
                        <div class="cp-cost-status-dot {{ $tranche['paid'] ? 'paid' : 'pending' }}"></div>
                    </div>
                    @endforeach
                </div>
            </div>

            @else
            {{-- CRIAR: tabela de custos simples --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-cash-coin"></i> Custos de Importação</h5>
                </div>
                <div class="row g-3 p-3">
                    @foreach([
                        ['name' => 'valor_carro',             'paid' => 'carro_pago',              'label' => 'Valor do Carro'],
                        ['name' => 'custo_inspecao_origem',   'paid' => 'inspecao_origem_pago',    'label' => 'Inspeção de Origem'],
                        ['name' => 'custo_transporte',        'paid' => 'transporte_pago',         'label' => 'Transporte'],
                        ['name' => 'custo_ipo',               'paid' => 'ipo_pago',               'label' => 'IPO'],
                        ['name' => 'isv',                     'paid' => 'isv_pago',               'label' => 'ISV'],
                        ['name' => 'custo_imt',               'paid' => 'imt_pago',               'label' => 'IMT'],
                        ['name' => 'custo_matricula',         'paid' => 'matricula_pago_impressa', 'label' => 'Matrícula'],
                        ['name' => 'custo_registo_automovel', 'paid' => 'registo_pago',           'label' => 'Registo Automóvel'],
                        ['name' => 'valor_primeira_tranche',  'paid' => 'primeira_tranche_pago',  'label' => '1ª Tranche'],
                        ['name' => 'valor_segunda_tranche',   'paid' => 'segunda_tranche_pago',   'label' => '2ª Tranche'],
                    ] as $f)
                    <div class="col-md-6">
                        <label class="form-label">{{ $f['label'] }} (€)</label>
                        <input type="number" name="{{ $f['name'] }}" class="form-control"
                               value="{{ old($f['name']) }}" step="0.01" placeholder="0,00" min="0">
                    </div>
                    <div class="col-md-6 d-flex align-items-end pb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="{{ $f['paid'] }}" value="1">
                            <label class="form-check-label">Pago</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- DADOS DO VEÍCULO (colapsável em edição) -->
            <div class="modern-card">
                @if($isEdit)
                <div class="modern-card-header cp-collapse-header collapsed"
                     data-bs-toggle="collapse" data-bs-target="#vehicleDataBody">
                    <h5 class="modern-card-title"><i class="bi bi-car-front"></i> Dados do Veículo</h5>
                    <i class="bi bi-chevron-down cp-collapse-icon ms-auto"></i>
                </div>
                <div class="collapse" id="vehicleDataBody">
                @else
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-car-front"></i> Dados do Veículo</h5>
                </div>
                <div>
                @endif
                    <div class="row g-3 p-3">
                        <div class="col-md-4">
                            <label class="form-label">Marca</label>
                            <input type="text" name="brand" class="form-control @error('brand') is-invalid @enderror"
                                   value="{{ old('brand', $convertedProposal->brand ?? '') }}">
                            @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modelo</label>
                            <input type="text" name="modelCar" class="form-control @error('modelCar') is-invalid @enderror"
                                   value="{{ old('modelCar', $convertedProposal->modelCar ?? '') }}">
                            @error('modelCar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Versão</label>
                            <input type="text" name="version" class="form-control @error('version') is-invalid @enderror"
                                   value="{{ old('version', $convertedProposal->version ?? '') }}">
                            @error('version')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ano</label>
                            <input type="number" name="year" class="form-control"
                                   value="{{ old('year', $convertedProposal->year ?? '') }}"
                                   min="1900" max="{{ date('Y') + 1 }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">KM</label>
                            <input type="number" name="km" class="form-control"
                                   value="{{ old('km', $convertedProposal->km ?? '') }}" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Matrícula Origem</label>
                            <input type="text" name="matricula_origem" class="form-control"
                                   value="{{ old('matricula_origem', $convertedProposal->matricula_origem ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Matrícula Destino</label>
                            <input type="text" name="matricula_destino" class="form-control"
                                   value="{{ old('matricula_destino', $convertedProposal->matricula_destino ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- OBSERVAÇÕES -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-chat-left-text"></i> Observações</h5>
                </div>
                <div class="row g-3 p-3">
                    <div class="col-12">
                        <label class="form-label">Contactos do Stand</label>
                        <textarea name="contactos_stand" class="form-control" rows="3"
                                  placeholder="Nome, telemóvel, email do stand de origem...">{{ old('contactos_stand', $convertedProposal->contactos_stand ?? '') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observações Gerais</label>
                        <textarea name="observacoes" class="form-control" rows="4"
                                  placeholder="Notas internas sobre o processo...">{{ old('observacoes', $convertedProposal->observacoes ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== COLUNA LATERAL ==================== -->
        <div class="col-lg-4">

            <!-- AÇÕES -->
            @include('components.admin.action-card', [
                'cancelButtonHref' => route('admin.v2.converted-proposals.index'),
                'submitButtonLabel' => $isEdit ? 'Guardar Alterações' : 'Criar Cotação Convertida',
                'timestamps' => $isEdit ? [
                    'created_at' => $convertedProposal->created_at,
                    'updated_at' => $convertedProposal->updated_at,
                ] : null,
            ])

            <!-- DADOS PRINCIPAIS -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-person-circle"></i> Dados Principais</h5>
                </div>
                <div class="row g-3 p-3">
                    <div class="col-12">
                        <label class="form-label required">Cliente</label>
                        <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                            <option value="">Selecione o cliente...</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                {{ old('client_id', $convertedProposal->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Cotação Original</label>
                        <select name="proposal_id" class="form-select @error('proposal_id') is-invalid @enderror">
                            <option value="">Sem cotação associada...</option>
                            @foreach($proposals as $proposal)
                            <option value="{{ $proposal->id }}"
                                {{ old('proposal_id', $convertedProposal->proposal_id ?? '') == $proposal->id ? 'selected' : '' }}>
                                Cotação #{{ $proposal->id }}
                                @if($proposal->brand ?? false) — {{ $proposal->brand }} {{ $proposal->model ?? '' }}@endif
                            </option>
                            @endforeach
                        </select>
                        @error('proposal_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">URL do Anúncio</label>
                        <input type="url" name="url" class="form-control @error('url') is-invalid @enderror"
                               value="{{ old('url', $convertedProposal->url ?? '') }}"
                               placeholder="https://...">
                        @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @if(!$isEdit)
                    <div class="col-12">
                        <label class="form-label">Estado Inicial</label>
                        <select name="status" class="form-select">
                            <option value="Iniciada">Iniciada</option>
                            <option value="Em Processo">Em Processo</option>
                        </select>
                    </div>
                    @endif
                </div>
            </div>

            <!-- COMISSÕES -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-percent"></i> Comissões</h5>
                </div>
                <div class="row g-3 p-3">
                    <div class="col-12">
                        <label class="form-label">Valor Comissão (€)</label>
                        <div class="input-group">
                            <input type="number" name="valor_comissao" class="form-control"
                                   value="{{ old('valor_comissao', $convertedProposal->valor_comissao ?? '') }}"
                                   step="0.01" placeholder="0,00">
                            <span class="input-group-text">€</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Comissão Final (€)</label>
                        <div class="input-group">
                            <input type="number" name="valor_comissao_final" class="form-control"
                                   value="{{ old('valor_comissao_final', $convertedProposal->valor_comissao_final ?? '') }}"
                                   step="0.01" placeholder="0,00">
                            <span class="input-group-text">€</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($isEdit && isset($statusHistory) && $statusHistory->isNotEmpty())
            <!-- HISTÓRICO DE ESTADOS -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-clock-history"></i> Histórico de Estados</h5>
                    <span class="badge bg-secondary ms-auto">{{ $statusHistory->count() }}</span>
                </div>
                <div class="p-3">
                    <div class="cp-history-timeline">
                        @foreach($statusHistory as $history)
                        <div class="cp-history-item">
                            <div class="cp-history-dot"></div>
                            <div class="cp-history-content">
                                <span class="cp-history-status">{{ $history->new_status }}</span>
                                @if($history->old_status)
                                <span class="cp-history-from">de: {{ $history->old_status }}</span>
                                @endif
                                <span class="cp-history-date">{{ $history->created_at->format('d/m/Y \à\s H:i') }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</form>

<!-- Toast de feedback -->
<div class="cp-status-toast" id="cpToast"></div>

@endsection

@push('scripts')
<script>
@if($isEdit)
function updateProposalStatus(status) {
    const current = '{{ $currentStatus }}';
    if (status === current) return;

    const label = status === 'Cancelada' ? 'Cancelar esta proposta?' : 'Avançar para "' + status + '"?';
    if (!confirm(label + '\n\nO cliente será notificado por email.')) return;

    fetch('{{ route("converted-proposals.updateStatus", $convertedProposal->id) }}', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Estado atualizado: ' + status + ' · Email enviado ao cliente', 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showToast('Erro ao atualizar o estado.', 'error');
        }
    })
    .catch(() => showToast('Erro de rede ao atualizar o estado.', 'error'));
}
@endif

function togglePaidRow(checkbox) {
    const row   = checkbox.closest('.cp-cost-row');
    const label = checkbox.closest('.cp-cost-paid').querySelector('.cp-paid-label');
    const dot   = row.querySelector('.cp-cost-status-dot');

    if (checkbox.checked) {
        row.classList.replace('pending', 'paid');
        dot.classList.replace('pending', 'paid');
        label.textContent = checkbox.name.includes('tranche') ? 'Recebido' : 'Pago';
    } else {
        row.classList.replace('paid', 'pending');
        dot.classList.replace('paid', 'pending');
        label.textContent = 'Pendente';
    }
}

function showToast(msg, type) {
    const t = document.getElementById('cpToast');
    t.className = 'cp-status-toast ' + type;
    t.innerHTML = '<i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-triangle') + '"></i> ' + msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3500);
}

// Toggle collapse icon direction
document.addEventListener('DOMContentLoaded', function () {
    const collapseEl = document.getElementById('vehicleDataBody');
    if (!collapseEl) return;
    collapseEl.addEventListener('show.bs.collapse', () => {
        collapseEl.previousElementSibling?.classList.remove('collapsed');
    });
    collapseEl.addEventListener('hide.bs.collapse', () => {
        collapseEl.previousElementSibling?.classList.add('collapsed');
    });
});
</script>
@endpush
