@extends('layouts.admin-v2')

@section('title', isset($user) ? 'Editar Utilizador' : 'Novo Utilizador')

@section('content')

<!-- Page Header -->
@php
$existAction = isset($user) ? 'Editar' : 'Criar';
@endphp
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-people', 'label' => 'Utilizadores', 'href' => route('admin.v2.users.index')],
        ['icon' => '', 'label' => $existAction]
    ],
    'title' => $existAction . ' Utilizador',
    'subtitle' => '',
    'actionHref' => '',
    'actionLabel' => ''
])

<!-- Formulário -->
<form action="{{ isset($user) ? route('admin.v2.users.update', $user->id) : route('admin.v2.users.store') }}" 
      method="POST">
    @csrf
    @if(isset($user))
        @method('PUT')
    @endif

    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <!-- Informações Básicas -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-person"></i>
                        Informações do Utilizador
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $user->name ?? '') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Apelido <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" id="last_name" 
                               class="form-control @error('last_name') is-invalid @enderror" 
                               value="{{ old('last_name', $user->last_name ?? '') }}" required>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $user->email ?? '') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="role" class="form-label">Perfil <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">Selecione um perfil</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" 
                                    {{ old('role', isset($user) && $user->roles->first()?->name == $role->name ? $role->name : '') == $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label">
                            Password {{ !isset($user) ? '*' : '(deixe em branco para manter)' }}
                        </label>
                        <input type="password" name="password" id="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               {{ !isset($user) ? 'required' : '' }}>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">
                            Confirmar Password {{ !isset($user) ? '*' : '' }}
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                               class="form-control" 
                               {{ !isset($user) ? 'required' : '' }}>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Lateral -->
        <div class="col-lg-4">
            <!-- Botões de Ação -->
            @include('components.admin.action-card', [
                'cancelButtonHref' => route('admin.v2.users.index'),
                'submitButtonLabel' => isset($user) ? 'Atualizar Utilizador' : 'Criar Utilizador',
                'timestamps' => isset($user) ? [
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ] : null
            ])
        </div>
    </div>
</form>

@endsection
