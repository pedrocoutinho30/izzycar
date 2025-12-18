@extends('layouts.admin-v2')

@section('title', isset($convertedProposal) ? 'Editar Proposta Convertida' : 'Nova Proposta Convertida')

@section('content')

    @php
    $existAction = isset($convertedProposal) ? 'Editar' : 'Criar';
    @endphp
    <!-- Page Header -->
    @include('components.admin.page-header', [
    'breadcrumbs' => [
    ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
    ['icon' => 'bi bi-people', 'label' => 'Propostas Convertidas', 'href' => route('admin.v2.converted-proposals.index')],
    ['icon' => '', 'label' => $existAction]
    ],
    'title' => $existAction . ' Proposta Convertida',
    'subtitle' => "Gestão do processo de importação",
    'actionHref' => "",
    'actionLabel' => ''
    ])
    <!-- FORMULÁRIO -->
    <form action="{{ isset($convertedProposal) ? route('admin.v2.converted-proposals.update', $convertedProposal->id) : route('admin.v2.converted-proposals.store') }}"
        method="POST">
        @csrf
        @if(isset($convertedProposal))
        @method('PUT')
        @endif

        <!-- Dados Principais -->
        <div class="row g-4">
            <!-- Coluna Principal (Esquerda) -->
            <div class="col-lg-8">


                <!-- Dados do Veículo -->
                <div class="modern-card">
                    <div class="modern-card-header">
                        <h5 class="modern-card-title">
                            <i class="bi bi-info-circle"></i>
                            Dados do Veículo
                        </h5>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Marca</label>
                            <input type="text" name="brand" class="form-control @error('brand') is-invalid @enderror"
                                value="{{ old('brand', $convertedProposal->brand ?? '') }}">
                            @error('brand')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Modelo</label>
                            <input type="text" name="modelCar" class="form-control @error('modelCar') is-invalid @enderror"
                                value="{{ old('modelCar', $convertedProposal->modelCar ?? '') }}">
                            @error('modelCar')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Versão</label>
                            <input type="text" name="version" class="form-control @error('version') is-invalid @enderror"
                                value="{{ old('version', $convertedProposal->version ?? '') }}">
                            @error('version')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Ano</label>
                            <input type="number" name="year" class="form-control @error('year') is-invalid @enderror"
                                value="{{ old('year', $convertedProposal->year ?? '') }}" min="1900" max="{{ date('Y') + 1 }}">
                            @error('year')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">KM</label>
                            <input type="number" name="km" class="form-control @error('km') is-invalid @enderror"
                                value="{{ old('km', $convertedProposal->km ?? '') }}" min="0">
                            @error('km')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Matrícula Origem</label>
                            <input type="text" name="matricula_origem" class="form-control @error('matricula_origem') is-invalid @enderror"
                                value="{{ old('matricula_origem', $convertedProposal->matricula_origem ?? '') }}">
                            @error('matricula_origem')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Matrícula Destino</label>
                            <input type="text" name="matricula_destino" class="form-control @error('matricula_destino') is-invalid @enderror"
                                value="{{ old('matricula_destino', $convertedProposal->matricula_destino ?? '') }}">
                            @error('matricula_destino')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Custos de Importação -->

                <div class="modern-card">
                    <div class="modern-card-header">
                        <h5 class="modern-card-title">
                            <i class="bi bi-info-circle"></i>
                            Custos de Importação
                    </div>
                    <div class="row g-3">
                        <!-- Inspeção Origem -->
                        <div class="col-md-6">
                            <label class="form-label">Custo Inspeção Origem (€)</label>
                            <input type="number" name="custo_inspecao_origem" class="form-control"
                                value="{{ old('custo_inspecao_origem', $convertedProposal->custo_inspecao_origem ?? '') }}" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="inspecao_origem_pago" value="1"
                                    {{ old('inspecao_origem_pago', $convertedProposal->inspecao_origem_pago ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label">Inspeção Origem Pago</label>
                            </div>
                        </div>

                        <!-- Transporte -->
                        <div class="col-md-6">
                            <label class="form-label">Custo Transporte (€)</label>
                            <input type="number" name="custo_transporte" class="form-control"
                                value="{{ old('custo_transporte', $convertedProposal->custo_transporte ?? '') }}" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="transporte_pago" value="1"
                                    {{ old('transporte_pago', $convertedProposal->transporte_pago ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label">Transporte Pago</label>
                            </div>
                        </div>

                        <!-- IPO -->
                        <div class="col-md-6">
                            <label class="form-label">Custo IPO (€)</label>
                            <input type="number" name="custo_ipo" class="form-control"
                                value="{{ old('custo_ipo', $convertedProposal->custo_ipo ?? '') }}" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="ipo_pago" value="1"
                                    {{ old('ipo_pago', $convertedProposal->ipo_pago ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label">IPO Pago</label>
                            </div>
                        </div>

                        <!-- ISV -->
                        <div class="col-md-6">
                            <label class="form-label">ISV (€)</label>
                            <input type="number" name="isv" class="form-control"
                                value="{{ old('isv', $convertedProposal->isv ?? '') }}" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="isv_pago" value="1"
                                    {{ old('isv_pago', $convertedProposal->isv_pago ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label">ISV Pago</label>
                            </div>
                        </div>

                        <!-- IMT -->
                        <div class="col-md-6">
                            <label class="form-label">Custo IMT (€)</label>
                            <input type="number" name="custo_imt" class="form-control"
                                value="{{ old('custo_imt', $convertedProposal->custo_imt ?? '') }}" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="imt_pago" value="1"
                                    {{ old('imt_pago', $convertedProposal->imt_pago ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label">IMT Pago</label>
                            </div>
                        </div>

                        <!-- Matrícula -->
                        <div class="col-md-6">
                            <label class="form-label">Custo Matrícula (€)</label>
                            <input type="number" name="custo_matricula" class="form-control"
                                value="{{ old('custo_matricula', $convertedProposal->custo_matricula ?? '') }}" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="matricula_pago_impressa" value="1"
                                    {{ old('matricula_pago_impressa', $convertedProposal->matricula_pago_impressa ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label">Matrícula Paga</label>
                            </div>
                        </div>

                        <!-- Registo Automóvel -->
                        <div class="col-md-6">
                            <label class="form-label">Custo Registo Automóvel (€)</label>
                            <input type="number" name="custo_registo_automovel" class="form-control"
                                value="{{ old('custo_registo_automovel', $convertedProposal->custo_registo_automovel ?? '') }}" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="registo_pago" value="1"
                                    {{ old('registo_pago', $convertedProposal->registo_pago ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label">Registo Pago</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Valores e Pagamentos -->
                <div class="modern-card">
                    <div class="modern-card-header">
                        <h5 class="modern-card-title">
                            <i class="bi bi-info-circle"></i>
                            Valores e Pagamentos
                        </h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Valor do Carro (€)</label>
                            <input type="number" name="valor_carro" class="form-control"
                                value="{{ old('valor_carro', $convertedProposal->valor_carro ?? '') }}" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="carro_pago" value="1"
                                    {{ old('carro_pago', $convertedProposal->carro_pago ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label">Carro Pago</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Valor 1ª Tranche (€)</label>
                            <input type="number" name="valor_primeira_tranche" class="form-control"
                                value="{{ old('valor_primeira_tranche', $convertedProposal->valor_primeira_tranche ?? '') }}" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="primeira_tranche_pago" value="1"
                                    {{ old('primeira_tranche_pago', $convertedProposal->primeira_tranche_pago ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label">1ª Tranche Paga</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Valor 2ª Tranche (€)</label>
                            <input type="number" name="valor_segunda_tranche" class="form-control"
                                value="{{ old('valor_segunda_tranche', $convertedProposal->valor_segunda_tranche ?? '') }}" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="segunda_tranche_pago" value="1"
                                    {{ old('segunda_tranche_pago', $convertedProposal->segunda_tranche_pago ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label">2ª Tranche Paga</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Valor Comissão (€)</label>
                            <input type="number" name="valor_comissao" class="form-control"
                                value="{{ old('valor_comissao', $convertedProposal->valor_comissao ?? '') }}" step="0.01">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Valor Comissão Final (€)</label>
                            <input type="number" name="valor_comissao_final" class="form-control"
                                value="{{ old('valor_comissao_final', $convertedProposal->valor_comissao_final ?? '') }}" step="0.01">
                        </div>
                    </div>
                </div>

                <!-- Observações -->

            </div>
            <!-- BOTÕES DE AÇÃO -->
            <div class="col-lg-4">
                <!-- BOTÕES DE AÇÃO -->
                @include('components.admin.action-card', [
                'cancelButtonHref' => route('admin.v2.converted-proposals.index'),
                'submitButtonLabel' => isset($convertedProposal) ? 'Atualizar Proposta' : 'Criar Proposta Convertida',
                'timestamps' => isset($convertedProposal) ? [
                'created_at' => $convertedProposal->created_at,
                'updated_at' => $convertedProposal->updated_at
                ] : null
                ])
                <!-- Dados Principais -->
                <div class="modern-card">
                    <div class="modern-card-header">
                        <h5 class="modern-card-title">
                            <i class="bi bi-info-circle"></i>
                            Dados Principais
                        </h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Cliente</label>
                            <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                <option value="">Selecione o cliente...</option>
                                @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $convertedProposal->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Proposta Original</label>
                            <select name="proposal_id" class="form-select @error('proposal_id') is-invalid @enderror">
                                <option value="">Sem proposta associada...</option>
                                @foreach($proposals as $proposal)
                                <option value="{{ $proposal->id }}" {{ old('proposal_id', $convertedProposal->proposal_id ?? '') == $proposal->id ? 'selected' : '' }}>
                                    Proposta #{{ $proposal->id }} - {{ $proposal->brand ?? '' }} {{ $proposal->model ?? '' }}
                                </option>
                                @endforeach
                            </select>
                            @error('proposal_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="Em Processo" {{ old('status', $convertedProposal->status ?? 'Em Processo') === 'Em Processo' ? 'selected' : '' }}>Em Processo</option>
                                <option value="Concluída" {{ old('status', $convertedProposal->status ?? '') === 'Concluída' ? 'selected' : '' }}>Concluída</option>
                                <option value="Cancelada" {{ old('status', $convertedProposal->status ?? '') === 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">URL do Anúncio</label>
                            <input type="url" name="url" class="form-control @error('url') is-invalid @enderror"
                                value="{{ old('url', $convertedProposal->url ?? '') }}">
                            @error('url')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modern-card">
                    <div class="modern-card-header">
                        <h5 class="modern-card-title">
                            <i class="bi bi-info-circle"></i>
                            Observações
                        </h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Contactos do Stand</label>
                            <textarea name="contactos_stand" class="form-control"
                                rows="3">{{ old('contactos_stand', $convertedProposal->contactos_stand ?? '') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observações Gerais</label>
                            <textarea name="observacoes" class="form-control"
                                rows="4">{{ old('observacoes', $convertedProposal->observacoes ?? '') }}</textarea>
                        </div>
                    </div>
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

        .form-check-input:checked {
            background-color: var(--admin-primary);
            border-color: var(--admin-primary);
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