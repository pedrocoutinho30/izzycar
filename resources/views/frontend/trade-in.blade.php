@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => $data->seo
])
@section('content')

@include('frontend.partials.hero-section', ['title' => $data->contents['title'] ?? 'Retoma de Automóveis', 'subtitle' => $data->contents['subtitle'] ?? 'Troque o seu carro usado por um novo'])

<section class="featured-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center mt-4 mb-4">
                
            </div>
        </div>
    </div>
</section>

<section class="section-padding" id="section_description_trade_in">
    <div class="container">
        <p class="text-center text-black mb-8">{!! $data->contents['content'] ?? '<p>Retome o seu veículo usado e adquira um novo com condições vantajosas. Preencha o formulário e receba uma proposta.</p>' !!}</p>
        
    </div>
</section>

<section class="section-padding" id="trade-in-form">
    <div class="container">
        <h3 class="text-center mb-4">Formulário de Retoma</h3>
        <div class="row ">
            <div class="col-lg-12">
                <div class="custom-block custom-block-transparent shadow-lg p-4">
                    
                    <!-- Progress Indicator -->
                    <div class="progress-steps mb-4">
                        <div class="step-indicator">
                            <div class="step active" data-step="1">
                                <span class="step-number">1</span>
                                <span class="step-text">Veículo</span>
                            </div>
                            <div class="step" data-step="2">
                                <span class="step-number">2</span>
                                <span class="step-text">Extras</span>
                            </div>
                            <div class="step" data-step="3">
                                <span class="step-number">3</span>
                                <span class="step-text">Tipo Venda</span>
                            </div>
                            <div class="step" data-step="4">
                                <span class="step-number">4</span>
                                <span class="step-text">Dados Pessoais</span>
                            </div>
                        </div>
                    </div>

                    <form id="tradeInForm" method="POST">
                        @csrf
                        
                        <!-- Step 1: Dados do Veículo -->
                        <div class="form-step active" data-step="1">
                            <h5 class="mb-4 text-black">Dados do Veículo</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="brand" class="form-label">Marca *</label>
                                    <select class="form-control" id="brand" name="brand" required>
                                        <option value="">Selecione a marca</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->name }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="model" class="form-label">Modelo *</label>
                                    <select class="form-control" id="model" name="model" required disabled>
                                        <option value="">Selecione primeiro a marca</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="submodel" class="form-label">Sub-modelo</label>
                                <input type="text" class="form-control" id="submodel" name="submodel" 
                                       placeholder="Ex: 1.5 dCi Business">
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="year" class="form-label">Ano *</label>
                                    <input type="number" class="form-control" id="year" name="year" 
                                           min="1900" max="{{ date('Y') + 1 }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="fuel" class="form-label">Combustível *</label>
                                    <select class="form-control" id="fuel" name="fuel" required>
                                        <option value="">Selecione</option>
                                        <option value="Gasolina">Gasolina</option>
                                        <option value="Gasóleo">Gasóleo</option>
                                        <option value="Híbrido">Híbrido</option>
                                        <option value="Elétrico">Elétrico</option>
                                        <option value="GPL">GPL</option>
                                        <option value="Outro">Outro</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="km" class="form-label">Quilómetros *</label>
                                    <input type="number" class="form-control" id="km" name="km" 
                                           min="0" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="body_type" class="form-label">Tipo de Carroçaria *</label>
                                <select class="form-control" id="body_type" name="body_type" required>
                                    <option value="">Selecione</option>
                                    <option value="Berlina">Berlina</option>
                                    <option value="Carrinha">Carrinha</option>
                                    <option value="SUV">SUV</option>
                                    <option value="Coupé">Coupé</option>
                                    <option value="Descapotável">Descapotável</option>
                                    <option value="Monovolume">Monovolume</option>
                                    <option value="Pick-up">Pick-up</option>
                                    <option value="Comercial">Comercial</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                        </div>

                        <!-- Step 2: Extras -->
                        <div class="form-step" data-step="2" style="display: none;">
                            <h5 class="mb-4">Extras do Veículo</h5>
                            
                            <div class="mb-3">
                                <p class="text-muted mb-3">Selecione os extras que o seu veículo possui:</p>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="extras[]" value="Ar Condicionado" id="extra1">
                                    <label class="form-check-label" for="extra1">
                                        Ar Condicionado
                                    </label>
                                </div>
                                <!-- Mais checkboxes serão adicionadas aqui -->
                            </div>
                        </div>

                        <!-- Step 3: Tipo de Venda -->
                        <div class="form-step" data-step="3" style="display: none;">
                            <h5 class="mb-4">A quem deseja vender?</h5>
                            
                            <div class="mb-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="sale_type" value="Particular" id="sale_particular" required>
                                    <label class="form-check-label" for="sale_particular">
                                        <strong>Vender a Particular</strong>
                                        <p class="text-muted mb-0 ms-4">Venda direta a outro cliente</p>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sale_type" value="Profissional" id="sale_professional" required>
                                    <label class="form-check-label" for="sale_professional">
                                        <strong>Vender a Stand/Profissional</strong>
                                        <p class="text-muted mb-0 ms-4">Venda para profissionais do setor automóvel</p>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Dados Pessoais -->
                        <div class="form-step" data-step="4" style="display: none;">
                            <h5 class="mb-4">Dados Pessoais</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nome *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Telemóvel *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Mensagem (Opcional)</label>
                                <textarea class="form-control" id="message" name="message" rows="3" 
                                          placeholder="Informações adicionais sobre o veículo..."></textarea>
                            </div>
                        </div>

                        <div id="formResponse" class="alert" style="display:none;"></div>

                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">
                                Anterior
                            </button>
                            <button type="button" class="btn btn-warning" id="nextBtn">
                                Seguinte
                            </button>
                            <button type="submit" class="btn btn-warning" id="submitBtn" style="display: none;">
                                Enviar Pedido
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.progress-steps {
    margin-bottom: 2rem;
}

