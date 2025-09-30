<!-- resources/views/proposals/form.blade.php -->
@extends('layouts.admin')

@section('main-content')

<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        {{ isset($proposal) ? 'Editar Proposta' : 'Criar Proposta' }}
    </h2>


    <form action="{{ isset($proposal) ? route('proposals.update', $proposal) : route('proposals.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @if(isset($proposal))
        @method('PUT')
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

        <h2>Informações fornecidas pelo cliente</h2>

        <!-- Client Selection -->
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label for="client_id">Cliente</label>
                    <select name="client_id" class="form-control rounded shadow-sm">
                        <option value="">Selecione um cliente</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ isset($proposal) && $proposal->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="business_type">Estado</label>
                    <select name="status" class="form-control rounded shadow-sm">
                        <option value="">Selecione um estado</option>
                        <option value="Pendente" {{  isset($proposal) && $proposal->status == 'Pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="Enviado" {{  isset($proposal) && $proposal->status == 'Enviado' ? 'selected' : '' }}>Enviado</option>
                        <option value="Aprovado" {{ isset($proposal) && $proposal->status == 'Aprovado' ? 'selected' : '' }}>Aprovado</option>
                        <option value="Reprovado" {{  isset($proposal) && $proposal->status == 'Reprovado' ? 'Reprovado' : '' }}>Reprovado</option>
                        <option value="Sem resposta" {{  isset($proposal) && $proposal->status == 'Sem resposta' ? 'Sem resposta' : '' }}>Sem resposta</option>
                    </select>
                </div>
            </div>
            <div class="form-group col-md-2 ">
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

            <div class="form-group col-md-2 ">
                <label for="model">Modelo</label>
                <select name="model" class="form-control rounded shadow-sm @error('model') is-invalid @enderror" id="model" required disabled>
                    <option value="">Selecione o modelo</option>
                </select>
            </div>

            <div class="form-group col-md-4">
                <label for="version">Versão</label>
                <input type="text" class="form-control rounded shadow-sm @error('version') is-invalid @enderror" id="version" name="version" value="{{ old('version', $proposal->version ?? '') }}">
                @error('version')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>

        <div class="row">
            <!-- <div class="col-md-2"> -->
            <!-- Ano -->
            <!-- <div class="form-group">
                    <label for="year">Ano</label>
                    <input type="number" name="year" value="{{ old('year', isset($proposal) ? $proposal->year : '') }}" class="form-control rounded shadow-sm">
                    @error('year')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div> -->
            <!-- </div> -->
            <!-- <div class="col-md-2"> -->
            <!-- Quilometragem -->
            <!-- <div class="form-group">
                    <label for="mileage">Quilometragem</label>
                    <input type="number" name="mileage" value="{{ old('mileage', isset($proposal) ? $proposal->mileage : '') }}" class="form-control rounded shadow-sm">
                    @error('mileage')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div> -->
            <!-- </div> -->
            <div class="col-md-2">
                <div class="form-group">
                    <label for="engine_capacity">Cilindrada</label>
                    <input type="text" name="engine_capacity" value="{{ old('engine_capacity', isset($proposal) ? $proposal->engine_capacity : '') }}" class="form-control rounded shadow-sm">
                    @error('engine_capacity')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="co2">CO2</label>
                    <input type="text" name="co2" value="{{ old('co2', isset($proposal) ? $proposal->co2 : '') }}" class="form-control rounded shadow-sm">
                    @error('co2')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="fuel">Combustível</label>
                    <select name="fuel" class="form-control rounded shadow-sm">
                        <option value="">Selecione um combustível</option>
                        <option value="Gasolina" {{  isset($proposal) && $proposal->fuel == 'Gasolina' ? 'selected' : '' }}>Gasolina</option>
                        <option value="Diesel" {{  isset($proposal) && $proposal->fuel == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                        <option value="Híbrido Plug-in/Gasolina" {{  isset($proposal) && $proposal->fuel == 'Híbrido Plug-in/Gasolina' ? 'selected' : '' }}>Híbrido Plug-in/Gasolina</option>
                        <option value="Híbrido Plug-in/Diesel" {{ isset($proposal) && $proposal->fuel == 'Híbrido Plug-in/Diesel' ? 'selected' : '' }}>Híbrido Plug-in/Diesel</option>
                        <option value="Elétrico" {{  isset($proposal) && $proposal->fuel == 'Elétrico' ? 'selected' : '' }}>Elétrico</option>
                    </select>
                    @error('fuel')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <!-- <div class="col-md-2">
                <div class="form-group">
                    <label for="value">Valor disponivel</label>
                    <input type="number" step="0.01" name="value" value="{{ old('value', isset($proposal) ? $proposal->value : '') }}" class="form-control rounded shadow-sm">
                    @error('value')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div> -->
        </div>

        <div class="form-group">
            <label for="notes">Observações</label>
            <textarea name="notes" class="form-control rounded shadow-sm" rows="10">{{ old('notes', isset($proposal) ? $proposal->notes : '') }}</textarea>
            @error('notes')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <hr />

        <h2>Custos</h2>
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label for="transport_cost">Custo Transporte</label>
                    <input type="number" step="0.01" name="transport_cost" oninput="calculateCommission()" id="transport_cost" value="{{ old('transport_cost', isset($proposal) ? $proposal->transport_cost : '1250') }}" placeholder="1250" class="form-control rounded shadow-sm">
                    @error('transport_cost')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <!-- Custo IPO -->
                <div class="form-group">
                    <label for="ipo_cost">Custo IPO</label>
                    <input type="number" step="0.01" name="ipo_cost" id="ipo_cost" oninput="calculateCommission()" value="{{ old('ipo_cost', isset($proposal) ? $proposal->ipo_cost : '100') }}" placeholder="100" class="form-control rounded shadow-sm">
                    @error('ipo_cost')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">

                <!-- Custo IMT -->
                <div class="form-group">
                    <label for="imt_cost">Custo IMT</label>
                    <input type="number" step="0.01" name="imt_cost" id="imt_cost" oninput="calculateCommission()" value="{{ old('imt_cost', isset($proposal) ? $proposal->imt_cost : '65') }}" placeholder="65" class="form-control rounded shadow-sm">
                    @error('imt_cost')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

            </div>
            <div class="col-md-2">
                <!-- Custo Registo -->
                <div class="form-group">
                    <label for="registration_cost">Custo Registo</label>
                    <input type="number" step="0.01" name="registration_cost" id="registration_cost" oninput="calculateCommission()" value="{{ old('registration_cost', isset($proposal) ? $proposal->registration_cost : '55') }}" placeholder="55" class="form-control rounded shadow-sm">
                    @error('registration_cost')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <!-- Custo ISV -->
                <div class="form-group">
                    <label for="isv_cost">Custo ISV</label>
                    <a href="https://www.izzycar.pt/gestao/simulador-isv" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    <input type="number" step="0.01" name="isv_cost" id="isv_cost" oninput="calculateCommission()" value="{{ old('isv_cost', isset($proposal) ? $proposal->isv_cost : '') }}" class="form-control rounded shadow-sm">
                    @error('isv_cost')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="license_plate_cost">Custo Matrículas</label>
                    <input type="number" step="0.01" name="license_plate_cost" id="license_plate_cost" oninput="calculateCommission()" value="{{ old('license_plate_cost', isset($proposal) ? $proposal->license_plate_cost : '40') }}" placeholder="40" class="form-control rounded shadow-sm">
                    @error('license_plate_cost')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <hr />



        <h2>Carro Proposto</h2>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="url">Url</label>
                    <a href="{{ old('url', isset($proposal) ? $proposal->url : '') }}" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    <input type="text" name="url" value="{{ old('url', isset($proposal) ? $proposal->url : '') }}" class="form-control rounded shadow-sm">
                    @error('url')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="proposed_car_mileage">Quilometragem do Carro Proposto</label>
                    <input type="number" name="proposed_car_mileage" value="{{ old('proposed_car_mileage', isset($proposal) ? $proposal->proposed_car_mileage : '') }}" class="form-control rounded shadow-sm">
                    @error('proposed_car_mileage')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="proposed_car_year_month">Ano/Mês do Carro Proposto</label>
                    <input type="text" name="proposed_car_year_month" value="{{ old('proposed_car_year_month', isset($proposal) ? $proposal->proposed_car_year_month : '') }}" class="form-control rounded shadow-sm">
                    @error('proposed_car_year_month')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="proposed_car_value">Valor do Carro Proposto</label>
                    <input type="number" step="0.01" oninput="calculateCommission()" id="proposed_car_value" name="proposed_car_value" value="{{ old('proposed_car_value', isset($proposal) ? $proposal->proposed_car_value : '') }}" class="form-control rounded shadow-sm">
                    @error('proposed_car_value')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>


        <x-image-manager :label="'Imagem'" :images="$images ?? ''" name="images" :multi="true" id="images" />







        <div class="row">
            <div class="col-md-4">

                <div class="form-group">
                    <label for="inspection_commission_cost">Comissão Vistoria</label>
                    <input type="number" id="inspection_commission_cost" name="inspection_commission_cost" value="{{ old('inspection_commission_cost', isset($proposal) ? $proposal->inspection_commission_cost : $inspection_commission_cost) }}" placeholder="{{$inspection_commission_cost}}" oninput="calculateTotal()" class="form-control rounded shadow-sm">
                    @error('inspection_commission_cost')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="commission_cost">Comissão venda</label>
                    <input type="number" id="commission_cost" name="commission_cost" value="{{ old('commission_cost', isset($proposal) ? $proposal->commission_cost : $commission_cost) }}" class="form-control rounded shadow-sm" placeholder="{{$commission_cost}}" oninput="calculateTotal()">
                    @error('commission_cost')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">

                <div class="form-group">
                    <label for="total_cost">Total</label>
                    <input type="number" readonly id="total_cost" name="total_cost" value="" class="form-control rounded shadow-sm">

                </div>
            </div>
        </div>
        <!-- <div class="form-group">
            <label for="proposed_car_notes">Observações </label>
            <textarea name="proposed_car_notes" rows="3" class="form-control rounded shadow-sm">{{ old('proposed_car_notes', isset($proposal) ? $proposal->proposed_car_notes : '') }}</textarea>
            @error('proposed_car_notes')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div> -->
        <div class="form-group">
            <label for="proposed_car_features">Características </label>
            <textarea name="proposed_car_features" rows="10" class="form-control rounded shadow-sm">{{ old('proposed_car_features', isset($proposal) ? $proposal->proposed_car_features : '') }}</textarea>
            @error('proposed_car_features')
            <div class="text-danger">{{ $message }}</div>
            @enderror
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
        <!-- Exibir imagens existentes -->

        @include('partials.seo_form', ['model' => $proposal ?? null])

        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('proposals.index') }}" class="btn btn-danger px-4 py-2 rounded-pill shadow">Voltar</a>
            <button type="submit" name="action" value="save_continue" class="btn btn-primary px-4 py-2 rounded-pill shadow me-2">
                <i class="bi bi-arrow-right-circle me-1"></i> Salvar e Continuar
            </button>
            <button type="submit" value="save" class="btn btn-success px-4 py-2 rounded-pill shadow">
                <i class="bi bi-check-circle me-1"></i> Salvar
            </button>
        </div>
    </form>
    @endsection
    <script>
        function calculateCommission() {

            let myCommision = 0.035;
            let inspectionCommision = 0.02;
            if (document.getElementById("proposed_car_value").value <= 10000) {
                myCommision = 0.07;
                inspectionCommision = 0.05;
            } else if (document.getElementById("proposed_car_value").value < 20000) {
                myCommision = 0.045;
                inspectionCommision = 0.03;
            } else if (document.getElementById("proposed_car_value").value > 50000) {
                myCommision = 0.02;
                inspectionCommision = 0.01;
            }

            let proposedValue = parseFloat(document.getElementById("proposed_car_value").value) || 0;

            // Calcula as comissões
            let commissionCost = {{$commission_cost}}; //(proposedValue * myCommision).toFixed(2);
            // let inspectionCommissionCost = (proposedValue * inspectionCommision).toFixed(2);

            // Atualiza os inputs
            document.getElementById("commission_cost").value = commissionCost;
            // document.getElementById("inspection_commission_cost").value = float(350.00);//inspectionCommissionCost;

            let transportCost = parseFloat(document.getElementById("transport_cost").value);
            let ipoCost = parseFloat(document.getElementById("ipo_cost").value);
            let imtCost = parseFloat(document.getElementById("imt_cost").value);
            let registrationCost = parseFloat(document.getElementById("registration_cost").value);
            let licensePlateCost = parseFloat(document.getElementById("license_plate_cost").value);
            let isvCost = parseFloat(document.getElementById("isv_cost").value) || 0;
            let inspectionCommissionCost = parseFloat(document.getElementById("inspection_commission_cost").value) || 0;

            console.log(isvCost, inspectionCommissionCost);
            let total = parseFloat(proposedValue) + transportCost + ipoCost + imtCost + registrationCost + isvCost + licensePlateCost + inspectionCommissionCost + parseFloat(commissionCost);
            document.getElementById("total_cost").value = parseFloat(total).toFixed(2);

        }

        function calculateTotal() {


            let proposedValue = parseFloat(document.getElementById("proposed_car_value").value) || 0;

            // Calcula as comissões
            let commissionCost = parseFloat(document.getElementById("commission_cost").value) || 0;
            // let inspectionCommissionCost = (proposedValue * inspectionCommision).toFixed(2);

            // Atualiza os inputs
            document.getElementById("commission_cost").value = commissionCost;
            // document.getElementById("inspection_commission_cost").value = float(350.00);//inspectionCommissionCost;

            let transportCost = parseFloat(document.getElementById("transport_cost").value);
            let ipoCost = parseFloat(document.getElementById("ipo_cost").value);
            let imtCost = parseFloat(document.getElementById("imt_cost").value);
            let registrationCost = parseFloat(document.getElementById("registration_cost").value);
            let licensePlateCost = parseFloat(document.getElementById("license_plate_cost").value);
            let isvCost = parseFloat(document.getElementById("isv_cost").value) || 0;
            let inspectionCommissionCost = parseFloat(document.getElementById("inspection_commission_cost").value) || 0;

            console.log(isvCost, inspectionCommissionCost);
            let total = parseFloat(proposedValue) + transportCost + ipoCost + imtCost + registrationCost + isvCost + licensePlateCost + inspectionCommissionCost + parseFloat(commissionCost);
            document.getElementById("total_cost").value = parseFloat(total).toFixed(2);

        }
    </script>


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