@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => $page->seo
])

@section('content')

<!-- Hero Section -->
<section class="hero-homepage">
    <picture class="hero-bg">
        <source srcset="{{ asset('img/hero.webp') }}" type="image/webp">
        <img src="{{ asset('img/2.jpg') }}" alt="Importação automóvel Izzycar" loading="eager" fetchpriority="high">
    </picture>
    <div class="hero-bg-gradient"></div>
    <div class="container">
        <div class="row min-vh-95 py-5 align-items-center">
            <div class="col-lg-8">
                <div class="hero-content">
                    <span class="hero-badge fade-in-up">
                        <span class="material-symbols-outlined">workspace_premium</span>
                        Importação Automóvel Chave na Mão
                    </span>
                    <h1 class="hero-title fade-in-up" data-delay="100">
                        O Seu Carro dos Sonhos,<br>
                        <span class="text-gradient">Ao Melhor Preço</span>
                    </h1>
                    <p class="hero-description fade-in-up" data-delay="200">
                        Especializados em importação de veículos de toda a Europa, oferecemos um serviço completo e transparente. Desde a procura até à entrega, cuidamos de cada detalhe para que o seu carro chegue pronto a conduzir.
                    </p>
                    <div class="hero-actions fade-in-up" data-delay="300">
                        <a href="{{ route('frontend.form-import') }}" class="btn-hero-primary">
                            <span>Quero Importar</span>
                        </a>
                        <a href="{{ route('frontend.cost-simulator') }}" class="btn-hero-secondary">
                            <span class="material-symbols-outlined">calculate</span>
                            <span>Simular Custos</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="scroll-indicator">
        <span class="material-symbols-outlined">expand_more</span>
    </div>
</section>

