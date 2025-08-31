<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Laravel SB Admin 2">
    <meta name="author" content="Alejandro RH">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Izzycar BO') }}</title>

    <!-- Fonts -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Favicon -->
    <link href="{{ asset('img/logo-simples.png') }}" class="img-fluid rounded-circle" rel="icon" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        .nav-link {
            width: 12rem !important;
        }

        #accordionSidebar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            box-shadow: 4px 0 12px rgb(20 40 70 / 0.2);
            border-radius: 0 16px 16px 0;
            transition: width 0.3s ease;
        }

        #accordionSidebar .sidebar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: #e0e6f0;
            letter-spacing: 2px;
            padding: 1.25rem 1rem;
        }

        #accordionSidebar .sidebar-brand-icon i {
            color: #a3c4f3;
            font-size: 2.2rem;
            transform: rotate(-12deg);
        }

        #accordionSidebar .nav-link {
            color: rgba(255 255 255 / 0.85);
            font-weight: 500;
            padding: 0.85rem 1.5rem;
            border-radius: 12px;
            margin: 0.25rem 1rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        #accordionSidebar .nav-link:hover,
        #accordionSidebar .nav-item.active .nav-link {
            background-color: rgba(255 255 255 / 0.15);
            color: #fff;
            box-shadow: 0 4px 12px rgb(163 196 243 / 0.4);
            font-weight: 600;
        }

        #accordionSidebar .nav-link i {
            color: rgba(255 255 255 / 0.75);
            margin-right: 0.9rem;
            transition: color 0.3s ease;
        }

        #accordionSidebar .nav-link:hover i,
        #accordionSidebar .nav-item.active .nav-link i {
            color: #a3c4f3;
        }

        #accordionSidebar hr.sidebar-divider {
            border-color: rgba(255 255 255 / 0.15);
            margin: 1.25rem 1rem;
        }

        #accordionSidebar .sidebar-heading {
            color: rgba(255 255 255 / 0.7);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            padding: 0 1.5rem;
            margin-top: 1.5rem;
            letter-spacing: 1px;
        }

        #sidebarToggle {
            background-color: transparent;
            border: none;
            color: rgba(255 255 255 / 0.6);
            font-size: 1.5rem;
            padding: 0.5rem;
            transition: color 0.3s ease;
        }

        #sidebarToggle:hover {
            color: #a3c4f3;
            cursor: pointer;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 0.85rem 1.5rem;
            background-color: rgba(255, 255, 255, 0.05);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-footer a {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            display: block;
            text-align: left;
            transition: background-color 0.3s ease;
        }

        .sidebar-footer a:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        .menu-label {
            display: block;
            text-align: center;
            white-space: normal;
            /* Permite quebrar linha */
            word-break: break-word;
            /* Quebra palavras longas se necessário */
            line-height: 1.2;
        }
    </style>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <!-- Sidebar - Accordion Wrapper -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/home') }}">
                <div class="sidebar-brand-icon rotate-n-15">
                    <img src="{{ asset('img/logo-simples.png') }}" alt="Logo Izzycar" class="img-fluid rounded-circle" style="width: 50px; height: auto;">
                </div>
            </a>
            @hasanyrole('admin|gestor')
            <!-- Accordion Item: Gestão Stand -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#collapseGestao" aria-expanded="true">
                    <i class="fas fa-fw fa-cogs"></i>
                    <span>Gestão Stand</span>
                </a>
                @php
                $menuItemsGestao = [
                ['route' => 'clients.index', 'icon' => 'fa-users', 'label' => __('Clientes'), 'permission' => 'gerir clientes'],
                ['route' => 'proposals.index', 'icon' => 'fa-file-signature', 'label' => __('Propostas'), 'permission' => 'gerir propostas'],
                ['route' => 'vehicles.index', 'icon' => 'fa-car-side', 'label' => __('Veículos'), 'permission' => 'gerir veiculos'],
                ['route' => 'expenses.index', 'icon' => 'fa-file-invoice-dollar','label' => __('Despesas'), 'permission' => 'gerir despesas'],
                ['route' => 'sales.index', 'icon' => 'fa-shopping-cart', 'label' => __('Vendas'), 'permission' => 'gerir vendas'],
                ['route' => 'suppliers.index', 'icon' => 'fa-truck-loading', 'label' => __('Fornecedores'), 'permission' => 'gerir fornecedores'],
                ['route' => 'partners.index', 'icon' => 'fa-handshake', 'label' => __('Parceiros'), 'permission' => 'gerir parceiros'],
                ['route' => 'ad-searches.index', 'icon' => 'fa-chart-line', 'label' => __('Análise de Mercado'), 'permission' => 'analisar mercado'],
                ];
                @endphp
                <div id="collapseGestao" class="collapse "> <!-- show = aberto por padrão -->
                    <div class=" py-2 collapse-inner rounded">
                        @foreach ($menuItemsGestao as $item)
                        @can($item['permission'])
                        <div class="nav-item {{ Nav::isRoute($item['route']) }}">
                            <a href="{{ route($item['route']) }}" class="nav-link" style="width: 12rem !important;">
                                <i class="fas fa-fw {{ $item['icon'] }}"></i>
                                <span class="menu-label">{{ $item['label'] }}</span>
                            </a>
                        </div>
                        @endcan
                        @endforeach
                    </div>
                </div>
            </li>
            @endhasanyrole
            <!-- Accordion Item: CMS -->
            @hasanyrole('admin|cms')

            <li class="nav-item">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#collapseCMS" aria-expanded="true">
                    <i class="fas fa-fw fa-file"></i>
                    <span>CMS</span>
                </a>
                @php
                $menuItemsCMS = [
                ['route' => 'pages.imports', 'icon' => 'fa-file', 'label' => __('Importações'), 'permission' => 'gerir importacoes'],
                ['route' => 'pages.legalizations', 'icon' => 'fa-file', 'label' => __('Legalizações'), 'permission' => 'gerir legalizacoes'],
                ['route' => 'pages.categories', 'icon' => 'fa-file', 'label' => __('Categorias de Notícias'), 'permission' => 'gerir categoria de noticias'],
                ['route' => 'pages.news', 'icon' => 'fa-file', 'label' => __('Notícias'), 'permission' => 'gerir noticias'],
                ['route' => 'pages.selling', 'icon' => 'fa-file', 'label' => __('Venda Automóvel'), 'permission' => 'gerir pagina de venda'],
                ];
                @endphp
                <div id="collapseCMS" class="collapse "> <!-- show = aberto por padrão -->
                    <div class=" py-2 collapse-inner rounded">
                        @foreach ($menuItemsCMS as $item)
                        @can($item['permission'])
                        <div class="nav-item {{ Nav::isRoute($item['route']) }}">
                            <a href="{{ route($item['route']) }}" class="nav-link" style="width: 12rem !important;">
                                <i class="fas fa-fw {{ $item['icon'] }}"></i>
                                <span class="menu-label">{{ $item['label'] }}</span>
                            </a>
                        </div>
                        @endcan
                        @endforeach
                    </div>
                </div>
            </li>
            @endhasanyrole
            @hasanyrole('admin|cms|gestor')
            <li class="nav-item">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#collapseAdmin" aria-expanded="true">
                    <i class="fas fa-fw fa-file"></i>
                    <span>ADMIN</span>
                </a>
                @php
                $menuItemsAdmin = [
                ['route' => 'brands.index', 'icon' => 'fa-tags', 'label' => __('Marcas'), 'permission' => 'gerir marcas'],
                ['route' => 'vehicle-attributes.index', 'icon' => 'fa-cog', 'label' => __('Atributos de Veículo'), 'permission' => 'gerir atributos veiculos'],
                ['route' => 'page-types.index', 'icon' => 'fa-file', 'label' => __('Tipos de Página'), 'permission' => 'gerir tipos de pagina'],
                ['route' => 'pages.index', 'icon' => 'fa-file', 'label' => __('Páginas'), 'permission' => 'gerir paginas'],
                ['route' => 'menus.index', 'icon' => 'fa-file', 'label' => __('Menus'), 'permission' => 'gerir menus'],
                ['route' => 'users.index', 'icon' => 'fa-person', 'label' => __('Utilizadores'), 'permission' => 'gerir utilizadores'],
                ];
                @endphp
                <div id="collapseAdmin" class="collapse "> 
                    <div class=" py-2 collapse-inner rounded">
                        @foreach ($menuItemsAdmin as $item)
                        @can($item['permission'])
                        <div class="nav-item {{ Nav::isRoute($item['route']) }}">
                            <a href="{{ route($item['route']) }}" class="nav-link" style="width: 12rem !important;">
                                <i class="fas fa-fw {{ $item['icon'] }}"></i>
                                <span class="menu-label">{{ $item['label'] }}</span>
                            </a>
                        </div>
                        @endcan
                        @endforeach
                    </div>
                </div>
            </li>
            @endhasanyrole
        </ul>


        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">


                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid mt-4">

                    @yield('main-content')

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>By <a href="https://izzycar.pt" target="_blank">Izzycar</a>. {{ now()->year }}</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('Ready to Leave?') }}</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-link" type="button" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <a class="btn btn-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
    @stack('scripts')
    <!-- Scripts -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
</body>

</html>