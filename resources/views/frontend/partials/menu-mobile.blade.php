{{-- resources/views/frontend/partials/menu-mobile.blade.php --}}
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container d-flex justify-content-between align-items-center">

        {{-- Logo --}}
        <a class="navbar-brand" href="{{ route('frontend.home') }}">
            <img src="{{ asset($logotipo) }}" alt="Logo" class="navbar-logo" loading="lazy">
        </a>


        {{-- Botão de abrir menu mobile (hamburger) --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu"
            aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- Menu Mobile --}}

        <div class="collapse navbar-collapse mobile-menu" id="mobileMenu">
            <ul class="navbar-nav w-100">
                @foreach($menus->where('parent_id', null) as $menu)
                @php
                $hasChildren = $menu->children_active->count() > 0;
                @endphp

                @if(!$hasChildren)
                <li class="nav-item">
                    <a class="nav-link" href="{{ $menu->url }}">{{ $menu->title }}</a>
                </li>
                @else
                <li class="nav-item">
                    <span class="nav-link fw-bold">{{ $menu->title }}</span>
                    <ul class="list-unstyled ms-3">
                        @foreach($menu->children_active as $child)
                        <li>
                            <a class="nav-link" href="{{ $child->url }}">{{ $child->title }}</a>
                        </li>
                        @endforeach
                    </ul>
                </li>
                @endif
                @endforeach
            </ul>
        </div>


        <div class="mobile-overlay"></div>

    </div>
</nav>
<style>
    .mobile-overlay {
        position: fixed;
        top: 100px;
        left: 0;
        width: 100%;
        height: 100%;
        backdrop-filter: blur(5px);
        /* aplica blur ao fundo */
        background: rgba(0, 0, 0, 0.3);
        /* escurece ligeiramente */
        z-index: 1040;
        /* menor que o menu (1050), maior que o resto */
        display: none;
        /* escondido por default */
    }

    /* Mostra overlay quando menu está aberto */
    .mobile-menu.show+.mobile-overlay {
        display: block;
    }

    /* Estilo para ocupar quase todo o ecrã */
    .mobile-menu {
        background-color: var(--primary-color);
        position: fixed;
        top: 85px;
        /* altura do header */
        left: 0;
        width: 100%;
        height: auto;
        overflow-y: auto;
        padding: 1rem;
        z-index: 1050;
    }

    /* Links mobile */
    .mobile-menu .nav-link {
        padding: 0.75rem 1rem;
    }

    .mobile-menu .nav-link:hover {
        color: var(--secondary-color);
    }

    .mobile-menu .fw-bold {
        color: var(--accent-color);
    }
</style>