@extends('layouts.admin-v2')

@section('title', 'Pipeline de Leads')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Leads', 'href' => route('admin.v2.leads.index')],
        ['icon' => '', 'label' => 'Pipeline Kanban']
    ],
    'title' => 'Pipeline de Leads',
    'subtitle' => 'Arraste os cartões entre colunas para actualizar o estado',
    'actionHref' => '',
    'actionLabel' => ''
])

{{-- Toggle lista / kanban --}}
<div class="d-flex justify-content-end mb-3 gap-2">
    <a href="{{ route('admin.v2.leads.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-list-ul me-1"></i> Vista de Lista
    </a>
    <a href="{{ route('admin.v2.leads.kanban') }}" class="btn btn-primary-modern btn-sm active">
        <i class="bi bi-kanban me-1"></i> Pipeline Kanban
    </a>
</div>

{{-- Sessão de feedback --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Board kanban --}}
<div class="kanban-board">
    @foreach($columns as $statusKey => $col)
    @php $colLeads = $leads->get($statusKey, collect()); @endphp
    <div class="kanban-col" data-status="{{ $statusKey }}">

        {{-- Cabeçalho da coluna --}}
        <div class="kanban-col__header">
            <div class="kanban-col__title">
                <span class="kanban-col__dot bg-{{ $col['color'] }}"></span>
                {{ $col['label'] }}
            </div>
            <span class="kanban-col__count">{{ $colLeads->count() }}</span>
        </div>

        {{-- Zona de drop --}}
        <div class="kanban-col__body" id="col-{{ $statusKey }}">
            @forelse($colLeads as $lead)
            @php
                $sourceLabels = [
                    'simulador'  => ['label' => 'Simulador', 'color' => 'info'],
                    'importacao' => ['label' => 'Importação', 'color' => 'primary'],
                    'retoma'     => ['label' => 'Retoma',    'color' => 'warning'],
                    'manual'     => ['label' => 'Manual',    'color' => 'secondary'],
                ];
                $src = $sourceLabels[$lead->lead_source] ?? ['label' => 'Outro', 'color' => 'secondary'];
                $lastActivity = $lead->activities->first();
            @endphp
            @php
                $fu = $lead->next_followup_at;
                $fuBadge = null;
                if ($fu) {
                    if ($fu->isPast())       $fuBadge = ['label' => 'Atraso', 'color' => 'danger'];
                    elseif ($fu->isToday())  $fuBadge = ['label' => 'Hoje',   'color' => 'warning'];
                }
            @endphp
            <div class="kanban-card {{ $fuBadge ? 'kanban-card--alert' : '' }}" data-id="{{ $lead->id }}" draggable="true"
                 title="{{ $lead->name }}{{ $lead->phone ? ' · ' . $lead->phone : '' }}{{ $lead->email ? ' · ' . $lead->email : '' }}">
                <div class="kanban-card__top">
                    <div class="kanban-card__name">{{ $lead->name }}</div>
                    <a href="{{ route('admin.v2.leads.show', $lead->id) }}"
                       class="kanban-card__link" title="Abrir lead">
                        <i class="bi bi-arrow-up-right-square"></i>
                    </a>
                </div>

                <div class="kanban-card__meta">
                    <span class="kanban-card__contact">
                        @if($lead->phone)<i class="bi bi-telephone"></i> {{ $lead->phone }}
                        @elseif($lead->email)<i class="bi bi-envelope"></i> {{ $lead->email }}
                        @else<i class="bi bi-calendar3"></i> {{ $lead->created_at->format('d/m/Y') }}@endif
                    </span>
                    @if($fuBadge)
                    <span class="badge bg-{{ $fuBadge['color'] }} badge-sm"><i class="bi bi-alarm"></i> {{ $fuBadge['label'] }}</span>
                    @elseif($fu)
                    <span class="kanban-card__fu"><i class="bi bi-alarm"></i> {{ $fu->format('d/m') }}</span>
                    @endif
                </div>

                <div class="kanban-card__meta">
                    <span class="badge bg-{{ $src['color'] }} badge-sm">{{ $src['label'] }}</span>
                    @if($lastActivity)
                    <span class="kanban-card__last" title="{{ $lastActivity->title }} · {{ $lastActivity->created_at->diffForHumans() }}">
                        {{ $lastActivity->created_at->diffForHumans(null, true) }}
                    </span>
                    @endif
                </div>
            </div>
            @empty
            <div class="kanban-col__empty">
                <i class="bi bi-inbox"></i>
                <span>Sem leads</span>
            </div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

{{-- Form oculto para actualizar estado via drag & drop --}}
<form id="status-form" method="POST" style="display:none">
    @csrf
    <input type="hidden" name="status" id="status-input">
</form>

@endsection

@push('styles')
<style>
/* ── Board: colunas estreitas lado a lado (kanban normal) ────────────────── */
.kanban-board {
    display: flex;
    gap: .6rem;
    align-items: flex-start;
    overflow-x: auto;
    padding-bottom: 1.5rem;
}

