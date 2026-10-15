{{-- Marcas de um vendedor: o logo (nome em title/alt) ou, se a marca não tem
     logo, o nome num badge pequeno. Parâmetros: $brands (coleção de Brand,
     já carregada), $size (altura do logo, por omissão 24). --}}
@php $size = $size ?? 24; @endphp
@if($brands->isNotEmpty())
<span class="d-inline-flex flex-wrap align-items-center gap-2">
    @foreach($brands as $brand)
        @if($brand->logo_url)
            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" title="{{ $brand->name }}" loading="lazy"
                 style="height: {{ (int) $size }}px; width: auto; max-width: {{ (int) $size * 2 }}px; object-fit: contain;"
                 onerror="this.outerHTML='<span class=&quot;badge bg-light text-dark border&quot;>{{ e($brand->name) }}</span>'">
        @else
            <span class="badge bg-light text-dark border" title="{{ $brand->name }}">{{ $brand->name }}</span>
        @endif
    @endforeach
</span>
@endif
