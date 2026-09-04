@extends('layouts.admin-v2')

@php
    $isEdit = $search !== null;
    $val = fn ($key, $default = null) => old($key, data_get($spec ?? [], $key, $default));
    $equipmentOptions = $equipmentOptions ?? collect();
    $selectedEquipmentIds = old('equipment_ids', $selectedEquipmentIds ?? []);
@endphp

@section('title', $isEdit ? 'Editar Pesquisa — Radar AutoScout24' : 'Nova Pesquisa — Radar AutoScout24')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ $isEdit ? route('admin.v2.radar.show', $search) : route('admin.v2.radar.index') }}" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left"></i> {{ $isEdit ? $search->name : 'Todos os radares' }}
            </a>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-broadcast"></i> {{ $isEdit ? 'Editar Pesquisa' : 'Nova Pesquisa' }}
            </h1>
            <p class="text-muted mb-0">Marca e modelo vêm diretamente da AutoScout24 — escolhe nos selects, sem risco de escrever o slug errado.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('admin.v2.radar.update', $search) : route('admin.v2.radar.store') }}">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-car-front"></i>
                    Marca e modelo
                </h5>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Marca</label>
                    <select name="make" id="f_make" class="form-select" required>
                        <option value="">Selecione a marca</option>
                        @foreach($makes as $make)
                        <option value="{{ $make['slug'] }}" {{ $val('make') === $make['slug'] ? 'selected' : '' }}>{{ $make['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Modelo</label>
                    <select name="model" id="f_model" class="form-select" disabled>
                        <option value="">Escolhe primeiro a marca</option>
                    </select>
                    <div class="form-text">Deixa vazio para pesquisar todos os modelos desta marca.</div>
                </div>
            </div>
        </div>

        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-ui-checks"></i>
                    Motorização, carroçaria e equipamento
                </h5>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Motorização</label>
                    <select name="motor_type" id="f_motor_type" class="form-select" disabled>
                        <option value="">Escolhe primeiro o modelo</option>
                    </select>
                    <div class="form-text">Ex.: E 220 d, E 300 de.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Variante de carroçaria</label>
                    <select name="model_variant" id="f_model_variant" class="form-select" disabled>
                        <option value="">Escolhe primeiro o modelo</option>
                    </select>
                    <div class="form-text">Ex.: carrinha (T-modell), All-terrain.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Linha de equipamento</label>
                    <select name="trim" id="f_trim" class="form-select" disabled>
                        <option value="">Escolhe primeiro o modelo</option>
                    </select>
                    <div class="form-text">Ex.: AMG Line, Avantgarde.</div>
                </div>
            </div>
        </div>

        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-sliders"></i>
                    Filtros
                </h5>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Ano de matrícula, de</label>
                    <input type="number" name="fregfrom" class="form-control" min="1990" max="{{ date('Y') + 1 }}" placeholder="2018" value="{{ $val('fregfrom') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ano de matrícula, até</label>
                    <input type="number" name="fregto" class="form-control" min="1990" max="{{ date('Y') + 1 }}" placeholder="2022" value="{{ $val('fregto') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quilómetros, de</label>
                    <input type="number" name="kmfrom" class="form-control" min="0" placeholder="0" value="{{ $val('kmfrom') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quilómetros, até</label>
                    <input type="number" name="kmto" class="form-control" min="0" placeholder="100000" value="{{ $val('kmto') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Potência, de</label>
                    <input type="number" name="powerfrom" class="form-control" min="0" placeholder="150" value="{{ $val('powerfrom') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Potência, até</label>
                    <input type="number" name="powerto" class="form-control" min="0" placeholder="" value="{{ $val('powerto') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Unidade de potência</label>
                    <select name="powertype" class="form-select">
                        <option value="">—</option>
                        @foreach($powertypeOptions as $option)
                        <option value="{{ $option['value'] }}" {{ $val('powertype', 'hp') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Combustível</label>
                    <select name="fuel" id="f_fuel" class="form-select">
                        <option value="">Qualquer</option>
                        @foreach($fuelOptions as $option)
                        <option value="{{ $option['value'] }}" {{ $val('fuel') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Caixa</label>
                    <select name="gear" id="f_gear" class="form-select">
                        <option value="">Qualquer</option>
                        @foreach($gearOptions as $option)
                        <option value="{{ $option['value'] }}" {{ $val('gear') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Preço, de (€)</label>
                    <input type="number" name="pricefrom" class="form-control" min="0" placeholder="0" value="{{ $val('pricefrom') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Preço, até (€)</label>
                    <input type="number" name="priceto" class="form-control" min="0" placeholder="30000" value="{{ $val('priceto') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipo de vendedor</label>
                    <select name="custtype" class="form-select">
                        <option value="">Todos</option>
                        <option value="D" {{ $val('custtype') === 'D' ? 'selected' : '' }}>Profissional (stand)</option>
                        <option value="P" {{ $val('custtype') === 'P' ? 'selected' : '' }}>Particular</option>
                    </select>
                </div>
                <div class="col-md-9 d-flex align-items-center">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="service_history" value="1" id="f_service_history" class="form-check-input" {{ $val('service_history') ? 'checked' : '' }}>
                        <label class="form-check-label" for="f_service_history">
                            Só com histórico completo de revisões (Scheckheftgepflegt)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-flag"></i>
                    Comparar com Portugal (Standvirtual)
                </h5>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="pt_enabled" value="1" id="f_pt_enabled" class="form-check-input" {{ $val('pt_enabled') ? 'checked' : '' }}>
                <label class="form-check-label" for="f_pt_enabled">
                    Recolher também anúncios do Standvirtual, para comparar preços em Portugal
                </label>
            </div>

            <div id="pt_fields" class="row g-3" style="display:none;">
                <div class="col-md-6">
                    <label class="form-label">Marca (Standvirtual)</label>
                    <select name="pt_make" id="f_pt_make" class="form-select">
                        <option value="">Selecione a marca</option>
                        @foreach($ptMakes as $make)
                        <option value="{{ $make['slug'] }}" {{ $val('pt_make') === $make['slug'] ? 'selected' : '' }}>{{ $make['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">O Standvirtual só mostra as 20 marcas mais populares aqui - fala comigo se precisares de outra.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Modelo (Standvirtual)</label>
                    <select name="pt_model" id="f_pt_model" class="form-select" disabled>
                        <option value="">Escolhe primeiro a marca</option>
                    </select>
                    <div class="form-text">Deixa vazio para pesquisar todos os modelos desta marca.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Ano de matrícula, de</label>
                    <input type="number" name="pt_fregfrom" class="form-control" min="1990" max="{{ date('Y') + 1 }}" value="{{ $val('pt_fregfrom') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ano de matrícula, até</label>
                    <input type="number" name="pt_fregto" class="form-control" min="1990" max="{{ date('Y') + 1 }}" value="{{ $val('pt_fregto') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quilómetros, de</label>
                    <input type="number" name="pt_kmfrom" class="form-control" min="0" value="{{ $val('pt_kmfrom') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quilómetros, até</label>
                    <input type="number" name="pt_kmto" class="form-control" min="0" value="{{ $val('pt_kmto') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Potência, de</label>
                    <input type="number" name="pt_powerfrom" class="form-control" min="0" value="{{ $val('pt_powerfrom') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Potência, até</label>
                    <input type="number" name="pt_powerto" class="form-control" min="0" value="{{ $val('pt_powerto') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Combustível</label>
                    <select name="pt_fuel" class="form-select">
                        <option value="">Qualquer</option>
                        @foreach($ptFuelOptions as $value => $label)
                        <option value="{{ $value }}" {{ $val('pt_fuel') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Caixa</label>
                    <select name="pt_gear" class="form-select">
                        <option value="">Qualquer</option>
                        @foreach($ptGearOptions as $value => $label)
                        <option value="{{ $value }}" {{ $val('pt_gear') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Preço, de (€)</label>
                    <input type="number" name="pt_pricefrom" class="form-control" min="0" value="{{ $val('pt_pricefrom') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Preço, até (€)</label>
                    <input type="number" name="pt_priceto" class="form-control" min="0" value="{{ $val('pt_priceto') }}">
                </div>
            </div>
        </div>

        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-flag"></i>
                    Comparar com Portugal (Carmine.pt)
                </h5>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="carmine_enabled" value="1" id="f_carmine_enabled" class="form-check-input" {{ $val('carmine_enabled') ? 'checked' : '' }}>
                <label class="form-check-label" for="f_carmine_enabled">
                    Recolher também anúncios do Carmine.pt (stands), para comparar preços em Portugal
                </label>
            </div>

            <div id="carmine_fields" class="row g-3" style="display:none;">
                <div class="col-md-6">
                    <label class="form-label">Marca (Carmine.pt)</label>
                    <select name="carmine_make" id="f_carmine_make" class="form-select">
                        <option value="">Selecione a marca</option>
                        @foreach($carmineMakes as $make)
                        <option value="{{ $make['slug'] }}" {{ $val('carmine_make') === $make['slug'] ? 'selected' : '' }}>{{ $make['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Modelo (Carmine.pt)</label>
                    <select name="carmine_model" id="f_carmine_model" class="form-select" disabled>
                        <option value="">Escolhe primeiro a marca</option>
                    </select>
                    <div class="form-text">Deixa vazio para pesquisar todos os modelos desta marca. A lista demora um pouco a carregar - cada modelo é confirmado ao vivo no Carmine.pt.</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Ano de matrícula, até</label>
                    <input type="number" name="carmine_fregto" class="form-control" min="1990" max="{{ date('Y') + 1 }}" value="{{ $val('carmine_fregto') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quilómetros, até</label>
                    <input type="number" name="carmine_kmto" class="form-control" min="0" value="{{ $val('carmine_kmto') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Preço, até (€)</label>
                    <input type="number" name="carmine_priceto" class="form-control" min="0" value="{{ $val('carmine_priceto') }}">
                </div>
                <div class="form-text">O Carmine.pt só permite filtrar por limite máximo (não tem "de").</div>
            </div>
        </div>

        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-list-check"></i>
                    Equipamento
                </h5>
            </div>
            @if($equipmentOptions->isEmpty())
                <p class="text-muted small mb-0">
                    Ainda não há equipamento disponível para filtrar. Vai sendo descoberto automaticamente à medida que as pesquisas correm -
                    depois ativa-o em <a href="{{ route('admin.v2.radar-equipment.index') }}">Equipamento</a> para poder escolhê-lo aqui.
                </p>
            @else
                <p class="text-muted small">O anúncio só entra nesta pesquisa se tiver <strong>todo</strong> o equipamento selecionado abaixo.</p>
                <div class="row g-2">
                    @foreach($equipmentOptions as $option)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="checkbox" name="equipment_ids[]" value="{{ $option->id }}" id="f_equipment_{{ $option->id }}" class="form-check-input"
                                   {{ in_array($option->id, $selectedEquipmentIds) ? 'checked' : '' }}>
                            <label class="form-check-label" for="f_equipment_{{ $option->id }}">{{ $option->label }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-tag"></i>
                    Nome da pesquisa
                </h5>
            </div>
            @if($isEdit)
                <input type="text" id="f_name" class="form-control" value="{{ $search->name }}" disabled>
                <div class="form-text">O nome não pode ser alterado depois de criada a pesquisa - cria uma pesquisa nova se precisares de outro nome.</div>
            @else
                <label class="form-label">Identificador único</label>
                <input type="text" name="name" id="f_name" class="form-control" placeholder="mercedes-benz-e-klasse-diesel-auto" value="{{ $val('name') }}" pattern="[a-z0-9]+(-[a-z0-9]+)*" required>
                <div class="form-text">Só letras minúsculas, números e hífens. É sugerido automaticamente a partir dos filtros acima, mas podes editar.</div>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-play-circle"></i> {{ $isEdit ? 'Guardar Alterações e Atualizar Dados' : 'Criar Pesquisa e Recolher Dados' }}
        </button>
    </form>

</div>

<script>
(function () {
    const makeSelect = document.getElementById('f_make');
    const modelSelect = document.getElementById('f_model');
    const motorTypeSelect = document.getElementById('f_motor_type');
    const modelVariantSelect = document.getElementById('f_model_variant');
    const trimSelect = document.getElementById('f_trim');
    const fuelSelect = document.getElementById('f_fuel');
    const gearSelect = document.getElementById('f_gear');
    const nameInput = document.getElementById('f_name');
    const ptEnabledCheckbox = document.getElementById('f_pt_enabled');
    const ptFieldsWrap = document.getElementById('pt_fields');
    const ptMakeSelect = document.getElementById('f_pt_make');
    const ptModelSelect = document.getElementById('f_pt_model');
    const carmineEnabledCheckbox = document.getElementById('f_carmine_enabled');
    const carmineFieldsWrap = document.getElementById('carmine_fields');
    const carmineMakeSelect = document.getElementById('f_carmine_make');
    const carmineModelSelect = document.getElementById('f_carmine_model');
    const isEdit = @json($isEdit);

    const initialModel = @json($val('model'));
    const initialMotorType = @json($val('motor_type'));
    const initialModelVariant = @json($val('model_variant'));
    const initialTrim = @json($val('trim'));
    const initialPtModel = @json($val('pt_model'));
    const initialCarmineModel = @json($val('carmine_model'));
    // Em edição o nome é fixo (input disabled) - nunca se deve tentar sugerir/sobrescrever.
    let nameDirty = isEdit || nameInput.value.trim().length > 0;
    if (!isEdit) {
        nameInput.addEventListener('input', () => { nameDirty = true; });
    }

    function slugify(text) {
        return (text || '')
            .toString()
            .toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function selectedOptionSlug(selectEl) {
        if (!selectEl.value) return '';
        const option = selectEl.options[selectEl.selectedIndex];
        return slugify(option ? option.textContent : selectEl.value);
    }

    function updateSuggestedName() {
        if (nameDirty) return;
        const parts = [
            makeSelect.value,
            modelSelect.value,
            selectedOptionSlug(motorTypeSelect),
            selectedOptionSlug(modelVariantSelect),
            selectedOptionSlug(trimSelect),
            selectedOptionSlug(fuelSelect),
            selectedOptionSlug(gearSelect),
        ].filter(Boolean).map(slugify).filter(Boolean);
        nameInput.value = parts.join('-');
    }

    function loadModels(makeSlug, selectedModel) {
        modelSelect.innerHTML = '<option value="">A carregar modelos...</option>';
        modelSelect.disabled = true;
        resetSubmodelSelects('Escolhe primeiro o modelo');

        if (!makeSlug) {
            modelSelect.innerHTML = '<option value="">Escolhe primeiro a marca</option>';
            return;
        }

        fetch(`{{ route('admin.v2.radar.models') }}?make=${encodeURIComponent(makeSlug)}`)
            .then(r => r.json())
            .then(models => {
                const options = ['<option value="">— Todos os modelos —</option>']
                    .concat(models.map(m => `<option value="${m.slug}" ${m.slug === selectedModel ? 'selected' : ''}>${m.label}</option>`));
                modelSelect.innerHTML = options.join('');
                modelSelect.disabled = false;
                updateSuggestedName();
                if (modelSelect.value) {
                    loadSubmodels(makeSlug, modelSelect.value, { motorType: initialMotorType, modelVariant: initialModelVariant, trim: initialTrim });
                }
            })
            .catch(() => {
                modelSelect.innerHTML = '<option value="">Não foi possível carregar os modelos</option>';
            });
    }

    function resetSubmodelSelects(placeholder) {
        [motorTypeSelect, modelVariantSelect, trimSelect].forEach(select => {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            select.disabled = true;
        });
    }

    function fillSubmodelSelect(select, options, selectedValue, emptyLabel) {
        const optionsHtml = [`<option value="">${emptyLabel}</option>`]
            .concat(options.map(o => `<option value="${o.value}" ${o.value === selectedValue ? 'selected' : ''}>${o.label}</option>`));
        select.innerHTML = optionsHtml.join('');
        select.disabled = options.length === 0;
    }

    function loadSubmodels(makeSlug, modelSlug, selected) {
        resetSubmodelSelects('A carregar...');

        if (!makeSlug || !modelSlug) {
            resetSubmodelSelects('Escolhe primeiro o modelo');
            return;
        }

        selected = selected || {};

        fetch(`{{ route('admin.v2.radar.submodels') }}?make=${encodeURIComponent(makeSlug)}&model=${encodeURIComponent(modelSlug)}`)
            .then(r => r.json())
            .then(data => {
                fillSubmodelSelect(motorTypeSelect, data.motorTypes || [], selected.motorType, 'Qualquer');
                fillSubmodelSelect(modelVariantSelect, data.modelVariants || [], selected.modelVariant, 'Qualquer');
                fillSubmodelSelect(trimSelect, data.trims || [], selected.trim, 'Qualquer');
                updateSuggestedName();
            })
            .catch(() => {
                resetSubmodelSelects('Não foi possível carregar');
            });
    }

    makeSelect.addEventListener('change', () => loadModels(makeSelect.value, null));
    modelSelect.addEventListener('change', () => {
        updateSuggestedName();
        loadSubmodels(makeSelect.value, modelSelect.value, {});
    });
    motorTypeSelect.addEventListener('change', updateSuggestedName);
    modelVariantSelect.addEventListener('change', updateSuggestedName);
    trimSelect.addEventListener('change', updateSuggestedName);
    fuelSelect.addEventListener('change', updateSuggestedName);
    gearSelect.addEventListener('change', updateSuggestedName);

    if (makeSelect.value) {
        loadModels(makeSelect.value, initialModel);
    }

    function loadPtModels(makeSlug, selectedModel) {
        ptModelSelect.innerHTML = '<option value="">A carregar modelos...</option>';
        ptModelSelect.disabled = true;

        if (!makeSlug) {
            ptModelSelect.innerHTML = '<option value="">Escolhe primeiro a marca</option>';
            return;
        }

        fetch(`{{ route('admin.v2.radar.pt-models') }}?make=${encodeURIComponent(makeSlug)}`)
            .then(r => r.json())
            .then(models => {
                const options = ['<option value="">— Todos os modelos —</option>']
                    .concat(models.map(m => `<option value="${m.slug}" ${m.slug === selectedModel ? 'selected' : ''}>${m.label}</option>`));
                ptModelSelect.innerHTML = options.join('');
                ptModelSelect.disabled = false;
            })
            .catch(() => {
                ptModelSelect.innerHTML = '<option value="">Não foi possível carregar os modelos</option>';
            });
    }

    function togglePtFields() {
        ptFieldsWrap.style.display = ptEnabledCheckbox.checked ? '' : 'none';
    }

    ptEnabledCheckbox.addEventListener('change', togglePtFields);
    ptMakeSelect.addEventListener('change', () => loadPtModels(ptMakeSelect.value, null));

    togglePtFields();
    if (ptMakeSelect.value) {
        loadPtModels(ptMakeSelect.value, initialPtModel);
    }

    function toggleCarmineFields() {
        carmineFieldsWrap.style.display = carmineEnabledCheckbox.checked ? '' : 'none';
    }

    function loadCarmineModels(makeSlug, selectedModel) {
        carmineModelSelect.innerHTML = '<option value="">A confirmar modelos reais no Carmine.pt... (pode demorar 20-30s)</option>';
        carmineModelSelect.disabled = true;

        if (!makeSlug) {
            carmineModelSelect.innerHTML = '<option value="">Escolhe primeiro a marca</option>';
            return;
        }

        fetch(`{{ route('admin.v2.radar.carmine-models') }}?make=${encodeURIComponent(makeSlug)}`)
            .then(r => r.json())
            .then(models => {
                const options = ['<option value="">— Todos os modelos —</option>']
                    .concat(models.map(m => `<option value="${m.slug}" ${m.slug === selectedModel ? 'selected' : ''}>${m.label}</option>`));
                carmineModelSelect.innerHTML = options.join('');
                carmineModelSelect.disabled = false;
            })
            .catch(() => {
                carmineModelSelect.innerHTML = '<option value="">Não foi possível carregar os modelos</option>';
            });
    }

    carmineEnabledCheckbox.addEventListener('change', toggleCarmineFields);
    carmineMakeSelect.addEventListener('change', () => loadCarmineModels(carmineMakeSelect.value, null));

    toggleCarmineFields();
    if (carmineMakeSelect.value) {
        loadCarmineModels(carmineMakeSelect.value, initialCarmineModel);
    }
})();
</script>
@endsection
