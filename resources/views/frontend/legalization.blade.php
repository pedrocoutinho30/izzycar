@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => $data->seo
])
@section('content')

@include('frontend.partials.hero-section', ['title' => $data->contents['title'], 'subtitle' => $data->contents['subtitle']])

<section class="section-padding">
    <div class="container">
        <div class="legalization-intro-card">
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

<section class="section-padding pt-0">
    <div class="container">
        <div class="section-header-modern">
            <h2 class="section-title-modern">Passos para legalizar o seu veículo</h2>
            <p class="section-subtitle-modern">Processo simplificado em etapas claras</p>
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
@endsection

@push('styles')
<style>
    .legalization-intro-card {
        background: white;
        border-radius: 24px;
        padding: 3rem;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
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
        color: #333;
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

    .legalization-step-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        height: 100%;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
    }

    .legalization-step-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 50px rgba(0,0,0,0.12);
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

    .legalization-step-card:hover .step-number {
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

    .legalization-step-card:hover .step-arrow {
        opacity: 1;
        transform: translateX(0);
    }

    @media (max-width: 992px) {
        .legalization-intro-card {
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
    }

    @media (max-width: 768px) {
        .legalization-intro-card {
            padding: 1.5rem;
        }

        .intro-content {
            font-size: 1rem;
        }

        .section-title-modern {
            font-size: 1.75rem;
        }

        .legalization-step-card {
            padding: 1.5rem;
        }

        .step-title {
            font-size: 1.2rem;
        }
    }
</style>
@endpush