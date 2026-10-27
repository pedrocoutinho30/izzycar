@extends('layouts.admin-mobile')

@section('title', 'Tarefas')

@section('content')
    @php
        $estadoCores = ['pendente' => 'warning', 'em_progresso' => 'info', 'concluida' => 'success', 'cancelada' => 'secondary'];
        // Os chips mantêm a pesquisa e o "só minhas" e trocam só o filtro.
        $chipUrl = fn (string $f) => route('admin.mobile.tasks.index', array_filter([
            'filtro' => $f, 'search' => $search, 'minhas' => $minhas ? null : '0',
        ], 'filled'));
        $minhasUrl = route('admin.mobile.tasks.index', array_filter([
            'filtro' => $filtro, 'search' => $search, 'minhas' => $minhas ? '0' : null,
        ], 'filled'));
    @endphp

    <div class="m-sticky-filters">
        <form method="GET" action="{{ route('admin.mobile.tasks.index') }}" role="search">
            <input type="hidden" name="filtro" value="{{ $filtro }}">
            @unless($minhas)
                <input type="hidden" name="minhas" value="0">
            @endunless
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ $search }}" class="form-control" style="font-size:16px"
                       placeholder="Título ou descrição" aria-label="Pesquisar tarefas" enterkeyhint="search">
            </div>
        </form>

        <div class="m-chips" role="group" aria-label="Filtrar tarefas">
            @foreach($filtros as $chave => $rotulo)
                <a href="{{ $chipUrl($chave) }}" class="m-chip {{ $filtro === $chave ? 'active' : '' }}">{{ $rotulo }}</a>
            @endforeach
            <a href="{{ $minhasUrl }}" class="m-chip {{ $minhas ? 'active' : '' }}" aria-pressed="{{ $minhas ? 'true' : 'false' }}">
                <i class="bi bi-person me-1"></i> Só minhas
            </a>
        </div>
    </div>

    <div id="m-tasks-list">
        @forelse($tasks as $task)
            @php
                $aberta = in_array($task->status, \App\Services\TaskService::STATUS_ABERTOS, true);
                $prazo = $task->due_date?->toDateString();
                $atrasada = $aberta && $prazo !== null && $prazo < $hoje;
                $paraHoje = $aberta && $prazo === $hoje;
                $responsavel = $task->user?->name ?? $task->user_name;
            @endphp
            <div class="m-card m-lead-card">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <strong class="m-lead-card__name">{{ $task->title }}</strong>
                    <span class="badge bg-{{ $estadoCores[$task->status] ?? 'secondary' }} flex-shrink-0">{{ $task->status_label }}</span>
                </div>
                @if($task->description)
                    <div class="small text-muted mt-1 text-break">{{ \Illuminate\Support\Str::limit($task->description, 140) }}</div>
                @endif
                <div class="small mt-1 {{ $atrasada ? 'text-danger fw-semibold' : '' }}">
                    <i class="bi bi-calendar-event"></i> {{ $task->due_date?->format('d/m/Y') ?? 'Sem prazo' }}
                    @if($atrasada) · Em atraso @elseif($paraHoje) · Hoje @endif
                </div>
                @if($responsavel || $task->v3Vehicle)
                    <div class="text-muted small mt-1 text-break">
                        @if($responsavel)<i class="bi bi-person"></i> {{ $responsavel }}@endif
                        @if($responsavel && $task->v3Vehicle) · @endif
                        @if($task->v3Vehicle)<i class="bi bi-car-front"></i> {{ $task->v3Vehicle->reference }} {{ $task->v3Vehicle->brand }} {{ $task->v3Vehicle->model }}@endif
                    </div>
                @endif
                @if($canUpdate)
                    <div class="m-contact-actions">
                        @if($aberta)
                            <form method="POST" action="{{ route('admin.mobile.tasks.complete', $task->id) }}" class="flex-fill d-flex">
                                @csrf
                                <button type="submit" class="btn btn-success flex-fill"><i class="bi bi-check2-circle me-1"></i> Concluir</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.mobile.tasks.edit', $task->id) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Editar</a>
                    </div>
                @endif
            </div>
        @empty
            <div class="m-card text-center text-muted py-4">
                <i class="bi bi-check2-square d-block fs-2 mb-1"></i>
                Nenhuma tarefa encontrada.
            </div>
        @endforelse
    </div>

    @if($tasks->nextPageUrl())
        <a href="{{ $tasks->nextPageUrl() }}" class="btn btn-outline-secondary w-100" id="m-load-more">Carregar mais</a>
    @endif

    @canroute('admin.tasks.index')
        <a href="{{ route('admin.tasks.index') }}" class="btn btn-link w-100 mt-2"><i class="bi bi-display me-1"></i> Abrir no desktop (calendário)</a>
    @endcanroute

    @if($canCreate)
        <a href="{{ route('admin.mobile.tasks.create') }}" class="m-fab" aria-label="Nova tarefa"><i class="bi bi-plus-lg"></i></a>
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
            pagina.querySelectorAll('#m-tasks-list > .m-lead-card').forEach((cartao) => {
                document.getElementById('m-tasks-list').appendChild(cartao);
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
