@extends('frontend.partials.layout')

@section('content')

@include('frontend.partials.hero-section', ['title' => "Resultado da Simulação", 'subtitle' => ""])

<section class="section-padding">
    <div class="container">
        <!-- Welcome Card -->
        <div class="result-welcome-card">
            <div class="result-welcome-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="result-welcome-text">
                <h2 class="result-welcome-title">Olá, {{ $name }}!</h2>
                <p>Obrigado por utilizar o nosso simulador de custos. Com base nas informações que forneceu, aqui está uma estimativa detalhada dos custos associados à importação do seu veículo.</p>
            </div>
        </div>

        <!-- Cost Breakdown Card -->
        <div class="result-cost-card">
            <div class="result-section-header">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="2" x2="12" y2="22"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <h3>Simulação de Custos</h3>
            </div>

            <div class="cost-items-list">
                <div class="cost-item">
                    <div class="cost-item-label">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <span>Custo do carro</span>
                    </div>
                    <div class="cost-item-value">{{ number_format($valorCarro, 2, ',', '.') }} €</div>
                </div>

                <div class="cost-item">
                    <div class="cost-item-label">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                        <span>ISV (Imposto)</span>
                    </div>
                    <div class="cost-item-value">{{ number_format($isv, 2, ',', '.') }} €</div>
                </div>

                <div class="cost-item">
                    <div class="cost-item-label">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <polyline points="17 11 19 13 23 9"></polyline>
                        </svg>
                        <span>Custos de serviço</span>
                    </div>
                    <div class="cost-item-value">{{ number_format($servicos, 2, ',', '.') }} €</div>
                </div>

                <div class="cost-item-total">
                    <div class="cost-item-label">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Preço Chave na Mão</span>
                    </div>
                    <div class="cost-item-value-total">{{ number_format($custoTotal, 2, ',', '.') }} €</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="result-actions">
            <a href="{{ route('frontend.cost-simulator') }}" class="btn-result-secondary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="1 4 1 10 7 10"></polyline>
                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                </svg>
                Nova Simulação
            </a>
            <a href="{{ route('frontend.form-import') }}" class="btn-result-primary">
                Quero Importar
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>

        <!-- ISV Table -->
        @if ($isv > 0)
        <div class="result-isv-card">
            <div class="result-section-header">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <h3>Tabela de Cálculo do ISV</h3>
            </div>
            <div class="isv-table-wrapper">
                {!! $tableIsv !!}
            </div>
        </div>
        @endif
    </div>
</section>

@endsection

@push('styles')
<style>
    .result-welcome-card {
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        border-radius: 24px;
        padding: 3rem;
        margin-bottom: 3rem;
        display: flex;
        gap: 2rem;
        align-items: center;
        color: white;
        box-shadow: 0 20px 60px rgba(110, 7, 7, 0.3);
    }

    .result-welcome-icon {
        flex-shrink: 0;
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.15);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .result-welcome-text {
        flex: 1;
    }

    .result-welcome-title {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
        color: white;
    }

    .result-welcome-text p {
        font-size: 1.1rem;
        line-height: 1.7;
        margin: 0;
        opacity: 0.95;
    }

    .result-cost-card,
    .result-isv-card {
        background: white;
        border-radius: 24px;
        padding: 3rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        border: 2px solid var(--accent-color);
    }

    .result-section-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .result-section-header svg {
        color: var(--accent-color);
        flex-shrink: 0;
    }

    .result-section-header h3 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111;
        margin: 0;
    }

    .cost-items-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .cost-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 16px;
        transition: all 0.3s ease;
    }

    .cost-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .cost-item-label {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
    }

    .cost-item-label svg {
        color: var(--accent-color);
        flex-shrink: 0;
    }

    .cost-item-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111;
    }

    .cost-item-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 2rem;
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        border-radius: 16px;
        color: white;
        margin-top: 1rem;
    }

    .cost-item-total .cost-item-label {
        color: white;
        font-size: 1.3rem;
    }

    .cost-item-total .cost-item-label svg {
        color: white;
    }

    .cost-item-value-total {
        font-size: 2rem;
        font-weight: 800;
        color: white;
    }

    .result-actions {
        display: flex;
        gap: 1.5rem;
        justify-content: center;
        margin-bottom: 3rem;
        flex-wrap: wrap;
    }

    .btn-result-secondary,
    .btn-result-primary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 1.25rem 2.5rem;
        font-weight: 700;
        font-size: 1.1rem;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-result-secondary {
        background: white;
        color: #111;
        border: 2px solid var(--accent-color);
    }

    .btn-result-secondary:hover {
        background: var(--accent-color);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(110, 7, 7, 0.3);
    }

    .btn-result-primary {
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        color: white;
        border: 2px solid var(--accent-color);
        box-shadow: 0 8px 25px rgba(110, 7, 7, 0.3);
    }

    .btn-result-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(110, 7, 7, 0.4);
        color: white;
    }

    .btn-result-secondary svg,
    .btn-result-primary svg {
        transition: transform 0.3s ease;
    }

    .btn-result-primary:hover svg {
        transform: translateX(5px);
    }

    .isv-table-wrapper {
        overflow-x: auto;
        margin-top: 1.5rem;
    }

    .isv-table-wrapper table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .isv-table-wrapper table th {
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        color: white;
        padding: 1rem;
        font-weight: 700;
        text-align: left;
        border: none;
    }

    .isv-table-wrapper table th:first-child {
        border-top-left-radius: 12px;
    }

    .isv-table-wrapper table th:last-child {
        border-top-right-radius: 12px;
    }

    .isv-table-wrapper table td {
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        color: #333;
    }

    .isv-table-wrapper table tr:last-child td {
        border-bottom: none;
    }

    .isv-table-wrapper table tr:hover {
        background: #f8f9fa;
    }

    @media (max-width: 768px) {
        .result-welcome-card {
            flex-direction: column;
            padding: 2rem;
            text-align: center;
        }

        .result-welcome-icon {
            width: 64px;
            height: 64px;
        }

        .result-welcome-icon svg {
            width: 32px;
            height: 32px;
        }

        .result-welcome-title {
            font-size: 1.5rem;
        }

        .result-welcome-text p {
            font-size: 1rem;
        }

        .result-cost-card,
        .result-isv-card {
            padding: 2rem 1.5rem;
        }

        .result-section-header h3 {
            font-size: 1.5rem;
        }

        .cost-item {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .cost-item-total {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .cost-item-value-total {
            font-size: 1.75rem;
        }

        .result-actions {
            flex-direction: column;
        }

        .btn-result-secondary,
        .btn-result-primary {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush