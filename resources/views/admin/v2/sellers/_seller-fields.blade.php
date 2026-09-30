{{-- Campos do vendedor, partilhados entre o formulário da página e o modal
     "Criar vendedor" das Oportunidades. $seller pode ser null; com
     $useOld = false (modal) os valores antigos da sessão são ignorados. --}}
@php
    $s = $seller ?? null;
    $useOld = $useOld ?? true;
    $compact = $compact ?? false;
    $idPrefix = $idPrefix ?? 'seller';
    $val = fn ($field, $default = null) => $useOld ? old($field, $default) : $default;
    $err = fn ($field) => $useOld && $errors->has($field) ? 'is-invalid' : '';
@endphp

<div class="row g-3">
    <div class="col-md-{{ $compact ? 12 : 6 }}">
        <label class="form-label" for="{{ $idPrefix }}Name">Nome da empresa / stand <span class="text-danger">*</span></label>
        <input type="text" name="name" id="{{ $idPrefix }}Name" class="form-control {{ $err('name') }}" value="{{ $val('name', $s?->name) }}" maxlength="255" placeholder="Ex.: Autohaus Müller GmbH" required>
        @if($useOld) @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-md-6">
        <label class="form-label" for="{{ $idPrefix }}Website">Website</label>
        <input type="text" name="website" id="{{ $idPrefix }}Website" class="form-control {{ $err('website') }}" value="{{ $val('website', $s?->website) }}" maxlength="255" placeholder="https://...">
        @if($useOld) @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-md-6">
        <label class="form-label" for="{{ $idPrefix }}Country">País</label>
        <select name="country" id="{{ $idPrefix }}Country" class="form-select {{ $err('country') }}" data-seller-country>
            <option value="">—</option>
            @foreach(\App\Models\ImportOpportunity::COUNTRIES as $code => $label)
            <option value="{{ $code }}" @selected($val('country', $s?->country) === $code)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="form-text">Usado para reconhecer números de telefone sem indicativo.</div>
        @if($useOld) @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-md-6">
        <label class="form-label" for="{{ $idPrefix }}Domains">Domínio(s) de email</label>
        <input type="text" name="domains" id="{{ $idPrefix }}Domains" class="form-control {{ $err('domains') }}" value="{{ $val('domains', $s?->domains_text) }}" maxlength="1000" placeholder="autohaus-muller.de, autohaus-muller.com" data-seller-domains>
        <div class="form-text">Separados por vírgula. Só servem para sugerir correspondências.</div>
        @if($useOld) @error('domains')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    @unless($compact)
    <div class="col-12">
        <label class="form-label" for="{{ $idPrefix }}Address">Morada</label>
        <textarea name="address" id="{{ $idPrefix }}Address" rows="2" class="form-control {{ $err('address') }}">{{ $val('address', $s?->address) }}</textarea>
        @if($useOld) @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-12">
        <label class="form-label" for="{{ $idPrefix }}Notes">Notas</label>
        <textarea name="notes" id="{{ $idPrefix }}Notes" rows="3" class="form-control {{ $err('notes') }}" placeholder="Ex.: Já trabalhámos várias vezes com este stand. Normalmente responde rapidamente.">{{ $val('notes', $s?->notes) }}</textarea>
        @if($useOld) @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    @endunless
</div>
