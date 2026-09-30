{{-- Seletor de Vendedor + Contacto (Oportunidades, Veículos). Espera
     $seller e $contact (podem ser null). Os modais "Novo vendedor"/"Novo
     contacto" vão para o fim da página (stack "scripts"), porque este
     seletor vive dentro de formulários e de outros modais. --}}
@php
    $seller = $seller ?? null;
    $contact = $contact ?? null;
    $sellerField = $sellerField ?? 'seller_id';
    $contactField = $contactField ?? 'seller_contact_id';
    $colClass = $colClass ?? 'col-md-6';
    $labelClass = $labelClass ?? 'form-label';
    $useOld = $useOld ?? true;
    $err = fn ($field) => $useOld && $errors->has($field) ? 'is-invalid' : '';
    $contactOptions = $seller ? $seller->activeContacts->values() : collect();
    if ($contact && !$contactOptions->contains('id', $contact->id)) {
        $contactOptions->push($contact);
    }
    // A diretiva json do Blade parte o argumento nas vírgulas — preparar aqui.
    $sellerData = $seller ? json_encode(['id' => $seller->id, 'name' => $seller->name, 'country' => $seller->country, 'country_label' => $seller->country_label]) : null;
@endphp

<div class="row g-3" data-seller-picker>
    <div class="{{ $colClass }}">
        <div class="d-flex justify-content-between align-items-baseline">
            <label class="{{ $labelClass }}">Vendedor</label>
            <button type="button" class="btn btn-link btn-sm p-0" data-action="new-seller"><i class="bi bi-plus"></i> Novo vendedor</button>
        </div>
        <select name="{{ $sellerField }}" class="{{ $err($sellerField) }}" data-role="seller" autocomplete="off">
            <option value="">Pesquisar vendedor...</option>
            @if($seller)
            <option value="{{ $seller->id }}" selected
                    data-data="{{ $sellerData }}">{{ $seller->name }}</option>
            @endif
        </select>
        @if($useOld) @error($sellerField)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
    </div>
    <div class="{{ $colClass }}">
        <div class="d-flex justify-content-between align-items-baseline">
            <label class="{{ $labelClass }}">Contacto</label>
            <button type="button" class="btn btn-link btn-sm p-0" data-action="new-contact" @disabled(!$seller)><i class="bi bi-plus"></i> Novo contacto</button>
        </div>
        <select name="{{ $contactField }}" class="{{ $err($contactField) }}" data-role="contact" autocomplete="off">
            <option value="">Escolher contacto...</option>
            @foreach($contactOptions as $option)
            <option value="{{ $option->id }}" @selected($contact?->id === $option->id)
                    data-data="{{ json_encode($option->toPickerArray()) }}">{{ $option->name }}</option>
            @endforeach
        </select>
        @if($useOld) @error($contactField)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
    </div>
</div>

@once
@push('scripts')
@include('admin.v2.sellers._quick-modals')
@endpush
@endonce
@include('admin.v2.sellers._scripts')
