{{-- App instalável da gestão mobile (PWA): o Android/Chrome usa o manifest; o
     iPhone usa sobretudo as meta apple-* e o apple-touch-icon (opaco, 180px).
     Partilhado pelo layout mobile e pelo login quando se vem da área mobile.
     Sem service worker próprio: a app não guarda páginas nem dados em cache. --}}
    <meta name="theme-color" content="#6e0707">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="IzzyCar Gestão">
    <meta name="application-name" content="IzzyCar Gestão">
    <link rel="apple-touch-icon" href="{{ asset('img/mobile/icon-180.png') }}">
    <link rel="manifest" href="{{ asset('mobile-manifest.json') }}">
