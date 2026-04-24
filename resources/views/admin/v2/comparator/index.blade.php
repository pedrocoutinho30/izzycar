@extends('layouts.admin-v2')

@section('title', 'Comparador de Veículos')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Comparador de Veículos'],
    ],
    'title'       => 'Comparador de Veículos',
    'subtitle'    => 'Compara até 5 veículos lado a lado e descobre o melhor negócio com IA',
    'actionHref'  => '',
    'actionLabel' => '',
])

{{-- ============================================================
     FORMULÁRIO DE ENTRADA
     ============================================================ --}}
<div class="modern-card mb-4" id="inputSection">
    <div class="modern-card-header d-flex justify-content-between align-items-center">
        <h5 class="modern-card-title mb-0">
            <i class="bi bi-pencil-square"></i> Dados dos Veículos
        </h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" id="addCarBtn" onclick="addCar()">
                <i class="bi bi-plus-circle"></i> Adicionar Carro
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" id="removeCarBtn" onclick="removeCar()" style="display:none;">
                <i class="bi bi-dash-circle"></i> Remover Último
            </button>
        </div>
    </div>
    <div class="modern-card-body" id="carsContainer">
        {{-- Os slots de carro são gerados por JS --}}
    </div>
    <div class="px-4 pb-4 d-flex gap-3">
        <button type="button" class="btn btn-primary btn-lg" onclick="buildComparison()">
            <i class="bi bi-columns-gap"></i> Comparar
        </button>
        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetAll()">
            <i class="bi bi-arrow-counterclockwise"></i> Limpar
        </button>
    </div>
</div>

{{-- ============================================================
     TABELA DE COMPARAÇÃO
     ============================================================ --}}
<div id="comparisonSection" style="display:none;">
    <div class="modern-card mb-4">
        <div class="modern-card-header d-flex justify-content-between align-items-center">
            <h5 class="modern-card-title mb-0">
                <i class="bi bi-table"></i> Comparação
            </h5>
            <button type="button" class="btn btn-success" id="analyzeBtn" onclick="analyzeWithAI()">
                <i class="bi bi-stars"></i> Analisar com IA
            </button>
        </div>
        <div class="modern-card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="comparisonTable">
                    <thead></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================
         ANÁLISE IA
         ============================================================ --}}
    <div class="modern-card mb-4" id="aiSection" style="display:none;">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0">
                <i class="bi bi-stars"></i> Análise de IA
            </h5>
        </div>
        <div class="modern-card-body">
            <div id="aiLoading" class="text-center py-4" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">A consultar a IA…</p>
            </div>
            <div id="aiResult" class="ai-result-box" style="display:none;"></div>
        </div>
    </div>
</div>

{{-- ============================================================
     ESTILOS
     ============================================================ --}}
