<form id="form-purchase" autocomplete="off">
    <div class="v3-tab-errors" style="display:none"></div>

    <div class="row g-3">

        <div class="col-12">
            <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.06em">Fornecedor & Data</h6>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Fornecedor</label>
            <select name="supplier_id" class="form-select">
                <option value="">— Sem fornecedor —</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ old('supplier_id', $vehicle->supplier_id) == $s->id ? 'selected' : '' }}>
                        {{ $s->company_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Data de Compra</label>
            <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', $vehicle->purchase_date?->format('Y-m-d')) }}">
        </div>

        <div class="col-12 mt-2">
            <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.06em">Regime IVA de Compra</h6>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Tipo de Compra (IVA)</label>
            <select name="purchase_type" id="purchaseType" class="form-select" onchange="updatePurchaseHint()">
                <option value="">— Selecionar —</option>
                <option value="Geral"    {{ old('purchase_type', $vehicle->purchase_type) === 'Geral'    ? 'selected' : '' }}>Regime Geral (IVA dedutível)</option>
                <option value="Margem"   {{ old('purchase_type', $vehicle->purchase_type) === 'Margem'   ? 'selected' : '' }}>Regime de Margem</option>
                <option value="Sem Iva"  {{ old('purchase_type', $vehicle->purchase_type) === 'Sem Iva'  ? 'selected' : '' }}>Isento de IVA</option>
            </select>
        </div>

        {{-- IVA rate (Geral only) --}}
        <div class="col-md-3" id="vatRateGroup" style="display:none">
            <label class="form-label fw-semibold">Taxa IVA (%)</label>
            <select name="purchase_vat_rate" id="purchaseVatRate" class="form-select" onchange="updatePurchaseHint()">
                <option value="">—</option>
                <option value="23" {{ old('purchase_vat_rate', $vehicle->purchase_vat_rate) == 23 ? 'selected' : '' }}>23%</option>
                <option value="13" {{ old('purchase_vat_rate', $vehicle->purchase_vat_rate) == 13 ? 'selected' : '' }}>13%</option>
                <option value="6"  {{ old('purchase_vat_rate', $vehicle->purchase_vat_rate) == 6  ? 'selected' : '' }}>6%</option>
            </select>
        </div>

        <div class="col-12">
            <div id="purchaseHint" class="small text-muted"></div>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold" id="purchasePriceLabel">Preço de Compra</label>
            <div class="input-group">
                <input type="number" name="purchase_price" id="purchasePrice" class="form-control"
                       value="{{ old('purchase_price', $vehicle->purchase_price) }}"
                       step="0.01" min="0" oninput="updateGeralCalc()">
                <span class="input-group-text">€</span>
            </div>
            <div id="geralCalcNote" class="small text-muted mt-1 d-none">
                Valor bruto estimado: <strong id="geralGross">—</strong>
            </div>
        </div>

        {{-- IVA pago (Geral only) --}}
        <div class="col-md-3" id="vatPaidGroup" style="display:none">
            <label class="form-label fw-semibold">IVA Pago na Compra (€)</label>
            <div class="input-group">
                <input type="number" name="purchase_vat_paid" id="purchaseVatPaid" class="form-control"
                       value="{{ old('purchase_vat_paid', $vehicle->purchase_vat_paid) }}"
                       step="0.01" min="0">
                <span class="input-group-text">€</span>
            </div>
        </div>

        <div class="col-12 d-flex gap-2 pt-2">
            <button type="button" class="btn btn-primary" onclick="v3SaveTab('purchase')">
                <i class="bi bi-check-lg me-1"></i> Guardar Dados de Compra
            </button>
        </div>
    </div>
</form>

<script>
function updatePurchaseHint() {
    const type    = document.getElementById('purchaseType').value;
    const hint    = document.getElementById('purchaseHint');
    const rateGrp = document.getElementById('vatRateGroup');
    const paidGrp = document.getElementById('vatPaidGroup');
    const calc    = document.getElementById('geralCalcNote');
    const lbl     = document.getElementById('purchasePriceLabel');

    rateGrp.style.display = type === 'Geral' ? '' : 'none';
    paidGrp.style.display = type === 'Geral' ? '' : 'none';
    calc.classList.toggle('d-none', type !== 'Geral');

    const hints = {
        'Geral':   '<i class="bi bi-info-circle me-1 text-primary"></i>Insira o valor <u>líquido</u> (sem IVA). O sistema calculará o IVA automaticamente.',
        'Margem':  '<i class="bi bi-info-circle me-1 text-secondary"></i>Insira o valor <u>bruto</u> total pago (IVA de margem — não se deduz na compra).',
        'Sem Iva': '<i class="bi bi-info-circle me-1 text-secondary"></i>Insira o valor <u>bruto</u> total pago (compra isenta de IVA).',
    };
    hint.innerHTML = hints[type] || '';

    if (type === 'Geral') {
        lbl.textContent = 'Preço de Compra (líquido s/ IVA)';
        updateGeralCalc();
    } else {
        lbl.textContent = 'Preço de Compra';
    }
}

function updateGeralCalc() {
    const net  = parseFloat(document.getElementById('purchasePrice').value);
    const rate = parseFloat(document.getElementById('purchaseVatRate')?.value) || 23;
    const span = document.getElementById('geralGross');
    if (!isNaN(net) && net > 0) {
        const gross = net * (1 + rate / 100);
        span.textContent = gross.toLocaleString('pt-PT', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' €';
    } else {
        span.textContent = '—';
    }
}

document.addEventListener('DOMContentLoaded', updatePurchaseHint);
</script>
