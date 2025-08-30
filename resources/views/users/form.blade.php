@extends('layouts.admin')

@section('main-content')
<div class="container-fluid px-4 py-5">
    <h2 class="mb-4 fw-bold text-primary">
        {{ isset($user) ? 'Editar Usuário' : 'Adicionar Usuário' }}
    </h2>

        <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" class="bg-white p-4 rounded shadow-s" method="POST">
            @csrf
            @if(isset($user))
            @method('PUT')
            @endif

            <div class="mb-3">
                <label for="name" class="form-label">Nome</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label for="last_name" class="form-label">Sobrenome</label>
                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">{{ isset($user) ? 'Nova Senha (opcional)' : 'Senha' }}</label>
                <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirmar Senha</label>
                <input type="password" name="password_confirmation" class="form-control" {{ isset($user) ? '' : 'required' }}>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" class="form-select form-control" required>
                    <option value="">-- Escolher role --</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}"
                        {{ old('role', isset($user) && $user->roles->first() ? $user->roles->first()->name : '') == $role->name ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-success">{{ isset($user) ? 'Atualizar' : 'Criar' }}</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>

</div>
@endsection