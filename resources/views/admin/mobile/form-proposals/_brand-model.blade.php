{{-- Marca → Modelo do catálogo (VehicleCatalog) com selects nativos (no iPhone
     abrem a roda de escolha). Sem pesquisa nem bibliotecas: o modelo é
     preenchido pela marca escolhida. Um valor antigo fora do catálogo continua
     visível. Espera $brand, $model, $required, $idPrefix. --}}
@php
    $required = $required ?? false;
    $catalog = \App\Support\VehicleCatalog::map();
    $catalogBrand = \App\Support\VehicleCatalog::brand($brand ?? null);
    $brandValue = $catalogBrand ?? ($brand ?? null);
    $modelValue = \App\Support\VehicleCatalog::model($brand ?? null, $model ?? null) ?? ($model ?? null);
    $models = $catalogBrand ? $catalog[$catalogBrand] : [];
@endphp

<div>
    <label class="form-label fw-semibold" for="{{ $idPrefix }}-brand">Marca @if($required)<span class="text-danger">*</span>@endif</label>
    <select name="brand" id="{{ $idPrefix }}-brand" class="form-select @error('brand') is-invalid @enderror"
            data-m-brand data-model-target="#{{ $idPrefix }}-model" @required($required)>
        <option value="">— Escolher marca —</option>
        @if($brandValue && !$catalogBrand)
            <option value="{{ $brandValue }}" selected>{{ $brandValue }} (fora do catálogo)</option>
        @endif
        @foreach(array_keys($catalog) as $nome)
            <option value="{{ $nome }}" @selected($nome === $brandValue)>{{ $nome }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="form-label fw-semibold" for="{{ $idPrefix }}-model">Modelo @if($required)<span class="text-danger">*</span>@endif</label>
    <select name="model" id="{{ $idPrefix }}-model" class="form-select @error('model') is-invalid @enderror" @required($required)>
        <option value="">{{ $brandValue ? '— Escolher modelo —' : 'Escolha primeiro a marca' }}</option>
        @if($modelValue && !in_array($modelValue, $models, true))
            <option value="{{ $modelValue }}" selected>{{ $modelValue }} (fora do catálogo)</option>
        @endif
        @foreach($models as $nome)
            <option value="{{ $nome }}" @selected($nome === $modelValue)>{{ $nome }}</option>
        @endforeach
    </select>
</div>

@once
@push('scripts')
<script>
    // Trocar a marca recarrega a lista de modelos a partir do catálogo.
    (function () {
        const catalogo = @json($catalog);
        document.querySelectorAll('[data-m-brand]').forEach((marca) => {
            marca.addEventListener('change', () => {
                const modelo = document.querySelector(marca.dataset.modelTarget);
                const lista = catalogo[marca.value] || [];
                modelo.replaceChildren(new Option(marca.value ? '— Escolher modelo —' : 'Escolha primeiro a marca', ''));
                lista.forEach((nome) => modelo.add(new Option(nome, nome)));
            });
        });
    })();
</script>
@endpush
@endonce
