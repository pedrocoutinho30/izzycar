# Plano de ação

Baseado em [problemas.md](problemas.md), [otimizacoes.md](otimizacoes.md), [limpeza.md](limpeza.md), [arquitetura.md](arquitetura.md) (propostas P1–P10) e [base-de-dados.md](base-de-dados.md). Esforços são estimativas: S ≤ 0,5 dia, M 1–2 dias, L 3–5 dias, XL > 1 semana.

Regras transversais (do `CLAUDE.md`): nunca correr `migrate`, seeders ou escrita via `tinker` contra a BD remota sem autorização do Pedro; toda a migration é validada em sqlite/cópia e entregue com o aviso "falta correr `php artisan migrate` em produção"; cada rota nova fica coberta em `config/permissions.php`.

## Estado final (revisão de 2026-10-31)

Quase tudo o que o código permitia corrigir sem decisões nem dados de produção está feito. Commits: `00eb765` segurança (webhook, SSRF, uploads, filemanager, admins), `73f68de` site público, `8da6d89` dashboard, `ee9af9b` IVA/venda/referência, `9616cc9` clientes/observer/importação, `7e991a7` agendador, `73df400` symfony/mime e `users/`, `86550ca` CSV/newsletter/HTML, `5d2a3be` desempenho, `1e52c53` limpeza, `3bb96ac` financeiro e integrações, `68dbbe3` segurança ronda 3, `058e135` funil, `a33b12b` remoção do código V1, `1bc16d9` testes de cobertura, `2baf1d3` viaturas, `dd006d8` inspeções. Suite: **1052 testes passam, 1 falha conhecida** (`ExampleTest`); eram 764 na auditoria.

| Tarefa | Estado |
|---|---|
| 1.1 `users/*.yaml` | Parcial (fora do índice e no `.gitignore`; falta trocar a password e decidir sobre o histórico) |
| 1.2 Ativar permissões | Aberto |
| 1.3 Uploads/ficheiros/admins | Parcial (`00eb765`, `68dbbe3`; faltam SVG em `NewsletterManagementController` e `PageController`, `authorizeResource` no filemanager; ficheiros privados sem fallback e com habilidade por tipo, falta o `--apply` em produção) |
| 1.4 SSRF e mass assignment | Parcial (SEG-004 e SEG-009 corrigidos; SEG-005 parcial) |
| 1.5 Webhook e dependência | Corrigido (`00eb765`, `73df400`, `dd006d8`) |
| 1.6 Integridade de eliminação | Aberto |
| 2.1 Email repetido | Corrigido (`9616cc9`) |
| 2.2 IVA despesas e saldos | Corrigido no código (`ee9af9b`, `3bb96ac`); falta reconciliar históricos e decidir o saldo de abertura |
| 2.3 Venda e cotação sem transação | Parcial (transação e `vat_type` em `ee9af9b`; cotação em `9616cc9`; apagar venda pela listagem em `2baf1d3`; falta índice único e o `NOT NULL` de `client_id`/`payment_method`) |
| 2.4 Referência de viatura | Corrigido (`ee9af9b`) |
| 2.5 Emails no observer | Corrigido sem fila (`9616cc9`) |
| 2.6 Lembretes e agendador | Parcial (`7e991a7`; falta `APP_TIMEZONE` e `onOneServer`) |
| 2.7 Rotas públicas mortas | Corrigido (`73f68de`) |
| 2.8 Cotação cancelada e transições | Parcial (`3bb96ac`; faltam transições e ENUMs) |
| 2.9 Dashboard `whereMonth` sem ano | Parcial (dashboard em `8da6d89`; faltam `ProposalV2Controller:151` e `ClientV2Controller:44`) |
| 2.10 Newsletter | Corrigido (`86550ca`) |
| 2.11 XSS `RichText`, JSON-LD e tabela do ISV | Corrigido (`86550ca`, `73f68de`, `68dbbe3`); a CSP fica em 5 |
| 2.12 Retomas e consignação só por email | Corrigido (`058e135`: pedido gravado antes do email, consignação cria ou reaproveita a lead; retoma com a viatura na atividade) |
| 2.13 Vendas V3 no menu | Corrigido (`058e135`: `sales.create` redireciona para as viaturas em stock, `edit` de vendas V3 para o separador Venda; `2baf1d3` apagar e pesquisar) |
| 2.14 Restantes LOG/SEG baixos | Parcial (SEG-012 corrigido em `68dbbe3`; SEG-017 parcial; ver `problemas.md`) |
| 2.15 Modelo 9 duplicado | Corrigido (`058e135`: `LegalizationTaskService::ensureModelo9Task`, idempotente, também no separador da viatura) |
| 2.16 Rascunhos e viaturas offline por URL | Corrigido (`68dbbe3`: 404) |
| 2.17 Bugs dos testes de cobertura | Corrigidos 8 (`2baf1d3`, `dd006d8`); abertos: `NOT NULL` vs validação em `suppliers`, `testimonials` e `sales`, auto-atribuição de tarefas (ver `problemas.md`) |
| 3.1 Fila real | Aberto |
| 3.2 Fotos de viaturas | Parcial (upload otimizado em `5d2a3be`; faltam backfill, variantes de cartão, `srcset`) |
| 3.3 Cache listagem e dashboard | Parcial (listagem e dashboard em `5d2a3be`; badges do menu calculados uma vez por pedido em `058e135`; falta cache curta do dashboard) |
| 3.4-3.7 | Aberto |
| Fase 4, itens 1 a 3 | Parcial (`1e52c53`) |
| Fase 4, item 4 (V1) | Corrigido (`a33b12b`: 69 ficheiros apagados e 3 reduzidos, 10 465 linhas) |
| Fase 4, itens 5 e 6 | Aberto |

