<section class="explore-section section-padding" id="section_servicos">
    <div class="container">
        <div class="row">

            <div class="col-12 text-center">
                <h2 class="mb-4">{{$servicos->title}}</h1>
            </div>

        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                
                @php $activeSet = false; @endphp
                @foreach (json_decode($servicos->contents[0]->field_value) as $serviceId)
                <?php
                $service = \App\Models\Page::find($serviceId)->contents;
                ?>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link @if(!$activeSet) active @php $activeSet = true; @endphp @endif"
                        id="tab-{{ $serviceId }}"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-pane-{{ $serviceId }}"
                        type="button"
                        role="tab"
                        aria-controls="tab-pane-{{ $serviceId }}"
                        aria-selected="{{ !$activeSet ? 'true' : 'false' }}"
                        data-service-id="{{ $serviceId }}"
                        onclick="setSelectedServiceId({{ $serviceId }})">
                        {{ $service[0]->field_value }}
                    </button>
                </li>
                @endforeach
            </ul>

        </div>
    </div>


    <?php
    // Obter o serviceId ativo da request ou usar o primeiro como padrão
    $serviceIds = json_decode($servicos->contents[0]->field_value);
    $selectedServiceId = request('serviceId', $serviceIds[0] ?? null);

    $serviceActive = \App\Models\Page::find($selectedServiceId)?->contents ?? collect();
    $subPages = $serviceActive->filter(function ($item) {
        return $item->field_name === 'page_repeater';
    })->pluck('field_value')->first();

    $subPages = json_decode($subPages);
    if (empty($subPages)) {
        $subPages = [];
    }
    $pages = \App\Models\Page::whereIn('id', array_values($subPages))->get();
    ?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="design-tab-pane" role="tabpanel" aria-labelledby="design-tab" tabindex="0">

                        <!-- <div class="col-lg-4 col-md-6 col-12 mb-4 mb-lg-0">
                                <div class="custom-block bg-white shadow-lg">
                                    <a href="topics-detail.html">
                                        <div class="d-flex">
                                            <div>
                                                <h5 class="mb-2">Web Design</h5>

                                                <p class="mb-0">Topic Listing Template based on Bootstrap 5</p>
                                            </div>

                                            <span class="badge bg-design rounded-pill ms-auto">14</span>
                                        </div>

                                        <img src="images/topics/undraw_Remote_design_team_re_urdx.png" class="custom-block-image img-fluid" alt="">
                                    </a>
                                </div>
                            </div> -->

                        <div class="swiper mySwiper" id="serviceCardsContainerWrapper">
                            <div class="swiper-wrapper" id="serviceCardsContainer">
                                <!-- Slides são inseridos aqui -->
                            </div>

                            <!-- Navegação -->
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>

                            <!-- Paginação opcional -->
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>


                </div>

            </div>
        </div>
</section>

<script>
    let swiperInstance = null;

    async function setSelectedServiceId(serviceId) {
        try {
            const response = await fetch(`/load-service-cards/${serviceId}`);
            const pages = await response.json();

            const container = document.getElementById('serviceCardsContainer');
            container.innerHTML = ''; // limpa os cards anteriores

            if (swiperInstance) {
                swiperInstance.destroy(true, true); // destrói swiper antigo
            }

            if (!pages.length) {
                container.innerHTML = '<p>Nenhum serviço encontrado.</p>';
                return;
            }

            pages.forEach(page => {
                const slide = `
                    <div class="swiper-slide">
                        <div class="custom-block card-listing shadow-lg">
                            <a href="#">
                                <div class="d-flex">
                                    <div>
                                        <h5 class="mb-2">${page.title}</h5>
                                        <p class="mb-0">${page.description}</p>
                                    </div>
                                </div>
                                <img src="${page.image}" class="custom-block-image img-fluid" alt="" loading="lazy">
                            </a>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', slide);
            });

            swiperInstance = new Swiper('.mySwiper', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: pages.length > 3,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                pagination: {
                    el: ".swiper-pagination",
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

        } catch (error) {
            console.error('Erro ao carregar serviços:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const activeBtn = document.querySelector('.nav-link.active');
        if (activeBtn) {
            const defaultServiceId = activeBtn.dataset.serviceId;
            if (defaultServiceId) setSelectedServiceId(defaultServiceId);
        }
    });
</script>
<style>
    /* Garante altura automática para o swiper */
    .swiper {
        padding-bottom: 50px;
        /* espaço para a paginação */
    }

    /* Os slides ocupam toda a altura e mantêm espaçamento */
    .swiper-slide {
        display: flex;
        justify-content: center;
        height: auto;
    }

    /* Garante que o card tenha altura ajustável */
    .custom-block {
        height: 100%;
        width: 100%;
    }

    /* Botões de navegação fora dos cards */
    .swiper-button-next,
    .swiper-button-prev {
        top: 40%;
        transform: translateY(-50%);
        color: #000;
        /* ou a tua cor de destaque */
    }

    /* Botões visíveis mesmo em ecrãs pequenos */
    .swiper-button-next::after,
    .swiper-button-prev::after {
        font-size: 24px;
    }

    /* Paginação centralizada e fora da área dos slides */
    .swiper-pagination {
        bottom: 0;
        text-align: center;
    }

    @media (max-width: 768px) {

        .swiper-button-next,
        .swiper-button-prev {
            display: none;
        }
    }
</style>