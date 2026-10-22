{{-- Alternar lista / kanban --}}
<div class="d-flex justify-content-end mb-3 gap-2">
    <a href="{{ route('admin.v2.converted-proposals.index') }}" class="btn btn-sm {{ ($kanbanActive ?? false) ? 'btn-outline-secondary' : 'btn-primary-modern active' }}">
        <i class="bi bi-list-ul me-1"></i> Vista de Lista
    </a>
    <a href="{{ route('admin.v2.converted-proposals.kanban') }}" class="btn btn-sm {{ ($kanbanActive ?? false) ? 'btn-primary-modern active' : 'btn-outline-secondary' }}">
        <i class="bi bi-kanban me-1"></i> Pipeline Kanban
    </a>
</div>
