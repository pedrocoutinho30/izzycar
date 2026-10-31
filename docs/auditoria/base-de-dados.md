# Dicionário da base de dados IzzyCar

> Auditoria só de leitura ao repositório. Gerada a partir das migrations (estrutura final reconstruída) e de pesquisa estática ao código. **Sem acesso à base de dados real.**

## 1. Âmbito, método e limitações

- **Cobertura:** 88 tabelas e 1090 campos (inclui tabelas de framework, spatie e push). Todas as tabelas criadas pelas 178 migrations estão cobertas, com todos os campos.
- **Estrutura:** reconstruída por um parser estático das migrations em `database/migrations` (ordem cronológica, aplicando `dropColumn`, `renameColumn`, `->change()` e blocos `hasColumn`). A tentativa de recriar o esquema com `php artisan migrate` em sqlite descartável não foi executada (comando recusado pelo sistema de permissões); por isso a estrutura **não foi validada contra um sqlite real** e pode divergir em detalhes (por exemplo, tipos de `ALTER` em SQL puro MySQL foram aplicados à mão: `testimonials.rating` e `radar_searches.make`).
- **Não foi ligada nenhuma base de dados.** A BD de produção pode ter colunas/tabelas fora das migrations (já há precedentes: `2026_09_30_135000_ensure_production_only_columns_exist` e `2026_10_01_135500_ensure_commission_columns_on_converted_proposals` acrescentam colunas que só existiam em produção) e dados que mostrem usos que o código não revela. As classificações são, por isso, **evidência estática**, nunca prova de «nunca usado».
- **Pesquisa de utilização:** `app/`, `resources/`, `routes/`, `config/`, `database/seeders`, `scripts/`, `scarperAutoscout/` (scraper Python que escreve directamente na MySQL), `public/js`, `bootstrap/`, `lang/`. Os testes foram excluídos da contagem. Padrões reconhecidos: `$fillable`/`$casts`/`$hidden`/`$appends`, `->campo`, `['campo']`, `'campo' =>`, `name="campo"` (formulários), regras de validação, `where/orderBy/select/pluck`, `DB::raw/selectRaw/whereRaw`, SQL em Python, relações (`belongsTo/hasMany` com ou sem chave explícita) e exports.
- **Limitações do método (heurístico):** (1) nomes genéricos (`name`, `status`, `type`, `notes`, `price`…) partilham-se entre tabelas; para estes a contagem restringe-se a ficheiros que referenciam o model/tabela ou a variáveis com o nome do model, e é aproximada; (2) nomes de coluna construídos dinamicamente (`$request->all()`, `array_keys`, `fill($data)`) e colunas lidas via `SELECT *`/`toArray()`/`toJson()` podem não deixar rasto textual; (3) `L`/`E` são contagens de linhas de código com padrão de leitura/escrita, não de execuções; (4) a coluna «Onde» resume categorias de ficheiros; (5) `.env` local usa `SESSION_DRIVER=file`, `CACHE_DRIVER=file`, `QUEUE_CONNECTION=sync` (só estes valores foram consultados; produção pode diferir).
- **Classificações:** `Utilização confirmada` (leitura e escrita encontradas), `Apenas leitura`, `Apenas escrita`, `Sem referências encontradas` (apenas migrations; **não** significa que nunca é usado), `Potencialmente obsoleto` (tabela/campo legado sem referências activas), `Utilização desconhecida/externa` (framework, drivers ou escritores externos que a pesquisa estática não consegue provar).

### Nota de revisão (2026-10-31): alterações de código posteriores à auditoria

Nenhuma migration foi criada e o esquema não mudou (`git diff` de `database/` desde `00eb765` só apaga um `.DS_Store`); por isso o dicionário de tabelas e campos continua válido e não é preciso correr `php artisan migrate`. Mudou **o que se lê e escreve** em alguns campos, o que pode alterar a classificação do CSV `utilizacao-base-de-dados.csv` (não regenerado):

- `clients.newsletter_consent`: o `subscribe` do rodapé deixou de o ligar (cria/mantém `false` e envia link de confirmação); passa a `true` só em `NewsletterController::confirm` (`86550ca`); `doSend` lê-o para recusar envios sem consentimento.
- `expenses.vat_amount` e `expenses.amount_net`: as despesas criadas no separador da viatura passam a gravar IVA incluído no bruto (`ee9af9b`); os registos já gravados com a fórmula antiga **não** foram corrigidos (ver LOG-003).
- `converted_proposals.proposal_id`: passa a ser posto a `null` quando uma convertida cancelada é substituída por uma nova aceitação da mesma cotação (`3bb96ac`); `ConvertedProposal::status` é lido com as duas grafias "Cancelada"/"Cancelado" normalizadas, sem alterar os dados.
- `form_proposals`: o formulário público de importação deixou de gravar `created_by`, `origin` e `proposal_id` vindos do pedido, e só grava `angariador_code` se existir em `users.referral_code` (`9616cc9`).
- `v3_vehicle_photos.path`: novos uploads ficam em WebP (se o GD suportar) com nome aleatório, redimensionados a 1920 px (`5d2a3be`).
- `testimonials.google_review_id`: gravado por `firstOrCreate`; continua sem índice único (LOG-014).
- `sales.v3_vehicle_id`: continua sem índice único (LOG-004 parcial); a venda é agora transacional.
- Tabelas ligadas a controllers V1 apagados pela limpeza V1 (`a33b12b`) (`ExpenseV2Controller`, `VehicleV2Controller`, `NewsV3Controller`, etc.): as "evidências" do CSV que citam esses ficheiros ficam desatualizadas quando a limpeza for concluída; reclassificar então.
- Os números de `README.md`/secção 2 (88 tabelas, 1090 campos) não mudaram.

## 2. Resumo de cobertura e classificação

| Classificação | Campos |
|---|---|
| Utilização confirmada | 1010 |
| Utilização desconhecida/externa | 35 |
| Potencialmente obsoleto | 21 |
| Apenas escrita | 11 |
| Apenas leitura | 11 |
| Sem referências encontradas | 2 |
| **Total** | **1090** |

### Tabelas

| Tabela | Model | Campos | Distribuição |
|---|---|---|---|
| `ad_listings` | AdListing | 46 | Utilização 27, Potencialmente 19 |
| `ad_searches` | AdSearch | 10 | Utilização 10 |
| `attribute_groups` | AttributeGroup | 5 | Utilização 5 |
| `audit_logs` | AuditLog | 11 | Utilização 10, Apenas 1 |
| `brand_seller` | — | 5 | Utilização 3, Sem 2 |
| `brands` | Brand | 5 | Utilização 5 |
| `cache` | — | 3 | Utilização 3 |
| `cache_locks` | — | 3 | Utilização 3 |
| `car_candidates` | CarCandidate | 11 | Utilização 11 |
| `clients` | Client | 30 | Utilização 30 |
| `consignment_evaluations` | ConsignmentEvaluation | 29 | Utilização 29 |
| `converted_proposal_documents` | ConvertedProposalDocument | 8 | Utilização 7, Apenas 1 |
| `converted_proposals` | ConvertedProposal | 49 | Utilização 49 |
| `cost_simulators` | CostSimulator | 27 | Utilização 27 |
| `expenses` | Expense | 24 | Utilização 23, Apenas 1 |
| `failed_jobs` | — | 7 | Utilização 1, Utilização 6 |
| `financial_movements` | FinancialMovement | 13 | Utilização 12, Apenas 1 |
| `form_proposals` | FormProposal | 38 | Utilização 38 |
| `import_opportunities` | ImportOpportunity | 31 | Utilização 31 |
| `import_opportunity_checklist_entries` | ImportOpportunityChecklistEntry | 7 | Utilização 7 |
| `import_opportunity_contacts` | ImportOpportunityContact | 9 | Utilização 9 |
| `import_simulator` | — | 3 | Utilização 1, Potencialmente 2 |
| `job_batches` | — | 10 | Utilização 1, Utilização 9 |
| `jobs` | — | 7 | Utilização 1, Utilização 6 |
| `lead_activities` | LeadActivity | 10 | Utilização 10 |
| `legalization_documents` | LegalizationDocument | 7 | Utilização 7 |
| `legalizations` | Legalization | 20 | Utilização 20 |
| `menus` | Menu | 8 | Utilização 8 |
| `model_cars` | ModelCar | 5 | Utilização 5 |
| `model_has_permissions` | — | 3 | Utilização 3 |
| `model_has_roles` | — | 3 | Utilização 3 |
| `model_years` | ModelYear | 5 | Utilização 4, Apenas 1 |
| `news_articles` | NewsArticle | 15 | Utilização 15 |
| `newsletter_offers` | NewsletterOffer | 14 | Utilização 14 |
| `newsletter_send_logs` | NewsletterSendLog | 6 | Utilização 6 |
| `newsletters` | Newsletter | 8 | Utilização 8 |
| `opportunity_checklist_items` | OpportunityChecklistItem | 8 | Utilização 7, Apenas 1 |
| `opportunity_checklists` | OpportunityChecklist | 8 | Utilização 5, Apenas 3 |
| `page_contents` | PageContent | 6 | Utilização 6 |
| `page_type_fields` | PageTypeField | 10 | Utilização 10 |
| `page_types` | PageType | 4 | Utilização 4 |
| `pages` | Page | 8 | Utilização 8 |
| `partners` | Partner | 15 | Utilização 15 |
| `password_reset_tokens` | — | 3 | Utilização 3 |
| `permissions` | — | 5 | Utilização 5 |
| `pre_leads` | PreLead | 7 | Utilização 7 |
| `proposal_attribute_values` | ProposalAttributeValue | 6 | Utilização 6 |
| `proposals` | Proposal | 33 | Utilização 33 |
| `push_subscriptions` | — | 9 | Utilização 9 |
| `radar_equipment` | RadarEquipment | 6 | Utilização 5, Apenas 1 |
| `radar_equipment_aliases` | RadarEquipmentAlias | 7 | Utilização 7 |
| `radar_listing_equipment` | — | 2 | Utilização 2 |
| `radar_listings` | RadarListing | 29 | Utilização 29 |
| `radar_price_history` | RadarPriceHistory | 4 | Utilização 4 |
| `radar_search_equipment` | — | 2 | Utilização 2 |
| `radar_search_runs` | RadarSearchRun | 9 | Utilização 7, Apenas 2 |
| `radar_searches` | RadarSearch | 14 | Utilização 14 |
| `recommendation_posts` | RecommendationPost | 18 | Utilização 18 |
| `role_has_permissions` | — | 2 | Utilização 2 |
| `roles` | — | 5 | Utilização 5 |
| `sales` | Sale | 23 | Utilização 20, Apenas 3 |
| `seller_contacts` | SellerContact | 16 | Utilização 16 |
| `seller_domains` | SellerDomain | 5 | Utilização 5 |
| `sellers` | Seller | 10 | Utilização 10 |
| `seo_metas` | SeoMeta | 20 | Utilização 15, Apenas 5 |
| `sessions` | — | 6 | Utilização 1, Utilização 5 |
| `settings` | Setting | 7 | Utilização 7 |
| `status_proposal_history` | StatusProposalHistory | 6 | Utilização 6 |
| `submodel_cars` | SubmodelCar | 6 | Utilização 6 |
| `suppliers` | Supplier | 16 | Utilização 16 |
| `tasks` | Task | 13 | Utilização 13 |
| `testimonials` | Testimonial | 10 | Utilização 10 |
| `transport_quotes` | TransportQuote | 18 | Utilização 18 |
| `users` | User | 21 | Utilização 20, Apenas 1 |
| `v3_vehicle_documents` | V3VehicleDocument | 8 | Utilização 8 |
| `v3_vehicle_photos` | V3VehiclePhoto | 9 | Utilização 9 |
| `v3_vehicles` | V3Vehicle | 39 | Utilização 39 |
| `vehicle_attribute_values` | VehicleAttributeValue | 7 | Utilização 7 |
| `vehicle_attributes` | VehicleAttribute | 12 | Utilização 12 |
| `vehicle_documents` | VehicleDocument | 7 | Utilização 7 |
| `vehicle_images` | VehicleImage | 6 | Utilização 6 |
| `vehicle_inspection_categories` | VehicleInspectionCategory | 9 | Utilização 9 |
| `vehicle_inspection_entries` | VehicleInspectionEntry | 8 | Utilização 8 |
| `vehicle_inspection_items` | VehicleInspectionItem | 10 | Utilização 10 |
| `vehicle_inspection_media` | VehicleInspectionMedia | 10 | Utilização 10 |
| `vehicle_inspection_templates` | VehicleInspectionTemplate | 7 | Utilização 7 |
| `vehicle_inspections` | VehicleInspection | 33 | Utilização 32, Apenas 1 |
| `vehicles` | Vehicle | 33 | Utilização 33 |

## 3. Estrutura atual vs. migrations históricas

Campos adicionados e depois removidos, renomeados ou alterados (a estrutura documentada na secção 5 já reflete o estado final):

| Tabela | Campo | Alteração | Migration |
|---|---|---|---|
| `newsletters` | `offer_1_id`, `offer_2_id`, `offer_3_id` | removidos (FKs para `newsletter_offers`; substituídos por `newsletter_offers.newsletter_id`) | 2026_03_17_102534 |
| `newsletter_offers` | `published_at` | removido | 2026_03_17_140423 |
| `expenses` | `type` | renomeado para `expense_category` (depois alterado para `text NULL`) | 2026_05_15_200000, 2026_10_01_160000 |
| `expenses` | `vat_rate` | enum de texto (`'23%'`...) removido e substituído por `decimal(5,2)` com o mesmo nome (via `vat_rate_decimal`) | 2026_05_15_200000 |
| `vehicle_images` | `image_path` | renomeado para `path` | 2025_12_16_000001 |
| `converted_proposals` | `veiculo_entregue`, `veiculo_entregue_em` | adicionados (2026_08_05_110200) e removidos três migrations depois | 2026_08_05_140000 |
| `transport_quotes` | `origin_city`, `origin_country`, `origin_postal_code` | removidos; consolidados em `origin_location` | 2026_08_27_095751 |
| `import_opportunities` | `seller_name`, `seller_contact` | removidos; substituídos por `seller_id`/`seller_contact_id` | 2026_09_30_100300 |
| `sales` | `vehicle_id` | passou a `NULL` (vendas de viaturas V3 usam `v3_vehicle_id`) | 2026_05_19_220000 |
| `vehicle_attribute_values` | `vehicle_id` | passou a `NULL` (usa `v3_vehicle_id`) | 2026_05_20_000002 |
| `vehicle_inspection_media` | `vehicle_inspection_entry_id` | passou a `NULL` (fotos gerais) | 2026_05_27_000004 |
| `status_proposal_history` | `old_status` | passou a `NULL` | 2026_10_01_135500 |
| `car_candidates` | `link` | `varchar` para `text` | 2026_08_26_090000 |
| `proposals` | `fuel` | de `enum` para `varchar(50)` | 2026_10_06_120000 |
| `testimonials` | `rating` | `tinyint unsigned` para `decimal(3,1)` (SQL MySQL; não corre em sqlite) | 2026_05_09_092633 |
| `radar_searches` | `make` | passou a `NULL` (SQL MySQL) | 2026_09_05_120000 |
| `radar_listings` | `external_id` | `UNIQUE(external_id)` substituído por `UNIQUE(source, external_id)` | 2026_09_03_150000 |
| `push_subscriptions` | `endpoint` | comprimento aumentado e `UNIQUE` removido | 2026_09_07_161232 |

Colunas que existiam só em produção e foram «garantidas» por migration (sinal de **drift** entre BD e migrations): `clients.owner_id`, `users.commission_fixed_value` (2026_09_30_135000), `converted_proposals.owner_id`, `comissao_paga`, `comissao_paga_em` (2026_10_01_135500).

Migrations só MySQL (não correm em sqlite, logo o esquema dos testes diverge do real nestes pontos): `add_consignment_page`, `change_rating_to_decimal_in_testimonials_table`, `create_missing_vehicle_inspection_tables`, `make_radar_searches_make_nullable`. A `create_missing_vehicle_inspection_tables` volta a criar `vehicle_inspection_items` e `vehicle_inspections` (já criadas em 2026_05_27_000001): em produção só faz sentido por a BD ter sido criada sem elas.

Migrations com dados (não só esquema): `sync_vehicle_attribute_aliases_from_lists` (vehicle_attributes), `opportunity_checklists` e `refine_opportunity_checklist_items` (seed de checklists), `assign_importador_to_users_without_role` (roles), `restructure_permissions_catalog` (permissions), `funnel_phase_two/three`, `add_token_to_legalizations`, `add_tracking_code_to_converted_proposals`, `fix_legacy_document_types_in_legalization_documents`.

## 4. Achados principais

1. **Models sem tabela:** `Car` (`cars`), `ProposedCar` (`proposed_cars`, e aponta para `ClientRequest`, classe inexistente), `MenuItem` (`menu_items`, usado em `Menu::items()`) e `ImportSimulator` (infere `import_simulators`; a tabela chama-se `import_simulator`). Nenhuma migration cria `cars`, `proposed_cars` nem `menu_items`. Existem ainda `app/Models/_Menu.php` e `Controllers/_MenuController.php` (prefixo `_`, aparentemente legado).
2. **`import_simulator`** só tem `id` e timestamps e nenhum código a usa (o simulador do site usa `cost_simulators`): candidata a remoção, após confirmar que está vazia.
3. **Tabelas de scaffolding sem driver correspondente no `.env` local:** `sessions`, `cache`, `cache_locks` (drivers `file`), `jobs`, `job_batches`, `failed_jobs` (fila `sync`), e `password_reset_tokens` (não há rotas de reposição de palavra-passe). Em produção os drivers podem ser outros: confirmar com as consultas da secção 6.
4. **Legado V1/V2 ainda ligado por FK:** `vehicles`, `vehicle_images`, `vehicle_documents`, `vehicle_attribute_values.vehicle_id`, `proposal_attribute_values`, `ad_searches`/`ad_listings`. `sales.vehicle_id` tem `ON DELETE CASCADE` para `vehicles` e `tasks.vehicle_id` também: apagar uma viatura legada apaga vendas/tarefas. As classificações «Potencialmente obsoleto» dependem de confirmar que estas tabelas já não recebem dados (secção 6).
5. **Relações sem FK constraint** (integridade só aplicacional): `converted_proposals.client_id` e `.proposal_id` (só índice; `proposal_id` é `UNIQUE`), `cost_simulators.client_id`, `form_proposals.client_id` e `.proposal_id`, `sales.trade_in_vehicle_id`, `status_proposal_history.converted_proposal_id`, `vehicles.supplier_id`.
6. **Tipos incompatíveis:** `form_proposals.client_id` é `VARCHAR(255)` e `form_proposals.proposal_id` é `INT` (os alvos são `BIGINT UNSIGNED`): joins com conversão implícita e sem índice.
7. **`proposals.client_id`:** declarado `foreignId()->constrained()->onDelete('cascade')->nullable()`; o `nullable()` aplica-se à definição da FK, não à coluna, pelo que a coluna é `NOT NULL` (verificar na BD real).
8. **Valores monetários em `FLOAT`** em `proposals` (`value`, `transport_cost`, `isv_cost`, `commission_cost`...): risco de arredondamento; as restantes tabelas usam `DECIMAL`.
9. **Estados em texto livre sem constraint** (confirmar valores distintos na secção 6): `clients.lead_status`, `clients.lead_source`, `form_proposals.status/origin`, `v3_vehicles.status`, `import_opportunities.status/contact_status`, `legalizations`, `vehicle_inspections.status`. Em contrapartida há `ENUM` MySQL (`clients.gender` com valor vazio, `converted_proposals.status`, `vehicles.fuel`, `news_articles.status`...) cuja alteração exige migration.
10. **Campos escritos mas nunca lidos (ou lidos mas nunca escritos):** ver classificação `Apenas escrita` (por exemplo `audit_logs.user_agent`, `sales.has_trade_in/trade_in_*`, `ad_listings` equipamentos) e `Apenas leitura` (`seo_metas.twitter_*` são lidos em `frontend/partials/seo.blade.php` mas nenhum código os escreve: provavelmente sempre `NULL`).
11. **Escritor externo:** o scraper Python (`scarperAutoscout/scraper/db.py`) liga-se diretamente à MySQL e faz `INSERT/UPDATE/SELECT *` em `radar_searches`, `radar_search_runs`, `radar_listings`, `radar_price_history`, `radar_equipment*`, `radar_listing_equipment`. Novas colunas `NOT NULL` sem default nestas tabelas partem os `INSERT` do scraper; `radar_searches.run_requested_at` é lido e limpo pelo Python. Os campos `Apenas escrita` do radar (`last_seen_at`, `finished_at`...) são lidos fora do Laravel só se o scraper os usar (não os lê).
12. **Acesso dinâmico a colunas** (a pesquisa textual não o vê): `ConvertedProposal.php:189-193` (`"{$prefix}_paga_em"`), `Expense.php:328-330` (`$converted->{$valueColumn}`), `ClientMatcher`/`ClientMergeService` (campos de cliente por lista), `AngariadorIdentityDocuments` e `AngariadorContractService` (campos de `users`), `PageContentController` (`update($request->all())` em `page_contents`). Estes foram tratados como utilização, mas outros acessos dinâmicos podem existir.
13. **Dois mecanismos financeiros em paralelo:** `expenses` (movimentos, evoluída de despesas) e `financial_movements` (polimórfica, sincronizada por `MovementService` e observers). Confirmar se `financial_movements` tem dados e qual alimenta os relatórios (secção 6).
14. **Dados pessoais/sensíveis** (RGPD): `users.iban`, `users.nif`, `users.documento_*_path`, `clients.vat_number/identification_number/birth_date`, `suppliers.identification_number`, `sellers`/`seller_contacts`, `consignment_evaluations` (nome, telefone, email, matrícula). Estão em texto simples; os ficheiros vão para storage privado (`docs/ficheiros-privados.md`).

## 5. Dicionário por tabela

Legenda das colunas: `L`/`E` = número de linhas de código com padrão de leitura/escrita encontradas (heurístico, ver secção 1); «Onde» = categorias de ficheiros e flags (`$fillable`, `$casts`, formulário/validação, export, API, comando...). Finalidades marcadas como *(inferido)* deduzem-se do nome/contexto (cerca de 200 campos de domínio ficam apenas com «finalidade não documentada»); as restantes vêm de leitura de models, código e documentação em `docs/`. As finalidades das tabelas são, em parte, inferidas.

### `ad_listings`

- **Finalidade:** Anúncios obtidos pelo scraper Standvirtual legado, com equipamentos. *(inferida do nome/colunas)*
- **Estado:** legado: scraper Standvirtual via comando ads:import.
- **Model:** `AdListing` (app/Models/AdListing.php)
- **PK:** id
- **Índices:** UNIQUE(ad_search_id,external_id)
- **FKs / constraints:** FK(ad_search_id) -> ad_searches.id ON DELETE cascade
- **Relações Eloquent:** search() belongsTo AdSearch::class, 'ad_search_id'
- **Migrations:** criada em `2025_06_03`; 4 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): AdSearchController.php; commands (1): ImportAdListings.php
- **Classificação dos campos:** Potencialmente obsoleto: 19, Utilização confirmada: 27

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 19 | 0 | comando, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `ad_search_id` | bigint unsigned | não | — | UNIQUE(ad_search_id,external_id), FK | Chave estrangeira / referência para ad_search (inferido) *(inferido)* | 2 | 1 | comando, model, $fillable, relação | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `external_id` | varchar(255) | não | — | UNIQUE(ad_search_id,external_id) | ID no site de origem (inferido) *(inferido)* | 10 | 4 | comando, script, view, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `title` | varchar(255) | não | — | — | Título (inferido) *(inferido)* | 1 | 2 | comando, view, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `price` | decimal(10,2) | não | — | — | Preço (inferido) *(inferido)* | 14 | 2 | comando, view, controller, model, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `year` | int | não | — | — | Ano (inferido) *(inferido)* | 19 | 2 | comando, view, controller, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `mileage` | int | sim | — | — | Quilometragem (inferido) *(inferido)* | 11 | 2 | comando, view, controller, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `location` | varchar(255) | sim | — | — | Localização (inferido) *(inferido)* | 0 | 1 | comando, $fillable | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `published_time` | varchar(255) | sim | — | — | Campo de domínio «published_time» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 1 | comando, $fillable | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `url` | text | não | — | — | URL (inferido) *(inferido)* | 5 | 1 | comando, view, controller, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 1 | 0 | controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `active` | tinyint(1) | não | `true` | — | Campo de domínio «active» (finalidade não documentada; inferido pelo nome) *(inferido)* | 184 | 2 | comando, script, view, controller | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `gearbox` | varchar(255) | sim | — | — | Caixa de velocidades (inferido) *(inferido)* | 1 | 1 | comando, view, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `teto_de_abrir` | tinyint(1) | sim | — | — | Campo de domínio «teto_de_abrir» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `teto_panoramico` | tinyint(1) | sim | — | — | Campo de domínio «teto_panoramico» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `camara_marcha_atras` | tinyint(1) | sim | — | — | Campo de domínio «camara_marcha_atras» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `camara_360` | tinyint(1) | sim | — | — | Campo de domínio «camara_360» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `sensor_estacionamento_dianteiro` | tinyint(1) | sim | — | — | Campo de domínio «sensor_estacionamento_dianteiro» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `sensor_estacionamento_traseiro` | tinyint(1) | sim | — | — | Campo de domínio «sensor_estacionamento_traseiro» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `assistente_estacionamento` | tinyint(1) | sim | — | — | Campo de domínio «assistente_estacionamento» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `sistema_estacionamento_autonomo` | tinyint(1) | sim | — | — | Campo de domínio «sistema_estacionamento_autonomo» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `apple_carplay` | tinyint(1) | sim | — | — | Campo de domínio «apple_carplay» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `android_auto` | tinyint(1) | sim | — | — | Campo de domínio «android_auto» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `bluetooth` | tinyint(1) | sim | — | — | Campo de domínio «bluetooth» (finalidade não documentada; inferido pelo nome) *(inferido)* | 6 | 3 | comando, script, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `porta_usb` | tinyint(1) | sim | — | — | Campo de domínio «porta_usb» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `carregador_wireless` | tinyint(1) | sim | — | — | Campo de domínio «carregador_wireless» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `sistema_navegacao` | tinyint(1) | sim | — | — | Campo de domínio «sistema_navegacao» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `estofos_alcantara` | tinyint(1) | sim | — | — | Campo de domínio «estofos_alcantara» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `estofos_pele` | tinyint(1) | sim | — | — | Campo de domínio «estofos_pele» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `estofos_tecido` | tinyint(1) | sim | — | — | Campo de domínio «estofos_tecido» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `banco_condutor_aquecido` | tinyint(1) | sim | — | — | Campo de domínio «banco_condutor_aquecido» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `banco_passageiro_aquecido` | tinyint(1) | sim | — | — | Campo de domínio «banco_passageiro_aquecido» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `banco_condutor_regulacao_eletrica` | tinyint(1) | sim | — | — | Campo de domínio «banco_condutor_regulacao_eletrica» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `bancos_memoria` | tinyint(1) | sim | — | — | Campo de domínio «bancos_memoria» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `fecho_central_sem_chave` | tinyint(1) | sim | — | — | Campo de domínio «fecho_central_sem_chave» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `arranque_sem_chave` | tinyint(1) | sim | — | — | Campo de domínio «arranque_sem_chave» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `cruise_control` | tinyint(1) | sim | — | — | Campo de domínio «cruise_control» (finalidade não documentada; inferido pelo nome) *(inferido)* | 2 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `cruise_control_adaptativo` | tinyint(1) | sim | — | — | Campo de domínio «cruise_control_adaptativo» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `cruise_control_predictivo` | tinyint(1) | sim | — | — | Campo de domínio «cruise_control_predictivo» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `suspensao_desportiva` | tinyint(1) | sim | — | — | Campo de domínio «suspensao_desportiva» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `retrovisores_retrateis` | tinyint(1) | sim | — | — | Campo de domínio «retrovisores_retrateis» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |
| `assistente_angulo_morto` | tinyint(1) | sim | — | — | Campo de domínio «assistente_angulo_morto» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `assistente_mudanca_faixa` | tinyint(1) | sim | — | — | Campo de domínio «assistente_mudanca_faixa» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `controlo_proximidade` | tinyint(1) | sim | — | — | Campo de domínio «controlo_proximidade» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 3 | comando, view, controller, $fillable, $casts | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `conducao_autonoma_basica` | tinyint(1) | sim | — | — | Campo de domínio «conducao_autonoma_basica» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 3 | comando, controller, $fillable, $casts | **Potencialmente obsoleto**; legado: scraper Standvirtual via comando ads:import |

### `ad_searches`

- **Finalidade:** Pesquisas de anúncios (scraper Standvirtual legado). *(inferida do nome/colunas)*
- **Estado:** legado: scraper Standvirtual via comando ads:import.
- **Model:** `AdSearch` (app/Models/AdSearch.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** listings() hasMany AdListing::class
- **Migrations:** criada em `2025_06_03`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): AdSearchController.php; commands (1): ImportAdListings.php; scripts/scraper externo (4): _testScript.py, autoscoutSearch.py, getFromStandVirtual.py, searchAd.py
- **Classificação dos campos:** Utilização confirmada: 10

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 23 | 0 | comando, script, view, controller | **Utilização confirmada**; Eloquent/framework |
| `brand` | varchar(255) | não | — | — | Marca (inferido) *(inferido)* | 4 | 2 | comando, view, controller, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `model` | varchar(255) | não | — | — | Modelo (inferido) *(inferido)* | 22 | 2 | comando, script, view, controller, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `submodel` | varchar(255) | sim | — | — | Campo de domínio «submodel» (finalidade não documentada; inferido pelo nome) *(inferido)* | 10 | 6 | formulário/validação, comando, view, controller, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `year_start` | int | sim | — | — | Campo de domínio «year_start» (finalidade não documentada; inferido pelo nome) *(inferido)* | 2 | 1 | comando, view, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `year_end` | int | sim | — | — | Campo de domínio «year_end» (finalidade não documentada; inferido pelo nome) *(inferido)* | 2 | 1 | comando, view, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import |
| `fuel` | varchar(255) | sim | — | — | Combustível (inferido) *(inferido)* | 3 | 1 | comando, script, view, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela); escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 1 | 0 | controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `description` | varchar(255) | não | `true` | — | Descrição (inferido) *(inferido)* | 9 | 1 | comando, script, view, $fillable | **Utilização confirmada**; legado: scraper Standvirtual via comando ads:import; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela); escrito/lido por scraper Python externo (sem passar pelo Laravel) |

### `attribute_groups`

- **Finalidade:** Grupos de atributos para ordenação/categorização dos atributos. *(inferida do nome/colunas)*
- **Model:** `AttributeGroup` (app/Models/AttributeGroup.php)
- **PK:** id
- **Índices:** UNIQUE(name)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** attributes() hasMany VehicleAttribute::class, 'attribute_group', 'name'
- **Migrations:** criada em `2025_10_01`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (9): AttributeGroupV2Controller.php, ProposalV2Controller.php, V3VehicleController.php, VehicleAttributeV2Controller.php, VehicleV2Controller.php, ExtrasController.php…
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 5; ex.: app/Http/Controllers/AttributeGroupController.php:24, app/Http/Controllers/AttributeGroupController.php:46, app/Http/Controllers/Admin/AttributeGroupV2Controller.php:49, app/Http/Controllers/Admin/AttributeGroupV2Controller.php:70
- **Classificação dos campos:** Utilização confirmada: 5

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 85 | 2 | API, view, controller | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | UNIQUE(name) | Nome (inferido) *(inferido)* | 58 | 13 | formulário/validação, API, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `order` | int | não | `0` | — | Ordem de apresentação (inferido) *(inferido)* | 40 | 18 | formulário/validação, API, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 14 | 2 | API, view, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 4 | 2 | API, view, controller | **Utilização confirmada**; Eloquent/framework |

### `audit_logs`

- **Finalidade:** Registo de auditoria de ações no backoffice. *(inferida do nome/colunas)*
- **Model:** `AuditLog` (app/Models/AuditLog.php)
- **PK:** id
- **Índices:** INDEX(auditable_type,auditable_id); INDEX(user_id,created_at)
- **FKs / constraints:** FK(user_id) -> users.id ON DELETE set null
- **Relações Eloquent:** user() belongsTo User::class
- **Migrations:** criada em `2026_06_29`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (5): AngariadorAdminController.php, AuditLogController.php, ExportXlsxController.php, RoleV2Controller.php, UserV2Controller.php; services (1): ClientMergeService.php; observers (2): ClientObserver.php, ProposalObserver.php; outros app (1): AppServiceProvider.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: app/Http/Controllers/Admin/AngariadorAdminController.php:179
- **Classificação dos campos:** Apenas escrita: 1, Utilização confirmada: 10

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 27 | 1 | observer, service, controller | **Utilização confirmada**; Eloquent/framework |
| `user_id` | bigint unsigned | sim | — | INDEX(user_id,created_at), FK | Chave estrangeira / referência para user (inferido) *(inferido)* | 3 | 2 | controller, model, $fillable, relação | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `action` | varchar(50) | não | — | — | Campo de domínio «action» (finalidade não documentada; inferido pelo nome) *(inferido)* | 32 | 7 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `auditable_type` | varchar(255) | sim | — | INDEX(auditable_type,auditable_id) | Campo de domínio «auditable_type» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 1 | service, model, $fillable | **Utilização confirmada** |
| `auditable_id` | bigint unsigned | sim | — | INDEX(auditable_type,auditable_id) | Chave estrangeira / referência para auditable (inferido) *(inferido)* | 1 | 2 | service, model, $fillable | **Utilização confirmada** |
| `description` | varchar(255) | não | — | — | Descrição (inferido) *(inferido)* | 2 | 2 | controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `old_values` | json | sim | — | — | Valores anteriores (JSON) | 4 | 1 | view, model, $fillable, $casts | **Utilização confirmada** |
| `new_values` | json | sim | — | — | Valores novos (JSON) | 4 | 1 | view, model, $fillable, $casts | **Utilização confirmada** |
| `ip_address` | varchar(45) | sim | — | — | Campo de domínio «ip_address» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 1 | view, model, $fillable | **Utilização confirmada** |
| `user_agent` | varchar(255) | sim | — | — | Campo de domínio «user_agent» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 1 | model, $fillable | **Apenas escrita** |
| `created_at` | timestamp | não | `CURRENT_TIMESTAMP` | INDEX(user_id,created_at) | Data de criação (Eloquent) | 15 | 5 | observer, service, controller, $casts | **Utilização confirmada**; Eloquent/framework |

