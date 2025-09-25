@php
$settings = App\Models\Setting::get();
@endphp


@extends('frontend.partials.layout')

@section('title', 'Proposta de Importação | Izzycar')

@section('content')

<meta name="robots" content="noindex, nofollow">
@php
$image = null;
if ($proposal->images) {
$image = $proposal->images;
}
@endphp

@if($image)

<meta name="image" content="{{$image}}">
<meta property="og:image" content="{{ $image }}" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
@endif
<main>

    <section class="hero-section d-flex justify-content-center align-items-center" id="section_home_import">
        <div class="container">
            <div class="row">

                <div class="col-lg-8 col-12 mx-auto">
                    <h1 class="text-white text-center"> Proposta Izzycar</h1>
                    <p class="lead text-center">Aqui acompanhamos todo o processo de importação do seu automóvel com transparência e atenção a cada detalhe.</p>
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
        <!-- Dados do Veículo -->
        <div class="card shadow-sm mb-4 bg-black text-white">
            <div class="card-body">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="fw-bold text-accent mb-0">
                            {{ $proposal->brand }} {{ $proposal->model }} {{ $proposal->version }}
                        </h3>
                        @if(App\Models\ConvertedProposal::where('proposal_id', $proposal->id)->exists())
                        <span class="btn btn-success btn-sm"> Proposta aceite</span>
                        @else
                        <a href="#"
                            class="btn btn-success btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmAcceptModal-{{ $proposal->id }}">
                            Aceitar proposta
                        </a>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <ul class="list-unstyled">

                                <li class="text-white mb-2"><strong>@include('components.icons.calendar')</strong> {{$proposal->proposed_car_year_month}}</li>
                                <li class="text-white mb-2"><strong>@include('components.icons.fuel')</strong> {{$proposal->fuel}}</li>
                                <li class="text-white mb-2"><strong>@include('components.icons.road')</strong> {{$proposal->proposed_car_mileage}} KM</li>
                                <li class="text-white mb-2"><strong>@include('components.icons.gearbox')</strong> Automático</li>
                                @if($proposal->fuel !== 'Elétrico')
                                <li class="text-white mb-2"><strong>@include('components.icons.motor')</strong> {{$proposal->engine_capacity}} CC</li>
                                @endif

                            </ul>
                        </div>
                        <?php
                        $totalCostWithoutIva = $proposal->commission_cost + $proposal->inspection_commission_cost + $proposal->license_plate_cost + $proposal->registration_cost + $proposal->imt_cost + $proposal->ipo_cost + $proposal->transport_cost + ($proposal->proposed_car_value / 1.19);
                        $totalCost = $proposal->commission_cost + $proposal->inspection_commission_cost + $proposal->license_plate_cost + $proposal->isv_cost + $proposal->registration_cost + $proposal->imt_cost + $proposal->ipo_cost + $proposal->transport_cost + $proposal->proposed_car_value;
                        $serviceCost = $proposal->commission_cost + $proposal->inspection_commission_cost + $proposal->license_plate_cost + $proposal->registration_cost + $proposal->imt_cost + $proposal->ipo_cost + $proposal->transport_cost;
                        ?>

                        <div class="col-md-8">
                            <div class="text-center mb-4">

                                {{-- Foto grande do carro --}}

                                @if($proposal->images)
                                <img src=" {{ $image }}" alt="Carro" class="img-fluid rounded shadow" style="max-height: 400px; object-fit: cover;">
                                @endif
                            </div>
                        </div>

                        <div class="row text-center justify-content-center align-items-stretch">
                            <!-- Preço -->
                            <div class="col-sm-12 col-md-2 mb-3 d-flex">
                                <div class="p-3 bg-dark rounded shadow-sm flex-fill d-flex flex-column">
                                    <h5 class="fw-bold text-accent">{{ $proposal->proposed_car_value }} €</h5>
                                    <small class="text-white">Preço do carro</small>
                                </div>
                            </div>

                            <!-- ISV -->
                            <div class="col-sm-12 col-md-2 mb-3 d-flex">
                                <div class="p-3 bg-dark rounded shadow-sm flex-fill d-flex flex-column">
                                    <h5 class="fw-bold text-accent">{{ $proposal->isv_cost }} €</h5>
                                    <small class="text-white">ISV</small>
                                </div>
                            </div>

                            <!-- Serviço -->
                            <div class="col-sm-12 col-md-2 mb-3 d-flex">
                                <div class="p-3 bg-dark rounded shadow-sm flex-fill d-flex flex-column">
                                    <h5 class="fw-bold text-accent">{{ round($serviceCost, 0) }} €</h5>
                                    <small class="text-white">Serviço <sup>*</sup></small>
                                </div>
                            </div>

                            <!-- Preço Chave na Mão -->
                            <div class="col-sm-12 col-md-2 mb-3 d-flex">
                                <div class="p-3 bg-dark rounded shadow-sm flex-fill d-flex flex-column">
                                    <h5 class="fw-bold text-accent">{{ round($totalCost, 0) }} €</h5>
                                    <small class="text-white">Preço chave na mão</small>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-2 mb-3 d-flex">
                                <div class="p-3 bg-dark rounded shadow-sm flex-fill d-flex flex-column">
                                    <h5 class="fw-bold text-accent">{{ round($totalCostWithoutIva, 0) }} €</h5>
                                    <small class="text-white">Preço chave na mão sem iva (empresas)</small>
                                </div>
                            </div>

                            <!-- Ver Anúncio -->
                            <div class="col-sm-12 col-md-2 mb-3 d-flex">
                                <a href="{{ $proposal->url }}" target="_blank" class="flex-fill d-flex ">
                                    <div class="p-3 rounded shadow-sm flex-fill d-flex flex-column justify-content-center bg-finance">
                                        <h5 class="fw-bold text-accent  text-warning">Ver anúncio</h5>
                                    </div>
                                </a>
                            </div>
                        </div>


                    </div>
                </div>

            </div>
        </div>

        <!-- Equipamento -->
        <div class="container my-5">
            <div class="row ml-1 mt-5 g-4">
                @foreach ($attributes as $group => $attrs)
                <div class="col-12">
                    <div class="card shadow-sm mb-4 bg-black text-white">
                        <div class="card-body">
                            <h5 class="card-title text-accent fw-semibold mb-4">{{ $group }}</h5>

                            <div class="row">
                                @foreach ($attrs as $attr => $value)
                                @if(!in_array($attr, ['Potência', 'Cilindrada', 'Transmissão']))
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-check-circle-fill me-2" style="color: var(--accent-color);"></i>
                                        <span class="text-white">{{ $attr == $value ? $attr : $attr . ': ' . $value }}</span>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <!-- Processo -->
        <div class="container my-5">
            <div class="card shadow-sm mb-4 bg-black text-white">
                <div class="card-body">
                    <h4 class="fw-bold mb-3 text-accent">Da escolha à entrega: o nosso processo</h4>
                    <div class="row text-center">
                        <div class="col">Aprovação: <br><strong class="text-accent">3 dias</strong></div>
                        <div class="col">Pagamento: <br><strong class="text-accent">4 dias</strong></div>
                        <div class="col">Transporte: <br><strong class="text-accent">12 dias</strong></div>
                        <div class="col">ISV: <br><strong class="text-accent">3 dias</strong></div>
                        <div class="col">Entrega: <br><strong class="text-accent">22 dias</strong></div>
                    </div>
                    <p class="mt-3 text-white">
                        * Os prazos indicados são aproximados e podem sofrer alterações por motivos logísticos ou administrativos.
                    </p>
                </div>
            </div>
        </div>

        <!-- Formalização -->
        <div class="container my-5">
            <div class="card shadow-sm mb-4 bg-black text-white">
                <div class="card-body">
                    <h4 class="fw-bold mb-3 text-accent">Formalização do Processo</h4>
                    <p>O processo é oficializado com a assinatura de dois contratos:</p>
                    <ul>
                        <li><strong>Prestação de Serviços:</strong> Define o nosso serviço de forma clara e estruturada.</li>
                        <li><strong>Compra e Venda com o Stand:</strong> Formaliza a aquisição do veículo e legalização em Portugal.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Pagamentos -->
        <div class="container my-5">
            <div class="card shadow-sm mb-4 bg-black text-white">
                <div class="card-body">
                    <h4 class="fw-bold mb-3 text-accent">Pagamentos</h4>
                    <p><strong>Serviço da Izzycar:</strong></p>
                    <ul>
                        <li>{{$settings->where('label', 'init_payment')->first()->value}} na adjudicação do serviço (após assinatura do contrato).</li>
                        <li>{{$settings->where('label', 'final_payment')->first()->value}} na entrega do automóvel em Portugal.</li>
                    </ul>
                    <p><strong>Aquisição do Automóvel:</strong> O pagamento é feito diretamente ao stand, por transferência bancária.</p>
                </div>
            </div>
        </div>


        <!-- Contactos -->
        <div class="container my-5">
            <div class="card shadow-sm mb-4 bg-black text-white">
                <div class="card-body">
                    <h4 class="fw-bold mb-3 text-accent">Contactos</h4>
                    <p><strong>Telefone:</strong>{{$settings->where('label', 'phone')->first()->value}}</p>
                    <p><strong>Email:</strong>{{$settings->where('label', 'email')->first()->value}}</p>
                </div>
            </div>
        </div>

</main>
@endsection
@stack('scripts')
<style>
    .confirm-accept-modal .modal-content {
        background-color: var(--primary-color);
        color: white;
    }




    .confirm-accept-modal .form-label {
        color: white;
        /* labels em branco */
    }

    .modal-backdrop.show {
        backdrop-filter: blur(20px);
        /* Ajuste a intensidade aqui */
        background-color: rgba(0, 0, 0, 0.8);
        /* mantém escurecimento */
    }

    .confirm-accept-modal .form-control {
        background-color: var(--secondary-color);
        border: 1px solid var(--accent-color);
        color: white;
    }

    .confirm-accept-modal .form-control:focus {
        background-color: rgba(255, 255, 255, 0.15);
        border-color: var(--accent-color);
        color: white;
    }
</style>