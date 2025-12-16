@extends('layouts.admin-v2')

@section('title', isset($sale) ? 'Editar Venda' : 'Nova Venda')

@section('content')
<div class="admin-content">
    <!-- HEADER -->
    <div class="content-header">
        <div>
            <h1 class="content-title">{{ isset($sale) ? 'Editar Venda' : 'Nova Venda' }}</h1>
            <p class="content-subtitle">Registe os dados da venda</p>
        </div>
        <div class="content-actions">
            <a href="{{ route('admin.v2.sales.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- FORMULÁRIO -->
    <form action="{{ isset($sale) ? route('admin.v2.sales.update', $sale->id) : route('admin.v2.sales.store') }}" 
          method="POST" class="form-card">
        @csrf
        @if(isset($sale))
            @method('PUT')
        @endif

        <!-- Dados Principais -->
        <div class="form-section">
            <div class="form-section-header">
                <h3 class="form-section-title">
                    <i class="bi bi-info-circle"></i> Dados Principais
                </h3>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">Veículo</label>
                    <select name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror" required>
                        <option value="">Selecione o veículo...</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $sale->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->reference }} - {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->year }})
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Cliente</label>
                    <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                        <option value="">Selecione o cliente...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', $sale->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }} {{ $client->vat_number ? '- NIF: ' . $client->vat_number : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Data da Venda</label>
                    <input type="date" name="sale_date" class="form-control @error('sale_date') is-invalid @enderror" 
                           value="{{ old('sale_date', $sale->sale_date ?? date('Y-m-d')) }}" required>
                    @error('sale_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Preço de Venda (€)</label>
                    <input type="number" name="sale_price" class="form-control @error('sale_price') is-invalid @enderror" 
                           value="{{ old('sale_price', $sale->sale_price ?? '') }}" step="0.01" min="0" required>
                    @error('sale_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Método de Pagamento</label>
                    <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                        <option value="">Selecione...</option>
                        <option value="Transferência" {{ old('payment_method', $sale->payment_method ?? '') === 'Transferência' ? 'selected' : '' }}>Transferência</option>
                        <option value="Numerário" {{ old('payment_method', $sale->payment_method ?? '') === 'Numerário' ? 'selected' : '' }}>Numerário</option>
                        <option value="Cheque" {{ old('payment_method', $sale->payment_method ?? '') === 'Cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="Financiamento" {{ old('payment_method', $sale->payment_method ?? '') === 'Financiamento' ? 'selected' : '' }}>Financiamento</option>
                        <option value="Outro" {{ old('payment_method', $sale->payment_method ?? '') === 'Outro' ? 'selected' : '' }}>Outro</option>
                    </select>
                    @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observation" class="form-control @error('observation') is-invalid @enderror" 
                              rows="3">{{ old('observation', $sale->observation ?? '') }}</textarea>
                    @error('observation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Margens e Rentabilidade (Opcional) -->
        <div class="form-section">
            <div class="form-section-header">
                <h3 class="form-section-title">
                    <i class="bi bi-graph-up"></i> Margens e Rentabilidade (Opcional)
                </h3>
                <p class="text-muted small mb-0">Estes campos são opcionais e podem ser calculados automaticamente pelo sistema</p>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Margem Bruta (%)</label>
                    <input type="number" name="gross_margin" class="form-control @error('gross_margin') is-invalid @enderror" 
                           value="{{ old('gross_margin', $sale->gross_margin ?? '') }}" step="0.01">
                    @error('gross_margin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Margem Líquida (%)</label>
                    <input type="number" name="net_margin" class="form-control @error('net_margin') is-invalid @enderror" 
                           value="{{ old('net_margin', $sale->net_margin ?? '') }}" step="0.01">
                    @error('net_margin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Taxa IVA (%)</label>
                    <input type="number" name="vat_rate" class="form-control @error('vat_rate') is-invalid @enderror" 
                           value="{{ old('vat_rate', $sale->vat_rate ?? 23) }}" step="0.01" min="0" max="100">
                    @error('vat_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">IVA Pago (€)</label>
                    <input type="number" name="vat_paid" class="form-control @error('vat_paid') is-invalid @enderror" 
                           value="{{ old('vat_paid', $sale->vat_paid ?? '') }}" step="0.01">
                    @error('vat_paid')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">IVA Dedutível Compra (€)</label>
                    <input type="number" name="vat_deducible_purchase" class="form-control @error('vat_deducible_purchase') is-invalid @enderror" 
                           value="{{ old('vat_deducible_purchase', $sale->vat_deducible_purchase ?? '') }}" step="0.01">
                    @error('vat_deducible_purchase')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">IVA Liquidação Venda (€)</label>
                    <input type="number" name="vat_settle_sale" class="form-control @error('vat_settle_sale') is-invalid @enderror" 
                           value="{{ old('vat_settle_sale', $sale->vat_settle_sale ?? '') }}" step="0.01">
                    @error('vat_settle_sale')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Custo Total (€)</label>
                    <input type="number" name="totalCost" class="form-control @error('totalCost') is-invalid @enderror" 
                           value="{{ old('totalCost', $sale->totalCost ?? '') }}" step="0.01">
                    @error('totalCost')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Total Despesas (€)</label>
                    <input type="number" name="totalExpenses" class="form-control @error('totalExpenses') is-invalid @enderror" 
                           value="{{ old('totalExpenses', $sale->totalExpenses ?? '') }}" step="0.01">
                    @error('totalExpenses')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Rentabilidade Líquida (%)</label>
                    <input type="number" name="net_profitability" class="form-control @error('net_profitability') is-invalid @enderror" 
                           value="{{ old('net_profitability', $sale->net_profitability ?? '') }}" step="0.01">
                    @error('net_profitability')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Rentabilidade Bruta (%)</label>
                    <input type="number" name="gross_profitability" class="form-control @error('gross_profitability') is-invalid @enderror" 
                           value="{{ old('gross_profitability', $sale->gross_profitability ?? '') }}" step="0.01">
                    @error('gross_profitability')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- BOTÕES DE AÇÃO -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-check-lg"></i> {{ isset($sale) ? 'Atualizar Venda' : 'Registar Venda' }}
            </button>
            <a href="{{ route('admin.v2.sales.index') }}" class="btn btn-outline-secondary btn-lg">
                Cancelar
            </a>
        </div>
    </form>
</div>

<style>
.form-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 0;
    overflow: hidden;
}

.form-section {
    padding: 2rem;
    border-bottom: 1px solid #eee;
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section-header {
    margin-bottom: 1.5rem;
}

.form-section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section-title i {
    color: var(--admin-primary);
}

.form-label.required::after {
    content: ' *';
    color: #dc3545;
}

.form-actions {
    padding: 2rem;
    background: #f8f9fa;
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
}

@media (max-width: 768px) {
    .form-section {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>
@endsection
