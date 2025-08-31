
        <div class="row ">
            @foreach ($news as $new)
            <div class="col-md-12">
                <div class="custom-block-topics-listing card-listing shadow-lg mb-5">
                    <a href="{{ route('frontend.news-details', $new->slug) }}" class="d-block text-decoration-none p-3">
                        <div class="d-flex">

                            {{-- Imagem da notícia --}}
                            <div class="image-wrapper">
                                <img src="{{ $new->image_path ? asset('storage/' . $new->image_path) : asset('images/default-car.jpg') }}"
                                    class="custom-block-image img-fluid"
                                    alt="Imagem da notícia: {{ $new->title }}">
                            </div>

                            {{-- Info lateral --}}
                            <div class="custom-block-topics-listing-info d-flex flex-column ms-3" style="flex:1;">
                                <div>
                                    <h3 class="text-white">{{ $new['contents']['title'] }}</h3>
                                    <h6 class="mb-2 text-white">
                                        {!! \Illuminate\Support\Str::limit($new['contents']['subtitle'], 200) !!}
                                    </h6>

                                    <p class="mb-1 list">
                                        @if(!empty($new->published_at))
                                        {{ \Carbon\Carbon::parse($new->published_at)->format('d/m/Y') }}
                                        @endif
                                    </p>
                                </div>

                                <!--TAGS fixas na parte inferior-->
                                @if(isset($new['contents']['categorias']) && is_array($new['contents']['categorias']))
                                <div class="tags mt-auto">
                                    @foreach($new['contents']['categorias'] as $categoria)
                                    <span class="badge-category">{{ $categoria['name'] }}</span>
                                    @endforeach
                                </div>
                                @endif

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
 