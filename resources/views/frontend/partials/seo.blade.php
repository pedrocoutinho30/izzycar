{{-- Meta padrão --}}

<html lang="pt">
<title>{{ $seo->title ?? config('app.name') }} | Izzycar - Especialistas em Importação de Veículos</title>
<meta name="description" content="{{ $seo->meta_description ?? '' }}">
<meta name="image" content="{{ $seo->meta_image ?? '' }}">
<meta name="keywords" content="{{ ($seo->meta_keywords ?? '') . ', ' . ($seo->meta_secundary_keywords ?? '') }}">
<link rel="canonical" href="{{ $seo->canonical_url ?? url()->current() }} ">

{{-- Open Graph --}}
<meta property="og:title" content="{{ $seo->og_title ?? $seo->title ?? config('app.name') }} | Izzycar - Especialistas em Importação de Veículos" />
<meta property="og:description" content="{{ $seo->og_description ?? $seo->meta_description ?? '' }}" />
<meta property="og:image" content="{{ $seo->og_image ?? $seo->meta_image ?? '' }}" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:url" content="{{ $seo->og_url ?? url()->current() }}" />
<meta property="og:type" content="{{ $seo->og_type ?? 'website' }}" />
<meta property="fb:admins" content="61572831810539" />

{{-- Twitter Card --}}
<meta name="twitter:card" content="{{ $seo->twitter_card ?? 'summary_large_image' }}">
<meta name="twitter:title" content="{{ $seo->twitter_title ?? $seo->title ?? config('app.name') }} | Izzycar - Especialistas em Importação de Veículos">
<meta name="twitter:description" content="{{ $seo->twitter_description ?? $seo->meta_description ?? '' }}">
<meta name="twitter:image" content="{{ $seo->twitter_image ?? $seo->meta_image ?? '' }}">
