# Plano de ação

Baseado em [problemas.md](problemas.md), [otimizacoes.md](otimizacoes.md), [limpeza.md](limpeza.md), [arquitetura.md](arquitetura.md) (propostas P1–P10) e [base-de-dados.md](base-de-dados.md). Nada disto foi implementado. Esforços são estimativas: S ≤ 0,5 dia, M 1–2 dias, L 3–5 dias, XL > 1 semana.

Regras transversais (do `CLAUDE.md`): nunca correr `migrate`, seeders ou escrita via `tinker` contra a BD remota sem autorização do Pedro; toda a migration é validada em sqlite/cópia e entregue com o aviso "falta correr `php artisan migrate` em produção"; cada rota nova fica coberta em `config/permissions.php`.

**Antes de começar (pedidos ao Pedro, desbloqueiam várias tarefas):**
1. Valores reais do `.env` de produção: `PERMISSIONS_ENFORCE`, `APP_DEBUG`, `APP_TIMEZONE`, `WHATSAPP_APP_SECRET`, drivers de `QUEUE`/`SESSION`/`CACHE`.
2. Correr as consultas de diagnóstico de `base-de-dados.md` (secção final) numa cópia ou em modo só de leitura.
3. Confirmar o que são as pastas de notas na raiz (`info-*`, `retomas`, `plataforma vendas`) antes de qualquer remoção.

---

## Fase 1 — Críticos e risco de perda/exposição de dados

### 1.1 Ficheiro `users/*.yaml` com hash de password (SEG-002)
- **Prioridade:** P0. **Esforço:** S. **Risco:** baixo. **Dependências:** nenhuma.
- **Benefício:** elimina exposição de credencial no repositório.
- **Passos:** confirmar se a password é usada noutro sítio e trocá-la; `git rm --cached` do ficheiro e restos de CMS antigo (`please`, `server.php`, após confirmação); acrescentar ao `.gitignore`. O hash fica no histórico git: decidir se se reescreve o histórico.
- **Aceitação:** `git ls-files users` vazio; password trocada.

### 1.2 Ativar a aplicação das permissões (SEG-001)
- **Prioridade:** P0. **Esforço:** L (por fases). **Risco:** alto (pode bloquear utilizadores legítimos). **Dependências:** valor real de `PERMISSIONS_ENFORCE`; lacunas de mapeamento (vender, despesas e legalização só exigem `vehicles.update`).
- **Benefício:** hoje qualquer perfil que não seja angariador acede a todo o `/gestao` (users, roles, exportações, ficheiros privados).
- **Passos:** analisar os `would_block` do log em produção durante 1–2 semanas (`permissions:report`); corrigir o catálogo; ligar `PERMISSIONS_ENFORCE` primeiro para rotas `admin_only`/users/roles/exports; depois o resto; adicionar verificação de dono nos controllers desktop de leads/clientes se for a regra pretendida.
- **Aceitação:** `PermissionCatalogTest` verde; teste por perfil nas áreas sensíveis; log sem `would_block` inesperados.

### 1.3 Proteger administradores e superfícies de upload/ficheiros (SEG-008, SEG-007, SEG-015 e achados da parte 2 de funcionalidades)
- **Prioridade:** P0–P1. **Esforço:** M. **Risco:** baixo. **Dependências:** 1.2 para coerência.
- **Passos:** pôr `/laravel-filemanager` dentro de `restrictAngariador` + permissão; recusar SVG e usar nomes gerados nos uploads públicos; validar tipo/extensão em `SettingV2Controller`; restringir `deleteGalleryImage`/`SocialPostController` a caminhos da própria galeria; impedir `UserV2Controller::update/destroy` sobre admins; remover o fallback para o disco público nos ficheiros privados.
- **Aceitação:** testes de upload com SVG/executável rejeitados; angariador recebe 403 no filemanager.

### 1.4 SSRF e mass assignment em endpoints públicos (SEG-004, SEG-005, SEG-009)
- **Prioridade:** P1. **Esforço:** M. **Risco:** baixo. **Dependências:** nenhuma.
- **Passos:** validar host exato (parse_url + lista) e desativar redirects no importador; validar `endpoint` das push subscriptions (https, hosts conhecidos); substituir `FormProposal::create($request->all())` por `$request->validated()` com FormRequest; verificar o código de angariador.
- **Aceitação:** testes com `https://evil.com/?autoscout24.`; campos `created_by`/`proposal_id` ignorados.

### 1.5 Webhook WhatsApp e atualização de dependência (SEG-003, SEG-010)
- **Prioridade:** P1. **Esforço:** S. **Risco:** baixo.
- **Passos:** recusar o webhook (500/403) se o segredo estiver vazio; `composer update symfony/mime` até ≥ 7.4.22 e correr os testes.
- **Aceitação:** `composer audit` limpo; teste do webhook sem segredo.

