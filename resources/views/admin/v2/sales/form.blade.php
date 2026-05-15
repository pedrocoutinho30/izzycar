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
            @if(isset($sale))
            @php
                $purchaseType  = $sale->vehicle->purchase_type ?? "—";
                $purchasePrice = $sale->vehicle->purchase_price ?? 0;
                $salePrice     = $sale->sale_price ?? 0;
                $totalExp      = $sale->totalExpenses ?? 0;
                $netMargin     = $sale->net_margin ?? 0;
                $grossMargin   = $sale->gross_margin ?? 0;
                $vatPaid       = $sale->vat_paid ?? 0;
                $vatSettle     = $sale->vat_settle_sale ?? 0;
                $vatDeducible  = $sale->vat_deducible_purchase ?? 0;
                $isProfit      = $netMargin >= 0;
                $typeLabel = match($purchaseType) {
                    "Geral"   => "Regime Geral — preço de compra sem IVA",
                    "Margem"  => "Regime de Margem — IVA só sobre a margem",
                    "Sem Iva" => "Sem IVA — compra isenta, venda c/ IVA 23%",
                    default   => $purchaseType,
                };
            @endphp
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-graph-up-arrow"></i>
                        Lucro / Prejuízo Real
                    </h5>
                    <span class="badge bg-secondary small">{{ $typeLabel }}</span>
                </div>
                <div class="modern-card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted small mb-1">Preço de Compra</div>
                                <div class="fw-bold fs-5">{{ number_format($purchasePrice, 2, ",", ".") }} €</div>
                                @if($purchaseType === "Geral")
                                    <div class="text-muted" style="font-size:.75rem">líquido → bruto: {{ number_format($purchasePrice * 1.23, 2, ",", ".") }} €</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted small mb-1">Total Despesas</div>
                                <div class="fw-bold fs-5">{{ number_format($totalExp, 2, ",", ".") }} €</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted small mb-1">Preço de Venda</div>
                                <div class="fw-bold fs-5">{{ number_format($salePrice, 2, ",", ".") }} €</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded p-3 text-center {{ $isProfit ? 'border-success bg-success bg-opacity-10' : 'border-danger bg-danger bg-opacity-10' }}">
                                <div class="small mb-1 {{ $isProfit ? 'text-success' : 'text-danger' }}">
                                    <i class="bi bi-{{ $isProfit ? 'arrow-up-circle-fill' : 'arrow-down-circle-fill' }} me-1"></i>
                                    Lucro Líquido Real
                                </div>
                                <div class="fw-bold fs-4 {{ $isProfit ? 'text-success' : 'text-danger' }}">
                                    {{ $isProfit ? '+' : '' }}{{ number_format($netMargin, 2, ',', '.') }} €
                                </div>
                                <div class="small {{ $isProfit ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($sale->net_profitability ?? 0, 1, ',', '.') }}% da venda
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th colspan="2" class="text-center text-muted small text-uppercase">IVA</th>
                                    <th colspan="2" class="text-center text-muted small text-uppercase">Margens</th>
                                    <th colspan="2" class="text-center text-muted small text-uppercase">Rentabilidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>IVA Liquidado (venda)</th>
                                    <td>{{ number_format($vatSettle, 2, ",", ".") }} €</td>
                                    <th>Margem Bruta</th>
                                    <td class="{{ $grossMargin >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($grossMargin, 2, ",", ".") }} €</td>
                                    <th>Rentab. Bruta</th>
                                    <td>{{ number_format($sale->gross_profitability ?? 0, 2, ",", ".") }} %</td>
                                </tr>
                                <tr>
                                    <th>IVA Dedutível (compra)</th>
                                    <td>{{ number_format($vatDeducible, 2, ",", ".") }} €</td>
                                    <th>Margem Líquida</th>
                                    <td class="{{ $netMargin >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netMargin, 2, ",", ".") }} €</td>
                                    <th>Rentab. Líquida</th>
                                    <td>{{ number_format($sale->net_profitability ?? 0, 2, ",", ".") }} %</td>
                                </tr>
                                <tr>
                                    <th>IVA a Pagar ao Estado</th>
                                    <td class="fw-semibold text-danger">{{ number_format($vatPaid, 2, ",", ".") }} €</td>
                                    <th>Custo Total</th>
                                    <td>{{ number_format($sale->totalCost ?? 0, 2, ",", ".") }} €</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @if($purchaseType === "Margem")
                    <div class="alert alert-info mt-3 mb-0 py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Regime Margem:</strong> O IVA incide apenas sobre a margem (venda − compra). O preço de compra é bruto, sem recuperação de IVA.
                    </div>
                    @elseif($purchaseType === "Geral")
                    <div class="alert alert-info mt-3 mb-0 py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Regime Geral:</strong> O IVA da compra ({{ number_format($vatDeducible, 2, ",", ".") }} €) é dedutível. O lucro líquido representa o ganho real após recuperação/pagamento de IVA.
                    </div>
                    @elseif($purchaseType === "Sem Iva")
                    <div class="alert alert-info mt-3 mb-0 py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Sem IVA:</strong> A compra não teve IVA — todo o IVA cobrado na venda ({{ number_format($vatSettle, 2, ",", ".") }} €) é entregue ao Estado.
                    </div>
                    @endif
                </div>
            </div>
            @endif
                </div>
            </div>
        </div>

        <!-- Coluna Secundária (Direita) -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 80px;">
            @include('components.admin.action-card', [
                'cancelButtonHref' => route('admin.v2.sales.index'),
                'submitButtonLabel' => isset($sale) ? 'Atualizar Venda' : 'Criar Venda',
                'timestamps' => isset($sale) ? [
                    'created_at' => $sale->created_at,
                    'updated_at' => $sale->updated_at,
                ] : null,
            ])
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