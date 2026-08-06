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
                        <label for="phone" class="form-label">Telemóvel</label>
                        <input type="text" name="phone" id="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $user->phone ?? '') }}" placeholder="9XX XXX XXX">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="location" class="form-label">Localização</label>
                        <input type="text" name="location" id="location"
                               class="form-control @error('location') is-invalid @enderror"
                               value="{{ old('location', $user->location ?? '') }}" placeholder="ex: Porto">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="nif" class="form-label">NIF</label>
                        <input type="text" name="nif" id="nif"
                               class="form-control @error('nif') is-invalid @enderror"
                               value="{{ old('nif', $user->nif ?? '') }}">
                        @error('nif')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="iban" class="form-label">IBAN</label>
                        <input type="text" name="iban" id="iban"
                               class="form-control @error('iban') is-invalid @enderror"
                               value="{{ old('iban', $user->iban ?? '') }}" placeholder="PT50...">
                        <div class="form-text">Usado para pagamento de comissões, quando aplicável.</div>
                        @error('iban')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="role" class="form-label">Perfil <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required onchange="toggleAngariadorFields(this.value)">
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

                    <div class="col-12" id="angariadorFields" style="display:none">
                        <div class="row g-3 p-3 mb-2" style="background:var(--admin-light);border-radius:var(--border-radius)">
                            <div class="col-md-6">
                                <label for="referral_code" class="form-label">Código de Angariador</label>
                                <input type="text" name="referral_code" id="referral_code"
                                       class="form-control @error('referral_code') is-invalid @enderror"
                                       value="{{ old('referral_code', $user->referral_code ?? '') }}" placeholder="ex: joao123">
                                <div class="form-text">Usado no link pessoal: izzycar.pt/formulario-importacao?angariador=<strong id="referralPreview">CODIGO</strong></div>
                                @error('referral_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="commission_fixed_value" class="form-label">Comissão</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" name="commission_fixed_value" id="commission_fixed_value"
                                           class="form-control @error('commission_fixed_value') is-invalid @enderror"
                                           value="{{ old('commission_fixed_value', $user->commission_fixed_value ?? (isset($user) ? '' : 100)) }}">
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="form-text">Valor fixo pago por cada proposta convertida das leads deste angariador.</div>
                                @error('commission_fixed_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    @if(!isset($user))
                    <div class="col-12">
                        <div class="alert alert-info d-flex align-items-center gap-2 mb-0">
                            <i class="bi bi-envelope-check fs-5"></i>
                            <div>Não precisa de definir uma password. Ao criar o utilizador, é enviado um email com um link para o próprio definir a sua password no primeiro acesso.</div>
                        </div>
                    </div>
                    @else
                    <div class="col-md-6">
                        <label for="password" class="form-label">
                            Password (deixe em branco para manter)
                        </label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">
                            Confirmar Password
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control">
                    </div>
                    @endif
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

<script>
function toggleAngariadorFields(role) {
    document.getElementById('angariadorFields').style.display = (role === 'angariador') ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', function () {
    toggleAngariadorFields(document.getElementById('role').value);

    const codeInput = document.getElementById('referral_code');
    const preview = document.getElementById('referralPreview');
    function updatePreview() { preview.textContent = codeInput.value || 'CODIGO'; }
    codeInput.addEventListener('input', updatePreview);
    updatePreview();
});
</script>

@endsection
