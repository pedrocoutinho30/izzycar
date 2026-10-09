{{-- Lista só de leitura dos movimentos associados a uma legalização ou viatura. Espera $movements (coleção de Expense). --}}
@php
    $expenseTotal = $movements->where('movement_type', 'expense')->sum('amount_gross');
    $incomeTotal  = $movements->where('movement_type', 'income')->sum('amount_gross');
    $statusColors = ['paid' => 'success', 'pending' => 'warning', 'cancelled' => 'secondary', 'partially_paid' => 'info'];
@endphp

@if($movements->isEmpty())
    <p class="text-muted small mb-0">Sem movimentos associados.</p>
@else
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Data</th>
                    <th class="text-end">Valor (€)</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($movements as $movement)
                    <tr>
                        <td class="small fw-semibold">{{ $movement->title ?: $movement->category_label }}</td>
                        <td><span class="badge bg-secondary small">{{ $movement->category_label }}</span></td>
                        <td class="small text-nowrap">{{ $movement->expense_date?->format('d/m/Y') }}</td>
                        <td class="text-end fw-semibold small text-{{ $movement->movement_type === 'income' ? 'success' : 'danger' }}">
                            {{ $movement->movement_type === 'income' ? '+' : '-' }}{{ number_format($movement->amount_gross, 2, ',', '.') }}
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$movement->status] ?? 'secondary' }} small">{{ \App\Models\Expense::statuses()[$movement->status] ?? $movement->status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.v2.movements.edit', $movement->id) }}" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Abrir movimento">
                                <i class="bi bi-box-arrow-up-right" style="font-size:.75rem"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="small fw-semibold">
                <tr>
                    <td colspan="3" class="text-end">Despesas</td>
                    <td class="text-end text-danger">-{{ number_format($expenseTotal, 2, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
                @if($incomeTotal > 0)
                    <tr>
                        <td colspan="3" class="text-end">Receitas</td>
                        <td class="text-end text-success">+{{ number_format($incomeTotal, 2, ',', '.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                @endif
            </tfoot>
        </table>
    </div>
@endif
