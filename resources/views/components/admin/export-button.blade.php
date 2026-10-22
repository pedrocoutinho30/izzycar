{{--
    COMPONENTE: Botão "Exportar Excel"

    USO:
    @include('components.admin.export-button', [
        'type' => 'leads',                                   // chave do App\Exports\ExportRegistry
        'action' => route('admin.v2.leads.export'),          // rota GET do módulo
        'filters' => request()->only(['search', 'lead_status']),  // filtros atuais da listagem
    ])

    DESCRIÇÃO:
    Abre um modal com os campos do catálogo por grupo (os por defeito vêm
    marcados). A ordem das linhas no modal é a ordem das colunas no ficheiro:
    ↑/↓ reordenam dentro do grupo. O GET leva fields[] pela ordem do DOM e
    os filtros atuais da listagem (exporta todos os resultados, não só a página).
--}}
@php
    $definition = \App\Exports\ExportRegistry::get($type);
    $uid = 'export-' . $type;
@endphp

@if($definition)
<button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#{{ $uid }}-modal"
        title="Exportar {{ mb_strtolower($definition->title()) }} para Excel (XLSX)">
    <i class="bi bi-file-earmark-excel me-1"></i> Exportar Excel
</button>

<div class="modal fade" id="{{ $uid }}-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form class="modal-content" method="GET" action="{{ $action }}" id="{{ $uid }}-form">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-excel me-2"></i>Exportar {{ mb_strtolower($definition->title()) }} para Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                {{-- Filtros atuais da listagem: o export aplica-os a todos os resultados --}}
                @foreach($filters as $name => $value)
                    @if(is_scalar($value) && $value !== '')
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                @endforeach

                <p class="text-muted small mb-3">
                    Exporta todos os resultados dos filtros atuais, não só a página visível.
                    Escolha as colunas e a ordem em que aparecem no ficheiro.
                </p>

                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-export-action="all">Selecionar todos</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-export-action="none">Limpar</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-export-action="default">Repor predefinição</button>
                </div>

                @foreach($definition->groups() as $group => $fields)
                    <div class="mb-3" data-export-group>
                        <h6 class="text-uppercase text-muted small fw-semibold border-bottom pb-1 mb-2">{{ $group }}</h6>
                        <ul class="list-unstyled mb-0" data-export-list>
                            @foreach($fields as $field)
                                <li class="d-flex align-items-center gap-2 py-1" data-export-item data-default="{{ $field->default ? 1 : 0 }}">
                                    <div class="form-check flex-grow-1 mb-0">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="{{ $field->key }}"
                                               id="{{ $uid }}-{{ $field->key }}" @checked($field->default)>
                                        <label class="form-check-label" for="{{ $uid }}-{{ $field->key }}">
                                            {{ $field->label }}
                                            @if($field->sensitive)
                                                <span class="badge bg-warning text-dark ms-1" title="Dado pessoal sensível">sensível</span>
                                            @endif
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" data-export-move="up" title="Subir" aria-label="Subir {{ $field->label }}"><i class="bi bi-arrow-up"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" data-export-move="down" title="Descer" aria-label="Descer {{ $field->label }}"><i class="bi bi-arrow-down"></i></button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

                <div class="alert alert-danger py-2 small d-none" data-export-error>Escolha pelo menos uma coluna.</div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary-modern"><i class="bi bi-download me-1"></i> Exportar</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const form = document.getElementById(@json($uid . '-form'));
    if (!form) return;
    const modalEl = document.getElementById(@json($uid . '-modal'));
    const error = form.querySelector('[data-export-error]');
    const boxes = () => form.querySelectorAll('input[name="fields[]"]');

    // Subir/descer dentro do grupo; a ordem final no DOM é a ordem das colunas.
    form.addEventListener('click', function (e) {
        const move = e.target.closest('[data-export-move]');
        if (move) {
            const item = move.closest('[data-export-item]');
            if (move.dataset.exportMove === 'up' && item.previousElementSibling) {
                item.parentNode.insertBefore(item, item.previousElementSibling);
            } else if (move.dataset.exportMove === 'down' && item.nextElementSibling) {
                item.parentNode.insertBefore(item.nextElementSibling, item);
            }
            return;
        }

        const action = e.target.closest('[data-export-action]')?.dataset.exportAction;
        if (!action) return;
        form.querySelectorAll('[data-export-item]').forEach(function (item) {
            item.querySelector('input[type=checkbox]').checked =
                action === 'all' ? true : (action === 'default' ? item.dataset.default === '1' : false);
        });
        error.classList.add('d-none');
    });

    form.addEventListener('submit', function (e) {
        if (![...boxes()].some(b => b.checked)) {
            e.preventDefault();
            error.classList.remove('d-none');
            return;
        }
        error.classList.add('d-none');
        // O download não sai da página: fecha o modal logo a seguir.
        setTimeout(() => bootstrap.Modal.getInstance(modalEl)?.hide(), 400);
    });
})();
</script>
@endpush
@endif
