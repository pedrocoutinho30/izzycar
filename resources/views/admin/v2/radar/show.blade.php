@extends('layouts.admin-v2')

@section('title', $radarSearch->name)

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.v2.radar.index') }}" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left"></i> Todos os radares
            </a>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-broadcast"></i> {{ $radarSearch->name }}
            </h1>
            <p class="text-muted mb-0">
                {{ $radarSearch->make ? ucfirst($radarSearch->make) : 'Todas as marcas' }}{{ $radarSearch->model ? ' ' . ucfirst($radarSearch->model) : '' }}
                &middot;
                <a href="{{ $radarSearch->base_url }}" target="_blank" rel="noopener">ver pesquisa na AutoScout24 <i class="bi bi-box-arrow-up-right"></i></a>
            </p>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.v2.radar.run', $radarSearch) }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise"></i> Correr novamente
                </button>
            </form>
            <a href="{{ route('admin.v2.radar.edit', $radarSearch) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <form method="POST" action="{{ route('admin.v2.radar.destroy', $radarSearch) }}" onsubmit="return confirm('Apagar a pesquisa &quot;{{ $radarSearch->name }}&quot;? Isto apaga também todos os anúncios e histórico de preços recolhidos. Não pode ser desfeito.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Apagar
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Custo de importação - soma-se ao preço de todos os anúncios alemães para o
         ranking/estrela e as estatísticas refletirem o custo real de trazer o
         carro para Portugal (ver RadarController::updateImportCost). --}}
    <div class="modern-card mb-4">
        <div class="modern-card-body py-3">
            <form method="POST" action="{{ route('admin.v2.radar.update-import-cost', $radarSearch) }}" class="d-flex flex-wrap align-items-end gap-2">
                @csrf
                @method('PATCH')
                <div>
                    <label class="form-label small mb-1"><i class="bi bi-truck"></i> Custo de importação por carro (€)</label>
                    <input type="number" name="import_cost_eur" class="form-control form-control-sm" min="0" step="0.01" style="width:160px" placeholder="Ex.: 2500" value="{{ $radarSearch->import_cost_eur }}">
                </div>
                <button type="submit" class="btn btn-sm btn-outline-primary">Guardar</button>
                <div class="small text-muted mb-1">Transporte, ISV, matrícula, etc. - somado ao preço de todos os anúncios da AutoScout24 abaixo, para o ranking e as médias refletirem o custo real de importar.</div>
            </form>
        </div>
    </div>

    {{-- Resumo comparativo Alemanha vs Portugal - responde "compensa importar?" sem ler as tabelas --}}
    @if($ptStats && $deStats['count'] > 0 && $ptStats['count'] > 0)
        @php
            $diff = $ptStats['avg'] - $deStats['avg'];
            $diffPct = $deStats['avg'] > 0 ? round(($diff / $deStats['avg']) * 100) : 0;
        @endphp
        <div class="modern-card mb-4">
            <div class="modern-card-body">
                <div class="row g-3 align-items-center text-center">
                    <div class="col-md-4">
                        <div class="small text-muted mb-1">🇩🇪 AutoScout24 (Alemanha)</div>
                        <div class="h4 mb-0">{{ number_format($deStats['avg'], 0, ',', '.') }} €</div>
                        <div class="small text-muted">
                            preço médio &middot; {{ $deStats['count'] }} {{ Str::plural('anúncio', $deStats['count']) }}
                            @if($radarSearch->import_cost_eur > 0)
                                <br>(inclui {{ number_format($radarSearch->import_cost_eur, 0, ',', '.') }} € de importação)
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        @if($diff > 0)
                            <div class="h3 mb-0 text-success">
                                <i class="bi bi-piggy-bank"></i> Poupas {{ number_format($diff, 0, ',', '.') }} €
                            </div>
                            <div class="small text-muted">importando da Alemanha ({{ $diffPct }}% mais barato que Portugal)</div>
                        @elseif($diff < 0)
                            <div class="h3 mb-0 text-danger">
                                <i class="bi bi-exclamation-triangle"></i> +{{ number_format(abs($diff), 0, ',', '.') }} €
                            </div>
                            <div class="small text-muted">a Alemanha está {{ abs($diffPct) }}% mais cara que Portugal</div>
                        @else
                            <div class="h3 mb-0 text-muted">Preços equivalentes</div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted mb-1">🇵🇹 Portugal ({{ $radarSearch->standvirtual_base_url && $radarSearch->carmine_base_url ? 'Standvirtual + Carmine.pt' : ($radarSearch->carmine_base_url ? 'Carmine.pt' : 'Standvirtual') }})</div>
                        <div class="h4 mb-0">{{ number_format($ptStats['avg'], 0, ',', '.') }} €</div>
                        <div class="small text-muted">preço médio &middot; {{ $ptStats['count'] }} {{ Str::plural('anúncio', $ptStats['count']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Filtro por intervalo (partilhado pelas duas tabelas abaixo) --}}
    <div class="modern-card mb-4">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="bi bi-funnel"></i> Filtrar (aplica-se às duas tabelas abaixo)</h5>
        </div>
        <form method="GET" action="{{ route('admin.v2.radar.show', $radarSearch) }}" class="modern-card-body">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="dir" value="{{ $dir }}">
            <div class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small mb-1">Kms, de</label>
                    <input type="number" name="km_min" class="form-control form-control-sm" min="0" style="width:120px" value="{{ request('km_min') }}">
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Kms, até</label>
                    <input type="number" name="km_max" class="form-control form-control-sm" min="0" style="width:120px" value="{{ request('km_max') }}">
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Preço, de (€)</label>
                    <input type="number" name="price_min" class="form-control form-control-sm" min="0" style="width:120px" value="{{ request('price_min') }}">
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Preço, até (€)</label>
                    <input type="number" name="price_max" class="form-control form-control-sm" min="0" style="width:120px" value="{{ request('price_max') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
                    @if(request()->hasAny(['km_min', 'km_max', 'price_min', 'price_max']))
                    <a href="{{ route('admin.v2.radar.show', array_filter(['radarSearch' => $radarSearch, 'sort' => $sort, 'dir' => $dir])) }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Anúncios ativos - Alemanha (AutoScout24) --}}
    <div class="modern-card mb-4">
        <div class="modern-card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="modern-card-title mb-0">
                🇩🇪 AutoScout24 (Alemanha)
                <span class="badge bg-secondary ms-2">{{ $listings->total() }}</span>
            </h5>
        </div>
        <div class="modern-card-body py-3">
            @include('admin.v2.radar._price-stats', ['stats' => $deStats])
        </div>
        <p class="text-muted small px-3 pb-2 mb-0">
            <span class="badge bg-light text-dark border">Nº</span> = posição no ranking de melhor combinação ano/kms/preço desta origem (1 = melhor).
            A cor do cartão indica a mesma coisa de forma visual (verde = melhor negócio, vermelho = pior).
            Quando a versão indica um trim acima da base (ex.: "trim +35%"), o preço é ajustado antes de comparar, para não penalizar versões topo de gama só por serem mais caras.
            @if(!empty($deStars))
                &nbsp;⭐ = melhor combinação ano/kms/preço do que o melhor anúncio em Portugal.
            @endif
        </p>
        <div class="modern-card-body p-3">
            @include('admin.v2.radar._listings-table', [
                'listings' => $listings,
                'ranks' => $deRanks,
                'scores' => $deScores,
                'stars' => $deStars,
                'importCost' => (float) ($radarSearch->import_cost_eur ?? 0),
                'newSince' => $newSince,
            ])
        </div>
    </div>

    {{-- Anúncios ativos - Portugal (Standvirtual + Carmine.pt) --}}
    @if($ptListings)
    <div class="modern-card">
        <div class="modern-card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="modern-card-title mb-0">
                🇵🇹 Portugal ({{ $radarSearch->standvirtual_base_url && $radarSearch->carmine_base_url ? 'Standvirtual + Carmine.pt' : ($radarSearch->carmine_base_url ? 'Carmine.pt' : 'Standvirtual') }})
                <span class="badge bg-secondary ms-2">{{ $ptListings->total() }}</span>
            </h5>
        </div>
        <div class="modern-card-body py-3">
            @include('admin.v2.radar._price-stats', ['stats' => $ptStats, 'badgeId' => 'pt-stats'])
        </div>
        <p class="text-muted small px-3 pb-2 mb-0">
            Desmarca um anúncio (ex.: versão de bateria/autonomia diferente, ou duplicado não detetado) para o excluir do preço médio.
            @if($radarSearch->standvirtual_base_url && $radarSearch->carmine_base_url)
                Anúncios que parecem ser o mesmo carro em ambos os sites são automaticamente fundidos num só.
            @endif
        </p>
        <div class="modern-card-body p-3">
            @include('admin.v2.radar._listings-table', [
                'listings' => $ptListings,
                'averageToggle' => true,
                'ranks' => $ptRanks,
                'scores' => $ptScores,
                'showSource' => $radarSearch->standvirtual_base_url && $radarSearch->carmine_base_url,
                'newSince' => $newSince,
            ])
        </div>
    </div>
    @endif

    {{-- Últimas recolhas --}}
    <div class="modern-card mt-4">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="bi bi-clock-history"></i> Últimas recolhas</h5>
        </div>
        <div class="modern-card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Início</th>
                            <th>Estado</th>
                            <th class="text-end">Páginas</th>
                            <th class="text-end">Anúncios encontrados</th>
                            <th>Erro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($runs as $run)
                        <tr>
                            <td>{{ $run->started_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @php
                                    $badgeColor = match($run->status) {
                                        'ok' => 'success',
                                        'blocked' => 'warning',
                                        'error' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeColor }} bg-opacity-75">{{ $run->status }}</span>
                            </td>
                            <td class="text-end">{{ $run->pages_scraped }}</td>
                            <td class="text-end">{{ $run->listings_found }}</td>
                            <td class="small text-muted">{{ $run->error_message ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Ainda não correu nenhuma vez.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@if($ptListings)
<script>
(function () {
    const statsWrap = document.getElementById('pt-stats');
    const toggleUrlTemplate = '{{ route('admin.v2.radar.toggle-average', ['radarListing' => '__ID__']) }}';

    function fmt(value) {
        return Number(value).toLocaleString('pt-PT');
    }

    function updateStats(data) {
        if (!data.count) {
            statsWrap.innerHTML = '<span class="badge bg-secondary bg-opacity-50">Sem anúncios com preço</span>';
            return;
        }
        const word = data.count === 1 ? 'anúncio' : 'anúncios';
        statsWrap.innerHTML = `
            <span class="badge bg-success bg-opacity-75" data-stat="avg">Média: ${fmt(data.avg)} € (${data.count} ${word})</span>
            <span class="badge bg-secondary bg-opacity-75" data-stat="median">Mediana: ${fmt(data.median)} €</span>
            <span class="badge bg-success" data-stat="min"><i class="bi bi-arrow-down"></i> ${fmt(data.min)} €</span>
            <span class="badge bg-danger" data-stat="max"><i class="bi bi-arrow-up"></i> ${fmt(data.max)} €</span>
        `;
    }

    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('average-toggle')) return;

        const checkbox = e.target;
        const listingId = checkbox.dataset.listingId;
        checkbox.disabled = true;

        fetch(toggleUrlTemplate.replace('__ID__', listingId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ include: checkbox.checked }),
        })
            .then(r => r.json())
            .then(data => {
                const card = checkbox.closest('.radar-card');
                card.classList.toggle('radar-card--excluded', !checkbox.checked);
                updateStats(data);
            })
            .finally(() => { checkbox.disabled = false; });
    });
})();
</script>
@endif

