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

<section class="section-padding" id="section_description_import">
    <div class="container">
        <p class="text-center text-black">{!!$data->contents['content']!!}</p>



    </div>
</section>
<section class="section-padding" id="section_legalization">
    <div class="container">

        <h3 class="text-center mb-4">Todos os passos para legalizar o seu veículo</h3>
        <div class="row">
            @foreach ($data->contents['enum'] as $item)
            <div class="col-md-6 mb-4 mr-4">
                <div class="custom-block card-listing shadow-lg p-4 h-100">
                    <h5 class="mb-2 text-white" style="min-height: 3.5em; line-height: 1.2; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ $item['title'] }}
                    </h5>
                    <p class="text-white">{{ $item['content'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
       
    </div>
</section>
@endsection