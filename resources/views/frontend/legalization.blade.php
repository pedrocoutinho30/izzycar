@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => $data->seo
])

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Service",
    "name": "Legalização Automóvel em Portugal",
    "alternateName": "Legalização de carros importados",
    "description": "Serviço completo de legalização de veículos importados em Portugal: inspeção de origem, cálculo e pagamento de ISV, IPO, IMT, matrícula e documentação.",
    "url": "https://izzycar.pt/legalizacao",
    "provider": {
        "@@type": "AutoDealer",
        "name": "Izzycar",
        "url": "https://izzycar.pt"
    },
    "areaServed": {
        "@@type": "Country",
        "name": "Portugal"
    },
    "serviceType": "Legalização Automóvel",
    "category": "Automóvel",
    "breadcrumb": {
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {"@@type": "ListItem", "position": 1, "name": "Início", "item": "https://izzycar.pt"},
            {"@@type": "ListItem", "position": 2, "name": "Legalização Automóvel", "item": "https://izzycar.pt/legalizacao"}
        ]
    }
}
</script>
@endpush

@section('content')

<!-- Hero Section -->
<section class="hero-page-section">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="row align-items-center min-vh-50 py-5">
            <div class="col-lg-7">
                <nav class="hero-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">Início</a>
                    <span>/</span>
                    <span>Legalização Automóvel</span>
                </nav>
                <span class="hero-badge fade-in-up">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    Legalização de Veículos
                </span>
                <h1 class="hero-title fade-in-up" data-delay="100">Legalização <span class="hero-accent">Automóvel</span></h1>
                <p class="hero-description fade-in-up" data-delay="200">{{ $data->contents['subtitle'] }}</p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge fade-in-up">Como Funciona</span>
            <h2 class="section-title fade-in-up" data-delay="100">Processo de Legalização</h2>
        </div>
        <div class="legalization-intro-card fade-in-up" data-delay="200">
            <div class="intro-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <line x1="10" y1="9" x2="8" y2="9"></line>
                </svg>
            </div>
            <div class="intro-content">
                {!!$data->contents['content']!!}
            </div>
        </div>
    </div>
</section>

<section class="section-padding pt-0">
    <div class="container">
        <div class="section-header-modern">
            <h2 class="section-title-modern">Passos para legalizar o seu veículo</h2>
            <p class="section-subtitle-modern">Processo simplificado em etapas claras</p>
        </div>

        <div class="row g-4">
            @foreach ($data->contents['enum'] as $index => $item)
            <div class="col-md-6">
                <div class="legalization-step-card">
                    <div class="step-number">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="step-content">
                        <h5 class="step-title">{{ $item['title'] }}</h5>
                        <div class="step-description">{!! $item['content'] !!}</div>
                    </div>
                    <div class="step-arrow">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Hero Section */
    .hero-page-section {
        position: relative;
        background: linear-gradient(135deg, #111111 0%, #1a1a1a 50%, #111111 100%);
        overflow: hidden;
        padding: 3rem 0;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%236e0707' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
    }

    .min-vh-60 {
        min-height: 60vh;
    }

    .hero-breadcrumb {
        display: flex; align-items: center; gap: .5rem; justify-content: flex-start;
        font-size: .82rem; color: rgba(255,255,255,.45);
        margin-bottom: 1rem;
    }
    .hero-breadcrumb a { color: rgba(255,255,255,.45); text-decoration: none; }
    .hero-breadcrumb a:hover { color: #fff; }
    .hero-breadcrumb span { color: rgba(255,255,255,.25); }
    .hero-breadcrumb span:last-child { color: rgba(255,255,255,.7); }

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
        font-size: clamp(2rem, 5vw, 3rem);
        font-weight: 900;
        color: #fff;
        line-height: 1.15;
        margin-bottom: 1.5rem;
        white-space: nowrap;
    }
    .hero-accent { color: var(--accent-color); }

    .hero-description {
        font-size: 1.2rem;
        color: rgba(255,255,255,0.8);
        line-height: 1.8;
        max-width: 700px;
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

    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards;
    }

    .fade-in-up[data-delay="100"] { animation-delay: 0.1s; }
    .fade-in-up[data-delay="200"] { animation-delay: 0.2s; }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .legalization-intro-card {
        background: white;
        border-radius: 24px;
        padding: 3rem;
        /* box-shadow: 0 10px 40px rgba(0,0,0,0.08); */
        border: 2px solid #990000;
        display: flex;
        gap: 2rem;
        align-items: flex-start;
    }

    .intro-icon {
        flex-shrink: 0;
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .intro-content {
        flex: 1;
        font-size: 1.1rem;
        line-height: 1.8;
        color: #333;
    }

    .intro-content p {
        margin-bottom: 1rem;
    }

    .section-header-modern {
        text-align: center;
        margin-bottom: 3rem;
    }

    .section-title-modern {
        font-size: 2.5rem;
        font-weight: 800;
        color: #111;
        margin-bottom: 1rem;
    }

    .section-subtitle-modern {
        font-size: 1.1rem;
        color: #6c757d;
        margin: 0;
    }

    .legalization-step-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        height: 100%;
        /* box-shadow: 0 10px 40px rgba(0,0,0,0.08); */
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
    }

    .legalization-step-card:hover {
        transform: translateY(-5px);
        /* box-shadow: 0 15px 50px rgba(0,0,0,0.12); */
        border-color: var(--accent-color);
    }

    .step-number {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        color: white;
        font-size: 2rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        opacity: 0.1;
        transition: all 0.3s ease;
    }

    .legalization-step-card:hover .step-number {
        opacity: 0.2;
        transform: scale(1.1);
    }

    .step-content {
        position: relative;
        z-index: 1;
    }

    .step-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 1rem;
        line-height: 1.3;
    }

    .step-description {
        font-size: 1rem;
        line-height: 1.7;
        color: #495057;
    }

    .step-description p {
        margin-bottom: 0.75rem;
    }

    .step-description ul,
    .step-description ol {
        padding-left: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .step-description li {
        margin-bottom: 0.5rem;
    }

    .step-arrow {
        position: absolute;
        bottom: 1.5rem;
        right: 1.5rem;
        opacity: 0;
        transform: translateX(-10px);
        transition: all 0.3s ease;
        color: var(--accent-color);
    }

    .legalization-step-card:hover .step-arrow {
        opacity: 1;
        transform: translateX(0);
    }

    @media (max-width: 992px) {
        .legalization-intro-card {
            flex-direction: column;
            padding: 2rem;
        }

        .intro-icon {
            width: 64px;
            height: 64px;
        }

        .intro-icon svg {
            width: 32px;
            height: 32px;
        }

        .section-title-modern {
            font-size: 2rem;
        }
    }

    @media (max-width: 768px) {
        .legalization-intro-card {
            padding: 1.5rem;
        }

        .intro-content {
            font-size: 1rem;
        }

        .section-title-modern {
            font-size: 1.75rem;
        }

        .legalization-step-card {
            padding: 1.5rem;
        }

        .step-title {
            font-size: 1.2rem;
        }
    }

    @media (max-width: 768px) {
        .hero-title { font-size: 1.8rem; white-space: normal; }
        .hero-description { font-size: 1rem; }
    }
</style>
@endpush

@push('scripts')
<script>
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
</script>
@endpush