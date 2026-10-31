# Plano de ação

Baseado em [problemas.md](problemas.md), [otimizacoes.md](otimizacoes.md), [limpeza.md](limpeza.md), [arquitetura.md](arquitetura.md) (propostas P1–P10) e [base-de-dados.md](base-de-dados.md). Esforços são estimativas: S ≤ 0,5 dia, M 1–2 dias, L 3–5 dias, XL > 1 semana.

Regras transversais (do `CLAUDE.md`): nunca correr `migrate`, seeders ou escrita via `tinker` contra a BD remota sem autorização do Pedro; toda a migration é validada em sqlite/cópia e entregue com o aviso "falta correr `php artisan migrate` em produção"; cada rota nova fica coberta em `config/permissions.php`.

## Estado actual (revisão de 2026-10-31)

Parte do plano já foi executada em commits; os restantes agentes estão a fazer mais correções em paralelo (tabela "Em curso"). Cada tarefa abaixo leva uma linha **Estado**. Commits: `00eb765` segurança (webhook, SSRF, uploads, filemanager, admins), `73f68de` site público, `8da6d89` dashboard, `ee9af9b` IVA/venda/referência, `9616cc9` clientes/observer/importação, `7e991a7` agendador, `73df400` symfony/mime e `users/`, `86550ca` CSV/newsletter/HTML, `5d2a3be` desempenho, `1e52c53` limpeza, `3bb96ac` financeiro e integrações. Suite de testes: 872 passam, 1 falha conhecida (`ExampleTest`).

| Tarefa | Estado |
|---|---|
| 1.1 `users/*.yaml` | Parcial (fora do índice e no `.gitignore`; falta trocar a password e decidir sobre o histórico) |
| 1.2 Ativar permissões | Aberto |
| 1.3 Uploads/ficheiros/admins | Parcial (`00eb765`) |
| 1.4 SSRF e mass assignment | Parcial (SEG-004 e SEG-009 corrigidos; SEG-005 parcial) |
| 1.5 Webhook e dependência | Corrigido (`00eb765`, `73df400`) |
| 1.6 Integridade de eliminação | Aberto |
| 2.1 Email repetido | Corrigido (`9616cc9`) |
| 2.2 IVA despesas e saldos | Corrigido no código (`ee9af9b`, `3bb96ac`); falta reconciliar históricos e decidir o saldo de abertura |
| 2.3 Venda e cotação sem transação | Parcial (transação e `vat_type` em `ee9af9b`; cotação em `9616cc9`; falta índice único) |
| 2.4 Referência de viatura | Corrigido (`ee9af9b`) |
| 2.5 Emails no observer | Corrigido sem fila (`9616cc9`) |
| 2.6 Lembretes e agendador | Parcial (`7e991a7`; falta `APP_TIMEZONE` e `onOneServer`) |
| 2.7 Rotas públicas mortas | Corrigido (`73f68de`) |
| 2.8 Cotação cancelada e transições | Parcial (`3bb96ac`; faltam transições e ENUMs) |
| 2.9 Dashboard `whereMonth` sem ano | Parcial (dashboard em `8da6d89`; faltam `ProposalV2Controller:151` e `ClientV2Controller:44`) |
| 2.10 Newsletter | Corrigido (`86550ca`) |
| 2.11 XSS `RichText` e JSON-LD | Parcial (`86550ca`, `73f68de`; falta `$tableIsv`, `PageContentController`, CSP) |
| 2.12 Retomas e consignação por email | Aberto |
| 2.13 Vendas V3 no menu | Em curso |
| 2.14 Restantes LOG/SEG baixos | Parcial (ver `problemas.md`) |
| 3.1 Fila real | Aberto |
| 3.2 Fotos de viaturas | Parcial (upload otimizado em `5d2a3be`; faltam backfill, variantes de cartão, `srcset`) |
| 3.3 Cache listagem e dashboard | Parcial (listagem e dashboard em `5d2a3be`; falta cache curta do dashboard) |
| 3.4-3.7 | Aberto |
| Fase 4, itens 1 a 3 | Parcial (`1e52c53`) |
| Fase 4, itens 4 a 6 | Em curso (limpeza V1) / Aberto |

**Em curso noutros trabalhos (não marcados como feitos):** SEG-012 (anti-spam), SEG-015 (ficheiros privados), SEG-017 (dados pessoais em logs), vazamento de rascunhos de notícias, vendas V3 no menu (2.13), Modelo 9 duplicado, simulador de custos, consignação, queries do layout, limpeza V1 e testes novos. Serão atualizados no fim.

**Antes de começar (pedidos ao Pedro, desbloqueiam várias tarefas):**
1. Valores reais do `.env` de produção: `PERMISSIONS_ENFORCE`, `APP_DEBUG`, `APP_TIMEZONE`, `WHATSAPP_APP_SECRET`, drivers de `QUEUE`/`SESSION`/`CACHE`.
2. Correr as consultas de diagnóstico de `base-de-dados.md` (secção final) numa cópia ou em modo só de leitura.
3. Confirmar o que são as pastas de notas na raiz (`info-*`, `retomas`, `plataforma vendas`) antes de qualquer remoção.

---

## Fase 1 — Críticos e risco de perda/exposição de dados

### 1.1 Ficheiro `users/*.yaml` com hash de password (SEG-002)
- **Estado:** Parcial. `users/*.yaml` saiu do índice git (`00eb765`) e `/users/` está no `.gitignore` (`73df400`). `please`, `server.php`, `retomas`, `plataforma vendas`, `info-*` e `AutoScoutSearch/` continuam rastreados. Falta: trocar a password, decidir sobre reescrever o histórico git (o hash continua lá). Esforço restante: S (a decisão é do Pedro).
- **Prioridade:** P0. **Esforço:** S. **Risco:** baixo. **Dependências:** nenhuma.
- **Benefício:** elimina exposição de credencial no repositório.
- **Passos:** confirmar se a password é usada noutro sítio e trocá-la; `git rm --cached` do ficheiro e restos de CMS antigo (`please`, `server.php`, após confirmação); acrescentar ao `.gitignore`. O hash fica no histórico git: decidir se se reescreve o histórico.
- **Aceitação:** `git ls-files users` vazio; password trocada.

### 1.2 Ativar a aplicação das permissões (SEG-001)
- **Estado:** Aberto. `PERMISSIONS_ENFORCE` inalterado. Esforço restante: L, como antes.
- **Prioridade:** P0. **Esforço:** L (por fases). **Risco:** alto (pode bloquear utilizadores legítimos). **Dependências:** valor real de `PERMISSIONS_ENFORCE`; lacunas de mapeamento (vender, despesas e legalização só exigem `vehicles.update`).
- **Benefício:** hoje qualquer perfil que não seja angariador acede a todo o `/gestao` (users, roles, exportações, ficheiros privados).
- **Passos:** analisar os `would_block` do log em produção durante 1–2 semanas (`permissions:report`); corrigir o catálogo; ligar `PERMISSIONS_ENFORCE` primeiro para rotas `admin_only`/users/roles/exports; depois o resto; adicionar verificação de dono nos controllers desktop de leads/clientes se for a regra pretendida.
- **Aceitação:** `PermissionCatalogTest` verde; teste por perfil nas áreas sensíveis; log sem `would_block` inesperados.

### 1.3 Proteger administradores e superfícies de upload/ficheiros (SEG-008, SEG-007, SEG-015 e achados da parte 2 de funcionalidades)
- **Estado:** Parcial (`00eb765`). Feito: filemanager atrás de `restrictAngariador`; `SettingV2Controller` sem SVG, com nome gerado e `mimes`; `NewsArticleController::deleteGalleryImage` e `SocialPostController` só aceitam caminhos da própria galeria; `UserV2Controller::update/destroy` recusam alteração de admins por quem não é admin. Falta: SVG em `NewsletterManagementController`/`PageController`/LFM, `authorizeResource` no filemanager, fallback do disco público nos ficheiros privados (SEG-015, em curso). Esforço restante: S.
- **Prioridade:** P0–P1. **Esforço:** M. **Risco:** baixo. **Dependências:** 1.2 para coerência.
- **Passos:** pôr `/laravel-filemanager` dentro de `restrictAngariador` + permissão; recusar SVG e usar nomes gerados nos uploads públicos; validar tipo/extensão em `SettingV2Controller`; restringir `deleteGalleryImage`/`SocialPostController` a caminhos da própria galeria; impedir `UserV2Controller::update/destroy` sobre admins; remover o fallback para o disco público nos ficheiros privados.
- **Aceitação:** testes de upload com SVG/executável rejeitados; angariador recebe 403 no filemanager.

### 1.4 SSRF e mass assignment em endpoints públicos (SEG-004, SEG-005, SEG-009)
- **Estado:** Parcial. SEG-004 corrigido (`00eb765`), SEG-009 corrigido (`9616cc9`), SEG-005 parcial (`https` e 500 caracteres, falta lista de hosts). Esforço restante: S.
- **Prioridade:** P1. **Esforço:** M. **Risco:** baixo. **Dependências:** nenhuma.
- **Passos:** validar host exato (parse_url + lista) e desativar redirects no importador; validar `endpoint` das push subscriptions (https, hosts conhecidos); substituir `FormProposal::create($request->all())` por `$request->validated()` com FormRequest; verificar o código de angariador.
- **Aceitação:** testes com `https://evil.com/?autoscout24.`; campos `created_by`/`proposal_id` ignorados.

### 1.5 Webhook WhatsApp e atualização de dependência (SEG-003, SEG-010)
- **Estado:** Corrigido. Webhook recusa sem segredo (`00eb765`), `symfony/mime` 7.4.22 (`73df400`), `composer audit` limpo. Resto menor: `verify()` do webhook com `===`.
- **Prioridade:** P1. **Esforço:** S. **Risco:** baixo.
- **Passos:** recusar o webhook (500/403) se o segredo estiver vazio; `composer update symfony/mime` até ≥ 7.4.22 e correr os testes.
- **Aceitação:** `composer audit` limpo; teste do webhook sem segredo.

### 1.6 Integridade na eliminação de clientes e viaturas (arquitetura P1/P2, achados do funil)
- **Estado:** Aberto. Nada feito. `V3Vehicle` continua sem observer de `deleted`, sem FKs em convertidas.
- **Prioridade:** P1. **Esforço:** M–L. **Risco:** alto (dados). **Dependências:** diagnóstico de órfãos em produção.
- **Problema:** eliminar um cliente apaga cotações e vendas por cascata; eliminar uma `V3Vehicle` deixa despesa e movimento órfãos; faltam FKs em `converted_proposals.client_id/proposal_id`, `status_proposal_history`, `cost_simulators.client_id`, `sales.trade_in_vehicle_id`; `form_proposals.client_id` é `string`.
- **Passos:** (1) guarda no controller (não eliminar clientes com cotações/vendas; usar arquivo); (2) observer `deleted` em `V3Vehicle` que trata despesas/movimentos; (3) consulta de órfãos em produção; (4) migration de limpeza + FKs com nomes curtos, só depois da limpeza; (5) migration separada para o tipo de `form_proposals.client_id`.
- **Aceitação:** testes de eliminação; `migrate` validado em sqlite e numa cópia; backup antes de produção.

---

## Fase 2 — Correções funcionais

| ID | Tarefa | Prio | Esforço | Risco | Passos resumidos | Aceitação (e estado) |
|---|---|---|---|---|---|---|
| 2.1 | Email repetido ("criar mesmo assim") dá 500 no desktop (leads/clientes) | Alta | S | baixo | Reutilizar `ClientService::emailOwner` no desktop | Teste: segundo registo com mesmo email dá mensagem, não 500 Estado: **Corrigido (`9616cc9`)**. |
| 2.2 | IVA das despesas V3 (LOG-003) e totais/saldos de movimentos (LOG-008) | Alta | M | médio (altera valores) | Usar `MovementService`; decidir o tratamento de históricos já gravados | Testes: 100 € a 23 % → 18,70 de IVA Estado: **Corrigido (`ee9af9b`, `3bb96ac`); históricos por reconciliar**. |
| 2.3 | Registo de venda sem transação nem unicidade (LOG-004), criação de cotação sem transação (LOG-010) | Alta | M | médio | `DB::transaction`, validar `vat_type` com enum, índice único | Duplo clique não duplica Estado: **Parcial (transações e `vat_type`; falta índice único)**. |
| 2.4 | Referência de viatura por `count()+1` (LOG-005) | Média | S | baixo | Gerar com máximo/sequência com lock | Teste: apagar e criar não colide Estado: **Corrigido (`ee9af9b`)**. |
| 2.5 | Emails no `ProposalObserver` (LOG-006) | Média | S (M com fila) | baixo | `try/catch` + registo; mais tarde fila (3.1) | Falha SMTP não deixa 500 Estado: **Corrigido sem fila (`9616cc9`)**. |
| 2.6 | Lembretes e agendador (LOG-001, 002, 009) | Alta | M | médio | Janela correta, flag de enviado, definir `APP_TIMEZONE=Europe/Lisbon`, `withoutOverlapping`/`onOneServer` | Testes dos comandos; horários conferidos Estado: **Parcial (`7e991a7`; falta `APP_TIMEZONE`, `onOneServer`)**. |
| 2.7 | Rotas públicas sem método e link `frontend.contact` (funcionalidades parte 2) | Alta | S | baixo | Implementar/remover rotas `/sobre-nos` etc.; corrigir `legalizations/status.blade.php:129` | Todas as rotas públicas respondem 200 Estado: **Corrigido (`73f68de`)**. |
| 2.8 | Cotação convertida cancelada e transições de estado (LOG-007); ENUMs de `proposals.status`/`converted_proposals.status` sem `Enviado`/`Cancelada`/`Pagamento do Carro` | Alta | M | alto | Confirmar o esquema real em produção; migration de ENUM→string ou alargar; enum PHP com transições | Testes de transição Estado: **Parcial (`3bb96ac`)**. |
| 2.9 | Dashboard V2 `whereMonth` sem ano | Média | S | baixo | Filtrar por intervalo de datas | Teste com dados de dois anos Estado: **Parcial (`8da6d89`; faltam `ProposalV2Controller:151`, `ClientV2Controller:44`)**. |
| 2.10 | Newsletter: unsubscribe com token assinado, double opt-in, validação de consentimento no envio (SEG-013) | Média | M | baixo | URL assinada, throttle | Testes Estado: **Corrigido (`86550ca`)**. |
| 2.11 | XSS: sanitizar `RichText::display` com HTMLPurifier já instalado (SEG-006), JSON-LD sem `addslashes` | Média | M | médio (pode mudar conteúdo) | Purifier com lista branca; `json_encode` | Teste com `<script>` Estado: **Parcial (`86550ca`, `73f68de`)**. |
| 2.12 | Retomas e consignação só por email (sem registo) | Média | M | baixo | Persistir pedido/lead antes de enviar | Pedido aparece no BO Estado: **Aberto**. |
| 2.13 | Vendas V3 escondidas no menu (menu leva ao CRUD V1) | Média | M | médio | Redirecionar menu/botões para o fluxo V3 | Navegação cobre o fluxo Estado: **Em curso**. |
| 2.14 | Restantes LOG-011 a LOG-018, SEG-011, 012, 014, 016–018 | Baixa | S cada | baixo | Ver detalhe em `problemas.md` | Por achado Estado: **Parcial**. |

---

## Fase 3 — Desempenho

### 3.1 Fila real (database) para emails, PDFs e processamento pesado
- **Estado:** Aberto. Continua sem `app/Jobs` nem `ShouldQueue`; as correções do observer e do agendador foram feitas sem fila.
- **Prioridade:** Alta. **Esforço:** M. **Risco:** médio. **Dependências:** worker por cron/supervisor em Hostinger; migrations `jobs`/`failed_jobs` (aviso de `migrate`).
- **Passos:** criar `app/Jobs`; mover emails da aceitação, formulários e newsletter; manter `sync` nos testes.
- **Aceitação:** pedidos de aceitação < tempo base medido; falhas vão para `failed_jobs`.

### 3.2 Otimizar fotos de viaturas (otimizacoes.md)
- **Estado:** Parcial. `ImageOptimizerService::storeOptimized` aplicado a `uploadPhoto` (`5d2a3be`): lado maior 1920 px, qualidade 82, WebP se o GD suportar, original se o resultado não for menor. Falta: variante de cartão, comando de backfill para as fotos existentes, `srcset`/`width`/`height`, confirmar WebP no GD de produção. Esforço restante: M. Medição real em produção por fazer.
- **Prioridade:** Alta (maior ganho no site público). **Esforço:** M. **Risco:** baixo.
- **Passos:** otimizar no `uploadPhoto`, gerar variante de cartão, comando de backfill (copiar `public/` à mão: ver memória de deploy), confirmar suporte WebP no servidor.
- **Aceitação:** peso da listagem pública medido antes/depois.

### 3.3 Cache da listagem pública e do dashboard
- **Estado:** Parcial (`5d2a3be`). Feito: chave da listagem só com filtros permitidos, só a foto de capa em cache; dashboard com menos queries. Falta: cache curta do dashboard, índices (3.4). Medições em `otimizacoes.md`.
- **Esforço:** M. **Passos:** chave só com filtros permitidos; eager load só da capa; cache curto no dashboard com invalidação. **Aceitação:** nº de queries e TTFB antes/depois.

### 3.4 Índices e consultas (só com query concreta)
- **Estado:** Aberto.
- **Esforço:** M. **Risco:** médio. **Passos:** medir com `EXPLAIN` numa cópia; propostas candidatas em `otimizacoes.md` (`clients(next_followup_at)`, `clients(is_lead, lead_status)`, `lead_activities(client_id, created_at)`, `proposals(status, created_at)`); retirar índice redundante em `converted_proposals`. **Aceitação:** `EXPLAIN` antes/depois; nomes de índice < 64 caracteres.

### 3.5 Listas em memória e N+1
- **Estado:** Parcial: selects de clientes de `ProposalV2Controller` passaram a `select('id','name'[,'email'])` (`5d2a3be`); falta o resto (kanbans, exports, Radar).
- **Esforço:** M. **Passos:** paginar/limitar kanbans, selects de clientes, `RadarController::paginateByRank`, exports com `chunk`/`cursor`. **Aceitação:** memória de pico medida.

### 3.6 Retenção e produção
- **Estado:** Aberto.
- **Esforço:** S–M. **Passos:** rotação de `laravel.log` (daily), prune de `audit_logs` (decisão RGPD do Pedro), `radar_price_history` só em mudança de preço, `radar_search_runs`; `config:cache`/`route:cache`/`view:cache` no deploy (confirmar compatibilidade com closures); `APP_DEBUG=false` e `LOG_LEVEL` adequado. **Aceitação:** tamanhos de tabelas estáveis.

### 3.7 Front-end
- **Estado:** Aberto.
- **Esforço:** M. **Passos:** reduzir bibliotecas de ícones no site público, SRI nos CDNs, compressão e `Cache-Control` versionado no `.htaccess`, build de produção do `public/js/app.js`. **Aceitação:** Lighthouse/WebPageTest antes/depois (plano de medição M1–M7 em `otimizacoes.md`).

---

## Fase 4 — Limpeza e reorganização