<!-- O Que Fazemos — cards no estilo bento (imagem de fundo), como no site atual -->
<section class="services-section section-padding">
    <div class="container">
        <div class="section-header text-center mb-4">
            <span class="section-badge fade-in-up">O Que Fazemos</span>
            <h2 class="section-title fade-in-up" data-delay="100">Tudo o que precisa, num só sítio</h2>
        </div>

        <div class="road-divider fade-in-up" data-delay="150" aria-hidden="true">
            <span class="road-divider-line"></span>
            <svg class="road-divider-car" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11"/><rect x="3" y="11" width="18" height="6" rx="2"/><circle cx="7.5" cy="17" r="1.5"/><circle cx="16.5" cy="17" r="1.5"/>
            </svg>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('frontend.import') }}" class="service-bento fade-in-up">
                    <picture>
                        <source srcset="{{ asset('img/2.webp') }}" type="image/webp">
                        <img src="{{ asset('img/2.jpg') }}" alt="Importação Chave na Mão" loading="lazy">
                    </picture>
                    <div class="service-bento-overlay"></div>
                    <div class="service-bento-content">
                        <h3 class="service-bento-title">Importação Chave na Mão</h3>
                        <p class="service-bento-description">Tratamos de todo o processo: procura, compra, transporte, legalização e entrega. Recebe o seu carro pronto a conduzir.</p>
                        <span class="service-bento-link">
                            Saber mais
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </span>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6">
                <a href="{{ route('frontend.legalization') }}" class="service-bento fade-in-up" data-delay="100">
                    <picture>
                        <source srcset="{{ asset('img/1.webp') }}" type="image/webp">
                        <img src="{{ asset('img/1.jpg') }}" alt="Legalização de Veículos" loading="lazy">
                    </picture>
                    <div class="service-bento-overlay"></div>
                    <div class="service-bento-content">
                        <h3 class="service-bento-title">Legalização de Veículos</h3>
                        <p class="service-bento-description">Já tem o carro? Tratamos da inspeção, matrícula e toda a documentação necessária para legalizar o seu veículo em Portugal.</p>
                        <span class="service-bento-link">
                            Saber mais
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </span>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6">
                <a href="{{ route('vehicles.list') }}" class="service-bento fade-in-up" data-delay="200">
                    <picture>
                        <source srcset="{{ asset('img/3.webp') }}" type="image/webp">
                        <img src="{{ asset('img/3.jpg') }}" alt="Veículos Usados" loading="lazy">
                    </picture>
                    <div class="service-bento-overlay"></div>
                    <div class="service-bento-content">
                        <h3 class="service-bento-title">Veículos Usados</h3>
                        <p class="service-bento-description">Carros já preparados e prontos a conduzir, disponíveis para entrega imediata.</p>
                        <span class="service-bento-link">
                            Ver Viaturas
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Porquê Escolher-nos — 3 cards pequenos -->
<section class="why-section-simple section-padding">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge fade-in-up">Porquê Escolher-nos</span>
            <h2 class="section-title fade-in-up" data-delay="100">Experiência e Confiança ao Seu Serviço</h2>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="why-mini-card fade-in-up">
                    <div class="why-mini-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h4>Processo Transparente</h4>
                    <p>Acompanhamento em tempo real de todas as etapas da importação.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="why-mini-card fade-in-up" data-delay="100">
                    <div class="why-mini-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <h4>Inspeção Rigorosa</h4>
                    <p>Todos os veículos são inspecionados antes da compra.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="why-mini-card fade-in-up" data-delay="200">
                    <div class="why-mini-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h4>Apoio Personalizado</h4>
                    <p>Equipa dedicada disponível para esclarecer todas as suas dúvidas.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section-padding testimonials-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge">Clientes Satisfeitos</span>
            <h2 class="section-title fade-in-up">O que dizem os nossos clientes</h2>
            <p class="section-description fade-in-up" data-delay="100">Opiniões reais de quem já trabalhou connosco</p>
            <div class="google-rating-summary fade-in-up" data-delay="150">
                <svg class="google-logo-summary" viewBox="0 0 24 24" width="24" height="24" aria-label="Google">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <div class="google-stars-summary">
                    @php
                        $mediaFloor = floor($media);
                        $mediaHalf  = ($media - $mediaFloor) >= 0.25;
                    @endphp
                    @for($s = 1; $s <= 5; $s++)
                        @if($s <= $mediaFloor)
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#FBBC04"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        @elseif($s == $mediaFloor + 1 && $mediaHalf)
                            <svg width="20" height="20" viewBox="0 0 24 24"><defs><linearGradient id="hsg-media-{{ $s }}"><stop offset="50%" stop-color="#FBBC04"/><stop offset="50%" stop-color="#e0e0e0"/></linearGradient></defs><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="url(#hsg-media-{{ $s }})"/></svg>
                        @else
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#e0e0e0"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        @endif
                    @endfor
                </div>
                <span class="google-rating-text">{{ $media }} &nbsp;·&nbsp; Google Reviews</span>
            </div>
            @if(config('services.google.review_url'))
            <div class="fade-in-up mt-3" data-delay="200">
                <a href="{{ config('services.google.review_url') }}" target="_blank" rel="noopener" class="btn-google-review">
                    <svg width="16" height="16" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9 3.2l6.7-6.7C35.7 2.4 30.2 0 24 0 14.6 0 6.6 5.4 2.7 13.3l7.8 6C12.4 13 17.8 9.5 24 9.5z"/><path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.4-4.1 6.9-10.1 7.1-17z"/><path fill="#FBBC05" d="M10.5 28.7A14.5 14.5 0 0 1 9.5 24c0-1.6.3-3.2.8-4.7l-7.8-6A24 24 0 0 0 0 24c0 3.9.9 7.5 2.5 10.8l8-6.1z"/><path fill="#34A853" d="M24 48c6.2 0 11.4-2 15.2-5.5l-7.5-5.8c-2 1.4-4.7 2.3-7.7 2.3-6.2 0-11.5-4.2-13.4-9.9l-8 6.2C6.5 42.6 14.6 48 24 48z"/><path fill="none" d="M0 0h48v48H0z"/></svg>
                    Deixar a sua opinião no Google
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                </a>
            </div>
            @endif
        </div>
@php
    $avatarColors = ['#990000','#1a73e8','#34A853','#F9AB00','#7B1FA2','#0F9D58','#E64A19','#00838F'];
    $delays = [100, 200, 300, 200, 300, 400];
