@extends('layouts.admin-mobile')

@section('title', 'Convertidas')

@section('content')
    @php
        // Os chips mantêm a pesquisa e trocam só a etapa.
        $chipUrl = fn (?string $etapa) => route('admin.mobile.converted-proposals.index', array_filter([
            'search' => request('search'),
            'status' => $etapa,
        ], 'filled'));
    @endphp

    <div class="m-sticky-filters">
        <form method="GET" action="{{ route('admin.mobile.converted-proposals.index') }}" role="search">
            @if($estado)
                <input type="hidden" name="status" value="{{ $estado }}">
            @endif
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="Cliente, marca, modelo ou matrícula" aria-label="Pesquisar cotações convertidas" enterkeyhint="search">
            </div>
        </form>

        <div class="m-chips" role="group" aria-label="Filtrar por etapa">
            <a href="{{ $chipUrl(null) }}" class="m-chip {{ blank($estado) ? 'active' : '' }}">Todas</a>
            @foreach($statuses as $valor)
                <a href="{{ $chipUrl($valor) }}" class="m-chip {{ $estado === $valor ? 'active' : '' }}">{{ $valor }}</a>
            @endforeach
        </div>
    </div>

    <div id="m-converted-list">
        @forelse($convertedProposals as $convertida)
            @php
                $etapa = $convertida->status ?: 'Iniciada';
                $cor = match (true) {
                    $convertida->isCancelled() => 'danger',
                    $etapa === 'Concluido' => 'success',
                    default => 'info',
                };
            @endphp
            <div class="m-card m-lead-card">
                <a href="{{ route('admin.mobile.converted-proposals.show', $convertida->id) }}" class="m-lead-card__link">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <strong class="m-lead-card__name">
                            <x-admin.brand-logo :brand="$convertida->brand" :size="22" class="me-1" />
                            {{ trim($convertida->brand . ' ' . $convertida->modelCar . ' ' . $convertida->version) ?: 'Sem viatura' }}
                        </strong>
                        <span class="badge bg-{{ $cor }} flex-shrink-0">{{ $etapa }}</span>
                    </div>
                    <div class="small mt-1">{{ $convertida->client?->name ?? 'Sem cliente' }}</div>
                    <div class="text-muted small">
                        #{{ $convertida->id }} · {{ $convertida->created_at->format('d/m/Y') }}
                        @if($convertida->matricula_destino) · {{ $convertida->matricula_destino }} @endif
                    </div>
                </a>
            </div>
        @empty
            <div class="m-card text-center text-muted py-4">
                <i class="bi bi-arrow-repeat d-block fs-2 mb-1"></i>
                Nenhuma cotação convertida encontrada.
            </div>
        @endforelse
    </div>

    @if($convertedProposals->nextPageUrl())
        <a href="{{ $convertedProposals->nextPageUrl() }}" class="btn btn-outline-secondary w-100" id="m-load-more">Carregar mais</a>
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
            pagina.querySelectorAll('#m-converted-list > .m-lead-card').forEach((cartao) => {
                document.getElementById('m-converted-list').appendChild(cartao);
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