/* ── Coluna ──────────────────────────────────────────────────────────────── */
.kanban-col {
    background: #f5f6f8;
    border-radius: 10px;
    min-height: 240px;
    display: flex;
    flex-direction: column;
    flex: 0 0 220px;
    width: 220px;
}
.kanban-col__header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .5rem .7rem;
    border-bottom: 1px solid #e8e8e8;
}
.kanban-col__title {
    display: flex; align-items: center; gap: .4rem;
    font-size: .82rem; font-weight: 700; color: #333;
}
.kanban-col__dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.kanban-col__count {
    background: #e0e0e0; color: #555;
    font-size: .7rem; font-weight: 700;
    padding: .05rem .42rem; border-radius: 20px;
}
.kanban-col__body {
    padding: .45rem;
    display: flex; flex-direction: column; gap: .35rem;
    flex: 1;
    min-height: 100px;
    transition: background .15s;
}
.kanban-col__body.drag-over { background: rgba(13,110,253,.06); border-radius: 8px; }
.kanban-col__empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: .3rem; padding: 1.2rem 1rem;
    color: #bbb; font-size: .75rem;
}
.kanban-col__empty i { font-size: 1.2rem; }

/* ── Cartão compacto (~metade da altura anterior) ─────────────────────────── */
.kanban-card {
    background: #fff;
    border-radius: 7px;
    padding: .4rem .55rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.07);
    cursor: grab;
    transition: box-shadow .15s, transform .15s;
    border: 1px solid #eee;
}
.kanban-card:active { cursor: grabbing; }
.kanban-card.dragging { opacity: .45; transform: scale(.97); }
.kanban-card__top { display: flex; align-items: center; gap: .35rem; }
.kanban-card__name { flex: 1; min-width: 0; font-weight: 700; font-size: .8rem; color: #111; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kanban-card__link { color: #aaa; font-size: .85rem; text-decoration: none; flex-shrink: 0; transition: color .15s; }
.kanban-card__link:hover { color: var(--admin-primary); }
.kanban-card__meta { display: flex; align-items: center; justify-content: space-between; gap: .4rem; font-size: .7rem; color: #666; margin-top: .1rem; }
.kanban-card__contact { min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kanban-card__fu { flex-shrink: 0; color: #888; }
.kanban-card__last { font-size: .66rem; color: #888; white-space: nowrap; }
.kanban-card__meta:last-child { margin-top: .2rem; }
.badge-sm { font-size: .6rem; padding: .15rem .35rem; flex-shrink: 0; }

/* ── Indicador de loading no card ────────────────────────────────────────── */
.kanban-card.moving { opacity: .6; pointer-events: none; }

/* ── Follow-up em atraso / hoje ─────────────────────────────────────────── */
.kanban-card--alert { border-left: 3px solid #dc3545; }
</style>
@endpush

@push('scripts')
<script>
/**
 * Kanban drag & drop
 * Ao largar um cartão numa coluna diferente, envia POST para actualizar o estado
 * e move o cartão no DOM sem recarregar a página.
 */
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let draggedCard = null;

    // ── Eventos dos cartões ───────────────────────────────────────────────
    document.querySelectorAll('.kanban-card').forEach(card => {
        card.addEventListener('dragstart', e => {
            draggedCard = card;
            card.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            draggedCard = null;
        });
    });

    // ── Eventos das colunas ───────────────────────────────────────────────
    document.querySelectorAll('.kanban-col__body').forEach(col => {
        col.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            col.classList.add('drag-over');
        });
        col.addEventListener('dragleave', () => col.classList.remove('drag-over'));
        col.addEventListener('drop', async e => {
            e.preventDefault();
            col.classList.remove('drag-over');

            if (!draggedCard) return;

            const newStatus = col.closest('.kanban-col').dataset.status;
            const leadId    = draggedCard.dataset.id;
            const oldCol    = draggedCard.closest('.kanban-col__body');

            // Não fazer nada se largar na mesma coluna
            if (oldCol === col) return;

            // Mover o cartão visualmente de imediato
            draggedCard.classList.add('moving');
            col.appendChild(draggedCard);
            updateColCounts();

            try {
                const res = await fetch(`/gestao/v2/leads/${leadId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus }),
                });

                if (!res.ok) throw new Error('Erro ao actualizar estado');

                showToast('Estado actualizado com sucesso', 'success');
            } catch (err) {
                // Reverter em caso de erro
                oldCol.appendChild(draggedCard);
                updateColCounts();
                showToast('Erro ao actualizar estado. Tente novamente.', 'danger');
            } finally {
                draggedCard.classList.remove('moving');
            }
        });
    });

    // ── Actualizar contadores das colunas ─────────────────────────────────
    function updateColCounts() {
        document.querySelectorAll('.kanban-col').forEach(col => {
            const count = col.querySelectorAll('.kanban-card').length;
            col.querySelector('.kanban-col__count').textContent = count;

            // Mostrar/esconder mensagem "Sem leads"
            let empty = col.querySelector('.kanban-col__empty');
            if (count === 0 && !empty) {
                empty = document.createElement('div');
                empty.className = 'kanban-col__empty';
                empty.innerHTML = '<i class="bi bi-inbox"></i><span>Sem leads</span>';
                col.querySelector('.kanban-col__body').appendChild(empty);
            } else if (count > 0 && empty) {
                empty.remove();
            }
        });
    }

    // ── Toast de feedback ─────────────────────────────────────────────────
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `kanban-toast kanban-toast--${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('show'); }, 10);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
    }
})();
</script>
@endpush

@push('styles')
<style>
/* ── Toast de feedback ───────────────────────────────────────────────────── */
.kanban-toast {
    position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
    padding: .75rem 1.25rem; border-radius: 8px;
    font-size: .88rem; font-weight: 600; color: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,.15);
    opacity: 0; transform: translateY(10px);
    transition: opacity .3s, transform .3s;
    pointer-events: none;
}
.kanban-toast.show { opacity: 1; transform: translateY(0); }
.kanban-toast--success { background: #198754; }
.kanban-toast--danger  { background: #dc3545; }
</style>
@endpush
