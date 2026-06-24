@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => $data->seo
])

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Service",
    "name": "Venda de Automóvel em Consignação",
    "alternateName": "Consignação automóvel Portugal",
    "description": "A Izzycar gere a venda do seu carro em consignação: avaliação, exposição, negociação e formalização — sem stress e com o máximo valor para o seu veículo.",
    "url": "https://izzycar.pt/consignacao",
    "provider": {
        "@@type": "AutoDealer",
        "name": "Izzycar",
        "url": "https://izzycar.pt"
    },
    "areaServed": { "@@type": "Country", "name": "Portugal" },
    "serviceType": "Consignação Automóvel",
    "category": "Automóvel",
    "breadcrumb": {
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {"@@type": "ListItem", "position": 1, "name": "Início", "item": "https://izzycar.pt"},
            {"@@type": "ListItem", "position": 2, "name": "Consignação Automóvel", "item": "https://izzycar.pt/consignacao"}
        ]
    }
}
</script>
@endpush

@section('content')

{{-- ── HERO ── --}}
<section class="cs-hero">
    <div class="cs-hero-overlay"></div>
    <div class="container">
        <div class="row">
            <div class="col-lg-7">
                <nav class="hero-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">Início</a>
                    <span>/</span>
                    <span>Consignação Automóvel</span>
                </nav>
                <span class="cs-badge fade-in-up">
                    <i class="bi bi-handshake"></i>
                    Venda à Consignação
                </span>
                <h1 class="cs-hero-title fade-in-up" data-delay="100">Venda o Seu Carro <span class="cs-hero-accent">à Consignação</span></h1>
                <p class="cs-hero-sub fade-in-up" data-delay="200">{{ $data->contents['subtitle'] }}</p>
                <div class="cs-hero-actions fade-in-up" data-delay="300">
                    <a href="#como-funciona" class="cs-btn-primary">
                        <i class="bi bi-arrow-down-circle"></i> Ver como funciona
                    </a>
                    <!-- <a href="{{ route('frontend.contact') }}" class="cs-btn-outline">
                        <i class="bi bi-telephone"></i> Falar Connosco
                    </a> -->
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── O QUE É ── --}}
<section class="cs-section">
    <div class="container">
        <div class="cs-what-grid">
            <div class="cs-what-text">
                <span class="cs-label">O que é</span>
                <h2 class="cs-section-title">Consignação de Venda de Veículos</h2>
                <p class="cs-lead">A consignação automóvel é uma solução para quem pretende vender o seu veículo sem ter de lidar com anúncios, contactos, visitas, negociações ou burocracias.</p>
                <p class="cs-body">Ao colocar o seu carro em consignação, <strong>continua a ser o proprietário do veículo</strong> enquanto nós tratamos de todo o processo de venda em seu nome.</p>
                <a href="{{ route('consignment.evaluation') }}" class="cs-btn-primary mt-4">
                    Avaliação sem compromisso <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="cs-what-cards">
                <div class="cs-stat-card">
                    <div class="cs-stat-icon"><i class="bi bi-key-fill"></i></div>
                    <div>
                        <div class="cs-stat-value">Sempre seu</div>
                        <div class="cs-stat-label">O carro fica consigo</div>
                    </div>
                </div>
                <div class="cs-stat-card">
                    <div class="cs-stat-icon"><i class="bi bi-currency-euro"></i></div>
                    <div>
                        <div class="cs-stat-value">0€ inicial</div>
                        <div class="cs-stat-label">Só paga quando vende</div>
                    </div>
                </div>
                <div class="cs-stat-card">
                    <div class="cs-stat-icon"><i class="bi bi-shield-check"></i></div>
                    <div>
                        <div class="cs-stat-value">100% seguro</div>
                        <div class="cs-stat-label">Negociação profissional</div>
                    </div>
                </div>
                <div class="cs-stat-card">
                    <div class="cs-stat-icon"><i class="bi bi-megaphone-fill"></i></div>
                    <div>
                        <div class="cs-stat-value">Máx. exposição</div>
                        <div class="cs-stat-label">Todos os portais + redes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── COMO FUNCIONA ── --}}
<section class="cs-section cs-section-alt" id="como-funciona">
    <div class="container">
        <div class="text-center mb-5">
            <span class="cs-label">Processo</span>
            <h2 class="cs-section-title">Como funciona?</h2>
            <p class="cs-section-sub">Seis passos simples do início ao fim — tratamos de tudo por si.</p>
        </div>

        <div class="cs-steps">

            <div class="cs-step">
                <div class="cs-step-num">1</div>
                <div class="cs-step-body">
                    <div class="cs-step-icon"><i class="bi bi-search"></i></div>
                    <div>
                        <h3 class="cs-step-title">Avaliação do veículo</h3>
                        <p class="cs-step-desc">Analisamos o estado geral, histórico, quilometragem e valor de mercado para definir um preço competitivo.</p>
                    </div>
                </div>
            </div>

            <div class="cs-step">
                <div class="cs-step-num">2</div>
                <div class="cs-step-body">
                    <div class="cs-step-icon"><i class="bi bi-file-earmark-text"></i></div>
                    <div>
                        <h3 class="cs-step-title">Assinatura do contrato de consignação</h3>
                        <p class="cs-step-desc">É celebrado um acordo onde ficam definidos os termos da venda, o valor pretendido e as condições do serviço.</p>
                    </div>
                </div>
            </div>

            <div class="cs-step">
                <div class="cs-step-num">3</div>
                <div class="cs-step-body">
                    <div class="cs-step-icon"><i class="bi bi-camera2"></i></div>
                    <div>
                        <h3 class="cs-step-title">Promoção do veículo</h3>
                        <p class="cs-step-desc">Tratamos da preparação do anúncio, sessão fotográfica, divulgação nos principais portais automóveis e redes sociais.</p>
                    </div>
                </div>
            </div>

            <div class="cs-step">
                <div class="cs-step-num">4</div>
                <div class="cs-step-body">
                    <div class="cs-step-icon"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <h3 class="cs-step-title">Gestão dos potenciais compradores</h3>
                        <p class="cs-step-desc">Recebemos contactos, esclarecemos dúvidas, agendamos visitas e acompanhamos eventuais testes de condução.</p>
                    </div>
                </div>
            </div>

            <div class="cs-step">
                <div class="cs-step-num">5</div>
                <div class="cs-step-body">
                    <div class="cs-step-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <div>
                        <h3 class="cs-step-title">Negociação e venda</h3>
                        <p class="cs-step-desc">Conduzimos todo o processo de negociação procurando obter o melhor valor possível para o seu veículo.</p>
                    </div>
                </div>
            </div>

            <div class="cs-step">
                <div class="cs-step-num">6</div>
                <div class="cs-step-body">
                    <div class="cs-step-icon"><i class="bi bi-check2-circle"></i></div>
                    <div>
                        <h3 class="cs-step-title">Tratamento da documentação</h3>
                        <p class="cs-step-desc">Após a venda, tratamos da documentação necessária para garantir uma transação segura e transparente.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ── VANTAGENS ── --}}
<section class="cs-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="cs-label">Benefícios</span>
            <h2 class="cs-section-title">Quais as vantagens?</h2>
        </div>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <div class="cs-benefit">
                    <i class="bi bi-clock cs-benefit-icon"></i>
                    <div>
                        <h5 class="cs-benefit-title">Não perde tempo</h5>
                        <p class="cs-benefit-desc">Sem anúncios, chamadas ou visitas de curiosos a gerir.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="cs-benefit">
                    <i class="bi bi-eye-slash cs-benefit-icon"></i>
                    <div>
                        <h5 class="cs-benefit-title">Sem visitas indesejadas</h5>
                        <p class="cs-benefit-desc">Filtramos apenas os compradores realmente interessados.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="cs-benefit">
                    <i class="bi bi-broadcast cs-benefit-icon"></i>
                    <div>
                        <h5 class="cs-benefit-title">Maior exposição</h5>
                        <p class="cs-benefit-desc">Divulgação em todos os portais automóveis e redes sociais.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="cs-benefit">
                    <i class="bi bi-person-check cs-benefit-icon"></i>
                    <div>
                        <h5 class="cs-benefit-title">Apoio profissional</h5>
                        <p class="cs-benefit-desc">Acompanhamento especializado em todo o processo de venda.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="cs-benefit">
                    <i class="bi bi-shield-lock cs-benefit-icon"></i>
                    <div>
                        <h5 class="cs-benefit-title">Segurança na negociação</h5>
                        <p class="cs-benefit-desc">Nunca avançamos sem a sua autorização. Decisão sempre sua.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="cs-benefit">
                    <i class="bi bi-file-earmark-check cs-benefit-icon"></i>
                    <div>
                        <h5 class="cs-benefit-title">Documentação tratada</h5>
                        <p class="cs-benefit-desc">Tratamos de todas as formalidades legais após a venda.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── QUANTO CUSTA ── --}}
<section class="cs-section cs-section-alt">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="cs-price-card">
                    <div class="cs-price-icon"><i class="bi bi-currency-euro"></i></div>
                    <div>
                        <span class="cs-label">Transparência total</span>
                        <h2 class="cs-section-title mt-1 mb-3">Quanto custa?</h2>
                        <p class="cs-lead mb-0">O serviço de consignação é <strong>remunerado apenas quando o veículo é vendido</strong>, através de uma comissão previamente acordada e totalmente transparente.</p>
                        <div class="cs-price-highlight mt-4">
                            <i class="bi bi-check-circle-fill"></i>
                            Sem custos iniciais &nbsp;·&nbsp;
                            <i class="bi bi-check-circle-fill"></i>
                            Comissão acordada &nbsp;·&nbsp;
                            <i class="bi bi-check-circle-fill"></i>
                            100% transparente
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="cs-cta">
    <div class="container">
        <div class="row align-items-center gy-4">
            <div class="col-lg-7">
                <h2 class="cs-cta-title">Quer vender o seu carro?</h2>
                <p class="cs-cta-sub">Entre em contacto connosco para uma avaliação sem compromisso. Teremos todo o gosto em explicar como podemos ajudá-lo a vender o seu veículo de forma simples, segura e sem preocupações.</p>
            </div>
            <div class="col-lg-5 d-flex flex-column flex-sm-row gap-3 justify-content-lg-end">
                <a href="{{ route('consignment.evaluation') }}" class="cs-cta-btn-primary">
                    <i class="bi bi-envelope-fill"></i> Pedir Avaliação
                </a>
                <a href="https://wa.me/351910000000" class="cs-cta-btn-whatsapp" target="_blank" rel="noopener">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
    :root { --cs-accent: #6e0707; --cs-accent2: #990000; }

    /* ── Shared ── */
    .cs-label {
        display: inline-block;
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--cs-accent);
        background: rgba(110,7,7,.08);
        padding: 4px 14px;
        border-radius: 50px;
        margin-bottom: .75rem;
    }
    .cs-section-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: #111;
        line-height: 1.2;
        margin-bottom: .5rem;
    }
    .cs-section-sub {
        font-size: 1.1rem;
        color: #6b7280;
        margin-top: .5rem;
    }
    .cs-lead  { font-size: 1.1rem; color: #374151; line-height: 1.8; }
    .cs-body  { font-size: 1rem;   color: #6b7280;  line-height: 1.8; }
    .cs-section     { padding: 5rem 0; }
    .cs-section-alt { padding: 5rem 0; background: #f4f4f6; }

    /* ── Buttons ── */
    .cs-btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--cs-accent); color: #fff;
        padding: 14px 28px; border-radius: 50px;
        font-weight: 700; font-size: .95rem;
        text-decoration: none; transition: .25s;
    }
    .cs-btn-primary:hover { background: var(--cs-accent2); color: #fff; transform: translateY(-2px); }
    .cs-btn-outline {
        display: inline-flex; align-items: center; gap: 8px;
        border: 2px solid rgba(255,255,255,.4); color: #fff;
        padding: 13px 28px; border-radius: 50px;
        font-weight: 700; font-size: .95rem;
        text-decoration: none; transition: .25s;
    }
    .cs-btn-outline:hover { border-color: #fff; background: rgba(255,255,255,.1); color: #fff; }

    /* ── Hero ── */
    .cs-hero {
        position: relative;
        background: linear-gradient(135deg, #111 0%, #1a1a1a 60%, #111 100%);
        padding: 5rem 0 4rem;
        overflow: hidden;
    }
    .cs-hero-overlay {
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%236e0707' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: .5;
    }
    .hero-breadcrumb {
        display: flex; align-items: center; gap: .5rem;
        font-size: .82rem; color: rgba(255,255,255,.45);
        margin-bottom: 1rem; justify-content: flex-start;
    }
    .hero-breadcrumb a { color: rgba(255,255,255,.45); text-decoration: none; }
    .hero-breadcrumb a:hover { color: #fff; }
    .hero-breadcrumb span { color: rgba(255,255,255,.25); }
    .hero-breadcrumb span:last-child { color: rgba(255,255,255,.7); }

    .cs-badge {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px;
        background: rgba(110,7,7,.25); border: 1px solid rgba(110,7,7,.4);
        border-radius: 50px; color: #fff;
        font-size: .88rem; font-weight: 600; margin-bottom: 1.5rem;
    }
    .cs-hero-title {
        font-size: clamp(1.8rem, 4.5vw, 3rem); font-weight: 900; color: #fff;
        line-height: 1.15; margin-bottom: 1.25rem; white-space: nowrap;
    }
    .cs-hero-accent { color: var(--accent-color); }
    .cs-hero-sub {
        font-size: 1.15rem; color: rgba(255,255,255,.75);
        line-height: 1.8; max-width: 640px; margin: 0 0 2rem;
    }
    .cs-hero-actions { display: flex; gap: 1rem; justify-content: flex-start; flex-wrap: wrap; }

    /* ── What grid ── */
    .cs-what-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: center;
    }
    .cs-what-cards {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .cs-stat-card {
        background: #fff;
        border: 1.5px solid #e9ecef;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: .25s;
    }
    .cs-stat-card:hover { border-color: var(--cs-accent); transform: translateY(-3px); }
    .cs-stat-icon {
        width: 48px; height: 48px;
        background: rgba(110,7,7,.08); border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem; color: var(--cs-accent);
        flex-shrink: 0;
    }
    .cs-stat-value { font-size: 1rem; font-weight: 800; color: #111; }
    .cs-stat-label { font-size: .8rem; color: #9ca3af; }

    /* ── Steps timeline ── */
    .cs-steps {
        max-width: 780px;
        margin: 0 auto;
        position: relative;
    }
    .cs-steps::before {
        content: '';
        position: absolute;
        left: 28px;
        top: 0; bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--cs-accent), rgba(110,7,7,.1));
    }
    .cs-step {
        display: flex;
        gap: 1.5rem;
        align-items: flex-start;
        padding-bottom: 2.5rem;
        position: relative;
    }
    .cs-step:last-child { padding-bottom: 0; }
    .cs-step-num {
        flex-shrink: 0;
        width: 58px; height: 58px;
        background: var(--cs-accent);
        color: #fff;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; font-weight: 900;
        position: relative; z-index: 1;
        box-shadow: 0 0 0 6px #f4f4f6;
    }
    .cs-step-body {
        flex: 1;
        background: #fff;
        border: 1.5px solid #e9ecef;
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        transition: .25s;
    }
    .cs-step-body:hover { border-color: var(--cs-accent); transform: translateX(4px); }
    .cs-step-icon {
        flex-shrink: 0;
        width: 44px; height: 44px;
        background: rgba(110,7,7,.08);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; color: var(--cs-accent);
    }
    .cs-step-title { font-size: 1.1rem; font-weight: 700; color: #111; margin-bottom: .4rem; }
    .cs-step-desc  { font-size: .95rem; color: #6b7280; line-height: 1.7; margin: 0; }

    /* ── Benefits ── */
    .cs-benefit {
        background: #fff;
        border: 1.5px solid #e9ecef;
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        height: 100%;
        transition: .25s;
    }
    .cs-benefit:hover { border-color: var(--cs-accent); transform: translateY(-3px); box-shadow: 0 8px 24px rgba(110,7,7,.08); }
    .cs-benefit-icon {
        font-size: 1.6rem;
        color: var(--cs-accent);
        flex-shrink: 0;
        margin-top: 2px;
    }
    .cs-benefit-title { font-size: 1rem; font-weight: 700; color: #111; margin-bottom: .35rem; }
    .cs-benefit-desc  { font-size: .9rem; color: #6b7280; line-height: 1.6; margin: 0; }

    /* ── Price card ── */
    .cs-price-card {
        background: #fff;
        border: 2px solid var(--cs-accent);
        border-radius: 24px;
        padding: 3rem;
        display: flex;
        gap: 2rem;
        align-items: flex-start;
    }
    .cs-price-icon {
        flex-shrink: 0;
        width: 72px; height: 72px;
        background: linear-gradient(135deg, var(--cs-accent) 0%, var(--cs-accent2) 100%);
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: #fff;
    }
    .cs-price-highlight {
        display: inline-flex; align-items: center; flex-wrap: wrap; gap: 6px;
        padding: 12px 20px;
        background: rgba(110,7,7,.06);
        border-radius: 12px;
        font-size: .9rem; font-weight: 600; color: var(--cs-accent);
    }
    .cs-price-highlight .bi { font-size: .85rem; }

    /* ── CTA ── */
    .cs-cta {
        background: linear-gradient(135deg, var(--cs-accent) 0%, var(--cs-accent2) 100%);
        padding: 5rem 0;
        margin-top: 0;
    }
    .cs-cta-title { font-size: 2.2rem; font-weight: 800; color: #fff; margin-bottom: .75rem; }
    .cs-cta-sub   { font-size: 1.05rem; color: rgba(255,255,255,.85); line-height: 1.75; margin: 0; }
    .cs-cta-btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        background: #fff; color: var(--cs-accent);
        padding: 15px 28px; border-radius: 50px;
        font-weight: 700; font-size: .95rem;
        text-decoration: none; transition: .25s;
        white-space: nowrap;
    }
    .cs-cta-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.25); color: var(--cs-accent); }
    .cs-cta-btn-whatsapp {
        display: inline-flex; align-items: center; gap: 8px;
        background: #25d366; color: #fff;
        padding: 15px 28px; border-radius: 50px;
        font-weight: 700; font-size: .95rem;
        text-decoration: none; transition: .25s;
        white-space: nowrap;
    }
    .cs-cta-btn-whatsapp:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.25); color: #fff; }

    /* ── Fade in ── */
    .fade-in-up {
        opacity: 0; transform: translateY(28px);
        animation: csFadeUp .7s ease forwards;
    }
    .fade-in-up[data-delay="100"] { animation-delay: .1s; }
    .fade-in-up[data-delay="200"] { animation-delay: .2s; }
    .fade-in-up[data-delay="300"] { animation-delay: .3s; }
    @keyframes csFadeUp { to { opacity: 1; transform: translateY(0); } }

    /* ── Responsive ── */
    @media (max-width: 992px) {
        .cs-what-grid { grid-template-columns: 1fr; gap: 2.5rem; }
        .cs-hero-title { font-size: 2rem; white-space: normal; }
        .cs-section-title { font-size: 1.85rem; }
        .cs-price-card { flex-direction: column; padding: 2rem; }
    }
    @media (max-width: 768px) {
        .cs-hero { padding: 3.5rem 0 3rem; }
        .cs-hero-title { font-size: 1.5rem; white-space: normal; }
        .cs-hero-sub   { font-size: 1rem; }
        .cs-section { padding: 3.5rem 0; }
        .cs-section-alt { padding: 3.5rem 0; }
        .cs-what-cards { grid-template-columns: 1fr 1fr; }
        .cs-steps::before { left: 22px; }
        .cs-step-num { width: 46px; height: 46px; font-size: 1rem; }
        .cs-step-body { padding: 1.25rem; }
        .cs-cta { padding: 3.5rem 0; }
        .cs-cta-title { font-size: 1.75rem; }
    }
</style>
@endpush

@push('scripts')
<script>
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.style.animationPlayState = 'running';
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.fade-in-up').forEach(el => {
        el.style.animationPlayState = 'paused';
        io.observe(el);
    });
</script>
@endpush
