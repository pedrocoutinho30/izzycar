@extends('layouts.admin')

@section('main-content')
<div class="max-w-5xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Lista de Marcas, Modelos e Anos</h1>

    <!-- <div class="row g-3 mt-4 mb-4">
        <div class="form-group col-md-2 mt-4">
            <label for="brand">Marca</label>
            <select name="brand" class="form-control rounded shadow-sm @error('brand') is-invalid @enderror " id="brand" required>
                <option value="">Selecione a marca</option>
                @foreach ($brands as $brand)
                <option value="{{ $brand->name }}">
                    {{ $brand->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-md-2 mt-4">
            <label for="model">Modelo</label>
            <select name="model" class="form-control rounded shadow-sm @error('model') is-invalid @enderror" id="model" required disabled>
                <option value="">Selecione o modelo</option>
            </select>
        </div>

    </div> -->
    <form method="GET" action="{{ route('brands.index') }}" class="row g-3 mt-4 mb-4">
        <div class="form-group col-md-2 mt-4">
            <label for="brand">Marca</label>
            <select name="brand" class="form-control rounded shadow-sm select2" id="brand" required>
                <option value="">Selecione a marca</option>
                @foreach ($brands as $brand)
                <option value="{{ $brand->name }}" {{ request('brand') == $brand->name ? 'selected' : '' }}>
                    {{ $brand->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2 mt-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <button type="button" class="btn btn-secondary ml-2" onclick="window.location='{{ route('brands.index') }}'">Limpar filtro</button>

        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="15%">Marca</th>
                    <th>Modelo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($brands as $brand)
                <tr>
                    <td>{{ $brand->name }}</td>
                    <td>
                        @if($brand->models && $brand->models->count() > 0)
                        <div class="row g-3">
                            @foreach($brand->models as $model)
                            <div class="col-4 mb-2">
                                {{ $model->name }}
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p>Nenhum modelo disponível</p>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
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