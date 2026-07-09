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
        "url": "https://izzycar.pt",
        "sameAs": ["https://www.facebook.com/profile.php?id=61572831810539","https://www.instagram.com/izzycarpt/"]
    },
    "areaServed": {"@@type": "Country", "name": "Portugal"},
    "serviceType": "Legalização Automóvel",
    "category": "Automóvel",
    "offers": {
        "@@type": "Offer",
        "description": "Serviço de legalização automóvel — peça a sua cotação gratuitamente",
        "priceCurrency": "EUR",
        "availability": "https://schema.org/InStock",
        "url": "https://izzycar.pt/legalizacao"
    }
}
</script>

<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {"@@type": "ListItem", "position": 1, "name": "Início", "item": "https://izzycar.pt"},
        {"@@type": "ListItem", "position": 2, "name": "Legalização Automóvel", "item": "https://izzycar.pt/legalizacao"}
    ]
}
</script>

@if(!empty($data->contents['enum']))
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "HowTo",
    "name": "Como legalizar um carro importado em Portugal",
    "description": "Passos para legalizar um veículo importado em Portugal: inspeção, ISV, IPO, IMT e matrícula portuguesa.",
    "step": [
        @foreach($data->contents['enum'] as $i => $step)
        {
            "@@type": "HowToStep",
            "position": {{ $i + 1 }},
            "name": "{{ addslashes($step['title']) }}",
            "text": "{{ addslashes(strip_tags($step['content'])) }}"
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ]
}
</script>
@endif
@endpush

@section('content')

<!-- Hero Section -->
<section class="hero-page-section">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="row align-items-center min-vh-50 py-5">
            <div class="col-lg-6">
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

            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center">
                <div class="hero-legal-card fade-in-up" data-delay="300">
                    <div class="hero-legal-card-header">
                        <div class="hero-legal-card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                        </div>
                        <span>Legalização Automóvel</span>
                    </div>
                    <div class="hero-legal-items">
                        <div class="hero-legal-item">
                            <div class="hero-legal-item-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                            <div>
                                <div class="hero-legal-item-title">ISV calculado e pago</div>
                                <div class="hero-legal-item-sub">Sem surpresas fiscais</div>
                            </div>
                        </div>
                        <div class="hero-legal-item">
                            <div class="hero-legal-item-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            </div>
                            <div>
                                <div class="hero-legal-item-title">2 a 4 semanas</div>
                                <div class="hero-legal-item-sub">Prazo médio do processo</div>
                            </div>
                        </div>
                        <div class="hero-legal-item">
                            <div class="hero-legal-item-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            </div>
                            <div>
                                <div class="hero-legal-item-title">IPO, IMT e matrícula</div>
                                <div class="hero-legal-item-sub">Toda a documentação incluída</div>
                            </div>
                        </div>
                        <div class="hero-legal-item">
                            <div class="hero-legal-item-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            </div>
                            <div>
                                <div class="hero-legal-item-title">100% legal em Portugal</div>
                                <div class="hero-legal-item-sub">Conformidade garantida</div>
                            </div>
                        </div>
                    </div>
                    <div class="hero-legal-card-footer">
                        <a href="{{ route('frontend.form-import') }}" class="hero-legal-cta">
                            Pedir cotação gratuita
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding section-intro-legal">
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

<section class="section-padding legalization-steps-section">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge fade-in-up">Passo a Passo</span>
            <h2 class="section-title fade-in-up" data-delay="100">Passos para legalizar o seu veículo</h2>
            <p class="section-description fade-in-up" data-delay="200">Processo simplificado em etapas claras</p>
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