Cada tarefa detalhada abaixo continua com a sua linha **Estado**; o resumo por tipo de bloqueio é a secção seguinte.

## O que falta, por tipo de bloqueio

Esforços: S ≤ 0,5 dia, M 1-2 dias, L 3-5 dias, XL > 1 semana.

### Precisa de decisão do Pedro
| Decisão | Tarefa | Esforço depois de decidir |
|---|---|---|
| Ligar `PERMISSIONS_ENFORCE` e por que fases (analisar o log `would_block` primeiro) | 1.2 (SEG-001) | L |
| Saldo de abertura de 15 757,28 € (`MovementService::INITIAL_BALANCE`): vale antes de 2025? passa para as `settings`? | LOG-008 | S |
| `mail.admin_address`: endereço real (`.pt` ou `.com`) e retirar o `env()` fora de `config/` | LOG-018 | S |
| `whereMonth` sem ano: o pretendido é "este mês" em `ProposalV2Controller.php:151` e `ClientV2Controller.php:44`? | 2.9 | S |
| Simulador de custos público: `+ 300` no `CostSimulatorController` (cálculo) contra `+ 200` no resultado; qual é o valor certo (ou vai para as `settings`)? | funcionalidades.md | S |
| Trocar a password de `users/*.yaml` e reescrever ou não o histórico git | 1.1 (SEG-002) | S a M |
| Ficheiros soltos da raiz (`info-*`, `retomas`, `plataforma vendas`, `please`, `server.php`, `AutoScoutSearch/`): apagar ou mover para `docs/` | Fase 4, item 2 | S |
| CMS V1 de páginas e menus, `ad-searches`/`anuncios` e `scripts/`: manter ou apagar | Fase 4 (G, J) | M |
| Política de transições das convertidas (reativar canceladas, tranches e movimentos) | LOG-007 | M |
| `final_payment` nas Definições (hoje sem efeito) e retenção de `audit_logs` (RGPD) | LOG-015, 3.6 | S |
| `suppliers`, `testimonials`, `sales`: corrigir a validação ou o esquema (depende dos dados abaixo) | bugs dos testes | S cada |
| CAPTCHA nos formulários públicos (opcional, o anti-spam já foi reforçado) | SEG-012 | S |
| Alteração por commitar em `frontend-footer-desktop.blade.php` (o gradiente do `.cta-card` passa de `--accent-color` a `--yellow-color`): commitar ou descartar | n/a | S |

### Precisa de migration (dizer no fim "falta correr `php artisan migrate` em produção")
| Migration | Tarefa | Pré-requisito |
|---|---|---|
| Índice único `sales.v3_vehicle_id` (nome curto) | 2.3 (LOG-004) | Consulta de duplicados em `sales` |
| `testimonials.comment` nullable, esquema de `suppliers` (`type`, `contact_name`, `email`) e de `sales` (`client_id`, `payment_method`) se se optar por mudar o esquema e não a validação | bugs dos testes | `SHOW CREATE TABLE` dessas tabelas |
| FKs em falta, observer de eliminação e tipo de `form_proposals.client_id` | 1.6 | Consulta de órfãos e backup |
| Tabelas `jobs`/`failed_jobs` | 3.1 | Worker em produção |
| Índices candidatos (`clients(next_followup_at)`, etc.) e índice único em `google_review_id` | 3.4, LOG-014 | `EXPLAIN` numa cópia |
| ENUMs de `proposals.status`/`converted_proposals.status` para `string` | 2.8 | `SHOW CREATE TABLE` |

### Precisa de dados ou verificação em produção
| O quê | Para quê |
|---|---|
| `.env`: `PERMISSIONS_ENFORCE`, `APP_TIMEZONE`, `APP_DEBUG`, `WHATSAPP_VERIFY_TOKEN`, `WHATSAPP_APP_SECRET`, `MAIL_BCC_ADDRESS`, drivers de fila, sessão e cache | 1.2, LOG-002, SEG-003, SEG-014, LOG-009 |
| `php artisan files:move-to-private` (simulação) e depois `--apply`, **antes** de publicar o código sem fallback | SEG-015 |
| `SHOW CREATE TABLE` de `suppliers`, `testimonials` e `sales`; consultas de diagnóstico de `base-de-dados.md` (drift, FKs, órfãos, duplicados em `sales`) | bugs dos testes, 1.6, LOG-004, LOG-012 |
| Confirmar WebP no GD do servidor; copiar a pasta `public/` à mão | 3.2 |
| Número de servidores e driver de cache | `onOneServer`, deduplicação dos lembretes |
| Mailer real de produção (`smtp` ou outro) | dependências `symfony/*mailer` |

### Pronto para fazer (sem decisão, migration nem dados de produção)
| Tarefa | Esforço |
|---|---|
| Apagar ou corrigir os 6 `route()` inexistentes que sobram (LOG-016) e acrescentar o teste dos `route('nome')` literais | S |
| Tirar o `dd()` de `AdSearchController.php:421`; recusar `svg` em `NewsletterManagementController` e `PageController` | S |
| Lista de hosts de push conhecidos em `PushSubscriptionController` (SEG-005) | S |
| `Setting::...->first()->value` sem guarda no `CostSimulatorController` (rebenta se faltar uma definição) | S |
| Corrigir `TaskService::create` (`$task->user_id !== $actorId`, comparação estrita com string) e inverter o teste | S |
| Tirar `getMessage()` dos logs de erro de retoma e consignação (SEG-017) | S |
| `destroySale` a guardar o estado anterior da viatura; validar `client_id` e `payment_method` como `required` em `storeSale` se o Pedro optar pela validação | S |
| `try/catch` em `tasks:send-reminders`, `legalizations:send-tracking-emails` e relatórios (LOG-009) | S a M |
| Cobrir o que falta: `store/update/run/destroy` do radar, comparador com chave, geocode dos transportes, crop/restore de fotos, relatórios `reports:*` | M |
| Apagar o código comentado de `SaleV2Controller` (cerca de 200 linhas); `puppeteer` e `tom-select` do `package.json` | S |
| Comando de backfill das fotos (o código; a execução depende de produção) | M |
| CI (GitHub Actions: testes e `composer audit`) | M |

### Notas de deploy
1. `composer install --no-dev` e limpar a cache de bootstrap (`rm bootstrap/cache/*.php`, depois `php artisan config:clear`; só voltar a `config:cache` com o `.env` completo): saíram pacotes (`laravel-snappy`, `browsershot`, `legacy-factories`) e o `bootstrap/cache` antigo pode apontar para providers que já não existem.
2. Ordem importante: **primeiro** `php artisan files:move-to-private --apply` (precedido da simulação sem `--apply`), **depois** publicar o código dos ficheiros privados (sem fallback).
3. Definir `WHATSAPP_VERIFY_TOKEN` (e confirmar `WHATSAPP_APP_SECRET`) no `.env`: sem token o `verify` do webhook recusa sempre.
4. Confirmar o suporte WebP no GD do servidor (upload otimizado de fotos).
5. Copiar à mão os ficheiros de `public/` alterados (a pasta pública do domínio é uma cópia separada).
6. Nenhuma migration foi criada nestas correções: não há `php artisan migrate` pendente por causa delas.

