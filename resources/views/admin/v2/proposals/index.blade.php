{{--
    ==================================================================
    PROPOSTAS - LISTAGEM V2
    ==================================================================
    
    View moderna de listagem de cotações com:
    - Stats cards no topo
    - Filtros avançados
    - Cards adaptativos mobile-first
    - Paginação
    - Actions rápidas
    
    @extends layouts.admin-v2
    ==================================================================
--}}

@extends('layouts.admin-v2')

@section('title', 'Cotações')

@section('content')

<!-- Page Header -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-file-earmark-text', 'label' => 'Cotações', 'href' => ''],
],
'title' => 'Cotações',
'subtitle' => 'Gerir todas as cotações de importação',
'actionHref' => route('admin.v2.proposals.create'),
'actionLabel' => 'Nova Cotação'
])
<!-- Stats Cards -->
@include('components.admin.stats-cards', ['stats' => $stats])

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.v2.export.proposals', request()->query()) }}"
       class="btn btn-outline-success btn-sm" title="Exportar cotações para CSV (abre no Excel)">
        <i class="bi bi-download me-1"></i> Exportar CSV
    </a>
</div>

@if($staleCount > 0 && !request()->hasAny(['status', 'search', 'client_id', 'date_from', 'date_to']))
<!-- ALERTA: Cotações antigas sem resposta -->
<div class="stale-alert" id="staleAlert">
    <div class="stale-alert-icon">
        <i class="bi bi-exclamation-triangle-fill"></i>
    </div>
    <div class="stale-alert-body">
        <strong>{{ $staleCount }} {{ $staleCount === 1 ? 'cotação' : 'cotações' }} sem resposta há mais de 30 dias</strong>
        <span>Estado Pendente ou Enviado — considere reprovar ou fazer follow-up.</span>
    </div>
    <div class="stale-alert-actions">
        <a href="{{ route('admin.v2.proposals.index', ['stale' => 1]) }}" class="stale-btn-view">
            <i class="bi bi-eye"></i> Ver {{ $staleCount === 1 ? 'a' : 'as' }} {{ $staleCount }}
        </a>
        <button type="button" class="stale-btn-reject" onclick="bulkRejectAll()">
            <i class="bi bi-x-circle"></i> Reprovar todas
        </button>
    </div>
</div>
@endif

@if(request()->boolean('stale'))
<div class="stale-filter-bar">
    <span><i class="bi bi-funnel-fill"></i> A mostrar cotações com +30 dias sem resposta (Pendente/Enviado)</span>
    <div class="stale-filter-actions">
        <button type="button" class="stale-btn-reject-sel" id="btnRejectSelected" onclick="rejectSelected()" style="display:none">
            <i class="bi bi-x-circle"></i> Reprovar selecionadas (<span id="selectedCount">0</span>)
        </button>
        <button type="button" class="stale-btn-reject" onclick="bulkRejectAll()">
            <i class="bi bi-x-circle-fill"></i> Reprovar todas as {{ $proposals->total() }}
        </button>
        <a href="{{ route('admin.v2.proposals.index') }}" class="stale-btn-clear">
            <i class="bi bi-x"></i> Limpar filtro
        </a>
    </div>
</div>
@endif

<!-- Filter Bar -->
@include('components.admin.filter-bar', [
'action' => route('admin.v2.proposals.index'),
'filters' => [
[
'name' => 'search',
'label' => 'Pesquisar',
'type' => 'text',
'placeholder' => 'Marca, modelo ou versão...',
'value' => request('search'),
'col' => 12
],
[
'name' => 'status',
'label' => 'Estado',
'type' => 'select',
'options' => ['Pendente', 'Aprovada', 'Reprovada', 'Enviado', 'Sem resposta'],
'value' => request('status'),
'col' => 4
],
[
'name' => 'client_id',
'label' => 'Cliente',
'type' => 'select',
'options' => $clients->mapWithKeys(function($client) {
return [$client->id => $client->name];
})->toArray(),
'value' => request('client_id'),
'col' => 4
],
[
'name' => 'date_from',
'label' => 'Data Inicial',
'type' => 'date',
'value' => request('date_from'),
'col' => 2
],
[
'name' => 'date_to',
'label' => 'Data Final',
'type' => 'date',
'value' => request('date_to'),
'col' => 2
]
]
])

