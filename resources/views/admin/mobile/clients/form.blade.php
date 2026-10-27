@extends('layouts.admin-mobile')

@php
    $editar = $client->exists;
    $valor = fn (string $campo) => old($campo, $client->{$campo});
    $data = fn (string $campo) => old($campo, $client->{$campo}?->format('Y-m-d'));
    // Tipo: a BD tem "Particular" e "particular"; compara-se sem maiúsculas para não perder o valor.
    $tipo = mb_strtolower((string) $valor('client_type'));
    $origens = ['Olx', 'StandVirtual', 'Facebook', 'Instagram', 'Amigo', 'Google', 'Outro'];
    $origem = $valor('origin');
    // Consentimento: "" (por definir), "1" ou "0".
    $consentimento = function (string $campo) use ($client) {
        $v = old($campo, $client->exists ? $client->{$campo} : null);

        return $v === null || $v === '' ? '' : ((string) (int) (bool) $v);
    };
    $voltar = $editar ? route('admin.mobile.clients.show', $client->id) : route('admin.mobile.clients.index');
@endphp

@section('title', $editar ? 'Editar cliente' : 'Novo cliente')

@section('header_actions')
    <a href="{{ $voltar }}" class="order-first m-topbar-back" aria-label="Voltar"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $erro)<div>{{ $erro }}</div>@endforeach
        </div>
    @endif

    <form action="{{ $editar ? route('admin.mobile.clients.update', $client->id) : route('admin.mobile.clients.store') }}" method="POST" class="d-grid gap-3" style="font-size:16px">
        @csrf
        @if($editar) @method('PUT') @endif

        {{-- Identificação --}}
        <div class="m-card d-grid gap-3 mb-0">
            <div class="fw-semibold"><i class="bi bi-person me-1"></i> Dados pessoais</div>

            <div>
                <label for="client-name" class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
                <input type="text" id="client-name" name="name" value="{{ $valor('name') }}" class="form-control @error('name') is-invalid @enderror"
                       required maxlength="255" autocomplete="name" autocapitalize="words" enterkeyhint="next">
            </div>

            <div>
                <label for="client-type" class="form-label fw-semibold">Tipo</label>
                <select id="client-type" name="client_type" class="form-select">
                    <option value="">Selecione...</option>
                    <option value="particular" @selected($tipo === 'particular')>Particular</option>
                    <option value="empresa" @selected($tipo === 'empresa')>Empresa</option>
                </select>
            </div>

            <div>
                <label for="client-gender" class="form-label fw-semibold">Género</label>
                <select id="client-gender" name="gender" class="form-select">
                    <option value="">Selecione...</option>
                    <option value="masculino" @selected($valor('gender') === 'masculino')>Masculino</option>
                    <option value="feminino" @selected($valor('gender') === 'feminino')>Feminino</option>
                </select>
            </div>

            <div>
                <label for="client-birth" class="form-label fw-semibold">Data de nascimento</label>
                <input type="date" id="client-birth" name="birth_date" value="{{ $data('birth_date') }}" class="form-control @error('birth_date') is-invalid @enderror">
            </div>

            <div>
                <label for="client-vat" class="form-label fw-semibold">NIF</label>
                <input type="text" id="client-vat" name="vat_number" value="{{ $valor('vat_number') }}" class="form-control @error('vat_number') is-invalid @enderror"
                       maxlength="9" inputmode="numeric" autocomplete="off" enterkeyhint="next">
            </div>

            <div>
                <label for="client-id-number" class="form-label fw-semibold">Nº de identificação (CC)</label>
                <input type="text" id="client-id-number" name="identification_number" value="{{ $valor('identification_number') }}"
                       class="form-control @error('identification_number') is-invalid @enderror" maxlength="50" autocomplete="off" autocapitalize="characters">
            </div>

            <div>
                <label for="client-id-validity" class="form-label fw-semibold">Validade do documento</label>
                <input type="date" id="client-id-validity" name="validate_identification_number" value="{{ $data('validate_identification_number') }}"
                       class="form-control @error('validate_identification_number') is-invalid @enderror">
            </div>
        </div>

        {{-- Contacto --}}
        <div class="m-card d-grid gap-3 mb-0">
            <div class="fw-semibold"><i class="bi bi-telephone me-1"></i> Contacto</div>

            <div>
                <label for="client-email" class="form-label fw-semibold">Email</label>
                <input type="email" id="client-email" name="email" value="{{ $valor('email') }}" class="form-control @error('email') is-invalid @enderror"
                       maxlength="255" inputmode="email" autocomplete="email" autocapitalize="none" enterkeyhint="next">
            </div>

            <div>
                <label for="client-phone" class="form-label fw-semibold">Telefone</label>
                <input type="tel" id="client-phone" name="phone" value="{{ $valor('phone') }}" class="form-control @error('phone') is-invalid @enderror"
                       maxlength="50" inputmode="tel" autocomplete="tel" enterkeyhint="next">
            </div>

            <div>
                <label for="client-language" class="form-label fw-semibold">Idioma</label>
                <select id="client-language" name="language" class="form-select">
                    <option value="pt" @selected(($valor('language') ?? 'pt') === 'pt')>Português</option>
                    <option value="en" @selected($valor('language') === 'en')>English</option>
                </select>
            </div>
        </div>

        {{-- Morada --}}
        <div class="m-card d-grid gap-3 mb-0">
            <div class="fw-semibold"><i class="bi bi-geo-alt me-1"></i> Morada</div>

            <div>
                <label for="client-address" class="form-label fw-semibold">Morada</label>
                <input type="text" id="client-address" name="address" value="{{ $valor('address') }}" class="form-control @error('address') is-invalid @enderror"
                       autocomplete="street-address">
            </div>

            <div>
                <label for="client-postal" class="form-label fw-semibold">Código postal</label>
                <input type="text" id="client-postal" name="postal_code" value="{{ $valor('postal_code') }}" class="form-control @error('postal_code') is-invalid @enderror"
                       maxlength="20" placeholder="0000-000" autocomplete="postal-code">
            </div>

            <div>
                <label for="client-city" class="form-label fw-semibold">Localidade</label>
                <input type="text" id="client-city" name="city" value="{{ $valor('city') }}" class="form-control @error('city') is-invalid @enderror"
                       maxlength="100" autocomplete="address-level2">
            </div>
        </div>

        {{-- Outros --}}
        <div class="m-card d-grid gap-3 mb-0">
            <div class="fw-semibold"><i class="bi bi-info-circle me-1"></i> Outras informações</div>

            <div>
                <label for="client-origin" class="form-label fw-semibold">Origem</label>
                <select id="client-origin" name="origin" class="form-select">
                    <option value="">Como conheceu a empresa?</option>
                    {{-- Uma origem antiga fora da lista (ex. "Manual BO") mantém-se ao guardar. --}}
                    @if(filled($origem) && !in_array($origem, $origens, true))
                        <option value="{{ $origem }}" selected>{{ $origem }}</option>
                    @endif
                    @foreach($origens as $opcao)
                        <option value="{{ $opcao }}" @selected($origem === $opcao)>{{ $opcao }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="client-consent-data" class="form-label fw-semibold">Consentimento de tratamento de dados</label>
                <select id="client-consent-data" name="data_processing_consent" class="form-select">
                    <option value="">Selecione...</option>
                    <option value="1" @selected($consentimento('data_processing_consent') === '1')>Sim</option>
                    <option value="0" @selected($consentimento('data_processing_consent') === '0')>Não</option>
                </select>
            </div>

            <div>
                <label for="client-consent-newsletter" class="form-label fw-semibold">Consentimento de newsletter</label>
                <select id="client-consent-newsletter" name="newsletter_consent" class="form-select">
                    <option value="">Selecione...</option>
                    <option value="1" @selected($consentimento('newsletter_consent') === '1')>Sim</option>
                    <option value="0" @selected($consentimento('newsletter_consent') === '0')>Não</option>
                </select>
            </div>

            <div>
                <label for="client-observation" class="form-label fw-semibold">Observações</label>
                <textarea id="client-observation" name="observation" rows="3" class="form-control">{{ $valor('observation') }}</textarea>
            </div>
        </div>

        @if($errors->has('duplicate'))
            <div class="form-check m-form-check">
                <input class="form-check-input" type="checkbox" name="confirm_duplicate" value="1" id="confirm-duplicate">
                <label class="form-check-label" for="confirm-duplicate">Criar mesmo assim</label>
            </div>
        @endif

        <button type="submit" class="btn btn-primary btn-lg fw-semibold">{{ $editar ? 'Guardar alterações' : 'Criar cliente' }}</button>
    </form>
@endsection
