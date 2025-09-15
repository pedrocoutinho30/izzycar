@extends('frontend.partials.layout')
@section('title', $news['contents']['title'] . ' | Izzycar')
@section('meta_description', 'Leia a notícia completa: {{$news["contents"]["subtitle"]}} - mantenha-se informado sobre o mercado automóvel, importação, legalização e novidades de carros usados.')


@section('content')
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8 h-100">
                {{-- Notícia detalhada --}}
                <div class="news-box">
                    <h4 class="text-accent"> {{$news['contents']['title']}}</h4>
                    <p class="news-content-white"> {!!$news['contents']['subtitle']!!}</p>
                    @if(!empty($news['contents']['date']))
                    <p class="text-muted mb-1" style="font-size: 0.8rem !important;">
                        {{ \Carbon\Carbon::parse($news['contents']['date'])->format('d.m.Y') }}
                    </p>
                    @endif
                    @if($news['contents']['image'])
                    <img src="{{  asset('storage/' . $news['contents']['image'])  }}" loading="lazy"
                        class="img-fluid mb-3 rounded image-news"
                        alt="Imagem ilustrativa"
                        alt="Imagem da notícia: {{ $news['contents']['title'] }}">
                    @endif
                    <div class="text-dark">
                        {!! $news['contents']['content'] !!}
                    </div>

                    @if(!empty($news['contents']['gallery']) )

                    <div id="newsGalleryCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach(json_decode($news['contents']['gallery']) as $index => $image)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ asset('storage/' . $image) }}" loading="lazy"
                                    class="d-block w-100 img-fluid rounded" style=" max-height: 500px; width: auto; object-fit: cover;"
                                    alt="Imagem da notícia: {{ $news['contents']['title'] }}">
                            </div>
                            @endforeach
                        </div>

                        <!-- Controles -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#newsGalleryCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#newsGalleryCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Próximo</span>
                        </button>

                        <!-- Indicadores -->
                        <div class="carousel-indicators">
                            @foreach(json_decode($news['contents']['gallery']) as $index => $image)
                            <button type="button"
                                data-bs-target="#newsGalleryCarousel"
                                data-bs-slide-to="{{ $index }}"
                                class="{{ $index === 0 ? 'active' : '' }}"
                                aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                aria-label="Slide {{ $index + 1 }}"></button>
                            @endforeach
                        </div>
                    </div>

                    @endif
                    <div class="text-dark">
                        {!! $news['contents']['summary'] !!}
                    </div>
                    @if(isset($news['contents']['categories']) && is_array(json_decode($news['contents']['categories'])))
                    <div class="tags mt-2">
                        @foreach(json_decode($news['contents']['categories']) as $category)
                        @php $categoryName = App\Models\Page::find($category); @endphp
                        <span class="badge-category">{{ $categoryName['contents'][0]['field_value'] }}</span>
                        @endforeach
                    </div>
                    @endif

                </div>



            </div>

            <div class="col-lg-4 ">

                {{-- Os nossos serviços --}}
                <div class="recent-news-box p-3 mt-4 ">
                    <div class="group-badge-stylish">Serviços</div>
                    <div class="list-group mt-3">
                        <a href="{{ route('frontend.import') }}" class="list-group-item mb-2 d-flex align-items-center">
                            <div class="vehicle-info">
                                <p class="mb-1 text-white" style="font-size: 0.9rem;">
                                    <strong>Importação</strong>
                                </p>
                            </div>
                        </a>

                        <a href="{{ route('frontend.legalization') }}" class="list-group-item mb-2 d-flex align-items-center">
                            <div class="vehicle-info">
                                <p class="mb-1 text-white" style="font-size: 0.9rem;">
                                    <strong>Legalização</strong>
                                </p>
                            </div>
                        </a>

                        <!-- <a href="{{ route('frontend.selling') }}" class="list-group-item mb-2 d-flex align-items-center">
                            <div class="vehicle-info">
                                <p class="mb-1 text-white" style="font-size: 0.9rem;">
                                    <strong>Venda</strong>
                                </p>
                            </div>
                        </a> -->
                    </div>
                </div>
                {{-- Bloco Carros à Venda --}}
               
                <div class="recent-news-box p-3 mt-4 ">
                    <div class="group-badge-stylish">Notícias Recentes</div>
                    <div class="list-group mt-3">
                        @foreach($recentNews as $recent)
                        <a href="{{ route('frontend.news-details', $recent->slug) }}"
                            class="list-group-item  mb-2">
                            <p class="mb-1 text-white">{{ $recent['contents']['title'] }}</p>
                            <small class="text-muted " style="font-size: 0.8rem !important;">
                                @if(!empty($recent['contents']['date']))
                                {{ \Carbon\Carbon::parse($recent['contents']['date'])->format('d.m.Y') }}
                                @endif
                            </small>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('styles')
<style>
    .news-content-white h1,
    .news-content-white h2,
    .news-content-white h3,
    .news-content-white h4,
    .news-content-white h5,
    .news-content-white h6 {
        color: var(--text-muted-color);
    }

    .news-content-white *:not([style*="color"]) {
        color: var(--primary-color) !important;
    }

    .news-box {
        border: 1px solid var(--accent-color);
        background-color: transparent;
        /* quadrado preto */
        border-radius: 10px;
        /* cantos arredondados (opcional) */
        padding: 10px;
        /* espaço interno */

        /* texto branco */
    }

    .image-news {
        width: 100%;
        height: auto;
        max-height: 400px;
        object-fit: cover;
        border-radius: 10px;
    }


    .recent-news-box {
        background: var(--muted-color);
        /* fundo preto */
        color: #fff;
        /* texto branco */
        border-radius: 8px;
        position: relative;
    }

    .recent-news-box .list-group-item {
        background: transparent;
        /* fundo do item transparente */
        color: var(--accent-color);
        /* texto branco */
        border: 1px solid var(--accent-color);
        /* separador suave */
    }

    .recent-news-box .list-group-item:hover {
        background: var(--accent-color);
        /* efeito hover */
    }

    .recent-news-box .list-group-item:hover .text-accent {
        background: var(--accent-color);
        color: var(--primary-color);
        /* efeito hover */
    }




    .group-badge-stylish {
        background: var(--accent-color);
        color: white;
        font-weight: bold;
        padding: 14px 20px;
        font-size: 1rem;
        border-top-right-radius: 12px;
        border-bottom-left-radius: 12px;
        position: relative;
        /* deixa de ser absolute */
        display: inline-block;
    }

    .sticky-sidebar {
        position: sticky;
        top: 110px;
        /* distância do topo ao fazer scroll */
        z-index: 10;
    }

    .sticky-sidebar-services {
        position: sticky;
        top: 110px;
        /* distância do topo ao fazer scroll */
        z-index: 10;
    }

    .sticky-sidebar-vehicles {
        position: sticky;
        top: 450px;
        /* distância do topo ao fazer scroll */
        z-index: 10;
    }

    .vehicle-info p,
    .vehicle-info small {
        margin: 0;
    }

    .vehicle-info:hover {
        color: var(--primary-color);
    }
</style>