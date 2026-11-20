# Marketing Studio — Fase 1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Entregar a base do Marketing Studio: criar posts a partir de viaturas reais, com templates, editor, exportação PNG/JPG/ZIP e biblioteca.

**Architecture:** Templates Blade parametrizados (1080 px de largura) descritos num registo PHP; o editor e a exportação usam o mesmo nó DOM, rasterizado no browser com `html2canvas` (já usado em `admin/v2/social-posts/recommendation.blade.php`). Posts guardam um instantâneo JSON dos dados da viatura; fotos servidas por rota autenticada do mesmo domínio.

**Tech Stack:** Laravel 12, Blade, Bootstrap, GD/Intervention, html2canvas 1.4.1 e JSZip 3.10.1 (cdnjs, versões fixas), PHPUnit sqlite.

**Spec:** `docs/superpowers/specs/2026-11-19-marketing-studio-design.md` (lê também a secção "Decisões do autor" e "Decisões do Pedro").

## Global Constraints

- Português de Portugal em toda a UI e conteúdos; seguir o código ao lado (comentários em português).
- Rotas `admin.marketing.*` dentro do grupo `gestao` de `routes/web.php`; coberta por `config/permissions.php` (`PermissionCatalogTest` tem de passar). Angariadores sem acesso nem menu.
- Controllers finos; lógica em `app/Marketing` e `app/Services`; validação em FormRequests (`authorize()` devolve `true`).
- Enums com `HasOptions`. Formatos: `feed_vertical` 1080×1350, `feed_square` 1080×1080, `story` 1080×1920.
- Estados do post: `rascunho|em_revisao|aprovado|agendado|publicado`; "publicado" só por ação manual.
- Migrations: FKs/índices com nomes curtos explícitos; **não correr `php artisan migrate`**; dizer no fim que falta correr em produção. Nunca editar migrations existentes.
- Testes: `Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations`; `ExampleTest` já falhava, ignorar mas não esconder.
- O Criador de Posts existente (`social-posts`) fica intacto (a importação só copia); não tocar em viaturas, leads ou cotações.
- JS em partials Blade (não em `public/js`) porque `public/` tem cópia separada no deploy; só as fontes vão para `public/fonts` (copiar à mão no deploy).
- Sem dados inventados: campo sem valor não é desenhado. Sem commits a menos que o Pedro peça (o formato quando pedido: `feat(marketing): …`).
- Html2canvas não suporta `object-fit` nem `backdrop-filter`: seguir as soluções já usadas em `recommendation.blade.php` (ver linhas ~330 e ~520).

## Review Focus

1. Viatura sem fotos → o fluxo avisa e o template renderiza sem fotografia, sem erro (teste no `VehiclePostData`).
2. Viatura sem `asking_price` → bloco de preço omitido (teste).
3. "Vendido/Entregue" com viatura `em_stock`, ou "Novo em stock" com viatura `vendido` → rejeitado (teste HTTP, 422).
4. Preço/estado da viatura muda depois do post → post antigo não muda; só `refresh` o altera (teste).
5. Rota de fotos: foto inexistente, de outra viatura ou sem sessão → 404/redirect, nunca ficheiro arbitrário (teste).

---

## Ficheiros

- Criar: `database/migrations/2026_11_19_100000_create_marketing_posts_table.php`, `…_100100_create_marketing_post_slides_table.php`
- Criar: `app/Models/MarketingPost.php`, `app/Models/MarketingPostSlide.php`
- Criar: `app/Enums/MarketingPostType.php`, `MarketingPostStatus.php`, `MarketingFormat.php`
- Criar: `app/Marketing/TemplateDefinition.php`, `TemplateRegistry.php`, `VehiclePostData.php`
- Criar: `app/Http/Controllers/Admin/MarketingPostController.php`, `MarketingPhotoController.php`
- Criar: `app/Http/Requests/MarketingPostRequest.php`
- Criar: `resources/views/admin/marketing/{index,create,editor}.blade.php`, `_editor-scripts.blade.php`, `templates/{premium,comercial,minimalista,equipamento,carrossel,novo-stock,vendido}.blade.php`, `templates/_base.blade.php`
- Criar: `public/fonts/inter/` (Inter 400/600/700 woff2, licença OFL)
- Modificar: `routes/web.php`, `config/permissions.php`, `resources/views/layouts/admin-v2.blade.php` (menu)
- Criar: `docs/marketing-studio.md`
- Testes: `tests/Feature/Marketing{Permissoes,Instantaneo,Fotos,Fluxo}Test.php`, `tests/Unit/MarketingTemplateRegistryTest.php`

---

### Task 1: Modelo de dados e enums

**Files:** migrations, `MarketingPost`, `MarketingPostSlide`, 3 enums, `tests/Feature/MarketingInstantaneoTest.php` (parte do modelo).

**Interfaces:**
- Produces: `MarketingPostType` (`Veiculo='veiculo'`, `Recomendacao='recomendacao'`, `Servico`, `Importacao`, `Educativo`, `Testemunho`), `MarketingPostStatus` (5 estados acima, `label()`, `color()`), `MarketingFormat` (casos `FeedVertical`, `FeedSquare`, `Story`; `width(): int`, `height(): int`, `label()`).
- Produces: `MarketingPost` com colunas `id, type, template, format, status, title, caption (text, null), hashtags (text, null), cta (null), tone (null), source_type (null), source_id (null), data (json), config (json), scheduled_for (datetime, null), published_at (datetime, null), channel (null), created_by, updated_by, timestamps`; casts para enums e arrays; relações `slides(): HasMany` (ordenadas por `position`) e `creator(): BelongsTo(User)`. `MarketingPostSlide`: `post_id, position, layout (null), content (json)`.

- [ ] **Step 1: Teste** `post_guarda_instantaneo_e_casts` — cria `MarketingPost` com `data=['brand'=>'BMW']` e `format='feed_square'`; afirma `$post->data['brand']==='BMW'`, `$post->format===MarketingFormat::FeedSquare` e `MarketingFormat::Story->height()===1920`.
- [ ] **Step 2:** Correr `php artisan test --filter=MarketingInstantaneoTest` → falha.
- [ ] **Step 3:** Escrever migrations (FK `mkt_slides_post_fk`, índices `mkt_posts_status_idx`, `mkt_posts_sched_idx`; `type`/`status`/`format` como `string`, não ENUM), modelos e enums.
- [ ] **Step 4:** Teste passa (sqlite em memória; nada é migrado em MySQL).

### Task 2: Permissões, rotas e menu

**Files:** `config/permissions.php`, `routes/web.php`, `layouts/admin-v2.blade.php`, `MarketingPostController@index` (vista mínima), `tests/Feature/MarketingPermissoesTest.php`.

**Interfaces:**
- Produces: módulo `marketing` com recurso `marketing-posts` (`routes: ['admin.marketing.*']`); atribuído ao perfil `cms` (`'marketing-posts' => '*'`). Rotas (nomes): `index` (GET `/gestao/marketing`), `create` (GET `/criar`), `store` (POST), `show`/`editor` (GET `/{post}/editor`), `update` (PUT `/{post}`), `duplicate` (POST `/{post}/duplicar`), `refresh` (POST `/{post}/atualizar-dados`), `mark-published` (POST `/{post}/publicado`), `destroy` (DELETE), `vehicles.search` (GET `/viaturas/pesquisa`), `photos.show` (GET `/fotos/{photo}`).

- [ ] **Step 1: Testes** — `admin_ve_indice`, `angariador_e_redirecionado_ou_negado` (mesmo comportamento que `AngariadorPortalTest` para rotas `gestao`), e `PermissionCatalogTest` existente continua verde.
- [ ] **Step 2:** Falham (404).
- [ ] **Step 3:** Registar rotas no grupo `gestao`; recurso em `config/permissions.php`; entrada de menu "Marketing Studio" junto de "Criador de posts" (seguir os dois blocos que listam `admin.v2.social-posts.*` em `admin-v2.blade.php`, linhas ~987 e ~1238, usando `@canroute`).
- [ ] **Step 4:** `php artisan test --filter="MarketingPermissoesTest|PermissionCatalogTest"` verde.

### Task 3: Fotos, fontes e logótipo

**Files:** `MarketingPhotoController`, `public/fonts/inter/*`, `tests/Feature/MarketingFotosTest.php`.

