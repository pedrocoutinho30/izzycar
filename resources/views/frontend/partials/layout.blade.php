<!doctype html>
<html lang="en">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0NT5HLTZ2J"></script>
    <script>
        // window.dataLayer = window.dataLayer || [];

        // function gtag() {
        //     dataLayer.push(arguments);
        // }
        // gtag('js', new Date());

        // gtag('config', 'G-0NT5HLTZ2J');
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