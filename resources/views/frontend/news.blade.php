@extends('frontend.partials.layout')

@section('title', 'Izzycar - Notícias')

@section('content')

<section class="section-padding mb-2">
    <div class="container">
<div class="desktop-only">
    {{-- Conteúdo Desktop --}}
    @include('frontend.news-desktop')
</div>

<div class="mobile-only">
    {{-- Conteúdo Mobile --}}
    @include('frontend.news-mobile')
</div>
</div></section>


@endsection