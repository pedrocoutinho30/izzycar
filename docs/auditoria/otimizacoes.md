# Plano de otimização IzzyCar

Auditoria só de leitura ao código (Laravel 12), feita a 2026-10-31. Nada foi alterado no código, no `.env` ou na base de dados.

## Limites desta auditoria (ler primeiro)

- **Não há medições reais.** Não foi feito EXPLAIN, nem ligação à BD remota, nem pedidos HTTP. Todos os benefícios abaixo são **estimativa** (E) até serem medidos com o plano da secção 4.
- Tamanhos de ficheiros (`du`/`find`) são do **checkout local**; produção pode diferir (o `public/` do domínio é uma cópia separada, ver memória de deploy).
- `.env` não foi lido (segredos). O `.env.example` diz `CACHE_DRIVER=file`, `SESSION_DRIVER=file`, `QUEUE_CONNECTION=sync`, `APP_DEBUG=true`, `LOG_LEVEL=debug`: **confirmar os valores reais em produção**.
- Contagens de linhas/volumes de tabelas desconhecidos: onde se propõe índice, a prioridade depende de `COUNT(*)` real (plano de medição, passo M1).
- A skill `salesforce-rulesets` foi carregada por instrução da organização, mas o projeto não é Salesforce (sem `sfdx-project.json`, `force-app`); as suas regras de segurança (sem escrita, sem execução na org) foram cumpridas por coincidência com o âmbito só de leitura. As regras de Apex/Nebula Logger não se aplicam.

Convenção: **[E]** = estimativa; esforço P (<2h), M (0,5-1 dia), G (>1 dia).

---

## 1. Resumo priorizado

| # | Proposta | Área | Impacto [E] | Esforço |
|---|---|---|---|---|
| 1 | Fotos V3 sem otimização (originais até 1,3 MB servidos tal e qual) | imagens | Alto (site público) | M |
| 2 | Cache da listagem pública: chave por `md5(request->all())`, `get()` de tudo, `with('photos')` | cache/BD | Médio-alto | P-M |
| 3 | Índices em `clients` (`is_lead`, `lead_status`, `next_followup_at`) e `lead_activities` | BD | Médio (cron por minuto + dashboard) | P |
| 4 | Dashboard V2: ~35 queries, `whereMonth` sem ano (bug de dados), gráfico em 6 + 8 queries | BD | Médio | M |
| 5 | Emails/PDF síncronos (`QUEUE_CONNECTION=sync`, zero Jobs) | filas | Alto na latência percebida | M-G |
| 6 | Produção: `APP_DEBUG`, `LOG_LEVEL`, `laravel.log` único (172 MB local), cache/sessions em ficheiro | config | Médio (estabilidade) | P |
| 7 | Retenção: `audit_logs`, `lead_activities`, `radar_price_history`, `radar_search_runs`, `sessions`, `jobs` sem prune | BD | Baixo hoje, crescente | M |
| 8 | Listas completas em memória (`Client::...->get()` para selects, kanban, exports, relatório de legalizações) | BD/memória | Médio | M |
| 9 | Front-end: recursos externos duplicados (Font Awesome + Bootstrap Icons + Material Symbols), admin sem bundle | CSS/JS | Médio | M |
| 10 | `Expires` de 1 ano em assets sem versionamento | cache HTTP | Risco de assets antigos | P |

---

## 2. Base de dados

### 2.1 Dashboard V2 (`app/Http/Controllers/Admin/DashboardV2Controller.php`)

**Problema/evidência**
- `index()` (linhas 25-260) faz cerca de 35 queries por carregamento, sem cache: contagens separadas por estado (l.30-70), 3 listas de follow-ups (l.125-160), gráfico de propostas com **6 queries `count()` em ciclo** (l.195-203).
- `whereMonth('created_at', now()->month)` **sem filtrar o ano** (l.58, 65, 66): "novos clientes/vendas/receita deste mês" soma o mesmo mês de todos os anos. É um bug de correção, não só de performance.
- `whereYear/whereMonth/whereDate` (l.150, 156, 199-200, 340-356, 391-415, 21 ocorrências) tornam a coluna não sargável: um índice em `created_at`/`next_followup_at` não é usado. Trocar por intervalos (`>= início`, `< fim`).
- Leads sem atividade (l.112-168): `pluck('id')` de todas as leads ativas, `pluck` de atividades, `diff` em PHP e depois `whereIn('id', ...)`. Dá para uma só query com `whereNotExists`/`whereDoesntHave('activities', created_at >= -3d)`.
- `$followupsAtrasados` (l.125) faz `get()` sem limite; só é preciso lista curta e contagem.
- `ReportDataService::legalizationsSnapshot()` (`app/Services/ReportDataService.php:101`): `Legalization::all()` para contar passos em PHP.

**Alteração proposta**
1. Corrigir `whereMonth` para intervalo `[startOfMonth, endOfMonth]` (confirmar com o Pedro que o pretendido é mesmo "este mês").
2. Substituir os 6 `count()` do gráfico por **uma** query `selectRaw('DATE_FORMAT(created_at,"%Y-%m") ym, COUNT(*)') ... groupBy('ym')` com intervalo (nota: SQL só MySQL; precisa de emulação em sqlite nos testes, como diz o CLAUDE.md), ou agregar por `whereBetween` mantendo agnóstico.
3. Agrupar contagens do mesmo modelo com `selectRaw('SUM(status=...)')` ou `CASE`.
4. Leads sem atividade por `whereNotExists`, `limit(10)` + `count()` separado.
5. Cache curto (60-120 s) por utilizador/âmbito para o bloco de estatísticas (`Cache::remember`), invalidação por TTL (aceitável num dashboard).

**Benefício [E]**: de ~35 para ~12-15 queries; 100-300 ms a menos em servidor partilhado com BD remota (cada ida e volta à BD remota custa tipicamente dezenas de ms). **Medir** (M2).
**Riscos**: mudar o significado dos números do mês (corrigir o bug altera valores mostrados); dados em cache até 2 min.
**Medir**: Laravel Debugbar/`DB::listen` em ambiente local com cópia anonimizada; contar queries e tempo (M2).
**Validação/rollback**: testes Feature do dashboard com datas fixas (`Carbon::setTestNow`); rollback = reverter commit (sem migração).
**Páginas**: `/gestao` (dashboard), comparações de períodos (l.330-420). **Esforço**: M.

### 2.2 Índices (só onde há padrão de query concreto)

Estado actual (migrations): `clients` só tem índices em `email` (unique), `email_normalized`, `phone_key` (`2026_10_01_160000_funnel_phase_three.php:27-28`); **não há índice** em `is_lead`, `lead_status`, `next_followup_at`, `owner_id` (confirmar que o FK de owner criou índice), `created_at`. `proposals`: nenhum índice explícito além do `proposal_code` unique e FKs. `lead_activities`: só FK implícito de `client_id`. `radar_listings`: só `external_id` unique, `(source, external_id)` unique e FK de `radar_search_id`.

| Índice proposto | Padrão de query concreto (ficheiro:linha) | Notas |
|---|---|---|
| `clients(is_lead, lead_status)` | Listagem de leads, estatísticas e kanban: `LeadV2Controller.php:62-79`, `DashboardV2Controller.php:112-118,166,...`, `GlobalSearchService.php:30-38`, exports `ExportController.php:16` | Cardinalidade baixa (booleano); só compensa se `clients` for grande. Validar com M1. |
| `clients(next_followup_at)` | **Cron a cada minuto** `SendFollowupReminder.php:21-25` (`whereBetween`) e dashboard (`next_followup_at < now()`, l.125) | Melhor candidato: 1440 execuções/dia sobre a tabela inteira. |
| `lead_activities(client_id, created_at)` | `DashboardV2Controller.php:118-121` (`client_id IN (...) AND created_at >= ...`), timeline `LeadV2Controller.php:123`, `latestActivity` | Substitui o índice simples de FK (que passa a redundante; o FK reaproveita o composto). |
| `proposals(status, created_at)` | `ProposalV2Controller.php:75-91,139-145,500-510` (kanban: 5 pares `status` + `ORDER BY updated_at/created_at`), dashboard (`Pendente`, `Sem resposta`, `updated_at < ...`) | Ordenações do kanban usam `updated_at` nos fechados: ponderar `(status, updated_at)`. |
| `radar_listings(radar_search_id, removed_at)` ou `(radar_search_id, source)` | `RadarController.php:290-298` (`listingsQuery` sobre os anúncios ativos de uma pesquisa, `paginateByRank` carrega-os todos) | Confirmar em `listingsQuery` os filtros reais antes de decidir as colunas. |
| `v3_vehicles(show_online, status)` | Site público `VehiclesController.php:28,67-92,105-130` (`show_online = 1` + `CASE status` + `created_at`) | O `ORDER BY CASE` impede índice de ordenação; o filtro `show_online` ajuda se a tabela crescer. Tabela pequena hoje (4 veículos de exemplo locais): **prioridade baixa**. |
| `audit_logs(created_at)` | `AuditLogController.php:14,22` (`ORDER BY created_at DESC` sem filtro, paginação 50) | Já existe `(user_id, created_at)` e `(auditable_type, auditable_id)`; a listagem global sem filtro faz filesort. Só vale após crescimento. |
| `proposals`/`clients` pesquisa `LIKE '%termo%'` | `GlobalSearchService.php:142-154`, `ProposalV2Controller.php:97-102` | **Não propor índice**: `%termo%` não usa B-tree. Alternativa só se for lento: FULLTEXT (MySQL InnoDB) com `MATCH ... AGAINST`; exige mudar a pesquisa e os testes sqlite. Medir primeiro. |

**Redundantes**: nenhum encontrado de forma clara. `lead_activities.client_id` ficaria redundante ao criar o composto (MySQL mantém um só). `converted_proposals`: `index('proposal_id')` (`2025_09_03_083206:86`) mais `unique('proposal_id')` posterior (`2026_10_01_140000:29`) são redundantes: o unique chega; remover o simples exige migration e `DROP INDEX` com nome real (verificar nomes em `SHOW INDEX`, M1).

**Riscos**: cada índice custa escrita; MySQL limita nomes a 64 caracteres (dar nomes curtos, regra do CLAUDE.md). **Migração**: nova migration por tabela, `Schema::table(... ->index([...], 'idx_nome'))`; **"falta correr `php artisan migrate` em produção"**; validar em sqlite (testes) e em cópia descartável MySQL; rollback `dropIndex`. **Medir**: `EXPLAIN` antes/depois das queries listadas (M1), em BD de staging com volume real. **Esforço**: P por índice.

### 2.3 N+1 e eager loading

Encontrado (verificar com M2 em views, porque o N+1 aparece na Blade):
- `V3VehicleController::index` (l.36-40) está bem: eager de capa e cliente, paginado.
- Site público: `VehiclesController.php:68,106` faz `with('photos')` (**todas** as fotos de cada viatura) para listagens que só mostram a capa; usar `with(['photos' => fn => where is_cover limit 1])` como já se faz em `:28` e `:240`. Como as listagens guardam o resultado em cache serializado, isto reduz também o tamanho do objeto em cache. Benefício [E]: menos linhas lidas (`n_viaturas × n_fotos` para `n_viaturas`).
- `ProposalV2Controller::kanban` (l.500-510): `with('client:id,name,email')` bom; cada coluna faz `count()` + lista: 10 queries. Aceitável; os fechados já estão limitados a 30.
- `LeadV2Controller::kanban` (l.~92-100): carrega **todas** as leads (`get()`) com `activities` limitada a 1 (limite por pai exige Laravel 11+ e MySQL com window functions; confirmar que o servidor suporta, senão o `limit(1)` aplica-se globalmente e só uma lead mostra atividade). Alternativa: usar a relação `latestActivity` (já usada no dashboard l.127). Limitar cada coluna (como o kanban de propostas) ou paginar por coluna.
- `ConvertedProposalV2Controller::kanban` (l.104): `get()` de todas as cotações convertidas, sem limite, agrupa em PHP. Aplicar o mesmo padrão de limite aos estados terminais.
- `LegalizationController` (l.~59): `Legalization::with(['client'])->withCount('documents')->latest()->get()`: lista inteira, sem paginação.
- `AuditLogController::index` (l.23): `User::orderBy('name')->get()` só para um select: `select('id','name')`.
- Pesquisa global (`GlobalSearchController.php:82-150`): 7 queries por pesquisa, cada uma com `limit`, `with('client')` em propostas/legalizações/vendas: aceitável. O custo está no `LIKE '%x%'` sobre colunas sem índice (ver 2.2) e em `orWhereHas('client'...)` (subquery EXISTS por linha). Medir com M1 antes de agir.
- Mobile (`MobileDashboardService`): já documenta "uma consulta agrupada por grupo"; não há problemas evidentes.

### 2.4 SELECT * e listas completas para selects

- `Client::orderBy('name')->get()` com todas as colunas para `<select>`: `ProposalV2Controller.php:127,172,173,238,239,353,354`, `SaleV2Controller.php:119,172`, `V3VehicleController.php:95`. Cresce linearmente com clientes/leads e é reenviado no HTML. **Proposta**: `select('id','name')` (ganho imediato, P) e, a prazo, pesquisa assíncrona (Tom Select já existe em `package.json`) em vez de carregar tudo. Benefício [E]: memória e HTML proporcionais ao nº de clientes; relevante a partir de milhares.
- Catálogos repetidos em cada render: `AttributeGroup::orderBy('order')->get()` e `VehicleAttribute::orderBy('order')->get()` em `ProposalV2Controller.php:181-182,235,241,362-363` e `V3VehicleController.php:100-101`. Dados quase estáticos: `Cache::remember` com invalidação nos controllers `AttributeGroupV2Controller`/`VehicleAttributeV2Controller` (chave versionada como `VehicleCache`). Benefício [E] pequeno (2 queries), baixo risco.
- `Setting::all()` em `ClientController.php:109,133` ignora a cache que `Setting` já tem (`Setting.php:31`): usar o acessor em cache.
- Controllers legados (`app/Http/Controllers/ExpenseController.php`, `VehicleController.php`, `SaleController.php`: `Vehicle::all()`, `Partner::all()`...) são código V1 (não reativar nem estender, CLAUDE.md): não otimizar; avaliar remoção.

### 2.5 Paginação vs `get()` em memória, exports e comandos

- `ExportController.php:28,82,120`: CSV de leads/clientes/propostas faz `->get()` e depois `streamCsv`. Para volumes grandes, usar `->cursor()`/`lazy()` dentro do callback do stream. `ExportService.php:128` já usa chunks (`self::CHUNK`): é o padrão a replicar. Benefício [E]: memória constante; relevante só acima de ~10-50 mil linhas.
- `GenerateSitemap.php:106,133`: `get()` de viaturas e artigos; volume pequeno, ignorar (ou `lazy()`).
- `RadarController::paginateByRank` (l.296-298): carrega **todos** os anúncios da pesquisa em PHP para paginar por ranking calculado. É o ponto mais pesado do Radar se as pesquisas tiverem milhares de anúncios. Opções: calcular o rank em SQL (window function) ou guardar o score em coluna atualizada pelo refresh (`radar:refresh-active`). Esforço G; só se M2 mostrar lentidão.
- `ClassifyRadarListingTrims` já usa `chunk`; comandos de email e relatórios operam sobre conjuntos pequenos.
- `RefreshActiveRadarSearches` usa `Cache::lock` (`:26`) com TTL 3300 s: com driver `file` o lock é por ficheiro (aceitável num só servidor).

### 2.6 Tipos, constraints e FKs (observações)

- `proposals.status` é `ENUM` com `'Enviada'` (`2025_03_06_093214:41`) enquanto o código usa `'Enviado'` (`ProposalV2Controller.php:~495`): verificar migrations posteriores que alterem o enum (CLAUDE.md avisa que alterar ENUM exige migration); possível inconsistência de dados ou `Aprovada`/`aprovada` (`ReportDataService.php:~27` aceita ambos). Rever antes de indexar `status`.
- `radar_listings.url` é `TEXT` sem índice, correto. `lead_activities` tem `icon`/`color` por registo (repetido, poupável com enum/derivado): ganho irrelevante, não propor.
- `audit_logs.old_values/new_values` são JSON com `getAttributes()` completo em `recordCreated/recordDeleted` (`AuditLog.php:62-80`): cresce com o tamanho do registo. Ver 2.7.

### 2.7 Crescimento e retenção

Tabelas sem qualquer prune/arquivo no código (`grep` de `Prunable`/`prune`/`model:prune`: nenhum resultado) e sem entrada no `Kernel::schedule`:

| Tabela | Origem do crescimento | Proposta | Notas |
|---|---|---|---|
| `audit_logs` | `login/logout` + create/update/delete (observers) com JSON completo (`AuditLog.php:38-59`), `user_agent` | Reter 12-24 meses online; arquivar/exportar o resto; `Prunable` + `model:prune` agendado | **Decisão de negócio/RGPD**: tem IP e user agent. Alinhar com política de privacidade. |
| `lead_activities` | Eventos de sistema por lead | Manter (é histórico comercial); só limitar `type='system'` antigos se pesar | Não apagar notas/chamadas. |
| `radar_price_history` | 1 linha por anúncio por refresh (3x/dia, `Kernel.php:62`) = 3 linhas/anúncio/dia, mesmo sem alteração de preço | **Gravar só quando o preço muda** (ou 1x/dia); reter 12 meses para anúncios removidos | É o maior risco de crescimento: 1000 anúncios ativos = ~1,1 M linhas/ano [E]. Confirmar no serviço de scraping se já deduplica antes de agir. |
| `radar_search_runs` | 3 execuções/dia/pesquisa ativa | Reter 90 dias | Tabela sem `created_at`, usar `started_at`. |
| `newsletter_send_logs` | Uma linha por newsletter×cliente; é registo de envio (evita duplicados) | Manter enquanto a newsletter existir | Tem unique correto. |
| `sessions` | Driver atual `file` (sem tabela); se passar a `database`/`redis`, `last_activity` já tem índice (`0001_01_01_000000:37`) | Manter `file` ou migrar para `database` apenas se houver 2+ servidores | Limpeza automática pelo GC do Laravel. |
| `jobs`/`failed_jobs` | Hoje `QUEUE_CONNECTION=sync` (não usados) | Ver 3.1 | Quando houver fila, agendar `queue:prune-failed --hours=168`. |
| `storage/logs/laravel.log` | Canal `stack` (verificar `single`), **172 MB num único ficheiro local** | Canal `daily` com `days: 14` (como `csp`/`permissions` já têm, `logging.php:51-71`) | Ver 3.5. |
| `storage/framework/cache` | Chaves versionadas nunca apagadas (ver 2.8) | Driver com TTL real (database/redis) ou `cache:prune-stale-tags`/limpeza | |

**Medir**: tamanho e linhas por tabela (M1: `information_schema.tables`). **Rollback**: prune é destrutivo: exportar para ficheiro/backup antes da primeira execução; começar com `--pretend`.

### 2.8 Cache (chaves, invalidação, TTL, driver)

**Evidência**
- Driver por omissão `file` (`config/cache.php:21`).
- Listagem pública: `VehiclesController.php:65,101` usa `md5(json_encode($request->all()))` como chave. **Qualquer parâmetro de query** (utm, fbclid, `page`, bots a testar valores) gera uma chave nova guardada 600 s; com `file` cada uma é um ficheiro serializado com a coleção de modelos + fotos. Risco de inchar `storage/framework/cache` e de miss constante. A invalidação por versão (`VehicleCache::key` + `flush()` com `Cache::forever`, `app/Support/VehicleCache.php:18-23`) é boa, mas as chaves antigas só expiram por TTL e, no driver `file`, só são removidas quando lidas ou limpas à mão.
- `V3Vehicle::saved/deleted` e `V3VehiclePhoto::saved/deleted` chamam `flush()` (`V3Vehicle.php:22`, `V3VehiclePhoto.php:16`): cada upload de fotos (N ficheiros) incrementa a versão N vezes (inócuo, mas faz leitura+escrita de ficheiro por foto).
- Nenhum cache a nível de página: `grep Cache-Control|ResponseCache` sem resultados nos controllers públicos.

**Alteração proposta**
1. Construir a chave só com os filtros **permitidos** (`$request->only(['brand','model','year','fuel','price_max','km_max'])`, normalizados e ordenados) em vez de `all()`.
2. Cachear **IDs** ordenados ou paginar em SQL em vez de a coleção completa; passar a `paginate()` se a listagem pública crescer.
3. Considerar `CACHE_DRIVER=database` (ou `redis` se o alojamento permitir) para ter TTL/limpeza reais; no Hostinger partilhado `file`/`database` são as opções realistas.
4. Cache HTTP de páginas públicas estáticas (home, serviços, simuladores, notícias): cabeçalhos `Cache-Control: public, max-age=300, s-maxage=...` por middleware, só para visitantes anónimos sem sessão/CSRF na página (atenção a formulários com `@csrf`: o token em HTML em cache invalida submissões; só aplicar a páginas sem formulário ou carregar o token por JS).
5. `Setting`/menus/logo já têm cache com `forget` na escrita (`Setting.php:42-44`): bom, manter.
6. Chaves de taxonomia (`autoscout:taxonomy:*`, TTL em dias): ok.

**Benefício [E]**: evita crescimento do cache e aumenta o hit-rate das listagens (hoje cada combinação de query string é miss na primeira visita). **Riscos**: página em cache com CSRF/mensagens flash; mudar o driver limpa a cache (aceitável). **Medir**: nº de ficheiros e tamanho de `storage/framework/cache` em produção; taxa de hit (log temporário). **Rollback**: reverter commit; `cache:clear`. **Páginas**: `/viaturas` e filtros AJAX (`filteredVehicles`), home (`vehicles-home`). **Esforço**: P-M.

---

## 3. Site público, backoffice e mobile

### 3.1 Tarefas síncronas que deviam ir para fila

**Evidência**: `QUEUE_CONNECTION=sync` no `.env.example`; **não existe** `app/Jobs`, nenhum `ShouldQueue`, `->queue(` ou `dispatch(` em `app`/`routes`. Todos os `Mail::...->send(` correm no pedido HTTP:
- Aceitação de cotação: gera PDF do contrato (`ContractService::generateContractPdf`), grava-o e envia 2 emails com anexo no mesmo pedido (`ProposalController.php:~905-945`; também `ConvertedProposalService.php:74`, `ConvertedProposalV2Controller.php:273`).
- Formulário público de importação: 2 emails por submissão (`Frontend/ImportController.php:167,172`); simulador de custos (`CostSimulatorController.php:218`).
- Observer de propostas: envia emails ao mudar de estado (`ProposalObserver.php:96,100`), ou seja, **dentro do `save()`**.
- Newsletter: ciclo `foreach` de `Mail::send` com `updateOrCreate` por destinatário, dentro do pedido (`NewsletterManagementController.php:~140-175`): para N clientes o pedido demora N envios SMTP; risco de timeout (limite do PHP no alojamento).
- Resto: comandos agendados (`reports:*`, lembretes) já correm fora do pedido, ok.
- IA/OpenAI (`RadarTrimPriceAiService`) e scrapers são invocados por comando agendado (`Kernel.php:62-66`), não por pedido: ok. Verificar se existe botão no backoffice que dispara refresh/scrape sincronamente (`RadarController`, 709 linhas): não confirmado nesta leitura.
- Geocoding (`GeocodingService`): confirmar pontos de chamada (não analisado em detalhe; plano M2).
- Miniaturas: `ThumbnailService::generate` corre no pedido de gravação do parceiro (`PartnerV2Controller.php:94,142`); volume baixo.

**Alteração proposta**
1. Passar `Mail::to(...)->send(...)` a `->queue(...)` (Mailables `implements ShouldQueue`) e usar `QUEUE_CONNECTION=database` (as tabelas `jobs`/`failed_jobs` já existem: `0001_01_01_000002_create_jobs_table.php`).
2. Worker no Hostinger partilhado: `php artisan queue:work --stop-when-empty` por cron a cada minuto (`schedule:run` já deve estar a correr via cron; adicionar `$schedule->command('queue:work --stop-when-empty --max-time=55')->everyMinute()->withoutOverlapping()`).
3. Newsletter: `Bus::batch` ou um Job por destinatário com `ShouldQueue`, mantendo o `NewsletterSendLog` (unique) como idempotência.
4. PDF do contrato: Job que gera, guarda `ConvertedProposalDocument` e envia; a UI mostra "a enviar".
5. `Mail::...->afterCommit()`/`ShouldQueue` + `afterCommit` no observer para não enviar se a transação reverter.

**Benefício [E]**: tempo de resposta do POST de aceitação/formulários cai de segundos (PDF + 2 SMTP) para <300 ms [E]; newsletter deixa de depender de timeout. **Riscos**: emails deixam de falhar visivelmente (tratar `failed_jobs` + alerta); sem worker nada é enviado (**risco alto se o cron falhar**: monitorizar `jobs` pendentes); serialização de modelos nos Jobs; testes que usam `Mail::fake()` continuam válidos (`Mail::assertQueued`). **Rollback**: voltar `QUEUE_CONNECTION=sync` (comportamento atual, sem alterar código). **Medir**: tempo do POST (M3) antes/depois; atraso de entrega. **Páginas**: aceitação pública da cotação, formulários de importação/simulador/contacto, newsletter, mudança de estado de cotação. **Esforço**: M (emails) / G (newsletter em lote + monitorização).

### 3.2 Imagens

**Medições locais (`du`/`find`, 2026-10-31)**
- `public/` 18 MB: `public/img` 12 MB (hero.webp 1,1 MB; `1.jpg` 1,7 MB, `2.jpg` 1,8 MB, `3.jpg` 3,1 MB; `img/manual` 2,5 MB), `public/vendor` 2,7 MB, `public/images` 1,8 MB.
- `storage/app/public` 38 MB: `v3-vehicles` **29 MB** (57 ficheiros, **0 em webp**, 30 com mais de 500 kB, fotos até 1,29 MB), `proposals` 2,6 MB (PNG de 1,7 MB), `import-opportunities` 1,6 MB (PNG 1,6 MB).

**Evidência**
- `V3VehicleController::uploadPhoto` (l.322-345) guarda o ficheiro original (`$file->store(...)`, limite 5 MB) **sem otimizar nem criar variantes**. `ImageOptimizerService` (WebP, `MAX_WIDTH=1920`, variantes thumb/medium/large) só é usado no legado `VehicleV2Controller.php:341,363` e em `ProposalService.php:402`; **as fotos V3, as que o site público mostra, não passam por ele**.
- Listagem pública: `<img src="storage/{path}" loading="lazy">` (`vehicles-home.blade.php:20`) serve o original em cartões de ~300-400 px de largura.
- Nas vistas públicas: 93 `<img`, 47 com `loading="lazy"`, apenas ~5 com `width=`/`height=` (CLS). A imagem do herói (`hero.webp` 1,1 MB) é pesada para LCP.
- `ThumbnailService` (Intervention/GD) já gera `*-{w}w.webp` e é usado em logo e parceiros, **não** em viaturas.

**Alteração proposta**
1. No upload V3: reduzir a 1600-1920 px, converter para WebP (qualidade ~80) e gerar variante de cartão (~480 px) com `ImageOptimizerService`/`ThumbnailService`; guardar caminhos no `V3VehiclePhoto` (ou derivar por convenção como `ThumbnailService::variantPath`).
2. Comando de backfill (como `GenerateImageThumbnails.php`) para as 57 fotos existentes, **nunca apagando o original** até validar.
3. Usar `<img srcset sizes width height>` e `fetchpriority="high"` só na imagem LCP; `loading="lazy"` + `decoding="async"` abaixo da dobra.
4. Recomprimir `public/img/1.jpg|2.jpg|3.jpg` (1,7-3,1 MB) e `hero.webp` (alvo <250 kB [E]) e verificar se são realmente usados (`grep`) antes de otimizar; remover os que não são.
5. PNG de propostas/oportunidades: converter para WebP/JPEG no upload.

**Benefício [E]**: fotos de viatura de ~900 kB para ~60-120 kB nos cartões (-85-90%); peso da `/viaturas` e home poderá cair de vários MB para <1 MB [E]. **Riscos**: GD sem suporte WebP no servidor (verificar `gd_info()` em produção); qualidade percebida; fotos antigas continuam grandes até ao backfill; **público/privado**: respeitar `docs/ficheiros-privados.md` (não mexer em ficheiros privados). **Medir**: Lighthouse/WebPageTest (LCP, bytes de imagem) em `/`, `/viaturas`, detalhe de viatura (M3). **Rollback**: variantes são adicionais; reverter o uso no Blade para o original. **Esforço**: M (upload + Blade) + P (backfill).

### 3.3 CSS/JS, fontes e recursos externos

**Medições locais**
- `public/css`: `bootstrap.min.css` 195 kB, `sb-admin-2.min.css` 177 kB, `bootstrap-icons.css` 89 kB, `layout.css` 61 kB, `original.css` 23 kB, `novo-front.css` 16 kB. `public/js`: `app.js` **814 kB (build de desenvolvimento, não minificado)**, `jquery.min.js` 86 kB, `bootstrap.bundle.min.js` 80 kB. `public/mix-manifest.json` só contém `app.js` e `app.css` (vazio, 0 B).
- `webpack.mix.js` (laravel-mix 5, `package.json:15`) só compila `resources/js/app.js` (apenas `require('./bootstrap')`, ou seja, axios/lodash) e `app.scss`; o CSS/JS que as páginas usam (`layout.css`, `bootstrap.min.css`, `custom.js`...) é **servido à mão de `public/`**, sem pipeline, sem minificação nem hash.

**Evidência (carregamento por layout)**
- Site público (`frontend/partials/layout.blade.php:125-166,324-326`): Bootstrap 5 local (bloqueante, 195 kB), `layout.css` com versão por `filemtime` (bom), fontes Google com `media="print" onload` (bom), Bootstrap Icons local (diferido), **Font Awesome 6.5 da cdnjs** (diferido), **Material Symbols** do Google (`display=block`) e Swiper da jsDelivr (`vehicles-home.blade.php:161`). Três bibliotecas de ícones para o mesmo site; JS com `defer` (bom); jQuery 86 kB carregado (verificar se algo de `custom.js`/`click-scroll.js` o exige).
- Backoffice V2 (`layouts/admin-v2.blade.php`, **91 kB de HTML**): Bootstrap 5.3.0 CSS/JS da jsDelivr, Bootstrap Icons 1.11 CDN e **Font Awesome 6.4 CDN** (linhas 28-32, 1728), CSS e JS **inline** (2 blocos `<style>`, centenas de linhas): não cacheáveis entre páginas. Mobile (`admin-mobile.blade.php:12-13,291`): Bootstrap + Icons por CDN. Layout `admin.blade.php` (legado): jQuery carregado **duas vezes** (l.155 `code.jquery.com` e l.438 local) mais SB Admin 2.
- Dependências de terceiros sem fixação nem SRI na maioria (risco de segurança/CSP, ver `docs/seguranca-csp.md`).

**Alteração proposta**
1. Mover o CSS/JS inline do `admin-v2` e `admin-mobile` para ficheiros em `public/` com versão (`?v=filemtime`, como o `layout.css`): cacheáveis entre navegações.
2. Escolher **uma** biblioteca de ícones por superfície: Bootstrap Icons no backoffice (já usado nas views `bi-*`); confirmar usos de `fa-*` com `grep` e, se residuais, remover Font Awesome (-~75 kB gzip de CSS + fontes [E]).
3. Self-host (ou pelo menos fixar versão + SRI) de Bootstrap/Icons/Swiper para eliminar 3-4 origens e DNS/TLS adicionais; já existe `public/vendor`.
4. Substituir `public/js/app.js` (814 kB, dev) por build de produção (`npm run prod`) ou remover se `layouts/app.blade.php` não está em uso (as views `clients/*`, `about`, `profile` que o estendem parecem legado V1).
5. Marcar `defer` no JS do admin onde possível; carregar Tom Select/Chart só nas páginas que o usam.
6. Purgar CSS não usado (PurgeCSS/`@tailwind` não aplicável) apenas após medir cobertura (Chrome Coverage): `bootstrap.min.css` 195 kB completo é o maior candidato [E: -50-70% do que a página usa].

**Benefício [E]**: site público -100-250 kB de CSS/fontes e menos pedidos externos; backoffice: HTML ~40-60% menor e navegações com CSS/JS em cache do browser. **Riscos**: remover FA sem inventariar quebra ícones; mudar CSP (`docs/seguranca-csp.md`) ao alterar origens; cache de 1 ano nos assets (ver 3.5). **Medir**: Lighthouse (bytes, render-blocking, LCP/INP/CLS), WebPageTest, Chrome Coverage (M3). **Rollback**: reverter commit; deploy do `public/` é manual (cópia separada): **lembrar de copiar os ficheiros de `public/`** (memória de deploy). **Esforço**: M.

### 3.4 Responsividade, acessibilidade, estados de loading/erro

Observações (não aprofundadas, validar manualmente):
- Imagens sem `width/height` (CLS) e `alt` genérico `Imagem {marca} {modelo}` correto mas repetitivo.
- Formulários públicos têm honeypot e `throttle` (`routes/web.php:53-99`), mas **não se evidenciaram** estados de loading/desativação do botão nem tratamento de erro AJAX uniforme nesta leitura: auditar `resources/views/frontend/forms/*` e os envios AJAX (`filteredVehicles`) com M4 (checklist). Com a fila (3.1) o feedback passa a ser "pedido recebido".
- Bootstrap (grid) + `mobile-bottom-nav` e PWA (`sw.js`, `manifest.json`) indicam preocupação mobile; verificar `theme-color`, contraste do dourado dos emails/UI, foco visível e `aria-label` em botões só com ícone (Lighthouse Accessibility).
- Service worker (`public/sw.js`, 12 kB): rever estratégia de cache (versão do cache, o que é pré-carregado) para não servir assets antigos após deploy manual.

### 3.5 Configuração de produção e observabilidade

**Evidência/riscos**
- `.env.example`: `APP_DEBUG=true`, `LOG_LEVEL=debug`; confirmar em produção `APP_DEBUG=false`, `APP_ENV=production`, `LOG_LEVEL=warning`/`error`. Debug ligado expõe stack traces/variáveis e é mais lento.
- `bootstrap/cache` local não tem `config.php`/`routes-v7.php`: em produção correr no deploy `php artisan config:cache && route:cache && view:cache && event:cache` (**atenção**: `route:cache` falha se houver rotas com closures; confirmar). `storage/framework/views` local 3 MB sem cache de views em deploy. Benefício [E]: 20-60 ms por pedido em hospedagem partilhada, sobretudo com opcache; **medir** (M3/TTFB).
- `laravel.log` 172 MB num só ficheiro (local): usar `LOG_CHANNEL=daily` com `days=14`, ou `stack` → `daily`; evitar logs verbosos por pedido (o canal `permissions` regista cada decisão: 1 MB/dia local, retém 60 dias, razoável; `PERMISSIONS_ENFORCE` desligado).
- Sem observabilidade: não há Telescope/Sentry/Nightwatch visíveis. Proposta mínima: `report()` para um canal de erros (Sentry/Flare/Nightwatch, a decidir pelo Pedro; **envolve serviço externo e dados pessoais: validar RGPD**), `DB::listen` com limiar de 500 ms para log de queries lentas em produção, e um *healthcheck*.
- `.htaccess`: `Expires` de 1 mês por omissão e **1 ano** para css/js/imagens (`public/.htaccess:48-57`). Só `layout.css` tem versão por `filemtime`; `custom.js`, `bootstrap.min.css`, `novo-front.css`, imagens fixas **não** têm versão: uma alteração fica 1 ano no browser dos visitantes. Versionar todos os assets locais (helper `asset_v()`/`mix`) ou reduzir `Expires` dos não versionados. Não foi encontrada compressão (`mod_deflate`/gzip) no `.htaccess` na pesquisa feita: confirmar na resposta HTTP real (`curl -I --compressed`, M3) e ativar `AddOutputFilterByType DEFLATE` se a hospedagem não comprimir já.
- Sessões e cache em `file`: aceitável num só servidor; sob carga o I/O de ficheiros em disco partilhado é lento. Opção: `SESSION_DRIVER=database` + índice `last_activity` (já existe).
- Garantir `php artisan queue:work` por cron antes de passar para `QUEUE_CONNECTION=database`.

