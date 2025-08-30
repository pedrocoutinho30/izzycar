<section class="section-padding mb-2">
    <div class="container">
        <div class="row">
            @foreach ($news as $new)
            <div class="col-12">
                <div class="custom-block-topics-listing card-listing shadow-lg mb-5">
                    <a href="{{ route('frontend.news-details', $new->slug) }}" class="d-block text-decoration-none p-3">
                        <div class="row align-items-center">

                            {{-- Texto (à esquerda no mobile, à direita no desktop) --}}
                            <div class="col-7 col-md-8 d-flex flex-column order-1 order-md-2">
                                <h6 class="text-white">{{ $new['contents']['title'] }}</h6>
                                <p class="mb-1 list">
                                    @if(!empty($new['contents']['date']))
                                        {{ \Carbon\Carbon::parse($new['contents']['date'])->format('d/m/Y') }}
                                    @endif
                                </p>

                                {{-- Tags --}}
                                @if(isset($new['contents']['categorias']) && is_array($new['contents']['categorias']))
                                <div class="tags mt-2">
                                    @foreach($new['contents']['categorias'] as $categoria)
                                        <span class="badge-category">{{ $categoria['name'] }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>

                            {{-- Imagem (à direita no mobile e à esquerda no desktop) --}}
                            <div class="col-5 col-md-4 order-2 order-md-1">
                                <img src="{{ $new->image_path ? asset('storage/' . $new->image_path) : asset('images/default-car.jpg') }}"
                                    class="custom-block-image img-fluid w-100"
                                    alt="Imagem da notícia: {{ $new->title }}">
                            </div>

                        </div>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <div class="row g-5">
            @include('frontend.partials.vehicles-home', ['vehicles' => $last_vehicles])
        </div>
    </div>
</section>
