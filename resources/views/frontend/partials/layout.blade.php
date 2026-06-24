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
        "@@context": "https://schema.org",
        "@@type": "AutoDealer",
        "name": "Izzycar",
        "url": "https://izzycar.pt",
        "logo": {
            "@@type": "ImageObject",
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
            "@@type": "Country",
            "name": "Portugal"
        },
        "address": {
            "@@type": "PostalAddress",
            "addressCountry": "PT",
            "addressLocality": "Portugal"
        },
        "hasOfferCatalog": {
            "@@type": "OfferCatalog",
            "name": "Serviços Izzycar",
            "itemListElement": [
                {
                    "@@type": "Offer",
                    "itemOffered": {
                        "@@type": "Service",
                        "name": "Importação de carros da Alemanha",
                        "description": "Serviço completo de importação de veículos da Alemanha, incluindo transporte, inspeção e registo em Portugal.",
                        "url": "https://izzycar.pt/importacao"
                    }
                },
                {
                    "@@type": "Offer",
                    "itemOffered": {
                        "@@type": "Service",
                        "name": "Importação de carros da Europa",
                        "description": "Serviço de importação automóvel de qualquer país da União Europeia, com acompanhamento total do processo.",
                        "url": "https://izzycar.pt/importacao"
                    }
                },
                {
                    "@@type": "Offer",
                    "itemOffered": {
                        "@@type": "Service",
                        "name": "Legalização automóvel",
                        "description": "Serviço completo de legalização de veículos importados em Portugal, incluindo ISV, IPO e matrícula.",
                        "url": "https://izzycar.pt/legalizacao"
                    }
                },
                {
                    "@@type": "Offer",
                    "itemOffered": {
                        "@@type": "Service",
                        "name": "Venda de carros em consignação",
                        "description": "Gerimos a venda do seu carro em consignação, cuidando de todo o processo.",
                        "url": "https://izzycar.pt/consignacao"
                    }
                },
                {
                    "@@type": "Offer",
                    "itemOffered": {
                        "@@type": "Service",
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
    <meta name="description" content="@yield('meta_description', 'Izzycar: importação automóvel da Alemanha e Europa chave na mão. ISV, transporte, matrícula — tudo incluído. Peça já a sua cotação gratuita.')">
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet"></noscript>


    {{-- Bootstrap (crítico — bloqueia render, necessário para layout base) --}}
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/layout.css') }}" rel="stylesheet">

    {{-- Ícones e Swiper: não-críticos, carregam após render inicial --}}
    <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet"></noscript>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css"></noscript>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous"></noscript>

    <!-- <link href="{{ asset('css/no-shadows.css') }}" rel="stylesheet"> -->
    <link rel="icon" href="https://izzycar.pt/storage/settings/logo_redondo2.png" type="image/png" sizes="48x48">
    <link rel="icon" type="image/png" sizes="32x32" href="https://izzycar.pt/storage/settings/logo_redondo2.png">
    <link rel="icon" type="image/png" sizes="192x192" href="https://izzycar.pt/storage/settings/logo_redondo2.png">

    <style>
        html, body { height: 100%; margin: 0; }
        body { display: flex; flex-direction: column; min-height: 100vh; }
        main { flex: 1; }
    </style>

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

    {{-- ── WhatsApp Button Fixo ── --}}
    @php $wa_phone = preg_replace('/\D/', '', $iz_phone ?? ''); @endphp
    @if($wa_phone)
    <a href="https://wa.me/{{ $wa_phone }}?text=Ol%C3%A1%2C+gostaria+de+obter+mais+informa%C3%A7%C3%B5es."
       class="wa-float-btn"
       target="_blank"
       rel="noopener"
       aria-label="Contactar via WhatsApp"
       title="Fale Connosco no WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor" width="28" height="28">
            <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
        </svg>
    </a>
    <style>
    .wa-float-btn {
        position: fixed;
        bottom: 1.75rem;
        right: 1.75rem;
        z-index: 9999;
        width: 56px;
        height: 56px;
        background: #25d366;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(37,211,102,.45);
        transition: transform .2s, box-shadow .2s;
        text-decoration: none;
    }
    .wa-float-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 28px rgba(37,211,102,.6);
        color: #fff;
    }
    @media (max-width: 768px) {
        .wa-float-btn { bottom: 1.25rem; right: 1.25rem; width: 50px; height: 50px; }
        .wa-float-btn svg { width: 24px; height: 24px; }
    }
    </style>
    @endif

    <!-- JAVASCRIPT FILES -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/jquery.sticky.js') }}"></script>
    <script src="{{ asset('js/click-scroll.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>

    @stack('scripts')
</body>

</html>