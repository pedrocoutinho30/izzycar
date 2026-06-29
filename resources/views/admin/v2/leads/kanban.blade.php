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
            <div class="kanban-card {{ $fuBadge ? 'kanban-card--alert' : '' }}" data-id="{{ $lead->id }}" draggable="true">
                <div class="kanban-card__top">
                    <div class="kanban-card__avatar">{{ strtoupper(substr($lead->name, 0, 1)) }}</div>
                    <div class="kanban-card__info">
                        <div class="kanban-card__name">{{ $lead->name }}</div>
                        <span class="badge bg-{{ $src['color'] }} badge-sm">{{ $src['label'] }}</span>
                    </div>
                    <a href="{{ route('admin.v2.leads.show', $lead->id) }}"
                       class="kanban-card__link" title="Abrir lead">
                        <i class="bi bi-arrow-up-right-square"></i>
                    </a>
                </div>

                @if($lead->phone || $lead->email)
                <div class="kanban-card__contact">
                    @if($lead->phone)
                    <span><i class="bi bi-telephone"></i> {{ $lead->phone }}</span>
                    @endif
                    @if($lead->email)
                    <span class="text-truncate"><i class="bi bi-envelope"></i> {{ $lead->email }}</span>
                    @endif
                </div>
                @endif

                @if($lastActivity)
                <div class="kanban-card__last">
                    <i class="bi bi-clock text-muted"></i>
                    {{ $lastActivity->title }} · {{ $lastActivity->created_at->diffForHumans() }}
                </div>
                @endif

                <div class="kanban-card__footer">
                    <span class="text-muted" style="font-size:.7rem">
                        <i class="bi bi-calendar3"></i> {{ $lead->created_at->format('d/m/Y') }}
                    </span>
                    @if($fuBadge)
                    <span class="badge bg-{{ $fuBadge['color'] }}" style="font-size:.65rem">
                        <i class="bi bi-alarm me-1"></i>{{ $fuBadge['label'] }}
                    </span>
                    @elseif($fu)
                    <span class="text-muted" style="font-size:.7rem">
                        <i class="bi bi-alarm"></i> {{ $fu->format('d/m') }}
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
/* ── Board ───────────────────────────────────────────────────────────────── */
.kanban-board {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    align-items: start;
    padding-bottom: 2rem;
}
@media (max-width: 1100px) { .kanban-board { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px)  { .kanban-board { grid-template-columns: 1fr; } }

/* ── Coluna ──────────────────────────────────────────────────────────────── */
.kanban-col {
    background: #f5f6f8;
    border-radius: 12px;
    min-height: 300px;
    display: flex;
    flex-direction: column;
}
.kanban-col__header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .75rem 1rem;
    border-bottom: 1px solid #e8e8e8;
}
.kanban-col__title {
    display: flex; align-items: center; gap: .5rem;
    font-size: .85rem; font-weight: 700; color: #333;
}
.kanban-col__dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
}
.kanban-col__count {
    background: #e0e0e0; color: #555;
    font-size: .72rem; font-weight: 700;
    padding: .1rem .45rem; border-radius: 20px;
}
.kanban-col__body {
    padding: .75rem;
    display: flex; flex-direction: column; gap: .6rem;
    flex: 1;
    min-height: 100px;
    transition: background .15s;
}
.kanban-col__body.drag-over {
    background: rgba(13,110,253,.06);
    border-radius: 8px;
}
.kanban-col__empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: .4rem; padding: 2rem 1rem;
    color: #bbb; font-size: .8rem;
}
.kanban-col__empty i { font-size: 1.5rem; }

/* ── Cartão ──────────────────────────────────────────────────────────────── */
.kanban-card {
    background: #fff;
    border-radius: 10px;
    padding: .9rem 1rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
    cursor: grab;
    transition: box-shadow .15s, transform .15s;
    border: 1px solid #eee;
}
.kanban-card:active { cursor: grabbing; }
.kanban-card.dragging {
    opacity: .45;
    transform: scale(.97);
}
.kanban-card__top {
    display: flex; align-items: center; gap: .6rem; margin-bottom: .6rem;
}
.kanban-card__avatar {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--admin-primary), #990000);
    color: #fff; font-size: .85rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}
.kanban-card__info { flex: 1; min-width: 0; }
.kanban-card__name { font-weight: 700; font-size: .88rem; color: #111; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kanban-card__link {
    color: #aaa; font-size: 1rem; text-decoration: none; flex-shrink: 0;
    transition: color .15s;
}
.kanban-card__link:hover { color: var(--admin-primary); }
.kanban-card__contact {
    display: flex; flex-direction: column; gap: .15rem;
    font-size: .75rem; color: #666; margin-bottom: .5rem;
}
.kanban-card__contact span { display: flex; align-items: center; gap: .3rem; overflow: hidden; }
.kanban-card__last {
    font-size: .72rem; color: #888; background: #f8f8f8;
    border-radius: 6px; padding: .3rem .5rem;
    margin-bottom: .5rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.kanban-card__footer {
    display: flex; justify-content: flex-end;
}
.badge-sm { font-size: .65rem; padding: .2rem .45rem; }

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
