<div class="row">

    {{-- Filtros à esquerda --}}
    <div class="col-lg-3 mb-4 pr-2">
        <div class="sticky-top" style="top: 110px;">
            <h4 class="text-center mb-4">Filtros</h4>

            <form id="filter-form" class="custom-form" role="search" onsubmit="return false;">
                <div class="vstack gap-3">
                    {{-- Marca --}}
                    <select name="brand" id="brand" class="form-select" aria-label="Marca">
                        <option value="">Marca</option>
                        @foreach($vehicles->pluck('brand')->unique() as $brand)
                        <option value="{{ $brand }}">{{ $brand }}</option>
                        @endforeach
                    </select>

                    {{-- Modelo --}}
                    <select name="model" id="model" class="form-select" aria-label="Modelo" disabled>
                        <option value="">Modelo</option>
                    </select>

                    {{-- Ano --}}
                    <select name="year" id="year" class="form-select" aria-label="Ano">
                        <option value="">Ano</option>
                        @foreach($vehicles->pluck('year')->unique()->sortDesc() as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>

                    {{-- Combustível --}}
                    <select name="fuel" id="fuel" class="form-select" aria-label="Combustível">
                        <option value="">Combustível</option>
                        @foreach($vehicles->pluck('fuel')->unique() as $fuel)
                        <option value="{{ $fuel }}">{{ $fuel }}</option>
                        @endforeach
                    </select>

                    {{-- Botão limpar filtros --}}
                    <div class="text-center">
                        <button id="clear-filters-btn" type="button" class="btn btn-secondary w-100" style="display:none;">
                            Limpar filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>



    {{-- Lista de carros à direita --}}
    <div class="col-lg-9 col-12">
        <div id="vehicles-container" class="row">
            @foreach ($vehicles as $vehicle)

            <div class="custom-block custom-block-transparent news-listing shadow-lg mb-5">
                <a href="{{ route('vehicles.details', [
                                    'brand' => Str::slug($vehicle->brand),
                                    'model' => Str::slug($vehicle->model),
                                    'id' => $vehicle->reference
                                ]) }}">
                    <div class="d-flex ">
                        {{-- Imagem ou Carrossel --}}
                        @if($vehicle->images->count() > 1)
                        <!--  data-bs-ride="carousel" para rodar imagens auto -->
                        <div id="carouselVehicle{{ $vehicle->id }}" class="carousel slide custom-block-image">
                            <div class="carousel-inner">
                                @foreach($vehicle->images as $key => $image)
                                <div class="carousel-item {{ $key === 0 ? 'active' : '' }} image-wrapper">
                                    <img src="{{  $image->image_path }}"
                                        class="d-block img-fluid" loading="lazy"
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
                        <img src="{{  $vehicle->images->first()->image_path  }}"
                            class="custom-block-image img-fluid" loading="lazy"
                            alt="Imagem {{ $vehicle->brand }} {{ $vehicle->model }}">
                        @endif
                        {{-- Info lateral --}}
                        <div class="custom-block-topics-listing-info d-flex">
                            <div>
                                <h3 class="text-accent">{{ $vehicle->brand }}</h3>
                                <h6 class="mb-2 text-accent">{{ $vehicle->model }} {{ $vehicle->version }}</h6>

                                <p class="mb-1 list">
                                    @if(!empty($vehicle->year))
                                    <span class="icon-colored">@include('components.icons.calendar')</span>&nbsp;&nbsp;
                                    <span class="text-dark">{{ $vehicle->year }} </span>&nbsp;&nbsp;
                                    @endif

                                    @if(!empty($vehicle->fuel) )
                                    <span class="icon-colored">@include('components.icons.fuel')</span>&nbsp;&nbsp;<span class="text-dark">{{ $vehicle->fuel }}</span>&nbsp;&nbsp;
                                    @endif

                                    @if(!empty($vehicle->kilometers))
                                    <span class="icon-colored">@include('components.icons.road')</span>&nbsp;&nbsp;<span class="text-dark">{{ $vehicle->kilometers   }} KM</span>
                                    @endif
                                </p>

                            </div>

                            {{-- Badge com o preço --}}
                            <span class="price  ms-auto align-self-start px-4 py-3 fs-5">
                                {{ number_format(round($vehicle->sell_price), 0, ',', ' ') }}&nbsp;€
                            </span>


                        </div>
                    </div>
                </a>

            </div>
            @endforeach
        </div>

    </div>
