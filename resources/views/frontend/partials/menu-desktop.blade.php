<nav class="navbar-modern navbar-expand-lg fixed-top">
    <div class="container d-flex justify-content-between align-items-center">
        {{-- Logo --}}
        <a class="navbar-brand-modern" href="{{ route('frontend.home') }}">
            <img src="{{ asset($logotipo) ?? '/images/default-logo.png' }}" alt="Logo" class="navbar-logo-modern"  loading="lazy">
        </a>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav-modern ms-auto">
                @foreach($menus->where('parent_id', null) as $menu)
                @php
                $hasChildren = $menu->children_active->count() > 0;
                @endphp
                
                @if(!$hasChildren)
                <li class="nav-item-modern">
                    <a class="nav-link-modern {{ request()->is(trim($menu->url, '/')) || request()->is(trim($menu->url, '/').'/*') ? 'active' : '' }}" href="{{ $menu->url }}">
                        <span>{{ $menu->title }}</span>
                    </a>
                </li>
                @else
                <li class="nav-item-modern dropdown-modern">
                    <a class="nav-link-modern dropdown-toggle" href="#" id="navbarDropdown-{{ $menu->id }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span>{{ $menu->title }}</span>
                    </a>
                    <ul class="dropdown-menu-modern" aria-labelledby="navbarDropdown-{{ $menu->id }}">
                        @foreach($menu->children_active as $child)
                        <li><a class="dropdown-item-modern" href="{{ $child->url }}">{{ $child->title }}</a></li>
                        @endforeach
                    </ul>
                </li>
                @endif
                @endforeach
            </ul>
        </div>
    </div>
</nav>

<style>
/* Navbar Moderno */
.navbar-modern {
    background: linear-gradient(135deg, #111111 0%, #1a1a1a 100%);
    padding: 1rem 0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
    z-index: 1000;
}

.navbar-modern.scrolled {
    padding: 0.5rem 0;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.4);
}

.navbar-brand-modern {
    display: flex;
    align-items: center;
    transition: transform 0.3s ease;
}

.navbar-brand-modern:hover {
    transform: scale(1.05);
}

.navbar-logo-modern {
    height: 100px;
    width: auto;
    transition: all 0.3s ease;
}

.navbar-modern.scrolled .navbar-logo-modern {
    height: 40px;
}

.navbar-nav-modern {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.nav-item-modern {
    position: relative;
    list-style: none;
}

.nav-link-modern {
    position: relative;
    color: rgba(255, 255, 255, 0.9) !important;
    font-weight: 600;
    font-size: 1rem;
    padding: 0.75rem 1.25rem;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-link-modern span {
    position: relative;
}

.nav-link-modern::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #990000 0%, #6e0707 100%);
    transform: translateX(-50%);
    transition: width 0.3s ease;
}

.nav-link-modern:hover {
    color: #fff !important;
}

.nav-link-modern:hover::before {
    width: 80%;
}

.nav-link-modern.active::before {
    width: 80%;
}

/* Dropdown Moderno */
.dropdown-modern {
    position: relative;
}

.dropdown-menu-modern {
    background: linear-gradient(135deg, #1a1a1a 0%, #111111 100%);
    border: 1px solid rgba(110, 7, 7, 0.3);
    border-radius: 12px;
    padding: 0.5rem 0;
    margin-top: 0.5rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
    min-width: 220px;
    backdrop-filter: blur(10px);
}

.dropdown-item-modern {
    color: rgba(255, 255, 255, 0.9);
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: block;
    position: relative;
    overflow: hidden;
}

.dropdown-item-modern::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 3px;
    height: 100%;
    background: linear-gradient(180deg, #990000 0%, #6e0707 100%);
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.dropdown-item-modern:hover {
    background: rgba(110, 7, 7, 0.2);
    color: #fff;
    padding-left: 2rem;
}

.dropdown-item-modern:hover::before {
    transform: scaleY(1);
}

@media (max-width: 992px) {
    .navbar-logo-modern {
        height: 40px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar-modern');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
});
</script>