### `brand_seller`

- **Finalidade:** Pivot marcas vendidas por cada vendedor. *(inferida do nome/colunas)*
- **Model:** sem model dedicado
- **PK:** id
- **Índices:** UNIQUE(brand_id,seller_id,uq_brand_seller)
- **FKs / constraints:** FK(brand_id) -> brands.id ON DELETE cascade; FK(seller_id) -> sellers.id ON DELETE cascade
- **Relações Eloquent:** —
- **Migrations:** criada em `2026_10_15`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 2; ex.: app/Models/Seller.php:64, app/Models/Brand.php:26
- **Classificação dos campos:** Sem referências encontradas: 2, Utilização confirmada: 3

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 1 | 0 | model | **Utilização confirmada**; Eloquent/framework |
| `brand_id` | bigint unsigned | não | — | UNIQUE(brand_id,seller_id,uq_brand_seller), FK | Chave estrangeira / referência para brand (inferido) *(inferido)* | 1 | 1 | comando | **Utilização confirmada** |
| `seller_id` | bigint unsigned | não | — | UNIQUE(brand_id,seller_id,uq_brand_seller), FK | Chave estrangeira / referência para seller (inferido) *(inferido)* | 10 | 4 | view, service, controller | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Sem referências encontradas** |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Sem referências encontradas** |

### `brands`

- **Finalidade:** Marcas de viaturas (taxonomia; logo; ligada a vendedores). *(inferida do nome/colunas)*
- **Model:** `Brand` (app/Models/Brand.php)
- **PK:** id
- **Índices:** UNIQUE(name)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** sellers() belongsToMany Seller::class, 'brand_seller'; models() hasMany ModelCar::class; modelCars() hasMany ModelCar::class
- **Migrations:** criada em `2025_05_13`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (18): AdSearchController.php, BrandLogoController.php, LegalizationController.php, ProposalV2Controller.php, SellerController.php, TransportQuoteController.php…; services (3): CarmineTaxonomyService.php, ProposalService.php, SellerService.php; commands (2): ImportBrandLogos.php, ImportCarData.php; requests (1): SellerRequest.php; views (29): form.blade.php, index.blade.php, _brand-model-select.blade.php, form.blade.php, _brand-badges.blade.php, _seller-fields.blade.php…; seeders (1): VehicleBrandSeeder.php; routes (3): api.php, frontend.php, web.php; config (1): permissions.php; outros app (2): BrandLogos.php, VehicleCatalog.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 156; ex.: app/Models/Seller.php:61, app/Models/Seller.php:62, app/Models/Seller.php:92, app/Support/VehicleCatalog.php:10
- **Classificação dos campos:** Utilização confirmada: 5

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 280 | 4 | API, comando, seeder, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | UNIQUE(name) | Nome (inferido) *(inferido)* | 286 | 97 | formulário/validação, API, comando, seeder, view, service | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 17 | 2 | API, view, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 5 | 2 | API, view, controller | **Utilização confirmada**; Eloquent/framework |
| `logo_path` | varchar(255) | sim | — | — | Caminho de ficheiro em disco (inferido) *(inferido)* | 15 | 4 | comando, view, controller, model, $fillable | **Utilização confirmada** |

### `cache`

- **Finalidade:** Cache aplicacional (driver database; tabela standard). *(inferida do nome/colunas)*
- **Estado:** tabela de framework; CACHE_DRIVER local = file.
- **Model:** sem model dedicado
- **PK:** key
- **Índices:** apenas PK/FKs
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `0001_01_01`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 107; ex.: app/Models/V3VehiclePhoto.php:15, app/Models/V3Vehicle.php:20, app/Models/Setting.php:11, app/Models/Setting.php:18
- **Classificação dos campos:** Utilização desconhecida/externa: 3

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `key` | varchar(255) | não | — | PK | Chave (inferido) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; CACHE_DRIVER local = file; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `value` | mediumtext | não | — | — | Valor (inferido) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; CACHE_DRIVER local = file; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `expiration` | int | não | — | — | Campo de domínio «expiration» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; CACHE_DRIVER local = file; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |

### `cache_locks`

- **Finalidade:** Bloqueios atómicos da cache (driver database). *(inferida do nome/colunas)*
- **Estado:** tabela de framework; CACHE_DRIVER local = file.
- **Model:** sem model dedicado
- **PK:** key
- **Índices:** apenas PK/FKs
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `0001_01_01`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Classificação dos campos:** Utilização desconhecida/externa: 3

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `key` | varchar(255) | não | — | PK | Chave (inferido) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; CACHE_DRIVER local = file; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `owner` | varchar(255) | não | — | — | Campo de domínio «owner» (finalidade não documentada; inferido pelo nome) *(inferido)* | 32 | 1 | export, comando, observer, view, service, controller | **Utilização desconhecida/externa**; CACHE_DRIVER local = file; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `expiration` | int | não | — | — | Campo de domínio «expiration» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; CACHE_DRIVER local = file; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |

### `car_candidates`

- **Finalidade:** Viaturas candidatas sugeridas a um cliente/lead (kanban). *(inferida do nome/colunas)*
- **Model:** `CarCandidate` (app/Models/CarCandidate.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(client_id) -> clients.id ON DELETE cascade
- **Relações Eloquent:** client() belongsTo Client::class
- **Migrations:** criada em `2026_08_25`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): CarCandidateController.php; views (2): show.blade.php, car-candidates.blade.php
- **Classificação dos campos:** Utilização confirmada: 11

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 27 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `client_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para client (inferido) *(inferido)* | 14 | 0 | view, controller, model, $fillable, relação | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `title` | varchar(255) | não | — | — | Título (inferido) *(inferido)* | 9 | 4 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `link` | text | sim | — | — | Ligação (URL) (inferido) *(inferido)* | 9 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `price` | decimal(10,2) | sim | — | — | Preço (inferido) *(inferido)* | 7 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `description` | text | sim | — | — | Descrição (inferido) *(inferido)* | 7 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `status` | varchar(255) | não | `'primeiro_contacto'` | — | Estado (inferido) *(inferido)* | 11 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `notes` | text | sim | — | — | Notas internas (inferido) *(inferido)* | 7 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `order_position` | int | não | `0` | — | Posição/ordem (inferido) *(inferido)* | 4 | 5 | view, service, controller, model, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 8 | 0 | view, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `clients`

- **Finalidade:** Clientes e leads (mesmo registo, distinguido por is_lead/lead_status): dados pessoais, consentimentos, dono/angariador, seguimento e chaves normalizadas para deteção de duplicados. *(inferida do nome/colunas)*
- **Model:** `Client` (app/Models/Client.php)
- **PK:** id
- **Índices:** UNIQUE(email); INDEX(email_normalized); INDEX(phone_key)
- **FKs / constraints:** FK(owner_id) -> users.id ON DELETE set null
- **Relações Eloquent:** client() belongsTo Client::class; sale() hasMany Sale::class, 'client_id'; vehicles() hasMany V3Vehicle::class, 'client_id'; costSimulators() hasMany CostSimulator::class, 'client_id'; activities() hasMany LeadActivity::class, 'client_id'; latestActivity() hasOne LeadActivity::class, 'client_id'; proposals() hasMany Proposal::class, 'client_id'; carCandidates() hasMany CarCandidate::class, 'client_id'; convertedProposals() hasMany ConvertedProposal::class, 'client_id'; formProposals() hasMany FormProposal::class, 'client_id'; owner() belongsTo User::class, 'owner_id'
- **Migrations:** criada em `2025_02_27`; 9 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (37): AngariadorAdminController.php, AngariadorController.php, CarCandidateController.php, CarComparatorController.php, ClientV2Controller.php, ConvertedProposalV2Controller.php…; services (21): AngariadorMetricsService.php, AutoscoutTaxonomyService.php, CarmineTaxonomyService.php, ClientMatcher.php, ClientMergeService.php, ClientService.php…; commands (3): ClientDuplicates.php, ClientMerge.php, SendFollowupReminder.php; exports (3): ClientExport.php, ExportRegistry.php, LeadExport.php; observers (1): ClientObserver.php; views (47): form.blade.php, index.blade.php, show.blade.php, show.blade.php, show.blade.php, create.blade.php…; seeders (2): ClientsTableSeeder.php, LeadsSeeder.php; scripts/scraper externo (5): autoscout_client.py, carmine_client.py, cli.py, equipment_client.py, standvirtual_client.py; mail/notificações (4): AlternativeQuoteRequestedMail.php, FinancingDocumentsMail.php, ImportFormConfirmationMail.php, NewLeadNotification.php; routes (1): web.php; config (2): permission.php, permissions.php; outros app (4): LeadSource.php, LeadStatus.php, AppServiceProvider.php, ClientMatch.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 283; ex.: app/Enums/LeadStatus.php:6, app/Enums/LeadSource.php:6, app/Exports/ExportRegistry.php:14, app/Exports/ClientExport.php:10
- **Classificação dos campos:** Utilização confirmada: 30

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 605 | 2 | comando, seeder, mail, observer, script, view | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 390 | 50 | formulário/validação, export, comando, seeder, mail, observer | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `vat_number` | varchar(255) | sim | — | — | NIF (inferido) *(inferido)* | 43 | 12 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada** |
| `birth_date` | date | sim | — | — | Data de nascimento (inferido) *(inferido)* | 14 | 9 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada** |
| `gender` | enum(,Homem,Mulher,Outro) | sim | — | — | Género (inferido) *(inferido)* | 18 | 9 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada** |
| `phone` | varchar(255) | sim | — | — | Telefone (inferido) *(inferido)* | 108 | 45 | formulário/validação, export, seeder, script, view, service | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `email` | varchar(255) | sim | — | UNIQUE(email) | Email (inferido) *(inferido)* | 156 | 56 | formulário/validação, export, comando, seeder, observer, view | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `identification_number` | varchar(255) | sim | — | — | Nº do documento de identificação (inferido) *(inferido)* | 21 | 13 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada** |
| `validate_identification_number` | date | sim | — | — | Campo de domínio «validate_identification_number» (finalidade não documentada; inferido pelo nome) *(inferido)* | 13 | 6 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada** |
| `address` | text | sim | — | — | Morada (inferido) *(inferido)* | 30 | 10 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `postal_code` | text | sim | — | — | Código postal (inferido) *(inferido)* | 23 | 10 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `city` | text | sim | — | — | Cidade (inferido) *(inferido)* | 31 | 10 | formulário/validação, export, seeder, script, view, service | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `client_type` | enum(Particular,empresa) | sim | — | — | Particular ou empresa (inferido) *(inferido)* | 39 | 14 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada** |
| `origin` | enum(,Olx,StandVirtual,Facebook,Instagram,Amigo,Outro) | sim | — | — | Origem (inferido) *(inferido)* | 68 | 38 | formulário/validação, export, comando, seeder, view, service | **Utilização confirmada** |
| `observation` | text | sim | — | — | Observações (inferido) *(inferido)* | 24 | 25 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 180 | 3 | export, comando, observer, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 12 | 0 | observer, view, controller | **Utilização confirmada**; Eloquent/framework |
| `data_processing_consent` | tinyint(1) | não | `false` | — | Consentimento de tratamento de dados (RGPD) (inferido) *(inferido)* | 25 | 16 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `newsletter_consent` | tinyint(1) | não | `false` | — | Consentimento de newsletter (inferido) *(inferido)* | 25 | 19 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `is_lead` | tinyint(1) | não | `false` | — | Distingue lead (true) de cliente (false) | 89 | 21 | export, comando, seeder, observer, view, service | **Utilização confirmada** |
| `converted_at` | timestamp | sim | — | — | Data de conversão de lead em cliente (inferido) *(inferido)* | 8 | 3 | export, view, service, controller, model, $fillable | **Utilização confirmada** |
| `lead_source` | varchar(255) | sim | — | — | Origem da lead (simulador, importacao, retoma, manual...) (inferido) *(inferido)* | 28 | 20 | export, comando, seeder, mail, view, service | **Utilização confirmada** |
| `lead_status` | varchar(255) | não | `'nova'` | — | Estado da lead no funil (nova, em_contacto, qualificada, fria, perdida) sem enum/constraint | 51 | 13 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `next_followup_at` | datetime | sim | — | — | Próximo seguimento agendado (inferido) *(inferido)* | 51 | 14 | formulário/validação, export, comando, observer, view, service | **Utilização confirmada** |
| `followup_note` | varchar(255) | sim | — | — | Nota do seguimento (inferido) *(inferido)* | 29 | 13 | formulário/validação, export, observer, view, service, controller | **Utilização confirmada** |
| `language` | varchar(2) | não | `'pt'` | — | Idioma de comunicação (pt/en...) | 12 | 4 | formulário/validação, export, comando, view, service, controller | **Utilização confirmada** |
| `angariador_code` | varchar(255) | sim | — | — | Código do angariador que originou o cliente | 9 | 3 | export, view, service, controller, model, $fillable | **Utilização confirmada** |
| `owner_id` | bigint unsigned | sim | — | FK | Utilizador dono/angariador (coluna que existia só em produção; migration de garantia) | 61 | 15 | formulário/validação, export, comando, view, service, controller | **Utilização confirmada** |
| `email_normalized` | varchar(255) | sim | — | INDEX(email_normalized) | Email normalizado para deteção de duplicados | 5 | 1 | comando, service, model | **Utilização confirmada** |
| `phone_key` | varchar(9) | sim | — | INDEX(phone_key) | Últimos 9 dígitos do telefone, chave de deteção de duplicados | 3 | 1 | comando, service, model | **Utilização confirmada** |

### `consignment_evaluations`

- **Finalidade:** Pedidos de avaliação para consignação submetidos no site. *(inferida do nome/colunas)*
- **Model:** `ConsignmentEvaluation` (app/Models/ConsignmentEvaluation.php)
- **PK:** id
- **Índices:** UNIQUE(reference)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `2026_06_12`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (2): ConsignmentEvaluationController.php, ConsignmentController.php; views (2): index.blade.php, show.blade.php
- **Classificação dos campos:** Utilização confirmada: 29

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 5 | 0 | view | **Utilização confirmada**; Eloquent/framework |
| `reference` | varchar(20) | não | — | UNIQUE(reference) | Referência interna (inferido) *(inferido)* | 6 | 1 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `brand` | varchar(255) | não | — | — | Marca (inferido) *(inferido)* | 9 | 4 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `model` | varchar(255) | não | — | — | Modelo (inferido) *(inferido)* | 9 | 4 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `version` | varchar(255) | sim | — | — | Versão (inferido) *(inferido)* | 5 | 3 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `year` | smallint | não | — | — | Ano (inferido) *(inferido)* | 6 | 3 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `kilometers` | int unsigned | não | — | — | Quilómetros (inferido) *(inferido)* | 5 | 3 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `plate` | varchar(20) | não | — | — | Matrícula (inferido) *(inferido)* | 7 | 4 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `fuel` | varchar(60) | não | — | — | Combustível (inferido) *(inferido)* | 5 | 3 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `gearbox` | varchar(30) | não | — | — | Caixa de velocidades (inferido) *(inferido)* | 4 | 3 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `power` | smallint unsigned | sim | — | — | Potência (inferido) *(inferido)* | 4 | 3 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `displacement` | smallint unsigned | sim | — | — | Campo de domínio «displacement» (finalidade não documentada; inferido pelo nome) *(inferido)* | 5 | 4 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `color` | varchar(60) | sim | — | — | Cor (inferido) *(inferido)* | 15 | 11 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `condition` | varchar(30) | não | — | — | Campo de domínio «condition» (finalidade não documentada; inferido pelo nome) *(inferido)* | 6 | 7 | formulário/validação, script, view, controller, model, $fillable | **Utilização confirmada** |
| `description` | text | sim | — | — | Descrição (inferido) *(inferido)* | 5 | 3 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `has_service_book` | tinyint(1) | não | `false` | — | Indicador booleano: service_book (inferido) *(inferido)* | 4 | 3 | formulário/validação, view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `has_2nd_key` | tinyint(1) | não | `false` | — | Indicador booleano: 2nd_key (inferido) *(inferido)* | 4 | 3 | formulário/validação, view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `has_iuc` | tinyint(1) | não | `false` | — | Indicador booleano: iuc (inferido) *(inferido)* | 4 | 3 | formulário/validação, view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `has_inspection` | tinyint(1) | não | `false` | — | Indicador booleano: inspection (inferido) *(inferido)* | 4 | 3 | formulário/validação, view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `photos` | json | sim | — | — | Campo de domínio «photos» (finalidade não documentada; inferido pelo nome) *(inferido)* | 33 | 1 | export, view, service, controller, $fillable, $casts | **Utilização confirmada** |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 8 | 3 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `phone` | varchar(20) | não | — | — | Telefone (inferido) *(inferido)* | 7 | 3 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `email` | varchar(255) | não | — | — | Email (inferido) *(inferido)* | 6 | 3 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `location` | varchar(100) | sim | — | — | Localização (inferido) *(inferido)* | 5 | 3 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `price_expectation` | int unsigned | sim | — | — | Valor monetário (inferido) *(inferido)* | 6 | 4 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `status` | enum(novo,contactado,avaliado,em_consignacao,vendido,cancelado) | não | `'novo'` | — | Estado (inferido) *(inferido)* | 11 | 7 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `notes` | text | sim | — | — | Notas internas (inferido) *(inferido)* | 3 | 3 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 4 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `converted_proposal_documents`

- **Finalidade:** Documentos gerados/assinados de cotações convertidas (contratos). *(inferida do nome/colunas)*
- **Model:** `ConvertedProposalDocument` (app/Models/ConvertedProposalDocument.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(converted_proposal_id) -> converted_proposals.id ON DELETE cascade
- **Relações Eloquent:** convertedProposal() belongsTo ConvertedProposal::class
- **Migrations:** criada em `2026_09_11`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (2): ConvertedProposalV2Controller.php, ProposalController.php
- **Classificação dos campos:** Apenas escrita: 1, Utilização confirmada: 7

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 25 | 0 | controller, model | **Utilização confirmada**; Eloquent/framework |
| `converted_proposal_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para converted_proposal (inferido) *(inferido)* | 3 | 5 | service, controller, model, $fillable, relação | **Utilização confirmada** |
| `tipo` | enum(gerado,assinado) | não | — | — | Tipo (inferido) *(inferido)* | 0 | 2 | controller, $fillable | **Apenas escrita**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `nome_original` | varchar(255) | não | — | — | Nome original do ficheiro carregado (inferido) *(inferido)* | 5 | 2 | controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `caminho` | varchar(255) | não | — | — | Caminho do ficheiro em disco (inferido) *(inferido)* | 11 | 2 | controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `enviado_em` | timestamp | sim | — | — | Campo de domínio «enviado_em» (finalidade não documentada; inferido pelo nome) *(inferido)* | 2 | 1 | view, controller, $fillable, $casts | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 8 | 0 | controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `converted_proposals`

- **Finalidade:** Cotações aceites (convertidas) a acompanhar ao longo do processo de importação: estado, custos e flags de pagamento, tranches, comissão do angariador, viatura e código de seguimento. *(inferida do nome/colunas)*
- **Model:** `ConvertedProposal` (app/Models/ConvertedProposal.php)
- **PK:** id
- **Índices:** INDEX(client_id); INDEX(proposal_id); INDEX(status); UNIQUE(proposal_id); UNIQUE(tracking_code,cp_tracking_code_unique)
- **FKs / constraints:** FK(owner_id) -> users.id ON DELETE set null; FK(v3_vehicle_id) -> v3_vehicles.id ON DELETE set null
- **Relações Eloquent:** client() belongsTo Client::class; proposal() belongsTo Proposal::class; statusHistories() hasMany StatusProposalHistory::class, 'converted_proposal_id'; owner() belongsTo User::class, 'owner_id'; v3Vehicle() belongsTo V3Vehicle::class, 'v3_vehicle_id'; documents() hasMany ConvertedProposalDocument::class
- **Migrations:** criada em `2025_09_03`; 10 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (11): AngariadorAdminController.php, AngariadorController.php, ConvertedProposalV2Controller.php, DashboardV2Controller.php, ClientController.php, ConvertedProposalController.php…; services (8): AngariadorMetricsService.php, ContractService.php, ConvertedProposalService.php, MobileDashboardService.php, MovementService.php, ProposalAcceptanceService.php…; commands (2): RecalculateConvertedTranches.php, SendOverdueCommissionAlert.php; views (6): show.blade.php, _status-modal.blade.php, index.blade.php, form.blade.php, timeline.blade.php, view-proposal.blade copy.php; routes (1): web.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 4; ex.: app/Http/Controllers/ConvertedProposalController.php:21, app/Http/Controllers/ConvertedProposalController.php:31, app/Http/Controllers/ConvertedProposalController.php:125, app/Http/Controllers/Admin/AngariadorController.php:21
- **Classificação dos campos:** Utilização confirmada: 49

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 311 | 1 | comando, observer, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `status` | enum(Iniciada,Negociação Carro,Transporte,IPO,DAV,ISV,IMT,Matriculação,Entrega,Registo automóvel,Concluido,Cancelado) | não | `'Iniciada'` | INDEX(status) | Estado (inferido) *(inferido)* | 155 | 60 | formulário/validação, observer, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `url` | varchar(255) | sim | — | — | URL (inferido) *(inferido)* | 30 | 15 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `client_id` | bigint unsigned | sim | — | INDEX(client_id) | Chave estrangeira / referência para client (inferido) *(inferido)* | 67 | 24 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; sem FK constraint na BD (integridade só aplicacional); nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `proposal_id` | bigint unsigned | sim | — | INDEX(proposal_id), UNIQUE(proposal_id) | Chave estrangeira / referência para proposal (inferido) *(inferido)* | 32 | 14 | formulário/validação, observer, view, service, controller, model | **Utilização confirmada**; sem FK constraint na BD (integridade só aplicacional) |
| `brand` | varchar(255) | sim | — | — | Marca (inferido) *(inferido)* | 122 | 17 | formulário/validação, comando, observer, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `modelCar` | varchar(255) | sim | — | — | Campo de domínio «modelCar» (finalidade não documentada; inferido pelo nome) *(inferido)* | 33 | 5 | formulário/validação, comando, view, service, controller, model | **Utilização confirmada** |
| `version` | varchar(255) | sim | — | — | Versão (inferido) *(inferido)* | 49 | 15 | formulário/validação, observer, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `year` | varchar(255) | sim | — | — | Ano (inferido) *(inferido)* | 43 | 14 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `km` | int | sim | — | — | Campo de domínio «km» (finalidade não documentada; inferido pelo nome) *(inferido)* | 23 | 10 | formulário/validação, comando, script, view, service, controller | **Utilização confirmada** |
| `matricula_origem` | varchar(255) | sim | — | — | Campo de domínio «matricula_origem» (finalidade não documentada; inferido pelo nome) *(inferido)* | 12 | 6 | formulário/validação, view, service, controller, $fillable | **Utilização confirmada** |
| `matricula_destino` | varchar(255) | sim | — | — | Campo de domínio «matricula_destino» (finalidade não documentada; inferido pelo nome) *(inferido)* | 15 | 5 | formulário/validação, mail, view, service, controller, $fillable | **Utilização confirmada** |
| `custo_inspecao_origem` | decimal(10,2) | sim | — | — | Custo de inspecao_origem (inferido) *(inferido)* | 6 | 5 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `inspecao_origem_pago` | tinyint(1) | não | `false` | — | Indicador de pagamento efetuado (inferido) *(inferido)* | 5 | 4 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `custo_transporte` | decimal(10,2) | sim | — | — | Custo de transporte (inferido) *(inferido)* | 6 | 6 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `transporte_pago` | tinyint(1) | não | `false` | — | Indicador de pagamento efetuado (inferido) *(inferido)* | 5 | 4 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `custo_ipo` | decimal(10,2) | sim | — | — | Custo de ipo (inferido) *(inferido)* | 6 | 6 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `ipo_pago` | tinyint(1) | não | `false` | — | Indicador de pagamento efetuado (inferido) *(inferido)* | 5 | 4 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `isv` | decimal(10,2) | sim | — | — | Campo de domínio «isv» (finalidade não documentada; inferido pelo nome) *(inferido)* | 10 | 9 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `isv_pago` | tinyint(1) | não | `false` | — | Indicador de pagamento efetuado (inferido) *(inferido)* | 5 | 4 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `custo_imt` | decimal(10,2) | sim | — | — | Custo de imt (inferido) *(inferido)* | 6 | 6 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `imt_pago` | tinyint(1) | não | `false` | — | Indicador de pagamento efetuado (inferido) *(inferido)* | 5 | 4 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `custo_matricula` | decimal(10,2) | sim | — | — | Custo de matricula (inferido) *(inferido)* | 5 | 6 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `matricula_pago_impressa` | tinyint(1) | não | `false` | — | Campo de domínio «matricula_pago_impressa» (finalidade não documentada; inferido pelo nome) *(inferido)* | 5 | 4 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `custo_registo_automovel` | decimal(10,2) | sim | — | — | Custo de registo_automovel (inferido) *(inferido)* | 6 | 5 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `registo_pago` | tinyint(1) | não | `false` | — | Indicador de pagamento efetuado (inferido) *(inferido)* | 5 | 4 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `valor_primeira_tranche` | decimal(10,2) | sim | — | — | Valor de primeira_tranche (inferido) *(inferido)* | 8 | 6 | formulário/validação, comando, view, service, controller, model | **Utilização confirmada** |
| `valor_segunda_tranche` | decimal(10,2) | sim | — | — | Valor de segunda_tranche (inferido) *(inferido)* | 8 | 6 | formulário/validação, comando, view, service, controller, model | **Utilização confirmada** |
| `primeira_tranche_pago` | tinyint(1) | não | `false` | — | Indicador de pagamento efetuado (inferido) *(inferido)* | 7 | 4 | formulário/validação, comando, view, controller, model, $fillable | **Utilização confirmada** |
| `segunda_tranche_pago` | tinyint(1) | não | `false` | — | Indicador de pagamento efetuado (inferido) *(inferido)* | 7 | 4 | formulário/validação, comando, view, controller, model, $fillable | **Utilização confirmada** |
| `valor_carro` | decimal(10,2) | sim | — | — | Valor de carro (inferido) *(inferido)* | 16 | 10 | formulário/validação, view, service, controller, $fillable | **Utilização confirmada** |
| `carro_pago` | tinyint(1) | não | `false` | — | Indicador de pagamento efetuado (inferido) *(inferido)* | 5 | 4 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `valor_comissao` | decimal(10,2) | sim | — | — | Valor de comissao (inferido) *(inferido)* | 6 | 6 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `valor_comissao_final` | decimal(10,2) | sim | — | — | Valor de comissao_final (inferido) *(inferido)* | 4 | 5 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `contactos_stand` | text | sim | — | — | Campo de domínio «contactos_stand» (finalidade não documentada; inferido pelo nome) *(inferido)* | 4 | 5 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `observacoes` | text | sim | — | — | Observações (inferido) *(inferido)* | 3 | 6 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `iva_dedutivel` | tinyint(1) | não | `false` | — | Campo de domínio «iva_dedutivel» (finalidade não documentada; inferido pelo nome) *(inferido)* | 3 | 1 | formulário/validação, view | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 119 | 2 | view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 4 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `comprovativo_pagamento` | varchar(255) | sim | — | — | Campo de domínio «comprovativo_pagamento» (finalidade não documentada; inferido pelo nome) *(inferido)* | 11 | 1 | view, controller, $fillable | **Utilização confirmada** |
| `owner_id` | bigint unsigned | sim | — | FK | Utilizador dono/angariador da cotação | 60 | 15 | formulário/validação, export, comando, view, service, controller | **Utilização confirmada** |
| `comissao_paga` | tinyint(1) | não | `false` | — | Indica se a comissão do angariador foi paga | 15 | 2 | comando, view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `comissao_paga_em` | date | sim | — | — | Data de pagamento (inferido) *(inferido)* | 5 | 2 | view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `v3_vehicle_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para v3_vehicle (inferido) *(inferido)* | 53 | 29 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `angariador_commission` | decimal(10,2) | sim | — | — | Instantâneo da comissão do angariador à data da conversão | 4 | 1 | model, $fillable, $casts | **Utilização confirmada** |
| `primeira_tranche_paga_em` | date | sim | — | — | Data de pagamento (inferido) *(inferido)* | 2 | 0 | view, model, $fillable, $casts | **Utilização confirmada**; escrita dinâmica em ConvertedProposal.php:189-193 ("{$prefix}_paga_em"); lida em Expense.php:321 |
| `segunda_tranche_paga_em` | date | sim | — | — | Data de pagamento (inferido) *(inferido)* | 2 | 0 | view, model, $fillable, $casts | **Utilização confirmada**; escrita dinâmica em ConvertedProposal.php:189-193 ("{$prefix}_paga_em") |
| `vin` | varchar(17) | sim | — | — | Número de quadro (VIN) (inferido) *(inferido)* | 5 | 3 | view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `tracking_code` | varchar(32) | sim | — | UNIQUE(tracking_code,cp_tracking_code_unique) | Código público de acompanhamento do processo (único) | 4 | 1 | view, controller, model | **Utilização confirmada** |

### `cost_simulators`

- **Finalidade:** Resultados de simulações de custos de importação feitas no site (com token público e tabela ISV). *(inferida do nome/colunas)*
- **Model:** `CostSimulator` (app/Models/CostSimulator.php)
- **PK:** id
- **Índices:** UNIQUE(token)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** client() belongsTo Client::class
- **Migrations:** criada em `2025_12_19`; 7 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (7): CostSimulatorController.php, DashboardV2Controller.php, LeadV2Controller.php, ClientController.php, CostSimulatorController.php, LeadController.php…; services (2): MobileDashboardService.php, ReportDataService.php; views (1): admin-v2.blade.php; seeders (1): LeadsSeeder.php; mail/notificações (2): CostSimulatorResultMail.php, NewCostSimulatorNotification.php
- **Classificação dos campos:** Utilização confirmada: 27

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 47 | 1 | seeder, mail, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `client_id` | bigint unsigned | não | — | — | Chave estrangeira / referência para client (inferido) *(inferido)* | 26 | 8 | seeder, view, controller, model, $fillable, relação | **Utilização confirmada**; sem FK constraint na BD (integridade só aplicacional); nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `car_value` | decimal(10,2) | não | — | — | Campo de domínio «car_value» (finalidade não documentada; inferido pelo nome) *(inferido)* | 10 | 4 | seeder, view, controller, $fillable | **Utilização confirmada** |
| `commission_cost` | decimal(10,2) | não | — | — | Comissão (inferido) *(inferido)* | 35 | 15 | formulário/validação, seeder, view, service, controller, $fillable | **Utilização confirmada** |
| `inspection_commission_cost` | decimal(10,2) | não | — | — | Comissão de inspeção (inferido) *(inferido)* | 35 | 15 | formulário/validação, seeder, view, service, controller, $fillable | **Utilização confirmada** |
| `transport` | decimal(10,2) | não | — | — | Campo de domínio «transport» (finalidade não documentada; inferido pelo nome) *(inferido)* | 4 | 4 | seeder, view, controller, $fillable | **Utilização confirmada** |
| `ipo_cost` | decimal(10,2) | não | — | — | Custo de inspeção periódica (IPO) (inferido) *(inferido)* | 30 | 15 | formulário/validação, seeder, view, service, controller, $fillable | **Utilização confirmada** |
| `imt_cost` | decimal(10,2) | não | — | — | Custo IMT (inferido) *(inferido)* | 30 | 15 | formulário/validação, seeder, view, service, controller, $fillable | **Utilização confirmada** |
| `registration_cost` | decimal(10,2) | não | — | — | Custo de registo (inferido) *(inferido)* | 30 | 15 | formulário/validação, seeder, view, service, controller, $fillable | **Utilização confirmada** |
| `plates_cost` | decimal(10,2) | não | — | — | Valor monetário (inferido) *(inferido)* | 4 | 4 | seeder, view, controller, $fillable | **Utilização confirmada** |
| `total_cost` | decimal(10,2) | não | — | — | Valor monetário (inferido) *(inferido)* | 18 | 6 | formulário/validação, seeder, view, service, controller, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 71 | 0 | view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 3 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `isv_cost` | decimal(10,2) | não | `0` | — | Custo de ISV (inferido) *(inferido)* | 34 | 15 | formulário/validação, seeder, view, service, controller, $fillable | **Utilização confirmada** |
| `read` | tinyint(1) | não | `false` | — | Campo de domínio «read» (finalidade não documentada; inferido pelo nome) *(inferido)* | 10 | 4 | seeder, script, view, service, controller, $fillable | **Utilização confirmada** |
| `brand` | varchar(255) | sim | — | — | Marca (inferido) *(inferido)* | 7 | 7 | seeder, mail, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `model` | varchar(255) | sim | — | — | Modelo (inferido) *(inferido)* | 7 | 7 | seeder, mail, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `fuel` | varchar(255) | sim | — | — | Combustível (inferido) *(inferido)* | 2 | 7 | seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `year` | varchar(255) | sim | — | — | Ano (inferido) *(inferido)* | 15 | 4 | seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `cc` | varchar(255) | sim | — | — | Campo de domínio «cc» (finalidade não documentada; inferido pelo nome) *(inferido)* | 4 | 3 | seeder, view, controller, $fillable | **Utilização confirmada** |
| `co2` | varchar(255) | sim | — | — | Emissões de CO2 (inferido) *(inferido)* | 38 | 17 | formulário/validação, seeder, view, service, controller, $fillable | **Utilização confirmada** |
| `emissao_particulas` | varchar(255) | sim | — | — | Campo de domínio «emissao_particulas» (finalidade não documentada; inferido pelo nome) *(inferido)* | 16 | 4 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `tipo_veiculo` | varchar(255) | sim | — | — | Campo de domínio «tipo_veiculo» (finalidade não documentada; inferido pelo nome) *(inferido)* | 23 | 4 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `autonomia` | varchar(255) | sim | — | — | Campo de domínio «autonomia» (finalidade não documentada; inferido pelo nome) *(inferido)* | 20 | 5 | formulário/validação, seeder, script, view, controller, $fillable | **Utilização confirmada** |
| `pais_matricula` | varchar(255) | sim | — | — | Campo de domínio «pais_matricula» (finalidade não documentada; inferido pelo nome) *(inferido)* | 13 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada** |
| `token` | varchar(64) | sim | — | UNIQUE(token) | Token (inferido) *(inferido)* | 1 | 4 | seeder, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `isv_table` | longtext | sim | — | — | Campo de domínio «isv_table» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 1 | controller, $fillable | **Utilização confirmada** |

### `expenses`

- **Finalidade:** Movimentos financeiros do backoffice (evoluiu de despesas para movimentos de receita/despesa, rota v2/movements); liga a viatura V3, legalização, cliente e origem (cotação convertida/venda). *(inferida do nome/colunas)*
- **Model:** `Expense` (app/Models/Expense.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(vehicle_id) -> vehicles.id ON DELETE set null; FK(partner_id) -> partners.id ON DELETE set null; FK(client_id) -> clients.id ON DELETE set null; FK(v3_vehicle_id) -> v3_vehicles.id ON DELETE set null; FK(legalization_id) -> legalizations.id ON DELETE set null
- **Relações Eloquent:** vehicle() belongsTo Vehicle::class; v3Vehicle() belongsTo V3Vehicle::class, 'v3_vehicle_id'; client() belongsTo Client::class; partner() belongsTo Partner::class; legalization() belongsTo Legalization::class
- **Migrations:** criada em `2025_02_28`; 6 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (13): DashboardV2Controller.php, ExpenseV2Controller.php, FinancialDashboardController.php, LegalizationController.php, ConvertedProposalController.php, MovementController.php…; services (3): MovementService.php, PrivateFiles.php, SaleCalculator.php; exports (1): MovementExport.php; observers (3): ExpenseObserver.php, SaleObserver.php, VehicleObserver.php; requests (1): ExportXlsxRequest.php; views (20): form.blade.php, index.blade.php, show.blade.php, form.blade.php, index.blade.php, show.blade.php…; routes (1): web.php; config (1): permissions.php; outros app (1): AppServiceProvider.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 110; ex.: app/Models/Legalization.php:167, app/Models/V3Vehicle.php:122, app/Models/V3Vehicle.php:131, app/Models/V3Vehicle.php:132
- **Classificação dos campos:** Apenas leitura: 1, Utilização confirmada: 23

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 229 | 1 | export, observer, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `vehicle_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para vehicle (inferido) *(inferido)* | 38 | 32 | formulário/validação, view, controller, model, $fillable, relação | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `title` | varchar(255) | não | — | — | Título (inferido) *(inferido)* | 51 | 45 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `amount` | decimal(10,2) | não | — | — | Valor (inferido) *(inferido)* | 44 | 21 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `expense_date` | date | não | — | — | Data do movimento (inferido) *(inferido)* | 41 | 20 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `partner_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para partner (inferido) *(inferido)* | 11 | 12 | formulário/validação, view, service, controller, $fillable, relação | **Utilização confirmada** |
| `observations` | text | sim | — | — | Observações (inferido) *(inferido)* | 15 | 15 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 60 | 0 | export, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 6 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `attachment_path` | varchar(255) | sim | — | — | Caminho de ficheiro em disco (inferido) *(inferido)* | 30 | 3 | export, view, service, controller, $fillable | **Utilização confirmada** |
| `expense_category` | text | sim | — | — | Categoria (antes coluna type da despesa) | 3 | 0 | model, $fillable | **Apenas leitura**; só em $fillable (escrita possível, sem escrita encontrada); em $fillable (escrita possível por mass-assignment) mas sem escrita encontrada |
| `vat_rate` | decimal(5,2) | sim | — | — | Taxa de IVA (inferido) *(inferido)* | 43 | 31 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `movement_type` | varchar(50) | não | `'expense'` | — | Tipo de movimento (expense/income) | 46 | 13 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `category` | varchar(100) | sim | — | — | Categoria (inferido) *(inferido)* | 30 | 16 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `client_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para client (inferido) *(inferido)* | 28 | 19 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `amount_gross` | decimal(12,2) | sim | — | — | Valor bruto (com IVA) (inferido) *(inferido)* | 29 | 10 | export, view, service, controller, model, $fillable | **Utilização confirmada** |
| `vat_amount` | decimal(12,2) | sim | — | — | Valor de IVA (inferido) *(inferido)* | 13 | 9 | export, view, service, controller, model, $fillable | **Utilização confirmada** |
| `amount_net` | decimal(12,2) | sim | — | — | Valor líquido (sem IVA) (inferido) *(inferido)* | 16 | 9 | export, view, service, controller, model, $fillable | **Utilização confirmada** |
| `payment_method` | varchar(50) | sim | — | — | Método de pagamento (inferido) *(inferido)* | 24 | 19 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `status` | varchar(50) | não | `'paid'` | — | Estado (inferido) *(inferido)* | 62 | 21 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `source_type` | varchar(100) | sim | — | — | Origem automática (ex. cotação convertida/venda) | 28 | 4 | export, observer, view, service, controller, model | **Utilização confirmada** |
| `source_id` | bigint unsigned | sim | — | — | ID da origem automática | 12 | 4 | observer, view, service, model, $fillable | **Utilização confirmada** |
| `v3_vehicle_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para v3_vehicle (inferido) *(inferido)* | 64 | 30 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `legalization_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para legalization (inferido) *(inferido)* | 11 | 5 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |

### `failed_jobs`

- **Finalidade:** Jobs falhados da fila. *(inferida do nome/colunas)*
- **Estado:** tabela de framework; QUEUE_CONNECTION local = sync.
- **Model:** sem model dedicado
- **PK:** id
- **Índices:** UNIQUE(uuid)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `0001_01_01`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: config/queue.php:86
- **Classificação dos campos:** Utilização confirmada: 1, Utilização desconhecida/externa: 6

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `uuid` | varchar(255) | não | — | UNIQUE(uuid) | Campo de domínio «uuid» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `connection` | text | não | — | — | Campo de domínio «connection» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `queue` | text | não | — | — | Campo de domínio «queue» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `payload` | longtext | não | — | — | Campo de domínio «payload» (finalidade não documentada; inferido pelo nome) *(inferido)* | 12 | 0 | script | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado).; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `exception` | longtext | não | — | — | Campo de domínio «exception» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `failed_at` | timestamp | não | `CURRENT_TIMESTAMP` | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |

### `financial_movements`

- **Finalidade:** Movimentos financeiros polimórficos (receitas/despesas calculadas e sincronizadas), usados em dashboards e relatórios. *(inferida do nome/colunas)*
- **Model:** `FinancialMovement` (app/Models/FinancialMovement.php)
- **PK:** id
- **Índices:** INDEX(movable_type,movable_id); INDEX(movement_date,type)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** movable() morphTo 
- **Migrations:** criada em `2026_05_15`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): FinancialDashboardController.php; services (2): MovementService.php, ReportDataService.php; observers (3): ExpenseObserver.php, SaleObserver.php, VehicleObserver.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 6; ex.: app/Models/Expense.php:201, app/Models/Expense.php:423, app/Http/Controllers/Admin/FinancialDashboardController.php:170, app/Http/Controllers/Admin/FinancialDashboardController.php:179
- **Classificação dos campos:** Apenas escrita: 1, Utilização confirmada: 12

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 36 | 0 | observer, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `movable_type` | varchar | não | — | INDEX(movable_type,movable_id) | Campo de domínio «movable_type» (finalidade não documentada; inferido pelo nome) *(inferido)* | 2 | 3 | observer, controller, model, $fillable | **Utilização confirmada** |
| `movable_id` | bigint unsigned | não | — | INDEX(movable_type,movable_id) | Chave estrangeira / referência para movable (inferido) *(inferido)* | 1 | 3 | observer, model, $fillable | **Utilização confirmada** |
| `type` | enum(income,expense) | não | — | INDEX(movement_date,type) | Tipo (inferido) *(inferido)* | 23 | 4 | service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `category` | varchar(255) | não | — | — | Categoria (inferido) *(inferido)* | 8 | 9 | view, service, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `description` | varchar(255) | não | — | — | Descrição (inferido) *(inferido)* | 0 | 3 | model, $fillable | **Apenas escrita**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `amount_gross` | decimal(12,2) | não | — | — | Valor bruto (com IVA) (inferido) *(inferido)* | 27 | 10 | export, view, service, controller, model, $fillable | **Utilização confirmada** |
| `vat_rate` | decimal(5,2) | sim | — | — | Taxa de IVA (inferido) *(inferido)* | 12 | 9 | view, service, model, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `vat_amount` | decimal(12,2) | não | `0` | — | Valor de IVA (inferido) *(inferido)* | 12 | 9 | export, view, service, controller, model, $fillable | **Utilização confirmada** |
| `amount_net` | decimal(12,2) | não | — | — | Valor líquido (sem IVA) (inferido) *(inferido)* | 15 | 9 | export, view, service, controller, model, $fillable | **Utilização confirmada** |
| `movement_date` | date | não | — | INDEX(movement_date,type) | Data (inferido pelo nome) *(inferido)* | 9 | 3 | view, service, controller, model, $fillable, $casts | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 17 | 0 | view, service, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 2 | 0 | view | **Utilização confirmada**; Eloquent/framework |

### `form_proposals`

- **Finalidade:** Pedidos de formulário / pedidos de cliente (site, BO, WhatsApp, angariador): preferências de viatura, retoma, origem e estado no funil. *(inferida do nome/colunas)*
- **Model:** `FormProposal` (app/Models/FormProposal.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(created_by) -> users.id ON DELETE set null
- **Relações Eloquent:** client() belongsTo Client::class; opportunities() hasMany ImportOpportunity::class; creator() belongsTo User::class, 'created_by'
- **Migrations:** criada em `2025_08_21`; 6 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (20): AngariadorController.php, ClientV2Controller.php, DashboardV2Controller.php, FormProposalV2Controller.php, GlobalSearchController.php, ImportOpportunityContactController.php…; services (6): FormProposalService.php, GlobalSearchService.php, ImportOpportunityService.php, MobileDashboardService.php, ProposalAcceptanceService.php, ProposalService.php; views (3): index.blade.php, index.blade.php, admin.blade.php; seeders (1): LeadsSeeder.php; mail/notificações (3): FinancingDocumentsMail.php, ImportFormConfirmationMail.php, NewFormProposalNotification.php; routes (1): web.php; outros app (1): VehicleFuel.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 7; ex.: app/Enums/VehicleFuel.php:9, app/Enums/VehicleFuel.php:49, app/Http/Controllers/Admin/FormProposalV2Controller.php:105, app/Http/Controllers/Admin/Mobile/ProposalController.php:92
- **Classificação dos campos:** Utilização confirmada: 38

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 301 | 1 | seeder, mail, observer, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 117 | 20 | seeder, mail, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `phone` | varchar(255) | não | — | — | Telefone (inferido) *(inferido)* | 33 | 20 | seeder, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `email` | varchar(255) | não | — | — | Email (inferido) *(inferido)* | 42 | 20 | seeder, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `client_id` | varchar(255) | sim | — | — | Chave estrangeira / referência para client (inferido) *(inferido)* | 61 | 26 | formulário/validação, seeder, view, service, controller, model | **Utilização confirmada**; sem FK constraint na BD (integridade só aplicacional); nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `status` | varchar(255) | não | `'novo'` | — | Estado do pedido no funil (novo, em_analise, convertido...) | 143 | 55 | seeder, observer, view, service, controller, model | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `source` | varchar(255) | sim | — | — | Origem (inferido) *(inferido)* | 3 | 5 | seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `message` | text | sim | — | — | Mensagem (inferido) *(inferido)* | 8 | 14 | seeder, view, service, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `ad_option` | enum(sim,nao_sei,nao_nao) | sim | — | — | Indica se o cliente tem anúncio em vista (inferido) *(inferido)* | 11 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada** |
| `ad_links` | text | sim | — | — | Links de anúncios indicados (inferido) *(inferido)* | 13 | 4 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada** |
| `brand` | varchar(255) | sim | — | — | Marca (inferido) *(inferido)* | 121 | 23 | formulário/validação, seeder, mail, observer, view, service | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `model` | varchar(255) | sim | — | — | Modelo (inferido) *(inferido)* | 95 | 18 | seeder, mail, observer, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `version` | varchar(255) | sim | — | — | Versão (inferido) *(inferido)* | 43 | 13 | formulário/validação, seeder, observer, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `fuel` | varchar(255) | sim | — | — | Combustível (inferido) *(inferido)* | 40 | 19 | seeder, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `year_min` | int | sim | — | — | Ano mínimo pretendido (inferido) *(inferido)* | 13 | 9 | formulário/validação, seeder, view, service, controller, $fillable | **Utilização confirmada** |
| `km_max` | int | sim | — | — | Quilómetros máximos pretendidos (inferido) *(inferido)* | 21 | 12 | formulário/validação, seeder, view, service, controller, $fillable | **Utilização confirmada** |
| `color` | varchar(255) | sim | — | — | Cor (inferido) *(inferido)* | 29 | 25 | seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `budget` | varchar(255) | sim | — | — | Orçamento (inferido) *(inferido)* | 24 | 8 | formulário/validação, seeder, script, view, service, controller | **Utilização confirmada** |
| `gearbox` | enum(indiferente,automatica,manual) | sim | — | — | Caixa de velocidades (inferido) *(inferido)* | 3 | 2 | seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `extras` | text | sim | — | — | Extras pretendidos (inferido) *(inferido)* | 12 | 6 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada** |
| `proposal_id` | int | sim | — | — | Chave estrangeira / referência para proposal (inferido) *(inferido)* | 32 | 14 | formulário/validação, observer, view, service, controller, model | **Utilização confirmada**; sem FK constraint na BD (integridade só aplicacional) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 94 | 0 | view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 6 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `payment_type` | varchar(255) | sim | — | — | Tipo de pagamento (inferido) *(inferido)* | 16 | 9 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada** |
| `estimated_purchase_date` | varchar(255) | sim | — | — | Data (inferido pelo nome) *(inferido)* | 11 | 6 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada** |
| `angariador_code` | varchar(255) | sim | — | — | Campo de domínio «angariador_code» (finalidade não documentada; inferido pelo nome) *(inferido)* | 9 | 3 | export, view, service, controller, model, $fillable | **Utilização confirmada** |
| `retoma_option` | varchar(255) | sim | — | — | Dados da retoma (viatura do cliente) (inferido) *(inferido)* | 5 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `retoma_brand` | varchar(255) | sim | — | — | Dados da retoma (viatura do cliente) (inferido) *(inferido)* | 5 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `retoma_model` | varchar(255) | sim | — | — | Dados da retoma (viatura do cliente) (inferido) *(inferido)* | 5 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `retoma_year` | int | sim | — | — | Dados da retoma (viatura do cliente) (inferido) *(inferido)* | 5 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `retoma_km` | int | sim | — | — | Dados da retoma (viatura do cliente) (inferido) *(inferido)* | 5 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `retoma_fuel` | varchar(255) | sim | — | — | Dados da retoma (viatura do cliente) (inferido) *(inferido)* | 5 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `retoma_info` | text | sim | — | — | Dados da retoma (viatura do cliente) (inferido) *(inferido)* | 5 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `retoma_photos` | json | sim | — | — | Dados da retoma (viatura do cliente) (inferido) *(inferido)* | 9 | 1 | view, controller, $fillable, $casts | **Utilização confirmada** |
| `origin` | varchar(20) | não | `'site'` | — | Origem do pedido (site, backoffice, ...) | 68 | 37 | formulário/validação, export, comando, seeder, view, service | **Utilização confirmada** |
| `title` | varchar(255) | sim | — | — | Título (inferido) *(inferido)* | 22 | 30 | view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_by` | bigint unsigned | sim | — | FK | Campo de domínio «created_by» (finalidade não documentada; inferido pelo nome) *(inferido)* | 3 | 3 | service, model, $fillable, relação | **Utilização confirmada** |
| `buyer_type` | varchar(20) | sim | — | — | Campo de domínio «buyer_type» (finalidade não documentada; inferido pelo nome) *(inferido)* | 13 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |

### `import_opportunities`

- **Finalidade:** Oportunidades de importação (viaturas encontradas para um pedido), com vendedor, contactos, estado e ligação à cotação. *(inferida do nome/colunas)*
- **Model:** `ImportOpportunity` (app/Models/ImportOpportunity.php)
- **PK:** id
- **Índices:** INDEX(status)
- **FKs / constraints:** FK(form_proposal_id) -> form_proposals.id ON DELETE cascade; FK(created_by) -> users.id ON DELETE set null; FK(proposal_id) -> proposals.id ON DELETE set null; FK(seller_id) -> sellers.id ON DELETE restrict; FK(seller_contact_id) -> seller_contacts.id ON DELETE set null
- **Relações Eloquent:** formProposal() belongsTo FormProposal::class; proposal() belongsTo Proposal::class; seller() belongsTo Seller::class; sellerContact() belongsTo SellerContact::class; checklistEntries() hasMany ImportOpportunityChecklistEntry::class; contacts() hasMany ImportOpportunityContact::class; creator() belongsTo User::class, 'created_by'
- **Migrations:** criada em `2026_09_29`; 5 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (6): ImportOpportunityContactController.php, ImportOpportunityController.php, ImportOpportunityController.php, ProposalController.php, ProposalV2Controller.php, ProposalController.php; services (6): ContactNormalizer.php, ConvertedProposalService.php, FormProposalService.php, ImportOpportunityService.php, ProposalAcceptanceService.php, ProposalService.php; observers (1): ProposalObserver.php; requests (2): ImportOpportunityRequest.php, SellerRequest.php; views (4): show.blade.php, _vehicle-fields.blade.php, show.blade.php, _seller-fields.blade.php; mail/notificações (1): AlternativeQuoteRequestedMail.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 2; ex.: app/Http/Controllers/Admin/Mobile/ProposalController.php:93, app/Services/ProposalService.php:64
- **Classificação dos campos:** Utilização confirmada: 31

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 132 | 1 | observer, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `form_proposal_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para form_proposal (inferido) *(inferido)* | 14 | 6 | formulário/validação, view, service, controller, $fillable, relação | **Utilização confirmada** |
| `brand` | varchar(255) | não | — | — | Marca (inferido) *(inferido)* | 38 | 16 | formulário/validação, mail, observer, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `model` | varchar(255) | não | — | — | Modelo (inferido) *(inferido)* | 34 | 14 | formulário/validação, mail, observer, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `version` | varchar(255) | sim | — | — | Versão (inferido) *(inferido)* | 25 | 13 | formulário/validação, mail, observer, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `year` | smallint unsigned | sim | — | — | Ano (inferido) *(inferido)* | 11 | 12 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `mileage` | int unsigned | sim | — | — | Quilometragem (inferido) *(inferido)* | 10 | 9 | formulário/validação, view, service, controller, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `price` | decimal(12,2) | sim | — | — | Preço (inferido) *(inferido)* | 8 | 2 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `currency` | char(3) | não | `'EUR'` | — | Campo de domínio «currency» (finalidade não documentada; inferido pelo nome) *(inferido)* | 2 | 1 | view, model, $fillable | **Utilização confirmada** |
| `fuel` | varchar(50) | sim | — | — | Combustível (inferido) *(inferido)* | 19 | 15 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `listing_url` | text | sim | — | — | Campo de domínio «listing_url» (finalidade não documentada; inferido pelo nome) *(inferido)* | 14 | 3 | formulário/validação, view, service, $fillable | **Utilização confirmada** |
| `vin` | varchar(17) | sim | — | — | Número de quadro (VIN) (inferido) *(inferido)* | 7 | 2 | formulário/validação, view, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `country` | char(2) | sim | — | — | País (inferido) *(inferido)* | 16 | 6 | formulário/validação, view, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `photo_path` | varchar(255) | sim | — | — | Caminho de ficheiro em disco (inferido) *(inferido)* | 10 | 2 | view, service, model, $fillable | **Utilização confirmada** |
| `vehicle_notes` | text | sim | — | — | Campo de domínio «vehicle_notes» (finalidade não documentada; inferido pelo nome) *(inferido)* | 3 | 2 | formulário/validação, view, service, $fillable | **Utilização confirmada** |
| `status` | varchar(30) | não | `'por_contactar'` | INDEX(status) | Estado da oportunidade (por_contactar, ... selecionado) | 60 | 45 | formulário/validação, observer, view, service, controller, model | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `contact_method` | varchar(30) | sim | — | — | Campo de domínio «contact_method» (finalidade não documentada; inferido pelo nome) *(inferido)* | 9 | 4 | formulário/validação, view, service, model, $fillable, $casts | **Utilização confirmada** |
| `contact_status` | varchar(30) | não | `'nao_contactado'` | — | Campo de domínio «contact_status» (finalidade não documentada; inferido pelo nome) *(inferido)* | 7 | 4 | formulário/validação, view, service, model, $fillable, $casts | **Utilização confirmada** |
| `contact_used` | varchar(255) | sim | — | — | Campo de domínio «contact_used» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 2 | formulário/validação, view, $fillable | **Utilização confirmada** |
| `last_contacted_at` | datetime | sim | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 6 | 3 | formulário/validação, view, service, $fillable, $casts | **Utilização confirmada** |
| `next_followup_at` | date | sim | — | — | Próximo seguimento agendado (inferido) *(inferido)* | 51 | 15 | formulário/validação, export, comando, observer, view, service | **Utilização confirmada** |
| `contact_notes` | text | sim | — | — | Campo de domínio «contact_notes» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 2 | formulário/validação, view, $fillable | **Utilização confirmada** |
| `notes` | text | sim | — | — | Notas internas (inferido) *(inferido)* | 14 | 16 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_by` | bigint unsigned | sim | — | FK | Campo de domínio «created_by» (finalidade não documentada; inferido pelo nome) *(inferido)* | 5 | 3 | service, model, $fillable, relação | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 15 | 0 | observer, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 3 | 0 | observer, controller | **Utilização confirmada**; Eloquent/framework |
| `proposal_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para proposal (inferido) *(inferido)* | 32 | 14 | formulário/validação, observer, view, service, controller, model | **Utilização confirmada** |
| `seller_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para seller (inferido) *(inferido)* | 13 | 7 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `seller_contact_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para seller_contact (inferido) *(inferido)* | 5 | 2 | formulário/validação, view, service, controller, $fillable, relação | **Utilização confirmada** |
| `client_quote_requested_at` | timestamp | sim | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 8 | 1 | view, controller, $fillable, $casts | **Utilização confirmada** |
| `offer_number` | varchar(100) | sim | — | — | Campo de domínio «offer_number» (finalidade não documentada; inferido pelo nome) *(inferido)* | 4 | 2 | formulário/validação, view, $fillable | **Utilização confirmada** |

### `import_opportunity_checklist_entries`

- **Finalidade:** Estado de cada item de checklist por oportunidade. *(inferida do nome/colunas)*
- **Model:** `ImportOpportunityChecklistEntry` (app/Models/ImportOpportunityChecklistEntry.php)
- **PK:** id
- **Índices:** UNIQUE(import_opportunity_id,opportunity_checklist_item_id,import_opp_checklist_entry_unique)
- **FKs / constraints:** FK(import_opportunity_id) -> import_opp_checklist_entries_opportunity_fk.id ON DELETE cascade; FK(opportunity_checklist_item_id) -> import_opp_checklist_entries_item_fk.id ON DELETE cascade; FK(updated_by) -> users.id ON DELETE set null
- **Relações Eloquent:** opportunity() belongsTo ImportOpportunity::class, 'import_opportunity_id'; item() belongsTo OpportunityChecklistItem::class, 'opportunity_checklist_item; updatedBy() belongsTo User::class, 'updated_by'
- **Migrations:** criada em `2026_09_29`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** services (1): ImportOpportunityService.php
- **Classificação dos campos:** Utilização confirmada: 7

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 15 | 0 | view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `import_opportunity_id` | bigint unsigned | não | — | UNIQUE(import_opportunity_id,opportunity_checklist_item_id,import_opp_checklist_entry_unique), FK | Chave estrangeira / referência para import_opportunity (inferido) *(inferido)* | 6 | 5 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `opportunity_checklist_item_id` | bigint unsigned | não | — | UNIQUE(import_opportunity_id,opportunity_checklist_item_id,import_opp_checklist_entry_unique), FK | Chave estrangeira / referência para opportunity_checklist_item (inferido) *(inferido)* | 2 | 1 | service, model, $fillable, relação | **Utilização confirmada** |
| `status` | varchar(20) | não | `'pending'` | — | Estado (inferido) *(inferido)* | 20 | 6 | view, service, controller, model, $fillable, $casts | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `updated_by` | bigint unsigned | sim | — | FK | Campo de domínio «updated_by» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 1 | service, model, $fillable, relação | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 1 | 0 | view | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `import_opportunity_contacts`

- **Finalidade:** Histórico de contactos feitos ao vendedor numa oportunidade. *(inferida do nome/colunas)*
- **Model:** `ImportOpportunityContact` (app/Models/ImportOpportunityContact.php)
- **PK:** id
- **Índices:** INDEX(import_opportunity_id,contacted_at,import_opp_contacts_opportunity_date_index)
- **FKs / constraints:** FK(import_opportunity_id) -> import_opportunities.id ON DELETE cascade; FK(user_id) -> users.id ON DELETE set null
- **Relações Eloquent:** opportunity() belongsTo ImportOpportunity::class, 'import_opportunity_id'; user() belongsTo User::class
- **Migrations:** criada em `2026_09_29`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (2): ImportOpportunityContactController.php, ImportOpportunityController.php; services (1): ImportOpportunityService.php
- **Classificação dos campos:** Utilização confirmada: 9

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 31 | 0 | view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `import_opportunity_id` | bigint unsigned | não | — | INDEX(import_opportunity_id,contacted_at,import_opp_contacts_opportunity_date_index), FK | Chave estrangeira / referência para import_opportunity (inferido) *(inferido)* | 6 | 5 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `contacted_at` | datetime | não | — | INDEX(import_opportunity_id,contacted_at,import_opp_contacts_opportunity_date_index) | Data/hora de evento (inferido pelo nome) *(inferido)* | 8 | 3 | formulário/validação, view, service, model, $fillable, $casts | **Utilização confirmada** |
| `method` | varchar(30) | não | — | — | Campo de domínio «method» (finalidade não documentada; inferido pelo nome) *(inferido)* | 37 | 5 | formulário/validação, comando, view, service, model, $fillable | **Utilização confirmada** |
| `type` | varchar(30) | não | — | — | Tipo (inferido) *(inferido)* | 7 | 2 | view, service, model, $fillable, $casts | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `message` | text | sim | — | — | Mensagem (inferido) *(inferido)* | 2 | 1 | view, service, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `user_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para user (inferido) *(inferido)* | 0 | 2 | service, $fillable, relação | **Utilização confirmada**; lido implicitamente via relação: belongsTo user() em ImportOpportunityContact; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `import_simulator`

- **Finalidade:** Tabela vazia (apenas id e timestamps) criada para o simulador de importação; sem utilização (o simulador usa cost_simulators). *(inferida do nome/colunas)*
- **Estado:** tabela vazia sem uso.
- **Model:** sem model dedicado
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `2025_09_23`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Classificação dos campos:** Potencialmente obsoleto: 2, Utilização confirmada: 1

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Potencialmente obsoleto**; tabela vazia sem uso |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Potencialmente obsoleto**; tabela vazia sem uso |

### `job_batches`

- **Finalidade:** Lotes de jobs (driver database). *(inferida do nome/colunas)*
- **Estado:** tabela de framework; QUEUE_CONNECTION local = sync.
- **Model:** sem model dedicado
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `0001_01_01`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Classificação dos campos:** Utilização confirmada: 1, Utilização desconhecida/externa: 9

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | varchar(255) | não | — | PK | Chave primária | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `total_jobs` | int | não | — | — | Campo de domínio «total_jobs» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `pending_jobs` | int | não | — | — | Campo de domínio «pending_jobs» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `failed_jobs` | int | não | — | — | Campo de domínio «failed_jobs» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `failed_job_ids` | longtext | não | — | — | Campo de domínio «failed_job_ids» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `options` | mediumtext | sim | — | — | Opções (JSON) (inferido) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `cancelled_at` | int | sim | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `created_at` | int | não | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `finished_at` | int | sim | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 0 | 1 | script | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado).; escrito/lido por scraper Python externo (sem passar pelo Laravel) |

### `jobs`

- **Finalidade:** Fila de jobs (driver database). *(inferida do nome/colunas)*
- **Estado:** tabela de framework; QUEUE_CONNECTION local = sync.
- **Model:** sem model dedicado
- **PK:** id
- **Índices:** INDEX(queue)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `0001_01_01`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 6; ex.: app/Console/Commands/GenerateImageThumbnails.php:18, app/Console/Commands/GenerateImageThumbnails.php:21, app/Console/Commands/GenerateImageThumbnails.php:25, app/Console/Commands/GenerateImageThumbnails.php:28
- **Classificação dos campos:** Utilização confirmada: 1, Utilização desconhecida/externa: 6

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `queue` | varchar(255) | não | — | INDEX(queue) | Campo de domínio «queue» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `payload` | longtext | não | — | — | Campo de domínio «payload» (finalidade não documentada; inferido pelo nome) *(inferido)* | 12 | 0 | script | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado).; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `attempts` | tinyint unsigned | não | — | — | Campo de domínio «attempts» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `reserved_at` | int unsigned | sim | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `available_at` | int unsigned | não | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `created_at` | int unsigned | não | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização desconhecida/externa**; QUEUE_CONNECTION local = sync; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |

### `lead_activities`

- **Finalidade:** Atividades/timeline de cada lead/cliente. *(inferida do nome/colunas)*
- **Model:** `LeadActivity` (app/Models/LeadActivity.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(client_id) -> clients.id ON DELETE cascade; FK(user_id) -> users.id ON DELETE set null
- **Relações Eloquent:** client() belongsTo Client::class; user() belongsTo User::class
- **Migrations:** criada em `2026_06_29`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (12): AngariadorAdminController.php, AngariadorController.php, DashboardV2Controller.php, LeadV2Controller.php, ClientController.php, LeadController.php…; services (8): ClientMatcher.php, ClientMergeService.php, ConvertedProposalService.php, FormProposalService.php, LeadService.php, ProposalAcceptanceService.php…; observers (1): ProposalObserver.php; views (5): show.blade.php, show.blade.php, lead-show.blade.php, show.blade.php, show.blade.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: app/Http/Controllers/Admin/AngariadorAdminController.php:181
- **Classificação dos campos:** Utilização confirmada: 10

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 180 | 2 | observer, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `client_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para client (inferido) *(inferido)* | 57 | 20 | view, service, controller, model, $fillable, relação | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `user_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para user (inferido) *(inferido)* | 4 | 2 | controller, model, $fillable, relação | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `type` | varchar(255) | não | `'system'` | — | Tipo (inferido) *(inferido)* | 26 | 13 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `title` | varchar(255) | não | — | — | Título (inferido) *(inferido)* | 31 | 24 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `body` | text | sim | — | — | Corpo do texto (inferido) *(inferido)* | 16 | 13 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `icon` | varchar(255) | não | `'bi-circle-fill'` | — | Ícone (inferido) *(inferido)* | 34 | 30 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `color` | varchar(255) | não | `'secondary'` | — | Cor (inferido) *(inferido)* | 32 | 28 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 117 | 3 | observer, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 3 | 0 | observer, controller | **Utilização confirmada**; Eloquent/framework |

### `legalization_documents`

- **Finalidade:** Documentos anexos a processos de legalização. *(inferida do nome/colunas)*
- **Model:** `LegalizationDocument` (app/Models/LegalizationDocument.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(legalization_id) -> legalizations.id ON DELETE cascade
- **Relações Eloquent:** legalization() belongsTo Legalization::class
- **Migrations:** criada em `2026_04_27`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (2): LegalizationController.php, V3VehicleController.php
- **Classificação dos campos:** Utilização confirmada: 7

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 46 | 0 | controller | **Utilização confirmada**; Eloquent/framework |
| `legalization_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para legalization (inferido) *(inferido)* | 10 | 5 | formulário/validação, export, view, service, controller, $fillable | **Utilização confirmada** |
| `tipo` | varchar(255) | não | — | — | Tipo (inferido) *(inferido)* | 6 | 6 | controller, model, $fillable | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `nome_original` | varchar(255) | não | — | — | Nome original do ficheiro carregado (inferido) *(inferido)* | 5 | 5 | controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `caminho` | varchar(255) | não | — | — | Caminho do ficheiro em disco (inferido) *(inferido)* | 14 | 5 | controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 2 | 0 | model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `legalizations`

- **Finalidade:** Processos de legalização de viaturas (IMT, homologação, Modelo 9/1460, ISV, estado dos passos, token de acompanhamento). *(inferida do nome/colunas)*
- **Model:** `Legalization` (app/Models/Legalization.php)
- **PK:** id
- **Índices:** UNIQUE(token)
- **FKs / constraints:** FK(client_id) -> clients.id ON DELETE set null; FK(vehicle_id) -> vehicles.id ON DELETE set null; FK(v3_vehicle_id) -> v3_vehicles.id ON DELETE set null
- **Relações Eloquent:** client() belongsTo Client::class; vehicle() belongsTo Vehicle::class; v3Vehicle() belongsTo V3Vehicle::class, 'v3_vehicle_id'; expenses() hasMany Expense::class; documents() hasMany LegalizationDocument::class
- **Migrations:** criada em `2026_04_27`; 10 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (6): GlobalSearchController.php, LegalizationController.php, V3VehicleController.php, VehicleV2Controller.php, LegalizationStatusController.php, PageController.php; services (10): AnnualReportService.php, ConvertedProposalService.php, GlobalSearchService.php, Modelo1460PdfService.php, Modelo1RaPdfService.php, Modelo9PdfService.php…; commands (2): SendLegalizationTrackingEmails.php, Kernel.php; exports (3): ExportRegistry.php, LegalizationExport.php, VehicleExport.php; requests (1): ExportXlsxRequest.php; views (18): form.blade.php, _view-toggle.blade.php, create.blade.php, edit.blade.php, index.blade.php, kanban.blade.php…; mail/notificações (1): LegalizationTrackingMail.php; routes (1): web.php; config (1): permissions.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 138; ex.: app/Models/Vehicle.php:81, app/Exports/ExportRegistry.php:16, app/Exports/LegalizationExport.php:18, app/Http/Controllers/PageController.php:99
- **Classificação dos campos:** Utilização confirmada: 20

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 193 | 0 | export, comando, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `client_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para client (inferido) *(inferido)* | 21 | 17 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `marca` | varchar(255) | não | — | — | Marca (inferido) *(inferido)* | 88 | 15 | formulário/validação, export, script, view, service, controller | **Utilização confirmada** |
| `modelo` | varchar(255) | não | — | — | Modelo (inferido) *(inferido)* | 81 | 13 | formulário/validação, export, script, view, service, controller | **Utilização confirmada** |
| `combustivel` | varchar(255) | não | `'Gasolina'` | — | Combustível (inferido) *(inferido)* | 60 | 22 | formulário/validação, export, script, view, service, controller | **Utilização confirmada** |
| `notas` | text | sim | — | — | Notas (inferido) *(inferido)* | 14 | 6 | formulário/validação, export, view, controller, $fillable | **Utilização confirmada** |
| `steps_completed` | json | sim | — | — | Passos concluídos (JSON) (inferido) *(inferido)* | 14 | 5 | export, view, service, controller, model, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 25 | 0 | export, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 2 | 0 | view | **Utilização confirmada**; Eloquent/framework |
| `matricula` | varchar(255) | sim | — | — | Matrícula (inferido) *(inferido)* | 27 | 12 | formulário/validação, export, mail, view, service, controller | **Utilização confirmada** |
| `num_homologacao` | varchar(255) | sim | — | — | Campo de domínio «num_homologacao» (finalidade não documentada; inferido pelo nome) *(inferido)* | 16 | 6 | formulário/validação, export, view, service, controller, $fillable | **Utilização confirmada** |
| `vehicle_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para vehicle (inferido) *(inferido)* | 9 | 8 | controller, model, $fillable, relação | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `v3_vehicle_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para v3_vehicle (inferido) *(inferido)* | 64 | 30 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `num_processo_imt` | varchar(100) | sim | — | — | Campo de domínio «num_processo_imt» (finalidade não documentada; inferido pelo nome) *(inferido)* | 9 | 4 | formulário/validação, export, view, service, controller, $fillable | **Utilização confirmada** |
| `regime_especial_isv` | tinyint(1) | não | `false` | — | Campo de domínio «regime_especial_isv» (finalidade não documentada; inferido pelo nome) *(inferido)* | 8 | 6 | formulário/validação, export, view, controller, model, $fillable | **Utilização confirmada** |
| `invoice_path` | varchar(255) | sim | — | — | Caminho de ficheiro em disco (inferido) *(inferido)* | 14 | 2 | view, controller, $fillable | **Utilização confirmada** |
| `token` | varchar(40) | sim | — | UNIQUE(token) | Token público para acompanhamento da legalização pelo cliente | 5 | 1 | view, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `email_enviado` | tinyint(1) | não | `false` | — | Campo de domínio «email_enviado» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 1 | comando, $casts | **Utilização confirmada** |
| `modelo9_dados` | json | sim | — | — | Dados preenchidos do Modelo 9 (JSON) | 5 | 2 | view, service, controller, $fillable, $casts | **Utilização confirmada** |
| `modelo1460_dados` | json | sim | — | — | Dados preenchidos do Modelo 1460 (JSON) | 2 | 1 | view, service, controller, $fillable, $casts | **Utilização confirmada** |

### `menus`

- **Finalidade:** Itens de menu do site público (hierárquicos). *(inferida do nome/colunas)*
- **Model:** `Menu` (app/Models/_Menu.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(parent_id) -> menus.id ON DELETE cascade
- **Relações Eloquent:** items() hasMany MenuItem::class
- **Migrations:** criada em `2025_08_23`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (4): MenuV2Controller.php, MenuController.php, MenuItemController.php, _MenuController.php; views (16): create.blade.php, edit.blade.php, index.blade.php, create.blade.php, edit.blade.php, index.blade.php…; routes (1): web.php; config (1): permissions.php; outros app (2): AppServiceProvider.php, AuthServiceProvider.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 86; ex.: app/Providers/AppServiceProvider.php:68, app/Providers/AppServiceProvider.php:69, app/Providers/AppServiceProvider.php:71, app/Providers/AppServiceProvider.php:91
- **Classificação dos campos:** Utilização confirmada: 8

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 57 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `title` | varchar(255) | não | — | — | Título (inferido) *(inferido)* | 44 | 11 | formulário/validação, view, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `url` | varchar(255) | sim | — | — | URL (inferido) *(inferido)* | 27 | 13 | formulário/validação, view, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `parent_id` | bigint unsigned | sim | — | FK | Item pai (hierarquia) (inferido) *(inferido)* | 35 | 11 | formulário/validação, view, controller, model | **Utilização confirmada** |
| `order` | int | não | `0` | — | Ordem de apresentação (inferido) *(inferido)* | 29 | 14 | formulário/validação, view, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `show_online` | tinyint(1) | não | `true` | — | Visível no site público (inferido) *(inferido)* | 37 | 24 | formulário/validação, export, comando, view, service, controller | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `model_cars`

- **Finalidade:** Modelos de viaturas por marca (taxonomia). *(inferida do nome/colunas)*
- **Model:** `ModelCar` (app/Models/ModelCar.php)
- **PK:** id
- **Índices:** UNIQUE(brand_id,name)
- **FKs / constraints:** FK(brand_id) -> brands.id ON DELETE cascade
- **Relações Eloquent:** brand() belongsTo Brand::class; years() hasMany ModelYear::class; modelYears() hasMany ModelYear::class; submodels() hasMany SubmodelCar::class, 'model_id'
- **Migrations:** criada em `2025_05_13`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** services (1): ProposalService.php; commands (1): ImportCarData.php; views (1): _brand-model-select.blade.php; routes (1): api.php; outros app (1): VehicleCatalog.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 2; ex.: app/Support/VehicleCatalog.php:10, resources/views/admin/v2/partials/_brand-model-select.blade.php:1
- **Classificação dos campos:** Utilização confirmada: 5

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 15 | 0 | comando, service | **Utilização confirmada**; Eloquent/framework |
| `brand_id` | bigint unsigned | não | — | UNIQUE(brand_id,name), FK | Chave estrangeira / referência para brand (inferido) *(inferido)* | 2 | 1 | comando, model, $fillable, relação | **Utilização confirmada** |
| `name` | varchar(255) | não | — | UNIQUE(brand_id,name) | Nome (inferido) *(inferido)* | 14 | 2 | comando, view, service, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `model_has_permissions`

- **Finalidade:** Pivot spatie: permissões directas por utilizador. *(inferida do nome/colunas)*
- **Model:** sem model dedicado
- **PK:** permission_id, model_type, model_id
- **Índices:** PRIMARY(permission_id,model_id,model_type); INDEX(model_id,model_type)
- **FKs / constraints:** FK(permission_id) -> permissions.id ON DELETE cascade
- **Relações Eloquent:** —
- **Migrations:** criada em `2025_08_26`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 2; ex.: config/permission.php:55, config/permission.php:130
- **Classificação dos campos:** Utilização confirmada: 3

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `permission_id` | bigint unsigned | não | — | PK, PRIMARY(permission_id,model_id,model_type), FK | Chave estrangeira / referência para permission (inferido) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |
| `model_type` | varchar(255) | não | — | PK, PRIMARY(permission_id,model_id,model_type), INDEX(model_id,model_type) | Campo de domínio «model_type» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |
| `model_id` | bigint unsigned | não | — | PK, PRIMARY(permission_id,model_id,model_type), INDEX(model_id,model_type) | Chave estrangeira / referência para model (inferido) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |

### `model_has_roles`

- **Finalidade:** Pivot spatie: roles por utilizador. *(inferida do nome/colunas)*
- **Model:** sem model dedicado
- **PK:** role_id, model_type, model_id
- **Índices:** PRIMARY(role_id,model_id,model_type); INDEX(model_id,model_type)
- **FKs / constraints:** FK(role_id) -> roles.id ON DELETE cascade
- **Relações Eloquent:** —
- **Migrations:** criada em `2025_08_26`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 2; ex.: config/permission.php:63, config/permission.php:130
- **Classificação dos campos:** Utilização confirmada: 3

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `role_id` | bigint unsigned | não | — | PK, PRIMARY(role_id,model_id,model_type), FK | Chave estrangeira / referência para role (inferido) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |
| `model_type` | varchar(255) | não | — | PK, PRIMARY(role_id,model_id,model_type), INDEX(model_id,model_type) | Campo de domínio «model_type» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |
| `model_id` | bigint unsigned | não | — | PK, PRIMARY(role_id,model_id,model_type), INDEX(model_id,model_type) | Chave estrangeira / referência para model (inferido) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |

### `model_years`

- **Finalidade:** Anos por modelo (taxonomia). *(inferida do nome/colunas)*
- **Model:** `ModelYear` (app/Models/ModelYear.php)
- **PK:** id
- **Índices:** UNIQUE(model_car_id,year)
- **FKs / constraints:** FK(model_car_id) -> model_cars.id ON DELETE cascade
- **Relações Eloquent:** modelCar() belongsTo ModelCar::class
- **Migrations:** criada em `2025_05_13`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** commands (1): ImportCarData.php
- **Classificação dos campos:** Apenas escrita: 1, Utilização confirmada: 4

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 1 | 0 | comando | **Utilização confirmada**; Eloquent/framework |
| `model_car_id` | bigint unsigned | não | — | UNIQUE(model_car_id,year), FK | Chave estrangeira / referência para model_car (inferido) *(inferido)* | 0 | 0 | $fillable, relação | **Utilização confirmada**; lido implicitamente via relação: belongsTo modelCar() em ModelYear |
| `year` | varchar(255) | não | — | UNIQUE(model_car_id,year) | Ano (inferido) *(inferido)* | 0 | 0 | $fillable | **Apenas escrita**; só em $fillable (escrita possível, sem escrita encontrada); só em $fillable |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `news_articles`

- **Finalidade:** Notícias do site público com SEO. *(inferida do nome/colunas)*
- **Model:** `NewsArticle` (app/Models/NewsArticle.php)
- **PK:** id
- **Índices:** UNIQUE(slug)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `2026_06_02`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (2): NewsArticleController.php, NewsController.php; commands (1): GenerateSitemap.php; seeders (1): MigrateNewsToNewsArticlesSeeder.php
- **Classificação dos campos:** Utilização confirmada: 15

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 8 | 0 | seeder, view, controller, model | **Utilização confirmada**; Eloquent/framework |
| `title` | varchar(255) | não | — | — | Título (inferido) *(inferido)* | 11 | 3 | seeder, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `slug` | varchar(255) | não | — | UNIQUE(slug) | Slug (identificador em URL) (inferido) *(inferido)* | 17 | 2 | comando, seeder, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `subtitle` | text | sim | — | — | Campo de domínio «subtitle» (finalidade não documentada; inferido pelo nome) *(inferido)* | 164 | 18 | formulário/validação, seeder, view, controller, model, $fillable | **Utilização confirmada** |
| `content` | longtext | não | — | — | Conteúdo (inferido) *(inferido)* | 4 | 2 | seeder, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `summary` | text | sim | — | — | Campo de domínio «summary» (finalidade não documentada; inferido pelo nome) *(inferido)* | 13 | 6 | formulário/validação, seeder, view, controller, model, $fillable | **Utilização confirmada** |
| `cover_image` | varchar(255) | sim | — | — | Campo de domínio «cover_image» (finalidade não documentada; inferido pelo nome) *(inferido)* | 29 | 3 | formulário/validação, seeder, view, controller, model, $fillable | **Utilização confirmada** |
| `gallery` | json | sim | — | — | Campo de domínio «gallery» (finalidade não documentada; inferido pelo nome) *(inferido)* | 35 | 2 | seeder, script, view, controller, model, $fillable | **Utilização confirmada** |
| `status` | enum(Publicado,Rascunho) | não | `'Rascunho'` | — | Estado (inferido) *(inferido)* | 8 | 5 | seeder, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `published_at` | timestamp | sim | — | — | Data de publicação (inferido) *(inferido)* | 7 | 2 | seeder, view, controller, model, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `seo_title` | varchar(255) | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 9 | 6 | formulário/validação, seeder, view, controller, model, $fillable | **Utilização confirmada** |
| `seo_description` | text | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 8 | 6 | formulário/validação, seeder, view, controller, model, $fillable | **Utilização confirmada** |
| `seo_keywords` | varchar(255) | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 5 | 5 | formulário/validação, seeder, view, controller, model, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 2 | 0 | seeder | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 2 | 0 | comando | **Utilização confirmada**; Eloquent/framework |

### `newsletter_offers`

- **Finalidade:** Ofertas (viaturas) incluídas em newsletters. *(inferida do nome/colunas)*
- **Model:** `NewsletterOffer` (app/Models/NewsletterOffer.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(newsletter_id) -> newsletters.id ON DELETE set null
- **Relações Eloquent:** newsletter() belongsTo Newsletter::class
- **Migrations:** criada em `2026_02_14`; 3 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (3): NewsletterController.php, NewsletterManagementController.php, NewsletterOfferController.php
- **Classificação dos campos:** Utilização confirmada: 14

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 14 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `image` | varchar(255) | não | — | — | Imagem (inferido) *(inferido)* | 15 | 5 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `brand` | varchar(255) | não | — | — | Marca (inferido) *(inferido)* | 7 | 5 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `model` | varchar(255) | não | — | — | Modelo (inferido) *(inferido)* | 7 | 5 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `year` | varchar(255) | não | — | — | Ano (inferido) *(inferido)* | 5 | 5 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `kms` | varchar(255) | não | — | — | Campo de domínio «kms» (finalidade não documentada; inferido pelo nome) *(inferido)* | 18 | 7 | formulário/validação, script, view, controller, $fillable | **Utilização confirmada** |
| `price` | varchar(255) | não | — | — | Preço (inferido) *(inferido)* | 5 | 5 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `savings` | varchar(255) | não | — | — | Campo de domínio «savings» (finalidade não documentada; inferido pelo nome) *(inferido)* | 11 | 8 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `equipamentos` | text | sim | — | — | Campo de domínio «equipamentos» (finalidade não documentada; inferido pelo nome) *(inferido)* | 12 | 6 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `combustivel` | varchar(255) | sim | — | — | Combustível (inferido) *(inferido)* | 60 | 22 | formulário/validação, export, script, view, service, controller | **Utilização confirmada** |
| `is_active` | tinyint(1) | não | `true` | — | Ativo (inferido) *(inferido)* | 13 | 6 | view, controller, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 1 | 0 | controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `newsletter_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para newsletter (inferido) *(inferido)* | 6 | 1 | controller, $fillable, relação | **Utilização confirmada** |

### `newsletter_send_logs`

- **Finalidade:** Registo de envios de newsletter por cliente (evita duplicados). *(inferida do nome/colunas)*
- **Model:** `NewsletterSendLog` (app/Models/NewsletterSendLog.php)
- **PK:** id
- **Índices:** UNIQUE(newsletter_id,client_id)
- **FKs / constraints:** FK(newsletter_id) -> newsletters.id ON DELETE cascade; FK(client_id) -> clients.id ON DELETE cascade
- **Relações Eloquent:** newsletter() belongsTo Newsletter::class; client() belongsTo Client::class
- **Migrations:** criada em `2026_05_14`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): NewsletterManagementController.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: app/Models/Newsletter.php:41
- **Classificação dos campos:** Utilização confirmada: 6

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 7 | 0 | controller | **Utilização confirmada**; Eloquent/framework |
| `newsletter_id` | bigint unsigned | não | — | UNIQUE(newsletter_id,client_id), FK | Chave estrangeira / referência para newsletter (inferido) *(inferido)* | 6 | 1 | controller, $fillable, relação | **Utilização confirmada** |
| `client_id` | bigint unsigned | não | — | UNIQUE(newsletter_id,client_id), FK | Chave estrangeira / referência para client (inferido) *(inferido)* | 2 | 1 | controller, $fillable, relação | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `sent_at` | timestamp | sim | — | — | Data/hora de envio (inferido) *(inferido)* | 10 | 2 | view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `newsletters`

