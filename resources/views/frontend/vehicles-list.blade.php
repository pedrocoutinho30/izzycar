@extends('frontend.partials.layout')

@section('title', 'Izzycar')
@php use Illuminate\Support\Str; @endphp
@section('content')


<section class="section-padding">
    <div class="container">

        <div class="desktop-only">
            {{-- Conteúdo Desktop --}}
            @include('frontend.vehicles-list-desktop')
        </div>

        <div class="mobile-only">
            {{-- Conteúdo Mobile --}}
            @include('frontend.vehicles-list-mobile')
        </div>
    </div>
</section>

@endsection
