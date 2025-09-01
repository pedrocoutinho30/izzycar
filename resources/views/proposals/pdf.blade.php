<!doctype html>
<html lang="en">

<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0NT5HLTZ2J"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-0NT5HLTZ2J');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>@yield('title',  "Proposta " . $proposal->brand . " " . $proposal->model . " " . $proposal->version )</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logo-arredondado.png') }}">

    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet">

    <link href=" {{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href=" {{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
    <link href=" {{ asset('css/layout.css') }}" rel="stylesheet">


    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />

    @stack('styles')

    <title>{{ $proposal->brand }} {{ $proposal->model }} - Proposta</title>

    {{-- Meta OG para redes sociais --}}
    <meta property="og:title" content="{{ $proposal->brand }} {{ $proposal->model }} {{ $proposal->version }}" />
    <meta property="og:description" content="Veja a proposta para o {{ $proposal->brand }} {{ $proposal->model }}." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />

    @php
    $image = null;
    if ($proposal->images) {
    $image = asset('storage/' . (json_decode($proposal->images, true)[0] ?? ''));
    }
    @endphp

    @if($image)
    <meta property="og:image" content="{{ $image }}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    @endif


</head>

<body id="top">

    <nav class="navbar navbar-expand-lg fixed-top" style="background-color: var(--primary-color);">
        <div class="container d-flex align-items-center justify-content-between">
            <a class="navbar-brand" href="#top">
                <img src=" {{ asset('img/logo-transparente.png') }}" alt="Logo" class="navbar-logo" style="height:auto; width:120px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>



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


        <div class="container my-5">
            <!-- Dados do Veículo -->
            <div class="card shadow-sm mb-4 bg-black text-white">
                <div class="card-body">
                    <h3 class="fw-bold mb-3  text-accent">{{ $proposal->brand }} {{ $proposal->model }} {{ $proposal->version }}</h3>
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
                <p><strong>Telefone:</strong>+351 914 250 947</p>
                <p><strong>Email:</strong> geral@izzycar.pt</p>
            </div>
        </div>

    </main>

    <!-- JAVASCRIPT FILES -->
    <script src=" {{ asset('js/jquery.min.js') }}"></script>
    <script src=" {{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src=" {{ asset('js/jquery.sticky.js') }}"></script>
    <script src=" {{ asset('js/click-scroll.js') }}"></script>
    <script src=" {{ asset('js/custom.js') }}"></script>

    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    @stack('scripts')
</body>

</html>