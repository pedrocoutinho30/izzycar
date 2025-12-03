      <form id="importForm" class="row g-4">
          @csrf

          <div class="col-12">
              <div class="form-section-header">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                      <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                  <span>Informações Pessoais</span>
              </div>
          </div>

          {{-- Nome --}}
          <div class="col-md-6">
              <label for="name" class="form-label">
                  Nome completo<span class="required-star">*</span>
              </label>
              <input type="text" name="name" id="name" class="form-control" placeholder="João Silva" required>
          </div>

          {{-- Telemóvel --}}
          <div class="col-md-6">
              <label for="phone" class="form-label">
                  Telemóvel<span class="required-star">*</span>
              </label>
              <input type="text" name="phone" id="phone" class="form-control" placeholder="+351 912 345 678" required>
          </div>

          {{-- Email --}}
          <div class="col-md-6">
              <label for="email" class="form-label">
                  E-mail<span class="required-star">*</span>
              </label>
              <input type="email" name="email" id="email" class="form-control" placeholder="joao@exemplo.com" required>
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
              <label for="message" class="form-label">Mensagem adicional</label>
              <textarea name="message" id="message" class="form-control" rows="3" placeholder="Deixe aqui informações adicionais que considere relevantes..."></textarea>
          </div>

          <div class="col-12">
              <div class="form-divider"></div>
          </div>

          <div class="col-12">
              <div class="form-section-header">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="1" y="3" width="15" height="13"></rect>
                      <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                      <circle cx="5.5" cy="18.5" r="2.5"></circle>
                      <circle cx="18.5" cy="18.5" r="2.5"></circle>
                  </svg>
                  <span>Detalhes do Veículo</span>
              </div>
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
          /* Form Section Header */
          .form-section-header {
              display: flex;
              align-items: center;
              gap: 0.75rem;
              padding: 1rem 0 0.5rem;
              border-bottom: 2px solid var(--accent-color);
              font-size: 1.1rem;
              font-weight: 700;
              color: #111;
              margin-bottom: 0.5rem;
          }

          .form-section-header svg {
              color: var(--accent-color);
          }

          .form-divider {
              height: 1px;
              background: linear-gradient(to right, transparent, #dee2e6, transparent);
              margin: 1rem 0;
          }

          /* Form Labels */
          #importForm .form-label {
              font-weight: 600;
              color: #111;
              margin-bottom: 0.5rem;
              font-size: 0.95rem;
              display: flex;
              align-items: center;
              gap: 0.5rem;
          }

          .required-star {
              color: var(--accent-color);
              font-weight: 700;
          }

          /* Form Controls */
          #importForm .form-control,
          #importForm .form-select {
              border: 2px solid #e9ecef;
              border-radius: 12px;
              padding: 0.75rem 1rem;
              font-size: 0.95rem;
              transition: all 0.3s ease;
              background: #f8f9fa;
          }

          #importForm .form-control:focus,
          #importForm .form-select:focus {
              border-color: var(--accent-color);
              box-shadow: 0 0 0 4px rgba(110, 7, 7, 0.1);
              background: white;
              outline: none;
          }

          #importForm .form-control:hover,
          #importForm .form-select:hover {
              border-color: #ced4da;
              background: white;
          }

          #importForm textarea.form-control {
              resize: vertical;
              min-height: 100px;
          }

          /* Select Styling */
          #importForm select.form-control {
              background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236e0707' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
              background-repeat: no-repeat;
              background-position: right 1rem center;
              background-size: 16px;
              padding-right: 3rem;
              appearance: none;
          }

          /* Budget Slider */
          #budgetSlider {
              width: 100%;
              height: 8px;
              background: linear-gradient(to right, #e9ecef 0%, var(--accent-color) 0%);
              border-radius: 10px;
              outline: none;
              transition: background 0.3s ease;
          }

          #budgetSlider::-webkit-slider-thumb {
              appearance: none;
              width: 24px;
              height: 24px;
              background: white;
              border: 3px solid var(--accent-color);
              border-radius: 50%;
              cursor: pointer;
              transition: all 0.3s ease;
              box-shadow: 0 2px 8px rgba(110, 7, 7, 0.3);
          }

          #budgetSlider::-webkit-slider-thumb:hover {
              transform: scale(1.2);
              box-shadow: 0 4px 12px rgba(110, 7, 7, 0.4);
          }

          #budgetSlider::-moz-range-thumb {
              width: 24px;
              height: 24px;
              background: white;
              border: 3px solid var(--accent-color);
              border-radius: 50%;
              cursor: pointer;
              transition: all 0.3s ease;
              box-shadow: 0 2px 8px rgba(110, 7, 7, 0.3);
          }

          #budgetValue {
              display: inline-block;
              padding: 0.5rem 1rem;
              background: var(--accent-color);
              color: white;
              border-radius: 8px;
              font-weight: 700;
              font-size: 1rem;
              box-shadow: 0 2px 8px rgba(110, 7, 7, 0.3);
              transition: all 0.3s ease;
          }

          /* Radio Buttons */
          #importForm .form-check-input {
              width: 20px;
              height: 20px;
              border: 2px solid #ced4da;
              cursor: pointer;
              transition: all 0.3s ease;
          }

          #importForm .form-check-input:checked {
              background-color: var(--accent-color);
              border-color: var(--accent-color);
          }

          #importForm .form-check-label {
              cursor: pointer;
              font-size: 0.95rem;
              color: #495057;
              margin-left: 0.5rem;
          }

          /* Info Icon */
          .bi-info-circle {
              color: var(--accent-color);
              cursor: help;
              font-size: 1rem;
          }

          /* Conditional Boxes */
          #ad_links_box,
          #preferences_box {
              background: #f8f9fa;
              border-radius: 16px;
              padding: 1.5rem;
              border: 2px dashed #dee2e6;
          }

          /* Submit Button */
          #contactSubmitProposalBtn {
              background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
              color: white;
              border: none;
              border-radius: 50px;
              padding: 1rem 3rem;
              font-size: 1.1rem;
              font-weight: 700;
              cursor: pointer;
              transition: all 0.3s ease;
              box-shadow: 0 4px 15px rgba(110, 7, 7, 0.3);
          }

          #contactSubmitProposalBtn:hover {
              transform: translateY(-2px);
              box-shadow: 0 6px 20px rgba(110, 7, 7, 0.4);
          }

          #contactSubmitProposalBtn:disabled {
              opacity: 0.6;
              cursor: not-allowed;
              transform: none;
          }

          /* Form Response */
          #formResponse {
              border-radius: 12px;
              padding: 1rem;
              margin-top: 1rem;
          }

          #formResponse .alert {
              border-radius: 12px;
              border: none;
              font-weight: 600;
          }

          #formResponse .alert-success {
              background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
              color: white;
          }

          #formResponse .alert-danger {
              background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
              color: white;
          }

          /* Small Text */
          .text-accent {
              color: var(--accent-color) !important;
              font-size: 0.85rem;
              display: block;
              margin-top: 0.5rem;
          }

          /* Responsive */
          @media (max-width: 768px) {
              #importForm .form-control,
              #importForm .form-select {
                  font-size: 16px; /* Prevent zoom on iOS */
              }

              #contactSubmitProposalBtn {
                  width: 100%;
                  padding: 1rem 2rem;
              }

              #budgetValue {
                  font-size: 0.9rem;
                  padding: 0.4rem 0.8rem;
              }
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