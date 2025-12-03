@php
$settings = App\Models\Setting::get();
$image = null;
if ($proposal->images) {
$image = $proposal->images;
}
@endphp

@include('frontend.partials.seo', [
'seo' => (object)[
'meta_image' => $image,
'title' => 'Proposta de importação do veículo ' . $proposal->brand . ' ' . $proposal->model . ' ' . $proposal->version . ' - Izzycar',
'meta_description' => 'Proposta detalhada para a importação do veículo ' . $proposal->brand . ' ' . $proposal->model . ' ' . $proposal->version . '. Preço chave na mão, custos, processo e muito mais.',
]
])

@extends('frontend.partials.layout')
<?php $no_footer = true; ?>
<meta name="robots" content="noindex, nofollow">


@section('title', 'Proposta de Importação')

@section('content')


<main>

    <section class="hero-section d-flex justify-content-center align-items-center" id="section_home_import" style="min-height: 40vh;">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 col-12 mx-auto text-center">
                    <div class="proposal-hero-badge mb-3">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11l3 3L22 4"></path>
                            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                        </svg>
                        <span>Proposta Personalizada</span>
                    </div>
                    <h1 class="text-white mb-3" style="font-size: 2.5rem; font-weight: 700;">A Sua Proposta de Importação</h1>
                    <p class="lead text-white-50" style="font-size: 1.2rem; max-width: 700px; margin: 0 auto;">
                        Processo completo, transparente e sem surpresas. Tudo incluído no preço final.
                    </p>
                </div>
            </div>
        </div>
    </section>
    <!-- Modal -->

    <div class="modal fade confirm-accept-modal" id="confirmAcceptModal-{{ $proposal->id }}" tabindex="-1" aria-labelledby="confirmAcceptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title " id="confirmAcceptModalLabel">Confirmar aceitação</h5>
                    <button type="button" class="btn-close bg-finance" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <p>Confirme que leu toda a informação da proposta e que quer mesmo avançar com o processo.
                        <br>
                        Antes de prosseguir, por favor complete os dados necessários para dar início ao processo:
                    </p>
                    <form id="clientDataForm" action="{{ route('proposals.accept', $proposal->id) }}" method="POST">
                        @csrf
                        @if($client->phone == null || $client->phone == '')
                        <div class="mb-3">
                            <label for="clientPhone" class="form-label">Telemóvel</label>
                            <input type="tel" class="form-control" id="clientPhone" name="phone" required>
                        </div>
                        @endif
                        @if($client->email == null || $client->email == '')
                        <div class="mb-3">
                            <label for="clientEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="clientEmail" name="email" required>
                        </div>
                        @endif
                        @if($client->address == null || $client->address == '')
                        <div class="mb-3">
                            <label for="address" class="form-label">Morada</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        @endif
                        @if($client->postal_code == null || $client->postal_code == '')
                        <div class="mb-3">
                            <label for="postalCode" class="form-label">Codigo Postal</label>
                            <input type="text" class="form-control" id="postalCode" name="postal_code" required>
                        </div>
                        @endif
                        @if($client->city == null || $client->city == '')
                        <div class="mb-3">
                            <label for="city" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        @endif
                        @if($client->vat_number == null || $client->vat_number == '')

                        <div class="mb-3">
                            <label for="clientNif" class="form-label">NIF</label>
                            <input type="text" class="form-control" id="clientNif" name="vat_number" required>
                        </div>
                        @endif
                        @if($client->identification_number == null || $client->identification_number == '')
                        <div class="mb-3">
                            <label for="clientCC" class="form-label">Número do Cartão de Cidadão</label>
                            <input type="text" class="form-control" id="clientCC" name="identification_number" required>
                        </div>
                        @endif
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success" id="contactSubmitBtn" form="clientDataForm">
                                Confirmar
                            </button>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const form = document.querySelector('.confirm-accept-modal form');
                                const submitBtn = document.getElementById('contactSubmitBtn');
                                if (form && submitBtn) {
                                    form.addEventListener('submit', function() {
                                        submitBtn.disabled = true;
                                        submitBtn.innerText = 'A enviar...';
                                    });
                                }
                            });
                        </script>
                    </form>
                </div>



            </div>
        </div>
    </div>

    <div class="container my-5">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="background-color: #198754 !important;">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <?php
        $totalCostWithoutIva = $proposal->commission_cost + $proposal->inspection_commission_cost + $proposal->license_plate_cost + $proposal->registration_cost + $proposal->imt_cost + $proposal->ipo_cost + $proposal->transport_cost + ($proposal->proposed_car_value / 1.19);
        $totalCost = $proposal->commission_cost + $proposal->inspection_commission_cost + $proposal->license_plate_cost + $proposal->isv_cost + $proposal->registration_cost + $proposal->imt_cost + $proposal->ipo_cost + $proposal->transport_cost + $proposal->proposed_car_value;
        $serviceCost = $proposal->commission_cost + $proposal->inspection_commission_cost + $proposal->license_plate_cost + $proposal->registration_cost + $proposal->imt_cost + $proposal->ipo_cost + $proposal->transport_cost;
        ?>

        <!-- Veículo Principal -->
        <div class="proposal-vehicle-card">
            <div class="vehicle-header">
                <div>
                    <div class="vehicle-title-badge">O SEU VEÍCULO</div>
                    <h2 class="vehicle-title">{{ $proposal->brand }} {{ $proposal->model }}</h2>
                    <p class="vehicle-subtitle">{{ $proposal->version }}</p>
                </div>
                @if(App\Models\ConvertedProposal::where('proposal_id', $proposal->id)->exists())
                <div class="status-badge status-accepted">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Proposta Aceite
                </div>
                @else
                <a href="#" class="cta-accept-btn" data-bs-toggle="modal" data-bs-target="#confirmAcceptModal-{{ $proposal->id }}">
                    <span>Aceitar Proposta</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
                @endif
            </div>

            <div class="vehicle-content">
                <div class="row g-4">
                    <div class="col-lg-5">
                        @if($proposal->images)
                        <div class="vehicle-image-wrapper">
                            <img src="{{ $image }}" 
                                onerror="this.src='{{ asset('img/logo-simples.png') }}';"
                                alt="{{ $proposal->brand }} {{ $proposal->model }}" 
                                class="vehicle-image">
                            <div class="image-overlay">
                                <a href="{{ $proposal->url }}" target="_blank" class="view-ad-btn">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                    </svg>
                                    Ver Anúncio Original
                                </a>
                            </div>
                        </div>
                        @endif

                        <div class="vehicle-specs">
                            <div class="spec-item">
                                <div class="spec-icon">@include('components.icons.calendar')</div>
                                <div class="spec-content">
                                    <div class="spec-label">Ano</div>
                                    <div class="spec-value">{{$proposal->proposed_car_year_month}}</div>
                                </div>
                            </div>
                            <div class="spec-item">
                                <div class="spec-icon">@include('components.icons.fuel')</div>
                                <div class="spec-content">
                                    <div class="spec-label">Combustível</div>
                                    <div class="spec-value">{{$proposal->fuel}}</div>
                                </div>
                            </div>
                            <div class="spec-item">
                                <div class="spec-icon">@include('components.icons.road')</div>
                                <div class="spec-content">
                                    <div class="spec-label">Quilómetros</div>
                                    <div class="spec-value">{{number_format($proposal->proposed_car_mileage, 0, ',', '.')}} KM</div>
                                </div>
                            </div>
                            <div class="spec-item">
                                <div class="spec-icon">@include('components.icons.gearbox')</div>
                                <div class="spec-content">
                                    <div class="spec-label">Transmissão</div>
                                    <div class="spec-value">Automático</div>
                                </div>
                            </div>
                            @if($proposal->fuel !== 'Elétrico')
                            <div class="spec-item">
                                <div class="spec-icon">@include('components.icons.motor')</div>
                                <div class="spec-content">
                                    <div class="spec-label">Cilindrada</div>
                                    <div class="spec-value">{{$proposal->engine_capacity}} CC</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="pricing-section">
                            <h3 class="pricing-title">Resumo Completo de Custos</h3>
                            <p class="pricing-subtitle">Transparência total. Sem custos escondidos.</p>

                            <div class="cost-breakdown">
                                <div class="cost-item">
                                    <div class="cost-label">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0110 0v4"></path>
                                        </svg>
                                        Preço do Veículo
                                    </div>
                                    <div class="cost-value">{{ number_format($proposal->proposed_car_value, 0, ',', '.') }} €</div>
                                </div>
                                <div class="cost-item">
                                    <div class="cost-label">
                                       <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 4.5c-1.5-.9-3.2-1.5-5-1.5C8 3 5 6 5 10s3 7 7 7c1.8 0 3.5-.6 5-1.5"></path>
                            <line x1="3" y1="9" x2="15" y2="9"></line>
                            <line x1="3" y1="11" x2="15" y2="11"></line>
                        </svg>
                                        ISV (Imposto)
                                    </div>
                                    <div class="cost-value">{{ number_format($proposal->isv_cost, 0, ',', '.') }} €</div>
                                </div>
                                <div class="cost-item">
                                    <div class="cost-label">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                        Serviço Izzycar <span class="info-tooltip" data-bs-toggle="tooltip" title="Inclui transporte, inspeção, matrícula, IMT, IPO e toda a gestão do processo">ⓘ</span>
                                    </div>
                                    <div class="cost-value">{{ number_format($serviceCost, 0, ',', '.') }} €</div>
                                </div>
                                <div class="cost-separator"></div>
                                <div class="cost-item cost-total">
                                    <div class="cost-label">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <strong>Total Chave na Mão</strong>
                                    </div>
                                    <div class="cost-value-total">{{ number_format($totalCost, 0, ',', '.') }} €</div>
                                </div>

                                @if($proposal->fuel == 'Elétrico' || $proposal->fuel == 'Híbrido Plug-in/Gasolina' || $proposal->fuel == 'Híbrido Plug-in/Diesel')
                                <div class="business-offer">
                                    <div class="business-badge">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                            <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"></path>
                                        </svg>
                                        Para Empresas
                                    </div>
                                    <div class="business-content">
                                        <div class="business-price">{{ number_format($totalCostWithoutIva, 0, ',', '.') }} €</div>
                                        <div class="business-label">Preço sem IVA (dedutível)</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipamento e Extras -->
        <div class="equipment-section">
            <div class="section-header-modern mb-4">
                <svg class="section-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4"></path>
                    <circle cx="12" cy="12" r="10"></circle>
                </svg>
                <div>
                    <h3 class="section-title-modern">Equipamento Incluído</h3>
                    <p class="section-subtitle-modern">Todas as características e extras deste veículo</p>
                </div>
            </div>

            @foreach ($attributes as $group => $attrs)
            <div class="equipment-group">
                <div class="equipment-group-header">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="9" y1="9" x2="15" y2="9"></line>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                    <h4>{{ $group }}</h4>
                </div>

                <div class="equipment-grid">
                    @foreach ($attrs as $attr => $value)
                    @if(!in_array($attr, ['Potência', 'Cilindrada', 'Transmissão']))
                    <div class="equipment-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>{{ $attr == $value ? $attr : $attr . ': ' . $value }}</span>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        <!-- @if($proposal->other_links)
        <div class="other-options-section">
            <div class="info-card-modern">
                <div class="info-card-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <h4 class="info-card-title">Outras Opções Disponíveis</h4>
                <p class="info-card-description">Encontrámos alternativas semelhantes que podem interessar:</p>
                <div class="other-links-list">
                    @foreach (json_decode($proposal->other_links) as $link)
                    <a href="{{ $link }}" target="_blank" class="other-link-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                        <span>Alternativa {{ $loop->iteration }}</span>
                        <svg class="arrow-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif -->
        <!-- Timeline do Processo -->
        <div class="process-timeline-section">
            <div class="section-header-modern">
                <svg class="section-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <div>
                    <h3 class="section-title-modern">Da Escolha à Entrega</h3>
                    <p class="section-subtitle-modern">O seu veículo em 5 etapas simples, 22 dias aproximadamente</p>
                </div>
            </div>

            <div class="timeline-container">
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-step">Etapa 1</div>
                        <h4 class="timeline-title">Aprovação</h4>
                        <p class="timeline-description">Análise da proposta e confirmação de disponibilidade do veículo</p>
                        <div class="timeline-duration">~3 dias</div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 4.5c-1.5-.9-3.2-1.5-5-1.5C8 3 5 6 5 10s3 7 7 7c1.8 0 3.5-.6 5-1.5"></path>
                            <line x1="3" y1="9" x2="15" y2="9"></line>
                            <line x1="3" y1="11" x2="15" y2="11"></line>
                        </svg>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-step">Etapa 2</div>
                        <h4 class="timeline-title">Pagamento</h4>
                        <p class="timeline-description">Processamento do pagamento e aquisição do veículo</p>
                        <div class="timeline-duration">~4 dias</div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-step">Etapa 3</div>
                        <h4 class="timeline-title">Transporte</h4>
                        <p class="timeline-description">Transporte seguro do veículo até Portugal</p>
                        <div class="timeline-duration">~12 dias</div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-step">Etapa 4</div>
                        <h4 class="timeline-title">Legalização ISV</h4>
                        <p class="timeline-description">Inspeção, cálculo e pagamento do ISV</p>
                        <div class="timeline-duration">~3 dias</div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-step">Etapa 5</div>
                        <h4 class="timeline-title">Entrega</h4>
                        <p class="timeline-description">Matrícula, documentação e entrega do veículo</p>
                    </div>
                </div>
            </div>

            <div class="timeline-note">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                Os prazos são aproximados e podem variar por motivos logísticos ou administrativos.
            </div>
        </div>

        <!-- Informações Contratuais e Pagamento -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="info-card-modern">
                    <div class="info-card-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <h4 class="info-card-title">Formalização Contratual</h4>
                    <p class="info-card-description">O processo é oficializado com dois contratos que garantem total segurança jurídica:</p>
                    <ul class="info-card-list">
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <div>
                                <strong>Contrato de Prestação de Serviços</strong>
                                <p>Define todos os serviços incluídos, prazos e responsabilidades</p>
                            </div>
                        </li>
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <div>
                                <strong>Contrato de Compra e Venda</strong>
                                <p>Formaliza a aquisição do veículo e sua legalização em Portugal</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="info-card-modern">
                    <div class="info-card-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                    </div>
                    <h4 class="info-card-title">Modelo de Pagamento</h4>
                    <p class="info-card-description">Pagamentos simples e seguros, divididos em etapas claras:</p>

                    <div class="payment-breakdown">
                        <div class="payment-item">
                            <div class="payment-badge">Serviço Izzycar</div>
                            <div class="payment-details">
                                <div class="payment-step">
                                    <span class="payment-percentage">{{$settings->where('label', 'init_payment')->first()->value}}</span>
                                    <span class="payment-when">Na assinatura do contrato</span>
                                </div>
                                <div class="payment-step">
                                    <span class="payment-percentage">{{$settings->where('label', 'final_payment')->first()->value}}</span>
                                    <span class="payment-when">Na entrega do veículo</span>
                                </div>
                            </div>
                        </div>

                        <div class="payment-item">
                            <div class="payment-badge">Veículo + ISV</div>
                            <div class="payment-details">
                                <div class="payment-step">
                                    <span class="payment-info">Pagamento direto ao stand através de transferência bancária e ao estado</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Final -->
        <div class="final-cta-section">
            <div class="final-cta-content">
                <h3 class="final-cta-title">Pronto para Começar?</h3>
                <p class="final-cta-subtitle">Tem dúvidas? A nossa equipa está disponível para ajudar.</p>
                <div class="final-cta-contacts">
                    <a href="tel:{{$settings->where('label', 'phone')->first()->value}}" class="contact-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"></path>
                        </svg>
                        {{$settings->where('label', 'phone')->first()->value}}
                    </a>
                    <a href="mailto:{{$settings->where('label', 'email')->first()->value}}" class="contact-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        {{$settings->where('label', 'email')->first()->value}}
                    </a>
                </div>
                @if(!App\Models\ConvertedProposal::where('proposal_id', $proposal->id)->exists())
                <a href="#" class="btn-accept-final" data-bs-toggle="modal" data-bs-target="#confirmAcceptModal-{{ $proposal->id }}">
                    <span>Aceitar Esta Proposta</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </a>
                @endif
            </div>
        </div>

</main>
@endsection
@push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Animate elements on scroll
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

        document.querySelectorAll('.timeline-item, .info-card-modern, .spec-item').forEach(el => {
            observer.observe(el);
        });
    });
</script>
@endpush

<style>
    
</style>