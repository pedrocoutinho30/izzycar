<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Izzycar Admin') - Backoffice</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /**
         * VARIÁVEIS CSS
         * Definição de cores e espaçamentos reutilizáveis em todo o BO
         */
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
            --sidebar-width: 280px;
            --topbar-height: 70px;
            --banner-height: 0px;
            --border-radius: 12px;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.12);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /**
         * RESET E BASE
         * Estilos base para garantir consistência
         */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
            color: var(--admin-secondary);
            overflow-x: hidden;
        }

        .impersonation-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--banner-height);
            z-index: 1040;
            background: #6e0707;
            color: #fff;
            text-align: center;
            padding: .5rem 1rem;
            font-size: .88rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /**
         * TOPBAR (Barra Superior)
         * Barra fixa no topo com logo, search e user menu
         */
        .admin-topbar {
            position: fixed;
            top: var(--banner-height);
            left: 0;
            right: 0;
            height: var(--topbar-height);
            background: white;
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            z-index: 1030;
            box-shadow: var(--shadow-sm);
        }

        /* Logo do backoffice */
        .admin-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--admin-primary);
            text-decoration: none;
            transition: var(--transition);
        }

        .admin-logo:hover {
            opacity: 0.8;
        }

        .admin-logo i {
            font-size: 2rem;
        }

        /* Botão de toggle do menu mobile */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--admin-secondary);
            cursor: pointer;
            padding: 0.5rem;
            margin-right: 1rem;
            transition: var(--transition);
        }

        .sidebar-toggle:hover {
            color: var(--admin-primary);
        }

        /* Search bar na topbar */
        .topbar-search {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: min(500px, 40vw);
        }

        .topbar-search input {
            width: 100%;
            padding: 0.625rem 1rem 0.625rem 2.5rem;
            border: 1px solid var(--admin-border);
            border-radius: 50px;
            font-size: 0.9rem;
            transition: var(--transition);
            background: var(--admin-light);
        }

        .topbar-search input:focus {
            outline: none;
            border-color: var(--admin-primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(110, 7, 7, 0.1);
        }

        .topbar-search .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        /* Painel de resultados da pesquisa global */
        .global-search-results {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            max-height: 70vh;
            overflow-y: auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--admin-border);
            z-index: 1050;
        }
        .global-search-results.show { display: block; }

        .gsr-group-title {
            padding: .6rem 1rem .3rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }
        .gsr-group-title .badge { font-size: .7rem; letter-spacing: .02em; }
        .gsr-item {
            display: flex;
            flex-direction: column;
            gap: .1rem;
            padding: .55rem 1rem;
            text-decoration: none;
            color: inherit;
            transition: background .1s;
        }
        .gsr-item:hover { background: var(--admin-hover); }
        .gsr-item__title { font-size: .87rem; font-weight: 600; color: #111; }
        .gsr-item__subtitle { font-size: .76rem; color: #888; }
        .gsr-see-all {
            display: block;
            text-align: center;
            padding: .65rem 1rem;
            font-size: .8rem;
            font-weight: 600;
            color: var(--admin-primary);
            text-decoration: none;
            border-top: 1px solid var(--admin-border);
            background: var(--admin-light);
        }
        .gsr-see-all:hover { background: var(--admin-hover); }
        .gsr-empty, .gsr-loading {
            padding: 1rem;
            text-align: center;
            font-size: .82rem;
            color: #999;
        }

        /* User menu na topbar */
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: var(--transition);
        }

        .topbar-user:hover {
            background: var(--admin-hover);
        }

        .topbar-user img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--admin-primary);
        }

        /**
         * SIDEBAR (Menu Lateral)
         * Menu de navegação lateral com links para diferentes módulos
         */
        .admin-sidebar {
            position: fixed;
            top: calc(var(--topbar-height) + var(--banner-height));
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: white;
            border-right: 1px solid var(--admin-border);
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1020;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        /* Scrollbar customizada para a sidebar */
        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 10px;
        }

        .admin-sidebar::-webkit-scrollbar-thumb:hover {
            background: #bbb;
        }

        /* Menu de navegação */
        .sidebar-nav {
            padding: 1.5rem 0;
        }

        /* Grupo de menu (ex: "Gestão", "Configurações") */
        .nav-group-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #999;
            margin-top: 1rem;
        }

        /* Cabeçalho de grupo colapsável */
        .nav-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: calc(100% - 2rem);
            margin: 1rem 1rem 0.25rem;
            padding: 0.6rem 0.75rem;
            background: none;
            border: none;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #999;
            cursor: pointer;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .nav-group:hover {
            background: var(--admin-hover);
            color: var(--admin-primary);
        }

        .nav-group__chevron {
            font-size: 0.85rem;
            transition: transform 0.2s ease;
        }

        .nav-group[aria-expanded="true"] .nav-group__chevron {
            transform: rotate(180deg);
        }

        /* Item de menu individual */
        .nav-item {
            margin: 0.25rem 1rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--admin-secondary);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .nav-link:hover {
            background: var(--admin-hover);
            color: var(--admin-primary);
        }

        /* Link ativo */
        .nav-link.active {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .nav-link.active:hover {
            color: white;
        }

        .nav-link i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }

        /* Badge de contagem (ex: "5 novas cotações") */
        .nav-badge {
            margin-left: auto;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 50px;
            background: var(--admin-primary);
            color: white;
            font-weight: 600;
        }

        /**
         * MAIN CONTENT AREA
         * Área principal onde o conteúdo das páginas é exibido
         */
        .admin-main {
            margin-left: var(--sidebar-width);
            margin-top: calc(var(--topbar-height) + var(--banner-height));
            padding: 2rem;
            min-height: calc(100vh - var(--topbar-height) - var(--banner-height));
            transition: var(--transition);
        }

        /**
         * PÁGINA HEADER
         * Cabeçalho comum em todas as páginas de gestão
         */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--admin-secondary);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #666;
            font-size: 1rem;
        }

        /* Breadcrumbs */
        .page-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .page-breadcrumb a {
            color: var(--admin-primary);
            text-decoration: none;
            transition: var(--transition);
        }

        .page-breadcrumb a:hover {
            text-decoration: underline;
        }

        .page-breadcrumb .separator {
            color: #999;
        }

        /**
         * CARDS MODERNOS
         * Cards reutilizáveis para diferentes contextos
         */
        .modern-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .modern-card:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--admin-border);
        }

        .modern-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--admin-light);
        }

        .modern-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--admin-secondary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modern-card-title i {
            color: var(--admin-primary);
        }

        /**
         * BOTÕES MODERNOS
         * Sistema de botões consistente e reutilizável
         */
        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-modern i {
            font-size: 1rem;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .btn-secondary-modern {
            background: var(--admin-light);
            color: var(--admin-secondary);
        }

        .btn-secondary-modern:hover {
            background: var(--admin-border);
            color: var(--admin-secondary);
        }

        .btn-danger-modern {
            background: var(--admin-danger);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .btn-danger-modern:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .btn-success-modern {
            background: var(--admin-success);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .btn-success-modern:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        /**
         * RESPONSIVE - MOBILE FIRST
         * Adaptações para diferentes tamanhos de tela
         */

        /* Tablets e menores */
        @media (max-width: 992px) {
            :root {
                --sidebar-width: 280px;
            }

            .sidebar-toggle {
                display: block;
            }

            .admin-sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
                z-index: 1021;
            }

            .admin-sidebar.show {
                transform: translateX(0);
                box-shadow: var(--shadow-lg);
            }

            .admin-main {
                margin-left: 0;
            }

            .topbar-search {
                display: none;
            }
        }

        /* Mobile */
        @media (max-width: 768px) {
            .admin-topbar {
                padding: 0 1rem;
            }

            .admin-logo span {
                display: none;
            }

            .admin-main {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .modern-card {
                padding: 1rem;
            }

            .topbar-user span {
                display: none;
            }
        }

        /* Overlay para mobile quando sidebar está aberta */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: calc(var(--topbar-height) + var(--banner-height));
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1020;
            opacity: 0;
            transition: var(--transition);
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        @media (max-width: 992px) {
            .admin-sidebar {
                z-index: 1021;
            }
        }

        /**
         * UTILITÁRIOS
         * Classes auxiliares reutilizáveis
         */
        .text-primary-admin {
            color: var(--admin-primary) !important;
        }

        .bg-primary-admin {
            background: var(--admin-primary) !important;
        }

        .gradient-primary {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-primary-dark));
        }

        /* Loading spinner */
        .spinner-modern {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Toast notifications placeholder */
        .toast-container {
            position: fixed;
            top: calc(var(--topbar-height) + 1rem);
            right: 1rem;
            z-index: 1040;
        }
    </style>

    @stack('styles')
</head>

<body @if(session('impersonator_id')) style="--banner-height: 42px;" @endif>

    @if(session('impersonator_id'))
    <div class="impersonation-banner">
        <i class="bi bi-eye-fill me-2"></i>
        A visualizar como <strong>{{ auth()->user()->name }} {{ auth()->user()->last_name }}</strong> (angariador)
        <form action="{{ route('admin.angariador.stop-impersonating') }}" method="POST" class="d-inline ms-3">
            @csrf
            <button type="submit" class="btn btn-sm btn-light">Voltar a Admin</button>
        </form>
    </div>
    @endif

    <!-- TOPBAR -->
    <div class="admin-topbar">
        <!-- Toggle sidebar (mobile) -->
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>

        <!-- Logo -->
        <a href="{{ route('admin.v2.dashboard') }}" class="admin-logo">
            <img src="{{ asset('img/logo_final.png') }}" alt="Izzycar Logo" style="height:80px;">
        </a>

        <!-- Search bar -->
        @hasanyrole('admin|gestor|cms')
        <div class="topbar-search position-relative d-none d-lg-block">
            <i class="bi bi-search search-icon"></i>
            <input type="text" placeholder="Pesquisar clientes, leads, cotações, viaturas..." id="globalSearch" autocomplete="off">
            <div class="global-search-results" id="globalSearchResults"></div>
        </div>
        @endhasanyrole

        <!-- Spacer -->


        <!-- Notifications -->
        <div class="dropdown me-3 ms-auto">
            <!-- <button class="btn-icon btn-secondary-modern" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
            </button> -->
            <!-- <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <h6 class="dropdown-header">Notificações</h6>
                </li>
                <li><a class="dropdown-item" href="#">Nova cotação recebida</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-center" href="#">Ver todas</a></li>
            </ul> -->
        </div>

        <!-- User menu -->
        <div class="dropdown">
            <div class="topbar-user" data-bs-toggle="dropdown">
                <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=6e0707&color=fff" alt="User">
                <span class="d-none d-md-block">{{ Auth::user()->name }}</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <!-- <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Perfil</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Definições</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li> -->
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bi bi-box-arrow-right me-2"></i> Sair
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <!-- SIDEBAR -->
    <aside class="admin-sidebar" id="adminSidebar">
        <nav class="sidebar-nav">
        @hasanyrole('admin|gestor|cms')
            @php
                $navGroups = [
                    'funil' => ['admin.v2.leads.*', 'admin.v2.pre-leads.*', 'admin.v2.form-proposals.*', 'admin.v2.proposals.*', 'admin.v2.converted-proposals.*', 'admin.v2.clients.*', 'admin.v2.cost-simulators.*'],
                    'operacoes' => ['admin.v3.vehicles.*', 'admin.v3.inspections.*', 'admin.legalizations.*', 'admin.transport-quotes.*', 'admin.v2.sales.*', 'admin.v2.movements.*', 'admin.v2.expenses.*', 'admin.tasks.*'],
                    'rede' => ['admin.v2.angariadores.*', 'admin.v2.suppliers.*', 'admin.v2.partners.*'],
                    'analise' => ['admin.v2.reports.*', 'calculator.profit', 'admin.v2.comparator.*', 'car-analysis.*', 'admin.v2.radar.*'],
                    'conteudo' => ['admin.news.*', 'admin.testimonials.*', 'admin.v2.newsletter-management.*', 'admin.v2.menus.*', 'admin.v2.social-posts.*'],
                    'config' => ['admin.v2.attribute-groups.*', 'admin.v2.vehicle-attributes.*', 'admin.v2.settings.*'],
                    'sistema' => ['admin.v2.users.*', 'admin.v2.audit-log', 'admin.v2.roles.*', 'admin.v2.permissions.*', 'admin.v2.manual'],
                ];
                $activeGroup = null;
                foreach ($navGroups as $key => $patterns) {
                    if (request()->routeIs($patterns)) { $activeGroup = $key; break; }
                }
            @endphp

            <!-- Dashboard -->
            <div class="nav-item">
                <a href="{{ route('admin.v2.dashboard') }}" class="nav-link {{ request()->routeIs('admin.v2.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            {{-- ═══════ FUNIL DE VENDAS ═══════ --}}
            <button type="button" class="nav-group" data-bs-toggle="collapse" data-bs-target="#navGroupFunil" aria-expanded="{{ $activeGroup === 'funil' ? 'true' : 'false' }}">
                <span>Funil de Vendas</span>
                <i class="bi bi-chevron-down nav-group__chevron"></i>
            </button>
            <div class="collapse {{ $activeGroup === 'funil' ? 'show' : '' }}" id="navGroupFunil">
                <div class="nav-item">
                    <a href="{{ route('admin.v2.leads.index') }}" class="nav-link {{ request()->routeIs('admin.v2.leads.*') ? 'active' : '' }}">
                        <i class="bi bi-funnel"></i>
                        <span>Leads</span>
                        @php $leadsCount = \App\Models\Client::where('is_lead', true)->whereNotIn('lead_status', ['fria', 'perdida'])->count(); @endphp
                        <span class="nav-badge" id="leads-nav-badge" {{ $leadsCount === 0 ? 'style=display:none' : '' }}>{{ $leadsCount }}</span>
                    </a>
                </div>
                <!-- <div class="nav-item">
                    <a href="{{ route('admin.v2.pre-leads.index') }}" class="nav-link {{ request()->routeIs('admin.v2.pre-leads.*') ? 'active' : '' }}">
                        <i class="bi bi-whatsapp"></i>
                        <span>Pré-Leads</span>
                        @php $preLeadsCount = \App\Models\PreLead::where('status', 'pendente')->count(); @endphp
                        @if($preLeadsCount > 0)
                        <span class="nav-badge">{{ $preLeadsCount }}</span>
                        @endif
                    </a>
                </div> -->
                <div class="nav-item">
                    <a href="{{ route('admin.v2.form-proposals.index') }}" class="nav-link {{ request()->routeIs('admin.v2.form-proposals.*') ? 'active' : '' }}">
                        <i class="bi bi-envelope"></i>
                        <span>Formulários</span>
                        @php $newFormsCount = \App\Models\FormProposal::whereIn('status', ['novo', null])->count(); @endphp
                        @if($newFormsCount > 0)
                        <span class="nav-badge">{{ $newFormsCount }}</span>
                        @endif
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.proposals.index') }}" class="nav-link {{ request()->routeIs('admin.v2.proposals.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Cotações</span>
                        @php $pendingCount = \App\Models\Proposal::where('status', 'Pendente')->count(); @endphp
                        @if($pendingCount > 0)
                        <span class="nav-badge">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.converted-proposals.index') }}" class="nav-link {{ request()->routeIs('admin.v2.converted-proposals.*') ? 'active' : '' }}">
                        <i class="bi bi-check2-circle"></i>
                        <span>Cotações Convertidas</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.clients.index') }}" class="nav-link {{ request()->routeIs('admin.v2.clients.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>Clientes</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.cost-simulators.index') }}" class="nav-link {{ request()->routeIs('admin.v2.cost-simulators.*') ? 'active' : '' }}">
                        <i class="bi bi-dollar"></i>
                        <span>Simulador Custos</span>
                        @php $newSimulationsCount = \App\Models\CostSimulator::where('read', 0)->count(); @endphp
                        @if($newSimulationsCount > 0)
                        <span class="nav-badge">{{ $newSimulationsCount }}</span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- ═══════ OPERAÇÕES ═══════ --}}
            <button type="button" class="nav-group" data-bs-toggle="collapse" data-bs-target="#navGroupOperacoes" aria-expanded="{{ $activeGroup === 'operacoes' ? 'true' : 'false' }}">
                <span>Operações</span>
                <i class="bi bi-chevron-down nav-group__chevron"></i>
            </button>
            <div class="collapse {{ $activeGroup === 'operacoes' ? 'show' : '' }}" id="navGroupOperacoes">
                {{-- Consignações: oculto temporariamente --}}
                <div class="nav-item">
                    <a href="{{ route('admin.v3.vehicles.index') }}" class="nav-link {{ request()->routeIs('admin.v3.vehicles.*') ? 'active' : '' }}">
                        <i class="bi bi-car-front-fill"></i>
                        <span>Viaturas</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v3.inspections.index') }}" class="nav-link {{ request()->routeIs('admin.v3.inspections.*') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Inspeções</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.legalizations.index') }}" class="nav-link {{ request()->routeIs('admin.legalizations.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-check"></i>
                        <span>Legalizações</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.transport-quotes.index') }}" class="nav-link {{ request()->routeIs('admin.transport-quotes.*') ? 'active' : '' }}">
                        <i class="bi bi-truck"></i>
                        <span>Transportes</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.sales.index') }}" class="nav-link {{ request()->routeIs('admin.v2.sales.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Vendas</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.movements.index') }}" class="nav-link {{ request()->routeIs('admin.v2.movements.*') || request()->routeIs('admin.v2.expenses.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i>
                        <span>Movimentos</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.tasks.index') }}" class="nav-link {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
                        <i class="bi bi-check2-square"></i>
                        <span>Tarefas</span>
                    </a>
                </div>
            </div>

            {{-- ═══════ ANGARIADORES & PARCEIROS ═══════ --}}
            <button type="button" class="nav-group" data-bs-toggle="collapse" data-bs-target="#navGroupRede" aria-expanded="{{ $activeGroup === 'rede' ? 'true' : 'false' }}">
                <span>Angariadores &amp; Parceiros</span>
                <i class="bi bi-chevron-down nav-group__chevron"></i>
            </button>
            <div class="collapse {{ $activeGroup === 'rede' ? 'show' : '' }}" id="navGroupRede">
                <div class="nav-item">
                    <a href="{{ route('admin.v2.angariadores.index') }}" class="nav-link {{ request()->routeIs('admin.v2.angariadores.index') || request()->routeIs('admin.v2.angariadores.show') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i>
                        <span>Angariadores</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.angariadores.comissoes') }}" class="nav-link {{ request()->routeIs('admin.v2.angariadores.comissoes') ? 'active' : '' }}">
                        <i class="bi bi-cash-coin"></i>
                        <span>Comissões</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.suppliers.index') }}" class="nav-link {{ request()->routeIs('admin.v2.suppliers.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span>Fornecedores</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.partners.index') }}" class="nav-link {{ request()->routeIs('admin.v2.partners.*') ? 'active' : '' }}">
                        <i class="bi bi-phone-vibrate"></i>
                        <span>Parceiros</span>
                    </a>
                </div>
            </div>

            {{-- ═══════ ANÁLISE & FERRAMENTAS ═══════ --}}
            <button type="button" class="nav-group" data-bs-toggle="collapse" data-bs-target="#navGroupAnalise" aria-expanded="{{ $activeGroup === 'analise' ? 'true' : 'false' }}">
                <span>Análise &amp; Ferramentas</span>
                <i class="bi bi-chevron-down nav-group__chevron"></i>
            </button>
            <div class="collapse {{ $activeGroup === 'analise' ? 'show' : '' }}" id="navGroupAnalise">
                <div class="nav-item">
                    <a href="{{ route('admin.v2.reports.index') }}" class="nav-link {{ request()->routeIs('admin.v2.reports.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Relatórios</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('calculator.profit') }}" class="nav-link {{ request()->routeIs('calculator.profit') ? 'active' : '' }}">
                        <i class="bi bi-calculator"></i>
                        <span>Calculadora de Lucro</span>
                    </a>
                </div>
                <!-- <div class="nav-item">
                    <a href="{{ route('admin.v2.comparator.index') }}" class="nav-link {{ request()->routeIs('admin.v2.comparator.*') ? 'active' : '' }}">
                        <i class="bi bi-columns-gap"></i>
                        <span>Comparador de Veículos</span>
                    </a>
                </div> -->
                <!-- <div class="nav-item">
                    <a href="{{ route('car-analysis.index') }}" class="nav-link {{ request()->routeIs('car-analysis.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line"></i>
                        <span>Análise de Carros</span>
                    </a>
                </div> -->
                <div class="nav-item">
                    <a href="{{ route('admin.v2.radar.index') }}" class="nav-link {{ request()->routeIs('admin.v2.radar.*') ? 'active' : '' }}">
                        <i class="bi bi-broadcast"></i>
                        <span>Radar</span>
                    </a>
                </div>
            </div>

            {{-- ═══════ CONTEÚDO DO SITE ═══════ --}}
            <button type="button" class="nav-group" data-bs-toggle="collapse" data-bs-target="#navGroupConteudo" aria-expanded="{{ $activeGroup === 'conteudo' ? 'true' : 'false' }}">
                <span>Conteúdo do Site</span>
                <i class="bi bi-chevron-down nav-group__chevron"></i>
            </button>
            <div class="collapse {{ $activeGroup === 'conteudo' ? 'show' : '' }}" id="navGroupConteudo">
                <div class="nav-item">
                    <a href="{{ route('admin.news.index') }}" class="nav-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                        <i class="bi bi-file-richtext"></i>
                        <span>Notícias</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}">
                        <i class="bi bi-chat-quote"></i>
                        <span>Testemunhos</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.newsletter-management.index') }}" class="nav-link {{ request()->routeIs('admin.v2.newsletter-management.*') ? 'active' : '' }}">
                        <i class="bi bi-newspaper"></i>
                        <span>Newsletter</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.menus.index') }}" class="nav-link {{ request()->routeIs('admin.v2.menus.*') ? 'active' : '' }}">
                        <i class="bi bi-list-nested"></i>
                        <span>Menus</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.social-posts.index') }}" class="nav-link {{ request()->routeIs('admin.v2.social-posts.*') ? 'active' : '' }}">
                        <i class="bi bi-magic"></i>
                        <span>Criador de Posts</span>
                    </a>
                </div>
            </div>

            {{-- ═══════ CONFIGURAÇÕES ═══════ --}}
            <button type="button" class="nav-group" data-bs-toggle="collapse" data-bs-target="#navGroupConfig" aria-expanded="{{ $activeGroup === 'config' ? 'true' : 'false' }}">
                <span>Configurações</span>
                <i class="bi bi-chevron-down nav-group__chevron"></i>
            </button>
            <div class="collapse {{ $activeGroup === 'config' ? 'show' : '' }}" id="navGroupConfig">
                <div class="nav-item">
                    <a href="{{ route('admin.v2.attribute-groups.index') }}" class="nav-link {{ request()->routeIs('admin.v2.attribute-groups.*') ? 'active' : '' }}">
                        <i class="bi bi-folder"></i>
                        <span>Grupos de Atributos</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.vehicle-attributes.index') }}" class="nav-link {{ request()->routeIs('admin.v2.vehicle-attributes.*') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i>
                        <span>Atributos de Veículos</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.settings.index') }}" class="nav-link {{ request()->routeIs('admin.v2.settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i>
                        <span>Configurações</span>
                    </a>
                </div>
            </div>

            {{-- ═══════ SISTEMA ═══════ --}}
            <button type="button" class="nav-group" data-bs-toggle="collapse" data-bs-target="#navGroupSistema" aria-expanded="{{ $activeGroup === 'sistema' ? 'true' : 'false' }}">
                <span>Sistema</span>
                <i class="bi bi-chevron-down nav-group__chevron"></i>
            </button>
            <div class="collapse {{ $activeGroup === 'sistema' ? 'show' : '' }}" id="navGroupSistema">
                <div class="nav-item">
                    <a href="{{ route('admin.v2.users.index') }}" class="nav-link {{ request()->routeIs('admin.v2.users.*') ? 'active' : '' }}">
                        <i class="bi bi-person-gear"></i>
                        <span>Utilizadores</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.roles.index') }}" class="nav-link {{ request()->routeIs('admin.v2.roles.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock"></i>
                        <span>Perfis</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.v2.permissions.*') ? 'active' : '' }}">
                        <i class="bi bi-key"></i>
                        <span>Permissões</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.audit-log') }}" class="nav-link {{ request()->routeIs('admin.v2.audit-log') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i>
                        <span>Log de Auditoria</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.v2.manual') }}" class="nav-link {{ request()->routeIs('admin.v2.manual') ? 'active' : '' }}">
                        <i class="bi bi-book"></i>
                        <span>Manual de Utilizador</span>
                    </a>
                </div>
            </div>
        @else
            {{-- Angariador puro: apenas a sua própria área --}}
            <div class="nav-item">
                <a href="{{ route('admin.angariador.dashboard') }}" class="nav-link {{ request()->routeIs('admin.angariador.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>O Meu Painel</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.angariador.leads') }}" class="nav-link {{ request()->routeIs('admin.angariador.leads') || request()->routeIs('admin.angariador.leads.show') ? 'active' : '' }}">
                    <i class="bi bi-funnel"></i>
                    <span>As Minhas Leads</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.angariador.propostas') }}" class="nav-link {{ request()->routeIs('admin.angariador.propostas') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Propostas</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.angariador.comissoes') }}" class="nav-link {{ request()->routeIs('admin.angariador.comissoes') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i>
                    <span>Comissões</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.angariador.formularios') }}" class="nav-link {{ request()->routeIs('admin.angariador.formularios') ? 'active' : '' }}">
                    <i class="bi bi-envelope-open"></i>
                    <span>Formulários</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.angariador.manual') }}" class="nav-link {{ request()->routeIs('admin.angariador.manual') ? 'active' : '' }}">
                    <i class="bi bi-book"></i>
                    <span>Manual do Angariador</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.angariador.faq') }}" class="nav-link {{ request()->routeIs('admin.angariador.faq') ? 'active' : '' }}">
                    <i class="bi bi-question-circle"></i>
                    <span>Perguntas Frequentes</span>
                </a>
            </div>
        @endhasanyrole
        </nav>
    </aside>

    <!-- Overlay para mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- MAIN CONTENT -->
    <main class="admin-main">
        <!-- Toast container para notificações -->
        <div class="toast-container"></div>

        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts globais do admin -->
    <script>
        /**
         * SIDEBAR TOGGLE (Mobile)
         * Controla abertura/fecho do menu lateral em mobile
         */
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            // Função para toggle da sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }

            // Event listeners
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }

            if (overlay) {
                overlay.addEventListener('click', toggleSidebar);
            }

            /**
             * CSRF TOKEN
             * Adiciona token CSRF a todos os requests AJAX
             */
            const token = document.querySelector('meta[name="csrf-token"]');
            if (token) {
                window.axios = window.axios || {};
                window.axios.defaults = window.axios.defaults || {};
                window.axios.defaults.headers = window.axios.defaults.headers || {};
                window.axios.defaults.headers.common = window.axios.defaults.headers.common || {};
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
            }

            /**
             * GLOBAL SEARCH
             * Pesquisa por aproximação em Leads, Clientes, Cotações,
             * Formulários, Legalizações, Viaturas e Vendas.
             */
            const globalSearch = document.getElementById('globalSearch');
            const globalSearchResults = document.getElementById('globalSearchResults');

            if (globalSearch && globalSearchResults) {
                const searchUrl = '{{ route('admin.v2.search') }}';
                const resultsUrl = '{{ route('admin.v2.search.results') }}';
                let debounceTimer = null;
                let currentController = null;

                function goToFullResults(term) {
                    window.location.href = resultsUrl + '?q=' + encodeURIComponent(term);
                }

                function renderResults(groups, term) {
                    if (!groups.length) {
                        globalSearchResults.innerHTML = '<div class="gsr-empty">Sem resultados.</div>';
                        return;
                    }

                    globalSearchResults.innerHTML = groups.map(group => `
                        <div class="gsr-group-title"><span class="badge bg-${group.color}"><i class="bi ${group.icon} me-1"></i>${group.label}</span></div>
                        ${group.items.map(item => `
                            <a href="${item.url}" class="gsr-item">
                                <span class="gsr-item__title">${escapeHtml(item.title)}</span>
                                ${item.subtitle ? `<span class="gsr-item__subtitle">${escapeHtml(item.subtitle)}</span>` : ''}
                            </a>
                        `).join('')}
                    `).join('') + `
                        <a href="#" class="gsr-see-all" data-term="${escapeHtml(term)}">Ver todas os resultados para "${escapeHtml(term)}" →</a>
                    `;
                }

                function escapeHtml(text) {
                    const div = document.createElement('div');
                    div.textContent = text ?? '';
                    return div.innerHTML;
                }

                globalSearch.addEventListener('input', function (e) {
                    const term = e.target.value.trim();

                    clearTimeout(debounceTimer);

                    if (term.length < 2) {
                        globalSearchResults.classList.remove('show');
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        if (currentController) currentController.abort();
                        currentController = new AbortController();

                        globalSearchResults.innerHTML = '<div class="gsr-loading">A pesquisar...</div>';
                        globalSearchResults.classList.add('show');

                        fetch(searchUrl + '?q=' + encodeURIComponent(term), {
                            signal: currentController.signal,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        })
                            .then(res => res.json())
                            .then(data => renderResults(data.groups || [], term))
                            .catch(err => {
                                if (err.name !== 'AbortError') {
                                    globalSearchResults.innerHTML = '<div class="gsr-empty">Erro ao pesquisar.</div>';
                                }
                            });
                    }, 300);
                });

                globalSearch.addEventListener('focus', function () {
                    if (globalSearchResults.innerHTML.trim() && globalSearch.value.trim().length >= 2) {
                        globalSearchResults.classList.add('show');
                    }
                });

                globalSearchResults.addEventListener('click', function (e) {
                    const seeAll = e.target.closest('.gsr-see-all');
                    if (seeAll) {
                        e.preventDefault();
                        goToFullResults(seeAll.dataset.term);
                    }
                });

                globalSearch.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        const term = globalSearch.value.trim();
                        if (term.length >= 2) {
                            e.preventDefault();
                            goToFullResults(term);
                        }
                    }
                });

                document.addEventListener('click', function (e) {
                    if (!e.target.closest('.topbar-search')) {
                        globalSearchResults.classList.remove('show');
                    }
                });

                globalSearch.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        globalSearchResults.classList.remove('show');
                        globalSearch.blur();
                    }
                });
            }
        });

        /**
         * TOAST NOTIFICATIONS
         * Sistema de notificações reutilizável
         */
        function showToast(message, type = 'success') {
            const container = document.querySelector('.toast-container');
            const toastId = 'toast-' + Date.now();

            const colors = {
                success: '#28a745',
                error: '#dc3545',
                warning: '#ffc107',
                info: '#17a2b8'
            };

            const icons = {
                success: 'bi-check-circle',
                error: 'bi-x-circle',
                warning: 'bi-exclamation-triangle',
                info: 'bi-info-circle'
            };

            const toastHTML = `
                <div class="toast align-items-center border-0 mb-2" id="${toastId}" role="alert" style="background: ${colors[type]}; color: white;">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi ${icons[type]} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', toastHTML);

            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, {
                delay: 3000
            });
            toast.show();

            // Remove do DOM após fechar
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }

        // Expor função globalmente
        window.showToast = showToast;

        /**
         * NOTIFICAÇÕES DE NOVAS LEADS
         * Polling a cada 30s. Quando chega uma lead nova:
         *  - Mostra toast no backoffice
         *  - Envia notificação nativa do browser (se autorizado)
         *  - Actualiza o badge do menu
         */
        (function initLeadNotifications() {
            const POLL_INTERVAL = 30000; // 30 segundos
            const API_URL       = '{{ route("admin.v2.api.new-leads") }}';
            const LEADS_URL     = '{{ route("admin.v2.leads.index") }}';
            const BADGE_EL      = document.getElementById('leads-nav-badge');

            let lastCheck = Date.now();

            // Pede permissão para notificações do browser
            if ('Notification' in window && Notification.permission === 'default') {
                // Pede permissão só quando o utilizador interage pela primeira vez
                document.addEventListener('click', function askOnce() {
                    Notification.requestPermission();
                    document.removeEventListener('click', askOnce);
                }, { once: true });
            }

            function sendBrowserNotif(lead) {
                if ('Notification' in window && Notification.permission === 'granted') {
                    const n = new Notification('Nova Lead — Izzycar', {
                        body: lead.name + ' submeteu um pedido.',
                        icon: '/favicon.ico',
                        tag:  'lead-' + lead.id,
                    });
                    n.onclick = () => { window.focus(); window.location.href = lead.url; };
                    setTimeout(() => n.close(), 8000);
                }
            }

            function updateBadge(count) {
                if (!BADGE_EL) return;
                if (count > 0) {
                    BADGE_EL.textContent = count;
                    BADGE_EL.style.display = '';
                } else {
                    BADGE_EL.style.display = 'none';
                }
            }

            async function poll() {
                try {
                    const res  = await fetch(API_URL + '?since=' + lastCheck, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) return;
                    const data = await res.json();

                    // Actualiza badge com total activo
                    updateBadge(data.total_active);

                    // Notifica por cada nova lead
                    if (data.count > 0) {
                        data.leads.forEach(lead => {
                            sendBrowserNotif(lead);
                        });
                        showToast(
                            data.count === 1
                                ? `Nova lead: <strong><a href="${data.leads[0].url}" class="text-white">${data.leads[0].name}</a></strong>`
                                : `${data.count} novas leads! <a href="${LEADS_URL}" class="text-white">Ver todas</a>`,
                            'info'
                        );
                    }

                    lastCheck = data.timestamp;
                } catch (e) {
                    // Falha silenciosa — não interrompe o utilizador
                }
            }

            // Só pollar se o utilizador está autenticado (layout admin)
            setInterval(poll, POLL_INTERVAL);
        })();
    </script>

    @stack('scripts')
</body>

</html>