</div>

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
        width: 200px !important;
        /* largura desejada */
        height: 200px;
        /* altura desejada */
        overflow: hidden;
        border-radius: 8px;
        /* opcional */
    }

    .image-wrapper img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        /* recorta e preenche */
        object-position: center;
        /* foca no centro */
        display: block;
    }

    .sticky-top {
        z-index: 100;
    }

    #filter-form select {
        background-color: var(--section-bg-color);
        /* fundo escuro */
        color: var(--white-color);
        border: 1px solid var(--border-color);
    }

    #filter-form select:disabled {
        opacity: 0.5;
    }

    #clear-filters-btn {
        background-color: var(--custom-btn-bg-color);
        color: var(--white-color);
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const brandSelect = document.getElementById('brand');
        const modelSelect = document.getElementById('model');
        const yearSelect = document.getElementById('year');
        const fuelSelect = document.querySelector('select[name="fuel"]');
        const kilometersSelect = document.querySelector('select[name="kilometers_range"]');
        const vehiclesContainer = document.getElementById('vehicles-container');
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

        function renderVehicle(vehicle) {
            const hasMultipleImages = vehicle.images.length > 1;

            let imagesHtml = "";

            if (hasMultipleImages) {
                imagesHtml = `
        <div id="carouselVehicle${vehicle.id}" class="carousel slide custom-block-image">
            <div class="carousel-inner">
                ${vehicle.images.map((image, index) => `
                    <div class="carousel-item ${index === 0 ? 'active' : ''} image-wrapper">
                        <img src="${image.image_path}" 
                             class="d-block img-fluid" loading="lazy"
                             alt="Imagem ${vehicle.brand} ${vehicle.model}">
                    </div>
                `).join("")}
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselVehicle${vehicle.id}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselVehicle${vehicle.id}" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
                <span class="visually-hidden">Seguinte</span>
            </button>
        </div>`;
            } else {
                imagesHtml = `
        <img src="${ vehicle.images[0].image_path }"
             class="custom-block-image img-fluid" loading="lazy"
             alt="Imagem ${vehicle.brand} ${vehicle.model}">`;
            }

            return `
    <div class="custom-block custom-block-transparent news-listing shadow-lg mb-5">
        <a href="/viaturas/${toSlug(vehicle.brand)}/${toSlug(vehicle.model)}/${vehicle.reference}">
            <div class="d-flex">
                ${imagesHtml}
                <div class="custom-block-topics-listing-info d-flex">
                    <div>
                        <h3 class="text-accent">${vehicle.brand}</h3>
                        <h6 class="mb-2 text-accent">${vehicle.model} ${vehicle.version ?? ""}</h6>

                        <p class="mb-1 list">
                            ${vehicle.year ? `<span class="icon-colored">@include('components.icons.calendar')</span>&nbsp;&nbsp;<span class="text-dark">${vehicle.year}&nbsp;&nbsp;</span>` : ""}
                            ${vehicle.fuel ? `<span class="icon-colored">@include('components.icons.fuel')</span>&nbsp;&nbsp;<span class="text-dark">${vehicle.fuel}&nbsp;&nbsp;</span>` : ""}
                            ${vehicle.kilometers ? `<span class="icon-colored">@include('components.icons.road')</span>&nbsp;&nbsp;<span class="text-dark">${vehicle.kilometers} KM</span>` : ""}
                        </p>
                    </div>
                    <span class="price ms-auto align-self-start px-4 py-3 fs-5">
                        ${Number(vehicle.sell_price).toLocaleString('pt-PT', { minimumFractionDigits: 0 })} €
                    </span>
                </div>
            </div>
        </a>
    </div>`;
        }

        function toSlug(str) {
            return str
                .toLowerCase()
                .normalize('NFD') // separa acentos
                .replace(/[\u0300-\u036f]/g, '') // remove acentos
                .replace(/\s+/g, '-') // espaços por hífen
                .replace(/[^a-z0-9-]/g, '') // remove caracteres inválidos
                .replace(/--+/g, '-') // múltiplos hífens para 1
                .replace(/^-+|-+$/g, ''); // remove hífens no início/fim
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


            if (vehicles.length === 0) {
                vehiclesContainer.innerHTML = '<p>Nenhum veículo encontrado.</p>';
                return;
            }

            vehiclesContainer.innerHTML = ""; // limpa os resultados anteriores
            vehicles.forEach(vehicle => {

                vehiclesContainer.innerHTML += renderVehicle(vehicle);
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
        // kilometersSelect.addEventListener('change', updateVehicles);

        // Botão limpar filtros
        clearFiltersBtn.addEventListener('click', clearFilters);
        // Chama updateVehicles no carregamento pra mostrar todos os veículos inicialmente
        updateVehicles();
    });
</script>