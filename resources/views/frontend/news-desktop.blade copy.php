
<div class="row g-4">
    @foreach ($news as $new)
    <div class="col-12">
        <div class="news-card-modern">
            <a href="{{ route('frontend.news-details', $new->slug) }}" class="news-card-link">
                <div class="news-card-content">
                    @if(isset($new['contents']['image']) && !empty($new['contents']['image']))
                    <div class="news-image-wrapper">
                        <img src="{{ asset('storage/' . $new['contents']['image']) }}"
                            class="news-image"
                            onerror="this.src='{{ asset('img/logo-simples.png') }}';"
                            alt="Imagem da notícia: {{ $new->title }}" loading="lazy">
                        <div class="news-image-overlay"></div>
                    </div>
                    @endif

                    <div class="news-info">
                        <div class="news-meta">
                            @if(!empty($new->published_at))
                            <span class="news-date">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                {{ \Carbon\Carbon::parse($new->published_at)->format('d/m/Y') }}
                            </span>
                            @endif
                        </div>
                        
                        <h3 class="news-title">{{ $new['contents']['title'] }}</h3>
                        
                        <p class="news-excerpt">
                            {!! \Illuminate\Support\Str::limit(strip_tags($new['contents']['subtitle']), 200) !!}
                        </p>

                        @if(isset($new['contents']['categories']) && is_array(json_decode($new['contents']['categories'])))
                        <div class="news-tags">
                            @foreach(json_decode($new['contents']['categories']) as $category)
                            @php $categoryName = App\Models\Page::find($category); @endphp
                            <span class="news-tag">{{ $categoryName['contents'][0]['field_value'] }}</span>
                            @endforeach
                        </div>
                        @endif

                        <div class="news-read-more">
                            <span>Ler mais</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    @endforeach
</div>

   