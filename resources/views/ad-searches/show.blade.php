@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
<h2>
    {{ $adSearch->brand }} {{ $adSearch->model }} {{ $adSearch->submodel }}
    ({{ $adSearch->year_start }} - {{ $adSearch->year_end }}) - {{ $adSearch->fuel }} - {{ $adSearch->description }}
</h2>
    


<div class="row g-4 mb-4">
    <!-- Preço médio -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100 p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="text-muted">Preço Mín / Máx</h6>
                <small class="text-muted"></small>
            </div>
            <h4 class="fw-bold text-primary">{{ number_format($listings->min('price'), 0, ',', '.') }} € - {{ number_format($listings->max('price'), 0, ',', '.') }} €</h4>
            <small class="text-muted">
                Média : {{ number_format($listings->avg('price'), 0, ',', '.') }} €
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100 p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="text-muted">Quilometragem Mín / Máx</h6>
                <small class="text-muted"></small>
            </div>
            <h4 class="fw-bold text-primary">{{ number_format($listings->min('mileage'), 0, ',', '.') }} km - {{ number_format($listings->max('mileage'), 0, ',', '.') }} km</h4>
            <small class="text-muted">
                Média : {{ number_format($listings->avg('mileage'), 0, ',', '.') }} km
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100 p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="text-muted">Intervalo de Anos</h6>
                <small class="text-muted"></small>
            </div>
            <h4 class="fw-bold text-primary">{{ $listings->min('year') }} - {{ $listings->max('year') }}</h4>
            <small class="text-muted">
                Média : {{ intval($listings->avg('year')) }}
            </small>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Ano</th>
                <th>KM</th>
                <th>Transmissão</th>
                <th>Link</th>
                <th>Ativo</th>
                <th>Preço</th>
                <th>Média similares</th>
                <th>Diferença</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($evaluatedListings as $listing)
            <tr @class(['text-muted'=> !$listing->active])>
                <td>{{ $listing->external_id }}</td>
                <td>{{ $listing->title }}</td>
                <td>{{ $listing->year }}</td>
                <td style="white-space: nowrap;">{{ number_format($listing->mileage, 0, ',', ' ') }} km</td>
                <td>{{ $listing->gearbox }}</td>
                <td> <span
                        style="cursor: pointer;"
                        onclick="navigator.clipboard.writeText('{{ $listing->url }}'); alert('Link copiado!');"
                        title="Copiar link">
                        🔗
                    </span></td>
                <td>
                    @if($listing->active)
                    ✅
                    @else
                    ❌
                    @endif
                </td>
                <td style="white-space: nowrap;">{{ number_format($listing->price, 0, ',', ' ') }} €</td>
                <td>
                    @if($listing->avg_price_similar)
                    {{ number_format($listing->avg_price_similar, 0, ',', ' ') }} €
                    @else
                    <em>—</em>
                    @endif
                </td>
                <td>
                    @if($listing->price_diff)
                    <span class="{{ $listing->price_diff < 0 ? 'text-success' : 'text-danger' }}">
                        {{ $listing->price_diff > 0 ? '+' : '' }}{{ number_format($listing->price_diff, 0, ',', ' ') }} €
                        ({{ number_format($listing->price_diff_percent, 1, ',', '') }}%)
                    </span>
                    @else
                    <em>—</em>
                    @endif
                </td>
                <td>
                    <a href="{{ route('ad-searches.detail', ['listing' => $listing->id, 'price_diff_percent' => $listing->price_diff_percent, 'avg_price_similar' => $listing->avg_price_similar, 'price_diff' => $listing->price_diff]) }}" class="btn btn-sm btn-info" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>


</div>

<a href="{{ route('ad-searches.index') }}" class="btn btn-danger mb-4">Voltar</a>
        </div>
    </div>
</div>
@endsection