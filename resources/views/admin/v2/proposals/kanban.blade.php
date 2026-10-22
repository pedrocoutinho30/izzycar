@extends('layouts.admin-v2')

@section('title', 'Pipeline de Cotações')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-file-earmark-text', 'label' => 'Cotações', 'href' => route('admin.v2.proposals.index')],
        ['icon' => '', 'label' => 'Pipeline Kanban', 'href' => ''],
    ],
    'title'       => 'Pipeline de Cotações',
    'subtitle'    => 'Arraste os cartões entre estados; ao enviar escolhe se o cliente é avisado por email',
    'actionHref'  => route('admin.v2.proposals.create'),
    'actionLabel' => 'Nova Cotação',
])

@include('admin.v2.proposals._view-toggle', ['kanbanActive' => true])

@push('styles')
<style>
    .prk-board { display: grid; grid-template-columns: repeat(5, minmax(190px, 1fr)); gap: .6rem; align-items: start; overflow-x: auto; padding-bottom: .5rem; }
    .prk-col { background: var(--bs-tertiary-bg, #f1f3f5); border-radius: .7rem; padding: .5rem; min-width: 0; }
    .prk-col__header { display: flex; justify-content: space-between; align-items: center; gap: .3rem; padding: .1rem .2rem .5rem; }
    .prk-col__title { font-size: .85rem; font-weight: 700; display: flex; align-items: center; gap: .4rem; }
    .prk-col__dot { width: 9px; height: 9px; border-radius: 50%; }
    .prk-col__count { font-size: .72rem; font-weight: 600; background: rgba(0,0,0,.08); border-radius: 999px; padding: 0 .5rem; }
    .prk-col__body { display: flex; flex-direction: column; gap: .4rem; min-height: 90px; max-height: calc(100vh - 380px); overflow-y: auto; border-radius: .5rem; }
    .prk-col__body.drag-over { background: rgba(13, 110, 253, .1); outline: 2px dashed var(--bs-primary, #0d6efd); outline-offset: -2px; }
    .prk-empty { font-size: .75rem; color: var(--bs-secondary-color, #6c757d); text-align: center; padding: 1rem 0; }
    .prk-more { font-size: .7rem; color: var(--bs-secondary-color, #6c757d); text-align: center; padding: .3rem 0; }
    .prk-card { display: block; background: var(--bs-body-bg, #fff); border: 1px solid rgba(0,0,0,.08); border-radius: .5rem; padding: .4rem .55rem; color: inherit; text-decoration: none; cursor: grab; }
    .prk-card:not([draggable]) { cursor: pointer; }
    .prk-card:hover { border-color: var(--bs-primary, #0d6efd); color: inherit; }
    .prk-card.dragging { opacity: .45; }
    .prk-card.moving { opacity: .6; pointer-events: none; }
    .prk-card__title { font-size: .82rem; font-weight: 600; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .prk-card__sub { font-size: .74rem; color: var(--bs-secondary-color, #6c757d); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .prk-card__foot { display: flex; justify-content: space-between; align-items: center; gap: .3rem; margin-top: .2rem; font-size: .68rem; color: var(--bs-secondary-color, #6c757d); }
    .prk-toast { position: fixed; right: 1rem; bottom: 1rem; z-index: 2000; }
</style>
@endpush

@php
    $colors = ['Pendente' => 'warning', 'Enviado' => 'info', 'Aprovada' => 'success', 'Reprovada' => 'danger', 'Sem resposta' => 'secondary'];
@endphp

<div class="prk-board">
    @foreach($columns as $status => $column)
    <div class="prk-col" data-column="{{ $status }}">
        <div class="prk-col__header">
            <div class="prk-col__title"><span class="prk-col__dot bg-{{ $colors[$status] }}"></span>{{ $status }}</div>
            <span class="prk-col__count" data-count>{{ $column['total'] }}</span>
        </div>
        <div class="prk-col__body" data-drop>
            @forelse($column['items'] as $proposal)
            @include('admin.v2.proposals._kanban-card', ['proposal' => $proposal])
            @empty
            <div class="prk-empty" data-empty>—</div>
            @endforelse
            @if($column['total'] > $column['items']->count())
            <div class="prk-more">+ {{ $column['total'] - $column['items']->count() }} mais na <a href="{{ route('admin.v2.proposals.index', ['status' => $status]) }}">lista</a></div>
            @endif
        </div>
    </div>
    @endforeach
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
        el.className = 'prk-toast alert alert-' + type + ' shadow py-2 px-3 mb-0';
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }

    function refreshColumn(body) {
        const cards = body.querySelectorAll('.prk-card').length;
        const count = body.closest('.prk-col').querySelector('[data-count]');
        count.textContent = Math.max(0, parseInt(count.textContent, 10) + (body._delta || 0));
        body._delta = 0;
        const empty = body.querySelector('[data-empty]');
        if (cards === 0 && !empty) {
            body.insertAdjacentHTML('afterbegin', '<div class="prk-empty" data-empty>—</div>');
        } else if (cards > 0 && empty) {
            empty.remove();
        }
    }

    // O que perguntar ao largar em cada estado (null = mudar sem perguntar).
    function question(status, card) {
        const hasEmail = card.dataset.hasEmail === '1';
        const title = card.dataset.title;
        switch (status) {
            case 'Enviado':
                return { status, title, hasEmail, message: 'Marcar esta cotação como enviada?',
                         notifyLabel: 'Enviar email ao cliente (e avisar o angariador)',
                         hint: hasEmail ? 'Desmarque se já a partilhou por outro canal (ex.: WhatsApp).' : '' };
            case 'Aprovada':
                return { status, title, askNotify: false, message: 'Aceitar esta cotação? Cria a cotação convertida e converte a lead em cliente. Não envia emails nem contrato, e não se pode voltar atrás por aqui.' };
            case 'Reprovada':
                return { status, title, askNotify: false, message: 'Reprovar esta cotação? As oportunidades ligadas passam a "rejeitado".' };
            default:
                return null;
        }
    }

    document.querySelectorAll('.prk-card[draggable]').forEach(card => {
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

            const status = zone.closest('.prk-col').dataset.column;
            const oldBody = card.closest('.prk-col__body');
            if (oldBody === zone) return;

            let notify = true;
            const ask = question(status, card);
            if (ask) {
                const answer = await window.askStatusChange(ask);
                if (!answer.confirmed) return;
                notify = answer.notify;
            }

            card.classList.add('moving');
            try {
                const res = await fetch(card.dataset.statusUrl, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ status, notify }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) {
                    showToast(data.message || 'Não foi possível mudar o estado.', 'danger');
                    return;
                }

                zone.querySelector('[data-empty]')?.remove();
                zone.insertBefore(card, zone.firstChild);
                oldBody._delta = -1;
                zone._delta = 1;
                refreshColumn(oldBody);
                refreshColumn(zone);

                if (status === 'Aprovada') {
                    card.removeAttribute('draggable');
                    showToast('Cotação aceite · cotação convertida criada', 'success');
                } else if (status === 'Enviado') {
                    showToast('Cotação enviada' + (data.notified ? ' · Email enviado ao cliente' : ' · Cliente não notificado'), 'success');
                } else {
                    showToast('Estado atualizado', 'success');
                }
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
