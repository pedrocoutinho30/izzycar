@extends('frontend.partials.layout')

@section('title', 'Izzycar - Politicas de Privacidade')

@section('content')
@php 
$settings = \App\Models\Setting::all();
@endphp

@include('frontend.partials.hero-section', ['title' => 'Política de Privacidade', 'subtitle' => 'Transparência no tratamento dos seus dados pessoais'])

<section class="section-padding">
    <div class="container">
        <div class="legal-intro-card">
            <div class="legal-intro-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <div class="legal-intro-text">
                <p>Na Izzycar respeitamos a sua privacidade e estamos empenhados em proteger os seus dados pessoais. Esta Política explica como recolhemos, utilizamos e salvaguardamos a informação que nos fornece.</p>
            </div>
        </div>

        <div class="legal-content-wrapper">
            <div class="legal-section">
                <div class="legal-section-number">01</div>
                <h3 class="legal-section-title">Responsável pelo tratamento</h3>
                <div class="legal-section-content">
                    <p>A Izzycar, NIF <strong class="text-accent">{{$settings->where('label', 'vat_number')->first()->value}}</strong>, com sede em <strong class="text-accent">{{$settings->where('label', 'address')->first()->value}}</strong>, é responsável pelo tratamento dos seus dados pessoais.</p>
                    <div class="contact-info-box">
                        <div class="contact-info-item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <a href="mailto:{{$settings->where('label', 'email')->first()->value}}" class="contact-link">{{$settings->where('label', 'email')->first()->value}}</a>
                        </div>
                        <div class="contact-info-item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <a href="tel:{{$settings->where('label', 'phone')->first()->value}}" class="contact-link">{{$settings->where('label', 'phone')->first()->value}}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">02</div>
                <h3 class="legal-section-title">Dados recolhidos</h3>
                <div class="legal-section-content">
                    <p>Podemos recolher os seguintes dados:</p>
                    <ul class="legal-list">
                        <li>Nome, email e telefone (quando nos contacta ou pede uma proposta)</li>
                        <li>Dados de navegação (endereço IP, cookies, estatísticas de utilização do site)</li>
                        <li>Informações adicionais fornecidas pelo utilizador nos formulários</li>
                    </ul>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">03</div>
                <h3 class="legal-section-title">Finalidade do tratamento</h3>
                <div class="legal-section-content">
                    <p>Os dados recolhidos destinam-se a:</p>
                    <ul class="legal-list">
                        <li>Responder a pedidos de contacto e enviar propostas</li>
                        <li>Gerir pedidos de importação e serviços contratados</li>
                        <li>Enviar comunicações informativas ou comerciais (quando autorizado)</li>
                        <li>Melhorar a experiência de utilização do website</li>
                    </ul>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">04</div>
                <h3 class="legal-section-title">Partilha de dados</h3>
                <div class="legal-section-content">
                    <p>Podemos partilhar dados com prestadores de serviços (ex.: alojamento web, ferramentas de marketing, Google Analytics, redes sociais) apenas na medida necessária à prestação dos serviços. <strong>Não vendemos dados pessoais a terceiros.</strong></p>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">05</div>
                <h3 class="legal-section-title">Conservação dos dados</h3>
                <div class="legal-section-content">
                    <p>Os dados pessoais são conservados pelo período necessário à finalidade a que se destinam, ou até que o utilizador exerça o direito de eliminação.</p>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">06</div>
                <h3 class="legal-section-title">Direitos do titular</h3>
                <div class="legal-section-content">
                    <p>O utilizador tem direito de acesso, retificação, eliminação, portabilidade e oposição ao tratamento dos seus dados pessoais.</p>
                    <p>Para exercer estes direitos, contacte-nos através de <a href="mailto:{{$settings->where('label', 'email')->first()->value}}" class="inline-link">{{$settings->where('label', 'email')->first()->value}}</a>.</p>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">07</div>
                <h3 class="legal-section-title">Cookies</h3>
                <div class="legal-section-content">
                    <p>Utilizamos cookies para melhorar a navegação e analisar estatísticas de utilização. O utilizador pode gerir as preferências de cookies no seu navegador.</p>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">08</div>
                <h3 class="legal-section-title">Alterações</h3>
                <div class="legal-section-content">
                    <p>Esta Política de Privacidade pode ser atualizada. <strong>Última atualização: 2025.</strong></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .legal-intro-card {
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        border-radius: 24px;
        padding: 3rem;
        margin-bottom: 3rem;
        display: flex;
        gap: 2rem;
        align-items: center;
        color: white;
    }

    .legal-intro-icon {
        flex-shrink: 0;
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.15);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .legal-intro-text {
        flex: 1;
    }

    .legal-intro-text p {
        font-size: 1.15rem;
        line-height: 1.7;
        margin: 0;
        opacity: 0.95;
    }

    .legal-content-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }

    .legal-section {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        position: relative;
        border-left: 4px solid var(--accent-color);
    }

    .legal-section-number {
        position: absolute;
        top: -15px;
        left: 2rem;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.25rem;
        box-shadow: 0 4px 15px rgba(110, 7, 7, 0.3);
    }

    .legal-section-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 1.5rem;
        padding-top: 0.5rem;
    }

    .legal-section-content {
        font-size: 1.05rem;
        line-height: 1.8;
        color: #333;
    }

    .legal-section-content p {
        margin-bottom: 1rem;
    }

    .legal-list {
        list-style: none;
        padding-left: 0;
        margin: 1rem 0;
    }

    .legal-list li {
        position: relative;
        padding-left: 2rem;
        margin-bottom: 0.75rem;
    }

    .legal-list li:before {
        content: "";
        position: absolute;
        left: 0;
        top: 0.5rem;
        width: 8px;
        height: 8px;
        background: var(--accent-color);
        border-radius: 50%;
    }

    .contact-info-box {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    .contact-info-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0.75rem 1.25rem;
        background: #f8f9fa;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .contact-info-item:hover {
        background: #e9ecef;
        transform: translateY(-2px);
    }

    .contact-info-item svg {
        color: var(--accent-color);
        flex-shrink: 0;
    }

    .contact-link {
        color: #111;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .contact-link:hover {
        color: var(--accent-color);
    }

    .inline-link {
        color: var(--accent-color);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border-bottom: 2px solid transparent;
    }

    .inline-link:hover {
        border-bottom-color: var(--accent-color);
    }

    @media (max-width: 768px) {
        .legal-intro-card {
            flex-direction: column;
            padding: 2rem;
            text-align: center;
        }

        .legal-intro-icon {
            width: 64px;
            height: 64px;
        }

        .legal-intro-icon svg {
            width: 32px;
            height: 32px;
        }

        .legal-intro-text p {
            font-size: 1rem;
        }

        .legal-section {
            padding: 2rem 1.5rem;
        }

        .legal-section-title {
            font-size: 1.5rem;
        }

        .legal-section-content {
            font-size: 1rem;
        }

        .contact-info-box {
            flex-direction: column;
        }
    }
</style>
@endpush