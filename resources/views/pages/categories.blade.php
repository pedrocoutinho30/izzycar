@extends('layouts.admin')
@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Categorias</h1>
                <a href="{{ route('pages.create', ['page_type_id' => $pageType->id ?? null]) }}" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Adicionar Categoria
                </a>
            </div>

            @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
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