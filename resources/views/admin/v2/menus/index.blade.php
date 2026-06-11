@extends('layouts.admin-v2')

@section('title', 'Menus do Frontend')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-list-nested', 'label' => 'Menus', 'href' => ''],
    ],
    'title'       => 'Menus do Frontend',
    'subtitle'    => 'Gerir os itens de navegação do site',
    'actionHref'  => route('admin.v2.menus.create'),
    'actionLabel' => 'Novo Item',
])

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="modern-card">
    <div class="modern-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="modern-card-title mb-0">
            <i class="bi bi-list-nested me-1"></i> Estrutura do Menu
        </h5>
        <a href="{{ route('admin.v2.menus.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Novo Item
        </a>
    </div>
    <div class="modern-card-body p-0">
        @if($menuItems->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-list-nested display-4 d-block mb-2"></i>
                Ainda não há itens de menu.
                <div class="mt-3">
                    <a href="{{ route('admin.v2.menus.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Criar primeiro item
                    </a>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="menuTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Título</th>
                            <th>URL</th>
                            <th>Tipo</th>
                            <th>Ordem</th>
                            <th>Visível</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menuItems as $menu)
                            {{-- Item principal --}}
                            <tr class="table-secondary">
                                <td class="align-middle text-muted small fw-semibold">{{ $menu->order ?? '—' }}</td>
                                <td class="align-middle fw-bold">
                                    <i class="bi bi-folder2 me-1 text-primary"></i>
                                    {{ $menu->title }}
                                    @if($menu->children->count())
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ $menu->children->count() }} sub</span>
                                    @endif
                                </td>
                                <td class="align-middle text-muted small">{{ $menu->url ?: '—' }}</td>
                                <td class="align-middle">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">Principal</span>
                                </td>
                                <td class="align-middle text-muted small">{{ $menu->order ?? 0 }}</td>
                                <td class="align-middle">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-online" type="checkbox"
                                               data-id="{{ $menu->id }}"
                                               data-url="{{ route('admin.v2.menus.toggle', $menu->id) }}"
                                               {{ $menu->show_online ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="align-middle text-end text-nowrap">
                                    <a href="{{ route('admin.v2.menus.edit', $menu->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.v2.menus.destroy', $menu->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Eliminar este item e todos os seus sub-itens?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            {{-- Sub-itens --}}
                            @foreach($menu->children as $child)
                            <tr>
                                <td class="align-middle text-muted small">{{ $child->order ?? '—' }}</td>
                                <td class="align-middle ps-4">
                                    <i class="bi bi-arrow-return-right me-1 text-muted"></i>
                                    {{ $child->title }}
                                </td>
                                <td class="align-middle text-muted small">{{ $child->url ?: '—' }}</td>
                                <td class="align-middle">
                                    <span class="badge bg-info bg-opacity-10 text-info">Sub-item</span>
                                </td>
                                <td class="align-middle text-muted small">{{ $child->order ?? 0 }}</td>
                                <td class="align-middle">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-online" type="checkbox"
                                               data-id="{{ $child->id }}"
                                               data-url="{{ route('admin.v2.menus.toggle', $child->id) }}"
                                               {{ $child->show_online ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="align-middle text-end text-nowrap">
                                    <a href="{{ route('admin.v2.menus.edit', $child->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.v2.menus.destroy', $child->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Eliminar este sub-item?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.toggle-online').forEach(function(cb) {
    cb.addEventListener('change', function() {
        const self = this;
        fetch(this.dataset.url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => { self.checked = data.show_online; })
        .catch(() => { self.checked = !self.checked; });
    });
});
</script>
@endpush
