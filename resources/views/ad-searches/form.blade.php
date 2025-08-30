@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">Preencher dados do carro</h2>

    <form method="POST" action="{{ route('ad-searches.submit') }}" class="bg-white p-4 rounded shadow-sm">
        @csrf
        <div class="row g-3">
            <div class="form-group col-md-4 mt-4 ">
                <label for="brand">Marca</label>
                <select name="brand" class="form-control rounded shadow-sm @error('brand') is-invalid @enderror " id="brand" required>
                    <option value="">Selecione a marca</option>
                    @foreach ($brands as $brand)
                    <option value="{{ $brand->name }}" {{ old('brand', $proposal->brand ?? '') == $brand->name ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-4 mt-4">
                <label for="model">Modelo</label>
                <select name="model" class="form-control rounded shadow-sm @error('model') is-invalid @enderror" id="model" required disabled>
                    <option value="">Selecione o modelo</option>
                </select>
            </div>
            <div class="col-md-4 mt-4">
                <label for="submodelo" class="form-label">Versão</label>
                <input type="text" name="submodelo" id="submodelo" class="form-control rounded shadow-sm">
            </div>

            <div class="col-md-4 mt-4">
                <label for="ano_init" class="form-label">Ano inicial</label>
                <input type="number" name="ano_init" id="ano_init" class="form-control rounded shadow-sm" required>
            </div>

            <div class="col-md-4 mt-4">
                <label for="ano_fin" class="form-label">Ano final</label>
                <input type="number" name="ano_fin" id="ano_fin" class="form-control rounded shadow-sm" required>
            </div>

            <div class="col-md-4 mt-4">
                <label for="combustivel" class="form-label">Combustível</label>
                <select name="combustivel" id="combustivel" class="form-control rounded shadow-sm" required>
                    <option value="">Escolher</option>
                    @foreach($combustiveis as $num => $nome)
                    <option value="{{ $nome }}">{{ $nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 mt-4">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea name="descricao" id="descricao" rows="3" class="form-control rounded shadow-sm"></textarea>
            </div>

            <div class="col-12 mt-4">
                <label for="url" class="form-label">URL</label>
                <input type="url" name="url" id="url" class="form-control rounded shadow-sm">
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow">
                <i class="bi bi-check-circle me-1"></i> Submeter
            </button>
        </div>
    </form>
</div>
@endsection


<script>
    window.brands = @json($brands);
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const brandSelect = document.getElementById('brand');
        const modelSelect = document.getElementById('model');
        const brands = window.brands;

        function updateModels() {
            const selectedBrand = brandSelect.value;
            // Limpa os modelos
            modelSelect.innerHTML = '<option value="">Selecione o modelo</option>';
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
                    const selectedModel = "{{ old('model', $proposal->model ?? '') }}";
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