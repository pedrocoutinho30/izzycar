      <form id="importForm" class="row g-3">
          @csrf

          {{-- Nome --}}
          <div class="col-md-6">
              <label for="name" class="form-label">Nome completo<span class="required-star">*</span></label>
              <input type="text" name="name" id="name" class="form-control" required>
          </div>

          {{-- Telemóvel --}}
          <div class="col-md-6">
              <label for="phone" class="form-label">Telemóvel<span class="required-star">*</span></label>
              <input type="text" name="phone" id="phone" class="form-control" required>
          </div>

          {{-- Email --}}
          <div class="col-md-6">
              <label for="email" class="form-label">E-mail<span class="required-star">*</span></label>
              <input type="email" name="email" id="email" class="form-control" required>
          </div>

          {{-- Como conheceu --}}
          <div class="col-md-6">
              <label for="source" class="form-label">Como conheceu a Izzycar?</label>
              <select name="source" id="source" class="form-control">
                  <option value="">Escolha uma opção</option>
                  <option value="Google">Google</option>
                  <option value="Facebook">Facebook</option>
                  <option value="Instagram">Instagram</option>
                  <option value="Amigo">Amigos/Família</option>
                  <option value="Olx">Olx/Standvirtual</option>
                  <option value="Outro">Outros</option>
              </select>
          </div>

          {{-- Mensagem --}}
          <div class="col-12">
              <label for="message" class="form-label">Mensagem</label>
              <textarea name="message" id="message" class="form-control" rows="3"></textarea>
          </div>

          {{-- Tem algum anúncio identificado? --}}
          <div class="col-12">
              <label class="form-label">Tem algum anúncio identificado?<span class="required-star">*</span><i class="bi bi-info-circle"
                      data-bs-toggle="tooltip"
                      data-bs-placement="right"
                      title="Se tiver um anúncio em vista, envie-nos para ajudarmos a verificar e contactar o stand. Se souber o carro que procura, forneça o máximo de informação para uma pesquisa personalizada. Se não souber, entraremos em contacto consigo o mais breve possível."></i></label>
              <select name="ad_option" id="ad_option" class="form-control" required>
                  <option value="">Escolha uma opção</option>
                  <option value="sim">Sim</option>
                  <option value="nao_sei">Não, mas sei o carro que procuro</option>
                  <option value="nao_nao">Não, nem sei o que procuro</option>
              </select>
              <small class="text-accent">Forneça o máximo de informações possível para que possamos ajudá-lo da melhor forma.</small>
          </div>

          {{-- Caso "Sim" --}}
          <div id="ad_links_box" class="col-12 d-none">
              <label for="ad_links" class="form-label">Coloque aqui os links dos anúncios identificados<span class="required-star">*</span></label>
              <textarea name="ad_links" id="ad_links" class="form-control" rows="3"></textarea>
          </div>

          {{-- Caso "Não mas sei o que procuro" --}}
          <div id="preferences_box" class="col-12 d-none">

              <div class="row g-3">
                  <div class="col-md-4">
                      <label for="brand" class="form-label">Marca<span class="required-star">*</span></label>

                      <select name="brand" id="brand" class="form-control">
                          <option value="">Escolha uma opção</option>
                          @foreach ($brands as $brand)
                          <option value="{{ $brand->name }}">{{ $brand->name }}</option>
                          @endforeach
                      </select>
                  </div>

                  <div class="col-md-4">
                      <label for="model" class="form-label">Modelo<span class="required-star">*</span></label>
                      <select name="model" id="model" class="form-control" disabled>
                          <option value="">Escolha uma opção</option>
                      </select>
                  </div>
                  <div class="col-md-4">
                      <label for="submodel" class="form-label">Sub-modelo<span class="required-star">*</span></label>
                      <input name="submodel" id="submodel" class="form-control">
                  </div>
                  <div class="col-md-4">
                      <label for="fuel" class="form-label">Combustível<span class="required-star">*</span></label>
                      <select name="fuel" id="fuel" class="form-control">
                          <option value="">Escolha uma opção</option>
                          <option value="gasolina">Gasolina</option>
                          <option value="diesel">Diesel</option>
                          <option value="hibrido_plugin_gasolina">Híbrido Plug-in/Gasolina</option>
                          <option value="hibrido_plugin_diesel">Híbrido Plug-in/Diesel</option>
                          <option value="eletrico">Elétrico</option>
                      </select>
                  </div>


                  <div class="col-md-4">
                      <label for="year_min" class="form-label">Ano - Mínimo<span class="required-star">*</span></label>
                      <!-- <input type="number" name="year_min" id="year_min" class="form-control"> -->
                      <select name="year_min" id="year_min" class="form-control">
                          <option value="">Escolha um ano</option>
                          @php
                          $currentYear = date('Y');
                          for($y = $currentYear; $y >= 1950; $y--){
                          echo "<option value=\"$y\">$y</option>";
                          }
                          @endphp
                      </select>
                  </div>

                  <div class="col-md-4">
                      <label for="km_max" class="form-label">Quilómetros - Máximo até<span class="required-star">*</span></label>
                     <select class="form-control" id="km_max" name="km_max">
                      <option value="">Escolha uma opção</option>
                        <option value="10000">10.000 km</option>
                        <option value="20000">20.000 km</option>
                        <option value="30000">30.000 km</option>
                        <option value="40000">40.000 km</option>
                        <option value="50000">50.000 km</option>
                        <option value="60000">60.000 km</option>
                        <option value="70000">70.000 km</option>
                        <option value="80000">80.000 km</option>
                        <option value="90000">90.000 km</option>
                        <option value="100000">100.000 km</option>
                        <option value="150000">150.000 km</option>
                        <option value="200000">200.000 km</option>
                        <option value="+200000">+ de 200.000 km</option>
                          
                    </select>

                      
                  </div>

                  <div class="col-md-4">
                      <label for="color" class="form-label">Cor de preferência</label>
                      <input type="text" name="color" id="color" class="form-control">
                  </div>

                  <!-- <div class="col-md-6">
                      <label for="budget" class="form-label">Budget<span class="required-star">*</span></label>
                      <select class="form-control" id="budget" name="budget">
                          <option value="">Escolha uma opção</option>
                          <option value="10k_15k">10.000€ - 15.000€</option>
                          <option value="15k_20k">15.000€ - 20.000€</option>
                          <option value="20k_25k">20.000€ - 25.000€</option>
                          <option value="25k_30k">25.000€ - 30.000€</option>
                          <option value="30k_40k">30.000€ - 40.000€</option>
                          <option value="40k_50k">40.000€ - 50.000€</option>
                          <option value="50k_60k">50.000€ - 60.000€</option>
                          <option value="60k_70k">60.000€ - 70.000€</option>
                          <option value="70k_80k">70.000€ - 80.000€</option>
                          <option value="80k+">Mais de 80.000€</option>
                      </select>
                  </div> -->


                  <div class="col-md-6">
                      <label for="budget" class="form-label">Budget <span class="text-danger">*</span></label>
                      <div class="position-relative">
                          <input type="range" min="10000" max="100000" step="1000" value="40000" class="form-range" name="budget" id="budgetSlider">
                          <span id="budgetValue" class="position-absolute" style="top:25px; left:50%; transform:translateX(-50%);  font-weight:bold;">
                              40.000€
                          </span>

                      </div>
                  </div>
                  <div class="col-md-6">
                      <label class="form-label">Caixa<span class="required-star">*</span></label>
                      <div class="row ms-2">
                          <div class="form-check col-md-4">
                              <input class="form-check-input" type="radio" name="gearbox" value="indiferente" id="gear_indiferente">
                              <label class="form-check-label" for="gear_indiferente">Indiferente</label>
                          </div>
                          <div class="form-check col-md-4">
                              <input class="form-check-input" type="radio" name="gearbox" value="automatica" id="gear_auto">
                              <label class="form-check-label" for="gear_auto">Automática</label>
                          </div>
                          <div class="form-check col-md-4">
                              <input class="form-check-input" type="radio" name="gearbox" value="manual" id="gear_manual">
                              <label class="form-check-label" for="gear_manual">Manual</label>
                          </div>
                      </div>
                  </div>

                  <div class="col-md-12">
                      <label for="extras" class="form-label">Extras da Viatura</label>
                      <textarea name="extras" id="extras" class="form-control" rows="2"></textarea>
                  </div>
              </div>
          </div>

          {{-- Botão --}}
          <div class="col-12 text-center">
              <button type="submit" class="btn bg-finance btn-lg" id="contactSubmitProposalBtn">Enviar Pedido</button>
          </div>
          <script>
              document.addEventListener('DOMContentLoaded', function() {

                  const slider = document.getElementById('budgetSlider');
                  const budgetValue = document.getElementById('budgetValue');

                  slider.addEventListener('input', function() {
                      let value = parseInt(this.value);

                      // Se o usuário chegar no máximo, mostra "+81.000€"
                      if (value >= parseInt(this.max)) {
                          budgetValue.textContent = "+100.000€";
                      } else {
                          budgetValue.textContent = value.toLocaleString() + "€";
                      }

                      // posiciona o valor acima do slider
                      const percent = (value - this.min) / (this.max - this.min);
                      budgetValue.style.left = `calc(${percent * 100}% )`;
                  });
                  const form = document.querySelector('#contactModal form');
                  const submitBtn = document.getElementById('contactSubmitProposalBtn');
                  if (form && submitBtn) {
                      form.addEventListener('submit', function() {
                          submitBtn.disabled = true;
                          submitBtn.innerText = 'A enviar...';
                      });
                  }
              });
          </script>
      </form>

      <div id="formResponse" class="mt-3" style="display:none;"></div>


      @push('styles')
      <style>
          #budgetSlider::-webkit-slider-thumb {
              background-color: var(--accent-color);
              /* cor da bola */
              border: none;
              border-radius: 50%;
              border-color: var(--accent-color);
              cursor: pointer;
              height: 20px;
              width: 20px;
              margin-top: -7px;
              /* ajusta verticalmente a thumb */
          }

          /* Firefox */
          #budgetSlider::-moz-range-thumb {
              background-color: var(--accent-color);
              border: none;
              border-radius: 50%;
              border-color: var(--accent-color);
              cursor: pointer;
              height: 20px;
              width: 20px;
          }

          /* Opera */
          #budgetSlider::-o-range-thumb {
              background-color: var(--accent-color);
                      border-color: var(--accent-color);
              border: none;
              border-radius: 50%;
              cursor: pointer;
              height: 20px;
              width: 20px;
          }

          .required-star {
              color: var(--accent-color);
              margin-left: 2px;
              margin-right: 2px;
          }

          #formPropostaModal .modal-content {
              background-color: var(--primary-color);
              color: white;
              /* para texto ficar legível */
          }

          #formPropostaModal .form-label {
              color: white;
              /* labels em branco */
          }

          .modal-backdrop.show {
              backdrop-filter: blur(20px);
              /* Ajuste a intensidade aqui */
              background-color: rgba(0, 0, 0, 0.8);
              /* mantém escurecimento */
          }

          #formPropostaModal .form-control {
              background-color: var(--secondary-color);
              border: 1px solid var(--accent-color);
              color: white;
          }

          #formPropostaModal .form-control:focus {
              background-color: rgba(255, 255, 255, 0.15);
              border-color: var(--accent-color);
              color: white;
          }

          #formPropostaModal .btn-primary {
              background-color: var(--accent-color);
              border: none;
          }
      </style>
      @endpush


      @push('scripts')
      <script>
          document.addEventListener("DOMContentLoaded", function() {
              var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
              var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                  return new bootstrap.Tooltip(tooltipTriggerEl)
              })
              document.getElementById('ad_option').addEventListener('change', function() {
                  let adLinksBox = document.getElementById('ad_links_box');
                  let preferencesBox = document.getElementById('preferences_box');

                  adLinksBox.classList.add('d-none');
                  preferencesBox.classList.add('d-none');

                  if (this.value === 'sim') {
                      adLinksBox.classList.remove('d-none');
                  } else if (this.value === 'nao_sei') {
                      preferencesBox.classList.remove('d-none');
                  }
              });

              const brandSelect = document.getElementById('brand');
              const modelSelect = document.getElementById('model');
              if (!brandSelect || !modelSelect || !window.brands) return;

              function updateModels() {
                  const selectedBrand = brandSelect.value;
                  modelSelect.innerHTML = '<option value="">Escolha uma opção</option>';
                  modelSelect.disabled = !selectedBrand;

                  if (!selectedBrand) return;

                  const brandObj = window.brands.find(b => b.name === selectedBrand);
                  if (brandObj && brandObj.models) {
                      brandObj.models.forEach(function(model) {
                          const option = document.createElement('option');
                          option.value = model.name;
                          option.textContent = model.name;
                          modelSelect.appendChild(option);
                      });
                  }
              }

              updateModels();
              brandSelect.addEventListener('change', updateModels);
          });
      </script>
      <script>
          window.brands = @json($brands);
      </script>
      <script>
          $(document).on("submit", "#importForm", function(e) {
              e.preventDefault();

              let form = $(this);
              let url = form.data("action") || "{{ route('frontend.import-submit') }}";

              $.ajax({
                  url: url,
                  type: "POST",
                  data: form.serialize(),
                  success: function(response) {
                      $("#formResponse")
                          .hide()
                          .html('<div class="alert alert-success">✅ Pedido enviado com sucesso!</div>')
                          .fadeIn();

                      form.trigger("reset");

                      if ($("#formPropostaModal").length) {
                          setTimeout(function() {
                              $("#formPropostaModal").modal("hide");
                              $("#formResponse").fadeOut();
                          }, 2000);
                      }
                  },
                  error: function() {
                      $("#formResponse").html(
                          '<div class="alert alert-danger">❌ Ocorreu um erro. Tente novamente.</div>'
                      );
                  }
              });
          });

          // Script para carregar modelos conforme a marca
          document.addEventListener("DOMContentLoaded", function() {

          });
      </script>

      <script>
          document.addEventListener("DOMContentLoaded", function() {
              const adOption = document.getElementById("ad_option");
              const adLinksBox = document.getElementById("ad_links_box");
              const adLinks = document.getElementById("ad_links");

              const brand = document.getElementById("brand");
              const model = document.getElementById("model");
              const submodel = document.getElementById("submodel");
              const fuel = document.getElementById("fuel");
              const year_min = document.getElementById("year_min");
              const km_max = document.getElementById("km_max");
              const budget = document.getElementById("budget");
              const gearbox = document.getElementsByName("gearbox");



              adOption.addEventListener("change", function() {
                  // Resetar
                  adLinksBox.classList.add("d-none");
                  adLinks.removeAttribute("required");
                  brand.removeAttribute("required");
                  model.removeAttribute("required");
                  submodel.removeAttribute("required");
                  fuel.removeAttribute("required");
                  year_min.removeAttribute("required");
                  km_max.removeAttribute("required");
                  budget.removeAttribute("required");
                  gearbox.forEach(g => g.removeAttribute("required"));
                  // Mostrar conforme escolha

                  if (this.value === "sim") {
                      adLinksBox.classList.remove("d-none");
                      adLinks.setAttribute("required", "required");
                  }

                  if (this.value === "nao_sei") {
                      brand.setAttribute("required", "required");
                      model.setAttribute("required", "required");

                      submodel.setAttribute("required", "required");
                      fuel.setAttribute("required", "required");
                      year_min.setAttribute("required", "required");
                      km_max.setAttribute("required", "required");
                      budget.setAttribute("required", "required");
                      gearbox.forEach(g => g.setAttribute("required", "required"));
                  }
              });
          });
      </script>
      @endpush