@endphp
        <div class="row g-4" id="reviewsGrid">
            @forelse($reviews as $index => $review)
            @if($review->comment !== '' && $review->comment !== null)
            @php
                $initial = mb_strtoupper(mb_substr($review->name, 0, 1));
                $color   = $avatarColors[$index % count($avatarColors)];
                $delay   = $delays[$index % count($delays)];
            @endphp
            <div class="col-lg-4 col-md-6 fade-in-up review-col" data-delay="{{ $delay }}" data-index="{{ $index }}">
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-avatar" style="background: {{ $color }};">{{ $initial }}</div>
                        <div class="reviewer-info">
                            <span class="reviewer-name">{{ $review->name }}</span>
                            <span class="reviewer-date">{{ ($review->review_date ?? $review->created_at)->diffForHumans() }}</span>
                        </div>
                        <div class="google-icon-wrap">
                            @if($review->origin === 'google')
                            <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9 3.2l6.7-6.7C35.7 2.4 30.2 0 24 0 14.6 0 6.6 5.4 2.7 13.3l7.8 6C12.4 13 17.8 9.5 24 9.5z"/><path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.4-4.1 6.9-10.1 7.1-17z"/><path fill="#FBBC05" d="M10.5 28.7A14.5 14.5 0 0 1 9.5 24c0-1.6.3-3.2.8-4.7l-7.8-6A24 24 0 0 0 0 24c0 3.9.9 7.5 2.5 10.8l8-6.1z"/><path fill="#34A853" d="M24 48c6.2 0 11.4-2 15.2-5.5l-7.5-5.8c-2 1.4-4.7 2.3-7.7 2.3-6.2 0-11.5-4.2-13.4-9.9l-8 6.2C6.5 42.6 14.6 48 24 48z"/><path fill="none" d="M0 0h48v48H0z"/></svg>
                            @endif
                        </div>
                    </div>
                    <div class="review-stars">
                        @php
                            $ratingFloor = floor($review->rating);
                            $hasHalf = ($review->rating - $ratingFloor) >= 0.25;
                        @endphp
                        @for($s = 1; $s <= 5; $s++)
                            @if($s <= $ratingFloor)
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="#FBBC04"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            @elseif($s == $ratingFloor + 1 && $hasHalf)
                                <svg width="16" height="16" viewBox="0 0 24 24"><defs><linearGradient id="hsg-{{ $index }}-{{ $s }}"><stop offset="50%" stop-color="#FBBC04"/><stop offset="50%" stop-color="#e0e0e0"/></linearGradient></defs><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="url(#hsg-{{ $index }}-{{ $s }})"/></svg>
                            @else
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="#e0e0e0"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            @endif
                        @endfor
                    </div>
                    <p class="review-text">"{{ $review->comment }}"</p>
                </div>
            </div>
            @endif
            @empty
            <div class="col-12 text-center text-muted py-4">Ainda não há testemunhos disponíveis.</div>
            @endforelse
        </div>

        <div class="text-center mt-4" id="reviewsLoadMore" style="display:none">
            <button type="button" class="btn-load-more-reviews" id="btnLoadMoreReviews">
                Ver mais opiniões
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
        </div>
    </div>
</section>

<script>
(function () {
    var cols   = Array.from(document.querySelectorAll('.review-col'));
    var btn    = document.getElementById('btnLoadMoreReviews');
    var wrap   = document.getElementById('reviewsLoadMore');
    var shown  = 0;

    function perPage() {
        return window.innerWidth >= 992 ? 3 : 2;
    }

    function showNext() {
        var n = perPage();
        var end = Math.min(shown + n, cols.length);
        for (var i = shown; i < end; i++) {
            cols[i].style.display = '';
        }
        shown = end;
        if (shown >= cols.length) wrap.style.display = 'none';
    }

    function init() {
        var n = perPage();
        cols.forEach(function (c) { c.style.display = 'none'; });
        shown = 0;
        showNext();
        wrap.style.display = cols.length > n ? '' : 'none';
    }

    btn.addEventListener('click', showNext);

    init();
    window.addEventListener('resize', init);
})();
</script>

<!-- Partners Section -->
@if($partners->isNotEmpty())
<section class="partners-section">
    <div class="container">
        <div class="text-center mb-4">
            <span class="section-badge">Parceiros de Confiança</span>
        </div>
        <div class="partners-strip">
            @foreach($partners as $partner)
                @if($partner->url)
                <a href="{{ $partner->url }}" target="_blank" rel="noopener noreferrer"
                   class="partner-logo" title="{{ $partner->name }}">
                    <img src="{{ asset('storage/' . $partner->image) }}" alt="{{ $partner->name }}">
                </a>
                @else
                <div class="partner-logo" title="{{ $partner->name }}">
                    <img src="{{ asset('storage/' . $partner->image) }}" alt="{{ $partner->name }}">
                </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- CTA Final Section -->