- **Finalidade:** Newsletters (título, texto, template, envio). *(inferida do nome/colunas)*
- **Model:** `Newsletter` (app/Models/Newsletter.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(offer_1_id) -> newsletter_offers.id ON DELETE set null; FK(offer_2_id) -> newsletter_offers.id ON DELETE set null; FK(offer_3_id) -> newsletter_offers.id ON DELETE set null
- **Relações Eloquent:** offers() hasMany NewsletterOffer::class; sendLogs() hasMany NewsletterSendLog::class; sentClients() belongsToMany Client::class, 'newsletter_send_logs'
- **Migrations:** criada em `2026_03_17`; 3 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (4): ExportController.php, NewsletterController.php, NewsletterManagementController.php, ImportController.php; views (19): show.blade.php, form.blade.php, show.blade.php, index.blade.php, show.blade.php, form.blade.php…; mail/notificações (2): NewsletterPreview.php, NewsletterUnsubscribeNotification.php; routes (1): web.php; config (1): permissions.php; outros app (1): LeadSource.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 14; ex.: app/Http/Controllers/Admin/NewsletterManagementController.php:21, app/Http/Controllers/Admin/NewsletterManagementController.php:26, resources/views/admin/v2/newsletter-management/index.blade.php:12, resources/views/admin/v2/newsletter-management/index.blade.php:23
- **Classificação dos campos:** Utilização confirmada: 8

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 119 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `title` | varchar(255) | não | — | — | Título (inferido) *(inferido)* | 55 | 6 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `subtitle` | varchar(255) | sim | — | — | Campo de domínio «subtitle» (finalidade não documentada; inferido pelo nome) *(inferido)* | 162 | 17 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `text` | text | sim | — | — | Texto (inferido) *(inferido)* | 70 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `sent_at` | timestamp | sim | — | — | Data/hora de envio (inferido) *(inferido)* | 10 | 2 | view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 33 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 1 | 0 | view | **Utilização confirmada**; Eloquent/framework |
| `template` | varchar(255) | não | `'standard'` | — | Campo de domínio «template» (finalidade não documentada; inferido pelo nome) *(inferido)* | 8 | 3 | formulário/validação, mail, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |

### `opportunity_checklist_items`

- **Finalidade:** Itens das checklists de oportunidades. *(inferida do nome/colunas)*
- **Model:** `OpportunityChecklistItem` (app/Models/OpportunityChecklistItem.php)
- **PK:** id
- **Índices:** UNIQUE(opportunity_checklist_id,slug)
- **FKs / constraints:** FK(opportunity_checklist_id) -> opportunity_checklists.id ON DELETE cascade
- **Relações Eloquent:** checklist() belongsTo OpportunityChecklist::class, 'opportunity_checklist_id'
- **Migrations:** criada em `2026_09_29`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Classificação dos campos:** Apenas leitura: 1, Utilização confirmada: 7

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 12 | 0 | view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `opportunity_checklist_id` | bigint unsigned | não | — | UNIQUE(opportunity_checklist_id,slug), FK | Chave estrangeira / referência para opportunity_checklist (inferido) *(inferido)* | 1 | 0 | model, $fillable, relação | **Utilização confirmada** |
| `slug` | varchar(255) | não | — | UNIQUE(opportunity_checklist_id,slug) | Slug (identificador em URL) (inferido) *(inferido)* | 1 | 0 | model, $fillable | **Apenas leitura**; só em $fillable (escrita possível, sem escrita encontrada); em $fillable (escrita possível por mass-assignment) mas sem escrita encontrada; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `label` | varchar(255) | não | — | — | Etiqueta/rótulo (inferido) *(inferido)* | 8 | 1 | formulário/validação, view, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `sort_order` | smallint unsigned | não | `0` | — | Ordem de apresentação (inferido) *(inferido)* | 2 | 1 | model, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `is_active` | tinyint(1) | não | `true` | — | Ativo (inferido) *(inferido)* | 2 | 1 | model, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `opportunity_checklists`

- **Finalidade:** Checklists de verificação de oportunidades por combustível. *(inferida do nome/colunas)*
- **Model:** `OpportunityChecklist` (app/Models/OpportunityChecklist.php)
- **PK:** id
- **Índices:** UNIQUE(slug)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** items() hasMany OpportunityChecklistItem::class
- **Migrations:** criada em `2026_09_29`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** services (1): ImportOpportunityService.php; outros app (1): VehicleFuel.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: app/Enums/VehicleFuel.php:10
- **Classificação dos campos:** Apenas leitura: 3, Utilização confirmada: 5

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 10 | 0 | service | **Utilização confirmada**; Eloquent/framework |
| `slug` | varchar(255) | não | — | UNIQUE(slug) | Slug (identificador em URL) (inferido) *(inferido)* | 1 | 0 | model, $fillable | **Apenas leitura**; só em $fillable (escrita possível, sem escrita encontrada); em $fillable (escrita possível por mass-assignment) mas sem escrita encontrada; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 2 | 0 | view, $fillable | **Apenas leitura**; só em $fillable (escrita possível, sem escrita encontrada); em $fillable (escrita possível por mass-assignment) mas sem escrita encontrada; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `applies_to_fuels` | json | sim | — | — | Campo de domínio «applies_to_fuels» (finalidade não documentada; inferido pelo nome) *(inferido)* | 4 | 0 | view, model, $fillable, $casts | **Apenas leitura**; só em $fillable (escrita possível, sem escrita encontrada); em $fillable (escrita possível por mass-assignment) mas sem escrita encontrada |
| `sort_order` | smallint unsigned | não | `0` | — | Ordem de apresentação (inferido) *(inferido)* | 3 | 1 | service, model, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `is_active` | tinyint(1) | não | `true` | — | Ativo (inferido) *(inferido)* | 3 | 1 | service, model, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `page_contents`

- **Finalidade:** CMS: valores dos campos de cada página. *(inferida do nome/colunas)*
- **Model:** `PageContent` (app/Models/PageContent.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(page_id) -> pages.id ON DELETE cascade
- **Relações Eloquent:** page() belongsTo Page::class
- **Migrations:** criada em `2025_06_21`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (2): NewsV3Controller.php, PageContentController.php; seeders (1): NewsArticlesSeeder.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: app/Http/Controllers/Admin/NewsV3Controller.php:41
- **Classificação dos campos:** Utilização confirmada: 6

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 17 | 0 | seeder, controller | **Utilização confirmada**; Eloquent/framework |
| `page_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para page (inferido) *(inferido)* | 9 | 12 | seeder, view, controller, model, $fillable, relação | **Utilização confirmada** |
| `field_name` | varchar(255) | não | — | — | Campo de domínio «field_name» (finalidade não documentada; inferido pelo nome) *(inferido)* | 42 | 21 | seeder, view, controller, $fillable | **Utilização confirmada** |
| `field_value` | text | sim | — | — | Campo de domínio «field_value» (finalidade não documentada; inferido pelo nome) *(inferido)* | 41 | 17 | seeder, view, controller, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 1 | 0 | controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `page_type_fields`

- **Finalidade:** CMS: definição de campos de cada tipo de página. *(inferida do nome/colunas)*
- **Model:** `PageTypeField` (app/Models/PageTypeField.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(page_type_id) -> page_types.id ON DELETE cascade
- **Relações Eloquent:** pageType() belongsTo PageType::class
- **Migrations:** criada em `2025_06_21`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): PageTypeController.php
- **Classificação dos campos:** Utilização confirmada: 10

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 9 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `page_type_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para page_type (inferido) *(inferido)* | 35 | 9 | formulário/validação, view, controller, $fillable, relação | **Utilização confirmada** |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 5 | 6 | view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `label` | varchar(255) | não | — | — | Etiqueta/rótulo (inferido) *(inferido)* | 7 | 1 | view, service, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `type` | enum(text,textarea,image,file,boolean,select,repeater,gallery,wysiwyg,radio,page,date,datetime) | não | `'text'` | — | Tipo (inferido) *(inferido)* | 6 | 1 | view, service, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `options` | json | sim | — | — | Opções (JSON) (inferido) *(inferido)* | 3 | 1 | view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `is_required` | tinyint(1) | não | `false` | — | Indicador booleano: required (inferido) *(inferido)* | 4 | 1 | view, controller | **Utilização confirmada** |
| `order` | int | não | `0` | — | Ordem de apresentação (inferido) *(inferido)* | 2 | 1 | view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `page_types`

- **Finalidade:** CMS: tipos de página. *(inferida do nome/colunas)*
- **Model:** `PageType` (app/Models/PageType.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** fields() hasMany PageTypeField::class; pages() hasMany Page::class
- **Migrations:** criada em `2025_06_21`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (3): NewsV3Controller.php, PageController.php, PageTypeController.php; commands (1): GenerateSitemap.php; seeders (2): MigrateNewsToNewsArticlesSeeder.php, NewsArticlesSeeder.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 2; ex.: app/Http/Controllers/PageController.php:49, app/Http/Controllers/PageController.php:172
- **Classificação dos campos:** Utilização confirmada: 4

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 50 | 0 | seeder, view, controller | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 25 | 8 | formulário/validação, seeder, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 3 | 0 | seeder, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 2 | 0 | comando | **Utilização confirmada**; Eloquent/framework |

### `pages`

- **Finalidade:** CMS: páginas editáveis (slug, hierarquia). *(inferida do nome/colunas)*
- **Model:** `Page` (app/Models/Page.php)
- **PK:** id
- **Índices:** UNIQUE(slug)
- **FKs / constraints:** FK(page_type_id) -> page_types.id ON DELETE cascade; FK(parent_id) -> pages.id ON DELETE cascade
- **Relações Eloquent:** pageType() belongsTo PageType::class; contents() hasMany PageContent::class; menu() belongsTo Menu::class, 'page_id'; parent() belongsTo Page::class, 'parent_id'; children() hasMany Page::class, 'parent_id'; seo() morphOne SeoMeta::class, 'seoable'
- **Migrations:** criada em `2025_06_21`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (13): NewsV3Controller.php, ConsignmentController.php, ImportController.php, LegalizationController.php, PageController.php, SellingController.php…; commands (1): GenerateSitemap.php; views (62): edit.blade.php, about.blade.php, form.blade.php, index.blade.php, profit.blade.php, form.blade.php…; seeders (2): MigrateNewsToNewsArticlesSeeder.php, NewsArticlesSeeder.php; scripts/scraper externo (4): autoscout_client.py, cli.py, config.py, scrape.py; routes (2): frontend.php, web.php; config (2): dompdf.php, permissions.php; outros app (1): AppServiceProvider.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 116; ex.: app/Models/PageType.php:24, app/Http/Controllers/_MenuController.php:38, app/Http/Controllers/_MenuController.php:39, app/Http/Controllers/_MenuController.php:105
- **Classificação dos campos:** Utilização confirmada: 8

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 275 | 2 | seeder, script, view, controller | **Utilização confirmada**; Eloquent/framework |
| `page_type_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para page_type (inferido) *(inferido)* | 36 | 10 | formulário/validação, seeder, view, controller, $fillable, relação | **Utilização confirmada** |
| `title` | varchar(255) | não | — | — | Título (inferido) *(inferido)* | 160 | 23 | formulário/validação, seeder, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `slug` | varchar(255) | não | — | UNIQUE(slug) | Slug (identificador em URL) (inferido) *(inferido)* | 53 | 20 | formulário/validação, comando, seeder, view, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `is_active` | tinyint(1) | não | `true` | — | Ativo (inferido) *(inferido)* | 2 | 2 | seeder, script, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela); escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 48 | 0 | seeder, view, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 16 | 0 | comando, view | **Utilização confirmada**; Eloquent/framework |
| `parent_id` | bigint unsigned | sim | — | FK | Item pai (hierarquia) (inferido) *(inferido)* | 29 | 11 | formulário/validação, view, controller, model, $fillable, relação | **Utilização confirmada** |

### `partners`

- **Finalidade:** Parceiros do negócio, parte exibidos no site público (logo, url, show_on_site). *(inferida do nome/colunas)*
- **Model:** `Partner` (app/Models/Partner.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `2025_02_27`; 3 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (6): ExpenseV2Controller.php, PartnerV2Controller.php, ExpenseController.php, VehiclesController.php, PartnerController.php, VehicleController.php; services (1): MovementService.php; commands (1): GenerateImageThumbnails.php; views (11): form.blade.php, form.blade.php, form.blade.php, form.blade.php, index.blade.php, form.blade.php…; seeders (1): PartnersTableSeeder.php; routes (2): frontend.php, web.php; config (1): permissions.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 74; ex.: app/Http/Controllers/ExpenseController.php:56, app/Http/Controllers/ExpenseController.php:57, app/Http/Controllers/ExpenseController.php:71, app/Http/Controllers/ExpenseController.php:81
- **Classificação dos campos:** Utilização confirmada: 15

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 87 | 2 | view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 70 | 7 | formulário/validação, seeder, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `phone` | varchar(255) | sim | — | — | Telefone (inferido) *(inferido)* | 8 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `address` | text | sim | — | — | Morada (inferido) *(inferido)* | 7 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `postal_code` | varchar(255) | sim | — | — | Código postal (inferido) *(inferido)* | 5 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `city` | varchar(255) | sim | — | — | Cidade (inferido) *(inferido)* | 8 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `country` | varchar(255) | sim | — | — | País (inferido) *(inferido)* | 8 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `email` | varchar(255) | sim | — | — | Email (inferido) *(inferido)* | 8 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `vat` | varchar(255) | sim | — | — | NIF (inferido) *(inferido)* | 16 | 13 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada** |
| `contact_name` | varchar(255) | sim | — | — | Campo de domínio «contact_name» (finalidade não documentada; inferido pelo nome) *(inferido)* | 16 | 13 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 12 | 0 | view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 3 | 0 | view | **Utilização confirmada**; Eloquent/framework |
| `image` | varchar(255) | sim | — | — | Imagem (inferido) *(inferido)* | 23 | 7 | formulário/validação, comando, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `url` | varchar(255) | sim | — | — | URL (inferido) *(inferido)* | 7 | 4 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `show_on_site` | tinyint(1) | não | `false` | — | Indicador de apresentação: on_site (inferido) *(inferido)* | 5 | 5 | formulário/validação, view, controller, $fillable, $casts | **Utilização confirmada** |

### `password_reset_tokens`

- **Finalidade:** Tokens de reposição de palavra-passe (tabela standard do scaffolding Laravel). *(inferida do nome/colunas)*
- **Estado:** tabela de framework; sem rotas de reposição de palavra-passe encontradas.
- **Model:** sem model dedicado
- **PK:** email
- **Índices:** apenas PK/FKs
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `0001_01_01`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 2; ex.: config/auth.php:98, config/auth.php:108
- **Classificação dos campos:** Utilização desconhecida/externa: 3

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `email` | varchar(255) | não | — | PK | Email (inferido) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; sem rotas de reposição de palavra-passe encontradas; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `token` | varchar(255) | não | — | — | Token (inferido) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; sem rotas de reposição de palavra-passe encontradas; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização desconhecida/externa**; sem rotas de reposição de palavra-passe encontradas; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |

### `permissions`

- **Finalidade:** Permissões (spatie/laravel-permission). *(inferida do nome/colunas)*
- **Model:** sem model dedicado
- **PK:** id
- **Índices:** UNIQUE(name,guard_name)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `2025_08_26`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 222; ex.: app/Providers/AuthServiceProvider.php:26, app/Providers/AuthServiceProvider.php:42, app/Providers/AuthServiceProvider.php:44, app/Providers/AuthServiceProvider.php:58
- **Classificação dos campos:** Utilização confirmada: 5

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 6 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | UNIQUE(name,guard_name) | Nome (inferido) *(inferido)* | 6 | 1 | formulário/validação, view | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões); nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `guard_name` | varchar(255) | não | — | UNIQUE(name,guard_name) | Guard do Laravel (inferido) *(inferido)* | 1 | 2 | controller | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 1 | 0 | view | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 1 | 0 | view | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |

### `pre_leads`

- **Finalidade:** Pré-leads (contactos iniciais, ex.: WhatsApp) antes de serem convertidos em leads. *(inferida do nome/colunas)*
- **Model:** `PreLead` (app/Models/PreLead.php)
- **PK:** id
- **Índices:** INDEX(phone)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `2026_08_18`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (2): PreLeadController.php, WhatsAppWebhookController.php
- **Classificação dos campos:** Utilização confirmada: 7

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 52 | 0 | API, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | sim | — | — | Nome (inferido) *(inferido)* | 27 | 4 | formulário/validação, API, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `phone` | varchar(255) | não | — | INDEX(phone) | Telefone (inferido) *(inferido)* | 26 | 2 | API, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `message` | text | sim | — | — | Mensagem (inferido) *(inferido)* | 4 | 1 | API, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `status` | varchar(255) | não | `'pendente'` | — | Estado (inferido) *(inferido)* | 1 | 2 | API, controller, $fillable | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 11 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `proposal_attribute_values`

- **Finalidade:** Valores de atributos associados a cotações (legado). *(inferida do nome/colunas)*
- **Estado:** legado: atributos por cotação.
- **Model:** `ProposalAttributeValue` (app/Models/ProposalAttributeValue.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(proposal_id) -> proposals.id ON DELETE cascade; FK(attribute_id) -> vehicle_attributes.id ON DELETE cascade
- **Relações Eloquent:** proposal() belongsTo Proposal::class; attribute() belongsTo VehicleAttribute::class, 'attribute_id'
- **Migrations:** criada em `2025_06_17`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): ProposalController.php; services (1): ProposalService.php
- **Classificação dos campos:** Utilização confirmada: 6

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 31 | 0 | service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `proposal_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para proposal (inferido) *(inferido)* | 32 | 14 | formulário/validação, observer, view, service, controller, model | **Utilização confirmada**; legado: atributos por cotação |
| `attribute_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para attribute (inferido) *(inferido)* | 11 | 10 | service, controller, model, $fillable, relação | **Utilização confirmada**; legado: atributos por cotação |
| `value` | varchar(255) | não | — | — | Valor (inferido) *(inferido)* | 37 | 11 | view, service, controller, model, $fillable | **Utilização confirmada**; legado: atributos por cotação; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 2 | 0 | controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `proposals`

- **Finalidade:** Cotações (propostas) de importação enviadas aos clientes, com custos e viatura proposta; têm página pública e código. *(inferida do nome/colunas)*
- **Model:** `Proposal` (app/Models/Proposal.php)
- **PK:** id
- **Índices:** UNIQUE(proposal_code)
- **FKs / constraints:** FK(client_id) -> clients.id ON DELETE cascade
- **Relações Eloquent:** client() belongsTo Client::class; attributeValues() hasMany ProposalAttributeValue::class; seo() morphOne SeoMeta::class, 'seoable'
- **Migrations:** criada em `2025_03_06`; 4 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (22): AngariadorAdminController.php, AngariadorController.php, ClientV2Controller.php, ConvertedProposalV2Controller.php, DashboardV2Controller.php, ExportController.php…; services (8): ConvertedProposalService.php, GlobalSearchService.php, ImportOpportunityService.php, MobileDashboardService.php, ProposalAcceptanceService.php, ProposalService.php…; commands (1): RecalculateConvertedTranches.php; exports (1): ClientExport.php; observers (1): ProposalObserver.php; views (58): show.blade.php, index.blade.php, show.blade.php, _fields.blade.php, create.blade.php, index.blade.php…; mail/notificações (3): AlternativeQuoteRequestedMail.php, ProposalSentMail.php, NewFormProposalNotification.php; routes (1): web.php; config (1): permissions.php; outros app (2): VehicleFuel.php, AppServiceProvider.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 544; ex.: app/Enums/VehicleFuel.php:38, app/Models/ConvertedProposal.php:171, app/Models/Proposal.php:66, app/Models/Client.php:152
- **Classificação dos campos:** Utilização confirmada: 33

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 488 | 1 | comando, mail, observer, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `client_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para client (inferido) *(inferido)* | 91 | 31 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `brand` | varchar(255) | sim | — | — | Marca (inferido) *(inferido)* | 183 | 23 | formulário/validação, comando, mail, observer, view, service | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `model` | varchar(255) | sim | — | — | Modelo (inferido) *(inferido)* | 131 | 15 | formulário/validação, mail, observer, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `version` | varchar(255) | sim | — | — | Versão (inferido) *(inferido)* | 72 | 19 | formulário/validação, mail, observer, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `year` | year | sim | — | — | Ano (inferido) *(inferido)* | 47 | 18 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `mileage` | int | sim | — | — | Quilometragem (inferido) *(inferido)* | 16 | 10 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `engine_capacity` | float | sim | — | — | Cilindrada (inferido) *(inferido)* | 20 | 10 | formulário/validação, script, view, service, controller, $fillable | **Utilização confirmada** |
| `co2` | float | sim | — | — | Emissões de CO2 (inferido) *(inferido)* | 38 | 15 | formulário/validação, view, service, controller, $fillable | **Utilização confirmada** |
| `fuel` | varchar(50) | sim | — | — | Combustível (inferido) *(inferido)* | 58 | 17 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `notes` | text | sim | — | — | Notas internas (inferido) *(inferido)* | 31 | 15 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `value` | float | sim | — | — | Valor (inferido) *(inferido)* | 126 | 25 | formulário/validação, observer, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `transport_cost` | float | não | — | — | Custo de transporte (inferido) *(inferido)* | 27 | 11 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `ipo_cost` | float | sim | — | — | Custo de inspeção periódica (IPO) (inferido) *(inferido)* | 31 | 12 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `imt_cost` | float | sim | — | — | Custo IMT (inferido) *(inferido)* | 31 | 12 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `registration_cost` | float | sim | — | — | Custo de registo (inferido) *(inferido)* | 31 | 12 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `isv_cost` | float | sim | — | — | Custo de ISV (inferido) *(inferido)* | 35 | 12 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `license_plate_cost` | float | sim | — | — | Custo das matrículas (inferido) *(inferido)* | 27 | 11 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `inspection_commission_cost` | float | sim | — | — | Comissão de inspeção (inferido) *(inferido)* | 36 | 12 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `commission_cost` | float | sim | — | — | Comissão (inferido) *(inferido)* | 36 | 12 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `proposed_car_mileage` | int | sim | — | — | Campo de domínio «proposed_car_mileage» (finalidade não documentada; inferido pelo nome) *(inferido)* | 19 | 12 | formulário/validação, view, service, controller, $fillable | **Utilização confirmada** |
| `proposed_car_year_month` | varchar(255) | sim | — | — | Campo de domínio «proposed_car_year_month» (finalidade não documentada; inferido pelo nome) *(inferido)* | 19 | 11 | formulário/validação, view, service, controller, $fillable | **Utilização confirmada** |
| `proposed_car_value` | float | sim | — | — | Campo de domínio «proposed_car_value» (finalidade não documentada; inferido pelo nome) *(inferido)* | 38 | 12 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `proposed_car_notes` | text | sim | — | — | Campo de domínio «proposed_car_notes» (finalidade não documentada; inferido pelo nome) *(inferido)* | 6 | 8 | formulário/validação, view, service, controller, $fillable | **Utilização confirmada** |
| `proposed_car_features` | text | sim | — | — | Campo de domínio «proposed_car_features» (finalidade não documentada; inferido pelo nome) *(inferido)* | 16 | 10 | formulário/validação, view, service, controller, $fillable | **Utilização confirmada** |
| `images` | json | sim | — | — | Imagens (JSON) (inferido) *(inferido)* | 63 | 12 | formulário/validação, script, view, service, controller, model | **Utilização confirmada** |
| `url` | varchar(255) | sim | — | — | URL (inferido) *(inferido)* | 44 | 31 | formulário/validação, mail, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `status` | enum(,Pendente,Enviada,Aprovada,Reprovada,Sem resposta) | sim | — | — | Estado (inferido) *(inferido)* | 248 | 91 | formulário/validação, observer, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `other_links` | varchar(255) | sim | — | — | Outros links (inferido) *(inferido)* | 16 | 7 | view, service, controller, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 188 | 2 | export, observer, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 10 | 0 | observer, view, controller | **Utilização confirmada**; Eloquent/framework |
| `proposal_code` | varchar(12) | sim | — | UNIQUE(proposal_code) | Código público da cotação (inferido) *(inferido)* | 34 | 2 | observer, view, service, controller, $fillable | **Utilização confirmada** |
| `iuc_cost` | decimal(8,2) | sim | — | — | Custo de IUC (inferido) *(inferido)* | 14 | 5 | formulário/validação, view, service, controller, $fillable | **Utilização confirmada** |

### `push_subscriptions`

- **Finalidade:** Subscrições de notificações push (laravel-notification-channels/webpush) por utilizador. *(inferida do nome/colunas)*
- **Model:** sem model dedicado
- **PK:** id
- **Índices:** INDEX(subscribable_type,subscribable_id); UNIQUE(endpoint)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `2026_09_07`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** config (1): webpush.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: config/webpush.php:27
- **Classificação dos campos:** Utilização confirmada: 9

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `subscribable_type` | varchar | não | — | INDEX(subscribable_type,subscribable_id) | Campo de domínio «subscribable_type» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote laravel-notification-channels/webpush; trait HasPushSubscriptions em User, PushSubscriptionController |
| `subscribable_id` | bigint unsigned | não | — | INDEX(subscribable_type,subscribable_id) | Chave estrangeira / referência para subscribable (inferido) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote laravel-notification-channels/webpush; trait HasPushSubscriptions em User, PushSubscriptionController |
| `endpoint` | varchar(255) | não | — | UNIQUE(endpoint) | Campo de domínio «endpoint» (finalidade não documentada; inferido pelo nome) *(inferido)* | 2 | 4 | controller | **Utilização confirmada**; pacote laravel-notification-channels/webpush; trait HasPushSubscriptions em User, PushSubscriptionController |
| `public_key` | varchar(255) | sim | — | — | Campo de domínio «public_key» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote laravel-notification-channels/webpush; trait HasPushSubscriptions em User, PushSubscriptionController |
| `auth_token` | varchar(255) | sim | — | — | Campo de domínio «auth_token» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote laravel-notification-channels/webpush; trait HasPushSubscriptions em User, PushSubscriptionController |
| `content_encoding` | varchar(255) | sim | — | — | Campo de domínio «content_encoding» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote laravel-notification-channels/webpush; trait HasPushSubscriptions em User, PushSubscriptionController |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; pacote laravel-notification-channels/webpush; trait HasPushSubscriptions em User, PushSubscriptionController |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; pacote laravel-notification-channels/webpush; trait HasPushSubscriptions em User, PushSubscriptionController |

### `radar_equipment`

- **Finalidade:** Equipamentos normalizados do radar. *(inferida do nome/colunas)*
- **Model:** `RadarEquipment` (app/Models/RadarEquipment.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** aliases() hasMany RadarEquipmentAlias::class; listings() belongsToMany RadarListing::class, 'radar_listing_equipment'; searches() belongsToMany RadarSearch::class, 'radar_search_equipment'
- **Migrations:** criada em `2026_09_04`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (2): RadarController.php, RadarEquipmentController.php; services (1): RadarValueScoreService.php; scripts/scraper externo (1): db.py
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 8; ex.: app/Models/RadarEquipment.php:9, app/Http/Controllers/Admin/RadarEquipmentController.php:60, app/Http/Controllers/Admin/RadarController.php:281, app/Http/Controllers/Admin/RadarController.php:282
- **Classificação dos campos:** Apenas escrita: 1, Utilização confirmada: 5

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 41 | 6 | script, service, controller | **Utilização confirmada**; Eloquent/framework |
| `label` | varchar(255) | não | — | — | Etiqueta/rótulo (inferido) *(inferido)* | 5 | 9 | script, service, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `slug` | varchar(255) | não | — | — | Slug (identificador em URL) (inferido) *(inferido)* | 0 | 4 | script, controller, $fillable | **Apenas escrita**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela); escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `show_in_filters` | tinyint(1) | não | `false` | — | Indicador de apresentação: in_filters (inferido) *(inferido)* | 6 | 4 | script, view, controller, $fillable, $casts | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 4 | script | **Utilização confirmada**; Eloquent/framework; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 6 | script | **Utilização confirmada**; Eloquent/framework; escrito/lido por scraper Python externo (sem passar pelo Laravel) |

### `radar_equipment_aliases`

- **Finalidade:** Mapeamento de chaves brutas de cada fonte para equipamentos normalizados. *(inferida do nome/colunas)*
- **Model:** `RadarEquipmentAlias` (app/Models/RadarEquipmentAlias.php)
- **PK:** id
- **Índices:** UNIQUE(source,raw_key)
- **FKs / constraints:** FK(radar_equipment_id) -> radar_equipment.id ON DELETE cascade
- **Relações Eloquent:** equipment() belongsTo RadarEquipment::class, 'radar_equipment_id'
- **Migrations:** criada em `2026_09_04`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): RadarEquipmentController.php; scripts/scraper externo (1): db.py
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 3; ex.: app/Models/RadarEquipmentAlias.php:9, scarperAutoscout/scraper/db.py:291, scarperAutoscout/scraper/db.py:306
- **Classificação dos campos:** Utilização confirmada: 7

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 23 | 3 | script, controller | **Utilização confirmada**; Eloquent/framework |
| `radar_equipment_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para radar_equipment (inferido) *(inferido)* | 6 | 7 | script, controller, model, $fillable, relação | **Utilização confirmada** |
| `source` | varchar(255) | não | — | UNIQUE(source,raw_key) | Origem (inferido) *(inferido)* | 9 | 14 | script, view, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela); escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `raw_key` | varchar(255) | não | — | UNIQUE(source,raw_key) | Campo de domínio «raw_key» (finalidade não documentada; inferido pelo nome) *(inferido)* | 9 | 5 | script, view, controller, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 4 | script | **Utilização confirmada**; Eloquent/framework; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 6 | script | **Utilização confirmada**; Eloquent/framework; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `raw_label` | varchar(255) | sim | — | — | Campo de domínio «raw_label» (finalidade não documentada; inferido pelo nome) *(inferido)* | 6 | 5 | script, controller, $fillable | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |

### `radar_listing_equipment`

- **Finalidade:** Pivot anúncio-equipamento. *(inferida do nome/colunas)*
- **Model:** sem model dedicado
- **PK:** —
- **Índices:** PRIMARY(radar_listing_id,radar_equipment_id)
- **FKs / constraints:** FK(radar_listing_id) -> radar_listings.id ON DELETE cascade; FK(radar_equipment_id) -> radar_equipment.id ON DELETE cascade
- **Relações Eloquent:** —
- **Migrations:** criada em `2026_09_04`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): RadarEquipmentController.php; scripts/scraper externo (1): db.py
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 6; ex.: app/Models/RadarListing.php:44, app/Models/RadarEquipment.php:24, app/Http/Controllers/Admin/RadarEquipmentController.php:70, app/Http/Controllers/Admin/RadarEquipmentController.php:73
- **Classificação dos campos:** Utilização confirmada: 2

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `radar_listing_id` | bigint unsigned | não | — | PRIMARY(radar_listing_id,radar_equipment_id), FK | Chave estrangeira / referência para radar_listing (inferido) *(inferido)* | 3 | 2 | script, controller | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `radar_equipment_id` | bigint unsigned | não | — | PRIMARY(radar_listing_id,radar_equipment_id), FK | Chave estrangeira / referência para radar_equipment (inferido) *(inferido)* | 5 | 7 | script, controller | **Utilização confirmada** |

### `radar_listings`

- **Finalidade:** Anúncios recolhidos pelo radar, com preço, vendedor, duplicados e classificação de versão. *(inferida do nome/colunas)*
- **Model:** `RadarListing` (app/Models/RadarListing.php)
- **PK:** id
- **Índices:** UNIQUE(external_id); UNIQUE(source,external_id)
- **FKs / constraints:** FK(radar_search_id) -> radar_searches.id ON DELETE set null; FK(duplicate_of_listing_id) -> radar_listings.id ON DELETE set null
- **Relações Eloquent:** search() belongsTo RadarSearch::class, 'radar_search_id'; priceHistory() hasMany RadarPriceHistory::class; duplicateOf() belongsTo self::class, 'duplicate_of_listing_id'; equipment() belongsToMany RadarEquipment::class, 'radar_listing_equipment'
- **Migrations:** criada em `2026_09_01`; 6 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): RadarController.php; services (1): RadarTrimPriceAiService.php; commands (1): ClassifyRadarListingTrims.php; scripts/scraper externo (1): db.py
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 11; ex.: app/Http/Controllers/Admin/RadarController.php:538, app/Services/RadarTrimPriceAiService.php:26, scarperAutoscout/scraper/db.py:106, scarperAutoscout/scraper/db.py:111
- **Classificação dos campos:** Utilização confirmada: 29

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 45 | 5 | comando, script, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `external_id` | varchar(255) | não | — | UNIQUE(external_id), UNIQUE(source,external_id) | ID no site de origem (inferido) *(inferido)* | 11 | 4 | comando, script, view, model, $fillable | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `radar_search_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para radar_search (inferido) *(inferido)* | 10 | 3 | comando, script, controller, model, $fillable, relação | **Utilização confirmada** |
| `make` | varchar(255) | sim | — | — | Campo de domínio «make» (finalidade não documentada; inferido pelo nome) *(inferido)* | 69 | 26 | formulário/validação, comando, script, view, service, controller | **Utilização confirmada** |
| `model` | varchar(255) | sim | — | — | Modelo (inferido) *(inferido)* | 18 | 20 | comando, script, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `version` | varchar(255) | sim | — | — | Versão (inferido) *(inferido)* | 4 | 3 | script, view, service, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `first_registration_year` | smallint unsigned | sim | — | — | Ano da primeira matrícula (inferido) *(inferido)* | 18 | 4 | script, view, service, controller, model, $fillable | **Utilização confirmada** |
| `mileage_km` | int unsigned | sim | — | — | Quilometragem (km) (inferido) *(inferido)* | 17 | 3 | script, view, service, controller, model, $fillable | **Utilização confirmada** |
| `power_hp` | int unsigned | sim | — | — | Potência (cv) (inferido) *(inferido)* | 8 | 3 | script, view, service, model, $fillable | **Utilização confirmada** |
| `fuel` | varchar(255) | sim | — | — | Combustível (inferido) *(inferido)* | 4 | 4 | script, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `gearbox` | varchar(255) | sim | — | — | Caixa de velocidades (inferido) *(inferido)* | 2 | 3 | script, view, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela); escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `body_type` | varchar(255) | sim | — | — | Tipo de carroçaria (inferido) *(inferido)* | 10 | 5 | script, controller, model, $fillable | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `seller_type` | varchar(255) | sim | — | — | Campo de domínio «seller_type» (finalidade não documentada; inferido pelo nome) *(inferido)* | 5 | 3 | script, model, $fillable | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `seller_name` | varchar(255) | sim | — | — | Nome do vendedor (inferido) *(inferido)* | 6 | 3 | script, view, model, $fillable | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `location_zip` | varchar(255) | sim | — | — | Campo de domínio «location_zip» (finalidade não documentada; inferido pelo nome) *(inferido)* | 6 | 2 | script, model, $fillable | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `location_city` | varchar(255) | sim | — | — | Campo de domínio «location_city» (finalidade não documentada; inferido pelo nome) *(inferido)* | 6 | 2 | script, model, $fillable | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `price_eur` | int unsigned | sim | — | — | Preço em EUR (inferido) *(inferido)* | 25 | 6 | script, view, service, controller, model, $fillable | **Utilização confirmada** |
| `url` | text | sim | — | — | URL (inferido) *(inferido)* | 5 | 2 | script, view, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `first_seen_at` | timestamp | não | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 6 | 1 | script, view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `last_seen_at` | timestamp | não | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 1 | 2 | script, model, $fillable, $casts | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `removed_at` | timestamp | sim | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 13 | 3 | comando, script, service, controller, model, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 4 | script | **Utilização confirmada**; Eloquent/framework; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 6 | script | **Utilização confirmada**; Eloquent/framework; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `source` | varchar(255) | não | `'autoscout24'` | UNIQUE(source,external_id) | Origem (inferido) *(inferido)* | 15 | 14 | script, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `include_in_average` | tinyint(1) | não | `true` | — | Entra no cálculo da média de preços | 5 | 1 | view, service, controller, model, $fillable, $casts | **Utilização confirmada** |
| `seller_phone` | varchar(255) | sim | — | — | Telefone do vendedor (inferido) *(inferido)* | 7 | 2 | script, view, model, $fillable | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `duplicate_of_listing_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para duplicate_of_listing (inferido) *(inferido)* | 8 | 3 | script, service, controller, model, $fillable, relação | **Utilização confirmada** |
| `trim_price_multiplier` | decimal(6,3) | sim | — | — | Multiplicador de preço por versão (classificação IA) | 5 | 1 | comando, view, service, model, $fillable, $casts | **Utilização confirmada** |
| `trim_classified_at` | timestamp | sim | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 3 | 1 | comando, model, $fillable, $casts | **Utilização confirmada** |

### `radar_price_history`

- **Finalidade:** Histórico de preços dos anúncios do radar. *(inferida do nome/colunas)*
- **Model:** `RadarPriceHistory` (app/Models/RadarPriceHistory.php)
- **PK:** id
- **Índices:** INDEX(radar_listing_id,scraped_at)
- **FKs / constraints:** FK(radar_listing_id) -> radar_listings.id ON DELETE cascade
- **Relações Eloquent:** listing() belongsTo RadarListing::class
- **Migrations:** criada em `2026_09_01`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): RadarController.php; scripts/scraper externo (1): db.py
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 4; ex.: app/Models/RadarPriceHistory.php:9, app/Http/Controllers/Admin/RadarController.php:537, scarperAutoscout/scraper/db.py:106, scarperAutoscout/scraper/db.py:171
- **Classificação dos campos:** Utilização confirmada: 4

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 24 | 5 | script, controller | **Utilização confirmada**; Eloquent/framework |
| `radar_listing_id` | bigint unsigned | não | — | INDEX(radar_listing_id,scraped_at), FK | Chave estrangeira / referência para radar_listing (inferido) *(inferido)* | 3 | 2 | script, controller, $fillable, relação | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `price_eur` | int unsigned | não | — | — | Preço em EUR (inferido) *(inferido)* | 24 | 6 | script, view, service, controller, model, $fillable | **Utilização confirmada** |
| `scraped_at` | timestamp | não | — | INDEX(radar_listing_id,scraped_at) | Data/hora de evento (inferido pelo nome) *(inferido)* | 1 | 1 | script, model, $fillable, $casts | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |

### `radar_search_equipment`

- **Finalidade:** Pivot pesquisa-equipamento (filtros). *(inferida do nome/colunas)*
- **Model:** sem model dedicado
- **PK:** —
- **Índices:** PRIMARY(radar_search_id,radar_equipment_id)
- **FKs / constraints:** FK(radar_search_id) -> radar_searches.id ON DELETE cascade; FK(radar_equipment_id) -> radar_equipment.id ON DELETE cascade
- **Relações Eloquent:** —
- **Migrations:** criada em `2026_09_04`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): RadarEquipmentController.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 5; ex.: app/Models/RadarSearch.php:39, app/Models/RadarSearch.php:42, app/Models/RadarEquipment.php:29, app/Http/Controllers/Admin/RadarEquipmentController.php:77
- **Classificação dos campos:** Utilização confirmada: 2

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `radar_search_id` | bigint unsigned | não | — | PRIMARY(radar_search_id,radar_equipment_id), FK | Chave estrangeira / referência para radar_search (inferido) *(inferido)* | 8 | 3 | comando, script, controller | **Utilização confirmada** |
| `radar_equipment_id` | bigint unsigned | não | — | PRIMARY(radar_search_id,radar_equipment_id), FK | Chave estrangeira / referência para radar_equipment (inferido) *(inferido)* | 5 | 7 | script, controller | **Utilização confirmada** |

### `radar_search_runs`

- **Finalidade:** Execuções do scraper por pesquisa (estado, contagens, erros). *(inferida do nome/colunas)*
- **Model:** `RadarSearchRun` (app/Models/RadarSearchRun.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(radar_search_id) -> radar_searches.id ON DELETE cascade
- **Relações Eloquent:** search() belongsTo RadarSearch::class, 'radar_search_id'
- **Migrations:** criada em `2026_09_01`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** services (1): AutoscoutScraperRunner.php; scripts/scraper externo (1): db.py
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 4; ex.: app/Services/AutoscoutScraperRunner.php:13, scarperAutoscout/scraper/db.py:78, scarperAutoscout/scraper/db.py:84, scarperAutoscout/scraper/db.py:97
- **Classificação dos campos:** Apenas escrita: 2, Utilização confirmada: 7

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 14 | 3 | script | **Utilização confirmada**; Eloquent/framework; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `radar_search_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para radar_search (inferido) *(inferido)* | 9 | 3 | comando, script, controller, model, $fillable, relação | **Utilização confirmada** |
| `started_at` | timestamp | não | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 6 | 1 | script, view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `finished_at` | timestamp | sim | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 0 | 1 | script, $fillable, $casts | **Apenas escrita**; escrito pelo scraper Python (db.py:98); não lido pelo código Laravel; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `status` | varchar(255) | não | `'running'` | — | Estado (inferido) *(inferido)* | 0 | 4 | script, $fillable | **Apenas escrita**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela); escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `listings_found` | int unsigned | não | `0` | — | Campo de domínio «listings_found» (finalidade não documentada; inferido pelo nome) *(inferido)* | 3 | 3 | script, view, $fillable | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `pages_scraped` | int unsigned | não | `0` | — | Campo de domínio «pages_scraped» (finalidade não documentada; inferido pelo nome) *(inferido)* | 5 | 3 | script, view, $fillable | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `error_message` | text | sim | — | — | Campo de domínio «error_message» (finalidade não documentada; inferido pelo nome) *(inferido)* | 6 | 3 | script, view, $fillable | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `source` | varchar(255) | não | `'autoscout24'` | — | Origem (inferido) *(inferido)* | 9 | 14 | script, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela); escrito/lido por scraper Python externo (sem passar pelo Laravel) |

