{{--
    COMPONENTE: Logo da marca

    USO: <x-admin.brand-logo :brand="$proposal->brand" />   (size em px, por omissão 26)

    A marca vem como texto; o logo é resolvido por nome (sem maiúsculas) a
    partir de um mapa carregado uma só vez por pedido (App\Support\BrandLogos).
    Sem logo não renderiza nada.
--}}
@props(['brand' => null, 'size' => 26])
@php $logoUrl = app(\App\Support\BrandLogos::class)->urlFor($brand); @endphp
@if($logoUrl)<img src="{{ $logoUrl }}" alt="{{ $brand }}" title="{{ $brand }}" loading="lazy" {{ $attributes->merge(['class' => 'brand-logo-img']) }} style="height: {{ (int) $size }}px; width: auto; max-width: {{ (int) $size * 2 }}px; object-fit: contain; vertical-align: middle;" onerror="this.remove()">@endif
