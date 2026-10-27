@extends('layouts.admin-mobile')

@php
    $veiculo = trim(implode(' ', array_filter([$converted->brand, $converted->modelCar, $converted->version])));
    $cliente = $converted->client;
    $proposta = $converted->proposal;
    $estado = $converted->status ?: 'Iniciada';
    $cancelada = $converted->isCancelled();
    $indiceAtual = array_search($estado, $statuses, true);
    $indiceAtual = $indiceAtual === false ? 0 : $indiceAtual;
    $cor = $cancelada ? 'danger' : ($estado === 'Concluido' ? 'success' : 'info');
    $euros = fn ($valor) => number_format((float) $valor, 2, ',', '.') . ' €';
    $data = fn ($valor) => $valor ? $valor->format('d/m/Y') : null;
    // A convertida vive dentro da lead/cliente.
    $fichaCliente = match (true) {
        ! $canViewClient => null,
        (bool) $cliente->is_lead => route('admin.mobile.leads.show', $cliente->id),
        default => route('admin.mobile.clients.show', $cliente->id),
    };
    // Custos do processo: [nome, valor, pago].
    $custos = [
        ['Inspeção na origem', $converted->custo_inspecao_origem, $converted->inspecao_origem_pago],
        ['Transporte', $converted->custo_transporte, $converted->transporte_pago],
        ['IPO', $converted->custo_ipo, $converted->ipo_pago],
        ['IMT', $converted->custo_imt, $converted->imt_pago],
        ['Matrícula', $converted->custo_matricula, $converted->matricula_pago_impressa],
        ['Registo automóvel', $converted->custo_registo_automovel, $converted->registo_pago],
        ['Comissão Izzycar', $converted->valor_comissao, null],
    ];
    $pago = fn ($flag) => $flag
        ? '<span class="badge bg-success">Pago</span>'
        : '<span class="badge bg-secondary">Por pagar</span>';
@endphp

@section('title', $veiculo ?: 'Cotação convertida')

@inject('phones', 'App\Services\PhoneNumberService')

