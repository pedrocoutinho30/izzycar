@extends('layouts.admin-v2')

@section('title', 'Dashboard Financeiro')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-wallet2', 'label' => 'Financeiro', 'href' => ''],
    ],
    'title'    => 'Dashboard Financeiro',
    'subtitle' => 'Extrato completo de entradas, saídas e IVA estimado',
])

@include('components.admin.stats-cards', ['stats' => $stats])

{{-- FILTROS --}}
<form method="GET" action="{{ route('admin.v2.financial.dashboard') }}" class="mb-4">
    <div class="modern-card">
        <div class="modern-card-body p-3">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Ano</label>
                    <select name="year" class="form-select">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Mês</label>
                    <select name="month" class="form-select">
                        <option value="">Todos os meses</option>
                        @foreach([1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'] as $m => $label)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tipo</label>
                    <select name="type" class="form-select">
                        <option value="">Todos</option>
                        <option value="income"  {{ $type === 'income'  ? 'selected' : '' }}>🟢 Entradas</option>
                        <option value="expense" {{ $type === 'expense' ? 'selected' : '' }}>🔴 Saídas</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary-modern w-100">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.v2.financial.dashboard') }}" class="btn btn-secondary-modern">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</form>

{{-- GRÁFICO MENSAL --}}
<div class="modern-card mb-4">
    <div class="modern-card-header d-flex justify-content-between align-items-center">
        <h5 class="modern-card-title mb-0"><i class="bi bi-bar-chart-line me-2"></i>Evolução Mensal {{ $year }}</h5>
    </div>
    <div class="modern-card-body p-3">
        <canvas id="financialChart" height="90"></canvas>
    </div>
</div>

{{-- RESUMO FINANCEIRO (VAT breakdown) --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="modern-card h-100">
            <div class="modern-card-body p-4 text-center">
                <div class="text-success fw-bold fs-4">€ {{ number_format($totalIncome, 2, ',', '.') }}</div>
                <div class="text-muted small mt-1">Total Entradas (bruto c/ IVA)</div>
                <hr class="my-2">
                <div class="text-muted small">IVA liquidado: <strong class="text-warning">€ {{ number_format($totalVatCharged, 2, ',', '.') }}</strong></div>
                <div class="text-muted small">Entradas líquidas: <strong class="text-success">€ {{ number_format($totalIncome - $totalVatCharged, 2, ',', '.') }}</strong></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="modern-card h-100">
            <div class="modern-card-body p-4 text-center">
                <div class="text-danger fw-bold fs-4">€ {{ number_format($totalExpenses, 2, ',', '.') }}</div>
                <div class="text-muted small mt-1">Total Saídas (bruto c/ IVA)</div>
                <hr class="my-2">
                <div class="text-muted small">IVA dedutível: <strong class="text-success">€ {{ number_format($totalVatDeducted, 2, ',', '.') }}</strong></div>
                <div class="text-muted small">Saídas líquidas: <strong class="text-danger">€ {{ number_format($totalExpenses - $totalVatDeducted, 2, ',', '.') }}</strong></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="modern-card h-100 {{ $netResult >= 0 ? 'border-start border-success border-3' : 'border-start border-danger border-3' }}">
            <div class="modern-card-body p-4 text-center">
                <div class="text-muted small fw-semibold">Resultado Bruto</div>
                <div class="{{ $netBalance >= 0 ? 'text-success' : 'text-danger' }} fw-bold fs-5 mt-1">
                    {{ $netBalance >= 0 ? '+' : '' }}€ {{ number_format($netBalance, 2, ',', '.') }}
                </div>
                <hr class="my-2">
                <div class="text-muted small">
                    − IVA a entregar ao Estado:
                    <strong class="text-warning">€ {{ number_format($netVat, 2, ',', '.') }}</strong>
                </div>
                <hr class="my-2">
                <div class="text-muted small fw-semibold">Resultado Líquido (real)</div>
                <div class="{{ $netResult >= 0 ? 'text-success' : 'text-danger' }} fw-bold fs-4">
                    {{ $netResult >= 0 ? '+' : '' }}€ {{ number_format($netResult, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- LEDGER TABLE --}}
<div class="modern-card">
    <div class="modern-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="modern-card-title mb-0">
            <i class="bi bi-journal-text me-2"></i>Movimentos Financeiros
            <span class="badge bg-secondary ms-2">{{ $movements->count() }}</span>
        </h5>
        <a href="{{ route('admin.v2.financial.seed') }}" class="btn btn-outline-secondary btn-sm"
           onclick="return confirm('Sincronizar todos os dados existentes? Isto irá reprocessar vendas, veículos e despesas.')">
            <i class="bi bi-arrow-clockwise me-1"></i> Sincronizar Dados Históricos
        </a>
    </div>

    @if($movements->isEmpty())
        <div class="modern-card-body p-5 text-center text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <p class="mb-2">Sem movimentos para o período selecionado.</p>
            <a href="{{ route('admin.v2.financial.seed') }}" class="btn btn-primary-modern"
               onclick="return confirm('Sincronizar todos os dados existentes?')">
                <i class="bi bi-arrow-clockwise me-1"></i> Sincronizar Dados Históricos
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:110px">Data</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th class="text-end">Bruto</th>
                        <th class="text-center">IVA %</th>
                        <th class="text-end">IVA €</th>
                        <th class="text-end">Líquido</th>
                        <th class="text-center" style="width:90px">Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $mov)
                    <tr class="{{ $mov->type === 'income' ? 'table-success-subtle' : '' }}">
                        <td class="text-muted small">
                            {{ \Carbon\Carbon::parse($mov->movement_date)->format('d/m/Y') }}
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $mov->description }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $mov->category }}</span>
                        </td>
                        <td class="text-end fw-semibold {{ $mov->type === 'income' ? 'text-success' : 'text-danger' }}">
                            {{ $mov->type === 'income' ? '+' : '-' }}
                            € {{ number_format($mov->amount_gross, 2, ',', '.') }}
                        </td>
                        <td class="text-center text-muted small">
                            {{ $mov->vat_rate ? number_format($mov->vat_rate, 0) . '%' : '—' }}
                        </td>
                        <td class="text-end text-muted small">
                            {{ $mov->vat_amount > 0 ? '€ ' . number_format($mov->vat_amount, 2, ',', '.') : '—' }}
                        </td>
                        <td class="text-end">
                            € {{ number_format($mov->amount_net, 2, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if($mov->type === 'income')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-arrow-down me-1"></i>Entrada
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-arrow-up me-1"></i>Saída
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">Total do período:</td>
                        <td class="text-end">
                            @php $periodGross = $movements->sum(fn($m) => $m->type === 'income' ? $m->amount_gross : -$m->amount_gross); @endphp
                            <span class="{{ $periodGross >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $periodGross >= 0 ? '+' : '' }}€ {{ number_format($periodGross, 2, ',', '.') }}
                            </span>
                        </td>
                        <td></td>
                        <td class="text-end text-muted">
                            € {{ number_format($movements->sum('vat_amount'), 2, ',', '.') }}
                        </td>
                        <td class="text-end">
                            @php $periodNet = $movements->sum(fn($m) => $m->type === 'income' ? $m->amount_net : -$m->amount_net); @endphp
                            <span class="{{ $periodNet >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $periodNet >= 0 ? '+' : '' }}€ {{ number_format($periodNet, 2, ',', '.') }}
                            </span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

    const incomeData   = @json(array_values($chartIncome));
    const expenseData  = @json(array_values($chartExpenses));

    new Chart(document.getElementById('financialChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Entradas',
                    data: incomeData,
                    backgroundColor: 'rgba(25, 135, 84, 0.75)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Saídas',
                    data: expenseData,
                    backgroundColor: 'rgba(220, 53, 69, 0.65)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                },
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label + ': € ' + ctx.parsed.y.toLocaleString('pt-PT', { minimumFractionDigits: 2 })
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => '€ ' + v.toLocaleString('pt-PT')
                    }
                }
            }
        }
    });
});
</script>
@endpush

<style>
.table-success-subtle {
    --bs-table-bg: rgba(25, 135, 84, 0.04);
}
</style>