### `radar_searches`

- **Finalidade:** Pesquisas do radar de anúncios (AutoScout24/Standvirtual/Carmine), com filtros, URLs base e custo de importação. *(inferida do nome/colunas)*
- **Model:** `RadarSearch` (app/Models/RadarSearch.php)
- **PK:** id
- **Índices:** UNIQUE(name)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** listings() hasMany RadarListing::class; runs() hasMany RadarSearchRun::class; latestRun() hasOne RadarSearchRun::class; equipment() belongsToMany RadarEquipment::class, 'radar_search_equipment'
- **Migrations:** criada em `2026_09_01`; 6 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): RadarController.php; services (2): AutoscoutScraperRunner.php, RadarValueScoreService.php; commands (1): ClassifyRadarListingTrims.php; scripts/scraper externo (2): cli.py, db.py
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 17; ex.: app/Http/Controllers/Admin/RadarController.php:66, app/Http/Controllers/Admin/RadarController.php:153, app/Http/Controllers/Admin/RadarController.php:445, app/Http/Controllers/Admin/RadarController.php:483
- **Classificação dos campos:** Utilização confirmada: 14

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 42 | 6 | comando, script, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | UNIQUE(name) | Nome (inferido) *(inferido)* | 40 | 6 | comando, script, view, service, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `make` | varchar(255) | sim | — | — | Campo de domínio «make» (finalidade não documentada; inferido pelo nome) *(inferido)* | 68 | 26 | formulário/validação, comando, script, view, service, controller | **Utilização confirmada** |
| `model` | varchar(255) | sim | — | — | Modelo (inferido) *(inferido)* | 21 | 19 | comando, script, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `filters` | json | sim | — | — | Campo de domínio «filters» (finalidade não documentada; inferido pelo nome) *(inferido)* | 69 | 5 | script, view, service, controller, $fillable, $casts | **Utilização confirmada** |
| `base_url` | text | não | — | — | Campo de domínio «base_url» (finalidade não documentada; inferido pelo nome) *(inferido)* | 32 | 5 | script, view, controller, $fillable | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 4 | script | **Utilização confirmada**; Eloquent/framework; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 6 | script | **Utilização confirmada**; Eloquent/framework; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `standvirtual_base_url` | text | sim | — | — | Campo de domínio «standvirtual_base_url» (finalidade não documentada; inferido pelo nome) *(inferido)* | 11 | 4 | script, view, controller, $fillable | **Utilização confirmada** |
| `carmine_base_url` | text | sim | — | — | Campo de domínio «carmine_base_url» (finalidade não documentada; inferido pelo nome) *(inferido)* | 11 | 4 | script, view, controller, $fillable | **Utilização confirmada** |
| `import_cost_eur` | decimal(10,2) | sim | — | — | Valor monetário (inferido) *(inferido)* | 7 | 3 | formulário/validação, view, service, controller, $fillable, $casts | **Utilização confirmada** |
| `is_active` | tinyint(1) | não | `true` | — | Ativo (inferido) *(inferido)* | 5 | 2 | script, view, controller, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `new_listings_seen_at` | timestamp | sim | `CURRENT_TIMESTAMP` | — | Marca o último momento em que o utilizador viu novos anúncios | 2 | 1 | controller, $fillable, $casts | **Utilização confirmada** |
| `run_requested_at` | timestamp | sim | — | — | Pedido de execução manual (lido e limpo pelo scraper Python) | 3 | 1 | script, service, $fillable, $casts | **Utilização confirmada**; escrito/lido por scraper Python externo (sem passar pelo Laravel) |

### `recommendation_posts`

- **Finalidade:** Publicações de recomendação (posts de viaturas para redes sociais) com galeria. *(inferida do nome/colunas)*
- **Model:** `RecommendationPost` (app/Models/RecommendationPost.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `2026_09_01`; 3 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): SocialPostController.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: app/Http/Controllers/Admin/SocialPostController.php:40
- **Classificação dos campos:** Utilização confirmada: 18

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 6 | 4 | formulário/validação, view, controller | **Utilização confirmada**; Eloquent/framework |
| `brand` | varchar(255) | não | — | — | Marca (inferido) *(inferido)* | 2 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `model` | varchar(255) | não | — | — | Modelo (inferido) *(inferido)* | 2 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `version` | varchar(255) | sim | — | — | Versão (inferido) *(inferido)* | 1 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `mileage` | int unsigned | sim | — | — | Quilometragem (inferido) *(inferido)* | 1 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `power` | int unsigned | sim | — | — | Potência (inferido) *(inferido)* | 1 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `fuel` | varchar(255) | sim | — | — | Combustível (inferido) *(inferido)* | 1 | 1 | view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `year` | smallint unsigned | sim | — | — | Ano (inferido) *(inferido)* | 1 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `equipment` | json | sim | — | — | Campo de domínio «equipment» (finalidade não documentada; inferido pelo nome) *(inferido)* | 17 | 1 | script, view, service, controller, $fillable, $casts | **Utilização confirmada** |
| `price` | decimal(10,2) | sim | — | — | Preço (inferido) *(inferido)* | 3 | 2 | formulário/validação, view, controller, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `savings` | decimal(10,2) | sim | — | — | Campo de domínio «savings» (finalidade não documentada; inferido pelo nome) *(inferido)* | 11 | 8 | formulário/validação, view, controller, $fillable, $casts | **Utilização confirmada** |
| `url` | varchar(255) | sim | — | — | URL (inferido) *(inferido)* | 1 | 2 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `image` | varchar(255) | sim | — | — | Imagem (inferido) *(inferido)* | 11 | 3 | view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 2 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `gallery_photos` | json | sim | — | — | Campo de domínio «gallery_photos» (finalidade não documentada; inferido pelo nome) *(inferido)* | 5 | 2 | view, controller, $fillable, $casts | **Utilização confirmada** |
| `gallery_layouts` | json | sim | — | — | Campo de domínio «gallery_layouts» (finalidade não documentada; inferido pelo nome) *(inferido)* | 2 | 3 | formulário/validação, view, controller, $fillable, $casts | **Utilização confirmada** |
| `gallery_photos_per_slide` | tinyint unsigned | não | `3` | — | Campo de domínio «gallery_photos_per_slide» (finalidade não documentada; inferido pelo nome) *(inferido)* | 3 | 3 | formulário/validação, view, controller, $fillable, $casts | **Utilização confirmada** |

### `role_has_permissions`

- **Finalidade:** Pivot spatie: permissões por role. *(inferida do nome/colunas)*
- **Model:** sem model dedicado
- **PK:** permission_id, role_id
- **Índices:** PRIMARY(permission_id,role_id)
- **FKs / constraints:** FK(permission_id) -> permissions.id ON DELETE cascade; FK(role_id) -> roles.id ON DELETE cascade
- **Relações Eloquent:** —
- **Migrations:** criada em `2025_08_26`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: config/permission.php:71
- **Classificação dos campos:** Utilização confirmada: 2

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `permission_id` | bigint unsigned | não | — | PK, PRIMARY(permission_id,role_id), FK | Chave estrangeira / referência para permission (inferido) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |
| `role_id` | bigint unsigned | não | — | PK, PRIMARY(permission_id,role_id), FK | Chave estrangeira / referência para role (inferido) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |

### `roles`

- **Finalidade:** Roles (spatie/laravel-permission). *(inferida do nome/colunas)*
- **Model:** sem model dedicado
- **PK:** id
- **Índices:** UNIQUE(name,guard_name)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `2025_08_26`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 111; ex.: app/Models/User.php:105, app/Models/User.php:107, app/Http/Middleware/RestrictAngariadorArea.php:24, app/Http/Middleware/AuthorizeResource.php:47
- **Classificação dos campos:** Utilização confirmada: 5

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 7 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | UNIQUE(name,guard_name) | Nome (inferido) *(inferido)* | 26 | 2 | formulário/validação, view, controller | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões); nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `guard_name` | varchar(255) | não | — | UNIQUE(name,guard_name) | Guard do Laravel (inferido) *(inferido)* | 1 | 2 | controller | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 2 | 0 | view | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 1 | 0 | view | **Utilização confirmada**; pacote spatie/laravel-permission (Gate/roles/permissões) |

### `sales`

- **Finalidade:** Vendas de viaturas a clientes, com margens, IVA e retomas (trade-in). *(inferida do nome/colunas)*
- **Model:** `Sale` (app/Models/Sale.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(vehicle_id) -> vehicles.id ON DELETE cascade; FK(client_id) -> clients.id ON DELETE cascade; FK(v3_vehicle_id) -> v3_vehicles.id ON DELETE set null
- **Relações Eloquent:** vehicle() belongsTo Vehicle::class; v3Vehicle() belongsTo \App\Models\V3Vehicle::class, 'v3_vehicle_id'; client() belongsTo Client::class
- **Migrations:** criada em `2025_03_01`; 4 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (9): DashboardV2Controller.php, FinancialDashboardController.php, GlobalSearchController.php, MovementController.php, SaleV2Controller.php, V3VehicleController.php…; services (3): GlobalSearchService.php, MovementService.php, ReportDataService.php; observers (1): SaleObserver.php; views (15): form.blade.php, show.blade.php, dashboard.blade.php, index.blade.php, index.blade.php, form.blade.php…; routes (1): web.php; config (1): permissions.php; outros app (1): AppServiceProvider.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 81; ex.: app/Models/V3Vehicle.php:135, app/Http/Controllers/ClientController.php:69, app/Http/Controllers/ClientController.php:70, app/Http/Controllers/SaleController.php:19
- **Classificação dos campos:** Apenas escrita: 3, Utilização confirmada: 20

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 183 | 1 | observer, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `vehicle_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para vehicle (inferido) *(inferido)* | 15 | 10 | formulário/validação, view, controller, model, $fillable, relação | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `client_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para client (inferido) *(inferido)* | 34 | 16 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `sale_date` | date | não | — | — | Data da venda (inferido) *(inferido)* | 26 | 9 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `sale_price` | decimal(10,2) | não | — | — | Preço de venda (inferido) *(inferido)* | 30 | 11 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `vat_rate` | enum(Sem iva,23%) | não | — | — | Taxa de IVA (inferido) *(inferido)* | 22 | 21 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `payment_method` | enum(Financiamento,Pronto pagamento) | não | — | — | Método de pagamento (inferido) *(inferido)* | 24 | 19 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `observation` | text | sim | — | — | Observações (inferido) *(inferido)* | 15 | 13 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `gross_margin` | decimal(10,2) | sim | — | — | Campo de domínio «gross_margin» (finalidade não documentada; inferido pelo nome) *(inferido)* | 33 | 9 | view, service, controller, $fillable | **Utilização confirmada** |
| `gross_profitability` | int | sim | — | — | Campo de domínio «gross_profitability» (finalidade não documentada; inferido pelo nome) *(inferido)* | 6 | 6 | view, service, controller, $fillable | **Utilização confirmada** |
| `net_margin` | decimal(10,2) | sim | — | — | Campo de domínio «net_margin» (finalidade não documentada; inferido pelo nome) *(inferido)* | 24 | 11 | view, service, controller, $fillable | **Utilização confirmada** |
| `net_profitability` | int | sim | — | — | Campo de domínio «net_profitability» (finalidade não documentada; inferido pelo nome) *(inferido)* | 12 | 6 | view, service, controller, $fillable | **Utilização confirmada** |
| `vat_paid` | decimal(10,2) | sim | — | — | Campo de domínio «vat_paid» (finalidade não documentada; inferido pelo nome) *(inferido)* | 9 | 7 | view, service, controller, $fillable | **Utilização confirmada** |
| `vat_deducible_purchase` | decimal(10,2) | sim | — | — | Campo de domínio «vat_deducible_purchase» (finalidade não documentada; inferido pelo nome) *(inferido)* | 4 | 6 | view, controller, $fillable | **Utilização confirmada** |
| `vat_settle_sale` | decimal(10,2) | sim | — | — | Campo de domínio «vat_settle_sale» (finalidade não documentada; inferido pelo nome) *(inferido)* | 8 | 7 | view, service, controller, model, $fillable | **Utilização confirmada** |
| `totalCost` | decimal(10,2) | sim | — | — | Campo de domínio «totalCost» (finalidade não documentada; inferido pelo nome) *(inferido)* | 6 | 5 | view, controller, $fillable | **Utilização confirmada** |
| `totalExpenses` | decimal(10,2) | sim | — | — | Campo de domínio «totalExpenses» (finalidade não documentada; inferido pelo nome) *(inferido)* | 7 | 6 | view, service, controller, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 73 | 0 | view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 4 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `has_trade_in` | tinyint(1) | não | `false` | — | Indicador booleano: trade_in (inferido) *(inferido)* | 0 | 2 | controller, $fillable | **Apenas escrita** |
| `trade_in_vehicle_id` | int | sim | — | — | Chave estrangeira / referência para trade_in_vehicle (inferido) *(inferido)* | 0 | 2 | controller, $fillable | **Apenas escrita**; sem FK constraint na BD (integridade só aplicacional) |
| `trade_in_value` | decimal(10,2) | sim | — | — | Campo de domínio «trade_in_value» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 2 | controller, $fillable | **Apenas escrita** |
| `v3_vehicle_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para v3_vehicle (inferido) *(inferido)* | 62 | 29 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |

### `seller_contacts`

- **Finalidade:** Contactos dos vendedores, com campos normalizados para deteção de duplicados. *(inferida do nome/colunas)*
- **Model:** `SellerContact` (app/Models/SellerContact.php)
- **PK:** id
- **Índices:** INDEX(email_normalized); INDEX(email_domain); INDEX(phone_normalized); INDEX(whatsapp_normalized)
- **FKs / constraints:** FK(seller_id) -> sellers.id ON DELETE cascade
- **Relações Eloquent:** seller() belongsTo Seller::class; opportunities() hasMany ImportOpportunity::class; vehicles() hasMany V3Vehicle::class
- **Migrations:** criada em `2026_09_30`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (2): SellerContactController.php, V3VehicleController.php; services (2): SellerDuplicateDetector.php, SellerService.php; requests (1): ImportOpportunityRequest.php; views (1): _vehicle-fields.blade.php; outros app (1): SellerMatches.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 2; ex.: app/Http/Requests/ImportOpportunityRequest.php:60, app/Http/Controllers/Admin/V3VehicleController.php:207
- **Classificação dos campos:** Utilização confirmada: 16

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 67 | 2 | view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `seller_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para seller (inferido) *(inferido)* | 14 | 7 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 25 | 2 | view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `role` | varchar(255) | sim | — | — | Campo de domínio «role» (finalidade não documentada; inferido pelo nome) *(inferido)* | 22 | 14 | formulário/validação, script, view, service, controller, model | **Utilização confirmada** |
| `email` | varchar(255) | sim | — | — | Email (inferido) *(inferido)* | 17 | 5 | view, service, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `phone` | varchar(50) | sim | — | — | Telefone (inferido) *(inferido)* | 11 | 4 | view, service, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `whatsapp` | varchar(50) | sim | — | — | Campo de domínio «whatsapp» (finalidade não documentada; inferido pelo nome) *(inferido)* | 24 | 6 | view, service, controller, model, $fillable | **Utilização confirmada** |
| `notes` | text | sim | — | — | Notas internas (inferido) *(inferido)* | 5 | 2 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `is_primary` | tinyint(1) | não | `false` | — | Indicador booleano: primary (inferido) *(inferido)* | 10 | 5 | view, service, model, $fillable, $casts | **Utilização confirmada** |
| `is_active` | tinyint(1) | não | `true` | — | Ativo (inferido) *(inferido)* | 15 | 8 | view, service, controller, model, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `email_normalized` | varchar(255) | sim | — | INDEX(email_normalized) | Valor normalizado para pesquisa/deteção de duplicados (inferido) *(inferido)* | 5 | 1 | comando, service, model | **Utilização confirmada** |
| `email_domain` | varchar(255) | sim | — | INDEX(email_domain) | Campo de domínio «email_domain» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 1 | service, model | **Utilização confirmada** |
| `phone_normalized` | varchar(20) | sim | — | INDEX(phone_normalized) | Valor normalizado para pesquisa/deteção de duplicados (inferido) *(inferido)* | 9 | 1 | view, service, model | **Utilização confirmada** |
| `whatsapp_normalized` | varchar(20) | sim | — | INDEX(whatsapp_normalized) | Valor normalizado para pesquisa/deteção de duplicados (inferido) *(inferido)* | 5 | 1 | view, service, model | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 1 | 0 | model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `seller_domains`

- **Finalidade:** Domínios associados a um vendedor (deteção de duplicados). *(inferida do nome/colunas)*
- **Model:** `SellerDomain` (app/Models/SellerDomain.php)
- **PK:** id
- **Índices:** INDEX(domain); UNIQUE(seller_id,domain)
- **FKs / constraints:** FK(seller_id) -> sellers.id ON DELETE cascade
- **Relações Eloquent:** seller() belongsTo Seller::class
- **Migrations:** criada em `2026_09_30`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Classificação dos campos:** Utilização confirmada: 5

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 1 | 0 | model | **Utilização confirmada**; Eloquent/framework |
| `seller_id` | bigint unsigned | não | — | UNIQUE(seller_id,domain), FK | Chave estrangeira / referência para seller (inferido) *(inferido)* | 10 | 4 | view, service, controller, $fillable, relação | **Utilização confirmada** |
| `domain` | varchar(255) | não | — | INDEX(domain), UNIQUE(seller_id,domain) | Campo de domínio «domain» (finalidade não documentada; inferido pelo nome) *(inferido)* | 8 | 2 | view, service, model, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `sellers`

- **Finalidade:** Vendedores/stands estrangeiros (nome, país, site, marcas). *(inferida do nome/colunas)*
- **Model:** `Seller` (app/Models/Seller.php)
- **PK:** id
- **Índices:** INDEX(is_active)
- **FKs / constraints:** FK(created_by) -> users.id ON DELETE set null
- **Relações Eloquent:** domains() hasMany SellerDomain::class; contacts() hasMany SellerContact::class; primaryContact() hasOne SellerContact::class; brands() belongsToMany Brand::class, 'brand_seller'; vehicles() hasMany V3Vehicle::class; opportunities() hasMany ImportOpportunity::class; creator() belongsTo User::class, 'created_by'
- **Migrations:** criada em `2026_09_30`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (3): SellerContactController.php, SellerController.php, V3VehicleController.php; services (2): SellerDuplicateDetector.php, SellerService.php; requests (2): ImportOpportunityRequest.php, SellerRequest.php; views (10): _vehicle-fields.blade.php, show.blade.php, _picker.blade.php, _quick-modals.blade.php, _scripts.blade.php, form.blade.php…; routes (1): web.php; config (1): permissions.php; outros app (1): SellerMatches.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 92; ex.: app/Models/Brand.php:24, app/Support/SellerMatches.php:73, app/Http/Requests/SellerRequest.php:35, app/Http/Requests/ImportOpportunityRequest.php:56
- **Classificação dos campos:** Utilização confirmada: 10

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 130 | 3 | view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 100 | 5 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `website` | varchar(255) | sim | — | — | Campo de domínio «website» (finalidade não documentada; inferido pelo nome) *(inferido)* | 14 | 2 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `country` | char(2) | sim | — | — | País (inferido) *(inferido)* | 22 | 11 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `address` | text | sim | — | — | Morada (inferido) *(inferido)* | 2 | 1 | formulário/validação, view, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `notes` | text | sim | — | — | Notas internas (inferido) *(inferido)* | 10 | 3 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `is_active` | tinyint(1) | não | `true` | INDEX(is_active) | Ativo (inferido) *(inferido)* | 27 | 10 | view, service, controller, model, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_by` | bigint unsigned | sim | — | FK | Campo de domínio «created_by» (finalidade não documentada; inferido pelo nome) *(inferido)* | 3 | 3 | service, model, $fillable, relação | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 5 | 0 | view, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 1 | 0 | view | **Utilização confirmada**; Eloquent/framework |

### `seo_metas`

- **Finalidade:** Meta SEO polimórfica (seoable) para páginas, notícias, viaturas, etc. *(inferida do nome/colunas)*
- **Model:** `SeoMeta` (app/Models/SeoMeta.php)
- **PK:** id
- **Índices:** INDEX(seoable_type,seoable_id)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** seoable() morphTo 
- **Migrations:** criada em `2025_09_16`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** outros app (1): HasSeo.php
- **Classificação dos campos:** Apenas leitura: 5, Utilização confirmada: 15

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 1 | 0 | model | **Utilização confirmada**; Eloquent/framework |
| `seoable_type` | varchar | não | — | INDEX(seoable_type,seoable_id) | Campo de domínio «seoable_type» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; morphOne/morphTo (HasSeo, Page, Proposal, SeoMeta::seoable) |
| `seoable_id` | bigint unsigned | não | — | INDEX(seoable_type,seoable_id) | Chave estrangeira / referência para seoable (inferido) *(inferido)* | 0 | 0 | — | **Utilização confirmada**; morphOne/morphTo (HasSeo, Page, Proposal, SeoMeta::seoable); sem FK constraint na BD (integridade só aplicacional) |
| `title` | varchar(255) | sim | — | — | Título (inferido) *(inferido)* | 1 | 0 | model, $fillable | **Apenas leitura**; só em $fillable (escrita possível, sem escrita encontrada); em $fillable (escrita possível por mass-assignment) mas sem escrita encontrada; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `meta_description` | text | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 14 | 2 | view, controller, $fillable | **Utilização confirmada** |
| `meta_image` | varchar(255) | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 4 | 1 | view, controller, $fillable | **Utilização confirmada** |
| `meta_keywords` | varchar(255) | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 4 | 2 | view, controller, $fillable | **Utilização confirmada** |
| `meta_secundary_keywords` | varchar(255) | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 2 | 1 | view, controller, $fillable | **Utilização confirmada** |
| `canonical_url` | varchar(255) | sim | — | — | Campo de domínio «canonical_url» (finalidade não documentada; inferido pelo nome) *(inferido)* | 4 | 2 | view, controller, $fillable | **Utilização confirmada** |
| `og_title` | varchar(255) | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 5 | 2 | view, controller, $fillable | **Utilização confirmada** |
| `og_description` | text | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 5 | 2 | view, controller, $fillable | **Utilização confirmada** |
| `og_image` | varchar(255) | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 5 | 1 | view, controller, $fillable | **Utilização confirmada** |
| `og_url` | varchar(255) | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 4 | 1 | view, controller, $fillable | **Utilização confirmada** |
| `og_type` | varchar(255) | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 8 | 2 | view, controller, $fillable | **Utilização confirmada** |
| `twitter_card` | varchar(255) | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 3 | 0 | view, $fillable | **Apenas leitura**; só em $fillable (escrita possível, sem escrita encontrada); em $fillable (escrita possível por mass-assignment) mas sem escrita encontrada |
| `twitter_title` | varchar(255) | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 3 | 0 | view, $fillable | **Apenas leitura**; só em $fillable (escrita possível, sem escrita encontrada); em $fillable (escrita possível por mass-assignment) mas sem escrita encontrada |
| `twitter_description` | text | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 3 | 0 | view, $fillable | **Apenas leitura**; só em $fillable (escrita possível, sem escrita encontrada); em $fillable (escrita possível por mass-assignment) mas sem escrita encontrada |
| `twitter_image` | varchar(255) | sim | — | — | Campo de SEO / partilha social (inferido) *(inferido)* | 4 | 0 | view, $fillable | **Apenas leitura**; só em $fillable (escrita possível, sem escrita encontrada); em $fillable (escrita possível por mass-assignment) mas sem escrita encontrada |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 1 | 0 | model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `sessions`

- **Finalidade:** Sessões de utilizador (driver database; tabela standard do scaffolding). *(inferida do nome/colunas)*
- **Estado:** tabela de framework; SESSION_DRIVER local = file.
- **Model:** sem model dedicado
- **PK:** id
- **Índices:** INDEX(user_id); INDEX(last_activity)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `0001_01_01`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** nenhum ficheiro referencia a tabela ou o model
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 9; ex.: app/Http/Controllers/Admin/AngariadorAdminController.php:186, app/Http/Controllers/Admin/AngariadorAdminController.php:224, config/session.php:58, config/session.php:62
- **Classificação dos campos:** Utilização confirmada: 1, Utilização desconhecida/externa: 5

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | varchar(255) | não | — | PK | Chave primária | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `user_id` | bigint unsigned | sim | — | INDEX(user_id) | Chave estrangeira / referência para user (inferido) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; SESSION_DRIVER local = file; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado).; sem FK constraint na BD (integridade só aplicacional) |
| `ip_address` | varchar(45) | sim | — | — | Campo de domínio «ip_address» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 0 | view | **Utilização desconhecida/externa**; SESSION_DRIVER local = file; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `user_agent` | text | sim | — | — | Campo de domínio «user_agent» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 0 | — | **Utilização desconhecida/externa**; SESSION_DRIVER local = file; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |
| `payload` | longtext | não | — | — | Campo de domínio «payload» (finalidade não documentada; inferido pelo nome) *(inferido)* | 12 | 0 | script | **Utilização desconhecida/externa**; SESSION_DRIVER local = file; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado).; escrito/lido por scraper Python externo (sem passar pelo Laravel) |
| `last_activity` | int | não | — | INDEX(last_activity) | Campo de domínio «last_activity» (finalidade não documentada; inferido pelo nome) *(inferido)* | 2 | 0 | export | **Utilização desconhecida/externa**; SESSION_DRIVER local = file; tabela de scaffolding. Em produção pode ser diferente (.env remoto não verificado). |

### `settings`

- **Finalidade:** Definições do site/aplicação em chave-valor (título, label, tipo, valor). *(inferida do nome/colunas)*
- **Model:** `Setting` (app/Models/Setting.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `2025_09_01`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (6): ProposalV2Controller.php, SettingV2Controller.php, ClientController.php, CostSimulatorController.php, ProposalController.php, SettingController.php; services (5): AngariadorContractService.php, ContractService.php, Modelo1460PdfService.php, ServiceTranches.php, ThumbnailService.php; commands (2): GenerateImageThumbnails.php, RecalculateConvertedTranches.php; views (33): form.blade.php, index.blade.php, contract_service.blade.php, cost_simulator_result.blade.php, financing_documents.blade.php, import_form_confirmation.blade.php…; routes (1): web.php; config (9): auth.php, cors.php, dompdf.php, lfm.php, mail.php, permission.php…; public js (1): app.js; outros app (2): BlockInProduction.php, AppServiceProvider.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 174; ex.: app/Http/Controllers/ProposalController.php:837, app/Http/Controllers/SettingController.php:13, app/Http/Controllers/SettingController.php:14, app/Http/Controllers/SettingController.php:19
- **Classificação dos campos:** Utilização confirmada: 7

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 97 | 0 | comando, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `title` | varchar(255) | não | — | — | Título (inferido) *(inferido)* | 75 | 11 | view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `label` | varchar(255) | não | — | — | Etiqueta/rótulo (inferido) *(inferido)* | 126 | 8 | formulário/validação, comando, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `type` | varchar(255) | não | `'text'` | — | Tipo (inferido) *(inferido)* | 32 | 4 | view, controller, $fillable | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `value` | text | sim | — | — | Valor (inferido) *(inferido)* | 113 | 16 | comando, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 12 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 4 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |

### `status_proposal_history`

- **Finalidade:** Histórico de mudanças de estado das cotações convertidas. *(inferida do nome/colunas)*
- **Model:** `StatusProposalHistory` (app/Models/StatusProposalHistory.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** proposal() belongsTo ConvertedProposal::class, 'converted_proposal_id'
- **Migrations:** criada em `2025_09_03`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): ProposalController.php; services (2): ConvertedProposalService.php, ProposalAcceptanceService.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: app/Models/StatusProposalHistory.php:10
- **Classificação dos campos:** Utilização confirmada: 6

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 35 | 0 | service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `converted_proposal_id` | bigint unsigned | não | — | — | Chave estrangeira / referência para converted_proposal (inferido) *(inferido)* | 4 | 5 | service, controller, model, $fillable, relação | **Utilização confirmada**; sem FK constraint na BD (integridade só aplicacional) |
| `old_status` | varchar(255) | sim | — | — | Campo de domínio «old_status» (finalidade não documentada; inferido pelo nome) *(inferido)* | 9 | 4 | view, service, controller, model, $fillable | **Utilização confirmada** |
| `new_status` | varchar(255) | não | — | — | Campo de domínio «new_status» (finalidade não documentada; inferido pelo nome) *(inferido)* | 14 | 4 | view, service, controller, model, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 5 | 0 | view, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `submodel_cars`

- **Finalidade:** Submodelos por marca/modelo (taxonomia). *(inferida do nome/colunas)*
- **Model:** `SubmodelCar` (app/Models/SubmodelCar.php)
- **PK:** id
- **Índices:** UNIQUE(brand_id,model_id,name)
- **FKs / constraints:** FK(brand_id) -> brands.id ON DELETE cascade; FK(model_id) -> model_cars.id ON DELETE cascade
- **Relações Eloquent:** brand() belongsTo Brand::class; model() hasMany ModelCar::class
- **Migrations:** criada em `2025_05_13`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** commands (1): ImportCarData.php
- **Classificação dos campos:** Utilização confirmada: 6

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 1 | 0 | comando | **Utilização confirmada**; Eloquent/framework |
| `brand_id` | bigint unsigned | não | — | UNIQUE(brand_id,model_id,name), FK | Chave estrangeira / referência para brand (inferido) *(inferido)* | 2 | 1 | comando, model, $fillable, relação | **Utilização confirmada** |
| `model_id` | bigint unsigned | não | — | UNIQUE(brand_id,model_id,name), FK | Chave estrangeira / referência para model (inferido) *(inferido)* | 1 | 0 | model, $fillable, relação | **Utilização confirmada** |
| `name` | varchar(255) | não | — | UNIQUE(brand_id,model_id,name) | Nome (inferido) *(inferido)* | 2 | 2 | comando, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `suppliers`

- **Finalidade:** Fornecedores de viaturas e serviços (usados em compras de viaturas e cotações de transporte). *(inferida do nome/colunas)*
- **Model:** `Supplier` (app/Models/Supplier.php)
- **PK:** id
- **Índices:** UNIQUE(email)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `2025_02_27`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (6): SupplierV2Controller.php, TransportQuoteController.php, V3VehicleController.php, VehicleV2Controller.php, SupplierController.php, VehicleController.php; views (12): form.blade.php, index.blade.php, form.blade.php, index.blade.php, map.blade.php, form.blade.php…; seeders (1): SuppliersTableSeeder.php; routes (1): web.php; config (1): permissions.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 81; ex.: app/Http/Controllers/VehicleController.php:45, app/Http/Controllers/VehicleController.php:54, app/Http/Controllers/VehicleController.php:82, app/Http/Controllers/VehicleController.php:167
- **Classificação dos campos:** Utilização confirmada: 16

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 118 | 1 | view, controller, model | **Utilização confirmada**; Eloquent/framework |
| `type` | enum(,Particular,Empresa) | não | — | — | Tipo (inferido) *(inferido)* | 28 | 8 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `company_name` | varchar(255) | sim | — | — | Campo de domínio «company_name» (finalidade não documentada; inferido pelo nome) *(inferido)* | 24 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada** |
| `contact_name` | varchar(255) | não | — | — | Campo de domínio «contact_name» (finalidade não documentada; inferido pelo nome) *(inferido)* | 16 | 13 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada** |
| `address` | text | sim | — | — | Morada (inferido) *(inferido)* | 6 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `postal_code` | varchar(255) | sim | — | — | Código postal (inferido) *(inferido)* | 5 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `city` | varchar(255) | sim | — | — | Cidade (inferido) *(inferido)* | 9 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `country` | varchar(255) | sim | — | — | País (inferido) *(inferido)* | 12 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `email` | varchar(255) | não | — | UNIQUE(email) | Email (inferido) *(inferido)* | 9 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `phone` | varchar(255) | sim | — | — | Telefone (inferido) *(inferido)* | 8 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `vat` | varchar(255) | sim | — | — | NIF (inferido) *(inferido)* | 16 | 13 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada** |
| `identification_number` | varchar(255) | sim | — | — | Nº do documento de identificação (inferido) *(inferido)* | 21 | 13 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada** |
| `identification_number_validity` | date | sim | — | — | Campo de domínio «identification_number_validity» (finalidade não documentada; inferido pelo nome) *(inferido)* | 5 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 5 | 0 | view, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 2 | 0 | view | **Utilização confirmada**; Eloquent/framework |
| `notes` | text | sim | — | — | Notas internas (inferido) *(inferido)* | 5 | 4 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |

### `tasks`

- **Finalidade:** Tarefas e lembretes ligados a utilizadores e viaturas. *(inferida do nome/colunas)*
- **Model:** `Task` (app/Models/Task.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(user_id) -> users.id ON DELETE set null; FK(vehicle_id) -> vehicles.id ON DELETE cascade; FK(v3_vehicle_id) -> v3_vehicles.id ON DELETE set null
- **Relações Eloquent:** user() belongsTo User::class; vehicle() belongsTo Vehicle::class; v3Vehicle() belongsTo V3Vehicle::class, 'v3_vehicle_id'
- **Migrations:** criada em `2026_04_14`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (4): DashboardV2Controller.php, LegalizationController.php, TaskController.php, TaskController.php; services (2): MobileDashboardService.php, TaskService.php; commands (2): SendTaskReminders.php, Kernel.php; views (13): form.blade.php, index.blade.php, dashboard.blade.php, index.blade.php, form.blade.php, index.blade.php…; seeders (1): TaskSeeder.php; mail/notificações (1): TaskAssignedNotification.php; routes (1): web.php; config (2): app.php, permissions.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 126; ex.: app/Http/Controllers/Admin/TaskController.php:23, app/Http/Controllers/Admin/TaskController.php:62, app/Http/Controllers/Admin/TaskController.php:79, app/Http/Controllers/Admin/TaskController.php:80
- **Classificação dos campos:** Utilização confirmada: 13

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 99 | 2 | comando, seeder, mail, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `title` | varchar(255) | não | — | — | Título (inferido) *(inferido)* | 36 | 23 | formulário/validação, comando, seeder, mail, view, service | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `description` | text | sim | — | — | Descrição (inferido) *(inferido)* | 17 | 15 | formulário/validação, seeder, view, service, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | não | `CURRENT_TIMESTAMP` | — | Data de criação (Eloquent) | 48 | 0 | view, service, controller, $casts | **Utilização confirmada**; Eloquent/framework |
| `due_date` | date | não | — | — | Data limite (inferido) *(inferido)* | 35 | 15 | formulário/validação, seeder, mail, view, service, controller | **Utilização confirmada** |
| `reminder_date` | date | sim | — | — | Data do lembrete (inferido) *(inferido)* | 13 | 14 | formulário/validação, seeder, view, service, controller, model | **Utilização confirmada** |
| `user_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para user (inferido) *(inferido)* | 11 | 11 | formulário/validação, seeder, view, service, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `user_name` | varchar(255) | sim | — | — | Nome do utilizador (quando não é utilizador do sistema) (inferido) *(inferido)* | 14 | 5 | formulário/validação, comando, seeder, view, service, controller | **Utilização confirmada** |
| `vehicle_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para vehicle (inferido) *(inferido)* | 1 | 5 | seeder, controller, $fillable, relação | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `status` | enum(pendente,em_progresso,concluida,cancelada) | não | `'pendente'` | — | Estado (inferido) *(inferido)* | 62 | 25 | formulário/validação, seeder, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `reminder_sent` | tinyint(1) | não | `false` | — | Lembrete já enviado (inferido) *(inferido)* | 5 | 1 | comando, view, model, $fillable, $casts | **Utilização confirmada** |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 6 | 0 | view, controller, $casts | **Utilização confirmada**; Eloquent/framework |
| `v3_vehicle_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para v3_vehicle (inferido) *(inferido)* | 50 | 27 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |

### `testimonials`

- **Finalidade:** Testemunhos/avaliações (incl. Google Reviews) apresentados no site. *(inferida do nome/colunas)*
- **Model:** `Testimonial` (app/Models/Testimonial.php)
- **PK:** id
- **Índices:** UNIQUE(google_review_id)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** —
- **Migrations:** criada em `2026_04_28`; 4 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (2): TestimonialController.php, VehiclesController.php; commands (1): SyncGoogleReviews.php; views (5): create.blade.php, edit.blade.php, index.blade.php, index.blade.php, admin-v2.blade.php; routes (1): web.php; config (1): permissions.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 38; ex.: app/Http/Controllers/Admin/TestimonialController.php:13, app/Http/Controllers/Admin/TestimonialController.php:16, app/Http/Controllers/Admin/TestimonialController.php:17, app/Http/Controllers/Admin/TestimonialController.php:18
- **Classificação dos campos:** Utilização confirmada: 10

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 33 | 2 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 52 | 5 | formulário/validação, comando, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `rating` | decimal(3,1) | não | `5.0` | — | Classificação (estrelas) (inferido) *(inferido)* | 12 | 5 | formulário/validação, comando, view, controller, $fillable, $casts | **Utilização confirmada** |
| `comment` | text | não | — | — | Comentário (inferido) *(inferido)* | 10 | 5 | formulário/validação, comando, view, controller, $fillable | **Utilização confirmada** |
| `origin` | varchar(255) | não | `'google'` | — | Origem (inferido) *(inferido)* | 66 | 27 | formulário/validação, export, comando, view, service, controller | **Utilização confirmada** |
| `published` | tinyint(1) | não | `false` | — | Publicado (inferido) *(inferido)* | 13 | 7 | formulário/validação, comando, view, controller, $fillable, $casts | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 7 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `review_date` | date | sim | — | — | Data da avaliação (inferido) *(inferido)* | 8 | 5 | formulário/validação, comando, view, controller, $fillable, $casts | **Utilização confirmada** |
| `google_review_id` | varchar(255) | sim | — | UNIQUE(google_review_id) | ID da review no Google (sincronização) | 1 | 1 | comando, $fillable | **Utilização confirmada**; sem FK constraint na BD (integridade só aplicacional) |

### `transport_quotes`

- **Finalidade:** Cotações de transporte de viaturas por fornecedor, origem/destino e preço. *(inferida do nome/colunas)*
- **Model:** `TransportQuote` (app/Models/TransportQuote.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(supplier_id) -> suppliers.id ON DELETE cascade
- **Relações Eloquent:** supplier() belongsTo Supplier::class
- **Migrations:** criada em `2026_01_22`; 3 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): TransportQuoteController.php; views (1): form.blade.php
- **Classificação dos campos:** Utilização confirmada: 18

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 6 | 1 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `brand` | varchar(255) | não | — | — | Marca (inferido) *(inferido)* | 15 | 5 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `model` | varchar(255) | não | — | — | Modelo (inferido) *(inferido)* | 10 | 5 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `origin_latitude` | decimal(10,7) | sim | — | — | Dados de origem/destino (inferido) *(inferido)* | 7 | 3 | formulário/validação, view, controller, $fillable, $casts | **Utilização confirmada** |
| `origin_longitude` | decimal(10,7) | sim | — | — | Dados de origem/destino (inferido) *(inferido)* | 7 | 3 | formulário/validação, view, controller, $fillable, $casts | **Utilização confirmada** |
| `destination_city` | varchar(255) | não | `'Oliveira de Azeméis'` | — | Dados de origem/destino (inferido) *(inferido)* | 1 | 1 | controller, $fillable | **Utilização confirmada** |
| `destination_country` | varchar(255) | não | `'Portugal'` | — | Dados de origem/destino (inferido) *(inferido)* | 1 | 1 | controller, $fillable | **Utilização confirmada** |
| `destination_latitude` | decimal(10,7) | sim | — | — | Dados de origem/destino (inferido) *(inferido)* | 1 | 1 | controller, $fillable, $casts | **Utilização confirmada** |
| `destination_longitude` | decimal(10,7) | sim | — | — | Dados de origem/destino (inferido) *(inferido)* | 1 | 1 | controller, $fillable, $casts | **Utilização confirmada** |
| `supplier_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para supplier (inferido) *(inferido)* | 16 | 15 | formulário/validação, view, controller, $fillable, relação | **Utilização confirmada** |
| `price` | decimal(10,2) | não | — | — | Preço (inferido) *(inferido)* | 5 | 4 | formulário/validação, view, controller, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `quote_date` | date | não | `now()` | — | Data da cotação (inferido) *(inferido)* | 8 | 4 | formulário/validação, view, controller, $fillable, $casts | **Utilização confirmada** |
| `estimated_delivery_days` | int | sim | — | — | Campo de domínio «estimated_delivery_days» (finalidade não documentada; inferido pelo nome) *(inferido)* | 5 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `observations` | text | sim | — | — | Observações (inferido) *(inferido)* | 3 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `car_type` | varchar(255) | sim | — | — | Campo de domínio «car_type» (finalidade não documentada; inferido pelo nome) *(inferido)* | 14 | 7 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `origin_location` | varchar(255) | sim | — | — | Dados de origem/destino (inferido) *(inferido)* | 10 | 6 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |

### `users`

- **Finalidade:** Utilizadores do backoffice (equipa, importadores, angariadores): credenciais, perfil, dados de comissão/IBAN, contrato assinado, documentos de identificação e acesso mobile. *(inferida do nome/colunas)*
- **Model:** `User` (app/Models/User.php)
- **PK:** id
- **Índices:** UNIQUE(email); UNIQUE(referral_code)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** ownedLeads() hasMany Client::class, 'owner_id'
- **Migrations:** criada em `0001_01_01`; 7 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (20): AngariadorAdminController.php, AuditLogController.php, ConvertedProposalV2Controller.php, LeadV2Controller.php, LeadController.php, ProposalController.php…; services (16): AngariadorContractService.php, AngariadorIdentityDocuments.php, AngariadorMetricsService.php, AutoscoutTaxonomyService.php, CarmineTaxonomyService.php, ConvertedProposalService.php…; views (14): form.blade.php, index.blade.php, show.blade.php, index.blade.php, form.blade.php, form.blade.php…; seeders (2): DatabaseSeeder.php, TaskSeeder.php; scripts/scraper externo (5): autoscout_client.py, carmine_client.py, standvirtual_client.py, _testScript.py, getFromStandVirtual.py; mail/notificações (2): NewsletterPreview.php, SetPasswordMail.php; routes (2): channels.php, web.php; config (3): auth.php, dompdf.php, permissions.php; public js (1): app.js; outros app (4): Authenticate.php, PermissionService.php, AuthServiceProvider.php, PushNotifier.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 193; ex.: app/Models/ConvertedProposal.php:235, app/Http/Controllers/UserController.php:15, app/Http/Controllers/UserController.php:16, app/Http/Controllers/UserController.php:22
- **Classificação dos campos:** Apenas leitura: 1, Utilização confirmada: 20

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 192 | 1 | seeder, script, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 180 | 31 | formulário/validação, mail, script, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `last_name` | varchar(255) | não | — | — | Apelido (inferido) *(inferido)* | 40 | 20 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `email` | varchar(255) | não | — | UNIQUE(email) | Email (inferido) *(inferido)* | 57 | 33 | formulário/validação, mail, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `email_verified_at` | timestamp | sim | — | — | Data de verificação do email (inferido) *(inferido)* | 0 | 0 | $casts | **Apenas leitura**; guard Auth (remember_token); email_verified_at só em $casts, sem fluxo de verificação |
| `password` | varchar(255) | não | — | — | Hash da palavra-passe (inferido) *(inferido)* | 35 | 23 | formulário/validação, script, view, controller, model, $fillable | **Utilização confirmada** |
| `remember_token` | varchar(100) | sim | — | — | Token «lembrar-me» (inferido) *(inferido)* | 2 | 0 | model, $hidden | **Utilização confirmada**; guard Auth (remember_token); email_verified_at só em $casts, sem fluxo de verificação |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 42 | 6 | view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 4 | 1 | view, controller, model | **Utilização confirmada**; Eloquent/framework |
| `referral_code` | varchar(255) | sim | — | UNIQUE(referral_code) | Código de referência do angariador usado em links do site | 15 | 8 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `phone` | varchar(255) | sim | — | — | Telefone (inferido) *(inferido)* | 31 | 13 | formulário/validação, script, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `location` | varchar(255) | sim | — | — | Localização (inferido) *(inferido)* | 14 | 8 | formulário/validação, script, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `nif` | varchar(20) | sim | — | — | NIF (inferido) *(inferido)* | 10 | 10 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `iban` | varchar(40) | sim | — | — | IBAN (inferido) *(inferido)* | 15 | 13 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `status` | varchar(255) | não | `'aprovado'` | — | Estado da conta (aprovado, pendente...) | 94 | 52 | formulário/validação, seeder, script, view, service, controller | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `commission_fixed_value` | decimal(10,2) | sim | — | — | Comissão fixa do angariador (existia só em produção; migration de garantia) | 12 | 9 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada** |
| `contrato_assinado_path` | varchar(255) | sim | — | — | Ficheiro do contrato de angariador assinado | 8 | 2 | view, service, model, $fillable | **Utilização confirmada** |
| `contrato_assinado_em` | timestamp | sim | — | — | Campo de domínio «contrato_assinado_em» (finalidade não documentada; inferido pelo nome) *(inferido)* | 3 | 2 | view, service, model, $fillable, $casts | **Utilização confirmada** |
| `documento_frente_path` | varchar(255) | sim | — | — | Documento de identificação (frente) | 3 | 2 | service, model, $fillable | **Utilização confirmada** |
| `documento_verso_path` | varchar(255) | sim | — | — | Documento de identificação (verso) | 3 | 2 | service, model, $fillable | **Utilização confirmada** |
| `access_mobile` | tinyint(1) | não | `false` | — | Acesso à versão mobile do backoffice | 7 | 5 | formulário/validação, view, controller, $casts | **Utilização confirmada** |

### `v3_vehicle_documents`

- **Finalidade:** Documentos das viaturas V3. *(inferida do nome/colunas)*
- **Model:** `V3VehicleDocument` (app/Models/V3VehicleDocument.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(v3_vehicle_id) -> v3_vehicles.id ON DELETE cascade
- **Relações Eloquent:** vehicle() belongsTo V3Vehicle::class, 'v3_vehicle_id'
- **Migrations:** criada em `2026_05_19`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): V3VehicleController.php; services (1): VehicleInspectionService.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: app/Models/V3VehicleDocument.php:10
- **Classificação dos campos:** Utilização confirmada: 8

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 134 | 0 | seeder, observer, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `v3_vehicle_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para v3_vehicle (inferido) *(inferido)* | 59 | 27 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `tipo` | varchar(50) | sim | — | — | Tipo (inferido) *(inferido)* | 6 | 5 | service, controller, model, $fillable | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `titulo` | varchar(255) | sim | — | — | Campo de domínio «titulo» (finalidade não documentada; inferido pelo nome) *(inferido)* | 35 | 16 | formulário/validação, script, view, service, controller, model | **Utilização confirmada** |
| `nome_original` | varchar(255) | não | — | — | Nome original do ficheiro carregado (inferido) *(inferido)* | 5 | 4 | service, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `caminho` | varchar(255) | não | — | — | Caminho do ficheiro em disco (inferido) *(inferido)* | 12 | 4 | service, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 5 | 0 | view, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 2 | 0 | comando, view | **Utilização confirmada**; Eloquent/framework |

### `v3_vehicle_photos`

- **Finalidade:** Fotografias das viaturas V3 (ordem, capa, ponto focal). *(inferida do nome/colunas)*
- **Model:** `V3VehiclePhoto` (app/Models/V3VehiclePhoto.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(v3_vehicle_id) -> v3_vehicles.id ON DELETE cascade
- **Relações Eloquent:** vehicle() belongsTo V3Vehicle::class, 'v3_vehicle_id'
- **Migrations:** criada em `2026_05_19`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): V3VehicleController.php; services (1): VehicleInspectionService.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: app/Models/V3VehiclePhoto.php:11
- **Classificação dos campos:** Utilização confirmada: 9

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 136 | 0 | seeder, observer, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `v3_vehicle_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para v3_vehicle (inferido) *(inferido)* | 59 | 27 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `path` | varchar(255) | não | — | — | Caminho do ficheiro (inferido) *(inferido)* | 20 | 2 | view, service, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `order_position` | smallint unsigned | não | `0` | — | Posição/ordem (inferido) *(inferido)* | 4 | 5 | view, service, controller, model, $fillable, $casts | **Utilização confirmada** |
| `is_cover` | tinyint(1) | não | `false` | — | Foto de capa (inferido) *(inferido)* | 9 | 5 | view, service, controller, model, $fillable, $casts | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 5 | 0 | view, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 2 | 0 | comando, view | **Utilização confirmada**; Eloquent/framework |
| `focal_x` | decimal(5,2) | não | `50` | — | Ponto focal horizontal (%) (inferido) *(inferido)* | 9 | 4 | view, controller, $fillable, $casts | **Utilização confirmada** |
| `focal_y` | decimal(5,2) | não | `50` | — | Ponto focal vertical (%) (inferido) *(inferido)* | 9 | 4 | view, controller, $fillable, $casts | **Utilização confirmada** |

### `v3_vehicles`

- **Finalidade:** Viaturas V3 (fonte de verdade): dados técnicos, compra, preço de venda, estado, publicação no site, destaque na home, vendedor/cliente. *(inferida do nome/colunas)*
- **Model:** `V3Vehicle` (app/Models/V3Vehicle.php)
- **PK:** id
- **Índices:** UNIQUE(reference)
- **FKs / constraints:** FK(supplier_id) -> suppliers.id ON DELETE set null; FK(generated_from_inspection_id) -> vehicle_inspections.id ON DELETE set null; FK(seller_id) -> sellers.id ON DELETE restrict; FK(seller_contact_id) -> seller_contacts.id ON DELETE set null; FK(client_id) -> clients.id ON DELETE set null
- **Relações Eloquent:** supplier() belongsTo Supplier::class; client() belongsTo Client::class; seller() belongsTo Seller::class; sellerContact() belongsTo SellerContact::class; photos() hasMany V3VehiclePhoto::class, 'v3_vehicle_id'; documents() hasMany V3VehicleDocument::class, 'v3_vehicle_id'; legalization() hasOne Legalization::class, 'v3_vehicle_id'; exportLegalization() hasOne Legalization::class, 'v3_vehicle_id'; expenses() hasMany Expense::class, 'v3_vehicle_id'; legalizationExpenses() hasManyThrough Expense::class, Legalization::class, 'v3_vehicle_id', 'legal; sales() hasMany Sale::class, 'v3_vehicle_id'; generatedFromInspection() belongsTo VehicleInspection::class, 'generated_from_inspection_id'; attributeValues() hasMany VehicleAttributeValue::class, 'v3_vehicle_id'
- **Migrations:** criada em `2026_05_19`; 8 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (8): DashboardV2Controller.php, GlobalSearchController.php, LegalizationController.php, MovementController.php, MovementV2Controller.php, TaskController.php…; services (5): ConvertedProposalService.php, GlobalSearchService.php, MovementService.php, TaskService.php, VehicleInspectionService.php; commands (1): GenerateSitemap.php; exports (1): VehicleExport.php; views (6): create.blade.php, edit.blade.php, index.blade.php, create.blade.php, index.blade.php, tab-general.blade.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 5; ex.: app/Models/V3Vehicle.php:26, app/Http/Controllers/Admin/LegalizationController.php:93, app/Http/Controllers/Admin/LegalizationController.php:350, app/Services/TaskService.php:32
- **Classificação dos campos:** Utilização confirmada: 39

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 211 | 5 | export, seeder, observer, view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `reference` | varchar(30) | sim | — | UNIQUE(reference) | Referência interna (inferido) *(inferido)* | 68 | 5 | formulário/validação, export, comando, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `brand` | varchar(100) | sim | — | — | Marca (inferido) *(inferido)* | 156 | 12 | formulário/validação, export, comando, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `model` | varchar(100) | sim | — | — | Modelo (inferido) *(inferido)* | 142 | 13 | formulário/validação, export, comando, view, service, controller | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `version` | varchar(100) | sim | — | — | Versão (inferido) *(inferido)* | 38 | 7 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `year` | smallint unsigned | sim | — | — | Ano (inferido) *(inferido)* | 90 | 15 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `fuel` | varchar(50) | sim | — | — | Combustível (inferido) *(inferido)* | 77 | 11 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `kilometers` | int unsigned | sim | — | — | Quilómetros (inferido) *(inferido)* | 39 | 7 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `power` | smallint unsigned | sim | — | — | Potência (inferido) *(inferido)* | 12 | 5 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `cylinder_capacity` | smallint unsigned | sim | — | — | Cilindrada (cc) (inferido) *(inferido)* | 12 | 8 | formulário/validação, export, view, controller, model, $fillable | **Utilização confirmada** |
| `color` | varchar(50) | sim | — | — | Cor (inferido) *(inferido)* | 21 | 13 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `vin` | varchar(17) | sim | — | — | Número de quadro (VIN) (inferido) *(inferido)* | 12 | 4 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `registration` | varchar(20) | sim | — | — | Matrícula (inferido) *(inferido)* | 43 | 15 | formulário/validação, export, script, view, service, controller | **Utilização confirmada** |
| `manufacture_date` | date | sim | — | — | Data (inferido pelo nome) *(inferido)* | 9 | 8 | formulário/validação, export, view, controller, model, $fillable | **Utilização confirmada** |
| `register_date` | date | sim | — | — | Data (inferido pelo nome) *(inferido)* | 9 | 8 | formulário/validação, export, view, controller, model, $fillable | **Utilização confirmada** |
| `available_to_sell_date` | date | sim | — | — | Data (inferido pelo nome) *(inferido)* | 9 | 8 | formulário/validação, export, view, controller, model, $fillable | **Utilização confirmada** |
| `notes` | text | sim | — | — | Notas internas (inferido) *(inferido)* | 6 | 4 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `show_online` | tinyint(1) | não | `false` | — | Publicar no site público | 36 | 24 | formulário/validação, export, comando, view, service, controller | **Utilização confirmada** |
| `status` | varchar(20) | não | `'em_stock'` | — | Estado: em_stock, reservado, vendido | 93 | 29 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `supplier_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para supplier (inferido) *(inferido)* | 16 | 15 | formulário/validação, view, controller, $fillable, relação | **Utilização confirmada** |
| `purchase_price` | decimal(10,2) | sim | — | — | Preço de compra (inferido) *(inferido)* | 31 | 8 | formulário/validação, view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `purchase_date` | date | sim | — | — | Data de compra (inferido) *(inferido)* | 15 | 8 | formulário/validação, view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `purchase_type` | varchar(20) | sim | — | — | Regime de compra (Margem/Geral/Sem IVA) (inferido) *(inferido)* | 34 | 8 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `purchase_vat_rate` | decimal(5,2) | sim | — | — | Taxa de IVA na compra (inferido) *(inferido)* | 9 | 5 | formulário/validação, view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `purchase_vat_paid` | decimal(10,2) | sim | — | — | IVA pago na compra (inferido) *(inferido)* | 14 | 5 | formulário/validação, view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 57 | 1 | export, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 5 | 1 | comando, view, controller, model | **Utilização confirmada**; Eloquent/framework |
| `sub_model` | varchar(150) | sim | — | — | Campo de domínio «sub_model» (finalidade não documentada; inferido pelo nome) *(inferido)* | 21 | 9 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `month` | tinyint unsigned | sim | — | — | Campo de domínio «month» (finalidade não documentada; inferido pelo nome) *(inferido)* | 54 | 9 | formulário/validação, comando, view, service, controller, model | **Utilização confirmada** |
| `day` | tinyint unsigned | sim | — | — | Campo de domínio «day» (finalidade não documentada; inferido pelo nome) *(inferido)* | 11 | 3 | formulário/validação, comando, view, controller, model, $fillable | **Utilização confirmada** |
| `is_imported` | tinyint(1) | não | `false` | — | Indicador booleano: imported (inferido) *(inferido)* | 9 | 3 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `ad_text` | text | sim | — | — | Texto do anúncio (inferido) *(inferido)* | 4 | 2 | view, controller, model, $fillable | **Utilização confirmada** |
| `asking_price` | decimal(10,2) | sim | — | — | Preço de venda pedido (site e JSON-LD) | 23 | 3 | formulário/validação, export, view, controller, $fillable, $casts | **Utilização confirmada** |
| `generated_from_inspection_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para generated_from_inspection (inferido) *(inferido)* | 1 | 1 | service, model, $fillable, relação | **Utilização confirmada** |
| `home_featured` | tinyint(1) | não | `false` | — | Destacar na página inicial | 4 | 2 | formulário/validação, view, controller, model, $fillable, $casts | **Utilização confirmada** |
| `home_featured_order` | int unsigned | sim | — | — | Ordem do destaque na home | 4 | 2 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada** |
| `seller_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para seller (inferido) *(inferido)* | 13 | 5 | view, service, controller, model, $fillable, relação | **Utilização confirmada** |
| `seller_contact_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para seller_contact (inferido) *(inferido)* | 6 | 1 | view, service, controller, model, $fillable, relação | **Utilização confirmada** |
| `client_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para client (inferido) *(inferido)* | 36 | 16 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |

### `vehicle_attribute_values`

- **Finalidade:** Valores de atributos por viatura (equipamentos). *(inferida do nome/colunas)*
- **Estado:** parcialmente legado: vehicle_id aponta para Vehicle V1/V2; v3_vehicle_id é o actual.
- **Model:** `VehicleAttributeValue` (app/Models/VehicleAttributeValue.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(vehicle_id) -> vehicles.id ON DELETE cascade; FK(attribute_id) -> vehicle_attributes.id ON DELETE cascade; FK(v3_vehicle_id) -> v3_vehicles.id ON DELETE cascade
- **Relações Eloquent:** vehicle() belongsTo Vehicle::class; attribute() belongsTo VehicleAttribute::class, 'attribute_id'; v3Vehicle() belongsTo V3Vehicle::class, 'v3_vehicle_id'
- **Migrations:** criada em `2025_06_17`; 3 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (3): V3VehicleController.php, VehicleV2Controller.php, VehicleController.php
- **Classificação dos campos:** Utilização confirmada: 7

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 49 | 0 | controller, model | **Utilização confirmada**; Eloquent/framework |
| `vehicle_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para vehicle (inferido) *(inferido)* | 6 | 7 | controller, model, $fillable, relação | **Utilização confirmada**; parcialmente legado: vehicle_id aponta para Vehicle V1/V2; v3_vehicle_id é o actual; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `attribute_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para attribute (inferido) *(inferido)* | 11 | 10 | service, controller, model, $fillable, relação | **Utilização confirmada**; parcialmente legado: vehicle_id aponta para Vehicle V1/V2; v3_vehicle_id é o actual |
| `value` | varchar(255) | não | — | — | Valor (inferido) *(inferido)* | 30 | 9 | view, controller, $fillable | **Utilização confirmada**; parcialmente legado: vehicle_id aponta para Vehicle V1/V2; v3_vehicle_id é o actual; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 2 | 0 | controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `v3_vehicle_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para v3_vehicle (inferido) *(inferido)* | 59 | 27 | formulário/validação, export, view, service, controller, model | **Utilização confirmada**; parcialmente legado: vehicle_id aponta para Vehicle V1/V2; v3_vehicle_id é o actual |

### `vehicle_attributes`

- **Finalidade:** Catálogo de atributos/equipamentos de viaturas (nome, chave, tipo, grupo, mapeamentos AutoScout/Mobile.de). *(inferida do nome/colunas)*
- **Model:** `VehicleAttribute` (app/Models/VehicleAttribute.php)
- **PK:** id
- **Índices:** UNIQUE(key)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** attributeGroup() belongsTo AttributeGroup::class, 'attribute_group', 'name'
- **Migrations:** criada em `2025_06_17`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (11): AttributeGroupV2Controller.php, ProposalV2Controller.php, V3VehicleController.php, VehicleAttributeV2Controller.php, VehicleV2Controller.php, ExtrasController.php…; seeders (1): VehicleAttributesTableSeeder.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 7; ex.: app/Http/Controllers/VehicleAttributeController.php:20, app/Http/Controllers/VehicleAttributeController.php:26, app/Http/Controllers/VehicleAttributeController.php:33, app/Http/Controllers/VehicleAttributeController.php:54
- **Classificação dos campos:** Utilização confirmada: 12

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 89 | 4 | API, view, controller | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 55 | 70 | formulário/validação, API, seeder, view, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `key` | varchar(255) | não | — | UNIQUE(key) | Chave (inferido) *(inferido)* | 20 | 67 | formulário/validação, API, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `type` | varchar(255) | não | — | — | Tipo (inferido) *(inferido)* | 28 | 66 | API, seeder, view, controller, $fillable | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `options` | json | sim | — | — | Opções (JSON) (inferido) *(inferido)* | 10 | 70 | formulário/validação, API, seeder, view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `order` | int | não | `0` | — | Ordem de apresentação (inferido) *(inferido)* | 36 | 77 | formulário/validação, API, seeder, view, controller, model | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `attribute_group` | varchar(255) | sim | — | — | Campo de domínio «attribute_group» (finalidade não documentada; inferido pelo nome) *(inferido)* | 29 | 72 | formulário/validação, API, seeder, view, controller, model | **Utilização confirmada** |
| `field_name_autoscout` | varchar(255) | sim | — | — | Campo de domínio «field_name_autoscout» (finalidade não documentada; inferido pelo nome) *(inferido)* | 8 | 7 | formulário/validação, API, view, controller, $fillable | **Utilização confirmada** |
| `field_name_mobile` | varchar(255) | sim | — | — | Campo de domínio «field_name_mobile» (finalidade não documentada; inferido pelo nome) *(inferido)* | 6 | 7 | formulário/validação, API, view, controller, $fillable | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 16 | 2 | API, view, controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 4 | 2 | API, view, controller | **Utilização confirmada**; Eloquent/framework |
| `alemao` | varchar(255) | sim | — | — | Campo de domínio «alemao» (finalidade não documentada; inferido pelo nome) *(inferido)* | 7 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |

### `vehicle_documents`

- **Finalidade:** Documentos de viaturas V1/V2 (legado). *(inferida do nome/colunas)*
- **Estado:** legado: documentos de Vehicle (V3 usa v3_vehicle_documents).
- **Model:** `VehicleDocument` (app/Models/VehicleDocument.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(vehicle_id) -> vehicles.id ON DELETE cascade
- **Relações Eloquent:** vehicle() belongsTo Vehicle::class
- **Migrations:** criada em `2026_05_19`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): VehicleV2Controller.php
- **Classificação dos campos:** Utilização confirmada: 7

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 10 | 0 | controller | **Utilização confirmada**; Eloquent/framework |
| `vehicle_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para vehicle (inferido) *(inferido)* | 6 | 4 | controller, model, $fillable, relação | **Utilização confirmada**; legado: documentos de Vehicle (V3 usa v3_vehicle_documents); nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `tipo` | varchar(255) | sim | — | — | Tipo (inferido) *(inferido)* | 1 | 2 | controller, $fillable | **Utilização confirmada**; legado: documentos de Vehicle (V3 usa v3_vehicle_documents); texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `nome_original` | varchar(255) | não | — | — | Nome original do ficheiro carregado (inferido) *(inferido)* | 5 | 1 | controller, $fillable | **Utilização confirmada**; legado: documentos de Vehicle (V3 usa v3_vehicle_documents); nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `caminho` | varchar(255) | não | — | — | Caminho do ficheiro em disco (inferido) *(inferido)* | 11 | 1 | controller, $fillable | **Utilização confirmada**; legado: documentos de Vehicle (V3 usa v3_vehicle_documents); nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 1 | 0 | controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `vehicle_images`

- **Finalidade:** Fotografias de viaturas V1/V2 (legado). *(inferida do nome/colunas)*
- **Estado:** legado: fotos de Vehicle (V3 usa v3_vehicle_photos).
- **Model:** `VehicleImage` (app/Models/VehicleImage.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(vehicle_id) -> vehicles.id ON DELETE cascade
- **Relações Eloquent:** vehicle() belongsTo Vehicle::class
- **Migrations:** criada em `2025_04_08`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): VehicleV2Controller.php
- **Classificação dos campos:** Utilização confirmada: 6

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 13 | 0 | view, controller | **Utilização confirmada**; Eloquent/framework |
| `vehicle_id` | bigint unsigned | não | — | FK | Chave estrangeira / referência para vehicle (inferido) *(inferido)* | 6 | 4 | controller, model, $fillable, relação | **Utilização confirmada**; legado: fotos de Vehicle (V3 usa v3_vehicle_photos); nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 1 | 0 | controller | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `order` | int | não | `0` | — | Ordem de apresentação (inferido) *(inferido)* | 5 | 2 | controller, $fillable | **Utilização confirmada**; legado: fotos de Vehicle (V3 usa v3_vehicle_photos); nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `path` | varchar(255) | não | — | — | Caminho do ficheiro (inferido) *(inferido)* | 5 | 1 | view, controller, $fillable | **Utilização confirmada**; legado: fotos de Vehicle (V3 usa v3_vehicle_photos); nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |

### `vehicle_inspection_categories`

- **Finalidade:** Categorias de itens por modelo de inspeção. *(inferida do nome/colunas)*
- **Model:** `VehicleInspectionCategory` (app/Models/VehicleInspectionCategory.php)
- **PK:** id
- **Índices:** UNIQUE(vehicle_inspection_template_id,slug)
- **FKs / constraints:** FK(vehicle_inspection_template_id) -> vehicle_inspection_templates.id ON DELETE cascade
- **Relações Eloquent:** template() belongsTo VehicleInspectionTemplate::class, 'vehicle_inspection_templa; items() hasMany VehicleInspectionItem::class, 'vehicle_inspection_category_i
- **Migrations:** criada em `2026_05_27`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** services (1): VehicleInspectionService.php; seeders (1): VehicleInspectionSeeder.php
- **Classificação dos campos:** Utilização confirmada: 9

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 9 | 0 | view, service | **Utilização confirmada**; Eloquent/framework |
| `vehicle_inspection_template_id` | bigint unsigned | não | — | UNIQUE(vehicle_inspection_template_id,slug), FK | Chave estrangeira / referência para vehicle_inspection_template (inferido) *(inferido)* | 3 | 4 | seeder, controller, model, $fillable, relação | **Utilização confirmada** |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 7 | 60 | seeder, view, service, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `slug` | varchar(255) | não | — | UNIQUE(vehicle_inspection_template_id,slug) | Slug (identificador em URL) (inferido) *(inferido)* | 3 | 12 | seeder, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `icon` | varchar(255) | sim | — | — | Ícone (inferido) *(inferido)* | 2 | 10 | seeder, view, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `sort_order` | int unsigned | não | `0` | — | Ordem de apresentação (inferido) *(inferido)* | 5 | 12 | seeder, service, model, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `applies_to_fuels` | json | sim | — | — | Campo de domínio «applies_to_fuels» (finalidade não documentada; inferido pelo nome) *(inferido)* | 7 | 12 | seeder, view, model, $fillable, $casts | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 3 | seeder | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 3 | seeder | **Utilização confirmada**; Eloquent/framework |

### `vehicle_inspection_entries`

- **Finalidade:** Resultado por item de uma inspeção. *(inferida do nome/colunas)*
- **Model:** `VehicleInspectionEntry` (app/Models/VehicleInspectionEntry.php)
- **PK:** id
- **Índices:** UNIQUE(vehicle_inspection_id,vehicle_inspection_item_id,inspection_item_unique); UNIQUE(vehicle_inspection_id,vehicle_inspection_item_id,inspection_item_unique)
- **FKs / constraints:** FK(vehicle_inspection_id) -> vehicle_inspections.id ON DELETE cascade; FK(vehicle_inspection_item_id) -> vehicle_inspection_items.id ON DELETE cascade; FK(vehicle_inspection_id) -> vehicle_inspections.id ON DELETE cascade; FK(vehicle_inspection_item_id) -> vehicle_inspection_items.id ON DELETE cascade
- **Relações Eloquent:** inspection() belongsTo VehicleInspection::class, 'vehicle_inspection_id'; item() belongsTo VehicleInspectionItem::class, 'vehicle_inspection_item_id'; media() hasMany VehicleInspectionMedia::class, 'vehicle_inspection_entry_id'
- **Migrations:** criada em `2026_05_27`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): VehicleInspectionController.php; services (1): VehicleInspectionService.php; views (1): report.blade.php
- **Classificação dos campos:** Utilização confirmada: 8

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 28 | 0 | view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `vehicle_inspection_id` | bigint unsigned | não | — | UNIQUE(vehicle_inspection_id,vehicle_inspection_item_id,inspection_item_unique), FK | Chave estrangeira / referência para vehicle_inspection (inferido) *(inferido)* | 10 | 1 | controller, model, $fillable, relação | **Utilização confirmada** |
| `vehicle_inspection_item_id` | bigint unsigned | não | — | UNIQUE(vehicle_inspection_id,vehicle_inspection_item_id,inspection_item_unique), FK | Chave estrangeira / referência para vehicle_inspection_item (inferido) *(inferido)* | 13 | 1 | controller, model, $fillable, relação | **Utilização confirmada** |
| `status` | varchar(255) | não | `'nao_verificado'` | — | Estado (inferido) *(inferido)* | 38 | 11 | view, service, controller, model, $fillable | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `priority` | varchar(255) | não | `'baixa'` | — | Campo de domínio «priority» (finalidade não documentada; inferido pelo nome) *(inferido)* | 22 | 3 | view, service, controller, $fillable | **Utilização confirmada** |
| `notes` | longtext | sim | — | — | Notas internas (inferido) *(inferido)* | 15 | 5 | view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 2 | 0 | view | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |

### `vehicle_inspection_items`

- **Finalidade:** Itens a inspecionar por categoria. *(inferida do nome/colunas)*
- **Model:** `VehicleInspectionItem` (app/Models/VehicleInspectionItem.php)
- **PK:** id
- **Índices:** UNIQUE(vehicle_inspection_category_id,slug); UNIQUE(vehicle_inspection_category_id,slug,vi_items_category_slug_unique)
- **FKs / constraints:** FK(vehicle_inspection_category_id) -> vehicle_inspection_categories.id ON DELETE cascade; FK(vehicle_inspection_category_id) -> vehicle_inspection_categories.id ON DELETE cascade
- **Relações Eloquent:** category() belongsTo VehicleInspectionCategory::class, 'vehicle_inspection_catego; entries() hasMany VehicleInspectionEntry::class, 'vehicle_inspection_item_id'
- **Migrations:** criada em `2026_05_27`; 2 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** services (1): VehicleInspectionService.php; seeders (1): VehicleInspectionSeeder.php
- **Classificação dos campos:** Utilização confirmada: 10

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 19 | 0 | view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `vehicle_inspection_category_id` | bigint unsigned | não | — | UNIQUE(vehicle_inspection_category_id,slug), UNIQUE(vehicle_inspection_category_id,slug,vi_items_category_slug_unique), FK | Chave estrangeira / referência para vehicle_inspection_category (inferido) *(inferido)* | 2 | 1 | seeder, model, $fillable, relação | **Utilização confirmada** |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 10 | 60 | seeder, view, service, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `slug` | varchar(255) | não | — | UNIQUE(vehicle_inspection_category_id,slug), UNIQUE(vehicle_inspection_category_id,slug,vi_items_category_slug_unique) | Slug (identificador em URL) (inferido) *(inferido)* | 2 | 12 | seeder, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `description` | text | sim | — | — | Descrição (inferido) *(inferido)* | 5 | 50 | seeder, view, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `sort_order` | int unsigned | não | `0` | — | Ordem de apresentação (inferido) *(inferido)* | 5 | 12 | seeder, service, model, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `applies_to_fuels` | json | sim | — | — | Campo de domínio «applies_to_fuels» (finalidade não documentada; inferido pelo nome) *(inferido)* | 7 | 12 | seeder, view, model, $fillable, $casts | **Utilização confirmada** |
| `is_critical` | tinyint(1) | não | `false` | — | Indicador booleano: critical (inferido) *(inferido)* | 6 | 49 | seeder, view, service, controller, $fillable, $casts | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 3 | seeder | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 3 | seeder | **Utilização confirmada**; Eloquent/framework |

### `vehicle_inspection_media`

- **Finalidade:** Fotos/ficheiros das inspeções. *(inferida do nome/colunas)*
- **Model:** `VehicleInspectionMedia` (app/Models/VehicleInspectionMedia.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(vehicle_inspection_entry_id) -> vehicle_inspection_entries.id ON DELETE cascade; FK(vehicle_inspection_entry_id) -> vehicle_inspection_entries.id ON DELETE cascade; FK(vehicle_inspection_id) -> vehicle_inspections.id ON DELETE cascade; FK(vehicle_inspection_entry_id) -> vehicle_inspection_entries.id ON DELETE cascade
- **Relações Eloquent:** inspection() belongsTo VehicleInspection::class, 'vehicle_inspection_id'; entry() belongsTo VehicleInspectionEntry::class, 'vehicle_inspection_entry_id'
- **Migrations:** criada em `2026_05_27`; 3 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): VehicleInspectionController.php; services (1): VehicleInspectionService.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: app/Http/Controllers/Admin/VehicleInspectionController.php:386
- **Classificação dos campos:** Utilização confirmada: 10

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 25 | 0 | view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `vehicle_inspection_entry_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para vehicle_inspection_entry (inferido) *(inferido)* | 6 | 2 | controller, model, $fillable, relação | **Utilização confirmada** |
| `type` | varchar(255) | não | — | — | Tipo (inferido) *(inferido)* | 3 | 2 | view, service, controller, $fillable | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `path` | varchar(255) | não | — | — | Caminho do ficheiro (inferido) *(inferido)* | 10 | 3 | view, service, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `original_name` | varchar(255) | não | — | — | Campo de domínio «original_name» (finalidade não documentada; inferido pelo nome) *(inferido)* | 3 | 2 | view, service, controller, $fillable | **Utilização confirmada** |
| `description` | text | sim | — | — | Descrição (inferido) *(inferido)* | 2 | 1 | view, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `sort_order` | int unsigned | não | `0` | — | Ordem de apresentação (inferido) *(inferido)* | 11 | 3 | service, controller, model, $fillable, $casts | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `vehicle_inspection_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para vehicle_inspection (inferido) *(inferido)* | 10 | 1 | controller, model, $fillable, relação | **Utilização confirmada** |

### `vehicle_inspection_templates`

- **Finalidade:** Modelos de inspeção de viaturas. *(inferida do nome/colunas)*
- **Model:** `VehicleInspectionTemplate` (app/Models/VehicleInspectionTemplate.php)
- **PK:** id
- **Índices:** UNIQUE(slug)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** categories() hasMany VehicleInspectionCategory::class, 'vehicle_inspection_templa
- **Migrations:** criada em `2026_05_27`; 1 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): VehicleInspectionController.php; services (1): VehicleInspectionService.php; seeders (1): VehicleInspectionSeeder.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 1; ex.: app/Http/Controllers/Admin/VehicleInspectionController.php:95
- **Classificação dos campos:** Utilização confirmada: 7

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 25 | 0 | view, service, controller | **Utilização confirmada**; Eloquent/framework |
| `name` | varchar(255) | não | — | — | Nome (inferido) *(inferido)* | 11 | 60 | seeder, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `slug` | varchar(255) | não | — | UNIQUE(slug) | Slug (identificador em URL) (inferido) *(inferido)* | 2 | 12 | seeder, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `description` | text | sim | — | — | Descrição (inferido) *(inferido)* | 1 | 51 | seeder, controller, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `is_default` | tinyint(1) | não | `false` | — | Indicador booleano: default (inferido) *(inferido)* | 1 | 1 | seeder, service, $fillable, $casts | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 0 | 3 | seeder | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 3 | seeder | **Utilização confirmada**; Eloquent/framework |