### 1.6 Integridade na eliminação de clientes e viaturas (arquitetura P1/P2, achados do funil)
- **Prioridade:** P1. **Esforço:** M–L. **Risco:** alto (dados). **Dependências:** diagnóstico de órfãos em produção.
- **Problema:** eliminar um cliente apaga cotações e vendas por cascata; eliminar uma `V3Vehicle` deixa despesa e movimento órfãos; faltam FKs em `converted_proposals.client_id/proposal_id`, `status_proposal_history`, `cost_simulators.client_id`, `sales.trade_in_vehicle_id`; `form_proposals.client_id` é `string`.
- **Passos:** (1) guarda no controller (não eliminar clientes com cotações/vendas; usar arquivo); (2) observer `deleted` em `V3Vehicle` que trata despesas/movimentos; (3) consulta de órfãos em produção; (4) migration de limpeza + FKs com nomes curtos, só depois da limpeza; (5) migration separada para o tipo de `form_proposals.client_id`.
- **Aceitação:** testes de eliminação; `migrate` validado em sqlite e numa cópia; backup antes de produção.

---

## Fase 2 — Correções funcionais

| ID | Tarefa | Prio | Esforço | Risco | Passos resumidos | Aceitação |
|---|---|---|---|---|---|---|
| 2.1 | Email repetido ("criar mesmo assim") dá 500 no desktop (leads/clientes) | Alta | S | baixo | Reutilizar `ClientService::emailOwner` no desktop | Teste: segundo registo com mesmo email dá mensagem, não 500 |
| 2.2 | IVA das despesas V3 (LOG-003) e totais/saldos de movimentos (LOG-008) | Alta | M | médio (altera valores) | Usar `MovementService`; decidir o tratamento de históricos já gravados | Testes: 100 € a 23 % → 18,70 de IVA |
| 2.3 | Registo de venda sem transação nem unicidade (LOG-004), criação de cotação sem transação (LOG-010) | Alta | M | médio | `DB::transaction`, validar `vat_type` com enum, índice único | Duplo clique não duplica |
| 2.4 | Referência de viatura por `count()+1` (LOG-005) | Média | S | baixo | Gerar com máximo/sequência com lock | Teste: apagar e criar não colide |
| 2.5 | Emails no `ProposalObserver` (LOG-006) | Média | S (M com fila) | baixo | `try/catch` + registo; mais tarde fila (3.1) | Falha SMTP não deixa 500 |
| 2.6 | Lembretes e agendador (LOG-001, 002, 009) | Alta | M | médio | Janela correta, flag de enviado, definir `APP_TIMEZONE=Europe/Lisbon`, `withoutOverlapping`/`onOneServer` | Testes dos comandos; horários conferidos |
| 2.7 | Rotas públicas sem método e link `frontend.contact` (funcionalidades parte 2) | Alta | S | baixo | Implementar/remover rotas `/sobre-nos` etc.; corrigir `legalizations/status.blade.php:129` | Todas as rotas públicas respondem 200 |
| 2.8 | Cotação convertida cancelada e transições de estado (LOG-007); ENUMs de `proposals.status`/`converted_proposals.status` sem `Enviado`/`Cancelada`/`Pagamento do Carro` | Alta | M | alto | Confirmar o esquema real em produção; migration de ENUM→string ou alargar; enum PHP com transições | Testes de transição |
| 2.9 | Dashboard V2 `whereMonth` sem ano | Média | S | baixo | Filtrar por intervalo de datas | Teste com dados de dois anos |
| 2.10 | Newsletter: unsubscribe com token assinado, double opt-in, validação de consentimento no envio (SEG-013) | Média | M | baixo | URL assinada, throttle | Testes |
| 2.11 | XSS: sanitizar `RichText::display` com HTMLPurifier já instalado (SEG-006), JSON-LD sem `addslashes` | Média | M | médio (pode mudar conteúdo) | Purifier com lista branca; `json_encode` | Teste com `<script>` |
| 2.12 | Retomas e consignação só por email (sem registo) | Média | M | baixo | Persistir pedido/lead antes de enviar | Pedido aparece no BO |
| 2.13 | Vendas V3 escondidas no menu (menu leva ao CRUD V1) | Média | M | médio | Redirecionar menu/botões para o fluxo V3 | Navegação cobre o fluxo |
| 2.14 | Restantes LOG-011 a LOG-018, SEG-011, 012, 014, 016–018 | Baixa | S cada | baixo | Ver detalhe em `problemas.md` | Por achado |

---

## Fase 3 — Desempenho

