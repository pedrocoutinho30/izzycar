@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        Editar Perfil
    </h2>

    <form method="POST" action="{{ route('roles.update',$role) }}" class="bg-white p-4 rounded shadow-sm">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="name" disabled value="{{ $role->name }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Permissões</label><br>


            <div class="row">
                @foreach($permissions as $permission)
                <div class="col-md-4">
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                            class="form-check-input"
                            {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ $permission->name }}</label>
                    </div>
                </div>
                @endforeach
            </div>


        </div>

        <button type="submit" class="btn btn-success">Atualizar</button>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection