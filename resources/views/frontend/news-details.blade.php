@extends('frontend.partials.layout')
@include('frontend.partials.seo', [
'seo' => $news['seo'] ?? null,
])

@section('content')
<section class="py-5">

    <div class="container mt-2">
        <div class="container">
            <a href="{{ route('frontend.news') }}" class="btn btn-outline-dark btn-back-news d-inline-flex align-items-center mb-0 mt-4" style="gap:8px; border-radius: 50px; font-weight: 600;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Voltar à listagem de notícias
            </a>
        </div>
        <div class="row g-5">
            <div class="col-lg-8 h-100">
                {{-- Notícia detalhada --}}
                <article class="news-detail-card">
                    <div class="news-detail-header">
                        @if(!empty($news['contents']['date']))
                        <div class="news-detail-meta">
                            <span class="news-detail-date">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                {{ \Carbon\Carbon::parse($news['contents']['date'])->format('d/m/Y') }}
                            </span>
                        </div>
                        @endif

                        <h1 class="news-detail-title">{{$news['contents']['title']}}</h1>

                        @if(isset($news['contents']['categories']) && is_array(json_decode($news['contents']['categories'])))
                        <div class="news-detail-categories">
                            @foreach(json_decode($news['contents']['categories']) as $category)
                            @php $categoryName = App\Models\Page::find($category); @endphp
                            <span class="news-detail-category">{{ $categoryName['contents'][0]['field_value'] }}</span>
                            @endforeach
                        </div>
                        @endif

                        <div class="news-detail-subtitle">
                            {!!$news['contents']['subtitle']!!}
                        </div>
                    </div>

                    @if($news['contents']['image'])
                    <div class="news-detail-image-wrapper">
                        <img src="{{  asset('storage/' . $news['contents']['image'])  }}" loading="lazy"
                            class="news-detail-image"
                            onerror="this.src='{{ asset('img/logo-simples.png') }}';"
                            alt="Imagem da notícia: {{ $news['contents']['title'] }}">
                    </div>
                    @endif

                    <div class="news-detail-content">
                        {!! $news['contents']['content'] !!}
                    </div>

                    @if(!empty($news['contents']['gallery']))
                    <div class="news-detail-gallery">
                        <div id="newsGalleryCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                @foreach(json_decode($news['contents']['gallery']) as $index => $image)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <img src="{{ asset('storage/' . $image) }}" loading="lazy"
                                        class="gallery-image"
                                        onerror="this.src='{{ asset('img/logo-simples.png') }}';"
                                        alt="Imagem da notícia: {{ $news['contents']['title'] }}">
                                </div>
                                @endforeach
                            </div>

                            <button class="carousel-control-prev" type="button" data-bs-target="#newsGalleryCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#newsGalleryCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Próximo</span>
                            </button>

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
                    </div>
                    @endif

                    @if(!empty($news['contents']['summary']))
                    <div class="news-detail-summary">
                        {!! $news['contents']['summary'] !!}
                    </div>
                    @endif
                </article>



            </div>

            <div class="col-lg-4">
                <div class="sidebar-sticky">
                    {{-- Os nossos serviços --}}
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                <path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path>
                            </svg>
                            <h5>Serviços</h5>
                        </div>
                        <div class="sidebar-list">
                            <a href="{{ route('frontend.import') }}" class="sidebar-item">
                                <div class="sidebar-item-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    </svg>
                                </div>
                                <span>Importação</span>
                                <svg class="sidebar-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>

                            <a href="{{ route('frontend.legalization') }}" class="sidebar-item">
                                <div class="sidebar-item-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                    </svg>
                                </div>
                                <span>Legalização</span>
                                <svg class="sidebar-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                        </div>
                    </div>

                    {{-- Notícias Recentes --}}
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 3h18v18H3zM8 3v18M16 8h-5M16 12h-5M16 16h-5"></path>
                            </svg>
                            <h5>Notícias Recentes</h5>
                        </div>
                        <div class="sidebar-list">
                            @foreach($recentNews as $recent)
                            <a href="{{ route('frontend.news-details', $recent->slug) }}" class="sidebar-news-item">
                                <div class="sidebar-news-content">
                                    <p class="sidebar-news-title">{{ $recent['contents']['title'] }}</p>
                                    @if(!empty($recent['contents']['date']))
                                    <span class="sidebar-news-date">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($recent['contents']['date'])->format('d.m.Y') }}
                                    </span>
                                    @endif
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('styles')
<style>
    /* News Detail Card */
    .news-detail-card {
        margin-top: 2rem;
        margin-bottom: 2rem;
        background: white;
        border-radius: 24px;
        padding: 3rem;
        box-shadow: 10px 10px 40px rgba(0, 0, 0, 0.08);
    }

    .news-detail-header {
        margin-bottom: 2.5rem;
    }

    .news-detail-meta {
        margin-bottom: 1.5rem;
    }

    .news-detail-date {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #6c757d;
        font-size: 0.95rem;
        font-weight: 500;
        padding: 8px 16px;
        background: #f8f9fa;
        border-radius: 50px;
    }

    .news-detail-date svg {
        color: var(--accent-color);
    }

    .news-detail-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #111;
        line-height: 1.2;
        margin-bottom: 1.5rem;
    }

    .news-detail-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .news-detail-category {
        display: inline-block;
        padding: 8px 18px;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        color: white;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .news-detail-subtitle {
        font-size: 1.2rem;
        color: #495057;
        line-height: 1.6;
        padding: 1.5rem;
        background: #f8f9fa;
        border-left: 4px solid var(--accent-color);
        border-radius: 8px;
    }

    .news-detail-image-wrapper {
        margin: 2.5rem 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .news-detail-image {
        width: 100%;
        height: auto;
        display: block;
    }

    .news-detail-content {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #333;
    }

    .news-detail-content h2,
    .news-detail-content h3,
    .news-detail-content h4 {
        color: #111;
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .news-detail-content p {
        margin-bottom: 1.5rem;
    }

    .news-detail-content ul,
    .news-detail-content ol {
        margin-bottom: 1.5rem;
        padding-left: 2rem;
    }

    .news-detail-content li {
        margin-bottom: 0.5rem;
    }

    .news-detail-gallery {
        margin: 2.5rem 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .gallery-image {
        width: 100%;
        height: auto;
        max-height: 500px;
        object-fit: cover;
    }

    .news-detail-summary {
        margin-top: 2.5rem;
        padding: 2rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 16px;
        font-size: 1.05rem;
        line-height: 1.7;
        color: #495057;
    }

    /* Sidebar */
    .sidebar-sticky {
        position: sticky;
        top: 120px;
    }

    .sidebar-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    }

    .sidebar-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f3f5;
    }

    .sidebar-card-header svg {
        color: var(--accent-color);
    }

    .sidebar-card-header h5 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: #111;
    }

    .sidebar-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .sidebar-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 12px;
        text-decoration: none;
        color: #111;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .sidebar-item:hover {
        background: var(--accent-color);
        color: white;
        transform: translateX(5px);
        border-color: var(--accent-color);
    }

    .sidebar-item-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidebar-item:hover .sidebar-item-icon svg,
    .sidebar-item:hover .sidebar-arrow {
        color: white;
    }

    .sidebar-arrow {
        margin-left: auto;
        transition: transform 0.3s ease;
    }

    .sidebar-item:hover .sidebar-arrow {
        transform: translateX(3px);
    }

    .sidebar-news-item {
        display: block;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .sidebar-news-item:hover {
        background: white;
        border-color: var(--accent-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .sidebar-news-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #111;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .sidebar-news-date {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #6c757d;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .sidebar-news-date svg {
        color: var(--accent-color);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .news-detail-card {
            padding: 2rem;
        }

        .news-detail-title {
            font-size: 2rem;
        }

        .sidebar-sticky {
            position: relative;
            top: 0;
        }
    }

    @media (max-width: 768px) {
        .news-detail-card {
            padding: 1.5rem;
        }

        .news-detail-title {
            font-size: 1.75rem;
        }

        .news-detail-content {
            font-size: 1rem;
        }

        .sidebar-card {
            padding: 1.5rem;
        }
    }
</style>