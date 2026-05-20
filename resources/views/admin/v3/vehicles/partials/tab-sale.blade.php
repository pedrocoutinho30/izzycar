@php
    $sale         = $vehicle->sales->first();
    $purchaseNet  = (float) ($vehicle->purchase_price ?? 0);
    $purchaseVatPaid = (float) ($vehicle->purchase_vat_paid ?? 0);
    $manualExpenses  = $vehicle->expenses->where('movement_type','expense')->where('category','!=','vehicle_purchase');
    $expensesTotal   = $manualExpenses->sum('amount_gross');
    $totalCosts      = round($purchaseNet + $expensesTotal, 2);
@endphp

{{-- ══ VENDA REGISTADA ══════════════════════════════════════════════ --}}
@if($sale)
<div class="alert alert-success d-flex align-items-center mb-4">
    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
    <div class="flex-grow-1">
        <strong>Venda Registada</strong>
        <div class="small mt-1">
            Vendido em {{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') : '—' }}
            por <strong>{{ number_format($sale->sale_price, 2, ',', '.') }} €</strong>
            @if($sale->client) · Cliente: {{ $sale->client->name }} @endif
        </div>
    </div>
    <form action="{{ route('admin.v3.vehicles.sale.destroy', [$vehicle->id, $sale->id]) }}" method="POST"
          onsubmit="return confirm('Anular esta venda?')" class="ms-3">
        @csrf @method('DELETE')
        <button class="btn btn-sm btn-outline-danger">
            <i class="bi bi-x-lg me-1"></i> Anular venda
        </button>
    </form>
</div>

<div class="row g-3 mb-4 text-center">
    @php
        $metricCards = [
            ['label' => 'Preço Venda', 'value' => number_format($sale->sale_price, 2, ',', '.') . ' €', 'color' => 'primary'],
            ['label' => 'Margem Bruta', 'value' => number_format($sale->gross_margin, 2, ',', '.') . ' €', 'color' => ($sale->gross_margin >= 0 ? 'success' : 'danger')],
            ['label' => 'Margem Líquida', 'value' => number_format($sale->net_margin, 2, ',', '.') . ' €', 'color' => ($sale->net_margin >= 0 ? 'success' : 'danger')],
            ['label' => 'IVA Entregue', 'value' => number_format($sale->vat_settle_sale, 2, ',', '.') . ' €', 'color' => 'secondary'],
            ['label' => 'Rent. Bruta', 'value' => number_format($sale->gross_profitability, 1, ',', '.') . '%', 'color' => 'info'],
            ['label' => 'Rent. Líquida', 'value' => number_format($sale->net_profitability, 1, ',', '.') . '%', 'color' => 'info'],
        ];
    @endphp
    @foreach($metricCards as $card)
    <div class="col-6 col-md-2">
        <div class="card border-0 bg-light h-100">
            <div class="card-body py-2">
                <p class="text-muted small mb-1">{{ $card['label'] }}</p>
                <p class="fw-bold small mb-0 text-{{ $card['color'] }}">{{ $card['value'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else

{{-- ══ FORMULÁRIO DE REGISTO ════════════════════════════════════════ --}}
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="badge bg-primary px-3 py-2">REGISTO DE VENDA</span>
        <small class="text-muted">Preencha para registar a venda deste veículo</small>
    </div>

    <form action="{{ route('admin.v3.vehicles.sale.store', $vehicle->id) }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Data de Venda *</label>
                <input type="date" name="sale_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Preço de Venda (c/ IVA) *</label>
                <div class="input-group">
                    <input type="number" name="sale_price" id="salePriceInput" class="form-control"
                           step="0.01" min="0" required oninput="saleCalc(); checkSaleReady()">
                    <span class="input-group-text">€</span>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Regime IVA de Venda *</label>
                <select name="vat_type" id="saleVatType" class="form-select" onchange="saleCalc()" required>
                    <option value="margem">Margem</option>
                    <option value="geral_23" selected>Geral 23%</option>
                    <option value="geral_13">Geral 13%</option>
                    <option value="geral_6">Geral 6%</option>
                    <option value="isento">Isento</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Método de Pagamento</label>
                <select name="payment_method" class="form-select">
                    <option value="">—</option>
                    @foreach(\App\Models\Expense::paymentMethods() as $k => $l)
                        <option value="{{ $k }}">{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Cliente *</label>
                <select name="client_id" id="saleClientId" class="form-select" onchange="checkSaleReady()" required>
                    <option value="">— Selecione um cliente —</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-semibold">Observação</label>
                <input type="text" name="observation" class="form-control" placeholder="Nota sobre esta venda…">
            </div>

            {{-- Live preview --}}
            <div class="col-12">
                <div id="salePreview" class="d-none">
                    <div class="row g-2 text-center">
                        <div class="col-6 col-md-3"><div class="card border-0 bg-light"><div class="card-body py-2"><p class="text-muted small mb-0">Receita Líquida</p><p class="fw-bold small mb-0" id="spNetRevenue">—</p></div></div></div>
                        <div class="col-6 col-md-3"><div class="card border-0 bg-light"><div class="card-body py-2"><p class="text-muted small mb-0">IVA a Entregar</p><p class="fw-bold small mb-0" id="spVatOwed">—</p></div></div></div>
                        <div class="col-6 col-md-3" id="spGrossCard"><div class="card border-0 bg-light"><div class="card-body py-2"><p class="text-muted small mb-0">Margem Bruta</p><p class="fw-bold small mb-0" id="spGross">—</p><small class="text-muted" id="spGrossPct"></small></div></div></div>
                        <div class="col-6 col-md-3" id="spNetCard"><div class="card border-0 bg-light"><div class="card-body py-2"><p class="text-muted small mb-0">Margem Líquida</p><p class="fw-bold small mb-0" id="spNet">—</p><small class="text-muted" id="spNetPct"></small></div></div></div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" id="saleSubmitBtn" class="btn btn-success" disabled>
                    <i class="bi bi-check-circle me-1"></i> Registar Venda
                </button>
                <small class="text-muted ms-2" id="saleReadyHint">Preencha o preço de venda e selecione um cliente para continuar.</small>
            </div>
        </div>
    </form>
</div>
@endif

{{-- ══ SIMULADOR ════════════════════════════════════════════════════ --}}
<hr class="my-4">
<div class="d-flex align-items-center gap-2 mb-3">
    <span class="badge bg-secondary px-3 py-2">SIMULAÇÃO</span>
    <small class="text-muted">Os valores abaixo são apenas simulação e não são guardados</small>
</div>

{{-- Cost structure --}}
<div class="card bg-light border-0 mb-3">
    <div class="card-body py-2 px-3">
        <p class="mb-1 text-muted fw-semibold text-uppercase" style="font-size:.65rem;letter-spacing:.05em">Estrutura de Custos</p>
        <div class="row g-1 small">
            <div class="col-6 col-md-3">
                <span class="text-muted">Preço Compra</span><br>
                <strong>{{ number_format($purchaseNet, 2, ',', '.') }} €</strong>
                @if($vehicle->purchase_type)<span class="badge bg-secondary ms-1" style="font-size:.6rem">{{ $vehicle->purchase_type }}</span>@endif
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted">IVA Compra recuperável</span><br>
                <strong>{{ $vehicle->purchase_type === 'Geral' ? number_format($purchaseVatPaid, 2, ',', '.') . ' €' : '0,00 €' }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted">Despesas ({{ $manualExpenses->count() }})</span><br>
                <strong>{{ number_format($expensesTotal, 2, ',', '.') }} €</strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted">Total Custos</span><br>
                <strong id="simTotalCosts">{{ number_format($totalCosts, 2, ',', '.') }} €</strong>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Regime IVA de Venda</label>
        <select id="simVatType" class="form-select form-select-sm" onchange="simCalcV3()">
            <option value="margem">Margem</option>
            <option value="geral_23" selected>Geral 23%</option>
            <option value="geral_13">Geral 13%</option>
            <option value="geral_6">Geral 6%</option>
            <option value="isento">Isento</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Preço de Venda (c/ IVA)</label>
        <div class="input-group input-group-sm">
            <input type="number" id="simSellPrice" class="form-control" step="0.01" min="0" placeholder="0.00" oninput="simCalcV3()">
            <span class="input-group-text">€</span>
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Despesas Adicionais (simulação)</label>
        <div class="input-group input-group-sm">
            <input type="number" id="simExtraCosts" class="form-control" step="0.01" min="0" value="0" oninput="simCalcV3()">
            <span class="input-group-text">€</span>
        </div>
    </div>
</div>

<div id="simResults" class="d-none">
    <div class="row g-2 text-center mb-3">
        <div class="col-6 col-md-3"><div class="card border-0 bg-white shadow-sm h-100"><div class="card-body py-2"><p class="text-muted small mb-1">Receita Líquida</p><p class="fw-bold small mb-0" id="simNetRevenue">—</p><small class="text-muted" style="font-size:.7rem">s/ IVA</small></div></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 bg-white shadow-sm h-100"><div class="card-body py-2"><p class="text-muted small mb-1">IVA a Entregar</p><p class="fw-bold small mb-0" id="simVatOwed">—</p></div></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100" id="simGrossCard"><div class="card-body py-2"><p class="text-muted small mb-1">Margem Bruta</p><p class="fw-bold small mb-0" id="simGross">—</p><small id="simGrossPct" class="text-muted" style="font-size:.7rem"></small></div></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100" id="simNetCard"><div class="card-body py-2"><p class="text-muted small mb-1">Margem Líquida</p><p class="fw-bold small mb-0" id="simNet">—</p><small id="simNetPct" class="text-muted" style="font-size:.7rem"></small></div></div></div>
    </div>
    <div id="simAlert" class="alert py-2 small mb-0"></div>
</div>

<script>
const V3_PREVIEW_URL = '{{ route("admin.v3.vehicles.sale.preview", $vehicle->id) }}';
const V3_CSRF        = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
const V3_BASE_COSTS  = {{ $totalCosts }};

function fmt(v) { return v.toLocaleString('pt-PT', { minimumFractionDigits:2, maximumFractionDigits:2 }); }

async function fetchMetrics(salePrice, vatType, extraCosts = 0) {
    const res = await fetch(V3_PREVIEW_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': V3_CSRF,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ sale_price: salePrice, vat_type: vatType, extra_costs: extraCosts }),
    });
    if (!res.ok) throw new Error('preview_failed');
    return res.json();
}

// ── Sale form: enable submit only when price + client are filled ───
function checkSaleReady() {
    const price  = parseFloat(document.getElementById('salePriceInput')?.value) || 0;
    const client = document.getElementById('saleClientId')?.value || '';
    const btn    = document.getElementById('saleSubmitBtn');
    const hint   = document.getElementById('saleReadyHint');
    if (!btn) return;
    const ready = price > 0 && client !== '';
    btn.disabled = !ready;
    if (hint) hint.classList.toggle('d-none', ready);
}

// ── Sale form live preview ─────────────────────────────────────────
async function saleCalc() {
    const price   = parseFloat(document.getElementById('salePriceInput')?.value) || 0;
    const vat     = document.getElementById('saleVatType')?.value || 'geral_23';
    const preview = document.getElementById('salePreview');
    if (price <= 0 || !preview) { preview?.classList.add('d-none'); return; }

    try {
        const m = await fetchMetrics(price, vat, 0);
        preview.classList.remove('d-none');
        document.getElementById('spNetRevenue').textContent = fmt(m.net_revenue) + ' €';
        document.getElementById('spVatOwed').textContent    = fmt(m.vat_settle_sale) + ' €';
        document.getElementById('spGross').textContent      = fmt(m.gross_margin) + ' €';
        document.getElementById('spNet').textContent        = fmt(m.net_margin) + ' €';
        document.getElementById('spGrossPct').textContent   = m.gross_profitability.toFixed(1) + '%';
        document.getElementById('spNetPct').textContent     = m.net_profitability.toFixed(1) + '%';
        document.getElementById('spGrossCard').className    = 'col-6 col-md-3';
        document.getElementById('spNetCard').className      = 'col-6 col-md-3';
        const gc = document.querySelector('#spGrossCard .card');
        const nc = document.querySelector('#spNetCard .card');
        if (gc) gc.className = 'card border-0 h-100 ' + (m.gross_margin >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10');
        if (nc) nc.className = 'card border-0 h-100 ' + (m.net_margin   >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10');
    } catch { /* ignore network errors during preview */ }
}

// ── Simulator ─────────────────────────────────────────────────────
async function simCalcV3() {
    const price = parseFloat(document.getElementById('simSellPrice').value) || 0;
    const vat   = document.getElementById('simVatType').value;
    const extra = parseFloat(document.getElementById('simExtraCosts').value) || 0;

    document.getElementById('simTotalCosts').textContent = fmt(V3_BASE_COSTS + extra) + ' €';
    if (price <= 0) { document.getElementById('simResults').classList.add('d-none'); return; }

    try {
        const m = await fetchMetrics(price, vat, extra);
        document.getElementById('simNetRevenue').textContent = fmt(m.net_revenue) + ' €';
        document.getElementById('simVatOwed').textContent    = fmt(m.vat_settle_sale) + ' €';
        document.getElementById('simGross').textContent      = fmt(m.gross_margin) + ' €';
        document.getElementById('simNet').textContent        = fmt(m.net_margin) + ' €';
        document.getElementById('simGrossPct').textContent   = m.gross_profitability.toFixed(1) + '%';
        document.getElementById('simNetPct').textContent     = m.net_profitability.toFixed(1) + '%';
        document.getElementById('simGrossCard').className    = 'card border-0 shadow-sm h-100 ' + (m.gross_margin >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10');
        document.getElementById('simNetCard').className      = 'card border-0 shadow-sm h-100 ' + (m.net_margin   >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10');

        const alertEl = document.getElementById('simAlert');
        if (m.net_margin < 0) {
            alertEl.className = 'alert alert-danger py-2 small mb-0';
            alertEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Venda com prejuízo.</strong>';
        } else if (m.net_profitability < 5) {
            alertEl.className = 'alert alert-warning py-2 small mb-0';
            alertEl.innerHTML = '<i class="bi bi-info-circle me-1"></i>Margem líquida baixa (' + m.net_profitability.toFixed(1) + '%).';
        } else {
            alertEl.className = 'alert alert-success py-2 small mb-0';
            alertEl.innerHTML = '<i class="bi bi-check-circle me-1"></i>Margem saudável de <strong>' + m.net_profitability.toFixed(1) + '%</strong>.';
        }
        document.getElementById('simResults').classList.remove('d-none');
    } catch { /* ignore */ }
}
</script>
