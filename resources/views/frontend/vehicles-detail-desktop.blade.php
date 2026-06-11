<section class="vd-page" role="main" itemscope itemtype="https://schema.org/Product">
    <div class="container-xl">

        {{-- Breadcrumbs --}}
        <nav aria-label="Breadcrumbs" class="mb-3">
            <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
                <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a href="{{ route('home') }}" itemprop="item"><span itemprop="name">Início</span></a>
                    <meta itemprop="position" content="1" />
                </li>
                <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a href="{{ route('vehicles.list') }}" itemprop="item"><span itemprop="name">Viaturas</span></a>
                    <meta itemprop="position" content="2" />
                </li>
                <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <span itemprop="name">{{ $vehicle->brand }} {{ $vehicle->model }}{{ $vehicle->version ? ' ' . $vehicle->version : '' }}</span>
                    <meta itemprop="position" content="3" />
                </li>
            </ol>
        </nav>

        {{-- Header: marca/modelo esquerda | preço/estado direita --}}
        <div class="vd-header">
            <div class="vd-header-left">
                <p class="vd-brand" itemprop="name">{{ $vehicle->brand }}</p>
                <h1 class="vd-model">{{ $vehicle->model }}@if($vehicle->version)<span class="vd-version"> {{ $vehicle->version }}</span>@endif</h1>
                <meta itemprop="brand" content="{{ $vehicle->brand }}" />
                <meta itemprop="sku" content="{{ $vehicle->reference }}" />
            </div>
            <div class="vd-header-right">
                @if($vehicle->status === 'reservado')
                    <span class="vd-status-badge" style="background:#f59e0b;">Reservado</span>
                @elseif($vehicle->status === 'vendido')
                    <span class="vd-status-badge" style="background:#dc2626;">Vendido</span>
                @elseif($vehicle->asking_price)
                    <p class="vd-price-label">Preço</p>
                    <div class="vd-price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                        <span itemprop="price">{{ number_format(round($vehicle->asking_price), 0, ',', ' ') }}</span>&nbsp;€
                        <meta itemprop="priceCurrency" content="EUR" />
                        <meta itemprop="availability" content="https://schema.org/InStock" />
                        <meta itemprop="url" content="{{ url()->current() }}" />
                    </div>
                    @if($vehicle->purchase_type === 'Geral')
                    <span class="vd-iva-badge"><i class="bi bi-receipt"></i> IVA Dedutível</span>
                    @endif
                @endif
            </div>
        </div>

        {{-- Layout 3 colunas --}}
        <div class="vd-layout">

            {{-- Col 1: miniaturas verticais --}}
            <div class="vd-thumbs" id="vdThumbs">
                @foreach($vehicle->photos as $key => $photo)
                <div class="vd-thumb {{ $key === 0 ? 'active' : '' }}" data-index="{{ $key }}">
                    <img src="{{ asset('storage/' . $photo->path) }}"
                         loading="{{ $key < 5 ? 'eager' : 'lazy' }}"
                         alt="{{ $vehicle->brand }} {{ $vehicle->model }} {{ $key + 1 }}">
                </div>
                @endforeach
            </div>

            {{-- Col 2: imagem principal + specs --}}
            <div class="vd-main">
                <div class="swiper mySwiperMain">
                    <div class="swiper-wrapper">
                        @foreach($vehicle->photos as $key => $photo)
                        <div class="swiper-slide">
                            <img src="{{ asset('storage/' . $photo->path) }}"
                                 loading="{{ $key === 0 ? 'eager' : 'lazy' }}"
                                 class="vd-main-img main-gallery-img"
                                 data-index="{{ $key }}"
                                 style="cursor:zoom-in; object-position:{{ $photo->focal_x ?? 50 }}% {{ $photo->focal_y ?? 50 }}%;"
                                 alt="{{ $vehicle->brand }} {{ $vehicle->model }} {{ $key + 1 }}">
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>

                <div class="vd-specs-bar">
                    <div class="vd-sbi">
                        <i class="bi bi-calendar3"></i>
                        <span class="vd-sbi-label">Ano</span>
                        <span class="vd-sbi-value" itemprop="vehicleModelDate">{{ $vehicle->year ?? '—' }}</span>
                    </div>
                    <div class="vd-sbi">
                        <i class="bi bi-speedometer2"></i>
                        <span class="vd-sbi-label">Km</span>
                        <span class="vd-sbi-value">
                            @if($vehicle->kilometers)
                                <span itemprop="mileageFromOdometer">{{ number_format($vehicle->kilometers, 0, ',', '.') }}</span> km
                            @else —
                            @endif
                        </span>
                    </div>
                    <div class="vd-sbi">
                        <i class="bi bi-fuel-pump"></i>
                        <span class="vd-sbi-label">Combustível</span>
                        <span class="vd-sbi-value" itemprop="fuelType">{{ $vehicle->fuel ?? '—' }}</span>
                    </div>
                    <div class="vd-sbi">
                        <i class="bi bi-cpu-fill"></i>
                        <span class="vd-sbi-label">Cilindrada</span>
                        <span class="vd-sbi-value">
                            @if($cilindrada) {{ $cilindrada }} cc @else — @endif
                        </span>
                    </div>
                    <div class="vd-sbi">
                        <i class="bi bi-lightning-charge"></i>
                        <span class="vd-sbi-label">Potência</span>
                        <span class="vd-sbi-value">
                            @if($potencia) {{ $potencia }} cv @else — @endif
                        </span>
                    </div>
                    <div class="vd-sbi">
                        <i class="bi bi-gear-wide-connected"></i>
                        <span class="vd-sbi-label">Transmissão</span>
                        <span class="vd-sbi-value">{{ $caixa ?: '—' }}</span>
                    </div>
                    @if($vehicle->fuel === 'Elétrico' || str_contains($vehicle->fuel ?? '', 'Híbrido'))
                    <div class="vd-sbi vd-sbi-electric">
                        <i class="bi bi-battery-half"></i>
                        <span class="vd-sbi-label">Autonomia</span>
                        <span class="vd-sbi-value">
                            @if($autonomia) {{ $autonomia }} km @else — @endif
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Col 3: painel lateral com scroll --}}
            <div class="vd-sidebar">

                {{-- Botões sempre visíveis --}}
                <div class="vd-actions">
                    <button class="vd-btn-primary w-100" data-bs-toggle="modal" data-bs-target="#contactModal">
                        <i class="bi bi-envelope-fill"></i> Pedir Informações
                    </button>
                    <div class="d-flex gap-2 mt-2">
                        <a href="https://wa.me/351928459346?text=Olá, gostaria de saber mais informações sobre o veículo {{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->version }} ({{ $vehicle->reference }}) Link: {{ route('vehicles.details', ['brand' => Str::slug($vehicle->brand), 'model' => Str::slug($vehicle->model), 'id' => $vehicle->reference]) }}"
                           target="_blank" class="vd-btn-secondary flex-grow-1">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </a>
                        <button class="vd-btn-icon" data-bs-toggle="modal" data-bs-target="#shareModal" title="Partilhar">
                            <i class="bi bi-share-fill"></i>
                        </button>
                    </div>
                </div>

                {{-- Cards de equipamento/atributos (scrolláveis) --}}
                <div class="vd-sidebar-scroll">
                    @foreach($attributes as $group => $attrs)
                    @php $visibleAttrs = collect($attrs)->reject(fn($v,$k) => in_array($k, ['Potência','Cilindrada','Transmissão']))->all(); @endphp
                    @if(count($visibleAttrs))
                    <div class="vd-attr-card">
                        <h6 class="vd-attr-title">{{ $group }}</h6>
                        <ul class="vd-attr-list">
                            @foreach($visibleAttrs as $attr => $value)
                            <li class="vd-attr-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>{{ $attr == $value ? $attr : $attr . ': ' . $value }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @endforeach
                </div>{{-- /vd-sidebar-scroll --}}

            </div>{{-- /vd-sidebar --}}

        </div>{{-- /vd-layout --}}

    </div>{{-- /container --}}
