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

        /**
         * TOPBAR (Barra Superior)
         * Barra fixa no topo com logo, search e user menu
         */
        .admin-topbar {
            position: fixed;
            top: 0;
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
            flex: 1;
            max-width: 500px;
            margin: 0 2rem;
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
            top: var(--topbar-height);
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

        /* Badge de contagem (ex: "5 novas propostas") */
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
            margin-top: var(--topbar-height);
            padding: 2rem;
            min-height: calc(100vh - var(--topbar-height));
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
        }

        .btn-danger-modern:hover {
            background: #c82333;
            color: white;
        }

        .btn-success-modern {
            background: var(--admin-success);
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
            top: var(--topbar-height);
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

<body>

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
        <!-- <div class="topbar-search position-relative d-none d-lg-block">
            <i class="bi bi-search search-icon"></i>
            <input type="text" placeholder="Pesquisar..." id="globalSearch">
        </div> -->

        <!-- Spacer -->
        <div class="flex-grow-1"></div>

        <!-- Notifications -->
        <div class="dropdown me-3">
            <button class="btn-icon btn-secondary-modern" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <h6 class="dropdown-header">Notificações</h6>
                </li>
                <li><a class="dropdown-item" href="#">Nova proposta recebida</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-center" href="#">Ver todas</a></li>
            </ul>
        </div>

        <!-- User menu -->
        <div class="dropdown">
            <div class="topbar-user" data-bs-toggle="dropdown">
                <img src="https://ui-avatars.com/api/?name=Admin&background=6e0707&color=fff" alt="User">
                <span class="d-none d-md-block">Admin</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Perfil</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Definições</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
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
            <!-- Dashboard -->
            <div class="nav-item">
                <a href="{{ route('admin.v2.dashboard') }}" class="nav-link {{ request()->routeIs('admin.v2.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <!-- Gestão -->
            <div class="nav-group-title">Importação</div>
            <div class="nav-item">
                <a href="{{ route('admin.v2.form-proposals.index') }}" class="nav-link {{ request()->routeIs('admin.v2.form-proposals.*') ? 'active' : '' }}">
                    <i class="bi bi-envelope"></i>
                    <span>Formulários</span>
                    @php
                    $newFormsCount = \App\Models\FormProposal::whereIn('status', ['novo', null])->count();
                    @endphp
                    @if($newFormsCount > 0)
                    <span class="nav-badge">{{ $newFormsCount }}</span>
                    @endif
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.v2.proposals.index') }}" class="nav-link {{ request()->routeIs('admin.v2.proposals.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Propostas</span>
                    @php
                    $pendingCount = \App\Models\Proposal::where('status', 'Pendente')->count();
                    @endphp
                    @if($pendingCount > 0)
                    <span class="nav-badge">{{ $pendingCount }}</span>
                    @endif
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.v2.converted-proposals.index') }}" class="nav-link {{ request()->routeIs('admin.v2.converted-proposals.*') ? 'active' : '' }}">
                    <i class="bi bi-check2-circle"></i>
                    <span>Propostas Convertidas</span>
                </a>
            </div>


            <div class="nav-group-title">Gestão</div>

            <div class="nav-item">
                <a href="{{ route('admin.v2.clients.index') }}" class="nav-link {{ request()->routeIs('admin.v2.clients.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Clientes</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.v2.vehicles.index') }}" class="nav-link {{ request()->routeIs('admin.v2.vehicles.*') ? 'active' : '' }}">
                    <i class="bi bi-car-front"></i>
                    <span>Veículos</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.v2.sales.index') }}" class="nav-link {{ request()->routeIs('admin.v2.sales.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i>
                    <span>Vendas</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.v2.expenses.index') }}" class="nav-link {{ request()->routeIs('admin.v2.expenses.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>Despesas</span>
                </a>
            </div>

            <!-- Parceiros -->
            <div class="nav-group-title">Rede</div>

            <div class="nav-item">
                <a href="{{ route('admin.v2.suppliers.index') }}" class="nav-link {{ request()->routeIs('admin.v2.suppliers.*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i>
                    <span>Fornecedores</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.v2.partners.index') }}" class="nav-link {{ request()->routeIs('admin.v2.partners.*') ? 'active' : '' }}">
                    <i class="bi bi-phone-vibrate"></i>
                    <span>Parceiros</span>
                </a>
            </div>



            <!-- Sistema Antigo -->
            <div class="nav-group-title">Sistema V1 (Antigo)</div>

            <div class="nav-item">
                <a href="{{ route('pages.index') }}" class="nav-link">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>CMS</span>
                </a>
            </div>



            <!-- Configurações -->
            <div class="nav-group-title">Configurações</div>

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

            <!-- Sistema -->
            <div class="nav-group-title">Sistema</div>

            <div class="nav-item">
                <a href="{{ route('admin.v2.users.index') }}" class="nav-link {{ request()->routeIs('admin.v2.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Utilizadores</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.v2.roles.index') }}" class="nav-link {{ request()->routeIs('admin.v2.roles.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>
                    <span>Perfis</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.v2.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.v2.permissions.*') ? 'active' : '' }}">
                    <i class="bi bi-key"></i>
                    <span>Permissões</span>
                </a>
            </div>
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
             * GLOBAL SEARCH (placeholder para funcionalidade futura)
             */
            const globalSearch = document.getElementById('globalSearch');
            if (globalSearch) {
                globalSearch.addEventListener('input', function(e) {
                    // TODO: Implementar pesquisa global
                    console.log('Search:', e.target.value);
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
    </script>

    @stack('scripts')
</body>

</html>