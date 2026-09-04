{{-- Espera: $stats (array de RadarController::priceStats), $badgeId (opcional, para atualização via JS) --}}
<div class="d-flex flex-wrap gap-2 align-items-center" id="{{ $badgeId ?? '' }}">
    @if($stats['count'] > 0)
        <span class="badge bg-success bg-opacity-75" data-stat="avg">
            Média: {{ number_format($stats['avg'], 0, ',', '.') }} € ({{ $stats['count'] }} {{ Str::plural('anúncio', $stats['count']) }})
        </span>
        <span class="badge bg-secondary bg-opacity-75" data-stat="median">
            Mediana: {{ number_format($stats['median'], 0, ',', '.') }} €
        </span>
        <span class="badge bg-success" data-stat="min">
            <i class="bi bi-arrow-down"></i> {{ number_format($stats['min'], 0, ',', '.') }} €
        </span>
        <span class="badge bg-danger" data-stat="max">
            <i class="bi bi-arrow-up"></i> {{ number_format($stats['max'], 0, ',', '.') }} €
        </span>
    @else
        <span class="badge bg-secondary bg-opacity-50">Sem anúncios com preço</span>
    @endif
</div>
