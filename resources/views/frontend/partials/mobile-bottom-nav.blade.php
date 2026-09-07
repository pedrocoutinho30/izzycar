{{-- Barra de navegação fixa no fundo do ecrã — única navegação mobile
     (substitui o antigo menu hamburger). Sem Simulador de Custos nem Pedir
     Cotação — ficam só os 5 destinos principais. Desktop mantém-se inalterado. --}}
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
            'label' => 'Legalização',
            'route' => 'frontend.legalization',
            'active' => request()->routeIs('frontend.legalization') || request()->routeIs('frontend.legalization.status'),
            'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        ],
        [
            'label' => 'Viaturas',
            'route' => 'vehicles.list',
            'active' => request()->routeIs('vehicles.list') || request()->routeIs('vehicles.details'),
            'icon' => '<path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11"/><rect x="3" y="11" width="18" height="6" rx="2"/><circle cx="7.5" cy="17" r="1.5"/><circle cx="16.5" cy="17" r="1.5"/>',
        ],
        [
            'label' => 'Notícias',
            'route' => 'frontend.news',
            'active' => request()->routeIs('frontend.news') || request()->routeIs('frontend.news-details'),
            'icon' => '<path d="M4 4h13a2 2 0 0 1 2 2v13a1 1 0 0 1-1 1H6a2 2 0 0 1-2-2z"/><path d="M19 9h2a1 1 0 0 1 1 1v9a2 2 0 0 1-2 2H8"/><line x1="8" y1="8" x2="14" y2="8"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="8" y1="16" x2="16" y2="16"/>',
        ],
    ];
@endphp
<nav class="mobile-bottom-nav" aria-label="Navegação principal (mobile)">
    @foreach($mbnItems as $item)
    <a href="{{ route($item['route']) }}" class="mbn-item {{ $item['active'] ? 'is-active' : '' }}">
        <span class="mbn-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
        </span>
        <span class="mbn-label">{{ $item['label'] }}</span>
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

.mbn-icon-wrap {
    display: flex;
    line-height: 0;
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.mbn-label {
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

/* Toque de animação "automóvel" — o ícone ativo dá um pequeno solavanco de
   suspensão, como um carro a assentar depois de travar. */
.mbn-item.is-active .mbn-icon-wrap {
    animation: mbnSuspensionBounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes mbnSuspensionBounce {
    0%   { transform: translateY(-6px) scale(0.92); }
    60%  { transform: translateY(2px) scale(1.05); }
    100% { transform: translateY(0) scale(1); }
}

@media (prefers-reduced-motion: reduce) {
    .mbn-item.is-active .mbn-icon-wrap { animation: none; }
}
</style>
