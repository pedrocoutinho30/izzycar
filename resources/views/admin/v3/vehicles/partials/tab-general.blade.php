<form id="form-general" autocomplete="off">
    <div class="v3-tab-errors" style="display:none"></div>

    <div class="row g-3">

        {{-- ── Identificação ──────────────────────────────────────────── --}}
        <div class="col-12">
            <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.06em">Identificação</h6>
        </div>

        {{-- Marca --}}
        <div class="col-md-4">
            <label class="form-label fw-semibold">Marca</label>
            <select name="brand" id="v3BrandSelect" class="form-select" onchange="v3LoadModels()">
                <option value="">— Selecionar —</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->name }}"
                            data-models="{{ json_encode($brand->models->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'submodels' => $m->submodels->pluck('name')])) }}"
                            {{ old('brand', $vehicle->brand) === $brand->name ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Modelo --}}
        <div class="col-md-4">
            <label class="form-label fw-semibold">Modelo</label>
            <select name="model" id="v3ModelSelect" class="form-select" onchange="v3LoadSubmodels()" disabled>
                <option value="">— Primeiro selecione a marca —</option>
            </select>
        </div>

        {{-- Submodelo --}}
        <div class="col-md-4" id="v3SubmodelGroup">
            <label class="form-label fw-semibold">Submodelo <span class="text-muted fw-normal small">(opcional)</span></label>
            <select name="sub_model" id="v3SubmodelSelect" class="form-select" disabled>
                <option value="">— Nenhum —</option>
            </select>
        </div>

        {{-- Versão --}}
        <div class="col-md-4">
            <label class="form-label fw-semibold">Versão / Acabamento</label>
            <input type="text" name="version" class="form-control" value="{{ old('version', $vehicle->version) }}" placeholder="ex: GTI 2.0 TSI">
        </div>

        {{-- Ano + Mês + Dia --}}
        <div class="col-md-2">
            <label class="form-label fw-semibold">Ano</label>
            <input type="number" name="year" id="v3Year" class="form-control"
                   value="{{ old('year', $vehicle->year) }}" min="1900" max="{{ now()->year + 2 }}"
                   oninput="v3ToggleMonthDay()">
        </div>
        <div class="col-md-2" id="v3MonthGroup" style="{{ $vehicle->year ? '' : 'display:none' }}">
            <label class="form-label fw-semibold">Mês <span class="text-muted fw-normal small">(opcional)</span></label>
            <select name="month" id="v3Month" class="form-select" onchange="v3ToggleDayField()">
                <option value="">—</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ old('month', $vehicle->month) == $m ? 'selected' : '' }}>
                        {{ str_pad($m, 2, '0', STR_PAD_LEFT) }} — {{ \Carbon\Carbon::createFromDate(2000, $m, 1)->locale('pt')->isoFormat('MMMM') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2" id="v3DayGroup" style="{{ $vehicle->month ? '' : 'display:none' }}">
            <label class="form-label fw-semibold">Dia <span class="text-muted fw-normal small">(opcional)</span></label>
            <select name="day" id="v3Day" class="form-select">
                <option value="">—</option>
                @foreach(range(1,31) as $d)
                    <option value="{{ $d }}" {{ old('day', $vehicle->day) == $d ? 'selected' : '' }}>
                        {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Combustível --}}
        <div class="col-md-3">
            <label class="form-label fw-semibold">Combustível</label>
            <select name="fuel" class="form-select">
                <option value="">— Selecionar —</option>
                @foreach(\App\Models\V3Vehicle::fuelOptions() as $f)
                    <option value="{{ $f }}" {{ old('fuel', $vehicle->fuel) === $f ? 'selected' : '' }}>{{ $f }}</option>
                @endforeach
            </select>
        </div>

        {{-- Cor --}}
        <div class="col-md-3">
            <label class="form-label fw-semibold">Cor</label>
            <input type="text" name="color" class="form-control" value="{{ old('color', $vehicle->color) }}" placeholder="ex: Cinzento Platina">
        </div>

        {{-- VIN --}}
        <div class="col-md-4">
            <label class="form-label fw-semibold">VIN / Nº de Série</label>
            <input type="text" name="vin" class="form-control" value="{{ old('vin', $vehicle->vin) }}" maxlength="17" placeholder="17 caracteres">
        </div>

        {{-- ── Métricas ────────────────────────────────────────────────── --}}
        <div class="col-12 mt-2">
            <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.06em">Métricas</h6>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold">Quilómetros</label>
            <div class="input-group">
                <input type="number" name="kilometers" class="form-control" value="{{ old('kilometers', $vehicle->kilometers) }}" min="0">
                <span class="input-group-text">km</span>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Potência</label>
            <div class="input-group">
                <input type="number" name="power" class="form-control" value="{{ old('power', $vehicle->power) }}" min="0">
                <span class="input-group-text">CV</span>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Cilindrada</label>
            <div class="input-group">
                <input type="number" name="cylinder_capacity" class="form-control" value="{{ old('cylinder_capacity', $vehicle->cylinder_capacity) }}" min="0">
                <span class="input-group-text">cc</span>
            </div>
        </div>

        {{-- ── Matrícula & Datas ───────────────────────────────────────── --}}
        <div class="col-12 mt-2">
            <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.06em">Matrícula & Datas</h6>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-semibold">Matrícula</label>
            <input type="text" name="registration" class="form-control" value="{{ old('registration', $vehicle->registration) }}" placeholder="ex: AA-00-BB">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Data Fabrico</label>
            <input type="date" name="manufacture_date" class="form-control" value="{{ old('manufacture_date', $vehicle->manufacture_date?->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Data Registo</label>
            <input type="date" name="register_date" class="form-control" value="{{ old('register_date', $vehicle->register_date?->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Disponível para Venda</label>
            <input type="date" name="available_to_sell_date" class="form-control" value="{{ old('available_to_sell_date', $vehicle->available_to_sell_date?->format('Y-m-d')) }}">
        </div>

        {{-- ── Estado & Flags ──────────────────────────────────────────── --}}
        <div class="col-12 mt-2">
            <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.06em">Estado</h6>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Estado do Veículo</label>
            <select name="status" class="form-select">
                @foreach(\App\Models\V3Vehicle::statusOptions() as $key => $label)
                    <option value="{{ $key }}" {{ old('status', $vehicle->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex flex-column gap-2 justify-content-end pb-1">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="show_online" id="showOnline" value="1"
                       {{ old('show_online', $vehicle->show_online) ? 'checked' : '' }}>
                <label class="form-check-label" for="showOnline">Mostrar Online</label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_imported" id="isImported" value="1"
                       {{ old('is_imported', $vehicle->is_imported) ? 'checked' : '' }}
                       onchange="v3ToggleImportedHint()">
                <label class="form-check-label" for="isImported">
                    <i class="bi bi-globe2 me-1"></i>Veículo Importado
                </label>
            </div>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div id="importedHint" class="small {{ $vehicle->is_imported ? '' : 'd-none' }}" style="padding-bottom:.3rem">
                <div class="alert alert-info py-2 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    A tab <strong>Legalização</strong> está disponível para gerir o processo de importação.
                </div>
            </div>
        </div>

        {{-- ── Preço de Venda ──────────────────────────────────────────── --}}
        <div class="col-12 mt-2">
            <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.06em">Preço</h6>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Preço de Venda (€)</label>
            <input type="number" name="asking_price" class="form-control" step="0.01" min="0"
                   value="{{ old('asking_price', $vehicle->asking_price) }}" placeholder="ex: 12500.00">
            <div class="form-text">Preço exibido no website</div>
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold">Notas Internas</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Observações internas sobre este veículo…">{{ old('notes', $vehicle->notes) }}</textarea>
        </div>

        {{-- ── Actions ─────────────────────────────────────────────────── --}}
        <div class="col-12 d-flex gap-2 pt-2">
            <button type="button" class="btn btn-primary" onclick="v3SaveTab('general')">
                <i class="bi bi-check-lg me-1"></i> Guardar Informação Geral
            </button>
        </div>
    </div>
</form>

<script>
// ── Brand → Model → Submodel cascade ─────────────────────────────────
(function () {
    const savedBrand    = @json(old('brand', $vehicle->brand));
    const savedModel    = @json(old('model', $vehicle->model));
    const savedSubmodel = @json(old('sub_model', $vehicle->sub_model));

    function v3LoadModels(triggerSub) {
        const brandSel  = document.getElementById('v3BrandSelect');
        const modelSel  = document.getElementById('v3ModelSelect');
        const subSel    = document.getElementById('v3SubmodelSelect');
        const opt       = brandSel.options[brandSel.selectedIndex];
        const models    = JSON.parse(opt.dataset.models || '[]');

        modelSel.innerHTML = '<option value="">— Selecionar modelo —</option>';
        models.forEach(m => {
            const o = document.createElement('option');
            o.value = m.name;
            o.textContent = m.name;
            o.dataset.submodels = JSON.stringify(m.submodels);
            modelSel.appendChild(o);
        });
        modelSel.disabled = models.length === 0;

        subSel.innerHTML = '<option value="">— Nenhum —</option>';
        subSel.disabled = true;

        if (triggerSub && savedModel) {
            modelSel.value = savedModel;
            v3LoadSubmodels(true);
        }
    }

    function v3LoadSubmodels(restoreSaved) {
        const modelSel = document.getElementById('v3ModelSelect');
        const subSel   = document.getElementById('v3SubmodelSelect');
        const opt      = modelSel.options[modelSel.selectedIndex];
        const subs     = JSON.parse(opt?.dataset.submodels || '[]');

        subSel.innerHTML = '<option value="">— Nenhum —</option>';
        subs.forEach(s => {
            const o = document.createElement('option');
            o.value = s; o.textContent = s;
            subSel.appendChild(o);
        });
        subSel.disabled = subs.length === 0;

        if (restoreSaved && savedSubmodel) {
            subSel.value = savedSubmodel;
        }
    }

    // Expose globally for onchange handlers
    window.v3LoadModels    = () => v3LoadModels(false);
    window.v3LoadSubmodels = () => v3LoadSubmodels(false);

    // Restore state on page load
    document.addEventListener('DOMContentLoaded', function () {
        if (savedBrand) {
            document.getElementById('v3BrandSelect').value = savedBrand;
            v3LoadModels(true);
        }
    });
})();

// ── Year → show/hide month ────────────────────────────────────────────
function v3ToggleMonthDay() {
    const yr = document.getElementById('v3Year').value;
    const mg = document.getElementById('v3MonthGroup');
    const dg = document.getElementById('v3DayGroup');
    if (!yr) { mg.style.display = 'none'; dg.style.display = 'none'; }
    else      { mg.style.display = ''; v3ToggleDayField(); }
}

function v3ToggleDayField() {
    const mo = document.getElementById('v3Month').value;
    const dg = document.getElementById('v3DayGroup');
    dg.style.display = mo ? '' : 'none';
    if (!mo) document.getElementById('v3Day').value = '';
}

// ── Imported toggle hint ──────────────────────────────────────────────
function v3ToggleImportedHint() {
    const chk  = document.getElementById('isImported');
    const hint = document.getElementById('importedHint');
    hint.classList.toggle('d-none', !chk.checked);
}
</script>
