{{--
Espera: $listings (paginator), $sort, $dir, $radarSearch
Opcionais: $averageToggle (bool), $ranks (array id=>posição), $scores (array id=>score bruto 0-3),
           $stars (array id=>true, só para AutoScout24),
           $showSource (bool, mostra de que site (Standvirtual/Carmine) veio cada anúncio),
           $importCost (float, custo de importação somado ao preço mostrado - só para a tabela AutoScout24),
           $newSince (Carbon|null, anúncios com first_seen_at posterior a isto levam badge "novo")

Cada cartão mostra o "nível de oportunidade" (RadarValueScoreService::tier())
através de uma cor discreta na borda esquerda + selo - a mesma informação que
o Nº de ranking já dava, mas percetível de relance sem ler números.
--}}
@php
    $averageToggle = $averageToggle ?? false;
    $ranks = $ranks ?? [];
    $scores = $scores ?? [];
    $stars = $stars ?? [];
    $showSource = $showSource ?? false;
    $importCost = $importCost ?? 0;
    $newSince = $newSince ?? null;
    $sourceLabels = ['standvirtual' => 'Standvirtual', 'carmine' => 'Carmine.pt', 'autoscout24' => 'AutoScout24'];
@endphp

<div class="d-flex flex-wrap align-items-center gap-3 mb-3 radar-sort-bar">
    <span class="text-muted small fw-semibold">Ordenar por:</span>
    <span class="radar-sort-chip">@include('admin.v2.radar._sort-link', ['field' => 'rank', 'label' => 'Nº'])</span>
    <span class="radar-sort-chip">@include('admin.v2.radar._sort-link', ['field' => 'first_registration_year', 'label' => 'Ano'])</span>
    <span class="radar-sort-chip">@include('admin.v2.radar._sort-link', ['field' => 'mileage_km', 'label' => 'Kms'])</span>
    <span class="radar-sort-chip">@include('admin.v2.radar._sort-link', ['field' => 'price_eur', 'label' => 'Preço'])</span>
</div>

@forelse($listings as $listing)
    @php
        $tier = \App\Services\RadarValueScoreService::tier($scores[$listing->id] ?? null);
        $isExcluded = $averageToggle && !$listing->include_in_average;
    @endphp
    @if($loop->first)
    <div class="row g-3">
    @endif
        <div class="col-md-6 col-xl-4">
            <div class="radar-card {{ $tier['class'] }} {{ $isExcluded ? 'radar-card--excluded' : '' }}">
                <div class="radar-card-top">
                    <div class="d-flex align-items-center gap-2">
                        @if($averageToggle)
                        <input type="checkbox" class="form-check-input average-toggle mt-0" data-listing-id="{{ $listing->id }}" {{ $listing->include_in_average ? 'checked' : '' }} title="Entra no cálculo do preço médio">
                        @endif
                        <span class="radar-tier-badge">{{ $tier['label'] }}</span>
                        @if(isset($ranks[$listing->id]))
                            <span class="badge bg-light text-dark border">#{{ $ranks[$listing->id] }}</span>
                        @endif
                        @if(!empty($stars[$listing->id]))
                            <span title="Melhor combinação ano/kms/preço do que o melhor anúncio em Portugal">⭐</span>
                        @endif
                    </div>
                    @if($newSince && $listing->first_seen_at && $listing->first_seen_at->gt($newSince))
                        <span class="badge radar-badge-novo">novo</span>
                    @endif
                </div>

                <div class="radar-card-title">
                    {{ $listing->make }} {{ $listing->model }}
                    @if($showSource)
                        <span class="badge bg-light text-dark border ms-1">{{ $sourceLabels[$listing->source] ?? $listing->source }}</span>
                    @endif
                </div>
                @if($listing->version)
                <div class="radar-card-version text-muted small">
                    {{ $listing->version }}
                    @if($listing->trim_price_multiplier && $listing->trim_price_multiplier > 1.05)
                        <span class="radar-trim-note" title="Estimativa por IA de quanto esta versão costuma custar a mais do que a versão base do modelo - o score já tem isto em conta.">
                            trim +{{ number_format(($listing->trim_price_multiplier - 1) * 100, 0) }}%
                        </span>
                    @endif
                </div>
                @endif

                <div class="radar-card-specs">
                    <span><i class="bi bi-calendar3"></i> {{ $listing->first_registration_year ?? '—' }}</span>
                    <span><i class="bi bi-speedometer2"></i> {{ $listing->mileage_km ? number_format($listing->mileage_km, 0, ',', ' ') . ' km' : '—' }}</span>
                    @if($listing->power_hp)
                    <span><i class="bi bi-lightning-charge"></i> {{ $listing->power_hp }} cv</span>
                    @endif
                    @if($listing->fuel)
                    <span><i class="bi bi-fuel-pump"></i> {{ $listing->fuel }}</span>
                    @endif
                </div>

                <div class="radar-card-footer">
                    <div class="radar-card-price">
                        @if($listing->price_eur && $importCost > 0)
                            {{ number_format($listing->price_eur + $importCost, 0, ',', '.') }} €
                            <div class="text-muted small fw-normal">{{ number_format($listing->price_eur, 0, ',', '.') }} € + {{ number_format($importCost, 0, ',', '.') }} € importação</div>
                        @else
                            {{ $listing->price_eur ? number_format($listing->price_eur, 0, ',', '.') . ' €' : '—' }}
                        @endif
                    </div>
                    <div class="radar-card-actions">
                        @if($listing->url)
                        <a href="{{ $listing->url }}" target="_blank" rel="noopener" title="Ver anúncio original">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                        @endif
                        @if($listing->seller_phone)
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $listing->seller_phone) }}" title="Ligar para {{ $listing->seller_name ?? 'o vendedor' }}: {{ $listing->seller_phone }}">
                            <i class="bi bi-telephone"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @if($loop->last)
    </div>
    @endif
@empty
    <div class="text-center text-muted py-5">
        <i class="bi bi-inbox display-6"></i>
        <p class="mt-2 mb-0">Ainda não há anúncios recolhidos.</p>
    </div>
@endforelse

@if($listings->hasPages())
<div class="modern-card-footer">
    {{ $listings->links() }}
</div>
@endif
