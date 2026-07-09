@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => $data->seo
])

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Service",
    "name": "Importação Automóvel Chave na Mão",
    "alternateName": "Importação de carros da Alemanha e Europa",
    "description": "Serviço completo de importação automóvel para Portugal — pesquisa, negociação, transporte, ISV, IPO, matrícula e entrega. Processo 100% gerido pela Izzycar.",
    "url": "https://izzycar.pt/importacao",
    "provider": {
        "@@type": "AutoDealer",
        "name": "Izzycar",
        "url": "https://izzycar.pt",
        "sameAs": ["https://www.facebook.com/profile.php?id=61572831810539","https://www.instagram.com/izzycarpt/"]
    },
    "areaServed": {"@@type": "Country", "name": "Portugal"},
    "serviceType": "Importação Automóvel",
    "category": "Automóvel",
    "offers": {
        "@@type": "Offer",
        "description": "Cotação personalizada de importação automóvel — peça a sua gratuitamente",
        "priceCurrency": "EUR",
        "availability": "https://schema.org/InStock",
        "url": "https://izzycar.pt/importacao"
    }
}
</script>

<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {"@@type": "ListItem", "position": 1, "name": "Início", "item": "https://izzycar.pt"},
        {"@@type": "ListItem", "position": 2, "name": "Importação Automóvel", "item": "https://izzycar.pt/importacao"}
    ]
}
</script>

@if(!empty($data->process_import['process_import']))
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "HowTo",
    "name": "Como importar um carro da Europa para Portugal",
    "description": "Processo completo passo a passo para importar um veículo da Alemanha ou Europa para Portugal, incluindo transporte, ISV, IPO e matrícula.",
    "estimatedCost": {"@@type": "MonetaryAmount", "currency": "EUR", "description": "Varia conforme o veículo e país de origem"},
    "step": [
        @foreach($data->process_import['process_import'] as $i => $step)
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

@if(!empty($faq['enum']))
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        @foreach($faq['enum'] as $i => $faqItem)
        {
            "@@type": "Question",
            "name": "{{ addslashes(strip_tags($faqItem['question'])) }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ addslashes(strip_tags($faqItem['answer'])) }}"
            }
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ]
}
</script>
@endif
@endpush

@section('content')

<!-- Hero Section Import -->
<section class="hero-import-section">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="row align-items-center min-vh-50 py-5">
            <div class="col-lg-6">
                <div class="hero-content">
                    <nav class="hero-breadcrumb" aria-label="breadcrumb">
                        <a href="{{ route('frontend.home') }}">Início</a>
                        <span>/</span>
                        <span>Importação Automóvel</span>
                    </nav>
                    <span class="hero-badge fade-in-up">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                            <path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path>
                        </svg>
                        Importação Chave na Mão
                    </span>
                    <h1 class="hero-title fade-in-up" data-delay="100">
                        {{ $data->process_import['title'] }}
                    </h1>
                    <p class="hero-description fade-in-up" data-delay="200">
                        {{ $data->process_import['subtitle'] }}
                    </p>
                    <div class="hero-actions fade-in-up" data-delay="300">
                        <a href="#form-cotação" class="btn-hero-primary" data-bs-toggle="modal" data-bs-target="#formPropostaModal">
                            <span>Pedir Cotação</span>
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
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center">
                <div class="hero-stats-card fade-in-up" data-delay="300">
                    <div class="hero-stats-card-header">
                        <div class="hero-stats-card-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                <path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path>
                            </svg>
                        </div>
                        <span>Importação Automóvel</span>
                    </div>
                    <div class="hero-stats-grid">
                        <div class="hero-stat-item">
                            <span class="hero-stat-value">30%</span>
                            <span class="hero-stat-label">Poupança média</span>
                        </div>
                        <div class="hero-stat-item">
                            <span class="hero-stat-value">3–6</span>
                            <span class="hero-stat-label">Semanas entrega</span>
                        </div>
                        <div class="hero-stat-item">
                            <span class="hero-stat-value">10+</span>
                            <span class="hero-stat-label">Países de origem</span>
                        </div>
                        <div class="hero-stat-item">
                            <span class="hero-stat-value">100%</span>
                            <span class="hero-stat-label">Processo garantido</span>
                        </div>
                    </div>
                    <div class="hero-stats-card-footer">
                        <div class="hero-stats-reviews">
                            <!-- <span class="hero-stars">★★★★★</span>
                            <span>Avaliação 5 estrelas no Google</span> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trust Badges -->
