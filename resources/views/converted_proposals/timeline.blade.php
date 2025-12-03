@extends('frontend.partials.layout')

@section('title', 'Izzycar - Linha do Tempo da Proposta')

@section('content')

<main>
    <!-- Hero Section -->
    <section class="hero-section d-flex justify-content-center align-items-center" id="section_home_import" style="min-height: 40vh;">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 col-12 mx-auto text-center">
                    <div class="proposal-hero-badge mb-3">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Acompanhamento em Tempo Real</span>
                    </div>
                    <h1 class="text-white mb-3" style="font-size: 2.5rem; font-weight: 700;">Estado da Sua Importação</h1>
                    <p class="lead text-white-50" style="font-size: 1.2rem; max-width: 700px; margin: 0 auto;">
                        Acompanhe cada etapa do processo de importação do seu veículo
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="container my-5">
        <!-- Vehicle Info Card -->
        <div class="proposal-vehicle-card mb-4">
            <div class="vehicle-header">
                <div>
                    <div class="vehicle-title-badge">O SEU VEÍCULO</div>
                    <h2 class="vehicle-title">{{ $convertedProposal->brand }} {{ $convertedProposal->model }}</h2>
                    <p class="vehicle-subtitle">{{ $convertedProposal->version }}</p>
                </div>
            </div>
            
            <div class="vehicle-content">
                <div class="row g-4">
                    <div class="col-lg-5">
                        @if($convertedProposal->proposal->images)
                        <div class="vehicle-image-wrapper">
                            
                        </div>

                        <div class="vehicle-image-wrapper">
                            <img src="{{ $convertedProposal->proposal->images }}" alt="{{ $convertedProposal->brand }} {{ $convertedProposal->model }}" class="vehicle-image">
                            <div class="image-overlay">
                                <a href="{{ route('proposals.detail', ['proposal_code' => $convertedProposal->proposal->proposal_code]) }}" target="_blank" class="view-ad-btn">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                    </svg>
                                    Ver Proposta
                                </a>
                            </div>
                        </div>
                        @endif
                        
                        <div class="vehicle-info-card">
                            <div class="info-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <div>
                                    <span class="info-label">Ano / Quilómetros</span>
                                    <span class="info-value">{{ $convertedProposal->year }} | {{ number_format($convertedProposal->km, 0, ',', '.') }} km</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <div>
                                    <span class="info-label">Cliente</span>
                                    <span class="info-value">{{ $convertedProposal->proposal->client->name }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <div>
                                    <span class="info-label">Data da Proposta</span>
                                    <span class="info-value">{{ \Carbon\Carbon::parse($convertedProposal->proposal->created_at)->isoFormat('DD-MM-YYYY') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-7">
                        @php
                        // Extrair apenas os status já concluídos
                        $completedStatuses = collect($status_history)->pluck('new_status')->toArray();

                        $lastCompletedIndex = null;
                        foreach ($allStatus as $i => $status) {
                            if (in_array($status['status'], $completedStatuses)) {
                                $lastCompletedIndex = $i;
                            }
                        }
                        $totalSteps = count($allStatus);
                        $progressPercent = $lastCompletedIndex !== null
                            ? (($lastCompletedIndex + 1) / $totalSteps) * 100
                            : 0;

                        // Garantir que o progresso não ultrapassa 100%
                        $progressPercent = min($progressPercent, 100);
                        @endphp

                        @if(in_array('Cancelado', $completedStatuses))
                        <!-- Processo Cancelado -->
                        <div class="process-cancelled-section">
                            <div class="cancelled-icon">
                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                            </div>
                            <h3 class="cancelled-title">Processo Cancelado</h3>
                            <p class="cancelled-description">O processo de importação foi cancelado. Para mais informações ou esclarecimentos, por favor contacte a nossa equipa.</p>
                            <div class="cancelled-contacts">
                                <a href="tel:+351123456789" class="contact-btn-cancelled">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"></path>
                                    </svg>
                                    Ligar
                                </a>
                                <a href="mailto:info@izzycar.pt" class="contact-btn-cancelled">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    Email
                                </a>
                            </div>
                        </div>
                        @else
                        <!-- Timeline do Processo -->
                        <div class="process-timeline-modern">
                            <div class="timeline-progress-bar">
                                <div class="timeline-progress-fill" style="height: {{ $progressPercent }}%;"></div>
                            </div>
                            
                            <div class="timeline-steps">
                                @foreach ($allStatus as $i => $status)
                                @php
                                $isActive = in_array($status['status'], $completedStatuses);
                                $isLastActive = $isActive && $i === $lastCompletedIndex;
                                $statusEntry = collect($status_history)->firstWhere('new_status', $status['status']);
                                @endphp

                                <div class="timeline-step-item {{ $isActive ? 'completed' : 'pending' }} {{ $isLastActive ? 'current' : '' }}"
                                    @if($isLastActive) id="last-active-step" @endif>
                                    
                                    <div class="timeline-step-marker">
                                        @if($isActive)
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        @else
                                        <span class="step-number">{{ $i + 1 }}</span>
                                        @endif
                                    </div>
                                    
                                    <div class="timeline-step-content">
                                        <div class="step-header">
                                            <h4 class="step-title">{{ $status['status'] }}</h4>
                                            @if($isActive)
                                            <span class="step-badge completed">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                                Concluído
                                            </span>
                                            @else
                                            <span class="step-badge pending">Pendente</span>
                                            @endif
                                        </div>
                                        
                                        @if ($statusEntry)
                                        <div class="step-details">
                                            <div class="step-date">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 6 12 12 16 14"></polyline>
                                                </svg>
                                                {{ \Carbon\Carbon::parse($statusEntry->created_at)->locale('pt_PT')->isoFormat('DD-MM-YYYY HH:mm') }}
                                            </div>
                                            @if($statusEntry->notes)
                                            <p class="step-notes">{{ $statusEntry->notes }}</p>
                                            @endif
                                        </div>
                                        @else
                                        <p class="step-pending-text">Aguarda processamento</p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Smooth scroll to last active step
        const lastActive = document.getElementById("last-active-step");
        if (lastActive) {
            setTimeout(() => {
                lastActive.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });
            }, 500);
        }

        // Animate timeline items on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.timeline-step-item').forEach(el => {
            observer.observe(el);
        });
    });
