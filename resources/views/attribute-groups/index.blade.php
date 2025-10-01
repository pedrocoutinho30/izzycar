@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h1>Grupos de Atributos</h1>

    <a href="{{ route('attribute-groups.create') }}" class="btn btn-primary mb-3">+ Novo Grupo</a>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif



    <div class="table-responsive  d-none d-md-block">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Ordem</th>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                <tr>
                    <td>{{ $group->order }}</td>
                    <td>{{ $group->name }}</td>
                    <td>
                        <a href="{{ route('attribute-groups.edit', $group->id) }}" class="btn btn-sm btn-outline-secondary me-2" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('attribute-groups.destroy', $group->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')"><i class="fas fa-trash"></i></button>
                        </form>






                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection