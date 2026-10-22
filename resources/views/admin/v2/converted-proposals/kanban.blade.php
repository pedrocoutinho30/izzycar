@extends('layouts.admin-v2')

@section('title', 'Pipeline de Cotações Convertidas')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-check2-circle', 'label' => 'Cotações Convertidas', 'href' => route('admin.v2.converted-proposals.index')],
        ['icon' => '', 'label' => 'Pipeline Kanban', 'href' => ''],
    ],
    'title'    => 'Pipeline de Cotações Convertidas',
    'subtitle' => 'Arraste os cartões entre etapas; ao largar escolhe se o cliente é avisado por email',
])

@include('admin.v2.converted-proposals._view-toggle', ['kanbanActive' => true])

@push('styles')
<style>
    .cpk-board { display: grid; grid-template-columns: repeat(11, minmax(125px, 1fr)); gap: .4rem; align-items: start; overflow-x: auto; padding-bottom: .5rem; }
    .cpk-col { background: var(--bs-tertiary-bg, #f1f3f5); border-radius: .6rem; padding: .35rem; min-width: 0; }
    .cpk-col__header { display: flex; justify-content: space-between; align-items: flex-start; gap: .25rem; padding: .1rem .15rem .35rem; }
    .cpk-col__title { font-size: .72rem; font-weight: 700; line-height: 1.15; }
    .cpk-col__count { font-size: .66rem; font-weight: 600; background: rgba(0,0,0,.08); border-radius: 999px; padding: 0 .4rem; flex: none; }
    .cpk-col__body { display: flex; flex-direction: column; gap: .3rem; min-height: 80px; max-height: calc(100vh - 400px); overflow-y: auto; border-radius: .4rem; }
    .cpk-col__body.drag-over, .cpk-zone.drag-over { background: rgba(13, 110, 253, .1); outline: 2px dashed var(--bs-primary, #0d6efd); outline-offset: -2px; }
    .cpk-empty { font-size: .7rem; color: var(--bs-secondary-color, #6c757d); text-align: center; padding: .8rem 0; }
    .cpk-card { display: block; background: var(--bs-body-bg, #fff); border: 1px solid rgba(0,0,0,.08); border-radius: .45rem; padding: .3rem .4rem; color: inherit; text-decoration: none; cursor: grab; }
    .cpk-card:hover { border-color: var(--bs-primary, #0d6efd); color: inherit; }
    .cpk-card.dragging { opacity: .45; }
    .cpk-card.moving { opacity: .6; pointer-events: none; }
    .cpk-card__title { font-size: .75rem; font-weight: 600; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cpk-card__sub { font-size: .68rem; color: var(--bs-secondary-color, #6c757d); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cpk-zones { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin-top: .75rem; }
    .cpk-zone { border: 2px dashed; border-radius: .6rem; padding: .8rem; text-align: center; font-weight: 600; }
    .cpk-zone small { display: block; font-weight: 400; color: var(--bs-secondary-color, #6c757d); }
    .cpk-zone--done { border-color: rgba(25,135,84,.55); color: var(--bs-success, #198754); background: rgba(25,135,84,.05); }
    .cpk-zone--cancelled { border-color: rgba(220,53,69,.55); color: var(--bs-danger, #dc3545); background: rgba(220,53,69,.05); }
    .cpk-toast { position: fixed; right: 1rem; bottom: 1rem; z-index: 2000; }
</style>
@endpush

<div class="cpk-board">
    @foreach($columns as $status)
    @php $items = $grouped->get($status, collect()); @endphp
    <div class="cpk-col" data-column="{{ $status }}">
        <div class="cpk-col__header">
            <div class="cpk-col__title">{{ $status }}</div>
            <span class="cpk-col__count" data-count>{{ $items->count() }}</span>
        </div>
        <div class="cpk-col__body" data-drop>
            @forelse($items as $converted)
            @include('admin.v2.converted-proposals._kanban-card', ['converted' => $converted])
            @empty
            <div class="cpk-empty" data-empty>—</div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

<div class="cpk-zones">
    <div class="cpk-zone cpk-zone--done" data-column="Concluido" data-drop>
        <i class="bi bi-check-circle-fill"></i> Concluídas
        <small>Largue aqui para concluir (<span data-zone-count>{{ $doneCount }}</span> já concluídas)</small>
    </div>
    <div class="cpk-zone cpk-zone--cancelled" data-column="Cancelada" data-drop>
        <i class="bi bi-x-circle-fill"></i> Canceladas
        <small>Largue aqui para cancelar (<span data-zone-count>{{ $cancelledCount }}</span> já canceladas)</small>
    </div>
</div>

@include('admin.v2.converted-proposals._status-modal')

@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let dragged = null;

    function showToast(message, type) {
        const el = document.createElement('div');
        el.className = 'cpk-toast alert alert-' + type + ' shadow py-2 px-3 mb-0';
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }

    function refreshColumn(body) {
        const col = body.closest('.cpk-col');
        if (!col) return;
        const cards = body.querySelectorAll('.cpk-card').length;
        col.querySelector('[data-count]').textContent = cards;
        const empty = body.querySelector('[data-empty]');
        if (cards === 0 && !empty) {
            body.insertAdjacentHTML('beforeend', '<div class="cpk-empty" data-empty>—</div>');
        } else if (cards > 0 && empty) {
            empty.remove();
        }
    }

    document.querySelectorAll('.cpk-card').forEach(card => {
        card.addEventListener('dragstart', e => {
            dragged = card;
            card.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.id);
        });
        card.addEventListener('dragend', () => { card.classList.remove('dragging'); dragged = null; });
    });

    document.querySelectorAll('[data-drop]').forEach(zone => {
        zone.addEventListener('dragover', e => {
            if (!dragged) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            zone.classList.add('drag-over');
        });
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
        zone.addEventListener('drop', async e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            const card = dragged;
            if (!card) return;

            const status = (zone.closest('[data-column]') || zone).dataset.column;
            const oldBody = card.closest('.cpk-col__body');
            if (oldBody === zone) return;

            const answer = await window.askStatusChange({
                status,
                from: oldBody.closest('.cpk-col').dataset.column,
                hasEmail: card.dataset.hasEmail === '1',
                title: card.dataset.title,
            });
            if (!answer.confirmed) return;

            card.classList.add('moving');
            try {
                const res = await fetch(card.dataset.statusUrl, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ status, notify: answer.notify }),
                });
                if (!res.ok) throw new Error();
                const data = await res.json();

                if (zone.querySelector('[data-zone-count]')) {
                    card.remove();
                    const counter = zone.querySelector('[data-zone-count]');
                    counter.textContent = parseInt(counter.textContent, 10) + 1;
                } else {
                    zone.appendChild(card);
                    refreshColumn(zone);
                }
                refreshColumn(oldBody);
                showToast('Estado atualizado' + (data.notified ? ' · Email enviado ao cliente' : ' · Cliente não notificado'), 'success');
            } catch (err) {
                showToast('Não foi possível mudar o estado. Tente novamente.', 'danger');
            } finally {
                card.classList.remove('moving');
            }
        });
    });
})();
</script>
@endpush
