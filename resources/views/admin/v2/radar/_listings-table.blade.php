{{--
Espera: $listings (paginator), $sort, $dir, $radarSearch
Opcionais: $averageToggle (bool), $ranks (array id=>posição), $stars (array id=>true, só para AutoScout24),
           $cheapestId, $mostExpensiveId, $showSource (bool, mostra de que site (Standvirtual/Carmine) veio cada anúncio),
           $importCost (float, custo de importação somado ao preço mostrado - só para a tabela AutoScout24)
--}}
@php
    $averageToggle = $averageToggle ?? false;
    $ranks = $ranks ?? [];
    $stars = $stars ?? [];
    $cheapestId = $cheapestId ?? null;
    $mostExpensiveId = $mostExpensiveId ?? null;
    $showSource = $showSource ?? false;
    $importCost = $importCost ?? 0;
    $sourceLabels = ['standvirtual' => 'Standvirtual', 'carmine' => 'Carmine.pt', 'autoscout24' => 'AutoScout24'];
@endphp
<div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                @if($averageToggle)
                <th class="text-center" title="Entra no cálculo do preço médio">Média</th>
                @endif
                <th class="text-center" title="Posição no ranking de melhor combinação ano/kms/preço desta origem">@include('admin.v2.radar._sort-link', ['field' => 'rank', 'label' => 'Nº'])</th>
                @if($showSource)
                <th>Origem</th>
                @endif
                <th>Marca / Modelo</th>
                <th>Versão</th>
                <th class="text-center">@include('admin.v2.radar._sort-link', ['field' => 'first_registration_year', 'label' => 'Ano'])</th>
                <th class="text-end">@include('admin.v2.radar._sort-link', ['field' => 'mileage_km', 'label' => 'Kms'])</th>
                <th class="text-end">@include('admin.v2.radar._sort-link', ['field' => 'price_eur', 'label' => 'Preço'])</th>
                <th class="text-end">Potência</th>
                <th>Combustível</th>
                <th>Caixa</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($listings as $listing)
            @php
                $rowClasses = [];
                $rowStyles = [];
                if ($averageToggle && !$listing->include_in_average) {
                    $rowClasses[] = 'text-muted';
                    $rowStyles[] = 'opacity:.55;';
                }
                if ($listing->id === $cheapestId) {
                    $rowStyles[] = 'outline: 2px solid #198754; outline-offset: -2px;';
                } elseif ($listing->id === $mostExpensiveId) {
                    $rowStyles[] = 'outline: 2px solid #dc3545; outline-offset: -2px;';
                }
            @endphp
            <tr class="{{ implode(' ', $rowClasses) }}" style="{{ implode(' ', $rowStyles) }}"
                @if($listing->id === $cheapestId) title="Mais barato desta origem"
                @elseif($listing->id === $mostExpensiveId) title="Mais caro desta origem"
                @endif>
                @if($averageToggle)
                <td class="text-center">
                    <input type="checkbox" class="form-check-input average-toggle" data-listing-id="{{ $listing->id }}" {{ $listing->include_in_average ? 'checked' : '' }}>
                </td>
                @endif
                <td class="text-center text-nowrap">
                    @if(!empty($stars[$listing->id]))
                        <span title="Melhor combinação ano/kms/preço do que o melhor anúncio em Portugal">⭐</span>
                    @endif
                    @if(isset($ranks[$listing->id]))
                        <span class="badge bg-light text-dark border">#{{ $ranks[$listing->id] }}</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                @if($showSource)
                <td>
                    <span class="badge bg-light text-dark border">{{ $sourceLabels[$listing->source] ?? $listing->source }}</span>
                </td>
                @endif
                <td class="fw-semibold">{{ $listing->make }} {{ $listing->model }}</td>
                <td class="text-muted small">{{ $listing->version ?? '—' }}</td>
                <td class="text-center">{{ $listing->first_registration_year ?? '—' }}</td>
                <td class="text-end text-nowrap">{{ $listing->mileage_km ? number_format($listing->mileage_km, 0, ',', ' ') . ' km' : '—' }}</td>
                <td class="text-end text-nowrap fw-semibold">
                    @if($listing->price_eur && $importCost > 0)
                        {{ number_format($listing->price_eur + $importCost, 0, ',', '.') }} €
                        <div class="text-muted small fw-normal">{{ number_format($listing->price_eur, 0, ',', '.') }} € + {{ number_format($importCost, 0, ',', '.') }} € importação</div>
                    @else
                        {{ $listing->price_eur ? number_format($listing->price_eur, 0, ',', '.') . ' €' : '—' }}
                    @endif
                </td>
                <td class="text-end text-nowrap">{{ $listing->power_hp ? $listing->power_hp . ' cv' : '—' }}</td>
                <td>{{ $listing->fuel ?? '—' }}</td>
                <td>{{ $listing->gearbox ?? '—' }}</td>
                <td class="text-nowrap">
                    @if($listing->url)
                    <a href="{{ $listing->url }}" target="_blank" rel="noopener" title="Ver anúncio original">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    @endif
                    @if($listing->seller_phone)
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $listing->seller_phone) }}" class="ms-2" title="Ligar para {{ $listing->seller_name ?? 'o vendedor' }}: {{ $listing->seller_phone }}">
                        <i class="bi bi-telephone"></i>
                    </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 10 + ($averageToggle ? 1 : 0) + ($showSource ? 1 : 0) }}" class="text-center text-muted py-5">
                    <i class="bi bi-inbox display-6"></i>
                    <p class="mt-2 mb-0">Ainda não há anúncios recolhidos.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($listings->hasPages())
<div class="modern-card-footer">
    {{ $listings->links() }}
</div>
@endif
