@extends('frontend.partials.layout')

@section('title', 'Izzycar - Notícias')

@section('content')

@include('frontend.partials.hero-section', ['title' => 'Notícias', 'subtitle' => 'Fique por dentro das novidades do mundo automotivo e da Izzycar'])

<section class="featured-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center mb-4">
                
            </div>
        </div>
    </div>
</section>
<section class="section-padding mb-2">
    <div class="container">
        <div class="desktop-only">
            {{-- Conteúdo Desktop --}}
            @include('frontend.news-desktop')
        </div>

        <div class="mobile-only">
            {{-- Conteúdo Mobile --}}
            @include('frontend.news-mobile')
        </div>
    </div>
</section>


@endsection