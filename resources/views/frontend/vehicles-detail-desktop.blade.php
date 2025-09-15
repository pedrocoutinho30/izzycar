<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-5">
            <!-- Coluna com imagens -->
            <div class="col-lg-8 border-gallery">
                <!-- Swiper principal (imagem grande) -->
                <div class="swiper mySwiperMain">
                    <div class="swiper-wrapper">
                        @foreach ($vehicle->images as $key => $image)
                        <div class="swiper-slide">
                            <img src="{{ $image->image_path }}" loading="lazy"
                                class="img-fluid rounded " style=" cursor: pointer; object-fit: cover; object-position: center;"
                                alt="{{ $vehicle->brand }} {{ $vehicle->model }} {{ $key + 1 }}">
                        </div>

                        @endforeach
                    </div>
                    <!-- Navegação -->
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>

                <!-- Swiper das miniaturas -->
                <div class="swiper mySwiperThumbs mt-1">
                    <div class="swiper-wrapper">
                        @foreach ($vehicle->images as $key => $image)
                        <div class="swiper-slide " style="width: auto; height: 100px; cursor: pointer; ">
                            <img src="{{$image->image_path }}"
                                class="img-fluid rounded" loading="lazy"
                                alt="{{ $vehicle->brand }} {{ $vehicle->model }} {{ $key + 1 }}">
                        </div>
                        @endforeach
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
                                    <h3 class="text-accent">{{ $vehicle->brand }}</h3>
                                    <h5 class="text-accent"> {{ $vehicle->model }} {{ $vehicle->version }}</h5>

                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-start ">
                                        <span class="icon-colored pe-3">@include('components.icons.calendar')</span>
                                        <p class="mb-0 text-dark">{{ $vehicle->year }}</p>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center">
                                        <span class="icon-colored pe-3">@include('components.icons.road')</span>
                                        <p class="mb-0 text-dark">{{ $vehicle->kilometers }} KM</p>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex align-items-start">
                                        <span class="icon-colored pe-3">@include('components.icons.fuel')</span>
                                        <p class="mb-0 text-dark">{{ $vehicle->fuel }}</p>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex align-items-start">
                                        <span class="icon-colored pe-3">@include('components.icons.motor')</span>
                                        <p class="mb-0 text-dark">{{$cilindrada}} CC</p>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex align-items-start">
                                        <span class="icon-colored pe-3">@include('components.icons.power')</span>
                                        <p class="mb-0 text-dark">{{$potencia}} CV</p>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex align-items-start">
                                        <span class="icon-colored pe-3">@include('components.icons.gearbox')</span>
                                        <p class="mb-0 text-dark">{{$caixa}}</p>
                                    </div>
                                </div>
                            </div>

                            <h3 class="d-flex align-items-end mt-4" style="color: var(--accent-color);">
                                {{ number_format(round($vehicle->sell_price), 0, ',', ' ') }}&nbsp;€
                            </h3>
                            <div class="d-flex gap-3 align-items-center mt-3">
                                <!-- Botão de partilha -->
                                <button type="button" class="btn btn-outline-form" data-bs-toggle="modal" data-bs-target="#shareModal">
                                    <i class="bi bi-share-fill text-dark"></i>
                                </button>
                                <a href="https://wa.me/351914250947?text=Olá, gostaria de saber mais informações sobre o veículo {{$vehicle->brand}} {{$vehicle->model}} {{$vehicle->version}} ({{ $vehicle->reference }})
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
                        <h5 class="text-accent fw-semibold mb-4">{{ $group }}</h5>

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

<section class="py-5 bg-light">
    <div class="container">

        <div class="row g-5">
            @include('frontend.partials.vehicles-home', ['vehicles' => $last_vehicles])
        </div>
    </div>
</section>

@push('styles')
<style>
    .border-gallery {
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 10px;
        background-color: var(--secondary-color);
        border-radius: 8px;
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
        border-radius: 10px;
    }

    /* Imagem principal */
    .mySwiperMain .swiper-slide img {
        width: 100%;
        max-height: 500px;
        object-fit: cover;
        border-radius: 10px;
    }

    /* Miniaturas */
    .mySwiperThumbs .swiper-slide img {
        border-radius: 6px;
        border: 2px solid transparent;
    }

    .mySwiperThumbs .swiper-slide-thumb-active img {
        border-color: var(--accent-color);
    }

    /* Mobile */
    @media (max-width: 768px) {
        .border-gallery {
            padding: 5px;
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
            width: 30px;
            height: 30px;
            background-color: rgba(0, 0, 0, 0.4);
            border-radius: 50%;
        }

        .swiper-button-next::after,
        .swiper-button-prev::after {
            font-size: 14px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
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
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
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