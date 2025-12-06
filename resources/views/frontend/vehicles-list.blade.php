@extends('frontend.partials.layout')

@section('title', 'Carros Usados em Portugal | Compra e Venda | IzzyCar')
@section('meta_description', 'Encontre carros usados de qualidade em Portugal. A IzzyCar oferece opções confiáveis para compra e venda de veículos, com garantia e transparência.')


@php use Illuminate\Support\Str; @endphp
@section('content')

<!-- Modern Hero Section -->
<section class="hero-section-modern">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8 mx-auto text-center hero-content">
                <div class="badge-modern mb-4 animate-fade-in-up">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path>
                        <circle cx="7" cy="17" r="2"></circle>
                        <circle cx="17" cy="17" r="2"></circle>
                    </svg>
                    <span>Viaturas Usadas</span>
                </div>
                <h1 class="display-3 fw-bold text-white mb-4 animate-fade-in-up" style="animation-delay: 0.2s;">
                    Encontre o Seu <span class="text-accent-gradient">Carro dos Sonhos</span>
                </h1>
                <p class="lead text-white-80 mb-0 animate-fade-in-up" style="animation-delay: 0.4s;">
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
