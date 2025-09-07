

        <div class="desktop-only">
            @include('frontend.partials.menu-desktop')
        </div>

        <div class="mobile-only">
            @include('frontend.partials.menu-mobile')
        </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // breakpoint mobile < lg
  const MOBILE_QUERY = '(max-width: 991.98px)';

  const normPath = (p) => {
    if (!p) return null;
    try {
      // constrói URL absolutando com o origin para lidar com hrefs relativos/absolutos
      const u = new URL(p, window.location.origin);
      let np = u.pathname.replace(/\/+$/, ''); // remove trailing slash
      return np === '' ? '/' : np;
    } catch (e) {
      return null;
    }
  };

  const getLinksForMode = (isMobile) => {
    const selector = isMobile ? '.mobile-only' : '.desktop-only';
    const container = document.querySelector(selector);
    if (!container) return [];
    return Array.from(container.querySelectorAll('a.nav-link[href], a.dropdown-item[href]'))
      .filter(a => {
        const href = a.getAttribute('href');
        return href && href !== '#' && !href.startsWith('javascript:');
      });
  };

  const clearActivesIn = (isMobile) => {
    const selector = isMobile ? '.mobile-only' : '.desktop-only';
    document.querySelectorAll(selector + ' .active').forEach(el => el.classList.remove('active'));
  };

  const activateLink = (a, isMobile) => {
    if (!a) return;
    a.classList.add('active');
    const dropdown = a.closest('.nav-item.dropdown');
    if (dropdown) {
      const toggle = dropdown.querySelector('.dropdown-toggle');
      if (toggle) toggle.classList.add('active');
    }

    // Se mobile, fecha o collapse (se aberto)
    // if (isMobile) {
    //   const mobileCollapse = document.getElementById('mobileMenu'); // assegura que o id é este
    //   if (mobileCollapse) {
    //     try {
    //       const bsInstance = bootstrap.Collapse.getInstance(mobileCollapse) || new bootstrap.Collapse(mobileCollapse, {toggle:false});
    //       bsInstance.hide();
    //     } catch (err) {
    //       // bootstrap não disponível? ignora.
    //     }
    //   }
    // }
  };

  const updateActive = () => {
    const isMobile = window.matchMedia(MOBILE_QUERY).matches;
    const links = getLinksForMode(isMobile);
    if (!links.length) return;

    clearActivesIn(isMobile);

    const currentPath = normPath(window.location.pathname);

    let winner = null;
    // exact match
    for (const a of links) {
      const p = normPath(a.getAttribute('href'));
      if (p && p === currentPath) { winner = a; break; }
    }

    // fallback: longest matching prefix
    if (!winner) {
      let bestLen = 0;
      for (const a of links) {
        const p = normPath(a.getAttribute('href'));
        if (!p || p === '/') continue;
        if (currentPath === p || currentPath.startsWith(p + '/')) {
          if (p.length > bestLen) { bestLen = p.length; winner = a; }
        }
      }
    }

    if (winner) activateLink(winner, isMobile);

    // add click handlers (mantém 1 ativo no clique; útil em SPA/PJAX)
    links.forEach(a => {
      // remove handlers duplicados
      a.removeEventListener('click', a.__menuActiveHandler);
      a.__menuActiveHandler = function (ev) {
        clearActivesIn(isMobile);
        activateLink(this, isMobile);
      };
      a.addEventListener('click', a.__menuActiveHandler);
    });
  };

  // run on load
  updateActive();

  // rerun on resize (debounced)
  let _t;
  window.addEventListener('resize', function() {
    clearTimeout(_t);
    _t = setTimeout(updateActive, 120);
  });

  // rerun when bootstrap collapse is shown (em caso de abrir/fechar)
  document.addEventListener('shown.bs.collapse', function(e){ updateActive(); });
  document.addEventListener('hidden.bs.collapse', function(e){ updateActive(); });
});
</script>






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