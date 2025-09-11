      <div class="sticky-top mx-4" style="top: 110px; z-index: 1;">
          <div class="row">
              @foreach ($news as $new)
              <div class="col-12">
                  <div class="custom-block-transparent news-listing shadow-lg mb-5 mt-3">
                      <a href="{{ route('frontend.news-details', $new->slug) }}" class="d-block text-decoration-none p-3">
                          <div class="row align-items-center">

                              {{-- Texto (à esquerda no mobile, à direita no desktop) --}}
                              <div class="col-md-12">
                                  <h6 class="text-accent">{{ $new['contents']['title'] }}</h6>


                                  @if($new['contents']['image'])
                                  <img src="{{  asset('storage/' .$new['contents']['image']) }}"
                                      class="d-block w-100  object-cover rounded" style="height: auto; "
                                      alt="Imagem da notícia: {{ $new->title }}">
                                  @endif
                                  <p class="mb-1 list">
                                      @if(!empty($new['contents']['date']))
                                      <small class="text-muted " style="font-size: 0.8rem !important;">
                                          {{ \Carbon\Carbon::parse($new['contents']['date'])->format('d.m.Y') }}
                                      </small>
                                      @endif
                                  </p>

                                  {{-- Tags --}}
                                  @if(isset($new['contents']['categories']) && is_array(json_decode($new['contents']['categories'])))
                                  <div class="tags mt-2">
                                      @foreach(json_decode($new['contents']['categories']) as $category)
                                      @php $categoryName = App\Models\Page::find($category); @endphp
                                      <span class="badge-category">{{ $categoryName['contents'][0]['field_value'] }}</span>
                                      @endforeach
                                  </div>
                                  @endif
                              </div>

                              {{-- Imagem (à direita no mobile e à esquerda no desktop) --}}


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

      <style>