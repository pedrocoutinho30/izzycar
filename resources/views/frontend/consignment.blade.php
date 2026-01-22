@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => $data->seo
])
@section('content')

<!-- Hero Section -->
<section class="hero-page-section">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="row align-items-center min-vh-50 py-5">
            <div class="col-lg-8 mx-auto text-center">
                <span class="hero-badge fade-in-up">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <polyline points="17 11 19 13 23 9"></polyline>
                    </svg>
                    Venda à Consignação
                </span>
                <h1 class="hero-title fade-in-up" data-delay="100">{{ $data->contents['title'] }}</h1>
                <p class="hero-description fade-in-up" data-delay="200">{{ $data->contents['subtitle'] }}</p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge fade-in-up">Como Funciona</span>
            <h2 class="section-title fade-in-up" data-delay="100">Processo de Consignação</h2>
        </div>
        <div class="consignment-intro-card fade-in-up" data-delay="200">
            <div class="intro-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </div>
            <div class="intro-content text-black">
                <p class="text-black">A Izzycar oferece um serviço completo de venda à consignação, pensado para tornar o processo de venda do seu veículo simples, rápido e transparente.</p>

                <p class="text-black">Com a nossa experiência no mercado automóvel, garantimos que o seu carro é apresentado da melhor forma possível aos potenciais compradores, maximizando o seu valor de venda.</p>

                <p class="text-black">Não perca tempo com negociações intermináveis, visitas inconvenientes ou preocupações com documentação - tratamos de tudo por si!</p>
            </div>
        </div>
    </div>
</section>

<!-- Vantagens da Consignação -->
<section class="section-padding bg-light pt-4">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title-modern">Por que escolher a consignação?</h2>
            <p class="section-badge fade-in-up">Vantagens exclusivas para si</p>

        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <h5 class="benefit-title">Poupe Tempo</h5>
                    <p class="benefit-description">Tratamos de todo o processo de venda, desde a publicidade até à entrega final do veículo.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h5 class="benefit-title">Sempre na Sua Posse</h5>
                    <p class="benefit-description">O carro continua sempre com o proprietário, sem qualquer risco ou entrega a terceiros.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                    </div>
                    <h5 class="benefit-title">Sem Custos Iniciais</h5>
                    <p class="benefit-description">Não é necessário qualquer pagamento para iniciar o processo de venda.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                    </div>
                    <h5 class="benefit-title">Marketing Profissional</h5>
                    <p class="benefit-description">Fotografias profissionais e publicidade nas principais plataformas de venda.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                    </div>
                    <h5 class="benefit-title">Preparação Completa</h5>
                    <p class="benefit-description">Limpeza profissional e preparação estética do veículo para maximizar o valor.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <h5 class="benefit-title">Gestão de Documentação</h5>
                    <p class="benefit-description">Tratamos do registo de propriedade e todas as formalidades legais necessárias.</p>
                </div>
            </div>
        </div>
    </div>
</section>


