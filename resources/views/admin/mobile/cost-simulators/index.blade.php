@extends('layouts.admin-mobile')

@section('title', 'Simulador de custos')

@inject('phones', 'App\Services\PhoneNumberService')

@section('content')
    @php
        $euros = fn ($valor) => number_format((float) $valor, 2, ',', '.') . ' €';
    @endphp

    <div class="m-sticky-filters">
        <form method="GET" action="{{ route('admin.mobile.cost-simulators.index') }}" role="search">
            @if($filterClientId)
                <input type="hidden" name="client_id" value="{{ $filterClientId }}">
            @endif
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ $search }}" class="form-control"
                       placeholder="Cliente, email, telefone, marca ou modelo" aria-label="Pesquisar simulações" enterkeyhint="search">
            </div>
        </form>

        @if($filterClient)
            <div class="m-chips" role="group" aria-label="Filtro por cliente">
                <a href="{{ route('admin.mobile.cost-simulators.index', array_filter(['search' => $search], 'filled')) }}" class="m-chip active">
                    {{ $filterClient->name }} <i class="bi bi-x-lg ms-1"></i><span class="visually-hidden">Remover filtro</span>
                </a>
            </div>
        @elseif($filterClientId)
            <div class="m-chips" role="group" aria-label="Filtro por cliente">
                <a href="{{ route('admin.mobile.cost-simulators.index', array_filter(['search' => $search], 'filled')) }}" class="m-chip active">
                    Cliente #{{ $filterClientId }} <i class="bi bi-x-lg ms-1"></i><span class="visually-hidden">Remover filtro</span>
                </a>
            </div>
        @endif
    </div>

    <div id="m-simulators-list">
        @forelse($costSimulators as $sim)
            @php
                $cliente = $sim->client;
                $tel = $cliente ? $phones->telUrl($cliente->phone) : null;
                $whatsapp = $cliente ? $phones->whatsappUrl($cliente->phone) : null;
            @endphp
            <div class="m-card m-lead-card">
                <a href="{{ route('admin.mobile.cost-simulators.show', $sim->id) }}" class="m-lead-card__link">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <strong class="m-lead-card__name">{{ $cliente?->name ?? 'Cliente removido' }}</strong>
                        @unless($sim->read)
                            <span class="badge bg-warning text-dark flex-shrink-0">Nova</span>
                        @endunless
                    </div>
                    <div class="small mt-1">{{ trim(($sim->brand ?? '') . ' ' . ($sim->model ?? '')) ?: 'Simulação' }}</div>
                    <div class="small text-muted mt-1">
                        Carro {{ $euros($sim->car_value) }} · ISV {{ $euros($sim->isv_cost) }}
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="fw-semibold text-success">Total {{ $euros($sim->total_cost) }}</span>
                        <span class="text-muted small">{{ $sim->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($cliente && ($cliente->email || $cliente->phone))
                        <div class="text-muted small mt-1 text-break">
                            {{ $cliente->phone }}@if($cliente->phone && $cliente->email) · @endif{{ $cliente->email }}
                        </div>
                    @endif
                </a>
                @if($tel || $cliente?->email)
                    <div class="m-contact-actions">
                        @if($tel)
                            <a href="{{ $tel }}" class="btn btn-outline-secondary" aria-label="Ligar"><i class="bi bi-telephone"></i></a>
                        @endif
                        @if($whatsapp)
                            <a href="{{ $whatsapp }}" class="btn btn-outline-success" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        @endif
                        @if($cliente->email)
                            <a href="mailto:{{ $cliente->email }}" class="btn btn-outline-primary" aria-label="Email"><i class="bi bi-envelope"></i></a>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="m-card text-center text-muted py-4">
                <i class="bi bi-calculator d-block fs-2 mb-1"></i>
                Nenhuma simulação encontrada.
            </div>
        @endforelse
    </div>

    @if($costSimulators->nextPageUrl())
        <a href="{{ $costSimulators->nextPageUrl() }}" class="btn btn-outline-secondary w-100" id="m-load-more">Carregar mais</a>
    @endif
@endsection

@push('scripts')
<script>
    // "Carregar mais" acrescenta a página seguinte à lista; sem JS, o link abre a página.
    document.addEventListener('click', async (event) => {
        const botao = event.target.closest('#m-load-more');
        if (!botao) return;
        event.preventDefault();
        botao.classList.add('disabled');
        try {
            const resposta = await fetch(botao.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!resposta.ok) throw new Error('HTTP ' + resposta.status); // abre a página inteira
            const pagina = new DOMParser().parseFromString(await resposta.text(), 'text/html');
            pagina.querySelectorAll('#m-simulators-list > .m-lead-card').forEach((cartao) => {
                document.getElementById('m-simulators-list').appendChild(cartao);
            });
            const seguinte = pagina.getElementById('m-load-more');
            if (seguinte) {
                botao.href = seguinte.href;
                botao.classList.remove('disabled');
            } else {
                botao.remove();
            }
        } catch (erro) {
            window.location = botao.href;
        }
    });
</script>
@endpush
