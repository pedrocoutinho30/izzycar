<section class="py-4 bg-light mt-4" role="main" itemscope itemtype="https://schema.org/Product">
    <div class="container">

        {{-- Breadcrumbs para SEO Mobile --}}
        <nav aria-label="Breadcrumbs" class="mb-3">
            <ol class="breadcrumb breadcrumb-mobile" itemscope itemtype="https://schema.org/BreadcrumbList">
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
        <div class="card news-listing shadow-sm mb-3">
            <div class="card-body">
                <h1 class="text-accent mb-1" itemprop="name">{{ $vehicle->brand }}</h1>
                <h2 class="text-accent mb-2" itemprop="model">{{ $vehicle->model }}@if($vehicle->version)<span itemprop="version"> {{ $vehicle->version }}</span>@endif</h2>
                <meta itemprop="brand" content="{{ $vehicle->brand }}" />
                <meta itemprop="sku" content="{{ $vehicle->reference }}" />
                @if($vehicle->status === 'reservado')
                <span class="badge rounded-pill fs-6 mb-3" style="background:#f59e0b;">Reservado</span>
                @elseif($vehicle->status === 'vendido')
                <span class="badge rounded-pill fs-6 mb-3" style="background:#dc2626;">Vendido</span>
                @endif

                @if($vehicle->asking_price && !in_array($vehicle->status ?? '', ['reservado', 'vendido']))
                <h3 class="text-accent mb-3" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                    <span itemprop="price">{{ number_format(round($vehicle->asking_price), 0, ',', ' ') }}</span> €
                    <meta itemprop="priceCurrency" content="EUR" />
                    <meta itemprop="availability" content="https://schema.org/InStock" />
                    <meta itemprop="url" content="{{ url()->current() }}" />
                </h3>
                @endif

                <div class="row g-2 mb-3">
                    @if($vehicle->year)
                    <div class="col-6 d-flex align-items-center">
                        <span class="icon-colored pe-2">@include('components.icons.calendar')</span>
                        <p class="mb-0 text-dark"><span itemprop="vehicleModelDate">{{ $vehicle->year }}</span></p>
                    </div>
                    @endif
                    @if($vehicle->kilometers)
                    <div class="col-6 d-flex align-items-center">
                        <span class="icon-colored pe-2">@include('components.icons.road')</span>
                        <p class="mb-0 text-dark"><span itemprop="mileageFromOdometer">{{ $vehicle->kilometers }}</span> KM</p>
                    </div>
                    @endif
                    @if($vehicle->fuel)
                    <div class="col-6 d-flex align-items-center">
                        <span class="icon-colored pe-2">@include('components.icons.fuel')</span>
                        <p class="mb-0 text-dark"><span itemprop="fuelType">{{ $vehicle->fuel }}</span></p>
                    </div>
                    @endif
                    @if($cilindrada)
                    <div class="col-6 d-flex align-items-center">
                        <span class="icon-colored pe-2">@include('components.icons.motor')</span>
                        <p class="mb-0 text-dark"><span itemprop="vehicleEngine" itemscope itemtype="https://schema.org/EngineSpecification"><span itemprop="engineDisplacement">{{$cilindrada}}</span></span> CC</p>
                    </div>
                    @endif
                    @if($potencia)
                    <div class="col-6 d-flex align-items-center">
                        <span class="icon-colored pe-2">@include('components.icons.power')</span>
                        <p class="mb-0 text-dark"><span itemprop="vehicleEngine" itemscope itemtype="https://schema.org/EngineSpecification"><span itemprop="enginePower">{{$potencia}}</span></span> CV</p>
                    </div>
                    @endif
                    @if($caixa)
                    <div class="col-6 d-flex align-items-center">
                        <span class="icon-colored pe-2">@include('components.icons.gearbox')</span>
                        <p class="mb-0 text-dark"><span itemprop="vehicleTransmission">{{$caixa}}</span></p>
                    </div>
                    @endif
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-outline-form flex-fill" data-bs-toggle="modal" data-bs-target="#shareModal">
                        <i class="bi bi-share-fill text-dark"></i>
                    </button>
                    <a href="https://wa.me/351914250947?text=Olá, gostaria de saber mais informações sobre o veículo {{$vehicle->brand}} {{$vehicle->model}} {{$vehicle->version}} ({{ $vehicle->reference }})
                                Link: {{ route('vehicles.details',  ['brand' => Str::slug($vehicle->brand),
                                    'model' => Str::slug($vehicle->model),
                                    'id' => $vehicle->reference]) }}"
                        target="_blank"
                        class="btn btn-outline-form">
                        <i class="bi bi-whatsapp fs-3 text-dark"></i>
                    </a>
                    <button class="btn btn-outline-form flex-fill text-dark" data-bs-toggle="modal" data-bs-target="#contactModal">
                        Informações
                    </button>
                </div>
            </div>
        </div>





        <!-- Equipamento / Atributos -->
        @foreach ($attributes as $group => $attrs)
        <div class="card news-listing shadow-sm mb-3">
            <div class="card-body">
                <h2 class="card-title text-accent fw-semibold mb-3">{{ $group }}</h2>
                <div class="row g-2">
                    @foreach ($attrs as $attr => $value)
                    @if(!in_array($attr, ['Potência', 'Cilindrada', 'Transmissão']))
                    <div class="col-12 d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2" style="color: var(--accent-color);"></i>
                        <span class="text-dark">{{ $attr == $value ? $attr : $attr . ': ' . $value }}</span>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        {{-- Veículos recentes --}}
        {{-- <div class="row g-3">
            @include('frontend.partials.vehicles-home', ['vehicles' => $last_vehicles])
        </div> --}}
    </div>
</section>


<style>
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