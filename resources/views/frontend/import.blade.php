@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => $page->seo
])
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
        <h3 class="text-center mb-4">Porque Importar com a Izzycar?</h3>
        <div class="row">
            @foreach ($why_import['enum_why_import'] as $item)
            <!-- <div class="col-md-6 mb-4 mr-4">
                <div class="custom-block card-listing shadow-lg  h-100">
                    <h5 class="text-accent">
                        {{ $item['title'] }}
                    </h5>
                    <p class="">{!! $item['content'] !!}</p>
                </div>

            </div> -->
            <div class="col-md-6 mb-4 mr-4">
                <div class="custom-block custom-block-transparent news-listing shadow-lg p-4 h-100">
                    <h5 class="text-accent">
                        {{ $item['title'] }}
                    </h5>
                    <div class="text-dark">{!! $item['content'] !!}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-padding" id="section_import">
    <div class="container">


        <div id="desktop-content">
            @include('frontend.partials.vertical-tabs', [
            'data' => $data->process_import['process_import'],
            'title' => "Passo a passo",
            ])
        </div>
        <div id="mobile-content">
            @include('frontend.partials.accordion-mobile', [
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
        <div id="desktop-content-custos">
            @include('frontend.partials.horizontal-tabs', [
            'data' => $data_custos['enum'],
            'title' => "Custos de importação",
            ])
        </div>
        <div id="mobile-content-custos">
            @include('frontend.partials.accordion-mobile', [
            'data' => $data_custos['enum'],
            'title' => "Custos de importação",
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


<section class="section-padding mb-4" id="section_faq">
    <div class="container">

        <h3 class="text-center mb-4">Perguntas Frequentes</h3>
        <!-- FAQ -->
        <div class="accordion custom-accordion" id="accordionFaq">
            @forelse ($faq['enum'] ?? [] as $faqItem)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-faq-{{ $loop->index }}">
                    <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse-faq-{{ $loop->index }}"
                        aria-expanded="false"
                        aria-controls="collapse-faq-{{ $loop->index }}">
                        {{ strip_tags($faqItem['question']) }}
                    </button>
                </h2>
                <div id="collapse-faq-{{ $loop->index }}" class="accordion-collapse collapse"
                    aria-labelledby="heading-faq-{{ $loop->index }}"
                    data-bs-parent="#accordionFaq">
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
    function handleResponsiveTabs() {
        const desktopDivPassos = document.getElementById('desktop-content');
        const mobileDivPassos = document.getElementById('mobile-content');

        if (window.innerWidth >= 769) {
            desktopDivPassos.style.display = 'block';
            mobileDivPassos.style.display = 'none';
        } else {
            desktopDivPassos.style.display = 'none';
            mobileDivPassos.style.display = 'block';
        }

        const desktopDivCustos = document.getElementById('desktop-content-custos');
        const mobileDivCustos = document.getElementById('mobile-content-custos');

        if (window.innerWidth >= 769) {
            desktopDivCustos.style.display = 'block';
            mobileDivCustos.style.display = 'none';
        } else {
            desktopDivCustos.style.display = 'none';
            mobileDivCustos.style.display = 'block';
        }
    }

    // Inicializa no load
    document.addEventListener('DOMContentLoaded', handleResponsiveTabs);

    // Atualiza se a janela for redimensionada
    window.addEventListener('resize', handleResponsiveTabs);


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