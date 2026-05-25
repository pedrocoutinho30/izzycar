@php use Illuminate\Support\Str; @endphp

<div class="row g-4 align-items-start">

    {{-- Sidebar de filtros --}}
    <div class="col-lg-3">
        <div class="vl-filter-sidebar sticky-top" style="top: 90px;">
            <div class="vl-filter-header">
                <i class="bi bi-sliders me-2"></i>Filtros
                <button id="clear-filters-btn" type="button" class="vl-clear-btn d-none">
                    <i class="bi bi-x-circle me-1"></i>Limpar
                </button>
            </div>

            <form id="filter-form" onsubmit="return false;" class="vl-filter-body">

                <div class="vl-filter-group">
                    <label class="vl-filter-label">Marca</label>
                    <select name="brand" id="brand" class="form-select vl-select">
                        <option value="">Todas as marcas</option>
                        @foreach($vehicles->pluck('brand')->filter()->unique()->sort() as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="vl-filter-group">
                    <label class="vl-filter-label">Modelo</label>
                    <select name="model" id="model" class="form-select vl-select" disabled>
                        <option value="">Todos os modelos</option>
                    </select>
                </div>

                <div class="vl-filter-group">
                    <label class="vl-filter-label">Ano</label>
                    <select name="year" id="year" class="form-select vl-select">
                        <option value="">Todos os anos</option>
                        @foreach($vehicles->pluck('year')->filter()->unique()->sortDesc() as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="vl-filter-group">
                    <label class="vl-filter-label">Combustível</label>
                    <select name="fuel" id="fuel" class="form-select vl-select">
                        <option value="">Todos</option>
                        @foreach($vehicles->pluck('fuel')->filter()->unique()->sort() as $f)
                        <option value="{{ $f }}">{{ $f }}</option>
                        @endforeach
                    </select>
                </div>

            </form>

            {{-- Contador --}}
            <div class="vl-count-badge">
                <span id="vehicles-count">{{ $vehicles->count() }}</span> viaturas encontradas
            </div>
        </div>
    </div>

    {{-- Lista de viaturas --}}
    <div class="col-lg-9">
        <div id="vehicles-container">
            @foreach ($vehicles as $vehicle)
            @php
                $cover = $vehicle->coverPhoto;
                $coverUrl = $cover ? asset('storage/' . $cover->path) : asset('img/no-image.png');
            @endphp
            <a href="{{ route('vehicles.details', [
                'brand' => Str::slug($vehicle->brand ?? ''),
                'model' => Str::slug($vehicle->model ?? ''),
                'id'    => $vehicle->reference
            ]) }}" class="vl-card-link">
                <div class="vl-card">
                    <div class="vl-card-img-wrap">
                        <img src="{{ $coverUrl }}" loading="lazy"
                            alt="{{ $vehicle->brand }} {{ $vehicle->model }}"
                            style="object-position: {{ $vehicle->coverPhoto?->focal_x ?? 50 }}% {{ $vehicle->coverPhoto?->focal_y ?? 50 }}%">
                        @if($vehicle->status === 'reservado')
                        <span class="vl-badge-reservado">Reservado</span>
                        @elseif($vehicle->status === 'vendido')
                        <span class="vl-badge-vendido">Vendido</span>
                        @endif
                    </div>
                    <div class="vl-card-body">
                        <div class="vl-card-title">
                            <span class="vl-brand">{{ $vehicle->brand }}</span>
                            <span class="vl-model">{{ $vehicle->model }}
                                @if($vehicle->sub_model) {{ $vehicle->sub_model }} @endif
                            </span>
                        </div>
                        @if($vehicle->version)
                        <p class="vl-version">{{ $vehicle->version }}</p>
                        @endif
                        <div class="vl-specs">
                            @if($vehicle->year)
                            <span class="vl-spec">
                                <i class="bi bi-calendar3"></i> {{ $vehicle->year_label }}
                            </span>
                            @endif
                            @if($vehicle->fuel)
                            <span class="vl-spec">
                                <i class="bi bi-fuel-pump"></i> {{ $vehicle->fuel }}
                            </span>
                            @endif
                            @if($vehicle->kilometers)
                            <span class="vl-spec">
                                <i class="bi bi-speedometer2"></i> {{ number_format($vehicle->kilometers, 0, ',', ' ') }} km
                            </span>
                            @endif
                            @if($vehicle->power)
                            <span class="vl-spec">
                                <i class="bi bi-lightning-charge"></i> {{ $vehicle->power }} cv
                            </span>
                            @endif
                        </div>
                        <div class="vl-card-footer">
                            <span class="vl-cta">Ver detalhes <i class="bi bi-arrow-right"></i></span>
                            <div class="vl-card-actions">
                                @if($vehicle->status !== 'vendido')
                                <button type="button" class="vl-action-btn vl-action-btn--wa" title="Contactar via WhatsApp"
                                        data-name="{{ $vehicle->brand }} {{ $vehicle->model }}"
                                        data-ref="{{ $vehicle->reference }}"
                                        onclick="event.stopPropagation(); event.preventDefault(); vlWA(this.dataset.name, this.dataset.ref)">
                                    <i class="bi bi-whatsapp"></i>
                                </button>
                                @endif
                                <button type="button" class="vl-action-btn vl-action-btn--share" title="Partilhar"
                                        data-url="{{ route('vehicles.details', ['brand' => Str::slug($vehicle->brand ?? ''), 'model' => Str::slug($vehicle->model ?? ''), 'id' => $vehicle->reference]) }}"
                                        data-title="{{ $vehicle->brand }} {{ $vehicle->model }}"
                                        onclick="event.stopPropagation(); event.preventDefault(); vlShare(this.dataset.url, this.dataset.title)">
                                    <i class="bi bi-share"></i>
                                </button>
                                @if($vehicle->status === 'reservado')
                                <span class="vl-footer-badge vl-footer-badge--reservado">Reservado</span>
                                @elseif($vehicle->status === 'vendido')
                                <span class="vl-footer-badge vl-footer-badge--vendido">Vendido</span>
                                @elseif($vehicle->asking_price)
                                <span class="vl-price">{{ number_format(round($vehicle->asking_price), 0, ',', ' ') }} €</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach

            <div id="no-results" class="vl-empty d-none">
                <i class="bi bi-search fs-1 mb-3 d-block text-muted"></i>
                <p class="text-muted">Nenhuma viatura encontrada com os filtros selecionados.</p>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">Limpar filtros</button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Filter Sidebar ───────────────────────────────────── */
