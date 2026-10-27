@extends('layouts.admin-mobile')

@section('title', $client->name)

@inject('phones', 'App\Services\PhoneNumberService')

@section('header_actions')
    <a href="{{ route('admin.mobile.clients.index') }}" class="order-first m-topbar-back" aria-label="Voltar aos clientes"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $erro)<div>{{ $erro }}</div>@endforeach
        </div>
    @endif

    @php
        $tel = $phones->telUrl($client->phone);
        $whatsapp = $phones->whatsappUrl($client->phone);
        $proposalColors = ['Pendente' => 'warning', 'Enviado' => 'info', 'Aprovada' => 'success', 'Reprovada' => 'danger', 'Recusada' => 'danger', 'Sem resposta' => 'secondary', 'Expirada' => 'secondary'];
        $formLabels = ['novo' => 'Novo', 'em_analise' => 'Em análise', 'convertido' => 'Convertido', 'rejeitado' => 'Rejeitado', 'arquivado' => 'Arquivado'];
        $formColors = ['novo' => 'warning', 'em_analise' => 'info', 'convertido' => 'success', 'rejeitado' => 'danger', 'arquivado' => 'secondary'];
        $followup = $client->next_followup_at;
        $validade = $client->validate_identification_number;
        $morada = trim(implode(' ', array_filter([$client->postal_code, $client->city])));
        // Viaturas do cliente: importações (sem venda) e compradas (venda).
        $viaturas = $client->vehicles->concat($client->sale->pluck('v3Vehicle')->filter())->unique('id');
    @endphp

    {{-- Resumo e contacto rápido --}}
    <div class="m-card">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="min-w-0">
                <div class="fw-semibold fs-5 text-break">{{ $client->name }}</div>
                <div class="text-muted small">
                    Cliente desde {{ ($client->converted_at ?? $client->created_at)->format('d/m/Y') }}
                </div>
            </div>
            @if($client->client_type)
                <span class="badge bg-secondary flex-shrink-0">{{ ucfirst(mb_strtolower($client->client_type)) }}</span>
            @endif
        </div>
        @if($tel || $client->email)
            <div class="m-contact-actions mt-3">
                @if($tel)
                    <a href="{{ $tel }}" class="btn btn-outline-secondary"><i class="bi bi-telephone me-1"></i>Ligar</a>
                @endif
                @if($whatsapp)
                    <a href="{{ $whatsapp }}" class="btn btn-outline-success" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
                @endif
                @if($client->email)
                    <a href="mailto:{{ $client->email }}" class="btn btn-outline-primary"><i class="bi bi-envelope me-1"></i>Email</a>
                @endif
            </div>
        @endif
        @if($canUpdate)
            <a href="{{ route('admin.mobile.clients.edit', $client->id) }}" class="btn btn-outline-primary w-100 mt-2">
                <i class="bi bi-pencil me-1"></i> Editar cliente
            </a>
        @endif
    </div>

    <div class="accordion m-accordion" id="clienteSeccoes">

        {{-- Dados do cliente (os mesmos do desktop) --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sec-dados" aria-expanded="true" aria-controls="sec-dados">
                    <i class="bi bi-person me-2"></i> Dados
                </button>
            </h2>
            <div id="sec-dados" class="accordion-collapse collapse show">
                <div class="accordion-body">
                    <dl class="m-dl mb-0">
                        <dt>Telefone</dt><dd>{{ $client->phone ?: '—' }}</dd>
                        <dt>Email</dt><dd class="text-break">{{ $client->email ?: '—' }}</dd>
                        <dt>NIF</dt><dd>{{ $client->vat_number ?: '—' }}</dd>
                        <dt>Nascimento</dt><dd>{{ $client->birth_date?->format('d/m/Y') ?? '—' }}</dd>
                        {{-- O documento de identificação só aparece no desktop no formulário de edição: aqui, a quem pode editar. --}}
                        @if($canUpdate)
                            <dt>Nº de identificação</dt><dd>{{ $client->identification_number ?: '—' }}</dd>
                            <dt>Validade</dt>
                            <dd>
                                {{ $validade?->format('d/m/Y') ?? '—' }}
                                @if($validade?->isPast()) <span class="badge bg-danger">Caducado</span> @endif
                            </dd>
                        @endif
                        <dt>Morada</dt>
                        <dd>
                            @if($client->address || $morada)
                                @if($client->address){{ $client->address }}<br>@endif
                                {{ $morada }}
                            @else
                                —
                            @endif
                        </dd>
                        <dt>Origem</dt><dd>{{ $client->origin ?: '—' }}</dd>
                        <dt>Consentimentos</dt>
                        <dd>
                            <span class="badge {{ $client->data_processing_consent ? 'bg-success' : 'bg-secondary' }}">Dados: {{ $client->data_processing_consent ? 'sim' : 'não' }}</span>
                            <span class="badge {{ $client->newsletter_consent ? 'bg-success' : 'bg-secondary' }}">Newsletter: {{ $client->newsletter_consent ? 'sim' : 'não' }}</span>
                        </dd>
                        @if($client->owner)
                            <dt>Proprietário</dt><dd>{{ trim($client->owner->name . ' ' . $client->owner->last_name) }}</dd>
                        @endif
                        @if($client->observation)
                            <dt>Observações</dt><dd>{{ $client->observation }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        {{-- Follow-up --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-followup" aria-expanded="false" aria-controls="sec-followup">
                    <i class="bi bi-alarm me-2"></i> Próximo follow-up
                    @if($followup?->isPast()) <span class="badge bg-danger ms-2">Em atraso</span> @endif
                </button>
            </h2>
            <div id="sec-followup" class="accordion-collapse collapse">
                <div class="accordion-body d-grid gap-2">
                    @if($followup)
                        <div class="{{ $followup->isPast() ? 'text-danger' : '' }}">
                            {{ $followup->format('d/m/Y') }} às {{ $followup->format('H:i') }}
                        </div>
                        @if($client->followup_note)
                            <div class="text-muted small">{{ $client->followup_note }}</div>
                        @endif
                    @else
                        <div class="text-muted small">Sem follow-up agendado.</div>
                    @endif

                    @if($canUpdate)
                        <form action="{{ route('admin.mobile.clients.followup', $client->id) }}" method="POST" class="d-grid gap-2">
                            @csrf
                            <input type="datetime-local" name="next_followup_at" class="form-control" required
                                   value="{{ $followup ? $followup->format('Y-m-d\TH:i') : '' }}" min="{{ now()->format('Y-m-d\TH:i') }}"
                                   aria-label="Data do follow-up">
                            <input type="text" name="followup_note" class="form-control" maxlength="255"
                                   value="{{ $client->followup_note }}" placeholder="Motivo / o que ficou acordado (opcional)">
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
                        <a href="{{ route('admin.mobile.form-proposals.create', ['client_id' => $client->id]) }}" class="btn btn-primary w-100 mt-2">
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
                    <span class="badge bg-secondary rounded-pill ms-2">{{ $client->proposals->count() }}</span>
                </button>
            </h2>
            <div id="sec-cotacoes" class="accordion-collapse collapse">
                <div class="accordion-body">
                    @forelse($client->proposals as $proposal)
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
                        <a href="{{ route('admin.mobile.proposals.create', ['client_id' => $client->id]) }}" class="btn btn-primary w-100 mt-2">
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
                    <span class="badge bg-secondary rounded-pill ms-2">{{ $client->convertedProposals->count() }}</span>
                </button>
            </h2>
            <div id="sec-convertidas" class="accordion-collapse collapse">
                <div class="accordion-body">
                    @forelse($client->convertedProposals as $convertida)
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

        {{-- Carros em análise: consulta (criar, editar e ordenar no desktop) --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-candidatos" aria-expanded="false" aria-controls="sec-candidatos">
                    <i class="bi bi-car-front me-2"></i> Carros em análise
                    <span class="badge bg-secondary rounded-pill ms-2">{{ $client->carCandidates->count() }}</span>
                </button>
            </h2>
            <div id="sec-candidatos" class="accordion-collapse collapse">
                <div class="accordion-body">
                    @forelse($client->carCandidates as $candidato)
                        @php $estadoCandidato = \App\Models\CarCandidate::STATUS_OPTIONS[$candidato->status] ?? null; @endphp
                        <div class="m-list-item">
                            <div class="fw-semibold text-break">{{ $candidato->title }}</div>
                            <div class="small">
                                <span class="badge bg-{{ $estadoCandidato['color'] ?? 'secondary' }}">{{ $estadoCandidato['label'] ?? $candidato->status }}</span>
                                @if($candidato->price) <span class="text-success fw-semibold ms-1">{{ number_format($candidato->price, 0, ',', '.') }} €</span> @endif
                            </div>
                            @if($candidato->description)
                                <div class="small text-muted">{{ $candidato->description }}</div>
                            @endif
                            @if($candidato->notes)
                                <div class="small"><i class="bi bi-sticky-fill"></i> {{ $candidato->notes }}</div>
                            @endif
                            @if(\App\Support\SafeUrl::isHttp($candidato->link))
                                <a href="{{ $candidato->link }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary mt-1 d-inline-flex align-items-center" style="min-height:44px">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir anúncio
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted small">Ainda não há carros em análise para este cliente.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Viaturas (como no desktop: importações e compras) --}}
        @if($viaturas->isNotEmpty())
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-viaturas" aria-expanded="false" aria-controls="sec-viaturas">
                        <i class="bi bi-car-front-fill me-2"></i> Viaturas
                        <span class="badge bg-secondary rounded-pill ms-2">{{ $viaturas->count() }}</span>
                    </button>
                </h2>
                <div id="sec-viaturas" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        @foreach($viaturas as $viatura)
                            <div class="m-list-item position-relative">
                                <div class="fw-semibold">{{ trim($viatura->brand . ' ' . $viatura->model) }}</div>
                                <div class="small text-muted">
                                    @if($viatura->reference) Ref: {{ $viatura->reference }} · @endif
                                    VIN: {{ $viatura->vin ?: '—' }}
                                </div>
                                @if($viatura->is_imported)
                                    <span class="badge bg-info">Importação</span>
                                @endif
                                @if($viatura->legalization)
                                    @php $pct = $viatura->legalization->progressPercent(); @endphp
                                    <span class="badge bg-{{ $pct === 100 ? 'success' : ($pct > 0 ? 'info' : 'secondary') }}">Legalização {{ $pct }}%</span>
                                @endif
                                @canroute('admin.v3.vehicles.edit')
                                    <a href="{{ route('admin.v3.vehicles.edit', $viatura->id) }}" class="stretched-link" aria-label="Abrir viatura no desktop"></a>
                                @endcanroute
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

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
                        <form action="{{ route('admin.mobile.clients.activity', $client->id) }}" method="POST" class="d-grid gap-2 mb-3">
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
        @canroute('admin.v2.clients.show')
        <a href="{{ route('admin.v2.clients.show', $client->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-display me-1"></i> Abrir no desktop
        </a>
        @endcanroute
    </div>
@endsection