<section class="legalization-cta-section">
    <div class="container">
        <div class="legalization-cta-card">
            <div class="legalization-cta-content">
                <h2 class="legalization-cta-title">Pronto para legalizar o seu veículo?</h2>
                <p class="legalization-cta-subtitle">A nossa equipa trata de todo o processo por si — rápido, transparente e sem surpresas.</p>
            </div>
            <div class="legalization-cta-actions">
                <a href="{{ route('frontend.form-import') }}" class="btn-legalization-primary">
                    Pedir cotação gratuita
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
                <a href="tel:{{ optional(\App\Models\Setting::where('label', 'phone')->first())->value }}" class="btn-legalization-secondary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    Ligar agora
                </a>
            </div>
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

    /* Card flutuante do hero */
    .hero-legal-card {
        background: rgba(255,255,255,0.06);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 24px;
        padding: 2.25rem;
        width: 100%;
        max-width: 380px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.4);
        position: relative;
        animation: floatLegalCard 5s ease-in-out infinite;
    }

    .hero-legal-card::before {
        content: '';
        position: absolute;
        top: -1px; left: 30px; right: 30px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        pointer-events: none;
    }

    @keyframes floatLegalCard {
        0%, 100% { transform: translateY(0); }
        50%       { transform: translateY(-10px); }
    }

    .hero-legal-card-header {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding-bottom: 1.25rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .hero-legal-card-icon {
        flex-shrink: 0;
        width: 44px; height: 44px;
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        box-shadow: 0 4px 14px rgba(153,0,0,0.45);
    }

    .hero-legal-card-header > span {
        color: rgba(255,255,255,0.9);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .hero-legal-items {
        display: flex;
        flex-direction: column;
        gap: 0.875rem;
        margin-bottom: 1.5rem;
    }

    .hero-legal-item {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 0.875rem 1rem;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 12px;
        transition: all 0.25s ease;
    }

    .hero-legal-item:hover {
        background: rgba(255,255,255,0.09);
        border-color: rgba(153,0,0,0.35);
    }

    .hero-legal-item-icon {
        flex-shrink: 0;
        width: 34px; height: 34px;
        background: rgba(153,0,0,0.2);
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        color: #ff8080;
    }

    .hero-legal-item-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: rgba(255,255,255,0.92);
        line-height: 1.2;
    }

    .hero-legal-item-sub {
        font-size: 0.75rem;
        color: rgba(255,255,255,0.5);
        margin-top: 0.1rem;
    }

    .hero-legal-card-footer {
        padding-top: 1.25rem;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .hero-legal-cta {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.85rem 1.5rem;
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        color: #fff;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 50px;
        text-decoration: none;
        box-shadow: 0 4px 16px rgba(153,0,0,0.45);
        transition: all 0.25s ease;
    }

    .hero-legal-cta:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 22px rgba(153,0,0,0.6);
    }

    /* Secção intro */
    #section_description_import,
    .section-intro-legal {
        background: linear-gradient(160deg, #f7f7f7 0%, #fafafa 60%, #f5f5f5 100%);
    }

    .legalization-intro-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 3rem;
        box-shadow: 0 6px 28px rgba(0,0,0,0.07);
        border: 1px solid rgba(0,0,0,0.05);
        border-top: 4px solid #990000;
        display: flex;
        gap: 2rem;
        align-items: flex-start;
        position: relative;
        overflow: hidden;
    }

    .legalization-intro-card::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 200px; height: 200px;
        background: radial-gradient(ellipse at top right, rgba(153,0,0,0.04) 0%, transparent 70%);
        pointer-events: none;
    }

    .intro-icon {
        flex-shrink: 0;
        width: 72px; height: 72px;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        color: white;
        box-shadow: 0 6px 18px rgba(110,7,7,0.35);
        position: relative; z-index: 1;
    }

    .intro-content {
        flex: 1;
        font-size: 1.05rem;
        line-height: 1.85;
        color: #333;
        position: relative; z-index: 1;
    }

    .intro-content p { margin-bottom: 1rem; }

    /* Secção passos */
    .legalization-steps-section {
        background: linear-gradient(160deg, #ffffff 0%, #f9f9f9 50%, #f5f5f5 100%);
    }

    .legalization-step-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 2rem;
        height: 100%;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        border: 1px solid rgba(0,0,0,0.06);
        border-top: 3px solid transparent;
        transition: all 0.3s ease;
        position: relative;
    }

    .legalization-step-card:hover {
        border-top-color: #990000;
        transform: translateY(-4px);
        box-shadow: 0 10px 32px rgba(0,0,0,0.11);
    }

    .step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px; height: 44px;
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        color: #fff;
        font-size: 0.95rem;
        font-weight: 800;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(153,0,0,0.35);
        margin-bottom: 1.25rem;
        position: static;
        opacity: 1;
        transition: box-shadow 0.3s ease, transform 0.3s ease;
    }

    .legalization-step-card:hover .step-number {
        box-shadow: 0 6px 18px rgba(153,0,0,0.5);
        transform: scale(1.06);
    }

    .step-content { position: relative; }

    .step-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }

    .step-description {
        font-size: 0.97rem;
        line-height: 1.75;
        color: #555;
    }

    .step-description p { margin-bottom: 0.6rem; }

    .step-description ul,
    .step-description ol {
        padding-left: 1.4rem;
        margin-bottom: 0.6rem;
    }

    .step-description li { margin-bottom: 0.4rem; }

    .step-arrow { display: none; }

    @media (max-width: 992px) {
        .legalization-intro-card {
            flex-direction: column;
            padding: 2rem;
        }
        .intro-icon { width: 60px; height: 60px; }
        .intro-icon svg { width: 30px; height: 30px; }
    }

    @media (max-width: 768px) {
        .legalization-intro-card { padding: 1.5rem; }
        .intro-content { font-size: 1rem; }
        .legalization-step-card { padding: 1.5rem; }
        .step-title { font-size: 1.1rem; }
    }

    @media (max-width: 768px) {
        .hero-title { font-size: 1.8rem; white-space: normal; }
        .hero-description { font-size: 1rem; }
    }

    /* CTA Section */
    .legalization-cta-section {
        padding: 5rem 0;
        background: linear-gradient(135deg, #0a0a0a 0%, #111111 100%);
    }

    .legalization-cta-card {
        background: linear-gradient(135deg, #6e0707 0%, #990000 100%);
        border-radius: 24px;
        padding: 3.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
        box-shadow: 0 20px 60px rgba(110, 7, 7, 0.4);
    }

    .legalization-cta-title {
        font-size: 2rem;
        font-weight: 800;
        color: white;
        margin-bottom: 0.75rem;
    }

    .legalization-cta-subtitle {
        font-size: 1.05rem;
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
    }

    .legalization-cta-actions {
        display: flex;
        gap: 1rem;
        flex-shrink: 0;
    }

    .btn-legalization-primary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 1rem 2rem;
        background: white;
        color: #6e0707;
        font-weight: 700;
        font-size: 1rem;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        white-space: nowrap;
    }

    .btn-legalization-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        color: #6e0707;
    }

    .btn-legalization-secondary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 1rem 2rem;
        background: transparent;
        color: white;
        font-weight: 700;
        font-size: 1rem;
        border-radius: 50px;
        border: 2px solid rgba(255, 255, 255, 0.5);
        text-decoration: none;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .btn-legalization-secondary:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: white;
        color: white;
        transform: translateY(-2px);
    }

    @media (max-width: 992px) {
        .legalization-cta-card {
            flex-direction: column;
            text-align: center;
            padding: 2.5rem;
        }

        .legalization-cta-title {
            font-size: 1.6rem;
        }

        .legalization-cta-actions {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .legalization-cta-section {
            padding: 3rem 0;
        }

        .legalization-cta-card {
            padding: 2rem 1.5rem;
        }

        .legalization-cta-title {
            font-size: 1.35rem;
        }

        .legalization-cta-actions {
            flex-direction: column;
        }

        .btn-legalization-primary,
        .btn-legalization-secondary {
            width: 100%;
            justify-content: center;
        }
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