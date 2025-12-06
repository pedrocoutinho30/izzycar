@extends('frontend.partials.layout')


@include('frontend.partials.seo', [
'seo' => $page->seo
])

@section('content')
@php
$home = App\Models\Page::where('slug', 'homepage')->first();
@endphp
@include('frontend.partials.hero-section', ['title' => 'Izzycar', 'subtitle' => $home['contents']->where('field_name', 'title')->first()->field_value])

<!-- Introduction Section with Modern Layout -->
<section class="intro-section py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="intro-content-card fade-in-up">
                    {!! $home['contents']->where('field_name', 'content')->first()->field_value !!}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section with Cards -->
<section class="values-section py-5" style="background-color: #f8f9fa;">
    <div class="container">
        <div class="row g-4">
            <!-- Nossa Missão -->
            <!-- <div class="col-lg-6 col-md-6"> -->
                <div class="value-card h-100 fade-in-up" data-delay="0">
                    <div class="value-icon-wrapper">
                        <svg class="value-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                    </div>
                    <div class="value-content">
                        {!! $home['contents']->where('field_name', 'nossa_missao')->first()->field_value !!}
                    </div>
                </div>
            <!-- </div> -->

            <!-- Nosso Papel -->
            <!-- <div class="col-lg-4 col-md-6">
                <div class="value-card h-100 fade-in-up" data-delay="100">
                    <div class="value-icon-wrapper">
                        <svg class="value-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div class="value-content">
                        {!! $home['contents']->where('field_name', 'nosso_papel')->first()->field_value !!}
                    </div>
                </div>
            </div> -->

            <!-- Nossos Valores -->
            <!-- <div class="col-lg-6 col-md-6"> -->
                <div class="value-card h-100 fade-in-up" data-delay="200">
                    <div class="value-icon-wrapper">
                        <svg class="value-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </div>
                    <div class="value-content">
                        {!! $home['contents']->where('field_name', 'nossos_valores')->first()->field_value !!}
                    </div>
                <!-- </div> -->
            </div>
        </div>
    </div>
</section>

<!-- CTA Section with Modern Design -->
<section class="cta-section py-5" style="background: linear-gradient(135deg, #111111 0%, #2a2a2a 100%); position: relative; overflow: hidden;">
    <div class="cta-bg-pattern"></div>
    <div class="container position-relative">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="text-white mb-4 fade-in-up" style="font-weight: 700; font-size: 2.5rem;">Pronto para Importar o Seu Carro?</h2>
                <p class="text-white-50 mb-4 fade-in-up" data-delay="100" style="font-size: 1.2rem;">Deixe-nos tratar de tudo. Experiência, confiança e qualidade ao seu serviço.</p>
                <a href="{{ route('frontend.form-import') }}" class="btn-modern fade-in-up" data-delay="200">
                    <span>Quero Importar</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>


    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <style>
        /* Modern Card Styles */
        .intro-content-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            /* box-shadow: 0 10px 40px rgba(0,0,0,0.08); */
            border: 1px solid rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .intro-content-card:hover {
            transform: translateY(-5px);
            /* box-shadow: 0 15px 50px rgba(0,0,0,0.12); */
        }

        .value-card {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            /* box-shadow: 0 5px 25px rgba(0,0,0,0.06); */
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .value-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            /* background: linear-gradient(90deg, #990000, #6e0707); */
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .value-card:hover {
            transform: translateY(-8px);
            border: 2px solid var(--accent-color);
            /* box-shadow: 0 15px 40px rgba(153,0,0,0.15); */
        }

        .value-card:hover::before {
            transform: scaleX(1);
        }

        .value-icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #990000, #6e0707);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }

        .value-card:hover .value-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        .value-icon {
            width: 40px;
            height: 40px;
            color: white;
        }

        .value-content {
            font-size: 1rem;
            line-height: 1.7;
        }

        /* Modern CTA Button */
        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 18px 40px;
            background: linear-gradient(135deg, #990000, #6e0707);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(153,0,0,0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(153,0,0,0.4);
            color: white;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-modern svg {
            transition: transform 0.3s ease;
        }

        .btn-modern:hover svg {
            transform: translateX(5px);
        }

        /* CTA Background Pattern */
        .cta-bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.03;
            background-image: 
                repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255,255,255,.1) 35px, rgba(255,255,255,.1) 70px);
        }

        /* Fade In Animations */
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

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Typography */
        @media (max-width: 768px) {
            .intro-content-card {
                padding: 2rem;
            }

            .value-card {
                padding: 2rem;
            }

            .btn-modern {
                padding: 15px 30px;
                font-size: 1rem;
            }

            .cta-section h2 {
                font-size: 2rem !important;
            }

            .cta-section p {
                font-size: 1rem !important;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
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

        // Parallax effect for CTA section
        window.addEventListener('scroll', () => {
            const ctaSection = document.querySelector('.cta-section');
            if (ctaSection) {
                const scrolled = window.pageYOffset;
                const rect = ctaSection.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    const pattern = ctaSection.querySelector('.cta-bg-pattern');
                    if (pattern) {
                        pattern.style.transform = `translateY(${scrolled * 0.3}px)`;
                    }
                }
            }
        });
    </script>
    @endpush

@endsection