**Interfaces:**
- Produces: `MarketingPhotoController@show(V3VehiclePhoto $photo): Response` — lê o ficheiro do disco `public` (`$photo->path`), redimensiona com Intervention (máx. 2160 px no lado maior, qualidade 90) e devolve `image/jpeg` com `Cache-Control: private, max-age=3600`; 404 se o ficheiro não existir. Rota `admin.marketing.photos.show`.
- Consumes: `V3VehiclePhoto` (campos `path`, `focal_x`, `focal_y`).

- [ ] **Step 1: Testes** — `devolve_imagem_redimensionada` (foto 3000×2000 gerada com GD em `Storage::fake('public')` → resposta 200, largura ≤ 2160), `ficheiro_em_falta_da_404`, `sem_sessao_nao_acede`. `path` nunca vem do pedido, só da BD (cobre Review Focus 5).
- [ ] **Step 2:** Falham.
- [ ] **Step 3:** Implementar controller. Descarregar Inter woff2 400/600/700 da distribuição oficial (OFL) para `public/fonts/inter/`; **se o descarregamento falhar, parar e perguntar ao Pedro**. Confirmar qual logótipo de `public/img` serve em fundo escuro; se nenhum, **parar e perguntar** antes de criar variante.
- [ ] **Step 4:** Testes verdes.

### Task 4: Registo de templates e instantâneo da viatura

**Files:** `app/Marketing/*`, `tests/Unit/MarketingTemplateRegistryTest.php`, `tests/Feature/MarketingInstantaneoTest.php` (parte do builder).

**Interfaces:**
- Produces: `TemplateDefinition(key, name, types: MarketingPostType[], formats: MarketingFormat[], minSlides, maxSlides, view, fields: string[])`; `TemplateRegistry::all(): array<string,TemplateDefinition>`, `::get(string $key)`, `::forType(MarketingPostType $t): array`. Chaves: `premium`, `comercial`, `minimalista`, `equipamento`, `carrossel`, `novo-stock`, `vendido`.
- Produces: `VehiclePostData::fromVehicle(V3Vehicle $v): array` com chaves `vehicle_id, reference, status, brand, model, version, year, kilometers, fuel, power, price (asking_price|null), equipment (list<string> de `attributeValues` com nome do atributo; só valores existentes), photos (list<{id, path, focal_x, focal_y, is_cover}> ordenadas), captured_at`.
- Produces: `VehiclePostData::assertTemplateAllowed(array $data, string $templateKey): void` — lança `\DomainException` se `vendido` com `status!='vendido'` ou `novo-stock` com `status!='em_stock'`.

- [ ] **Step 1: Testes** — registo: todas as chaves existem e cada `view` existe em `resources/views/admin/marketing/templates`; `fromVehicle` omite `price` quando `asking_price` é null e devolve `photos=[]` sem fotos (Review Focus 1 e 2); `assertTemplateAllowed` lança nos dois casos (Review Focus 3); a lista de equipamentos nunca inclui valores vazios.
- [ ] **Step 2:** Falham.
- [ ] **Step 3:** Implementar (equipamentos/caixa: ver `VehicleAttribute` para os nomes reais; só incluir "caixa" se existir como atributo).
- [ ] **Step 4:** Verdes.

### Task 5: Fluxo "criar publicação" e gestão

**Files:** `MarketingPostController`, `MarketingPostRequest`, `create.blade.php`, `tests/Feature/MarketingFluxoTest.php`.

**Interfaces:**
- Consumes: `VehiclePostData`, `TemplateRegistry`.
- Produces: `vehicles.search` devolve JSON `[{id, reference, label, status, cover_url}]` (pesquisa por marca/modelo/referência, máx. 20); `store` cria o post com `data = VehiclePostData::fromVehicle`, `source_type=V3Vehicle`, `status=rascunho`, `created_by=auth()->id()` e redireciona para `editor`; `duplicate` copia (estado `rascunho`, título com " (cópia)"); `refresh` substitui `data` pelo instantâneo atual e só então; `mark-published` define `status=publicado` e `published_at=now()` apenas por POST explícito.

