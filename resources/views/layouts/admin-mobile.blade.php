<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IzzyCar') - IzzyCar Mobile</title>

    <meta name="theme-color" content="#6e0707">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="IzzyCar">
    <link rel="apple-touch-icon" href="{{ asset('img/logo-arredondado.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">

    <style>
        :root {
            --admin-primary: #6e0707;
            --admin-primary-dark: #4a0505;
            --admin-secondary: #111111;
            --admin-success: #28a745;
            --admin-danger: #dc3545;
            --admin-warning: #ffc107;
            --admin-info: #17a2b8;
            --admin-light: #f8f9fa;
            --admin-dark: #343a40;
            --admin-border: #dee2e6;
            --admin-hover: #f1f3f5;
            --border-radius: 12px;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.12);
            --topbar-height: 56px;
            --bottomnav-height: 60px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
            color: var(--admin-secondary);
            overflow-x: hidden;
            padding-left: env(safe-area-inset-left);
            padding-right: env(safe-area-inset-right);
            padding-top: calc(var(--topbar-height) + env(safe-area-inset-top));
            padding-bottom: calc(var(--bottomnav-height) + env(safe-area-inset-bottom) + 12px);
        }

        /* Evita o zoom automático do iOS ao focar campos */
        input, select, textarea, .form-control, .form-select { font-size: 16px !important; }
        .btn { min-height: 44px; }

        .m-topbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: calc(var(--topbar-height) + env(safe-area-inset-top));
            padding: env(safe-area-inset-top) calc(12px + env(safe-area-inset-right)) 0 calc(12px + env(safe-area-inset-left));
            background: var(--admin-primary);
            color: #fff;
            box-shadow: var(--shadow-md);
        }
        .m-topbar h1 { flex: 1; margin: 0; font-size: 1.15rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .m-topbar a, .m-topbar button { color: #fff; }

        .m-content { padding: 14px 12px; }

        .m-card {
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            padding: 14px;
            margin-bottom: 12px;
        }

        .m-bottomnav {
            position: fixed;
            left: 0; right: 0; bottom: 0;
            z-index: 1030;
            display: flex;
            background: #fff;
            border-top: 1px solid var(--admin-border);
            padding-bottom: env(safe-area-inset-bottom);
            padding-left: env(safe-area-inset-left);
            padding-right: env(safe-area-inset-right);
        }
        .m-bottomnav a, .m-bottomnav button {
            flex: 1;
            min-height: 44px;
            height: var(--bottomnav-height);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            font-size: 0.72rem;
            color: #6c757d;
            text-decoration: none;
            background: none;
            border: 0;
        }
        .m-bottomnav i { font-size: 1.35rem; line-height: 1; }
        .m-bottomnav .active { color: var(--admin-primary); font-weight: 600; }

        .m-sheet-item {
            display: flex; align-items: center; gap: 10px;
            min-height: 48px; padding: 8px 4px;
            color: var(--admin-secondary); text-decoration: none;
            background: none; border: 0; width: 100%; text-align: left;
        }
    </style>
    @stack('styles')
</head>

<body>
    <header class="m-topbar">
        <h1>@yield('title', 'IzzyCar')</h1>
        @yield('header_actions')
    </header>

    <main class="m-content">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @php
        // Os destinos ainda por criar não podem rebentar a página.
        $href = fn (string $rota) => \Illuminate\Support\Facades\Route::has($rota) ? route($rota) : '#';
    @endphp
    <nav class="m-bottomnav" aria-label="Navegação principal">
        <a href="{{ $href('admin.mobile.leads.index') }}" class="{{ request()->routeIs('admin.mobile.leads.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i><span>Leads</span>
        </a>
        <a href="{{ $href('admin.mobile.clients.index') }}" class="{{ request()->routeIs('admin.mobile.clients.*') ? 'active' : '' }}">
            <i class="bi bi-person-lines-fill"></i><span>Clientes</span>
        </a>
        <a href="{{ $href('admin.mobile.search') }}" class="{{ request()->routeIs('admin.mobile.search') ? 'active' : '' }}">
            <i class="bi bi-search"></i><span>Pesquisa</span>
        </a>
        <button type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMais" aria-controls="mobileMais">
            <i class="bi bi-three-dots"></i><span>Mais</span>
        </button>
    </nav>

    <div class="offcanvas offcanvas-bottom" tabindex="-1" id="mobileMais" aria-labelledby="mobileMaisLabel" style="height:auto; padding-bottom: env(safe-area-inset-bottom);">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileMaisLabel">Mais</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
        <div class="offcanvas-body pt-0">
            <a class="m-sheet-item" href="{{ route('admin.v2.dashboard') }}">
                <i class="bi bi-display"></i> Ir para a versão desktop
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="m-sheet-item"><i class="bi bi-box-arrow-right"></i> Terminar sessão</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
