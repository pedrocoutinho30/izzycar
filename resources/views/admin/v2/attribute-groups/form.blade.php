@extends('layouts.admin-v2')

@section('title', isset($group) ? 'Editar Grupo' : 'Novo Grupo')

@section('content')

<!-- Page Header -->
@php
$existAction = isset($group) ? 'Editar' : 'Criar';
@endphp
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-folder', 'label' => 'Grupos de Atributos', 'href' => route('admin.v2.attribute-groups.index')],
        ['icon' => '', 'label' => $existAction]
    ],
    'title' => $existAction . ' Grupo de Atributos',
    'subtitle' => '',
    'actionHref' => '',
    'actionLabel' => ''
])

<!-- Formulário -->
<form action="{{ isset($group) ? route('admin.v2.attribute-groups.update', $group->id) : route('admin.v2.attribute-groups.store') }}" 
      method="POST">
    @csrf
    @if(isset($group))
        @method('PUT')
    @endif

    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-folder"></i>
                        Informações do Grupo
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $group->name ?? '') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="order" class="form-label">Ordem <span class="text-danger">*</span></label>
                        <input type="number" name="order" id="order" 
                               class="form-control @error('order') is-invalid @enderror" 
                               value="{{ old('order', $group->order ?? 0) }}" required>
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Lateral -->
        <div class="col-lg-4">
            @include('components.admin.action-card', [
                'cancelButtonHref' => route('admin.v2.attribute-groups.index'),
                'submitButtonLabel' => isset($group) ? 'Atualizar Grupo' : 'Criar Grupo',
                'timestamps' => isset($group) ? [
                    'created_at' => $group->created_at,
                    'updated_at' => $group->updated_at
                ] : null
            ])
        </div>
    </div>
</form>

@endsection
