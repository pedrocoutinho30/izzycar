@php
    $settings = App\Models\Setting::get();
@endphp

@extends('frontend.partials.layout')
<meta name="robots" content="noindex, nofollow">

@section('title', 'Proposta Expirada')

@section('content')

<main>

    {{-- Hero idêntico ao da proposta normal --}}
    <section class="hero-section d-flex justify-content-center align-items-center" id="section_home_import" style="min-height: 40vh;">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-12 mx-auto text-center">
                    <div class="proposal-hero-badge mb-3" style="background:rgba(255,255,255,.15);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>Proposta Expirada</span>
                    </div>
                    <h1 class="text-white mb-3" style="font-size: 2.5rem; font-weight: 700;">
                        Esta proposta já não está disponível
                    </h1>
                    <p class="lead text-white-50" style="font-size: 1.1rem; max-width: 620px; margin: 0 auto;">
                        As propostas têm validade de <strong class="text-white">15 dias</strong> a partir da data de emissão.
                        A proposta
                        @if($proposal->brand && $proposal->model)
                            para o <strong class="text-white">{{ $proposal->brand }} {{ $proposal->model }}</strong>
                        @endif
                        foi criada a {{ $proposal->created_at->format('d/m/Y') }} e expirou a {{ $proposal->created_at->addDays(15)->format('d/m/Y') }}.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Conteúdo principal --}}
    <section class="section-padding" style="background:#f8f9fa; padding: 4rem 0;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">

                    <div class="card border-0 shadow-sm text-center" style="border-radius:16px; overflow:hidden;">
                        <div class="card-body p-5">

                            {{-- Ícone --}}
                            <div class="mb-4" style="display:inline-flex; align-items:center; justify-content:center;
                                        width:80px; height:80px; border-radius:50%;
                                        background:#fff3cd; color:#856404; font-size:2rem;">
                                ⏱
                            </div>

                            <h2 class="fw-bold mb-2" style="font-size:1.5rem;">O que posso fazer?</h2>
                            <p class="text-muted mb-4">
                                Pode pedir uma nova proposta actualizada ou entrar em contacto connosco directamente para esclarecimentos.
                            </p>

                            <div class="d-grid gap-3">
                                <a href="{{ route('frontend.form-import') }}"
                                   class="btn btn-primary btn-lg"
                                   style="border-radius:10px; font-weight:600; padding:.85rem 1.5rem;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" class="me-2" style="vertical-align:middle">
                                        <path d="M12 5v14M5 12h14"></path>
                                    </svg>
                                    Pedir nova proposta
                                </a>

                                <a href="https://wa.me/351928459346?text=Olá%2C%20gostaria%20de%20pedir%20mais%20informações%20sobre%20uma%20proposta%20expirada."
                                   class="btn btn-outline-secondary btn-lg"
                                   target="_blank" rel="noopener noreferrer"
                                   style="border-radius:10px; font-weight:600; padding:.85rem 1.5rem;">
                                    <i class="bi bi-whatsapp me-2" style="font-size:1.1rem; vertical-align:middle;"></i>
                                    Entrar em contacto
                                </a>
                            </div>

                        </div>

                        <div class="card-footer border-0 py-3" style="background:#f1f3f5;">
                            <small class="text-muted">
                                Tem dúvidas? Contacte-nos por email ou telefone e teremos todo o gosto em ajudar.
                            </small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

</main>

@endsection