.step-indicator {
    display: flex;
    justify-content: space-between;
    position: relative;
    padding: 0 15px;
}

.step-indicator::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 15%;
    right: 15%;
    height: 2px;
    background: #ddd;
    z-index: 0;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
    flex: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #ddd;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s;
}

.step-text {
    font-size: 12px;
    color: #666;
    text-align: center;
}

.step.active .step-number,
.step.completed .step-number {
    background: #ffc107;
    color: #fff;
}

.step.completed .step-number::after {
    content: '✓';
}

.form-step {
    min-height: 400px;
    margin-bottom: 1rem;
}

.d-flex.justify-content-between {
    position: relative;
    z-index: 10;
    margin-top: 2rem !important;
    padding-top: 1rem;
}

@media (max-width: 768px) {
    .step-text {
        font-size: 10px;
    }
    
    .step-number {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
    
    .form-step {
        min-height: auto;
    }
}
</style>

@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const brands = {!! json_encode($brands) !!};
    let currentStep = 1;
    const totalSteps = 4;
    
    const form = document.getElementById('tradeInForm');
    const formResponse = document.getElementById('formResponse');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    // Brand and Model selection
    const brandSelect = document.getElementById('brand');
    const modelSelect = document.getElementById('model');
    
    brandSelect.addEventListener('change', function() {
        const selectedBrandName = this.value;
        modelSelect.innerHTML = '<option value="">Selecione o modelo</option>';
        modelSelect.disabled = !selectedBrandName;
        
        if (selectedBrandName) {
            const selectedBrand = brands.find(b => b.name === selectedBrandName);
            if (selectedBrand && selectedBrand.models) {
                selectedBrand.models.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.name;
                    option.textContent = model.name;
                    modelSelect.appendChild(option);
                });
            }
        }
    });
    
    // Step Navigation
    function showStep(step) {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(s => s.style.display = 'none');
        
        // Show current step
        const currentStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
        if (currentStepElement) {
            currentStepElement.style.display = 'block';
        }
        
        // Update step indicators
        document.querySelectorAll('.step').forEach((s, index) => {
            s.classList.remove('active', 'completed');
            if (index + 1 < step) {
                s.classList.add('completed');
            } else if (index + 1 === step) {
                s.classList.add('active');
            }
        });
        
        // Update buttons
        prevBtn.style.display = step === 1 ? 'none' : 'inline-block';
        nextBtn.style.display = step === totalSteps ? 'none' : 'inline-block';
        submitBtn.style.display = step === totalSteps ? 'inline-block' : 'none';
        
        // Scroll to top of form
        document.getElementById('trade-in-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    function validateStep(step) {
        const stepElement = document.querySelector(`.form-step[data-step="${step}"]`);
        const inputs = stepElement.querySelectorAll('input[required], select[required]');
        
        for (let input of inputs) {
            if (!input.value) {
                input.reportValidity();
                return false;
            }
        }
        return true;
    }
    
    nextBtn.addEventListener('click', function() {
        if (validateStep(currentStep)) {
            currentStep++;
            showStep(currentStep);
        }
    });
    
    prevBtn.addEventListener('click', function() {
        currentStep--;
        showStep(currentStep);
    });
    
    // Submit form
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateStep(currentStep)) {
            return;
        }
        
        const formData = new FormData(form);
        submitBtn.disabled = true;
        submitBtn.textContent = 'A enviar...';
        
        fetch('{{ route("frontend.trade-in-submit") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            formResponse.style.display = 'block';
            if (data.status === 'success') {
                formResponse.className = 'alert alert-success';
                formResponse.textContent = data.message;
                form.reset();
                currentStep = 1;
                showStep(currentStep);
                modelSelect.disabled = true;
            } else {
                formResponse.className = 'alert alert-danger';
                formResponse.textContent = data.message || 'Ocorreu um erro. Por favor, tente novamente.';
            }
        })
        .catch(error => {
            formResponse.style.display = 'block';
            formResponse.className = 'alert alert-danger';
            formResponse.textContent = 'Ocorreu um erro. Por favor, tente novamente.';
            console.error('Error:', error);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Enviar Pedido';
            formResponse.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });
    
    // Initialize first step
    showStep(currentStep);
});
</script>
