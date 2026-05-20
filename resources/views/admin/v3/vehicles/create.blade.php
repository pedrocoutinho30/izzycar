@extends('layouts.admin-v2')

@section('title', 'Novo Veículo V3')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-car-front-fill', 'label' => 'Veículos V3', 'href' => route('admin.v3.vehicles.index')],
        ['icon' => 'bi bi-plus', 'label' => 'Novo', 'href' => ''],
    ],
    'title'    => 'Novo Veículo',
    'subtitle' => 'Preencha os dados básicos para criar o veículo',
])

<div class="modern-card" style="max-width:520px">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-car-front-fill me-1"></i> Dados Básicos</h5>
    </div>
    <div class="modern-card-body">

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $e }}</div>@endforeach
            </div>
        @endif

        <form action="{{ route('admin.v3.vehicles.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Marca</label>
                    <select name="brand" id="v3CreateBrandSelect" class="form-select" onchange="v3CreateLoadModels()" autofocus>
                        <option value="">— Selecionar —</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->name }}"
                                    data-models='@json($brand->models->pluck('name')->values())'
                                    {{ old('brand') === $brand->name ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Modelo</label>
                    <select name="model" id="v3CreateModelSelect" class="form-select" disabled>
                        <option value="">— Primeiro selecione a marca —</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Ano</label>
                    <input type="number" name="year" class="form-control" value="{{ old('year') }}" min="1900" max="{{ now()->year + 2 }}" placeholder="{{ now()->year }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Combustível</label>
                    <select name="fuel" class="form-select">
                        <option value="">— Selecionar —</option>
                        @foreach(\App\Models\V3Vehicle::fuelOptions() as $f)
                            <option value="{{ $f }}" {{ old('fuel') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-right-circle me-1"></i> Criar e preencher detalhes
                    </button>
                    <a href="{{ route('admin.v3.vehicles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
(function () {
    const savedBrand = @json(old('brand'));
    const savedModel = @json(old('model'));

    function v3CreateLoadModels() {
        const brandSel = document.getElementById('v3CreateBrandSelect');
        const modelSel = document.getElementById('v3CreateModelSelect');
        const opt = brandSel.options[brandSel.selectedIndex];
        const models = JSON.parse(opt?.dataset.models || '[]');

        modelSel.innerHTML = '<option value="">— Selecionar modelo —</option>';
        models.forEach((modelName) => {
            const option = document.createElement('option');
            option.value = modelName;
            option.textContent = modelName;
            modelSel.appendChild(option);
        });

        modelSel.disabled = models.length === 0;

        if (savedModel) {
            modelSel.value = savedModel;
        }
    }

    window.v3CreateLoadModels = v3CreateLoadModels;

    document.addEventListener('DOMContentLoaded', function () {
        if (savedBrand) {
            document.getElementById('v3CreateBrandSelect').value = savedBrand;
            v3CreateLoadModels();
        }
    });
})();
</script>

@endsection
