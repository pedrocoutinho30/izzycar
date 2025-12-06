@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => $data->seo
])
@section('content')

@include('frontend.partials.hero-section', ['title' => $data->process_import['title'], 'subtitle' => $data->process_import['subtitle']])

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
                <p class="cta-subtitle-import">Receba uma proposta personalizada sem compromisso</p>
            </div>
            <a href="#form-proposta" class="btn-cta-import" data-bs-toggle="modal" data-bs-target="#formPropostaModal">
                Pedir Proposta
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
        <div class="import-description-card">
            <div class="import-description-content">
                {!!$data->process_import['description']!!}
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light" id="section_why_import">
    <div class="container">
        <div class="section-header-import">
            <h2 class="section-title-import">Porque Importar com a Izzycar?</h2>
            <p class="section-subtitle-import">Vantagens que fazem a diferença</p>
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

<section class="section-padding" id="section_import">
    <div class="container">


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
                <p class="cta-inline-text">Solicite a sua proposta personalizada</p>
            </div>
            <a href="#form-proposta" class="btn-cta-inline" data-bs-toggle="modal" data-bs-target="#formPropostaModal">
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
        <div class="section-header-import">
            <h2 class="section-title-import">Custos de Importação</h2>
            <p class="section-subtitle-import">Transparência total em cada etapa do processo</p>
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
            <a href="#form-proposta" class="btn-cta-inline" data-bs-toggle="modal" data-bs-target="#formPropostaModal">
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
        <div class="section-header-import">
            <h2 class="section-title-import">Perguntas Frequentes</h2>
            <p class="section-subtitle-import">Tire as suas dúvidas sobre importação</p>
        </div>

        <div class="faq-wrapper-modern">
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
                <div id="collapse-faq-{{ $loop->index }}" class="collapse faq-answer-collapse"
                    data-bs-parent="#section_faq">
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
    .import-description-card {
        background: white;
        border-radius: 24px;
        padding: 3rem;
        /* box-shadow: 0 10px 40px rgba(0,0,0,0.08); */
    }

    .import-description-content {
        font-size: 1.1rem;
        line-height: 1.8;
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
        top: 0.6rem;
        width: 8px;
        height: 8px;
        background: var(--accent-color);
        border-radius: 50%;
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

    /* Why Import Cards */
    .why-import-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        height: 100%;
        /* box-shadow: 0 10px 40px rgba(0,0,0,0.08); */
        transition: all 0.3s ease;
        display: flex;
        gap: 1.5rem;
        border: 2px solid transparent;
    }

    .why-import-card:hover {
        transform: translateY(-5px);
        /* box-shadow: 0 15px 50px rgba(0,0,0,0.12); */
        border-color: var(--accent-color);
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
        transition: all 0.3s ease;
    }

    .why-import-card:hover .why-import-icon {
        transform: scale(1.1) rotate(5deg);
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
</style>
@endpush

<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

<script>
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
</script>