# Candidatos a limpeza — IzzyCar

Auditoria só de leitura, feita a 2026-10-31 (o texto descreve o repositório à data da auditoria; as marcas **[FEITO]**, **[PARCIAL]** e **[MANTIDO]** refletem o estado final, depois dos commits `00eb765`…`dd006d8`; a remoção do código V1 foi feita em `a33b12b`); não houve ligação à base de dados remota (o `route:list` correu com sqlite em memória). Sem segredos nem dados pessoais neste documento.

Convenções: **Risco** = probabilidade de partir algo ao eliminar. **Confiança** = confiança de que o candidato está mesmo sem uso. **Ganho** distingue armazenamento (A) de manutenção/desempenho (M).

Método: `route:list --json` (598 rotas) cruzado com ficheiros de `app/Http/Controllers`; `grep -w` do nome de cada classe em `app`, `routes`, `resources`, `config`, `tests`, `database`; nomes `route('...')` usados em views/PHP contra o nome das rotas; `git ls-files` e `.gitignore` para ver o que está versionado; `du` para tamanhos.

## 0. Antes de apagar seja o que for

- O deploy faz `git pull` e a pasta `public/` do domínio é uma cópia separada (ver memória do projeto). Apagar ficheiros de `public/` no repositório **não** os remove do servidor, e ficheiros em falta aqui podem ainda estar a ser servidos lá. Confirmar na cópia pública antes de concluir que algo não é usado.
- Confirmar no servidor (cron e logs de acesso) antes de apagar rotas legado: podem existir favoritos ou links externos para URLs antigos de `/gestao/...`.
- `php artisan test` antes e depois; o `PermissionCatalogTest` falha se uma rota de `/gestao` ficar fora de `config/permissions.php` (e, ao apagar rotas, entradas órfãs no catálogo devem sair juntas).
- Apagar em commits pequenos por grupo (um por secção abaixo) para ser fácil reverter com `git revert`.

## Estado final da limpeza (revisão de 2026-10-31)

O commit `1e52c53` (chore(limpeza)) removeu 40 ficheiros e dependências sem uso; `00eb765` tirou `users/*.yaml` do índice e `73df400` pôs `/users/` no `.gitignore`; **`a33b12b` (chore(limpeza): remover controllers, views e métodos V1 sem uso) apagou 69 ficheiros e reduziu 3 (72 ficheiros, 4 linhas acrescentadas e 10 465 removidas)**. Em `68dbbe3` saíram também os `use` de controllers V1 já apagados em `routes/web.php`. Os números abaixo foram confirmados com `git show --stat`, `--diff-filter=D` e `--numstat` desse commit.

### Removido em `a33b12b`

| Grupo | Ficheiros | Linhas |
|---|---|---|
| Controllers V1 e legado apagados | 20 | 2 467 |
| Models sem uso (`_Menu`, `ProposedCar`) | 2 | 50 |
| Views V1 apagadas | 47 | 7 052 |
| Controllers reduzidos aos métodos com rota (`ProposalController` de 717 para 262 linhas, `ClientController` de 111 para 41, `ConvertedProposalController` de 72 para 55) | 3 | 896 (líquidas) |
| **Total** | **72** (69 apagados e 3 reduzidos) | **10 465 removidas** |

- Controllers apagados: `VehicleController`, `ExpenseController`, `SaleController`, `SupplierController`, `PartnerController`, `UserController`, `RoleController`, `PermissionController`, `SettingController`, `VehicleAttributeController`, `AttributeGroupController`, `FormProposalController`, `_MenuController`, `Admin/VehicleV2Controller`, `Admin/ExpenseV2Controller`, `Admin/NewsV3Controller`, `Admin/NewsletterOfferController`, `Frontend/AnuncioController`, `Auth/RegisterController`, `Auth/VerificationController`.
- Views apagadas (47): as pastas V1 `vehicles`, `expenses`, `sales`, `suppliers`, `partners`, `settings`, `users`, `roles`, `permissions`, `vehicle_attributes`, `attribute-groups`, `form_proposal`, `_menus`, `clients` (exceto `contract_service`), `converted_proposals` (exceto `timeline`), quatro de `proposals` (`index`, `form`, `_pdf` e `Old_pdf`), `admin/v2/vehicles` (2), `admin/v2/newsletter` (2), `admin/v2/newsletter-management/index-old` e `admin/v3/news` (2).
- Métodos sem rota removidos de `ProposalController` (ficam `detail`, `requestAlternativeQuote`, `accept` e `sendAcceptanceEmail`), de `ClientController` (fica `contractService`) e de `ConvertedProposalController` (ficam `updateStatus` e `detailTimeline`). Sai com isto o código que executava `scripts/process_link.py`.

### O que fica, e porquê

- `ProposalController`, `ClientController::contractService` e `ConvertedProposalController`: servem a página pública da cotação, a aceitação, a timeline e o contrato de serviço; têm rota.
- `Vehicle`, `Expense`, `VehicleImage`, `VehicleDocument`, `Car`, `ModelCar`, `ModelYear`, `SubmodelCar` e os observers `VehicleObserver`/`ExpenseObserver`: ainda ligados (relações em `Sale`, `Legalization`, `FinancialMovement`, comando `import:car-data`, `routes/api.php`); só saem quando a migração para V3 e movimentos estiver fechada.
- `SaleV2Controller` e as rotas `v2/sales`: vivos (a listagem e a eliminação servem as vendas V3; `create` redireciona para o fluxo V3). Ficam cerca de 200 linhas de código comentado.
- CMS V1 de páginas, tipos de página e menus (`PageController`, `PageTypeController`, `PageContentController`, `MenuController`), `brands`, `anuncios` e `ad-searches` (`AdSearchController`, com um `dd()` na linha 421): continuam ligados por URL; decisão do Pedro (G).
- `layouts/app`, `layouts/old_admin` e as views `welcome`, `home`, `auth/register`, `auth/verify`: sem uso real, mas não foram tocados (guardam os 2 `route()` inexistentes de LOG-016 que sobram em `auth/`).
- Ficheiros da raiz (`info-*`, `retomas`, `plataforma vendas`, `please`, `server.php`, `AutoScoutSearch/*.csv`), `scripts/` e assets de `public/`: por decisão do Pedro e por causa da cópia separada de `public/`.
- O PermissionCatalogTest continua verde: a remoção não deixou entradas órfãs no catálogo. Os `route()` que ficaram por resolver (6) estão em LOG-016 de `problemas.md`.

| Candidato | Estado |
|---|---|
| A `laravel.log` (local) | Mantido: não versionado, depende de truncar e de mudar para o canal `daily` |
| B `scarperAutoscout/venv/` | Mantido (local, ignorado) |
| C cópias e `.bak` de views | **Feito** (`1e52c53`): 20 views (18 em `frontend/` e 2 em `proposals/`) mais `scripts/marcas_modelos_submodelos copy.txt` |
| `.DS_Store` | **Feito** (`1e52c53`): 13 ficheiros removidos do índice e `.DS_Store` no `.gitignore` |
| D `users/*.yaml` | **Parcial**: fora do índice (`00eb765`) e ignorado (`73df400`); o hash continua no histórico git |
| E ficheiros soltos na raiz | **Parcial**: `scripts/soup_debug.html` removido (`1e52c53`); mantidos `info-faq`, `info-faq-proposta`, `info-metricas`, `info-transportes.md`, `info-venda`, `plataforma vendas`, `retomas`, `please`, `server.php`, `AutoScoutSearch/*.csv` à espera de confirmação do Pedro (podem ser a única cópia dos briefings) |
| F controllers/views V1 sem rota | **Feito** (`a33b12b`): 20 controllers, 2 models e 47 views apagados, 3 controllers reduzidos |
| G rotas V1 ainda ligadas | Mantido (decisão do Pedro) |
| H assets de `public/` sem referência | Mantido: exige confirmar a cópia separada de `public/` no domínio |
| I dependências | **Parcial** (`1e52c53`): removidos `barryvdh/laravel-snappy` (+ `config/snappy.php`), `spatie/browsershot`, `laravel/legacy-factories` e o script `pre-update-cmd` do Statamic; ver secção 7 |
| J `scripts/` | Mantido (decisão sobre `ad-searches`) |
| K comandos sem documentação | Mantido |
| `ProposalController::downloadPdf` | **Feito** (`1e52c53`): método removido, que era o único uso do Browsershot |

## 1. Resumo por prioridade

| # | Candidato | Risco | Confiança | Ganho | Espaço |
|---|---|---|---|---|---|
| A | `storage/logs/laravel.log` (ignorado, local) | baixo | alta | A + M | 179 MB |
| B | `scarperAutoscout/venv/` (ignorado, local) | baixo | alta | A | 146 MB |
| C **[FEITO]** | Ficheiros `* copy.*` e `.bak` em `resources/views` (versionados) | baixo | alta | M | ~0,4 MB |
| D **[PARCIAL]** | `users/*.yaml` (resto de Statamic, versionado, com hash de password) | baixo | alta | M + segurança | 4 KB |
| E **[PARCIAL]** | Ficheiros soltos na raiz (`info-*`, `plataforma vendas`, `retomas`, `please`, `server.php`, `AutoScoutSearch/`) | baixo | alta/média | M | ~60 KB |
| F **[FEITO `a33b12b`]** | Controllers, modelos e views V1 sem rota | baixo-médio | alta | M | ~0,7 MB |
| G | Rotas e views V1 ainda ligadas (CMS de páginas/menus, `brands`, `anuncios`, `ad-searches`) | médio | média | M | ~0,2 MB |
| H | Imagens `public/img/{1,2,3}.jpg` e outros assets sem referência | baixo | média-alta | A + desempenho | ~8 MB |
| I **[PARCIAL]** | Dependências Composer sem uso (`snappy`, `htmlpurifier`, `easynav`, `legacy-factories`, scripts `Statamic`) | baixo-médio | média-alta | M | ~2,3 MB em `vendor/` |
| J | `scripts/` (Python antigo) | médio | média | M | 0,8 MB |
| K | Comandos Artisan manuais sem documentação | baixo | n/a (manter, documentar) | M | — |

## 2. Ficheiros pesados e ignorados (armazenamento local)

Medido com `du`: `storage` 234 MB, `node_modules` 209 MB, `vendor` 185 MB, `scarperAutoscout` 146 MB, `.git` 49 MB, `public` 18 MB.

### A. `storage/logs/laravel.log`
- Local: `storage/logs/laravel.log`, 179 MB (cresceu até 31 Out). Ignorado (`storage/logs` no `.gitignore`), não versionado.
- Motivo: log único sem rotação (`LOG_CHANNEL=single` presumível). Ocupa 76% de `storage`.
- Evidência: `ls -la storage/logs`; os outros logs (`permissions-*.log`, `csp-*.log`) já são diários e pequenos (1 MB no máximo).
- Incerteza: o `laravel.log` **do servidor** pode ser ainda maior; este é o da máquina local. Conteúdo pode ter dados pessoais (stack traces com payloads).
- Verificar: ver as últimas linhas por causa de erros recorrentes antes de truncar (um erro a repetir-se milhões de vezes é a causa provável do tamanho e merece ser corrigido, não só limpo).
- Acção: truncar/arquivar localmente; no servidor, passar a canal `daily` com `LOG_DAILY_DAYS`. Ganho: armazenamento e leitura dos logs.

### B. `scarperAutoscout/venv/`
- 146 MB de 146 MB da pasta; `scarperAutoscout/.gitignore` já ignora `venv/` e `__pycache__/`. Só ocupa espaço local.
- O código do scraper (96 KB, 21 ficheiros) **está em uso**: `RadarController` lê `scarperAutoscout/searches/*.yaml` e `AutoscoutScraperRunner` dispara o Python. Não apagar a pasta, só o `venv` se for recriável com `requirements.txt`.
- Incerteza: se o servidor usa um `venv` próprio, este é só da máquina de desenvolvimento.
- Risco baixo. Verificar que `requirements.txt` instala limpo antes de apagar.

### Outros (não são candidatos, só contexto)
- `storage/app` 59 MB e `storage/app/public` 38 MB são dados da aplicação (fotos de viaturas, PDFs de legalização, contratos): **não apagar**.
- `storage/framework/views` 3,0 MB é cache de Blade, regenerável; irrelevante.
- `vendor/mpdf` 95 MB (sobretudo fontes TTF) é usado (Modelo 9, 1460, 1RA); não é candidato.
- `.phpunit.result.cache` e `.superpowers/` já estão ignorados (`.gitignore` e `.git/info/exclude`).
- **[FEITO em `1e52c53`]** `.DS_Store`: **13 ficheiros versionados** (`git ls-files | grep DS_Store`: raiz, `app/`, `app/Http/`, `database/`, `public/`, `public/vendor/`, `resources/`, `resources/views/`, `scripts/`, `scripts/imagens_carro/`, `storage/`, `storage/framework/`, `tests/`). O estado git inicial já mostra dois como modificados. Acção: `git rm --cached` e acrescentar `.DS_Store` ao `.gitignore`. Risco nulo, ganho de ruído em diffs.

## 3. Ficheiros versionados soltos e pastas na raiz

Todos estes estão versionados (`git ls-files`) e nenhum é referenciado por código (`grep` em `app`, `routes`, `config`, `tests`).

### C. Cópias e backups de views (versionados, 21 ficheiros, ~0,4 MB) **[FEITO em `1e52c53`: removidos todos, `git ls-files | grep -E "copy|\.bak"` devolve vazio]**
- `resources/views/frontend/`: `index.blade copy.php`, `import.blade copy.php`, `legalization.blade copy.php`, `terms.blade copy.php`, `privacy.blade copy.php`, `news.blade copy.php`, `news-desktop.blade copy.php`, `news-mobile.blade copy.php`, `news-details.blade copy.php`, `estruturas para usar.blade copy.php`, `vehicles-list-desktop.blade copy.php`, `vehicles-list-mobile.blade copy.php`, `vehicles-detail-mobile.blade copy.php`, `vehicles-list-desktop.blade.php.bak`, `vehicles-list-mobile.blade.php.bak`, `vehicles-detail-mobile.blade.php.bak`; `frontend/cost-simulator/result.blade copy.php`, `simulator.blade copy.php`.
- `resources/views/proposals/`: `pdf.blade copy.php`, `view-proposal.blade copy.php`.
- `scripts/marcas_modelos_submodelos copy.txt`.
- Motivo: nomes com espaço e `copy` não são resolvíveis como views (`view('x.y')` não os alcança) e os `.bak` não terminam em `.blade.php`. O git já guarda o histórico.
- Evidência: grep dos nomes sem `copy` só dá resultados dentro das próprias cópias; `PublicLayoutAssetsTest` menciona `layout_` e `img/2.jpg` mas como asserção negativa.
- Incerteza: nenhuma relevante. Antes de apagar, comparar com o original se alguém guardou ali um bloco para reaproveitar (por exemplo `estruturas para usar`, 32 KB, parece uma biblioteca de blocos).
- Risco baixo, confiança alta. Ganho: manutenção (menos ruído em pesquisas, menos risco de editar a cópia errada).

### D. `users/*.yaml` (resto de Statamic) **[PARCIAL: saiu do índice em `00eb765`, ignorado em `73df400`; o hash continua no histórico git]**
- Um ficheiro YAML versionado com nome, id e **hash de password** de uma conta. É formato de utilizadores Statamic; o projeto não tem Statamic (só uma referência morta em `composer.json`, ver I).
- Risco: baixo para apagar; o ponto importante é de segurança: o hash está no histórico git. Recomenda-se remover do versionamento e, se a password for reutilizada, trocá-la. Não reproduzo o conteúdo aqui.

### E. Ficheiros soltos na raiz **[PARCIAL: só `scripts/soup_debug.html` saiu, em `1e52c53`; o resto mantém-se à espera de confirmação do Pedro]**
| Ficheiro/pasta | O que é | Referenciado? | Veredicto |
|---|---|---|---|
| `info-faq`, `info-faq-proposta`, `info-importação`, `info-importação 2`, `info-legalização`, `info-metricas`, `info-transportes.md`, `info-venda` | Notas de texto (3 a 4 KB cada) de conteúdo/briefing; nomes com acentos e espaço | Não | Mover para `docs/` ou apagar; risco baixo, confiança média (podem ser a única cópia do briefing de conteúdo, confirmar com o Pedro) |
| `plataforma vendas`, `retomas` | Notas de especificação antigas (esquema de tabela `vehicles` com ENUM; já ultrapassado por V3) | Não | Apagar ou mover para `docs/`; baixo/média |
| `please` | Cópia de `artisan` (com o nome mudado) | Não | Apagar; baixo/alta |
| `server.php` | Router do servidor embutido do PHP (legado de Laravel ≤ 8; `php artisan serve` já não o exige no Laravel 12) | Não, mas pode ser usado por `php -S` manual | Apagar; baixo/média-alta |
| `AutoScoutSearch/` | Um CSV (20 KB) de anúncios exportados | Não | Apagar; baixo/alta |
| `webpack.mix.js`, `package.json` | Laravel Mix para `resources/js/app.js` e `resources/sass/app.scss` | `public/js/app.js` e `css/app.css` só usados por `layouts/app.blade.php`/`welcome` | Ver secção G/H |

## 4. Código PHP sem uso (V1 e legado)

Evidência principal: dos 598 nomes de rota, estes controllers **não têm nenhuma rota** (comparado com `route:list --json`):

| Controller / classe | Linhas | Referências fora do próprio ficheiro | Veredicto |
|---|---|---|---|
| `VehicleController` | 262 | só `use` em `routes/web.php` (sem rotas) | Remover. V1, substituído por `V3VehicleController` |
| `ExpenseController` | 159 | só `use` em `routes/web.php` | Remover (substituído por movimentos) |
| `SaleController`, `SupplierController`, `PartnerController`, `UserController`, `RoleController`, `PermissionController`, `SettingController`, `VehicleAttributeController`, `AttributeGroupController` (raiz) | 45 a 221 | só `use` em `routes/web.php` | Remover; existem equivalentes `*V2Controller` com rotas |
| `FormProposalController` (raiz) | 22 | nenhuma | Remover |
| `_MenuController` | 122 | nenhuma | Remover |
| `Admin/VehicleV2Controller` | 427 | nenhuma (as rotas `v2/vehicles` são closures de redirect) | Remover (o CLAUDE.md já o classifica como legado) |
| `Admin/ExpenseV2Controller` | 196 | nenhuma | Remover, depois de confirmar que `v2/movements` cobre tudo |
| `Admin/NewsV3Controller` | 217 | nenhuma; as views usam rotas `admin.v3.news.*` que **não existem** | Remover com `resources/views/admin/v3/news` |
| `Admin/NewsletterOfferController` | 86 | nenhuma; views `admin/v2/newsletter` usam rotas `admin.v2.newsletter.*` inexistentes | Remover com a view |
| `Frontend/AnuncioController` | 20 | nenhuma | Remover |
| `Auth/RegisterController`, `Auth/VerificationController` | 76, 42 | `Auth::routes(['register' => false])` e sem `verify` | Remover; baixo (o registo está desligado) |
| `Models/_Menu` (15 linhas), `Models/ProposedCar` (34) | | zero referências em `app`, `routes`, `resources`, `tests`, `database` | Remover (confirmar que a tabela `proposed_cars` não é lida por SQL cru) |

Estes são o que o enunciado chamava V1: `VehicleController`, `ExpenseController`, `_MenuController`, `_Menu.php` confirmados sem uso. **`ProposalController` NÃO está sem uso**: serve a página pública `proposta/{codigo}` (`detail`), o pedido de cotação alternativa e a aceitação (`accept`). O mesmo para `ConvertedProposalController` (`detailTimeline`, `updateStatus`) e `ClientController` (só `contractService`).

### Métodos mortos em controllers vivos

**[FEITO]** `ProposalController::downloadPdf` saiu em `1e52c53` e os restantes métodos sem rota de `ProposalController`, `ClientController` e `ConvertedProposalController` saíram em `a33b12b` (lista abaixo, a da auditoria, com os que já não existem).
- `ProposalController`: sem rota, `index, create, getDataFromUrl, store, edit, updateStatus, update, destroy, generatePdf, downloadPdf, duplicate, sentWhatsapp, create_by_form` (restam `detail`, `requestAlternativeQuote`, `accept`, `sendAcceptanceEmail`). Em vez de apagar o ficheiro de 987 linhas, esvaziá-lo. `getDataFromUrl` executa `scripts/process_link.py` e `downloadPdf` usa Browsershot contra `127.0.0.1:8000` (ambos código morto com risco: execução de comandos).
- `ClientController`: `index, create, store, show, edit, update, destroy, generateContractPdf` sem rota (resta `contractService`).
- `ConvertedProposalController`: `index, edit, update` sem rota.
- `AdSearchController::old_show`; `Admin/SaleV2Controller::calculate*` (3 métodos); `Admin/TransportQuoteController::show`; `Frontend/VehiclesController::vehicleStory` (a view `vehicles-story` existe, sem rota); `PageController::getNews` (rota comentada). `Frontend/PageController::getEnumValues` está em uso (chamado por outros controllers).
- Confiança alta para métodos sem rota; **verificar chamadas dinâmicas** (`Route::resource`, `->action`, `call_user_func`) com `grep -rn "'metodo'"` antes de apagar cada um. `Frontend/ImportSimulatorController` tem 4 métodos auxiliares sem rota (`calculo_*`, `agravamentoParticulas`, `calcularIdade`) que são provavelmente chamados internamente, não tocar sem ler.

### Modelos legado ainda ligados (não apagar já)
`Vehicle`, `VehicleImage`, `VehicleDocument`, `Expense`, `Car`, `ModelCar`, `ModelYear`, `SubmodelCar`: ainda referenciados (observers registados em `AppServiceProvider`, relações em `Sale`, `Legalization`, `FinancialMovement`, comando `import:car-data`, `routes/api.php`). Só podem sair depois de a migração para V3/movimentos estar fechada. `ExpenseObserver` e `VehicleObserver` estão registados, logo ainda disparam.

### Serviços, exports, mails, observers
Nenhum serviço, export, mail ou notification ficou sem referência (todos com pelo menos 1 uso). Os que têm uma só referência são usados por comandos agendados ou controllers; não são candidatos.

## 5. Rotas e views órfãs

### F. Views V1 sem rota (transitivamente órfãs)
Os controllers V1 acima referenciam estas views, por isso um `grep` simples dá falsos positivos; olhando para as rotas, ficam sem acesso:
`resources/views/{clients (exceto contract_service), vehicles, expenses, sales, suppliers, partners, settings, users, roles, permissions, vehicle_attributes, attribute-groups, proposals/{index,form,pdf,_pdf,Old_pdf}, converted_proposals/{index,edit}, form_proposal, _menus}` (~444 KB), mais `layouts/old_admin`, `layouts/app` (só `welcome`/`home`), `admin/v2/vehicles` e `admin/v2/newsletter`, `admin/v3/news`, `admin/v2/newsletter-management/index-old`.
- **Manter**: `clients/contract_service`, `proposals/{view-proposal,expired}`, `converted_proposals/timeline`, `layouts/admin` (ainda usado por brands, anuncios, ad-searches, pages, menus, page-types, isv, car-analysis), `pdf/*`, `errors/*` e `auth/*` (usados pelo framework, `Auth::routes`).
- Prova de órfãs: a análise de nomes `route('...')` encontrou **mais de 120 chamadas a rotas que não existem** (por exemplo `vehicles.*`, `expenses.*`, `proposals.create/edit/store`, `users.*`, `roles.*`, `admin.v3.news.*`). Se alguma destas views fosse aberta, rebentava com `RouteNotFoundException`; portanto não estão em uso.
- `resources/views/auth/register`, `auth/verify`, `auth/passwords/{confirm,email,reset}`: sem uso se o registo e reset via UI estiverem desligados; **confirmar** que o reset de password não é usado (`password.reset` aparece na lista de rotas sem referências literais, mas o `Auth::routes` usa-o via notificação de reset). Manter `email`/`reset` até confirmar.
- Frontend órfãs: `frontend/___vehicles-detail`, `frontend/antigo layout_ vehicles-list`, `frontend/news-desktop`/`news-mobile` (só citadas por cópias), `frontend/partials/{_frontend-navbar,hero-section,horizontal-tabs,menu-mobile,menu,servicos}` (grep só encontra cópias), `components/icons/segment`. Confiança média: includes dinâmicos (`@include("...$x")`) não foram todos descartados.

### G. Rotas V1 ainda ligadas mas fora do menu V2
`brands` (resource), `anuncios`, `ad-searches` (+ `getFromStandVirtual.py`), `page-types`, `pages`, `page-contents`, `menus`, `menu-items`, `pages.homepage/categories/legalizations/imports/selling`, `car-analysis` (este **está** no menu V2). As rotas `brands.*`, `menus.*`, `pages.*`, `page-types.*`, `page-contents.*`, `menu-items.*` não têm qualquer referência `route()` fora dos próprios CRUD, e o menu V2 usa `admin.v2.brands`/`admin.v2.menus`. Estão acessíveis por URL e portanto **risco médio**: perguntar ao Pedro se o CMS de páginas/tipos de página ainda alimenta o site público (`Frontend/PageController`, `Page`, `PageContent` são usados pelo frontend para texto de Importação/Legalização/Venda; os modelos têm de ficar mesmo que o CRUD V1 saia).
- Rotas nomeadas sem qualquer referência literal (58): inclui `admin.legalizations.modelo1460`, `admin.legalizations.modelo9`, `admin.tasks.json`, `admin.news.destroy/toggle-status/gallery-image.delete`, `admin.v3.vehicles.photos.crop/focal/restore`, `admin.v3.vehicles.generate-ad/save-ad`, `admin.v3.vehicles.legalization.*-document`, `admin.v2.car-candidates.update`, `admin.v2.permissions.destroy/edit`, `admin.v2.vehicle-attributes.sort`. **Não apagar**: muitas são chamadas por JS com URL construída ou `fetch` (nome da rota não aparece). Verificar cada uma com `grep` do URL e, de preferência, com logs de acesso do servidor.
- `routes/web.php`: há ~10 linhas comentadas (`proposals.detail` antigo, `news`, `anuncios-frontend`) e blocos de comentário a documentar V1 «desligado». Limpeza cosmética.

## 6. Assets e imagens

### H. `public/`
Medido: `public/img` 12 MB, `public/vendor` 2,7 MB, `public/images` 1,8 MB, `public/js` 980 KB, `public/css` 556 KB. 111 ficheiros versionados.
- `public/img/1.jpg`, `2.jpg`, `3.jpg` (1,8 + 1,9 + 3,2 = 6,6 MB): a home usa `1-900.jpg`, `2-800.jpg`, `3-900.jpg` e as `.webp`; um teste afirma que `img/2.jpg` **não** aparece no HTML. Sem outras referências em `app`, `resources`, `routes`, `config`, `tests`. O `otimizacoes.md` já assinala o mesmo. Confiança alta nas três; **verificar** que não há `og:image`, e-mail ou página externa a apontar para a URL pública antiga (o URL ficaria 404).
- Sem referências em código (basename não aparece em views, JS, config, docs; copias/`.bak` excluídas):
  - `public/images/mercedes-home.jpeg` (388 KB), `colleagues-working-cozy-office-medium-shot.jpg` (256 KB), `rear-view-young-college-student.jpg`, `faq_graphic.jpg` (136 KB cada).
  - `public/images/topics/*` (12 ficheiros, ~330 KB; ilustrações `undraw_*` de um tema SB Admin).
  - `public/img/manual/{manual-indice,faq,formularios}.png` (~1,1 MB; os outros do `manual/` estão em uso).
  - `public/img/cover-{login,register,forgot-password}.png`, `public/img/svg/undraw_editable_dywm.svg`, `_favicon.png`.
  - `public/css/original.css`, `public/css/novo-front.css` (referido só em cópias), `public/vendor/popper.js/`.
  - `public/mix-manifest.json`.
- Em uso (confirmado): `bootstrap.min.css`, `bootstrap-icons.css`, `sb-admin-2.min.css` (também em `sw.js`), `layout.css`, `jquery.min.js`, `jquery.sticky.js`, `click-scroll.js`, `custom.js`, `bootstrap.bundle.min.js`.
- `public/js/app.js` (796 KB) e `public/css/app.css`: build do Laravel Mix, carregados só por `layouts/app.blade.php` (e `welcome` usa `@vite`, que **não** está instalado: view órfã). Se `layouts.app` sair (views V1 `home`, `profile`), podem sair estes dois e também `webpack.mix.js`, `resources/js`, `resources/sass` e as dependências npm.
- `public/vendor/laravel-filemanager/{files,images}` (844 KB: `adobe.pdf`, `word.docx`, `sleeping-dog.jpg` de demonstração, versionados). Sem uso real; **confirmar** primeiro que o LFM (usado em `layouts/admin` e `components/image-manager`) guarda em `storage`, não nestas pastas (ver `config/lfm.php`).
- Lembrete: copiar as remoções para a pasta pública separada do servidor.
- Ganho: ~8 MB de repositório e menos ficheiros a copiar no deploy; desempenho só se os ficheiros fossem servidos (não são pedidos pelo site, por isso o ganho de desempenho é praticamente nulo; `1.jpg/2.jpg/3.jpg` e `hero.webp` valem sobretudo por recompressão, ver `otimizacoes.md`).

## 7. Dependências

### I. Composer (`composer.json`) **[PARCIAL, ver coluna Veredicto/Estado]**
| Pacote | Uso encontrado | Veredicto |
|---|---|---|
| `barryvdh/laravel-snappy` (+ `config/snappy.php`) | só config e cache de pacotes; nenhum `Snappy::` ou `SnappyPdf` em `app` | **[FEITO `1e52c53`]** pacote e `config/snappy.php` removidos |
| `ezyang/htmlpurifier` (2,1 MB) | só em `proposals/Old_pdf.blade.php` (view órfã) | **[MANTIDO, e agora tem uso]**: `RichText::sanitize()` passou a usá-lo (`86550ca`, SEG-006). Já não é candidato a remoção |
| `devmarketer/easynav` | `Nav::isRoute` só em `layouts/admin` e `about` (V1) | **[MANTIDO]** depende de G (CMS V1 e `layouts/admin` ainda em uso) |
| `laravel/legacy-factories` | nenhum uso do helper `factory()` (só `::factory`) em `tests` e `database` | **[FEITO `1e52c53`]** removido |
| `spatie/browsershot` (+ `puppeteer` em `package.json`, 14 MB em `node_modules`) | só em `ProposalController::downloadPdf` (sem rota) | **[PARCIAL `1e52c53`]** método e pacote Composer removidos; `puppeteer` continua em `package.json` (não foi tocado: o npm não foi mexido) |
| `symfony/http-client`, `symfony/mailgun-mailer`, `symfony/postmark-mailer` | o `.env` usa `smtp`; configs `mailgun`/`postmark` existem mas nenhum mailer os usa; sem `HttpClient` no código | Provável remoção; **médio**: confirmar o mailer de produção (pode ser diferente do local) |
| `composer.json` > `scripts.pre-update-cmd` | chama `Statamic\...\Composer\Scripts::preUpdateCmd` sem o pacote Statamic instalado | **[FEITO `1e52c53`]** removido |
| `barryvdh/laravel-dompdf` + `dompdf/dompdf` direto | usado | Manter; `dompdf/dompdf` direto é redundante (já vem do barryvdh) |
| `mpdf`, `openspout`, `intervention/image`, `laravel-sitemap`, `webpush`, `spatie/permission`, `unisharp/laravel-filemanager`, `symfony/yaml`, `guzzle` | usados | Manter |
| `spatie/laravel-ignition` | só em `require-dev` | Manter |

