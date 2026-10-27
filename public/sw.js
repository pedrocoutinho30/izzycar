/**
 * IzzyCar PWA — Service Worker
 *
 * Registado pelos layouts do backoffice desktop (admin-v2: scope /gestao/;
 * admin: scope /). Serve sobretudo as notificações push. A app mobile
 * (/gestao/mobile) não regista outro worker; se este já estiver registado no
 * browser, as regras abaixo aplicam-se também a ela.
 *
 * Estratégia (conservadora — nada autenticado vai para a Cache Storage):
 *  - Áreas privadas (/gestao, /login, /logout, /password, ...): nunca cache.
 *    Navegações: só rede, com offline.html se não houver ligação (nunca uma
 *    página antiga). Restantes pedidos: o worker não interfere.
 *  - Assets estáticos públicos (CSS/JS/fontes/imagens): cache-first, actualiza
 *    em background, e só se a resposta puder ser guardada (sem no-store/private).
 *  - Navegação noutras páginas: só rede, com offline.html como fallback.
 *  - Tudo o resto (JSON, fragmentos, etc.): o worker não interfere nem guarda.
 */

// v2: as versões anteriores podiam guardar respostas JSON/HTML de /gestao
// (o "resto" era network-first com cache). Mudar a versão apaga essas caches
// no activate.
const CACHE_VERSION = 'izzycar-v2';

// Assets críticos pré-cached no install
const PRECACHE_ASSETS = [
  '/offline.html',
  '/css/sb-admin-2.min.css',
  '/img/logo-simples.png',
  '/img/logo-arredondado.png',
];

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
  '/api/',
  '/broadcasting',
  '/csp-report',
];

const isPrivatePath = (pathname) =>
  PRIVATE_PATHS.some((p) => pathname === p || pathname.startsWith(p.endsWith('/') ? p : p + '/'));

const isStaticAsset = (pathname) =>
  /\.(css|js|png|jpg|jpeg|gif|webp|svg|ico|woff|woff2|ttf|eot)$/i.test(pathname);

// Só guarda respostas próprias, completas e que o servidor deixa guardar.
// (Set-Cookie não é visível para o worker; por isso só se guardam assets estáticos.)
const isCacheable = (response) => {
  if (!response || response.status !== 200 || response.type !== 'basic') return false;
  const cacheControl = (response.headers.get('Cache-Control') || '').toLowerCase();
  return !/no-store|private/.test(cacheControl);
};

const offlineResponse = () =>
  caches.match('/offline.html').then(
    (cached) => cached || new Response('Sem ligação', { status: 503 })
  );

// ── Install ──────────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_VERSION)
      .then((cache) => cache.addAll(PRECACHE_ASSETS))
      .then(() => self.skipWaiting())
  );
});

// ── Activate ─────────────────────────────────────────────────────────────────
// Apaga todas as caches de versões anteriores (podem conter páginas autenticadas).
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) =>
        Promise.all(
          keys
            .filter((key) => key !== CACHE_VERSION)
            .map((key) => caches.delete(key))
        )
      )
      .then(() => self.clients.claim())
  );
});

// ── Fetch ─────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
  const { request } = event;

  // Só GET; POST/PUT/PATCH/DELETE seguem directamente para a rede.
  if (request.method !== 'GET') return;

  const url = new URL(request.url);

  // Cross-origin (CDN, fonts externas, etc.): não interfere.
  if (url.origin !== location.origin) return;

  // ── Navegação (HTML): só rede, offline.html sem ligação ─────────────────
  // Inclui /gestao/mobile e o login: nunca se mostra uma página guardada.
  if (request.mode === 'navigate') {
    event.respondWith(fetch(request).catch(offlineResponse));
    return;
  }

  // Áreas privadas: o worker não toca (nem cache, nem fallback).
  if (isPrivatePath(url.pathname)) return;

  // ── Assets estáticos públicos: cache-first, actualiza em background ─────
  if (isStaticAsset(url.pathname)) {
    event.respondWith(
      caches.match(request).then((cached) => {
        const networkFetch = fetch(request).then((response) => {
          if (isCacheable(response)) {
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
    return;
  }

  // Resto (JSON, fragmentos HTML, pedidos AJAX): sem cache — o browser trata.
});

// ── Push notifications ──────────────────────────────────────────────────────
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
