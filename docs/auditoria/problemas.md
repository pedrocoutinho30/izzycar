# Problemas encontrados

Revisão estática (ver README). Parte A: segurança e autorização (SEG-xxx). Parte B: lógica, dados e integrações (LOG-xxx). Testes existentes correram em sqlite em memória: 764 passaram, 1 falhou (ExampleTest, já conhecido).

---

# Auditoria IzzyCar - Segurança e autorização

Data: 2026-10-31. Tipo: **revisão estática** (leitura de código e configuração). Nada foi executado contra a aplicação, nenhuma ligação à BD remota, nenhum pedido a serviços da aplicação. Corri apenas `composer audit` (consulta a base de avisos, só leitura) e `npm audit --offline`. Não foram escritas credenciais nem dados pessoais neste relatório; onde há segredos só se indica ficheiro e tipo.

Convenção de reprodução: "seguro" = sempre em ambiente local com sqlite em memória (testes PHPUnit) ou numa BD descartável, nunca no `.env` local (que aponta para a BD remota).

Resumo de gravidades: 2 altas, 8 médias, 8 baixas (SEG-001 a SEG-018).

---

## Altas

### SEG-001 - `PERMISSIONS_ENFORCE` desligado: o catálogo de permissões não bloqueia nada
- **Gravidade:** alta. **Confiança:** confirmado (código); o impacto concreto depende de que perfis/utilizadores existem em produção.
- **Evidência:** `config/permissions.php:33` (`'enforce' => env('PERMISSIONS_ENFORCE', false)`); `app/Http/Middleware/AuthorizeResource.php:40-62` (com enforce desligado só faz `Log::warning('would_block')` e chama `$next`); grupo de rotas `routes/web.php:106` (`auth`, `restrictAngariador`, `authorizeResource`); `app/Http/Middleware/RestrictAngariadorArea.php` (só barra o utilizador "angariador puro").
- **Cenário:** qualquer utilizador autenticado com pelo menos um perfil que não seja só "angariador" (por exemplo um perfil "cms", ou um comercial com `leads.view.own`) alcança TODAS as rotas de `/gestao`: utilizadores (`admin.v2.users.*`), perfis (`admin.v2.roles.*`), definições, movimentos financeiros, relatórios, exportações de leads/clientes, ficheiros privados, rotas `admin_only` (CMS de páginas, `page-contents`, `menus`, `email-preview`, `ad-searches`) e impersonation. Só o código das rotas mobile e do portal angariador verifica o dono do registo; os controllers desktop (`LeadV2Controller::show/updateStatus/convert/destroy`, `ClientV2Controller`, `ProposalV2Controller`) fazem `findOrFail($id)` sem filtro de dono, pelo que o âmbito "próprios" não existe no desktop.
- **Impacto:** perda de confidencialidade (dados pessoais de clientes e leads, valores financeiros, documentos) e de integridade (alterar/apagar registos, criar utilizadores com perfis intermédios, editar perfis `RoleV2Controller::update` e atribuir-se permissões). Mitigações existentes: só o admin pode dar/retirar o perfil `admin` (`UserV2Controller::ensureCanAssignRoles`), o `admin` não é editável nos perfis, as passwords seguem política forte. Utilizadores afectados: todos os clientes/leads (RGPD) e a empresa.
- **Reprodução segura (local, sqlite):** num teste com `RefreshesDatabaseWithoutMysqlOnlyMigrations`, criar utilizador com perfil sem permissões (ex. "cms"), `actingAs`, `get(route('admin.v2.users.index'))` e `get(route('admin.v2.leads.show', $lead))` e verificar 200; repetir com `config(['permissions.enforce' => true])` e verificar 403. Em produção, só ler `storage/logs/permissions*.log` (`would_block`) e `php artisan permissions:report` (leitura) para ver o que já falharia.
- **Correção:** (1) analisar o log `would_block` e corrigir os perfis reais; (2) ligar `PERMISSIONS_ENFORCE=true` primeiro em staging e depois em produção (reversível: voltar a `false` + `config:clear`); (3) até lá, acrescentar verificação do dono (`PermissionService::allows`/`applyScope`) nos controllers desktop de leads/clientes/cotações, como já existe nos mobile; (4) fixar o default para `true` no config (`env('PERMISSIONS_ENFORCE', true)`) para que um `.env` sem a variável falhe de forma segura.
- **Validar:** testes de feature por perfil com enforce ligado (os testes `PermissionCatalogTest` já cobrem o catálogo, falta cobrir acesso efectivo); confirmar 403 em users/roles para o perfil "cms".
- **Esforço:** médio (1-3 dias, o grosso é afinar perfis). **Dependências:** decisão do Pedro sobre perfis reais; memória do projeto diz que o refactor de permissões granulares está pendente.

### SEG-002 - Ficheiro versionado com hash de password e email pessoal (`users/`), e restos de outro CMS na raiz
- **Gravidade:** alta. **Confiança:** confirmado (ficheiro rastreado por git).
- **Evidência:** `git ls-files users` devolve `users/<email>.yaml` (3 chaves: `name`, `id`, `password_hash`); `please` (CLI de outro framework, rastreado), `server.php`, `retomas` (nome de ficheiro, mas contém notas de esquema), `plataforma vendas`, `info-*`, `AutoScoutSearch/*.csv` e `scripts/` (scrapers, listas, `soup_debug.html`) também versionados. `.gitignore` não cobre `users/`.
- **Cenário:** quem tiver acesso ao repositório (colaboradores, forks, backups, histórico git, clone no servidor) obtém o hash de uma conta, potencialmente reaproveitada noutro serviço, e um email pessoal. Um hash bcrypt é crackável offline se a password for fraca. O mesmo ficheiro vive no histórico mesmo que seja apagado agora.
- **Impacto:** possível comprometimento de uma conta de administração (se a password foi reutilizada) e exposição de dado pessoal. Utilizador afectado: o titular da conta.
- **Reprodução segura:** `git ls-files users`; `git log --all --oneline -- users` (só leitura). Não abrir o conteúdo em canais partilhados.
- **Correção:** confirmar se a conta existe em produção/outros serviços e **mudar essa password**; `git rm -r --cached users` e acrescentar `/users` ao `.gitignore`; se o repositório for remoto/partilhado, reescrever o histórico (`git filter-repo`) e rodar a password na mesma; remover `please`/`server.php` se não forem usados (confirmar com `grep`); mover `info-*`, `plataforma vendas`, `retomas` e CSVs de cotações reais para fora do repositório se tiverem dados de negócio.
- **Validar:** `git ls-files | grep -E '^users/'` sem resultados; `git log --all -- users` vazio após reescrita.
- **Esforço:** baixo (horas) sem reescrita de histórico; médio com reescrita. **Dependências:** saber onde está o repositório remoto e quem tem acesso.

---

## Médias

### SEG-003 - Webhook WhatsApp: HMAC calculado com segredo vazio se `WHATSAPP_APP_SECRET` não estiver definido
- **Gravidade:** média. **Confiança:** suspeito (depende do `.env` de produção, que não li).
- **Evidência:** `app/Http/Middleware/VerifyWhatsAppSignature.php:20-24` (`hash_hmac('sha256', corpo, (string) config('services.whatsapp.app_secret'))` sem verificar que o segredo existe); `config/services.php:46-51`; `routes/api.php:34-39`; `.env.example` lista `WHATSAPP_APP_SECRET=` vazio.
- **Cenário:** com segredo vazio, um atacante calcula `hash_hmac('sha256', $corpo, '')` e passa a assinatura. O endpoint `POST /api/whatsapp/webhook` cria `PreLead` (pendentes de aprovação) e escreve texto livre na BD.
- **Impacto:** poluição/spam de pré-leads e envio de texto malicioso para o ecrã do backoffice (ver SEG-006); sem execução directa. Também `verify()` compara `hub_verify_token` com `===`: se `WHATSAPP_VERIFY_TOKEN` for nulo e o pedido não enviar o token, `null === null` devolve o desafio (inofensivo, mas indica falta de validação de configuração).
- **Reprodução segura (teste local):** `config(['services.whatsapp.app_secret' => null])`, POST ao webhook com o header calculado com chave vazia, esperar 200 em vez de 403.
- **Correção:** no middleware, `abort(503)` ou 403 se o segredo estiver vazio; no `verify()`, exigir token não vazio e usar `hash_equals`.
- **Validar:** teste com segredo vazio devolve 403; teste com segredo certo continua a 200.
- **Esforço:** baixo (< 1 h). **Dependências:** confirmar o `.env` de produção.

