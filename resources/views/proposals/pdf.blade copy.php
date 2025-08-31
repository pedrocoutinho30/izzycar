

@extends('frontend.partials.layout', ['no_header' => true, 'no_footer' => true])

@section('content')
<section class="hero-section d-flex justify-content-center align-items-center" id="section_home_import">
    <div class="container">
        <div class="row">

            <div class="col-lg-8 col-12 mx-auto">
                <h1 class="text-white text-center"> Proposta Izzycar</h1>
                <p class="lead">Aqui acompanhamos todo o processo de importação do seu automóvel com transparência e atenção a cada detalhe.</p>
            </div>
        </div>

    </div>
</section>


<div class="container my-5">
    <!-- Dados do Veículo -->
    <div class="card shadow-sm mb-4 bg-black text-white">
        <div class="card-body">
            <h3 class="fw-bold mb-3  text-accent">{{ $proposal->brand }} {{ $proposal->model }}</h3>
            <div class="row">
                <div class="col-md-4">
                    <ul class="list-unstyled">

                        <li><strong>@include('components.icons.calendar')</strong> {{$proposal->proposed_car_year_month}}</li>
                        <li><strong>@include('components.icons.fuel')</strong> {{$proposal->fuel}}</li>
                        <li><strong>@include('components.icons.road')</strong> {{$proposal->proposed_car_mileage}} KM</li>

                    </ul>
                </div>
                <?php
                $totalCost = $proposal->commission_cost + $proposal->inspection_commission_cost + $proposal->license_plate_cost + $proposal->isv_cost + $proposal->registration_cost + $proposal->imt_cost + $proposal->ipo_cost + $proposal->transport_cost + $proposal->proposed_car_value;
                $serviceCost = $proposal->commission_cost + $proposal->inspection_commission_cost + $proposal->license_plate_cost + $proposal->registration_cost + $proposal->imt_cost + $proposal->ipo_cost + $proposal->transport_cost;
                ?>

                <div class="col-md-8">
                    <div class="text-center mb-4">

                        {{-- Foto grande do carro --}}
                        <?php
                        if ($proposal->images) {

                            $image = json_decode($proposal->images, true)[0] ?? null;
                        }
                        ?>

                        <img src="{{ asset('storage/' . $image) }}" alt="Carro" class="img-fluid rounded shadow" style="max-height: 400px; object-fit: cover;">
                    </div>
                </div>

                <div class="row text-center justify-content-center align-items-stretch">
                    <!-- Preço -->
                    <div class="col-md-2 mb-3 d-flex">
                        <div class="p-3 bg-dark rounded shadow-sm flex-fill d-flex flex-column">
                            <h5 class="fw-bold text-accent">{{ $proposal->proposed_car_value }} €</h5>
                            <small class="text-white">Preço do carro</small>
                        </div>
                    </div>

                    <!-- ISV -->
                    <div class="col-md-2 mb-3 d-flex">
                        <div class="p-3 bg-dark rounded shadow-sm flex-fill d-flex flex-column">
                            <h5 class="fw-bold text-accent">{{ $proposal->isv_cost }} €</h5>
                            <small class="text-white">ISV</small>
                        </div>
                    </div>

                    <!-- Serviço -->
                    <div class="col-md-2 mb-3 d-flex">
                        <div class="p-3 bg-dark rounded shadow-sm flex-fill d-flex flex-column">
                            <h5 class="fw-bold text-accent">{{ round($serviceCost, 0) }} €</h5>
                            <small class="text-white">Serviço <sup>*</sup></small>
                        </div>
                    </div>

                    <!-- Preço Chave na Mão -->
                    <div class="col-md-2 mb-3 d-flex">
                        <div class="p-3 bg-dark rounded shadow-sm flex-fill d-flex flex-column">
                            <h5 class="fw-bold text-accent">{{ round($totalCost, 0) }} €</h5>
                            <small class="text-white">Preço chave na mão</small>
                        </div>
                    </div>

                    <!-- Ver Anúncio -->
                    <div class="col-md-2 mb-3 d-flex">
                        <a href="{{ $proposal->url }}" target="_blank" class="flex-fill d-flex">
                            <div class="p-3 bg-dark rounded shadow-sm flex-fill d-flex flex-column justify-content-center">
                                <h5 class="fw-bold text-accent">Ver anúncio</h5>
                            </div>
                        </a>
                    </div>
                </div>


            </div>
        </div>

    </div>
</div>

<!-- Processo -->
<div class="container my-5">
    <div class="card shadow-sm mb-4 bg-black text-white">
        <div class="card-body">
            <h4 class="fw-bold mb-3 text-accent">Da escolha à entrega: o nosso processo</h4>
            <div class="row text-center">
                <div class="col">Aprovação: <br><strong class="text-accent">2 dias</strong></div>
                <div class="col">Pagamento: <br><strong class="text-accent">3 dias</strong></div>
                <div class="col">Transporte: <br><strong class="text-accent">12 dias</strong></div>
                <div class="col">ISV: <br><strong class="text-accent">3 dias</strong></div>
                <div class="col">Entrega: <br><strong class="text-accent">20 dias</strong></div>
            </div>
            <p class="mt-3 text-muted">
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
            <div class="card-body">
                <h4 class="fw-bold mb-3 text-accent">Pagamentos</h4>
                <p><strong>Serviço da Izzycar:</strong></p>
                <ul>
                    <li>50% na adjudicação do serviço (após assinatura do contrato).</li>
                    <li>50% na entrega do automóvel em Portugal.</li>
                </ul>
                <p><strong>Aquisição do Automóvel:</strong> O pagamento é feito diretamente ao stand, por transferência bancária.</p>
            </div>
        </div>
    </div>
</div>


<!-- Contactos -->
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="fw-bold mb-3">Contactos</h4>
        <p><strong>Telefone:</strong> +351 914 250 947</p>
        <p><strong>Email:</strong> geral@izzycar.pt</p>
    </div>
</div>

</div>
@endsection