@extends('layouts.admin')
@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Páginas</h1>
                <a href="{{ route('pages.create') }}" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Adicionar Página
                </a>
            </div>

            @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="mb-4">
                <!-- <form method="GET" action="{{ route('pages.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="type" class="form-label">Filtrar por Tipo de Página</label>
                        <select name="type" id="type" class="form-control form-select">
                            <option value="">Todos</option>
                            @foreach ($pageTypes as $type)
                            <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary shadow-sm">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                    </div>
                    <div class="col-md-auto">
                        <a href="{{ route('pages.index') }}" class="btn btn-outline-secondary shadow-sm">
                            <i class="fas fa-times me-1"></i> Limpar
                        </a>
                    </div>
                </form> -->
                <div class="mb-4">
                    <ul class="nav nav-tabs" id="pageTypeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ request('type') == '' ? 'active' : '' }}"
                                href="{{ route('pages.index') }}">
                                Todos
                            </a>
                        </li>
                        @foreach ($pageTypes as $type)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ request('type') == $type->id ? 'active' : '' }}"
                                href="{{ route('pages.index', ['type' => $type->id]) }}">
                                {{ $type->name }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Slug</th>
                            <th>Tipo</th>
                            <th>Criada em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pages as $page)
                        @if ($page->parent_id == null)
                        <tr>
                            <td>{{ $page->id }}</td>
                            <td>{{ $page->title }}</td>
                            <td>{{ $page->slug }}</td>
                            <td>{{ $page->pageType->name ?? '-' }}</td>
                            <td>{{ $page->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Ações">
                                    <a href="{{ route('pages.edit', $page->id) }}" class="btn btn-sm btn-outline-secondary me-2" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('pages.destroy', $page->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                                        @csrf
                                        @method('DELETE')


                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @foreach ($page->children as $child)
                        <tr>
                            <td>{{ $child->id }}</td>
                            <td>{{ $child->title }}</td>
                            <td>{{ $child->slug }}</td>
                            <td>{{ $child->pageType->name ?? '-' }}</td>
                            <td>{{ $child->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Ações">
                                    <a href="{{ route('pages.edit', $child->id) }}" class="btn btn-sm btn-outline-secondary me-2" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('pages.destroy', $child->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @else

                        @endif
                        @empty
                        <tr>
                            <td colspan="6">Nenhuma página encontrada.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection