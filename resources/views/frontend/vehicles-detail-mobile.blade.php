<section class="py-4 bg-light mt-4" role="main" itemscope itemtype="https://schema.org/Product">
    <div class="container">

        {{-- Breadcrumbs para SEO Mobile --}}
        <nav aria-label="Breadcrumbs" class="mb-3">
            <ol class="breadcrumb breadcrumb-mobile" itemscope itemtype="https://schema.org/BreadcrumbList">
                <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <span itemprop="name">Início</span>
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

        <!-- Galeria Mobile -->
        <div class="mb-3 border-gallery">
            <!-- Swiper principal -->
            <div class="swiper mySwiperMainMobile">
                <div class="swiper-wrapper">
                    @foreach ($vehicle->photos as $key => $photo)
                    <div class="swiper-slide">
                        <img src="{{ asset('storage/' . $photo->path) }}" loading="lazy"
                            class="img-fluid  d-block w-100  object-cover rounded mobile-gallery-img" data-index="{{ $key }}" style="height: 200px; width: 100%; object-fit: cover; object-position: {{ $photo->focal_x ?? 50 }}% {{ $photo->focal_y ?? 50 }}%"
                            alt="{{ $vehicle->brand }} {{ $vehicle->model }} {{ $key + 1 }}">
                    </div>
                    @endforeach
                </div>
                <!-- Navegação Mobile (única) -->
                <div class="swiper-button-next swiper-button-next-mobile"></div>
                <div class="swiper-button-prev swiper-button-prev-mobile"></div>
            </div>

            <!-- Swiper miniaturas -->
            <div class="swiper mySwiperThumbsMobile">
                <div class="swiper-wrapper">
                    @foreach ($vehicle->photos as $key => $photo)
                    <div class="swiper-slide" style="width: 60px;">
                        <img src="{{ asset('storage/' . $photo->path) }}" loading="lazy"
                            class="img-fluid rounded"
                            alt="{{ $vehicle->brand }} {{ $vehicle->model }} {{ $key + 1 }}">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Detalhes do veículo -->
        <div class="card vd-mobile-card mb-3">
            <div class="card-body">

                <div class="vd-header mb-3">
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
                        @endif
                    </div>
                </div>

                <div class="vd-specs-grid mb-3">
                    @if($vehicle->year)
                    <div class="vd-spec-card" style="--si:0">
                        <div class="vd-spec-head">
                            <div class="vd-spec-icon"><i class="bi bi-calendar3"></i></div>
                            <span class="vd-spec-label">Ano</span>
                        </div>
                        <span class="vd-spec-value" itemprop="vehicleModelDate">{{ $vehicle->year }}</span>
                    </div>
                    @endif
                    @if($vehicle->kilometers)
                    <div class="vd-spec-card" style="--si:1">
                        <div class="vd-spec-head">
                            <div class="vd-spec-icon"><i class="bi bi-speedometer2"></i></div>
                            <span class="vd-spec-label">Km</span>
                        </div>
                        <span class="vd-spec-value">
                            <span itemprop="mileageFromOdometer">{{ number_format($vehicle->kilometers, 0, ',', '.') }}</span>
                            <small class="vd-spec-unit">km</small>
                        </span>
                    </div>
                    @endif
                    @if($vehicle->fuel)
                    <div class="vd-spec-card" style="--si:2">
                        <div class="vd-spec-head">
                            <div class="vd-spec-icon"><i class="bi bi-fuel-pump"></i></div>
                            <span class="vd-spec-label">Combustível</span>
                        </div>
                        <span class="vd-spec-value" itemprop="fuelType">{{ $vehicle->fuel }}</span>
                    </div>
                    @endif
                    @if($cilindrada)
                    <div class="vd-spec-card" style="--si:3">
                        <div class="vd-spec-head">
                            <div class="vd-spec-icon"><i class="bi bi-cpu-fill"></i></div>
                            <span class="vd-spec-label">Cilindrada</span>
                        </div>
                        <span class="vd-spec-value" itemprop="vehicleEngine" itemscope itemtype="https://schema.org/EngineSpecification">
                            <span itemprop="engineDisplacement">{{ $cilindrada }}</span>
                            <small class="vd-spec-unit">cc</small>
                        </span>
                    </div>
                    @endif
                    @if($potencia)
                    <div class="vd-spec-card" style="--si:4">
                        <div class="vd-spec-head">
                            <div class="vd-spec-icon"><i class="bi bi-lightning-charge"></i></div>
                            <span class="vd-spec-label">Potência</span>
                        </div>
                        <span class="vd-spec-value" itemprop="vehicleEngine" itemscope itemtype="https://schema.org/EngineSpecification">
                            <span itemprop="enginePower">{{ $potencia }}</span>
                            <small class="vd-spec-unit">cv</small>
                        </span>
                    </div>
                    @endif
                    @if($caixa)
                    <div class="vd-spec-card" style="--si:5">
                        <div class="vd-spec-head">
                            <div class="vd-spec-icon"><i class="bi bi-gear-wide-connected"></i></div>
                            <span class="vd-spec-label">Transmissão</span>
                        </div>
                        <span class="vd-spec-value" itemprop="vehicleTransmission">{{ $caixa }}</span>
                    </div>
                    @endif
                    @if($autonomia && $vehicle->fuel === 'Elétrico' || str_contains($vehicle->fuel ?? '', 'Híbrido'))
                    <div class="vd-spec-card vd-spec-electric" style="--si:6">
                        <div class="vd-spec-head">
                            <div class="vd-spec-icon"><i class="bi bi-battery-half"></i></div>
                            <span class="vd-spec-label">Autonomia</span>
                        </div>
                        <span class="vd-spec-value">
                            {{ $autonomia }}<small class="vd-spec-unit"> km</small>
                        </span>
                    </div>
                    @endif
                </div>

                <div class="vd-actions mt-3">
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
            </div>
        </div>





        <!-- Equipamento / Atributos -->
        @foreach($attributes as $group => $attrs)
        @php $visibleAttrs = collect($attrs)->reject(fn($v,$k) => in_array($k, ['Potência','Cilindrada','Transmissão']))->all(); @endphp
        @if(count($visibleAttrs))
        <div class="vd-attr-card mb-3">
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

        {{-- Veículos recentes --}}
        {{-- <div class="row g-3">
            @include('frontend.partials.vehicles-home', ['vehicles' => $last_vehicles])
        </div> --}}
    </div>
</section>


<style>
    /* ── Mobile card wrapper ────────────────────────────────────────────── */
    .vd-mobile-card { background: #fff; border-radius: 14px; border: 1px solid #e9ecef; box-shadow: 0 2px 12px rgba(0,0,0,.06); }

    /* ── Header ─────────────────────────────────────────────────────────── */
    .vd-header { display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; }
    .vd-header-left { flex: 1; min-width: 0; }
    .vd-brand { font-size: .95rem; font-weight: 900; letter-spacing: 1.2px; text-transform: uppercase; color: var(--accent-color); margin-bottom: .2rem; display: flex; align-items: center; gap: .45rem; }
    .vd-brand::before { content: ''; display: inline-block; width: 14px; height: 3px; background: var(--accent-color); border-radius: 2px; flex-shrink: 0; }
    .vd-model { font-size: 1.3rem; font-weight: 700; color: #111; margin: 0; line-height: 1.2; }
    .vd-version { color: #6b7280; font-weight: 500; }
    .vd-header-right { text-align: right; flex-shrink: 0; }
    .vd-price-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin-bottom: .05rem; }
    .vd-price { font-size: 1.5rem; font-weight: 800; color: var(--accent-color); line-height: 1; }
    .vd-status-badge { display: inline-block; color: #fff; font-size: .82rem; font-weight: 700; padding: .35rem .9rem; border-radius: 20px; }

    /* ── Action buttons ─────────────────────────────────────────────────── */
    .vd-actions { flex-shrink: 0; }
    .vd-btn-primary { display: flex; align-items: center; justify-content: center; gap: .4rem; background: var(--accent-color); color: #fff; border: none; border-radius: 10px; padding: .7rem 1rem; font-size: .9rem; font-weight: 600; cursor: pointer; transition: background .2s; text-decoration: none; width: 100%; }
    .vd-btn-primary:hover { background: #8b0000; color: #fff; }
    .vd-btn-secondary { display: flex; align-items: center; justify-content: center; gap: .4rem; background: #25d366; color: #fff; border: none; border-radius: 10px; padding: .65rem .9rem; font-size: .88rem; font-weight: 600; cursor: pointer; transition: background .2s; text-decoration: none; }
    .vd-btn-secondary:hover { background: #1da951; color: #fff; }
    .vd-btn-icon { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: #f3f4f6; border: 1.5px solid #e5e7eb; border-radius: 10px; color: #374151; font-size: .9rem; cursor: pointer; transition: background .2s; flex-shrink: 0; }
    .vd-btn-icon:hover { background: #e9ecef; }

    /* ── Attribute cards ────────────────────────────────────────────────── */
    .vd-attr-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: .85rem 1rem; }
    .vd-attr-title { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--accent-color); margin-bottom: .55rem; padding-bottom: .35rem; border-bottom: 1.5px solid rgba(110,7,7,.1); }
    .vd-attr-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: .28rem; }
    .vd-attr-item { display: flex; align-items: flex-start; gap: .4rem; font-size: .83rem; color: #374151; line-height: 1.4; }
    .vd-attr-item i { color: var(--accent-color); font-size: .72rem; flex-shrink: 0; margin-top: .2rem; }

    /* ── Specs Grid ─────────────────────────────────────────────────────── */
    .vd-specs-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: .5rem;
    }
    .vd-spec-card {
        background: #f8f9fa;
        border: 1.5px solid #e9ecef;
        border-radius: 12px;
        padding: .65rem .7rem;
        display: flex;
        flex-direction: column;
        gap: .35rem;
        transition: box-shadow .22s ease, transform .22s ease;
        animation: vdSpecUp .45s ease both;
        animation-delay: calc(var(--si, 0) * 60ms);
    }
    .vd-spec-head {
        display: flex;
        align-items: center;
        gap: .4rem;
    }
    .vd-spec-icon {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        background: rgba(110,7,7,.08);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent-color, #6e0707);
        font-size: .72rem;
        flex-shrink: 0;
    }
    .vd-spec-label {
        font-size: .63rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #9ca3af;
        line-height: 1;
    }
    .vd-spec-value {
        font-size: .92rem;
        font-weight: 700;
        color: #111;
        line-height: 1.2;
        padding-left: 2px;
    }
    .vd-spec-unit {
        font-size: .7rem;
        font-weight: 500;
        color: #6b7280;
    }
    .vd-spec-electric { background: #f0fdf4; border-color: #86efac; }
    .vd-spec-electric .vd-spec-icon { background: rgba(22,163,74,.12); color: #16a34a; }
    .vd-spec-electric .vd-spec-label { color: #4ade80; }
    .vd-spec-electric .vd-spec-value { color: #15803d; }
    .vd-spec-electric .vd-spec-unit { color: #16a34a; }
    @keyframes vdSpecUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Modern Mobile Gallery */
    .border-gallery {
        background: var(--white-color);
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: none;
    }

    .object-cover {
        object-fit: cover;
    }

    .swiper-slide img {
        border-radius: 12px;
    }

    .mySwiperThumbsMobile .swiper-slide img {
        border: 2px solid transparent;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .mySwiperThumbsMobile .swiper-slide-thumb-active img {
        border-color: var(--accent-color);
        box-shadow: 0 4px 12px rgba(110, 7, 7, 0.3);
    }

    /* Modern Card */
    .news-listing {
        background: var(--white-color) !important;
        border-radius: 12px;
        border: 1px solid rgba(110, 7, 7, 0.1);
    }

    .news-listing h3,
    .news-listing h5,
    .news-listing h2 {
        color: var(--accent-color);
        font-weight: 600;
    }

    .breadcrumb-mobile {
        background-color: transparent;
        padding: 0.5rem 0;
        margin-bottom: 1rem;
    }
    .breadcrumb-mobile .breadcrumb-item {
        font-size: 0.85rem;
    }
    .breadcrumb-mobile .breadcrumb-item a {
        color: var(--accent-color);
        text-decoration: none;
        font-weight: 500;
    }
    .breadcrumb-mobile .breadcrumb-item a:active {
        text-decoration: underline;
    }
    .breadcrumb-mobile .breadcrumb-item.active {
        color: #6b7280;
        font-size: 0.85rem;
    }

    .news-listing h5.card-title {
        padding-bottom: 10px;
        border-bottom: 2px solid rgba(110, 7, 7, 0.2);
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
        padding: 10px 16px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .btn-outline-form:hover {
        background: linear-gradient(135deg, #6e0707 0%, #990000 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(110, 7, 7, 0.3);
    }

    /* Navigation Buttons */
    .swiper-button-next-mobile,
    .swiper-button-prev-mobile {
        background-color: rgba(110, 7, 7, 0.9);
        width: 35px;
        height: 35px;
        border-radius: 50%;
    }

    .swiper-button-next-mobile::after,
    .swiper-button-prev-mobile::after {
        font-size: 16px;
        color: white;
    }

    /* Check icons */
    .bi-check-circle-fill {
        color: var(--accent-color);
        font-size: 1.1rem;
    }

    /* Modal Styles */
    #contactModalMobile .modal-content {
        background-color: var(--primary-color);
        color: white;
        border-radius: 12px;
    }

    #contactModalMobile .form-label {
        color: white;
    }

    .modal-backdrop.show {
        backdrop-filter: blur(20px);
        background-color: rgba(0, 0, 0, 0.8);
    }

    #contactModalMobile .form-control {
        background-color: var(--secondary-color);
        border: 1px solid var(--accent-color);
        color: white;
        border-radius: 8px;
    }

    #contactModalMobile .form-control:focus {
        background-color: rgba(255, 255, 255, 0.15);
        border-color: var(--accent-color);
        color: white;
        box-shadow: 0 0 0 3px rgba(110, 7, 7, 0.2);
    }

    #contactModalMobile .btn-primary {
        background: linear-gradient(135deg, #6e0707 0%, #990000 100%);
        border: none;
        border-radius: 50px;
        padding: 12px 24px;
        font-weight: 500;
    }
</style>


@push('scripts')
<script>
    // Swiper thumbs mobile
    const swiperThumbsMobile = new Swiper(".mySwiperThumbsMobile", {
        spaceBetween: 10,
        slidesPerView: 'auto',
        freeMode: true,
        watchSlidesProgress: true,
    });

    // Swiper principal mobile
    const swiperMainMobile = new Swiper(".mySwiperMainMobile", {
        spaceBetween: 10,
        navigation: {
            nextEl: ".swiper-button-next-mobile",
            prevEl: ".swiper-button-prev-mobile",
        },
        thumbs: {
            swiper: swiperThumbsMobile,
        },
        breakpoints: {
            0: {
                spaceBetween: 5
            },
            768: {
                spaceBetween: 10
            }
        }
    });

    document.querySelectorAll('.mobile-gallery-img').forEach(img => {
        img.addEventListener('click', () => {
            if (typeof window.vlLightboxOpen === 'function') {
                window.vlLightboxOpen(parseInt(img.dataset.index));
            }
        });
    });

    /* Open lightbox when clicking swiper navigation buttons */
    const swiperNextBtnMobile = document.querySelector('.swiper-button-next-mobile');
    const swiperPrevBtnMobile = document.querySelector('.swiper-button-prev-mobile');
    if (swiperNextBtnMobile) {
        swiperNextBtnMobile.addEventListener('click', () => {
            if (typeof window.vlLightboxOpen === 'function') {
                window.vlLightboxOpen(swiperMainMobile.realIndex);
            }
        });
    }
    if (swiperPrevBtnMobile) {
        swiperPrevBtnMobile.addEventListener('click', () => {
            if (typeof window.vlLightboxOpen === 'function') {
                window.vlLightboxOpen(swiperMainMobile.realIndex);
            }
        });
    }

    function copyShareLink() {
        const link = "{{ request()->fullUrl() }}";
        navigator.clipboard.writeText(link).then(() => {
            alert("Link copiado para a área de transferência!");
        }).catch(err => {
            console.error('Erro ao copiar link: ', err);
        });
    }
</script>
@endpush