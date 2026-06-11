@php
    $seoTitle = ($seo->title ?? config('app.name')) . ' | Izzycar — Importação Automóvel Portugal';
    $seoDesc  = $seo->meta_description ?? '';
    $seoImg   = $seo->og_image ?? $seo->meta_image ?? '';
    $seoUrl   = $seo->canonical_url ?? url()->current();
    $seoKeys  = trim(($seo->meta_keywords ?? '') . ', ' . ($seo->meta_secundary_keywords ?? ''), ', ');
@endphp

@section('title', $seoTitle)
@section('meta_description', $seoDesc)

@push('head')
@if($seoKeys)
<meta name="keywords" content="{{ $seoKeys }}">
@endif
<link rel="canonical" href="{{ $seoUrl }}">

{{-- Open Graph --}}
<meta property="og:title" content="{{ $seo->og_title ?? $seo->title ?? config('app.name') }}">
<meta property="og:description" content="{{ $seo->og_description ?? $seoDesc }}">
<meta property="og:image" content="{{ $seoImg }}">
<meta property="og:url" content="{{ $seo->og_url ?? $seoUrl }}">
<meta property="og:type" content="{{ $seo->og_type ?? 'website' }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="{{ $seo->twitter_card ?? 'summary_large_image' }}">
<meta name="twitter:title" content="{{ $seo->twitter_title ?? $seo->title ?? config('app.name') }}">
<meta name="twitter:description" content="{{ $seo->twitter_description ?? $seoDesc }}">
<meta name="twitter:image" content="{{ $seo->twitter_image ?? $seoImg }}">
@endpush
