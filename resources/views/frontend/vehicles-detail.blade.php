@extends('frontend.partials.layout')
@section('title', 'Izzycar')

@section('content')

<div class="desktop-only">
    {{-- Conteúdo Desktop --}}
    @include('frontend.vehicles-detail-desktop')

</div>

<div class="mobile-only">
    {{-- Conteúdo Mobile --}}
    @include('frontend.vehicles-detail-mobile')
</div>
@include('frontend.partials.contact-modal', [
'vehicle' => $vehicle,
])
<!-- Modal -->
@include('frontend.partials.share-modal', [
'urldecode' => urldecode(request()->fullUrl()),
'vehicle' => $vehicle,
])
</div>


@endsection