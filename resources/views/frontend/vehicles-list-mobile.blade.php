@php use Illuminate\Support\Str; @endphp

{{-- Filtros colapsáveis no mobile --}}
<div class="vl-mobile-filters mb-4">
    <button class="vl-mobile-filter-toggle" type="button"
        data-bs-toggle="collapse" data-bs-target="#mobileFiltersCollapse">
        <i class="bi bi-sliders me-2"></i>Filtros
        <span id="mobile-active-count" class="vl-mobile-active-count d-none">0</span>
        <i class="bi bi-chevron-down ms-auto" id="mobileFilterChevron"></i>
    </button>

    <div class="collapse" id="mobileFiltersCollapse">
        <form id="filter-form-mobile" onsubmit="return false;" class="vl-mobile-filter-body">
            <div class="row g-2">
                <div class="col-6">
                    <label class="vl-filter-label-m">Marca</label>
                    <select name="brand" id="brandMobile" class="form-select form-select-sm vl-select-m">
                        <option value="">Todas</option>
                        @foreach($vehicles->pluck('brand')->filter()->unique()->sort() as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="vl-filter-label-m">Modelo</label>
                    <select name="model" id="modelMobile" class="form-select form-select-sm vl-select-m" disabled>
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="vl-filter-label-m">Ano</label>
                    <select name="year" id="yearMobile" class="form-select form-select-sm vl-select-m">
                        <option value="">Todos</option>
                        @foreach($vehicles->pluck('year')->filter()->unique()->sortDesc() as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="vl-filter-label-m">Combustível</label>
                    <select name="fuel" id="fuelMobile" class="form-select form-select-sm vl-select-m">
                        <option value="">Todos</option>
                        @foreach($vehicles->pluck('fuel')->filter()->unique()->sort() as $f)
                        <option value="{{ $f }}">{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 pt-1">
                    <button type="button" id="clear-filters-btn-mobile"
                        class="btn btn-outline-secondary btn-sm w-100 d-none"
                        onclick="clearFiltersMobile()">
                        <i class="bi bi-x-circle me-1"></i>Limpar filtros
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="vl-mobile-count">
        <span id="vehicles-count-mobile">{{ $vehicles->count() }}</span> viaturas
    </div>
</div>

{{-- Grid de viaturas --}}
<div id="vehicles-container-mobile" class="row g-3">
    @foreach ($vehicles as $vehicle)
    @php
        $cover = $vehicle->coverPhoto;
        $coverUrl = $cover ? asset('storage/' . $cover->path) : asset('img/no-image.png');
    @endphp
    <div class="col-12">
        <a href="{{ route('vehicles.details', [
            'brand' => Str::slug($vehicle->brand ?? ''),
            'model' => Str::slug($vehicle->model ?? ''),
            'id'    => $vehicle->reference
        ]) }}" class="vlm-card-link">
            <div class="vlm-card">
                <div class="vlm-img-wrap">
                    <img src="{{ $coverUrl }}" loading="lazy"
                        alt="{{ $vehicle->brand }} {{ $vehicle->model }}"
                        style="object-position: {{ $vehicle->coverPhoto?->focal_x ?? 50 }}% {{ $vehicle->coverPhoto?->focal_y ?? 50 }}%">
                    @if($vehicle->status === 'reservado')
                    <span class="vlm-badge-reservado">Reservado</span>
                    @elseif($vehicle->status === 'vendido')
                    <span class="vlm-badge-vendido">Vendido</span>
                    @endif
                </div>
                <div class="vlm-body">
                    <div class="vlm-brand">{{ $vehicle->brand }}</div>
                    <div class="vlm-model">{{ $vehicle->model }}@if($vehicle->sub_model) <span class="vlm-sub">{{ $vehicle->sub_model }}</span>@endif</div>
                    <div class="vlm-specs">
                        @if($vehicle->year)<span>{{ $vehicle->year }}</span>@endif
                        @if($vehicle->fuel)<span>{{ $vehicle->fuel }}</span>@endif
                        @if($vehicle->kilometers)<span>{{ number_format($vehicle->kilometers, 0, ',', ' ') }} km</span>@endif
                    </div>
                    @if($vehicle->asking_price && !in_array($vehicle->status, ['reservado', 'vendido']))
                    <div class="vlm-price-row">
                        <span class="vlm-price-label">Preço</span>
                        <span class="vlm-price">{{ number_format(round($vehicle->asking_price), 0, ',', ' ') }} €</span>
                    </div>
                    @endif
                </div>
            </div>
        </a>
    </div>
    @endforeach

    <div id="no-results-mobile" class="col-12 d-none">
        <div class="text-center py-5">
            <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
            <p class="text-muted">Nenhuma viatura encontrada.</p>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFiltersMobile()">Limpar filtros</button>
        </div>
    </div>
</div>

<style>
/* ── Mobile Filter Bar ────────────────────────────── */
.vl-mobile-filters { background: #fff; border-radius: 14px; border: 1px solid rgba(110,7,7,.1); overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.05); }
.vl-mobile-filter-toggle {
    width: 100%; background: #fdf7f7; border: none; padding: 14px 18px;
    display: flex; align-items: center; font-weight: 700; color: var(--accent-color);
    font-size: .95rem; cursor: pointer;
}
.vl-mobile-active-count {
    background: var(--accent-color); color: #fff;
    border-radius: 50%; width: 20px; height: 20px;
    font-size: .7rem; display: inline-flex; align-items: center; justify-content: center;
    margin-left: 6px; font-weight: 700;
}
.vl-mobile-filter-body { padding: 14px 16px 16px; border-top: 1px solid rgba(110,7,7,.08); }
.vl-filter-label-m { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: #888; font-weight: 600; display: block; margin-bottom: 4px; }
.vl-select-m { border-radius: 8px; font-size: .85rem; border-color: #e0e0e0; }
.vl-select-m:focus { border-color: var(--accent-color); box-shadow: 0 0 0 2px rgba(110,7,7,.1); }
.vl-mobile-count { padding: 8px 18px; font-size: .8rem; color: #888; font-weight: 600; border-top: 1px solid #f0f0f0; text-align: right; }
.vl-mobile-count span { color: var(--accent-color); }

/* ── Vehicle Card Grid ────────────────────────────── */
.vlm-card-link { text-decoration: none; display: block; }
.vlm-card { border-radius: 12px; overflow: hidden; background: #fff; border: 1px solid rgba(110,7,7,.1); box-shadow: 0 2px 10px rgba(0,0,0,.06); transition: transform .2s, box-shadow .2s; }
.vlm-card:active { transform: scale(.98); }
.vlm-img-wrap { position: relative; width: 100%; padding-top: 70%; overflow: hidden; }
.vlm-img-wrap img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: center; }
.vlm-badge-reservado { position: absolute; top: 7px; left: 7px; background: #f59e0b; color: #fff; font-size: .65rem; font-weight: 700; border-radius: 14px; padding: 2px 8px; text-transform: uppercase; }
.vlm-badge-vendido { position: absolute; top: 7px; left: 7px; background: #dc2626; color: #fff; font-size: .65rem; font-weight: 700; border-radius: 14px; padding: 2px 8px; text-transform: uppercase; }
.vlm-body { padding: 10px 12px 12px; }
.vlm-brand { font-size: .95rem; font-weight: 800; color: var(--accent-color); line-height: 1.2; }
.vlm-model { font-size: .85rem; font-weight: 600; color: #333; margin-bottom: 6px; }
.vlm-sub { font-weight: 400; color: #666; font-size: .8rem; }
.vlm-specs { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 8px; }
.vlm-specs span { background: #f5f5f5; border-radius: 10px; font-size: .7rem; color: #555; padding: 2px 8px; }
.vlm-price-row { display: flex; align-items: baseline; gap: .4rem; margin-top: .5rem; padding-top: .5rem; border-top: 1px solid #f0f0f0; }
.vlm-price-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #aaa; }
.vlm-price { font-size: 1.25rem; font-weight: 900; color: var(--accent-color); line-height: 1; letter-spacing: -.02em; }
.vlm-price-contact { font-size: .82rem; color: #888; font-style: italic; }

/* ── Loading skeleton ──────────────────────────────── */
.vlm-skeleton { border-radius: 12px; overflow: hidden; background: #fff; border: 1px solid #eee; }
.vlm-sk-img { width: 100%; padding-top: 70%; background: #f0f0f0; animation: vl-pulse 1.4s ease-in-out infinite; }
.vlm-sk-body { padding: 10px 12px 14px; display: flex; flex-direction: column; gap: 8px; }
.vlm-sk-line { height: 12px; border-radius: 6px; background: #f0f0f0; animation: vl-pulse 1.4s ease-in-out infinite; }
.vlm-sk-line.wide { width: 65%; }
.vlm-sk-line.narrow { width: 40%; }
</style>

<script>
(function () {
    const brandSel = document.getElementById('brandMobile');
    const modelSel = document.getElementById('modelMobile');
    const yearSel  = document.getElementById('yearMobile');
    const fuelSel  = document.getElementById('fuelMobile');
    const container = document.getElementById('vehicles-container-mobile');
    const noResults = document.getElementById('no-results-mobile');
    const countEl   = document.getElementById('vehicles-count-mobile');
    const clearBtn  = document.getElementById('clear-filters-btn-mobile');
    const activeCount = document.getElementById('mobile-active-count');

    function showSkeletons() {
        container.innerHTML = [1,2,3,4].map(() => `
            <div class="col-12">
                <div class="vlm-skeleton">
                    <div class="vlm-sk-img"></div>
                    <div class="vlm-sk-body">
                        <div class="vlm-sk-line wide"></div>
                        <div class="vlm-sk-line narrow"></div>
                        <div class="vlm-sk-line"></div>
                    </div>
                </div>
            </div>`).join('');
        noResults.classList.add('d-none');
    }

    function updateClearBtn() {
        const n = [brandSel, modelSel, yearSel, fuelSel].filter(s => s.value).length;
        clearBtn.classList.toggle('d-none', n === 0);
        activeCount.textContent = n;
        activeCount.classList.toggle('d-none', n === 0);
        activeCount.classList.toggle('d-inline-flex', n > 0);
    }

    function fmt(n) {
        return n ? Number(n).toLocaleString('pt-PT', { maximumFractionDigits: 0 }) : null;
    }

    function renderCard(v) {
        const img = v.cover_url
            ? `<img src="${v.cover_url}" loading="lazy" alt="${v.brand} ${v.model}" style="object-position:${v.cover_focal_x ?? 50}% ${v.cover_focal_y ?? 50}%">`
            : `<div style="position:absolute;top:0;left:0;width:100%;height:100%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;"><i class="bi bi-car-front text-muted fs-2"></i></div>`;
        const badge = v.status === 'reservado'
            ? `<span class="vlm-badge-reservado">Reservado</span>`
            : v.status === 'vendido'
            ? `<span class="vlm-badge-vendido">Vendido</span>` : '';
        const price = v.asking_price && v.status !== 'reservado' && v.status !== 'vendido'
            ? `<div class="vlm-price-row"><span class="vlm-price-label">Preço</span><span class="vlm-price">${fmt(v.asking_price)} €</span></div>`
            : ``;
        const sub = v.sub_model ? ` <span class="vlm-sub">${v.sub_model}</span>` : '';
        const specs = [
            v.year        ? v.year        : null,
            v.fuel        ? v.fuel        : null,
            v.kilometers  ? fmt(v.kilometers) + ' km' : null,
        ].filter(Boolean).map(s => `<span>${s}</span>`).join('');

        return `<div class="col-12">
            <a href="${v.url}" class="vlm-card-link">
                <div class="vlm-card">
                    <div class="vlm-img-wrap">${img}${badge}</div>
                    <div class="vlm-body">
                        <div class="vlm-brand">${v.brand}</div>
                        <div class="vlm-model">${v.model}${sub}</div>
                        <div class="vlm-specs">${specs}</div>
                        ${price}
                    </div>
                </div>
            </a>
        </div>`;
    }

    async function updateModels() {
        modelSel.innerHTML = '<option value="">Todos</option>'; modelSel.disabled = true;
        if (!brandSel.value) return;
        const models = await (await fetch(`/modelos-por-marca?brand=${encodeURIComponent(brandSel.value)}`)).json();
        models.forEach(m => { const o = document.createElement('option'); o.value = m; o.text = m; modelSel.appendChild(o); });
        modelSel.disabled = false;
    }
    async function updateYears() {
        yearSel.innerHTML = '<option value="">Todos</option>'; yearSel.disabled = true;
        if (!brandSel.value) return;
        const years = await (await fetch(`/anos-por-marca-modelo?brand=${encodeURIComponent(brandSel.value)}&model=${encodeURIComponent(modelSel.value)}`)).json();
        years.forEach(y => { const o = document.createElement('option'); o.value = y; o.text = y; yearSel.appendChild(o); });
        yearSel.disabled = false;
    }
    async function updateFuels() {
        const params = new URLSearchParams({ brand: brandSel.value, model: modelSel.value, year: yearSel.value });
        const fuels = await (await fetch(`/combustiveis-por-marca-modelo-ano?${params}`)).json();
        fuelSel.innerHTML = '<option value="">Todos</option>';
        fuels.forEach(f => { const o = document.createElement('option'); o.value = f; o.text = f; fuelSel.appendChild(o); });
    }
    async function updateVehicles() {
        showSkeletons();
        const params = new URLSearchParams({ brand: brandSel.value, model: modelSel.value, year: yearSel.value, fuel: fuelSel.value });
        const vehicles = await (await fetch(`/viaturas-filtradas?${params}`)).json();
        if (!vehicles.length) {
            container.innerHTML = '';
            noResults.classList.remove('d-none');
            countEl.textContent = '0';
            updateClearBtn(); return;
        }
        noResults.classList.add('d-none');
        container.innerHTML = vehicles.map(renderCard).join('');
        countEl.textContent = vehicles.length;
        updateClearBtn();
    }

    window.clearFiltersMobile = function () {
        brandSel.value = '';
        modelSel.innerHTML = '<option value="">Todos</option>'; modelSel.disabled = true;
        yearSel.innerHTML  = '<option value="">Todos</option>'; yearSel.disabled = true;
        fuelSel.innerHTML  = '<option value="">Todos</option>';
        updateClearBtn(); updateVehicles();
    };

    brandSel.addEventListener('change', async () => { await updateModels(); await updateYears(); await updateFuels(); updateVehicles(); });
    modelSel.addEventListener('change', async () => { await updateYears(); await updateFuels(); updateVehicles(); });
    yearSel.addEventListener ('change', async () => { await updateFuels(); updateVehicles(); });
    fuelSel.addEventListener ('change', updateVehicles);

    // Chevron toggle animation
    document.getElementById('mobileFiltersCollapse').addEventListener('show.bs.collapse', () => {
        document.getElementById('mobileFilterChevron').style.transform = 'rotate(180deg)';
    });
    document.getElementById('mobileFiltersCollapse').addEventListener('hide.bs.collapse', () => {
        document.getElementById('mobileFilterChevron').style.transform = '';
    });

    updateVehicles();
})();
</script>
