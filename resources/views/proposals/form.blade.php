<!-- resources/views/proposals/form.blade.php -->
@extends('layouts.admin')

@section('main-content')

<h1>{{ isset($proposal) ? 'Editar Proposta' : 'Criar Proposta' }}</h1>

<form action="{{ isset($proposal) ? route('proposals.update', $proposal) : route('proposals.store') }}" method="POST" enctype="multipart/form-data">
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
                <select name="client_id" class="form-control">
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
                <select name="status" class="form-control">
                    <option value="">Selecione um estado</option>
                    <option value="Pendente" {{  isset($proposal) && $proposal->status == 'Pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="Enviado" {{  isset($proposal) && $proposal->status == 'Enviado' ? 'selected' : '' }}>Enviado</option>
                    <option value="Aprovado" {{ isset($proposal) && $proposal->status == 'Aprovado' ? 'selected' : '' }}>Aprovado</option>
                    <option value="Reprovado" {{  isset($proposal) && $proposal->status == 'Reprovado' ? 'Reprovado' : '' }}>Reprovado</option>
                    <option value="Sem resposta" {{  isset($proposal) && $proposal->status == 'Sem resposta' ? 'Sem resposta' : '' }}>Sem resposta</option>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <!-- Marca -->
            <div class="form-group">
                <label for="brand">Marca</label>
                <input type="text" name="brand" value="{{ old('brand', isset($proposal) ? $proposal->brand : '') }}" class="form-control">
                @error('brand')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <!-- Modelo -->
            <div class="form-group">
                <label for="model">Modelo</label>
                <input type="text" name="model" value="{{ old('model', isset($proposal) ? $proposal->model : '') }}" class="form-control">
                @error('model')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <!-- Ano -->
            <div class="form-group">
                <label for="year">Ano</label>
                <input type="number" name="year" value="{{ old('year', isset($proposal) ? $proposal->year : '') }}" class="form-control">
                @error('year')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2">
            <!-- Quilometragem -->
            <div class="form-group">
                <label for="mileage">Quilometragem</label>
                <input type="number" name="mileage" value="{{ old('mileage', isset($proposal) ? $proposal->mileage : '') }}" class="form-control">
                @error('mileage')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="engine_capacity">Cilindrada</label>
                <input type="text" name="engine_capacity" value="{{ old('engine_capacity', isset($proposal) ? $proposal->engine_capacity : '') }}" class="form-control">
                @error('engine_capacity')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="co2">CO2</label>
                <input type="text" name="co2" value="{{ old('co2', isset($proposal) ? $proposal->co2 : '') }}" class="form-control">
                @error('co2')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="fuel">Combustível</label>
                <select name="fuel" class="form-control">
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
        <div class="col-md-2">
            <div class="form-group">
                <label for="value">Valor disponivel</label>
                <input type="number" step="0.01" name="value" value="{{ old('value', isset($proposal) ? $proposal->value : '') }}" class="form-control">
                @error('value')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="notes">Observações</label>
        <textarea name="notes" class="form-control" rows="10">{{ old('notes', isset($proposal) ? $proposal->notes : '') }}</textarea>
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
                <input type="number" step="0.01" name="transport_cost" id="transport_cost" value="{{ old('transport_cost', isset($proposal) ? $proposal->transport_cost : '1100') }}" placeholder="1100" class="form-control">
                @error('transport_cost')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <!-- Custo IPO -->
            <div class="form-group">
                <label for="ipo_cost">Custo IPO</label>
                <input type="number" step="0.01" name="ipo_cost" id="ipo_cost" value="{{ old('ipo_cost', isset($proposal) ? $proposal->ipo_cost : '100') }}" placeholder="100" class="form-control">
                @error('ipo_cost')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-2">

            <!-- Custo IMT -->
            <div class="form-group">
                <label for="imt_cost">Custo IMT</label> 
                <input type="number" step="0.01" name="imt_cost" id="imt_cost" value="{{ old('imt_cost', isset($proposal) ? $proposal->imt_cost : '65') }}" placeholder="65" class="form-control">
                @error('imt_cost')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

        </div>
        <div class="col-md-2">
            <!-- Custo Registo -->
            <div class="form-group">
                <label for="registration_cost">Custo Registo</label>
                <input type="number" step="0.01" name="registration_cost" id="registration_cost" value="{{ old('registration_cost', isset($proposal) ? $proposal->registration_cost : '55') }}" placeholder="55" class="form-control">
                @error('registration_cost')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <!-- Custo ISV -->
            <div class="form-group">
                <label for="isv_cost">Custo ISV</label>
                <input type="number" step="0.01" name="isv_cost" id="isv_cost" value="{{ old('isv_cost', isset($proposal) ? $proposal->isv_cost : '') }}" class="form-control">
                @error('isv_cost')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="license_plate_cost">Custo Matrículas</label>
                <input type="number" step="0.01" name="license_plate_cost" id="license_plate_cost" value="{{ old('license_plate_cost', isset($proposal) ? $proposal->license_plate_cost : '40') }}" placeholder="40" class="form-control">
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
                <input type="text" name="url" value="{{ old('url', isset($proposal) ? $proposal->url : '') }}" class="form-control">
                @error('url')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="proposed_car_mileage">Quilometragem do Carro Proposto</label>
                <input type="number" name="proposed_car_mileage" value="{{ old('proposed_car_mileage', isset($proposal) ? $proposal->proposed_car_mileage : '') }}" class="form-control">
                @error('proposed_car_mileage')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="proposed_car_year_month">Ano/Mês do Carro Proposto</label>
                <input type="text" name="proposed_car_year_month" value="{{ old('proposed_car_year_month', isset($proposal) ? $proposal->proposed_car_year_month : '') }}" class="form-control">
                @error('proposed_car_year_month')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="proposed_car_value">Valor do Carro Proposto</label>
                <input type="number" step="0.01" oninput="calculateCommission()" id="proposed_car_value" name="proposed_car_value" value="{{ old('proposed_car_value', isset($proposal) ? $proposal->proposed_car_value : '') }}" class="form-control">
                @error('proposed_car_value')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="proposed_car_notes">Observações </label>
        <textarea name="proposed_car_notes" rows="3" class="form-control">{{ old('proposed_car_notes', isset($proposal) ? $proposal->proposed_car_notes : '') }}</textarea>
        @error('proposed_car_notes')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group">
        <label for="proposed_car_features">Características </label>
        <textarea name="proposed_car_features" rows="10" class="form-control">{{ old('proposed_car_features', isset($proposal) ? $proposal->proposed_car_features : '') }}</textarea>
        @error('proposed_car_features')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <!-- Exibir imagens existentes -->
    @if(isset($images) && count($images) > 0)
    <div>
        <h3>Imagens atuais:</h3>
        @foreach($images as $image)
        <div>
            <img src="{{ Storage::url($image) }}" alt="Imagem proposta" width="100" height="100">
            <input type="checkbox" name="delete_images[]" value="{{ $image }}"> Apagar imagem
        </div>
        @endforeach
    </div>
    @endif

    <div class="form-group">
        <label for="images">Imagens</label>
        <input type="file" name="images[]" accept="image/*" multiple class="form-control">
        @error('images')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="row">
        <div class="col-md-4">

            <div class="form-group">
                <label for="inspection_commission_cost">Comissão Vistoria</label>
                <input type="number" readonly id="inspection_commission_cost" name="inspection_commission_cost" value="{{ old('inspection_commission_cost', isset($proposal) ? $proposal->inspection_commission_cost : '') }}" class="form-control">
                @error('inspection_commission_cost')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-4">

            <div class="form-group">
                <label for="commission_cost">Comissão venda</label>
                <input type="number" readonly id="commission_cost" name="commission_cost" value="{{ old('commission_cost', isset($proposal) ? $proposal->commission_cost : '') }}" class="form-control">
                @error('commission_cost')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-4">

            <div class="form-group">
                <label for="total_cost">Total</label>
                <input type="number" readonly id="total_cost" name="total_cost" value="" class="form-control">

            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">{{ isset($proposal) ? 'Atualizar Proposta' : 'Criar Proposta' }}</button>
    <a class="btn btn-secondary" href="{{ route('proposals.index') }}">Voltar à listagem</a>
</form>
@endsection
<script>
    function calculateCommission() {

        let myCommision = 0.04;
        let inspectionCommision = 0.02;
        if (document.getElementById("proposed_car_value").value <= 10000) {
            myCommision = 0.07;
            inspectionCommision = 0.05;
        } else if (document.getElementById("proposed_car_value").value < 20000) {
            myCommision = 0.05;
            inspectionCommision = 0.03;
        }

        let proposedValue = parseFloat(document.getElementById("proposed_car_value").value) || 0;
        console.log(proposedValue);
        console.log(myCommision);
        // Calcula as comissões
        let commissionCost = (proposedValue * myCommision).toFixed(2);
        let inspectionCommissionCost = (proposedValue * inspectionCommision).toFixed(2);

        console.log(commissionCost)
        // Atualiza os inputs
        document.getElementById("commission_cost").value = commissionCost;
        document.getElementById("inspection_commission_cost").value = inspectionCommissionCost;

        let transportCost = parseFloat(document.getElementById("transport_cost").value);
        let ipoCost = parseFloat(document.getElementById("ipo_cost").value);
        let imtCost = parseFloat(document.getElementById("imt_cost").value);
        let registrationCost = parseFloat(document.getElementById("registration_cost").value);
        let isvCost = parseFloat(document.getElementById("isv_cost").value);
        let licensePlateCost = parseFloat(document.getElementById("license_plate_cost").value);

        let total = parseFloat(proposedValue) + transportCost + ipoCost + imtCost + registrationCost + isvCost + licensePlateCost + parseFloat(inspectionCommissionCost) + parseFloat(commissionCost);
        document.getElementById("total_cost").value = total;

    }

    
</script>