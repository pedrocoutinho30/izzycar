{{--
    ==================================================================
    DASHBOARD V2
    ==================================================================
    
    Dashboard principal do backoffice com:
    - Stats cards
    - Alertas importantes
    - Atividade recente
    - Quick actions
    
    @extends layouts.admin-v2
    ==================================================================
--}}

@extends('layouts.admin-v2')

@section('title', 'Dashboard')

@section('content')

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Visão geral do seu negócio</p>
    </div>
</div>

<!-- Stats Cards -->
@include('components.admin.stats-cards', ['stats' => $stats])

<!-- Alertas Importantes -->
@if(count($alerts) > 0)
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-bell"></i>
                    Alertas e Notificações
                </h5>
            </div>

            @foreach($alerts as $alert)
            <div class="alert alert-{{ $alert['type'] }} d-flex align-items-start mb-3">
                <i class="bi {{ $alert['icon'] }} me-3" style="font-size: 1.5rem;"></i>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-1">{{ $alert['title'] }}</h6>
                    <p class="mb-2">{{ $alert['message'] }}</p>
                    @if(isset($alert['action']))
                    <a href="{{ $alert['action']['href'] }}" class="btn btn-sm btn-{{ $alert['type'] }}">
                        {{ $alert['action']['text'] }}
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
<!-- Gráficos de Estatísticas -->
<div class="row g-4 mt-4">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-graph-up"></i>
                    Estatísticas Mensais
                </h5>
            </div>
            <div class="modern-card-body">
                <!-- Filtros -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="filterMode" id="modeRange" value="range" checked>
                            <label class="btn btn-outline-primary" for="modeRange">Intervalo de Datas</label>
                            
                            <input type="radio" class="btn-check" name="filterMode" id="modeCompare" value="compare">
                            <label class="btn btn-outline-primary" for="modeCompare">Comparar 2 Meses</label>
                        </div>
                    </div>
                </div>

                <!-- Filtro: Intervalo de Datas -->
                <div id="rangeFilters" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label">Data Início - Mês</label>
                        <select id="startMonth" class="form-select">
                            <option value="1">Janeiro</option>
                            <option value="2">Fevereiro</option>
                            <option value="3">Março</option>
                            <option value="4">Abril</option>
                            <option value="5">Maio</option>
                            <option value="6">Junho</option>
                            <option value="7">Julho</option>
                            <option value="8">Agosto</option>
                            <option value="9">Setembro</option>
                            <option value="10">Outubro</option>
                            <option value="11">Novembro</option>
                            <option value="12">Dezembro</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ano</label>
                        <select id="startYear" class="form-select"></select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data Fim - Mês</label>
                        <select id="endMonth" class="form-select">
                            <option value="1">Janeiro</option>
                            <option value="2">Fevereiro</option>
                            <option value="3">Março</option>
                            <option value="4">Abril</option>
                            <option value="5">Maio</option>
                            <option value="6">Junho</option>
                            <option value="7">Julho</option>
                            <option value="8">Agosto</option>
                            <option value="9">Setembro</option>
                            <option value="10">Outubro</option>
                            <option value="11">Novembro</option>
                            <option value="12">Dezembro</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ano</label>
                        <select id="endYear" class="form-select"></select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" onclick="loadCharts()">
                            <i class="bi bi-arrow-clockwise"></i> Atualizar
                        </button>
                    </div>
                </div>

                <!-- Filtro: Comparação -->
                <div id="compareFilters" class="row g-3 mb-4" style="display: none;">
                    <div class="col-12 mb-2">
                        <strong>Período 1:</strong>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Mês</label>
                        <select id="compare1Month" class="form-select">
                            <option value="1">Janeiro</option>
                            <option value="2">Fevereiro</option>
                            <option value="3">Março</option>
                            <option value="4">Abril</option>
                            <option value="5">Maio</option>
                            <option value="6">Junho</option>
                            <option value="7">Julho</option>
                            <option value="8">Agosto</option>
                            <option value="9">Setembro</option>
                            <option value="10">Outubro</option>
                            <option value="11">Novembro</option>
                            <option value="12">Dezembro</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ano</label>
                        <select id="compare1Year" class="form-select"></select>
                    </div>
                    <div class="col-12 mb-2 mt-3">
                        <strong>Período 2:</strong>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Mês</label>
                        <select id="compare2Month" class="form-select">
                            <option value="1">Janeiro</option>
                            <option value="2">Fevereiro</option>
                            <option value="3">Março</option>
                            <option value="4">Abril</option>
                            <option value="5">Maio</option>
                            <option value="6">Junho</option>
                            <option value="7">Julho</option>
                            <option value="8">Agosto</option>
                            <option value="9">Setembro</option>
                            <option value="10">Outubro</option>
                            <option value="11">Novembro</option>
                            <option value="12">Dezembro</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ano</label>
                        <select id="compare2Year" class="form-select"></select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" onclick="loadCharts()">
                            <i class="bi bi-arrow-clockwise"></i> Comparar
                        </button>
                    </div>
                </div>

                <!-- Tipo de Gráfico -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Gráfico</label>
                        <select id="chartType" class="form-select" onchange="updateChartType()">
                            <option value="bar">Gráfico de Barras</option>
                            <option value="line">Gráfico de Linhas</option>
                        </select>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h6 class="text-center mb-3">Novos Clientes</h6>
                            <canvas id="clientsChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h6 class="text-center mb-3">Novas Propostas</h6>
                            <canvas id="proposalsChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h6 class="text-center mb-3">Simulações de Custos</h6>
                            <canvas id="costSimulatorsChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h6 class="text-center mb-3">Propostas Convertidas</h6>
                            <canvas id="convertedProposalsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Atividade Recente -->