<section class="trust-badges-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="trust-badge-item fade-in-up">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    <h4>Resposta em 24h</h4>
                    <p>Cotação personalizada rápida</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="trust-badge-item fade-in-up" data-delay="100">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <h4>Inspeção de Origem</h4>
                    <p>Verificamos antes de comprar</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="trust-badge-item fade-in-up" data-delay="200">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    <h4>Sem Custos Escondidos</h4>
                    <p>Orçamento fixo e transparente</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="trust-badge-item fade-in-up" data-delay="300">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <h4>Suporte Dedicado</h4>
                    <p>Acompanhamento do início ao fim</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section-modern">
    <div class="container">
        <div class="cta-card-import">
            <div class="cta-icon-import">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path>
                </svg>
            </div>
            <div class="cta-content-import">
                <h3 class="cta-title-import">Pronto para importar?</h3>
                <p class="cta-subtitle-import">Receba uma cotação personalizada sem compromisso</p>
            </div>
            <a href="#form-cotação" class="btn-cta-import" data-bs-toggle="modal" data-bs-target="#formPropostaModal">
                Pedir Cotação
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
    </div>
</section>

<section class="section-padding" id="section_description_import">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge fade-in-up">Como Funciona</span>
            <h2 class="section-title fade-in-up" data-delay="100">Importação Simplificada</h2>
        </div>
        <div class="import-description-card fade-in-up" data-delay="200">
            <div class="import-description-content">
                {!!$data->process_import['description']!!}
            </div>
        </div>
    </div>
</section>

<section class="section-padding" id="section_why_import">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge fade-in-up">Nossas Vantagens</span>
            <h2 class="section-title fade-in-up" data-delay="100">Porque Importar com a Izzycar?</h2>
            <p class="section-description fade-in-up" data-delay="200">Vantagens que fazem a diferença na sua importação</p>
        </div>

        <div class="row g-4">
            @foreach ($why_import['enum_why_import'] as $index => $item)
            <div class="col-md-6">
                <div class="why-import-card">
                    <div class="why-import-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            @if($index % 4 == 0)
                            <polyline points="20 6 9 17 4 12"></polyline>
                            @elseif($index % 4 == 1)
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                            <path d="M2 17l10 5 10-5"></path>
                            @elseif($index % 4 == 2)
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                            @else
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            @endif
                        </svg>
                    </div>
                    <div class="why-import-content">
                        <h5 class="why-import-title">{{ $item['title'] }}</h5>
                        <div class="why-import-description">{!! $item['content'] !!}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-padding section-import-steps" id="section_import">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge fade-in-up">Passo a Passo</span>
            <h2 class="section-title fade-in-up" data-delay="100">Como Importamos o Seu Carro</h2>
            <p class="section-description fade-in-up" data-delay="200">Processo detalhado do início ao fim</p>
        </div>

        <div id="desktop-content">
            @include('frontend.partials.vertical-tabs', [
            'data' => $data->process_import['process_import'],
            'title' => "Passo a passo",
            ])
        </div>
        <div id="mobile-content">
            @include('frontend.partials.accordion-mobile', [
            'data' => $data->process_import['process_import'],
            'title' => "Passo a passo",
            ])
        </div>
    </div>
</section>

<section class="cta-inline-section">
    <div class="container">
        <div class="cta-inline-card">
            <div class="cta-inline-content">
                <h4 class="cta-inline-title">Comece a sua importação agora</h4>
                <p class="cta-inline-text">Solicite a sua cotação personalizada</p>
            </div>
            <a href="#form-cotação" class="btn-cta-inline" data-bs-toggle="modal" data-bs-target="#formPropostaModal">
                Quero Importar um Carro
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
    </div>
