{{-- Campos de um contacto (contact[...]), usados no formulário do vendedor,
     nos modais de contacto da ficha e nos modais rápidos das Oportunidades.
     $contact pode ser null; $useOld = false ignora os valores da sessão (ex.
     modais de edição que não foram os submetidos). --}}
@php
    $c = $contact ?? null;
    $useOld = $useOld ?? true;
    $showPrimary = $showPrimary ?? true;
    $required = $required ?? true;
    $idPrefix = $idPrefix ?? 'contact';
    $val = fn ($field, $default = null) => $useOld ? old("contact.{$field}", $default) : $default;
    $err = fn ($field) => $useOld && $errors->has("contact.{$field}") ? 'is-invalid' : '';
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="{{ $idPrefix }}Name">Nome @if($required)<span class="text-danger">*</span>@endif</label>
        <input type="text" name="contact[name]" id="{{ $idPrefix }}Name" class="form-control {{ $err('name') }}" value="{{ $val('name', $c?->name) }}" maxlength="255" @if($required) required @endif>
        @if($useOld) @error('contact.name')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-md-6">
        <label class="form-label" for="{{ $idPrefix }}Role">Cargo / função</label>
        <input type="text" name="contact[role]" id="{{ $idPrefix }}Role" class="form-control {{ $err('role') }}" value="{{ $val('role', $c?->role) }}" maxlength="255" placeholder="Ex.: Sales Manager">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="{{ $idPrefix }}Email">Email</label>
        <input type="email" name="contact[email]" id="{{ $idPrefix }}Email" class="form-control {{ $err('email') }}" value="{{ $val('email', $c?->email) }}" maxlength="255" data-dup-field>
        @if($useOld) @error('contact.email')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-md-4">
        <label class="form-label" for="{{ $idPrefix }}Phone">Telefone</label>
        <input type="tel" name="contact[phone]" id="{{ $idPrefix }}Phone" class="form-control {{ $err('phone') }}" value="{{ $val('phone', $c?->phone) }}" maxlength="50" placeholder="+49 ..." data-dup-field>
        @if($useOld) @error('contact.phone')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-md-4">
        <label class="form-label" for="{{ $idPrefix }}Whatsapp">WhatsApp</label>
        <input type="tel" name="contact[whatsapp]" id="{{ $idPrefix }}Whatsapp" class="form-control {{ $err('whatsapp') }}" value="{{ $val('whatsapp', $c?->whatsapp) }}" maxlength="50" placeholder="+49 ..." data-dup-field>
        @if($useOld) @error('contact.whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-12">
        <label class="form-label" for="{{ $idPrefix }}Notes">Notas</label>
        <textarea name="contact[notes]" id="{{ $idPrefix }}Notes" rows="2" class="form-control {{ $err('notes') }}">{{ $val('notes', $c?->notes) }}</textarea>
    </div>
    @if($showPrimary)
    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="contact[is_primary]" value="0">
            <input class="form-check-input" type="checkbox" name="contact[is_primary]" value="1" id="{{ $idPrefix }}Primary" @checked($val('is_primary', $c?->is_primary))>
            <label class="form-check-label" for="{{ $idPrefix }}Primary">Contacto principal</label>
        </div>
    </div>
    @endif
</div>
