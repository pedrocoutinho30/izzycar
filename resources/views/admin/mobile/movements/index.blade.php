@extends('layouts.admin-mobile')

@section('title', 'Movimentos')

@php
    $euros = fn ($valor) => number_format((float) $valor, 2, ',', '.') . ' €';
    // Filtros ativos (sem os nulos), para os links dos chips e "limpar".
    $ativos = array_filter($filters, fn ($v) => $v !== null);
    $com = fn (array $mudar) => route('admin.mobile.movements.index', array_filter(array_merge($ativos, $mudar), fn ($v) => $v !== null));
    // Filtros do painel (além da pesquisa e do tipo, que têm chips próprios).
    $noPainel = collect(['category', 'status', 'v3_vehicle_id', 'date_from', 'date_to'])->filter(fn ($k) => $filters[$k] !== null)->count()
        + ($filters['year'] !== (string) now()->year ? 1 : 0);
    $statusColors = ['paid' => 'success', 'pending' => 'warning', 'partially_paid' => 'info', 'cancelled' => 'secondary'];
    $periodo = match (true) {
        $hasDates => 'Totais de sempre (com datas, o ano não conta)',
        $filters['year'] === 'all' => 'Totais desde o início',
        default => 'Totais de ' . $filters['year'],
    };
@endphp