Detalhe e verificações em [limpeza.md](limpeza.md). Os itens 1 a 3 estão em parte feitos (`1e52c53`; ver `limpeza.md`); o 4 (V1) está em curso noutro trabalho. Ordem proposta, do mais seguro para o menos:
1. **Ficheiros não versionados** (`laravel.log` 179 MB, após rotação; `venv` recriável) — S, risco baixo.
2. **Lixo versionado**: 21 ficheiros `* copy.*`/`.bak` em views, `.DS_Store`, `scripts/soup_debug.html`, `AutoScoutSearch/*.csv` — S, risco baixo; confirmar com o Pedro.
3. **Dependências sem uso** (`laravel-snappy`, `easynav`, `legacy-factories`, `browsershot` após remover o método morto, `puppeteer`) — S–M; correr testes e verificar o deploy.
4. **Controllers e views V1 sem rota** — M, risco médio; apagar em lotes pequenos com `route:list` e testes entre lotes. Não tocar em `ProposalController`, `ClientController` e `ConvertedProposalController` (servem páginas públicas), apenas nos métodos sem rota.
5. **Assets públicos sem referência** (~8 MB) — verificar a cópia separada de `public/` no domínio antes.
6. **Legado `Vehicle`/`Expense`** (arquitetura P4/P5) — XL, só em 3 fases e depois da Fase 1.6; nunca apagar migrations nem dados.

Aceitação geral: suite de testes igual ou melhor (hoje 872 verdes + `ExampleTest` conhecido), `route:list` sem erros, sem `route()` para nomes inexistentes.

---

## Fase 5 — Melhorias futuras

- **Consolidar `Expense` e `FinancialMovement`** (P4) com morphMap e fonte única de verdade. XL.
- **Enums PHP e `string` em vez de ENUM MySQL** para estados (P6). L.
- **Money como inteiros/decimal** em `proposals` (hoje FLOAT) e `SaleCalculator` (LOG-013, LOG-015). L.
- **Ecrã de fusão de clientes** (hoje só comando Artisan). M.
- **Mobile:** service worker e push no layout mobile, notificações com URLs mobile, âmbito "próprios" também no desktop. L.
- **CI** (GitHub Actions: testes + `composer audit`), observabilidade (Sentry/log agregado), testes para os módulos sem cobertura (inspeções, vendas V3, `SaleCalculator`, `ServiceTranches`, radar, newsletter, comandos agendados). L, contínuo.
- **CSP em modo enforce** sem `unsafe-inline` depois de remover scripts inline. L.
- **Novos models/relações** propostos em `arquitetura.md` (P3, P9).

---

## Ordem sugerida (atualizada)

Já feito: 1.5, 2.1, 2.4, 2.5, 2.7, 2.10, e a maior parte de 2.2, 1.3, 1.4, 2.3, 2.6, 2.8, 2.11, 3.2, 3.3.

Por fazer, por esta ordem:
1. Decisões e informação do Pedro (secção "Antes de começar" e `README.md`): `.env` de produção (`PERMISSIONS_ENFORCE`, `APP_TIMEZONE`, `APP_DEBUG`, `WHATSAPP_APP_SECRET`, drivers), saldo de abertura, `admin_address`, password do `users/*.yaml` e histórico git.
2. `APP_TIMEZONE=Europe/Lisbon` e `onOneServer` se houver mais de um nó (restos de 2.6, S).
3. Restos pequenos de 2.9 (`ProposalV2Controller:151`, `ClientV2Controller:44`), 2.3 (índice único em `sales.v3_vehicle_id`, `destroySale`) e 1.3/1.4 (SVG restantes, lista de hosts push) (S cada).
4. 1.2 (análise de `would_block`, ligar por fases) e 1.6 (diagnóstico de órfãos, backup, FKs): L cada, risco alto.
5. 3.1 (fila), restante de 3.2 (backfill) e 3.4 (índices, só após `EXPLAIN`), 2.12.
6. Depois: restante Fase 3, Fase 4 por lotes (V1, em curso), Fase 5.
7. No fim, atualizar este plano com o resultado dos trabalhos em curso (SEG-012/015/017, vendas V3 no menu, rascunhos, Modelo 9, simulador, consignação, queries do layout, limpeza V1, testes).