### npm (`package.json`) **[MANTIDO: nada foi alterado em `package.json`; `tom-select` e `puppeteer` continuam]**
- `tom-select`: o código carrega-o por CDN (`jsdelivr`), nenhum `import` local; remover (baixo/alta).
- `puppeteer`: ver Browsershot. `axios`, `lodash`, `vue-template-compiler`, `sass`, `sass-loader`, `resolve-url-loader`, `laravel-mix`: só alimentam `resources/js/app.js` (um `require('./bootstrap')`) e `resources/sass`; sem Vue em uso real (só `ExampleComponent.vue`). Toda a pipeline Mix pode sair com `layouts/app`. Ganho de manutenção (menos avisos de segurança do `npm audit`); `node_modules` (209 MB) não é versionado, por isso não há ganho no repositório.

## 8. Comandos Artisan

O schedule está em `app/Console/Kernel.php` (o `bootstrap/app.php` liga esse kernel), e agenda 11 comandos. O `google:sync-reviews` está comentado no schedule. Sem agendamento e sem documentação:
- `images:thumbnails`, `import:car-data {file}`, `permissions:report`, `permissions:sync`: zero referências em `docs`, `README`, `CLAUDE.md`, `app` e `tests`. São ferramentas manuais, não mortas: `permissions:sync` e `permissions:report` suportam o refactor de permissões; `import:car-data` alimenta `Car/ModelCar` (legado). Recomenda-se documentar em `docs/` em vez de apagar.
- `ads:import`: chamado por `AdSearchController` e pelo fluxo `scripts/getFromStandVirtual.py` (legado V1).
- `clients:duplicates`, `clients:merge`, `brands:import-logos`, `files:move-to-private`, `converted-proposals:recalculate-tranches`: documentados.
- `google:sync-reviews` está comentado no Kernel: decidir se se liga ou se se apaga o comando.

## 9. `scripts/` e `scarperAutoscout/`

### J. `scripts/` (19 ficheiros versionados, 0,8 MB) **[PARCIAL: `soup_debug.html` e a cópia `marcas_modelos_submodelos copy.txt` saíram em `1e52c53`; o resto mantém-se]**
Scripts Python antigos (Selenium, scraping de Standvirtual/AutoScout, listas de marcas). Referenciados só por `AdSearchController` (`getFromStandVirtual.py`) e `ProposalController::getDataFromUrl` (`process_link.py`, método morto). O `scripts/readme` descreve passos manuais. `scripts/soup_debug.html` (636 KB) é um dump de depuração, sem uso: candidato claro (baixo/alta). `scripts/_testScript.py` (28 KB) e as listas `marcas*.txt` são de uso eventual. Se o Radar de preços (`scarperAutoscout/`) substituiu o `ad-searches`, todo `scripts/` pode sair com o `AdSearchController`; **médio** até confirmar se o Pedro ainda usa as `ad-searches`.

## 10. Plano sugerido por ordem de segurança

1. Truncar `storage/logs/laravel.log` (e corrigir o erro repetido) e passar a canal diário.
2. `git rm --cached` dos `.DS_Store` e `.gitignore`.
3. Apagar `* copy.*`, `.bak`, `scripts/soup_debug.html`, `AutoScoutSearch/`, `please`, `users/*.yaml` e ficheiros de nota da raiz (ou mover para `docs/`).
4. Apagar `public/img/{1,2,3}.jpg` e os outros assets sem referência (após confirmar na pasta pública do servidor).
5. Remover controllers V1 sem rota e as views V1 transitivamente órfãs, mais `Models/_Menu`, `ProposedCar`; remover do catálogo de permissões o que ficar órfão; correr `php artisan test`.
6. Esvaziar `ProposalController`, `ClientController`, `ConvertedProposalController` dos métodos sem rota.
7. Remover dependências Composer/npm (`snappy`, `htmlpurifier`, `easynav`, `legacy-factories`, `browsershot`, `puppeteer`, `tom-select`, script Statamic); correr `composer update` num branch e os testes.
8. Decidir com o Pedro: CMS V1 (`pages`, `menus`, `page-types`), `ad-searches`/`anuncios`, `scripts/`, mailer de produção, pipeline Mix.

Estado do plano acima: passos 2 e 3 feitos em parte (`.DS_Store`, cópias, `.bak`, `soup_debug.html`, `users/` fora do índice; `AutoScoutSearch/`, `please` e notas da raiz mantidos), passo 7 feito em parte (`snappy`, `browsershot`, `legacy-factories`, script Statamic; `htmlpurifier` passou a ser usado), passos 5 e 6 feitos em `a33b12b` (controllers, views, `_Menu`, `ProposedCar` e métodos mortos), passos 1, 4 e 8 por fazer.

Nada aqui implica apagar migrations históricas nem dados de `storage/app`.