### 3.1 Fila real (database) para emails, PDFs e processamento pesado
- **Prioridade:** Alta. **Esforço:** M. **Risco:** médio. **Dependências:** worker por cron/supervisor em Hostinger; migrations `jobs`/`failed_jobs` (aviso de `migrate`).
- **Passos:** criar `app/Jobs`; mover emails da aceitação, formulários e newsletter; manter `sync` nos testes.
- **Aceitação:** pedidos de aceitação < tempo base medido; falhas vão para `failed_jobs`.

### 3.2 Otimizar fotos de viaturas (otimizacoes.md)
- **Prioridade:** Alta (maior ganho no site público). **Esforço:** M. **Risco:** baixo.
- **Passos:** otimizar no `uploadPhoto`, gerar variante de cartão, comando de backfill (copiar `public/` à mão: ver memória de deploy), confirmar suporte WebP no servidor.
- **Aceitação:** peso da listagem pública medido antes/depois.

### 3.3 Cache da listagem pública e do dashboard
- **Esforço:** M. **Passos:** chave só com filtros permitidos; eager load só da capa; cache curto no dashboard com invalidação. **Aceitação:** nº de queries e TTFB antes/depois.

### 3.4 Índices e consultas (só com query concreta)
- **Esforço:** M. **Risco:** médio. **Passos:** medir com `EXPLAIN` numa cópia; propostas candidatas em `otimizacoes.md` (`clients(next_followup_at)`, `clients(is_lead, lead_status)`, `lead_activities(client_id, created_at)`, `proposals(status, created_at)`); retirar índice redundante em `converted_proposals`. **Aceitação:** `EXPLAIN` antes/depois; nomes de índice < 64 caracteres.

### 3.5 Listas em memória e N+1
- **Esforço:** M. **Passos:** paginar/limitar kanbans, selects de clientes, `RadarController::paginateByRank`, exports com `chunk`/`cursor`. **Aceitação:** memória de pico medida.

### 3.6 Retenção e produção
- **Esforço:** S–M. **Passos:** rotação de `laravel.log` (daily), prune de `audit_logs` (decisão RGPD do Pedro), `radar_price_history` só em mudança de preço, `radar_search_runs`; `config:cache`/`route:cache`/`view:cache` no deploy (confirmar compatibilidade com closures); `APP_DEBUG=false` e `LOG_LEVEL` adequado. **Aceitação:** tamanhos de tabelas estáveis.

### 3.7 Front-end
- **Esforço:** M. **Passos:** reduzir bibliotecas de ícones no site público, SRI nos CDNs, compressão e `Cache-Control` versionado no `.htaccess`, build de produção do `public/js/app.js`. **Aceitação:** Lighthouse/WebPageTest antes/depois (plano de medição M1–M7 em `otimizacoes.md`).

---

## Fase 4 — Limpeza e reorganização

Detalhe e verificações em [limpeza.md](limpeza.md). Ordem proposta, do mais seguro para o menos:
1. **Ficheiros não versionados** (`laravel.log` 179 MB, após rotação; `venv` recriável) — S, risco baixo.
2. **Lixo versionado**: 21 ficheiros `* copy.*`/`.bak` em views, `.DS_Store`, `scripts/soup_debug.html`, `AutoScoutSearch/*.csv` — S, risco baixo; confirmar com o Pedro.
3. **Dependências sem uso** (`laravel-snappy`, `easynav`, `legacy-factories`, `browsershot` após remover o método morto, `puppeteer`) — S–M; correr testes e verificar o deploy.
4. **Controllers e views V1 sem rota** — M, risco médio; apagar em lotes pequenos com `route:list` e testes entre lotes. Não tocar em `ProposalController`, `ClientController` e `ConvertedProposalController` (servem páginas públicas), apenas nos métodos sem rota.
5. **Assets públicos sem referência** (~8 MB) — verificar a cópia separada de `public/` no domínio antes.
6. **Legado `Vehicle`/`Expense`** (arquitetura P4/P5) — XL, só em 3 fases e depois da Fase 1.6; nunca apagar migrations nem dados.

Aceitação geral: suite de testes igual ou melhor (764 verdes + `ExampleTest` conhecido), `route:list` sem erros, sem `route()` para nomes inexistentes.

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

## Ordem sugerida

1. Semana 1: 1.1, 1.5, 2.7, 2.1, 2.6 (rápidos, alto valor) + pedidos de informação ao Pedro.
2. Semanas 2–3: 1.3, 1.4, 2.2, 2.3, 2.4, 2.5 e início de 1.2 (análise dos `would_block`).
3. Semanas 3–5: 1.6 (com diagnóstico e backup), 3.1, 3.2, 2.8.
4. Depois: restante Fase 3, Fase 4 por lotes, Fase 5 conforme prioridades do negócio.
