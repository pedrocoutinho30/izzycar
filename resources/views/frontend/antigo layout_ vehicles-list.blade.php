@extends('frontend.partials.layout')

@section('title', 'Izzycar')
@php use Illuminate\Support\Str; @endphp
@section('content')
<header class="site-header d-flex flex-column justify-content-center align-items-center">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-5 col-12">
                <nav aria-label="breadcrumb">


                    <h2 class="text-white">Os nossos carros</h2>
                </nav>
            </div>

        </div>
    </div>
</header>


<section class="section-padding">
    <div class="container">
        <div class="row">

            <form id="filter-form" class="custom-form mt-4 pt-2 mb-lg-0 mb-5" role="search" onsubmit="return false;">

                <div class="row g-3">
                    {{-- Marca --}}
                    <div class="col-md-3">
                        <select name="brand" id="brand" class="form-select" aria-label="Marca">
                            <option value="">Marca</option>
                            @foreach($vehicles->pluck('brand')->unique() as $brand)
                            <option value="{{ $brand }}">{{ $brand }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Modelo (inicialmente vazio e desativado) --}}
                    <div class="col-md-3">
                        <select name="model" id="model" class="form-select" aria-label="Modelo" disabled>
                            <option value="">Modelo</option>
                        </select>
                    </div>

                    {{-- Ano --}}
                    <div class="col-md-3">
                        <select name="year" id="year" class="form-select" aria-label="Ano">
                            <option value="">Ano</option>
                            @foreach($vehicles->pluck('year')->unique()->sortDesc() as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Combustível --}}
                    <div class="col-md-3">
                        <select name="fuel" id="fuel" class="form-select" aria-label="Combustível">
                            <option value="">Combustível</option>
                            @foreach($vehicles->pluck('fuel')->unique() as $fuel)
                            <option value="{{ $fuel }}">{{ $fuel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- {{-- Quilómetros --}}
                    <div class="col-md-4">
                        <select name="kilometers_range" class="form-select" aria-label="Kms">
                            <option value="">Kms</option>
                            <option value="0-50000">Até 50.000 km</option>
                            <option value="50001-100000">50.001 a 100.000 km</option>
                            <option value="100001-150000">100.001 a 150.000 km</option>
                            <option value="150001+">Mais de 150.000 km</option>
                        </select>
                    </div> -->

                    {{-- Botão de pesquisa --}}
                    <div class="col-12 text-center">
                        <button id="clear-filters-btn" type="button" class="btn btn-secondary" style="display:none;">
                            Limpar filtros
                        </button>
                    </div>
                </div>
            </form>


            <div class="col-lg-8 col-12  mx-auto">
                @foreach ($vehicles as $vehicle)
                <div class="custom-block custom-block-topics-listing card-listing shadow-lg mb-5">
                    <div class="d-flex ">
                        {{-- Imagem ou Carrossel --}}
                        @if($vehicle->images->count() > 1)
                        <div id="carouselVehicle{{ $vehicle->id }}" class="carousel slide custom-block-image" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                @foreach($vehicle->images as $key => $image)
                                <div class="carousel-item {{ $key === 0 ? 'active' : '' }} image-wrapper">
                                    <img src="{{ asset('storage/' . $image->image_path) }}"
                                        class="d-block w-100 img-fluid"
                                        alt="Imagem {{ $vehicle->brand }} {{ $vehicle->model }}">
                                </div>
                                @endforeach
                            </div>
                            {{-- Controles do carrossel --}}
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselVehicle{{ $vehicle->id }}" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselVehicle{{ $vehicle->id }}" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Seguinte</span>
                            </button>
                        </div>
                        @else
                        <img src="{{ $vehicle->images->isNotEmpty() ? asset('storage/' . $vehicle->images->first()->image_path) : asset('images/default-car.jpg') }}"
                            class="custom-block-image img-fluid"
                            alt="Imagem {{ $vehicle->brand }} {{ $vehicle->model }}">
                        @endif
                        {{-- Info lateral --}}
                        <div class="custom-block-topics-listing-info d-flex">
                            <div>
                                <h3 class="text-white" >{{ $vehicle->brand }}</h3>
                                <h6 class="mb-2 text-white">{{ $vehicle->model }} {{ $vehicle->version }}</h6>

                                <p class="mb-1 list">
                                    @if(!empty($vehicle->year))
                                    <i class="bi bi-calendar-event"></i> {{ $vehicle->year }}&nbsp;&nbsp;
                                    @endif

                                    @if(!empty($vehicle->fuel) )
                                    <i class="fas fa-gas-pump"></i> {{ $vehicle->fuel }}&nbsp;&nbsp;
                                    @endif

                                    @if(!empty($vehicle->kilometers))
                                    <i class="fas fa-road"></i> {{ $vehicle->kilometers   }} KM
                                    @endif
                                </p>

                                <a href="{{ route('vehicles.details', [
                                    'brand' => Str::slug($vehicle->brand),
                                    'model' => Str::slug($vehicle->model),
                                    'id' => $vehicle->reference
                                ]) }}" class="btn custom-btn mt-3 mt-lg-4">Ver Detalhes</a>
                            </div>

                            {{-- Badge com o preço --}}
                            <span class="price ms-auto align-self-start px-4 py-3 fs-5">
                                {{ number_format($vehicle->sell_price, 0, ',', '.') }} €
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>




        </div>
    </div>