<div class="row g-4">
    <!-- Últimas Propostas -->
    <div class="col-lg-6">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-file-earmark-text"></i>
                    Últimas Propostas
                </h5>
                <a href="{{ route('admin.v2.proposals.index') }}" class="btn btn-sm btn-secondary-modern">
                    Ver Todas
                </a>
            </div>

            @if($recentProposals->count() > 0)
                <div class="activity-list">
                    @foreach($recentProposals as $proposal)
                    <div class="activity-item">
                        <div class="activity-icon bg-primary">
                            <i class="bi bi-car-front"></i>
                        </div>
                        <div class="activity-content">
                            <h6 class="activity-title">
                                {{ $proposal->brand }} {{ $proposal->model }}
                            </h6>
                            <p class="activity-meta">
                                {{ $proposal->client ? $proposal->client->name : 'Sem cliente' }} • 
                                {{ $proposal->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="activity-status">
                            <span class="badge bg-{{ $proposal->status == 'Pendente' ? 'warning' : 'success' }}">
                                {{ $proposal->status }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted text-center py-4">Nenhuma proposta recente</p>
            @endif
        </div>
    </div>

    <!-- Últimos Formulários -->
    <div class="col-lg-6">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-envelope"></i>
                    Últimos Formulários
                </h5>
                <a href="{{ route('admin.v2.form-proposals.index') }}" class="btn btn-sm btn-secondary-modern">
                    Ver Todos
                </a>
            </div>

            @if($recentFormProposals->count() > 0)
                <div class="activity-list">
                    @foreach($recentFormProposals as $form)
                    <div class="activity-item">
                        <div class="activity-icon bg-warning">
                            <i class="bi bi-envelope-open"></i>
                        </div>
                        <div class="activity-content">
                            <h6 class="activity-title">{{ $form->name }}</h6>
                            <p class="activity-meta">
                                {{ $form->brand ?? 'Sem marca' }} {{ $form->model ?? '' }} • 
                                {{ $form->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="activity-status">
                            @if($form->proposal_id)
                                <span class="badge bg-success">Convertido</span>
                            @else
                                <span class="badge bg-warning">Novo</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted text-center py-4">Nenhum formulário recente</p>
            @endif
        </div>
    </div>
</div>



<!-- Últimas Vendas -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-cash-coin"></i>
                    Últimas Vendas
                </h5>
                <a href="{{ route('admin.v2.sales.index') }}" class="btn btn-sm btn-secondary-modern">
                    Ver Todas
                </a>
            </div>

            @if($recentSales->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Veículo</th>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSales as $sale)
                            <tr>
                                <td>
                                    <strong>{{ $sale->vehicle ? $sale->vehicle->brand . ' ' . $sale->vehicle->model : 'N/A' }}</strong>
                                </td>
                                <td>{{ $sale->client ? $sale->client->name : 'N/A' }}</td>
                                <td>{{ $sale->created_at->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <strong class="text-success">{{ number_format($sale->sell_price ?? 0, 0, ',', '.') }}€</strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">Nenhuma venda recente</p>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Activity List */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 8px;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        background: #e9ecef;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }

    .activity-content {
        flex: 1;
    }

    .activity-title {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: var(--admin-secondary);
    }

    .activity-meta {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 0;
    }

    .activity-status {
        margin-left: auto;
    }

    /* Custom alert styles */
    .alert {
        border-left: 4px solid;
    }

    .alert-warning {
        border-left-color: #ffc107;
    }

    .alert-info {
        border-left-color: #17a2b8;
    }

    .alert-success {
        border-left-color: #28a745;
    }

    /* Chart container */
    .chart-container {
        position: relative;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
</style>
@endpush

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
let clientsChart, proposalsChart, costSimulatorsChart, convertedProposalsChart;
let currentChartType = 'bar';

// Inicializar selects de ano
document.addEventListener('DOMContentLoaded', function() {
    const currentYear = new Date().getFullYear();
    const yearSelects = ['startYear', 'endYear', 'compare1Year', 'compare2Year'];
    
    yearSelects.forEach(selectId => {
        const select = document.getElementById(selectId);
        for (let year = 2025; year <= currentYear; year++) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            if (year === currentYear) option.selected = true;
            select.appendChild(option);
        }
    });

    // Definir valores padrão
    const currentMonth = new Date().getMonth() + 1;
    document.getElementById('startMonth').value = '1';
    document.getElementById('endMonth').value = currentMonth;
    document.getElementById('compare1Month').value = currentMonth;
    document.getElementById('compare2Month').value = currentMonth;

    // Toggle entre modos de filtro
    document.querySelectorAll('input[name="filterMode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'range') {
                document.getElementById('rangeFilters').style.display = 'flex';
                document.getElementById('compareFilters').style.display = 'none';
            } else {
                document.getElementById('rangeFilters').style.display = 'none';
                document.getElementById('compareFilters').style.display = 'flex';
            }
        });
    });

    // Carregar gráficos iniciais
    loadCharts();
});

function loadCharts() {
    const mode = document.querySelector('input[name="filterMode"]:checked').value;
    let url = '{{ route('admin.v2.dashboard.chart-data') }}?mode=' + mode;

    if (mode === 'range') {
        const startMonth = document.getElementById('startMonth').value;
        const startYear = document.getElementById('startYear').value;
        const endMonth = document.getElementById('endMonth').value;
        const endYear = document.getElementById('endYear').value;

        const startDate = `${startYear}-${startMonth.padStart(2, '0')}-01`;
        const endDate = `${endYear}-${endMonth.padStart(2, '0')}-01`;

        // Validar intervalo máximo de 1 ano
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffMonths = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
        
        if (diffMonths > 12) {
            alert('O intervalo máximo é de 1 ano (12 meses).');
            return;
        }

        if (start > end) {
            alert('A data de início deve ser anterior à data de fim.');
            return;
        }

        url += `&start_date=${startDate}&end_date=${endDate}`;
    } else {
        const compare1Month = document.getElementById('compare1Month').value;
        const compare1Year = document.getElementById('compare1Year').value;
        const compare2Month = document.getElementById('compare2Month').value;
        const compare2Year = document.getElementById('compare2Year').value;

        const startDate = `${compare1Year}-${compare1Month.padStart(2, '0')}-01`;
        const endDate = `${compare1Year}-${compare1Month.padStart(2, '0')}-01`;
        const compareStartDate = `${compare2Year}-${compare2Month.padStart(2, '0')}-01`;
        const compareEndDate = `${compare2Year}-${compare2Month.padStart(2, '0')}-01`;

        url += `&start_date=${startDate}&end_date=${endDate}&compare_start_date=${compareStartDate}&compare_end_date=${compareEndDate}`;
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            updateCharts(data);
        })
        .catch(error => {
            console.error('Erro ao carregar dados:', error);
            alert('Erro ao carregar gráficos. Por favor, tente novamente.');
        });
}

function updateCharts(data) {
    const mode = document.querySelector('input[name="filterMode"]:checked').value;

    if (mode === 'range') {
        // Modo intervalo - 1 dataset
        const dataset = data.datasets[0];
        updateChart('clientsChart', data.labels, [
            {
                label: 'Novos Clientes',
                data: dataset.clients,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2
            }
        ]);

        updateChart('proposalsChart', data.labels, [
            {
                label: 'Novas Propostas',
                data: dataset.proposals,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2
            }
        ]);

        updateChart('costSimulatorsChart', data.labels, [
            {
                label: 'Simulações de Custos',
                data: dataset.costSimulators,
                backgroundColor: 'rgba(255, 159, 64, 0.5)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 2
            }
        ]);

        updateChart('convertedProposalsChart', data.labels, [
            {
                label: 'Propostas Convertidas',
                data: dataset.convertedProposals,
                backgroundColor: 'rgba(153, 102, 255, 0.5)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 2
            }
        ]);
    } else {
        // Modo comparação - 2 datasets
        const dataset1 = data.datasets[0];
        const dataset2 = data.datasets[1];

        updateChart('clientsChart', data.labels, [
            {
                label: dataset1.label,
                data: dataset1.clients,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2
            },
            {
                label: dataset2.label,
                data: dataset2.clients,
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2
            }
        ]);

        updateChart('proposalsChart', data.labels, [
            {
                label: dataset1.label,
                data: dataset1.proposals,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2
            },
            {
                label: dataset2.label,
                data: dataset2.proposals,
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2
            }
        ]);

        updateChart('costSimulatorsChart', data.labels, [
            {
                label: dataset1.label,
                data: dataset1.costSimulators,
                backgroundColor: 'rgba(255, 159, 64, 0.5)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 2
            },
            {
                label: dataset2.label,
                data: dataset2.costSimulators,
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2
            }
        ]);

        updateChart('convertedProposalsChart', data.labels, [
            {
                label: dataset1.label,
                data: dataset1.convertedProposals,
                backgroundColor: 'rgba(153, 102, 255, 0.5)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 2
            },
            {
                label: dataset2.label,
                data: dataset2.convertedProposals,
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2
            }
        ]);
    }
}

function updateChart(canvasId, labels, datasets) {
    const ctx = document.getElementById(canvasId);
    
    // Destruir gráfico existente
    if (canvasId === 'clientsChart' && clientsChart) clientsChart.destroy();
    if (canvasId === 'proposalsChart' && proposalsChart) proposalsChart.destroy();
    if (canvasId === 'costSimulatorsChart' && costSimulatorsChart) costSimulatorsChart.destroy();
    if (canvasId === 'convertedProposalsChart' && convertedProposalsChart) convertedProposalsChart.destroy();

    const chart = new Chart(ctx, {
        type: currentChartType,
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Guardar referência ao gráfico
    if (canvasId === 'clientsChart') clientsChart = chart;
    if (canvasId === 'proposalsChart') proposalsChart = chart;
    if (canvasId === 'costSimulatorsChart') costSimulatorsChart = chart;
    if (canvasId === 'convertedProposalsChart') convertedProposalsChart = chart;
}

function updateChartType() {
    currentChartType = document.getElementById('chartType').value;
    loadCharts();
}
</script>
@endpush