</section>

@if(!empty($data_custos))
<section class="section-padding bg-light" id="section_import_costs">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge fade-in-up">Preços Transparentes</span>
            <h2 class="section-title fade-in-up" data-delay="100">Custos de Importação</h2>
            <p class="section-description fade-in-up" data-delay="200">Transparência total em cada etapa do processo</p>
        </div>

        <div class="row g-4">
            @foreach ($data_custos['enum'] as $index => $cost)
            @php
            $icons = [
            '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>',
            '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
            <line x1="1" y1="10" x2="23" y2="10"></line>',
            '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
            <line x1="12" y1="22.08" x2="12" y2="12"></line>',
            '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="8.5" cy="7" r="4"></circle>
            <polyline points="17 11 19 13 23 9"></polyline>',
            '<circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>',
            '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>'
            ];
            $colors = ['#6e0707', '#0066cc', '#00a86b', '#ff6b35', '#9c27b0', '#ff9800'];
            $icon = $icons[$index % count($icons)];
            $color = $colors[$index % count($colors)];
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="cost-card-innovative" data-color="{{ $color }}">
                    <div class="cost-card-header">
                        <div class="cost-icon-wrapper" style="background: linear-gradient(135deg, {{ $color }} 0%, {{ $color }}dd 100%);">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                {!! $icon !!}
                            </svg>
                        </div>
                        <span class="cost-badge" style="background: {{ $color }};">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h5 class="cost-card-title">{{ $cost['title'] }}</h5>
                    <div class="cost-card-content">
                        {!! $cost['content'] ?? 'Informação detalhada sobre este custo' !!}
                    </div>
                    <div class="cost-card-footer">
                        <span class="cost-learn-more">
                            @if($cost['title'] == 'Inspeção, Matrícula e Legalização' || $cost['title'] == 'Valor do Transporte' || $cost['title'] == 'Honorários do Serviço' )
                            Incluído no serviço
                            @elseif($cost['title'] == 'Imposto Único de Circulação (IUC)' || $cost['title'] == 'Imposto Sobre Veículos (ISV)' )
                            Pago às autoridades portuguesas
                            @elseif($cost['title'] == 'Preço da Viatura' )
                            Direto ao vendedor
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="cta-inline-section">
    <div class="container">
        <div class="cta-inline-card">
            <div class="cta-inline-content">
                <h4 class="cta-inline-title">Quer saber quanto custa importar?</h4>
                <p class="cta-inline-text">Solicite uma simulação detalhada e personalizada</p>
            </div>
            <a href="#form-cotação" class="btn-cta-inline" data-bs-toggle="modal" data-bs-target="#formPropostaModal">
                Pedir Simulação de Custos
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
    </div>
</section>
@endif


<section class="section-padding" id="section_faq">
    <div class="container">
        <div class="section-header text-center mb-4">
            <span class="section-badge fade-in-up">FAQ</span>
            <h2 class="section-title fade-in-up" data-delay="100">Perguntas Frequentes</h2>
            <p class="section-description fade-in-up" data-delay="200">Tire as suas dúvidas sobre importação</p>
        </div>

        @if(!empty($faq['enum']))
        <div class="faq-controls text-center mb-4">
            <button type="button" id="faq-toggle-all" class="btn-faq-toggle" data-expanded="false">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
                <span>Expandir Tudo</span>
            </button>
        </div>
        @endif

        <div class="faq-wrapper-modern" id="faq-list">
            @forelse ($faq['enum'] ?? [] as $faqItem)
            <div class="faq-item-modern">
                <button class="faq-question-modern" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapse-faq-{{ $loop->index }}"
                    aria-expanded="false"
                    aria-controls="collapse-faq-{{ $loop->index }}">
                    <span class="faq-number">{{ str_pad($faqItem['order'], 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="faq-question-text">{{ strip_tags($faqItem['question']) }}</span>
                    <svg class="faq-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div id="collapse-faq-{{ $loop->index }}" class="collapse faq-answer-collapse">
                    <div class="faq-answer-modern">
                        {!! $faqItem['answer'] !!}
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <p>Sem FAQs disponíveis.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>


@include('frontend.partials.import-form-modal', [
'brands' => $brands,
])

@endsection

@push('styles')
<style>
    /* Hero Import Section */
    .hero-import-section {
        position: relative;
        background: linear-gradient(135deg, #111111 0%, #1a1a1a 50%, #111111 100%);
        overflow: hidden;
        padding: 4rem 0 2rem;
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

    .min-vh-75 {
        min-height: 75vh;
    }
    .min-vh-50 {
        min-height: 50vh;
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero-breadcrumb {
        display: flex; align-items: center; gap: .5rem;
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
        font-size: 3rem;
        font-weight: 900;
        color: #fff;
        line-height: 1.2;
        margin-bottom: 1.5rem;
    }

    .hero-description {
        font-size: 1.2rem;
        color: rgba(255,255,255,0.8);
        line-height: 1.8;
        margin-bottom: 2.5rem;
        max-width: 600px;
    }

    .hero-actions {
        display: flex;
        gap: 1rem;
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
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-hero-secondary:hover {
        background: rgba(255,255,255,0.1);
        border-color: white;
        color: white;
    }

    /* Hero Stats Card */
    .hero-stats-card {
        background: rgba(255,255,255,0.06);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 28px;
        padding: 2.5rem;
        width: 100%;
        max-width: 380px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.04);
        position: relative;
        animation: floatCard 5s ease-in-out infinite;
    }

    .hero-stats-card::before {
        content: '';
        position: absolute;
        top: -1px;
        left: 30px;
        right: 30px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        pointer-events: none;
    }

    @keyframes floatCard {
        0%, 100% { transform: translateY(0px); }
        50%       { transform: translateY(-12px); }
    }

    .hero-stats-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.75rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .hero-stats-card-icon {
        flex-shrink: 0;
        width: 46px;
        height: 46px;
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        box-shadow: 0 4px 14px rgba(153,0,0,0.45);
    }

    .hero-stats-card-header > span {
        color: rgba(255,255,255,0.92);
        font-weight: 700;
        font-size: 1rem;
    }

    .hero-stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .hero-stat-item {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 14px;
        padding: 1.25rem 1rem;
        text-align: center;
        transition: all 0.3s ease;
    }

    .hero-stat-item:hover {
        background: rgba(255,255,255,0.09);
        border-color: rgba(153,0,0,0.4);
    }

    .hero-stat-value {
        display: block;
        font-size: 1.8rem;
        font-weight: 900;
        color: #fff;
        line-height: 1;
        margin-bottom: 0.4rem;
        letter-spacing: -0.02em;
    }

    .hero-stat-label {
        font-size: 0.75rem;
        color: rgba(255,255,255,0.6);
        font-weight: 500;
        line-height: 1.3;
    }

    .hero-stats-card-footer {
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .hero-stats-reviews {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .hero-stars {
        color: #fbbf24;
        font-size: 1rem;
        letter-spacing: 2px;
    }

    .hero-stats-reviews > span:last-child {
        font-size: 0.85rem;
        color: rgba(255,255,255,0.7);
        font-weight: 500;
    }

    /* Trust Badges */
    .trust-badges-section {
        padding: 3rem 0;
        background: linear-gradient(135deg, #0d0d0d 0%, #111111 50%, #0d0d0d 100%);
    }

    .trust-badge-item {
        text-align: center;
        padding: 2rem 1.5rem;
        background: rgba(255,255,255,0.04);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 16px;
        height: 100%;
        transition: all 0.3s ease;
    }

    .trust-badge-item:hover {
        background: rgba(255,255,255,0.08);
        border-color: rgba(153,0,0,0.4);
        box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 0 0 1px rgba(153,0,0,0.2);
        transform: translateY(-4px);
    }

    .trust-badge-item svg {
        color: var(--accent-color);
        margin-bottom: 1rem;
    }

    .trust-badge-item h4 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 0.5rem;
    }

    .trust-badge-item p {
        font-size: 0.9rem;
        color: rgba(255,255,255,0.6);
        margin: 0;
    }

    /* Section Headers */
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

    /* Animations */
    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards;
    }

    .fade-in-up[data-delay="100"] { animation-delay: 0.1s; }
    .fade-in-up[data-delay="200"] { animation-delay: 0.2s; }
    .fade-in-up[data-delay="300"] { animation-delay: 0.3s; }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* CTA Section Modern */
    .cta-section-modern {
        padding: 3rem 0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .cta-card-import {
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        border-radius: 24px;
        padding: 2.5rem;
        display: flex;
        align-items: center;
        gap: 2rem;
        /* box-shadow: 0 20px 60px rgba(110, 7, 7, 0.3); */
    }

    .cta-icon-import {
        flex-shrink: 0;
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .cta-content-import {
        flex: 1;
    }

    .cta-title-import {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.5rem;
    }

    .cta-subtitle-import {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
    }

    .btn-cta-import {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 1rem 2rem;
        background: white;
        color: #111;
        font-weight: 700;
        font-size: 1.05rem;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        white-space: nowrap;
    }

    .btn-cta-import:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        color: #111;
    }

    .btn-cta-import svg {
        transition: transform 0.3s ease;
    }

    .btn-cta-import:hover svg {
        transform: translateX(5px);
    }

    /* Import Description */
    /* Como Funciona section */
    #section_description_import {
        background: linear-gradient(160deg, #f7f7f7 0%, #fafafa 60%, #f5f5f5 100%);
        position: relative;
    }

    .import-description-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 3rem;
        box-shadow: 0 6px 30px rgba(0,0,0,0.07);
        border: 1px solid rgba(0,0,0,0.05);
        border-top: 4px solid #990000;
        position: relative;
        overflow: hidden;
    }

    .import-description-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: radial-gradient(ellipse at top right, rgba(153,0,0,0.04) 0%, transparent 70%);
        pointer-events: none;
    }

    .import-description-content {
        font-size: 1.05rem;
        line-height: 1.85;
        color: #333;
    }

    .import-description-content p {
        margin-bottom: 1rem;
    }

    .import-description-content ul {
        list-style: none;
        padding-left: 0;
        margin: 1.5rem 0;
    }

    .import-description-content ul li {
        position: relative;
        padding-left: 2rem;
        margin-bottom: 0.75rem;
        color: #333;
    }

    .import-description-content ul li:before {
        content: "";
        position: absolute;
        left: 0;
        top: 0.55rem;
        width: 9px;
        height: 9px;
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(153,0,0,0.35);
    }

    /* Section Header Import */
    .section-header-import {
        text-align: center;
        margin-bottom: 3rem;
    }

    .section-title-import {
        font-size: 2.5rem;
        font-weight: 800;
        color: #111;
        margin-bottom: 1rem;
    }

    .section-subtitle-import {
        font-size: 1.1rem;
        color: #6c757d;
        margin: 0;
    }

    /* Why Import Section */
    #section_why_import {
        background: linear-gradient(160deg, #f7f7f7 0%, #fafafa 50%, #fff7f7 100%);
        position: relative;
    }

    #section_why_import::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 300px;
        height: 300px;
        background: radial-gradient(ellipse at center, rgba(153,0,0,0.05) 0%, transparent 70%);
        pointer-events: none;
    }

    /* Why Import Cards */
    .why-import-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 2rem;
        height: 100%;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        transition: all 0.3s ease;
        display: flex;
        gap: 1.5rem;
        border: 1px solid rgba(0,0,0,0.06);
        border-left: 3px solid transparent;
    }

    .why-import-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 36px rgba(0,0,0,0.11);
        border-left-color: #990000;
    }

    .why-import-icon {
        flex-shrink: 0;
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 16px rgba(110,7,7,0.35);
        transition: all 0.3s ease;
    }

    .why-import-card:hover .why-import-icon {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 6px 22px rgba(110,7,7,0.5);
    }

    .why-import-content {
        flex: 1;
    }

    .why-import-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 0.75rem;
    }

    .why-import-description {
        font-size: 1rem;
        line-height: 1.7;
        color: #495057;
    }

    .why-import-description p {
        margin-bottom: 0.5rem;
    }

    /* CTA Inline */
    .cta-inline-section {
        padding: 3rem 0;
    }

    .cta-inline-card {
        background: linear-gradient(135deg, #111111 0%, #2a2a2a 100%);
        border-radius: 24px;
        padding: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
    }

    .cta-inline-content {
        flex: 1;
    }

    .cta-inline-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.5rem;
    }

    .cta-inline-text {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.8);
        margin: 0;
    }

    .btn-cta-inline {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        color: white;
        font-weight: 700;
        font-size: 1rem;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(110, 7, 7, 0.3);
        white-space: nowrap;
    }

    .btn-cta-inline:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(110, 7, 7, 0.4);
        color: white;
    }

    .btn-cta-inline svg {
        transition: transform 0.3s ease;
    }

    .btn-cta-inline:hover svg {
        transform: translateX(5px);
    }

    /* Section Como Importamos */
    .section-import-steps {
        background: linear-gradient(160deg, #ffffff 0%, #f9f9f9 50%, #f5f5f5 100%);
    }

    /* INNOVATIVE COST CARDS */
    .cost-card-innovative {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        height: 100%;
        /* box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); */
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
        display: flex;
        flex-direction: column;
    }

    .cost-card-innovative::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--accent-color) 0%, #990000 100%);
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }

    .cost-card-innovative:hover::before {
        transform: scaleX(1);
    }

    .cost-card-innovative:hover {
        transform: translateY(-10px) scale(1.02);
        /* box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15); */
        border-color: var(--accent-color);
    }

    .cost-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }

    .cost-icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transition: all 0.4s ease;
    }

    .cost-card-innovative:hover .cost-icon-wrapper {
        transform: rotateY(180deg) scale(1.1);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
    }

    .cost-badge {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 800;
        font-size: 1.1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .cost-card-innovative:hover .cost-badge {
        transform: scale(1.15) rotate(5deg);
    }

    .cost-card-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 1rem;
        line-height: 1.3;
    }

    .cost-card-content {
        font-size: 1rem;
        line-height: 1.7;
        color: #495057;
        margin-bottom: 1.5rem;
    }

    .cost-card-content p {
        margin-bottom: 0.5rem;
    }

    .cost-card-footer {
        padding-top: 1rem;
        border-top: 2px solid #f0f0f0;
        transition: border-color 0.3s ease;
        margin-top: auto;
    }

    .cost-card-innovative:hover .cost-card-footer {
        border-color: var(--accent-color);
    }

    .cost-learn-more {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--accent-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* FAQ Modern */
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

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6c757d;
    }

    .empty-state svg {
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 1.1rem;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .cta-card-import {
            flex-direction: column;
            text-align: center;
            padding: 2rem;
        }

        .cta-icon-import {
            width: 64px;
            height: 64px;
        }

        .cta-title-import {
            font-size: 1.5rem;
        }

        .section-title-import {
            font-size: 2rem;
        }

        .why-import-card {
            flex-direction: column;
            text-align: center;
        }

        .cta-inline-card {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (max-width: 768px) {
        .cta-section-modern {
            padding: 2rem 0;
        }

        .cta-card-import {
            padding: 1.5rem;
        }

        .cta-title-import {
            font-size: 1.25rem;
        }

        .import-description-card {
            padding: 2rem;
        }

        .import-description-content {
            font-size: 1rem;
        }

        .section-title-import {
            font-size: 1.75rem;
        }

        .section-subtitle-import {
            font-size: 1rem;
        }

        .why-import-icon {
            width: 56px;
            height: 56px;
        }

        .why-import-icon svg {
            width: 24px;
            height: 24px;
        }

        .why-import-title {
            font-size: 1.2rem;
        }

        .cta-inline-card {
            padding: 1.5rem;
        }

        .cta-inline-title {
            font-size: 1.25rem;
        }

        .btn-cta-inline {
            width: 100%;
            justify-content: center;
        }

        .cost-card-innovative {
            padding: 1.5rem;
        }

        .cost-icon-wrapper {
            width: 60px;
            height: 60px;
        }

        .cost-badge {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .cost-card-title {
            font-size: 1.2rem;
        }

        .faq-question-modern {
            padding: 1.25rem 1.5rem;
        }

        .faq-number {
            width: 36px;
            height: 36px;
            font-size: 0.85rem;
        }

        .faq-question-text {
            font-size: 1rem;
        }

        .faq-answer-modern {
            padding: 0 1.5rem 1.25rem 4.5rem;
            font-size: 0.95rem;
        }
    }

    /* FAQ Toggle Button */
    .btn-faq-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.625rem 1.5rem;
        background: transparent;
        color: #555;
        font-size: 0.9rem;
        font-weight: 600;
        border: 1px solid #ddd;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-faq-toggle:hover {
        background: rgba(110, 7, 7, 0.08);
        border-color: rgba(110, 7, 7, 0.4);
        color: #6e0707;
    }

    .btn-faq-toggle svg {
        transition: transform 0.3s ease;
    }

    .btn-faq-toggle[data-expanded="true"] svg {
        transform: rotate(180deg);
    }
</style>
@endpush

<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

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

    function handleResponsiveTabs() {
        const desktopDivPassos = document.getElementById('desktop-content');
        const mobileDivPassos = document.getElementById('mobile-content');

        if (window.innerWidth >= 769) {
            desktopDivPassos.style.display = 'block';
            mobileDivPassos.style.display = 'none';
        } else {
            desktopDivPassos.style.display = 'none';
            mobileDivPassos.style.display = 'block';
        }

        const desktopDivCustos = document.getElementById('desktop-content-custos');
        const mobileDivCustos = document.getElementById('mobile-content-custos');

        if (window.innerWidth >= 769) {
            desktopDivCustos.style.display = 'block';
            mobileDivCustos.style.display = 'none';
        } else {
            desktopDivCustos.style.display = 'none';
            mobileDivCustos.style.display = 'block';
        }
    }

    // Inicializa no load
    document.addEventListener('DOMContentLoaded', handleResponsiveTabs);

    // Atualiza se a janela for redimensionada
    window.addEventListener('resize', handleResponsiveTabs);


    const swiper = new Swiper('.mySwiper', {
        slidesPerView: 1,
        spaceBetween: 10,
        loop: false,
        watchOverflow: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
            disabledClass: 'swiper-button-disabled'
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            768: {
                slidesPerView: 2,
            },
            992: {
                slidesPerView: 3,
            }
        }
    });

    // FAQ Expandir/Fechar Tudo — corre após Bootstrap estar disponível
    window.addEventListener('load', function() {
        const faqToggleBtn = document.getElementById('faq-toggle-all');
        if (!faqToggleBtn) return;

        faqToggleBtn.addEventListener('click', function() {
            const expanded = this.dataset.expanded === 'true';
            const collapses = document.querySelectorAll('#faq-list .faq-answer-collapse');

            collapses.forEach(function(el) {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                expanded ? bsCollapse.hide() : bsCollapse.show();
            });

            this.dataset.expanded = expanded ? 'false' : 'true';
            this.querySelector('span').textContent = expanded ? 'Expandir Tudo' : 'Fechar Tudo';
        });

        // Sincroniza o botão quando um item muda individualmente
        const syncBtn = function() {
            const allOpen = Array.from(document.querySelectorAll('#faq-list .faq-answer-collapse'))
                .every(function(c) { return c.classList.contains('show'); });
            faqToggleBtn.dataset.expanded = allOpen ? 'true' : 'false';
            faqToggleBtn.querySelector('span').textContent = allOpen ? 'Fechar Tudo' : 'Expandir Tudo';
        };

        document.querySelectorAll('#faq-list .faq-answer-collapse').forEach(function(el) {
            el.addEventListener('shown.bs.collapse', syncBtn);
            el.addEventListener('hidden.bs.collapse', syncBtn);
        });
    });
</script>