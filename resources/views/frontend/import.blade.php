@extends('frontend.partials.layout')

@section('title', 'Izzycar')

@section('content')



<section class="hero-section d-flex justify-content-center align-items-center" id="section_home_import">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-12 mx-auto text-center">
                <h1 class="text-white">{{$data->process_import['title']}}</h1>
                <a href="#form-proposta" class="btn btn-warning btn-lg mt-4" data-bs-toggle="modal" data-bs-target="#formPropostaModal">
                    Pedir Proposta
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section-padding" id="section_description_import">
    <div class="container">
        <h3 class="text-center "> {{$data->process_import['subtitle']}}</h3>
        <h5 class="text-center text-muted"> {!!$data->process_import['description']!!}</h5>
        <h6>{!!$data->process_import['content']!!}</h6>
    </div>
</section>

<section class="section-padding" id="section_why_import">
    <div class="container">
        <h3 class="text-center mb-4">Porque Importar com a Izzycar</h3>
        <div class="row">
            @foreach ($why_import['enum_why_import'] as $item)
            <div class="col-md-6 mb-4 mr-4">

                <div class="custom-block card-listing shadow-lg p-4 h-100">

                    <h5 class="mb-2" style="min-height: 3.5em; line-height: 1.2; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ $item['title'] }}
                    </h5>
                    <p>{{ $item['content'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-padding" id="section_import">
    <div class="container">

        @include('frontend.partials.vertical-tabs', [
        'data' => $data->process_import['process_import'],
        'title' => "Passo a passo",
        ])
    </div>
</section>

<section class="section-padding text-center">
    <a href="#form-proposta" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#formPropostaModal">
        Quero Importar um Carro
    </a>
</section>

@if(!empty($data_custos))
<section class="section-padding" id="section_import_costs">
    <div class="container">
        @include('frontend.partials.vertical-tabs', [
        'data' => $data_custos['enum'],
        'title' => "Custos de importação",
        ])
    </div>
</section>
<section class="section-padding text-center">
    <a href="#form-proposta" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#formPropostaModal">
        Pedir Simulação de Custos
    </a>
</section>
@endif


<section class="section-padding" id="section_faq">
    <div class="container">
        <h3 class="text-center mb-4">Perguntas Frequentes</h3>
        <div class="accordion custom-accordion" id="accordionExample">
            @forelse ($faq['enum'] ?? [] as $faqItem)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{ $loop->index }}">
                    <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse{{ $loop->index }}"
                        aria-expanded="false"
                        aria-controls="collapse{{ $loop->index }}">
                        {{ $faqItem['awnser'] }}
                    </button>
                </h2>
                <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse"
                    aria-labelledby="heading{{ $loop->index }}"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body text-dark">
                        {!! $faqItem['response'] !!}
                    </div>
                </div>
            </div>
            @empty
            <p class="text-center text-muted">Sem FAQs disponíveis.</p>
            @endforelse
        </div>

    </div>
</section>


@include('frontend.partials.import-form-modal', [
    'brands' => $brands,
])



@endsection


<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        document.getElementById('ad_option').addEventListener('change', function() {
            let adLinksBox = document.getElementById('ad_links_box');
            let preferencesBox = document.getElementById('preferences_box');

            adLinksBox.classList.add('d-none');
            preferencesBox.classList.add('d-none');

            if (this.value === 'sim') {
                adLinksBox.classList.remove('d-none');
            } else if (this.value === 'nao_sei') {
                preferencesBox.classList.remove('d-none');
            }
        });
    });
</script>
<script>
    const swiper = new Swiper('.mySwiper', {
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