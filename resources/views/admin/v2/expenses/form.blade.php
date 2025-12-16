@extends('layouts.admin-v2')

@section('title', isset($expense) ? 'Editar Despesa' : 'Nova Despesa')

@section('content')
<!-- HEADER -->
@php
$existAction = isset($expense) ? 'Editar' : 'Criar';
@endphp
<!-- Page Header -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => ''],
['icon' => 'bi bi-people', 'label' => 'Despesas', 'href' => route('admin.v2.expenses.index')],
['icon' => '', 'label' => $existAction]
],
'title' => $existAction . ' Despesa',
'subtitle' => "",
'actionHref' => "",
'actionLabel' => ''
])

<!-- FORMULÁRIO -->
<form action="{{ isset($expense) ? route('admin.v2.expenses.update', $expense->id) : route('admin.v2.expenses.store') }}"
    method="POST">
    @csrf
    @if(isset($expense))
    @method('PUT')
    @endif
    <div class="row g-4">
        <!-- Coluna Principal (Esquerda) -->
        <div class="col-lg-8">
            <!-- Dados Principais -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-info-circle"></i>
                        Informação da Despesa
                    </h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Título</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title', $expense->title ?? '') }}" required>
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tipo de Despesa</label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror">
                            <option value="">Selecione...</option>
                            @php
                            $options = [
                            'Documentação' => 'Documentação',
                            'IMT' => 'IMT',
                            'Inspeção Técnica' => 'Inspeção Técnica',
                            'IPO' => 'IPO',
                            'ISV' => 'ISV',
                            'IUC' => 'IUC',
                            'Manutenção' => 'Manutenção',
                            'Matrículas' => 'Matrículas',
                            'Peças' => 'Peças',
                            'Reboque' => 'Reboque',
                            'Registo automóvel' => 'Registo automóvel',
                            'Reparação' => 'Reparação',
                            'Seguro' => 'Seguro',
                            'Transporte' => 'Transporte',
                            'Outros' => 'Outros',

                            ];
                            @endphp
                            @foreach($options as $value => $label)
                            <option value="{{ $value }}" {{ old('type', $expense->type ?? '') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                            <!-- <option value="Reparação" {{ old('type', $expense->type ?? '') === 'Reparação' ? 'selected' : '' }}>Reparação</option>
                        <option value="Inspeção" {{ old('type', $expense->type ?? '') === 'Inspeção' ? 'selected' : '' }}>Inspeção</option>
                        <option value="Seguro" {{ old('type', $expense->type ?? '') === 'Seguro' ? 'selected' : '' }}>Seguro</option>
                        <option value="Transporte" {{ old('type', $expense->type ?? '') === 'Transporte' ? 'selected' : '' }}>Transporte</option>
                        <option value="Documentação" {{ old('type', $expense->type ?? '') === 'Documentação' ? 'selected' : '' }}>Documentação</option>
                        <option value="Outro" {{ old('type', $expense->type ?? '') === 'Outro' ? 'selected' : '' }}>Outro</option> -->
                        </select>
                        @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Veículo (Opcional)</label>
                        <select name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror">
                            <option value="">Sem veículo associado</option>
                            @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $expense->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->reference }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                            </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Parceiro</label>
                        <select name="partner_id" class="form-select @error('partner_id') is-invalid @enderror">
                            <option value="">Sem parceiro</option>
                            @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('partner_id', $expense->partner_id ?? '') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('partner_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Montante (€)</label>
                        <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount', $expense->amount ?? '') }}" step="0.01" min="0" required>
                        @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Taxa IVA (%)</label>
                        <input type="number" name="vat_rate" class="form-control @error('vat_rate') is-invalid @enderror"
                            value="{{ old('vat_rate', $expense->vat_rate ?? 23) }}" step="0.01" min="0" max="100">
                        @error('vat_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Data da Despesa</label>
                        <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror"
                            value="{{ old('expense_date', $expense->expense_date ?? date('Y-m-d')) }}" required>
                        @error('expense_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Observações</label>
                        <textarea name="observations" class="form-control @error('observations') is-invalid @enderror"
                            rows="4">{{ old('observations', $expense->observations ?? '') }}</textarea>
                        @error('observations')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <!-- Coluna Secundária (Direita) -->
        <div class="col-lg-4">
            <!-- BOTÕES DE AÇÃO -->
            @include('components.admin.action-card', [
            'cancelButtonHref' => route('admin.v2.expenses.index'),
            'submitButtonLabel' => isset($expense) ? 'Atualizar Despesa' : 'Criar Despesa',
            'timestamps' => isset($expense) ? [
            'created_at' => $expense->created_at,
            'updated_at' => $expense->updated_at
            ] : null
            ])
        </div>
    </div>

    
</form>

@endsection
@push('styles')
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
@endpush