</script>
@endpush

<style>
  

    /* Hero Badge */
    .proposal-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 50px;
        color: white;
        font-size: 0.9rem;
        font-weight: 500;
        backdrop-filter: blur(10px);
    }

    .proposal-hero-badge svg {
        color: var(--accent-color);
    }

    /* Vehicle Card */
    .proposal-vehicle-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 3rem;
        max-width: 100%;
    }

    .vehicle-header {
        background: linear-gradient(135deg, #111111 0%, #2a2a2a 100%);
        padding: 2.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .vehicle-title-badge {
        color: var(--accent-color);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 2px;
        margin-bottom: 0.5rem;
    }

    .vehicle-title {
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }

    .vehicle-subtitle {
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.2rem;
        margin: 0.5rem 0 0 0;
    }

    .vehicle-content {
        padding: 3rem;
    }

    /* Vehicle Image */
    .vehicle-image-wrapper {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    .vehicle-image {
        width: 100%;
        height: auto;
        display: block;
    }

    /* Vehicle Info Card */
    .vehicle-info-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .info-item svg {
        color: var(--accent-color);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .info-item > div {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .info-label {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
    }

    .info-value {
        font-size: 1rem;
        color: #111;
        font-weight: 600;
    }

    /* Process Timeline Modern */
    .process-timeline-modern {
        position: relative;
        padding-left: 3rem;
    }

    .timeline-progress-bar {
        position: absolute;
        left: 20px;
        top: 20px;
        bottom: 20px;
        width: 4px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }

    .timeline-progress-fill {
        width: 100%;
        background: linear-gradient(to bottom, #10b981, var(--accent-color));
        transition: height 1s ease;
        border-radius: 4px;
    }

    .timeline-steps {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .timeline-step-item {
        position: relative;
        opacity: 0;
        transform: translateX(-20px);
        transition: all 0.5s ease;
    }

    .timeline-step-item.animate-in {
        opacity: 1;
        transform: translateX(0);
    }

    .timeline-step-marker {
        position: absolute;
        left: -3rem;
        top: 0;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        z-index: 2;
        transition: all 0.3s ease;
    }

    .timeline-step-item.pending .timeline-step-marker {
        background: #e9ecef;
        color: #6c757d;
        border: 3px solid white;
    }

    .timeline-step-item.completed .timeline-step-marker {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: 3px solid white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    }

    .timeline-step-item.current .timeline-step-marker {
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        50% {
            box-shadow: 0 4px 25px rgba(16, 185, 129, 0.7);
        }
    }

    .timeline-step-marker .step-number {
        display: block;
    }

    .timeline-step-content {
        background: white;
        padding: 1.75rem 2rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 2px solid #f1f3f5;
        transition: all 0.3s ease;
    }

    .timeline-step-item.completed .timeline-step-content {
        border-color: #10b981;
        background: linear-gradient(to right, rgba(16, 185, 129, 0.03), white);
    }

    .timeline-step-item.current .timeline-step-content {
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.15);
    }

    .step-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .step-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #111;
        margin: 0;
    }

    .step-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .step-badge.completed {
        background: #d1fae5;
        color: #065f46;
    }

    .step-badge.pending {
        background: #f3f4f6;
        color: #6c757d;
    }

    .step-details {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .step-date {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6c757d;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .step-date svg {
        color: var(--accent-color);
    }

    .step-notes {
        color: #495057;
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 0;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 3px solid var(--accent-color);
    }

    .step-pending-text {
        color: #9ca3af;
        font-size: 0.95rem;
        font-style: italic;
        margin: 0;
    }

    /* Process Cancelled Section */
    .process-cancelled-section {
        text-align: center;
        padding: 4rem 2rem;
    }

    .cancelled-icon {
        width: 96px;
        height: 96px;
        background: linear-gradient(135deg, #dc2626, #991b1b);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        color: white;
    }

    .cancelled-title {
        font-size: 2rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 1rem;
    }

    .cancelled-description {
        font-size: 1.1rem;
        color: #6c757d;
        max-width: 600px;
        margin: 0 auto 2rem;
        line-height: 1.6;
    }

    .cancelled-contacts {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .contact-btn-cancelled {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        background: linear-gradient(135deg, #990000, #6e0707);
        color: white;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(153, 0, 0, 0.3);
    }

    .contact-btn-cancelled:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(153, 0, 0, 0.4);
        color: white;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .vehicle-title {
            font-size: 2rem;
        }

        .process-timeline-modern {
            padding-left: 2.5rem;
        }

        .timeline-progress-bar {
            left: 15px;
        }

        .timeline-step-marker {
            left: -2.5rem;
            width: 36px;
            height: 36px;
        }
    }

    @media (max-width: 768px) {
        .proposal-hero-badge {
            font-size: 0.85rem;
            padding: 8px 16px;
        }

        .hero-section h1 {
            font-size: 1.75rem !important;
        }

        .hero-section .lead {
            font-size: 1rem !important;
        }

        .vehicle-header {
            flex-direction: column;
            padding: 1.5rem;
        }

        .vehicle-title {
            font-size: 1.75rem !important;
        }

        .vehicle-subtitle {
            font-size: 1rem !important;
        }

        .vehicle-content {
            padding: 1.5rem 1rem;
        }

        .proposal-vehicle-card {
            border-radius: 16px;
            margin-bottom: 2rem;
        }

        .vehicle-image-wrapper {
            margin-bottom: 1.5rem;
        }

        .vehicle-info-card {
            padding: 1.25rem;
        }

        .process-timeline-modern {
            padding-left: 2rem;
        }

        .timeline-progress-bar {
            left: 10px;
        }

        .timeline-step-marker {
            left: -2.2rem;
            width: 32px;
            height: 32px;
            font-size: 0.9rem;
        }

        .timeline-step-marker svg {
            width: 18px;
            height: 18px;
        }

        .timeline-step-content {
            padding: 1.25rem 1rem;
        }

        .step-title {
            font-size: 1.1rem;
        }

        .step-badge {
            font-size: 0.75rem;
            padding: 5px 12px;
        }

        .step-date {
            font-size: 0.85rem;
        }

        .step-notes {
            font-size: 0.85rem;
            padding: 0.75rem;
        }

        .cancelled-icon {
            width: 72px;
            height: 72px;
        }

        .cancelled-icon svg {
            width: 48px;
            height: 48px;
        }

        .cancelled-title {
            font-size: 1.5rem;
        }

        .cancelled-description {
            font-size: 1rem;
        }

        .cancelled-contacts {
            flex-direction: column;
            align-items: stretch;
        }

        .contact-btn-cancelled {
            justify-content: center;
        }

        .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }

    @media (max-width: 576px) {
        .vehicle-content {
            padding: 1rem 0.75rem;
        }

        .vehicle-title {
            font-size: 1.5rem !important;
        }

        .proposal-vehicle-card {
            border-radius: 12px;
        }

        .step-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .cancelled-contacts {
            padding: 0 1rem;
        }
    }
</style>