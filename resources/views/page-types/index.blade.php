@extends('layouts.admin')
@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Tipos de Página</h1>
                <a href="{{ route('page-types.create') }}" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Criar Novo Tipo
                </a>
            </div>

            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($pageTypes->isEmpty())
            <p>Nenhum tipo de página encontrado.</p>
            @else
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Nome</th>
                        <th>Campos</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pageTypes as $pageType)
                    <tr>
                        <td>{{ $pageType->name }}</td>
                        <td>{{ $pageType->fields->count() }}</td>
                        <td>
                            <a href="{{ route('page-types.edit', $pageType) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('page-types.destroy', $pageType) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja eliminar este tipo de página?');">
                                @csrf
                                @method('DELETE')
                                @if($pageType->pages->count() > 0)
                                <button type="button" class="btn btn-sm btn-danger" disabled title="Existem páginas associadas"> <i class="fas fa-trash"></i></button>
                                @else
                                <button type="submit" class="btn btn-sm btn-danger"> <i class="fas fa-trash"></i></button>
                                @endif
                            </form>
                            <form action="{{ route('page-types.duplicate', $pageType->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-secondary" title="Duplicar">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $pageTypes->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection