{{-- Pedidos de importação de uma lead/cliente, com "Novo pedido" (pedido
     manual para agrupar oportunidades — ex. cliente antigo que quer um
     segundo carro). Espera $client e $requests (com opportunities_count). --}}
@php
    $statusLabels = ['novo' => 'Novo', 'em_analise' => 'Em análise', 'convertido' => 'Convertido', 'rejeitado' => 'Rejeitado', 'arquivado' => 'Arquivado'];
    $statusColors = ['novo' => 'warning', 'em_analise' => 'info', 'convertido' => 'success', 'rejeitado' => 'danger', 'arquivado' => 'secondary'];
    $openModal = $errors->any() && old('_form') === 'client-request';
@endphp

<div class="modern-card mb-4" id="pedidos">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-envelope-open"></i>
            Pedidos de importação
        </h5>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary rounded-pill">{{ $requests->count() }}</span>
            @canroute('admin.v2.form-proposals.store')
            <button type="button" class="btn btn-sm btn-primary-modern" data-bs-toggle="modal" data-bs-target="#clientRequestModal">
                <i class="bi bi-plus"></i> Novo pedido
            </button>
            @endcanroute
        </div>
    </div>

    @if($requests->isEmpty())
        <div class="modern-card-body text-center py-4">
            <i class="bi bi-envelope-open text-muted" style="font-size:2.5rem"></i>
            <p class="text-muted mt-2 mb-0">Sem pedidos de importação. Crie um para começar a juntar oportunidades.</p>
        </div>
    @else
    <div class="modern-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Pedido</th>
                        <th>Origem</th>
                        <th>Estado</th>
                        <th class="text-center">Oportunidades</th>
                        <th>Data</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <strong>{{ $request->label }}</strong>
                            @php $wanted = trim(implode(' ', array_filter([$request->brand, $request->model, $request->version]))); @endphp
                            @if(filled($request->title) && $wanted !== '')<br><small class="text-muted">{{ $wanted }}</small>@endif
                            @if($request->budget)<br><small class="text-muted">Orçamento: {{ number_format((float) $request->budget, 0, ',', '.') }} €</small>@endif
                        </td>
                        <td>
                            @if($request->isManual())
                                <span class="badge bg-light text-dark border"><i class="bi bi-person-gear me-1"></i>Manual</span>
                            @else
                                <span class="badge bg-light text-dark border"><i class="bi bi-globe me-1"></i>Site</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }}">{{ $statusLabels[$request->status] ?? ucfirst(str_replace('_', ' ', (string) $request->status)) }}</span></td>
                        <td class="text-center">{{ $request->opportunities_count }}</td>
                        <td>{{ $request->created_at->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.v2.form-proposals.show', $request->id) }}" class="btn btn-sm btn-outline-primary" title="Abrir pedido">
                                <i class="bi bi-arrow-up-right-square"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@canroute('admin.v2.form-proposals.store')
<div class="modal fade" id="clientRequestModal" tabindex="-1" aria-hidden="true" @if($openModal) data-client-request-open @endif>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.v2.form-proposals.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_form" value="client-request">
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Novo pedido de importação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        Use quando o cliente pediu diretamente (sem o formulário do site) ou quer um novo carro — as oportunidades ficam agrupadas neste pedido. Todos os campos são opcionais.
                    </p>
                    @include('admin.v2.form-proposals._request-fields', ['formProposal' => null, 'useOld' => $openModal])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-modern"><i class="bi bi-plus"></i> Criar pedido</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('[data-client-request-open]').forEach(modal => new bootstrap.Modal(modal).show());
</script>
@endpush
@endcanroute
