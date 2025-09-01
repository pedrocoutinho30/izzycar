@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">Permissões</h1>
                <a href="{{ route('permissions.create') }}" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Adicionar Permissão
                </a>
            </div>



            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $permission)
                        <tr>
                            <td>{{ $permission->name }}</td>
                            <td>
                                <a href="{{ route('permissions.edit',$permission) }}" class="btn btn-sm btn-warning"> <i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('permissions.destroy',$permission) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Apagar?')"> <i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection