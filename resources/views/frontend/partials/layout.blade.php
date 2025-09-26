<!doctype html>
<html lang="en">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0NT5HLTZ2J"></script>

    <head>
        <!-- Outros meta tags -->
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


        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="google-site-verification" content="8ey-yAqrOmo1lTV1ZnDmJdyRs8KDw-XoE128jpKtys4" />
        <!-- CSS FILES -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet">


        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
        <link href="{{ asset('css/layout.css') }}" rel="stylesheet">
        <link rel="icon" href="https://izzycar.pt/storage/settings/logo_redondo2.png" type="image/png" sizes="48x48">
        <link rel="icon" type="image/png" sizes="32x32" href="https://izzycar.pt/storage/settings/logo_redondo2.png">
        <link rel="icon" type="image/png" sizes="192x192" href="https://izzycar.pt/storage/settings/logo_redondo2.png">

        <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8939798080612976"
            crossorigin="anonymous"></script>

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