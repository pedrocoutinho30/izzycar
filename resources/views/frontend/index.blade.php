@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => $page->seo
])

@section('content')

<!-- Hero Section -->
<section class="hero-homepage">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="row align-items-center min-vh-95 py-5">
            <div class="col-lg-7">
                <div class="hero-content">
                    <span class="hero-badge fade-in-up">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        </svg>
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
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                        <a href="{{ route('frontend.cost-simulator') }}" class="btn-hero-secondary">
                            <span>€</span>
                            <span>Simular Custos</span>
                        </a>
                    </div>
                    <div class="hero-stats fade-in-up" data-delay="400">
                        <!-- <div class="hero-stat">
                            <div class="hero-stat-number">500+</div>
                            <div class="hero-stat-label">Carros Importados</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-number">98%</div>
                            <div class="hero-stat-label">Clientes Satisfeitos</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-number">15+</div>
                            <div class="hero-stat-label">Anos de Experiência</div>
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex align-items-center justify-content-center">
                <div class="hero-image-wrapper fade-in-up" data-delay="300">
                    <div class="hero-glow"></div>
                    <picture>
                        <source srcset="{{ asset('img/hero.webp') }}" type="image/webp">
                        <img src="{{ asset('img/2.jpg') }}"
                             alt="Importação automóvel Izzycar"
                             class="hero-image"
                             width="500" height="600"
                             loading="eager">
                    </picture>
                </div>
            </div>
        </div>
    </div>
    <div class="scroll-indicator">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-color)" stroke-width="2">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </div>
</section>

