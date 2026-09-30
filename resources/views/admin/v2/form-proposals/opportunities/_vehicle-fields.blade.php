{{-- Campos do veículo/vendedor, partilhados entre o modal de criação rápida
     ($quick = true: só os essenciais à vista, o resto recolhido) e o
     formulário de edição da página de detalhe. $opportunity pode ser null. --}}
@php
    $quick = $quick ?? false;
    $o = $opportunity ?? null;
    // Depois de um erro de validação, mostrar o vendedor/contacto escolhidos.
    $pickerSeller = old('seller_id') ? \App\Models\Seller::with('activeContacts')->find(old('seller_id')) : $o?->seller;
    $pickerContact = old('seller_contact_id')
        ? \App\Models\SellerContact::where('seller_id', $pickerSeller?->id)->find(old('seller_contact_id'))
        : (old('seller_id') ? null : $o?->sellerContact);
    $val = fn ($field, $default = null) => old($field, $o ? ($o->{$field} instanceof \BackedEnum ? $o->{$field}->value : $o->{$field}) : $default);
    $err = fn ($field) => $errors->has($field) ? 'is-invalid' : '';
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Marca <span class="text-danger">*</span></label>
        <input type="text" name="brand" class="form-control {{ $err('brand') }}" value="{{ $val('brand', $defaults['brand'] ?? null) }}" required maxlength="100">
        @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Modelo <span class="text-danger">*</span></label>
        <input type="text" name="model" class="form-control {{ $err('model') }}" value="{{ $val('model', $defaults['model'] ?? null) }}" required maxlength="100">
        @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Ano</label>
        <input type="number" name="year" class="form-control {{ $err('year') }}" value="{{ $val('year') }}" min="1950" max="{{ now()->year + 1 }}">
        @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Quilómetros</label>
        <input type="number" name="mileage" class="form-control {{ $err('mileage') }}" value="{{ $val('mileage') }}" min="0" step="1">
        @error('mileage')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Preço</label>
        <div class="input-group">
            <input type="number" name="price" class="form-control {{ $err('price') }}" value="{{ $val('price') }}" min="0" step="0.01">
            <span class="input-group-text">€</span>
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">URL do anúncio</label>
        <input type="url" name="listing_url" class="form-control {{ $err('listing_url') }}" value="{{ $val('listing_url') }}" placeholder="https://...">
        @error('listing_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        @include('admin.v2.sellers._picker', ['seller' => $pickerSeller, 'contact' => $pickerContact])
    </div>
</div>

@if($quick)
<a class="d-inline-block mt-3 small" data-bs-toggle="collapse" href="#oppMoreFields" role="button"
   aria-expanded="{{ $errors->hasAny(['version','fuel','vin','country','photo']) ? 'true' : 'false' }}">
    <i class="bi bi-chevron-down"></i> Mais detalhes (opcional)
</a>
<div class="collapse {{ $errors->hasAny(['version','fuel','vin','country','photo']) ? 'show' : '' }}" id="oppMoreFields">
@endif

<div class="row g-3 mt-0">
    <div class="col-md-6">
        <label class="form-label">Versão</label>
        <input type="text" name="version" class="form-control {{ $err('version') }}" value="{{ $val('version') }}" maxlength="255">
        @error('version')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Combustível</label>
        <select name="fuel" class="form-select {{ $err('fuel') }}">
            <option value="">—</option>
            @foreach(\App\Enums\VehicleFuel::options() as $value => $label)
            <option value="{{ $value }}" @selected($val('fuel', $defaults['fuel'] ?? null) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="form-text">Elétrico ativa a checklist específica de VE.</div>
        @error('fuel')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">VIN</label>
        <input type="text" name="vin" class="form-control text-uppercase {{ $err('vin') }}" value="{{ $val('vin') }}" maxlength="17">
        @error('vin')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">País</label>
        <select name="country" class="form-select {{ $err('country') }}">
            <option value="">—</option>
            @foreach(\App\Models\ImportOpportunity::COUNTRIES as $code => $label)
            <option value="{{ $code }}" @selected($val('country') === $code)>{{ $label }}</option>
            @endforeach
        </select>
        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Foto</label>
        <input type="file" name="photo" class="form-control {{ $err('photo') }}" accept="image/jpeg,image/png,image/webp">
        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if($o?->photo_path)
        <div class="form-check mt-1">
            <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="removePhoto">
            <label class="form-check-label small" for="removePhoto">Remover foto atual</label>
        </div>
        @endif
    </div>
    @unless($quick)
    <div class="col-12">
        <label class="form-label">Observações sobre o veículo</label>
        <textarea name="vehicle_notes" rows="3" class="form-control {{ $err('vehicle_notes') }}">{{ $val('vehicle_notes') }}</textarea>
        @error('vehicle_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @endunless
</div>

@if($quick)
</div>
@endif
