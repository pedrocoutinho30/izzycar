
        <div class="row ">
            @foreach ($news as $new)
            <div class="col-md-12">
                <div class="custom-block-topics-listing news-listing shadow-lg mb-5">
                    <a href="{{ route('frontend.news-details', $new->slug) }}" class="d-block text-decoration-none p-3">
                        <div class="d-flex">
                            {{-- Imagem da notícia --}}

                            <div class="image-wrapper">
                                @if(isset($new['contents']['image']) && !empty($new['contents']['image']) )
                                <img src="{{ asset('storage/' . $new['contents']['image']) }}"
                                    class="custom-block-image img-fluid"
                                    alt="Imagem da notícia: {{ $new->title }}" loading="lazy">
                                @endif
                            </div>

                            {{-- Info lateral --}}
                            <div class="custom-block-topics-listing-info d-flex flex-column ms-3" style="flex:1;">
                                <div>
                                    <h3 class="text-accent">{{ $new['contents']['title'] }}</h3>
                                    <h6 class="mb-2 text-muted">
                                        {!! \Illuminate\Support\Str::limit($new['contents']['subtitle'], 200) !!}
                                    </h6>

                                    <p class="mb-1 list">
                                        @if(!empty($new->published_at))
                                        {{ \Carbon\Carbon::parse($new->published_at)->format('d/m/Y') }}
                                        @endif
                                    </p>
                                </div>

                                <!--TAGS fixas na parte inferior-->
                                @if(isset($new['contents']['categories']) && is_array(json_decode($new['contents']['categories'])))
                                  <div class="tags mt-2">
                                      @foreach(json_decode($new['contents']['categories']) as $category)
                                      @php $categoryName = App\Models\Page::find($category); @endphp
                                      <span class="badge-category">{{ $categoryName['contents'][0]['field_value'] }}</span>
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
 