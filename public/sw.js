/**
 * IzzyCar PWA — Service Worker
 *
 * Registado pelos layouts do backoffice desktop (admin-v2: scope /gestao/;
 * admin: scope /). Serve sobretudo as notificações push. A app mobile
 * (/gestao/mobile) não regista outro worker; se este já estiver registado no
 * browser, as regras abaixo aplicam-se também a ela.
 *
 * Estratégia (conservadora — nada autenticado nem de clientes vai para a Cache Storage):
 *  - Só se guardam assets estáticos do próprio site, por LISTA DE PERMITIDOS
 *    (/css, /js, /fonts, /img, /images, /vendor). Tudo o resto fica de fora,
 *    incluindo /storage e /uploads (fotos e documentos de clientes, que o
 *    Apache serve como ficheiros estáticos sem o cabeçalho "private").
 *  - Navegações: só rede, com offline.html se não houver ligação (nunca uma
 *    página guardada).
 *  - Não-GET, outras origens e restantes pedidos (JSON, fragmentos, ...): o
 *    worker não interfere.
 *
 * As funções de decisão (isPrivatePath, isCacheableRequest, isCacheableResponse)
 * são puras e exportadas no fim para os testes em node; no browser não há
 * `module` e esse bloco é ignorado.
 */

// v3: lista de permitidos. A v1 guardava JSON/HTML de /gestao e a v2 ainda
// guardava ficheiros de /storage; o activate apaga essas caches.
const CACHE_VERSION = 'izzycar-v3';

// Assets pré-cached no install (cada um à parte: se algum faltar, o install não falha).
const PRECACHE_ASSETS = [
  '/offline.html',
  '/css/sb-admin-2.min.css',
  '/img/logo-simples.png',
  '/img/logo-arredondado.png',
];

// Pastas de public/ só com assets do site (nada carregado por utilizadores).
// /vendor tem os assets publicados dos pacotes (bootstrap, fontawesome, filemanager);
// os ficheiros do filemanager vão para /storage, que não está aqui.
const STATIC_PATH = /^\/(css|js|fonts|img|images|vendor)\//;

// Caminhos com sessão/dados de clientes: nunca guardados nem servidos da cache.
const PRIVATE_PATHS = [
  '/gestao',
  '/login',
  '/logout',
  '/password',
  '/definir-password',
  '/two-factor',
  '/manual-angariador',
  '/laravel-filemanager',
  '/registo-angariador',
  '/sanctum',
  '/telescope',
  '/horizon',
  '/api',
  '/broadcasting',
  '/csp-report',
  '/storage',
  '/uploads',
];

/**
 * Caminho como o servidor o vai ver: descodificado (%61 → a), com barras
 * repetidas reduzidas, em minúsculas e (salvo keepIndex) sem o prefixo /index.php.
 * Devolve null se não se conseguir descodificar ou se tiver segmentos . / ..
 * (quem chama trata null como privado / não guardável).
 */
const normalizePath = (pathname, keepIndex = false) => {
  let path;
  try {
    path = decodeURIComponent(String(pathname || ''));
  } catch (e) {
    return null;
  }
  path = path.replace(/\\/g, '/').replace(/\/{2,}/g, '/').toLowerCase();
  if (/(^|\/)\.\.?(\/|$)/.test(path) || /[\x00-\x1f\x7f]/.test(path)) return null;
  while (!keepIndex && (path === '/index.php' || path.startsWith('/index.php/'))) {
    path = path.slice('/index.php'.length) || '/';
  }
  return path.startsWith('/') ? path : '/' + path;
};

const isPrivatePath = (pathname) => {
  const path = normalizePath(pathname);
  if (path === null) return true;
  return PRIVATE_PATHS.some((p) => path === p || path.startsWith(p + '/'));
};

// Asset do site? O caminho tem de começar mesmo por uma pasta permitida
// (/index.php/css/... passa pelo Laravel, por isso não conta).
const isStaticPublicPath = (pathname) => {
  const path = normalizePath(pathname, true);
  return path !== null && !isPrivatePath(pathname) && STATIC_PATH.test(path);
};

// O pedido pode ir à cache? Só GET, da mesma origem, sem ser navegação e para um asset permitido.
const isCacheableRequest = (request, origin) => {
  if (!request || request.method !== 'GET' || request.mode === 'navigate') return false;
  let url;
  try {
    url = new URL(request.url);
  } catch (e) {
    return false;
  }
  return url.origin === origin && isStaticPublicPath(url.pathname);
};

// A resposta pode ser guardada? Própria, completa, sem redirect e sem cabeçalhos que o proíbam.
// (No browser o Set-Cookie não é visível para o worker; a verificação fica por precaução.)
const isCacheableResponse = (response) => {
  if (!response || response.status !== 200 || response.redirected) return false;
  if (response.type && response.type !== 'basic' && response.type !== 'default') return false;
  const headers = response.headers;
  const get = (name) => (headers && typeof headers.get === 'function' ? headers.get(name) : null);
  const cacheControl = String(get('Cache-Control') || '').toLowerCase();
  if (/no-store|private/.test(cacheControl)) return false;
  if (get('Set-Cookie')) return false;
  return true;
};

if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    CACHE_VERSION, PRECACHE_ASSETS, normalizePath, isPrivatePath, isStaticPublicPath,
    isCacheableRequest, isCacheableResponse,
  };
}

if (typeof self !== 'undefined' && typeof self.addEventListener === 'function') {
  const offlineResponse = () =>
    caches.match('/offline.html').then(
      (cached) => cached || new Response('Sem ligação', { status: 503 })
    );

  // Apaga todas as caches que não sejam desta versão (podem ter páginas autenticadas
  // ou ficheiros de clientes).
  const purgeOldCaches = () =>
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_VERSION).map((key) => caches.delete(key)))
    );

  // ── Install ────────────────────────────────────────────────────────────────
  // Cada asset à parte e sem falhar: se /offline.html (ou outro) não existir no
  // servidor, o worker novo instala na mesma e substitui o antigo.
  self.addEventListener('install', (event) => {
    event.waitUntil(
      caches.open(CACHE_VERSION)
        .then((cache) => Promise.allSettled(PRECACHE_ASSETS.map((asset) => cache.add(asset))))
        .catch(() => {})
        .then(() => self.skipWaiting())
    );
  });

  // ── Activate ───────────────────────────────────────────────────────────────
  // Apaga as caches antigas, assume o controlo das páginas e volta a apagar:
  // um worker antigo de outra registration (os layouts registam scopes / e
  // /gestao/, com a mesma Cache Storage) pode ter recriado a cache entretanto.
  self.addEventListener('activate', (event) => {
    event.waitUntil(
      purgeOldCaches()
        .then(() => self.clients.claim())
        .then(purgeOldCaches)
    );
  });

  // ── Fetch ──────────────────────────────────────────────────────────────────
  self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Só GET; POST/PUT/PATCH/DELETE seguem directamente para a rede.
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // Cross-origin (CDN, fonts externas, etc.): não interfere.
    if (url.origin !== self.location.origin) return;

    // ── Navegação (HTML): só rede, offline.html sem ligação ───────────────
    // Inclui /gestao/mobile e o login: nunca se mostra uma página guardada.
    if (request.mode === 'navigate') {
      event.respondWith(fetch(request).catch(offlineResponse));
      return;
    }

    // Fora da lista de permitidos (privados, /storage, JSON, ...): o worker não toca.
    if (!isCacheableRequest(request, self.location.origin)) return;

    // ── Assets estáticos do site: cache-first, actualiza em background ─────
    event.respondWith(
      caches.match(request).then((cached) => {
        const networkFetch = fetch(request).then((response) => {
          if (isCacheableResponse(response)) {
            const clone = response.clone();
            caches.open(CACHE_VERSION).then((cache) => cache.put(request, clone));
          }
          return response;
        });
        if (cached) {
          // Devolve a cache já e actualiza em background (erros de rede ignorados).
          networkFetch.catch(() => {});
          return cached;
        }
        return networkFetch;
      })
    );
  });

  // ── Push notifications ────────────────────────────────────────────────────
  self.addEventListener('push', (event) => {
    let payload = {};
    try {
      payload = event.data ? event.data.json() : {};
    } catch (e) {
      payload = { title: 'IzzyCar', body: event.data ? event.data.text() : '' };
    }

    const title = payload.title || 'IzzyCar';
    const options = {
      body: payload.body || '',
      icon: payload.icon || '/img/logo-arredondado.png',
      badge: payload.badge || '/img/logo-simples.png',
      tag: payload.tag || undefined,
      data: payload.data || {},
    };

    event.waitUntil(self.registration.showNotification(title, options));
  });

  // Clique na notificação: foca uma aba já aberta na app, ou abre uma nova no
  // URL indicado pelo payload (ex.: a ficha da lead/tarefa em causa).
  self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = (event.notification.data && event.notification.data.url) || '/gestao/v2/dashboard';

    event.waitUntil(
      clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
        for (const client of clientList) {
          if (client.url.includes(targetUrl) && 'focus' in client) return client.focus();
        }
        for (const client of clientList) {
          if ('focus' in client) {
            client.focus();
            if ('navigate' in client) return client.navigate(targetUrl);
          }
        }
        if (clients.openWindow) return clients.openWindow(targetUrl);
      })
    );
  });
}
