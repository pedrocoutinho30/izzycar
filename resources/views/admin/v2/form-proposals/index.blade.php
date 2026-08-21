@extends('layouts.admin-v2')

@section('title', 'Formulários de Cotação')

@section('content')


<!-- Page Header -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-file-earmark-text', 'label' => 'Formulários de Cotação', 'href' => ''],
],
'title' => 'Formulários de Cotação',
'subtitle' => 'Pedidos recebidos através do website',
'actionHref' => '',
'actionLabel' => ''
])
    <!-- STATS CARDS -->
    @include('components.admin.stats-cards', ['stats' => $stats])

    <!-- FILTROS -->
    @include('components.admin.filter-bar', [
        'filters' => [
            [
                'type' => 'text',
                'name' => 'search',
                'label' => 'Pesquisar',
                'placeholder' => 'Pesquisar por nome, email, telefone...',
                'value' => request('search')
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => 'Estado',
                'placeholder' => 'Estado',
                'value' => request('status'),
                'options' => [
                    'novo' => 'Novo',
                    'em_analise' => 'Em Análise',
                    'convertido' => 'Convertido',
                    'arquivado' => 'Arquivado'
                ]
            ]
        ]
    ])

    <!-- LISTA DE FORMULÁRIOS -->
<div class="modern-card">
    <div class="modern-card-header flex-wrap gap-2">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Formulários de Cotação
        </h5>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="form-check mb-0 fp-select-all-wrap" id="fpSelectAllWrap" style="display:none">
                <input type="checkbox" class="form-check-input" id="fpSelectAll" onchange="fpToggleAll(this)">
                <label class="form-check-label small" for="fpSelectAll">Selecionar todos</label>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="fpSelectModeBtn" onclick="fpToggleSelectionMode()">
                <i class="bi bi-check2-square"></i> Selecionar
            </button>
            <span class="badge bg-secondary rounded-pill">{{ $formProposals->total() }} total</span>
        </div>
    </div>

    <div id="fpBulkBar" class="fp-bulk-bar" style="display:none">
        <span><strong id="fpSelectedCount">0</strong> selecionado(s)</span>
        <select id="fpBulkStatus" class="form-select form-select-sm" style="max-width:200px">
            <option value="novo">Novo</option>
            <option value="em_analise">Em Análise</option>
            <option value="convertido">Convertido</option>
            <option value="rejeitado">Rejeitado</option>
            <option value="arquivado">Arquivado</option>
        </select>
        <button type="button" class="btn btn-primary btn-sm" onclick="fpApplyBulkStatus()">
            <i class="bi bi-check2"></i> Aplicar estado
        </button>
    </div>

    <div id="fpList">
        @forelse($formProposals as $form)
            @php
                $statusColors = [
                    'novo' => 'danger',
                    'em_analise' => 'warning',
                    'convertido' => 'success',
                    'arquivado' => 'secondary'
                ];
                $statusLabels = [
                    'novo' => 'Novo',
                    'em_analise' => 'Em Análise',
                    'convertido' => 'Convertido',
                    'arquivado' => 'Arquivado'
                ];
                $paymentLabels = [
                    'pronto_pagamento' => 'Pronto pagamento',
                    'financiamento' => 'Financiamento'
                ];
                $purchaseLabels = [
                    'imediato' => 'Imediato (até 30 dias)',
                    '1_3_meses' => '1-3 meses',
                    '3_6_meses' => '3-6 meses',
                    'pesquisar' => 'Apenas a pesquisar'
                ];
                $currentStatus = $form->status ?? 'novo';
            @endphp

            <div class="fp-card-wrapper" data-id="{{ $form->id }}">
                <div class="fp-checkbox-col">
                    <input type="checkbox" class="form-check-input fp-checkbox" value="{{ $form->id }}" onchange="fpUpdateSelectedCount()">
                </div>
                <div class="fp-card-content">
            @include('components.admin.item-card', [
                'image' => 'https://ui-avatars.com/api/?name=' . urlencode($form->name) . '&background=6e0707&color=fff&bold=true',
                'title' => $form->name,
                'subtitle' => $form->email ?? $form->phone,
                'badges' => array_filter([
                    [
                        'text' => $statusLabels[$currentStatus] ?? 'Novo',
                        'color' => $statusColors[$currentStatus] ?? 'secondary'
                    ],
                    $form->payment_type ? [
                        'text' => $paymentLabels[$form->payment_type] ?? $form->payment_type,
                        'color' => 'dark'
                    ] : null,
                    $form->estimated_purchase_date ? [
                        'text' => $purchaseLabels[$form->estimated_purchase_date] ?? $form->estimated_purchase_date,
                        'color' => 'info'
                    ] : null,
                    $form->brand ? [
                        'text' => $form->brand . ($form->model ? ' ' . $form->model : ''),
                        'color' => 'primary'
                    ] : null,
                    $form->budget ? [
                        'text' => 'Budget: ' . number_format($form->budget, 0, ',', '.') . '€',
                        'color' => 'info'
                    ] : null
                ]),
                'meta' => array_filter([
                    [
                        'icon' => 'bi-envelope',
                        'text' => $form->email ?? 'Sem email'
                    ],
                    [
                        'icon' => 'bi-telephone',
                        'text' => $form->phone ?? 'Sem telefone'
                    ],
                    [
                        'icon' => 'bi-calendar',
                        'text' => $form->created_at->format('d/m/Y H:i')
                    ]
                ]),
                'actions' => [
                    [
                        'href' => route('admin.v2.form-proposals.show', $form->id),
                        'icon' => 'bi-eye',
                        'label' => 'Ver Detalhes',
                        'color' => 'primary'
                    ],
                    [
                        'href' => route('admin.v2.form-proposals.destroy', $form->id),
                        'icon' => 'bi-trash',
                        'label' => 'Eliminar',
                        'color' => 'danger',
                        'method' => 'delete',
                        'confirm' => 'Tem a certeza que pretende eliminar este formulário?'
                    ]
                ]
            ])
                </div>
            </div>
        @empty
            @include('components.admin.empty-state', [
                'icon' => 'bi-envelope',
                'title' => 'Nenhum formulário encontrado',
                'description' => 'Não existem formulários de cotação ou não há resultados para os filtros aplicados.'
            ])
        @endforelse
    </div>
    </div>

    <!-- PAGINAÇÃO -->
    @if($formProposals->hasPages())
        <div class="pagination-wrapper">
            {{ $formProposals->links() }}
        </div>
    @endif
