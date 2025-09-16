@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => $data->seo
])
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

        <h3 class="text-center mb-4">Passos para legalizar o seu veículo</h3>
        <div class="row">
            @foreach ($data->contents['enum'] as $item)
            <!-- <div class="col-md-6 mb-4 mr-4">
                <div class="custom-block card-listing shadow-lg  h-100">
                    <h5 class="text-accent" >
                        {{ $item['title'] }}
                    </h5>
                    <p class="text-muted">{!! $item['content'] !!}</p>
                </div>
            </div> -->
            <div class="col-md-6 mb-4 mr-4">
                <div class="custom-block custom-block-transparent news-listing shadow-lg p-4 h-100">
                    <h5 class="text-accent">
                        {{ $item['title'] }}
                    </h5>
                    <div class="text-dark mb-4">{!! $item['content'] !!}</div>
                </div>
            </div>
            @endforeach
        </div>
       
    </div>
</section>
@endsection