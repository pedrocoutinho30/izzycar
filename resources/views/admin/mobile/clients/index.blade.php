@extends('layouts.admin-mobile')

@section('title', 'Clientes')

@inject('phones', 'App\Services\PhoneNumberService')

@section('content')
    @php
        $tipoAtual = request('client_type');
        // Os chips mantêm a pesquisa e trocam só o tipo.
        $chipUrl = fn (?string $tipo) => route('admin.mobile.clients.index', array_filter([
            'search' => request('search'),
            'client_type' => $tipo,
        ], 'filled'));
    @endphp

    <div class="m-sticky-filters">
        <form method="GET" action="{{ route('admin.mobile.clients.index') }}" role="search">
            @if($tipoAtual)
                <input type="hidden" name="client_type" value="{{ $tipoAtual }}">
            @endif
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="Nome, email, telefone ou NIF" aria-label="Pesquisar clientes" enterkeyhint="search">
            </div>
        </form>

        <div class="m-chips" role="group" aria-label="Filtrar por tipo">
            <a href="{{ $chipUrl(null) }}" class="m-chip {{ blank($tipoAtual) ? 'active' : '' }}">Todos</a>
            <a href="{{ $chipUrl('particular') }}" class="m-chip {{ $tipoAtual === 'particular' ? 'active' : '' }}">Particulares</a>
            <a href="{{ $chipUrl('empresa') }}" class="m-chip {{ $tipoAtual === 'empresa' ? 'active' : '' }}">Empresas</a>
        </div>
    </div>

    <div id="m-clients-list">
        @forelse($clients as $client)
            @php
                $tel = $phones->telUrl($client->phone);
                $whatsapp = $phones->whatsappUrl($client->phone);
            @endphp
            <div class="m-card m-lead-card">
                <a href="{{ route('admin.mobile.clients.show', $client->id) }}" class="m-lead-card__link">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <strong class="m-lead-card__name">{{ $client->name }}</strong>
                        @if($client->client_type)
                            <span class="badge bg-secondary flex-shrink-0">{{ ucfirst(mb_strtolower($client->client_type)) }}</span>
                        @endif
                    </div>
                    <div class="text-muted small mt-1">
                        Cliente desde {{ ($client->converted_at ?? $client->created_at)->format('d/m/Y') }}
                        @if($client->city) · {{ $client->city }} @endif
                    </div>
                    @if($client->next_followup_at)
                        <div class="small mt-1 {{ $client->next_followup_at->isPast() ? 'text-danger' : 'text-muted' }}">
                            <i class="bi bi-alarm"></i> {{ $client->next_followup_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </a>
                @if($tel || $client->email)
                    <div class="m-contact-actions">
                        @if($tel)
                            <a href="{{ $tel }}" class="btn btn-outline-secondary" aria-label="Ligar"><i class="bi bi-telephone"></i></a>
                        @endif
                        @if($whatsapp)
                            <a href="{{ $whatsapp }}" class="btn btn-outline-success" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        @endif
                        @if($client->email)
                            <a href="mailto:{{ $client->email }}" class="btn btn-outline-primary" aria-label="Email"><i class="bi bi-envelope"></i></a>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="m-card text-center text-muted py-4">
                <i class="bi bi-person-lines-fill d-block fs-2 mb-1"></i>
                Nenhum cliente encontrado.
            </div>
        @endforelse
    </div>

    @if($clients->nextPageUrl())
        <a href="{{ $clients->nextPageUrl() }}" class="btn btn-outline-secondary w-100" id="m-load-more">Carregar mais</a>
    @endif

    @if($canCreate)
        <a href="{{ route('admin.mobile.clients.create') }}" class="m-fab" aria-label="Novo cliente"><i class="bi bi-plus-lg"></i></a>
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
            const pagina = new DOMParser().parseFromString(await resposta.text(), 'text/html');
            pagina.querySelectorAll('#m-clients-list > .m-lead-card').forEach((cartao) => {
                document.getElementById('m-clients-list').appendChild(cartao);
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
