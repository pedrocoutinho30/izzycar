@extends('layouts.admin-v2')

@section('title', 'Pipeline de Legalizações')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-file-earmark-check', 'label' => 'Legalizações', 'href' => route('admin.legalizations.index')],
        ['icon' => '', 'label' => 'Pipeline Kanban', 'href' => ''],
    ],
    'title'       => 'Pipeline de Legalizações',
    'subtitle'    => 'Arraste os cartões entre colunas para mudar o passo em curso; largue em "Concluídas" para concluir',
    'actionHref'  => route('admin.legalizations.create'),
    'actionLabel' => 'Nova Legalização',
])

@include('admin.v2.legalizations._view-toggle', ['kanbanActive' => true])

<style>
    .lgk-board { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: .75rem; align-items: start; }
    @media (max-width: 1199.98px) {
        .lgk-board { grid-template-columns: repeat(7, minmax(190px, 1fr)); overflow-x: auto; padding-bottom: .5rem; }
    }
    .lgk-col { background: var(--bs-tertiary-bg, #f1f3f5); border-radius: .75rem; padding: .6rem; min-width: 0; }
    .lgk-col__header { display: flex; justify-content: space-between; align-items: center; gap: .4rem; padding: .15rem .25rem .6rem; }
    .lgk-col__title { font-size: .85rem; font-weight: 600; line-height: 1.2; display: flex; gap: .4rem; align-items: center; min-width: 0; }
    .lgk-col__step { display: inline-flex; align-items: center; justify-content: center; width: 1.35rem; height: 1.35rem; flex: none; border-radius: 50%; background: var(--bs-primary, #0d6efd); color: #fff; font-size: .72rem; }
    .lgk-col__count { font-size: .75rem; font-weight: 600; background: rgba(0,0,0,.08); border-radius: 999px; padding: .05rem .55rem; }
    .lgk-col__body { display: flex; flex-direction: column; gap: .5rem; min-height: 90px; max-height: calc(100vh - 420px); overflow-y: auto; border-radius: .5rem; transition: background .15s; }
    .lgk-col__body.drag-over, .lgk-done.drag-over { background: rgba(13, 110, 253, .1); outline: 2px dashed var(--bs-primary, #0d6efd); outline-offset: -2px; }
    .lgk-col__empty { font-size: .8rem; color: var(--bs-secondary-color, #6c757d); text-align: center; padding: 1.2rem 0; }
    .lgk-card { display: block; background: var(--bs-body-bg, #fff); border: 1px solid rgba(0,0,0,.08); box-shadow: 0 1px 2px rgba(0,0,0,.05); border-radius: .6rem; padding: .65rem .75rem; color: inherit; text-decoration: none; cursor: grab; }
    .lgk-card:hover { border-color: var(--bs-primary, #0d6efd); color: inherit; }
    .lgk-card.dragging { opacity: .45; }
    .lgk-card.moving { opacity: .6; pointer-events: none; }
    .lgk-card__title { font-size: .92rem; font-weight: 600; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .lgk-card__sub { font-size: .8rem; color: var(--bs-secondary-color, #6c757d); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .lgk-card__plate { font-size: .72rem; font-family: var(--bs-font-monospace, monospace); color: var(--bs-secondary-color, #6c757d); }
    .lgk-card__foot { display: flex; align-items: center; gap: .5rem; margin-top: .45rem; font-size: .74rem; color: var(--bs-secondary-color, #6c757d); }
    .lgk-card__foot .progress { flex: 1; height: 6px; }
    .lgk-done { margin-top: 1rem; border: 2px dashed rgba(25, 135, 84, .55); border-radius: .75rem; padding: 1rem; text-align: center; color: var(--bs-success, #198754); font-weight: 600; background: rgba(25, 135, 84, .05); }
    .lgk-done small { display: block; font-weight: 400; color: var(--bs-secondary-color, #6c757d); }
    .lgk-toast { position: fixed; right: 1rem; bottom: 1rem; z-index: 2000; }
</style>

<div class="lgk-board">
    @foreach($columns as $step => $col)
    @php $items = $grouped->get($step, collect()); @endphp
    <div class="lgk-col" data-column="{{ $step }}">
        <div class="lgk-col__header" title="{{ $col['title'] }}">
            <div class="lgk-col__title">
                <span class="lgk-col__step">{{ $step }}</span>
                <span>{{ $col['label'] }}</span>
            </div>
            <span class="lgk-col__count" data-count>{{ $items->count() }}</span>
        </div>
        <div class="lgk-col__body" data-drop>
            @forelse($items as $leg)
            @php
                $progress = $leg->progressPercent();
                $color = $progress > 0 ? 'info' : 'secondary';
            @endphp
            <a href="{{ route('admin.legalizations.show', $leg) }}" class="lgk-card" draggable="true"
               data-id="{{ $leg->id }}" data-move-url="{{ route('admin.legalizations.move', $leg) }}"
               title="{{ trim($leg->marca . ' ' . $leg->modelo) }}{{ $leg->client ? ' · ' . $leg->client->name : '' }}">
                <div class="lgk-card__title">{{ trim($leg->marca . ' ' . $leg->modelo) ?: 'Sem viatura' }}</div>
                <div class="lgk-card__sub">{{ $leg->client?->name ?? 'Sem cliente' }}</div>
                @if($leg->matricula)<div class="lgk-card__plate">{{ $leg->matricula }}</div>@endif
                <div class="lgk-card__foot">
                    <div class="progress"><div class="progress-bar bg-{{ $color }}" data-bar style="width: {{ $progress }}%"></div></div>
                    <span data-steps>{{ count($leg->steps_completed ?? []) }}/7</span>
                    <span><i class="bi bi-paperclip"></i>{{ $leg->documents_count }}</span>
                </div>
            </a>
            @empty
            <div class="lgk-col__empty" data-empty>—</div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

<div class="lgk-done" id="lgkDone" data-column="done" data-drop>
    <i class="bi bi-check-circle-fill"></i> Concluídas
    <small>Largue aqui um cartão para o concluir (<span id="lgkDoneCount">{{ $doneCount }}</span> já concluídas — ver na vista de lista)</small>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let dragged = null;

    function showToast(message, type) {
        const el = document.createElement('div');
        el.className = 'lgk-toast alert alert-' + type + ' shadow py-2 px-3 mb-0';
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }

    function refreshColumn(body) {
        const col = body.closest('.lgk-col');
        if (!col) return;
        const cards = body.querySelectorAll('.lgk-card').length;
        col.querySelector('[data-count]').textContent = cards;
        const empty = body.querySelector('[data-empty]');
        if (cards === 0 && !empty) {
            body.insertAdjacentHTML('beforeend', '<div class="lgk-col__empty" data-empty>—</div>');
        } else if (cards > 0 && empty) {
            empty.remove();
        }
    }

    document.querySelectorAll('.lgk-card').forEach(card => {
        card.addEventListener('dragstart', e => {
            dragged = card;
            card.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.id);
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            dragged = null;
        });
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

            const holder = zone.closest('[data-column]');
            const column = holder.dataset.column;
            const oldBody = card.closest('.lgk-col__body');
            if (oldBody === zone) return;

            // Voltar atrás desmarca passos já concluídos: pedir confirmação.
            const from = parseInt(oldBody.closest('.lgk-col').dataset.column, 10);
            if (column !== 'done' && parseInt(column, 10) < from
                && !confirm('Mover para um passo anterior desmarca os passos seguintes. Continuar?')) {
                return;
            }

            card.classList.add('moving');
            try {
                const res = await fetch(card.dataset.moveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ column }),
                });
                if (!res.ok) throw new Error();
                const data = await res.json();

                if (column === 'done') {
                    card.remove();
                    const counter = document.getElementById('lgkDoneCount');
                    counter.textContent = parseInt(counter.textContent, 10) + 1;
                    showToast('Legalização concluída', 'success');
                } else {
                    zone.appendChild(card);
                    card.querySelector('[data-steps]').textContent = data.steps + '/7';
                    const bar = card.querySelector('[data-bar]');
                    bar.style.width = data.progress + '%';
                    bar.className = 'progress-bar bg-' + (data.progress > 0 ? 'info' : 'secondary');
                    showToast(data.task_created ? 'Movida. Criada a tarefa de entrega do Modelo 9.' : 'Passo atualizado', 'success');
                }
                refreshColumn(oldBody);
                if (column !== 'done') refreshColumn(zone);
            } catch (err) {
                showToast('Não foi possível mover a legalização. Tente novamente.', 'danger');
            } finally {
                card.classList.remove('moving');
            }
        });
    });
})();
</script>
@endpush
