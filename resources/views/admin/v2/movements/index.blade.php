@extends('layouts.admin-v2')

@section('title', 'Movimentos Financeiros')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-journal-text', 'label' => 'Movimentos', 'href' => ''],
    ],
    'title'       => 'Movimentos Financeiros',
    'subtitle'    => 'Registo de receitas, despesas, salários, impostos e outros movimentos',
    'actionHref'   => route('admin.v2.movements.create'),
    'actionLabel'  => 'Novo Movimento',
    'action2Href'  => route('admin.v2.financial.dashboard'),
    'action2Label' => 'Ver Resultados',
    'action2Icon'  => 'bi-bar-chart-line',
])

@include('components.admin.stats-cards', ['stats' => $stats])

{{-- FILTROS --}}
<form method="GET" action="{{ route('admin.v2.movements.index') }}" class="mb-4">
    <div class="modern-card">
        <div class="modern-card-body p-3">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Pesquisar</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control" placeholder="Título ou observações...">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Tipo</label>
                    <select name="movement_type" class="form-select">
                        <option value="">Todos</option>
                        @foreach(\App\Models\Expense::movementTypes() as $val => $label)
                            <option value="{{ $val }}" {{ request('movement_type') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Categoria</label>
                    <select name="category" class="form-select">
                        <option value="">Todas</option>
                        @foreach(\App\Models\Expense::categories() as $val => $label)
                            <option value="{{ $val }}" {{ request('category') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        @foreach(\App\Models\Expense::statuses() as $val => $label)
                            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Veículo</label>
                    <select name="v3_vehicle_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ request('v3_vehicle_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->reference }} – {{ $v->brand }} {{ $v->model }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary-modern w-100">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <a href="{{ route('admin.v2.movements.index') }}" class="btn btn-secondary-modern">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>

            </div>

            {{-- Segunda linha: ano + datas --}}
            <div class="row g-3 mt-0">
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Ano</label>
                    <select name="year" class="form-select" id="movYearSelect" onchange="toggleMovDates(this.value)">
                        <option value="all" {{ $year === 'all' ? 'selected' : '' }}>Desde o início</option>
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ (string)$y === $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3" id="movDateFrom">
                    <label class="form-label fw-semibold small">Data de</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-3" id="movDateTo">
                    <label class="form-label fw-semibold small">Data até</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Se preencher datas, o filtro de ano é ignorado.
                    </small>
                </div>
            </div>

            <script>
            function toggleMovDates(val) {
                // No restriction — just cosmetic; server ignores year when dates set
            }
            </script>

        </div>
    </div>
</form>

{{-- TABELA --}}
<div class="modern-card">
    <div class="modern-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="modern-card-title mb-0">
            <i class="bi bi-journal-text me-2"></i>Lista de Movimentos
            <span class="badge bg-secondary ms-2">{{ $movements->total() }} total</span>
        </h5>
        <a href="{{ route('admin.v2.movements.create') }}" class="btn btn-primary-modern btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Novo Movimento
        </a>
    </div>

    @if($movements->isEmpty())
        @include('components.admin.empty-state', [
            'icon'        => 'bi-journal-text',
            'title'       => 'Nenhum movimento encontrado',
            'description' => 'Ainda não existem movimentos registados ou não há resultados para os filtros aplicados.',
            'actionUrl'   => route('admin.v2.movements.create'),
            'actionText'  => 'Registar Primeiro Movimento',
        ])
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:100px">Data</th>
                        <th>Descrição</th>
                        <th>Tipo</th>
                        <th>Categoria</th>
                        <th class="text-end">Bruto</th>
                        <th class="text-end">IVA</th>
                        <th class="text-end">Líquido</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-end">Saldo Real</th>
                        <th>Estado</th>
                        <th class="text-end" style="width:110px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $mov)
                    @php
                        $isIncome = $mov->movement_type === 'income';
                        $statusColors = [
                            'paid'           => 'success',
                            'pending'        => 'warning',
                            'partially_paid' => 'info',
                            'cancelled'      => 'secondary',
                        ];
                        $typeColors = [
                            'expense'    => 'danger',
                            'income'     => 'success',
                            'transfer'   => 'info',
                            'salary'     => 'primary',
                            'commission' => 'warning',
                            'tax'        => 'dark',
                            'refund'     => 'teal',
                            'other'      => 'secondary',
                        ];
                        $statusColor = $statusColors[$mov->status] ?? 'secondary';
                        $typeColor   = $typeColors[$mov->movement_type] ?? 'secondary';
                        $isAutoGenerated = !empty($mov->source_type);
                        $sourceUrl = null;
                        $sourceIcon = null;
                        if ($mov->source_type === \App\Models\Sale::class) {
                            $sourceUrl  = route('admin.v2.sales.edit', $mov->source_id);
                            $sourceIcon = 'bi-file-earmark-text';
                            $sourceTitle = 'Ver Venda';
                        } elseif ($mov->source_type === \App\Models\V3Vehicle::class) {
                            $sourceUrl  = route('admin.v3.vehicles.edit', $mov->source_id);
                            $sourceIcon = 'bi-car-front';
                            $sourceTitle = 'Ver Veículo';
                        } elseif ($mov->source_type === \App\Models\Vehicle::class) {
                            $sourceUrl  = route('admin.v2.vehicles.edit', $mov->source_id);
                            $sourceIcon = 'bi-car-front';
                            $sourceTitle = 'Ver Veículo (V2)';
                        }
                    @endphp
                    <tr class="{{ $isAutoGenerated ? 'table-light' : '' }}">
                        <td class="text-muted small">
                            {{ $mov->expense_date ? $mov->expense_date->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            <div class="fw-semibold">
                                {{ $mov->title }}
                                @if($isAutoGenerated)
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1" style="font-size:.65rem" title="Gerado automaticamente">
                                        <i class="bi bi-robot me-1"></i>Auto
                                    </span>
                                @endif
                            </div>
                            @if($mov->v3Vehicle)
                                <small class="text-muted">
                                    <i class="bi bi-car-front me-1"></i>{{ $mov->v3Vehicle->brand }} {{ $mov->v3Vehicle->model }} ({{ $mov->v3Vehicle->reference }})
                                </small>
                            @endif
                            @if($mov->client)
                                <small class="text-muted d-block">
                                    <i class="bi bi-person me-1"></i>{{ $mov->client->name }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $typeColor }}-subtle text-{{ $typeColor }} border border-{{ $typeColor }}-subtle">
                                {{ $mov->movement_type_label }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $mov->category_label }}</span>
                        </td>
                        <td class="text-end fw-semibold {{ $isIncome ? 'text-success' : 'text-danger' }}">
                            {{ $isIncome ? '+' : '-' }}€ {{ number_format($mov->amount_gross ?? $mov->amount, 2, ',', '.') }}
                        </td>
                        <td class="text-end text-muted small">
                            {{ $mov->vat_rate ? number_format($mov->vat_rate, 0) . '%' : '—' }}
                        </td>
                        <td class="text-end">
                            € {{ number_format($mov->amount_net ?? $mov->amount, 2, ',', '.') }}
                        </td>
                        @php $rowSaldo = $runningBalances[$mov->id] ?? null; @endphp
                        <td class="text-end fw-semibold {{ $rowSaldo !== null ? ($rowSaldo >= 0 ? 'text-primary' : 'text-danger') : 'text-muted' }}">
                            {{ $rowSaldo !== null ? '€ ' . number_format($rowSaldo, 2, ',', '.') : '—' }}
                        </td>
                        @php $rowSaldoReal = $runningBalancesReal[$mov->id] ?? null; @endphp
                        <td class="text-end fw-semibold {{ $rowSaldoReal !== null ? ($rowSaldoReal >= 0 ? 'text-success' : 'text-danger') : 'text-muted' }}"
                            title="{{ $rowSaldoReal === null ? 'Movimento pendente — não contabilizado no saldo real' : '' }}">
                            {{ $rowSaldoReal !== null ? '€ ' . number_format($rowSaldoReal, 2, ',', '.') : '—' }}
                        </td>
                        <td>>
                            <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} border border-{{ $statusColor }}-subtle">
                                {{ $mov->status_label }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if($isAutoGenerated)
                                @if($sourceUrl)
                                <a href="{{ $sourceUrl }}" class="btn btn-sm btn-outline-secondary" title="{{ $sourceTitle }}">
                                    <i class="bi {{ $sourceIcon }}"></i>
                                </a>
                                @endif
                                <span class="btn btn-sm btn-light text-muted" title="Gerado automaticamente — editar na fonte">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                            @else
                            <a href="{{ route('admin.v2.movements.edit', $mov->id) }}"
                               class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.v2.movements.destroy', $mov->id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Eliminar este movimento?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end">Total do período:</td>
                        @php
                            $periodGross = $movements->sum(fn($m) => $m->isIncome() ? ($m->amount_gross ?? $m->amount) : -($m->amount_gross ?? $m->amount));
                            $periodVat   = $movements->sum('vat_amount');
                            $periodNet   = $movements->sum(fn($m) => $m->isIncome() ? ($m->amount_net ?? $m->amount) : -($m->amount_net ?? $m->amount));
                        @endphp
                        <td class="text-end {{ $periodGross >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $periodGross >= 0 ? '+' : '' }}€ {{ number_format($periodGross, 2, ',', '.') }}
                        </td>
                        <td class="text-end text-muted">
                            € {{ number_format($periodVat, 2, ',', '.') }}
                        </td>
                        <td class="text-end {{ $periodNet >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $periodNet >= 0 ? '+' : '' }}€ {{ number_format($periodNet, 2, ',', '.') }}
                        </td>
                        <td class="text-end fw-semibold text-primary">
                            € {{ number_format($currentSaldo, 2, ',', '.') }}
                        </td>
                        <td class="text-end fw-semibold text-success">
                            € {{ number_format($currentSaldoReal, 2, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="p-3">
                {{ $movements->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
