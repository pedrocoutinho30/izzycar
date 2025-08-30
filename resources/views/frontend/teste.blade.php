  <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/home') }}">
                <div class="sidebar-brand-icon rotate-n-15">
                    <!-- <i class="fas fa-laugh-wink"></i> -->
                    <img src="{{ asset('img/logo-simples.png') }}" alt="Logo Izzycar" class="img-fluid rounded-circle" style="width: 50px; height: auto;">
                </div>
                <!-- <div class="sidebar-brand-text mx-3"></div> -->
            </a>

        
            <div class="sidebar-heading">
                {{ __('Gestão Stand') }}
            </div>

            @php
            $menuItems = [
            ['route' => 'clients.index', 'icon' => 'fa-users', 'label' => __('Clientes')],
            ['route' => 'proposals.index', 'icon' => 'fa-file-signature', 'label' => __('Propostas')],
            ['route' => 'vehicles.index', 'icon' => 'fa-car-side', 'label' => __('Veículos')],
            ['route' => 'vehicle-attributes.index', 'icon' => 'fa-cog', 'label' => __('Atributos de Veículo')],
            ['route' => 'expenses.index', 'icon' => 'fa-file-invoice-dollar','label' => __('Despesas')],
            ['route' => 'sales.index', 'icon' => 'fa-shopping-cart', 'label' => __('Vendas')],
            ['route' => 'brands.index', 'icon' => 'fa-tags', 'label' => __('Marcas')],
            ['route' => 'suppliers.index', 'icon' => 'fa-truck-loading', 'label' => __('Fornecedores')],
            ['route' => 'partners.index', 'icon' => 'fa-handshake', 'label' => __('Parceiros')],
            ['route' => 'ad-searches.index', 'icon' => 'fa-chart-line', 'label' => __('Análise de Mercado')],
            ];
            @endphp

            @foreach ($menuItems as $item)
            <li class="nav-item {{ Nav::isRoute($item['route']) }}">
                <a href="{{ route($item['route']) }}" class="nav-link" style="width: 12rem !important;">
                    <i class="fas fa-fw {{ $item['icon'] }}"></i>
                    <span class="menu-label">{{ $item['label'] }}</span>
                </a>
            </li>
            @endforeach
            <div class="sidebar-heading">
                {{ __('CMS') }}
            </div>

            @php
            $menuItems = [
            ['route' => 'page-types.index', 'icon' => 'fa-file', 'label' => __('Tipos de Página')],
            ['route' => 'pages.index', 'icon' => 'fa-file', 'label' => __('Páginas')],
            ['route' => 'menus.index', 'icon' => 'fa-file', 'label' => __('Menus')],
            ];
            @endphp

            @foreach ($menuItems as $item)
            <li class="nav-item {{ Nav::isRoute($item['route']) }}">
                <a href="{{ route($item['route']) }}" class="nav-link" style="width: 12rem !important;">
                    <i class="fas fa-fw {{ $item['icon'] }}"></i>
                    <span class="menu-label">{{ $item['label'] }}</span>
                </a>
            </li>
            @endforeach

            <!-- Divider -->
            <!-- <hr class="sidebar-divider d-none d-md-block"> -->

            <!-- Sidebar Toggler (Sidebar) -->
            <!-- <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

            <hr class="sidebar-divider my-0 mt-10"> -->


            <!-- Nav Item - Logout -->
            <div class="sidebar-footer">
                <a class="nav-link text-white" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-fw fa-sign-out-alt"></i>
                    <span>{{ __('Logout') }}</span>
                </a>
            </div>
        </ul>