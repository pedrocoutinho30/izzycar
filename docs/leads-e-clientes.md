# Leads e clientes

Estado verificado no código em 2026-10. O que não foi confirmado está marcado com **(por confirmar)**.

## Lead e cliente são o mesmo registo

Ambos vivem em `clients` (`app/Models/Client.php`). `is_lead` (boolean) distingue-os. **Converter** (`convertToClient()`) só faz `is_lead=false` e `converted_at=now()`: o `lead_status` fica congelado no último valor.

A conversão acontece em três sítios: `LeadV2Controller::convert` (manual), `ProposalAcceptanceService::accept` e `ProposalObserver::updated` (cotação passa a `Aprovada`). Os dois últimos escrevem ambos na timeline **(por confirmar se o log "Lead convertido" pode duplicar)**.

- `lead_status`: `nova`, `em_contacto`, `fria`, `perdida`. Só validado nas rotas de leads (sem enum nem constraint na BD). O kanban agrupa por `lead_status ?? 'nova'`.
- `lead_source`: texto livre. Valores usados: `manual`, `importacao`, `retoma`, `simulador`, `contacto`, `newsletter_footer`, `whatsapp`.
- `client_type`: `Particular` ou `Empresa` (sem regra de valores).
- Dono: `owner_id` (User) e `angariador_code`. Seguimento: `next_followup_at`, `followup_note`.
- Sem SoftDeletes: `destroy` apaga de vez.
- Observer `ClientObserver` regista criação, edição e eliminação em `AuditLog`.
- Hook `saving`: recalcula `email_normalized` e `phone_key` com `ClientMatcher`. **Updates em massa (`DB::table`, `where()->update()`) não os atualizam.**

## Ecrãs e rotas

- **Leads** (`LeadV2Controller`, `admin.v2.leads.*`): lista, kanban, ficha, criar, mudar estado, converter, follow-up, atribuir dono, atividades, eliminar. `show` e `destroy` filtram `is_lead=true` (um cliente dá 404 aí). Leads `perdida` ficam no fim da lista.
- **Clientes** (`ClientV2Controller`, `admin.v2.clients.*`): lista (`is_lead=false`), ficha, criar, editar. `show/edit/update/destroy` **abrem também leads**. `update` **não verifica duplicados**.
- **Pré-leads** (`PreLeadController`, `admin.v2.pre-leads.*`): mensagens de WhatsApp (`Api/WhatsAppWebhookController`, só texto, ignora números já existentes) que esperam aprovação. `approve` cria lead (`lead_source=whatsapp`) ou, se o telefone já existe, só regista a atividade; em ambos os casos o pré-lead é apagado. `reject` apaga.
- **Timeline**: `LeadActivity` (tipos `system`, `note`, `call`, `email`, `whatsapp`, `facebook`, `meeting`); `LeadActivity::log()` cria eventos de sistema. Continua a ser usada depois de a lead virar cliente.
- `CarCandidate` (carros candidatos do cliente): modelo existe; **(por confirmar onde se gere)**.

## Entradas que criam leads

Site (`ImportController::submitFormImport`, `TradeInController`, `CostSimulatorController`, `ContactController`, rodapé da newsletter), WhatsApp (pré-leads), backoffice e angariadores (`AngariadorController`, que não pode criar se o email ou telefone já existe). Todas usam `ClientMatcher`, **exceto** o `ClientController` legado (`Client::create`).

O formulário de importação do site só aceita telemóveis portugueses (`9[1236]…`, grava 9 dígitos) e atribui o angariador pelo `referral_code`.

## Duplicados e fusão

