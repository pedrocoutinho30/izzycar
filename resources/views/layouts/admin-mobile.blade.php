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
    <meta name="apple-mobile-web-app-title" content="IzzyCar Gestão">
    <link rel="apple-touch-icon" href="{{ asset('img/mobile/icon-180.png') }}">
    <link rel="manifest" href="{{ asset('mobile-manifest.json') }}">

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
        .form-control:not(textarea), .form-select { min-height: 44px; }
        .m-form-check { min-height: 44px; display: flex; align-items: center; gap: 10px; padding-left: 0; }
        .m-form-check .form-check-input { margin: 0; width: 1.5em; height: 1.5em; float: none; }
        .m-form-check .form-check-label { flex: 1; min-height: 44px; display: flex; align-items: center; }

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
        .m-topbar h1 { flex: 1; min-width: 0; margin: 0; font-size: 1.05rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        /* Logo redondo (só o círculo com o J) à esquerda; o título da página fica ao lado. Ficheiro próprio, já recortado, dourado legível sobre o vinho. */
        .m-topbar-logo { flex-shrink: 0; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px; }
        .m-topbar-logo img { display: block; width: 44px; height: 44px; max-width: none; }
        .m-topbar a, .m-topbar button { color: #fff; }

        .m-content { padding: 14px 12px; }

        .m-section-title { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #6c757d; margin: 4px 2px 8px; }
        .m-section-title i { color: var(--admin-primary); margin-right: 4px; }
        .m-stat {
            display: flex; flex-direction: column; justify-content: space-between; gap: 4px; min-height: 88px; height: 100%;
            padding: 12px; background: #fff; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);
            color: inherit; text-decoration: none;
        }
        .m-stat:active { background: var(--admin-hover); }
        .m-stat__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 6px; }
        .m-stat__value { font-size: 2rem; line-height: 1; font-weight: 700; color: var(--admin-primary); }
        .m-stat__value.is-alert { color: var(--admin-danger); }
        .m-stat__icon { font-size: 1.3rem; color: #adb5bd; }
        .m-stat__label { font-size: 0.82rem; color: #6c757d; line-height: 1.2; }

        /* Faixa "Para tratar" do início: cartões grandes, com realce quando há atraso. */
        .m-todo {
            display: flex; flex-direction: column; justify-content: space-between; gap: 4px; min-height: 104px; height: 100%;
            padding: 14px; background: #fff; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--admin-primary); color: inherit; text-decoration: none;
        }
        .m-todo:active { background: var(--admin-hover); }
        .m-todo.is-alert { border-left-color: var(--admin-danger); }
        .m-todo__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 6px; }
        .m-todo__value { font-size: 2.4rem; line-height: 1; font-weight: 700; color: var(--admin-primary); }
        .m-todo.is-alert .m-todo__value { color: var(--admin-danger); }
        .m-todo__icon { font-size: 1.5rem; color: #adb5bd; }
        .m-todo__label { font-size: 0.95rem; font-weight: 600; line-height: 1.2; }
        .m-todo__detail { font-size: 0.8rem; font-weight: 600; color: var(--admin-danger); }

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

        .m-topbar-back { display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 44px; margin-left: -10px; text-decoration: none; }

        /* Pesquisa e chips fixos no topo das listas */
        .m-sticky-filters {
            position: sticky; top: calc(var(--topbar-height) + env(safe-area-inset-top)); z-index: 1020;
            margin: -14px -12px 12px; padding: 12px 12px 4px; background: #f5f7fa;
        }
        .m-chips { display: flex; gap: 8px; overflow-x: auto; padding: 10px 0 6px; scrollbar-width: none; }
        .m-chips::-webkit-scrollbar { display: none; }
        .m-chip {
            flex-shrink: 0; display: inline-flex; align-items: center; min-height: 44px; padding: 0 16px;
            border-radius: 22px; border: 1px solid var(--admin-border); background: #fff;
            color: var(--admin-secondary); text-decoration: none; font-size: 0.9rem; white-space: nowrap;
        }
        .m-chip.active { background: var(--admin-primary); border-color: var(--admin-primary); color: #fff; }

        .m-lead-card__link { display: block; color: inherit; text-decoration: none; }
        .m-lead-card__name { overflow-wrap: anywhere; }
        .m-contact-actions { display: flex; gap: 8px; margin-top: 10px; }
        .m-contact-actions .btn { flex: 1; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; }

        .m-fab {
            position: fixed; z-index: 1025;
            right: calc(16px + env(safe-area-inset-right));
            bottom: calc(var(--bottomnav-height) + env(safe-area-inset-bottom) + 16px);
            width: 56px; height: 56px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: var(--admin-primary); color: #fff; font-size: 1.6rem; box-shadow: var(--shadow-md);
        }

        .m-accordion .accordion-button { min-height: 52px; font-weight: 600; }
        .m-accordion .accordion-button:not(.collapsed) { color: var(--admin-primary); background: #fbf1f1; }
        .m-list-item { padding: 10px 0; border-bottom: 1px solid var(--admin-border); }
        .m-list-item:last-child { border-bottom: 0; }
        .m-dl { display: grid; grid-template-columns: auto 1fr; gap: 6px 12px; }
        .m-dl dt { font-weight: 500; color: #6c757d; }
        .m-dl dd { margin: 0; }
        .m-timeline-item { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--admin-border); }
        .m-timeline-item:last-child { border-bottom: 0; }
        .m-timeline-icon { flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.85rem; }
        .min-w-0 { min-width: 0; }
    </style>
    @stack('styles')
</head>

<body>
    <header class="m-topbar">
        <a href="{{ route('admin.mobile.dashboard') }}" class="m-topbar-logo">
            <img src="{{ asset('img/mobile/logo-redondo.png') }}" alt="IzzyCar - ir para o início" width="44" height="44">
        </a>
        <h1>@yield('title', 'IzzyCar')</h1>
        @yield('header_actions')
    </header>

    <main class="m-content">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
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

    <nav class="m-bottomnav" aria-label="Navegação principal">
        <a href="{{ route('admin.mobile.leads.index') }}" class="{{ request()->routeIs('admin.mobile.leads.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i><span>Leads</span>
        </a>
        @canroute('admin.mobile.clients.index')
        <a href="{{ route('admin.mobile.clients.index') }}" class="{{ request()->routeIs('admin.mobile.clients.*') ? 'active' : '' }}">
            <i class="bi bi-person-lines-fill"></i><span>Clientes</span>
        </a>
        @endcanroute
        <a href="{{ route('admin.mobile.search') }}" class="{{ request()->routeIs('admin.mobile.search') ? 'active' : '' }}">
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
            @canroute('admin.mobile.form-proposals.index')
            <a class="m-sheet-item" href="{{ route('admin.mobile.form-proposals.index') }}">
                <i class="bi bi-envelope-open"></i> Formulários
            </a>
            @endcanroute
            @canroute('admin.mobile.proposals.index')
            <a class="m-sheet-item" href="{{ route('admin.mobile.proposals.index') }}">
                <i class="bi bi-file-earmark-text"></i> Cotações
            </a>
            @endcanroute
            {{-- Rota partilhada: quem a pode abrir decide o ConvertedProposalService (todas ou as dos seus clientes). --}}
            @if(app(\App\Services\ConvertedProposalService::class)->viewScope(auth()->user()))
            <a class="m-sheet-item" href="{{ route('admin.mobile.converted-proposals.index') }}">
                <i class="bi bi-arrow-repeat"></i> Convertidas
            </a>
            @endif
            @canroute('admin.mobile.cost-simulators.index')
            <a class="m-sheet-item" href="{{ route('admin.mobile.cost-simulators.index') }}">
                <i class="bi bi-calculator"></i> Simulador de custos
            </a>
            @endcanroute
            @canroute('admin.mobile.tasks.index')
            <a class="m-sheet-item" href="{{ route('admin.mobile.tasks.index') }}">
                <i class="bi bi-check2-square"></i> Tarefas
            </a>
            @endcanroute
            @canroute('admin.mobile.movements.index')
            <a class="m-sheet-item" href="{{ route('admin.mobile.movements.index') }}">
                <i class="bi bi-journal-text"></i> Movimentos
            </a>
            @endcanroute
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
