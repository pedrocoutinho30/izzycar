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
                            <img src="{{ $image }}" alt="{{ $proposal->brand }} {{ $proposal->model }}" class="vehicle-image">
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
    /* Global mobile-friendly rules */
    * {
        box-sizing: border-box;
    }

    body {
        overflow-x: hidden;
    }

    .container {
        max-width: 100%;
        overflow-x: hidden;
    }

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
        font-size: 1.00rem;
        font-weight: 900;
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

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .status-accepted {
        background: #10b981;
        color: white;
    }

    .cta-accept-btn {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 14px 32px;
        background: linear-gradient(135deg, #990000, #6e0707);
        color: white;
        border: none;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(153, 0, 0, 0.3);
    }

    .cta-accept-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(153, 0, 0, 0.4);
        color: white;
    }

    .cta-accept-btn svg {
        transition: transform 0.3s ease;
    }

    .cta-accept-btn:hover svg {
        transform: translateX(5px);
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
        transition: transform 0.3s ease;
    }

    .vehicle-image-wrapper:hover .vehicle-image {
        transform: scale(1.05);
    }

    .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, transparent 50%);
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 2rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .vehicle-image-wrapper:hover .image-overlay {
        opacity: 1;
    }

    .view-ad-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 24px;
        background: white;
        color: #111;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .view-ad-btn:hover {
        background: var(--accent-color);
        color: white;
        transform: translateY(-2px);
    }

    /* Vehicle Specs */
    .vehicle-specs {
        display: grid;
        gap: 1rem;
    }

    .spec-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: #f8f9fa;
        border-radius: 12px;
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateX(-20px);
    }

    .spec-item.animate-in {
        opacity: 1;
        transform: translateX(0);
    }

    .spec-item:hover {
        background: #f1f3f5;
        transform: translateX(5px);
    }

    .spec-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 12px;
        color: var(--accent-color);
    }

    .spec-icon svg {
        width: 24px;
        height: 24px;
    }

    .spec-content {
        flex: 1;
    }

    .spec-label {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .spec-value {
        font-size: 1.1rem;
        color: #111;
        font-weight: 600;
    }

    /* Pricing Section */
    .pricing-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        padding: 2.5rem;
        border-radius: 16px;
        border: 2px solid #e9ecef;
    }

    .pricing-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 0.5rem;
    }

    .pricing-subtitle {
        color: #6c757d;
        font-size: 1rem;
        margin-bottom: 2rem;
    }

    .cost-breakdown {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .cost-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 0;
        border-bottom: 1px solid #f1f3f5;
    }

    .cost-item:last-child {
        border-bottom: none;
    }

    .cost-label {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #495057;
        font-size: 1rem;
        font-weight: 500;
        flex: 1;
        min-width: 0;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .cost-label svg {
        color: var(--accent-color);
        flex-shrink: 0;
        min-width: 18px;
    }

    .cost-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: #111;
        flex-shrink: 0;
        white-space: nowrap;
        margin-left: auto;
    }

    .cost-separator {
        height: 2px;
        background: linear-gradient(90deg, var(--accent-color), #990000);
        margin: 1rem 0;
        border-radius: 2px;
    }

    .cost-total {
        padding-top: 1.5rem;
        margin-top: 0.5rem;
    }

    .cost-total .cost-label {
        font-size: 1.2rem;
        color: #111;
    }

    .cost-value-total {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, #990000, #6e0707);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .info-tooltip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        background: var(--accent-color);
        color: white;
        border-radius: 50%;
        font-size: 0.75rem;
        cursor: help;
        margin-left: 4px;
    }

    .business-offer {
        margin-top: 1.5rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #986b33, #b8860b);
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .business-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50px;
        color: white;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .business-content {
        text-align: right;
    }

    .business-price {
        font-size: 1.75rem;
        font-weight: 800;
        color: white;
    }

    .business-label {
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* Timeline */
    .process-timeline-section {
        background: white;
        padding: 3rem;
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        margin-bottom: 3rem;
    }

    .section-header-modern {
        display: flex;
        align-items: flex-start;
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .section-icon {
        color: var(--accent-color);
        flex-shrink: 0;
    }

    .section-title-modern {
        font-size: 2rem;
        font-weight: 700;
        color: #111;
        margin: 0;
    }

    .section-subtitle-modern {
        color: #6c757d;
        font-size: 1.1rem;
        margin: 0.5rem 0 0 0;
    }

    .timeline-container {
        position: relative;
        padding-left: 3rem;
    }

    .timeline-container::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 20px;
        bottom: 20px;
        width: 3px;
        background: linear-gradient(to bottom, var(--accent-color), #990000);
        border-radius: 3px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 3rem;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.5s ease;
    }

    .timeline-item.animate-in {
        opacity: 1;
        transform: translateY(0);
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -3rem;
        top: 0;
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #990000, #6e0707);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(153, 0, 0, 0.3);
        z-index: 2;
    }

    .timeline-marker svg {
        color: white;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 1.5rem 2rem;
        border-radius: 12px;
        border-left: 4px solid var(--accent-color);
    }

    .timeline-step {
        color: var(--accent-color);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .timeline-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #111;
        margin: 0 0 0.5rem 0;
    }

    .timeline-description {
        color: #6c757d;
        font-size: 1rem;
        margin: 0 0 0.75rem 0;
    }

    .timeline-duration {
        display: inline-block;
        padding: 6px 16px;
        background: white;
        border-radius: 50px;
        color: var(--accent-color);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .timeline-note {
        margin-top: 2rem;
        padding: 1.25rem;
        background: #fff3cd;
        border-left: 4px solid #986b33;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #856404;
        font-size: 0.95rem;
    }

    .timeline-note svg {
        flex-shrink: 0;
        color: #986b33;
    }

    /* Info Cards Modern */
    .info-card-modern {
        background: white;
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
    }

    .info-card-modern.animate-in {
        opacity: 1;
        transform: translateY(0);
    }

    .info-card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
    }

    .info-card-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #990000, #6e0707);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        color: white;
    }

    .info-card-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 1rem;
    }

    .info-card-description {
        color: #6c757d;
        font-size: 1rem;
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .info-card-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-card-list li {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        align-items: flex-start;
    }

    .info-card-list li:last-child {
        margin-bottom: 0;
    }

    .info-card-list svg {
        color: #10b981;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .info-card-list strong {
        display: block;
        color: #111;
        font-size: 1.05rem;
        margin-bottom: 4px;
    }

    .info-card-list p {
        color: #6c757d;
        font-size: 0.95rem;
        margin: 0;
        line-height: 1.5;
    }

    /* Payment Breakdown */
    .payment-breakdown {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .payment-item {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 12px;
    }

    .payment-badge {
        display: inline-block;
        padding: 6px 14px;
        background: var(--accent-color);
        color: white;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }

    .payment-details {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .payment-step {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
    }

    .payment-percentage {
        font-weight: 700;
        color: #111;
        font-size: 1.1rem;
    }

    .payment-when {
        color: #6c757d;
        font-size: 0.95rem;
    }

    .payment-info {
        color: #6c757d;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    /* Final CTA */
    .final-cta-section {
        background: linear-gradient(135deg, #111111 0%, #2a2a2a 100%);
        padding: 4rem 3rem;
        border-radius: 24px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .final-cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255, 255, 255, .02) 35px, rgba(255, 255, 255, .02) 70px);
        pointer-events: none;
    }

    .final-cta-content {
        position: relative;
        z-index: 1;
    }

    .final-cta-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: white;
        margin-bottom: 1rem;
    }

    .final-cta-subtitle {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 2.5rem;
    }

    .final-cta-contacts {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .contact-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .contact-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.3);
        color: white;
        transform: translateY(-2px);
    }

    .btn-accept-final {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 18px 40px;
        background: linear-gradient(135deg, #990000, #6e0707);
        color: white;
        border: none;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(153, 0, 0, 0.4);
    }

    .btn-accept-final:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(153, 0, 0, 0.5);
        color: white;
    }

    .btn-accept-final svg {
        transition: transform 0.3s ease;
    }

    .btn-accept-final:hover svg {
        transform: scale(1.2);
    }

    /* Modal Styles */
    .confirm-accept-modal .modal-content {
        background-color: var(--primary-color);
        color: white;
        border-radius: 20px;
        border: none;
    }

    .confirm-accept-modal .modal-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1.5rem 2rem;
    }

    .confirm-accept-modal .modal-body {
        padding: 2rem;
    }

    .confirm-accept-modal .form-label {
        color: white;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .modal-backdrop.show {
        backdrop-filter: blur(20px);
        background-color: rgba(0, 0, 0, 0.8);
    }

    .confirm-accept-modal .form-control {
        background-color: var(--secondary-color);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .confirm-accept-modal .form-control:focus {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: var(--accent-color);
        color: white;
        box-shadow: 0 0 0 0.2rem rgba(153, 0, 0, 0.25);
    }

    /* Equipment Section */
    .equipment-section {
        background: white;
        padding: 3rem;
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        margin-bottom: 3rem;
    }

    .equipment-group {
        margin-bottom: 2.5rem;
    }

    .equipment-group:last-child {
        margin-bottom: 0;
    }

    .equipment-group-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f3f5;
    }

    .equipment-group-header svg {
        color: var(--accent-color);
        flex-shrink: 0;
    }

    .equipment-group-header h4 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #111;
        margin: 0;
    }

    .equipment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
    }

    .equipment-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 1rem 1.25rem;
        background: #f8f9fa;
        border-radius: 10px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .equipment-item:hover {
        background: white;
        border-color: var(--accent-color);
        transform: translateX(5px);
    }

    .equipment-item svg {
        color: #10b981;
        flex-shrink: 0;
    }

    .equipment-item span {
        color: #495057;
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.4;
    }

    /* Other Options Section */
    .other-options-section {
        margin-bottom: 3rem;
    }

    .other-links-list {
        display: block;
        flex-direction: column;
        gap: 0.75rem;
    }

    .other-link-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 1.25rem 1.5rem;
        background: #f8f9fa;
        border-radius: 12px;
        text-decoration: none;
        color: #111;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .other-link-item:hover {
        background: white;
        border-color: var(--accent-color);
        color: var(--accent-color);
        transform: translateX(5px);
    }

    .other-link-item svg:first-child {
        color: var(--accent-color);
        flex-shrink: 0;
    }

    .other-link-item span {
        flex: 1;
    }

    .other-link-item .arrow-icon {
        color: currentColor;
        transition: transform 0.3s ease;
    }

    .other-link-item:hover .arrow-icon {
        transform: translateX(5px);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .vehicle-title {
            font-size: 2rem;
        }

        .section-title-modern {
            font-size: 1.5rem;
        }

        .pricing-section {
            padding: 2rem;
        }

        .cost-item {
            flex-wrap: wrap;
        }

        .cost-label {
            font-size: 0.95rem;
        }

        .cost-value {
            font-size: 1.2rem;
        }

        .timeline-container {
            padding-left: 2rem;
        }

        .timeline-container::before {
            left: 10px;
        }

        .timeline-marker {
            left: -2.5rem;
            width: 36px;
            height: 36px;
        }

        .final-cta-title {
            font-size: 2rem;
        }

        .equipment-grid {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        }
    }

    @media (max-width: 768px) {
        /* Hero adjustments */
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

        /* Vehicle header */
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

        .cta-accept-btn {
            width: 100%;
            justify-content: center;
            padding: 12px 24px;
            font-size: 0.95rem;
        }

        .status-badge {
            width: 100%;
            justify-content: center;
        }

        /* Vehicle content */
        .vehicle-content {
            padding: 1.5rem 1rem;
        }

        .proposal-vehicle-card {
            border-radius: 16px;
            margin-bottom: 2rem;
        }

        /* Image wrapper */
        .vehicle-image-wrapper {
            margin-bottom: 1.5rem;
        }

        /* Specs - stack better on mobile */
        .vehicle-specs {
            margin-bottom: 1.5rem;
        }

        .spec-item {
            padding: 1rem;
        }

        .spec-icon {
            width: 40px;
            height: 40px;
        }

        .spec-value {
            font-size: 1rem;
        }

        /* PRICING SECTION - Main fix area */
        .pricing-section {
            padding: 1.25rem;
            margin-top: 1.5rem;
        }

        .pricing-title {
            font-size: 1.35rem;
            margin-bottom: 0.5rem;
        }

        .pricing-subtitle {
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        /* Cost breakdown - prevent breaking */
        .cost-breakdown {
            padding: 1.25rem;
        }

        .cost-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem 0;
        }

        .cost-label {
            width: 100%;
            font-size: 0.95rem;
            gap: 10px;
        }

        .cost-label svg {
            width: 16px;
            height: 16px;
        }

        .cost-value {
            width: 100%;
            text-align: left;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--accent-color);
        }

        .cost-total .cost-label {
            font-size: 1.05rem;
        }

        .cost-value-total {
            font-size: 1.75rem;
            text-align: left;
            width: 100%;
        }

        .info-tooltip {
            margin-left: 6px;
        }

        /* Business offer mobile */
        .business-offer {
            flex-direction: column;
            gap: 1rem;
            padding: 1.25rem;
            margin-top: 1.25rem;
        }

        .business-badge {
            width: 100%;
            justify-content: center;
            text-align: center;
        }

        .business-content {
            text-align: center;
        }

        .business-price {
            font-size: 1.5rem;
        }

        .business-label {
            font-size: 0.85rem;
        }

        /* Timeline */
        .process-timeline-section {
            padding: 1.5rem 1rem;
            border-radius: 16px;
            margin-bottom: 2rem;
        }

        .section-header-modern {
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .section-icon {
            width: 28px;
            height: 28px;
        }

        .section-title-modern {
            font-size: 1.35rem;
        }

        .section-subtitle-modern {
            font-size: 0.95rem;
        }

        .timeline-container {
            padding-left: 1.5rem;
        }

        .timeline-container::before {
            left: 8px;
        }

        .timeline-marker {
            left: -1.9rem;
            width: 36px;
            height: 36px;
        }

        .timeline-marker svg {
            width: 20px;
            height: 20px;
        }

        .timeline-content {
            padding: 1.25rem 1rem;
        }

        .timeline-title {
            font-size: 1.2rem;
        }

        .timeline-description {
            font-size: 0.9rem;
        }

        .timeline-duration {
            font-size: 0.85rem;
            padding: 5px 14px;
        }

        .timeline-note {
            padding: 1rem;
            font-size: 0.85rem;
        }

        /* Info cards */
        .info-card-modern {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-card-icon {
            width: 56px;
            height: 56px;
        }

        .info-card-title {
            font-size: 1.25rem;
        }

        .info-card-description {
            font-size: 0.95rem;
        }

        .info-card-list strong {
            font-size: 0.95rem;
        }

        .info-card-list p {
            font-size: 0.9rem;
        }

        /* Payment breakdown */
        .payment-breakdown {
            gap: 1rem;
        }

        .payment-item {
            padding: 1.25rem;
        }

        .payment-badge {
            font-size: 0.8rem;
        }

        .payment-step {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .payment-percentage {
            font-size: 1rem;
        }

        .payment-when {
            font-size: 0.85rem;
        }

        .payment-info {
            font-size: 0.85rem;
        }

        /* Equipment */
        .equipment-section {
            padding: 1.5rem 1rem;
            border-radius: 16px;
            margin-bottom: 2rem;
        }

        .equipment-group {
            margin-bottom: 2rem;
        }

        .equipment-group-header h4 {
            font-size: 1.15rem;
        }

        .equipment-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .equipment-item {
            padding: 0.85rem 1rem;
        }

        .equipment-item span {
            font-size: 0.9rem;
        }

        /* Final CTA */
        .final-cta-section {
            padding: 2.5rem 1.25rem;
            border-radius: 16px;
        }

        .final-cta-title {
            font-size: 1.75rem;
        }

        .final-cta-subtitle {
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .final-cta-contacts {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }

        .contact-btn {
            justify-content: center;
            font-size: 0.9rem;
            padding: 12px 20px;
        }

        .btn-accept-final {
            width: 100%;
            justify-content: center;
            padding: 16px 32px;
            font-size: 1rem;
        }

        /* Other links */
        .other-link-item {
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
        }

        /* Container adjustments */
        .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }

    /* Extra small devices */
    @media (max-width: 576px) {
        .vehicle-content {
            padding: 1rem 0.75rem;
        }

        .pricing-section {
            padding: 1rem;
        }

        .cost-breakdown {
            padding: 1rem;
        }

        .cost-value {
            font-size: 1.3rem;
        }

        .cost-value-total {
            font-size: 1.5rem;
        }

        .vehicle-title {
            font-size: 1.5rem !important;
        }

        .pricing-title {
            font-size: 1.2rem;
        }

        .section-title-modern {
            font-size: 1.2rem;
        }

        .final-cta-title {
            font-size: 1.5rem;
        }

        .proposal-vehicle-card {
            border-radius: 12px;
        }

        .process-timeline-section,
        .equipment-section,
        .final-cta-section {
            border-radius: 12px;
        }
    }
</style>