.vl-filter-sidebar {
    background: #fff;
    border-radius: 14px;
    border: 1px solid rgba(110,7,7,.1);
    overflow: hidden;
    box-shadow: 0 2px 16px rgba(0,0,0,.06);
}
.vl-filter-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    font-weight: 700;
    font-size: 1rem;
    color: var(--accent-color);
    border-bottom: 1px solid rgba(110,7,7,.1);
    background: #fdf7f7;
}
.vl-clear-btn {
    background: none;
    border: 1px solid var(--accent-color);
    color: var(--accent-color);
    border-radius: 20px;
    font-size: .78rem;
    padding: 3px 10px;
    cursor: pointer;
    transition: all .2s;
}
.vl-clear-btn:hover { background: var(--accent-color); color: #fff; }
.vl-filter-body { padding: 16px 20px; }
.vl-filter-group { margin-bottom: 14px; }
.vl-filter-label {
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #888;
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
}
.vl-select {
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    font-size: .9rem;
    transition: border-color .2s;
}
.vl-select:focus { border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(110,7,7,.1); }
.vl-select:disabled { opacity: .5; }
.vl-count-badge {
    margin: 0 20px 18px;
    background: rgba(110,7,7,.06);
    color: var(--accent-color);
    border-radius: 8px;
    padding: 8px 14px;
    font-size: .83rem;
    font-weight: 600;
    text-align: center;
}

/* ── Vehicle Card (horizontal list) ──────────────────── */
.vl-card-link { text-decoration: none; display: block; margin-bottom: 18px; }
.vl-card {
    display: flex;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    border: 1px solid rgba(110,7,7,.1);
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    transition: transform .25s, box-shadow .25s;
}
.vl-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(110,7,7,.15); }
.vl-card-img-wrap {
    position: relative;
    width: 300px;
    min-width: 300px;
    height: 210px;
    overflow: hidden;
    border-radius: 14px 0 0 14px;
}
.vl-card-img-wrap img {
    width: 100%; height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform .35s;
}
.vl-card:hover .vl-card-img-wrap img { transform: scale(1.04); }
.vl-badge-reservado {
    position: absolute; top: 10px; left: 10px;
    background: #f59e0b; color: #fff;
    font-size: .72rem; font-weight: 700;
    border-radius: 20px; padding: 3px 10px;
    text-transform: uppercase; letter-spacing: .05em;
}
.vl-badge-vendido {
    position: absolute; top: 10px; left: 10px;
    background: #dc2626; color: #fff;
    font-size: .72rem; font-weight: 700;
    border-radius: 20px; padding: 3px 10px;
    text-transform: uppercase; letter-spacing: .05em;
}
.vl-card-body {
    flex: 1;
    padding: 18px 22px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.vl-card-title { margin-bottom: 4px; }
.vl-brand {
    display: block;
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--accent-color);
    line-height: 1.1;
}
.vl-model {
    display: block;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--primary-color);
    line-height: 1.2;
}
.vl-version { font-size: .82rem; color: var(--accent-color); opacity: .75; margin: 2px 0 10px; }
.vl-specs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
.vl-spec {
    background: rgba(110,7,7,.06);
    border-radius: 20px;
    padding: 4px 12px;
    font-size: .83rem;
    color: var(--primary-color);
    display: flex; align-items: center; gap: 4px;
}
.vl-spec i { color: var(--accent-color); font-size: .85rem; }
.vl-footer-badge {
    font-size: .8rem;
    font-weight: 700;
    border-radius: 20px;
    padding: 5px 14px;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.vl-footer-badge--reservado { background: #fef3c7; color: #92400e; }
.vl-footer-badge--vendido   { background: #fee2e2; color: #991b1b; }
.vl-card-footer { display: flex; align-items: center; justify-content: space-between; }
.vl-price {
    font-size: 1.45rem;
    font-weight: 800;
    color: var(--accent-color);
}
.vl-price-contact {
    font-size: 1rem;
    font-weight: 600;
    color: #888;
    font-style: italic;
}
.vl-cta {
    background: var(--accent-color);
    color: #fff;
    border-radius: 22px;
    padding: 8px 18px;
    font-size: .83rem;
    font-weight: 600;
    transition: background .2s, transform .2s;
    white-space: nowrap;
}
.vl-card:hover .vl-cta { background: #990000; transform: translateX(2px); }
.vl-card-actions { display: flex; align-items: center; gap: 8px; }
.vl-action-btn {
    width: 36px; height: 36px;
    border-radius: 50%;
    border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    cursor: pointer;
    transition: all .2s;
    flex-shrink: 0;
    text-decoration: none;
}
.vl-action-btn--wa { background: #25d366; color: #fff; }
.vl-action-btn--wa:hover { background: #1ebe5d; transform: scale(1.1); color: #fff; }
.vl-action-btn--share { background: rgba(110,7,7,.1); color: var(--accent-color); }
.vl-action-btn--share:hover { background: var(--accent-color); color: #fff; transform: scale(1.1); }
.vl-toast {
    position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%);
    background: #222; color: #fff;
    padding: 10px 24px; border-radius: 24px;
    font-size: .88rem; font-weight: 600;
    z-index: 9999; pointer-events: none;
    animation: vlToastIn .2s ease;
}
@keyframes vlToastIn {
    from { opacity:0; transform: translateX(-50%) translateY(10px); }
    to   { opacity:1; transform: translateX(-50%) translateY(0); }
}

/* ── Empty State ──────────────────────────────────────── */
.vl-empty { text-align: center; padding: 60px 20px; }

/* ── Loading skeleton ─────────────────────────────────── */
.vl-skeleton {
    border-radius: 14px; overflow: hidden;
    background: #fff; border: 1px solid #eee;
    display: flex; margin-bottom: 18px; height: 210px;
}
.vl-skeleton-img { width: 300px; min-width: 300px; background: #f0f0f0; animation: vl-pulse 1.4s ease-in-out infinite; }
.vl-skeleton-body { flex: 1; padding: 20px; display: flex; flex-direction: column; gap: 10px; }
.vl-skeleton-line { height: 14px; border-radius: 6px; background: #f0f0f0; animation: vl-pulse 1.4s ease-in-out infinite; }
.vl-skeleton-line.wide { width: 60%; }
.vl-skeleton-line.narrow { width: 35%; }
@keyframes vl-pulse { 0%,100%{ opacity:1 } 50%{ opacity:.5 } }
</style>

<script>
window.vlWA = function(name, ref) {
    const msg = 'Olá, tenho interesse no ' + name + ' (ref: ' + ref + ')';
    window.open('https://wa.me/351928459346?text=' + encodeURIComponent(msg), '_blank');
};

window.vlShare = function(url, title) {
    const fullUrl = url.startsWith('http') ? url : (window.location.origin + url);
    if (navigator.share) {
        navigator.share({ title: title, url: fullUrl }).catch(function(){});
    } else {
        navigator.clipboard.writeText(fullUrl).then(function() {
            var t = document.createElement('div');
            t.className = 'vl-toast';
            t.textContent = 'Link copiado!';
            document.body.appendChild(t);
            setTimeout(function(){ t.remove(); }, 2500);
        });
    }
};

(function () {
    const brandSel = document.getElementById('brand');
    const modelSel = document.getElementById('model');
    const yearSel  = document.getElementById('year');
    const fuelSel  = document.getElementById('fuel');
    const container = document.getElementById('vehicles-container');
    const noResults = document.getElementById('no-results');
    const countEl   = document.getElementById('vehicles-count');
    const clearBtn  = document.getElementById('clear-filters-btn');

    function showSkeletons() {
        container.innerHTML = [1,2,3].map(() => `
            <div class="vl-skeleton">
                <div class="vl-skeleton-img"></div>
                <div class="vl-skeleton-body">
                    <div class="vl-skeleton-line wide"></div>
                    <div class="vl-skeleton-line narrow"></div>
                    <div class="vl-skeleton-line"></div>
                    <div class="vl-skeleton-line narrow"></div>
                </div>
            </div>`).join('');
        noResults.classList.add('d-none');
    }

    function toSlug(str) {
        if (!str) return '';
        return str.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
            .replace(/\s+/g,'-').replace(/[^a-z0-9-]/g,'')
            .replace(/--+/g,'-').replace(/^-+|-+$/g,'');
    }

    function fmt(n) {
        return n ? Number(n).toLocaleString('pt-PT', { maximumFractionDigits: 0 }) : null;
    }

    function renderVehicle(v) {
        const imgTag = v.cover_url
            ? `<img src="${v.cover_url}" loading="lazy" alt="${v.brand} ${v.model}" style="object-position:${v.cover_focal_x ?? 50}% ${v.cover_focal_y ?? 50}%">`
            : `<div style="width:100%;height:100%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;"><i class="bi bi-car-front text-muted fs-1"></i></div>`;

        const badge = v.status === 'reservado'
            ? `<span class="vl-badge-reservado">Reservado</span>`
            : v.status === 'vendido'
            ? `<span class="vl-badge-vendido">Vendido</span>` : '';

        const footerRight = v.status === 'reservado'
            ? `<span class="vl-footer-badge vl-footer-badge--reservado">Reservado</span>`
            : v.status === 'vendido'
            ? `<span class="vl-footer-badge vl-footer-badge--vendido">Vendido</span>`
            : v.asking_price
            ? `<span class="vl-price">${fmt(v.asking_price)} €</span>`
            : ``;

        const specs = [
            v.year    ? `<span class="vl-spec"><i class="bi bi-calendar3"></i> ${v.year}</span>` : '',
            v.fuel    ? `<span class="vl-spec"><i class="bi bi-fuel-pump"></i> ${v.fuel}</span>` : '',
            v.kilometers ? `<span class="vl-spec"><i class="bi bi-speedometer2"></i> ${fmt(v.kilometers)} km</span>` : '',
        ].filter(Boolean).join('');

        const subModel = v.sub_model ? ` ${v.sub_model}` : '';
        const version  = v.version   ? `<p class="vl-version">${v.version}</p>` : '';

        const waBtn = v.status !== 'vendido'
            ? `<button type="button" class="vl-action-btn vl-action-btn--wa" title="Contactar via WhatsApp"
                   onclick="event.stopPropagation(); event.preventDefault(); vlWA('${(v.brand + ' ' + v.model).replace(/'/g, "\\'")}',' ${String(v.reference).replace(/'/g, "\\'")}')"><i class="bi bi-whatsapp"></i></button>`
            : '';
        const shareBtn = `<button type="button" class="vl-action-btn vl-action-btn--share" title="Partilhar"
            onclick="event.stopPropagation(); event.preventDefault(); vlShare('${v.url.replace(/'/g, "\\'")}',' ${(v.brand + ' ' + v.model).replace(/'/g, "\\'")}')"><i class="bi bi-share"></i></button>`;

        return `
        <a href="${v.url}" class="vl-card-link">
            <div class="vl-card">
                <div class="vl-card-img-wrap">
                    ${imgTag}${badge}
                </div>
                <div class="vl-card-body">
                    <div>
                        <div class="vl-card-title">
                            <span class="vl-brand">${v.brand}</span>
                            <span class="vl-model">${v.model}${subModel}</span>
                        </div>
                        ${version}
                        <div class="vl-specs">${specs}</div>
                    </div>
                    <div class="vl-card-footer">
                        <span class="vl-cta">Ver detalhes <i class="bi bi-arrow-right"></i></span>
                        <div class="vl-card-actions">${waBtn}${shareBtn}${footerRight}</div>
                    </div>
                </div>
            </div>
        </a>`;
    }

    function updateClearBtn() {
        const active = brandSel.value || modelSel.value || yearSel.value || fuelSel.value;
        clearBtn.classList.toggle('d-none', !active);
    }

    async function updateModels() {
        modelSel.innerHTML = '<option value="">Todos os modelos</option>';
        modelSel.disabled = true;
        if (!brandSel.value) return;
        const res = await fetch(`/modelos-por-marca?brand=${encodeURIComponent(brandSel.value)}`);
        const models = await res.json();
        models.forEach(m => {
            const o = document.createElement('option'); o.value = m; o.text = m;
            modelSel.appendChild(o);
        });
        modelSel.disabled = false;
    }

    async function updateYears() {
        yearSel.innerHTML = '<option value="">Todos os anos</option>';
        yearSel.disabled = true;
        if (!brandSel.value) return;
        const url = `/anos-por-marca-modelo?brand=${encodeURIComponent(brandSel.value)}&model=${encodeURIComponent(modelSel.value)}`;
        const years = await (await fetch(url)).json();
        years.forEach(y => {
            const o = document.createElement('option'); o.value = y; o.text = y;
            yearSel.appendChild(o);
        });
        yearSel.disabled = false;
    }

    async function updateFuels() {
        const params = new URLSearchParams({ brand: brandSel.value, model: modelSel.value, year: yearSel.value });
        const fuels = await (await fetch(`/combustiveis-por-marca-modelo-ano?${params}`)).json();
        fuelSel.innerHTML = '<option value="">Todos</option>';
        fuels.forEach(f => {
            const o = document.createElement('option'); o.value = f; o.text = f;
            fuelSel.appendChild(o);
        });
    }

    async function updateVehicles() {
        showSkeletons();
        const params = new URLSearchParams({
            brand: brandSel.value,
            model: modelSel.value,
            year:  yearSel.value,
            fuel:  fuelSel.value,
        });
        const vehicles = await (await fetch(`/viaturas-filtradas?${params}`)).json();

        if (!vehicles.length) {
            container.innerHTML = '';
            noResults.classList.remove('d-none');
            countEl.textContent = '0';
            updateClearBtn();
            return;
        }
        noResults.classList.add('d-none');
        container.innerHTML = vehicles.map(renderVehicle).join('');
        countEl.textContent = vehicles.length;
        updateClearBtn();
    }

    window.clearFilters = function () {
        brandSel.value = '';
        modelSel.innerHTML = '<option value="">Todos os modelos</option>';
        modelSel.disabled = true;
        yearSel.innerHTML  = '<option value="">Todos os anos</option>';
        yearSel.disabled = true;
        fuelSel.innerHTML  = '<option value="">Todos</option>';
        updateClearBtn();
        updateVehicles();
    };

    brandSel.addEventListener('change', async () => { await updateModels(); await updateYears(); await updateFuels(); updateVehicles(); });
    modelSel.addEventListener('change', async () => { await updateYears(); await updateFuels(); updateVehicles(); });
    yearSel.addEventListener ('change', async () => { await updateFuels(); updateVehicles(); });
    fuelSel.addEventListener ('change', updateVehicles);
    clearBtn.addEventListener('click', clearFilters);

    updateVehicles();
})();
</script>
