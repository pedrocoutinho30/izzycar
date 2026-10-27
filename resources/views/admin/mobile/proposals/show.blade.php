@extends('layouts.admin-mobile')

@section('title', trim($proposal->brand . ' ' . $proposal->model))

@php
    $rota = fn (string $nome) => \Illuminate\Support\Facades\Route::has($nome);
    $cliente = $proposal->client;
    // A cotação vive dentro da lead/cliente: voltar para lá.
    $fichaCliente = match (true) {
        ! $canViewClient => null,
        (bool) $cliente->is_lead => route('admin.mobile.leads.show', $cliente->id),
        $rota('admin.mobile.clients.show') => route('admin.mobile.clients.show', $cliente->id),
        default => null,
    };
    $estado = $proposal->status ?? 'Pendente';
    $estadoCores = ['Pendente' => 'warning', 'Enviado' => 'info', 'Aprovada' => 'success', 'Reprovada' => 'danger', 'Sem resposta' => 'secondary'];
    $validaAte = $proposal->created_at->copy()->addDays(\App\Models\Proposal::VALIDITY_DAYS);
    $expirada = $proposal->isExpired();
    $linkPublico = route('proposals.detail', $proposal->proposal_code);
    $euros = fn ($valor) => number_format((float) $valor, 2, ',', '.') . ' €';
    $custos = [
        'Valor do carro' => $proposal->proposed_car_value,
        'Transporte' => $proposal->transport_cost,
        'Inspeção na origem' => $proposal->inspection_commission_cost,
        'IPO' => $proposal->ipo_cost,
        'IMT' => $proposal->imt_cost,
        'Registo automóvel' => $proposal->registration_cost,
        'Matrícula' => $proposal->license_plate_cost,
        'ISV' => $proposal->isv_cost,
        'Comissão' => $proposal->commission_cost,
    ];
    $outrosLinks = collect(json_decode((string) $proposal->other_links, true) ?: [])->filter(fn ($link) => is_string($link) && filled($link));
@endphp

