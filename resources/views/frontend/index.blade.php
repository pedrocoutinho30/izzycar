@extends('frontend.partials.layout')

@section('title', 'Izzycar')

@section('content')
<section class="hero-section d-flex justify-content-center align-items-center" id="section_home">
    <div class="container">
        <div class="row">

            <div class="col-lg-8 col-12 mx-auto">
                <h1 class="text-white text-center"> Izzycar </h1>
                </h1>

                <h5 class="text-center"> Importação de Veículos na Mão</h5>
            </div>
        </div>
    </div>
</section>


<section class="featured-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center mb-4">
                <img src="{{ asset('storage/' . $vehicles[0]->images[0]->image_path) }}"
                    class="img-fluid rounded" style="max-width: auto; max-height: 200px;"
                    alt="{{ $vehicles[0]->brand }} {{ $vehicles[0]->model }}">

            </div>

    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
        new Swiper('.mySwiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
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
    @endpush

    <!-- 
            <div class="col-lg-4 col-12 mb-4 mb-lg-0">
                <div class="custom-block bg-white shadow-lg">
                    <a href="topics-detail.html">
                        <div class="d-flex">
                            <div>
                                <h5 class="mb-2">O que fazemos</h5>
                                <p class="mb-0">Somos uma consultora especializada na importação chave na mão. </p>
                            </div>
                        </div>
                        <img src="images/topics/undraw_Remote_design_team_re_urdx.png" class="custom-block-image img-fluid" alt="">
                    </a>
                </div>
            </div>

            <div class="col-lg-6 col-12">
                <div class="custom-block custom-block-overlay">
                    <div class="d-flex flex-column h-100">
                        <img src="images/mercedes-home.jpeg" class="custom-block-image img-fluid" alt="" style="max-width: 100%; max-height: 500px;">

                        <div class="custom-block-overlay-text d-flex">
                            <div>
                                <h5 class="text-white mb-2">Os nossos carros</h5>

                                <p class="text-white">E como não só de importações nos fazemos, aqui vamos ter um acesso direto aos nossos carros </p>

                                <a href="{{ route('vehicles.list') }}" class="btn custom-btn mt-2 mt-lg-3">Ver todos</a>
                            </div>

                            <span class="badge bg-finance  rounded-pill ms-auto">{{ $vehicles_count }}</span>
                        </div>



                        <div class="section-overlay"></div>
                    </div>
                </div>
            </div> -->

    </div>
    </div>
</section>

<?php
// $servicos = $navItems->where('url', 'servicos')->first()->page;

// 
?>

@include('frontend.partials.vehicles-home', ['vehicles' => $last_vehicles])


@endsection