**Esforço**: P (configuração e deploy), M (observabilidade).

---

## 4. Plano de medição

Princípio: **medir antes, aplicar uma alteração de cada vez, medir depois, mesmas condições**. Nenhuma ligação à BD de produção sem autorização do Pedro; usar cópia anonimizada local ou BD descartável, e depois (com autorização) uma consulta só de leitura em produção para métricas.

| Passo | Ferramenta | Alvo | Condições |
|---|---|---|---|
| M1 BD estática | `information_schema.tables` (linhas/tamanho), `SHOW INDEX`, `EXPLAIN`/`EXPLAIN ANALYZE` | Queries listadas em 2.2: leads por estado, follow-up por intervalo, kanban de propostas, `listingsQuery` do radar, pesquisa global `LIKE` | MySQL com volume real (cópia anonimizada). Executar cada query 3x, registar o tempo da 2ª e 3ª (cache quente) e o plano (`type`, `rows`, `Extra: Using filesort`). Só **leitura**. Autorização explícita do Pedro se for em produção. |
| M2 Queries por pedido | Laravel Debugbar ou `DB::listen` em local/staging; `php artisan db:monitor` não aplicável | `/gestao` (dashboard), `/gestao/v2/leads`, kanban de leads/propostas/convertidas, `/gestao/mobile`, pesquisa global (`/gestao/search?q=...`), `/viaturas` | Contar queries, tempo total de BD, memória (`memory_get_peak_usage`). Dados com volume realista (seed local, **não** o `.env` remoto). Pedidos autenticados com utilizador admin e angariador. |
| M3 Web | Lighthouse (mobile, 4x CPU slowdown, rede "Slow 4G") e WebPageTest (Lisboa/Frankfurt, 3 execuções, mediana); `curl -I --compressed` | `/`, `/viaturas`, detalhe de viatura, `/importacao`, simulador, notícia; backoffice `/gestao`, `/gestao/mobile` | Métricas: TTFB, LCP, CLS, INP/TBT, bytes por tipo (imagem/CSS/JS/fonte), nº de pedidos e origens. Browser limpo (cache fria) e repetição (cache quente). Mesma ligação e mesmo servidor entre antes/depois. |
| M4 UX/erro | Checklist manual + Lighthouse Accessibility | Formulários públicos e AJAX | Estados loading/erro/sucesso, foco, teclado, leitor de ecrã. |
| M5 Filas | Cronómetro do pedido (`curl -w %{time_total}`) no POST de aceitação/formulário, antes e depois | Aceitação de cotação, importação, newsletter (lote pequeno de teste) | Sem enviar emails reais: `MAIL_MAILER=log`/Mailpit em local. |
| M6 Armazenamento | `du -sh`, `find -size`, nº de ficheiros em `storage/framework/cache` | Cache, logs, imagens | Repetir após 7 dias de tráfego para ver crescimento. |
| M7 Produção contínua | Log de queries lentas (>500 ms), `failed_jobs`, tamanho de logs, Search Console (Core Web Vitals, 28 dias) | Site em produção | Comparar antes/depois de cada alteração com a mesma janela. |

Critérios de aceitação sugeridos (alvos [E], a validar): dashboard < 20 queries e < 400 ms de BD; `/viaturas` < 1,5 MB e LCP < 2,5 s em mobile; POST de aceitação < 500 ms com fila; sem crescimento de `storage/framework/cache` superior a X MB/semana (X definido após M6).

## 5. Ordem de execução recomendada

1. P: Config de produção (3.5), `Expires`/versionamento (3.5), logs `daily` (3.5), `select('id','name')` nas listas (2.4).
2. P-M: Chave de cache da listagem + `with` de capa (2.8, 2.3); otimização das fotos V3 + backfill (3.2).
3. M: Dashboard (2.1) e índices validados por M1 (2.2).
4. M-G: Fila de emails/PDF e newsletter em lote (3.1).
5. M: Consolidação de CSS/JS/ícones (3.3); retenção de tabelas (2.7), após decisão sobre RGPD.
6. G, só se M2 mostrar necessidade: ranking do Radar em SQL (2.5).

## 6. Pontos por confirmar (não verificados nesta leitura)

- Valores reais do `.env` de produção (driver de cache/sessão/fila, `APP_DEBUG`, canal de logs).
- Volumes reais das tabelas e nomes reais dos índices/FKs na BD (o código mostra migrations, não o estado aplicado).
- Se `radar_price_history` já evita linhas duplicadas no scraper (serviço não analisado em detalhe).
- Pontos de chamada de `GeocodingService` e se há ações do backoffice que disparam scrape/OpenAI de forma síncrona.
- Se `route:cache` é compatível (closures em `routes/*.php`) e se o GD de produção suporta WebP.
- `ReportDataService.php:40-43` filtra por período; o `selectRaw ... groupBy('type','category')` mais abaixo (l.~85-95) pertence a outro método e não foi verificado se filtra por período.
