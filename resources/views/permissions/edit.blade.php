@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        Editar Permissão
    </h2>

    <form method="POST" action="{{ route('permissions.update',$permission) }}" class="bg-white p-4 rounded shadow-sm">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="name" value="{{ $permission->name }}" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Atualizar</button>
        <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
