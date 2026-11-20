# Marketing Studio

Área do backoffice (`/gestao/marketing`, rotas `admin.marketing.*`, permissão `marketing-posts` no módulo "Conteúdo do site") para criar, guardar e exportar publicações para as redes sociais. Só planeamento e exportação: **não publica nada automaticamente**.

## Fluxos
- **Publicação de viatura**: escolhe uma `V3Vehicle`, um template e um formato; os dados e as fotos são lidos do catálogo e guardados num instantâneo (`marketing_posts.data`).
- **Recomendação**: carro de fora (anúncio), com entrada manual e fotos carregadas (`marketing-posts/{id}/` no disco `public`). "Importar do Criador de Posts" copia os `recommendation_posts` antigos para rascunhos do Studio (idempotente, não altera os originais). O Criador de Posts antigo (`social-posts`) continua intacto.
- **Escolha do tipo** (`/gestao/marketing/criar`): Viatura, Recomendação, Educativo, Serviço, Importação, Testemunho. Só "Viatura" exige uma viatura do catálogo.
- **Educativo e Serviço** (sem fonte): escolhes um tema da biblioteca (`config/marketing.php`, só título e estrutura sugerida; **sem texto fiscal pré-escrito**), um template de texto (dica rápida, mito ou verdade, pergunta frequente, passo a passo, erros comuns, carrossel educativo, serviço) e escreves os itens no editor (pares a/b, ≤ 8). Temas fiscais/legais (`legal_check`) mostram "Verificar a legislação atual antes de publicar" e pedem confirmação ao exportar; um post sem conteúdo não exporta.
- **Testemunho**: só testemunhos com `testimonials.marketing_consent = true` (checkbox "Autoriza uso em publicações" nos formulários de testemunhos). O texto nunca é reescrito: o editor só aceita um excerto contínuo do comentário original (comentários > 280 caracteres exigem escolher um excerto). Por omissão só o primeiro nome; o nome completo é opção explícita.
- **Importação realizada**: parte de uma viatura com `is_imported` ou de entrada manual (marca, modelo, fotos validadas). Exige confirmar que a importação está concluída e que o cliente autorizou a divulgação. O instantâneo **nunca** inclui cliente, matrícula nem VIN e não guarda ligação à viatura (por isso "Atualizar dados" não se aplica). País de origem e poupança só aparecem se preenchidos.
- **Legenda com IA** (editor → Legenda): tom (profissional, premium, comercial, informativo, descontraído) e botão "Gerar legenda". Chamada à OpenAI no backend (`MarketingCaptionService`), prompt só com os factos do post. Sem chave, com erro/timeout/resposta inválida usa uma legenda determinística com os dados reais (HTTP 200, `ai=false` e aviso). Limite diário por utilizador (HTTP 429 sem chamar a OpenAI). Só preenche os campos: nunca grava sozinha. O registo (`marketing.caption`) não leva o texto.
- **Editor**: campos (título, subtítulo, preço, poupança, destaques, CTA, etiqueta), seleção/ordem de fotos, ponto focal (duplo clique numa foto escolhida), template e formato. A pré-visualização é o mesmo HTML que é exportado.
- **Exportação** (computador): PNG/JPG por slide e ZIP no carrossel, a 1080 px de largura, com `html2canvas` 1.4.1 e JSZip 3.10.1 (cdnjs). Legenda: copiar ou descarregar `.txt`.
- **Dashboard** (`/gestao/marketing/dashboard`): contagens reais por estado e por categoria, publicadas este mês, fila "prontas a publicar" (aprovadas e agendadas, as agendadas por data), em revisão e recentes. Sem métricas de alcance nem estimativas.
- **Calendário e agendamento** (`/gestao/marketing/calendario`): vista mensal/semanal, filtro por rede (Instagram, Facebook; "ambos" aparece nos dois). No editor, cartão "Agendamento" (data/hora e rede). Um post **aprovado** com data passa a "Agendado" (e volta a aprovado se perder a data); nunca passa a "Publicado" sozinho. Planeamento interno: não publica.
- **Plano de 30 dias**: botão no calendário cria 30 rascunhos (ver [marketing-plano-30-dias.md](marketing-plano-30-dias.md)). Viatura, testemunho, importação e recomendação ficam "por selecionar" até escolheres o conteúdo real.
- **Biblioteca**: filtro por tipo/estado/título; duplicar, editar, reexportar, eliminar.

## Regras de conteúdo (não inventar)
- Campo sem valor não é desenhado. Preço = `asking_price`; se for alterado no editor, aparece o aviso "difere do catálogo".
- "Vendido / Entregue" só com viatura `vendido`; "Novo em stock" só com `em_stock` (valida pelo instantâneo; "Atualizar dados" relê a viatura).
- Poupança só existe se o utilizador a preencher. Nunca é calculada.
- "Publicado" só por botão manual; o campo de estado do editor não o permite.
- As fotos das viaturas podem mostrar matrículas; não há desfoque automático. Rever antes de publicar.

## Configuração de IA (`.env`, opcional)
- `OPENAI_API_KEY` (já usada no projeto). Sem ela, as legendas são montadas sem IA.
- `MARKETING_AI_MODEL` (omissão `gpt-4o-mini`) e `MARKETING_AI_DAILY_LIMIT` (omissão `30` por utilizador e dia, contado em cache).

## Dados
- `marketing_posts` e `marketing_post_slides` (migrations `2026_11_19_100000/100100`). Tipo, estado e formato são strings (enums PHP em `app/Enums/Marketing*`).
- `data` = instantâneo (nunca alterado por edições); `config` = edições do utilizador (`overrides`, `photo_order`, `focal`, `gallery_*`). "Atualizar dados" só substitui `data`.

## Acrescentar um template
1. `resources/views/admin/marketing/templates/<chave>.blade.php` (recebe `$content`, `$slides`, `$format`, `$w`, `$h`; usa `<section class="mk-slide …">` com a dimensão inline; fotos com `templates/_photo`, nunca `object-fit` nem `backdrop-filter` nem `filter`, que o html2canvas não renderiza).
2. Estilos em `admin/marketing/_styles.blade.php` (variáveis da marca: `#111`, `#6e0707`, `#4a0505`, `#B59151`, Inter).
3. Entrada em `App\Marketing\TemplateRegistry::all()` com tipos, formatos e campos.
4. Os testes `MarketingTemplatesRenderTest` cobrem-no automaticamente.

## Deploy
- `php artisan migrate` (tabelas novas e `testimonials.marketing_consent`, migrations `2026_11_19_100000/100100` e `2026_11_20_100000`) — **ainda por correr em produção**. Depois, marcar a autorização nos testemunhos que a tenham (por omissão nenhum é elegível).
- Copiar à mão para a pasta pública (`public/` tem cópia separada): `public/fonts/inter/` e `public/img/marketing/izzycar-logo-dourado.png` (logótipo oficial recortado nas margens transparentes, pixels intactos).
- O JS do editor está inline nas views; não há build.

## Fora desta fase
Nada previsto: as 4 fases do plano estão concluídas. Evoluções possíveis: arrastar posts no calendário, publicação automática (fora do âmbito por decisão).