**Antes de começar (pedidos ao Pedro, desbloqueiam várias tarefas):**
1. Valores reais do `.env` de produção (lista acima).
2. Correr as consultas de diagnóstico de `base-de-dados.md` (secção final) numa cópia ou em modo só de leitura, incluindo `SHOW CREATE TABLE` de `suppliers`, `testimonials` e `sales`.
3. Confirmar o que são as pastas de notas na raiz antes de qualquer remoção.

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
- **Estado:** Parcial (`00eb765`, `68dbbe3`). Feito: ficheiros privados sem fallback para o disco público e com habilidade por tipo (`68dbbe3`; falta o `--apply` em produção); filemanager atrás de `restrictAngariador`; `SettingV2Controller` sem SVG, com nome gerado e `mimes`; `NewsArticleController::deleteGalleryImage` e `SocialPostController` só aceitam caminhos da própria galeria; `UserV2Controller::update/destroy` recusam alteração de admins por quem não é admin. Falta: SVG em `NewsletterManagementController`/`PageController`/LFM, `authorizeResource` no filemanager. Esforço restante: S.
- **Prioridade:** P0–P1. **Esforço:** M. **Risco:** baixo. **Dependências:** 1.2 para coerência.
- **Passos:** pôr `/laravel-filemanager` dentro de `restrictAngariador` + permissão; recusar SVG e usar nomes gerados nos uploads públicos; validar tipo/extensão em `SettingV2Controller`; restringir `deleteGalleryImage`/`SocialPostController` a caminhos da própria galeria; impedir `UserV2Controller::update/destroy` sobre admins; remover o fallback para o disco público nos ficheiros privados.
- **Aceitação:** testes de upload com SVG/executável rejeitados; angariador recebe 403 no filemanager.

### 1.4 SSRF e mass assignment em endpoints públicos (SEG-004, SEG-005, SEG-009)
- **Estado:** Parcial. SEG-004 corrigido (`00eb765`), SEG-009 corrigido (`9616cc9`), SEG-005 parcial (`https` e 500 caracteres, falta lista de hosts). Esforço restante: S.
- **Prioridade:** P1. **Esforço:** M. **Risco:** baixo. **Dependências:** nenhuma.
- **Passos:** validar host exato (parse_url + lista) e desativar redirects no importador; validar `endpoint` das push subscriptions (https, hosts conhecidos); substituir `FormProposal::create($request->all())` por `$request->validated()` com FormRequest; verificar o código de angariador.
- **Aceitação:** testes com `https://evil.com/?autoscout24.`; campos `created_by`/`proposal_id` ignorados.

### 1.5 Webhook WhatsApp e atualização de dependência (SEG-003, SEG-010)
- **Estado:** Corrigido. Webhook recusa sem segredo (`00eb765`), `verify()` com token não vazio e `hash_equals` (`dd006d8`), `symfony/mime` 7.4.22 (`73df400`), `composer audit` limpo.
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
| 2.11 | XSS: sanitizar `RichText::display` com HTMLPurifier já instalado (SEG-006), JSON-LD sem `addslashes` | Média | M | médio (pode mudar conteúdo) | Purifier com lista branca; `json_encode` | Teste com `<script>` Estado: **Corrigido (`86550ca`, `73f68de`, `68dbbe3`)**. |
| 2.12 | Retomas e consignação só por email (sem registo) | Média | M | baixo | Persistir pedido/lead antes de enviar | Pedido aparece no BO Estado: **Corrigido (`058e135`)**. |
| 2.13 | Vendas V3 escondidas no menu (menu leva ao CRUD V1) | Média | M | médio | Redirecionar menu/botões para o fluxo V3 | Navegação cobre o fluxo Estado: **Corrigido (`058e135`, `2baf1d3`)**. |
| 2.14 | Restantes LOG-011 a LOG-018, SEG-011, 012, 014, 016–018 | Baixa | S cada | baixo | Ver detalhe em `problemas.md` | Por achado Estado: **Parcial** (SEG-012 corrigido em `68dbbe3`). |

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

Detalhe e verificações em [limpeza.md](limpeza.md). Os itens 1 a 3 estão em parte feitos (`1e52c53`; ver `limpeza.md`); o 4 (V1) está feito (`a33b12b`). Ordem proposta, do mais seguro para o menos:
1. **Ficheiros não versionados** (`laravel.log` 179 MB, após rotação; `venv` recriável) — S, risco baixo.
2. **Lixo versionado**: 21 ficheiros `* copy.*`/`.bak` em views, `.DS_Store`, `scripts/soup_debug.html`, `AutoScoutSearch/*.csv` — S, risco baixo; confirmar com o Pedro.
3. **Dependências sem uso** (`laravel-snappy`, `easynav`, `legacy-factories`, `browsershot` após remover o método morto, `puppeteer`) — S–M; correr testes e verificar o deploy.
4. **[FEITO `a33b12b`] Controllers e views V1 sem rota**: 20 controllers, 2 models e 47 views apagados; `ProposalController`, `ClientController` e `ConvertedProposalController` ficaram só com os métodos com rota (ver `limpeza.md`).
5. **Assets públicos sem referência** (~8 MB) — verificar a cópia separada de `public/` no domínio antes.
6. **Legado `Vehicle`/`Expense`** (arquitetura P4/P5) — XL, só em 3 fases e depois da Fase 1.6; nunca apagar migrations nem dados.

Aceitação geral: suite de testes igual ou melhor (hoje 1052 verdes + `ExampleTest` conhecido), `route:list` sem erros, sem `route()` para nomes inexistentes (hoje 6, ver LOG-016).

---

## Fase 5 — Melhorias futuras

- **Consolidar `Expense` e `FinancialMovement`** (P4) com morphMap e fonte única de verdade. XL.
- **Enums PHP e `string` em vez de ENUM MySQL** para estados (P6). L.
- **Money como inteiros/decimal** em `proposals` (hoje FLOAT) e `SaleCalculator` (LOG-013, LOG-015). L.
- **Ecrã de fusão de clientes** (hoje só comando Artisan). M.
- **Mobile:** service worker e push no layout mobile, notificações com URLs mobile, âmbito "próprios" também no desktop. L.
- **CI** (GitHub Actions: testes + `composer audit`), observabilidade (Sentry/log agregado), testes para o que ainda não tem cobertura (radar `store/update/run/destroy`, comparador com chave, geocode dos transportes, crop/restore de fotos, relatórios `reports:*`); inspeções, vendas V3, legalização, financeiro, fornecedores, parceiros, transportes, webhook e sitemap já estão cobertos. L, contínuo.
- **CSP em modo enforce** sem `unsafe-inline` depois de remover scripts inline. L.
- **Novos models/relações** propostos em `arquitetura.md` (P3, P9).

---

## Ordem sugerida (final)

Já feito: 1.5, 2.1, 2.4, 2.5, 2.7, 2.10, 2.11, 2.12, 2.13, 2.15, 2.16, Fase 4 item 4, e a maior parte de 2.2, 1.3, 1.4, 2.3, 2.6, 2.8, 3.2, 3.3.

Por fazer, por esta ordem:
1. Decisões e dados do Pedro (secções "Precisa de decisão do Pedro" e "Precisa de dados ou verificação em produção"): `.env` de produção, `SHOW CREATE TABLE`, saldo de abertura, `admin_address`, simulador 300/200, password do `users/*.yaml` e histórico git.
2. Deploy com a ordem das "Notas de deploy" (`files:move-to-private --apply` antes do código novo; `WHATSAPP_VERIFY_TOKEN`).
3. "Pronto para fazer": é tudo S e sem risco; fazer em lotes pequenos com testes (route() restantes, `dd()`, SVG, hosts de push, `TaskService`, logs, `Setting` do simulador).
4. Depois da resposta às decisões: `APP_TIMEZONE=Europe/Lisbon` e `onOneServer` (restos de 2.6), `whereMonth` (2.9), `suppliers`/`testimonials`/`sales` (validação ou migration), índice único em `sales.v3_vehicle_id` (migration, com consulta de duplicados antes).
5. 1.2 (análise de `would_block`, ligar por fases) e 1.6 (diagnóstico de órfãos, backup, FKs): L cada, risco alto.
6. 3.1 (fila), restante de 3.2 (backfill) e 3.4 (índices, só após `EXPLAIN`).
7. Depois: restante Fase 3, Fase 4 itens 5 e 6, Fase 5.
