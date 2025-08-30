@extends('frontend.partials.layout')
@section('title', 'Izzycar')

@section('content')
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8 ">


                <div class="news-box">
                    <h4 class="text-accent"> {{$news['contents']['title']}}</h4>
                    <p class="text-muted"> {!!$news['contents']['subtitle']!!}</p>
                    @if(!empty($news['contents']['date']))
                    <p class="text-muted mb-1" style="font-size: 0.9rem;">
                        {{ \Carbon\Carbon::parse($news['contents']['date'])->format('d-m-Y') }}
                    </p>
                    @endif
                    <img src="{{ $news->image_path ? asset('storage/' . $news->image_path) : asset('images/default-car.jpg') }}"
                        class="img-fluid mb-3 rounded image-news"
                        alt="Imagem ilustrativa"
                        alt="Imagem da notícia: {{ $news['contents']['title'] }}">
                    <div class="text-white">
                        <div class="news-content-white">
                            {!! $news['contents']['content'] !!}
                        </div>

                    </div>

                    <div class="news-content-white">
                        {!! $news['contents']['summary'] !!}
                    </div>
                    @if(!empty($news['contents']['categorias']) && is_array($news['contents']['categorias']) && count($news['contents']['categorias']) > 0)
                    <div class="tags mt-auto">
                        @foreach($news['contents']['categorias'] as $categoria)
                        <span class="badge-category">{{ $categoria['name'] }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>


            </div>
            <div class="col-lg-4 ">
                <div class="recent-news-box p-3 ">
                    <div class="group-badge-stylish">Notícias Recentes</div>
                    <div class="list-group mt-3">
                        @foreach($recentNews as $recent)
                        <a href="{{ route('frontend.news-details', $recent->slug) }}"
                            class="list-group-item  mb-2">
                            <p class="mb-1 text-white">{{ $recent['contents']['title'] }}</p>
                            <small class="text-muted">
                                @if(!empty($recent['contents']['date']))
                                {{ \Carbon\Carbon::parse($recent['contents']['date'])->format('d-m-Y') }}
                                @endif
                            </small>
                        </a>
                        @endforeach
                    </div>
                </div>
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

                        <a href="{{ route('frontend.selling') }}" class="list-group-item mb-2 d-flex align-items-center">
                            <div class="vehicle-info">
                                <p class="mb-1 text-white" style="font-size: 0.9rem;">
                                    <strong>Venda</strong>
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
                {{-- Bloco Carros à Venda --}}
                <div class="recent-news-box p-3 mt-4 ">
                    <div class="group-badge-stylish">Carros à Venda</div>
                    <div class="list-group mt-3">
                        @foreach($vehicles as $vehicle)
                        <a href="{{ route('vehicles.details', [
                                    'brand' => Str::slug($vehicle->brand),
                                    'model' => Str::slug($vehicle->model),
                                    'id' => $vehicle->reference
                                ]) }}" class="list-group-item mb-2 d-flex align-items-center">

                            {{-- Imagem pequena --}}
                            <img src="{{ $vehicle->images->isNotEmpty() ? asset('storage/' . $vehicle->images->first()->image_path) : asset('images/default-car.jpg') }}"
                                alt="{{ $vehicle->brand }} {{ $vehicle->model }}"
                                class="img-fluid rounded me-2"
                                style="width: 60px; height: 60px; object-fit: cover;">

                            {{-- Info lateral --}}
                            <div class="vehicle-info">
                                <p class="mb-1 text-white" style="font-size: 0.9rem;">
                                    <strong>{{ $vehicle->brand }} {{ $vehicle->model }}</strong> {{ $vehicle->version }}
                                </p>
                                <small class="text-muted" style="font-size: 0.8rem;">
                                    {{ $vehicle->year ?? '-' }} | {{ number_format($vehicle->kilometers ?? 0, 0, ',', '.') }} KM
                                </small>
                                <p class="mb-0 text-accent" style="font-size: 0.9rem; font-weight: bold;">
                                    € {{ number_format($vehicle->sell_price ?? 0, 0, ',', '.') }}
                                </p>
                            </div>
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
        color: var(--text-muted-color) !important;
    }

    .news-box {
        border: 1px solid #ddd;
        background-color: var(--secondary-color);
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
        background: var(--secondary-color);
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
</style>