@push('styles')
<style>
    /* Radar — cartões de oportunidade (RadarValueScoreService::tier()).
       Cor discreta (borda esquerda + selo pequeno), não o cartão inteiro,
       para o sinal ser visível sem ficar berrante. */
    .radar-card {
        position: relative;
        height: 100%;
        background: #fff;
        border: 1px solid var(--admin-border, #dee2e6);
        border-left: 4px solid transparent;
        border-radius: var(--border-radius, 12px);
        padding: 0.9rem 1rem;
        transition: var(--transition, all .2s ease);
    }

    .radar-card:hover {
        box-shadow: var(--shadow-sm, 0 2px 8px rgba(0,0,0,.08));
    }

    .radar-card--excluded {
        opacity: .5;
    }

    .radar-tier-excelente { border-left-color: #198754; }
    .radar-tier-bom       { border-left-color: #6fbf73; }
    .radar-tier-medio     { border-left-color: #ffc107; }
    .radar-tier-fraco     { border-left-color: #dc3545; }
    .radar-tier-none      { border-left-color: #adb5bd; }

    .radar-tier-badge {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
    }

    .radar-tier-excelente .radar-tier-badge { background: rgba(25,135,84,.12); color: #146c43; }
    .radar-tier-bom .radar-tier-badge       { background: rgba(111,191,115,.15); color: #3f7a44; }
    .radar-tier-medio .radar-tier-badge     { background: rgba(255,193,7,.18); color: #8a6400; }
    .radar-tier-fraco .radar-tier-badge     { background: rgba(220,53,69,.12); color: #a52834; }
    .radar-tier-none .radar-tier-badge      { background: rgba(173,181,189,.2); color: #495057; }

    .radar-badge-novo {
        background: rgba(13,110,253,.12);
        color: #0d47a1;
        font-weight: 600;
    }

    .radar-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .radar-card-title {
        font-weight: 700;
        color: var(--admin-secondary, #111);
    }

    .radar-card-version {
        margin-bottom: 0.4rem;
    }

    .radar-trim-note {
        display: inline-block;
        margin-left: 0.4rem;
        font-size: 0.7rem;
        font-weight: 700;
        color: #8a6400;
        background: rgba(255,193,7,.15);
        border-radius: 999px;
        padding: 0.05rem 0.5rem;
        cursor: help;
    }

    .radar-card-specs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem 0.9rem;
        font-size: 0.82rem;
        color: #495057;
        margin: 0.5rem 0 0.7rem;
    }

    .radar-card-specs i { color: var(--admin-primary, #6e0707); margin-right: 0.2rem; }

    .radar-card-footer {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 0.5rem;
        padding-top: 0.6rem;
        border-top: 1px solid var(--admin-light, #f8f9fa);
    }

    .radar-card-price {
        font-weight: 800;
        font-size: 1.05rem;
        color: var(--admin-secondary, #111);
    }

    .radar-card-actions a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: var(--admin-light, #f8f9fa);
        color: var(--admin-primary, #6e0707);
        margin-left: 0.35rem;
    }

    .radar-sort-chip a { font-size: 0.85rem; font-weight: 600; }
</style>
@endpush
@endsection