@section('content')
    <div class="m-sticky-filters">
        <form method="GET" action="{{ route('admin.mobile.movements.index') }}" role="search">
            @foreach(collect($ativos)->except(['search']) as $chave => $valor)
                <input type="hidden" name="{{ $chave }}" value="{{ $valor }}">
            @endforeach
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ $filters['search'] }}" class="form-control"
                       placeholder="Título ou observações" aria-label="Pesquisar movimentos" enterkeyhint="search">
            </div>
        </form>

        <div class="m-chips" role="group" aria-label="Tipo de movimento">
            <a href="{{ $com(['movement_type' => null]) }}" class="m-chip {{ $filters['movement_type'] === null ? 'active' : '' }}">Todos</a>
            @foreach(\App\Models\Expense::movementTypes() as $valor => $nome)
                <a href="{{ $com(['movement_type' => $valor]) }}" class="m-chip {{ $filters['movement_type'] === $valor ? 'active' : '' }}">{{ $nome }}s</a>
            @endforeach
            <button type="button" class="m-chip {{ $noPainel ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#m-mov-filtros"
                    aria-expanded="false" aria-controls="m-mov-filtros">
                <i class="bi bi-funnel me-1"></i> Filtros{{ $noPainel ? " ({$noPainel})" : '' }}
            </button>
            @if($filterClient)
                <a href="{{ $com(['client_id' => null]) }}" class="m-chip active">
                    {{ $filterClient->name }} <i class="bi bi-x-lg ms-1"></i><span class="visually-hidden">Remover filtro</span>
                </a>
            @elseif($filters['client_id'])
                <a href="{{ $com(['client_id' => null]) }}" class="m-chip active">
                    Cliente #{{ $filters['client_id'] }} <i class="bi bi-x-lg ms-1"></i><span class="visually-hidden">Remover filtro</span>
                </a>
            @endif
            @if($filters['convertida'])
                <a href="{{ $com(['convertida' => null]) }}" class="m-chip active">
                    Convertida #{{ $filters['convertida'] }} <i class="bi bi-x-lg ms-1"></i><span class="visually-hidden">Remover filtro</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Filtros do desktop: ano, categoria, estado, viatura e datas --}}
    <div class="collapse" id="m-mov-filtros">
        <form method="GET" action="{{ route('admin.mobile.movements.index') }}" class="m-card d-grid gap-3">
            @foreach(collect($ativos)->only(['search', 'movement_type', 'client_id', 'convertida']) as $chave => $valor)
                <input type="hidden" name="{{ $chave }}" value="{{ $valor }}">
            @endforeach

            <div>
                <label for="mov-year" class="form-label fw-semibold">Ano</label>
                <select id="mov-year" name="year" class="form-select">
                    <option value="all" @selected($filters['year'] === 'all')>Desde o início</option>
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" @selected((string) $y === $filters['year'])>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="mov-category" class="form-label fw-semibold">Categoria</label>
                <select id="mov-category" name="category" class="form-select">
                    <option value="">Todas</option>
                    @foreach(\App\Models\Expense::categories() as $valor => $nome)
                        <option value="{{ $valor }}" @selected($filters['category'] === $valor)>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="mov-status" class="form-label fw-semibold">Estado</label>
                <select id="mov-status" name="status" class="form-select">
                    <option value="">Todos</option>
                    @foreach(\App\Models\Expense::statuses() as $valor => $nome)
                        <option value="{{ $valor }}" @selected($filters['status'] === $valor)>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="mov-vehicle" class="form-label fw-semibold">Viatura</label>
                <select id="mov-vehicle" name="v3_vehicle_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected($filters['v3_vehicle_id'] === $v->id)>{{ $v->reference }} – {{ $v->brand }} {{ $v->model }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label for="mov-from" class="form-label fw-semibold">Data de</label>
                    <input type="date" id="mov-from" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                </div>
                <div class="col-6">
                    <label for="mov-to" class="form-label fw-semibold">Data até</label>
                    <input type="date" id="mov-to" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                </div>
            </div>
            <div class="small text-muted"><i class="bi bi-info-circle me-1"></i>Se preencher datas, o filtro de ano é ignorado.</div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">Aplicar</button>
                <a href="{{ route('admin.mobile.movements.index') }}" class="btn btn-outline-secondary flex-fill d-inline-flex align-items-center justify-content-center">Limpar</a>
            </div>
        </form>
    </div>

    {{-- Totais e saldos: os mesmos dos cartões do desktop --}}
    <div class="m-card">
        <div class="small text-muted mb-2">{{ $periodo }}</div>
        <div class="row g-2">
            <div class="col-6">
                <div class="small text-muted">Total entradas</div>
                <div class="fw-bold text-success">{{ $euros($summary['totalIncome']) }}</div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Total saídas</div>
                <div class="fw-bold text-danger">{{ $euros($summary['totalExpenses']) }}</div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Saldo (c/ pendentes)</div>
                <div class="fw-bold {{ $summary['saldo'] >= 0 ? 'text-primary' : 'text-danger' }}">{{ $euros($summary['saldo']) }}</div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Saldo real (só pagos)</div>
                <div class="fw-bold {{ $summary['saldoReal'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $euros($summary['saldoReal']) }}</div>
            </div>
        </div>
        <div class="small text-muted border-top mt-2 pt-2">
            {{ $filteredCount }} {{ $filteredCount === 1 ? 'movimento' : 'movimentos' }} com estes filtros
            · <span class="text-success">+{{ $euros($filteredIncome) }}</span>
            · <span class="text-danger">−{{ $euros($filteredExpenses) }}</span>
        </div>
    </div>

    <div id="m-movements-list">
        @forelse($movements as $mov)
            @php
                $entrada = $mov->isIncome();
                $valor = $mov->amount_gross ?? $mov->amount;
            @endphp
            <div class="m-card m-lead-card">
                <a href="{{ route('admin.mobile.movements.show', $mov->id) }}" class="m-lead-card__link">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <strong class="m-lead-card__name min-w-0">{{ $mov->title }}</strong>
                        <span class="fw-bold flex-shrink-0 {{ $entrada ? 'text-success' : 'text-danger' }}">{{ $entrada ? '+' : '−' }}{{ $euros($valor) }}</span>
                    </div>
                    @if($mov->v3Vehicle)
                        <div class="small text-muted mt-1"><i class="bi bi-car-front me-1"></i>{{ $mov->v3Vehicle->brand }} {{ $mov->v3Vehicle->model }} ({{ $mov->v3Vehicle->reference }})</div>
                    @endif
                    @if($mov->client)
                        <div class="small text-muted mt-1"><i class="bi bi-person me-1"></i>{{ $mov->client->name }}</div>
                    @endif
                    <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                        <span class="badge bg-light text-dark border">{{ $mov->category_label }}</span>
                        <span class="badge bg-{{ $statusColors[$mov->status] ?? 'secondary' }}">{{ $mov->status_label }}</span>
                        @if($mov->source_type)
                            <span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-lock-fill me-1"></i>Auto</span>
                        @endif
                        <span class="text-muted small ms-auto">{{ $mov->expense_date?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                </a>
            </div>
        @empty
            <div class="m-card text-center text-muted py-4">
                <i class="bi bi-journal-text d-block fs-2 mb-1"></i>
                @if($filters['year'] !== 'all' || count($ativos) > 1)
                    Sem movimentos com estes filtros.
                    <a href="{{ route('admin.mobile.movements.index', ['year' => 'all']) }}" class="d-block mt-2">Ver desde o início, sem filtros</a>
                @else
                    Ainda não existem movimentos registados.
                @endif
            </div>
        @endforelse
    </div>

    @if($movements->nextPageUrl())
        <a href="{{ $movements->nextPageUrl() }}" class="btn btn-outline-secondary w-100" id="m-load-more">Carregar mais</a>
    @endif

    @canroute('admin.v2.movements.index')
        <a href="{{ route('admin.v2.movements.index', array_filter(collect($ativos)->except(['convertida'])->all(), fn ($v) => $v !== null)) }}" class="btn btn-link w-100 mt-2">
            <i class="bi bi-display me-1"></i> Abrir no desktop (exportar, gráficos)
        </a>
    @endcanroute

    @if($canCreate)
        <a href="{{ route('admin.mobile.movements.create') }}" class="m-fab" aria-label="Novo movimento"><i class="bi bi-plus-lg"></i></a>
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
            pagina.querySelectorAll('#m-movements-list > .m-lead-card').forEach((cartao) => {
                document.getElementById('m-movements-list').appendChild(cartao);
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
