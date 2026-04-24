@extends('layouts.admin-v2')

@section('title', 'Calculadora de Rentabilidade')

@section('content')
<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-calculator"></i> Calculadora de Rentabilidade
            </h1>
            <p class="text-muted mb-0">Análise de compra e venda de veículos importados</p>
        </div>
    </div>

    <div class="row">

        <!-- ── Formulário ── -->
        <div class="col-lg-6">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-pencil-square"></i> Parâmetros da Operação
                    </h5>
                </div>
                <div class="modern-card-body">
                    <form id="profitForm" novalidate>

                        <!-- Regime Fiscal -->
                        <div class="mb-4">
                            <h6 class="section-title text-primary">
                                <i class="bi bi-file-earmark-text"></i> Regime Fiscal
                            </h6>
                            <div class="regime-selector">
                                <div class="regime-option">
                                    <input type="radio" name="regime" id="regimeNormal" value="normal" checked>
                                    <label for="regimeNormal">
                                        <i class="bi bi-receipt"></i>
                                        <span class="regime-name">Regime Normal</span>
                                        <small>IVA sobre o preço de venda</small>
                                    </label>
                                </div>
                                <div class="regime-option">
                                    <input type="radio" name="regime" id="regimeMargem" value="margem">
                                    <label for="regimeMargem">
                                        <i class="bi bi-percent"></i>
                                        <span class="regime-name">Regime de Margem</span>
                                        <small>IVA apenas sobre a margem</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Compra -->
                        <div class="mb-4">
                            <h6 class="section-title text-primary">
                                <i class="bi bi-cart-fill"></i> Compra
                            </h6>
                            <div class="mb-3">
                                <label for="purchasePrice" class="form-label">
                                    Preço de Compra
                                    <small class="text-muted">(sem IVA recuperável)</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control" id="purchasePrice"
                                           placeholder="0.00" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- Despesas -->
                        <div class="mb-4">
                            <h6 class="section-title text-warning">
                                <i class="bi bi-wallet2"></i> Despesas
                            </h6>
                            <div class="mb-3">
                                <label for="expensesTotal" class="form-label">
                                    Despesas Totais
                                    <small class="text-muted">(transporte, reparações, documentação…)</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control" id="expensesTotal"
                                           placeholder="0.00" step="0.01" min="0" value="0">
                                </div>
                            </div>
                            <!-- Só relevante no regime normal; desactivado no regime de margem -->
                            <div class="mb-3" id="expensesVATRow">
                                <label for="expensesVAT" class="form-label">
                                    IVA incluído nas Despesas
                                    <small class="text-muted">(dedutível no regime normal)</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control" id="expensesVAT"
                                           placeholder="0.00" step="0.01" min="0" value="0">
                                </div>
                            </div>
                        </div>

                        <!-- IVA -->
                        <div class="mb-4">
                            <h6 class="section-title text-secondary">
                                <i class="bi bi-percent"></i> Taxa de IVA
                            </h6>
                            <div class="mb-3">
                                <label for="vatRate" class="form-label">Taxa aplicável</label>
                                <div class="input-group" style="max-width: 160px;">
                                    <input type="number" class="form-control" id="vatRate"
                                           value="23" step="0.1" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Modo de Cálculo -->
                        <div class="mb-4">
                            <h6 class="section-title text-success">
                                <i class="bi bi-sliders"></i> Modo de Cálculo
                            </h6>
                            <div class="mode-selector mb-3">
                                <div class="mode-option">
                                    <input type="radio" name="calcMode" id="modeGivenSalePrice"
                                           value="givenSalePrice" checked>
                                    <label for="modeGivenSalePrice">
                                        <i class="bi bi-tag"></i> Preço de Venda
                                    </label>
                                </div>
                                <div class="mode-option">
                                    <input type="radio" name="calcMode" id="modeTargetProfit"
                                           value="targetProfit">
                                    <label for="modeTargetProfit">
                                        <i class="bi bi-currency-euro"></i> Lucro Desejado
                                    </label>
                                </div>
                                <div class="mode-option">
                                    <input type="radio" name="calcMode" id="modeTargetMargin"
                                           value="targetMargin">
                                    <label for="modeTargetMargin">
                                        <i class="bi bi-graph-up-arrow"></i> Margem Desejada
                                    </label>
                                </div>
                            </div>

                            <!-- Input condicional: Preço de Venda (com IVA) -->
                            <div id="inputGivenSalePrice">
                                <label for="givenSalePrice" class="form-label">
                                    Preço de Venda <small class="text-muted">(com IVA)</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control" id="givenSalePrice"
                                           placeholder="0.00" step="0.01" min="0">
                                </div>
                            </div>

                            <!-- Input condicional: Lucro líquido desejado -->
                            <div id="inputTargetProfit" style="display:none;">
                                <label for="targetProfit" class="form-label">Lucro Líquido Desejado</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control" id="targetProfit"
                                           placeholder="0.00" step="0.01" min="0">
                                </div>
                            </div>

                            <!-- Input condicional: Margem % sobre preço de venda -->
                            <div id="inputTargetMargin" style="display:none;">
                                <label for="targetMargin" class="form-label">
                                    Margem Desejada
                                    <small class="text-muted">(% sobre preço de venda)</small>
                                </label>
                                <div class="input-group" style="max-width: 200px;">
                                    <input type="number" class="form-control" id="targetMargin"
                                           placeholder="0.0" step="0.1" min="0" max="99">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-calculator"></i> Calcular
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="resetBtn">
                                <i class="bi bi-arrow-counterclockwise"></i> Limpar
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- ── Resultados ── -->
        <div class="col-lg-6">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-graph-up"></i> Resultados
                    </h5>
                </div>
                <div class="modern-card-body">

                    <!-- Estado vazio -->
                    <div id="emptyState" class="text-center py-5">
                        <i class="bi bi-calculator" style="font-size: 4rem; color: #ddd;"></i>
                        <p class="text-muted mt-3">Preencha os dados e clique em "Calcular" para ver os resultados.</p>
                    </div>

                    <!-- Resultados calculados -->
                    <div id="resultsContainer" style="display:none;">

                        <!-- Badges de contexto -->
                        <div class="mb-3">
                            <span class="badge regime-badge" id="regimeBadge">Regime Normal</span>
                            <span class="badge mode-badge ms-1" id="modeBadge">Preço de Venda</span>
                        </div>

                        <!-- Custo -->
                        <div class="result-section mb-3">
                            <h6 class="result-section-title text-danger">
                                <i class="bi bi-arrow-down-circle-fill"></i> Custo
                            </h6>
                            <div class="result-item">
                                <span class="result-label">Preço de Compra:</span>
                                <span class="result-value" id="res-purchasePrice">€ 0,00</span>
                            </div>
                            <div class="result-item">
                                <span class="result-label">Despesas Totais:</span>
                                <span class="result-value" id="res-expensesTotal">€ 0,00</span>
                            </div>
                            <!-- Linha visível apenas no regime normal -->
                            <div class="result-item" id="res-expensesVATRow">
                                <span class="result-label text-success">IVA Deduzido (despesas):</span>
                                <span class="result-value text-success" id="res-expensesVAT">- € 0,00</span>
                            </div>
                            <div class="result-item border-top pt-2 mt-1">
                                <span class="result-label fw-bold">Custo Total:</span>
                                <span class="result-value fw-bold text-danger" id="res-totalCost">€ 0,00</span>
                            </div>
                        </div>

                        <!-- Venda -->
                        <div class="result-section mb-3">
                            <h6 class="result-section-title text-success">
                                <i class="bi bi-arrow-up-circle-fill"></i> Venda
                            </h6>
                            <!-- Linha visível apenas no regime normal (há distinção formal ex/inc IVA) -->
                            <div class="result-item" id="res-salePriceExVATRow">
                                <span class="result-label">Preço de Venda (sem IVA):</span>
                                <span class="result-value" id="res-salePriceExVAT">€ 0,00</span>
                            </div>
                            <div class="result-item">
                                <span class="result-label">
                                    IVA (sobre <span id="res-vatBase">venda</span>):
                                </span>
                                <span class="result-value text-muted" id="res-vatAmount">€ 0,00</span>
                            </div>
                            <div class="result-item border-top pt-2 mt-1">
                                <span class="result-label fw-bold">Preço de Venda (com IVA):</span>
                                <span class="result-value fw-bold text-success" id="res-salePriceIncVAT">€ 0,00</span>
                            </div>
                        </div>

                        <!-- Lucro & Indicadores -->
                        <div class="profit-box text-center rounded p-4" id="profitBox">
                            <div class="row g-3">
                                <div class="col-12">
                                    <small class="profit-label">Lucro Líquido</small>
                                    <div class="profit-value" id="res-profit">€ 0,00</div>
                                </div>
                                <div class="col-6">
                                    <div class="profit-metric">
                                        <small class="profit-label">Markup</small>
                                        <div class="profit-metric-value" id="res-markup">0,00%</div>
                                        <small class="profit-sublabel">sobre custo</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="profit-metric">
                                        <small class="profit-label">Margem</small>
                                        <div class="profit-metric-value" id="res-margin">0,00%</div>
                                        <small class="profit-sublabel">sobre preço de venda</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .modern-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .modern-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e9ecef;
    }
    .modern-card-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
    }
    .modern-card-body {
        padding: 24px;
    }

    /* ── Section titles ── */
    .section-title {
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.75rem;
        padding-bottom: 0.35rem;
        border-bottom: 2px solid currentColor;
        opacity: 0.85;
    }

    /* ── Regime selector ── */
    .regime-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .regime-option input[type="radio"] { display: none; }
    .regime-option label {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 12px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        background: #f8f9fa;
        gap: 4px;
        user-select: none;
    }
    .regime-option label i { font-size: 1.4rem; color: #adb5bd; }
    .regime-option label .regime-name { font-weight: 600; font-size: 0.9rem; color: #495057; }
    .regime-option label small { color: #adb5bd; font-size: 0.75rem; }
    .regime-option input:checked + label { border-color: #667eea; background: #f0f2ff; }
    .regime-option input:checked + label i,
    .regime-option input:checked + label .regime-name { color: #667eea; }

    /* ── Mode selector ── */
    .mode-selector {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 8px;
    }
    .mode-option input[type="radio"] { display: none; }
    .mode-option label {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 10px 8px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        background: #f8f9fa;
        font-size: 0.8rem;
        font-weight: 500;
        color: #6c757d;
        gap: 4px;
        user-select: none;
    }
    .mode-option label i { font-size: 1.1rem; }
    .mode-option input:checked + label { border-color: #28a745; background: #f0fff4; color: #28a745; }

    /* ── Field disabled (regime de margem) ── */
    .field-disabled { opacity: 0.4; pointer-events: none; }

    /* ── Results ── */
    .result-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
    }
    .result-section-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.6rem;
    }
    .result-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.35rem 0;
        font-size: 0.9rem;
    }
    .result-label { color: #6c757d; }
    .result-value { font-weight: 600; color: #2c3e50; }

    /* ── Profit box ── */
    .profit-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.35);
    }
    .profit-box.positive {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        box-shadow: 0 4px 15px rgba(56, 239, 125, 0.3);
    }
    .profit-box.negative {
        background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
        box-shadow: 0 4px 15px rgba(238, 9, 121, 0.3);
    }
    .profit-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.85; }
    .profit-sublabel { font-size: 0.7rem; opacity: 0.7; }
    .profit-value { font-size: 2rem; font-weight: 700; line-height: 1.2; }
    .profit-metric { background: rgba(255,255,255,0.15); border-radius: 8px; padding: 10px; }
    .profit-metric-value { font-size: 1.4rem; font-weight: 700; }

    /* ── Badges ── */
    .regime-badge { background-color: #667eea; font-size: 0.8rem; padding: 0.4em 0.7em; }
    .mode-badge   { background-color: #28a745; font-size: 0.8rem; padding: 0.4em 0.7em; }

    /* ── Form controls ── */
    .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25); }
    .input-group-text { background-color: #f8f9fa; border-color: #ced4da; font-weight: 600; }
</style>

<script>
/**
 * ============================================================
 * Calculadora de Rentabilidade — Veículos Importados
 * Regimes suportados: IVA Normal | IVA sobre a Margem
 * ============================================================
 */

// ── Formatação ────────────────────────────────────────────────

/** Formata um número como moeda em euros (notação PT). */
function fmtCurrency(value) {
    return '\u20ac\u00a0' + value.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/** Formata uma percentagem com 2 casas decimais (notação PT). */
function fmtPercent(value) {
    return value.toFixed(2).replace('.', ',') + '\u00a0%';
}

// ── Cálculo: Regime Normal de IVA ────────────────────────────
/**
 * No regime normal:
 *   - O IVA das despesas (expensesVAT) é dedutível ao Estado.
 *   - Custo real = purchasePrice + (expensesTotal − expensesVAT)
 *   - O preço sem IVA é a base económica; o IVA é um passante.
 *   - salePriceIncVAT = salePriceExVAT × (1 + vatRate)
 *
 * @param {number} purchasePrice  Preço de compra (sem IVA recuperável)
 * @param {number} expensesTotal  Total de despesas
 * @param {number} expensesVAT    Parcela de IVA nas despesas (dedutível)
 * @param {number} vatRate        Taxa de IVA em decimal (ex: 0.23)
 * @param {string} mode           'givenSalePrice' | 'targetProfit' | 'targetMargin'
 * @param {number} modeValue      Valor associado ao modo escolhido
 * @returns {object} Resultado com todos os indicadores
 */
function calcNormalRegime(purchasePrice, expensesTotal, expensesVAT, vatRate, mode, modeValue) {
    // Custo efectivo: o IVA das despesas é recuperado, por isso não pesa no custo
    const totalCost = purchasePrice + (expensesTotal - expensesVAT);

    let salePriceExVAT;

    if (mode === 'givenSalePrice') {
        // O utilizador fornece o preço de venda com IVA; invertemos para obter o valor líquido
        salePriceExVAT = modeValue / (1 + vatRate);

    } else if (mode === 'targetProfit') {
        // profit = salePriceExVAT − totalCost  →  salePriceExVAT = totalCost + profit
        salePriceExVAT = totalCost + modeValue;

    } else { // targetMargin
        // margin% = profit / salePriceExVAT  →  salePriceExVAT = totalCost / (1 − margin)
        const m = modeValue / 100;
        if (m >= 1) throw new Error('A margem não pode ser ≥ 100%.');
        salePriceExVAT = totalCost / (1 - m);
    }

    const salePriceIncVAT = salePriceExVAT * (1 + vatRate);
    const vatAmount       = salePriceIncVAT - salePriceExVAT;  // IVA liquidado na venda
    const profit          = salePriceExVAT - totalCost;         // Lucro líquido (sem IVA, que é passante)
    const markup          = totalCost > 0 ? (profit / totalCost) * 100 : 0;
    const margin          = salePriceExVAT > 0 ? (profit / salePriceExVAT) * 100 : 0;

    return { totalCost, salePriceExVAT, salePriceIncVAT, vatAmount, profit, markup, margin, vatBase: 'venda' };
}

// ── Cálculo: Regime de IVA sobre a Margem ────────────────────
/**
 * No regime de margem (usado em carros usados):
 *   - Não há dedução de IVA nas despesas.
 *   - Custo total = purchasePrice + expensesTotal
 *   - O IVA incide apenas sobre a margem bruta:
 *       margem     = salePriceIncVAT − totalCost
 *       vatAmount  = margem × vatRate / (1 + vatRate)
 *   - O preço de venda já inclui o IVA embutido na margem.
 *   - Lucro líquido = margem − vatAmount = margem / (1 + vatRate)
 *
 * @param {number} purchasePrice  Preço de compra
 * @param {number} expensesTotal  Total de despesas
 * @param {number} vatRate        Taxa de IVA em decimal (ex: 0.23)
 * @param {string} mode           'givenSalePrice' | 'targetProfit' | 'targetMargin'
 * @param {number} modeValue      Valor associado ao modo escolhido
 * @returns {object} Resultado com todos os indicadores
 */
function calcMargemRegime(purchasePrice, expensesTotal, vatRate, mode, modeValue) {
    // Sem dedução: o IVA das despesas não é recuperado
    const totalCost = purchasePrice + expensesTotal;

    let salePriceIncVAT;

    if (mode === 'givenSalePrice') {
        // Preço de venda já inclui o IVA embutido
        salePriceIncVAT = modeValue;

    } else if (mode === 'targetProfit') {
        // profit = grossMargin / (1 + vatRate)  →  grossMargin = profit × (1 + vatRate)
        // salePriceIncVAT = totalCost + grossMargin
        salePriceIncVAT = totalCost + modeValue * (1 + vatRate);

    } else { // targetMargin
        // margin% = grossMargin / salePriceIncVAT  →  salePriceIncVAT = totalCost / (1 − margin)
        const m = modeValue / 100;
        if (m >= 1) throw new Error('A margem não pode ser ≥ 100%.');
        salePriceIncVAT = totalCost / (1 - m);
    }

    const grossMargin = salePriceIncVAT - totalCost;               // Margem bruta (antes de IVA)
    const vatAmount   = grossMargin * vatRate / (1 + vatRate);     // IVA entregue ao Estado
    const profit      = grossMargin - vatAmount;                   // Lucro líquido após IVA
    const markup      = totalCost > 0 ? (profit / totalCost) * 100 : 0;
    const margin      = salePriceIncVAT > 0 ? (grossMargin / salePriceIncVAT) * 100 : 0;

    // No regime de margem, o preço ex-IVA não é discriminado em factura — indicamos null
    return { totalCost, salePriceExVAT: null, salePriceIncVAT, vatAmount, profit, markup, margin, vatBase: 'margem' };
}

// ── Validação de inputs ───────────────────────────────────────

/**
 * Valida os inputs antes de calcular.
 * Lança um Error com mensagem legível em caso de valor inválido.
 */
function validateInputs(purchasePrice, expensesTotal, expensesVAT, vatRate, mode, modeValue) {
    if (isNaN(purchasePrice) || purchasePrice <= 0)
        throw new Error('O preço de compra deve ser um valor positivo.');
    if (isNaN(expensesTotal) || expensesTotal < 0)
        throw new Error('As despesas totais não podem ser negativas.');
    if (isNaN(expensesVAT) || expensesVAT < 0)
        throw new Error('O IVA das despesas não pode ser negativo.');
    if (expensesVAT > expensesTotal)
        throw new Error('O IVA das despesas não pode exceder as despesas totais.');
    if (isNaN(vatRate) || vatRate < 0 || vatRate > 100)
        throw new Error('A taxa de IVA deve estar entre 0 e 100.');
    if (isNaN(modeValue) || modeValue < 0)
        throw new Error('O valor introduzido para o modo de cálculo é inválido.');
    if (mode === 'targetMargin' && modeValue >= 100)
        throw new Error('A margem desejada deve ser inferior a 100%.');
}

// ── Controlador principal ─────────────────────────────────────

function runCalculation() {
    clearError();

    const regime = document.querySelector('input[name="regime"]:checked').value;
    const mode   = document.querySelector('input[name="calcMode"]:checked').value;

    const purchasePrice = parseFloat(document.getElementById('purchasePrice').value)  || 0;
    const expensesTotal = parseFloat(document.getElementById('expensesTotal').value)  || 0;
    const expensesVAT   = parseFloat(document.getElementById('expensesVAT').value)    || 0;
    const vatRate       = parseFloat(document.getElementById('vatRate').value)        || 23;

    // Valor variável conforme o modo de cálculo escolhido
    const modeInputMap = { givenSalePrice: 'givenSalePrice', targetProfit: 'targetProfit', targetMargin: 'targetMargin' };
    const modeValue = parseFloat(document.getElementById(modeInputMap[mode]).value) || 0;

    const vatRateDecimal = vatRate / 100;

    try {
        validateInputs(purchasePrice, expensesTotal, expensesVAT, vatRate, mode, modeValue);

        const result = regime === 'normal'
            ? calcNormalRegime(purchasePrice, expensesTotal, expensesVAT, vatRateDecimal, mode, modeValue)
            : calcMargemRegime(purchasePrice, expensesTotal, vatRateDecimal, mode, modeValue);

        renderResults(result, regime, mode, purchasePrice, expensesTotal, expensesVAT);
    } catch (err) {
        showError(err.message);
    }
}

// ── Renderização dos resultados ───────────────────────────────

function renderResults(r, regime, mode, purchasePrice, expensesTotal, expensesVAT) {
    // Badges de contexto
    const regimeBadge = document.getElementById('regimeBadge');
    regimeBadge.textContent = regime === 'normal' ? 'Regime Normal' : 'Regime de Margem';
    regimeBadge.style.backgroundColor = regime === 'normal' ? '#667eea' : '#f59e0b';

    const modeLabels = {
        givenSalePrice : 'Preço de Venda',
        targetProfit   : 'Lucro Desejado',
        targetMargin   : 'Margem Desejada',
    };
    document.getElementById('modeBadge').textContent = modeLabels[mode];

    // Secção Custo
    document.getElementById('res-purchasePrice').textContent = fmtCurrency(purchasePrice);
    document.getElementById('res-expensesTotal').textContent = fmtCurrency(expensesTotal);

    const expVATRow = document.getElementById('res-expensesVATRow');
    if (regime === 'normal') {
        document.getElementById('res-expensesVAT').textContent = '- ' + fmtCurrency(expensesVAT);
        expVATRow.style.display = '';
    } else {
        expVATRow.style.display = 'none';
    }
    document.getElementById('res-totalCost').textContent = fmtCurrency(r.totalCost);

    // Secção Venda
    const exVATRow = document.getElementById('res-salePriceExVATRow');
    if (r.salePriceExVAT !== null) {
        document.getElementById('res-salePriceExVAT').textContent = fmtCurrency(r.salePriceExVAT);
        exVATRow.style.display = '';
    } else {
        exVATRow.style.display = 'none';
    }
    document.getElementById('res-vatBase').textContent = r.vatBase;
    document.getElementById('res-vatAmount').textContent    = fmtCurrency(r.vatAmount);
    document.getElementById('res-salePriceIncVAT').textContent = fmtCurrency(r.salePriceIncVAT);

    // Lucro & métricas
    document.getElementById('res-profit').textContent = fmtCurrency(r.profit);
    document.getElementById('res-markup').textContent = fmtPercent(r.markup);
    document.getElementById('res-margin').textContent = fmtPercent(r.margin);

    // Cor do profit box
    const profitBox = document.getElementById('profitBox');
    profitBox.className = 'profit-box text-center rounded p-4';
    if (r.profit > 0)      profitBox.classList.add('positive');
    else if (r.profit < 0) profitBox.classList.add('negative');

    document.getElementById('resultsContainer').style.display = 'block';
    document.getElementById('emptyState').style.display = 'none';
}

// ── Utilitários de UI ─────────────────────────────────────────

function showError(msg) {
    let el = document.getElementById('calcError');
    if (!el) {
        el = document.createElement('div');
        el.id = 'calcError';
        el.className = 'alert alert-danger mt-3';
        document.getElementById('profitForm').appendChild(el);
    }
    el.textContent = msg;
    el.style.display = 'block';
}

function clearError() {
    const el = document.getElementById('calcError');
    if (el) el.style.display = 'none';
}

/** Mostra apenas o input do modo de cálculo activo. */
function updateModeInputVisibility(mode) {
    document.getElementById('inputGivenSalePrice').style.display = mode === 'givenSalePrice' ? '' : 'none';
    document.getElementById('inputTargetProfit').style.display   = mode === 'targetProfit'   ? '' : 'none';
    document.getElementById('inputTargetMargin').style.display   = mode === 'targetMargin'   ? '' : 'none';
}

/** Ajusta a UI consoante o regime fiscal seleccionado. */
function updateRegimeUI(regime) {
    // O campo de IVA das despesas só é relevante no regime normal
    const expVATRow = document.getElementById('expensesVATRow');
    if (regime === 'normal') {
        expVATRow.classList.remove('field-disabled');
    } else {
        expVATRow.classList.add('field-disabled');
    }
}

function resetForm() {
    document.getElementById('profitForm').reset();
    clearError();
    document.getElementById('resultsContainer').style.display = 'none';
    document.getElementById('emptyState').style.display = 'block';
    updateModeInputVisibility('givenSalePrice');
    updateRegimeUI('normal');
}

// ── Event Listeners ───────────────────────────────────────────

document.getElementById('profitForm').addEventListener('submit', function (e) {
    e.preventDefault();
    runCalculation();
});

document.getElementById('resetBtn').addEventListener('click', resetForm);

document.querySelectorAll('input[name="calcMode"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        updateModeInputVisibility(this.value);
    });
});

document.querySelectorAll('input[name="regime"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        updateRegimeUI(this.value);
    });
});

// Estado inicial
updateRegimeUI('normal');
updateModeInputVisibility('givenSalePrice');
</script>
@endsection
