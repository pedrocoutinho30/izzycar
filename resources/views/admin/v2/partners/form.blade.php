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
    method="POST" enctype="multipart/form-data">
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

                    <div class="col-12">
                        <label class="form-label">Website / URL</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                            <input type="url" name="url" class="form-control @error('url') is-invalid @enderror"
                                value="{{ old('url', $partner->url ?? '') }}" placeholder="https://www.exemplo.com">
                        </div>
                        @error('url')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
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

        <!-- SIDEBAR -->
        <div class="col-lg-4">
            <!-- Imagem -->
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-image"></i>
                        Imagem / Logótipo
                    </h5>
                </div>
                <div class="partner-img-preview-wrap mb-3">
                    <img id="partnerImgPreview"
                         src="{{ isset($partner) && $partner->image ? asset('storage/' . $partner->image) : 'https://ui-avatars.com/api/?name=' . urlencode($partner->name ?? 'P') . '&background=6e0707&color=fff&bold=true&size=200' }}"
                         alt="Preview"
                         class="partner-img-preview">
                </div>
                <label class="form-label">Carregar imagem</label>
                <input type="file" name="image" id="partnerImage"
                       class="form-control @error('image') is-invalid @enderror"
                       accept="image/jpg,image/jpeg,image/png,image/webp">
                <div class="form-text">JPG, PNG ou WEBP · máx. 2 MB</div>
                @error('image')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror

                <hr class="my-3">

                <div class="form-check form-switch d-flex align-items-center gap-2 ps-0">
                    <input class="form-check-input ms-0" type="checkbox" role="switch"
                           name="show_on_site" id="showOnSite" value="1"
                           {{ old('show_on_site', $partner->show_on_site ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="showOnSite">
                        Mostrar no site
                    </label>
                </div>
                <div class="form-text">Se ativo, o parceiro aparece na página pública do site.</div>
            </div>

            <!-- BOTÕES DE AÇÃO -->
            @include('components.admin.action-card', [
            'cancelButtonHref' => route('admin.v2.partners.index'),
            'submitButtonLabel' => isset($partner) ? 'Atualizar Parceiro' : 'Criar Parceiro',
            'timestamps' => isset($partner) ? [
            'created_at' => $partner->created_at,
            'updated_at' => $partner->updated_at
            ] : null
            ])
        </div>
    </div>
</form>

<script>
document.getElementById('partnerImage').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => document.getElementById('partnerImgPreview').src = e.target.result;
    reader.readAsDataURL(file);
});
</script>

<style>
    .partner-img-preview-wrap {
        width: 100%; aspect-ratio: 1; border-radius: 12px; overflow: hidden;
        background: #f1f3f5; border: 2px dashed #dee2e6;
        display: flex; align-items: center; justify-content: center;
    }
    .partner-img-preview {
        width: 100%; height: 100%; object-fit: contain; border-radius: 10px;
    }

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