### SEG-004 - SSRF no "importar de anúncio": validação do domínio com `str_contains`
- **Gravidade:** média. **Confiança:** confirmado (código).
- **Evidência:** `app/Services/VehicleListingImportService.php:19-23` (`str_contains($host, 'autoscout24.')`) e `:42-47` (Guzzle `get($url)` com redirects por omissão); rota `routes/web.php:259` (`admin.v2.proposals.importFromListing`), validação só `url|max:1000` em `ProposalV2Controller.php:281-285`.
- **Cenário:** um URL como `http://autoscout24.exemplo-do-atacante.tld/...` passa o teste e o servidor faz o pedido; o servidor do atacante pode redireccionar para `http://169.254.169.254/` ou serviços internos. O corpo só é devolvido quando contém `__NEXT_DATA__`, o que torna a exfiltração limitada (SSRF quase cego), mas permite sondar a rede interna.
- **Impacto:** acesso a serviços internos do alojamento; utilizadores com acesso à rota (todos os perfis não angariador, ver SEG-001).
- **Reprodução segura:** teste unitário com mock do cliente Guzzle a verificar que `import('http://autoscout24.evil.test/x')` entra em `importFromAutoScout24`.
- **Correção:** validar `parse_url` com host exacto/sufixo (`=== 'www.autoscout24.pt'` ou `str_ends_with($host, '.autoscout24.com')` precedido de ponto), forçar `https`, desativar redirects (`'allow_redirects' => false` ou limitar a hosts permitidos) e rejeitar IPs privados.
- **Validar:** testes com `autoscout24.evil.test`, `http://127.0.0.1`, `https://www.autoscout24.pt/anuncio`.
- **Esforço:** baixo.

### SEG-005 - Push subscription sem validação do `endpoint` (SSRF)
- **Gravidade:** média/baixa. **Confiança:** suspeito.
- **Evidência:** `app/Http/Controllers/Admin/PushSubscriptionController.php:17-26` (`'endpoint' => ['required','string']`); rota partilhada (`config/permissions.php`, `admin.v2.push-subscriptions.*`).
- **Cenário:** um utilizador autenticado regista como endpoint um URL interno; ao enviar notificações (`PushNotifier::notifyStaff`) o servidor faz POST a esse URL.
- **Impacto:** pedidos POST cegos a serviços internos, com corpo cifrado; esforço do atacante elevado.
- **Correção:** `url`, `starts_with:https://`, `max:500` e lista de hosts de push conhecidos (`fcm.googleapis.com`, `*.push.apple.com`, `updates.push.services.mozilla.com`, `*.notify.windows.com`).
- **Validar:** teste com endpoint `http://localhost` rejeitado. **Esforço:** baixo.

### SEG-006 - HTML sem sanitização em conteúdo do site (XSS armazenado) com CSP só em report-only
- **Gravidade:** média. **Confiança:** confirmado (padrão); exploração exige conta do backoffice com acesso a conteúdo (ou webhook, SEG-003).
- **Evidência:** `app/Support/RichText.php` só troca cores, não sanitiza; usos de `{!! RichText::display(...) !!}` em `resources/views/frontend/news-details.blade.php:113,138,168`, `import.blade.php:259,295,391,466`, `legalization.blade.php:172,193`, `partials/vertical-tabs.blade.php:313`, `horizontal-tabs.blade.php:42`, `accordion-mobile.blade.php:149`; `{!! $item['a'] !!}` em `admin/v2/manual/faq.blade.php:74`; `{!! $tableIsv !!}` em `frontend/cost-simulator/result.blade.php:115` (HTML gerado e guardado em `cost_simulators.isv_table`, derivado de `ImportSimulator` com input público); `PageContentController.php:24,29` (`PageContent::create($request->all())`); `ezyang/htmlpurifier` está instalado mas não é usado (`grep Purifier app` vazio). CSP: `config/security.php:31-33` (`'unsafe-inline'`) e report-only (`docs/seguranca-csp.md`).
- **Cenário:** um utilizador do backoffice (ou conta comprometida) grava `<script>`/`onerror=` num campo de notícia/página; corre no browser de todos os visitantes, e do admin quando pré-visualiza. Para `isv_table`, rever se algum campo do pedido público entra no HTML sem escape em `ImportSimulator::calcular`.
- **Impacto:** roubo de sessão do backoffice (cookie é `HttpOnly`, mas o script pode agir como o utilizador), defacement, phishing no domínio. Visitantes e staff.
- **Reprodução segura (local):** criar uma página/notícia de teste com `<img src=x onerror=...>` e renderizar a vista no teste; confirmar que o HTML sai intacto.
- **Correção:** sanitizar à entrada ou à saída com HTMLPurifier (já instalado) numa só função (`RichText::display` chama `Purifier` antes de recolorir); escapar `$tableIsv` ou garantir que `ImportSimulator` só usa valores numéricos; restringir `PageContentController` a `validate()`; depois avançar a CSP para nonces e bloqueio (ver SEG-016).
- **Validar:** testes de renderização com payloads `<script>`, `onerror`, `javascript:`; confirmar que formatação normal (negrito, listas, cores) se mantém.
- **Esforço:** médio.

### SEG-007 - Uploads públicos permitem SVG (ou nome controlado pelo cliente) no domínio principal
- **Gravidade:** média/baixa. **Confiança:** confirmado.
- **Evidência:** `app/Http/Controllers/SettingController.php:94` e `Admin/SettingV2Controller` (`mimes:...svg...` para imagens de settings, disco `public`); `Admin/NewsletterManagementController.php:211,250` (`svg` em `newsletter-offers`, disco `public`); `PageController.php:~425-442` (extensões permitidas incluem `svg`, nome derivado do nome original em `files/`, sobrescreve ficheiros com o mesmo nome); `config/lfm.php` (`files` aceita `text/plain`, `pdf`). `public/.htaccess` só nega PHP em `storage/app/public/.htaccess`.
- **Cenário:** um SVG com `<script>` carregado em `/storage/settings/x.svg` e aberto directamente executa JS na origem do site (o `<img>` não executa, a navegação directa sim). Combinado com nome previsível (`files/<slug>.ext`) permite substituir um ficheiro legítimo do CMS.
- **Impacto:** XSS na origem pública por quem consiga fazer upload (ver SEG-001/SEG-009); sobrescrita de ficheiros do site.
- **Reprodução segura:** teste local a fazer upload de um SVG de teste inofensivo e confirmar que fica em `storage/app/public`.
- **Correção:** retirar `svg` das listas (ou sanitizar com biblioteca SVG), usar `store()` com nome aleatório em vez de nome do cliente, servir `/storage/**.svg` com `Content-Security-Policy: sandbox` / `Content-Disposition: attachment` via `.htaccess`.
- **Validar:** teste de upload `.svg` devolve 422; ficheiros novos com nome aleatório. **Esforço:** baixo.

### SEG-008 - Laravel Filemanager acessível a qualquer utilizador autenticado, fora de `restrictAngariador`/`authorizeResource`
- **Gravidade:** média. **Confiança:** confirmado (rotas e config).
- **Evidência:** `routes/web.php:102-104` (`middleware => ['web','auth']` apenas); `config/lfm.php` (`allow_shared_folder` true, `disk` public, `max_size` 50000 KB, `text/plain` e `pdf` permitidos).
- **Cenário:** um angariador aprovado (utilizador externo) abre `/laravel-filemanager`, lista, envia e **apaga** ficheiros da pasta partilhada (`shares`) e envia PDF/texto até 50 MB para o disco público.
- **Impacto:** apagar imagens usadas no site/notícias, alojar conteúdo arbitrário no domínio (phishing com PDF), consumo de disco. Utilizadores: todos os angariadores.
- **Reprodução segura (local):** teste com utilizador só "angariador" a `get('/laravel-filemanager')` e esperar 403 (hoje devolve 200).
- **Correção:** mover o grupo para `['web','auth','restrictAngariador','authorizeResource']` ou criar middleware próprio (`role:admin|cms`), baixar `max_size` (ex. 5 MB), remover `text/plain`, desligar a pasta partilhada se não for usada.
- **Validar:** teste de 403 para angariador; CKEditor continua a funcionar para admin. **Esforço:** baixo.

### SEG-009 - Formulário público de importação grava `$request->all()` no modelo e aceita código de angariador livre
- **Gravidade:** média/baixa. **Confiança:** confirmado.
- **Evidência:** `app/Http/Controllers/Frontend/ImportController.php:68` (`$formPropposalData = $request->all()`) e `:179` (`FormProposal::create($formPropposalData)`); `app/Models/FormProposal.php:9-47` (`fillable` inclui `proposal_id`, `created_by`, `origin`, `title`, `angariador_code`, `status`); só `status`, `client_id`, `angariador_code`, `version` são forçados depois. Atribuição de dono por `referral_code` em `:100-101,114-143`.
- **Cenário:** qualquer visitante envia campos extra (`proposal_id`, `created_by`, `origin`) que acabam gravados; pode também associar-se a um código de angariador alheio para lhe "dar" a lead (ou, num cliente já existente sem dono, atribuir `owner_id`), o que gera comissões indevidas.
- **Impacto:** integridade dos dados do funil, possível fraude de comissões, dados falsos no backoffice. Rate limit existente (5/min, honeypot, ver SEG-012).
- **Reprodução segura (teste local):** POST válido com `created_by=1&proposal_id=1` e inspecionar o registo criado.
- **Correção:** usar apenas `$validated = $request->validate(...)` (ou `only([...campos do formulário])`) em vez de `all()`; tratar `angariador` como pista (não vinculativa) até revisão, e registar na timeline quando um cliente existente recebe dono pelo formulário público.
- **Validar:** teste que garante que `created_by`/`proposal_id` do pedido são ignorados. **Esforço:** baixo.

### SEG-010 - Dependência com aviso conhecido: `symfony/mime` 7.4.19
- **Gravidade:** média/baixa. **Confiança:** confirmado (`composer audit`).
- **Evidência:** `composer audit` reporta PKSA-wtxr-p26d-nn42 / CVE-2026-45070 (injecção de cabeçalhos de email por nomes de parâmetros MIME com carateres inválidos); versão instalada 7.4.19, corrigida em 7.4.22. Outras versões relevantes: laravel/framework 12.70.1, guzzle 7.16.1, spatie/laravel-permission 6.25.0, dompdf 3.1.6, intervention/image 3.11.7, laravel-filemanager 2.13.0 (sem avisos reportados). `npm audit --offline`: 0 vulnerabilidades (com cache local; não verifica avisos novos).
- **Cenário:** só explorável se nomes de parâmetros de anexos/cabeçalhos puderem ser controlados por um atacante (o projecto anexa ficheiros de consignação, `ConsignmentController`).
- **Impacto:** baixo no uso actual. **Correção:** `composer update symfony/mime --with-dependencies` em ambiente de teste, correr `php artisan test`, publicar. **Validar:** `composer audit` limpo. **Esforço:** baixo.

---

## Baixas

### SEG-011 - Exportações CSV/XLSX sem proteção contra injecção de fórmulas
- **Gravidade:** baixa/média. **Confiança:** confirmado.
- **Evidência:** `app/Http/Controllers/Admin/ExportController.php:158-163` (`fputcsv` directo) e `app/Services/ExportService.php` (sem escape de células iniciadas por `=`, `+`, `-`, `@`); dados de origem pública (nome, email, mensagem do formulário).
- **Cenário:** um visitante submete como nome `=HYPERLINK(...)`; o staff abre o ficheiro no Excel e a fórmula executa/faz pedidos.
- **Correção:** prefixar com `'` (ou tabulação) qualquer valor de texto que comece por `= + - @` antes de escrever. **Validar:** teste unitário do helper. **Esforço:** baixo.

### SEG-012 - Anti-spam contornável e registo de angariador sem anti-spam
- **Gravidade:** baixa. **Confiança:** confirmado.
- **Evidência:** `app/Http/Middleware/BlockSpamSubmissions.php:23-27` (o tempo mínimo só conta se `_form_started > 0`; omitir o campo ou o honeypot ignora a verificação); `routes/web.php:91-93` (`/registo-angariador` só com `throttle:5,1`, sem `antispam`, e a validação `unique:users,email` permite enumerar emails registados).
- **Correção:** tratar ausência de `_form_started` como suspeita, juntar CAPTCHA (Turnstile/hCaptcha) aos formulários públicos e ao registo, resposta genérica no registo. **Esforço:** baixo/médio.

### SEG-013 - `unsubscribe` público por GET, sem assinatura nem throttle
- **Gravidade:** baixa. **Confiança:** confirmado.
- **Evidência:** `routes/web.php:95-96`; `Admin/NewsletterController.php:18-50` (altera `newsletter_consent` por email sem prova de posse, e envia sempre um email ao admin; regista o email em log).
- **Cenário:** qualquer pessoa retira o consentimento de terceiros e gera emails ao admin em ciclo (sem rate limit).
- **Correção:** `URL::signedRoute` (middleware `signed` já existe em `app/Http/Middleware/ValidateSignature.php`) nos links dos emails, `throttle`, e não registar o email completo. **Esforço:** baixo.

### SEG-014 - Sessão, cookies e proxies: valores por omissão
- **Gravidade:** baixa. **Confiança:** suspeito (depende do `.env`).
- **Evidência:** `config/session.php:49` (`encrypt => false`), `:169` (`secure => env('SESSION_SECURE_COOKIE')` sem omissão `true`), `SESSION_DRIVER` omissão `file`, `SESSION_LIFETIME` 120; `app/Http/Middleware/TrustProxies.php` (`$proxies` nulo: atrás do proxy da Hostinger `$request->ip()` pode ser o IP do proxy, anulando o `throttle` por IP e o log de IPs); `public/.htaccess` HSTS sem `includeSubDomains`.
- **Correção:** `SESSION_SECURE_COOKIE=true`, considerar `SESSION_ENCRYPT=true`, definir `$proxies` (ou `'*'` se o hosting for sempre atrás de proxy) e verificar com `request()->ip()` em staging. **Esforço:** baixo. **Atenção:** testar login após alterar cookies seguros.

### SEG-015 - Ficheiros privados: leitura por qualquer perfil do backoffice e fallback para o disco público
- **Gravidade:** baixa. **Confiança:** confirmado.
- **Evidência:** `config/permissions.php` (`admin.v2.files.show` em `shared`); `app/Services/PrivateFiles.php:47-60` (`disco()` procura em `local` e depois em `public`); docs/ficheiros-privados.md (passo de `files:move-to-private --apply` por fazer em produção, não verificável daqui). O `PrivateFiles::resposta` em si está bem protegido (whitelist de tipos, `..` rejeitado, `realpath`, `nosniff`, `Cache-Control: private`); downloads de documentos de legalização/cotações convertidas verificam que o documento pertence ao pai.
- **Cenário:** qualquer perfil não angariador (ex. "cms") lê fotos de retomas, comprovativos e anexos financeiros; enquanto o comando não for aplicado, os ficheiros antigos continuam em `/storage/...` com URL directo (nomes aleatórios de `store()`, mas sem autenticação).
- **Correção:** exigir permissão por tipo (`movements.view`, `commissions.view`, etc.) em vez de `shared`; correr `php artisan files:move-to-private` (dry-run) e depois `--apply` em produção. **Validar:** `/storage/retoma/...` devolve 404. **Esforço:** baixo.

### SEG-016 - CSP em modo report-only com `'unsafe-inline'`; endpoint `/csp-report` público
- **Gravidade:** baixa. **Confiança:** confirmado (documentado em `docs/seguranca-csp.md`).
- **Evidência:** `app/Http/Middleware/ContentSecurityPolicyReportOnly.php`, `config/security.php:31-33,64` (`img-src https:`), `VerifyCsrfToken::$except = ['csp-report']`, `CspReportController` (limita 8 KB, 5 relatórios por pedido, 300 caracteres por campo, `throttle:30,1`; log sem query string: bem feito).
- **Cenário:** a CSP não bloqueia XSS (ver SEG-006). O endpoint de relatórios pode ser inundado dentro do rate limit (30/min por IP) enchendo `csp.log` (rotativo 14 dias): impacto reduzido.
- **Correção:** plano já descrito no doc (nonces, remover handlers `on*=`, passar a bloqueio). **Esforço:** alto (muitos scripts inline).

### SEG-017 - Registo de dados pessoais em logs
- **Gravidade:** baixa. **Confiança:** confirmado.
- **Evidência:** `Admin/NewsletterController.php:34,48,90` (emails em `Log::info`), `Admin/NewsletterManagementController.php:178,181`, `AppServiceProvider.php:61,65` (nome+email no AuditLog de login/logout; aceitável como auditoria mas acessível a quem veja o log de auditoria). `storage/logs` está no `.gitignore`.
- **Correção:** registar `client_id` em vez de email; definir retenção dos logs (`LOG_DAILY_DAYS`). **Esforço:** baixo.

### SEG-018 - Código legado/inactivo com superfície de ataque
- **Gravidade:** baixa. **Confiança:** confirmado.
- **Evidência:** `app/Http/Controllers/AdSearchController.php:421` (`dd()` com `getErrorOutput()` do processo Python, rota `ad-searches.*` ainda registada, só admin mas sem enforce, SEG-001); `PageContentController` (`$request->all()`), `SettingController`/`ProposalController` V1 com validação de SVG, `*.blade.php.bak` e vistas "copy" em `resources/views` (ficheiros com `{!! !!}` não usados); `Admin/RadarController::store` grava YAML em `scarperAutoscout/searches/` (nome validado por regex `^[a-z0-9]+(-[a-z0-9]+)*$`, sem traversal: bem), `AutoscoutScraperRunner` usa `escapeshellarg` (bem). Rota `POST v2/financial/seed` protegida por `role:admin` (bem). `CORS` `allowed_origins: ['*']` em `api/*` (endpoints só devolvem dados públicos de marcas/anos/extras).
- **Correção:** apagar controllers/vistas V1 e `.bak` não usados (seguir a regra do CLAUDE.md de não manter V1), tirar `dd()`. **Esforço:** baixo.

---

## Pontos verificados sem problema (resumo de boas práticas)
- SQL injection: `DB::raw/whereRaw/orderByRaw/selectRaw` usam texto fixo ou parâmetros; ordenações dinâmicas (`RadarController`) usam lista branca; pesquisas `LIKE` escapam e parametrizam. Sem achados.
- Isolamento do angariador no portal `admin.angariador.*`: `AngariadorController` verifica `owner_id` em lead, cotação convertida e comprovativo (com verificação do prefixo da pasta); `restrictAngariador` bloqueia fora da área.
- Tokens públicos (`legalizacao/estado/{token}`, simulador, `tracking_code`, `proposal_code`) são aleatórios e longos; aceitação pública com `throttle`.
- Mass assignment: nenhum `$guarded = []`; `User::$fillable` não inclui `access_mobile` nem roles; atribuição de perfil `admin` protegida no controller.
- Passwords: política 10+ caracteres, maiúsculas/minúsculas/números, HIBP; passwords de contas criadas são aleatórias com link de definição por email; contas `pendente` não entram.
- Cookies de sessão `HttpOnly`, `SameSite=lax`; CSRF activo em todo o grupo `web` (única exceção `csp-report`, justificada).
- Uploads privados: tipos e tamanhos validados (`mimes` + `max`) em retomas, consignação, comprovativos, despesas, documentos; `.htaccess` impede execução de PHP em `storage/app/public`.

## Checklist do que foi revisto / não revisto

Revisto (estático):
- [x] CLAUDE.md, docs/seguranca-csp.md, docs/ficheiros-privados.md
- [x] `Http/Kernel.php`, middlewares (`AuthorizeResource`, `RestrictAngariadorArea`, `VerifyCsrfToken`, `VerifyWhatsAppSignature`, `BlockInProduction`, `BlockSpamSubmissions`, `EnsureMobileAccess`, `TrustProxies`)
- [x] `config/permissions.php`, `PermissionRegistry`, `PermissionService` (ver SEG-001)
- [x] `routes/web.php` (estrutura, rotas fora do grupo `gestao`), `routes/frontend.php`, `routes/api.php`
- [x] Login, definir password, registo de angariador, impersonation, `UserV2Controller`, `RoleV2Controller`
- [x] `AngariadorController` (IDOR), downloads de documentos (V3, legalização, cotações convertidas), `PrivateFiles`/`PrivateFileController`
- [x] Grep de SQL raw, `{!! !!}`, `request->all()`, uploads, `exec`, `Http::`/Guzzle, `dd`, `Log::`
- [x] Webhook WhatsApp, `CspReportController`, push subscriptions, newsletter
- [x] Segredos versionados: `.env.example` (sem valores), padrões de segredos em código (sem achados em PHP/Python/JS/YAML), ficheiros da raiz (`users/`, `please`, `server.php`, `info-*`, `scripts/`, CSV)
- [x] `composer audit` (1 aviso: SEG-010), `npm audit --offline` (0)

Não revisto ou só parcialmente:
- [ ] `.env` real e configuração de produção (não lido por regra): valores de `APP_DEBUG`, `PERMISSIONS_ENFORCE`, `SESSION_*`, `WHATSAPP_*`; disable_functions do alojamento.
- [ ] Histórico git completo (só `git log -- .env` e `users`); segredos em commits antigos não pesquisados exaustivamente.
- [ ] Todos os controllers desktop um a um (leads/clientes/cotações/movimentos) quanto a IDOR com âmbito "próprios": verificado por amostragem (`LeadV2Controller`, `ClientV2Controller`), a conclusão generaliza-se por padrão, sem confirmar cada método.
- [ ] Todas as 117 ocorrências de `{!! !!}` (vistas "copy" e emails/PDF só em dados internos não analisadas ao pormenor) e JS inline com dados do servidor.
- [ ] `ImportSimulator::calcular` (origem de `isv_table`), `ConsignmentController`, `ContactController`, geração de PDFs (dompdf com `isRemoteEnabled`?), scrapers Python em `scarperAutoscout/` e `scripts/` (SSRF/dependências Python), `requirements.txt`.
- [ ] Testes dinâmicos (não executei a aplicação nem a suite de testes), `package-lock.json` versões antigas (só `npm audit --offline`).
- [ ] Configuração do servidor (Apache/Hostinger): cópia separada de `public/` (ver memória do projeto), cabeçalhos efectivos, permissões de ficheiros.


---

# Auditoria IzzyCar: problemas funcionais e de lógica

Data: 2026-10-31. Âmbito: só leitura. Nenhum código, `.env` ou dado foi alterado; não houve ligação à BD remota nem envio de emails ou pedidos externos.

## Método e limites

- Revisão estática: leitura de `SaleCalculator`, `ServiceTranches`, `MovementService`, `ProposalAcceptanceService`, `ProposalService` (criação), `ConvertedProposalService`, `ClientMergeService`, observers, `App\Console\Kernel`, comandos agendados, `GeocodingService`, `RadarTrimPriceAiService`, `SyncGoogleReviews` e `V3VehicleController` (despesas e vendas).
- Verificação em execução: `php artisan test` (ver secção própria) e `php artisan route:list --json`, que só lê as rotas e não toca na BD.
- Não foram lidos por inteiro: `LeadService`, `ClientService`, controllers públicos de formulários, `SaleV2Controller`, relatórios mensal, trimestral e anual. As conclusões sobre esses módulos ficam por fazer.
- Os níveis de confiança são "confirmado" (o código mostra o erro sem ambiguidade) e "suspeito" (depende de configuração de produção ou da intenção do negócio).

## Resumo por gravidade

| ID | Gravidade | Confiança | Tema |
|---|---|---|---|
| LOG-001 | Alta | Confirmado | Lembrete de follow-up chega 60 min antes, sem deduplicação |
| LOG-002 | Alta | Suspeito | Fuso horário `UTC` por omissão; agendamentos e "hoje" desfasados |
| LOG-003 | Média | Confirmado | IVA das despesas V3 calculado sobre o bruto |
| LOG-004 | Média | Confirmado | Venda de viatura sem transação, duplo clique e `vat_type` livre |
| LOG-005 | Média | Confirmado | Referência de viatura por `count()`: colide após eliminar |
| LOG-006 | Média | Confirmado | Emails síncronos no observer: falha deixa a cotação a meio |
| LOG-007 | Média | Suspeito | Cotação convertida cancelada não liberta a cotação; estados sem regras |
| LOG-008 | Média | Confirmado | Totais e saldos de movimentos enganadores |
| LOG-009 | Média | Confirmado | Agendador sem `withoutOverlapping`, `onOneServer` nem falhas tratadas |
| LOG-010 | Média | Suspeito | `ProposalService::create` sem transação |
| LOG-011 | Baixa | Confirmado | `radar:classify-trims` salta lotes (`chunk` + filtro alterado) |
| LOG-012 | Baixa | Suspeito | Merge de clientes: contagens fora da transação, sem bloqueio |
| LOG-013 | Baixa | Confirmado | Dinheiro em `float` e `vat_type` desconhecido tratado como isento |
| LOG-014 | Baixa | Confirmado | Geocoding e Google Reviews engolem erros |
| LOG-015 | Baixa | Confirmado | `ServiceTranches`: `pct2` inconsistente com `tranche2` |
| LOG-016 | Baixa | Confirmado | Código V1 morto com `route()` para nomes inexistentes |
| LOG-017 | Baixa | Confirmado | Anexos de movimentos alterados fora de transação |
| LOG-018 | Baixa | Confirmado | Email de BCC fixo no código |

## Problemas

### LOG-001 — Lembrete de follow-up enviado 60 minutos antes e sem deduplicação
- Gravidade: Alta. Confiança: confirmado.
- Evidência: `app/Console/Commands/SendFollowupReminder.php:17-24`. O código faz `$agora->addMinutes(60)` com o comentário "Ignorar segundos", e depois `whereBetween('next_followup_at', [$agora, $limite])`. A agenda é `everyMinute()` em `app/Console/Kernel.php:39`.
- Cenário:
  - A janela consultada é `[agora+60min, agora+61min]`, e não o minuto exato descrito na assinatura do comando e no Kernel.
  - O `whereBetween` é inclusivo nos dois extremos, com `$limite = $agora + 1 min`. Um follow-up agendado para HH:MM:00 cai em duas execuções consecutivas, por isso o admin e o angariador recebem dois emails.
  - Não há flag de "enviado" nem `withoutOverlapping`. Se o cron falhar um minuto, o lembrete perde-se.
  - O primeiro `Mail::send` (admin) não está protegido: uma falha impede o email ao angariador.
  - O texto do email usa `$agora` (já com +60 min), pelo que o cabeçalho mostra a hora do follow-up. O envio é que chega uma hora antes.
- Impacto: lembretes antecipados ou duplicados. Lembretes perdidos quando o cron falha ou o servidor reinicia.
- Reprodução segura: teste unitário em sqlite com `Mail::fake()` e `Carbon::setTestNow()`. Criar um `Client` com `next_followup_at = T`, correr o comando em `T-61min:00` e em `T-60min:00`, e contar os `FollowupReminderMail` (esperado: 2).
- Correção: decidir se o objetivo é "no minuto" ou "1 h antes" e documentá-lo. Janela semiaberta `>= agora->startOfMinute()` e `< agora->startOfMinute()->addMinute()`. Acrescentar a coluna `followup_reminded_at` (ou comparar com `next_followup_at`) para ser idempotente. Envolver cada envio em `try/catch` com `report()`. Acrescentar `->withoutOverlapping()->onOneServer()` no Kernel.
- Validação: testes para a janela e para a deduplicação. Correr o comando duas vezes seguidas e esperar um só email.
- Esforço: 0,5 a 1 dia. Dependências: migration para a coluna nova (dizer que falta `php artisan migrate` em produção) e decisão do Pedro sobre a semântica do lembrete.

### LOG-002 — Fuso horário por omissão `UTC`
- Gravidade: Alta. Confiança: suspeito (depende do `.env` de produção).
- Evidência: `config/app.php:70` (`env('APP_TIMEZONE', 'UTC')`). Não existe `APP_TIMEZONE` em `.env` nem em `.env.example`. Não há `date_default_timezone_set` nem `Europe/Lisbon` em `app/`, `config/` ou `bootstrap/`.
- Cenário: `now()`, `today()`, `dailyAt('00:00')`, `dailyAt('09:00')`, `reports:monthly` à `01:00` e `whereDate('next_followup_at', today())` (`LeadV2Controller.php:64-69`) correm em UTC. Em Portugal há 0 h ou 1 h de diferença consoante o horário de verão. Datas guardadas e mostradas ficam deslocadas. "Hoje" no dashboard muda à 01:00 local no verão. Os 48 h de `isCommissionOverdue` e a validade da cotação variam uma hora. O hack de +60 min do LOG-001 sugere que alguém já notou o desfasamento.
- Impacto: follow-ups, lembretes e relatórios fora da hora esperada. Dados de tarefas "de hoje" trocados à volta da meia-noite.
- Reprodução segura: `php artisan tinker` está proibido. Em teste, comparar `config('app.timezone')` e `now()->timezoneName`. No servidor, `php artisan about | grep -i timezone` (só leitura).
- Correção: definir `APP_TIMEZONE=Europe/Lisbon` no `.env` de produção e em `.env.example`. Rever os dados já gravados em UTC (colunas `datetime` como `next_followup_at`) antes da mudança. Alternativa: `->timezone('Europe/Lisbon')` nas tarefas agendadas.
- Validação: teste com `Carbon::setTestNow` à meia-noite de verão. Conferir manualmente o dashboard.
- Esforço: 0,5 dia, mais revisão de dados. Dependências: decisão sobre conversão dos registos existentes. Interage com LOG-001.

### LOG-003 — IVA das despesas V3 calculado sobre o valor bruto
- Gravidade: Média. Confiança: confirmado.
- Evidência: `app/Http/Controllers/Admin/V3VehicleController.php:244-246`: `$vatAmount = round($amountGross * $vatRate / 100, 2)`. Em contraste, `MovementService::computeVatFields` (`MovementService.php:123-125`) e `SaleCalculator` (linha 45-46) usam `gross - gross/(1+rate)`.
- Cenário: despesa de 123,00 € a 23 % de IVA. O correto é IVA 23,00 e líquido 100,00. O controller grava IVA 28,29 e líquido 94,71. Para 100,00 € a 23 % dá IVA 23,00 e líquido 77,00, em vez de 18,70 e 81,30.
- Impacto: `vat_amount` e `amount_net` ficam errados em todas as despesas criadas pelo separador da viatura. Afeta os relatórios que somam esses campos. A margem da venda usa `amount_gross` e `vat_rate`, por isso não é afetada.
- Reprodução segura: teste Feature em sqlite. POST em `admin.v3.vehicles.expenses.store` (confirmar o nome com `route:list`) com `amount=123`, `vat_rate=23`, e ler `vat_amount` e `amount_net`.
- Correção: chamar `MovementService::computeVatFields($data)` no `storeExpense`, que já é a fonte única. Corrigir os registos existentes com um script revisto à mão, sem `migrate` automático.
- Validação: teste que compara o resultado do controller com o `MovementService` para taxas 0, 6, 13 e 23.
- Esforço: 0,5 dia, mais reconciliação dos dados. Dependências: autorização do Pedro para corrigir dados em produção.

### LOG-004 — Registo de venda sem transação, duplo clique e `vat_type` livre
- Gravidade: Média. Confiança: confirmado.
- Evidência: `V3VehicleController.php:489-545`.
  - A verificação `$vehicle->sales()->exists()` e o `Sale::create` não estão numa transação.
  - A migration `2026_05_19_200002_add_v3_vehicle_id_to_expenses_and_sales.php` não cria índice único em `sales.v3_vehicle_id`.
  - `vat_type` é validado só com `required|string` (linha 502), não com `in:geral_23,geral_13,geral_6,margem,isento`.
  - `Sale::create` e `vehicle->update(['status' => 'vendido'])` são duas escritas separadas.
  - `destroySale` repõe sempre `em_stock` (linha 541), mesmo que a viatura estivesse noutro estado antes.
  - O `vat_type` (por exemplo `margem`) não é guardado, só `vat_rate`.
- Cenário:
  - Dois cliques rápidos podem criar duas vendas. O `SaleObserver::saved` cria então dois movimentos de receita.
  - Um valor desconhecido de `vat_type` cai em `isento` sem aviso.
  - Se o `update` do estado falhar, fica uma venda com a viatura ainda em stock.
- Impacto: receita duplicada nos movimentos e nos relatórios. Margem calculada com o regime de IVA errado.
- Reprodução segura: teste que envia dois POST seguidos (o segundo deve ser recusado), e um POST com `vat_type=xyz` (deve dar 422). O teste concorrente real só é possível fora de sqlite.
- Correção: `DB::transaction` com `V3Vehicle::lockForUpdate()`. Índice único em `sales.v3_vehicle_id` (migration com nome curto, sem tocar na histórica). Regra `Rule::in(...)` ou um `FormRequest` com enum de IVA. Guardar o `vat_type` ou guardar o estado anterior da viatura.
- Validação: testes de duplicado e de valor inválido.
- Esforço: 1 dia. Dependências: verificar duplicados existentes em `sales` antes de criar o índice único.

### LOG-005 — Referência de viatura gerada por `count() + 1`
- Gravidade: Média. Confiança: confirmado.
- Evidência: `app/Models/V3Vehicle.php:67-72`. `V3Vehicle` não usa `SoftDeletes` e `destroy` faz `delete()` definitivo (`V3VehicleController.php:117-127`). A coluna `reference` é única (`create_v3_vehicles_table.php:13`).
- Cenário:
  - Existem as viaturas V3-2026-001, -002 e -003. Elimina-se a -002. A contagem passa a 2, por isso a próxima referência é -003, que já existe. O `INSERT` falha com erro 500.
  - Dois pedidos simultâneos (por exemplo `store` e `createVehicleAndLegalization` ao aceitar uma cotação) leem a mesma contagem e colidem.
- Impacto: erro 500 ao criar viatura. Na aceitação de cotação, o `createVehicleAndLegalization` falha dentro da transação.
- Reprodução segura: teste em sqlite que cria 3 viaturas, apaga a segunda e chama `generateReference()`.
- Correção: usar o maior sufixo existente do ano (`MAX`) com bloqueio, ou uma tabela de sequência, com repetição em `UniqueConstraintViolationException`.
- Validação: teste da eliminação intermédia e teste do retry.
- Esforço: 0,5 dia. Dependências: nenhuma.

### LOG-006 — Emails síncronos no observer: falha deixa a cotação a meio
- Gravidade: Média. Confiança: confirmado.
- Evidência: `app/Observers/ProposalObserver.php:20-23, 49-52, 84-99`. Em `updated`, `notifyProposalSent` envia `Mail::to(...)->send(...)` sem `try/catch`, ao cliente e depois ao angariador. O envio é síncrono, sem `ShouldQueue`. O mesmo vale para `created` com estado "Enviado".
- Cenário:
  - A cotação já foi gravada como "Enviado" quando o email falha, por exemplo por SMTP em baixo ou email inválido.
  - A exceção sobe ao controller e o utilizador vê erro 500, com o estado já alterado.
  - O `LeadActivity::log` e o agendamento do follow-up (linhas 101-129) nunca correm.
  - Se o email do cliente falha, o do angariador não é enviado. Ao tentar de novo, o estado já é "Enviado" e nenhum observer dispara.
  - Em `ConvertedProposalService::changeStatus` o mesmo problema está resolvido com `try/catch` e `report($e)`. Falta aplicar o mesmo padrão aqui.
- Impacto: cotação "Enviada" sem registo, sem follow-up e possivelmente sem o cliente a saber. Resposta HTTP de erro apesar de a ação ter sido aplicada.
- Reprodução segura: teste com `Mail::shouldReceive('to')->andThrow(...)`, que dispara a mudança de estado e verifica o resultado da resposta e o `LeadActivity`.
- Correção: `try/catch` com `report()` por destinatário e registo do resultado no `LeadActivity`. Idealmente `Mail::queue` com `ShouldQueue` (a fila é `sync` nos testes, ver `QUEUE_CONNECTION` em produção). Executar o log e o follow-up antes do envio.
- Validação: teste com falha de email que confirma que o resto do fluxo corre.
- Esforço: 0,5 dia. Dependências: confirmar `QUEUE_CONNECTION` e se existe worker em produção.

### LOG-007 — Cotação convertida cancelada não liberta a cotação; estados sem regras de transição
- Gravidade: Média. Confiança: suspeito (depende da intenção do negócio).
- Evidência:
  - `ProposalAcceptanceService.php:73-80`: uma cotação aceite não muda de estado, e diz "para a anular, cancele a cotação convertida".
  - `Proposal::isAccepted()` (`Proposal.php:81-84`) devolve `true` enquanto existir qualquer `ConvertedProposal` com o seu `proposal_id`, incluindo as canceladas.
  - A mesma linha de acesso tem índice único em `proposal_id` (referido em `ProposalAcceptanceService.php:70`).
  - `ConvertedProposal.php:13` aceita as duas grafias "Cancelada" e "Cancelado". `ConvertedProposalService::changeStatus` aceita qualquer estado para qualquer outro, sem ordem nem validação de reativação.
- Cenário:
  - Cancela-se a convertida. A cotação continua "Aprovada" e `isAccepted()` continua `true`. Não se pode voltar a aceitar nem mudar de estado.
  - Pode reativar-se uma cancelada para "Concluido" sem restrições. As tranches pagas e os movimentos gerados por `Expense::syncFromConvertedProposal` ficam por tratar.
- Impacto: o cliente que cancelou não pode recomeçar a partir da mesma cotação (tem de ser criada outra). Dados divergentes entre pipeline e finanças.
- Reprodução segura: teste que aceita, cancela a convertida e tenta mudar o estado da cotação.
- Correção: definir a política com o Pedro. Opções: `isAccepted()` ignorar canceladas e permitir reativar ou reaceitar, ou libertar o `proposal_id` ao cancelar. Normalizar a grafia para uma só (enum). Validar o pipeline.
- Validação: testes de ciclo aceitar, cancelar e reaceitar. A suite `ConvertedProposalStatusCharacterizationTest` já caracteriza parte disto.
- Esforço: 1 a 2 dias. Dependências: decisão de negócio e migração de estados existentes.

### LOG-008 — Totais e saldos dos movimentos enganadores
- Gravidade: Média. Confiança: confirmado (o código), com intenção a validar.
- Evidência: `app/Services/MovementService.php:21, 148-180`.
  - O saldo parte sempre de `INITIAL_BALANCE = 15757.28` (constante float no código) em cada ano escolhido (linha 160 e seguintes). Os movimentos de anos anteriores não entram. O doc-comment admite "acumulam o ano".
  - `$pending` (linha 150) soma `amount_gross` das receitas e despesas pendentes juntas, sem sinal.
  - Os totais de despesa e de receita incluem pendentes, enquanto `saldoReal` as exclui.
  - O saldo está em `float` com somas acumuladas.
- Cenário: a listagem de 2026 mostra o saldo de abertura como se 2025 não tivesse movimentos. "Pendente" de 1000 € de receita mais 1000 € de despesa mostra 2000 €.
- Impacto: números de tesouraria incorretos para decisões. O saldo de abertura só é válido para o primeiro ano.
- Reprodução segura: teste em sqlite com movimentos em 2025 e 2026 e `summary('2026', false)`.
- Correção: saldo de abertura do ano = `INITIAL_BALANCE` mais soma de tudo antes do ano. Separar o pendente de receitas e de despesas, ou assinar o valor. Mover a constante para a tabela `settings`. Fazer as somas em centavos inteiros ou `bcmath`.
- Validação: `MovementServiceTest` com dois anos.
- Esforço: 0,5 a 1 dia. Dependências: confirmar com o Pedro a semântica pretendida.

### LOG-009 — Agendador sem proteção de sobreposição, servidor único nem tratamento de falhas
- Gravidade: Média. Confiança: confirmado.
- Evidência: `app/Console/Kernel.php:22-67`. Não há `withoutOverlapping` nem `onOneServer` em `app/` ou `routes/` (grep sem resultados). Só `radar:refresh-active` usa `Cache::lock` à mão (`RefreshActiveRadarSearches.php:23`).
- Cenário:
  - `leads:followup-reminder` corre cada minuto (ver LOG-001). `tasks:send-reminders`, `legalizations:send-tracking-emails` e os relatórios não têm `try/catch` global (os relatórios gravam o PDF e depois enviam o email: se o email falhar, não há repetição).
  - `commissions:overdue-alert` envia todos os dias a mesma lista de comissões em atraso, sem registar o que já foi avisado.
  - `reports:quarterly` usa `dailyAt('01:10')` com `->when(...)` e `today()` em UTC (LOG-002).
  - `SendMonthlyReport` usa `now()->subDay()` e `mkdir` sem verificar o resultado.
  - Sem `->runInBackground()` nem `emailOutputOnFailure`: uma falha passa despercebida.
  - `radar:refresh-active` trava o lock 3300 s, e `Cache::lock` com driver `file` ou `array` não é seguro com vários servidores.
- Impacto: emails duplicados se o cron for acionado em dois processos. Execuções silenciosamente falhadas. Spam diário de alertas.
- Reprodução segura: `php artisan schedule:list` (só leitura) para listar. Testar os comandos em sqlite com `Mail::fake()`.
- Correção: `->withoutOverlapping(10)` nas tarefas frequentes, `->onOneServer()` se houver mais de um nó (exige cache partilhada), `->onFailure()` ou `emailOutputOnFailure`. Registar o último alerta de comissão por convertida.
- Validação: `schedule:list` e teste de comando com falha de email simulada.
- Esforço: 0,5 a 1 dia. Dependências: saber quantos servidores e que driver de cache existe em produção.

### LOG-010 — Criação de cotação em várias escritas sem transação
- Gravidade: Média. Confiança: suspeito (li só o início de `create`).
- Evidência: `app/Services/ProposalService.php:215-260`. O método cria a `Proposal`, grava atributos, guarda a imagem (ficheiro em disco), atualiza `FormProposal` e aplica o estado final, sem `DB::transaction`. Só `ProposalStatusNotAppliedException` está prevista para a falha do último passo.
- Cenário: falha ao guardar a imagem ou ao atualizar o pedido depois de `Proposal::create`. A cotação fica criada pela metade, sem link ao pedido e sem estado final.
- Impacto: cotações órfãs ou incompletas, duplicadas ao repetir o envio.
- Reprodução segura: teste que força uma exceção em `storeImage` (mock) e conta as `Proposal`.
- Correção: transação à volta das escritas de BD e ficheiros guardados só após o commit (`DB::afterCommit`). Manter a exceção do estado fora da transação.
- Validação: teste com falha injetada.
- Esforço: 0,5 dia. Dependências: rever o resto de `create`/`update` (não lido).

### LOG-011 — `radar:classify-trims` salta lotes
- Gravidade: Baixa. Confiança: confirmado (padrão conhecido do Laravel).
- Evidência: `app/Console/Commands/ClassifyRadarListingTrims.php:44-69`. Usa `chunk(25)` sobre `whereNull('trim_classified_at')` e atualiza `trim_classified_at` dentro do callback.
- Cenário: `chunk` pagina por `offset`. Depois do 1.º lote atualizado, a página 2 salta 25 registos ainda por classificar. Em cada execução só cerca de metade é tratada. O resto fica para a seguinte (3 execuções por dia).
- Impacto: classificação mais lenta do que o previsto, e o `--limit=300` pode não ser atingido. Sem perda de dados.
- Reprodução segura: teste em sqlite com 60 anúncios e `RadarTrimPriceAiService` substituído por mock. Esperado: 60 classificados, observado: 30.
- Correção: trocar `chunk` por `chunkById`.
- Validação: o mesmo teste.
- Esforço: 15 minutos. Dependências: nenhuma.

### LOG-012 — Merge de clientes: contagens fora da transação e sem bloqueio
- Gravidade: Baixa. Confiança: suspeito.
- Evidência: `app/Services/ClientMergeService.php:52-70, 76-117`. As contagens (`$moved`) e os campos a preencher são calculados antes de `DB::transaction`. Os registos não são bloqueados (`lockForUpdate`). A deteção das tabelas usa só a coluna literal `client_id`.
- Cenário:
  - Uma cotação criada entre o cálculo e a transação é movida, mas não aparece no relatório de auditoria (`moved`).
  - Se uma tabela tiver `client_id` com índice único (por exemplo uma relação 1-para-1), o `UPDATE` falha por conflito e a transação reverte tudo. A mensagem de erro é técnica.
  - Tabelas que referenciam o cliente por outro nome (por exemplo `lead_id`) ou por colunas polimórficas, exceto `AuditLog`, não são movidas e ficam órfãs ou apagadas por `ON DELETE`.
- Impacto: baixo, porque o comando é manual e revisto. Perda silenciosa se existir FK com outro nome.
- Reprodução segura: `ClientMergeService` com `apply=false` em sqlite e inspecionar `referencingTables()`.
- Correção: calcular as contagens dentro da transação, com `lockForUpdate`. Listar as FKs reais para `clients` (`information_schema`, só leitura) e comparar com `referencingTables()`.
- Validação: acrescentar um teste que cria referências em todas as tabelas encontradas.
- Esforço: 0,5 dia. Dependências: lista de FKs em produção.

### LOG-013 — Dinheiro em `float` e IVA desconhecido tratado como isento
- Gravidade: Baixa. Confiança: confirmado.
- Evidência:
  - `app/Services/SaleCalculator.php:21-27, 31-37, 49-58`. Todos os cálculos usam `float` e `round` só no fim. `vatRateMap[$vatType] ?? 0.0` trata qualquer valor desconhecido como isento. `normaliseVatType` devolve `isento` por omissão (linha 99).
  - `purchase_price` e `purchase_vat_paid` são lidos como `float` do modelo (`V3VehicleController.php:761-763`).
  - No regime de margem, o IVA é `max(0, (venda - compra) * 23/123)`. A taxa 23 % está fixa, mesmo que a viatura seja de outra taxa.
- Cenário: diferenças de 0,01 € entre a soma das parcelas e o total (por exemplo despesas somadas por linha e arredondadas depois). Uma taxa de IVA nova ou um erro de digitação passa a "isento" sem erro.
- Impacto: pequenas diferenças de cêntimos nas margens e no IVA de relatórios. Margem sobrestimada se `vat_type` vier errado.
- Reprodução segura: teste unitário de `SaleCalculator::compute` com várias despesas a 23 % e 6 %.
- Correção: usar centavos inteiros ou `bcmath`. Lançar exceção para `vat_type` desconhecido. Parametrizar a taxa do regime de margem.
- Validação: criar `SaleCalculatorTest` (hoje inexistente) com casos de 23/13/6/margem/isento e vendas abaixo do custo.
- Esforço: 1 dia. Dependências: confirmar com o contabilista as regras do regime de margem.

### LOG-014 — Geocoding e Google Reviews engolem erros
- Gravidade: Baixa. Confiança: confirmado.
- Evidência:
  - `app/Services/GeocodingService.php:51-53`: `catch (\Exception $e) { return null; }` sem `Log`. Sem controlo de 1 pedido/s apesar do comentário de cabeçalho.
  - `app/Console/Commands/SyncGoogleReviews.php:226-251`: a verificação de duplicados (`exists()` e depois `create`) não é atómica, e a tabela pode não ter índice único em `google_review_id` (não verificado). A tarefa está comentada no Kernel (linha 36).
  - `RadarTrimPriceAiService.php:74-80`: a resposta 429 da OpenAI não tem repetição e devolve só texto de erro.
- Cenário: falha de rede ou limite do Nominatim parece "morada não encontrada". Execuções simultâneas do comando de reviews podem duplicar.
- Impacto: diagnóstico difícil, resultados vazios sem explicação.
- Reprodução segura: `Http::fake` ou mock do Guzzle com exceção, e verificação do retorno e do log.
- Correção: `Log::warning` com o motivo (sem dados pessoais), distinção entre "sem resultados" e "erro". `updateOrCreate` por `google_review_id` e índice único. Repetição com espera para 429.
- Validação: testes com respostas simuladas (erro, vazio, 429).
- Esforço: 0,5 dia. Dependências: nenhuma.

### LOG-015 — `ServiceTranches`: `pct2` inconsistente com `tranche2`
- Gravidade: Baixa. Confiança: confirmado.
- Evidência: `app/Services/ServiceTranches.php:24-37`. `tranche2 = total - tranche1`, mas `pct2` vem de `final_payment` (por omissão `100 - pct1`).
- Cenário: nas Definições, `init_payment=60` e `final_payment=50`. As tranches são 60 % e 40 %, mas `pct2` devolve 50. Quem mostrar as percentagens (página pública da cotação) mostra 60/50.
- Impacto: percentagens apresentadas que não somam 100 e não correspondem aos valores.
- Reprodução segura: teste unitário de `ServiceTranches::split(100.0, ['init_payment' => '60', 'final_payment' => '50'])`.
- Correção: ignorar `final_payment` e usar `100 - pct1`, ou validar na página de Definições que a soma é 100.
- Validação: teste unitário (não existe hoje).
- Esforço: 15 minutos. Dependências: decisão sobre qual das duas definições manda.

### LOG-016 — Código V1 morto com `route()` para nomes inexistentes
- Gravidade: Baixa. Confiança: confirmado.
- Evidência: o `route:list` tem 569 nomes. Do conjunto de `route('...')` usados em `app/` e `resources/` (574 nomes únicos), 111 não existem. Os não resolvidos vêm sobretudo de código V1 sem rota:
  - `app/Http/Controllers/UserController.php`, `RoleController.php`, `SettingController.php`, `ClientController.php`, `SupplierController.php`, `FormProposalController.php`, `ProposalController.php` e as vistas `resources/views/{users,roles,settings,clients,suppliers,proposals,form_proposal,converted_proposals}`.
  - `app/Http/Controllers/Admin/NewsV3Controller.php:83,112` (`admin.v3.news.edit`), `NewsletterOfferController.php:42` (`admin.v2.newsletter.index`) e as vistas `admin/v2/vehicles`, `admin/v2/newsletter` e `admin/v3/news`. As rotas ativas são `admin.news.*` e `admin.v2.newsletter-management.*`.
  - Componentes partilhados `components/admin/item-card.blade.php:22-23` (`proposals.edit`, `proposals.destroy`), `filter-bar.blade.php:22` (`proposals.index`) e `empty-state.blade.php:11` (`route('create')`, em comentário de uso).
  - `layouts/app.blade.php:51` e `welcome.blade.php:43` (`route('register')`, rota desativada em `routes/web.php:73`).
- Cenário: usar `item-card` ou `filter-bar` sem passar os valores explícitos rebenta com `RouteNotFoundException`. Os restantes só rebentam se alguém reativar as rotas.
- Impacto: baixo hoje. Risco de erro 500 e de confusão com V1 que o CLAUDE.md proíbe estender.
- Reprodução segura: `php artisan route:list --json` e comparar com `grep -rho "route('"` (feito para esta auditoria).
- Correção: confirmar o uso real dos dois componentes e dos controllers, e remover o código morto, ou apontar para as rotas V2/V3. Acrescentar um teste que percorre os `route('nome')` literais e verifica a sua existência.
- Validação: o teste automático novo.
- Esforço: 0,5 a 1 dia. Dependências: confirmar que nada depende deste código.

### LOG-017 — Anexos de movimentos alterados fora de transação
- Gravidade: Baixa. Confiança: confirmado.
- Evidência: `app/Services/MovementService.php:77-98`. O anexo antigo é apagado antes de guardar o novo e antes do `update` da BD.
- Cenário: se `PrivateFiles::guardar` ou o `update` falhar, o comprovativo antigo já foi apagado e a BD continua a apontar para ele. Se o `update` falhar depois de guardar o novo, fica um ficheiro órfão.
- Impacto: perda de comprovativos contabilísticos.
- Reprodução segura: teste com `PrivateFiles` simulado a falhar.
- Correção: guardar o novo, atualizar a BD e só então apagar o antigo (após commit).
- Validação: teste com falha injetada.
- Esforço: 0,5 dia. Dependências: nenhuma.

### LOG-018 — Email de BCC fixo no código
- Gravidade: Baixa. Confiança: confirmado.
- Evidência: `app/Services/ConvertedProposalService.php:73` com `->bcc('izzycarpt@gmail.com')`. Também `config('mail.admin_address', env(...))` chamado com `env()` fora de `config/` em `SendFollowupReminder.php:30` e `SendOverdueCommissionAlert.php:32`, o que devolve `null` com `config:cache` se a chave de config não existir.
- Impacto: troca de destinatário exige alterar código. Com `config:cache`, o fallback `env()` deixa de funcionar.
- Reprodução segura: `grep -rn "env(" app` e verificação de `mail.admin_address` em `config/mail.php`.
- Correção: mover para `config('mail.admin_address')`, definido em `config/mail.php`.
- Validação: teste de envio com `Mail::fake()` e `assertSent` com o BCC configurado.
- Esforço: 15 minutos. Dependências: nenhuma.

## Pontos verificados sem problema

- `ProposalAcceptanceService::accept` está bem desenhado: transação, `lockForUpdate` na cotação e no cliente, tratamento de `UniqueConstraintViolationException` e índice único em `proposal_id`. Duplo clique e dois separadores são seguros.
- `proposal_code` (`Str::random(8)` com 2 iniciais, 10 caracteres para uma coluna de 12) tem índice único. A colisão é muito improvável, e `mb_*` evita cortar acentos. Falta repetição em caso de colisão, mas o risco é negligenciável.
- `SaleObserver` e `ExpenseObserver` mantêm os movimentos sincronizados na gravação e na eliminação. Depende de LOG-004 para evitar a duplicação.
- `ConvertedProposalService::changeStatus` trata a falha de email com `try/catch` e `report()`.

## Resultado dos testes existentes

- Confirmado antes de correr: `phpunit.xml` usa `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=array`, `CACHE_DRIVER=array` e `SESSION_DRIVER=array`.
- Comando: `php artisan test`. Duração: cerca de 40 s.
- Resultado: 764 passaram, 1 falhou (5272 asserções).
- Falha: `Tests\Feature\ExampleTest::test_the_application_returns_a_successful_response` (`GET /`). Erro: `no such table: settings` em sqlite. O teste não usa o trait `RefreshesDatabaseWithoutMysqlOnlyMigrations`, por isso a página inicial pública consulta `settings` numa BD vazia. É a falha pré-existente indicada no CLAUDE.md. Não foi escondida nem alterada.
- Recomendação: deixar o `ExampleTest` a usar o trait da suite, ou removê-lo se já não tiver valor, em vez de o ignorar.

## Lacunas de cobertura de testes por módulo

Contagem de ficheiros em `tests/` que mencionam cada símbolo (0 significa sem cobertura direta):

| Módulo | Cobertura | Lacuna |
|---|---|---|
| `SaleCalculator` | 0 | Sem testes: IVA 23/13/6, regime de margem, venda abaixo do custo, taxa desconhecida |
| `V3VehicleController::storeSale`, `storeExpense`, `destroySale` | 0 | Duplicado, IVA da despesa, `vat_type` inválido, estado da viatura |
| `ServiceTranches` | 0 (só via `ConvertedProposalTranchesTest`) | Percentagens que não somam 100, arredondamentos de cêntimos |
| `ProposalAcceptanceService` | 0 por nome, coberto indiretamente (`ProposalAcceptanceIdentificationTest`) | Concorrência, cancelar e reaceitar, email com falha |
| `SendFollowupReminder`, `SendTaskReminders`, `SendOverdueCommissionAlert` | 0 | Janela horária, deduplicação, falha de email |
| Relatórios `reports:monthly`, `quarterly`, `annual` | 0 | Período correto (`subDay`), fuso, falha de PDF ou email |
| `Kernel` (agendamento) | 1 | Sem asserção sobre sobreposição e `onOneServer` |
| `ClientMergeService` | 1 | Tabelas com FKs de outro nome, índices únicos |
| `GeocodingService`, `SyncGoogleReviews`, `RadarTrimPriceAiService`, `ClassifyRadarListingTrims` | 0 | Respostas de erro, 429, JSON inválido, `chunk` |
| `V3Vehicle::generateReference` | 13 (uso, não o algoritmo) | Eliminação intermédia e concorrência |
| `MovementService::summary` | `MovementServiceTest` existe | Falta o cenário multi-ano e o pendente com sinais |
| Rotas e `route()` | `PermissionCatalogTest` | Sem teste que verifique `route('nome')` literais |

## Ordem de correção sugerida

1. LOG-002 e LOG-001 (fuso e lembretes), porque afetam o negócio todos os dias.
2. LOG-004, LOG-005 e LOG-003 (vendas, referências e IVA), porque tocam em dinheiro e em erros 500.
3. LOG-006, LOG-009 e LOG-010 (emails, agendador e transações).
4. LOG-007 e LOG-008, depois de decisões de negócio.
5. O resto (LOG-011 a LOG-018) como limpeza, com um teste novo por correção.