### `vehicle_inspections`

- **Finalidade:** Inspeções realizadas (dados da viatura, pontuação, resultado, ligação à viatura V3). *(inferida do nome/colunas)*
- **Model:** `VehicleInspection` (app/Models/VehicleInspection.php)
- **PK:** id
- **Índices:** apenas PK/FKs
- **FKs / constraints:** FK(vehicle_inspection_template_id) -> vehicle_inspection_templates.id ON DELETE set null; FK(v3_vehicle_id) -> v3_vehicles.id ON DELETE set null; FK(vehicle_inspection_template_id) -> vehicle_inspection_templates.id ON DELETE set null; FK(v3_vehicle_id) -> v3_vehicles.id ON DELETE set null; FK(parent_inspection_id) -> vehicle_inspections.id ON DELETE set null
- **Relações Eloquent:** parent() belongsTo VehicleInspection::class, 'parent_inspection_id'; revisions() hasMany VehicleInspection::class, 'parent_inspection_id'; template() belongsTo VehicleInspectionTemplate::class, 'vehicle_inspection_templa; vehicle() belongsTo V3Vehicle::class, 'v3_vehicle_id'; entries() hasMany VehicleInspectionEntry::class, 'vehicle_inspection_id'; media() hasManyThrough VehicleInspectionMedia::class, VehicleInspectionEntry::class; generalMedia() hasMany VehicleInspectionMedia::class, 'vehicle_inspection_id'
- **Migrations:** criada em `2026_05_27`; 3 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (1): VehicleInspectionController.php; services (1): VehicleInspectionService.php
- **Classificação dos campos:** Apenas escrita: 1, Utilização confirmada: 32

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 37 | 0 | view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `vehicle_inspection_template_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para vehicle_inspection_template (inferido) *(inferido)* | 2 | 3 | controller, model, $fillable, relação | **Utilização confirmada** |
| `v3_vehicle_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para v3_vehicle (inferido) *(inferido)* | 59 | 27 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `status` | varchar(255) | não | `'draft'` | — | Estado (inferido) *(inferido)* | 45 | 12 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; texto livre sem enum/constraint na BD; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `brand` | varchar(255) | sim | — | — | Marca (inferido) *(inferido)* | 10 | 4 | view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `model` | varchar(255) | sim | — | — | Modelo (inferido) *(inferido)* | 9 | 4 | view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `sub_model` | varchar(255) | sim | — | — | Campo de domínio «sub_model» (finalidade não documentada; inferido pelo nome) *(inferido)* | 20 | 9 | formulário/validação, export, view, service, controller, model | **Utilização confirmada** |
| `version` | varchar(255) | sim | — | — | Versão (inferido) *(inferido)* | 5 | 5 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `year` | smallint unsigned | sim | — | — | Ano (inferido) *(inferido)* | 14 | 6 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `kilometers` | int unsigned | sim | — | — | Quilómetros (inferido) *(inferido)* | 5 | 6 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `vin` | varchar(32) | sim | — | — | Número de quadro (VIN) (inferido) *(inferido)* | 7 | 5 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `registration` | varchar(20) | sim | — | — | Matrícula (inferido) *(inferido)* | 42 | 15 | formulário/validação, export, script, view, service, controller | **Utilização confirmada** |
| `color` | varchar(255) | sim | — | — | Cor (inferido) *(inferido)* | 3 | 4 | service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `fuel` | varchar(255) | sim | — | — | Combustível (inferido) *(inferido)* | 14 | 5 | view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `power` | smallint unsigned | sim | — | — | Potência (inferido) *(inferido)* | 4 | 5 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `transmission` | varchar(255) | sim | — | — | Campo de domínio «transmission» (finalidade não documentada; inferido pelo nome) *(inferido)* | 13 | 5 | formulário/validação, script, view, controller, $fillable | **Utilização confirmada** |
| `traction` | varchar(255) | sim | — | — | Campo de domínio «traction» (finalidade não documentada; inferido pelo nome) *(inferido)* | 7 | 5 | formulário/validação, view, controller, $fillable | **Utilização confirmada** |
| `notes` | longtext | sim | — | — | Notas internas (inferido) *(inferido)* | 8 | 6 | formulário/validação, view, service, controller, model, $fillable | **Utilização confirmada**; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `recommendations` | longtext | sim | — | — | Campo de domínio «recommendations» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 1 | view, service, $fillable | **Utilização confirmada** |
| `inspection_result` | varchar(255) | sim | — | — | Campo de domínio «inspection_result» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 1 | view, service, $fillable | **Utilização confirmada** |
| `total_points` | decimal(8,2) | não | `0` | — | Campo de domínio «total_points» (finalidade não documentada; inferido pelo nome) *(inferido)* | 4 | 1 | view, service, $fillable, $casts | **Utilização confirmada** |
| `max_points` | decimal(8,2) | não | `0` | — | Campo de domínio «max_points» (finalidade não documentada; inferido pelo nome) *(inferido)* | 6 | 1 | view, service, $fillable, $casts | **Utilização confirmada** |
| `verified_items` | int unsigned | não | `0` | — | Campo de domínio «verified_items» (finalidade não documentada; inferido pelo nome) *(inferido)* | 3 | 1 | view, service, $fillable, $casts | **Utilização confirmada** |
| `ok_items` | int unsigned | não | `0` | — | Campo de domínio «ok_items» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 1 | view, service, $fillable, $casts | **Utilização confirmada** |
| `attention_items` | int unsigned | não | `0` | — | Campo de domínio «attention_items» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 1 | view, service, $fillable, $casts | **Utilização confirmada** |
| `problem_items` | int unsigned | não | `0` | — | Campo de domínio «problem_items» (finalidade não documentada; inferido pelo nome) *(inferido)* | 3 | 1 | view, service, $fillable, $casts | **Utilização confirmada** |
| `unverified_items` | int unsigned | não | `0` | — | Campo de domínio «unverified_items» (finalidade não documentada; inferido pelo nome) *(inferido)* | 0 | 1 | service, $fillable, $casts | **Apenas escrita** |
| `critical_issues` | int unsigned | não | `0` | — | Campo de domínio «critical_issues» (finalidade não documentada; inferido pelo nome) *(inferido)* | 1 | 1 | view, service, $fillable, $casts | **Utilização confirmada** |
| `completed_at` | timestamp | sim | — | — | Data/hora de evento (inferido pelo nome) *(inferido)* | 3 | 2 | view, service, controller, $fillable, $casts | **Utilização confirmada** |
| `converted_at` | timestamp | sim | — | — | Data de conversão de lead em cliente (inferido) *(inferido)* | 8 | 2 | export, view, service, controller, $fillable, $casts | **Utilização confirmada** |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 3 | 0 | view, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 0 | 0 | — | **Utilização confirmada**; Eloquent/framework |
| `parent_inspection_id` | bigint unsigned | sim | — | FK | Chave estrangeira / referência para parent_inspection (inferido) *(inferido)* | 13 | 1 | view, controller, model, $fillable, relação | **Utilização confirmada** |