- [ ] **Step 1: Testes** — `store_cria_post_com_instantaneo`; `store_rejeita_vendido_para_viatura_em_stock` e `…novo_stock_para_viatura_vendida` (422, Review Focus 3); `alterar_viatura_nao_muda_post_antigo` e `refresh_atualiza_so_quando_pedido` (Review Focus 4); `duplicate_volta_a_rascunho`; `mark_published_exige_post_e_define_data`.
- [ ] **Step 2:** Falham.
- [ ] **Step 3:** Implementar. Ecrã `create` em passos simples: pesquisa de viatura (JS com fetch), template (filtrado por `forType` e pelos formatos do template), formato.
- [ ] **Step 4:** Verdes.

### Task 5b: Posts de recomendação e importação dos antigos

**Files:** `app/Marketing/RecommendationPostData.php`, `MarketingPostController` (`createRecommendation`, `storeRecommendation`), `MarketingRecommendationImporter` (`app/Services`), `MarketingPhotoController@post` , `create.blade.php`, `tests/Feature/MarketingRecomendacaoTest.php`.

**Interfaces:**
- Produces: `RecommendationPostData::fromInput(array $v, array $photoPaths): array` com chaves `brand, model, version, mileage, power, fuel, year, equipment (máx. 4, sem vazios), price, savings, url, photos (list<{path, focal_x, focal_y}>), captured_at` — campos vazios omitidos; `savings` e `price` nunca calculados.
- Produces: `storeRecommendation(Request)` valida como o `SocialPostController@store` atual (marca e modelo obrigatórios, `gallery_photos` máx. 12 imagens ≤ 8 MB, `url` válido) e guarda ficheiros em `marketing-posts/{post id}/` (disco `public`); cria `MarketingPost(type=recomendacao, status=rascunho)`.
- Produces: rotas `admin.marketing.recommendations.create|store` e `admin.marketing.post-photos.show` (GET `/fotos-post/{post}/{index}`, índice na lista `data.photos`, mesmo redimensionamento da Task 3, 404 fora do intervalo); `admin.marketing.import-recommendations` (POST).
- Produces: `MarketingRecommendationImporter::import(): array{imported:int, skipped:int}` — para cada `RecommendationPost` sem post importado (marca `data.legacy_id`), copia os ficheiros de `image` e `gallery_photos` para `marketing-posts/{id}/` e cria o post em rascunho com `gallery_layouts` e `gallery_photos_per_slide` em `config`. Idempotente; não altera nem apaga os registos ou ficheiros antigos.

- [ ] **Step 1: Testes** — `store_recomendacao_guarda_fotos_e_instantaneo` (`Storage::fake('public')`, 2 fotos válidas), `rejeita_ficheiro_que_nao_e_imagem`, `rejeita_mais_de_12_fotos`, `equipamento_limitado_a_4_linhas`, `sem_poupanca_nao_aparece_no_instantaneo`, `importacao_copia_sem_alterar_antigos` (os `RecommendationPost` e ficheiros originais intactos), `importacao_e_idempotente` (2.ª execução → `imported=0`), `foto_de_post_fora_do_indice_da_404`, `angariador_nao_importa`.
- [ ] **Step 2:** Falham.
- [ ] **Step 3:** Implementar. Botão "Importar do Criador de Posts" no índice (com confirmação e contagem do resultado). Campos do formulário iguais aos atuais; ecrã criado em passos como o do veículo, partilhando o seletor de template/formato.
- [ ] **Step 4:** Verdes.

### Task 6: Base dos templates e editor com exportação

**Files:** `templates/_base.blade.php`, `editor.blade.php`, `_editor-scripts.blade.php`, `MarketingPostController@editor/update`.

**Interfaces:**
- Produces: `_base.blade.php` recebe `$content` (array normalizado: `title, subtitle, price, highlights[], photos[{url,focal_x,focal_y}], cta`), `$format` (`MarketingFormat`), `$config` e desenha um nó `#studio-canvas` com a dimensão do formato, com variáveis CSS para cores (`--mk-primary:#6e0707`, `--mk-primary-dark:#4a0505`, `--mk-gold:#B59151`, `--mk-ink:#111`) e `@font-face` Inter local. Cada template `@include`-a a base ou estende-a.
- Produces: `update` grava `title, caption, hashtags, cta, config, format, template`, e `data.overrides` (título/subtítulo/preço/destaques/fotos escolhidas) sem tocar no resto do instantâneo; preço alterado fica marcado `price_overridden=true`.
- Editor (JS): painel de campos (título, subtítulo, preço, destaques ligar/desligar, CTA), seletor de foto principal e fotos de carrossel, ponto focal por clique, mudança de template/formato; pré-visualização escalada por `transform: scale()`; botões "Exportar PNG/JPG" (um slide) e "ZIP" (carrossel) com `html2canvas(node,{width,height,scale:1,useCORS:true})` depois de `await document.fonts.ready`; legenda em textarea com botão "Copiar" e descarregar `.txt`. Em ecrãs < 992 px mostra aviso "Exportar no computador".

