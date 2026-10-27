@extends('layouts.admin-mobile')

@php
    $sim = $costSimulator;
    $euros = fn ($valor) => number_format((float) $valor, 2, ',', '.') . ' €';
    $fichaCliente = match (true) {
        ! $canViewClient => null,
        (bool) $client->is_lead => route('admin.mobile.leads.show', $client->id),
        default => route('admin.mobile.clients.show', $client->id),
    };
    // Custos pela ordem do desktop: [nome, valor].
    $custos = array_values(array_filter([
        ['Valor do carro', $sim->car_value],
        ['ISV (Imposto sobre Veículos)', $sim->isv_cost],
        ['Comissão', $sim->commission_cost],
        ['Inspeção na origem', $sim->inspection_commission_cost],
        ['Transporte', $sim->transport],
        ['IPO (Inspeção Periódica Obrigatória)', $sim->ipo_cost],
        ['IMT (Imposto Municipal sobre Transações)', $sim->imt_cost],
        ['Registo automóvel', $sim->registration_cost],
        ['Matrículas', $sim->plates_cost],
    ]));
@endphp

@section('title', 'Simulação #' . $sim->id)

@inject('phones', 'App\Services\PhoneNumberService')

@section('header_actions')
    <a href="{{ route('admin.mobile.cost-simulators.index') }}" class="order-first m-topbar-back" aria-label="Voltar"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    @php
        $tel = $client ? $phones->telUrl($client->phone) : null;
        $whatsapp = $client ? $phones->whatsappUrl($client->phone) : null;
    @endphp

    {{-- Resumo --}}
    <div class="m-card">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="min-w-0">
                <div class="fw-semibold fs-5 text-break">{{ trim(($sim->brand ?? '') . ' ' . ($sim->model ?? '')) ?: 'Simulação' }}</div>
                <div class="small mt-1">
                    @if($fichaCliente)
                        <a href="{{ $fichaCliente }}" class="d-inline-flex align-items-center" style="min-height:44px">{{ $client->name }}</a>
                    @else
                        {{ $client?->name ?? 'Cliente removido' }}
                    @endif
                </div>
            </div>
            @if($sim->read)
                <span class="badge bg-success flex-shrink-0">Lido</span>
            @else
                <span class="badge bg-warning text-dark flex-shrink-0">Nova</span>
            @endif
        </div>
        <div class="small text-muted">Simulação #{{ $sim->id }} · {{ $sim->created_at->format('d/m/Y H:i') }}</div>
        <div class="d-flex justify-content-between align-items-center mt-3 p-3 rounded" style="background:#fbf1f1; border:1px solid var(--admin-primary)">
            <span class="fw-semibold">Custo total</span>
            <span class="fs-4 fw-bold" style="color:var(--admin-primary)">{{ $euros($sim->total_cost) }}</span>
        </div>

        @if($tel || $client?->email)
            <div class="m-contact-actions">
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
    </div>

    {{-- Cliente --}}
    <div class="m-card">
        <h2 class="h6 mb-3"><i class="bi bi-person me-1"></i> Cliente</h2>
        @if($client)
            <dl class="m-dl mb-0">
                <dt>Nome</dt><dd class="text-break">{{ $client->name }}</dd>
                <dt>Email</dt><dd class="text-break">{{ $client->email ?: '—' }}</dd>
                <dt>Telefone</dt><dd>{{ $client->phone ?: '—' }}</dd>
                <dt>Origem</dt><dd><span class="badge bg-info"><i class="bi bi-calculator"></i> Simulador de custos</span></dd>
            </dl>
            @if($fichaCliente)
                <a href="{{ $fichaCliente }}" class="btn btn-outline-primary w-100 mt-3 d-flex align-items-center justify-content-center" style="min-height:44px">
                    <i class="bi bi-person me-1"></i> {{ $client->is_lead ? 'Ver lead' : 'Ver cliente' }}
                </a>
            @endif
        @else
            <div class="alert alert-warning mb-0">Cliente não encontrado ou foi removido.</div>
        @endif
    </div>

    {{-- Viatura e dados submetidos --}}
    <div class="m-card">
        <h2 class="h6 mb-3"><i class="bi bi-car-front me-1"></i> Viatura</h2>
        <dl class="m-dl mb-0">
            <dt>Marca</dt><dd>{{ $sim->brand ?: '—' }}</dd>
            <dt>Modelo</dt><dd>{{ $sim->model ?: '—' }}</dd>
            @if($sim->year)
                <dt>Ano</dt><dd>{{ $sim->year }}</dd>
            @endif
            @if($sim->fuel)
                <dt>Combustível</dt><dd><span class="badge bg-secondary"><i class="bi bi-fuel-pump"></i> {{ ucfirst($sim->fuel) }}</span></dd>
            @endif
            @if($sim->cc)
                <dt>Cilindrada</dt><dd>{{ $sim->cc }} cc</dd>
            @endif
            @if($sim->co2)
                <dt>CO₂</dt><dd>{{ $sim->co2 }} g/km</dd>
            @endif
            @if($sim->tipo_veiculo)
                <dt>Tipo de veículo</dt><dd>{{ ucfirst(str_replace('_', ' ', $sim->tipo_veiculo)) }}</dd>
            @endif
            @if($sim->pais_matricula)
                <dt>País da matrícula</dt>
                <dd>{{ $sim->pais_matricula == 'uniao-europeia' ? 'União Europeia' : ucfirst($sim->pais_matricula) }}</dd>
            @endif
            @if($sim->autonomia)
                <dt>Autonomia da bateria</dt>
                <dd>{{ $sim->autonomia == 'igual_superior' ? 'Igual ou superior a 50 km' : 'Inferior a 50 km' }}</dd>
            @endif
            @if($sim->emissao_particulas)
                <dt>Emissão de partículas</dt>
                <dd>{{ $sim->emissao_particulas == '+0.0001' ? '> 0.0001 g/km' : '≤ 0.0001 g/km' }}</dd>
            @endif
        </dl>
    </div>

    {{-- Custos calculados --}}
    <div class="m-card">
        <h2 class="h6 mb-2"><i class="bi bi-currency-euro me-1"></i> Custos</h2>
        @foreach($custos as [$nome, $valor])
            <div class="m-list-item d-flex justify-content-between align-items-start gap-3">
                <span>{{ $nome }}</span>
                <span class="fw-semibold text-nowrap">{{ $euros($valor) }}</span>
            </div>
        @endforeach
        <div class="d-flex justify-content-between align-items-center gap-3 mt-2 pt-2 border-top border-2">
            <span class="fw-bold">CUSTO TOTAL</span>
            <span class="fw-bold fs-5" style="color:var(--admin-primary)">{{ $euros($sim->total_cost) }}</span>
        </div>
    </div>

    {{-- Registo --}}
    <div class="m-card">
        <h2 class="h6 mb-3"><i class="bi bi-clock-history me-1"></i> Registo</h2>
        <dl class="m-dl mb-0">
            <dt>Criado em</dt><dd>{{ $sim->created_at->format('d/m/Y H:i') }}</dd>
            <dt>Atualizado em</dt><dd>{{ $sim->updated_at->format('d/m/Y H:i') }}</dd>
        </dl>
    </div>

    {{-- Eliminar só no desktop --}}
    <a href="{{ route('admin.v2.cost-simulators.show', $sim->id) }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center mb-3" style="min-height:44px">
        <i class="bi bi-display me-1"></i> Abrir no desktop
    </a>
@endsection
