@extends('layouts.admin-mobile')

@section('title', 'Leads')

@inject('phones', 'App\Services\PhoneNumberService')

@section('content')
    @php
        $estadoAtual = request('lead_status');
        // Os chips mantêm a pesquisa e o outro filtro e trocam só o seu.
        $chipUrl = fn (?string $estado) => route('admin.mobile.leads.index', array_filter([
            'search' => request('search'),
            'lead_status' => $estado,
            'followup' => $followup,
        ], 'filled'));
        $followupUrl = fn (?string $valor) => route('admin.mobile.leads.index', array_filter([
            'search' => request('search'),
            'lead_status' => $estadoAtual,
            'followup' => $valor,
        ], 'filled'));
    @endphp

    <div class="m-sticky-filters">
        <form method="GET" action="{{ route('admin.mobile.leads.index') }}" role="search">
            @if($estadoAtual)
                <input type="hidden" name="lead_status" value="{{ $estadoAtual }}">
            @endif
            @if($followup)
                <input type="hidden" name="followup" value="{{ $followup }}">
            @endif
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="Nome, email ou telefone" aria-label="Pesquisar leads" enterkeyhint="search">
            </div>
        </form>

        <div class="m-chips" role="group" aria-label="Filtrar por estado">
            <a href="{{ $chipUrl(null) }}" class="m-chip {{ blank($estadoAtual) ? 'active' : '' }}">Todas</a>
            @foreach(\App\Enums\LeadStatus::cases() as $estado)
                <a href="{{ $chipUrl($estado->value) }}" class="m-chip {{ $estadoAtual === $estado->value ? 'active' : '' }}">{{ $estado->label() }}</a>
            @endforeach
        </div>

        <div class="m-chips" role="group" aria-label="Filtrar por follow-up">
            <a href="{{ $followupUrl($followup === 'hoje' ? null : 'hoje') }}" class="m-chip {{ $followup === 'hoje' ? 'active' : '' }}">Follow-up hoje</a>
            <a href="{{ $followupUrl($followup === 'atraso' ? null : 'atraso') }}" class="m-chip {{ $followup === 'atraso' ? 'active' : '' }}">Follow-up em atraso</a>
        </div>
    </div>

    <div id="m-leads-list">
        @forelse($leads as $lead)
            @include('admin.mobile.leads._card', ['lead' => $lead])
        @empty
            <div class="m-card text-center text-muted py-4">
                <i class="bi bi-people d-block fs-2 mb-1"></i>
                Nenhuma lead encontrada.
            </div>
        @endforelse
    </div>

    @if($leads->nextPageUrl())
        <a href="{{ $leads->nextPageUrl() }}" class="btn btn-outline-secondary w-100" id="m-load-more">Carregar mais</a>
    @endif

    @if($canCreate)
        <a href="{{ route('admin.mobile.leads.create') }}" class="m-fab" aria-label="Nova lead"><i class="bi bi-plus-lg"></i></a>
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
            pagina.querySelectorAll('#m-leads-list > .m-lead-card').forEach((cartao) => {
                document.getElementById('m-leads-list').appendChild(cartao);
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
