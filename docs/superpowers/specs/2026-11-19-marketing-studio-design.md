# Marketing Studio — design

Data: 2026-11-19 · Estado: para aprovação

## Intenção
Transformar o backoffice numa ferramenta de criação de conteúdos de marketing: gerar posts (Instagram/Facebook) a partir de dados reais (viaturas, testemunhos, importações) ou de conteúdos de serviços/educativos, com templates reutilizáveis, editor visual com pré-visualização fiel, legendas com IA, biblioteca e, mais tarde, calendário. Objetivo de negócio: mais contactos qualificados, com comunicação rigorosa (nada inventado).

## Objetivos
- Área "Marketing Studio" integrada em `/gestao`, com autenticação, layout `layouts.admin-v2` e componentes existentes.
- Pré-visualização = ficheiro exportado (mesmo HTML/CSS).
- Pelo menos 6 templates distintos, parametrizados, adicionáveis sem tocar no editor.
- Dados e fotos das viaturas carregados do catálogo `V3Vehicle`/`V3VehiclePhoto`.
- Exportação PNG/JPG em 1080×1350, 1080×1080, 1080×1920; carrossel individual ou ZIP; legenda e hashtags em texto.
- Posts guardam um instantâneo dos dados; alterações posteriores à viatura não mexem em posts antigos; "atualizar dados" é explícito.

## Não-objetivos
- Publicação automática nas redes sociais; APIs externas (só planeamento interno).
- Métricas fictícias ou de alcance/engagement.
- Novo logótipo, novas cores ou nova identidade.
- Alterar viaturas, leads, cotações ou outras áreas existentes.

## Anti-objetivos (falha mesmo que "funcione")
- Preços, equipamentos, testemunhos, importações ou regras fiscais inventados.
- Mostrar "Vendido/Entregue" para viatura que não está vendida.
- Expor dados pessoais, matrículas ou testemunhos sem autorização.
- Diferenças visíveis entre pré-visualização e exportação.
- Chave de IA no frontend.

## Restrições do projeto
- Laravel 12, PHP 8.2, Blade, Bootstrap; sem Imagick (só GD); alojamento Hostinger (sem Chromium assumido).
- Rotas em `routes/web.php` dentro do grupo `gestao`; cada rota coberta por `config/permissions.php` (`PermissionCatalogTest`). Novo módulo `marketing` com recursos (posts, biblioteca, dashboard, calendário).
- Controllers finos; lógica em `app/Services`; validação em FormRequests; enums com `HasOptions`.
- Migrations: nomes curtos de FKs/índices, sem editar as aplicadas; **não correr migrate** (dizer que falta em produção). Testes em sqlite em memória com `RefreshesDatabaseWithoutMysqlOnlyMigrations`.
- Português de Portugal em toda a interface e conteúdos. Commits só quando pedido.

## Identidade visual (existente, a confirmar na Fase 1)
Logótipos em `public/img` (`logo-transparente.png`, `logo_final.png`, `logo-simples.png`); bordeaux `#6e0707`/`#4a0505`, dourado `#B59151`/`#92642D`, preto `#111`; tipografia Inter. Os templates usam estes tokens como variáveis. Se faltar algum recurso (ex. logótipo claro para fundos escuros), pára-se e pergunta-se antes de criar alternativa.

## Abordagens consideradas
- **A. HTML/CSS no browser + rasterização no cliente (escolhida).** Templates Blade partilhados por editor e exportação; funciona no Hostinger; um só código de renderização. Risco: fontes/imagens cross-origin — mitigado servindo Inter e fotos do mesmo domínio (via rota própria de imagem) e testando cada template.
- B. Puppeteer/Chromium no servidor: máxima fidelidade, mas depende de infraestrutura que provavelmente não existe. Possível evolução futura reutilizando os mesmos templates.
- C. GD/Intervention no servidor: corre em qualquer lado, mas duas implementações (editor vs. exportação), tipografia fraca, quebra a fidelidade.

## Arquitetura
**Templates.** Cada template = partial Blade `resources/views/admin/marketing/templates/<chave>.blade.php` + entrada num registo PHP (`App\Marketing\TemplateRegistry`): chave, nome, tipos de conteúdo suportados, formatos suportados, campos editáveis, nº de slides e valores por omissão. O partial recebe um array normalizado `content` (título, subtítulo, preço, destaques, fotos, CTA…) e `format` (dimensões); desenha-se a 1080 px de largura e escala-se com CSS `transform` no ecrã. Adicionar template = partial + entrada no registo.

**Exportação.** JS no cliente rasteriza o nó de 1080 px (biblioteca `html-to-image`, versão fixa, servida localmente) para PNG/JPG; carrossel em ZIP via JSZip. Fontes embebidas localmente (Inter em `public/fonts`). Fotos servidas por rota autenticada do mesmo domínio para evitar canvas contaminado. Não deformar fotos (`object-fit` + ponto focal de `V3VehiclePhoto`).

**Dados.**
- `marketing_posts`: id, tipo (`veiculo|servico|importacao|educativo|testemunho`), template, formato, estado (`rascunho|em_revisao|aprovado|agendado|publicado`), título, legenda, hashtags, CTA, tom, `source_type`/`source_id` (nullable, ex. V3Vehicle/Testimonial), `data` JSON (instantâneo dos dados usados), `config` JSON (config visual e posições), `scheduled_for`, `published_at`, `channel`, `created_by`, `updated_by`, timestamps.
- `marketing_post_slides`: post_id, posição, template/layout do slide, `content` JSON.
- Fotos escolhidas guardam o caminho no instantâneo, não só o id.
- Duplicar copia o post (estado volta a rascunho). "Atualizar dados" relê a fonte e substitui o instantâneo apenas por ação explícita.
- "Publicado" só por ação manual de confirmação.

**Regras de conteúdo.**
- Veículo: só campos existentes; equipamentos vêm de `VehicleAttributeValue`; sem dado → o campo não aparece. Preço = `asking_price`, editável no post mas assinalado se diferir do catálogo.
- "Vendido/Entregue" só selecionável se `status = vendido`.
- Testemunho: texto de `Testimonial.comment`, nunca reescrito; só ficam elegíveis testemunhos com autorização de uso. Exige acrescentar campo de autorização (`marketing_consent`) a `testimonials` (migration pequena, nullable/default false).
- Importações: lista de `V3Vehicle` com `is_imported`/cliente quando entregues; senão entrada manual. Sem nome de cliente nem matrícula por omissão.
- Serviços/educativos: biblioteca de conteúdos em ficheiro de configuração/seed próprio, escritos de forma geral e marcados "verificar legislação antes de publicar" nos temas fiscais; sem valores de taxas.

**IA (Fase 2).** `MarketingCaptionService` no backend usando a `OPENAI_API_KEY` já configurada (`config/services.php`), tom selecionável, prompt só com dados do instantâneo, limite de tamanho, timeout, tratamento de erros com mensagem clara, registo de pedidos e limite por utilizador/dia para controlar custos. Resultado sempre editável.

**Permissões.** Módulo `marketing` em `config/permissions.php` (posts, biblioteca, dashboard, calendário); middleware existente; validação também nos controllers/Requests. Menu: nova entrada no layout do BO.

## Fases
1. **Base:** migrations, módulo/menu/permissões, registo de templates, 6 templates (Premium, Comercial, Minimalista, Destaque de equipamento, Carrossel, Novo em stock + Vendido/Entregue), fluxo Veículo, editor, exportação, biblioteca/histórico (duplicar, editar, reexportar).
2. **Legendas e outros tipos:** IA, serviços, importações, educativos, testemunhos.
3. **Dashboard** (contagens reais por estado e categoria, próximas e recentes).
4. **Calendário editorial** (semana/mês, filtro por rede) **e plano de 30 dias** (documento + posts em rascunho com conteúdo real pendente de seleção).
Testes por fase (sqlite): permissões, instantâneo/atualização explícita, elegibilidade vendido/testemunho, exportação em teste manual por template e formato.

## Deixado ao construtor
Nomes exatos de rotas/colunas, biblioteca de rasterização e versão, desenho concreto dos templates dentro das regras acima, estrutura do editor (campos e arrastar/posição por elemento), textos dos conteúdos educativos.

## Decisões do autor (a rever)
- **Fotos:** guardadas no disco `public` (`storage/app/public`, via symlink `public/storage`). O editor lê-as por uma rota autenticada do mesmo domínio que serve uma cópia redimensionada (máx. 2160 px no lado maior, GD) para não estourar memória; o aviso de baixa resolução usa as dimensões do original. Atenção ao deploy: o symlink/cópia de `public/` é separado, por isso a rota lê do disco, não de `public/storage`.
- **Inter:** auto-hospedada em `public/fonts` (pesos 400/600/700, licença OFL), copiada à mão para a pasta pública no deploy. Logótipo: usa-se o existente; se nenhum servir em fundo escuro, pára-se e pergunta-se antes de criar variante.
- **Formatos:** cada template declara os formatos que suporta; nem todos têm de suportar os três. Stories só onde o layout o permitir.
- **Estados da viatura:** `em_stock`, `reservado`, `vendido`. "Novo em stock" exige `em_stock`; "Vendido/Entregue" exige `vendido`. Se o estado da viatura mudar depois, o post mantém o instantâneo e mostra um aviso de divergência.
- **Testemunhos:** `marketing_consent` (boolean, default false) entra na Fase 2 com um checkbox no formulário existente de testemunhos; os atuais ficam não elegíveis até serem marcados. O nome do cliente só aparece se houver consentimento; por omissão usa-se só o primeiro nome.
- **IA (Fase 2):** modelo económico configurável por `.env`, limite diário por utilizador configurável, pedidos registados no canal de log existente.

## Decisões do Pedro (confirmadas)
- **Editor Fase 1:** campos de texto, escolha de fotos, ponto focal e alternância de elementos, com pré-visualização ao vivo. Sem arrastar elementos (fica como evolução futura).
- **Exportação:** computador (Chrome) na Fase 1; no mobile mostra-se aviso. Sem matriz Safari/iOS.
- **Plano de 30 dias:** 30 posts em rascunho no calendário (conteúdo real por selecionar) + resumo em Markdown em `docs/`.
- **Acesso (decisão do autor, o Pedro não indicou):** admin tem acesso total (Gate::before); o módulo `marketing` fica atribuível a outros perfis pelo ecrã de perfis; angariadores sem acesso e sem entrada no menu.

## Alteração de âmbito (2026-11-19, pedida pelo Pedro): posts de recomendação
O Pedro quer passar a criar mais conteúdo para redes sociais e pediu que os posts de recomendação do atual Criador de Posts (`recommendation_posts`) passem para o Marketing Studio, com novo layout.
- Novo tipo `recomendacao` (`MarketingPostType::Recomendacao`), na Fase 1. É um carro de fora recomendado (não é `V3Vehicle`): entrada manual com os mesmos campos de hoje (marca, modelo, versão, km, potência, combustível, ano, até 4 linhas de equipamento, preço, poupança, URL do anúncio), foto principal e galeria até 12 fotos carregadas, validadas como imagem (máx. 8 MB). Guardadas em `storage/app/public/marketing-posts/`, servidas pela rota autenticada.
- Poupança e preço só aparecem se preenchidos; nunca calculados nem inventados.
- Mantém as grelhas de galeria (1 ou 3 fotos por slide, layouts escolhíveis) e ganha os templates do Studio, com o novo visual, em vez do layout antigo. Exporta nos 3 formatos.
- **Migração dos posts existentes:** ação explícita "Importar do Criador de Posts" que copia cada `RecommendationPost` (e copia os ficheiros de imagem) para um `MarketingPost` em rascunho. Não apaga nem altera os antigos. O Criador de Posts antigo fica no menu, intacto, até o Pedro decidir retirá-lo.

## Riscos
- Fidelidade fontes/imagens na rasterização (mitigada).
- Falta de dados de equipamentos/caixa em algumas viaturas (campos omitidos).
- Fotos de qualidade baixa: avisar no editor com base nas dimensões.