</div>

<style>
.content-grid > * {
    animation: fadeInUp 0.4s ease-out backwards;
}

.content-grid > *:nth-child(1) { animation-delay: 0.05s; }
.content-grid > *:nth-child(2) { animation-delay: 0.1s; }
.content-grid > *:nth-child(3) { animation-delay: 0.15s; }
.content-grid > *:nth-child(4) { animation-delay: 0.2s; }
.content-grid > *:nth-child(5) { animation-delay: 0.25s; }
.content-grid > *:nth-child(6) { animation-delay: 0.3s; }

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

.fp-bulk-bar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    padding: 0.75rem 1.25rem;
    background: #fff7ed;
    border-bottom: 1px solid #fed7aa;
}

.fp-card-wrapper {
    display: flex;
    align-items: stretch;
    gap: 0;
}

.fp-checkbox-col {
    width: 0;
    flex-shrink: 0;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: width 0.2s ease;
}

#fpList.fp-selection-mode .fp-checkbox-col {
    width: 2.75rem;
}

.fp-checkbox-col .fp-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
    flex-shrink: 0;
}

.fp-card-content {
    flex: 1;
    min-width: 0;
}
</style>

@push('scripts')
<script>
const FP_CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

function fpUpdateSelectedCount() {
    const count = document.querySelectorAll('.fp-checkbox:checked').length;
    document.getElementById('fpSelectedCount').textContent = count;
    document.getElementById('fpBulkBar').style.display = count > 0 ? 'flex' : 'none';

    const all = document.querySelectorAll('.fp-checkbox');
    document.getElementById('fpSelectAll').checked = all.length > 0 && count === all.length;
}

function fpToggleAll(checkbox) {
    document.querySelectorAll('.fp-checkbox').forEach(c => c.checked = checkbox.checked);
    fpUpdateSelectedCount();
}

function fpToggleSelectionMode() {
    const list = document.getElementById('fpList');
    const btn = document.getElementById('fpSelectModeBtn');
    const active = list.classList.toggle('fp-selection-mode');

    document.getElementById('fpSelectAllWrap').style.display = active ? 'flex' : 'none';
    btn.innerHTML = active
        ? '<i class="bi bi-x-lg"></i> Cancelar seleção'
        : '<i class="bi bi-check2-square"></i> Selecionar';
    btn.classList.toggle('btn-outline-primary', !active);
    btn.classList.toggle('btn-outline-secondary', active);

    if (!active) {
        document.querySelectorAll('.fp-checkbox').forEach(c => c.checked = false);
        document.getElementById('fpSelectAll').checked = false;
        fpUpdateSelectedCount();
    }
}

function fpApplyBulkStatus() {
    const checked = [...document.querySelectorAll('.fp-checkbox:checked')].map(c => c.value);
    if (!checked.length) return;
    const status = document.getElementById('fpBulkStatus').value;

    if (!confirm(`Alterar o estado de ${checked.length} formulário(s) selecionado(s)?`)) return;

    fetch('{{ route("admin.v2.form-proposals.bulk-status") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': FP_CSRF },
        body: JSON.stringify({ ids: checked, status: status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Erro ao atualizar o estado.');
        }
    })
    .catch(() => alert('Erro ao atualizar o estado.'));
}
</script>
@endpush
@endsection
