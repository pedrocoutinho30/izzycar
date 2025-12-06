@extends('frontend.partials.layout')


@section('title', $vehicle->brand . ' ' . $vehicle->model . ' Usado à Venda | IzzyCar')

@section('meta_description', 'Encontre carros usados de qualidade em Portugal. A IzzyCar oferece opções confiáveis para compra e venda de veículos, com garantia e transparência.')

@section('content')

<div class="desktop-only">
    @include('frontend.vehicles-detail-desktop')

</div>

<div class="mobile-only">
    @include('frontend.vehicles-detail-mobile')
</div>
@include('frontend.partials.contact-modal', [
'vehicle' => $vehicle,
])
@include('frontend.partials.share-modal', [
'urldecode' => urldecode(request()->fullUrl()),
'vehicle' => $vehicle,
])
</div>


@endsection