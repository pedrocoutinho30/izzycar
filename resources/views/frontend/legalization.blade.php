@extends('frontend.partials.layout')

@section('title', 'Izzycar - Legalização de Veículos')

@section('content')

@include('frontend.partials.hero-section', ['title' => $data->contents['title'], 'subtitle' => $data->contents['subtitle']])

<section class="featured-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center mb-4">

            </div>
        </div>
    </div>
</section>


<section class="section-padding" id="section_legalization">
    <div class="container">
        <p> {{$data->contents['content']}}</p>

        @include('frontend.partials.vertical-tabs', [
        'data' => $data->contents['enum'],
        'title' => "",
        ])
    </div>
</section>
@endsection