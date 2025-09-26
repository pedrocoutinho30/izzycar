@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => (object)[
'meta_image' => "",
'title' => 'Simulador de Custos de Importação ',
'meta_description' => 'Simule os custos de importar o seu carro para Portugal. Obtenha uma estimativa detalhada e transparente, incluindo ISV, transporte e outros encargos. Planeie a sua importação com confiança e sem surpresas.',
]
])




@section('content')

@include('frontend.partials.hero-section', ['title' => "Simulador de Custos", 'subtitle' => ""])



<section class="featured-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center mt-4 mb-4">

            </div>
        </div>
    </div>
</section>


<section class="explore-section section-padding mt-4" class="bg-dark rounded shadow-sm">
    <div class="container mt-4">
        <p class="text-center text-black mb-8">
            Saiba exatamente quanto vai gastar ao importar o seu carro para Portugal.
            Com o nosso simulador, obtenha todas as despesas detalhadas, do ISV ao transporte, em segundos.
            Rápido, simples e sem surpresas — importação chave na mão ao seu alcance.</p>
        <form method="POST" action="{{ route('frontend.cost-simulator.calculate') }}">
            @csrf

            <div class="row g-3">
                <h5 class="mt-3 mb-0 text-accent">Detalhes do Veículo</h5>
                <hr class="mb-1 mt-0">
                <div class="col-md-4">
                    <label for="pais_matricula" class="form-label">País da matrícula<span class="required-star">*</span></label>
                    <select name="pais_matricula" id="pais_matricula" class="form-select" required>
                        <option value="uniao-europeia">Estado-Membro da União Europeia</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="estado_viatura" class="form-label">Estado da viatura<span class="required-star">*</span></label>
                    <select name="estado_viatura" id="estado_viatura" class="form-select" required>
                        <option value="usado">Usado</option>
                        <option value="novo">Novo</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="data_matricula" class="form-label">Data da matrícula<span class="required-star">*</span></label>
                    <input type="date" name="data_matricula" id="data_matricula" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="combustivel" class="form-label">Combustível<span class="required-star">*</span></label>
                    <select name="combustivel" id="combustivel" class="form-select" required>
                        <option value="gasolina">Gasolina</option>
                        <option value="diesel">Diesel</option>
                        <option value="eletrico">Elétrico</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="cilindrada" class="form-label">Cilindrada<span class="required-star">*</span></label>
                    <input type="number" name="cilindrada" id="cilindrada" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="tipo_medicao" class="form-label">Método de homologação das emissões de CO2<span class="required-star">*</span></label>
                    <select name="tipo_medicao" id="tipo_medicao" class="form-select">
                        <option value="WLTP">WLTP</option>
                        <option value="NEDC">NEDC</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="co2" class="form-label">CO2<span class="required-star">*</span></label>
                    <input type="number" name="co2" id="co2" class="form-control">
                </div>
                <div class="col-md-4" id="emissao_particulas_container" style="display: none;">
                    <label for="emissao_particulas" class="form-label">Emissão de partículas (g/km)<span class="required-star">*</span></label>
                    <select name="emissao_particulas" id="emissao_particulas" class="form-select">
                        <option value="+0.0001">&gt;0.0001</option>
                        <option value="-0.0001">&le;0.0001</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="tipo_veiculo" class="form-label">Tipo de veículo<span class="required-star">*</span></label>
                    <select name="tipo_veiculo" id="tipo_veiculo" class="form-select">
                        <option value="passageiros">Ligeiro passageiros</option>
                        <option value="hibrido">Ligeiro Híbrido</option>
                        <option value="hibrido_plug_in">Ligeiro Híbrido plug-in</option>
                    </select>
                </div>
                <div class="col-md-4" id="autonomia_container" style="display: none;">
                    <label for="autonomia" class="form-label">Autonomia da bateria<span class="required-star">*</span></label>
                    <select name="autonomia" id="autonomia" class="form-select">
                        <option value="igual_superior">igual ou superior a 50 kms</option>
                        <option value="inferior">inferior a 50 kms</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="valor_carro" class="form-label">Valor do carro<span class="required-star">*</span></label>
                    <input type="number" name="valor_carro" id="valor_carro" class="form-control" required>
                </div>
                <h5 class="mt-4 mb-0 text-accent">Seus Dados</h5>
                <hr class="mb-1 mt-0">
                <div class="col-md-4">
                    <label for="name" class="form-label">Nome<span class="required-star">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label">Email<span class="required-star">*</span></label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="phone" class="form-label">Telefone<span class="required-star">*</span></label>
                    <input type="text" name="phone" id="phone" class="form-control" required>
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn custom-btn btn-lg mt-4 ">Simular</button>
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

        </form>
    </div>
    <div class="container mt-4">
        <div id="resultado" style="margin-top: 20px; margin-bottom: 20px; margin-left: 20px;"></div>
    </div>

</section>
<style>
    .required-star {
        color: var(--accent-color);
    }
</style>


<!-- <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script> -->
<!-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('isvForm');
        const resultadoDiv = document.getElementById('resultado');

        form.addEventListener('submit', function(e) {
            e.preventDefault(); // impede submit tradicional

            const formData = new FormData(form); // coleta todos os campos

            axios.post("{{ route('isv.calcular') }}", formData)
                .then(function(response) {

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
</script> -->
@endsection