- [ ] **Step 1: Teste** `update_guarda_overrides_sem_alterar_instantaneo_original` e `preco_alterado_fica_assinalado` (HTTP).
- [ ] **Step 2:** Falham.
- [ ] **Step 3:** Implementar. Verificar manualmente no browser: exportado a 1080×N, texto idêntico à pré-visualização, fotos não deformadas.
- [ ] **Step 4:** Testes verdes e verificação manual registada em `docs/marketing-studio.md`.

### Task 7: Os oito templates

**Files:** `templates/{premium,comercial,minimalista,equipamento,carrossel,novo-stock,vendido,recomendacao}.blade.php`.

`recomendacao` (tipo `recomendacao`, formatos feed vertical/quadrado/story): foto dominante, marca/modelo/versão, 3–4 características, preço, e bloco de "poupança" só se existir, mais CTA. Os templates `premium`, `comercial`, `minimalista`, `equipamento` e `carrossel` também aceitam o tipo `recomendacao`. Slides de galeria: 1 ou 3 fotos por slide com os layouts de grelha do Criador de Posts atual (referência: `photoGridHtml` em `recommendation.blade.php`), redesenhados com o visual do Studio; `config.gallery_layouts` escolhe o layout por slide.

**Interfaces:** consomem o contrato de `_base`. Cada um declara os formatos suportados no `TemplateRegistry` (`carrossel`: 3–8 slides com capa, exterior, interior, equipamentos, características e CTA; `vendido`/`novo-stock`: feed vertical e quadrado).

- [ ] **Step 1: Teste** em `MarketingTemplateRegistryTest`: para cada template e formato suportado, renderizar a view com dados de exemplo, com e sem fotos/preço, e verificar que não lança e que não aparece "R$"/"null"/texto de placeholder.
- [ ] **Step 2:** Falha.
- [ ] **Step 3:** Desenhar cada template com variedade real (Premium: foto dominante e tipografia 600/700 com espaço negativo; Comercial: preço em destaque + 3 características + CTA; Minimalista: foto, modelo, preço, logo; Equipamento: foto + lista de destaques; Carrossel; Novo em stock; Vendido/Entregue), sempre com o logótipo oficial, cores oficiais e sem emojis, gradientes exagerados ou sombras fortes.
- [ ] **Step 4:** Testes verdes; exportar cada template em todos os formatos suportados e inspecionar (screenshot) antes de dar por concluído.

### Task 8: Biblioteca e histórico

**Files:** `index.blade.php`, `MarketingPostController@index`.

- [ ] **Step 1: Teste** `indice_lista_posts_com_filtros` (filtra por estado e tipo; mostra autor, template, formato e datas) e `destroy_remove_post_e_slides`.
- [ ] **Step 2:** Falha.
- [ ] **Step 3:** Listagem com cartões (miniatura = pré-visualização escalada), filtros, ações duplicar/editar/reexportar/eliminar; componentes `components/admin/page-header`, `filter-bar`, `empty-state`.
- [ ] **Step 4:** Verde.

### Task 9: Documentação e verificação final

- [ ] **Step 1:** Escrever `docs/marketing-studio.md` (arquitetura, como adicionar um template, regras de conteúdo, deploy: copiar `public/fonts/inter` à mão, correr `php artisan migrate` em produção) e referenciá-lo em `CLAUDE.md` na lista de documentação.
- [ ] **Step 2:** `php artisan test` completo: tudo verde exceto `ExampleTest` (reportar à parte).
- [ ] **Step 3:** Verificação manual por template/formato (lista no doc) e confirmação de que o Criador de Posts existente continua a funcionar.
- [ ] **Step 4:** Resumo ao Pedro: o que ficou, o que falta correr em produção, o que fica para as Fases 2–4.
