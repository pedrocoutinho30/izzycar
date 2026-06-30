@extends('layouts.admin-v2')
@section('title', 'Editar Página — ' . $page->name)

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door',                       'label' => 'Dashboard',          'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-layout-text-window-reverse',       'label' => 'Gestor de Conteúdo', 'href' => route('admin.v2.cms.index')],
        ['icon' => '', 'label' => $page->name]
    ],
    'title'       => $page->name,
    'subtitle'    => 'Slug: /' . $page->slug,
    'actionHref'  => route('admin.v2.cms.create-block', $page->id),
    'actionLabel' => 'Novo Bloco',
])

@if(session('success'))
<div class="alert alert-success alert-dismissible mb-4">
    <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- ── Configuração da página ── --}}
    <div class="col-lg-4">
        <div class="modern-card sticky-top" style="top:80px">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-gear"></i> Configuração</h5>
            </div>
            <form method="POST" action="{{ route('admin.v2.cms.update-page', $page->id) }}" class="p-3">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nome interno <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $page->name) }}" required>
                    <div class="form-text">Apenas visível no back-office.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text text-muted">/</span>
                        <input type="text" name="slug" class="form-control font-monospace" value="{{ old('slug', $page->slug) }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ordem</label>
                    <input type="number" name="order" class="form-control" value="{{ old('order', $page->order) }}" min="0">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="active" value="1" id="pageActive"
                        {{ $page->active ? 'checked' : '' }}>
                    <label class="form-check-label" for="pageActive">Página activa</label>
                </div>
                <button type="submit" class="btn btn-primary-modern w-100">
                    <i class="bi bi-save me-1"></i> Guardar Configuração
                </button>
            </form>

            <div class="border-top p-3">
                <form method="POST" action="{{ route('admin.v2.cms.destroy-page', $page->id) }}"
                    onsubmit="return confirm('Eliminar a página e todos os seus blocos?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i> Eliminar Página
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Lista de blocos ── --}}
    <div class="col-lg-8">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-layout-three-columns"></i> Blocos
                    <span class="badge bg-secondary ms-1">{{ $blocks->count() }}</span>
                </h5>
                <a href="{{ route('admin.v2.cms.create-block', $page->id) }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-plus-lg me-1"></i> Novo Bloco
                </a>
            </div>

            @if($blocks->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-layout-three-columns fs-2 d-block mb-2"></i>
                Esta página ainda não tem blocos.<br>
                <a href="{{ route('admin.v2.cms.create-block', $page->id) }}">Adicionar o primeiro bloco</a>
            </div>
            @else
            <div id="blockList">
                @foreach($blocks as $block)
                <div class="block-row d-flex align-items-center gap-3 px-3 py-3 border-bottom"
                     data-id="{{ $block->id }}" style="{{ $block->active ? '' : 'opacity:.5' }}">

                    {{-- Drag handle --}}
                    <div class="block-drag text-muted" title="Arrastar para reordenar" style="cursor:grab">
                        <i class="bi bi-grip-vertical fs-5"></i>
                    </div>

                    {{-- Type badge --}}
                    <div class="flex-shrink-0">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.7rem;white-space:nowrap">
                            {{ \App\Models\CmsBlock::TYPES[$block->type] ?? $block->type }}
                        </span>
                    </div>

                    {{-- Info --}}
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate">{{ $block->title ?: $block->name }}</div>
                        @if($block->subtitle)
                        <div class="small text-muted text-truncate">{{ Str::limit($block->subtitle, 70) }}</div>
                        @endif
                        <div class="small text-muted font-monospace">{{ $block->name }}</div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2 flex-shrink-0">
                        <button class="btn btn-icon btn-sm {{ $block->active ? 'btn-success' : 'btn-outline-secondary' }}"
                                onclick="toggleBlock({{ $block->id }}, this)"
                                title="{{ $block->active ? 'Visível — clique para ocultar' : 'Oculto — clique para mostrar' }}">
                            <i class="bi {{ $block->active ? 'bi-eye-fill' : 'bi-eye-slash' }}"></i>
                        </button>
                        <a href="{{ route('admin.v2.cms.edit-block', [$page->id, $block->id]) }}"
                           class="btn btn-icon btn-sm btn-outline-primary" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST"
                            action="{{ route('admin.v2.cms.destroy-block', [$page->id, $block->id]) }}"
                            onsubmit="return confirm('Eliminar este bloco?')" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>

@endsection

@push('styles')
<style>
.block-row { transition: background .1s; }
.block-row:hover { background: #fafafa; }
.block-row:last-child { border-bottom: none !important; }
.block-row.sortable-ghost { opacity: .3; }
.block-drag:active { cursor: grabbing; }
.btn-icon { width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center; }
</style>
@endpush

@push('scripts')
{{-- SortableJS via CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const REORDER_URL = '{{ route("admin.v2.cms.reorder-blocks", $page->id) }}';

// Drag-to-reorder
const list = document.getElementById('blockList');
if (list) {
    new Sortable(list, {
        handle: '.block-drag',
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd() {
            const order = [...list.querySelectorAll('.block-row')].map(r => r.dataset.id);
            fetch(REORDER_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ order }),
            });
        }
    });
}

function toggleBlock(id, btn) {
    const row = btn.closest('.block-row');
    fetch('{{ url("gestao/v2/cms/" . $page->id . "/blocos") }}/' + id + '/toggle', {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': CSRF },
    }).then(r => r.json()).then(data => {
        btn.classList.toggle('btn-success', data.active);
        btn.classList.toggle('btn-outline-secondary', !data.active);
        btn.querySelector('i').className = 'bi ' + (data.active ? 'bi-eye-fill' : 'bi-eye-slash');
        row.style.opacity = data.active ? '' : '.5';
    });
}
</script>
@endpush
