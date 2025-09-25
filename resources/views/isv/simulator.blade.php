@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Simulador ISV</h1>

            </div>

            <form id="isvForm">
                @csrf

                <div class="row g-3 ">
                    <div class="col-md-3 mb-3">
                        <label for="pais_matricula" class="form-label">País da matrícula<span class="required-star">*</span></label>
                        <select name="pais_matricula" id="pais_matricula" class="form-select  form-control" required>
                            <option value="uniao-europeia">Estado-Membro da União Europeia</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="estado_viatura" class="form-label">Estado da viatura<span class="required-star">*</span></label>
                        <select name="estado_viatura" id="estado_viatura" class="form-select  form-control" required>
                            <option value="usado">Usado</option>
                            <option value="novo">Novo</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="data_matricula" class="form-label">Data da matrícula<span class="required-star">*</span></label>
                        <input type="date" name="data_matricula" id="data_matricula" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="combustivel" class="form-label">Combustível<span class="required-star">*</span></label>
                        <select name="combustivel" id="combustivel" class="form-select  form-control" required>
                            <option value="gasolina">Gasolina</option>
                            <option value="diesel">Diesel</option>
                            <option value="eletrico">Elétrico</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="cilindrada" class="form-label">Cilindrada<span class="required-star">*</span></label>
                        <input type="number" name="cilindrada" id="cilindrada" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="tipo_medicao" class="form-label">Método de homologação das emissões de CO2<span class="required-star">*</span></label>
                        <select name="tipo_medicao" id="tipo_medicao" class="form-select  form-control">
                            <option value="WLTP">WLTP</option>
                            <option value="NEDC">NEDC</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="co2" class="form-label">CO2<span class="required-star">*</span></label>
                        <input type="number" name="co2" id="co2" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3" id="emissao_particulas_container" style="display: none;">
                        <label for="emissao_particulas" class="form-label">Emissão de partículas (g/km)<span class="required-star">*</span></label>
                        <select name="emissao_particulas" id="emissao_particulas" class="form-select form-control">
                            <option value="+0.0001">&gt;0.0001</option>
                            <option value="-0.0001">&le;0.0001</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="tipo_veiculo" class="form-label">Tipo de veículo<span class="required-star">*</span></label>
                        <select name="tipo_veiculo" id="tipo_veiculo" class="form-select  form-control">
                            <option value="passageiros">Ligeiro passageiros</option>
                            <option value="hibrido">Ligeiro Híbrido</option>
                            <option value="hibrido_plug_in">Ligeiro Híbrido plug-in</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3" id="autonomia_container" style="display: none;">
                        <label for="autonomia" class="form-label">Autonomia da bateria<span class="required-star">*</span></label>
                        <select name="autonomia" id="autonomia" class="form-select  form-control">
                            <option value="igual_superior">igual ou superior a 50 kms</option>
                            <option value="inferior">inferior a 50 kms</option>
                        </select>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const combustivelInput = document.getElementById('combustivel');
                        const tipoVeiculoInput = document.getElementById('tipo_veiculo');

                        const paisMatriculaDiv = document.getElementById('pais_matricula').parentElement;
                        const dataMatriculaDiv = document.getElementById('data_matricula').parentElement;
                        const cilindradaDiv = document.getElementById('cilindrada').parentElement;
                        const cilindradaInput = document.getElementById('cilindrada');
                        const co2Div = document.getElementById('co2').parentElement;
                        const co2Input = document.getElementById('co2');
                        const tipoMedicaoDiv = document.getElementById('tipo_medicao').parentElement;
                        const tipoMedicaoInput = document.getElementById('tipo_medicao');
                        const particulasDiv = document.getElementById('emissao_particulas_container');
                        const particulasInput = document.getElementById('emissao_particulas');
                        const autonomiaDiv = document.getElementById('autonomia_container');
                        const autonomiaInput = document.getElementById('autonomia');
                        const tipoVeiculoDiv = document.getElementById('tipo_veiculo').parentElement;

                        function toggleCampos() {
                            const combustivel = combustivelInput.value.trim().toLowerCase();
                            const tipoVeiculo = tipoVeiculoInput.value.trim().toLowerCase();

                            // Sempre visíveis
                            paisMatriculaDiv.style.display = '';
                            dataMatriculaDiv.style.display = '';
                            combustivelInput.parentElement.style.display = '';

                            // Inicialmente esconder todos opcionais
                            cilindradaDiv.style.display = 'none';
                            cilindradaInput.removeAttribute('required');
                            co2Div.style.display = 'none';
                            co2Input.removeAttribute('required');
                            tipoMedicaoDiv.style.display = 'none';
                            tipoMedicaoInput.removeAttribute('required');
                            particulasDiv.style.display = 'none';
                            particulasInput.removeAttribute('required');
                            autonomiaDiv.style.display = 'none';
                            autonomiaInput.removeAttribute('required');
                            tipoVeiculoDiv.style.display = 'none';
                            tipoVeiculoInput.removeAttribute('required');
                            // Lógica para elétrico
                            if (combustivel === 'eletrico') {
                                // Só mostra os obrigatórios
                            }
                            // Lógica para diesel
                            else if (combustivel === 'diesel') {
                                cilindradaDiv.style.display = '';
                                cilindradaInput.setAttribute('required', 'required');
                                co2Div.style.display = '';
                                co2Input.setAttribute('required', 'required');
                                tipoMedicaoDiv.style.display = '';
                                tipoMedicaoInput.setAttribute('required', 'required');
                                particulasDiv.style.display = '';
                                particulasInput.setAttribute('required', 'required');
                                tipoVeiculoDiv.style.display = '';
                                tipoVeiculoInput.setAttribute('required', 'required');
                            }
                            // Outros combustíveis
                            else {
                                cilindradaDiv.style.display = '';
                                cilindradaInput.setAttribute('required', 'required');
                                co2Div.style.display = '';
                                co2Input.setAttribute('required', 'required');
                                tipoMedicaoDiv.style.display = '';
                                tipoMedicaoInput.setAttribute('required', 'required');
                                tipoVeiculoDiv.style.display = '';
                                tipoVeiculoInput.setAttribute('required', 'required');
                            }

                            // Autonomia só se hibrido plug-in
                            if (tipoVeiculo === 'hibrido_plug_in') {
                                autonomiaDiv.style.display = '';
                                autonomiaInput.setAttribute('required', 'required');
                            }
                        }

                        combustivelInput.addEventListener('change', toggleCampos);
                        tipoVeiculoInput.addEventListener('change', toggleCampos);
                        toggleCampos(); // inicializa
                    });
                </script>
                <div class="text-center">
                    <button type="submit" class="btn btn-outline-primary shadow-sm">Calcular</button>
                </div>
            </form>
        </div>
        <div class="container mt-4">
            <div id="resultado" style="margin-top: 20px; margin-bottom: 20px; margin-left: 20px;"></div>
        </div>
    </div>
</div>


<style>
    .required-star {
        color: var(--accent-color);
    }
</style>


<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('isvForm');
        const resultadoDiv = document.getElementById('resultado');

        form.addEventListener('submit', function(e) {
            e.preventDefault(); // impede submit tradicional

            const formData = new FormData(form); // coleta todos os campos

            axios.post("{{ route('isv.calcular') }}", formData)
                .then(function(response) {
                    // Supondo que o controller retorne JSON
                    // Exemplo: {dataMatricula: "2025-09-24", faixa: "0-5 anos", reducao: 0.25}

                    const data = response.data;

                    let html = `${data.html}`;

                    resultadoDiv.innerHTML = html;
                })
                .catch(function(error) {
                    console.error(error);
                    resultadoDiv.innerHTML = '<p style="color:red;">Ocorreu um erro ao calcular.</p>';
                });
        });
    });
</script>
@endsection