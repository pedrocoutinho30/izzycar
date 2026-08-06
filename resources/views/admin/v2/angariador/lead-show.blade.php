@extends('layouts.admin-v2')

@section('title', 'Lead — ' . $client->name)

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Início', 'href' => route('admin.angariador.dashboard')],
        ['icon' => '', 'label' => 'As Minhas Leads', 'href' => route('admin.angariador.leads')],
        ['icon' => '', 'label' => $client->name],
    ],
    'title' => $client->name,
    'subtitle' => 'Registado em ' . $client->created_at->format('d/m/Y'),
])

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Propostas — apenas visível depois de a equipa criar uma proposta --}}
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-file-earmark-text"></i> Proposta enviada ao cliente</h5>
            </div>

            @if($client->proposals->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-hourglass-split fs-2 d-block mb-2"></i>
                    Esta lead ainda está a ser tratada pela nossa equipa. Quando for enviada uma proposta ao cliente, verá aqui o respetivo estado e link.
                </div>
            @else
                @foreach($client->proposals->sortByDesc('created_at') as $proposal)
                @php
                    $pStatusColors = ['Pendente' => 'warning', 'Aprovada' => 'success', 'Reprovada' => 'danger', 'Enviado' => 'info', 'Sem resposta' => 'secondary'];
                    $pColor = $pStatusColors[$proposal->status] ?? 'secondary';
                @endphp
                <div class="lead-activity-item align-items-center">
                    <div class="lad-icon" style="background:rgba(13,110,253,.1);color:#0d6efd;">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="lad-body">
                        <div class="lad-title">{{ trim(($proposal->brand ?? '') . ' ' . ($proposal->model ?? '—')) }}</div>
                        <div class="lad-meta">Enviada em {{ $proposal->created_at->format('d/m/Y') }}</div>
                        <div class="lad-tags">
                            <span class="lad-tag badge bg-{{ $pColor }}">{{ $proposal->status ?? 'Pendente' }}</span>
                        </div>
                    </div>
                    @if($proposal->proposal_code)
                    <a href="{{ route('proposals.detail', $proposal->proposal_code) }}" target="_blank"
                       class="btn btn-icon btn-primary-modern ms-auto flex-shrink-0" title="Ver proposta (igual à que o cliente recebeu)">
                        <i class="bi bi-arrow-up-right-square"></i>
                    </a>
                    @endif
                </div>
                @endforeach
            @endif
        </div>

        {{-- Timeline & Notas ─────────────────────────────────────────── --}}
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-clock-history"></i> Timeline & Notas</h5>
            </div>

            {{-- Formulário para registar nova actividade --}}
            <div class="timeline-add-form">
                <form action="{{ route('admin.angariador.leads.activity', $client->id) }}" method="POST">
                    @csrf
                    <div class="timeline-type-tabs" role="group">
                        @foreach(\App\Models\LeadActivity::TYPES as $key => $cfg)
                        @if($key !== 'system')
                        <label class="type-tab {{ $loop->first ? 'active' : '' }}">
                            <input type="radio" name="type" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }}>
                            <i class="{{ $cfg['icon'] }}"></i> {{ $cfg['label'] }}
                        </label>
                        @endif
                        @endforeach
                    </div>

                    <input type="text" name="title" class="form-control mt-2 mb-2"
                        placeholder="Resumo (ex: Liguei, não atendeu / Enviou email com interesse no BMW 320d)"
                        required maxlength="255">

                    <textarea name="body" class="form-control mb-2" rows="2"
                        placeholder="Detalhe adicional (opcional)..." maxlength="2000"></textarea>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-sm btn-primary-modern">
                            <i class="bi bi-plus-circle me-1"></i> Registar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Lista de actividades --}}
            @if($client->activities->isNotEmpty())
            <div class="timeline-list">
                @foreach($client->activities as $act)
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
                            @if($act->user)
                            · <strong>{{ $act->user->name }}</strong>
                            @else
                            · <span class="text-muted">Sistema</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-4 text-center text-muted small">
                <i class="bi bi-clock-history d-block fs-4 mb-1"></i>
                Sem actividade registada. Use o formulário acima para adicionar a primeira nota.
            </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-person"></i> Dados da Lead</h5>
            </div>
            <div class="lead-info-list">
                @if($client->email)
                <div class="lil-row"><i class="bi bi-envelope text-muted"></i><span>{{ $client->email }}</span></div>
                @endif
                @if($client->phone)
                <div class="lil-row"><i class="bi bi-telephone text-muted"></i><span>{{ $client->phone }}</span></div>
                @endif
                <div class="lil-row"><i class="bi bi-calendar text-muted"></i><span>Registado em {{ $client->created_at->format('d/m/Y') }}</span></div>
            </div>
        </div>

        {{-- Follow-up --}}
        @php
            $followupDate = $client->next_followup_at;
            $followupState = null;
            if ($followupDate) {
                if ($followupDate->isPast())         $followupState = 'atraso';
                elseif ($followupDate->isToday())    $followupState = 'hoje';
                else                                 $followupState = 'agendado';
            }
            $followupColors = ['atraso' => 'danger', 'hoje' => 'warning', 'agendado' => 'info'];
            $followupIcons  = ['atraso' => 'bi-exclamation-circle-fill', 'hoje' => 'bi-alarm-fill', 'agendado' => 'bi-calendar-check'];
        @endphp

        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-alarm"></i> Próximo Follow-up</h5>
                @if($followupDate)
                <span class="badge bg-{{ $followupColors[$followupState] }}">
                    <i class="{{ $followupIcons[$followupState] }} me-1"></i>
                    {{ $followupState === 'atraso' ? 'Em atraso' : ($followupState === 'hoje' ? 'Hoje' : 'Agendado') }}
                </span>
                @endif
            </div>

            @if($followupDate)
            <div class="followup-current bg-{{ $followupColors[$followupState] }}-subtle border-{{ $followupColors[$followupState] }}">
                <div class="followup-current__date">
                    <i class="bi {{ $followupIcons[$followupState] }} text-{{ $followupColors[$followupState] }}"></i>
                    {{ $followupDate->format('d/m/Y') }} às {{ $followupDate->format('H:i') }}
                </div>
                @if($client->followup_note)
                <div class="followup-current__note">{{ $client->followup_note }}</div>
                @endif
            </div>
            @endif

            <form action="{{ route('admin.angariador.leads.followup', $client->id) }}" method="POST" class="p-3">
                @csrf
                <label class="form-label small fw-semibold mb-1">
                    {{ $followupDate ? 'Reagendar para' : 'Agendar contacto' }}
                </label>
                <input type="datetime-local" name="next_followup_at" class="form-control mb-2"
                    value="{{ $followupDate ? $followupDate->format('Y-m-d\TH:i') : '' }}"
                    min="{{ now()->format('Y-m-d\TH:i') }}" required>
                <input type="text" name="followup_note" class="form-control mb-2"
                    placeholder="Motivo / o que ficou acordado (opcional)"
                    value="{{ $client->followup_note ?? '' }}" maxlength="255">
                <button type="submit" class="btn btn-warning w-100 btn-sm fw-semibold">
                    <i class="bi bi-alarm me-1"></i> {{ $followupDate ? 'Reagendar' : 'Agendar Follow-up' }}
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.lead-activity-item { display: flex; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--admin-border); }
.lead-activity-item:last-child { border-bottom: none; }
.lad-icon { width: 36px; height: 36px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .9rem; }
.lad-body { flex: 1; min-width: 0; }
.lad-title { font-weight: 600; font-size: .9rem; color: #111; }
.lad-meta { font-size: .75rem; color: #6c757d; margin: .15rem 0 .4rem; }
.lad-tags { display: flex; flex-wrap: wrap; gap: .35rem; }
.lead-info-list { padding: .5rem 1.25rem 1rem; }
.lil-row { display: flex; align-items: center; gap: .6rem; padding: .5rem 0; border-bottom: 1px solid #f5f5f5; font-size: .88rem; color: #333; }
.lil-row:last-child { border-bottom: none; }

/* ── Follow-up ─────────────────────────────────────────────────────── */
.followup-current {
    margin: 0 1.25rem .75rem;
    padding: .65rem .85rem;
    border-radius: 8px;
    border-left: 3px solid;
}
.followup-current__date { font-size: .88rem; font-weight: 600; display: flex; align-items: center; gap: .4rem; }
.followup-current__note { font-size: .8rem; color: #555; margin-top: .25rem; }

/* ── Timeline & Notas ──────────────────────────────────────────────── */
.timeline-add-form { padding: 1rem 1.25rem; border-bottom: 1px solid var(--admin-border); background: #fafafa; }

.timeline-type-tabs { display: flex; gap: .5rem; flex-wrap: wrap; }
.type-tab {
    display: flex; align-items: center; gap: .35rem;
    padding: .3rem .75rem; border-radius: 20px; font-size: .78rem; font-weight: 600;
    border: 1px solid #dee2e6; background: #fff; cursor: pointer; color: #555;
    transition: all .15s;
}
.type-tab input[type=radio] { display: none; }
.type-tab:has(input:checked),
.type-tab.active { background: #111; color: #fff; border-color: #111; }

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

@push('scripts')
<script>
document.querySelectorAll('.type-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.type-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
    });
});
</script>
@endpush
