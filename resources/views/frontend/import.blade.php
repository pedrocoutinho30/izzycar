@extends('frontend.partials.layout')

@section('title', 'Izzycar - Importação de Veículos')

@section('content')

@include('frontend.partials.hero-section', ['title' => $data->process_import['title'], 'subtitle' => $data->process_import['subtitle']])

<section class="featured-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center mt-4 mb-4">
                <a href="#form-proposta" class="btn btn-warning btn-lg mt-4" data-bs-toggle="modal" data-bs-target="#formPropostaModal">
                    Pedir Proposta
                </a>
            </div>
        </div>
    </div>
</section>



<section class="section-padding" id="section_description_import">
    <div class="container">
        <p class="text-center text-black">{!!$data->process_import['description']!!} </p>
        <p class="text-center text-black">{!!$data->process_import['content']!!}</p>



    </div>
</section>

<section class="section-padding" id="section_why_import">
    <div class="container">
        <h3 class="text-center mb-4">Porque Importar com a Izzycar</h3>
        <div class="row">
            @foreach ($why_import['enum_why_import'] as $item)
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

<section class="section-padding" id="section_import">
    <div class="container">

        <div class="desktop-only">
            @include('frontend.partials.vertical-tabs', [
            'data' => $data->process_import['process_import'],
            'title' => "Passo a passo",
            ])
        </div>
        <div class="mobile-only">
            @include('frontend.partials.vertical-tabs-mobile', [
            'data' => $data->process_import['process_import'],
            'title' => "Passo a passo",
            ])
        </div>
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
        <div class="desktop-only">
            @include('frontend.partials.vertical-tabs', [
            'data' => $data_custos['enum'],
            'title' => "Custos de importação",
            ])
        </div>
        <div class="mobile-only">
            @include('frontend.partials.vertical-tabs-mobile', [
            'data' => $data_custos['enum'],
            'title' => "Custos de importaçãos",
            ])
        </div>
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
                        {{ $faqItem['question'] }}
                    </button>
                </h2>
                <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse"
                    aria-labelledby="heading{{ $loop->index }}"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body text-dark">
                        {!! $faqItem['answer'] !!}
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