<section class="cta-final-section">
    <div class="cta-overlay"></div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="cta-title fade-in-up">Comece a Importar Hoje</h2>
                <p class="cta-description fade-in-up" data-delay="100">
                    Peça uma cotação sem compromisso e descubra quanto pode economizar ao importar o seu próximo carro connosco.
                </p>
                <div class="cta-buttons fade-in-up" data-delay="200">
                    <a href="{{ route('frontend.form-import') }}" class="btn-cta-primary">
                        <span>Pedir Cotação</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                    <a href="{{ route('frontend.cost-simulator') }}" class="btn-cta-outline">
                        <span>€</span>
                        <span>Simular Custos</span>
                    </a>
                </div>
                <div class="cta-contact fade-in-up" data-delay="300">
                    <p>Ou contacte-nos diretamente:</p>
                    <a href="tel:+351912345678" class="cta-phone">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        +351 928 459 346
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    /* ── Partners ── */
    .partners-section {
        padding: 3rem 0;
        border-top: 1px solid #f0f0f0;
        border-bottom: 1px solid #f0f0f0;
        background: #fff;
    }
    .partners-strip {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 3rem;
        flex-wrap: wrap;
    }
    .partner-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        opacity: .55;
        filter: grayscale(100%);
        transition: opacity .25s, filter .25s;
    }
    .partner-logo:hover {
        opacity: 1;
        filter: grayscale(0%);
    }
    .partner-logo img {
        height: 40px;
        width: auto;
        max-width: 140px;
        object-fit: contain;
    }

    .min-vh-95 {
        min-height: 95vh;
    }

    /* Hero Section */
    .hero-homepage {
        position: relative;
        overflow: hidden;
        background: #111111;
    }

    .hero-bg {
        position: absolute;
        inset: 0;
        z-index: 0;
    }

    .hero-bg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.45;
    }

    .hero-bg-gradient {
        position: absolute;
        inset: 0;
        z-index: 1;
        background: linear-gradient(180deg, rgba(17,17,17,0.55) 0%, rgba(17,17,17,0.75) 50%, #111111 100%),
                    linear-gradient(90deg, #111111 0%, rgba(17,17,17,0.4) 55%, transparent 100%);
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: var(--radius-sharp-sm);
        color: #fff;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 2rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .hero-badge .material-symbols-outlined {
        font-size: 18px;
        color: #cf1c1c;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 900;
        color: #fff;
        line-height: 1.2;
        margin-bottom: 1.5rem;
    }

    .text-gradient {
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-description {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.8);
        line-height: 1.8;
        margin-bottom: 2.5rem;
        max-width: 600px;
    }

    .hero-actions {
        display: flex;
        gap: 1rem;
        margin-bottom: 3rem;
        flex-wrap: wrap;
    }

    .btn-hero-primary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 18px 40px;
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        color: white;
        border-radius: var(--radius-sharp-sm);
        font-size: 1.1rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(153, 0, 0, 0.4);
    }

    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(153, 0, 0, 0.5);
        color: white;
    }

    .btn-hero-secondary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 18px 40px;
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--radius-sharp-sm);
        font-size: 1.1rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-hero-secondary .material-symbols-outlined {
        font-size: 20px;
    }

    .btn-hero-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: white;
        color: white;
    }

    .scroll-indicator {
        position: absolute;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        animation: bounce 2s infinite;
        color: rgba(255, 255, 255, 0.6);
    }

    .scroll-indicator .material-symbols-outlined {
        font-size: 28px;
    }

    @keyframes bounce {
        0%, 100% { transform: translateX(-50%) translateY(0); }
        50%      { transform: translateX(-50%) translateY(-10px); }
    }

    /* Section Headers (partilhados) */
    .section-padding {
        padding: 5rem 0;
    }

    .section-badge {
        display: inline-block;
        padding: 8px 20px;
        background: rgba(110, 7, 7, 0.1);
        color: #990000;
        border-radius: var(--radius-sharp-sm);
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 1rem;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #111;
        margin-bottom: 1rem;
    }

    .section-description {
        font-size: 1.1rem;
        color: #6c757d;
        max-width: 700px;
        margin: 0 auto;
    }

    /* ── O Que Fazemos ── */
    .services-section {
        background: linear-gradient(135deg, #111111 0%, #161616 50%, #111111 100%);
    }

    .services-section .section-title {
        color: #fff;
    }

    .services-section .section-badge {
        background: rgba(153,0,0,0.2);
        color: #ff8080;
    }

    .service-bento {
        position: relative;
        display: block;
        min-height: 400px;
        height: 100%;
        border-radius: 6px;
        overflow: hidden;
        text-decoration: none;
        background: #1a1a1a;
    }

    .service-bento picture,
    .service-bento img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.55;
        transition: opacity 0.4s ease, transform 0.5s ease;
    }

    .service-bento:hover img {
        opacity: 0.7;
        transform: scale(1.05);
    }

    .service-bento-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(17,17,17,0.1) 0%, rgba(17,17,17,0.55) 55%, rgba(17,17,17,0.92) 100%);
        transition: background 0.4s ease;
    }

    .service-bento:hover .service-bento-overlay {
        background: linear-gradient(180deg, rgba(17,17,17,0.15) 0%, rgba(17,17,17,0.6) 55%, rgba(17,17,17,0.95) 100%);
    }

    .service-bento-content {
        position: relative;
        z-index: 1;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 2.5rem;
    }

    .service-bento-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 0.75rem;
    }

    .service-bento-description {
        font-size: 1rem;
        color: rgba(255,255,255,0.8);
        line-height: 1.7;
        margin-bottom: 1.25rem;
        max-width: 420px;
    }

    .service-bento-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #ff8080;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.06em;
        text-decoration: none;
        transition: gap 0.3s ease, color 0.2s ease;
    }

    .service-bento-link .material-symbols-outlined {
        font-size: 18px;
    }

    .service-bento:hover .service-bento-link {
        gap: 12px;
        color: #ff6060;
    }

    .road-divider {
        position: relative;
        display: flex;
        align-items: center;
        max-width: 640px;
        margin: 0 auto 3rem;
        height: 26px;
    }
    .road-divider-line {
        position: absolute;
        left: 0; right: 0; top: 50%;
        height: 2px;
        transform: translateY(-50%);
        background-image: repeating-linear-gradient(90deg, rgba(255,128,128,0.4) 0 16px, transparent 16px 28px);
        background-size: 200% 100%;
        animation: roadMove 9s linear infinite;
    }
    .road-divider-car {
        position: relative;
        z-index: 1;
        color: #ff8080;
        background: #161616;
        padding: 0 6px;
        animation: roadDrive 3.5s ease-in-out infinite;
    }
    @keyframes roadMove {
        from { background-position: 0 0; }
        to   { background-position: -200% 0; }
    }
    @keyframes roadDrive {
        0%, 100% { transform: translateX(-6px); }
        50%      { transform: translateX(6px); }
    }
    @media (prefers-reduced-motion: reduce) {
        .road-divider-line, .road-divider-car { animation: none; }
    }

    /* ── Porquê Escolher-nos — 3 cards pequenos ── */
    .why-section-simple {
        background: linear-gradient(160deg, #ffffff 0%, #fafafa 55%, #fff7f7 100%);
    }

    .why-mini-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: var(--radius-sharp-md);
        padding: 2rem 1.75rem;
        height: 100%;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
    }

    .why-mini-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 32px rgba(0,0,0,0.1);
    }

    .why-mini-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 1.25rem;
        border-radius: var(--radius-sharp-sm);
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(110,7,7,0.35);
        transition: transform 0.3s ease;
    }

    .why-mini-card:hover .why-mini-icon {
        transform: scale(1.08);
    }

    .why-mini-card h4 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 0.5rem;
    }

    .why-mini-card p {
        font-size: 0.92rem;
        color: #6c757d;
        line-height: 1.6;
        margin: 0;
    }

    /* Testimonials Section */
    .testimonials-section {
        background: #f8f9fa;
    }

    .google-rating-summary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: var(--radius-sharp-sm);
        padding: 10px 24px;
        margin-top: 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .google-logo-summary {
        height: 20px;
        width: auto;
    }

    .google-stars-summary {
        display: flex;
        gap: 2px;
    }

    .google-rating-text {
        font-size: 0.95rem;
        font-weight: 600;
        color: #555;
    }

    .review-card {
        background: white;
        border-radius: var(--radius-sharp-md);
        padding: 1.75rem;
        border: 1px solid #e8e8e8;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        touch-action: pan-y;
    }

    .review-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        border-color: #dadada;
    }

    .btn-load-more-reviews {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .65rem 1.75rem;
        border-radius: var(--radius-sharp-sm);
        border: 2px solid var(--accent-color);
        background: transparent;
        color: var(--accent-color);
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
    }
    .btn-load-more-reviews:hover {
        background: var(--accent-color);
        color: #fff;
    }

    .review-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 0.75rem;
    }

    .reviewer-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
    }

    .reviewer-info {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .reviewer-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #111;
        line-height: 1.3;
    }

    .reviewer-date {
        font-size: 0.8rem;
        color: #9e9e9e;
    }

    .google-icon-wrap {
        flex-shrink: 0;
    }

    .review-stars {
        display: flex;
        gap: 2px;
        margin-bottom: 0.85rem;
    }

    .review-text {
        font-size: 0.95rem;
        color: #444;
        line-height: 1.7;
        margin: 0;
        flex: 1;
    }

    .btn-google-review {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 22px;
        background: white;
        color: #444;
        border: 1.5px solid #dadada;
        border-radius: var(--radius-sharp-sm);
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.25s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .btn-google-review:hover {
        border-color: #4285F4;
        color: #4285F4;
        box-shadow: 0 4px 16px rgba(66,133,244,0.15);
        transform: translateY(-2px);
    }

    /* Animations */
    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards;
    }

    .fade-in-up[data-delay="80"]  { animation-delay: 0.08s; }
    .fade-in-up[data-delay="100"] { animation-delay: 0.1s; }
    .fade-in-up[data-delay="150"] { animation-delay: 0.15s; }
    .fade-in-up[data-delay="160"] { animation-delay: 0.16s; }
    .fade-in-up[data-delay="200"] { animation-delay: 0.2s; }
    .fade-in-up[data-delay="240"] { animation-delay: 0.24s; }
    .fade-in-up[data-delay="300"] { animation-delay: 0.3s; }
    .fade-in-up[data-delay="400"] { animation-delay: 0.4s; }
    .fade-in-up[data-delay="500"] { animation-delay: 0.5s; }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 992px) {
        .hero-title {
            font-size: 2.5rem;
        }

        .section-title {
            font-size: 2rem;
        }
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem;
        }

        .hero-description {
            font-size: 1rem;
        }

        .hero-actions {
            flex-direction: column;
        }

        .btn-hero-primary,
        .btn-hero-secondary {
            width: 100%;
            justify-content: center;
        }

        .section-padding {
            padding: 3rem 0;
        }

        .section-title {
            font-size: 1.75rem;
        }

        .cta-title {
            font-size: 2rem;
        }

        .cta-buttons {
            flex-direction: column;
        }

        .btn-cta-primary,
        .btn-cta-outline {
            width: 100%;
            justify-content: center;
        }
    }

    /* CTA Final Section */
    .cta-final-section {
        padding: 6rem 0;
        background: linear-gradient(135deg, #111111 0%, #2a2a2a 100%);
        position: relative;
        overflow: hidden;
    }

    .cta-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .cta-title {
        font-size: 3rem;
        font-weight: 800;
        color: white;
        margin-bottom: 1.5rem;
    }

    .cta-description {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 2.5rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .cta-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .btn-cta-primary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 18px 40px;
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        color: white;
        border-radius: var(--radius-sharp-sm);
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(153, 0, 0, 0.4);
    }

    .btn-cta-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(153, 0, 0, 0.5);
        color: white;
    }

    .btn-cta-outline {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 18px 40px;
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--radius-sharp-sm);
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-cta-outline:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: white;
        color: white;
    }

    .cta-contact {
        margin-top: 2rem;
    }

    .cta-contact p {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.95rem;
        margin-bottom: 0.75rem;
    }

    .cta-phone {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: white;
        font-size: 1.3rem;
        font-weight: 700;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .cta-phone:hover {
        color: #990000;
    }
</style>
@endpush

@push('scripts')
<script>
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in-up').forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

</script>
@endpush

@endsection
