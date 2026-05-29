@extends('layouts.admin-v2')

@section('title', 'Nova Inspeção V3')

@section('content')
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-clipboard-check', 'label' => 'Inspeções', 'href' => route('admin.v3.inspections.index')],
        ['icon' => 'bi bi-plus-lg', 'label' => 'Nova', 'href' => ''],
    ],
    'title' => 'Nova Inspeção',
    'subtitle' => 'Crie a inspeção e depois preencha a checklist completa no detalhe.',
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-car-front-fill me-1"></i> Dados Base</h5>
    </div>
    <div class="modern-card-body">
        <form action="{{ route('admin.v3.inspections.store') }}" method="POST" autocomplete="off">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Marca</label>
                    <select name="brand" id="inspectionBrandSelect" class="form-select" onchange="inspectionLoadModels()">
                        <option value="">— Selecionar —</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->name }}" data-models='@json($brand->models->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'submodels' => $m->submodels->pluck('name')]))' {{ old('brand') === $brand->name ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Modelo</label>
                    <select name="model" id="inspectionModelSelect" class="form-select" onchange="inspectionLoadSubmodels()" disabled>
                        <option value="">— Primeiro selecione a marca —</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Submodelo</label>
                    <select name="sub_model" id="inspectionSubmodelSelect" class="form-select" disabled>
                        <option value="">— Nenhum —</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Versão</label>
                    <input type="text" name="version" class="form-control" value="{{ old('version') }}" placeholder="ex: GT Line">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Ano</label>
                    <input type="number" name="year" class="form-control" value="{{ old('year') }}" min="1900" max="{{ now()->year + 2 }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Quilómetros</label>
                    <input type="number" name="kilometers" class="form-control" value="{{ old('kilometers') }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Combustível</label>
                    <select name="fuel" id="inspectionFuelSelect" class="form-select">
                        <option value="">— Selecionar —</option>
                        @foreach(['Diesel','Gasolina','Híbrido','Elétrico'] as $fuel)
                            <option value="{{ $fuel }}" @selected(old('fuel') === $fuel)>{{ $fuel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Potência</label>
                    <input type="number" name="power" class="form-control" value="{{ old('power') }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">VIN</label>
                    <input type="text" name="vin" class="form-control" value="{{ old('vin') }}" maxlength="32">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Matrícula</label>
                    <input type="text" name="registration" class="form-control" value="{{ old('registration') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cor</label>
                    <input type="text" name="color" class="form-control" value="{{ old('color') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Caixa</label>
                    <select name="transmission" class="form-select">
                        <option value="">—</option>
                        <option value="Manual" @selected(old('transmission') === 'Manual')>Manual</option>
                        <option value="Automática" @selected(old('transmission') === 'Automática')>Automática</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tração</label>
                    <select name="traction" class="form-select">
                        <option value="">—</option>
                        <option value="FWD" @selected(old('traction') === 'FWD')>FWD</option>
                        <option value="RWD" @selected(old('traction') === 'RWD')>RWD</option>
                        <option value="AWD/4x4" @selected(old('traction') === 'AWD/4x4')>AWD/4x4</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Template</label>
                    <select name="template_id" class="form-select">
                        <option value="">—</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" @selected(old('template_id') === $template->id)>{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Notas</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Observações gerais da inspeção…">{{ old('notes') }}</textarea>
                </div>
                <div class="col-12 d-flex gap-2 pt-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-clipboard-check me-1"></i>Criar Inspeção</button>
                    <a href="{{ route('admin.v3.inspections.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const savedBrand = @json(old('brand'));
    const savedModel = @json(old('model'));
    const savedSubmodel = @json(old('sub_model'));
    let bootstrap = true;

    function inspectionLoadModels() {
        const brandSel = document.getElementById('inspectionBrandSelect');
        const modelSel = document.getElementById('inspectionModelSelect');
        const subSel = document.getElementById('inspectionSubmodelSelect');
        const opt = brandSel.options[brandSel.selectedIndex];
        const models = JSON.parse(opt?.dataset.models || '[]');

        modelSel.innerHTML = '<option value="">— Selecionar modelo —</option>';
        models.forEach(model => {
            const option = document.createElement('option');
            option.value = model.name;
            option.textContent = model.name;
            option.dataset.submodels = JSON.stringify(model.submodels);
            modelSel.appendChild(option);
        });

        modelSel.disabled = models.length === 0;
        subSel.innerHTML = '<option value="">— Nenhum —</option>';
        subSel.disabled = true;

        if (bootstrap && savedModel) {
            modelSel.value = savedModel;
            inspectionLoadSubmodels();
        }
    }

    function inspectionLoadSubmodels() {
        const modelSel = document.getElementById('inspectionModelSelect');
        const subSel = document.getElementById('inspectionSubmodelSelect');
        const opt = modelSel.options[modelSel.selectedIndex];
        const subs = JSON.parse(opt?.dataset.submodels || '[]');

        subSel.innerHTML = '<option value="">— Nenhum —</option>';
        subs.forEach(submodel => {
            const option = document.createElement('option');
            option.value = submodel;
            option.textContent = submodel;
            subSel.appendChild(option);
        });
        subSel.disabled = subs.length === 0;

        if (bootstrap && savedSubmodel) {
            subSel.value = savedSubmodel;
        }
    }

    window.inspectionLoadModels = inspectionLoadModels;
    window.inspectionLoadSubmodels = inspectionLoadSubmodels;

    document.addEventListener('DOMContentLoaded', function () {
        if (savedBrand) {
            document.getElementById('inspectionBrandSelect').value = savedBrand;
            inspectionLoadModels();
        }

        bootstrap = false;
    });
})();
</script>
@endsection
