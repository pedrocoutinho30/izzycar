@extends('layouts.admin-v2')

@section('title', isset($partner) ? 'Editar Parceiro' : 'Novo Parceiro')

@section('content')

@php
$existAction = isset($partner) ? 'Editar' : 'Criar';
@endphp
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-gear', 'label' => 'Parceiros', 'href' => route('admin.v2.partners.index')],
['icon' => '', 'label' => $existAction]
],
'title' => $existAction . ' Parceiro',
'subtitle' => '',
'actionHref' => '',
'actionLabel' => ''
])


<!-- FORMULÁRIO -->
<form action="{{ isset($partner) ? route('admin.v2.partners.update', $partner->id) : route('admin.v2.partners.store') }}"
    method="POST">
    @csrf
    @if(isset($partner))
    @method('PUT')
    @endif

    <!-- Dados Principais -->


    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-gear"></i>
                        Dados do Parceiro
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nome</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $partner->name ?? '') }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nome do Contacto</label>
                        <input type="text" name="contact_name" class="form-control @error('contact_name') is-invalid @enderror"
                            value="{{ old('contact_name', $partner->contact_name ?? '') }}">
                        @error('contact_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">NIF</label>
                        <input type="text" name="vat" class="form-control @error('vat') is-invalid @enderror"
                            value="{{ old('vat', $partner->vat ?? '') }}">
                        @error('vat')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">País</label>
                        <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                            value="{{ old('country', $partner->country ?? 'Portugal') }}">
                        @error('country')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>



            <!-- Contactos -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-gear"></i>
                        Contactos
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $partner->email ?? '') }}">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $partner->phone ?? '') }}">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>


            <!-- Morada -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-gear"></i>
                        Morada
                    </h5>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Endereço</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                            value="{{ old('address', $partner->address ?? '') }}">
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Código Postal</label>
                        <input type="text" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror"
                            value="{{ old('postal_code', $partner->postal_code ?? '') }}" placeholder="0000-000">
                        @error('postal_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Cidade</label>
                        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                            value="{{ old('city', $partner->city ?? '') }}">
                        @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTÕES DE AÇÃO -->
        <div class="col-lg-4">
            <!-- BOTÕES DE AÇÃO -->
            {{-- SECÇÃO: Ações --}}
            @include('components.admin.action-card', [
            'cancelButtonHref' => route('admin.v2.partners.index'),
            'submitButtonLabel' => isset($partner) ? 'Atualizar Parceiro' : 'Criar Parceiro',
            'timestamps' => isset($partner) ? [
            'created_at' => $partner->created_at,
            'updated_at' => $partner->updated_at
            ] : null
            ])
        </div>
</form>

<style>
    .form-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 0;
        overflow: hidden;
    }

    .form-section {
        padding: 2rem;
        border-bottom: 1px solid #eee;
    }

    .form-section:last-of-type {
        border-bottom: none;
    }

    .form-section-header {
        margin-bottom: 1.5rem;
    }

    .form-section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-section-title i {
        color: var(--admin-primary);
    }

    .form-label.required::after {
        content: ' *';
        color: #dc3545;
    }

    .form-actions {
        padding: 2rem;
        background: #f8f9fa;
        display: flex;
        gap: 1rem;
        justify-content: flex-start;
    }

    @media (max-width: 768px) {
        .form-section {
            padding: 1.5rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
        }
    }
</style>
@endsection