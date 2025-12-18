@extends('layouts.admin-v2')

@section('title', isset($sale) ? 'Editar Venda' : 'Nova Venda')

@section('content')
<!-- Page Header -->
@php
$existAction = isset($sale) ? 'Editar' : 'Criar';
@endphp
<!-- Page Header -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-file-earmark-text', 'label' => 'Vendas', 'href' => route('admin.v2.sales.index')],
['icon' => '', 'label' => $existAction]
],
'title' => $existAction . ' Venda',
'subtitle' => "",
'actionHref' => "",
'actionLabel' => ''
])




<!-- FORMULÁRIO -->
<form action="{{ isset($sale) ? route('admin.v2.sales.update', $sale->id) : route('admin.v2.sales.store') }}"
    method="POST">
    @csrf
    @if(isset($sale))
    @method('PUT')
    @endif

    <!-- Dados Principais -->
    <div class="row g-4">
        <!-- Coluna Principal (Esquerda) -->
        <div class="col-lg-8">

            {{-- SECÇÃO 1: Informações Básicas --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-info-circle"></i>
                        Dados da Venda
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
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

                    <div class="col-md-4">
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
                        <label class="form-label required">Taxa de Iva</label>
                        <select name="vat_rate" class="form-select @error('vat_rate') is-invalid @enderror">
                            <option value="">Selecione...</option>
                            <option value="Sem IVA" {{ isset($sale) && $sale->vat_rate == 'Sem IVA' ? 'selected' : '' }}>Sem IVA</option>
                            <option value="23" {{ isset($sale) && $sale->vat_rate == '23' ? 'selected' : '' }}>23%</option>
                        </select>
                        @error('vat_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Método de Pagamento</label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                            <option value="">Selecione...</option>
                            <option value="Financiamento" {{ isset($sale) && $sale->payment_method == 'Financiamento' ? 'selected' : '' }}>Financiamento</option>
                            <option value="Pronto pagamento" {{ isset($sale) && $sale->payment_method == 'Pronto pagamento' ? 'selected' : '' }}>Pronto Pagamento</option>
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

            <!-- Margens e Rentabilidade (só mostra na edição) -->
            @if(isset($sale) )
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-car-front"></i>
                        Margens e Rentabilidade
                    </h5>
                </div>

                <div class="modern-card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th>Margem Bruta (€)</th>
                                    <td>{{ number_format($sale->gross_margin ?? 0, 2) }}</td>
                                    <th>Margem Líquida (€)</th>
                                    <td>{{ number_format($sale->net_margin ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Taxa IVA (%)</th>
                                    <td>{{ $sale->vat_rate_margem ?? '-' }}</td>
                                    <th>IVA Pago (€)</th>
                                    <td>{{ number_format($sale->vat_paid ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>IVA Dedutível Compra (€)</th>
                                    <td>{{ number_format($sale->vat_deducible_purchase ?? 0, 2) }}</td>
                                    <th>IVA Liquidação Venda (€)</th>
                                    <td>{{ number_format($sale->vat_settle_sale ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Custo Total (€)</th>
                                    <td>{{ number_format($sale->totalCost ?? 0, 2) }}</td>
                                    <th>Total Despesas (€)</th>
                                    <td>{{ number_format($sale->totalExpenses ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Rentabilidade Bruta (%)</th>
                                    <td>{{ number_format($sale->gross_profitability ?? 0, 2) }}</td>
                                    <th>Rentabilidade Líquida (%)</th>
                                    <td>{{ number_format($sale->net_profitability ?? 0, 2) }}</td>

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
             @endif
        </div>
        <!-- Coluna Secundária (Direita) -->
        <div class="col-lg-4">
            <!-- BOTÕES DE AÇÃO -->
            {{-- SECÇÃO: Ações --}}
            @include('components.admin.action-card', [
            'cancelButtonHref' => route('admin.v2.sales.index'),
            'submitButtonLabel' => isset($sale) ? 'Atualizar Venda' : 'Registar Venda',
            'timestamps' => isset($sale) ? [
            'created_at' => $sale->created_at,
            'updated_at' => $sale->updated_at
            ] : null
            ])
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-car-front"></i>
                        Retoma
                    </h5>
                </div>
                <div class="modern-card-body">
                    <div class="col-md-12">

                        <label class="form-label">Tem retoma?</label>
                        <select name="has_trade_in" id="has_trade_in" class="form-select @error('has_trade_in') is-invalid @enderror">
                            <option value="0" {{ old('has_trade_in', $sale->has_trade_in ?? 0) == 0 ? 'selected' : '' }}>Não</option>
                            <option value="1" {{ old('has_trade_in', $sale->has_trade_in ?? 0) == 1 ? 'selected' : '' }}>Sim</option>
                        </select>
                    </div>

                    <div id="trade_in_vehicle_container" style="display: {{ old('has_trade_in', $sale->has_trade_in ?? 0) == 1 ? 'block' : 'none' }}; margin-top: 1rem;">
                        <div class="col-md-12">
                            <label class="form-label">Selecione o veículo de retoma</label>
                            <select name="trade_in_vehicle_id" class="form-select @error('trade_in_vehicle_id') is-invalid @enderror">
                                <option value="">Selecione o veículo...</option>
                                @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('trade_in_vehicle_id', $sale->trade_in_vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->reference }} - {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->year }})
                                </option>
                                @endforeach
                            </select>
                            @error('trade_in_vehicle_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Valor da retoma</label>
                            <input type="text" name="trade_in_value" class="form-control @error('trade_in_value') is-invalid @enderror" value="{{ old('trade_in_value', $sale->trade_in_value ?? '') }}">
                            @error('trade_in_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @push('scripts')
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const hasTradeIn = document.getElementById('has_trade_in');
                            const tradeInContainer = document.getElementById('trade_in_vehicle_container');
                            hasTradeIn.addEventListener('change', function() {
                                if (this.value == '1') {
                                    tradeInContainer.style.display = 'block';
                                } else {
                                    tradeInContainer.style.display = 'none';
                                }
                            });
                        });
                    </script>
                    @endpush
                    @error('has_trade_in')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-car-front"></i>
                        Despesas Associadas
                    </h5>
                </div>
                <div class="modern-card-body">
                    @if(isset($expenses) && $expenses->count() > 0)
                    <ul class="list-group">
                        @foreach($expenses as $expense)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $expense->title }}
                            <span>€{{ number_format($expense->amount, 2) }}</span>
                        </li>
                        @endforeach
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Total Despesas</strong>
                            <span>€{{ number_format($expenses->sum('amount'), 2) }}</span>
                        </li>
                    </ul>
                    @else
                    <p class="text-muted">Sem despesas registradas para este veículo.</p>
                    @endif
                </div>
            </div>
        </div>

</form>

<style>
    .form-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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