<!-- Trust Section -->
<section class="trust-section">
    <div class="container">
        <div class="trust-content">
            <div class="row align-items-center">
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="trust-item fade-in-up">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--accent-color)" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <polyline points="9 12 11 14 15 10"></polyline>
                        </svg>
                        <h4>100% Seguro</h4>
                        <p>Processo transparente e garantido</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="trust-item fade-in-up" data-delay="100">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--accent-color)" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <h4>Entrega Rápida</h4>
                        <p>3-6 semanas em média</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="trust-item fade-in-up" data-delay="200">

                        <span style="font-size:48px; color:var(--accent-color); font-weight:700;">€</span>


                        <h4>Melhor Preço</h4>
                        <p>Economize até 30%</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="trust-item fade-in-up" data-delay="300">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--accent-color)" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <polyline points="17 11 19 13 23 9"></polyline>
                        </svg>
                        <h4>Suporte Total</h4>
                        <p>Do início ao fim</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section section-padding">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge fade-in-up">Nossos Serviços</span>
            <h2 class="section-title fade-in-up" data-delay="100">Como Podemos Ajudar</h2>
            <p class="section-description fade-in-up" data-delay="200">Oferecemos uma gama completa de serviços para tornar a sua importação simples e segura</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-6 col-md-6">
                <div class="service-card fade-in-up">
                    <div class="service-icon-wrapper">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                            <path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path>
                        </svg>
                    </div>
                    <h3 class="service-title">Importação Chave na Mão</h3>
                    <p class="service-description">Tratamos de todo o processo: procura, compra, transporte, legalização e entrega. Recebe o seu carro pronto a conduzir.</p>
                    <a href="{{ route('frontend.import') }}" class="service-link">
                        Saber mais
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="col-lg-6 col-md-6">
                <div class="service-card fade-in-up" data-delay="100">
                    <div class="service-icon-wrapper">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                    <h3 class="service-title">Legalização de Veículos</h3>
                    <p class="service-description">Já tem o carro? Tratamos da inspeção, matrícula e toda a documentação necessária para legalizar o seu veículo em Portugal.</p>
                    <a href="{{ route('frontend.legalization') }}" class="service-link">
                        Saber mais
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- <div class="col-lg-4 col-md-6">
                <div class="service-card fade-in-up" data-delay="200">
                    <div class="service-icon-wrapper">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <h3 class="service-title">Venda de Carros</h3>
                    <p class="service-description">Carros nacionais e importados prontos para entrega imediata. Veículos inspecionados e com garantia.</p>
                    <a href="" class="service-link">
                        Ver stock
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div> -->
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="why-section section-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="why-content">
                    <span class="section-badge fade-in-up">Porque Escolher-nos</span>
                    <h2 class="section-title fade-in-up" data-delay="100">Experiência e Confiança<br>ao Seu Serviço</h2>
                    <p class="why-description fade-in-up" data-delay="200">
                        A Izzycar é a sua parceira de confiança para importação de veículos. Trabalhamos com transparência total, sem custos escondidos.
                    </p>

                    <div class="why-features">
                        <div class="why-feature fade-in-up" data-delay="300">
                            <div class="why-feature-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="why-feature-content">
                                <h4>Processo Transparente</h4>
                                <p>Acompanhamento em tempo real de todas as etapas da importação</p>
                            </div>
                        </div>

                        <div class="why-feature fade-in-up" data-delay="400">
                            <div class="why-feature-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="why-feature-content">
                                <h4>Inspeção Rigorosa</h4>
                                <p>Todos os veículos são inspecionados antes da compra</p>
                            </div>
                        </div>

                        <div class="why-feature fade-in-up" data-delay="500">
                            <div class="why-feature-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="why-feature-content">
                                <h4>Apoio Personalizado</h4>
                                <p>Equipa dedicada disponível para esclarecer todas as suas dúvidas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 d-flex align-items-stretch">
                <div class="why-image-grid fade-in-up" data-delay="300" style="display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; gap: 1rem; height: 100%; max-height: 480px; min-height: 320px;">
                    <div class="why-image-item why-image-large" style="grid-row: 1 / span 2; grid-column: 1 / 2; height: 100%;">
                        <picture>
                            <source srcset="{{ asset('img/2.webp') }}" type="image/webp">
                            <img src="{{ asset('img/2.jpg') }}" alt="Profissionalismo Izzycar" loading="lazy" width="527" height="960" style="height: 100%; width: 100%; object-fit: cover; max-height: 480px;">
                        </picture>
                    </div>
                    <div class="why-image-item" style="grid-row: 1 / 2; grid-column: 2 / 3; height: 100%;">
                        <picture>
                            <source srcset="{{ asset('img/1.webp') }}" type="image/webp">
                            <img src="{{ asset('img/1.jpg') }}" alt="Qualidade Garantida" loading="lazy" width="768" height="960" style="height: 100%; width: 100%; object-fit: cover; max-height: 240px;">
                        </picture>
                    </div>
                    <div class="why-image-item" style="grid-row: 2 / 3; grid-column: 2 / 3; height: 100%;">
                        <picture>
                            <source srcset="{{ asset('img/3.webp') }}" type="image/webp">
                            <img src="{{ asset('img/3.jpg') }}" alt="Experiência Comprovada" loading="lazy" width="641" height="960" style="height: 100%; width: 100%; object-fit: cover; max-height: 240px;">
                        </picture>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="process-section section-padding">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge fade-in-up">Como Funciona</span>
            <h2 class="section-title fade-in-up" data-delay="100">Simples em 4 Passos</h2>
            <p class="section-description fade-in-up" data-delay="200">Do contacto inicial à entrega do seu carro</p>
        </div>

        <div class="process-steps-grid">
            <div class="process-step-card fade-in-up">
                <div class="process-step-num">01</div>
                <h3>Pedido de Cotação</h3>
                <p>Preencha o formulário com as características do carro que deseja. Respondemos em 24h com uma cotação detalhada e transparente.</p>
            </div>

            <div class="process-step-card fade-in-up" data-delay="100">
                <div class="process-step-num">02</div>
                <h3>Procura e Seleção</h3>
                <p>Procuramos o veículo perfeito para si nos melhores mercados europeus. Inspecionamos e enviamos relatório fotográfico completo.</p>
            </div>

            <div class="process-step-card fade-in-up" data-delay="200">
                <div class="process-step-num">03</div>
                <h3>Compra e Transporte</h3>
                <p>Após aprovação, compramos e tratamos do transporte seguro até Portugal. Acompanhe todo o processo em tempo real.</p>
            </div>

            <div class="process-step-card fade-in-up" data-delay="300">
                <div class="process-step-num">04</div>
                <h3>Legalização e Entrega</h3>
                <p>Tratamos de toda a papelada, inspeção e matrícula. Recebe o seu carro pronto a conduzir, com documentação completa.</p>
            </div>
        </div>

        <div class="text-center mt-5 fade-in-up" data-delay="400">
            <a href="{{ route('frontend.import') }}" class="btn-cta-modern">
                Ver Processo Detalhado
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
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
        <div class="row g-4">
            @forelse($reviews as $index => $review)
            @if($review->comment !== '' && $review->comment !== null)
                
            @php
                $initial = mb_strtoupper(mb_substr($review->name, 0, 1));
                $color   = $avatarColors[$index % count($avatarColors)];
                $delay   = $delays[$index % count($delays)];
            @endphp
            <div class="col-lg-4 col-md-6 fade-in-up" data-delay="{{ $delay }}">
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
    </div>
