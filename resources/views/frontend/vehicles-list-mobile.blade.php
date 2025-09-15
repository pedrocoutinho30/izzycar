  <div class="sticky-top mx-4" style="top: 110px; z-index: 1;">
      <h4 class="text-center mb-4" data-bs-toggle="collapse" data-bs-target="#filtersCollapse"
          aria-expanded="false" aria-controls="filtersCollapse">

          Filtros
          <span class="ms-2 fs-6" id="toggle-icon">&#9660;</span> <!-- seta para baixo -->
      </h4>
      <div class="collapse mb-3" id="filtersCollapse">
          <form id="filter-form" class="custom-form" role="search" onsubmit="return false;">
              <div class="vstack gap-3">
                  {{-- Marca --}}
                  <select name="brand" id="brandMobile" class="form-select" aria-label="Marca">
                      <option value="">Marca</option>
                      @foreach($vehicles->pluck('brand')->unique() as $brand)
                      <option value="{{ $brand }}">{{ $brand }}</option>
                      @endforeach
                  </select>

                  {{-- Modelo --}}
                  <select name="model" id="modelMobile" class="form-select" aria-label="Modelo" disabled>
                      <option value="">Modelo</option>
                  </select>

                  {{-- Ano --}}
                  <select name="year" id="yearMobile" class="form-select" aria-label="Ano">
                      <option value="">Ano</option>
                      @foreach($vehicles->pluck('year')->unique()->sortDesc() as $year)
                      <option value="{{ $year }}">{{ $year }}</option>
                      @endforeach
                  </select>

                  {{-- Combustível --}}
                  <select name="fuel" id="fuelMobile" class="form-select" aria-label="Combustível">
                      <option value="">Combustível</option>
                      @foreach($vehicles->pluck('fuel')->unique() as $fuel)
                      <option value="{{ $fuel }}">{{ $fuel }}</option>
                      @endforeach
                  </select>

                  {{-- Botão limpar filtros --}}
                  <div class="text-center">
                      <button id="clear-filters-btn-mobile" type="button" class="btn btn-secondary w-100" style="display:none;">
                          Limpar filtros
                      </button>
                  </div>
              </div>
          </form>
      </div>

      <div id="vehicles-container-mobile" class="row">
          @foreach ($vehicles as $vehicle)

          <div class="custom-block custom-block-transparent news-listing shadow-lg mb-5">
              <a href="{{ route('vehicles.details', [
                                    'brand' => Str::slug($vehicle->brand),
                                    'model' => Str::slug($vehicle->model),
                                    'id' => $vehicle->reference
                                ]) }}">

                  {{-- Imagem ou Carrossel --}}
                  @if($vehicle->images->count() > 1)
                  <div id="carouselVehicle{{ $vehicle->id }}" class="carousel slide mb-2" data-bs-ride="carousel">
                      <div class="carousel-inner" style="height: 200px;"> <!-- altura fixa -->
                          @foreach($vehicle->images as $key => $image)
                          <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                              <img src="{{ asset('storage/' . $image->image_path) }}" loading="lazy"
                                  class="d-block w-100  object-cover rounded" style="height: 200px; width: 100%;"
                                  alt="Imagem {{ $vehicle->brand }} {{ $vehicle->model }}">
                          </div>
                          @endforeach
                      </div>
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
                      class="img-fluid mb-2 object-cover rounded" style="height: 200px; width: 100%;" loading="lazy"
                      alt="Imagem {{ $vehicle->brand }} {{ $vehicle->model }}">
                  @endif


                  {{-- Marca / Modelo --}}
                  <div class="text-center mb-2">
                      <h3 class="text-accent mb-1">{{ $vehicle->brand }}</h3>
                      <h6 class="text-accent">{{ $vehicle->model }} {{ $vehicle->version }}</h6>
                  </div>

                  {{-- Especificações e preço --}}
                  <div class="d-flex flex-column flex-md-row justify-content-between align-items-center  p-2 ">

                      <div class="text-white mb-2 mb-md-0 text-center">
                          <p class="mb-1 text-center">
                              @if(!empty($vehicle->year))
                              <span class="icon-colored">@include('components.icons.calendar')</span>&nbsp;<span class="text-dark" style="font-size: 1.0rem !important;">{{ $vehicle->year }}&nbsp;&nbsp;</span>
                              @endif

                              @if(!empty($vehicle->fuel))
                              <span class="icon-colored">@include('components.icons.fuel')</span>&nbsp;<span class="text-dark" style="font-size: 1.0rem !important;">{{ $vehicle->fuel }}&nbsp;&nbsp;</span>
                              @endif

                              @if(!empty($vehicle->kilometers))
                              <span class="icon-colored">@include('components.icons.road')</span>&nbsp;<span class="text-dark" style="font-size: 1.0rem !important;">{{ $vehicle->kilometers }} KM</span>
                              @endif
                          </p>
                      </div>

                      <div>
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


  <script>
      document.addEventListener("DOMContentLoaded", () => {
          const collapse = document.getElementById("filtersCollapse");
          const toggleIcon = document.getElementById("toggle-icon");

          collapse.addEventListener("show.bs.collapse", () => {
              toggleIcon.innerHTML = "&#9650;"; // seta para cima
          });

          collapse.addEventListener("hide.bs.collapse", () => {
              toggleIcon.innerHTML = "&#9660;"; // seta para baixo
          });

          const brandSelect = document.getElementById('brandMobile');
          const modelSelect = document.getElementById('modelMobile');
          const yearSelect = document.getElementById('yearMobile');
          const fuelSelect = document.getElementById('fuelMobile');
          //   const kilometersSelect = document.querySelector('select[name="kilometers_range"]');
          const vehiclesContainer = document.getElementById('vehicles-container-mobile');
          const clearFiltersBtn = document.getElementById('clear-filters-btn-mobile');
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
                        <img src="/storage/${image.image_path}" 
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
        <img src="${vehicle.images.length > 0 ? '/storage/' + vehicle.images[0].image_path : '/images/default-car.jpg'}"
             class="custom-block-image img-fluid" loading="lazy"
             alt="Imagem ${vehicle.brand} ${vehicle.model}">`;
              }

              return `
    <div class="custom-block custom-block-transparent news-listing shadow-lg mb-5">
        <a href="/vehicles/${vehicle.brand.toLowerCase()}/${vehicle.model.toLowerCase()}/${vehicle.id}">
            <div class="d-flex">
                ${imagesHtml}
                <div class="custom-block-topics-listing-info d-flex">
                    <div>
                        <h3 class="text-accent">${vehicle.brand}</h3>
                        <h6 class="mb-2 text-accent">${vehicle.model} ${vehicle.version ?? ""}</h6>

                        <p class="mb-1 text-center list " style="font-size: 1.0rem !important;">
                            ${vehicle.year ? `<span class="icon-colored">@include('components.icons.calendar')</span>&nbsp;&nbsp;<span class="text-dark" style="font-size: 1.0rem !important;">${vehicle.year}&nbsp;&nbsp;</span>` : ""}
                            ${vehicle.fuel ? `<span class="icon-colored">@include('components.icons.fuel')</span>&nbsp;&nbsp;<span class="text-dark" style="font-size: 1.0rem !important;">${vehicle.fuel}&nbsp;&nbsp;</span>` : ""}
                            ${vehicle.kilometers ? `<span class="icon-colored">@include('components.icons.road')</span>&nbsp;&nbsp;<span class="text-dark" style="font-size: 1.0rem !important;">${vehicle.kilometers} KM</span>` : ""}
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
                  let imagesHtml = '';

                  if (vehicle.images && vehicle.images.length > 1) {
                      imagesHtml += `<div id="carouselVehicle${vehicle.id}" class="carousel slide mb-2" data-bs-ride="carousel">
            <div class="carousel-inner" style="height:200px;">`;

                      vehicle.images.forEach((image, index) => {
                          imagesHtml += `
                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                    <img src="/storage/${image.image_path}" class="d-block w-100 object-cover rounded" style="height:200px; width:100%;" loading="lazy"
                    alt="Imagem ${vehicle.brand} ${vehicle.model}">
                </div>`;
                      });

                      imagesHtml += `</div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselVehicle${vehicle.id}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselVehicle${vehicle.id}" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Seguinte</span>
            </button>
        </div>`;
                  } else {
                      const imagePath = vehicle.images && vehicle.images.length ? `/storage/${vehicle.images[0].image_path}` : '/images/default-car.jpg';
                      imagesHtml += `<img src="${imagePath}" class="img-fluid mb-2 object-cover rounded" style="height:200px; width:100%;" alt="Imagem ${vehicle.brand} ${vehicle.model}">`;
                  }

                  const html = `
        <div class="custom-block custom-block-transparent news-listing shadow-lg mb-5">
            <a href="/viaturas/${toSlug(vehicle.brand)}/${toSlug(vehicle.model)}/${vehicle.reference}">
                ${imagesHtml}

                <div class="text-center mb-2">
                    <h3 class="text-accent mb-1">${vehicle.brand}</h3>
                    <h6 class="text-accent">${vehicle.model} ${vehicle.version}</h6>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-2">
                    <div class="text-white mb-2 mb-md-0">
                        <p class="mb-1 text-center" > 
                            ${vehicle.year ? `<span class="icon-colored">@include('components.icons.calendar')</span> <span class="text-dark" style="font-size: 1.0rem !important;">${vehicle.year} </span> ` : ''}
                            ${vehicle.fuel ? `<span class="icon-colored">@include('components.icons.fuel')</span> <span class="text-dark" style="font-size: 1.0rem !important;">${vehicle.fuel} </span> ` : ''}
                            ${vehicle.kilometers ? `<span class="icon-colored">@include('components.icons.road')</span> <span class="text-dark" style="font-size: 1.0rem !important;">${vehicle.kilometers} KM</span>` : ''}
                        </p>
                    </div>

                    <div>
                        <span class="price ms-auto align-self-start px-4 py-3 fs-5">
                             ${Number(vehicle.sell_price).toLocaleString('pt-PT', { minimumFractionDigits: 0 })} €
                        </span>
                    </div>
                </div>
            </a>
        </div>
    `;

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
                  vehiclesContainer.innerHTML += html;
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
          //   kilometersSelect.addEventListener('change', updateVehicles);

          // Botão limpar filtros
          clearFiltersBtn.addEventListener('click', clearFilters);
          // Chama updateVehicles no carregamento pra mostrar todos os veículos inicialmente
          updateVehicles();
      });
  </script>
  <style>
      .object-cover {
          object-fit: cover;
      }

      #clear-filters-btn-mobile {
          background-color: var(--custom-btn-bg-color);
          color: var(--white-color);
      }
  </style>