- `ClientMatcher` (`app/Services/ClientMatcher.php`) é a regra única: **mesmo email OU mesmo telefone**; o nome nunca conta. Email normalizado (minúsculas); `phone_key` = últimos 9 dígitos (menos de 9 não identifica ninguém). `find()` devolve o registo mais antigo e `conflict` quando email e telefone apontam para registos diferentes.
- `complete()` só preenche `name`, `email`, `phone` vazios e os consentimentos só passam a `true`. `logMatch()` avisa na timeline se o nome difere.
- `ContactNormalizer` **não é para clientes**: só para vendedores (`Seller*`).
- Marcas do vendedor (pivot `brand_seller`): editáveis no formulário; o trait `AssociatesSellerBrand` (em `ImportOpportunity` e `V3Vehicle`) junta a marca ao vendedor ao guardar com `seller_id` (nunca remove; marca fora do catálogo ignora-se). Updates em massa não passam pelo hook.
- Comando `clients:duplicates` (só leitura): agrupa por email e telefone, sugere qual manter (prefere cliente a lead).
- Comando `clients:merge {keep} {remove*} {--apply}`: simula por omissão. `ClientMergeService` descobre por reflexão todas as tabelas com coluna `client_id` e redireciona-as; preenche só campos vazios; consentimentos em OR; continua lead só se todos eram leads; apaga o duplicado com `deleteQuietly`; não funde `lead_status`, `lead_source` nem follow-up; **sem desfazer**. Uma tabela nova com `client_id` entra automaticamente na fusão.

## Pedidos de formulário (`FormProposal`)

Pedido de importação, com `origin` `site` ou `manual`.
- Site: cria ou completa o cliente (`lead_source=importacao`), cria o pedido com `status=novo`, envia o mail de confirmação (e documentos de financiamento) e uma notificação push.
- Manual (`FormProposalV2Controller::store`, `admin.v2.form-proposals.store`): exige `client_id`; nasce `em_analise`; marca e modelo validados contra `VehicleCatalog`.
- Estados (strings): `novo`, `em_analise`, `rejeitado`, `arquivado` (manuais) e `convertido` (automático, definido em `ProposalAcceptanceService::accept`). O cartão "Convertidos" conta `proposal_id`, não o estado.
- Eliminar o pedido apaga as oportunidades (FK em cascata) e as fotos à parte.

## Oportunidades (`ImportOpportunity`)

Carro ou anúncio em análise, sempre dentro de um pedido. Rotas aninhadas `admin.v2.form-proposals.opportunities.*`.
- `OpportunityStatus`: `por_contactar`, `contactado`, `aguardar_resposta`, `em_negociacao`, `em_analise`, `selecionado`, `rejeitado`. **Não há máquina de estados**: qualquer valor do enum é aceite.
- Transições automáticas: primeiro contacto "outreach" (`logContact`) passa `por_contactar`→`contactado`; `linkProposal` (ao criar cotação a partir dela) passa a `selecionado`; cotação `Reprovada` passa as ligadas a `rejeitado`.
- `contact_status`: `nao_contactado`, `contactado`, `respondeu`, `negociacao_iniciada`.
- Checklists (`OpportunityChecklist` com `applies_to_fuels`; itens e entradas `pending|confirmed|not_applicable`): filtradas pelo combustível da oportunidade (`VehicleFuel`); o progresso não bloqueia nada.
- Dados do anúncio: `listing_url`, `offer_number` (número de oferta, texto até 100 caracteres, migration `2026_10_14_100000`), `vin`, `country`, foto.
- Uma cotação por oportunidade (`createFromOpportunity` redireciona se já existe).

## Armadilhas

1. Rotas diferentes tratam leads e clientes de forma diferente (404 vs abrir): confirmar `is_lead` antes de assumir.
2. `lead_status` e `lead_source` sem restrição: quem escreve diretamente (site, WhatsApp, angariadores) pode gravar qualquer coisa.
3. Duplicados por email/telefone podem juntar pessoas diferentes (famílias): o merge é manual.
4. Telefones estrangeiros não passam no formulário do site.
5. O `ClientController` legado e `ClientV2Controller::update` não verificam duplicados.
