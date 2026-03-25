@extends('layouts.admin-v2')

@section('title', 'Calculadora de Lucro')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-calculator"></i> Calculadora de Lucro de Veículos
            </h1>
            <p class="text-muted mb-0">Calcule o lucro considerando IVA e despesas</p>
        </div>
    </div>

    <div class="row">
        <!-- Formulário de Entrada -->
        <div class="col-lg-6">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-pencil-square"></i>
                        Dados da Operação
                    </h5>
                </div>
                <div class="modern-card-body">
                    <form id="profitForm">
                        <!-- Compra -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-cart-fill"></i> Compra
                            </h6>
                            
                            <div class="mb-3">
                                <label for="purchasePrice" class="form-label">Valor de Compra (com IVA)</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control" id="purchasePrice" 
                                           placeholder="0.00" step="0.01" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="purchaseVat" class="form-label">IVA da Compra</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="purchaseVat" 
                                           value="23" step="0.1" min="0" max="100" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Despesas -->
                        <div class="mb-4">
                            <h6 class="text-warning mb-3">
                                <i class="bi bi-wallet2"></i> Despesas
                            </h6>
                            
                            <div class="mb-3">
                                <label for="expenses" class="form-label">Despesas Adicionais (sem IVA)</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control" id="expenses" 
                                           placeholder="0.00" step="0.01" value="0" required>
                                </div>
                                <small class="text-muted">Transporte, reparações, documentação, etc.</small>
                            </div>
                        </div>

                        <!-- Venda -->
                        <div class="mb-4">
                            <h6 class="text-success mb-3">
                                <i class="bi bi-cash-coin"></i> Venda
                            </h6>
                            
                            <div class="mb-3">
                                <label for="salePrice" class="form-label">Valor de Venda (com IVA)</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control" id="salePrice" 
                                           placeholder="0.00" step="0.01" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="saleVat" class="form-label">IVA da Venda</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="saleVat" 
                                           value="23" step="0.1" min="0" max="100" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-calculator"></i> Calcular Lucro
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="bi bi-arrow-counterclockwise"></i> Limpar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Resultados -->
        <div class="col-lg-6">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-graph-up"></i>
                        Resultados
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div id="resultsContainer" style="display: none;">
                        <!-- Detalhes da Compra -->
                        <div class="result-section mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-cart-fill"></i> Análise da Compra
                            </h6>
                            <div class="result-item">
                                <span class="result-label">Preço Líquido (sem IVA):</span>
                                <span class="result-value" id="netPurchase">€ 0.00</span>
                            </div>
                            <div class="result-item">
                                <span class="result-label">IVA Pago:</span>
                                <span class="result-value text-muted" id="vatPurchase">€ 0.00</span>
                            </div>
                            <div class="result-item">
                                <span class="result-label">Despesas Adicionais:</span>
                                <span class="result-value text-warning" id="totalExpenses">€ 0.00</span>
                            </div>
                            <div class="result-item border-top pt-2 mt-2">
                                <span class="result-label fw-bold">Custo Total:</span>
                                <span class="result-value fw-bold text-danger" id="totalCost">€ 0.00</span>
                            </div>
                        </div>

                        <!-- Detalhes da Venda -->
                        <div class="result-section mb-4">
                            <h6 class="text-success mb-3">
                                <i class="bi bi-cash-coin"></i> Análise da Venda
                            </h6>
                            <div class="result-item">
                                <span class="result-label">Preço Líquido (sem IVA):</span>
                                <span class="result-value" id="netSale">€ 0.00</span>
                            </div>
                            <div class="result-item">
                                <span class="result-label">IVA a Liquidar:</span>
                                <span class="result-value text-muted" id="vatSale">€ 0.00</span>
                            </div>
                        </div>

                        <!-- Lucro Final -->
                        <div class="result-section">
                            <div class="profit-box p-4 text-center rounded" id="profitBox">
                                <h6 class="text-uppercase mb-2">Lucro Final</h6>
                                <h2 class="mb-2" id="finalProfit">€ 0.00</h2>
                                <div class="profit-percentage badge" id="profitPercentage">0%</div>
                            </div>
                        </div>
                    </div>

                    <div id="emptyState" class="text-center py-5">
                        <i class="bi bi-calculator" style="font-size: 4rem; color: #ddd;"></i>
                        <p class="text-muted mt-3">Preencha os dados e clique em "Calcular" para ver os resultados</p>
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

    .result-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
    }

    .result-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
    }

    .result-label {
        color: #6c757d;
    }

    .result-value {
        font-weight: 600;
        color: #2c3e50;
    }

    .profit-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .profit-box.positive {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        box-shadow: 0 4px 15px rgba(56, 239, 125, 0.4);
    }

    .profit-box.negative {
        background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
        box-shadow: 0 4px 15px rgba(238, 9, 121, 0.4);
    }

    .profit-percentage {
        font-size: 1.1rem;
        padding: 0.5rem 1rem;
        background: rgba(255,255,255,0.2);
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-color: #ced4da;
        font-weight: 600;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
</style>

<script>
document.getElementById('profitForm').addEventListener('submit', function(e) {
    e.preventDefault();
    calculateProfit();
});

function calculateProfit() {
    // Obter valores
    const purchasePrice = parseFloat(document.getElementById('purchasePrice').value) || 0;
    const purchaseVat = parseFloat(document.getElementById('purchaseVat').value) || 0;
    const expenses = parseFloat(document.getElementById('expenses').value) || 0;
    const salePrice = parseFloat(document.getElementById('salePrice').value) || 0;
    const saleVat = parseFloat(document.getElementById('saleVat').value) || 0;

    // Cálculos
    const netPurchase = purchasePrice / (1 + (purchaseVat / 100));
    const vatPurchase = purchasePrice - netPurchase;
    const totalCost = netPurchase + expenses;
    
    const netSale = salePrice / (1 + (saleVat / 100));
    const vatSale = salePrice - netSale;
    
    const finalProfit = netSale - totalCost;
    const profitPercentage = totalCost > 0 ? ((finalProfit / totalCost) * 100) : 0;

    // Mostrar resultados
    document.getElementById('netPurchase').textContent = formatCurrency(netPurchase);
    document.getElementById('vatPurchase').textContent = formatCurrency(vatPurchase);
    document.getElementById('totalExpenses').textContent = formatCurrency(expenses);
    document.getElementById('totalCost').textContent = formatCurrency(totalCost);
    
    document.getElementById('netSale').textContent = formatCurrency(netSale);
    document.getElementById('vatSale').textContent = formatCurrency(vatSale);
    
    document.getElementById('finalProfit').textContent = formatCurrency(finalProfit);
    document.getElementById('profitPercentage').textContent = profitPercentage.toFixed(2) + '%';

    // Aplicar cor ao box de lucro
    const profitBox = document.getElementById('profitBox');
    profitBox.className = 'profit-box p-4 text-center rounded';
    if (finalProfit > 0) {
        profitBox.classList.add('positive');
    } else if (finalProfit < 0) {
        profitBox.classList.add('negative');
    }

    // Mostrar resultados e esconder empty state
    document.getElementById('resultsContainer').style.display = 'block';
    document.getElementById('emptyState').style.display = 'none';
}

function formatCurrency(value) {
    return '€ ' + value.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function resetForm() {
    document.getElementById('profitForm').reset();
    document.getElementById('resultsContainer').style.display = 'none';
    document.getElementById('emptyState').style.display = 'block';
}
</script>
@endsection
