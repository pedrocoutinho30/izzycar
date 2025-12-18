@extends('layouts.admin-v2')

@section('title', isset($role) ? 'Editar Perfil' : 'Novo Perfil')

@section('content')

<!-- Page Header -->
@php
$existAction = isset($role) ? 'Editar' : 'Criar';
@endphp
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-person-badge', 'label' => 'Perfis', 'href' => route('admin.v2.roles.index')],
        ['icon' => '', 'label' => $existAction]
    ],
    'title' => $existAction . ' Perfil',
    'subtitle' => '',
    'actionHref' => '',
    'actionLabel' => ''
])

<!-- Formulário -->
<form action="{{ isset($role) ? route('admin.v2.roles.update', $role->id) : route('admin.v2.roles.store') }}" 
      method="POST">
    @csrf
    @if(isset($role))
        @method('PUT')
    @endif

    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <!-- Informações Básicas -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-person-badge"></i>
                        Informações do Perfil
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label">Nome do Perfil <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $role->name ?? '') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Permissões -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-key"></i>
                        Permissões
                    </h5>
                </div>

                <div class="row g-3">
                    @if($permissions->count() > 0)
                        @foreach($permissions as $permission)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="permissions[]" 
                                       value="{{ $permission->id }}" 
                                       id="permission{{ $permission->id }}"
                                       {{ isset($role) && $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Não existem permissões criadas. <a href="{{ route('admin.v2.permissions.create') }}">Criar primeira permissão</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Coluna Lateral -->
        <div class="col-lg-4">
            @include('components.admin.action-card', [
                'cancelButtonHref' => route('admin.v2.roles.index'),
                'submitButtonLabel' => isset($role) ? 'Atualizar Perfil' : 'Criar Perfil',
                'timestamps' => isset($role) ? [
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at
                ] : null
            ])
        </div>
    </div>
</form>

@endsection
