@extends('frontend.partials.layout')

@section('title', 'Carros Usados em Portugal | Compra e Venda | IzzyCar')
@section('meta_description', 'Encontre carros usados de qualidade em Portugal. A IzzyCar oferece opções confiáveis para compra e venda de veículos, com garantia e transparência.')


@php use Illuminate\Support\Str; @endphp
@section('content')

<!-- Hero Section -->
<section class="hero-vehicles-section">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="row align-items-center min-vh-50 py-5">
            <div class="col-lg-7">
                <span class="hero-badge-vehicles fade-in-up">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path>
                        <circle cx="7" cy="17" r="2"></circle>
                        <circle cx="17" cy="17" r="2"></circle>
                    </svg>
                    Viaturas Usadas
                </span>
                <h1 class="hero-title-vehicles fade-in-up" data-delay="100">
                    Encontre o Seu <span class="hero-accent">Carro</span>
                </h1>
                <p class="hero-desc-vehicles fade-in-up" data-delay="200">
                    Viaturas usadas de qualidade, selecionadas com rigor e transparência
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">

        <div class="desktop-only">
            {{-- Conteúdo Desktop --}}
            @include('frontend.vehicles-list-desktop')
        </div>

        <div class="mobile-only">
            {{-- Conteúdo Mobile --}}
            @include('frontend.vehicles-list-mobile')
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
    .hero-vehicles-section {
        position: relative;
        background: linear-gradient(135deg, #111111 0%, #1a1a1a 50%, #111111 100%);
        overflow: hidden;
        padding: 3rem 0;
    }
    .hero-vehicles-section .hero-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%236e0707' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
    }
    .hero-badge-vehicles {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: rgba(110,7,7,.2);
        border: 1px solid rgba(110,7,7,.3);
        border-radius: 50px;
        color: #fff;
        font-size: .9rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    .hero-title-vehicles {
        font-size: clamp(1.8rem, 4.5vw, 3rem);
        font-weight: 900;
        color: #fff;
        line-height: 1.15;
        margin-bottom: 1rem;
        white-space: nowrap;
    }
    @media(max-width:640px) { .hero-title-vehicles { white-space: normal; font-size: 1.7rem; } }
    .hero-title-vehicles .hero-accent {
        color: var(--accent-color);
    }
    .hero-desc-vehicles {
        font-size: 1.1rem;
        color: rgba(255,255,255,.8);
        line-height: 1.8;
    }
    .min-vh-50 { min-height: 40vh; }
    .fade-in-up {
        opacity: 0;
        transform: translateY(24px);
        animation: fadeInUp .7s ease forwards;
    }
    .fade-in-up[data-delay="100"] { animation-delay: .1s; }
    .fade-in-up[data-delay="200"] { animation-delay: .2s; }
    @keyframes fadeInUp { to { opacity:1; transform:translateY(0); } }
</style>
@endpush