<style>
    /* Car slot card */
    .car-slot {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.25rem;
        transition: border-color .2s;
    }
    .car-slot:focus-within {
        border-color: #0d6efd;
    }
    .car-slot-header {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: 1rem;
        font-weight: 600;
        color: #495057;
    }
    .car-slot-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* Comparison table */
    #comparisonTable th {
        background: #212529;
        color: #fff;
        vertical-align: middle;
        text-align: center;
        white-space: nowrap;
        padding: .75rem 1rem;
    }
    #comparisonTable td {
        vertical-align: middle;
        text-align: center;
        padding: .65rem 1rem;
    }
    #comparisonTable td:first-child {
        text-align: left;
        font-weight: 600;
        background: #f8f9fa;
        white-space: nowrap;
    }
    #comparisonTable tr:hover td {
        background-color: #f0f4ff;
    }
    #comparisonTable td:first-child:hover {
        background-color: #e9ecef;
    }
    .check-yes { color: #198754; font-size: 1.2rem; }
    .check-no  { color: #dc3545; font-size: 1.2rem; }
    .price-cell { font-weight: 700; color: #0d6efd; }
    .row-section-header td {
        background: #e9ecef !important;
        font-weight: 700;
        color: #495057;
        font-size: .85rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    /* AI result */
    .ai-result-box {
        background: #f0f7ff;
        border: 1px solid #b6d4fe;
        border-radius: 10px;
        padding: 1.5rem;
        white-space: pre-wrap;
        font-size: .95rem;
        line-height: 1.7;
    }
    .ai-result-box h3, .ai-result-box h4 { font-size: 1rem; font-weight: 700; margin-top: 1rem; }

    /* Badge best deal */
    .best-deal-badge {
        display: inline-block;
        background: #198754;
        color: #fff;
        font-size: .7rem;
        font-weight: 700;
        padding: .15rem .45rem;
        border-radius: 99px;
        margin-left: .4rem;
        vertical-align: middle;
    }
</style>

{{-- ============================================================
     JAVASCRIPT
     ============================================================ --}}
<script>
// ============================================================
// CONFIGURATION
// ============================================================
const MIN_CARS = 2;
const MAX_CARS = 5;

const FUEL_OPTIONS = [
    { value: 'Gasolina',   label: 'Gasolina' },
    { value: 'Diesel',     label: 'Diesel' },
    { value: 'Híbrido',    label: 'Híbrido' },
    { value: 'Plug-in Híbrido', label: 'Plug-in Híbrido' },
    { value: 'Elétrico',   label: 'Elétrico' },
    { value: 'GPL',        label: 'GPL' },
];

// Extras detection: each entry has a key, label and keyword list (PT/DE/EN)
const EXTRAS_DEFINITIONS = [
    { key: 'parking_sensors',  label: 'Sensores de estacionamento', keywords: ['sensor', 'sensores', 'einparkhilfe', 'parktronic', 'park assist', 'parking sensor', 'pdc', 'parkassist'] },
    { key: 'camera',           label: 'Câmara traseira',            keywords: ['câmara', 'camera', 'kamera', 'rückfahrkamera', 'backup camera', 'rear camera', 'visão traseira', 'camara', 'reversing cam', 'reversing camera'] },
    { key: 'panoramic_roof',   label: 'Teto panorâmico',            keywords: ['panorâmico', 'panoramico', 'panoramic', 'panoramadach', 'glasdach', 'panorama', 'teto panoram'] },
    { key: 'leather',          label: 'Pele / Couro',               keywords: ['couro', 'pele', 'leder', 'leather', 'nappa', 'alcantara'] },
    { key: 'navigation',       label: 'Navegação GPS',              keywords: ['navegação', 'navegacao', 'gps', 'navi', 'navigation', 'sat nav', 'satnav', 'cartes', 'maps'] },
    { key: 'heated_seats',     label: 'Bancos aquecidos',           keywords: ['bancos aquecidos', 'assentos aquecidos', 'sitzheizung', 'heated seats', 'seat heating', 'aquecidos'] },
    { key: 'sunroof',          label: 'Teto de abrir',              keywords: ['tecto solar', 'teto solar', 'tejadilho', 'schiebedach', 'sunroof', 'moonroof', 'toit ouvrant'] },
    { key: 'alloy_wheels',     label: 'Jantes de liga leve',        keywords: ['jantes', 'jantes liga', 'alufelgen', 'alloy wheels', 'felgen', 'alufälgen'] },
    { key: 'bluetooth',        label: 'Bluetooth',                  keywords: ['bluetooth', ' bt '] },
    { key: 'carplay',          label: 'Apple CarPlay / Android Auto', keywords: ['carplay', 'apple carplay', 'android auto', 'wireless carplay'] },
    { key: 'cruise_control',   label: 'Cruise Control',             keywords: ['cruise control', 'tempomat', 'controlo de velocidade', 'piloto automático', 'piloto automatico', 'adaptive cruise'] },
    { key: 'xenon_led',        label: 'Faróis Xénon / LED',         keywords: ['xenon', 'led', 'farois led', 'led-scheinwerfer', 'led headlights', 'full led', 'bi-xenon', 'matrix'] },
    { key: 'fog_lights',       label: 'Faróis de nevoeiro',         keywords: ['nevoeiro', 'faróis de nevoeiro', 'nebelscheinwerfer', 'fog lights', 'fog lamp', 'nebelleuchten'] },
    { key: 'keyless',          label: 'Keyless Entry / Go',         keywords: ['keyless', 'sem chave', 'schlüssellos', 'access&start', 'comfort key', 'keyless go', 'keyless entry', 'smart entry'] },
    { key: 'seven_seats',      label: '7 Lugares',                  keywords: ['7 lugares', '7 lugas', '7 plätze', '7 seats', 'sete lugares', 'seven seats', '7-seater'] },
    { key: 'memory_seats',     label: 'Bancos com memória',         keywords: ['memória', 'memoria', 'gedächtnis', 'memory seats', 'seat memory'] },
    { key: 'electric_seats',   label: 'Bancos elétricos',           keywords: ['bancos elétricos', 'bancos eletricos', 'elektrische sitze', 'electric seats', 'power seats'] },
    { key: 'ambient_light',    label: 'Iluminação ambiente',        keywords: ['iluminação ambiente', 'iluminacao ambiente', 'ambientebeleuchtung', 'ambient lighting', 'ambient light'] },
    { key: 'heads_up',         label: 'Head-Up Display',            keywords: ['head-up', 'heads-up', 'hud', 'projeção no para-brisas'] },
    { key: 'wireless_charging',label: 'Carregamento sem fios',      keywords: ['sem fios', 'wireless charging', 'induktives laden', 'qi charging', 'carregamento wireless'] },
];

// ============================================================
// STATE
// ============================================================
let carCount = 0;

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    // Start with 2 cars
    addCar();
    addCar();
});

// ============================================================
// CAR SLOT MANAGEMENT
// ============================================================
function addCar() {
    if (carCount >= MAX_CARS) return;
    carCount++;

    const container = document.getElementById('carsContainer');
    const col = document.createElement('div');
    col.className = 'mb-3';
    col.id = `carSlot_${carCount}`;
    col.innerHTML = buildCarSlotHTML(carCount);
    container.appendChild(col);

    updateButtons();
}

function removeCar() {
    if (carCount <= MIN_CARS) return;
    const slot = document.getElementById(`carSlot_${carCount}`);
    if (slot) slot.remove();
    carCount--;
    updateButtons();
}

function updateButtons() {
    const addBtn    = document.getElementById('addCarBtn');
    const removeBtn = document.getElementById('removeCarBtn');
    if (addBtn)    addBtn.disabled = (carCount >= MAX_CARS);
    if (removeBtn) removeBtn.style.display = (carCount > MIN_CARS) ? '' : 'none';
}

function buildCarSlotHTML(n) {
    const fuelOpts = FUEL_OPTIONS.map(f =>
        `<option value="${f.value}"${f.value === 'Gasolina' ? ' selected' : ''}>${f.label}</option>`
    ).join('');

    return `
    <div class="car-slot">
        <div class="car-slot-header">
            <span class="car-slot-number">${n}</span>
            Carro ${n}
        </div>
        <div class="row g-2">
            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label form-label-sm">Marca *</label>
                <input type="text" class="form-control form-control-sm" id="brand_${n}" placeholder="BMW" autocomplete="off">
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label form-label-sm">Modelo *</label>
                <input type="text" class="form-control form-control-sm" id="model_${n}" placeholder="320d" autocomplete="off">
            </div>
            <div class="col-4 col-md-3 col-lg-1">
                <label class="form-label form-label-sm">Ano *</label>
                <input type="number" class="form-control form-control-sm" id="year_${n}" placeholder="2020" min="1980" max="2030">
            </div>
            <div class="col-4 col-md-3 col-lg-2">
                <label class="form-label form-label-sm">Km *</label>
                <input type="number" class="form-control form-control-sm" id="km_${n}" placeholder="50000" min="0">
            </div>
            <div class="col-4 col-md-3 col-lg-2">
                <label class="form-label form-label-sm">Combustível *</label>
                <select class="form-select form-select-sm" id="fuel_${n}">${fuelOpts}</select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label form-label-sm">Preço (€) *</label>
                <input type="number" class="form-control form-control-sm" id="price_${n}" placeholder="18500" min="0" step="100">
            </div>
            <div class="col-12">
                <label class="form-label form-label-sm">
                    Lista de Extras
                    <small class="text-muted fw-normal">(texto livre em PT / DE / EN — separados por vírgula ou nova linha)</small>
                </label>
                <textarea class="form-control form-control-sm" id="extras_${n}" rows="3"
                    placeholder="Pele, sensores de estacionamento, câmara traseira, teto panorâmico, LED, Navi…"></textarea>
            </div>
        </div>
    </div>`;
}

// ============================================================
// READ FORM DATA
// ============================================================
function readCars() {
    const cars = [];
    let valid = true;

    for (let i = 1; i <= carCount; i++) {
        const brand = (document.getElementById(`brand_${i}`)?.value || '').trim();
        const model = (document.getElementById(`model_${i}`)?.value || '').trim();
        const year  = parseInt(document.getElementById(`year_${i}`)?.value) || 0;
        const km    = parseInt(document.getElementById(`km_${i}`)?.value)    || 0;
        const fuel  = document.getElementById(`fuel_${i}`)?.value || '';
        const price = parseFloat(document.getElementById(`price_${i}`)?.value) || 0;
        const extras = (document.getElementById(`extras_${i}`)?.value || '').trim();

        if (!brand || !model || !year || !fuel || !price) {
            valid = false;
            highlightInvalid(i);
        }

        cars.push({ brand, model, year, km, fuel, price, extras });
    }

    return valid ? cars : null;
}

function highlightInvalid(n) {
    ['brand','model','year','km','price'].forEach(f => {
        const el = document.getElementById(`${f}_${n}`);
        if (el && !el.value.trim()) {
            el.classList.add('is-invalid');
            el.addEventListener('input', () => el.classList.remove('is-invalid'), { once: true });
        }
    });
}

// ============================================================
// EXTRAS DETECTION
// ============================================================
function detectExtras(extrasText) {
    const lowerText = extrasText.toLowerCase();
    const result = {};
    EXTRAS_DEFINITIONS.forEach(def => {
        result[def.key] = def.keywords.some(kw => lowerText.includes(kw.toLowerCase()));
    });
    return result;
}

// ============================================================
// BUILD COMPARISON TABLE
// ============================================================
function buildComparison() {
    const cars = readCars();
    if (!cars) {
        showToast('Preenche os campos obrigatórios (Marca, Modelo, Ano, Combustível e Preço).', 'danger');
        return;
    }

    const thead = document.querySelector('#comparisonTable thead');
    const tbody = document.querySelector('#comparisonTable tbody');

    // Build header row
    let headerRow = '<tr><th style="width:220px">Atributo</th>';
    cars.forEach((car, i) => {
        headerRow += `<th>🚗 ${escapeHtml(car.brand)} ${escapeHtml(car.model)}<br><small class="opacity-75">${car.year}</small></th>`;
    });
    headerRow += '</tr>';
    thead.innerHTML = headerRow;

    // Compute extras for each car
    const carsExtras = cars.map(car => detectExtras(car.extras));

    // Build rows
    const rows = [];

    // --- Section: Identificação ---
    rows.push({ section: 'Identificação' });
    rows.push({ label: 'Marca',       values: cars.map(c => escapeHtml(c.brand)) });
    rows.push({ label: 'Modelo',      values: cars.map(c => escapeHtml(c.model)) });
    rows.push({ label: 'Ano',         values: cars.map(c => c.year) });
    rows.push({ label: 'Combustível', values: cars.map(c => escapeHtml(c.fuel)) });

    // --- Section: Condição ---
    rows.push({ section: 'Condição' });
    rows.push({
        label: 'Quilómetros',
        values: cars.map(c => formatKm(c.km)),
        highlight: 'min_num',
        rawValues: cars.map(c => c.km),
    });
    rows.push({
        label: 'Preço',
        values: cars.map(c => formatPrice(c.price)),
        className: 'price-cell',
        highlight: 'min_num',
        rawValues: cars.map(c => c.price),
    });

    // --- Section: Extras ---
    rows.push({ section: 'Equipamentos' });
    EXTRAS_DEFINITIONS.forEach(def => {
        rows.push({
            label: def.label,
            values: carsExtras.map(e => e[def.key]),
            isBoolean: true,
        });
    });

    // Render rows
    let tbodyHTML = '';
    rows.forEach(row => {
        if (row.section) {
            tbodyHTML += `<tr class="row-section-header"><td colspan="${cars.length + 1}">${row.section}</td></tr>`;
            return;
        }

        // Find best value indices for highlighting
        let bestIndices = [];
        if (row.highlight === 'min_num' && row.rawValues) {
            const minVal = Math.min(...row.rawValues);
            bestIndices = row.rawValues.map((v, i) => v === minVal ? i : -1).filter(i => i >= 0);
        }

        tbodyHTML += `<tr><td>${row.label}</td>`;
        row.values.forEach((val, i) => {
            const isBest = bestIndices.includes(i);
            let cellClass = row.className || '';
            let cellContent = '';

            if (row.isBoolean) {
                cellContent = val
                    ? '<i class="bi bi-check-circle-fill check-yes" title="Sim"></i>'
                    : '<i class="bi bi-x-circle-fill check-no" title="Não"></i>';
            } else {
                cellContent = val;
                if (isBest) {
                    cellContent += `<span class="best-deal-badge" title="Melhor valor">✓</span>`;
                    cellClass += ' table-success';
                }
            }

            tbodyHTML += `<td class="${cellClass}">${cellContent}</td>`;
        });
        tbodyHTML += '</tr>';
    });
    tbody.innerHTML = tbodyHTML;

    // Show comparison section
    document.getElementById('comparisonSection').style.display = '';
    document.getElementById('aiSection').style.display = 'none';
    document.getElementById('aiResult').style.display = 'none';
    document.getElementById('aiResult').innerHTML = '';

    // Smooth scroll
    document.getElementById('comparisonSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ============================================================
// AI ANALYSIS
// ============================================================
function analyzeWithAI() {
    const cars = readCars();
    if (!cars) {
        showToast('Primeiro compara os veículos.', 'danger');
        return;
    }

    const aiSection = document.getElementById('aiSection');
    const aiLoading = document.getElementById('aiLoading');
    const aiResult  = document.getElementById('aiResult');
    const analyzeBtn = document.getElementById('analyzeBtn');

    aiSection.style.display = '';
    aiLoading.style.display = '';
    aiResult.style.display  = 'none';
    aiResult.innerHTML = '';
    analyzeBtn.disabled = true;
    analyzeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> A analisar…';

    aiSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

    // Build payload
    const payload = { cars };

    fetch('{{ route("admin.v2.comparator.analyze") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify(payload),
    })
    .then(res => res.json())
    .then(data => {
        aiLoading.style.display = 'none';
        aiResult.style.display  = '';
        analyzeBtn.disabled = false;
        analyzeBtn.innerHTML = '<i class="bi bi-stars"></i> Analisar com IA';

        if (data.error) {
            aiResult.innerHTML = `<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> ${escapeHtml(data.error)}</div>`;
        } else {
            aiResult.innerHTML = formatAIResponse(data.analysis || '');
        }
    })
    .catch(err => {
        aiLoading.style.display = 'none';
        aiResult.style.display  = '';
        analyzeBtn.disabled = false;
        analyzeBtn.innerHTML = '<i class="bi bi-stars"></i> Analisar com IA';
        aiResult.innerHTML = `<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> Erro de rede: ${escapeHtml(err.message)}</div>`;
    });
}

// ============================================================
// RESET
// ============================================================
function resetAll() {
    // Remove all slots
    const container = document.getElementById('carsContainer');
    container.innerHTML = '';
    carCount = 0;

    // Hide comparison
    document.getElementById('comparisonSection').style.display = 'none';

    // Re-add 2 slots
    addCar();
    addCar();
}

// ============================================================
// HELPERS
// ============================================================
function formatKm(km) {
    if (!km) return '0 km';
    return new Intl.NumberFormat('pt-PT').format(km) + ' km';
}

function formatPrice(price) {
    if (!price) return '0 €';
    return new Intl.NumberFormat('pt-PT', { style: 'currency', currency: 'EUR', minimumFractionDigits: 0 }).format(price);
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

function formatAIResponse(text) {
    // Basic markdown-like formatting
    let html = escapeHtml(text);
    // Bold: **text**
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    // Headers: ### text
    html = html.replace(/^### (.+)$/gm, '<h4>$1</h4>');
    html = html.replace(/^## (.+)$/gm,  '<h3>$1</h3>');
    html = html.replace(/^# (.+)$/gm,   '<h2>$1</h2>');
    // Bullet points: - text
    html = html.replace(/^[-•] (.+)$/gm, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>');
    // Newlines
    html = html.replace(/\n/g, '<br>');
    return html;
}

function showToast(message, type = 'info') {
    // Simple inline alert near the compare button
    const existing = document.getElementById('comparatorToast');
    if (existing) existing.remove();

    const alert = document.createElement('div');
    alert.id = 'comparatorToast';
    alert.className = `alert alert-${type} alert-dismissible fade show mt-3 mx-4`;
    alert.role = 'alert';
    alert.innerHTML = `${escapeHtml(message)} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;

    document.getElementById('inputSection').appendChild(alert);

    setTimeout(() => { if (alert.parentNode) alert.remove(); }, 4000);
}
</script>

@endsection