</section>

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
        background: linear-gradient(135deg, #111111 0%, #1a1a1a 50%, #111111 100%);
        overflow: hidden;
    }

    .hero-overlay {
        position: absolute;
        top: 10px;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%236e0707' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
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
        background: rgba(110, 7, 7, 0.2);
        border: 1px solid rgba(110, 7, 7, 0.3);
        border-radius: 50px;
        color: #fff;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 2rem;
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
        border-radius: 50px;
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
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-hero-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: white;
        color: white;
    }

    .hero-stats {
        display: flex;
        gap: 3rem;
        flex-wrap: wrap;
    }

    .hero-stat {
        text-align: center;
    }

    .hero-stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        color: #990000;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .hero-stat-label {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .hero-image-wrapper {
        position: relative;
        text-align: center;
    }

    .hero-glow {
        position: absolute;
        inset: -20%;
        background: radial-gradient(ellipse at 50% 60%, rgba(153,0,0,0.28) 0%, rgba(200,60,0,0.1) 40%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
        filter: blur(20px);
        animation: glowPulse 4s ease-in-out infinite;
    }

    @keyframes glowPulse {
        0%, 100% { opacity: 0.7; transform: scale(1); }
        50%       { opacity: 1;   transform: scale(1.06); }
    }

    .hero-image-wrapper picture,
    .hero-image-wrapper img {
        position: relative;
        z-index: 1;
    }

    .hero-image {
        max-width: 100%;
        height: 580px;
        width: 100%;
        object-fit: cover;
        object-position: center top;
        border-radius: 24px;
        box-shadow: 0 30px 80px rgba(0,0,0,0.4);
        border: 2px solid rgba(110,7,7,0.3);
    }

    .scroll-indicator {
        position: absolute;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        animation: bounce 2s infinite;
        color: rgba(255, 255, 255, 0.5);
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateX(-50%) translateY(0);
        }

        50% {
            transform: translateX(-50%) translateY(-10px);
        }
    }

    /* Trust Section */
    .trust-section {
        padding: 3rem 0;
        background: linear-gradient(135deg, #0d0d0d 0%, #111111 50%, #0d0d0d 100%);
    }

    .trust-content {
        padding: 2rem;
        border-radius: 24px;
        background: transparent;
    }

    .trust-item {
        text-align: center;
        padding: 1.75rem 1.5rem;
        background: rgba(255,255,255,0.04);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 16px;
        transition: all 0.3s ease;
        height: 100%;
    }

    .trust-item:hover {
        background: rgba(255,255,255,0.07);
        border-color: rgba(153,0,0,0.4);
        box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 0 0 1px rgba(153,0,0,0.2);
        transform: translateY(-3px);
    }

    .trust-item svg {
        color: var(--accent-color);
        margin-bottom: 1rem;
    }

    .trust-item h4 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 0.5rem;
    }

    .trust-item p {
        font-size: 0.95rem;
        color: rgba(255,255,255,0.6);
        margin: 0;
    }

    /* Services Section */
    .services-section {
        background: linear-gradient(135deg, #111111 0%, #161616 50%, #111111 100%);
    }

    .services-section .section-title {
        color: #fff;
    }

    .services-section .section-description {
        color: rgba(255,255,255,0.65);
    }

    .services-section .section-badge {
        background: rgba(153,0,0,0.2);
        color: #ff8080;
    }

    .section-padding {
        padding: 5rem 0;
    }

    .section-badge {
        display: inline-block;
        padding: 8px 20px;
        background: rgba(110, 7, 7, 0.1);
        color: #990000;
        border-radius: 50px;
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

    .service-card {
        background: rgba(255,255,255,0.04);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        padding: 2.5rem;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.08);
        transition: all 0.35s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .service-card:hover {
        background: rgba(255,255,255,0.07);
        border-color: rgba(153,0,0,0.45);
        box-shadow: 0 12px 40px rgba(0,0,0,0.35), 0 0 0 1px rgba(153,0,0,0.25);
        transform: translateY(-6px);
    }

    .service-icon-wrapper {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease;
    }

    .service-card:hover .service-icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }

    .service-icon-wrapper svg {
        color: white;
    }

    .service-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 1rem;
    }

    .service-description {
        font-size: 1rem;
        color: rgba(255,255,255,0.65);
        line-height: 1.7;
        margin-bottom: 1.5rem;
        flex: 1;
    }

    .service-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #ff8080;
        font-weight: 700;
        text-decoration: none;
        transition: gap 0.3s ease, color 0.2s ease;
    }

    .service-link:hover {
        gap: 12px;
        color: #ff6060;
    }

    /* Why Section */
    /* Why Section — override bg-light */
    .why-section {
        background: linear-gradient(160deg, #ffffff 0%, #fafafa 55%, #fff7f7 100%);
        position: relative;
        overflow: hidden;
    }

    .why-section::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 320px;
        height: 320px;
        background: radial-gradient(ellipse at center, rgba(153,0,0,0.06) 0%, transparent 70%);
        pointer-events: none;
    }

    .why-section::after {
        content: '';
        position: absolute;
        bottom: -60px;
        left: -60px;
        width: 250px;
        height: 250px;
        background: radial-gradient(ellipse at center, rgba(153,0,0,0.04) 0%, transparent 70%);
        pointer-events: none;
    }

    .why-content {
        padding-right: 2rem;
        position: relative;
        z-index: 1;
    }

    .why-description {
        font-size: 1.05rem;
        color: #555;
        line-height: 1.85;
        margin-bottom: 2rem;
    }

    .why-feature {
        display: flex;
        gap: 1.25rem;
        margin-bottom: 1rem;
        padding: 1.25rem 1.5rem;
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid rgba(0,0,0,0.06);
        border-left: 3px solid transparent;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .why-feature:hover {
        transform: translateX(6px);
        border-left-color: #990000;
        box-shadow: 0 6px 24px rgba(0,0,0,0.09);
    }

    .why-feature-icon {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(110,7,7,0.35);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .why-feature:hover .why-feature-icon {
        transform: scale(1.08);
        box-shadow: 0 6px 20px rgba(110,7,7,0.5);
    }

    .why-feature-icon svg {
        color: white;
    }

    .why-feature-content h4 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 0.25rem;
    }

    .why-feature-content p {
        font-size: 0.9rem;
        color: #6c757d;
        margin: 0;
        line-height: 1.6;
    }

    .why-image-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        position: relative;
        z-index: 1;
    }

    .why-image-item {
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,0.13);
        border: 2px solid rgba(255,255,255,0.9);
        transition: transform 0.35s ease, box-shadow 0.35s ease;
    }

    .why-image-item:hover {
        transform: scale(1.02);
        box-shadow: 0 14px 40px rgba(0,0,0,0.18);
    }

    .why-image-large {
        grid-row: span 2;
    }

    .why-image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Process Section */
    .process-section {
        background: linear-gradient(160deg, #f7f7f7 0%, #fafafa 50%, #f5f5f5 100%);
        position: relative;
        overflow: hidden;
    }

    .process-section::before {
        content: '';
        position: absolute;
        top: -100px;
        left: -100px;
        width: 350px;
        height: 350px;
        background: radial-gradient(ellipse at center, rgba(153,0,0,0.05) 0%, transparent 70%);
        pointer-events: none;
    }

    .process-steps-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        position: relative;
        max-width: 1100px;
        margin: 0 auto;
    }

    /* Horizontal connector line */
    .process-steps-grid::before {
        content: '';
        position: absolute;
        top: 30px;
        left: calc(30px + 1.5rem / 2);
        right: calc(30px + 1.5rem / 2);
        height: 2px;
        background: linear-gradient(90deg, #990000 0%, rgba(153,0,0,0.15) 100%);
        z-index: 0;
    }

    .process-step-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 2rem 1.5rem 1.75rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        border: 1px solid rgba(0,0,0,0.06);
        border-top: 3px solid transparent;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .process-step-card:hover {
        border-top-color: #990000;
        transform: translateY(-5px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.11);
    }

    .process-step-num {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: 800;
        color: #fff;
        box-shadow: 0 4px 16px rgba(153,0,0,0.4);
        margin-bottom: 1.25rem;
        border: 3px solid #ffffff;
    }

    .process-step-card h3 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 0.6rem;
    }

    .process-step-card p {
        font-size: 0.9rem;
        color: #6c757d;
        line-height: 1.7;
        margin: 0;
    }

    .btn-cta-modern {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 35px;
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        color: white;
        border-radius: 50px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        /* box-shadow: 0 8px 25px rgba(153, 0, 0, 0.3); */
    }

    .btn-cta-modern:hover {
        transform: translateY(-3px);
        /* box-shadow: 0 12px 35px rgba(153, 0, 0, 0.4); */
        color: white;
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
        border-radius: 50px;
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
        border-radius: 16px;
        padding: 1.75rem;
        border: 1px solid #e8e8e8;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .review-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        border-color: #dadada;
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
        border-radius: 50px;
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
        border-radius: 50px;
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
        border-radius: 50px;
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

    /* Animations */
    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards;
    }

    .fade-in-up[data-delay="100"] {
        animation-delay: 0.1s;
    }

    .fade-in-up[data-delay="200"] {
        animation-delay: 0.2s;
    }

    .fade-in-up[data-delay="300"] {
        animation-delay: 0.3s;
    }

    .fade-in-up[data-delay="400"] {
        animation-delay: 0.4s;
    }

    .fade-in-up[data-delay="500"] {
        animation-delay: 0.5s;
    }

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

        .hero-stats {
            gap: 2rem;
        }

        .section-title {
            font-size: 2rem;
        }

        .cta-title {
            font-size: 2.5rem;
        }

        .why-content {
            padding-right: 0;
            margin-bottom: 3rem;
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

        .hero-stats {
            gap: 1.5rem;
        }

        .hero-stat-number {
            font-size: 2rem;
        }

        .section-padding {
            padding: 3rem 0;
        }

        .section-title {
            font-size: 1.75rem;
        }

        .process-steps-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .process-steps-grid::before {
            display: none;
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

    @media (max-width: 480px) {
        .process-steps-grid {
            grid-template-columns: 1fr;
        }
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

    // Counter animation for hero stats
    const animateCounter = (element, target, duration = 2000) => {
        let start = 0;
        const increment = target / (duration / 16);
        const timer = setInterval(() => {
            start += increment;
            if (start >= target) {
                element.textContent = target + (element.textContent.includes('%') ? '%' : '+');
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(start) + (element.textContent.includes('%') ? '%' : '+');
            }
        }, 16);
    };

    // Trigger counter animation when hero stats are visible
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                entry.target.classList.add('animated');
                const statNumbers = entry.target.querySelectorAll('.hero-stat-number');
                statNumbers.forEach(stat => {
                    const text = stat.textContent;
                    const number = parseInt(text.replace(/\D/g, ''));
                    stat.textContent = '0' + (text.includes('%') ? '%' : '+');
                    setTimeout(() => animateCounter(stat, number), 300);
                });
            }
        });
    }, {
        threshold: 0.5
    });

    const heroStats = document.querySelector('.hero-stats');
    if (heroStats) {
        statsObserver.observe(heroStats);
    }
</script>
@endpush

@endsection