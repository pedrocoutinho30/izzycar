@extends('frontend.partials.layout')


@include('frontend.partials.seo', [
'seo' => $page->seo
])

@section('content')
@php
$home = App\Models\Page::where('slug', 'homepage')->first();
@endphp
@include('frontend.partials.hero-section', ['title' => 'Izzycar', 'subtitle' => $home['contents']->where('field_name', 'title')->first()->field_value])

<section class="featured-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center mb-4">
            </div>
        </div>
    </div>
</section>


<div class="container mt-4">


    <div class="text-center mb-4 text-dark">{!! $home['contents']->where('field_name', 'content')->first()->field_value !!}</div>

    <div class="text-center text-dark">{!! $home['contents']->where('field_name', 'nossa_missao')->first()->field_value !!}</div>


    <div class="text-center text-dark">{!! $home['contents']->where('field_name', 'nosso_papel')->first()->field_value !!}</div>


    <div class="text-center text-dark">{!! $home['contents']->where('field_name', 'nossos_valores')->first()->field_value !!}</div>




    <div class="text-center mb-4">
        <a href="{{ route('frontend.form-import') }}" class="btn btn-warning btn-lg mt-4">
            Quero importar
        </a>
    </div>

</div>
</section>


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






    @endsection