<!-- Proposals List -->
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-list-ul"></i>
            Lista de Cotações
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $proposals->total() }} total</span>
    </div>

    @if($proposals->count() > 0)
    <div class="proposals-list">
        @foreach($proposals as $proposal)
        @php
        $statusColors = [
            'Pendente'     => 'warning',
            'Aprovada'     => 'success',
            'Reprovada'    => 'danger',
            'Enviado'      => 'info',
            'Sem resposta' => 'secondary'
        ];
        $statusColor = $statusColors[$proposal->status] ?? 'secondary';
        $proposalImage = $proposal->images ? asset('storage/' . $proposal->images) : null;
        $isStale = in_array($proposal->status, ['Pendente', 'Enviado']) && $proposal->created_at->lt(now()->subDays(30));

        $extraActions = [];
        if ($proposal->status !== 'Reprovada') {
            $extraActions[] = [
                'icon'    => 'bi-x-circle',
                'href'    => '#',
                'color'   => 'danger',
                'label'   => 'Reprovar',
                'onclick' => "rejectOne({$proposal->id}, this)"
            ];
        }
        @endphp

        <div class="proposal-card-wrapper {{ $isStale ? 'is-stale' : '' }}" data-id="{{ $proposal->id }}">
            @if(request()->boolean('stale'))
            <div class="stale-checkbox-wrap">
                <input type="checkbox" class="stale-checkbox form-check-input" value="{{ $proposal->id }}"
                       onchange="updateSelectedCount()">
            </div>
            @endif

            @if($isStale)
            <div class="stale-badge-inline"><i class="bi bi-clock-history"></i> +30 dias sem resposta</div>
            @endif

            @include('components.admin.item-card', [
            'item'     => $proposal,
            'title'    => $proposal->brand . ' ' . $proposal->model,
            'subtitle' => $proposal->version,
            'image'    => $proposalImage,
            'badges'   => [
                ['text' => $proposal->status, 'color' => $statusColor],
                ['text' => $proposal->proposed_car_year_month ?? 'N/A', 'color' => 'secondary', 'icon' => 'calendar'],
                ['text' => $proposal->proposed_car_mileage ?? 'N/A', 'color' => 'secondary', 'icon' => 'speedometer'],
                ['text' => $proposal->fuel ?? 'N/A', 'color' => 'secondary', 'icon' => 'fuel-pump']
            ],
            'meta' => [
                ['icon' => 'person',         'text' => $proposal->client ? $proposal->client->name : 'Sem cliente'],
                ['icon' => 'calendar-event', 'text' => 'Criada em ' . $proposal->created_at->format('d/m/Y')],
                ['icon' => 'currency-euro',  'text' => number_format($proposal->proposed_car_value ?? 0, 0, ',', '.') . '€']
            ],
            'actions' => array_merge([
                [
                    'icon'   => 'bi-eye',
                    'href'   => route('proposals.detail', ['proposal_code' => $proposal->proposal_code]),
                    'color'  => 'success',
                    'label'  => 'Ver Detalhes',
                    'target' => '_blank'
                ],
                [
                    'icon'  => 'bi-pencil',
                    'href'  => route('admin.v2.proposals.edit', $proposal->id),
                    'color' => 'primary',
                    'label' => 'Editar'
                ],
                [
                    'icon'    => 'bi-trash',
                    'href'    => route('admin.v2.proposals.destroy', $proposal->id),
                    'color'   => 'danger',
                    'label'   => 'Eliminar',
                    'method'  => 'DELETE',
                    'confirm' => 'Tem certeza que deseja eliminar esta cotação?'
                ]
            ], $extraActions)
            ])
        </div>
        @endforeach
    </div>

    <!-- Pagination -->

    @include('components.admin.pagination-footer', [
    'items' => $proposals,
    'label' => 'cotações'
    ])
    @else
    <!-- Estado vazio -->
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
        </div>
        <h4 class="text-muted mb-2">Nenhuma cotação encontrada</h4>
        <p class="text-muted mb-4">
            @if(request()->hasAny(['status', 'client_id', 'search', 'date_from', 'date_to']))
            Não foram encontradas cotações com os filtros aplicados.
            @else
            Comece por criar a sua primeira cotação.
            @endif
        </p>
        @if(request()->hasAny(['status', 'client_id', 'search', 'date_from', 'date_to']))
        <a href="{{ route('admin.v2.proposals.index') }}" class="btn btn-secondary-modern">
            <i class="bi bi-x-circle"></i>
            Limpar Filtros
        </a>
        @else
        <a href="{{ route('admin.v2.proposals.create') }}" class="btn btn-primary-modern">
            <i class="bi bi-plus-lg"></i>
            Criar Primeira Cotação
        </a>
        @endif
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    .proposals-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .item-card {
        animation: fadeInUp 0.3s ease forwards;
        opacity: 0;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .item-card:nth-child(1) { animation-delay: 0.05s; }
    .item-card:nth-child(2) { animation-delay: 0.10s; }
    .item-card:nth-child(3) { animation-delay: 0.15s; }
    .item-card:nth-child(4) { animation-delay: 0.20s; }
    .item-card:nth-child(5) { animation-delay: 0.25s; }

    /* ============================================================
       ALERTA DE COTAÇÕES ANTIGAS
       ============================================================ */
    .stale-alert {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: linear-gradient(135deg, #fff7ed 0%, #fff3e0 100%);
        border: 1px solid #fed7aa;
        border-left: 4px solid #f97316;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }

    .stale-alert-icon {
        font-size: 1.5rem;
        color: #f97316;
        flex-shrink: 0;
    }

    .stale-alert-body {
        flex: 1;
        min-width: 0;
    }

    .stale-alert-body strong {
        display: block;
        font-size: 0.9rem;
        color: #9a3412;
        margin-bottom: 0.1rem;
    }

    .stale-alert-body span {
        font-size: 0.8rem;
        color: #c2410c;
    }

    .stale-alert-actions {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
        flex-wrap: wrap;
    }

    .stale-btn-view {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        background: #fff;
        border: 1.5px solid #f97316;
        color: #f97316;
        text-decoration: none;
        transition: all 0.2s;
        cursor: pointer;
    }
    .stale-btn-view:hover { background: #f97316; color: #fff; }

    .stale-btn-reject {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        background: #dc2626;
        border: 1.5px solid #dc2626;
        color: #fff;
        cursor: pointer;
        transition: all 0.2s;
    }
    .stale-btn-reject:hover { background: #b91c1c; border-color: #b91c1c; }

    .stale-btn-reject-sel {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        background: #fff;
        border: 1.5px solid #dc2626;
        color: #dc2626;
        cursor: pointer;
        transition: all 0.2s;
    }
    .stale-btn-reject-sel:hover { background: #fee2e2; }

    .stale-btn-clear {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.9rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        background: #f1f5f9;
        border: 1.5px solid #e2e8f0;
        color: #64748b;
        text-decoration: none;
        transition: all 0.2s;
    }
    .stale-btn-clear:hover { background: #e2e8f0; }

    /* Barra de filtro stale ativo */
    .stale-filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        background: #fef3c7;
        border: 1px solid #fde68a;
        border-left: 4px solid #f59e0b;
        border-radius: 10px;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #92400e;
        flex-wrap: wrap;
    }

    .stale-filter-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
    }

    /* Wrapper do card com checkbox de seleção */
    .proposal-card-wrapper {
        position: relative;
    }

    .proposal-card-wrapper.is-stale .item-card {
        border-left: 3px solid #f97316 !important;
    }

    .stale-checkbox-wrap {
        position: absolute;
        top: 50%;
        left: -30px;
        transform: translateY(-50%);
        z-index: 5;
    }

    .stale-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        border-color: #f97316;
    }
    .stale-checkbox:checked { background-color: #f97316; border-color: #f97316; }

    .stale-badge-inline {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #c2410c;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 6px;
        padding: 0.2rem 0.6rem;
        margin-bottom: 0.4rem;
    }

    @media (max-width: 768px) {
        .stale-alert { flex-direction: column; align-items: flex-start; }
        .stale-filter-bar { flex-direction: column; align-items: flex-start; }
        .stale-checkbox-wrap { left: 0; top: -16px; transform: none; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(session('success'))
    showToast('{{ session("success") }}', 'success');
    @endif
    @if(session('error'))
    showToast('{{ session("error") }}', 'error');
    @endif
});

const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

function rejectOne(id, btn) {
    if (!confirm('Reprovar esta cotação?')) return;
    btn.disabled = true;

    fetch('{{ route("admin.v2.proposals.updateStatus", ":id") }}'.replace(':id', id), {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ status: 'Reprovada' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const wrapper = btn.closest('.proposal-card-wrapper') || btn.closest('.item-card');
            if (wrapper) {
                wrapper.style.transition = 'opacity 0.4s, transform 0.4s';
                wrapper.style.opacity = '0';
                wrapper.style.transform = 'translateX(20px)';
                setTimeout(() => wrapper.remove(), 450);
            }
            showStaleToast('Cotação reprovada.', 'success');
        }
    })
    .catch(() => { btn.disabled = false; showStaleToast('Erro ao reprovar.', 'error'); });
}

function bulkRejectAll() {
    const total = {{ $proposals->total() ?? 0 }};
    if (!confirm(`Reprovar TODAS as cotações com +30 dias sem resposta (${total} no total)?\n\nEsta ação não pode ser desfeita.`)) return;

    fetch('{{ route("admin.v2.proposals.bulkReject") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ ids: [] })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showStaleToast('Cotações reprovadas com sucesso.', 'success');
            setTimeout(() => window.location.href = '{{ route("admin.v2.proposals.index") }}', 1200);
        }
    })
    .catch(() => showStaleToast('Erro ao reprovar.', 'error'));
}

function rejectSelected() {
    const checked = [...document.querySelectorAll('.stale-checkbox:checked')].map(c => c.value);
    if (!checked.length) return;
    if (!confirm(`Reprovar ${checked.length} cotações selecionadas?`)) return;

    fetch('{{ route("admin.v2.proposals.bulkReject") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ ids: checked })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            checked.forEach(id => {
                const el = document.querySelector(`.proposal-card-wrapper[data-id="${id}"]`);
                if (el) { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }
            });
            updateSelectedCount();
            showStaleToast(`${checked.length} cotações reprovadas.`, 'success');
        }
    })
    .catch(() => showStaleToast('Erro ao reprovar.', 'error'));
}

function updateSelectedCount() {
    const count = document.querySelectorAll('.stale-checkbox:checked').length;
    const el = document.getElementById('selectedCount');
    const btn = document.getElementById('btnRejectSelected');
    if (el) el.textContent = count;
    if (btn) btn.style.display = count > 0 ? 'inline-flex' : 'none';
}

function showStaleToast(msg, type) {
    if (typeof showToast === 'function') { showToast(msg, type); return; }
    alert(msg);
}
</script>
@endpush