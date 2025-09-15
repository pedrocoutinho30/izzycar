@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">{{ isset($vehicle) ? 'Editar Veículo' : 'Criar Novo Veículo' }}</h2>

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif


    <form action="{{ isset($vehicle) ? route('vehicles.update', $vehicle->id) : route('vehicles.store') }}" id="vehicle-form" method="POST" class="bg-white p-4 rounded shadow-sm" enctype="multipart/form-data">
        @csrf
        @if (isset($vehicle))
        @method('PUT') <!-- Para quando for editar, utiliza o método PUT -->
        @endif
        <div class="col-12 mt-2">
            <h4 class="border-bottom color-info">Dados gerais</h4>
        </div>
        <div class="row g-3 mt-2 mb-4">
            <div class="form-group col-md-2 mt-4">
                <label for="show_online">Mostrar no site</label>
                <select name="show_online" class="form-control rounded shadow-sm" id="show_online" required>
                    <option value="0" {{ old('show_online', $vehicle->show_online ?? '') == '0' ? 'selected' : '' }}>Não</option>
                    <option value="1" {{ old('show_online', $vehicle->show_online ?? '') == '1' ? 'selected' : '' }}>Sim</option>
                </select>
            </div>
            <div class="form-group col-md-2 mt-4">
                <label for="reference">Referência</label>
                <input type="text" readonly name="reference" class="form-control rounded shadow-sm" value="{{ old('reference', $vehicle->reference ?? '') }}" required>
            </div>
            <div class="form-group col-md-2 mt-4">
                <label for="business_type">Tipo de Negócio</label>
                <select name="business_type" class="form-control rounded shadow-sm">
                    <option value="novo" {{ isset($vehicle) && $vehicle->business_type == 'novo' ? 'selected' : '' }}>Novo</option>
                    <option value="usado" {{ isset($vehicle) && $vehicle->business_type == 'usado' ? 'selected' : '' }}>Usado</option>
                    <option value="seminovo" {{ isset($vehicle) && $vehicle->business_type == 'seminovo' ? 'selected' : '' }}>Semi-novo</option>
                </select>
            </div>
            <div class="form-group col-md-2 mt-4">
                <label for="supplier_id">Fornecedor</label>
                <select name="supplier_id" class="form-control rounded shadow-sm">
                    <option value="">Selecione o fornecedor</option>
                    @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ isset($vehicle) && $vehicle->supplier_id == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->company_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 mt-4">
                <label for="consigned_vehicle">Veículo Consignado</label>
                <select class="form-control rounded shadow-sm @error('consigned_vehicle') is-invalid @enderror" id="consigned_vehicle" name="consigned_vehicle">
                    <option value="" {{ old('consigned_vehicle', $vehicle->consigned_vehicle ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    <option value="0" {{ old('consigned_vehicle', $vehicle->consigned_vehicle ?? '') == '0' ? 'selected' : '' }}>Não</option>
                    <option value="1" {{ old('consigned_vehicle', $vehicle->consigned_vehicle ?? '') == '1' ? 'selected' : '' }}>Sim</option>
                </select>
                @error('consigned_vehicle')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row g-3 mt-4 mb-4">
            <div class="form-group col-md-2 mt-4">
                <label for="brand">Marca</label>
                <select name="brand" class="form-control rounded shadow-sm @error('brand') is-invalid @enderror " id="brand" required>
                    <option value="">Selecione a marca</option>
                    @foreach ($brands as $brand)
                    <option value="{{ $brand->name }}" {{ old('brand', $vehicle->brand ?? '') == $brand->name ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                    @endforeach
                </select>
                <!-- <input type="text" name="brand" class="form-control rounded shadow-sm" value="{{ old('brand', $vehicle->brand ?? '') }}" required> -->
            </div>

            <div class="form-group col-md-2 mt-4">
                <label for="model">Modelo</label>
                <select name="model" class="form-control rounded shadow-sm @error('model') is-invalid @enderror" id="model" required disabled>
                    <option value="">Selecione o modelo</option>
                </select>
                <!-- <input type="text" name="model" class="form-control rounded shadow-sm" value="{{ old('model', $vehicle->model ?? '') }}" required> -->
            </div>

            <div class="form-group col-md-4 mt-4">
                <label for="version">Versão</label>
                <input type="text" class="form-control rounded shadow-sm @error('version') is-invalid @enderror" id="version" name="version" value="{{ old('version', $vehicle->version ?? '') }}">
                @error('version')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row g-3 mt-4 mb-4">

            <div class="form-group col-md-2 mt-4">
                <label for="version">Ano</label>
                <input type="number" class="form-control rounded shadow-sm @error('year') is-invalid @enderror" id="year" name="year" value="{{ old('year', $vehicle->year ?? '') }}">
                @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-2 mt-4">
                <label for="kilometers">Quilometragem</label>
                <input type="number" class="form-control rounded shadow-sm @error('kilometers') is-invalid @enderror" id="kilometers" name="kilometers" value="{{ old('kilometers', $vehicle->kilometers ?? '') }}">
                @error('kilometers')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-2 mt-4">
                <label for="fuel">Combustível</label>
                <select class="form-control rounded shadow-sm @error('fuel') is-invalid @enderror" id="fuel" name="fuel">
                    <option value="" {{ old('fuel', $vehicle->fuel ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    <option value="Diesel" {{ old('fuel', $vehicle->fuel ?? '') == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                    <option value="Gasolina" {{ old('fuel', $vehicle->fuel ?? '') == 'Gasolina' ? 'selected' : '' }}>Gasolina</option>
                    <option value="Elétrico" {{ old('fuel', $vehicle->fuel ?? '') == 'Elétrico' ? 'selected' : '' }}>Elétrico</option>
                    <option value="Hibrido-plug-in/Gasolina" {{ old('fuel', $vehicle->fuel ?? '') == 'Hibrido-plug-in/Gasolina' ? 'selected' : '' }}>Hibrido-plug-in/Gasolina</option>
                    <option value="Hibrido-plug-in/Diesel" {{ old('fuel', $vehicle->fuel ?? '') == 'Hibrido-plug-in/Diesel' ? 'selected' : '' }}>Hibrido-plug-in/Diesel</option>
                    <option value="Outro" {{ old('fuel', $vehicle->fuel ?? '') == 'Outro' ? 'selected' : '' }}>Outro</option>
                </select>
                @error('fuel')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row g-3 mt-4 mb-4">

            <div class="form-group col-md-2 mt-4">
                <label for="purchase_type">Tipo de Compra</label>
                <select class="form-control rounded shadow-sm @error('purchase_type') is-invalid @enderror" id="purchase_type" name="purchase_type" required>
                    <option value="" {{ old('purchase_type', $vehicle->purchase_type ?? '') == '' ? 'selected' : '' }}>Selecione</option>
                    <option value="Margem" {{ old('purchase_type', $vehicle->purchase_type ?? '') == 'Margem' ? 'selected' : '' }}>Margem</option>
                    <option value="Geral" {{ old('purchase_type', $vehicle->purchase_type ?? '') == 'Geral' ? 'selected' : '' }}>Geral</option>
                    <option value="Sem Iva" {{ old('purchase_type', $vehicle->purchase_type ?? '') == 'Sem Iva' ? 'selected' : '' }}>Sem Iva</option>
                </select>
                @error('purchase_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-2 mt-4">
                <label for="purchase_date">Data de Compra</label>
                <input type="date" name="purchase_date" class="form-control rounded shadow-sm" value="{{ old('purchase_date', $vehicle->purchase_date ?? '') }}">
            </div>


            <div class="form-group col-md-2 mt-4">
                <label for="purchase_price">Preço de Compra</label>
                <input type="number" name="purchase_price" id="purchase_price" step="any" class="form-control rounded shadow-sm" value="{{ old('purchase_price', $vehicle->purchase_price ?? '') }}" required>
            </div>

            <div class="form-group col-md-2 mt-4">
                <label for="sell_price">Preço de Venda Previsto</label>
                <input type="number" name="sell_price" id="sell_price" step="any" class="form-control rounded shadow-sm" value="{{ old('sell_price', $vehicle->sell_price ?? '') }}">
            </div>



            <!-- <div class="form-group col-md-6 mt-4">
                <label for="power">Potência (cv)</label>
                <input type="number" class="form-control rounded shadow-sm @error('power') is-invalid @enderror" id="power" name="power" value="{{ old('power', $vehicle->power ?? '') }}">
                @error('power')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div> -->

            <!-- <div class="form-group col-md-6 mt-4">
                <label for="cylinder_capacity">Capacidade Cilíndrica</label>
                <input type="number" class="form-control rounded shadow-sm @error('cylinder_capacity') is-invalid @enderror" id="cylinder_capacity" name="cylinder_capacity" value="{{ old('cylinder_capacity', $vehicle->cylinder_capacity ?? '') }}">
                @error('cylinder_capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div> -->


            <!-- <input type="checkbox" class="form-check-input" id="consigned_vehicle" name="consigned_vehicle" {{ old('consigned_vehicle', $vehicle->consigned_vehicle ?? false) ? 'checked' : '' }}> -->







            <div class="form-group col-md-2 mt-4">
                <label for="manufacture_date">Data de Fabrico</label>
                <input type="date" class="form-control rounded shadow-sm @error('manufacture_date') is-invalid @enderror" id="manufacture_date" name="manufacture_date" value="{{ old('manufacture_date', $vehicle->manufacture_date ?? '') }}">
                @error('manufacture_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group col-md-2 mt-4">
                <label for="register_date">Data de Registo</label>
                <input type="date" class="form-control rounded shadow-sm @error('register_date') is-invalid @enderror" id="register_date" name="register_date" value="{{ old('register_date', $vehicle->register_date ?? '') }}">
                @error('register_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group col-md-2 mt-4">
                <label for="available_to_sell_date">Data Disponível para Venda</label>
                <input type="date" class="form-control rounded shadow-sm @error('available_to_sell_date') is-invalid @enderror" id="available_to_sell_date" name="available_to_sell_date" value="{{ old('available_to_sell_date', $vehicle->available_to_sell_date ?? '') }}">
                @error('available_to_sell_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group col-md-2 mt-4">
                <label for="registration">Matrícula</label>
                <input type="text" class="form-control rounded shadow-sm @error('registration') is-invalid @enderror" id="registration" name="registration" value="{{ old('registration', $vehicle->registration ?? '') }}">
                @error('registration')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-3 mt-4">
                <label for="vin">VIN</label>
                <input type="text" class="form-control rounded shadow-sm @error('vin') is-invalid @enderror" id="vin" name="vin" value="{{ old('vin', $vehicle->vin ?? '') }}">
                @error('vin')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>


            <!-- Campos adicionais de acordo com os dados do veículo -->

            <!-- <div class="form-group col-md-6 mt-4">
                <label for="color">Cor</label>
                <input type="text" name="color" class="form-control rounded shadow-sm" value="{{ old('color', $vehicle->color ?? '') }}">
            </div> -->

            <!-- Outros campos do formulário -->

            <!-- <div class="form-group col-md-6 mt-4">
                <label for="images">Imagens</label>
                <input type="file" name="images[]" id="images" class="form-control rounded shadow-sm" multiple>
            </div>


            @if (isset($vehicle) && $vehicle->images->count() > 0)
            <div class="form-group col-md-6 mt-4">
                <label>Imagens Existentes:</label>
                <div class="row">
                    @foreach ($vehicle->images as $image)
                    <div class="col-md-3">
                        <img src="{{ asset('storage/' . $image->image_path) }}" class="img-thumbnail" alt="Imagem do veículo">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif -->

            <div class="form-group col-md-6 mt-4">
              
                <x-image-manager :label="'Imagens do Veículo'" :images="$existingImages" name="images" :multi="true" id="vehicleImages" />
            </div>


            <div class="row g-3 mt-2">

                @foreach($attributes as $groupName => $groupAttributes)
                <div class="col-12 mt-4">
                    <h4 class="border-bottom pb-2 color-info">{{ $groupName }}</h4>
                </div>

                @foreach($groupAttributes as $attr)
                @php
                $fieldName = 'attributes[' . $attr->id . ']';
                $existingValue = old($fieldName, $attributeValues[$attr->id]->value ?? null);
                @endphp

                <div class="form-group col-md-4 mt-2">
                    @if($attr->type === 'boolean')
                    @php
                    $isChecked = $existingValue ? 'checked' : '';
                    @endphp

                    <label class="form-label d-block">{{ $attr->name }}</label>
                    <div class="form-switch-toggle">
                        <input type="checkbox"
                            id="{{ $fieldName }}"
                            name="{{ $fieldName }}"
                            value="1"
                            class="d-none"
                            {{ $isChecked }}>
                        <label for="{{ $fieldName }}" class="toggle-switch-label"></label>
                    </div>
                    @else
                    <label for="{{ $fieldName }}">{{ $attr->name }}</label>

                    @if($attr->type === 'text' || $attr->type === 'number')
                    <input type="{{ $attr->type }}"
                        name="{{ $fieldName }}"
                        id="{{ $fieldName }}"
                        value="{{ $existingValue }}"
                        class="form-control rounded shadow-sm">
                    @elseif($attr->type === 'select')
                    <select name="{{ $fieldName }}"
                        id="{{ $fieldName }}"
                        class="form-control rounded shadow-sm">
                        <option value="">Selecione</option>
                        @foreach($attr->options as $option)
                        <option value="{{ $option }}" {{ $existingValue == $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                        @endforeach
                    </select>
                    @endif
                    @endif
                </div>
                @endforeach
                @endforeach
            </div>


            <div class="col-12">
                <h4 class="border-bottom color-info">Despesas</h4> <a
                    href="{{ route('expenses.create', ['vehicle_id' => $vehicle->id]) }}"
                    target="_blank"
                    class="btn btn-outline-primary mt-4">
                    Adicionar Despesa
                </a>
            </div>

            @if(isset($vehicle) && $vehicle->expenses->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Título</th>
                            <th scope="col">Valor</th>
                            <th scope="col">Taxa de IVA</th>
                            <th scope="col">Data da Despesa</th>
                            <th scope="col">Parceiro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)

                        <tr>
                            <th scope="row">{{ $expense->id }}</th>
                            <td>{{ $expense->title }}</td>
                            <td>{{ number_format($expense->amount, 2, ',', '.') }}€</td>
                            <td>{{ $expense->vat_rate }}</td>
                            <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
                            <td>{{ $expense->partner ? $expense->partner->name : 'N/A' }}</td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted">Nenhuma despesa registrada para este veículo.</p>
            @endif


            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('vehicles.index') }}" class="btn btn-danger px-4 py-2 rounded-pill shadow">Voltar</a>
                <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow">
                    <i class="bi bi-check-circle me-1"></i> Salvar
                </button>
            </div>
    </form>

</div>



@endsection

<style>
    .form-switch-toggle {
        position: relative;
        width: 60px;
        height: 30px;
    }

    .toggle-switch-label {
        display: block;
        width: 100%;
        height: 100%;
        background-color: rgb(121, 118, 118);
        /* vermelho */
        border-radius: 30px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        position: relative;
    }

    .toggle-switch-label::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 24px;
        height: 24px;
        background-color: #fff;
        border-radius: 50%;
        transition: 0.3s ease;
    }

    input[type="checkbox"]:checked+.toggle-switch-label {
        background-color: #28a745;
        /* verde */
    }

    input[type="checkbox"]:checked+.toggle-switch-label::after {
        transform: translateX(30px);
    }
</style>

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
                    const selectedModel = "{{ old('model', $vehicle->model ?? '') }}";
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