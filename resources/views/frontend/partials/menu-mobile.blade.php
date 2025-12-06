{{-- Menu Mobile Moderno --}}
<nav class="navbar-mobile-modern fixed-top">
    <div class="container d-flex justify-content-between align-items-center">
        {{-- Logo --}}
        <a class="navbar-brand-mobile" href="{{ route('frontend.home') }}">
            <img src="{{ asset($logotipo) }}" alt="Logo" class="navbar-logo-mobile" loading="lazy">
        </a>

        {{-- Hamburger Button --}}
        <button class="hamburger-modern" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu"
            aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>

    {{-- Menu Mobile --}}
    <div class="collapse mobile-menu-modern" id="mobileMenu">
        <div class="mobile-menu-content">
            <ul class="mobile-nav-list">
                @foreach($menus->where('parent_id', null) as $menu)
                @php
                $hasChildren = $menu->children_active->count() > 0;
                @endphp

                @if(!$hasChildren)
                <li class="mobile-nav-item">
                    <a class="mobile-nav-link" href="{{ $menu->url }}">
                        <span>{{ $menu->title }}</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </li>
                @else
                <li class="mobile-nav-item mobile-nav-parent">
                    <div class="mobile-nav-link-parent">
                        <span>{{ $menu->title }}</span>
                    </div>
                    <ul class="mobile-nav-submenu">
                        @foreach($menu->children_active as $child)
                        <li>
                            <a class="mobile-nav-sublink" href="{{ $child->url }}">
                                <span>{{ $child->title }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </li>
                @endif
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Overlay --}}
    <div class="mobile-overlay-modern" data-bs-toggle="collapse" data-bs-target="#mobileMenu"></div>
</nav>

<style>
/* Navbar Mobile Moderno */
.navbar-mobile-modern {
    background: linear-gradient(135deg, #111111 0%, #1a1a1a 100%);
    padding: 1rem 0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    z-index: 1000;
}

.navbar-brand-mobile {
    display: flex;
    align-items: center;
}

.navbar-logo-mobile {
    height: 80px;
    width: auto;
}

/* Hamburger Menu Moderno */
.hamburger-modern {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    width: 40px;
    height: 40px;
    background: transparent;
    border: none;
    padding: 8px;
    cursor: pointer;
    position: relative;
    z-index: 1051;
}

.hamburger-line {
    width: 100%;
    height: 3px;
    background: #fff;
    border-radius: 2px;
    transition: all 0.3s ease;
}

.hamburger-modern.collapsed .hamburger-line:nth-child(1) {
    transform: translateY(8px) rotate(45deg);
}

.hamburger-modern.collapsed .hamburger-line:nth-child(2) {
    opacity: 0;
}

.hamburger-modern.collapsed .hamburger-line:nth-child(3) {
    transform: translateY(-8px) rotate(-45deg);
}

/* Menu Mobile Content */
.mobile-menu-modern {
    position: fixed;
    top: 72px;
    left: 0;
    width: 100%;
    height: calc(100vh - 72px);
    background: linear-gradient(135deg, #1a1a1a 0%, #111111 100%);
    overflow-y: auto;
    z-index: 1049;
}

.mobile-menu-content {
    padding: 2rem 1.5rem;
}

.mobile-nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.mobile-nav-item {
    margin-bottom: 0.5rem;
}

.mobile-nav-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 600;
    font-size: 1.05rem;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.mobile-nav-link:hover,
.mobile-nav-link:active {
    background: rgba(110, 7, 7, 0.2);
    border-color: rgba(110, 7, 7, 0.5);
    color: #fff;
    transform: translateX(5px);
}

.mobile-nav-link svg {
    transition: transform 0.3s ease;
}

.mobile-nav-link:hover svg {
    transform: translateX(3px);
}

/* Parent Menu Items */
.mobile-nav-link-parent {
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, rgba(110, 7, 7, 0.2) 0%, rgba(110, 7, 7, 0.1) 100%);
    border-radius: 12px;
    color: #fff;
    font-weight: 700;
    font-size: 1.1rem;
    border: 2px solid rgba(110, 7, 7, 0.3);
    margin-bottom: 0.75rem;
}

/* Submenu */
.mobile-nav-submenu {
    list-style: none;
    padding: 0 0 0 1rem;
    margin: 0.75rem 0 0 0;
}

.mobile-nav-sublink {
    display: flex;
    align-items: center;
    padding: 0.875rem 1.25rem;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 10px;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 500;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all 0.3s ease;
    margin-bottom: 0.5rem;
    border-left: 3px solid transparent;
}

.mobile-nav-sublink:hover,
.mobile-nav-sublink:active {
    background: rgba(110, 7, 7, 0.15);
    border-left-color: #990000;
    color: #fff;
    padding-left: 1.5rem;
}

/* Overlay */
.mobile-overlay-modern {
    position: fixed;
    top: 72px;
    left: 0;
    width: 100%;
    height: calc(100vh - 72px);
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(8px);
    z-index: 1048;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.mobile-menu-modern.show ~ .mobile-overlay-modern {
    opacity: 1;
    visibility: visible;
}

/* Scrollbar Customizado */
.mobile-menu-modern::-webkit-scrollbar {
    width: 6px;
}

.mobile-menu-modern::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.2);
}

.mobile-menu-modern::-webkit-scrollbar-thumb {
    background: rgba(110, 7, 7, 0.5);
    border-radius: 3px;
}

.mobile-menu-modern::-webkit-scrollbar-thumb:hover {
    background: rgba(110, 7, 7, 0.7);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger-modern');
    const mobileMenu = document.querySelector('#mobileMenu');
    
    // Sincronizar hamburger com o estado do menu
    mobileMenu.addEventListener('shown.bs.collapse', function() {
        hamburger.classList.add('collapsed');
    });
    
    mobileMenu.addEventListener('hidden.bs.collapse', function() {
        hamburger.classList.remove('collapsed');
    });
    
    // Fechar menu ao clicar em link
    document.querySelectorAll('.mobile-nav-link, .mobile-nav-sublink').forEach(link => {
        link.addEventListener('click', function() {
            const bsCollapse = new bootstrap.Collapse(mobileMenu, {
                toggle: false
            });
            bsCollapse.hide();
        });
    });
});
</script>
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