</section>


@endsection
<style>
    .custom-block-image {
        width: 200px;
        height: auto;
        object-fit: cover;
        border-radius: 8px;
    }

    .custom-block-topics-listing-info {
        padding-left: 20px;
        width: 100%;
        align-items: center;
    }

    .badge.bg-success {
        font-size: 1rem;
        padding: 10px 16px;
    }

    .image-wrapper {
        width: 300px;
        /* largura desejada */
        height: 200px;
        /* altura desejada */
        overflow: hidden;
        border-radius: 8px;
        /* opcional */
    }

    .image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* recorta e preenche */
        object-position: center;
        /* foca no centro */
        display: block;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const brandSelect = document.getElementById('brand');
        const modelSelect = document.getElementById('model');
        const yearSelect = document.getElementById('year');
        const fuelSelect = document.querySelector('select[name="fuel"]');
        const kilometersSelect = document.querySelector('select[name="kilometers_range"]');
        const vehiclesContainer = document.querySelector('.col-lg-8.col-12.mt-2.mx-auto');
        const clearFiltersBtn = document.getElementById('clear-filters-btn');
        clearFiltersBtn.addEventListener('click', clearFilters);

        // Função que verifica se algum filtro está ativo
        function isAnyFilterActive() {
            return (
                brandSelect.value !== '' ||
                modelSelect.value !== '' ||
                yearSelect.value !== '' ||
                fuelSelect.value !== ''
            );
        }

        // Atualiza a visibilidade do botão limpar filtros
        function updateClearButtonVisibility() {
            if (isAnyFilterActive()) {
                clearFiltersBtn.style.display = 'inline-block';
            } else {
                clearFiltersBtn.style.display = 'none';
            }
        }

        // Limpa todos os filtros
        function clearFilters() {
            console.log('Clear filters clicked');
            brandSelect.value = '';

            // Reset modelo
            modelSelect.innerHTML = '<option value="">Modelo</option>';
            modelSelect.disabled = true;

            // Reset ano
            yearSelect.innerHTML = '<option value="">Ano</option>';
            yearSelect.disabled = true;

            // Reset combustível
            fuelSelect.innerHTML = '<option value="">Combustível</option>';

            // Reset kms se quiser ativar (por enquanto está comentado no HTML)
            if (kilometersSelect) {
                kilometersSelect.value = '';
            }

            updateClearButtonVisibility();
            updateVehicles();
        }
        async function updateModels() {
            const brand = brandSelect.value;
            modelSelect.innerHTML = '<option value="">Modelo</option>';
            modelSelect.disabled = true;
            if (!brand) return;
            const res = await fetch(`/modelos-por-marca?brand=${encodeURIComponent(brand)}`);
            const models = await res.json();
            models.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m;
                opt.text = m;
                modelSelect.appendChild(opt);
            });
            modelSelect.disabled = false;
        }

        async function updateYears() {
            const brand = brandSelect.value;
            const model = modelSelect.value;
            yearSelect.innerHTML = '<option value="">Ano</option>';
            yearSelect.disabled = true;
            if (!brand) return;
            const url = `/anos-por-marca-modelo?brand=${encodeURIComponent(brand)}&model=${encodeURIComponent(model)}`;
            const res = await fetch(url);
            const years = await res.json();
            years.forEach(y => {
                const opt = document.createElement('option');
                opt.value = y;
                opt.text = y;
                yearSelect.appendChild(opt);
            });
            yearSelect.disabled = false;
        }

        async function updateFuels() {
            const brand = brandSelect.value;
            const model = modelSelect.value;
            const year = yearSelect.value;
            const params = new URLSearchParams({
                brand,
                model,
                year
            });
            const res = await fetch(`/combustiveis-por-marca-modelo-ano?${params.toString()}`);
            const fuels = await res.json();
            fuelSelect.innerHTML = '<option value="">Combustível</option>';
            fuels.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f;
                opt.text = f;
                fuelSelect.appendChild(opt);
            });
        }

        async function updateVehicles() {
            const params = new URLSearchParams({
                brand: brandSelect.value,
                model: modelSelect.value,
                year: yearSelect.value,
                fuel: fuelSelect.value,
            });

            const res = await fetch(`/viaturas-filtradas?${params.toString()}`);
            const vehicles = await res.json();

            vehiclesContainer.innerHTML = '';

            if (vehicles.length === 0) {
                vehiclesContainer.innerHTML = '<p>Nenhum veículo encontrado.</p>';
                return;
            }

            vehicles.forEach(vehicle => {
                vehiclesContainer.innerHTML += `
                <div class="custom-block custom-block-topics-listing bg-white shadow-lg mb-5">
                    <div class="d-flex">
                        <img src="${vehicle.image ?? '/images/default-car.jpg'}" class="custom-block-image img-fluid" alt="Imagem ${vehicle.brand} ${vehicle.model}">
                        <div class="custom-block-topics-listing-info d-flex">
                            <div>
                                <h5 class="mb-2">${vehicle.brand} ${vehicle.model} ${vehicle.version || ''}</h5>
                                <p class="mb-1"><strong>Ano:</strong> ${vehicle.year}</p>
                                <p class="mb-1"><strong>Combustível:</strong> ${vehicle.fuel}</p>
                                <a href="/vehicles/${vehicle.id}" class="btn custom-btn mt-3 mt-lg-4">Ver Detalhes</a>
                            </div>
                            <span class="badge bg-info ms-auto align-self-start px-4 py-3 fs-5">
                                ${Number(vehicle.sell_price).toLocaleString('pt-PT')} €
                            </span>
                        </div>
                    </div>
                </div>
            `;
            });
            updateClearButtonVisibility();
        }

        // Quando muda a marca
        brandSelect.addEventListener('change', async () => {
            await updateModels();
            await updateYears();
            await updateFuels();
            await updateVehicles();
        });

        // Quando muda o modelo
        modelSelect.addEventListener('change', async () => {
            await updateYears();
            await updateFuels();
            await updateVehicles();
        });

        // Quando muda o ano
        yearSelect.addEventListener('change', async () => {
            await updateFuels();
            await updateVehicles();
        });

        // Quando muda o combustível
        fuelSelect.addEventListener('change', updateVehicles);

        // Quando muda os kms
        kilometersSelect.addEventListener('change', updateVehicles);

        // Botão limpar filtros
        clearFiltersBtn.addEventListener('click', clearFilters);
        // Chama updateVehicles no carregamento pra mostrar todos os veículos inicialmente
        updateVehicles();
    });
</script>