@extends('frontend.partials.layout')

@section('title', 'Izzycar - Legalização de Veículos')

@section('content')


<section class="hero-section d-flex justify-content-center align-items-center" id="section_home_legalization">
    <div class="container">
        <div class="row">

            <div class="col-lg-8 col-12 mx-auto">
                <h1 class="text-white text-center"> {{$data->contents['title']}}
                </h1>

                <h5 class="text-center"> {{$data->contents['subtitle']}}</h5>

                <p> {{$data->contents['content']}}</p>
            </div>
        </div>

    </div>
</section>

<section class="section-padding" id="section_legalization">
    <div class="container">
    @include('frontend.partials.vertical-tabs', [
            'data' => $data->contents['enum'],
            'title' => "",
        ])
    </div>
</section>
@endsection