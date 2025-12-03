      <div class="row g-3">
          @foreach ($news as $new)
          <div class="col-12">
              <div class="news-card-mobile">
                  <a href="{{ route('frontend.news-details', $new->slug) }}" class="news-card-link-mobile">
                      @if($new['contents']['image'])
                      <div class="news-image-wrapper-mobile">
                          <img src="{{ asset('storage/' .$new['contents']['image']) }}"
                              class="news-image-mobile"
                              alt="Imagem da notícia: {{ $new->title }}" loading="lazy">
                      </div>
                      @endif

                      <div class="news-content-mobile">
                          <h6 class="news-title-mobile">{{ $new['contents']['title'] }}</h6>

                          @if(!empty($new['contents']['date']))
                          <div class="news-date-mobile">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                  <circle cx="12" cy="12" r="10"></circle>
                                  <polyline points="12 6 12 12 16 14"></polyline>
                              </svg>
                              {{ \Carbon\Carbon::parse($new['contents']['date'])->format('d.m.Y') }}
                          </div>
                          @endif

                          @if(isset($new['contents']['categories']) && is_array(json_decode($new['contents']['categories'])))
                          <div class="news-tags-mobile">
                              @foreach(json_decode($new['contents']['categories']) as $category)
                              @php $categoryName = App\Models\Page::find($category); @endphp
                              <span class="news-tag-mobile">{{ $categoryName['contents'][0]['field_value'] }}</span>
                              @endforeach
                          </div>
                          @endif
                      </div>
                  </a>
              </div>
          </div>
          @endforeach
      </div>

