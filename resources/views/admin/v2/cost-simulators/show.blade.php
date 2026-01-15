{{--
    ==================================================================
    SIMULADOR DE CUSTOS - DETALHES V2
    ==================================================================
    
    Página de detalhes completa da simulação de custos
    
    @extends layouts.admin-v2
    ==================================================================
--}}

@extends('layouts.admin-v2')

@section('title', 'Detalhes da Simulação #' . $costSimulator->id)

@section('content')

<!-- Page Header -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-calculator', 'label' => 'Simulador de Custos', 'href' => route('admin.v2.cost-simulators.index')],
['icon' => '', 'label' => 'Detalhes #' . $costSimulator->id]
],
'title' => 'Simulação de Custos #' . $costSimulator->id,
'subtitle' => 'Detalhes completos da simulação realizada em ' . $costSimulator->created_at->format('d/m/Y'),
'actionHref' => '',
'actionLabel' => ''
])

<div class="row g-4">
    <!-- Coluna Principal -->
    <div class="col-lg-8">
        
        {{-- SECÇÃO 1: Informações do Cliente --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-person"></i>
                    Informações do Cliente
                </h5>
            </div>

            <div class="row g-3">
                @if($costSimulator->client)
                <div class="col-md-6">
                    <label class="form-label text-muted small">Nome</label>
                    <p class="mb-0"><strong>{{ $costSimulator->client->name }}</strong></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Email</label>
                    <p class="mb-0">
                        <strong>
                            <i class="bi bi-envelope me-1"></i>
                            {{ $costSimulator->client->email }}
                        </strong>
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Telefone</label>
                    <p class="mb-0">
                        <strong>
                            <i class="bi bi-telephone me-1"></i>
                            {{ $costSimulator->client->phone }}
                        </strong>
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Origem</label>
                    <p class="mb-0">
                        <span class="badge bg-info">
                            <i class="bi bi-calculator"></i>
                            Simulador de Custos
                        </span>
                    </p>
                </div>
                <div class="col-12 mt-3">
                    <a href="{{ route('admin.v2.clients.edit', $costSimulator->client_id) }}" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-person"></i>
                        Ver Perfil Completo do Cliente
                    </a>
                </div>
                @else
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Cliente não encontrado ou foi removido.
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- SECÇÃO 2: Detalhes do Veículo --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-car-front"></i>
                    Detalhes do Veículo
                </h5>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted small">Marca</label>
                    <p class="mb-0"><strong>{{ $costSimulator->brand }}</strong></p>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Modelo</label>
                    <p class="mb-0"><strong>{{ $costSimulator->model }}</strong></p>
                </div>
                @if($costSimulator->year)
                <div class="col-md-4">
                    <label class="form-label text-muted small">Ano</label>
                    <p class="mb-0"><strong>{{ $costSimulator->year }}</strong></p>
                </div>
                @endif
                
                @if($costSimulator->fuel)
                <div class="col-md-4">
                    <label class="form-label text-muted small">Combustível</label>
                    <p class="mb-0">
                        <span class="badge bg-secondary">
                            <i class="bi bi-fuel-pump"></i>
                            {{ ucfirst($costSimulator->fuel) }}
                        </span>
                    </p>
                </div>
                @endif

                @if($costSimulator->cc)
                <div class="col-md-4">
                    <label class="form-label text-muted small">Cilindrada</label>
                    <p class="mb-0"><strong>{{ $costSimulator->cc }} cc</strong></p>
                </div>
                @endif

                @if($costSimulator->co2)
                <div class="col-md-4">
                    <label class="form-label text-muted small">CO₂</label>
                    <p class="mb-0"><strong>{{ $costSimulator->co2 }} g/km</strong></p>
                </div>
                @endif

                @if($costSimulator->tipo_veiculo)
                <div class="col-md-6">
                    <label class="form-label text-muted small">Tipo de Veículo</label>
                    <p class="mb-0"><strong>{{ ucfirst(str_replace('_', ' ', $costSimulator->tipo_veiculo)) }}</strong></p>
                </div>
                @endif

                @if($costSimulator->pais_matricula)
                <div class="col-md-6">
                    <label class="form-label text-muted small">País da Matrícula</label>
                    <p class="mb-0">
                        <strong>
                            {{ $costSimulator->pais_matricula == 'uniao-europeia' ? 'União Europeia' : ucfirst($costSimulator->pais_matricula) }}
                        </strong>
                    </p>
                </div>
                @endif

                @if($costSimulator->autonomia)
                <div class="col-md-6">
                    <label class="form-label text-muted small">Autonomia da Bateria</label>
                    <p class="mb-0">
                        <strong>
                            {{ $costSimulator->autonomia == 'igual_superior' ? 'Igual ou superior a 50 km' : 'Inferior a 50 km' }}
                        </strong>
                    </p>
                </div>
                @endif

                @if($costSimulator->emissao_particulas)
                <div class="col-md-6">
                    <label class="form-label text-muted small">Emissão de Partículas</label>
                    <p class="mb-0">
                        <strong>
                            {{ $costSimulator->emissao_particulas == '+0.0001' ? '> 0.0001 g/km' : '≤ 0.0001 g/km' }}
                        </strong>
                    </p>
                </div>
                @endif
            </div>
        </div>

        {{-- SECÇÃO 3: Breakdown de Custos --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-currency-euro"></i>
                    Breakdown de Custos
                </h5>
            </div>

            <div class="costs-breakdown">
                <div class="cost-item">
                    <div class="cost-label">
                        <i class="bi bi-car-front-fill text-primary"></i>
                        Valor do Carro
                    </div>
                    <div class="cost-value primary">
                        {{ number_format($costSimulator->car_value, 2, ',', '.') }}€
                    </div>
                </div>

                <div class="cost-item">
                    <div class="cost-label">
                        <i class="bi bi-file-earmark-text text-warning"></i>
                        ISV (Imposto sobre Veículos)
                    </div>
                    <div class="cost-value">
                        {{ number_format($costSimulator->isv_cost, 2, ',', '.') }}€
                    </div>
                </div>

                @if($costSimulator->iuc_cost)
                <div class="cost-item">
                    <div class="cost-label">
                        <i class="bi bi-receipt text-info"></i>
                        IUC (Imposto Único de Circulação)
                    </div>
                    <div class="cost-value">
                        {{ number_format($costSimulator->iuc_cost, 2, ',', '.') }}€
                    </div>
                </div>
                @endif

                <div class="cost-item">
                    <div class="cost-label">
                        <i class="bi bi-percent text-secondary"></i>
                        Comissão
                    </div>
                    <div class="cost-value">
                        {{ number_format($costSimulator->commission_cost, 2, ',', '.') }}€
                    </div>
                </div>

                <div class="cost-item">
                    <div class="cost-label">
                        <i class="bi bi-search text-secondary"></i>
                        Inspeção na Origem
                    </div>
                    <div class="cost-value">
                        {{ number_format($costSimulator->inspection_commission_cost, 2, ',', '.') }}€
                    </div>
                </div>

                <div class="cost-item">
                    <div class="cost-label">
                        <i class="bi bi-truck text-secondary"></i>
                        Transporte
                    </div>
                    <div class="cost-value">
                        {{ number_format($costSimulator->transport, 2, ',', '.') }}€
                    </div>
                </div>

                <div class="cost-item">
                    <div class="cost-label">
                        <i class="bi bi-clipboard-check text-secondary"></i>
                        IPO (Inspeção Periódica Obrigatória)
                    </div>
                    <div class="cost-value">
                        {{ number_format($costSimulator->ipo_cost, 2, ',', '.') }}€
                    </div>
                </div>

                <div class="cost-item">
                    <div class="cost-label">
                        <i class="bi bi-file-text text-secondary"></i>
                        IMT (Imposto Municipal sobre Transações)
                    </div>
                    <div class="cost-value">
                        {{ number_format($costSimulator->imt_cost, 2, ',', '.') }}€
                    </div>
                </div>

                <div class="cost-item">
                    <div class="cost-label">
                        <i class="bi bi-journal-text text-secondary"></i>
                        Registo Automóvel
                    </div>
                    <div class="cost-value">
                        {{ number_format($costSimulator->registration_cost, 2, ',', '.') }}€
                    </div>
                </div>

                <div class="cost-item">
                    <div class="cost-label">
                        <i class="bi bi-signpost text-secondary"></i>
                        Matrículas
                    </div>
                    <div class="cost-value">
                        {{ number_format($costSimulator->plates_cost, 2, ',', '.') }}€
                    </div>
                </div>

                <div class="cost-item total">
                    <div class="cost-label">
                        <i class="bi bi-calculator-fill"></i>
                        <strong>CUSTO TOTAL</strong>
                    </div>
                    <div class="cost-value">
                        <strong>{{ number_format($costSimulator->total_cost, 2, ',', '.') }}€</strong>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Coluna Lateral -->
    <div class="col-lg-4">
        
        {{-- SECÇÃO: Resumo Rápido --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-info-circle"></i>
                    Resumo Rápido
                </h5>
            </div>

            <div class="summary-card">
                <div class="summary-item">
                    <span class="summary-label">ID da Simulação:</span>
                    <span class="summary-value">#{{ $costSimulator->id }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Estado:</span>
                    <span class="summary-value">
                        @if($costSimulator->read)
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Lido
                        </span>
                        @else
                        <span class="badge bg-warning">
                            <i class="bi bi-envelope"></i> Novo
                        </span>
                        @endif
                    </span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Veículo:</span>
                    <span class="summary-value"><strong>{{ $costSimulator->brand }} {{ $costSimulator->model }}</strong></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Valor do Carro:</span>
                    <span class="summary-value"><strong>{{ number_format($costSimulator->car_value, 2, ',', '.') }}€</strong></span>
                </div>
                <div class="summary-item highlight">
                    <span class="summary-label">Custo Total:</span>
                    <span class="summary-value"><strong>{{ number_format($costSimulator->total_cost, 2, ',', '.') }}€</strong></span>
                </div>
            </div>
        </div>

        {{-- SECÇÃO: Timestamps --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-clock-history"></i>
                    Informações de Registo
                </h5>
            </div>

            <div class="timestamps-card">
                <div class="timestamp-item">
                    <i class="bi bi-calendar-plus"></i>
                    <div>
                        <small class="text-muted">Criado em</small>
                        <p class="mb-0"><strong>{{ $costSimulator->created_at->format('d/m/Y H:i') }}</strong></p>
                    </div>
                </div>
                <div class="timestamp-item">
                    <i class="bi bi-calendar-check"></i>
                    <div>
                        <small class="text-muted">Atualizado em</small>
                        <p class="mb-0"><strong>{{ $costSimulator->updated_at->format('d/m/Y H:i') }}</strong></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECÇÃO: Ações --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-gear"></i>
                    Ações
                </h5>
            </div>

            <div class="d-grid gap-2">
                @if($costSimulator->client)
                <a href="{{ route('admin.v2.clients.edit', $costSimulator->client_id) }}" 
                   class="btn btn-primary">
                    <i class="bi bi-person"></i>
                    Ver Perfil do Cliente
                </a>
                @endif

                <a href="{{ route('admin.v2.cost-simulators.index') }}" 
                   class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Voltar à Listagem
                </a>

                <form action="{{ route('admin.v2.cost-simulators.destroy', $costSimulator->id) }}" 
                      method="POST" 
                      onsubmit="return confirm('Tem a certeza que pretende eliminar esta simulação?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-trash"></i>
                        Eliminar Simulação
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
    /* Costs Breakdown */
    .costs-breakdown {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .cost-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .cost-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .cost-item.total {
        background: linear-gradient(135deg, var(--admin-primary) 0%, #990000 100%);
        color: white;
        padding: 1.25rem;
        margin-top: 1rem;
        font-size: 1.1rem;
    }

    .cost-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
        color: #495057;
    }

    .cost-item.total .cost-label {
        color: white;
    }

    .cost-value {
        font-weight: 600;
        font-size: 1.05rem;
        color: var(--admin-secondary);
    }

    .cost-value.primary {
        color: var(--admin-primary);
        font-size: 1.15rem;
    }

    .cost-item.total .cost-value {
        color: white;
        font-size: 1.5rem;
    }

    /* Summary Card */
    .summary-card {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-item.highlight {
        background: rgba(110, 7, 7, 0.05);
        padding: 1rem;
        border-radius: 8px;
        margin-top: 0.5rem;
        border: 2px solid var(--admin-primary);
    }

    .summary-label {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .summary-value {
        font-weight: 600;
        color: var(--admin-secondary);
    }

    .summary-item.highlight .summary-value {
        color: var(--admin-primary);
        font-size: 1.25rem;
    }

    /* Timestamps Card */
    .timestamps-card {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .timestamp-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .timestamp-item i {
        font-size: 1.5rem;
        color: var(--admin-primary);
        margin-top: 0.25rem;
    }

    .timestamp-item small {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .cost-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .cost-value {
            align-self: flex-end;
        }

        .summary-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }

    /* Animation */
    .modern-card {
        animation: fadeInUp 0.4s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush
