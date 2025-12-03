@extends('frontend.partials.layout')

@section('title', 'Izzycar - Termos e Condições')

@section('content')
@php
$settings = \App\Models\Setting::all();
@endphp

@include('frontend.partials.hero-section', ['title' => 'Termos e Condições', 'subtitle' => 'Regras de utilização do nosso website'])

<section class="section-padding">
    <div class="container">
        <div class="legal-intro-card">
            <div class="legal-intro-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <line x1="10" y1="9" x2="8" y2="9"></line>
                </svg>
            </div>
            <div class="legal-intro-text">
                <p>O website <strong>www.izzycar.pt</strong> é propriedade da Izzycar, NIF <strong>{{$settings->where('label', 'vat_number')->first()->value}}</strong>, com sede em <strong>{{$settings->where('label', 'address')->first()->value}}</strong>. A utilização deste website implica a aceitação dos presentes Termos e Condições.</p>
            </div>
        </div>

        <div class="legal-content-wrapper">
            <div class="legal-section">
                <div class="legal-section-number">01</div>
                <h3 class="legal-section-title">Âmbito</h3>
                <div class="legal-section-content">
                    <p>A Izzycar dedica-se a serviços de aconselhamento automóvel, importação e venda de veículos. Este website destina-se à divulgação de serviços, disponibilização de informações e receção de pedidos de contacto.</p>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">02</div>
                <h3 class="legal-section-title">Utilização do site</h3>
                <div class="legal-section-content">
                    <p>O utilizador compromete-se a fornecer informações verdadeiras e completas ao utilizar os formulários do site e a não utilizar este serviço para fins ilícitos.</p>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">03</div>
                <h3 class="legal-section-title">Informação e preços</h3>
                <div class="legal-section-content">
                    <p>A Izzycar procura manter toda a informação atualizada e correta. No entanto, os valores apresentados para veículos, taxas e serviços são meramente informativos e podem ser alterados sem aviso prévio devido a variações legais, fiscais ou alfandegárias.</p>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">04</div>
                <h3 class="legal-section-title">Propriedade intelectual</h3>
                <div class="legal-section-content">
                    <p>Todos os conteúdos do site (textos, imagens, logótipos e design) são propriedade da Izzycar e não podem ser copiados, reproduzidos ou utilizados sem autorização prévia.</p>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">05</div>
                <h3 class="legal-section-title">Responsabilidade</h3>
                <div class="legal-section-content">
                    <p>A Izzycar não se responsabiliza por interrupções no acesso ao site, erros técnicos, ou por conteúdos de links externos.</p>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">06</div>
                <h3 class="legal-section-title">Dados pessoais</h3>
                <div class="legal-section-content">
                    <p>O tratamento de dados pessoais rege-se pela nossa <a href="{{route('frontend.privacy')}}" class="inline-link">Política de Privacidade</a>.</p>
                </div>
            </div>

            <div class="legal-section">
                <div class="legal-section-number">07</div>
                <h3 class="legal-section-title">Lei aplicável</h3>
                <div class="legal-section-content">
                    <p>Estes Termos regem-se pela lei portuguesa. Em caso de litígio, será competente o foro legal aplicável em Portugal.</p>
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
    }
</style>
@endpush