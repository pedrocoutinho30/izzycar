{{-- Barra de navegação fixa no fundo do ecrã — só mobile/tablet (estilo app),
     inspirada nos mockups de viaturas/simulador. Desktop mantém-se inalterado. --}}
@php
    $mbnItems = [
        [
            'label' => 'Home',
            'route' => 'frontend.home',
            'active' => request()->routeIs('frontend.home'),
            'icon' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        ],
        [
            'label' => 'Importação',
            'route' => 'frontend.import',
            'active' => request()->routeIs('frontend.import') || request()->routeIs('frontend.form-import'),
            'icon' => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
        ],
        [
            'label' => 'Viaturas',
            'route' => 'vehicles.list',
            'active' => request()->routeIs('vehicles.list') || request()->routeIs('vehicles.details'),
            'icon' => '<path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11"/><rect x="3" y="11" width="18" height="6" rx="2"/><circle cx="7.5" cy="17" r="1.5"/><circle cx="16.5" cy="17" r="1.5"/>',
        ],
        [
            'label' => 'Simulador',
            'route' => 'frontend.cost-simulator',
            'active' => request()->routeIs('frontend.cost-simulator') || request()->routeIs('frontend.cost-simulator.result'),
            'icon' => '<line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        ],
    ];
@endphp
<nav class="mobile-bottom-nav" aria-label="Navegação principal (mobile)">
    @foreach($mbnItems as $item)
    <a href="{{ route($item['route']) }}" class="mbn-item {{ $item['active'] ? 'is-active' : '' }}">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
        <span>{{ $item['label'] }}</span>
    </a>
    @endforeach
</nav>

<style>
:root {
    --mbn-h: 64px;
}

.mobile-bottom-nav {
    display: none;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1030;
    height: var(--mbn-h);
    padding-bottom: env(safe-area-inset-bottom);
    background: linear-gradient(135deg, #111111 0%, #1a1a1a 100%);
    border-top: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 -4px 20px rgba(0,0,0,0.3);
}

@media (max-width: 991.98px) {
    .mobile-bottom-nav { display: flex; align-items: stretch; justify-content: space-around; }
}

.mbn-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.2rem;
    color: rgba(255,255,255,0.55);
    text-decoration: none;
    padding: 0.4rem 0.25rem;
    transition: color 0.2s ease;
}

.mbn-item span {
    font-size: 0.66rem;
    font-weight: 600;
    letter-spacing: 0.02em;
}

.mbn-item.is-active {
    color: #fff;
}

.mbn-item.is-active svg {
    color: #ff5555;
}
</style>
