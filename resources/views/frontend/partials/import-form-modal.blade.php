<div class="modal fade" id="formPropostaModal" tabindex="-1" aria-labelledby="formPropostaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formPropostaLabel">Peça já a sua proposta</h5>
                <button type="button" class="btn-close bg-finance" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                {{-- Formulário --}}
                <form id="importForm" class="row g-3">
                    @csrf

                    {{-- Nome --}}
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nome completo</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    {{-- Telemóvel --}}
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Telemóvel</label>
                        <input type="text" name="phone" id="phone" class="form-control" required>
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>

                    {{-- Como conheceu --}}
                    <div class="col-md-6">
                        <label for="source" class="form-label">Como conheceu a Izzycar?</label>
                        <select name="source" id="source" class="form-control">
                            <option value="">Escolha uma opção</option>
                            <option value="google">Google</option>
                            <option value="facebook">Facebook</option>
                            <option value="instagram">Instagram</option>
                            <option value="amigos">Amigos/Família</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>

                    {{-- Mensagem --}}
                    <div class="col-12">
                        <label for="message" class="form-label">Mensagem</label>
                        <textarea name="message" id="message" class="form-control" rows="3"></textarea>
                    </div>

                    {{-- Tem algum anúncio identificado? --}}
                    <div class="col-12">
                        <label class="form-label">Tem algum anúncio identificado?</label>
                        <select name="ad_option" id="ad_option" class="form-control" required>
                            <option value="">Escolha uma opção</option>
                            <option value="sim">Sim</option>
                            <option value="nao_sei">Não, mas sei o que procuro</option>
                            <option value="nao_nao">Não, nem sei o que procuro</option>
                        </select>
                    </div>

                    {{-- Caso "Sim" --}}
                    <div id="ad_links_box" class="col-12 d-none">
                        <label for="ad_links" class="form-label">Coloque aqui os links dos anúncios identificados</label>
                        <textarea name="ad_links" id="ad_links" class="form-control" rows="3"></textarea>
                    </div>

                    {{-- Caso "Não mas sei o que procuro" --}}
                    <div id="preferences_box" class="col-12 d-none">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="brand" class="form-label">Marca</label>

                                <select name="brand" id="brand" class="form-control">
                                    <option value="">Escolha uma opção</option>
                                    @foreach ($brands as $brand)
                                    <option value="{{ $brand->name }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="model" class="form-label">Modelo</label>
                                <select name="model" id="model" class="form-control" required disabled>
                                    <option value="">Escolha uma opção</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="model" class="form-label">Sub-modelo</label>
                                <input name="submodel" id="submodel" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="fuel" class="form-label">Combustível</label>
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
                                <label for="year_min" class="form-label">Ano - Mínimo</label>
                                <input type="number" name="year_min" id="year_min" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label for="km_max" class="form-label">Quilómetros - Máximo</label>
                                <input type="number" name="km_max" id="km_max" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label for="color" class="form-label">Cor de preferência</label>
                                <input type="text" name="color" id="color" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label for="budget" class="form-label">Budget</label>
                                <select class="form-control" id="budget" name="budget">
                                    <option value="">Escolha uma opção</option>
                                    <option value="até_10k">Até 10.000€</option>
                                    <option value="10k_15k">10.000€ - 15.000€</option>
                                    <option value="15k_20k">15.000€ - 20.000€</option>
                                    <option value="20k_25k">20.000€ - 25.000€</option>
                                    <option value="25k_30k">25.000€ - 30.000€</option>
                                    <option value="30k_40k">30.000€ - 40.000€</option>
                                    <option value="40k_50k">40.000€ - 50.000€</option>
                                    <option value="50k+">Mais de 50.000€</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Caixa</label>
                                <div class="row ml-2">
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

            </div>

            <div id="formResponse" class="mt-3" style="display:none;"></div>

        </div>
    </div>
</div>


@push('styles')
<style>
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
<script>
    window.brands = @json($brands);
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        $("#importForm").on("submit", function(e) {
            e.preventDefault();

            let form = $(this);
            let url = ""; // Use the correct route here
            url = "{{ route('frontend.import-submit') }}"; // Adjust the URL as needed
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

                    // Fechar modal depois de 2s e apagar mensagem
                    setTimeout(function() {
                        $("#formPropostaModal").modal("hide");

                        $("#formResponse").fadeOut();
                    }, 2000);
                },
                error: function(xhr) {
                    $("#formResponse").html(
                        '<div class="alert alert-danger">❌ Ocorreu um erro. Tente novamente.</div>'
                    );
                }
            });
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
        const brandSelect = document.getElementById('brand');
        const modelSelect = document.getElementById('model');
        const brands = window.brands;

        function updateModels() {
            const selectedBrand = brandSelect.value;
            // Limpa os modelos
            modelSelect.innerHTML = '<option value="">Escolha uma opção</option>';
            modelSelect.disabled = !selectedBrand;

            if (!selectedBrand) return;

            // Procura a marca selecionada no array brands
            const brandObj = brands.find(b => b.name === selectedBrand);
            if (brandObj && brandObj.models) {
                brandObj.models.forEach(function(model) {
                    const option = document.createElement('option');
                    option.value = model.name;
                    option.textContent = model.name;
                    // Se for edição, seleciona o modelo correto
                    const selectedModel = "";
                    if (option.value === selectedModel) {
                        option.selected = true;
                    }
                    modelSelect.appendChild(option);
                });
            }
        }

        // Atualiza ao carregar a página (para edição)
        updateModels();

        // Atualiza ao mudar a marca
        brandSelect.addEventListener('change', updateModels);
    });
</script>