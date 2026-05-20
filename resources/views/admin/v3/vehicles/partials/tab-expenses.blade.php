@php
    $manualExpenses  = $vehicle->expenses->where('category', '!=', 'vehicle_purchase');
    $expensesTotal   = $manualExpenses->where('movement_type', 'expense')->sum('amount_gross');
    $expensesPending = $manualExpenses->where('status', 'pending')->count();
@endphp

{{-- ── Summary cards ───────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-light h-100">
            <div class="card-body py-2 text-center">
                <p class="text-muted small mb-1">Total Despesas</p>
                <p class="fw-bold fs-6 mb-0 text-danger">{{ number_format($expensesTotal, 2, ',', '.') }} €</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-light h-100">
            <div class="card-body py-2 text-center">
                <p class="text-muted small mb-1">Nº Movimentos</p>
                <p class="fw-bold fs-6 mb-0">{{ $manualExpenses->count() }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-light h-100">
            <div class="card-body py-2 text-center">
                <p class="text-muted small mb-1">Custo Total Veículo</p>
                <p class="fw-bold fs-6 mb-0">{{ number_format($vehicle->total_cost, 2, ',', '.') }} €</p>
                <small class="text-muted" style="font-size:.7rem">compra + despesas</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-light h-100">
            <div class="card-body py-2 text-center">
                <p class="text-muted small mb-1">Pendentes</p>
                <p class="fw-bold fs-6 mb-0 {{ $expensesPending > 0 ? 'text-warning' : '' }}">{{ $expensesPending }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Existing expenses ───────────────────────────────────────────── --}}
@if($manualExpenses->count())
<div class="table-responsive mb-4">
    <table class="table table-sm table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Data</th>
                <th class="text-end">Valor (€)</th>
                <th>IVA</th>
                <th>Estado</th>
                <th>Pagamento</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($manualExpenses as $exp)
            <tr>
                <td>
                    <div class="fw-semibold small">{{ $exp->title }}</div>
                    @if($exp->observations)
                        <div class="text-muted" style="font-size:.7rem">{{ Str::limit($exp->observations, 40) }}</div>
                    @endif
                </td>
                <td><span class="badge bg-secondary small">{{ $exp->category_label }}</span></td>
                <td class="small text-nowrap">{{ $exp->expense_date?->format('d/m/Y') }}</td>
                <td class="text-end fw-semibold small text-{{ $exp->movement_type === 'income' ? 'success' : 'danger' }}">
                    {{ $exp->movement_type === 'income' ? '+' : '-' }}{{ number_format($exp->amount_gross, 2, ',', '.') }}
                </td>
                <td class="small">{{ $exp->vat_rate ? $exp->vat_rate . '%' : '—' }}</td>
                <td>
                    @php $statusColors = ['paid' => 'success', 'pending' => 'warning', 'cancelled' => 'secondary', 'partially_paid' => 'info']; @endphp
                    <span class="badge bg-{{ $statusColors[$exp->status] ?? 'secondary' }} small">{{ \App\Models\Expense::statuses()[$exp->status] ?? $exp->status }}</span>
                </td>
                <td class="small">{{ \App\Models\Expense::paymentMethods()[$exp->payment_method] ?? '—' }}</td>
                <td>
                    <form action="{{ route('admin.v3.vehicles.expenses.destroy', [$vehicle->id, $exp->id]) }}" method="POST"
                          onsubmit="return confirm('Eliminar esta despesa?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger py-0 px-1"><i class="bi bi-trash" style="font-size:.75rem"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<p class="text-muted small mb-4">Sem despesas registadas.</p>
@endif

{{-- ── Add expense form ────────────────────────────────────────────── --}}
<div class="border rounded p-3 bg-light">
    <h6 class="fw-semibold mb-3"><i class="bi bi-plus-circle me-1 text-primary"></i> Nova Despesa</h6>
    <form action="{{ route('admin.v3.vehicles.expenses.store', $vehicle->id) }}" method="POST">
        @csrf
        <div class="row g-2">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Descrição *</label>
                <input type="text" name="title" class="form-control form-control-sm" required placeholder="ex: Pneus, Revisão, Transporte…">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tipo *</label>
                <select name="movement_type" class="form-select form-select-sm" required>
                    @foreach(\App\Models\Expense::movementTypes() as $k => $l)
                        <option value="{{ $k }}" {{ $k === 'expense' ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Categoria</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">— Sem categoria —</option>
                    @foreach(\App\Models\Expense::categories() as $k => $l)
                        <option value="{{ $k }}">{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Valor Bruto *</label>
                <div class="input-group input-group-sm">
                    <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                    <span class="input-group-text">€</span>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">IVA (%)</label>
                <select name="vat_rate" class="form-select form-select-sm">
                    <option value="">0%</option>
                    <option value="6">6%</option>
                    <option value="13">13%</option>
                    <option value="23">23%</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Data *</label>
                <input type="date" name="expense_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Pagamento</label>
                <select name="payment_method" class="form-select form-select-sm">
                    <option value="">—</option>
                    @foreach(\App\Models\Expense::paymentMethods() as $k => $l)
                        <option value="{{ $k }}">{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Estado</label>
                <select name="status" class="form-select form-select-sm">
                    @foreach(\App\Models\Expense::statuses() as $k => $l)
                        <option value="{{ $k }}" {{ $k === 'paid' ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label small fw-semibold">Observações</label>
                <input type="text" name="observations" class="form-control form-control-sm" placeholder="Observações opcionais…">
            </div>
            <div class="col-12 pt-1">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Despesa
                </button>
            </div>
        </div>
    </form>
</div>
