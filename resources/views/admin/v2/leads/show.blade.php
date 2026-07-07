@extends('layouts.admin-v2')

@section('title', 'Lead — ' . $lead->name)

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Leads', 'href' => route('admin.v2.leads.index')],
        ['icon' => '', 'label' => $lead->name]
    ],
    'title' => $lead->name,
    'subtitle' => 'Lead desde ' . $lead->created_at->format('d/m/Y'),
    'actionHref' => '',
    'actionLabel' => ''
])

<div class="row g-4">
    {{-- Coluna principal --}}
    <div class="col-lg-8">

        {{-- Formulários de importação --}}
        @if($formProposals->isNotEmpty())
        @foreach($formProposals as $fp)
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-envelope-open"></i>
                    Pedido de Importação
                    <span class="badge bg-{{ match($fp->status) {
                        'novo'       => 'warning',
                        'em_analise' => 'info',
                        'convertido' => 'success',
                        'rejeitado'  => 'danger',
                        default      => 'secondary'
                    } }} ms-2 fw-normal fs-xs">{{ ucfirst(str_replace('_', ' ', $fp->status)) }}</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">{{ $fp->created_at->format('d/m/Y H:i') }}</span>
                    <a href="{{ route('admin.v2.form-proposals.show', $fp->id) }}"
                       class="btn btn-icon btn-primary-modern" title="Abrir pedido completo">
                        <i class="bi bi-arrow-up-right-square"></i>
                    </a>
                </div>
            </div>

            <div class="fp-grid">
                {{-- Mensagem --}}
                @if($fp->message)
                <div class="fp-block fp-block--full">
                    <div class="fp-label">Mensagem</div>
                    <div class="fp-value">{{ $fp->message }}</div>
                </div>
                @endif

                {{-- Veículo pretendido --}}
                @if($fp->brand || $fp->model || $fp->version)
                <div class="fp-block">
                    <div class="fp-label">Veículo</div>
                    <div class="fp-value">{{ implode(' ', array_filter([$fp->brand, $fp->model, $fp->version])) }}</div>
                </div>
                @endif

                @if($fp->fuel)
                <div class="fp-block">
                    <div class="fp-label">Combustível</div>
                    <div class="fp-value">{{ $fp->fuel }}</div>
                </div>
                @endif

                @if($fp->gearbox)
                <div class="fp-block">
                    <div class="fp-label">Caixa</div>
                    <div class="fp-value">{{ $fp->gearbox }}</div>
                </div>
                @endif

                @if($fp->color)
                <div class="fp-block">
                    <div class="fp-label">Cor</div>
                    <div class="fp-value">{{ $fp->color }}</div>
                </div>
                @endif

                @if($fp->year_min)
                <div class="fp-block">
                    <div class="fp-label">Ano mínimo</div>
                    <div class="fp-value">{{ $fp->year_min }}</div>
                </div>
                @endif

                @if($fp->km_max)
                <div class="fp-block">
                    <div class="fp-label">Km máximos</div>
                    <div class="fp-value">{{ number_format($fp->km_max, 0, ',', '.') }} km</div>
                </div>
                @endif

                @if($fp->budget)
                <div class="fp-block">
                    <div class="fp-label">Orçamento</div>
                    <div class="fp-value fw-semibold">€ {{ number_format($fp->budget, 0, ',', '.') }}</div>
                </div>
                @endif

                @if($fp->payment_type)
                <div class="fp-block">
                    <div class="fp-label">Pagamento</div>
                    <div class="fp-value">{{ $fp->payment_type }}</div>
                </div>
                @endif

                @if($fp->estimated_purchase_date)
                <div class="fp-block">
                    <div class="fp-label">Compra prevista</div>
                    @php
                        $purchaseLabels = [
                            '1_3_meses'   => '1 a 3 meses',
                            '3_6_meses'   => '3 a 6 meses',
                            '6_12_meses'  => '6 a 12 meses',
                            'mais_1_ano'  => 'Mais de 1 ano',
                        ];
                        $purchaseDisplay = $purchaseLabels[$fp->estimated_purchase_date]
                            ?? (is_numeric(strtotime($fp->estimated_purchase_date))
                                ? \Carbon\Carbon::parse($fp->estimated_purchase_date)->format('d/m/Y')
                                : $fp->estimated_purchase_date);
                    @endphp
                    <div class="fp-value">{{ $purchaseDisplay }}</div>
                </div>
                @endif

                @if($fp->source)
                <div class="fp-block">
                    <div class="fp-label">Origem</div>
                    <div class="fp-value">{{ $fp->source }}</div>
                </div>
                @endif

                @if($fp->ad_option && $fp->ad_option !== 'nao_nao')
                <div class="fp-block">
                    <div class="fp-label">Já encontrou anúncio?</div>
                    <div class="fp-value">{{ $fp->ad_option === 'sim' ? 'Sim' : 'Não sabe' }}</div>
                </div>
                @endif

                @if($fp->ad_links)
                <div class="fp-block fp-block--full">
                    <div class="fp-label">Links de anúncios</div>
                    <div class="fp-value">{{ $fp->ad_links }}</div>
                </div>
                @endif

                @if($fp->extras)
                <div class="fp-block fp-block--full">
                    <div class="fp-label">Extras pretendidos</div>
                    <div class="fp-value">{{ $fp->extras }}</div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
        @endif

        {{-- Simulações de custos --}}
        @if($simulators->isNotEmpty())
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-calculator"></i> Simulações de Custos</h5>
                <span class="badge bg-secondary rounded-pill">{{ $simulators->count() }}</span>
            </div>
            @foreach($simulators as $sim)
            <div class="lead-activity-item">
                <div class="lad-icon lad-icon--calc">
                    <i class="bi bi-calculator"></i>
                </div>
                <div class="lad-body">
                    <div class="lad-title">Simulação realizada</div>
                    <div class="lad-meta">{{ $sim->created_at->format('d/m/Y H:i') }}</div>
                    <div class="lad-tags">
                        @if($sim->brand) <span class="lad-tag">{{ $sim->brand }}</span> @endif
                        @if($sim->model) <span class="lad-tag">{{ $sim->model }}</span> @endif
                        @if($sim->car_value) <span class="lad-tag">Carro: €{{ number_format($sim->car_value, 0, ',', '.') }}</span> @endif
                        @if($sim->total_cost) <span class="lad-tag lad-tag--accent">Total: €{{ number_format($sim->total_cost, 0, ',', '.') }}</span> @endif
                    </div>
                </div>
                <a href="{{ route('admin.v2.cost-simulators.show', $sim->id) }}"
                   class="btn btn-icon btn-secondary-modern ms-auto flex-shrink-0" title="Ver simulação completa">
                    <i class="bi bi-arrow-up-right-square"></i>
                </a>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Cotações associadas --}}
        @if($lead->proposals->isNotEmpty())
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-file-earmark-text"></i> Cotações</h5>
                <span class="badge bg-secondary rounded-pill">{{ $lead->proposals->count() }}</span>
            </div>
            @foreach($lead->proposals->sortByDesc('created_at') as $proposal)
            @php
                $pStatusColors = ['Pendente' => 'warning', 'Aprovada' => 'success', 'Recusada' => 'danger', 'Sem resposta' => 'secondary', 'Expirada' => 'secondary'];
                $pColor = $pStatusColors[$proposal->status] ?? 'secondary';
            @endphp
            <div class="lead-activity-item align-items-center">
                <div class="lad-icon" style="background:rgba(13,110,253,.1);color:#0d6efd;">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="lad-body">
                    <div class="lad-title">{{ trim(($proposal->brand ?? '') . ' ' . ($proposal->model ?? '—')) }}</div>
                    <div class="lad-meta">{{ $proposal->created_at->format('d/m/Y') }}</div>
                    <div class="lad-tags">
                        <span class="lad-tag badge bg-{{ $pColor }}-subtle text-{{ $pColor }} border border-{{ $pColor }}-subtle">
                            {{ $proposal->status ?? 'Pendente' }}
                        </span>
                        @if($proposal->value)
                            <span class="lad-tag lad-tag--accent">{{ number_format($proposal->value, 0, ',', '.') }} €</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('admin.v2.proposals.edit', $proposal->id) }}"
                   class="btn btn-icon btn-primary-modern ms-auto flex-shrink-0" title="Ver cotação">
                    <i class="bi bi-arrow-up-right-square"></i>
                </a>
            </div>
            @endforeach
        </div>
        @endif

        @if($formProposals->isEmpty() && $simulators->isEmpty())
        <div class="modern-card mb-4">
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                Sem atividade registada para este lead.
            </div>
        </div>
        @endif

        {{-- ── TIMELINE + NOTAS ─────────────────────────────────────────── --}}
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-clock-history"></i> Timeline & Notas</h5>
            </div>

            {{-- Formulário para registar nova actividade --}}
            <div class="timeline-add-form">
                <form action="{{ route('admin.v2.leads.activity', $lead->id) }}" method="POST">
                    @csrf
                    {{-- Tipo de actividade --}}
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

                    {{-- Título / resumo --}}
                    <input type="text" name="title" class="form-control mt-2 mb-2"
                        placeholder="Resumo (ex: Liguei, não atendeu / Enviou email com interesse no BMW 320d)"
                        required maxlength="255">

                    {{-- Detalhe opcional --}}
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
            @if($activities->isNotEmpty())
            <div class="timeline-list">
                @foreach($activities as $act)
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

    {{-- Sidebar --}}
    <div class="col-lg-4">
        @php
            $currentStatus = $lead->lead_status ?? 'nova';
            $statusConfig = [
                'nova'        => ['label' => 'Nova',        'color' => 'success',   'icon' => 'bi-circle-fill'],
                'em_contacto' => ['label' => 'Em Contacto', 'color' => 'info',      'icon' => 'bi-telephone-fill'],
                'fria'        => ['label' => 'Fria',        'color' => 'secondary', 'icon' => 'bi-snow'],
                'perdida'     => ['label' => 'Perdida',     'color' => 'danger',    'icon' => 'bi-x-circle-fill'],
            ];
            $sc = $statusConfig[$currentStatus] ?? $statusConfig['nova'];
        @endphp

        {{-- Dados do lead --}}
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-person"></i> Dados do Lead</h5>
            </div>
            <div class="lead-info-list">
                @if($lead->email)
                <div class="lil-row">
                    <i class="bi bi-envelope text-muted"></i>
                    <span>{{ $lead->email }}</span>
                </div>
                @endif
                @if($lead->phone)
                <div class="lil-row">
                    <i class="bi bi-telephone text-muted"></i>
                    <span>{{ $lead->phone }}</span>
                </div>
                @endif
                @php
                    $sourceLabels = [
                        'simulador'  => 'Simulador de Custos',
                        'importacao' => 'Formulário de Importação',
                        'retoma'     => 'Retoma',
                        'manual'     => 'Manual',
                    ];
                @endphp
                <div class="lil-row">
                    <i class="bi bi-funnel text-muted"></i>
                    <span>{{ $sourceLabels[$lead->lead_source] ?? 'Outro' }}</span>
                </div>
                <div class="lil-row">
                    <i class="bi bi-calendar text-muted"></i>
                    <span>Lead desde {{ $lead->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="lil-row">
                    <i class="bi bi-circle-half text-muted"></i>
                    <span class="badge bg-{{ $sc['color'] }} fw-normal">{{ $sc['label'] }}</span>
                </div>
            </div>
        </div>

        {{-- Follow-up --}}
        @php
            $followupDate = $lead->next_followup_at;
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

            {{-- Mostrar follow-up actual se existir --}}
            @if($followupDate)
            <div class="followup-current bg-{{ $followupColors[$followupState] }}-subtle border-{{ $followupColors[$followupState] }}">
                <div class="followup-current__date">
                    <i class="bi {{ $followupIcons[$followupState] }} text-{{ $followupColors[$followupState] }}"></i>
                    {{ $followupDate->format('d/m/Y') }} às {{ $followupDate->format('H:i') }}
                </div>
                @if($lead->followup_note)
                <div class="followup-current__note">{{ $lead->followup_note }}</div>
                @endif
            </div>
            @endif

            {{-- Formulário para agendar / reagendar --}}
            <form action="{{ route('admin.v2.leads.followup', $lead->id) }}" method="POST" class="p-3">
                @csrf
                <label class="form-label small fw-semibold mb-1">
                    {{ $followupDate ? 'Reagendar para' : 'Agendar contacto' }}
                    {{-- Dica de utilização --}}
                    <span class="text-muted fw-normal">— ex: amanhã de manhã para confirmar interesse</span>
                </label>
                <input type="datetime-local" name="next_followup_at" class="form-control mb-2"
                    value="{{ $followupDate ? $followupDate->format('Y-m-d\TH:i') : '' }}"
                    min="{{ now()->format('Y-m-d\TH:i') }}" required>
                <input type="text" name="followup_note" class="form-control mb-2"
                    placeholder="Motivo / o que ficou acordado (opcional)"
                    value="{{ $lead->followup_note ?? '' }}" maxlength="255">
                <button type="submit" class="btn btn-warning w-100 btn-sm fw-semibold">
                    <i class="bi bi-alarm me-1"></i> {{ $followupDate ? 'Reagendar' : 'Agendar Follow-up' }}
                </button>
            </form>
        </div>

        {{-- Estado da lead --}}
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-circle-half"></i> Estado</h5>
                <span class="badge bg-{{ $sc['color'] }}">
                    <i class="{{ $sc['icon'] }} me-1"></i>{{ $sc['label'] }}
                </span>
            </div>
            <div class="p-3 d-grid gap-2">
                @foreach($statusConfig as $key => $cfg)
                @if($key !== $currentStatus)
                <form action="{{ route('admin.v2.leads.status', $lead->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="{{ $key }}">
                    <button type="submit" class="btn btn-outline-{{ $cfg['color'] }} w-100 text-start">
                        <i class="{{ $cfg['icon'] }} me-2"></i>Marcar como {{ $cfg['label'] }}
                    </button>
                </form>
                @endif
                @endforeach
            </div>
        </div>

        {{-- Ações --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-lightning"></i> Ações</h5>
            </div>
            <div class="p-3 d-grid gap-2">
                <form action="{{ route('admin.v2.leads.convert', $lead->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success w-100"
                            onclick="return confirm('Converter este lead em cliente?')">
                        <i class="bi bi-person-check me-1"></i> Converter em Cliente
                    </button>
                </form>
                <a href="{{ route('admin.v2.proposals.create', ['client_id' => $lead->id]) }}"
                   class="btn btn-primary w-100">
                    <i class="bi bi-file-earmark-text me-1"></i> Criar Cotação
                </a>
                <a href="{{ route('admin.v2.leads.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-left me-1"></i> Voltar aos Leads
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.fp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0;
    padding: .25rem 1.25rem 1.25rem;
}
.fp-block {
    padding: .65rem .75rem;
    border-right: 1px solid #f5f5f5;
    border-bottom: 1px solid #f5f5f5;
}
.fp-block--full { grid-column: 1 / -1; }
.fp-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #aaa; margin-bottom: .2rem; }
.fp-value { font-size: .88rem; color: #222; word-break: break-word; }
.fs-xs { font-size: .72rem; }

.lead-activity-item {
    display: flex; gap: 1rem; padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--admin-border);
}
.lead-activity-item:last-child { border-bottom: none; }

.lad-icon {
    width: 36px; height: 36px; flex-shrink: 0; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem;
}
.lad-icon--mail { background: rgba(13,110,253,.1); color: #0d6efd; }
.lad-icon--calc { background: rgba(25,135,84,.1); color: #198754; }

.lad-body { flex: 1; min-width: 0; }
.lad-title { font-weight: 600; font-size: .9rem; color: #111; }
.lad-meta  { font-size: .75rem; color: #6c757d; margin: .15rem 0 .4rem; }
.lad-text  { font-size: .85rem; color: #444; margin: .35rem 0 .4rem; }
.lad-tags  { display: flex; flex-wrap: wrap; gap: .35rem; }
.lad-tag   { background: #f1f3f5; border-radius: 6px; padding: .15rem .5rem; font-size: .75rem; color: #555; }
.lad-tag--accent { background: rgba(110,7,7,.08); color: #6e0707; font-weight: 600; }

.lead-info-list { padding: .5rem 1.25rem 1rem; }
.lil-row { display: flex; align-items: center; gap: .6rem; padding: .5rem 0;
           border-bottom: 1px solid #f5f5f5; font-size: .88rem; color: #333; }
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
/* Activar tab de tipo ao clicar */
document.querySelectorAll('.type-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.type-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
    });
});
</script>
@endpush