### `vehicles`

- **Finalidade:** Viaturas do modelo V1/V2 (legado), substituído por v3_vehicles. *(inferida do nome/colunas)*
- **Estado:** legado (V1/V2): V3Vehicle é a fonte de verdade.
- **Model:** `Vehicle` (app/Models/Vehicle.php)
- **PK:** id
- **Índices:** UNIQUE(reference); UNIQUE(vin)
- **FKs / constraints:** nenhuma FK declarada nas migrations
- **Relações Eloquent:** supplier() belongsTo Supplier::class; images() hasMany VehicleImage::class; attributeValues() hasMany VehicleAttributeValue::class; expenses() hasMany Expense::class, 'vehicle_id'; sale() hasOne Sale::class, 'vehicle_id'; documents() hasMany VehicleDocument::class; legalizations() hasMany Legalization::class
- **Migrations:** criada em `2025_02_27`; 5 migration(s) tocam a tabela.
- **Onde é usada (ficheiros que referenciam o model/tabela):** controllers (23): ClientV2Controller.php, ConvertedProposalV2Controller.php, DashboardV2Controller.php, ExpenseV2Controller.php, FinancialDashboardController.php, GlobalSearchController.php…; services (4): GlobalSearchService.php, ImageOptimizerService.php, MovementService.php, VehicleInspectionService.php; commands (2): GenerateSitemap.php, ImportCarData.php; exports (3): ClientExport.php, ExportRegistry.php, VehicleExport.php; observers (1): VehicleObserver.php; requests (1): ExportXlsxRequest.php; views (60): show.blade.php, show.blade.php, form.blade.php, index.blade.php, show.blade.php, show.blade.php…; seeders (2): TaskSeeder.php, VehicleTableSeeder.php; routes (2): frontend.php, web.php; config (1): permissions.php; outros app (2): PermissionRegistry.php, AppServiceProvider.php
- **Referências literais ao nome da tabela (SQL directo/DB::table/validações exists:/scripts):** 370; ex.: app/Models/Seller.php:67, app/Models/Seller.php:162, app/Models/SellerContact.php:63, app/Models/Client.php:132
- **Classificação dos campos:** Utilização confirmada: 33

| Campo | Tipo | Nulo | Default | Índices | Finalidade | L | E | Onde | Classificação / notas |
|---|---|---|---|---|---|---|---|---|---|
| `id` | bigint unsigned | não | — | PK | Chave primária | 484 | 6 | export, comando, seeder, observer, view, service | **Utilização confirmada**; Eloquent/framework |
| `reference` | varchar(255) | não | — | UNIQUE(reference) | Referência interna (inferido) *(inferido)* | 98 | 9 | formulário/validação, export, comando, seeder, view, service | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `business_type` | enum(,Novo,Usado,Semi-novo) | sim | — | — | Tipo de negócio (novo/usado) (inferido) *(inferido)* | 9 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `brand` | varchar(255) | não | — | — | Marca (inferido) *(inferido)* | 235 | 30 | formulário/validação, export, comando, seeder, view, service | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `model` | varchar(255) | não | — | — | Modelo (inferido) *(inferido)* | 196 | 26 | formulário/validação, export, comando, seeder, view, service | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `version` | varchar(255) | sim | — | — | Versão (inferido) *(inferido)* | 54 | 17 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `year` | varchar(255) | sim | — | — | Ano (inferido) *(inferido)* | 124 | 32 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `supplier_id` | bigint unsigned | sim | — | — | Chave estrangeira / referência para supplier (inferido) *(inferido)* | 16 | 16 | formulário/validação, seeder, view, controller, $fillable, relação | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade; sem FK constraint na BD (integridade só aplicacional) |
| `purchase_date` | date | sim | — | — | Data de compra (inferido) *(inferido)* | 15 | 9 | formulário/validação, seeder, view, controller, model, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `purchase_type` | enum(,Margem,Geral,Sem Iva) | sim | — | — | Regime de compra (Margem/Geral/Sem IVA) (inferido) *(inferido)* | 34 | 9 | formulário/validação, seeder, view, controller, model, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `purchase_price` | decimal(10,2) | sim | — | — | Preço de compra (inferido) *(inferido)* | 31 | 9 | formulário/validação, seeder, view, controller, model, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `sell_price` | decimal(10,2) | sim | — | — | Valor monetário (inferido) *(inferido)* | 15 | 7 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `kilometers` | int | sim | — | — | Quilómetros (inferido) *(inferido)* | 45 | 14 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `power` | int | sim | — | — | Potência (inferido) *(inferido)* | 15 | 13 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `cylinder_capacity` | int | sim | — | — | Cilindrada (cc) (inferido) *(inferido)* | 11 | 9 | formulário/validação, export, seeder, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `consigned_vehicle` | tinyint(1) | não | `false` | — | Viatura em consignação (inferido) *(inferido)* | 10 | 9 | formulário/validação, seeder, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `fuel` | enum(,Diesel,Gasolina,Elétrico,Hibrido-plug-in/Gasolina,Hibrido-plug-in/Diesel,Outro) | sim | — | — | Combustível (inferido) *(inferido)* | 98 | 30 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `sold` | tinyint(1) | não | `false` | — | Vendida (inferido) *(inferido)* | 2 | 1 | seeder, view, controller | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `sale_date` | date | sim | — | — | Data da venda (inferido) *(inferido)* | 27 | 10 | formulário/validação, seeder, view, service, controller, model | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `vin` | varchar(255) | não | — | UNIQUE(vin) | Número de quadro (VIN) (inferido) *(inferido)* | 22 | 16 | formulário/validação, export, seeder, view, service, controller | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade; nome genérico: contagem aproximada (âmbito por ficheiros ligados à tabela) |
| `manufacture_date` | date | sim | — | — | Data (inferido pelo nome) *(inferido)* | 8 | 9 | formulário/validação, export, seeder, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `register_date` | date | sim | — | — | Data (inferido pelo nome) *(inferido)* | 8 | 9 | formulário/validação, export, seeder, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `available_to_sell_date` | date | sim | — | — | Data (inferido pelo nome) *(inferido)* | 8 | 9 | formulário/validação, export, seeder, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `created_at` | timestamp | sim | — | — | Data de criação (Eloquent) | 104 | 1 | export, view, service, controller, model | **Utilização confirmada**; Eloquent/framework |
| `updated_at` | timestamp | sim | — | — | Data da última atualização (Eloquent) | 14 | 1 | comando, view, controller, model | **Utilização confirmada**; Eloquent/framework |
| `show_online` | tinyint(1) | não | `false` | — | Visível no site público (inferido) *(inferido)* | 35 | 24 | formulário/validação, export, comando, view, service, controller | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `warranty_price` | decimal(10,2) | sim | — | — | Preço da garantia (inferido) *(inferido)* | 3 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `warranty_start_date` | date | sim | — | — | Data (inferido pelo nome) *(inferido)* | 3 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `warranty_end_date` | date | sim | — | — | Data (inferido pelo nome) *(inferido)* | 3 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `warranty_type` | varchar(255) | sim | — | — | Tipo de garantia (inferido) *(inferido)* | 4 | 3 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `payment_type` | varchar(255) | sim | — | — | Tipo de pagamento (inferido) *(inferido)* | 16 | 6 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `purchase_vat_rate` | decimal(5,2) | sim | — | — | Taxa de IVA na compra (inferido) *(inferido)* | 8 | 5 | formulário/validação, view, controller, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |
| `purchase_vat_paid` | decimal(10,2) | sim | — | — | IVA pago na compra (inferido) *(inferido)* | 14 | 5 | formulário/validação, view, controller, model, $fillable | **Utilização confirmada**; legado (V1/V2): V3Vehicle é a fonte de verdade |

## 6. Consultas de diagnóstico (só leitura)

Para correr pelo Pedro numa base **de leitura** (cópia/réplica, ou em produção apenas `SELECT`). Nenhuma altera dados. Substituir `DATABASE()` não é necessário se a ligação já usa a BD certa.

### 6.1 Linhas e tamanho por tabela

```sql
SELECT table_name,
       table_rows AS linhas_aprox,
       ROUND(data_length/1024/1024, 2)  AS dados_mb,
       ROUND(index_length/1024/1024, 2) AS indices_mb,
       update_time
FROM information_schema.tables
WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'
ORDER BY (data_length + index_length) DESC;
```

### 6.2 Deriva entre a BD real e o dicionário (colunas e tabelas)

```sql
-- Lista todas as colunas reais; comparar com o CSV (tabela,campo)
SELECT table_name, column_name, column_type, is_nullable, column_default, column_key
FROM information_schema.columns
WHERE table_schema = DATABASE()
ORDER BY table_name, ordinal_position;

-- Tabelas que existem na BD (confrontar com a secção 2; ex.: menu_items, cars, proposed_cars, migrations)
SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY 1;

-- Migrations aplicadas em produção (confrontar com database/migrations)
SELECT migration, batch FROM migrations ORDER BY id;
```

### 6.3 Campos sempre NULL / sem uso (gerador de consultas)

Esta consulta **gera** SQL (não o executa). Copiar o resultado e correr: devolve, por coluna anulável, quantos registos existem e quantos estão preenchidos.

```sql
SELECT CONCAT('SELECT \'', c.table_name, '.', c.column_name, '\' AS campo, COUNT(*) AS total, COUNT(`', c.column_name, '`) AS preenchidos FROM `', c.table_name, '` UNION ALL') AS sql_gerado
FROM information_schema.columns c
JOIN information_schema.tables t ON t.table_schema = c.table_schema AND t.table_name = c.table_name AND t.table_type = 'BASE TABLE'
WHERE c.table_schema = DATABASE() AND c.is_nullable = 'YES'
  AND c.table_name NOT IN ('sessions','cache','cache_locks','jobs','job_batches','failed_jobs')
ORDER BY c.table_name, c.ordinal_position;
-- Remover o ultimo "UNION ALL" do resultado antes de executar.
```

Casos concretos prováveis (alto interesse):

```sql
SELECT COUNT(*) total, COUNT(twitter_card) tw_card, COUNT(twitter_title) tw_title, COUNT(twitter_description) tw_desc, COUNT(twitter_image) tw_img, COUNT(og_type) og_type, COUNT(meta_secundary_keywords) sec_kw FROM seo_metas;
SELECT COUNT(*) total, SUM(has_trade_in) com_retoma, COUNT(trade_in_vehicle_id) c_veic, COUNT(trade_in_value) c_valor FROM sales;
SELECT COUNT(*) total, COUNT(user_agent) preenchidos FROM audit_logs;
SELECT COUNT(*) total, COUNT(currency) c, SUM(currency <> 'EUR') nao_eur FROM import_opportunities;
SELECT COUNT(*) total, MAX(updated_at) ultimo FROM import_simulator;
SELECT COUNT(*) total, MAX(updated_at) ultimo FROM ad_listings;
SELECT COUNT(*) total, MAX(updated_at) ultimo FROM ad_searches;
SELECT COUNT(*) total, MAX(updated_at) ultimo FROM vehicles;
SELECT COUNT(*) total, MAX(updated_at) ultimo FROM vehicle_images;
SELECT COUNT(*) total, MAX(updated_at) ultimo FROM vehicle_documents;
SELECT COUNT(*) total, MAX(updated_at) ultimo FROM proposal_attribute_values;
SELECT COUNT(*) total, COUNT(vehicle_id) com_legado, COUNT(v3_vehicle_id) com_v3 FROM vehicle_attribute_values;
SELECT COUNT(*) total, COUNT(vehicle_id) com_legado, COUNT(v3_vehicle_id) com_v3 FROM sales;
SELECT COUNT(*) total, MAX(updated_at) ultimo FROM financial_movements;
SELECT movement_type, COUNT(*) FROM expenses GROUP BY movement_type;
SELECT COUNT(*) total, COUNT(remember_token) c_remember, COUNT(email_verified_at) c_verif FROM users;
```

### 6.4 Tabelas de framework (confirmar drivers reais)

```sql
SELECT 'sessions' t, COUNT(*) n, MAX(last_activity) ultimo FROM sessions
UNION ALL SELECT 'cache', COUNT(*), MAX(expiration) FROM cache
UNION ALL SELECT 'jobs', COUNT(*), MAX(created_at) FROM jobs
UNION ALL SELECT 'failed_jobs', COUNT(*), NULL FROM failed_jobs
UNION ALL SELECT 'job_batches', COUNT(*), MAX(created_at) FROM job_batches
UNION ALL SELECT 'password_reset_tokens', COUNT(*), NULL FROM password_reset_tokens
UNION ALL SELECT 'push_subscriptions', COUNT(*), MAX(updated_at) FROM push_subscriptions;
```

### 6.5 Atividade recente por tabela (as que têm `updated_at`)

```sql
SELECT CONCAT('SELECT \'', table_name, '\' AS tabela, COUNT(*) AS n, MAX(updated_at) AS ultima_alteracao FROM `', table_name, '` UNION ALL') AS sql_gerado
FROM information_schema.columns
WHERE table_schema = DATABASE() AND column_name = 'updated_at'
ORDER BY table_name;
-- Remover o ultimo "UNION ALL" e executar.
```

### 6.6 Órfãos (relações sem FK constraint e FKs lógicas)

```sql
SELECT 'converted_proposals.client_id' rel, COUNT(*) orfaos FROM converted_proposals cp LEFT JOIN clients c ON c.id = cp.client_id WHERE cp.client_id IS NOT NULL AND c.id IS NULL
UNION ALL SELECT 'converted_proposals.proposal_id', COUNT(*) FROM converted_proposals cp LEFT JOIN proposals p ON p.id = cp.proposal_id WHERE cp.proposal_id IS NOT NULL AND p.id IS NULL
UNION ALL SELECT 'cost_simulators.client_id', COUNT(*) FROM cost_simulators s LEFT JOIN clients c ON c.id = s.client_id WHERE s.client_id IS NOT NULL AND c.id IS NULL
UNION ALL SELECT 'form_proposals.client_id (varchar)', COUNT(*) FROM form_proposals f LEFT JOIN clients c ON c.id = CAST(f.client_id AS UNSIGNED) WHERE f.client_id IS NOT NULL AND f.client_id <> '' AND c.id IS NULL
UNION ALL SELECT 'form_proposals.client_id nao numerico', COUNT(*) FROM form_proposals WHERE client_id IS NOT NULL AND client_id NOT REGEXP '^[0-9]+$'
UNION ALL SELECT 'form_proposals.proposal_id', COUNT(*) FROM form_proposals f LEFT JOIN proposals p ON p.id = f.proposal_id WHERE f.proposal_id IS NOT NULL AND p.id IS NULL
UNION ALL SELECT 'sales.trade_in_vehicle_id', COUNT(*) FROM sales s LEFT JOIN vehicles v ON v.id = s.trade_in_vehicle_id WHERE s.trade_in_vehicle_id IS NOT NULL AND v.id IS NULL
UNION ALL SELECT 'status_proposal_history.converted_proposal_id', COUNT(*) FROM status_proposal_history h LEFT JOIN converted_proposals cp ON cp.id = h.converted_proposal_id WHERE cp.id IS NULL
UNION ALL SELECT 'vehicles.supplier_id', COUNT(*) FROM vehicles v LEFT JOIN suppliers s ON s.id = v.supplier_id WHERE v.supplier_id IS NOT NULL AND s.id IS NULL
UNION ALL SELECT 'seo_metas (seoable sem pai) Page', COUNT(*) FROM seo_metas m LEFT JOIN pages p ON p.id = m.seoable_id WHERE m.seoable_type LIKE '%Page' AND p.id IS NULL
UNION ALL SELECT 'seo_metas (seoable sem pai) Proposal', COUNT(*) FROM seo_metas m LEFT JOIN proposals p ON p.id = m.seoable_id WHERE m.seoable_type LIKE '%Proposal' AND p.id IS NULL
UNION ALL SELECT 'financial_movements movable orfao (Expense)', COUNT(*) FROM financial_movements fm LEFT JOIN expenses e ON e.id = fm.movable_id WHERE fm.movable_type LIKE '%Expense' AND e.id IS NULL
UNION ALL SELECT 'audit_logs.auditable_id Client', COUNT(*) FROM audit_logs a LEFT JOIN clients c ON c.id = a.auditable_id WHERE a.auditable_type LIKE '%Client' AND c.id IS NULL
UNION ALL SELECT 'model_has_roles users inexistentes', COUNT(*) FROM model_has_roles r LEFT JOIN users u ON u.id = r.model_id WHERE r.model_type LIKE '%User' AND u.id IS NULL;
```

### 6.7 Duplicados

```sql
SELECT 'clients.email_normalized' chave, email_normalized valor, COUNT(*) n FROM clients WHERE email_normalized IS NOT NULL AND email_normalized <> '' GROUP BY email_normalized HAVING n > 1
UNION ALL SELECT 'clients.phone_key', phone_key, COUNT(*) FROM clients WHERE phone_key IS NOT NULL AND phone_key <> '' GROUP BY phone_key HAVING COUNT(*) > 1
UNION ALL SELECT 'clients.vat_number', vat_number, COUNT(*) FROM clients WHERE vat_number IS NOT NULL AND vat_number <> '' GROUP BY vat_number HAVING COUNT(*) > 1
UNION ALL SELECT 'v3_vehicles.vin', vin, COUNT(*) FROM v3_vehicles WHERE vin IS NOT NULL AND vin <> '' GROUP BY vin HAVING COUNT(*) > 1
UNION ALL SELECT 'v3_vehicles.registration', registration, COUNT(*) FROM v3_vehicles WHERE registration IS NOT NULL AND registration <> '' GROUP BY registration HAVING COUNT(*) > 1
UNION ALL SELECT 'converted_proposals.vin', vin, COUNT(*) FROM converted_proposals WHERE vin IS NOT NULL AND vin <> '' GROUP BY vin HAVING COUNT(*) > 1
UNION ALL SELECT 'seller_contacts.email_normalized', email_normalized, COUNT(*) FROM seller_contacts WHERE email_normalized IS NOT NULL GROUP BY email_normalized HAVING COUNT(*) > 1
UNION ALL SELECT 'seller_contacts.phone_normalized', phone_normalized, COUNT(*) FROM seller_contacts WHERE phone_normalized IS NOT NULL GROUP BY phone_normalized HAVING COUNT(*) > 1
UNION ALL SELECT 'sellers.name', LOWER(name), COUNT(*) FROM sellers GROUP BY LOWER(name) HAVING COUNT(*) > 1
UNION ALL SELECT 'radar_listings (marca,modelo,ano,preco,km) duplicados', CONCAT_WS('|', make, model, first_registration_year, price_eur, mileage_km), COUNT(*) FROM radar_listings WHERE removed_at IS NULL GROUP BY make, model, first_registration_year, price_eur, mileage_km HAVING COUNT(*) > 1
UNION ALL SELECT 'testimonials.google_review_id', google_review_id, COUNT(*) FROM testimonials WHERE google_review_id IS NOT NULL GROUP BY google_review_id HAVING COUNT(*) > 1
UNION ALL SELECT 'pre_leads.phone', phone, COUNT(*) FROM pre_leads GROUP BY phone HAVING COUNT(*) > 1;
```

### 6.8 Valores distintos de campos de estado sem enum/constraint

```sql
SELECT 'clients.lead_status' campo, lead_status valor, COUNT(*) n FROM clients GROUP BY lead_status
UNION ALL SELECT 'clients.lead_source', lead_source, COUNT(*) FROM clients GROUP BY lead_source
UNION ALL SELECT 'clients.origin', origin, COUNT(*) FROM clients GROUP BY origin
UNION ALL SELECT 'clients.language', language, COUNT(*) FROM clients GROUP BY language
UNION ALL SELECT 'form_proposals.status', status, COUNT(*) FROM form_proposals GROUP BY status
UNION ALL SELECT 'form_proposals.origin', origin, COUNT(*) FROM form_proposals GROUP BY origin
UNION ALL SELECT 'form_proposals.source', source, COUNT(*) FROM form_proposals GROUP BY source
UNION ALL SELECT 'v3_vehicles.status', status, COUNT(*) FROM v3_vehicles GROUP BY status
UNION ALL SELECT 'v3_vehicles.purchase_type', purchase_type, COUNT(*) FROM v3_vehicles GROUP BY purchase_type
UNION ALL SELECT 'import_opportunities.status', status, COUNT(*) FROM import_opportunities GROUP BY status
UNION ALL SELECT 'import_opportunities.contact_status', contact_status, COUNT(*) FROM import_opportunities GROUP BY contact_status
UNION ALL SELECT 'proposals.status', status, COUNT(*) FROM proposals GROUP BY status
UNION ALL SELECT 'proposals.fuel', fuel, COUNT(*) FROM proposals GROUP BY fuel
UNION ALL SELECT 'converted_proposals.status', status, COUNT(*) FROM converted_proposals GROUP BY status
UNION ALL SELECT 'users.status', status, COUNT(*) FROM users GROUP BY status
UNION ALL SELECT 'tasks.status', status, COUNT(*) FROM tasks GROUP BY status
UNION ALL SELECT 'expenses.movement_type', movement_type, COUNT(*) FROM expenses GROUP BY movement_type
UNION ALL SELECT 'expenses.status', status, COUNT(*) FROM expenses GROUP BY status
UNION ALL SELECT 'expenses.category', category, COUNT(*) FROM expenses GROUP BY category
UNION ALL SELECT 'legalization_documents.tipo', tipo, COUNT(*) FROM legalization_documents GROUP BY tipo
UNION ALL SELECT 'vehicle_inspections.status', status, COUNT(*) FROM vehicle_inspections GROUP BY status
UNION ALL SELECT 'radar_search_runs.status', status, COUNT(*) FROM radar_search_runs GROUP BY status
UNION ALL SELECT 'radar_listings.source', source, COUNT(*) FROM radar_listings GROUP BY source
UNION ALL SELECT 'pre_leads.status', status, COUNT(*) FROM pre_leads GROUP BY status
UNION ALL SELECT 'car_candidates.status', status, COUNT(*) FROM car_candidates GROUP BY status
UNION ALL SELECT 'testimonials.origin', origin, COUNT(*) FROM testimonials GROUP BY origin;
```

### 6.9 Índices: existentes, redundantes e (se disponível) não usados

```sql
SELECT table_name, index_name, non_unique, GROUP_CONCAT(column_name ORDER BY seq_in_index) colunas
FROM information_schema.statistics WHERE table_schema = DATABASE()
GROUP BY table_name, index_name, non_unique ORDER BY table_name, index_name;

-- Requer o schema sys (MySQL 5.7+/8; pode não existir no alojamento partilhado)
SELECT * FROM sys.schema_redundant_indexes;
SELECT * FROM sys.schema_unused_indexes WHERE object_schema = DATABASE();
```

### 6.10 Tamanho de colunas pesadas (JSON/TEXT/BLOB)

```sql
SELECT 'sessions.payload' c, ROUND(SUM(LENGTH(payload))/1048576,2) mb FROM sessions
UNION ALL SELECT 'cache.value', ROUND(SUM(LENGTH(value))/1048576,2) FROM cache
UNION ALL SELECT 'jobs.payload', ROUND(SUM(LENGTH(payload))/1048576,2) FROM jobs
UNION ALL SELECT 'failed_jobs.payload+exception', ROUND(SUM(LENGTH(payload)+LENGTH(exception))/1048576,2) FROM failed_jobs
UNION ALL SELECT 'audit_logs.old/new_values', ROUND(SUM(LENGTH(old_values)+LENGTH(new_values))/1048576,2) FROM audit_logs
UNION ALL SELECT 'cost_simulators.isv_table', ROUND(SUM(LENGTH(isv_table))/1048576,2) FROM cost_simulators
UNION ALL SELECT 'consignment_evaluations.photos', ROUND(SUM(LENGTH(photos))/1048576,2) FROM consignment_evaluations
UNION ALL SELECT 'form_proposals.retoma_photos', ROUND(SUM(LENGTH(retoma_photos))/1048576,2) FROM form_proposals
UNION ALL SELECT 'news_articles.content', ROUND(SUM(LENGTH(content))/1048576,2) FROM news_articles
UNION ALL SELECT 'radar_listings.url', ROUND(SUM(LENGTH(url))/1048576,2) FROM radar_listings
UNION ALL SELECT 'radar_price_history (linhas)', COUNT(*) FROM radar_price_history;
```

### 6.11 Integridade de dados das tabelas financeiras e de funil

```sql
-- Duas mesmas receitas/despesas em expenses e financial_movements?
SELECT (SELECT COUNT(*) FROM expenses) AS expenses, (SELECT COUNT(*) FROM financial_movements) AS financial_movements;
-- Cotacoes convertidas sem tracking_code ou sem viatura V3
SELECT COUNT(*) total, SUM(tracking_code IS NULL) sem_tracking, SUM(v3_vehicle_id IS NULL) sem_v3, SUM(owner_id IS NULL) sem_dono FROM converted_proposals;
-- Clientes lead sem seguimento
SELECT lead_status, COUNT(*) n, SUM(next_followup_at IS NULL) sem_followup FROM clients WHERE is_lead = 1 GROUP BY lead_status;
-- Roles/permissoes: utilizadores sem role
SELECT COUNT(*) FROM users u WHERE NOT EXISTS (SELECT 1 FROM model_has_roles r WHERE r.model_id = u.id AND r.model_type LIKE '%User');
```
