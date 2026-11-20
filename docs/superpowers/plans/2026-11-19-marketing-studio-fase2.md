# Marketing Studio — Fase 2 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deixar de obrigar a escolher uma viatura: criar posts educativos, de serviços, de importações realizadas e de testemunhos, e gerar legendas com IA em todos os tipos.

**Architecture:** Mesma arquitetura da Fase 1 (instantâneo em `data`, edições em `config.overrides`, templates Blade + `html2canvas`). `PostContent` passa a ramificar por tipo; posts sem fonte guardam o conteúdo escrito pelo utilizador em `data`. Legendas por um serviço de backend com a chave OpenAI só no servidor.

**Tech Stack:** Laravel 12, Blade, `Http` client (Guzzle), OpenAI chat completions (já usado no projeto), PHPUnit sqlite.

**Spec:** `docs/superpowers/specs/2026-11-19-marketing-studio-design.md`. Fase 1: `docs/superpowers/plans/2026-11-19-marketing-studio-fase1.md` e `docs/marketing-studio.md` (ler antes: instantâneo, overrides, `TemplateRegistry`, regras de conteúdo).

## Global Constraints

- Tudo da Fase 1 continua válido (PT-PT, rotas `admin.marketing.*`, `config/permissions.php`, controllers finos, FormRequests, migrations seguras sem `migrate`, testes sqlite, sem commits a menos que pedido, sem `object-fit`/`backdrop-filter`/`filter` nos templates).
- **Nada de factos inventados.** Educativos e serviços não trazem texto fiscal/legal pré-escrito: a biblioteca só dá temas e estruturas; o utilizador escreve. Temas fiscais/legais (`legal_check=true`) mostram aviso "Verificar a legislação atual antes de publicar" no editor e pedem confirmação antes de exportar.
- Testemunhos: só `Testimonial.marketing_consent = true`; texto nunca reescrito (só excerto contínuo do original); por omissão só o primeiro nome.
- Importações: sem nome do cliente nem matrícula; exige confirmação explícita de autorização de divulgação.
- IA: chave só no backend (`config('services.openai.key')`); prompt só com o instantâneo; sem chave ou em erro há fallback determinístico (não bloqueia o utilizador); limite diário por utilizador; resultado sempre editável e nunca grava sozinho.
- Variáveis novas de `.env` (opcionais): `MARKETING_AI_MODEL` (omissão `gpt-4o-mini`), `MARKETING_AI_DAILY_LIMIT` (omissão `30`). Documentar em `docs/marketing-studio.md`.

## Review Focus

1. Post educativo sem conteúdo preenchido → exportar bloqueado e preview sem blocos vazios nem "null" (teste).
2. Excerto de testemunho que não existe no original, ou testemunho sem consentimento → 422 (teste).
3. IA sem chave, com erro HTTP, com timeout ou resposta inválida → fallback e HTTP 200 (teste com `Http::fake`).
4. Limite diário excedido → 429 sem chamar a OpenAI (teste).
5. Importação sem a confirmação de autorização → 422; nunca surge nome de cliente nem matrícula no instantâneo (teste).

---

## Ficheiros

- Criar: `config/marketing.php` (biblioteca de temas + limites de IA), `app/Marketing/TextPostData.php`, `TestimonialPostData.php`, `ImportPostData.php`, `app/Services/MarketingCaptionService.php`, `app/Services/MarketingPhotoStore.php`
- Criar: `app/Http/Controllers/Admin/MarketingContentController.php` (educativo/serviço/importação/testemunho), `MarketingCaptionController.php`
- Criar: views `admin/marketing/create.blade.php` (passa a escolha de tipo), `vehicle-create.blade.php` (o atual), `content-create.blade.php`; templates `dica-rapida, mito-verdade, pergunta-frequente, passo-a-passo, erros-comuns, educativo-carrossel, servico, importacao, testemunho`
- Criar: migration `2026_11_20_100000_add_marketing_consent_to_testimonials_table.php`
- Modificar: `PostContent`, `PostRenderer`, `TemplateRegistry`, `MarketingPostController` (index/create/editor/update), `MarketingPostUpdateRequest`, `routes/web.php`, `TestimonialController` + views `admin/v2/testimonials/{create,edit}`, `Testimonial` (fillable/cast), `_editor-scripts`, `editor`, `index`
- Testes: `tests/Feature/Marketing{Tipos,Educativos,Testemunhos,Importacoes,Legendas}Test.php`

