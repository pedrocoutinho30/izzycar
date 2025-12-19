@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => (object)[
'meta_image' => "",
'title' => 'Simulador de Custos de Importação ',
'meta_description' => 'Simule os custos de importar o seu carro para Portugal. Obtenha uma estimativa detalhada e transparente, incluindo ISV, transporte e outros encargos. Planeie a sua importação com confiança e sem surpresas.',
]
])




@section('content')

<!-- Hero Section -->
<section class="hero-simulator-section">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="row align-items-center min-vh-50 py-5">
            <div class="col-lg-8 mx-auto text-center">
                <span class="hero-badge fade-in-up">
                    <span>€</span>
                    Calcule os Custos
                </span>
                <h1 class="hero-title fade-in-up" data-delay="100">Simulador de Custos</h1>
                <p class="hero-description fade-in-up" data-delay="200">Descubra quanto custa importar o seu carro dos sonhos para Portugal</p>
            </div>
        </div>
    </div>
</section>

<section class="explore-section section-padding mt-4">
    <div class="container mt-4">
        <div class="simulator-intro-card">
            <div class="simulator-intro-icon">
                <span style="font-size:48px; color:var(--white-color); font-weight:700;">€</span>

            </div>
            <div class="simulator-intro-text">
                <p>Saiba exatamente quanto vai gastar ao importar o seu carro para Portugal. Com o nosso simulador, obtenha todas as despesas detalhadas, do ISV ao transporte, em segundos. Rápido, simples e sem surpresas — importação chave na mão ao seu alcance.</p>
            </div>
        </div>

        <div class="simulator-form-card">
            <form method="POST" action="{{ route('frontend.cost-simulator.calculate') }}">
            @csrf

                @csrf
                
                <div class="form-section-header">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    <h5>Detalhes do Veículo</h5>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <label for="pais_matricula" class="form-label-modern">País da matrícula<span class="required-star">*</span></label>
                        <select name="pais_matricula" id="pais_matricula" class="form-control-modern" required>
                            <option value="uniao-europeia">Estado-Membro da União Europeia</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="estado_viatura" class="form-label-modern">Estado da viatura<span class="required-star">*</span></label>
                        <select name="estado_viatura" id="estado_viatura" class="form-control-modern" required>
                            <option value="usado">Usado</option>
                            <option value="novo">Novo</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="brand" class="form-label-modern">Marca<span class="required-star">*</span></label>
                        <select name="brand" id="brand" class="form-control-modern" required>
                            <option value="">Selecione</option>
                            @foreach($brands as $brand)
                            <option value="{{ $brand->name }}" data-models="{{ json_encode($brand->models->pluck('name')) }}">
                                {{ $brand->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="model" class="form-label-modern">Modelo<span class="required-star">*</span></label>
                        <select name="model" id="model" class="form-control-modern" required disabled>
                            <option value="">Primeiro selecione a marca</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="data_matricula" class="form-label-modern">Data da matrícula<span class="required-star">*</span></label>
                        <input type="date" name="data_matricula" id="data_matricula" class="form-control-modern" required>
                    </div>
                    <div class="col-md-4">
                        <label for="combustivel" class="form-label-modern">Combustível<span class="required-star">*</span></label>
                        <select name="combustivel" id="combustivel" class="form-control-modern" required>
                            <option value="gasolina">Gasolina</option>
                            <option value="diesel">Diesel</option>
                            <option value="eletrico">Elétrico</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="cilindrada" class="form-label-modern">Cilindrada<span class="required-star">*</span></label>
                        <input type="number" name="cilindrada" id="cilindrada" class="form-control-modern" placeholder="ex: 2000">
                    </div>
                    <div class="col-md-4">
                        <label for="tipo_medicao" class="form-label-modern">Método de homologação CO2<span class="required-star">*</span></label>
                        <select name="tipo_medicao" id="tipo_medicao" class="form-control-modern">
                            <option value="WLTP">WLTP</option>
                            <option value="NEDC">NEDC</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="co2" class="form-label-modern">CO2 (g/km)<span class="required-star">*</span></label>
                        <input type="number" name="co2" id="co2" class="form-control-modern" placeholder="ex: 120">
                    </div>
                    <div class="col-md-4" id="emissao_particulas_container" style="display: none;">
                        <label for="emissao_particulas" class="form-label-modern">Emissão de partículas (g/km)<span class="required-star">*</span></label>
                        <select name="emissao_particulas" id="emissao_particulas" class="form-control-modern">
                            <option value="+0.0001">&gt;0.0001</option>
                            <option value="-0.0001">&le;0.0001</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="tipo_veiculo" class="form-label-modern">Tipo de veículo<span class="required-star">*</span></label>
                        <select name="tipo_veiculo" id="tipo_veiculo" class="form-control-modern">
                            <option value="passageiros">Ligeiro passageiros</option>
                            <option value="hibrido">Ligeiro Híbrido</option>
                            <option value="hibrido_plug_in">Ligeiro Híbrido plug-in</option>
                        </select>
                    </div>
                    <div class="col-md-4" id="autonomia_container" style="display: none;">
                        <label for="autonomia" class="form-label-modern">Autonomia da bateria<span class="required-star">*</span></label>
                        <select name="autonomia" id="autonomia" class="form-control-modern">
                            <option value="igual_superior">igual ou superior a 50 kms</option>
                            <option value="inferior">inferior a 50 kms</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="valor_carro" class="form-label-modern">Valor do carro (€)<span class="required-star">*</span></label>
                        <input type="number" name="valor_carro" id="valor_carro" class="form-control-modern" placeholder="ex: 25000" required>
                    </div>
                </div>

                <div class="form-section-header mt-5">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <h5>Seus Dados</h5>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <label for="name" class="form-label-modern">Nome<span class="required-star">*</span></label>
                        <input type="text" name="name" id="name" class="form-control-modern" placeholder="Seu nome completo" required>
                    </div>
                    <div class="col-md-4">
                        <label for="email" class="form-label-modern">Email<span class="required-star">*</span></label>
                        <input type="email" name="email" id="email" class="form-control-modern" placeholder="seu@email.com" required>
                    </div>
                    <div class="col-md-4">
                        <label for="phone" class="form-label-modern">Telefone<span class="required-star">*</span></label>
                        <input type="text" name="phone" id="phone" class="form-control-modern" placeholder="+351 XXX XXX XXX" required>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <button type="submit" class="btn-submit-modern">
                        Simular Custos
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>
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
    </div>
    <div class="container mt-4">
        <div id="resultado" style="margin-top: 20px; margin-bottom: 20px;"></div>
    </div>

</section>

@push('styles')
<style>
    /* Hero Section */
    .hero-simulator-section {
        position: relative;
        background: linear-gradient(135deg, #111111 0%, #1a1a1a 50%, #111111 100%);
        overflow: hidden;
        padding: 3rem 0;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%236e0707' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
    }

    .min-vh-60 {
        min-height: 60vh;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: rgba(110, 7, 7, 0.2);
        border: 1px solid rgba(110, 7, 7, 0.3);
        border-radius: 50px;
        color: #fff;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 2rem;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 900;
        color: #fff;
        line-height: 1.2;
        margin-bottom: 1.5rem;
    }

    .hero-description {
        font-size: 1.2rem;
        color: rgba(255,255,255,0.8);
        line-height: 1.8;
        max-width: 700px;
        margin: 0 auto;
    }

    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards;
    }

    .fade-in-up[data-delay="100"] { animation-delay: 0.1s; }
    .fade-in-up[data-delay="200"] { animation-delay: 0.2s; }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .simulator-intro-card {
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 3rem;
        display: flex;
        gap: 2rem;
        align-items: center;
        color: white;
        /* box-shadow: 0 20px 60px rgba(110, 7, 7, 0.3); */
    }

    .simulator-intro-icon {
        flex-shrink: 0;
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.15);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .simulator-intro-text {
        flex: 1;
    }

    .simulator-intro-text p {
        font-size: 1.1rem;
        line-height: 1.7;
        margin: 0;
        opacity: 0.95;
    }

    .simulator-form-card {
        background: white;
        border-radius: 24px;
        padding: 3rem;
        border: 2px solid #990000;
        /* box-shadow: 0 10px 40px rgba(0,0,0,0.08); */
    }

    .form-section-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .form-section-header svg {
        color: var(--accent-color);
        flex-shrink: 0;
    }

    .form-section-header h5 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111;
        margin: 0;
    }

    .form-label-modern {
        display: block;
        font-size: 0.95rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.75rem;
    }

    .required-star {
        color: var(--accent-color);
        margin-left: 2px;
    }

    .form-control-modern {
        width: 100%;
        padding: 0.875rem 1rem;
        font-size: 1rem;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        background: #f8f9fa;
        transition: all 0.3s ease;
        outline: none;
    }

    .form-control-modern:focus {
        border-color: var(--accent-color);
        background: white;
        box-shadow: 0 0 0 4px rgba(110, 7, 7, 0.1);
    }

    .form-control-modern::placeholder {
        color: #adb5bd;
    }

    .btn-submit-modern {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 1.25rem 3rem;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(110, 7, 7, 0.3);
    }

    .btn-submit-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(110, 7, 7, 0.4);
    }

    .btn-submit-modern svg {
        transition: transform 0.3s ease;
    }

    .btn-submit-modern:hover svg {
        transform: translateX(5px);
    }

    @media (max-width: 768px) {
        .simulator-intro-card {
            flex-direction: column;
            padding: 2rem;
            text-align: center;
        }

        .simulator-intro-icon {
            width: 64px;
            height: 64px;
        }

        .simulator-intro-icon svg {
            width: 32px;
            height: 32px;
        }

        .simulator-intro-text p {
            font-size: 1rem;
        }

        .simulator-form-card {
            padding: 2rem 1.5rem;
        }

        .form-section-header h5 {
            font-size: 1.25rem;
        }

        .btn-submit-modern {
            width: 100%;
            justify-content: center;
            padding: 1rem 2rem;
        }

        .hero-title { font-size: 2rem; }
        .hero-description { font-size: 1rem; }
    }
</style>
@endpush

@push('scripts')
<script>
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in-up').forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });

    // Brand/Model Cascade
    document.addEventListener('DOMContentLoaded', function() {
        const brandSelect = document.getElementById('brand');
        const modelSelect = document.getElementById('model');

        brandSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const models = JSON.parse(selectedOption.dataset.models || '[]');

            // Limpar modelos existentes
            modelSelect.innerHTML = '<option value="">Selecione o modelo</option>';

            // Adicionar novos modelos
            models.forEach(model => {
                const option = document.createElement('option');
                option.value = model;
                option.textContent = model;
                modelSelect.appendChild(option);
            });

            // Habilitar select de modelo
            modelSelect.disabled = models.length === 0;
        });
    });
</script>
@endpush
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