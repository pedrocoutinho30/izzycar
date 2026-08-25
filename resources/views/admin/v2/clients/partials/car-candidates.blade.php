{{--
    "Carros em Análise" — usado tanto em admin/v2/clients/show.blade.php
    como em admin/v2/leads/show.blade.php. Espera sempre uma variável
    $client (em leads/show, passar ['client' => $lead] no @include).
--}}
@php
    $isCarCandidateEditError = $errors->any() && old('edit_id');
@endphp

<div class="modern-card" id="car-candidates">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-car-front"></i> Carros em Análise</h5>
        <button type="button" class="btn btn-sm btn-primary-modern" data-bs-toggle="modal" data-bs-target="#carCandidateAddModal">
            <i class="bi bi-plus"></i> Adicionar
        </button>
    </div>

    <div id="carCandidatesList" data-client-id="{{ $client->id }}">
        @forelse($client->carCandidates as $candidate)
        <div class="car-candidate-item" data-id="{{ $candidate->id }}"
             data-title="{{ $candidate->title }}"
             data-link="{{ $candidate->link }}"
             data-price="{{ $candidate->price }}"
             data-description="{{ $candidate->description }}"
             data-status="{{ $candidate->status }}"
             data-notes="{{ $candidate->notes }}">
            <div class="car-candidate-item__drag" title="Arrastar para reordenar"><i class="bi bi-grip-vertical"></i></div>
            <div class="car-candidate-item__body">
                <div class="car-candidate-item__header">
                    <strong>{{ $candidate->title }}</strong>
                    <span class="badge bg-{{ \App\Models\CarCandidate::STATUS_OPTIONS[$candidate->status]['color'] ?? 'secondary' }}">
                        {{ \App\Models\CarCandidate::STATUS_OPTIONS[$candidate->status]['label'] ?? $candidate->status }}
                    </span>
                    @if($candidate->price)
                    <span class="text-success fw-semibold">{{ number_format($candidate->price, 0, ',', '.') }} €</span>
                    @endif
                </div>
                @if($candidate->description)
                <div class="car-candidate-item__desc text-muted small">{{ $candidate->description }}</div>
                @endif
                @if($candidate->notes)
                <div class="car-candidate-item__notes small"><i class="bi bi-sticky-fill"></i> {{ $candidate->notes }}</div>
                @endif
            </div>
            <div class="car-candidate-item__actions">
                @if($candidate->link)
                <a href="{{ $candidate->link }}" target="_blank" class="btn btn-icon btn-secondary-modern" title="Abrir anúncio">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
                @endif
                <button type="button" class="btn btn-icon btn-primary-modern" title="Editar" onclick="openCarCandidateEdit(this)">
                    <i class="bi bi-pencil"></i>
                </button>
                <form action="{{ route('admin.v2.car-candidates.destroy', [$client->id, $candidate->id]) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Remover este carro da lista?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-icon btn-danger-modern" title="Remover">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="p-4 text-center text-muted small">Ainda não há carros em análise para este cliente.</div>
        @endforelse
    </div>
</div>

{{-- Modal: Adicionar --}}
<div class="modal fade" id="carCandidateAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.v2.car-candidates.store', $client->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Carro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @if(!$isCarCandidateEditError) @error('title') is-invalid @enderror @endif"
                                   value="{{ $isCarCandidateEditError ? '' : old('title') }}" placeholder="Ex: BMW 320d 2019" required>
                            @if(!$isCarCandidateEditError)
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Preço (€)</label>
                            <input type="number" step="0.01" min="0" name="price" class="form-control @if(!$isCarCandidateEditError) @error('price') is-invalid @enderror @endif"
                                   value="{{ $isCarCandidateEditError ? '' : old('price') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Link do Anúncio</label>
                            <input type="url" name="link" class="form-control @if(!$isCarCandidateEditError) @error('link') is-invalid @enderror @endif"
                                   value="{{ $isCarCandidateEditError ? '' : old('link') }}" placeholder="https://...">
                            @if(!$isCarCandidateEditError)
                            @error('link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="form-label">Estado da Conversa</label>
                            <select name="status" class="form-select">
                                @foreach(\App\Models\CarCandidate::STATUS_OPTIONS as $key => $opt)
                                <option value="{{ $key }}" {{ old('status', 'primeiro_contacto') === $key ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea name="description" class="form-control" rows="2">{{ $isCarCandidateEditError ? '' : old('description') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea name="notes" class="form-control" rows="2">{{ $isCarCandidateEditError ? '' : old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Adicionar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Editar (partilhado, preenchido via JS) --}}
<div class="modal fade" id="carCandidateEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="carCandidateEditForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_id" id="carCandidateEditId">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Carro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($isCarCandidateEditError)
                    <div class="alert alert-danger py-2 px-3 small">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="carCandidateEditTitle" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Preço (€)</label>
                            <input type="number" step="0.01" min="0" name="price" id="carCandidateEditPrice" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Link do Anúncio</label>
                            <input type="url" name="link" id="carCandidateEditLink" class="form-control" placeholder="https://...">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Estado da Conversa</label>
                            <select name="status" id="carCandidateEditStatus" class="form-select">
                                @foreach(\App\Models\CarCandidate::STATUS_OPTIONS as $key => $opt)
                                <option value="{{ $key }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea name="description" id="carCandidateEditDescription" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea name="notes" id="carCandidateEditNotes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .car-candidate-item {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .85rem 1.25rem;
        border-bottom: 1px solid #f0f0f0;
    }
    .car-candidate-item:last-child { border-bottom: none; }
    .car-candidate-item__drag {
        cursor: grab;
        color: #bbb;
        padding-top: .2rem;
    }
    .car-candidate-item.sortable-ghost { opacity: .4; }
    .car-candidate-item__body { flex: 1; min-width: 0; }
    .car-candidate-item__header {
        display: flex;
        align-items: center;
        gap: .6rem;
        flex-wrap: wrap;
    }
    .car-candidate-item__desc { margin-top: .2rem; }
    .car-candidate-item__notes { margin-top: .2rem; color: #6c757d; }
    .car-candidate-item__actions {
        display: flex;
        gap: .35rem;
        flex-shrink: 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('carCandidatesList');
    if (list && !list._sortable) {
        list._sortable = Sortable.create(list, {
            animation: 150,
            handle: '.car-candidate-item__drag',
            ghostClass: 'sortable-ghost',
            onEnd: function () {
                const order = [...list.querySelectorAll('[data-id]')].map(el => el.dataset.id);
                fetch('{{ route("admin.v2.car-candidates.reorder", $client->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ order }),
                }).catch(() => {});
            }
        });
    }

    @if($isCarCandidateEditError)
    document.getElementById('carCandidateEditTitle').value = @json(old('title'));
    document.getElementById('carCandidateEditPrice').value = @json(old('price'));
    document.getElementById('carCandidateEditLink').value = @json(old('link'));
    document.getElementById('carCandidateEditStatus').value = @json(old('status'));
    document.getElementById('carCandidateEditDescription').value = @json(old('description'));
    document.getElementById('carCandidateEditNotes').value = @json(old('notes'));
    document.getElementById('carCandidateEditId').value = @json(old('edit_id'));
    document.getElementById('carCandidateEditForm').action =
        '{{ url("gestao/v2/clients/{$client->id}/car-candidates") }}/' + @json(old('edit_id'));
    new bootstrap.Modal(document.getElementById('carCandidateEditModal')).show();
    @elseif($errors->any())
    new bootstrap.Modal(document.getElementById('carCandidateAddModal')).show();
    @endif
});

function openCarCandidateEdit(btn) {
    const item = btn.closest('.car-candidate-item');
    const clientId = document.getElementById('carCandidatesList').dataset.clientId;

    document.getElementById('carCandidateEditId').value = item.dataset.id;
    document.getElementById('carCandidateEditTitle').value = item.dataset.title || '';
    document.getElementById('carCandidateEditPrice').value = item.dataset.price || '';
    document.getElementById('carCandidateEditLink').value = item.dataset.link || '';
    document.getElementById('carCandidateEditStatus').value = item.dataset.status || 'primeiro_contacto';
    document.getElementById('carCandidateEditDescription').value = item.dataset.description || '';
    document.getElementById('carCandidateEditNotes').value = item.dataset.notes || '';
    document.getElementById('carCandidateEditForm').action =
        '{{ url("gestao/v2/clients") }}/' + clientId + '/car-candidates/' + item.dataset.id;

    new bootstrap.Modal(document.getElementById('carCandidateEditModal')).show();
}
</script>
@endpush