<?php
$faq = [
    [
        'question' => 'O carro fica em vosso nome durante a consignação?',
        'answer' => '<strong>Não.</strong> O veículo mantém-se sempre em nome do proprietário durante todo o processo de venda.'
    ],
    [
        'question' => 'Tenho de entregar o carro à vossa guarda?',
        'answer' => 'Não. O carro permanece com o proprietário até existir um comprador interessado e negócio fechado.'
    ],
    [
        'question' => 'Posso continuar a usar o carro enquanto está à venda?',
        'answer' => 'Sim. Enquanto não existir um acordo de venda, o veículo continua disponível para uso normal do proprietário.'
    ],
    [
        'question' => 'Quem trata dos contactos e das visitas?',
        'answer' => 'Nós tratamos de todos os contactos e filtramos apenas interessados reais, evitando perdas de tempo.'
    ],
    [
        'question' => 'Como é definido o preço de venda?',
        'answer' => 'O preço é definido em conjunto com o proprietário, com base numa análise de mercado atual.'
    ],
    [
        'question' => 'Tenho algum custo se o carro não for vendido?',
        'answer' => 'Não. O nosso serviço só é pago em caso de venda efetiva do veículo.'
    ],
    [
        'question' => 'Quem trata da documentação na venda?',
        'answer' => 'Acompanhamos todo o processo de venda, garantindo uma transação segura e legal.'
    ],
    [
        'question' => 'O carro pode ser vendido sem o meu consentimento?',
        'answer' => 'Nunca. Qualquer proposta é sempre apresentada ao proprietário e só avançamos com a sua autorização.'
    ],
];
?>
<!-- Processo de Consignação -->
<section class="section-padding pt-4" id="section_faq">
    <div class="container">
        <div class="section-header text-center mb-5">
            <p class="section-badge fade-in-up">FAQ</p>
            <h2 class="section-title-modern">Perguntas Frequentes</h2>
        </div>

        <div class="faq-wrapper-modern">
            @forelse ($faq ?? [] as $faqItem)
            <div class="faq-item-modern">
                <button class="faq-question-modern" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapse-faq-{{ $loop->index + 1 }}"
                    aria-expanded="false"
                    aria-controls="collapse-faq-{{ $loop->index + 1 }}">
                    <span class="faq-number">{{ $loop->index + 1 }}</span>
                    <span class="faq-question-text">{!! $faqItem['question'] !!}</span>
                    <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div id="collapse-faq-{{ $loop->index + 1 }}" class="collapse faq-answer-collapse"
                    data-bs-parent="#section_faq">
                    <div class="faq-answer-modern">
                        <p class="text-black">{!! $faqItem['answer'] !!}</p>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-center">Nenhuma pergunta frequente disponível no momento.</p>
            @endforelse
        </div>


    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="cta-title">Pronto para vender o seu veículo?</h2>
                <p class="cta-description">Entre em contacto connosco e receba uma proposta personalizada sem compromisso.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="{{ route('frontend.contact') }}" class="btn-cta">
                    <span>Fale Connosco</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
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
        font-size: 3rem;
        font-weight: 900;
        color: #fff;
        line-height: 1.2;
        margin-bottom: 1.5rem;
    }

    .hero-description {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.8);
        line-height: 1.8;
        max-width: 700px;
        margin: 0 auto;
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

    .consignment-intro-card {
        background: white;
        border-radius: 24px;
        padding: 3rem;
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
        color: #000000;
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

    /* Benefit Cards */
    .benefit-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        height: 100%;
        border: 2px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .benefit-card:hover {
        transform: translateY(-5px);
        border-color: var(--accent-color);
        box-shadow: 0 10px 30px rgba(110, 7, 7, 0.1);
    }

    .benefit-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-bottom: 1.5rem;
    }

    .benefit-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 1rem;
    }

    .benefit-description {
        font-size: 1rem;
        line-height: 1.6;
        color: #666;
        margin: 0;
    }

    /* Step Cards */
    .consignment-step-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        height: 100%;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
    }

    .consignment-step-card:hover {
        transform: translateY(-5px);
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

    .consignment-step-card:hover .step-number {
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

    .consignment-step-card:hover .step-arrow {
        opacity: 1;
        transform: translateX(0);
    }

    /* CTA Section */
    .cta-section {
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        padding: 5rem 0;
        margin-top: 4rem;
    }

    .cta-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: white;
        margin-bottom: 1rem;
    }

    .cta-description {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
    }

    .btn-cta {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 32px;
        background: white;
        color: var(--accent-color);
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-cta:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        color: var(--accent-color);
    }

    @media (max-width: 992px) {
        .consignment-intro-card {
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

        .cta-title {
            font-size: 2rem;
        }

        .cta-description {
            font-size: 1rem;
        }
    }

    @media (max-width: 768px) {
        .consignment-intro-card {
            padding: 1.5rem;
        }

        .intro-content {
            font-size: 1rem;
        }

        .section-title-modern {
            font-size: 1.75rem;
        }

        .consignment-step-card {
            padding: 1.5rem;
        }

        .step-title {
            font-size: 1.2rem;
        }

        .hero-title {
            font-size: 2rem;
        }

        .hero-description {
            font-size: 1rem;
        }

        .cta-title {
            font-size: 1.75rem;
        }

        .cta-section {
            padding: 3rem 0;
        }
    }

    .faq-wrapper-modern {
        max-width: 900px;
        margin: 0 auto;
    }

    .faq-item-modern {
        background: white;
        border-radius: 16px;
        margin-bottom: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .faq-item-modern:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }

    .faq-question-modern {
        width: 100%;
        background: white;
        border: none;
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: left;
    }

    .faq-question-modern:hover {
        background: #f8f9fa;
    }

    .faq-number {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .faq-question-text {
        flex: 1;
        font-size: 1.1rem;
        font-weight: 600;
        color: #111;
    }

    .faq-icon {
        flex-shrink: 0;
        color: var(--accent-color);
        transition: transform 0.3s ease;
    }

    .faq-question-modern:not(.collapsed) .faq-icon {
        transform: rotate(180deg);
    }

    .faq-answer-modern {
        padding: 0 2rem 1.5rem 5rem;
        font-size: 1rem;
        line-height: 1.7;
        color: #495057;
    }

    .faq-answer-modern p {
        margin-bottom: 0.75rem;
    }

    .faq-answer-modern ul,
    .faq-answer-modern ol {
        padding-left: 1.5rem;
        margin-bottom: 0.75rem;
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