@extends('frontend.partials.layout')

@section('title', 'Izzycar')
@php use Illuminate\Support\Str; @endphp
@section('content')

@include('frontend.partials.hero-section', ['title' => 'Usados', 'subtitle' => 'Encontre o carro dos seus sonhos com a Izzycar'])

<section class="featured-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center mb-4">
                <!-- <img src="{{ asset('storage/' . $vehicles[0]->images[0]->image_path) }}"
                    class="img-fluid rounded" style="max-width: auto; max-height: 200px;"
                    alt="{{ $vehicles[0]->brand }} {{ $vehicles[0]->model }}"> -->

            </div>
        </div>
    </div>
</section>
<section class="section-padding">
    <div class="container">

        <div class="desktop-only">
            {{-- Conteúdo Desktop --}}
            @include('frontend.vehicles-list-desktop')
        </div>

        <div class="mobile-only">
            {{-- Conteúdo Mobile --}}
            @include('frontend.vehicles-list-mobile')
        </div>
    </div>
</section>

@endsection
