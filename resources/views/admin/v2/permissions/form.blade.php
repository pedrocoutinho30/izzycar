@extends('layouts.admin-v2')

@section('title', isset($permission) ? 'Editar Permissão' : 'Nova Permissão')

@section('content')

<!-- Page Header -->
@php
$existAction = isset($permission) ? 'Editar' : 'Criar';
@endphp
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-key', 'label' => 'Permissões', 'href' => route('admin.v2.permissions.index')],
        ['icon' => '', 'label' => $existAction]
    ],
    'title' => $existAction . ' Permissão',
    'subtitle' => '',
    'actionHref' => '',
    'actionLabel' => ''
])

<!-- Formulário -->
<form action="{{ isset($permission) ? route('admin.v2.permissions.update', $permission->id) : route('admin.v2.permissions.store') }}" 
      method="POST">
    @csrf
    @if(isset($permission))
        @method('PUT')
    @endif

    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-key"></i>
                        Informações da Permissão
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label">Nome da Permissão <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $permission->name ?? '') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Ex: view-users, edit-proposals, delete-vehicles</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Lateral -->
        <div class="col-lg-4">
            @include('components.admin.action-card', [
                'cancelButtonHref' => route('admin.v2.permissions.index'),
                'submitButtonLabel' => isset($permission) ? 'Atualizar Permissão' : 'Criar Permissão',
                'timestamps' => isset($permission) ? [
                    'created_at' => $permission->created_at,
                    'updated_at' => $permission->updated_at
                ] : null
            ])
        </div>
    </div>
</form>

@endsection
