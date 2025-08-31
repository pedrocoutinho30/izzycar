<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container d-flex justify-content-between align-items-center">

        {{-- Logo --}}
        <a class="navbar-brand" href="{{ route('frontend.home') }}">
            <img src="{{ asset('img/logo-transparente.png') }}" alt="Logo" class="navbar-logo" style="height:auto; width:120px;">
        </a>

        {{-- Menu central --}}
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <!-- <ul class="nav nav-tabs">
                @foreach($menus as $menu)
                @if($menu->children->isEmpty())
                <li class="nav-item">
                    <a class="nav-link {{ $menu->route_name && Route::currentRouteName() == $menu->route_name ? 'active' : '' }}"
                        href="{{ $menu->route_name ? route($menu->route_name) : '#' }}">
                        {{ $menu->title }}
                    </a>
                </li>
                @else
                <li class="nav-item dropdown">
                    @php
                    $active = false;
                    foreach($menu->children as $child) {
                    if($child->route_name && Route::currentRouteName() == $child->route_name) {
                    $active = true;
                    break;
                    }
                    }
                    @endphp
                    <a class="nav-link dropdown-toggle {{ $active ? 'active' : '' }}" data-bs-toggle="dropdown" href="#">
                        {{ $menu->title }}
                    </a>
                    <ul class="dropdown-menu">
                        @foreach($menu->children as $child)
                        <li>
                            <a class="dropdown-item {{ $child->route_name && Route::currentRouteName() == $child->route_name ? 'active' : '' }}"
                                href="{{ route($child->route_name) }}">
                                {{ $child->title }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </li>
                @endif
                @endforeach
            </ul> -->

            <ul class="navbar-nav ms-lg-5 me-lg-auto">
                @foreach($menus->where('parent_id', null) as $menu)
                @php
                $hasChildren = $menu->children_active->count() > 0;
                $active = $menu->route_name && Route::currentRouteName() == $menu->route_name;

                foreach($menu->children_active as $child) {
                if($child->route_name && Route::currentRouteName() == $child->route_name) {
                $active = true;
                break;
                }
                }
                @endphp

                @if(!$hasChildren)
                <li class="nav-item">
                    <a class="nav-link {{ $active ? 'active' : '' }}"
                        href="{{ $menu->route_name ? route($menu->route_name) : '#' }}">
                        {{ $menu->title }}  
                    </a>
                </li>
                @else
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ $active ? 'active' : '' }}"
                        href=""
                        id="navbarDropdown{{ $menu->id }}"
                        role="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                        {{ $menu->title }}
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown{{ $menu->id }}">
                        @foreach($menu->children_active as $child)
                        <li>
                            <a class="dropdown-item {{ $child->route_name && Route::currentRouteName() == $child->route_name ? 'active' : '' }}"
                                href="{{ route($child->route_name) }}">
                                {{ $child->title }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </li>
                @endif
                @endforeach
            </ul>


        </div>

        {{-- Navbar toggler (mobile) --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

    </div>
</nav>


<style>
    body {
        padding-top: 70px;
        /* Ajusta conforme a altura da navbar */
    }

    .navbar {
        border-bottom: none;
        /* remove qualquer linha */
        background-color: var(--primary-color);
    }

    .navbar .nav-link {
        color: white;
        border-radius: 0;
        /* sem arredondamento */
        background: none;
        padding: 0.5rem 1rem;
        transition: color 0.3s;
    }

    .navbar .nav-link:hover {
        color: var(--secondary-color);
    }

    .navbar .nav-link.active {
        color: var(--accent-color) !important;
        font-weight: bold;
        border-bottom: 2px solid var(--accent-color);
        /* underline ativo */
    }

    .dropdown-menu .dropdown-item {
        border-radius: 0;
    }

    .dropdown-menu .dropdown-item.active {
        background-color: transparent !important;
        color: var(--accent-color);
        font-weight: bold;
        text-decoration: underline;
        /* underline para ativo */
    }
</style>