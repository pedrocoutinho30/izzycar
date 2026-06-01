@extends('frontend.partials.layout')

@php
    /* ── Cover photo ─────────────────────────────────────────────────────── */
    $coverPhoto     = $vehicle->photos->where('is_cover', true)->first()
                        ?? $vehicle->photos->first();
    $coverPhotoUrl  = $coverPhoto
                        ? asset('storage/' . $coverPhoto->path)
                        : asset('img/logo-transparente.jpeg');

    /* ── Strings ─────────────────────────────────────────────────────────── */
    $vehicleFullName = trim(
        ($vehicle->brand  ?? '') . ' ' .
        ($vehicle->model  ?? '') .
        ($vehicle->version ? ' ' . $vehicle->version : '')
    );
    $ogTitle = $vehicleFullName
             . ($vehicle->year ? ' ' . $vehicle->year : '')
             . ' | IzzyCar';

    $descParts = [$vehicleFullName . ' à venda na IzzyCar.'];
    if ($vehicle->year)         $descParts[] = 'Ano ' . $vehicle->year . '.';
    if ($vehicle->kilometers)   $descParts[] = number_format($vehicle->kilometers, 0, ',', '.') . ' km.';
    if ($vehicle->fuel)         $descParts[] = ucfirst($vehicle->fuel) . '.';
    if ($vehicle->asking_price) $descParts[] = number_format($vehicle->asking_price, 0, ',', '.') . ' €.';
    $descParts[] = 'Carros usados de qualidade em Portugal.';
    $ogDescription = implode(' ', $descParts);

    $currentUrl = url()->current();

    /* ── Schema.org JSON-LD ──────────────────────────────────────────────── */
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Car',
        'name'     => $vehicleFullName,
        'brand'    => ['@type' => 'Brand', 'name' => $vehicle->brand ?? ''],
        'model'    => $vehicle->model ?? '',
        'image'    => $coverPhotoUrl,
        'url'      => $currentUrl,
        'seller'   => [
            '@type'     => 'AutoDealer',
            'name'      => 'IzzyCar',
            'url'       => 'https://izzycar.pt',
            'telephone' => '+351928459346',
        ],
    ];
    if ($vehicle->version)          $schema['version']             = $vehicle->version;
    if ($vehicle->year)             $schema['vehicleModelDate']    = (string) $vehicle->year;
    if ($vehicle->kilometers)       $schema['mileageFromOdometer'] = ['@type' => 'QuantitativeValue', 'value' => $vehicle->kilometers, 'unitCode' => 'KMT'];
    if ($vehicle->fuel)             $schema['fuelType']            = ucfirst($vehicle->fuel);
    if ($vehicle->color)            $schema['color']               = $vehicle->color;
    if ($vehicle->reference)        $schema['productID']           = $vehicle->reference;
    if ($vehicle->cylinder_capacity) $schema['engineDisplacement'] = ['@type' => 'QuantitativeValue', 'value' => $vehicle->cylinder_capacity, 'unitCode' => 'CMQ'];
    if ($vehicle->power)            $schema['vehicleEngine']       = ['@type' => 'EngineSpecification', 'enginePower' => ['@type' => 'QuantitativeValue', 'value' => $vehicle->power, 'unitCode' => 'BHP']];
    if ($vehicle->asking_price) {
        $availability = match($vehicle->status) {
            'em_stock'  => 'https://schema.org/InStock',
            'reservado' => 'https://schema.org/PreOrder',
            default     => 'https://schema.org/SoldOut',
        };
        $schema['offers'] = [
            '@type'        => 'Offer',
            'price'        => (string) $vehicle->asking_price,
            'priceCurrency'=> 'EUR',
            'availability' => $availability,
            'url'          => $currentUrl,
        ];
    }
@endphp

@section('title', $vehicleFullName . ($vehicle->year ? ' ' . $vehicle->year : '') . ' Usado à Venda | IzzyCar')
@section('meta_description', $ogDescription)

@push('head')
{{-- Open Graph (Facebook, WhatsApp, LinkedIn, etc.) --}}
<meta property="og:type"             content="product" />
<meta property="og:locale"           content="pt_PT" />
<meta property="og:site_name"        content="IzzyCar" />
<meta property="og:url"              content="{{ $currentUrl }}" />
<meta property="og:title"            content="{{ $ogTitle }}" />
<meta property="og:description"      content="{{ $ogDescription }}" />
<meta property="og:image"            content="{{ $coverPhotoUrl }}" />
<meta property="og:image:secure_url" content="{{ $coverPhotoUrl }}" />
<meta property="og:image:width"      content="1200" />
<meta property="og:image:height"     content="630" />
<meta property="og:image:alt"        content="{{ $vehicleFullName }}" />

{{-- Twitter / X Card --}}
<meta name="twitter:card"        content="summary_large_image" />
<meta name="twitter:title"       content="{{ $ogTitle }}" />
<meta name="twitter:description" content="{{ $ogDescription }}" />
<meta name="twitter:image"       content="{{ $coverPhotoUrl }}" />

{{-- Canonical --}}
<link rel="canonical" href="{{ $currentUrl }}" />

{{-- Schema.org Structured Data --}}
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@endpush

@section('content')

<div class="desktop-only">
    @include('frontend.vehicles-detail-desktop')

</div>

<div class="mobile-only">
    @include('frontend.vehicles-detail-mobile')
</div>

{{-- Shared same-page lightbox overlay for vehicle images --}}
<div id="vl-lightbox" role="dialog" aria-modal="true" aria-label="Galeria de imagens">
    <button id="vl-lb-close" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
    <button id="vl-lb-prev" aria-label="Anterior"><i class="bi bi-chevron-left"></i></button>
    <button id="vl-lb-next" aria-label="Seguinte"><i class="bi bi-chevron-right"></i></button>
    <div id="vl-lb-img-wrap">
        <img id="vl-lb-img" src="" alt="">
    </div>
    <div id="vl-lb-counter"></div>
</div>
@include('frontend.partials.contact-modal', [
'vehicle' => $vehicle,
])
@include('frontend.partials.share-modal', [
'urldecode' => urldecode(request()->fullUrl()),
'vehicle' => $vehicle,
])

@endsection