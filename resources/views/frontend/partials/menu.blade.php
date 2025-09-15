<div class="collapse navbar-collapse d-lg-none" id="navbarNav">
    <ul class="navbar-nav w-100">
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
        <li class="nav-item">
            <button class="nav-link w-100 text-start dropdown-toggle {{ $active ? 'active' : '' }}"
                data-bs-toggle="collapse"
                data-bs-target="#submenu{{ $menu->id }}">
                {{ $menu->title }}
            </button>
            <div class="collapse" id="submenu{{ $menu->id }}">
                <ul class="list-unstyled ps-3">
                    @foreach($menu->children_active as $child)
                    <li>
                        <a class="dropdown-item {{ $child->route_name && Route::currentRouteName() == $child->route_name ? 'active' : '' }}"
                            href="{{ route($child->route_name) }}">
                            {{ $child->title }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </li>
        @endif
        @endforeach
    </ul>
</div>

