<!doctype html>
<html lang="pt-PT">

<head>
    @php
        $s = App\Models\Setting::get();
        $iz_phone   = $s->where('label','phone')->first()?->value   ?? '';
        $iz_email   = $s->where('label','email')->first()?->value   ?? '';
        $iz_address = $s->where('label','address')->first()?->value ?? '';
        $iz_fb      = $s->where('label','facebook')->first()?->value ?? 'https://www.facebook.com/profile.php?id=61572831810539';
        $iz_ig      = $s->where('label','insta')->first()?->value   ?? 'https://www.instagram.com/izzycarpt/';
    @endphp

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0NT5HLTZ2J"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','G-0NT5HLTZ2J');</script>

    <!-- Schema.org — AutoDealer -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "AutoDealer",
        "name": "Izzycar",
        "url": "https://izzycar.pt",
        "logo": {
            "@type": "ImageObject",
            "url": "https://izzycar.pt/storage/settings/logo_redondo.png",
            "width": 192,
            "height": 192
        },
        "image": "https://izzycar.pt/storage/settings/logo_redondo.png",
        "description": "Izzycar é especialista em importação automóvel chave na mão, venda de carros nacionais e previamente importados preparados para entrega imediata. Ajudamos clientes a trazer carros da Alemanha e outros países para Portugal, cuidando de todo o processo de importação, inspeção e registo.",
        "telephone": "{{ $iz_phone }}",
        "email": "{{ $iz_email }}",
        "priceRange": "€€",
        "currenciesAccepted": "EUR",
        "paymentAccepted": "Transferência Bancária, Multibanco",
        "areaServed": {
            "@type": "Country",
            "name": "Portugal"
        },
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "PT",
            "addressLocality": "Portugal"
        },
        "hasOfferCatalog": {
            "@type": "OfferCatalog",
            "name": "Serviços Izzycar",
            "itemListElement": [
                {
                    "@type": "Offer",
                    "itemOffered": {
                        "@type": "Service",
                        "name": "Importação de carros da Alemanha",
                        "description": "Serviço completo de importação de veículos da Alemanha, incluindo transporte, inspeção e registo em Portugal.",
                        "url": "https://izzycar.pt/importacao"
                    }
                },
                {
                    "@type": "Offer",
                    "itemOffered": {
                        "@type": "Service",
                        "name": "Importação de carros da Europa",
                        "description": "Serviço de importação automóvel de qualquer país da União Europeia, com acompanhamento total do processo.",
                        "url": "https://izzycar.pt/importacao"
                    }
                },
                {
                    "@type": "Offer",
                    "itemOffered": {
                        "@type": "Service",
                        "name": "Legalização automóvel",
                        "description": "Serviço completo de legalização de veículos importados em Portugal, incluindo ISV, IPO e matrícula.",
                        "url": "https://izzycar.pt/legalizacao"
                    }
                },
                {
                    "@type": "Offer",
                    "itemOffered": {
                        "@type": "Service",
                        "name": "Venda de carros em consignação",
                        "description": "Gerimos a venda do seu carro em consignação, cuidando de todo o processo.",
                        "url": "https://izzycar.pt/consignacao"
                    }
                },
                {
                    "@type": "Offer",
                    "itemOffered": {
                        "@type": "Service",
                        "name": "Simulador de custos de importação",
                        "description": "Calcule gratuitamente os custos de importar o seu carro para Portugal, incluindo ISV e todos os encargos.",
                        "url": "https://izzycar.pt/simulador-custos"
                    }
                }
            ]
        },
        "sameAs": [
            "{{ $iz_fb }}",
            "{{ $iz_ig }}"
        ]
    }
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#6e0707">
    <meta name="format-detection" content="telephone=no">

    <title>@yield('title', 'Izzycar — Importação Automóvel Chave na Mão | Portugal')</title>
    <meta name="description" content="@yield('meta_description', 'Izzycar: importação automóvel da Alemanha e Europa chave na mão. ISV, transporte, matrícula — tudo incluído. Peça já a sua proposta gratuita.')">
    <meta name="author" content="Izzycar">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <meta name="google-site-verification" content="8ey-yAqrOmo1lTV1ZnDmJdyRs8KDw-XoE128jpKtys4">

    <!-- Open Graph global -->
    <meta property="og:site_name" content="Izzycar">
    <meta property="og:locale" content="pt_PT">
    <meta property="og:type" content="website">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="fb:app_id" content="61572831810539">

    <!-- DNS prefetch -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//www.googletagmanager.com">

    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet">


    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('css/layout.css') }}" rel="stylesheet">
    <!-- <link href="{{ asset('css/no-shadows.css') }}" rel="stylesheet"> -->
    <link rel="icon" href="https://izzycar.pt/storage/settings/logo_redondo2.png" type="image/png" sizes="48x48">
    <link rel="icon" type="image/png" sizes="32x32" href="https://izzycar.pt/storage/settings/logo_redondo2.png">
    <link rel="icon" type="image/png" sizes="192x192" href="https://izzycar.pt/storage/settings/logo_redondo2.png">

    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    

    @stack('head')
    @stack('styles')
</head>

<body id="top">

    @include('frontend.partials.header')
    @include('frontend.partials.cookies')

    <main>
        @yield('content')
    </main>
    @if(!isset($no_footer) || !$no_footer)
    @include('frontend.partials.frontend-footer')
    @endif
    <!-- JAVASCRIPT FILES -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/jquery.sticky.js') }}"></script>
    <script src="{{ asset('js/click-scroll.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>

    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    @stack('scripts')
</body>

</html>
<style>
    html,
    body {
        height: 100%;
        margin: 0;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    main {
        flex: 1;
    }
</style>