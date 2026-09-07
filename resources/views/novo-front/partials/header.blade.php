{{-- Desktop: menu atual, inalterado --}}
<div class="novo-desktop-only">
  @include('frontend.partials.menu-desktop')
</div>

{{-- Mobile: apenas uma barra superior fina com o logo — a navegação
     acontece toda na barra inferior de ícones (mobile-bottom-nav). --}}
<div class="novo-mobile-only">
  <nav class="novo-mobile-topbar">
    <a href="{{ route('novo-front.home') }}" class="novo-mobile-brand">
      <picture>
        <source srcset="{{ asset(preg_replace('/\.(png|jpe?g)$/i', '.webp', $logotipo)) }}" type="image/webp">
        <img src="{{ asset($logotipo) }}" alt="Izzycar" width="121" height="80" fetchpriority="high">
      </picture>
    </a>
  </nav>
</div>

<style>
.novo-desktop-only { display: block; }
.novo-mobile-only { display: none; }

.novo-mobile-topbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1030;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #111111 0%, #1a1a1a 100%);
    box-shadow: 0 2px 12px rgba(0,0,0,.25);
}
.novo-mobile-brand { display: flex; align-items: center; height: 100%; }
.novo-mobile-brand img { height: 36px; width: auto; }

@media (max-width: 991.98px) {
    .novo-desktop-only { display: none; }
    .novo-mobile-only { display: block; }
}
</style>
