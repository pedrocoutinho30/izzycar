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
                                <img src="{{  $vehicle->images->first()->image_path }}" class="img-fluid " loading="lazy" alt="Imagem {{ $vehicle->brand }} {{ $vehicle->model }}" style="max-width: 100%; max-height: 300px;">
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
    /* Modern Vehicle Swiper Styles */
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
        box-sizing: border-box;
    }

    .custom-block {
        width: 100%;
        max-width: 360px;
        background-color: var(--white-color) !important;
        border-radius: 12px;
        border: 1px solid rgba(110, 7, 7, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .custom-block:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(110, 7, 7, 0.2) !important;
    }

    .custom-block a {
        text-decoration: none;
    }

    .custom-block h5 {
        font-weight: 600;
        color: var(--accent-color);
    }

    .custom-block h3 {
        color: var(--accent-color);
        font-weight: 700;
        font-size: 1.8rem;
    }

    .icon-colored {
        color: var(--accent-color);
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

    /* Image Wrapper */
    .image-wrapper {
        width: 100% !important;
        height: 220px;
        overflow: hidden;
        border-radius: 12px 12px 0 0;
    }

    .image-wrapper img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        object-position: center;
        display: block;
        transition: transform 0.3s ease;
    }

    .custom-block:hover .image-wrapper img {
        transform: scale(1.05);
    }

    /* Pagination */
    .swiper-pagination-bullet {
        background: var(--accent-color);
        opacity: 0.5;
    }

    .swiper-pagination-bullet-active {
        opacity: 1;
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