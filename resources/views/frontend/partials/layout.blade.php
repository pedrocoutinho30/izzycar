<!doctype html>
<html lang="en">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0NT5HLTZ2J"></script>

    <!-- Outros meta tags -->
    @verbatim
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "AutoDealer",
            "name": "Izzycar",
            "url": "https://izzycar.pt",
            "logo": "https://izzycar.pt/storage/settings/logo_redondo.png",
            "description": "Izzycar é especialista em importação automóvel chave na mão, venda de carros nacionais e previamente importados preparados para entrega imediata. Ajudamos clientes a trazer carros da Alemanha e outros países para Portugal, cuidando de todo o processo de importação, inspeção e registo. Também fazemos legalização automóvel e venda de consignados.",
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "PT"
            },
            "hasOfferCatalog": {
                "@type": "OfferCatalog",
                "name": "Serviços de automóvel Izzycar",
                "itemListElement": [{
                        "@type": "Offer",
                        "name": "Importação de carros da Alemanha",
                        "description": "Serviço completo de importação de veículos da Alemanha, incluindo transporte, inspeção e registo em Portugal."
                    },
                    {
                        "@type": "Offer",
                        "name": "Importação de carros de outros países",
                        "description": "Serviço de importação automóvel de qualquer país da União Europeia, com acompanhamento total do processo."
                    },
                    {
                        "@type": "Offer",
                        "name": "Venda de carros nacionais e previamente importados",
                        "description": "Carros prontos para entrega imediata, nacionais ou previamente importados e preparados para o cliente."
                    },
                    {
                        "@type": "Offer",
                        "name": "Legalização automóvel",
                        "description": "Serviço completo de legalização de veículos, incluindo inspeção e registo em Portugal."
                    },
                    {
                        "@type": "Offer",
                        "name": "Venda de carros em consignação",
                        "description": "Gerimos a venda do seu carro em consignação, cuidando de todo o processo de venda."
                    }
                ]
            },
            "sameAs": [
                "https://www.facebook.com/profile.php?id=61572831810539",
                "https://www.instagram.com/izzycarpt/"
            ]
        }
    </script>
    @endverbatim

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'IzzyCar - Carros Usados em Portugal')</title>
    <meta name="description" content="@yield('meta_description', 'Encontre carros usados de qualidade em Portugal. A IzzyCar oferece opções confiáveis para compra e venda de veículos, com garantia e transparência.')">
    <meta name="author" content="IzzyCar">
    <meta name="google-site-verification" content="8ey-yAqrOmo1lTV1ZnDmJdyRs8KDw-XoE128jpKtys4" />
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

    <style>
        /* ── Page Loader ─────────────────────────────────────────────────────── */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #ffffff 0%, #f5f5f5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeOut 0.5s ease-out 2.5s forwards;
        }

        .page-loader.hidden {
            animation: fadeOut 0.5s ease-out forwards;
            pointer-events: none;
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                visibility: visible;
            }
            to {
                opacity: 0;
                visibility: hidden;
            }
        }

        .loader-content {
            text-align: center;
        }

        .loader-circle {
            position: relative;
            width: 140px;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loader-circle::before {
            content: '';
            position: absolute;
            width: 140px;
            height: 140px;
            border: 4px solid #e5e7eb;
            border-top-color: #990000;
            border-right-color: #990000;
            border-radius: 50%;
            animation: loaderSpin 2.5s ease-in-out forwards;
        }

        @keyframes loaderSpin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .loader-logo {
            position: relative;
            z-index: 1;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: contain;
            animation: logoFadeIn 0.6s ease-out 0.4s both;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        @keyframes logoFadeIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>

<body id="top">

    {{-- Page Loader --}}
    <div id="page-loader" class="page-loader">
        <div class="loader-content">
            <div class="loader-circle">
                <img src="https://izzycar.pt/storage/settings/logo.png" alt="IzzyCar Logo" class="loader-logo">
            </div>
        </div>
    </div>

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

    <script>
    // Page Loader - Hide after page loads
    window.addEventListener('load', function() {
        const loader = document.getElementById('page-loader');
        if (loader) {
            setTimeout(function() {
                loader.classList.add('hidden');
            }, 2500);
        }
    });

    // Fallback: hide after 4 seconds max
    setTimeout(function() {
        const loader = document.getElementById('page-loader');
        if (loader && !loader.classList.contains('hidden')) {
            loader.classList.add('hidden');
        }
    }, 4000);
    </script>

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