# Content-Security-Policy (CSP)

A CSP está em modo **report-only**: o browser só regista violações, não bloqueia nada. Objetivo: observar, afinar e só depois ativar.

- Middleware: `App\Http\Middleware\ContentSecurityPolicyReportOnly` (grupo `web` em `app/Http/Kernel.php`).
- Política: `config/security.php` (origens agrupadas por diretiva).
- Relatórios: `POST /csp-report` (`CspReportController`, sem auth nem CSRF, `throttle:30,1`, corpo > 8 KB descartado, ruído de extensões do browser ignorado). Responde 204.
- Log: canal `csp`, `storage/logs/csp.log` (daily, 14 dias).
- Variáveis `.env`: `CSP_ENABLED=true` (omissão), `CSP_REPORT_ONLY=true` (omissão; `false` emite `Content-Security-Policy` e BLOQUEIA).

## Limitação conhecida

O projeto usa muitos `<script>`/`<style>` inline e atributos `on*="..."`, por isso `script-src` e `style-src` têm `'unsafe-inline'`. Isto enfraquece a proteção contra XSS. A resolver mais tarde com nonces e movendo os handlers `on*=` para `addEventListener`. `img-src` inclui `https:` (aberto) por agora.

## Ver o log

```
tail -f storage/logs/csp.log
grep -h "csp-violation" storage/logs/csp-*.log | grep -o '"blocked-uri":"[^"]*"' | sort | uniq -c | sort -rn | head -30
```

Cada linha tem `blocked-uri`, `violated-directive`, `document-uri` (sem query string) e `source-file`.

## Interpretar violações

- `blocked-uri` é a origem que seria bloqueada; `violated-directive` diz em que diretiva falta (`script-src-elem`, `style-src-elem`, `img-src`, `connect-src`, `frame-src`...).
- `inline` / `eval`: não aparecem enquanto houver `'unsafe-inline'`; `eval` (`unsafe-eval` não está permitido) pode surgir de bibliotecas, decidir caso a caso.
- Origem legítima da aplicação: acrescentar a `config/security.php`.
- Origem desconhecida, ou `source-file` estranho: investigar (extensão do utilizador, script injetado, conteúdo de editor). Extensões `chrome-extension://`, `moz-extension://` e `safari-extension://` já são ignoradas.

## Acrescentar uma origem

Editar a lista da diretiva em `config/security.php`, p.ex. `'script-src' => [..., 'https://novo.exemplo.com']`. Em produção: `php artisan config:clear` (ou `config:cache`).

## Passar de report-only a bloqueio

1. Publicar e observar 1-2 semanas, cobrindo site público e backoffice (mapas, editores, gráficos, cookies/analytics após consentimento).
2. Para cada violação legítima, acrescentar a origem; para as restantes, corrigir ou aceitar o bloqueio.
3. Ensaiar primeiro em staging com `CSP_REPORT_ONLY=false`.
4. Em produção, `CSP_REPORT_ONLY=false` e `php artisan config:clear`. Manter o `/csp-report` ativo para continuar a ver o que é bloqueado. Reverter = voltar a `true`.
5. Fase seguinte: nonces e remoção de `'unsafe-inline'`.

## Origens encontradas e onde são usadas

| Origem | Tipo | Onde |
|---|---|---|
| cdn.jsdelivr.net | script, style, font | Bootstrap, Bootstrap Icons, Swiper, Tom Select, SortableJS, Chart.js, axios (layouts e vistas frontend/admin) |
| cdnjs.cloudflare.com | script, style, font | Font Awesome (layout público), Cropper.js, html2canvas (admin) |
| unpkg.com | script | MarkerClusterer (admin `transport-quotes/map`) |
| cdn.ckeditor.com | script, style, connect | CKEditor 4/5 (admin: notícias, páginas, newsletter) |
| code.jquery.com | script | `layouts/admin.blade.php` (layout antigo) |
| fonts.googleapis.com / fonts.gstatic.com | style / font | Montserrat, Open Sans, Material Symbols (layout público e admin) |
| www.googletagmanager.com | script, connect | gtag.js (`frontend/partials/cookies.blade.php`, só após consentimento) |
| www.google-analytics.com, region1.google-analytics.com, analytics.google.com | connect (e img) | Envio de eventos GA4 |
| static.hotjar.com, script.hotjar.com, *.hotjar.com/.io (https e wss), vars.hotjar.com | script, connect, frame | Hotjar (cookies.blade.php, só após consentimento) |
| maps.googleapis.com | script, connect, img | Google Maps (admin `transport-quotes/map`) |
| www.google.com | frame | Embeds Google Maps (previsto) |
| ui-avatars.com, via.placeholder.com, maps.google.com (ícones) | img | Avatares e placeholders no backoffice (cobertos por `img-src https:`) |
| `data:` / `blob:` | img, font | Imagens inline, cropper/pré-visualizações |
| wa.me, facebook.com, instagram.com | só links (`<a>`) | Não são afetados pela CSP (navegação). |

Notas:
- Sem `fetch`/XHR/WebSocket para domínios externos no código próprio (só `axios`/`fetch` para o próprio site); `connect-src` externos vêm de GA e Hotjar.
- `form-action 'self'`: nenhum `<form action="https://...">` encontrado.
- Não há iframes ativos no site (apenas num ficheiro de estruturas `copy.php` não usado). YouTube não está permitido.
- Ficheiros `welcome.blade.php` (fonts.bunny.net), `coming-soon` e vistas `* copy.php` não estão em uso real e não foram incluídos.
- Emails e PDF não contam (não passam por browser com CSP).

## Verificação

`php artisan test --filter=ContentSecurityPolicyTest`