</section>

{{-- Lightbox --}}
@php
    $lightboxPhotos = $vehicle->photos->map(fn($p) => asset('storage/' . $p->path))->values()->toArray();
 $related = $last_vehicles->where('id', '!=', $vehicle->id)->take(3)->values(); 
 @endphp
@if($related->count())
<section class="related-section">
    <div class="container">
        <div class="related-header">
            <div>
                <h4 class="related-title">Outras Viaturas</h4>
                <p class="related-subtitle">Poderá também gostar</p>
            </div>
            <a href="{{ route('vehicles.list') }}" class="related-link">Ver todas <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="related-swiper-wrapper">
            <div class="swiper mySwiperRelated">
                <div class="swiper-wrapper">
                    @foreach($related as $rv)
                    <div class="swiper-slide">
                        <a href="{{ route('vehicles.details', ['brand' => Str::slug($rv->brand), 'model' => Str::slug($rv->model), 'id' => $rv->reference]) }}" class="text-decoration-none related-card-link">
                            <div class="related-card">
                                <div class="related-card-img">
                                    <img src="{{ optional($rv->photos->first())->path ? asset('storage/' . $rv->photos->first()->path) : asset('img/no-image.png') }}" alt="{{ $rv->brand }} {{ $rv->model }}" loading="lazy">
                                    <div class="related-img-overlay"></div>
                                    @if($rv->status === 'reservado')
                                    <span class="related-badge" style="background:#f59e0b;">Reservado</span>
                                    @elseif($rv->status === 'vendido')
                                    <span class="related-badge" style="background:#6b7280;">Vendido</span>
                                    @else
                                    <span class="related-badge" style="background: var(--accent-color);">Em stock</span>
                                    @endif
                                </div>
                                <div class="related-card-body">
                                    <div class="related-card-top">
                                        <p class="related-brand">{{ $rv->brand }}</p>
                                        <h6 class="related-model">{{ $rv->model }} {{ $rv->version }}</h6>
                                    </div>
                                    <div class="related-card-specs">
                                        @if($rv->year)
                                        <span class="related-spec"><i class="bi bi-calendar3"></i> {{ $rv->year }}</span>
                                        @endif
                                        @if($rv->kilometers)
                                        <span class="related-spec"><i class="bi bi-speedometer"></i> {{ number_format($rv->kilometers, 0, ',', '.') }} km</span>
                                        @endif
                                        @if($rv->fuel)
                                        <span class="related-spec"><i class="bi bi-fuel-pump"></i> {{ ucfirst($rv->fuel) }}</span>
                                        @endif
                                    </div>
                                    <div class="related-card-footer">
                                        @if($rv->status === 'em_stock')
                                        <span class="related-price">{{ number_format(round($rv->asking_price ?? 0), 0, ',', ' ') }} €</span>
                                        @elseif($rv->status === 'reservado')
                                        <span class="related-price" style="color:#f59e0b;">Reservado</span>
                                        @else
                                        <span class="related-price" style="color:#6b7280;">Vendido</span>
                                        @endif
                                        <span class="related-cta">Ver viatura <i class="bi bi-arrow-right"></i></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="related-nav related-prev"><i class="bi bi-chevron-left"></i></div>
            <div class="related-nav related-next"><i class="bi bi-chevron-right"></i></div>
        </div>
    </div>