@section('header_actions')
    <a href="{{ $fichaCliente ?? route('admin.mobile.proposals.index') }}" class="order-first m-topbar-back" aria-label="Voltar"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    {{-- Resumo --}}
    <div class="m-card">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="min-w-0">
                <div class="fw-semibold fs-5 text-break">
                    <x-admin.brand-logo :brand="$proposal->brand" :size="24" class="me-1" />
                    {{ trim($proposal->brand . ' ' . $proposal->model) }}
                </div>
                @if($proposal->version)<div class="text-muted small">{{ $proposal->version }}</div>@endif
                <div class="small mt-1">
                    @if($fichaCliente)
                        <a href="{{ $fichaCliente }}" class="d-inline-flex align-items-center" style="min-height:44px">{{ $cliente->name }}</a>
                    @else
                        {{ $cliente?->name ?? 'Sem cliente' }}
                    @endif
                </div>
            </div>
            <span class="badge bg-{{ $estadoCores[$estado] ?? 'secondary' }} flex-shrink-0">{{ $estado }}</span>
        </div>

        <div class="fs-4 fw-bold mt-2">{{ $euros($proposal->totalCost()) }}</div>
        <div class="small {{ $expirada ? 'text-danger' : 'text-muted' }}">
            Cotação #{{ $proposal->id }} · criada a {{ $proposal->created_at->format('d/m/Y') }} ·
            @if($expirada)
                <span class="badge bg-danger">Expirada</span> desde {{ $validaAte->format('d/m/Y') }}
            @else
                válida até {{ $validaAte->format('d/m/Y') }}
            @endif
        </div>
        <div class="small text-muted">A validade ({{ \App\Models\Proposal::VALIDITY_DAYS }} dias) conta desde a criação; editar ou reenviar não a renova.</div>
    </div>

    {{-- Link público para o cliente --}}
    <div class="m-card">
        <label class="fw-semibold mb-1" for="m-proposal-link"><i class="bi bi-link-45deg me-1"></i> Link para o cliente</label>
        <input type="text" id="m-proposal-link" class="form-control" value="{{ $linkPublico }}" readonly>
        <div class="d-grid gap-2 mt-2">
            <button type="button" class="btn btn-primary" data-m-share data-url="{{ $linkPublico }}"
                    data-title="Cotação {{ trim($proposal->brand . ' ' . $proposal->model) }}">
                <i class="bi bi-share me-1"></i> Copiar / partilhar
            </button>
            <a href="{{ $linkPublico }}" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right me-1"></i> Abrir página do cliente</a>
        </div>
        <div class="small text-success mt-1 d-none" data-m-share-feedback role="status">Link copiado.</div>
    </div>

    {{-- Convertida --}}
    @if($converted)
        <div class="m-card">
            <div class="fw-semibold"><i class="bi bi-check-circle text-success me-1"></i> Cotação aceite</div>
            <div class="small text-muted">Cotação convertida #{{ $converted->id }} · {{ $converted->status ?? 'Iniciada' }}</div>
            @if($canViewConverted)
                <a href="{{ route('admin.mobile.converted-proposals.show', $converted->id) }}" class="btn btn-dark w-100 mt-2">Ver cotação convertida</a>
            @endif
        </div>
    @endif

    <div class="accordion m-accordion" id="proposalSeccoes">
        {{-- Custos --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sec-custos" aria-expanded="true" aria-controls="sec-custos">
                    <i class="bi bi-currency-euro me-2"></i> Custos
                </button>
            </h2>
            <div id="sec-custos" class="accordion-collapse collapse show">
                <div class="accordion-body">
                    <dl class="m-dl mb-0">
                        @foreach($custos as $nome => $valor)
                            <dt>{{ $nome }}</dt><dd class="text-end">{{ $euros($valor) }}</dd>
                        @endforeach
                        <dt class="fw-bold text-dark">Total</dt><dd class="text-end fw-bold">{{ $euros($proposal->totalCost()) }}</dd>
                        @if((float) $proposal->iuc_cost > 0)
                            <dt>IUC (à parte)</dt><dd class="text-end">{{ $euros($proposal->iuc_cost) }}</dd>
                        @endif
                    </dl>
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
                        <dt>Ano</dt><dd>{{ $proposal->proposed_car_year_month ?: ($proposal->year ?: '—') }}</dd>
                        <dt>Quilómetros</dt><dd>{{ $proposal->proposed_car_mileage !== null ? number_format($proposal->proposed_car_mileage, 0, ',', '.') . ' km' : '—' }}</dd>
                        <dt>Combustível</dt><dd>{{ $proposal->fuel ?: '—' }}</dd>
                        <dt>Código</dt><dd class="font-monospace">{{ $proposal->proposal_code }}</dd>
                    </dl>
                    @if($proposal->proposed_car_features)
                        <div class="fw-semibold mt-2">Equipamento</div>
                        <div class="small" style="white-space: pre-line">{{ $proposal->proposed_car_features }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Notas e links --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-notas" aria-expanded="false" aria-controls="sec-notas">
                    <i class="bi bi-journal-text me-2"></i> Notas e links
                </button>
            </h2>
            <div id="sec-notas" class="accordion-collapse collapse">
                <div class="accordion-body d-grid gap-2">
                    <div>
                        <div class="fw-semibold">Notas internas</div>
                        @if($proposal->notes)
                            <div class="small" style="white-space: pre-line">{{ $proposal->notes }}</div>
                        @else
                            <div class="small text-muted">Sem notas.</div>
                        @endif
                    </div>
                    @foreach(collect([$proposal->url])->merge($outrosLinks)->filter() as $link)
                        @if(\App\Support\SafeUrl::isHttp($link))
                            <a href="{{ trim($link) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary text-start text-truncate">
                                <i class="bi bi-box-arrow-up-right me-1"></i> {{ $link }}
                            </a>
                        @else
                            <div class="small text-break">{{ $link }}</div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Estado e aceitação --}}
    @if($canUpdate && ! $converted)
        <div class="m-card mt-3" id="estado">
            <div class="fw-semibold mb-2"><i class="bi bi-circle-half me-1"></i> Estado</div>
            <div class="d-grid gap-2">
                @foreach($statuses as $valor)
                    @continue($valor === $estado || $valor === 'Aprovada')
                    @if($valor === 'Enviado')
                        <form action="{{ route('admin.mobile.proposals.status', $proposal->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="Enviado">
                            <input type="hidden" name="notify" value="1">
                            <button type="submit" class="btn btn-outline-info w-100 text-start" onclick="return confirm('Marcar como Enviado? Envia um email ao cliente com o link da cotação.')">Marcar como Enviado (email ao cliente)</button>
                        </form>
                        <form action="{{ route('admin.mobile.proposals.status', $proposal->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="Enviado">
                            <input type="hidden" name="notify" value="0">
                            <button type="submit" class="btn btn-outline-info w-100 text-start">Marcar como Enviado sem email</button>
                        </form>
                    @else
                        <form action="{{ route('admin.mobile.proposals.status', $proposal->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $valor }}">
                            <button type="submit" class="btn btn-outline-{{ $estadoCores[$valor] ?? 'secondary' }} w-100 text-start">Marcar como {{ $valor }}</button>
                        </form>
                    @endif
                @endforeach
            </div>

            <form action="{{ route('admin.mobile.proposals.accept', $proposal->id) }}" method="POST" class="d-grid mt-3">
                @csrf
                <button type="submit" class="btn btn-success fw-semibold"
                        onclick="return confirm('Aceitar esta cotação? Cria a cotação convertida e converte a lead em cliente. Não envia emails nem o contrato.')">
                    <i class="bi bi-check2-circle me-1"></i> Aceitar cotação
                </button>
            </form>
        </div>
    @elseif($converted)
        <p class="small text-muted mt-2">Uma cotação aceite não muda de estado — para a anular, cancele a cotação convertida.</p>
    @endif

    <div class="d-grid gap-2 mt-3">
        @if($canUpdate)
            <a href="{{ route('admin.mobile.proposals.edit', $proposal->id) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i> Editar cotação</a>
        @endif
        @canroute('admin.v2.proposals.edit')
        <a href="{{ route('admin.v2.proposals.edit', $proposal->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-display me-1"></i> Abrir no desktop
        </a>
        @endcanroute
    </div>
@endsection

@push('scripts')
<script>
    // Partilhar o link (folha de partilha do iPhone); sem ela, copia para a área de transferência.
    document.querySelectorAll('[data-m-share]').forEach((botao) => {
        botao.addEventListener('click', async () => {
            const url = botao.dataset.url;
            const aviso = document.querySelector('[data-m-share-feedback]');
            if (navigator.share) {
                try {
                    await navigator.share({ title: botao.dataset.title, url });
                    return;
                } catch (erro) {
                    if (erro.name === 'AbortError') return;
                }
            }
            try {
                await navigator.clipboard.writeText(url);
            } catch (erro) {
                const campo = document.getElementById('m-proposal-link');
                campo.select();
                document.execCommand('copy');
            }
            aviso?.classList.remove('d-none');
        });
    });
</script>
@endpush