@section('header_actions')
    <a href="{{ $canViewClient && $cliente->is_lead ? route('admin.mobile.leads.show', $cliente->id) : route('admin.mobile.converted-proposals.index') }}" class="order-first m-topbar-back" aria-label="Voltar"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    @php
        $tel = $canViewClient ? $phones->telUrl($cliente->phone) : null;
        $whatsapp = $canViewClient ? $phones->whatsappUrl($cliente->phone) : null;
    @endphp

    {{-- Resumo --}}
    <div class="m-card">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="min-w-0">
                <div class="fw-semibold fs-5 text-break">
                    <x-admin.brand-logo :brand="$converted->brand" :size="24" class="me-1" />
                    {{ trim($converted->brand . ' ' . $converted->modelCar) ?: 'Sem viatura' }}
                </div>
                @if($converted->version)<div class="text-muted small">{{ $converted->version }}</div>@endif
                <div class="small mt-1">
                    @if($fichaCliente)
                        <a href="{{ $fichaCliente }}" class="d-inline-flex align-items-center" style="min-height:44px">{{ $cliente->name }}</a>
                    @else
                        {{ $cliente?->name ?? 'Sem cliente' }}
                    @endif
                </div>
            </div>
            <span class="badge bg-{{ $cor }} flex-shrink-0">{{ $estado }}</span>
        </div>
        <div class="small text-muted">Cotação convertida #{{ $converted->id }} · convertida a {{ $converted->created_at->format('d/m/Y') }}</div>

        @if($tel || ($canViewClient && $cliente->email))
            <div class="m-contact-actions mt-3">
                @if($tel)
                    <a href="{{ $tel }}" class="btn btn-outline-secondary"><i class="bi bi-telephone me-1"></i>Ligar</a>
                @endif
                @if($whatsapp)
                    <a href="{{ $whatsapp }}" class="btn btn-outline-success" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
                @endif
                @if($cliente->email)
                    <a href="mailto:{{ $cliente->email }}" class="btn btn-outline-primary"><i class="bi bi-envelope me-1"></i>Email</a>
                @endif
            </div>
        @endif
    </div>

    {{-- Pipeline --}}
    <div class="m-card" id="pipeline">
        <div class="fw-semibold mb-1"><i class="bi bi-signpost-split me-1"></i> Estado do processo</div>
        @if($cancelada)
            <div class="alert alert-danger py-2 my-2"><i class="bi bi-x-circle me-1"></i> Esta cotação convertida foi cancelada.</div>
        @endif
        <ul class="list-unstyled mb-0">
            @foreach($statuses as $i => $etapa)
                @php
                    $feita = ! $cancelada && $i <= $indiceAtual;
                    $atual = ! $cancelada && $i === $indiceAtual;
                @endphp
                <li class="d-flex align-items-center gap-2 py-2 border-bottom" @if($atual) aria-current="step" @endif>
                    <i class="bi {{ $feita ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}"></i>
                    <span class="{{ $atual ? 'fw-bold' : '' }} {{ $feita ? '' : 'text-muted' }}">{{ $etapa }}</span>
                    @if($feita && isset($reachedAt[$etapa]))
                        <small class="text-muted ms-auto">{{ $reachedAt[$etapa]->format('d/m/Y') }}</small>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Mudar o estado --}}
    @if($canChangeStatus)
        <div class="m-card" id="estado">
            <form action="{{ route('admin.mobile.converted-proposals.status', $converted->id) }}" method="POST" class="d-grid gap-2" data-m-status-form>
                @csrf
                @method('PATCH')
                <label for="m-converted-status" class="fw-semibold"><i class="bi bi-arrow-left-right me-1"></i> Mudar estado</label>
                <select id="m-converted-status" name="status" class="form-select" required>
                    @foreach([...$statuses, 'Cancelada'] as $valor)
                        <option value="{{ $valor }}" @selected($valor === $estado || ($valor === 'Cancelada' && $cancelada))
                                data-silent="{{ in_array($valor, \App\Models\ConvertedProposal::SILENT_BY_DEFAULT, true) ? '1' : '0' }}">{{ $valor }}</option>
                    @endforeach
                </select>
                @if(filled($cliente?->email))
                    <input type="hidden" name="notify" value="0">
                    <div class="form-check m-form-check">
                        <input class="form-check-input" type="checkbox" name="notify" value="1" id="m-converted-notify" checked>
                        <label class="form-check-label" for="m-converted-notify">Enviar email ao cliente</label>
                    </div>
                @else
                    <input type="hidden" name="notify" value="0">
                    <div class="small text-muted">O cliente não tem email: não é avisado.</div>
                @endif
                <button type="submit" class="btn btn-primary">Guardar estado</button>
            </form>
        </div>
    @endif

    <div class="accordion m-accordion" id="convertidaSeccoes">
        {{-- Histórico --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-historico" aria-expanded="false" aria-controls="sec-historico">
                    <i class="bi bi-clock-history me-2"></i> Histórico
                    <span class="badge bg-secondary rounded-pill ms-2">{{ $history->count() }}</span>
                </button>
            </h2>
            <div id="sec-historico" class="accordion-collapse collapse">
                <div class="accordion-body">
                    @forelse($history as $registo)
                        <div class="m-list-item">
                            <div>{{ $registo->old_status ?: '—' }} <i class="bi bi-arrow-right-short"></i> <strong>{{ $registo->new_status }}</strong></div>
                            <div class="small text-muted">{{ $registo->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    @empty
                        <div class="text-muted small">Sem mudanças de estado.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Viatura --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-viatura" aria-expanded="false" aria-controls="sec-viatura">
                    <i class="bi bi-car-front me-2"></i> Viatura
                </button>
            </h2>
            <div id="sec-viatura" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <dl class="m-dl mb-0">
                        <dt>Ano</dt><dd>{{ $converted->year ?: '—' }}</dd>
                        <dt>Quilómetros</dt><dd>{{ $converted->km !== null ? number_format((float) $converted->km, 0, ',', '.') . ' km' : '—' }}</dd>
                        <dt>Matrícula origem</dt><dd>{{ $converted->matricula_origem ?: '—' }}</dd>
                        <dt>Matrícula PT</dt><dd>{{ $converted->matricula_destino ?: '—' }}</dd>
                        <dt>VIN</dt><dd class="font-monospace text-break">{{ $converted->vin ?: '—' }}</dd>
                        @if($converted->v3Vehicle)
                            <dt>Viatura</dt><dd>{{ $converted->v3Vehicle->reference }}</dd>
                        @endif
                    </dl>
                    @if($converted->url)
                        @if(\App\Support\SafeUrl::isHttp($converted->url))
                            <a href="{{ trim($converted->url) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary w-100 text-start text-truncate mt-2">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Anúncio
                            </a>
                        @else
                            <div class="small text-break mt-2">{{ $converted->url }}</div>
                        @endif
                    @endif
                    @if($converted->v3Vehicle)
                        @canroute('admin.v3.vehicles.edit')
                        <a href="{{ route('admin.v3.vehicles.edit', $converted->v3Vehicle->id) }}" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-display me-1"></i> Viatura no desktop
                        </a>
                        @endcanroute
                    @endif
                </div>
            </div>
        </div>

        {{-- Pagamentos e custos (o mesmo que o desktop mostra na edição) --}}
        @if($canSeeFinance)
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-pagamentos" aria-expanded="false" aria-controls="sec-pagamentos">
                        <i class="bi bi-currency-euro me-2"></i> Pagamentos e custos
                    </button>
                </h2>
                <div id="sec-pagamentos" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="fw-semibold">Tranches do serviço</div>
                        @foreach([
                            ['1.ª tranche', $converted->valor_primeira_tranche, $converted->primeira_tranche_pago, $converted->primeira_tranche_paga_em],
                            ['2.ª tranche', $converted->valor_segunda_tranche, $converted->segunda_tranche_pago, $converted->segunda_tranche_paga_em],
                        ] as [$nome, $valor, $flag, $em])
                            <div class="m-list-item d-flex justify-content-between align-items-center gap-2">
                                <div>
                                    <div>{{ $nome }} · {{ $euros($valor) }}</div>
                                    @if($flag && $em)<div class="small text-muted">Recebida a {{ $data($em) }}</div>@endif
                                </div>
                                {!! $pago($flag) !!}
                            </div>
                        @endforeach

                        <div class="fw-semibold mt-3">Custos do processo</div>
                        @foreach($custos as [$nome, $valor, $flag])
                            <div class="m-list-item d-flex justify-content-between align-items-center gap-2">
                                <div>{{ $nome }} · {{ $euros($valor) }}</div>
                                @if($flag !== null){!! $pago($flag) !!}@endif
                            </div>
                        @endforeach
                        <div class="m-list-item d-flex justify-content-between fw-bold">
                            <span>Total do serviço</span><span>{{ $euros($converted->serviceTotal()) }}</span>
                        </div>

                        <div class="fw-semibold mt-3">Fora do serviço Izzycar</div>
                        <div class="m-list-item d-flex justify-content-between align-items-center gap-2">
                            <div>Valor do carro · {{ $euros($converted->valor_carro) }}</div>
                            {!! $pago($converted->carro_pago) !!}
                        </div>
                        <div class="m-list-item d-flex justify-content-between align-items-center gap-2">
                            <div>ISV · {{ $euros($converted->isv) }}</div>
                            {!! $pago($converted->isv_pago) !!}
                        </div>
                        @if($converted->valor_comissao_final !== null)
                            <div class="m-list-item">Comissão final · {{ $euros($converted->valor_comissao_final) }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Comissão do angariador --}}
        @if($canSeeAngariadorCommission)
            @php $comissao = $converted->angariadorCommissionAmount(); @endphp
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-comissao" aria-expanded="false" aria-controls="sec-comissao">
                        <i class="bi bi-cash-coin me-2"></i> Comissão do angariador
                    </button>
                </h2>
                <div id="sec-comissao" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div>Valor: <strong>{{ $comissao !== null ? $euros($comissao) : '—' }}</strong> para <strong>{{ $converted->owner->name ?? '—' }}</strong></div>
                        @if($converted->comissao_paga)
                            <div class="text-success small mt-1"><i class="bi bi-check-circle me-1"></i>Paga em {{ $data($converted->comissao_paga_em) ?? '—' }}</div>
                        @elseif($cancelada)
                            <div class="text-muted small mt-1">Processo cancelado: não há comissão a pagar.</div>
                        @elseif($converted->isCommissionOverdue())
                            <div class="text-danger small mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Pendente — em atraso (mais de 48h desde a entrega).</div>
                        @else
                            <div class="text-warning small mt-1"><i class="bi bi-hourglass-split me-1"></i>Pendente — paga quando o estado avança para "Entrega".</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Notas --}}
        @if($canSeeFinance)
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-notas" aria-expanded="false" aria-controls="sec-notas">
                        <i class="bi bi-journal-text me-2"></i> Observações
                    </button>
                </h2>
                <div id="sec-notas" class="accordion-collapse collapse">
                    <div class="accordion-body d-grid gap-2">
                        <div>
                            <div class="fw-semibold">Observações</div>
                            <div class="small {{ $converted->observacoes ? '' : 'text-muted' }}" style="white-space: pre-line">{{ $converted->observacoes ?: 'Sem observações.' }}</div>
                        </div>
                        @if($converted->contactos_stand)
                            <div>
                                <div class="fw-semibold">Contactos do stand</div>
                                <div class="small" style="white-space: pre-line">{{ $converted->contactos_stand }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="d-grid gap-2 mt-3">
        @if($movementsCount)
            <a href="{{ route('admin.mobile.movements.index', ['convertida' => $converted->id, 'year' => 'all']) }}" class="btn btn-outline-primary">
                <i class="bi bi-journal-text me-1"></i> Movimentos desta importação ({{ $movementsCount }})
            </a>
        @endif
        @if($canViewProposal)
            <a href="{{ route('admin.mobile.proposals.show', $proposta->id) }}" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-text me-1"></i> Cotação de origem
            </a>
        @endif
        @canroute('admin.v2.converted-proposals.edit')
        <a href="{{ route('admin.v2.converted-proposals.edit', $converted->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-display me-1"></i> Abrir no desktop
        </a>
        <div class="small text-muted text-center">Pagamentos, documentos e contrato editam-se no desktop.</div>
        @endcanroute
    </div>
@endsection

@push('scripts')
<script>
    // Etapas de burocracia interna não avisam o cliente por omissão; o utilizador decide sempre.
    document.querySelectorAll('[data-m-status-form]').forEach((form) => {
        const select = form.querySelector('select[name="status"]');
        const aviso = form.querySelector('#m-converted-notify');
        select.addEventListener('change', () => {
            if (aviso) aviso.checked = select.selectedOptions[0]?.dataset.silent !== '1';
        });
        form.addEventListener('submit', (event) => {
            if (select.value === 'Cancelada' && !confirm('Cancelar esta cotação convertida?')) {
                event.preventDefault();
            }
        });
    });
</script>
@endpush