</section>
@endif

@push('styles')
<style>
    /* ── Page ───────────────────────────────────────────────────────────── */
    .vd-page { padding: 5.5rem 0 3rem; background: #f4f4f6; }

    /* ── Header ─────────────────────────────────────────────────────────── */
    .vd-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
    .vd-header-left { flex: 1; min-width: 0; }
    .vd-brand { font-size: 1.05rem; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; color: var(--accent-color); margin-bottom: .3rem; display: flex; align-items: center; gap: .5rem; }
    .vd-brand::before { content: ''; display: inline-block; width: 18px; height: 3px; background: var(--accent-color); border-radius: 2px; flex-shrink: 0; }
    .vd-model { font-size: 1.7rem; font-weight: 700; color: #111; margin: 0; line-height: 1.2; }
    .vd-version { color: #6b7280; font-weight: 500; }
    .vd-header-right { text-align: right; flex-shrink: 0; }
    .vd-price-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin-bottom: .1rem; }
    .vd-price { font-size: 2rem; font-weight: 800; color: var(--accent-color); line-height: 1; }
    .vd-status-badge { display: inline-block; color: #fff; font-size: .9rem; font-weight: 700; padding: .45rem 1.1rem; border-radius: 20px; }
    .vd-iva-badge { display: inline-flex; align-items: center; gap: .25rem; font-size: .7rem; font-weight: 700; color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: .28rem .55rem; margin-top: .4rem; white-space: nowrap; }

    /* ── 3-col layout ───────────────────────────────────────────────────── */
    .vd-layout { display: grid; grid-template-columns: 88px 1fr 290px; gap: 1rem; align-items: start; }

    /* ── Thumbnail strip ────────────────────────────────────────────────── */
    .vd-thumbs { display: flex; flex-direction: column; gap: .4rem; max-height: 520px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(110,7,7,.25) transparent; padding-right: 2px; }
    .vd-thumb { width: 80px; height: 60px; border-radius: 8px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: border-color .2s, box-shadow .2s; flex-shrink: 0; }
    .vd-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .vd-thumb.active { border-color: var(--accent-color); box-shadow: 0 0 0 1px var(--accent-color); }
    .vd-thumb:hover:not(.active) { border-color: rgba(110,7,7,.35); }

    /* ── Main swiper ────────────────────────────────────────────────────── */
    .vd-main { min-width: 0; }
    .mySwiperMain { border-radius: 14px; overflow: hidden; background: #e9e9e9; }
    .vd-main-img { width: 100%; height: 460px; object-fit: cover; display: block; }

    /* Swiper nav buttons */
    .mySwiperMain .swiper-button-next,
    .mySwiperMain .swiper-button-prev {
        background: rgba(110,7,7,.85);
        width: 38px; height: 38px;
        border-radius: 50%;
        transition: background .2s;
    }
    .mySwiperMain .swiper-button-next:hover,
    .mySwiperMain .swiper-button-prev:hover { background: rgba(110,7,7,1); }
    .mySwiperMain .swiper-button-next::after,
    .mySwiperMain .swiper-button-prev::after { font-size: 15px; color: #fff; }

    /* ── Specs bar (single card, all items in one row) ───────────────────── */
    .vd-specs-bar {
        display: flex; align-items: stretch;
        background: #fff; border: 1.5px solid #e9ecef; border-radius: 12px;
        margin-top: .75rem; overflow: hidden;
        animation: vdSpecUp .4s ease both;
    }
    .vd-sbi {
        flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .18rem; padding: .6rem .4rem; text-align: center;
        border-right: 1px solid #f0f0f0; min-width: 0;
    }
    .vd-sbi:last-child { border-right: none; }
    .vd-sbi > i { font-size: .8rem; color: var(--accent-color); }
    .vd-sbi-label { font-size: .58rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; white-space: nowrap; }
    .vd-sbi-value { font-size: .82rem; font-weight: 700; color: #111; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
    .vd-sbi-electric > i { color: #16a34a; }
    .vd-sbi-electric .vd-sbi-value { color: #15803d; }
    .vd-sbi-electric .vd-sbi-label { color: #4ade80; }
    @keyframes vdSpecUp { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }

    /* ── Sidebar ────────────────────────────────────────────────────────── */
    .vd-sidebar { position: sticky; top: 6rem; max-height: calc(100vh - 7rem); display: flex; flex-direction: column; overflow: hidden; }
    .vd-sidebar-scroll { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(110,7,7,.2) transparent; display: flex; flex-direction: column; gap: .75rem; padding-right: 2px; padding-top: .1rem; }

    /* ── Action buttons ─────────────────────────────────────────────────── */
    .vd-actions { flex-shrink: 0; padding-bottom: .85rem; margin-bottom: .75rem; border-bottom: 1.5px solid #e9ecef; }
    .vd-btn-primary { display: flex; align-items: center; justify-content: center; gap: .4rem; background: var(--accent-color); color: #fff; border: none; border-radius: 10px; padding: .7rem 1rem; font-size: .9rem; font-weight: 600; cursor: pointer; transition: background .2s, transform .2s; text-decoration: none; }
    .vd-btn-primary:hover { background: #8b0000; transform: translateY(-1px); color: #fff; }
    .vd-btn-secondary { display: flex; align-items: center; justify-content: center; gap: .4rem; background: #25d366; color: #fff; border: none; border-radius: 10px; padding: .6rem .9rem; font-size: .88rem; font-weight: 600; cursor: pointer; transition: background .2s; text-decoration: none; }
    .vd-btn-secondary:hover { background: #1da951; color: #fff; }
    .vd-btn-icon { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: #f3f4f6; border: 1.5px solid #e5e7eb; border-radius: 10px; color: #374151; font-size: .9rem; cursor: pointer; transition: background .2s; flex-shrink: 0; }
    .vd-btn-icon:hover { background: #e9ecef; }

    /* ── Attribute cards ────────────────────────────────────────────────── */
    .vd-attr-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: .85rem 1rem; }
    .vd-attr-title { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--accent-color); margin-bottom: .55rem; padding-bottom: .35rem; border-bottom: 1.5px solid rgba(110,7,7,.1); }
    .vd-attr-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: .28rem; }
    .vd-attr-item { display: flex; align-items: flex-start; gap: .4rem; font-size: .82rem; color: #374151; line-height: 1.4; }
    .vd-attr-item i { color: var(--accent-color); font-size: .72rem; flex-shrink: 0; margin-top: .2rem; }

    /* ── Breadcrumbs ────────────────────────────────────────────────────── */
    .breadcrumb { background: transparent; padding: .75rem 0; margin-bottom: 0; }
    .breadcrumb-item { font-size: .9rem; }
    .breadcrumb-item a { color: var(--accent-color); text-decoration: none; font-weight: 500; }
    .breadcrumb-item a:hover { text-decoration: underline; }
    .breadcrumb-item.active { color: #6b7280; }

    /* ── Lightbox ───────────────────────────────────────────────────────── */
    #vl-lightbox { display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,.92); align-items: center; justify-content: center; }
    #vl-lightbox.active { display: flex; }
    #vl-lb-img-wrap { max-width: 90vw; max-height: 88vh; display: flex; align-items: center; justify-content: center; }
    #vl-lb-img { max-width: 90vw; max-height: 88vh; object-fit: contain; border-radius: 8px; box-shadow: 0 8px 40px rgba(0,0,0,.6); transition: opacity .2s; user-select: none; -webkit-user-drag: none; }
    #vl-lb-close { position: fixed; top: 18px; right: 22px; background: rgba(255,255,255,.12); border: none; color: #fff; width: 44px; height: 44px; border-radius: 50%; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background .2s; z-index: 10001; }
    #vl-lb-close:hover { background: rgba(255,255,255,.25); }
    #vl-lb-prev, #vl-lb-next { position: fixed; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,.1); border: none; color: #fff; width: 50px; height: 50px; border-radius: 50%; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background .2s; z-index: 10001; }
    #vl-lb-prev { left: 18px; } #vl-lb-next { right: 18px; }
    #vl-lb-prev:hover, #vl-lb-next:hover { background: rgba(255,255,255,.25); }
    #vl-lb-prev.lb-hidden, #vl-lb-next.lb-hidden { opacity: .25; pointer-events: none; }
    #vl-lb-counter { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); color: rgba(255,255,255,.7); font-size: .85rem; letter-spacing: .5px; z-index: 10001; }

    /* ── Related Section ────────────────────────────────────────────────── */
    .related-section { background: #f4f4f6; padding: 3rem 0 4rem; }
    .related-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 2rem; }
    .related-title { font-size: 1.5rem; font-weight: 700; color: #111; margin-bottom: .2rem; }
    .related-subtitle { color: #6b7280; font-size: .9rem; margin: 0; }
    .related-link { font-size: .9rem; font-weight: 600; color: var(--accent-color); text-decoration: none; white-space: nowrap; }
    .related-link:hover { text-decoration: underline; }
    .related-swiper-wrapper { position: relative; padding: 0 50px; }
    .mySwiperRelated { overflow: hidden; }
    .mySwiperRelated .swiper-slide { height: auto; display: flex; }
    .related-card-link { display: flex; flex: 1; }
    .related-card { display: flex; flex-direction: column; width: 100%; background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid rgba(0,0,0,.06); box-shadow: 0 2px 12px rgba(0,0,0,.06); transition: transform .3s, box-shadow .3s; }
    .related-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(110,7,7,.14); }
    .related-card-img { position: relative; height: 195px; overflow: hidden; background: #e9e9e9; flex-shrink: 0; }
    .related-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s; }
    .related-card:hover .related-card-img img { transform: scale(1.06); }
    .related-img-overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,.35) 0%, transparent 50%); pointer-events: none; }
    .related-badge { position: absolute; top: 12px; left: 12px; color: #fff; font-size: .72rem; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; padding: 4px 12px; border-radius: 20px; }
    .related-card-body { display: flex; flex-direction: column; flex: 1; padding: 1.1rem 1.2rem 1rem; }
    .related-card-top { flex: 1; }
    .related-brand { font-size: .78rem; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: var(--accent-color); margin-bottom: .15rem; }
    .related-model { font-size: 1rem; font-weight: 600; color: #111; margin-bottom: .8rem; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .related-card-specs { display: flex; flex-wrap: wrap; gap: .4rem .9rem; margin-bottom: 1rem; }
    .related-spec { font-size: .8rem; color: #6b7280; display: flex; align-items: center; gap: 4px; }
    .related-spec i { color: #9ca3af; }
    .related-card-footer { display: flex; align-items: center; justify-content: space-between; padding-top: .8rem; border-top: 1px solid #f0f0f0; margin-top: auto; }
    .related-price { font-size: 1.15rem; font-weight: 700; color: var(--accent-color); }
    .related-cta { font-size: .8rem; font-weight: 600; color: #9ca3af; transition: color .2s; }
    .related-card:hover .related-cta { color: var(--accent-color); }
    .related-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: var(--accent-color); box-shadow: 0 2px 10px rgba(0,0,0,.12); cursor: pointer; z-index: 10; transition: background .2s, box-shadow .2s; user-select: none; }
    .related-nav:hover { background: var(--accent-color); color: #fff; box-shadow: 0 4px 16px rgba(110,7,7,.3); }
    .related-prev { left: 0; } .related-next { right: 0; }
    .related-nav.swiper-button-disabled { opacity: .35; pointer-events: none; }
</style>
@endpush

@push('scripts')
<script>
    const swiperMain = new Swiper(".mySwiperMain", {
        spaceBetween: 10,
        navigation: {
            nextEl: '.mySwiperMain .swiper-button-next',
            prevEl: '.mySwiperMain .swiper-button-prev',
        },
    });

    /* ── Custom vertical thumb sync ─────────────────────────────────────── */
    (function () {
        var thumbs = document.querySelectorAll('.vd-thumb');

        function syncThumbs(index) {
            thumbs.forEach(function (th, i) { th.classList.toggle('active', i === index); });
            var active = document.querySelector('.vd-thumb.active');
            if (active) active.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }

        thumbs.forEach(function (th) {
            th.addEventListener('click', function () {
                swiperMain.slideTo(parseInt(this.dataset.index));
            });
        });

        swiperMain.on('slideChange', function () { syncThumbs(swiperMain.realIndex); });
    })();

    /* ── Redirecionar scroll da página para a sidebar ───────────────────── */
    (function () {
        var sidebarScroll = document.querySelector('.vd-sidebar-scroll');
        var layout        = document.querySelector('.vd-layout');
        if (!sidebarScroll || !layout) return;

        document.addEventListener('wheel', function (e) {
            var rect = layout.getBoundingClientRect();
            if (rect.bottom < 0 || rect.top > window.innerHeight) return;

            var delta = e.deltaY;
            var atBot = sidebarScroll.scrollTop + sidebarScroll.clientHeight >= sidebarScroll.scrollHeight - 2;
            var atTop = sidebarScroll.scrollTop <= 0;

            if (delta > 0 && !atBot) {
                e.preventDefault();
                sidebarScroll.scrollTop += delta;
            } else if (delta < 0 && !atTop) {
                e.preventDefault();
                sidebarScroll.scrollTop += delta;
            }
        }, { passive: false });
    })();

    new Swiper(".mySwiperRelated", {
        spaceBetween: 24,
        slidesPerView: 1,
        speed: 500,
        grabCursor: true,
        navigation: {
            nextEl: '.related-next',
            prevEl: '.related-prev',
        },
        breakpoints: {
            576: { slidesPerView: 2, spaceBetween: 20 },
            992: { slidesPerView: 3, spaceBetween: 24 },
        }
    });

    /* ── Lightbox ─────────────────────────────────────────────────────────── */
    (function () {
        const photos  = @json($lightboxPhotos);
        const lb      = document.getElementById('vl-lightbox');
        const lbImg   = document.getElementById('vl-lb-img');
        const lbClose = document.getElementById('vl-lb-close');
        const lbPrev  = document.getElementById('vl-lb-prev');
        const lbNext  = document.getElementById('vl-lb-next');
        const lbCount = document.getElementById('vl-lb-counter');
        let current = 0;

        function show(index) {
            current = index;
            lbImg.style.opacity = '0';
            lbImg.src = photos[current];
            lbImg.onload = () => { lbImg.style.opacity = '1'; };
            lbCount.textContent = (current + 1) + ' / ' + photos.length;
            lbPrev.classList.toggle('lb-hidden', current === 0);
            lbNext.classList.toggle('lb-hidden', current === photos.length - 1);
        }

        function open(index) {
            show(index);
            lb.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        window.vlLightboxOpen = open;

        function close() {
            lb.classList.remove('active');
            document.body.style.overflow = '';
        }

        /* Click on main gallery image */
        document.querySelectorAll('.main-gallery-img').forEach(img => {
            img.addEventListener('click', () => open(parseInt(img.dataset.index)));
        });

        /* Open lightbox when clicking swiper navigation buttons */
        const swiperNextBtn = document.querySelector('.mySwiperMain .swiper-button-next');
        const swiperPrevBtn = document.querySelector('.mySwiperMain .swiper-button-prev');
        if (swiperNextBtn) {
            swiperNextBtn.addEventListener('click', () => open(swiperMain.realIndex));
        }
        if (swiperPrevBtn) {
            swiperPrevBtn.addEventListener('click', () => open(swiperMain.realIndex));
        }

        lbClose.addEventListener('click', close);
        lbPrev.addEventListener('click', () => { if (current > 0) show(current - 1); });
        lbNext.addEventListener('click', () => { if (current < photos.length - 1) show(current + 1); });

        /* Click outside image */
        lb.addEventListener('click', e => { if (e.target === lb) close(); });

        /* Keyboard */
        document.addEventListener('keydown', e => {
            if (!lb.classList.contains('active')) return;
            if (e.key === 'Escape')      close();
            if (e.key === 'ArrowLeft'  && current > 0)                show(current - 1);
            if (e.key === 'ArrowRight' && current < photos.length - 1) show(current + 1);
        });

        /* Touch swipe */
        let touchX = null;
        lb.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
        lb.addEventListener('touchend',   e => {
            if (touchX === null) return;
            const diff = touchX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) diff > 0
                ? (current < photos.length - 1 && show(current + 1))
                : (current > 0 && show(current - 1));
            touchX = null;
        });
    })();
</script>
@endpush