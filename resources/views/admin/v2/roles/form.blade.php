@extends('layouts.admin-v2')

@section('title', $role ? 'Editar Perfil' : 'Novo Perfil')

@section('content')

<!-- Page Header -->
@php
$existAction = $role ? 'Editar' : 'Criar';
$isProtected = $role && in_array($role->name, $protectedRoles, true);
$isAdmin = $role?->name === 'admin';
@endphp
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-person-badge', 'label' => 'Perfis', 'href' => route('admin.v2.roles.index')],
        ['icon' => '', 'label' => $role ? $role->name : $existAction]
    ],
    'title' => $role ? 'Perfil: ' . $role->name : 'Novo Perfil',
    'subtitle' => 'As permissões de um utilizador resultam da soma dos seus perfis',
    'actionHref' => '',
    'actionLabel' => ''
])

@if($errors->any())
<div class="alert alert-danger mb-3">
    <i class="bi bi-exclamation-triangle me-2"></i>Verifique os campos assinalados.
    <ul class="mb-0 mt-1 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<!-- Formulário -->
<form action="{{ $role ? route('admin.v2.roles.update', $role->id) : route('admin.v2.roles.store') }}"
      method="POST">
    @csrf
    @if($role)
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
                               value="{{ old('name', $role->name ?? '') }}" required @readonly($isProtected)>
                        @if($isProtected)
                            <div class="form-text">Este perfil é usado pelo sistema — o nome não pode ser alterado.</div>
                        @endif
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @if($isAdmin)
            <div class="alert alert-info">
                <i class="bi bi-shield-check me-2"></i>
                O perfil <strong>admin</strong> tem <strong>acesso total</strong> a todos os módulos, ações e âmbitos — incluindo permissões que venham a ser criadas. Não precisa de configuração.
            </div>
            @endif
        </div>

        <!-- Coluna Lateral -->
        <div class="col-lg-4">
            @include('components.admin.action-card', [
                'cancelButtonHref' => route('admin.v2.roles.index'),
                'submitButtonLabel' => $role ? 'Atualizar Perfil' : 'Criar Perfil',
                'timestamps' => $role ? [
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at
                ] : null
            ])
        </div>

        @unless($isAdmin)
        <div class="col-12">
            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                <strong>Todos</strong>: todos os registos. <strong>Próprios</strong>: só os registos associados ao utilizador (ex. leads de que é angariador).
            </p>
            @include('admin.v2.roles._matrix', ['grants' => $grants, 'readonly' => false])
        </div>
        @endunless
    </div>
</form>

@endsection