---

### Task 1: Escolha do tipo ("Criar publicação")

**Interfaces:**
- Produces: `admin.marketing.create` (GET `/criar`) mostra os 6 tipos; `admin.marketing.vehicles.create` (GET `/criar/viatura`) é o formulário atual de viatura; rotas por tipo: `admin.marketing.content.create` (GET `/criar/{type}`, type ∈ `educativo|servico|importacao|testemunho`, outro → 404) e `admin.marketing.content.store` (POST `/criar/{type}`). Botões da biblioteca (`index`) passam a apontar para o ecrã de tipos.
- Atualizar `MarketingFluxoTest::test_pagina_de_criar_abre` e `MarketingBibliotecaTest` para as novas rotas.

- [ ] **Step 1: Testes** (`MarketingTiposTest`): `escolha_mostra_os_seis_tipos` (ver "Viatura", "Recomendação", "Educativo", "Serviço", "Importação", "Testemunho"), `formulario_de_viatura_continua_a_funcionar` (GET `vehicles.create` 200), `tipo_desconhecido_da_404`, `angariador_nao_acede`.
- [ ] **Step 2:** Falham. **Step 3:** Implementar (renomear a view atual, novo ecrã de cartões). **Step 4:** Verdes + `PermissionCatalogTest`.

### Task 2: Educativos e serviços (sem fonte)

**Interfaces:**
- Produces: `config('marketing.topics')`: lista de `{key, type: educativo|servico, title, template, legal_check: bool, hint}` só com temas e estrutura (ex.: "O que é o ISV?", `legal_check=true`, `hint='Explica o conceito e remete para o simulador'`); **sem** texto fiscal. Inclui os temas da spec (ISV, DAV, homologação, COC, elétrico, custos, importar vs. comprar, usado, documentação, isenção por transferência de residência; serviços: importação, legalização, consultoria, simulação, ISV, apoio documental, transporte).
- Produces: `TextPostData::fromInput(array $v): array` com `title, subtitle?, items: list<{a:string, b:string}> (≤8), cta?, legal_check: bool, topic?`; itens com `a` e `b` vazios são descartados.
- Produces: `PostContent::build` devolve também `kind` (`vehicle|text|quote|import`), `items`, `legal_check`; `PostContent::slides` para `mito-verdade` e `educativo-carrossel`: capa + um slide por item + CTA (máx. `maxSlides`). Os overrides passam a aceitar `items[]{a,b}`.
- Produces: templates (todos só texto, identidade Izzycar, 3 formatos exceto onde indicado): `dica-rapida` (título + 1 item), `mito-verdade` (item = `a` mito / `b` verdade, carrossel), `pergunta-frequente` (`a` pergunta, `b` resposta), `passo-a-passo` (≤6 itens numerados, `a` passo, `b` detalhe), `erros-comuns` (≤5), `educativo-carrossel`, `servico` (título, subtítulo, ≤4 pontos, CTA; foto opcional).
- Produces: `MarketingContentController@create/store` para `educativo|servico`: escolhe tema (opcional, preenche só o título e a estrutura), template e formato → cria post em rascunho com `source_type=null`.
- Editor: bloco "Conteúdo" com lista dinâmica de pares (a/b) quando o template é de texto; aviso `legal_check`; `exportNodes` pede `confirm()` se `legal_check`, e fica bloqueado com aviso "Preenche o conteúdo" se `items` vazios.

- [ ] **Step 1: Testes** (`MarketingEducativosTest`): `biblioteca_de_temas_nao_traz_texto_fiscal` (cada tópico só tem as chaves `key,type,title,template,legal_check,hint` e `hint` ≤ 160 caracteres), `temas_fiscais_marcam_legal_check` (ISV, DAV, isenção por transferência de residência), `store_cria_post_sem_fonte`, `post_vazio_nao_gera_blocos_nem_null` (render sem itens), `preview_com_itens_mostra_o_texto_escapado`, `carrossel_educativo_cria_um_slide_por_item`, `tipos_de_texto_so_aceitam_templates_de_texto`, `legal_check_aparece_no_editor`, mais teste de render por template/formato (alargar `MarketingTemplatesRenderTest`).
- [ ] **Step 2:** Falham. **Step 3:** Implementar. Validar visualmente cada template como na Fase 1 (Chrome headless + `html2canvas`; screenshots em scratchpad). **Step 4:** Verdes.

### Task 3: Testemunhos

**Interfaces:**
- Produces: migration `testimonials.marketing_consent` (boolean, default false, sem backfill); `Testimonial` fillable/cast; checkbox "Autoriza uso em publicações" nos formulários `create`/`edit` e validação em `TestimonialController@store/update`.
- Produces: `TestimonialPostData::fromTestimonial(Testimonial $t): array` com `testimonial_id, comment, rating, origin, first_name, review_date`; lança `\DomainException` se `marketing_consent` for falso ou o comentário for vazio. `quote` por omissão = `comment` inteiro (se ≤ 280 caracteres; senão exige o utilizador escolher um excerto).
- Produces: `GET marketing/testemunhos/pesquisa` (JSON; só com consentimento); template `testemunho` (aspas grandes, estrelas desenhadas em CSS, nome, origem); `overrides.quote` validado em `update`/`preview`: tem de ser excerto contínuo do `comment` (ignorando maiúsculas e espaços repetidos), senão 422; `overrides.show_full_name` (boolean, omissão falso) só afeta o nome.

- [ ] **Step 1: Testes** (`MarketingTestemunhosTest`): `sem_consentimento_da_422`, `com_consentimento_cria_post_com_primeiro_nome`, `excerto_inexistente_da_422`, `excerto_valido_aparece_na_imagem`, `comentario_nao_e_reescrito` (o `comment` no instantâneo é idêntico ao original), `pesquisa_so_lista_testemunhos_autorizados`, `formulario_guarda_consentimento`, `atualizar_dados_nao_apanha_testemunho_que_perdeu_consentimento` (refresh bloqueado).
- [ ] **Step 2:** Falham. **Step 3:** Implementar. **Step 4:** Verdes. Lembrar o Pedro de marcar o consentimento nos testemunhos existentes (por omissão nenhum é elegível).

### Task 4: Importações realizadas

**Interfaces:**
- Consumes: `V3Vehicle` com `is_imported=true` (pesquisa `vehicles.search` aceita `imported=1`), ou entrada manual.
- Produces: `ImportPostData::fromVehicle(V3Vehicle $v, array $extra): array` e `::fromManual(array $v, array $photoPaths): array` com `brand, model, year?, origin_country?, delivered_at?, highlights (≤4), savings? (manual, "poupança comprovada"), photos, authorized: true`; **nunca** inclui `client_id`, nome do cliente, matrícula (`registration`) nem VIN.
- Produces: `MarketingPhotoStore::store(MarketingPost $post, array $files): list<string>` (extraído da recomendação: `ImageOptimizerService::storeOptimized` em `marketing-posts/{id}/`, máx. 12, 8 MB) usado por recomendação e importação manual; `MarketingRecommendationController` passa a usá-lo (testes da Fase 1 não mudam).
- Produces: template `importacao` com título por omissão "Mais um {Marca} entregue em Portugal!" e subtítulo "Importado de {país}" (só se o país existir), editáveis; `store` exige `authorized=true` ("confirmo que a importação está concluída e o cliente autorizou a divulgação"), senão 422.

- [ ] **Step 1: Testes** (`MarketingImportacoesTest`): `sem_autorizacao_da_422` (Review Focus 5), `instantaneo_nao_tem_cliente_matricula_nem_vin`, `titulo_por_omissao_usa_a_marca`, `sem_pais_nao_ha_subtitulo_de_origem`, `manual_guarda_fotos_validadas`, `pesquisa_filtra_importadas`, `recomendacao_continua_a_guardar_fotos` (regressão do refactor).
- [ ] **Step 2:** Falham. **Step 3:** Implementar. **Step 4:** Verdes.

### Task 5: Legendas com IA

**Interfaces:**
- Produces: `MarketingCaptionService::generate(MarketingPost $post, string $tone, int $userId): array{caption:string, hashtags:string, cta:string, ai:bool, warning:?string}`; tons: `profissional|premium|comercial|informativo|descontraido`. Lança `\App\Marketing\CaptionLimitExceeded` se o utilizador excedeu `config('marketing.ai_daily_limit')` (contagem em `Cache`, chave por utilizador e dia, antes de chamar a OpenAI).
- Produces: pedido `Http::withToken(key)->timeout(25)->post('https://api.openai.com/v1/chat/completions', …)` com modelo `config('marketing.ai_model')`, `response_format` JSON (`caption, hashtags, cta`), `max_tokens 700`; prompt de sistema: PT-PT, não inventar dados, sem afirmações fiscais/legais nem prazos, ≤ 900 caracteres, CTA relevante, 5–8 hashtags; prompt de utilizador com **só** o instantâneo e o tipo/tom/orientação por tipo (viatura: características e benefícios concretos; serviço: problema → pedido de contacto; importação: experiência e resultado; testemunho: satisfação sem exageros; educativo: informativo e útil). Fallback determinístico (`ai=false`, `warning` explicativo) sem chave, em erro HTTP, timeout, JSON inválido ou campos em falta: legenda montada com os dados reais + CTA + hashtags base (`#izzycar` e do tipo).
- Produces: `POST marketing/{post}/legenda` → JSON; `429` se limite excedido; validação do tom. Registo em `Log` (post, utilizador, tom, `ai`, tokens) **sem** o texto. Editor: seletor de tom + botão "Gerar legenda" que preenche os campos (confirma se já houver legenda) sem gravar.

- [ ] **Step 1: Testes** (`MarketingLegendasTest`, `Http::fake`): `sem_chave_usa_fallback_com_dados_reais`, `resposta_valida_preenche_legenda_hashtags_e_cta`, `erro_http_usa_fallback_e_devolve_200`, `json_invalido_usa_fallback`, `prompt_so_tem_dados_do_instantaneo` (afirma que o corpo enviado contém a marca e não contém `client`, `registration` nem o e-mail do utilizador), `limite_diario_da_429_sem_chamar_a_openai` (`Http::assertNothingSent`), `tom_invalido_da_422`, `chave_nunca_aparece_no_html_do_editor`, `angariador_nao_gera`.
- [ ] **Step 2:** Falham. **Step 3:** Implementar. **Step 4:** Verdes.

### Task 6: Fecho da Fase 2

- [ ] **Step 1:** Atualizar `docs/marketing-studio.md` (tipos, biblioteca de temas, consentimento de testemunhos, `.env` de IA, deploy: `php artisan migrate` da migration de consentimento) e o plano.
- [ ] **Step 2:** `php artisan test` completo (só o `ExampleTest` pode falhar); verificação visual de todos os templates novos nos formatos suportados (Chrome headless nativo e `html2canvas`).
- [ ] **Step 3:** Revisão final por agente independente (`code-reviewer`), um passe de correções com teste RED→GREEN, e resumo ao Pedro (o que falta correr em produção, decisões, diferidos).
