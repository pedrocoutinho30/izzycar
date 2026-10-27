@extends('layouts.admin-mobile')

@section('title', $lead->name)

@inject('phones', 'App\Services\PhoneNumberService')

@section('header_actions')
    <a href="{{ route('admin.mobile.leads.index') }}" class="order-first m-topbar-back" aria-label="Voltar às leads"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $erro)<div>{{ $erro }}</div>@endforeach
        </div>
    @endif

    @php
        $estado = \App\Enums\LeadStatus::tryFrom($lead->lead_status ?? 'nova');
        $tel = $phones->telUrl($lead->phone);
        $whatsapp = $phones->whatsappUrl($lead->phone);
        $proposalColors = ['Pendente' => 'warning', 'Enviado' => 'info', 'Aprovada' => 'success', 'Reprovada' => 'danger', 'Recusada' => 'danger', 'Sem resposta' => 'secondary', 'Expirada' => 'secondary'];
        $formLabels = ['novo' => 'Novo', 'em_analise' => 'Em análise', 'convertido' => 'Convertido', 'rejeitado' => 'Rejeitado', 'arquivado' => 'Arquivado'];
        $formColors = ['novo' => 'warning', 'em_analise' => 'info', 'convertido' => 'success', 'rejeitado' => 'danger', 'arquivado' => 'secondary'];
        $followup = $lead->next_followup_at;
    @endphp

    {{-- Resumo e contacto rápido --}}
    <div class="m-card">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
                <div class="fw-semibold fs-5">{{ $lead->name }}</div>
                <div class="text-muted small">
                    {{ \App\Enums\LeadSource::labelOf($lead->lead_source) ?? 'Outro' }} · Lead desde {{ $lead->created_at->format('d/m/Y') }}
                </div>
            </div>
            <span class="badge bg-{{ $estado?->color() ?? 'secondary' }} flex-shrink-0">{{ \App\Enums\LeadStatus::labelOf($lead->lead_status) }}</span>
        </div>
        @if($tel || $lead->email)
            <div class="m-contact-actions mt-3">
                @if($tel)
                    <a href="{{ $tel }}" class="btn btn-outline-secondary"><i class="bi bi-telephone me-1"></i>Ligar</a>
                @endif
                @if($whatsapp)
                    <a href="{{ $whatsapp }}" class="btn btn-outline-success" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
                @endif
                @if($lead->email)
                    <a href="mailto:{{ $lead->email }}" class="btn btn-outline-primary"><i class="bi bi-envelope me-1"></i>Email</a>
                @endif
            </div>
        @endif
    </div>

    <div class="accordion m-accordion" id="leadSeccoes">

        {{-- Contacto --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sec-contacto" aria-expanded="true" aria-controls="sec-contacto">
                    <i class="bi bi-person me-2"></i> Contacto
                </button>
            </h2>
            <div id="sec-contacto" class="accordion-collapse collapse show">
                <div class="accordion-body">
                    <dl class="m-dl mb-0">
                        <dt>Telefone</dt><dd>{{ $lead->phone ?: '—' }}</dd>
                        <dt>Email</dt><dd class="text-break">{{ $lead->email ?: '—' }}</dd>
                        <dt>Origem</dt><dd>{{ $lead->origin ?: '—' }}</dd>
                        <dt>Proprietário</dt><dd>{{ $lead->owner ? trim($lead->owner->name . ' ' . $lead->owner->last_name) : 'Sem proprietário' }}</dd>
                        @if($lead->observation)
                            <dt>Observações</dt><dd>{{ $lead->observation }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        {{-- Estado e follow-up --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-estado" aria-expanded="false" aria-controls="sec-estado">
                    <i class="bi bi-circle-half me-2"></i> Estado e follow-up
                </button>
            </h2>
            <div id="sec-estado" class="accordion-collapse collapse">
                <div class="accordion-body d-grid gap-2">
                    @foreach($canUpdate ? $statuses : [] as $valor)
                        @continue($valor === ($lead->lead_status ?? 'nova'))
                        @php $opcao = \App\Enums\LeadStatus::from($valor); @endphp
                        <form action="{{ route('admin.mobile.leads.status', $lead->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="{{ $valor }}">
                            <button type="submit" class="btn btn-outline-{{ $opcao->color() }} w-100 text-start">
                                <i class="bi {{ $opcao->icon() }} me-2"></i>Marcar como {{ $opcao->label() }}
                            </button>
                        </form>
                    @endforeach

                    @if($canUpdate)<hr class="my-2">@endif
                    <div class="fw-semibold"><i class="bi bi-alarm me-1"></i> Próximo follow-up</div>
                    @if($followup)
                        <div class="{{ $followup->isPast() ? 'text-danger' : '' }}">
                            {{ $followup->format('d/m/Y') }} às {{ $followup->format('H:i') }}
                            @if($followup->isPast()) <span class="badge bg-danger">Em atraso</span> @endif
                        </div>
                        @if($lead->followup_note)
                            <div class="text-muted small">{{ $lead->followup_note }}</div>
                        @endif
                    @else
                        <div class="text-muted small">Sem follow-up agendado.</div>
                    @endif

                    @if($canUpdate)
                        <form action="{{ route('admin.mobile.leads.followup', $lead->id) }}" method="POST" class="d-grid gap-2">
                            @csrf
                            <input type="datetime-local" name="next_followup_at" class="form-control" required
                                   value="{{ $followup ? $followup->format('Y-m-d\TH:i') : '' }}" min="{{ now()->format('Y-m-d\TH:i') }}"
                                   aria-label="Data do follow-up">
                            <input type="text" name="followup_note" class="form-control" maxlength="255"
                                   value="{{ $lead->followup_note }}" placeholder="Motivo / o que ficou acordado (opcional)">
                            <button type="submit" class="btn btn-warning fw-semibold">{{ $followup ? 'Reagendar' : 'Agendar follow-up' }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pedidos de formulário --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-formularios" aria-expanded="false" aria-controls="sec-formularios">
                    <i class="bi bi-envelope-open me-2"></i> Formulários
                    <span class="badge bg-secondary rounded-pill ms-2">{{ $formProposals->count() }}</span>
                </button>
            </h2>
            <div id="sec-formularios" class="accordion-collapse collapse">
                <div class="accordion-body">
                    @forelse($formProposals as $pedido)
                        <div class="m-list-item position-relative">
                            <div class="fw-semibold">{{ $pedido->label }}</div>
                            <div class="small text-muted">
                                {{ $pedido->isManual() ? 'Manual' : 'Site' }} · {{ $pedido->created_at->format('d/m/Y') }}
                                · {{ $pedido->opportunities_count }} {{ $pedido->opportunities_count === 1 ? 'oportunidade' : 'oportunidades' }}
                            </div>
                            @if($pedido->status)
                                <span class="badge bg-{{ $formColors[$pedido->status] ?? 'secondary' }}">{{ $formLabels[$pedido->status] ?? $pedido->status }}</span>
                            @endif
                            @if($canOpenRequests)
                                <a href="{{ route('admin.mobile.form-proposals.show', $pedido->id) }}" class="stretched-link" aria-label="Abrir pedido {{ $pedido->label }}"></a>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted small">Sem pedidos de importação.</div>
                    @endforelse

                    @if($canCreateRequest)
                        <a href="{{ route('admin.mobile.form-proposals.create', ['client_id' => $lead->id]) }}" class="btn btn-primary w-100 mt-2">
                            <i class="bi bi-envelope-plus me-1"></i> Novo pedido
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Cotações --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-cotacoes" aria-expanded="false" aria-controls="sec-cotacoes">
                    <i class="bi bi-file-earmark-text me-2"></i> Cotações
                    <span class="badge bg-secondary rounded-pill ms-2">{{ $lead->proposals->count() }}</span>
                </button>
            </h2>
            <div id="sec-cotacoes" class="accordion-collapse collapse">
                <div class="accordion-body">
                    @forelse($lead->proposals as $proposal)
                        @php
                            $proposalStatus = $proposal->status ?? 'Pendente';
                            $convertida = $convertedByProposal->get($proposal->id);
                        @endphp
                        <div class="m-list-item position-relative">
                            <div class="fw-semibold">{{ trim(($proposal->brand ?? '') . ' ' . ($proposal->model ?? '—')) }}</div>
                            <div class="small text-muted">
                                {{ $proposal->created_at->format('d/m/Y') }}
                                @if($proposal->value) · {{ number_format($proposal->value, 0, ',', '.') }} € @endif
                            </div>
                            <span class="badge bg-{{ $proposalColors[$proposalStatus] ?? 'secondary' }}">{{ $proposalStatus }}</span>
                            @if($canOpenProposals)
                                <a href="{{ route('admin.mobile.proposals.show', $proposal->id) }}" class="stretched-link" aria-label="Abrir cotação {{ trim(($proposal->brand ?? '') . ' ' . ($proposal->model ?? '')) }}"></a>
                            @endif
                            @if($convertida)
                                @if($canOpenConverted)
                                    <a href="{{ route('admin.mobile.converted-proposals.show', $convertida->id) }}" class="badge bg-dark text-decoration-none d-inline-flex align-items-center justify-content-center position-relative" style="min-height:44px;min-width:44px;z-index:2">Convertida <i class="bi bi-arrow-right-short"></i></a>
                                @else
                                    <span class="badge bg-dark">Convertida</span>
                                @endif
                            @endif
                        </div>
                    @empty
                        <div class="text-muted small">Sem cotações.</div>
                    @endforelse

                    @if($canCreateProposal)
                        <a href="{{ route('admin.mobile.proposals.create', ['client_id' => $lead->id]) }}" class="btn btn-primary w-100 mt-2">
                            <i class="bi bi-file-earmark-plus me-1"></i> Criar cotação
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Cotações convertidas --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-convertidas" aria-expanded="false" aria-controls="sec-convertidas">
                    <i class="bi bi-arrow-repeat me-2"></i> Convertidas
                    <span class="badge bg-secondary rounded-pill ms-2">{{ $lead->convertedProposals->count() }}</span>
                </button>
            </h2>
            <div id="sec-convertidas" class="accordion-collapse collapse">
                <div class="accordion-body">
                    @forelse($lead->convertedProposals as $convertida)
                        <div class="m-list-item position-relative">
                            <div class="fw-semibold">{{ trim(($convertida->brand ?? '') . ' ' . ($convertida->modelCar ?? '—')) }}</div>
                            <div class="small text-muted">{{ $convertida->created_at->format('d/m/Y') }}</div>
                            <span class="badge bg-{{ $convertida->isCancelled() ? 'danger' : 'info' }}">{{ $convertida->status ?? 'Iniciada' }}</span>
                            @if($canOpenConverted)
                                <a href="{{ route('admin.mobile.converted-proposals.show', $convertida->id) }}" class="stretched-link" aria-label="Abrir convertida {{ trim(($convertida->brand ?? '') . ' ' . ($convertida->modelCar ?? '')) }}"></a>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted small">Sem cotações convertidas.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Simulações de custos --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-simuladores" aria-expanded="false" aria-controls="sec-simuladores">
                    <i class="bi bi-calculator me-2"></i> Simuladores
                    <span class="badge bg-secondary rounded-pill ms-2">{{ $simulators->count() }}</span>
                </button>
            </h2>
            <div id="sec-simuladores" class="accordion-collapse collapse">
                <div class="accordion-body">
                    @forelse($simulators as $sim)
                        <div class="m-list-item">
                            <div class="fw-semibold">{{ trim(($sim->brand ?? '') . ' ' . ($sim->model ?? '')) ?: 'Simulação' }}</div>
                            <div class="small text-muted">
                                {{ $sim->created_at->format('d/m/Y H:i') }}
                                @if($sim->car_value) · Carro: {{ number_format($sim->car_value, 0, ',', '.') }} € @endif
                                @if($sim->total_cost) · Total: {{ number_format($sim->total_cost, 0, ',', '.') }} € @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">Sem simulações.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Timeline e nota rápida --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-timeline" aria-expanded="false" aria-controls="sec-timeline">
                    <i class="bi bi-clock-history me-2"></i> Timeline
                    <span class="badge bg-secondary rounded-pill ms-2">{{ $activities->count() }}</span>
                </button>
            </h2>
            <div id="sec-timeline" class="accordion-collapse collapse">
                <div class="accordion-body">
                    @if($canUpdate)
                        <form action="{{ route('admin.mobile.leads.activity', $lead->id) }}" method="POST" class="d-grid gap-2 mb-3">
                            @csrf
                            <select name="type" class="form-select" aria-label="Tipo de atividade">
                                @foreach(\App\Models\LeadActivity::TYPES as $tipo => $cfg)
                                    @continue($tipo === 'system')
                                    <option value="{{ $tipo }}">{{ $cfg['label'] }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="title" class="form-control" required maxlength="255" placeholder="Resumo (ex: Liguei, não atendeu)">
                            <textarea name="body" class="form-control" rows="2" maxlength="2000" placeholder="Detalhe (opcional)"></textarea>
                            <button type="submit" class="btn btn-primary">Registar</button>
                        </form>
                    @endif

                    @forelse($activities as $atividade)
                        <div class="m-timeline-item">
                            <span class="m-timeline-icon bg-{{ $atividade->color }}"><i class="{{ $atividade->icon }}"></i></span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold">{{ $atividade->title }}</div>
                                @if($atividade->body)
                                    <div class="small">{{ $atividade->body }}</div>
                                @endif
                                <div class="small text-muted">
                                    {{ $atividade->created_at->format('d/m/Y H:i') }} · {{ $atividade->user?->name ?? 'Sistema' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">Sem atividade registada.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 mt-3">
        @if($canAssignOwner)
            <form action="{{ route('admin.mobile.leads.owner', $lead->id) }}" method="POST" class="d-grid gap-2 m-card mb-0">
                @csrf
                <label class="fw-semibold" for="lead-owner"><i class="bi bi-person-badge me-1"></i> Proprietário</label>
                <select name="owner_id" id="lead-owner" class="form-select">
                    <option value="">— Sem proprietário —</option>
                    @foreach($owners as $dono)
                        <option value="{{ $dono->id }}" @selected($lead->owner_id == $dono->id)>{{ trim($dono->name . ' ' . $dono->last_name) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline-primary">Guardar proprietário</button>
            </form>
        @endif
        @if($canUpdate)
            <form action="{{ route('admin.mobile.leads.convert', $lead->id) }}" method="POST" class="d-grid">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Converter esta lead em cliente?')">
                    <i class="bi bi-person-check me-1"></i> Converter em cliente
                </button>
            </form>
        @endif
        @if($canDelete)
            <form action="{{ route('admin.mobile.leads.destroy', $lead->id) }}" method="POST" class="d-grid">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Eliminar esta lead? Não pode ser desfeito.')">
                    <i class="bi bi-trash me-1"></i> Eliminar lead
                </button>
            </form>
        @endif
        @canroute('admin.v2.leads.show')
        <a href="{{ route('admin.v2.leads.show', $lead->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-display me-1"></i> Abrir no desktop
        </a>
        @endcanroute
    </div>
@endsection
