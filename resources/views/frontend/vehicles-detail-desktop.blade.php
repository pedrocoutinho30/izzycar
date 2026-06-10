<section class="py-5 bg-light" style="padding-top: 6rem !important;" role="main" itemscope itemtype="https://schema.org/Product">
    <div class="container">
        {{-- Breadcrumbs para SEO --}}
        <nav aria-label="Breadcrumbs" class="mb-4">
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

        <div class="row g-5">
            <!-- Coluna com imagens -->
            <div class="col-lg-8">
                <div class="border-gallery">
                <!-- Swiper principal (imagem grande) -->
                <div class="swiper mySwiperMain">
                    <div class="swiper-wrapper">
                        @foreach ($vehicle->photos as $key => $photo)
                        <div class="swiper-slide">
                            <img src="{{ asset("storage/" . $photo->path) }}" loading="lazy"
                                class="img-fluid rounded main-gallery-img" data-index="{{ $key }}"
                                style="cursor: zoom-in; object-fit: cover; object-position: {{ $photo->focal_x ?? 50 }}% {{ $photo->focal_y ?? 50 }}%;"
                                alt="{{ $vehicle->brand }} {{ $vehicle->model }} {{ $key + 1 }}">
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>

                <!-- Swiper das miniaturas -->
                <div class="swiper mySwiperThumbs mt-1">
                    <div class="swiper-wrapper">
                        @foreach ($vehicle->photos as $key => $photo)
                        <div class="swiper-slide" style="width: auto; height: 100px; cursor: pointer;">
                            <img src="{{ asset("storage/" . $photo->path) }}" loading="lazy"
                                class="img-fluid rounded"
                                alt="{{ $vehicle->brand }} {{ $vehicle->model }} {{ $key + 1 }}">
                        </div>
                        @endforeach
                    </div>
                </div>
                </div>
            </div>

            <!-- Coluna com detalhes -->
            <div class="col-lg-4 ">
                <div class="col-12">
                    <div class="card custom-block-transparent news-listing shadow-sm h-100">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mt-4">
                                    <h1 class="text-accent" itemprop="name">{{ $vehicle->brand }}</h1>
                                    <h2 class="text-accent" itemprop="model"> {{ $vehicle->model }}@if($vehicle->version)<span itemprop="version"> {{ $vehicle->version }}</span>@endif</h2>
                                    <meta itemprop="brand" content="{{ $vehicle->brand }}" />
                                    <meta itemprop="sku" content="{{ $vehicle->reference }}" />
                                    @if($vehicle->status === 'reservado')
                                    <span class="badge rounded-pill fs-6 mt-1" style="background:#f59e0b;">Reservado</span>
                                    @elseif($vehicle->status === 'vendido')
                                    <span class="badge rounded-pill fs-6 mt-1" style="background:#dc2626;">Vendido</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row mt-4">
                                @if($vehicle->year)
                                <div class="col-md-12">
                                    <div class="d-flex align-items-start ">
                                        <span class="icon-colored pe-3">@include('components.icons.calendar')</span>
                                        <p class="mb-0 text-dark"><strong>Ano:</strong> <span itemprop="vehicleModelDate">{{ $vehicle->year }}</span></p>
                                    </div>
                                </div>
                                @endif
                                @if($vehicle->kilometers)
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center">
                                        <span class="icon-colored pe-3">@include('components.icons.road')</span>
                                        <p class="mb-0 text-dark"><strong>Quilometragem:</strong> <span itemprop="mileageFromOdometer">{{ $vehicle->kilometers }}</span> KM</p>
                                    </div>
                                </div>
                                @endif
                                @if($vehicle->fuel)
                                <div class="col-md-12">
                                    <div class="d-flex align-items-start">
                                        <span class="icon-colored pe-3">@include('components.icons.fuel')</span>
                                        <p class="mb-0 text-dark"><strong>Combustível:</strong> <span itemprop="fuelType">{{ $vehicle->fuel }}</span></p>
                                    </div>
                                </div>
                                @endif
                                @if($cilindrada)
                                <div class="col-md-12">
                                    <div class="d-flex align-items-start">
                                        <span class="icon-colored pe-3">@include('components.icons.motor')</span>
                                        <p class="mb-0 text-dark"><strong>Cilindrada:</strong> <span itemprop="vehicleEngine" itemscope itemtype="https://schema.org/EngineSpecification"><span itemprop="engineDisplacement">{{$cilindrada}}</span></span> CC</p>
                                    </div>
                                </div>
                                @endif
                                @if($potencia)
                                <div class="col-md-12">
                                    <div class="d-flex align-items-start">
                                        <span class="icon-colored pe-3">@include('components.icons.power')</span>
                                        <p class="mb-0 text-dark"><strong>Potência:</strong> <span itemprop="vehicleEngine" itemscope itemtype="https://schema.org/EngineSpecification"><span itemprop="enginePower">{{$potencia}}</span></span> CV</p>
                                    </div>
                                </div>
                                @endif
                                @if($caixa)
                                <div class="col-md-12">
                                    <div class="d-flex align-items-start">
                                        <span class="icon-colored pe-3">@include('components.icons.gearbox')</span>
                                        <p class="mb-0 text-dark"><strong>Transmissão:</strong> <span itemprop="vehicleTransmission">{{$caixa}}</span></p>
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if($vehicle->asking_price && !in_array($vehicle->status ?? '', ['reservado', 'vendido']))
                            <h3 class="d-flex align-items-end mt-4" style="color: var(--accent-color);" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                                <span itemprop="price">{{ number_format(round($vehicle->asking_price), 0, ',', ' ') }}</span>&nbsp;€
                                <meta itemprop="priceCurrency" content="EUR" />
                                <meta itemprop="availability" content="https://schema.org/InStock" />
                                <meta itemprop="url" content="{{ url()->current() }}" />
                            </h3>
                            @endif
                            <div class="d-flex gap-3 align-items-center mt-3">
                                <!-- Botão de partilha -->
                                <button type="button" class="btn btn-outline-form" data-bs-toggle="modal" data-bs-target="#shareModal">
                                    <i class="bi bi-share-fill text-dark"></i>
                                </button>
                                <a href="https://wa.me/351928459346?text=Olá, gostaria de saber mais informações sobre o veículo {{$vehicle->brand}} {{$vehicle->model}} {{$vehicle->version}} ({{ $vehicle->reference }})
                                Link: {{ route('vehicles.details',  ['brand' => Str::slug($vehicle->brand),
                                    'model' => Str::slug($vehicle->model),
                                    'id' => $vehicle->reference]) }}"
                                    target="_blank"
                                    class="btn btn-outline-form">
                                    <i class="bi bi-whatsapp text-dark"></i>
                                </a>
                                <button type="button" class="btn btn-outline-form text-dark" data-bs-toggle="modal" data-bs-target="#contactModal">
                                    Informações
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Equipamento -->
        <div class="row ml-1 mt-5 g-4">
            @foreach ($attributes as $group => $attrs)
            <div class="col-12">
                <div class="card news-listing shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="text-accent fw-semibold mb-4">{{ $group }}</h2>

                        <div class="row">
                            @foreach ($attrs as $attr => $value)
                            @if(!in_array($attr, ['Potência', 'Cilindrada', 'Transmissão']))
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-check-circle-fill me-2" style="color: var(--accent-color);"></i>
                                    <span class="text-dark">{{ $attr == $value ? $attr : $attr . ': ' . $value }}</span>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
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
    /* ── Breadcrumbs ─────────────────────────────────────────────────────── */
    .breadcrumb {
        background-color: transparent;
        padding: 0.75rem 0;
        margin-bottom: 0;
        border-radius: 0;
    }
    .breadcrumb-item {
        font-size: 0.9rem;
    }
    .breadcrumb-item a {
        color: var(--accent-color);
        text-decoration: none;
        font-weight: 500;
    }
    .breadcrumb-item a:hover {
        text-decoration: underline;
    }
    .breadcrumb-item.active {
        color: #6b7280;
    }

    /* ── Lightbox ────────────────────────────────────────────────────────── */
    #vl-lightbox {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0, 0, 0, .92);
        align-items: center;
        justify-content: center;
    }
    #vl-lightbox.active { display: flex; }

    #vl-lb-img-wrap {
        max-width: 90vw;
        max-height: 88vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #vl-lb-img {
        max-width: 90vw;
        max-height: 88vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 8px 40px rgba(0,0,0,.6);
        transition: opacity .2s ease;
        user-select: none;
        -webkit-user-drag: none;
    }
    #vl-lb-close {
        position: fixed;
        top: 18px;
        right: 22px;
        background: rgba(255,255,255,.12);
        border: none;
        color: #fff;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .2s;
        z-index: 10001;
    }
    #vl-lb-close:hover { background: rgba(255,255,255,.25); }

    #vl-lb-prev, #vl-lb-next {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255,255,255,.1);
        border: none;
        color: #fff;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .2s;
        z-index: 10001;
    }
    #vl-lb-prev { left: 18px; }
    #vl-lb-next { right: 18px; }
    #vl-lb-prev:hover, #vl-lb-next:hover { background: rgba(255,255,255,.25); }
    #vl-lb-prev.lb-hidden, #vl-lb-next.lb-hidden { opacity: .25; pointer-events: none; }

    #vl-lb-counter {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        color: rgba(255,255,255,.7);
        font-size: .85rem;
        letter-spacing: .5px;
        z-index: 10001;
    }

    .border-gallery {
        background: var(--white-color);
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: none;
    }

    .group-badge-stylish {
        position: absolute;
        background: var(--accent-color);
        color: white;
        font-weight: bold;
        padding: 14px 20px;
        font-size: 1rem;
        z-index: 10;

        /* Bordas personalizadas */
        border-top-right-radius: 12px;
        border-bottom-left-radius: 12px;

        /* “Bico” no canto superior esquerdo e inferior direito */
        /* clip-path: polygon(
        100% 0,
        100% 100%,
        100% 100%,
        0 100%
    ); */


    }

    .swiper {
        width: 100%;
        height: auto;
    }

    .swiper-slide img {
        width: 100%;
        border-radius: 12px;
    }

    /* Imagem principal */
    .mySwiperMain .swiper-slide img {
        width: 100%;
        max-height: 650px;
        object-fit: cover;
        border-radius: 12px;
        animation: imageGrowth 2.5s ease-in-out forwards;
    }

    @keyframes imageGrowth {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(2);
        }
        100% {
            transform: scale(1);
        }
    }

    /* Mobile gallery image animation */
    .mobile-gallery-img {
        animation: imageGrowth 2.5s ease-in-out forwards !important;
    }

    /* Miniaturas */
    .mySwiperThumbs .swiper-slide {
        width: auto !important;
        height: 100px;
        flex-shrink: 0;
    }

    .mySwiperThumbs .swiper-slide img {
        height: 100px;
        width: auto;
        max-width: 160px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid transparent;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .mySwiperThumbs .swiper-slide img:hover {
        border-color: rgba(110, 7, 7, 0.3);
    }

    .mySwiperThumbs .swiper-slide-thumb-active img {
        border-color: var(--accent-color);
        box-shadow: 0 4px 12px rgba(110, 7, 7, 0.3);
    }

    /* Modern Info Card */
    .custom-block-transparent {
        background: var(--white-color) !important;
        border-radius: 12px;
        border: 1px solid rgba(110, 7, 7, 0.1);
        transition: all 0.3s ease;
    }

    .custom-block-transparent:hover {
        box-shadow: 0 8px 30px rgba(110, 7, 7, 0.15) !important;
    }

    .custom-block-transparent h3 {
        font-size: 1.8rem;
        font-weight: 600;
    }

    .custom-block-transparent h5 {
        font-size: 1.2rem;
        font-weight: 400;
        opacity: 0.9;
    }

    .icon-colored {
        color: var(--accent-color);
        font-size: 1.2rem;
    }

    /* Buttons Modern */
    .btn-outline-form {
        background: var(--white-color);
        border: 2px solid var(--accent-color);
        color: var(--accent-color);
        border-radius: 50px;
        padding: 10px 20px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .btn-outline-form:hover {
        background: linear-gradient(135deg, #6e0707 0%, #990000 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(110, 7, 7, 0.3);
    }

    /* Equipment Card Modern */
    .news-listing {
        background: var(--white-color) !important;
        border-radius: 12px;
        border: 1px solid rgba(110, 7, 7, 0.1);
    }

    .news-listing h5 {
        color: var(--accent-color);
        font-weight: 600;
        padding-bottom: 10px;
        border-bottom: 2px solid rgba(110, 7, 7, 0.2);
    }

    .bi-check-circle-fill {
        color: var(--accent-color);
        font-size: 1.1rem;
    }

    /* Swiper Navigation */
    .swiper-button-next,
    .swiper-button-prev {
        background-color: rgba(110, 7, 7, 0.9);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .swiper-button-next:hover,
    .swiper-button-prev:hover {
        background-color: rgba(110, 7, 7, 1);
        transform: scale(1.1);
    }

    .swiper-button-next::after,
    .swiper-button-prev::after {
        font-size: 18px;
        color: white;
    }

    /* Mobile */
    @media (max-width: 768px) {
        .border-gallery {
            padding: 10px;
        }

        /* Reduz altura da imagem principal */
        .mySwiperMain .swiper-slide img {
            max-height: 300px;
        }

        /* Miniaturas com mais espaço para dedo */
        .mySwiperThumbs .swiper-slide {
            width: 60px !important;
        }

        /* Botões de navegação menores */
        .swiper-button-next,
        .swiper-button-prev {
            width: 35px;
            height: 35px;
        }

        .swiper-button-next::after,
        .swiper-button-prev::after {
            font-size: 16px;
        }
    }

    /* ── Related Section ─────────────────────────────────────────────── */
    .related-section {
        background: #f4f4f6;
        padding: 3rem 0 4rem;
    }
    .related-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 2rem;
    }
    .related-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111;
        margin-bottom: .2rem;
    }
    .related-subtitle {
        color: #6b7280;
        font-size: .9rem;
        margin: 0;
    }
    .related-link {
        font-size: .9rem;
        font-weight: 600;
        color: var(--accent-color);
        text-decoration: none;
        white-space: nowrap;
    }
    .related-link:hover { text-decoration: underline; }

    /* Swiper wrapper with room for nav arrows */
    .related-swiper-wrapper {
        position: relative;
        padding: 0 50px;
    }
    .mySwiperRelated {
        overflow: hidden;
    }
    .mySwiperRelated .swiper-slide {
        height: auto;
        display: flex;
    }

    /* Card */
    .related-card-link { display: flex; flex: 1; }
    .related-card {
        display: flex;
        flex-direction: column;
        width: 100%;
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,.06);
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        transition: transform .3s ease, box-shadow .3s ease;
    }
    .related-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 40px rgba(110,7,7,.14);
    }
    .related-card-img {
        position: relative;
        height: 195px;
        overflow: hidden;
        background: #e9e9e9;
        flex-shrink: 0;
    }
    .related-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .5s ease;
    }
    .related-card:hover .related-card-img img { transform: scale(1.06); }
    .related-img-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,.35) 0%, transparent 50%);
        pointer-events: none;
    }
    .related-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 20px;
    }

    /* Card body */
    .related-card-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 1.1rem 1.2rem 1rem;
    }
    .related-card-top { flex: 1; }
    .related-brand {
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
        color: var(--accent-color);
        margin-bottom: .15rem;
    }
    .related-model {
        font-size: 1rem;
        font-weight: 600;
        color: #111;
        margin-bottom: .8rem;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .related-card-specs {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem .9rem;
        margin-bottom: 1rem;
    }
    .related-spec {
        font-size: .8rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .related-spec i { color: #9ca3af; }

    /* Footer row */
    .related-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: .8rem;
        border-top: 1px solid #f0f0f0;
        margin-top: auto;
    }
    .related-price {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--accent-color);
    }
    .related-cta {
        font-size: .8rem;
        font-weight: 600;
        color: #9ca3af;
        transition: color .2s;
    }
    .related-card:hover .related-cta { color: var(--accent-color); }

    /* Nav arrows */
    .related-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        background: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: var(--accent-color);
        box-shadow: 0 2px 10px rgba(0,0,0,.12);
        cursor: pointer;
        z-index: 10;
        transition: background .2s, box-shadow .2s;
        user-select: none;
    }
    .related-nav:hover {
        background: var(--accent-color);
        color: #fff;
        box-shadow: 0 4px 16px rgba(110,7,7,.3);
    }
    .related-prev { left: 0; }
    .related-next { right: 0; }
    .related-nav.swiper-button-disabled { opacity: .35; pointer-events: none; }
</style>
@endpush

@push('scripts')
<script>
    const swiperThumbs = new Swiper(".mySwiperThumbs", {
        spaceBetween: 10,
        slidesPerView: 'auto',
        freeMode: true,
        watchSlidesProgress: true,
    });

    const swiperMain = new Swiper(".mySwiperMain", {
        spaceBetween: 10,
        navigation: {
            nextEl: '.mySwiperMain .swiper-button-next',
            prevEl: '.mySwiperMain .swiper-button-prev',
        },
        thumbs: {
            swiper: swiperThumbs,
        },
        breakpoints: {
            0: { // Mobile
                spaceBetween: 5,
            },
            768: { // Desktop
                spaceBetween: 10,
            }
        }
    });

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