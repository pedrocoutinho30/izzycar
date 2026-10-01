{{-- Marca → Modelo com os selects do catálogo (brands / model_cars), com
     pesquisa. Parâmetros: $brand, $model (valores atuais), $required,
     $colClass, $idPrefix, $brandError, $modelError. Um valor antigo que não
     esteja no catálogo continua visível ("fora do catálogo") para não se
     perder ao editar. O catálogo vai uma só vez por página. --}}
@php
    $brand = $brand ?? null;
    $model = $model ?? null;
    $required = $required ?? false;
    $colClass = $colClass ?? 'col-md-6';
    $idPrefix = $idPrefix ?? 'bm' . uniqid();
    $catalog = \App\Support\VehicleCatalog::map();
    $catalogBrand = \App\Support\VehicleCatalog::brand($brand);
    $brandValue = $catalogBrand ?? $brand;
    $modelValue = \App\Support\VehicleCatalog::model($brand, $model) ?? $model;
@endphp

<div class="{{ $colClass }}" data-brand-model data-model-target="#{{ $idPrefix }}Model">
    <label class="form-label" for="{{ $idPrefix }}Brand">Marca @if($required)<span class="text-danger">*</span>@endif</label>
    <select name="brand" id="{{ $idPrefix }}Brand" class="form-select {{ $brandError ?? '' }}" data-role="brand" @required($required) autocomplete="off">
        <option value="">Pesquisar marca...</option>
        @if($brandValue && !$catalogBrand)
            <option value="{{ $brandValue }}" selected>{{ $brandValue }} (fora do catálogo)</option>
        @endif
        @foreach(array_keys($catalog) as $name)
            <option value="{{ $name }}" @selected($name === $brandValue)>{{ $name }}</option>
        @endforeach
    </select>
    @if(!empty($brandError)) @error('brand')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
</div>
<div class="{{ $colClass }}">
    <label class="form-label" for="{{ $idPrefix }}Model">Modelo @if($required)<span class="text-danger">*</span>@endif</label>
    <select name="model" id="{{ $idPrefix }}Model" class="form-select {{ $modelError ?? '' }}" data-role="model" data-current="{{ $modelValue }}" @required($required) autocomplete="off">
        <option value="">{{ $brandValue ? 'Pesquisar modelo...' : 'Escolha primeiro a marca' }}</option>
    </select>
    @if(!empty($modelError)) @error('model')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
</div>

@once
@push('scripts')
<script>
window.IZ_VEHICLE_CATALOG = @json($catalog);
(function () {
    function ready(fn) { document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', fn) : fn(); }
    function tomSelectReady() {
        if (window.TomSelect) return Promise.resolve();
        if (!document.querySelector('link[href*="tom-select"]')) {
            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://cdn.jsdelivr.net/npm/tom-select@2/dist/css/tom-select.bootstrap5.min.css';
            document.head.appendChild(css);
        }
        return new Promise(resolve => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/tom-select@2/dist/js/tom-select.complete.min.js';
            script.onload = resolve;
            document.head.appendChild(script);
        });
    }

    function init(wrapper) {
        const brandSelect = wrapper.querySelector('[data-role="brand"]');
        const modelSelect = document.querySelector(wrapper.dataset.modelTarget);
        const current = modelSelect.dataset.current || '';

        const brandTs = new TomSelect(brandSelect, { placeholder: 'Pesquisar marca...', maxOptions: 300 });
        const modelTs = new TomSelect(modelSelect, { placeholder: 'Pesquisar modelo...', maxOptions: 500 });

        function loadModels(brand, keep) {
            const models = (window.IZ_VEHICLE_CATALOG || {})[brand] || [];
            modelTs.clear(true);
            modelTs.clearOptions();
            models.forEach(name => modelTs.addOption({ value: name, text: name }));
            // Valor antigo fora do catálogo: mantém-se visível.
            if (keep && keep !== '' && !models.includes(keep)) {
                modelTs.addOption({ value: keep, text: keep + ' (fora do catálogo)' });
            }
            modelTs.refreshOptions(false);
            if (keep) modelTs.setValue(keep, true);
            brand ? modelTs.enable() : modelTs.disable();
        }

        loadModels(brandTs.getValue(), current);
        brandTs.on('change', (brand) => loadModels(brand, null));
    }

    ready(() => {
        const wrappers = document.querySelectorAll('[data-brand-model]');
        if (wrappers.length) tomSelectReady().then(() => wrappers.forEach(init));
    });
})();
</script>
@endpush
@endonce
