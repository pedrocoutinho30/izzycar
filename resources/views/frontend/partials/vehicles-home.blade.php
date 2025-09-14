<section class="explore-section section-padding" id="section_veiculos">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-3">
                <h2 class="mb-2">Veículos em Destaque</h2>
            </div>
        </div>

        <div class="swiper mySwiperHome mt-2">
            <div class="swiper-wrapper">
                @foreach ($vehicles as $vehicle)
                <div class="swiper-slide ">
                    <div class="custom-block custom-block-transparent news-listing  p-4 h-100">
                        <a href="{{ route('vehicles.details', [
                                    'brand' => Str::slug($vehicle->brand),
                                    'model' => Str::slug($vehicle->model),
                                    'id' => $vehicle->reference
                                ]) }}" class="text-decoration-none text-dark">
                            <div class="image-wrapper mb-3">
                                <img src="{{ $vehicle->images->isNotEmpty() ? asset('storage/' . $vehicle->images->first()->image_path) : asset('images/default-car.jpg') }}" class="img-fluid " alt="Imagem {{ $vehicle->brand }} {{ $vehicle->model }}" style="max-width: 100%; max-height: 300px;">
                            </div>
                            <h5 class="mb-2 text-accent" style="min-height: 3.5em; line-height: 1.2; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->version }}
                            </h5>
                            <div class="d-flex align-items-start ">
                                <span class="icon-colored pe-3">@include('components.icons.calendar')</span>
                                <p class="mb-0">{{ $vehicle->year }}</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="icon-colored pe-3">@include('components.icons.road')</span>
                                <p class="mb-0">{{ $vehicle->kilometers }} KM</p>
                            </div>

                            <div class="d-flex align-items-start">
                                <span class="icon-colored pe-3">@include('components.icons.fuel')</span>
                                <p class="mb-0">{{ $vehicle->fuel }}</p>
                            </div>
                            <h3 class="d-flex align-items-end mt-4" style="color: var(--accent-color);">
                                {{ number_format(round($vehicle->sell_price), 0, ',', ' ') }}&nbsp;€
                            </h3>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Navegação -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>

            <!-- Paginação opcional -->
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>
<style>
    .swiper {
        padding-bottom: 50px;
        background-color: transparent !important;
    }

    .swiper-slide {
        background-color: transparent !important;
        display: flex;
        justify-content: center;
        height: auto;
        overflow: visible !important;
        padding-top: 10px;
        /* espaço extra em cima */
        box-sizing: border-box;
    }

    .custom-block {
        width: 100%;
        max-width: 360px;
        background-color: transparent !important;
    }

    .swiper-button-next,
    .swiper-button-prev {
        color: #000;
    }

    .image-wrapper {
        width: auto !important;
        /* largura desejada */
        height: 200px;
        /* altura desejada */
        overflow: hidden;
        border-radius: 8px;
        /* opcional */
    }

    .image-wrapper img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        /* recorta e preenche */
        object-position: center;
        /* foca no centro */
        display: block;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script>
    new Swiper('.mySwiperHome', {
        slidesPerView: 1,
        spaceBetween: 10,
        loop: false,
        watchOverflow: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
            disabledClass: 'swiper-button-disabled'
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            768: {
                slidesPerView: 2,
            },
            992: {
                slidesPerView: 3,
            }
        }
    });
</script>