# Auditoria IzzyCar

Data da auditoria: 2026-10-31; última revisão (estado final, até ao commit `dd006d8`): 2026-10-31. Plataforma Laravel 12 (PHP ≥ 8.2, MySQL): site público, backoffice V2/V3, versão mobile e portal de angariadores.

## Resumo

- **Estado geral:** a aplicação tem uma base funcional ampla e uma suite de testes sólida. À data da auditoria eram 765 testes (764 a passar); **no estado final são 1053 testes, 1052 passam e 1 falha: `ExampleTest`, a falha conhecida de sempre, não escondida** (eram 872 passados na revisão anterior e 764 na auditoria). Os riscos principais que restam estão na autorização (`PERMISSIONS_ENFORCE`), na integridade dos dados na eliminação, no fuso horário e em valores do `.env` de produção; o legado V1 sem rota já foi removido.
- **Mais urgente (estado final entre parênteses):**
  1. `PERMISSIONS_ENFORCE` desligado por omissão: o catálogo de permissões só regista (SEG-001). **Aberto.** O acesso aos ficheiros privados por tipo (SEG-015) depende disto para bloquear.
  2. Ficheiro versionado com hash de password (SEG-002). **Parcial:** fora do índice e ignorado; o hash continua no histórico git e a password não foi trocada.
  3. Apagar clientes ou viaturas deixa dados órfãos ou apaga em cascata (arquitetura P1/P2). **Aberto.**
  4. Erros de cálculo (IVA nas despesas V3) e operações sem transação (LOG-003, LOG-004). **Corrigido** (LOG-003) e **parcial** (LOG-004: falta índice único em `sales.v3_vehicle_id`).
  5. Tudo síncrono: fila `sync`, sem `app/Jobs`. **Aberto** (os emails do observer e dos formulários públicos já não perdem o pedido se falharem, mas continuam síncronos).
  6. Fuso horário `UTC` por omissão (LOG-002). **Aberto**, depende do `APP_TIMEZONE` de produção.
- **Contagens:** 36 problemas registados (18 de segurança: 2 altos, 8 médios, 8 baixos; 18 de lógica) mais os achados funcionais por módulo; 75 models (eram 77: `a33b12b` apagou `_Menu` e `ProposedCar`), 88 tabelas, 178 migrations e 1090 campos catalogados (o dicionário da BD não foi regenerado: ver nota em `base-de-dados.md`). Estado dos 36 problemas: **17 corrigidos, 15 parciais, 0 em curso, 4 abertos** (quadro completo em [problemas.md](problemas.md)). Os testes de cobertura encontraram 13 bugs: **8 corrigidos e 5 abertos** (secção própria em `problemas.md`).
- **Limpeza V1:** `a33b12b` apagou 20 controllers, 2 models e 47 views e reduziu 3 controllers (72 ficheiros, 10 465 linhas); ver [limpeza.md](limpeza.md).

## Estado das correções

Commits feitos a partir da auditoria (todos de 2026-10-31): `00eb765` (webhook, SSRF, uploads, filemanager, admins), `73f68de` (site público), `8da6d89` (dashboard), `ee9af9b` (IVA, venda, referência), `9616cc9` (clientes, observer, importação), `7e991a7` (agendador), `73df400` (`symfony/mime`, `users/`), `86550ca` (CSV, newsletter, HTML), `5d2a3be` (desempenho), `1e52c53` (limpeza), `3bb96ac` (financeiro e integrações), `68dbbe3` (segurança ronda 3), `058e135` (funil), `a33b12b` (remoção do código V1), `1bc16d9` (testes de cobertura), `2baf1d3` (viaturas), `dd006d8` (inspeções e webhook com token vazio). Os sete primeiros da lista da revisão anterior foram feitos antes do commit dos documentos (`f9f85fd`), que por isso os descrevia como por corrigir; `7450832` foi o commit da revisão intermédia dos documentos.

| Estado | Itens |
|---|---|
| Corrigido (17) | SEG-003, 004, 006, 009, 010, 011, 012, 013; LOG-001, 003, 005, 006, 008, 010, 011, 015, 017 |
| Parcial (15) | SEG-002, 005, 007, 008, 015, 017, 018; LOG-004, 007, 009, 012, 013, 014, 016, 018 |
| Em curso | nenhum |
| Aberto (4) | SEG-001, 014, 016; LOG-002 |
| Outros itens do plano | Corrigidos: rascunhos e viaturas offline em 404, vendas V3 como fluxo por omissão, tarefa do Modelo 9 idempotente, consignação que cria lead, queries do layout, limpeza V1, testes de cobertura. Abertos: fila real, índices, FKs e integridade de eliminação, retenção de dados, front-end, simulador de custos (300 contra 200) |

Sem alterações ao esquema: nenhuma migration foi criada desde a auditoria, por isso não há `php artisan migrate` pendente por causa destas correções. As migrations que o plano ainda prevê (índice único em `sales`, `NOT NULL` de `suppliers`/`testimonials`/`sales`, FKs, `jobs`) estão separadas em [plano-de-acao.md](plano-de-acao.md).

## Âmbito

Código PHP/Blade/JS do repositório, rotas, middleware, models, migrations, comandos agendados, integrações, assets e dependências. Interfaces: site público, backoffice desktop, mobile e portal angariador.

## Método e limitações (leitura obrigatória)

- **Revisão estática.** Leitura de código, `route:list` e `composer audit`/`npm audit --offline`. O que foi executado: `php artisan test` em sqlite em memória (fila `sync`, email `array`), `route:list` em sqlite em memória e os audits de dependências. Nada mais foi corrido e o site não foi testado em navegador.
- **Sem acesso à BD real.** O `.env` local aponta para produção e não foi usado. Logo:
  - o dicionário de BD é reconstruído a partir das 178 migrations por um parser estático **não validado contra um sqlite real** (a tentativa de `migrate` em sqlite foi recusada pelo sistema de permissões e não foi contornada);
  - existe *drift* conhecido entre repositório e produção (`clients.owner_id`, `users.commission_fixed_value`, colunas de comissão em `converted_proposals`);
  - contagens de registos, tamanhos, índices reais e `EXPLAIN` **não existem**; `base-de-dados.md` traz consultas de leitura para os obter;
  - «sem referências» na classificação **não** significa «nunca usado» (a confiança da classificação é média; nomes genéricos como `name`/`status` inflacionam contagens).
- **Benefícios de otimização são estimativas**, exceto as contagens de queries medidas em testes (dashboard 41 para 26, gráfico 6 para 1, `chart-data` 13 para 5, listagem pública com `utm`/`fbclid`/`page` a 0 queries a `v3_vehicle*`); tempos e pesos reais continuam por medir. Ver `otimizacoes.md`.
- **Não revisto em profundidade:** valores do `.env` de produção, histórico git completo, scraper Python (só a interface com a BD), geração de PDF, `LeadService`/`ClientService`/`SaleV2Controller` por inteiro, serviços de relatórios, taxonomias, vistas Blade uma a uma. Cada documento tem o seu registo de cobertura.
- Nenhuma credencial, token ou dado pessoal foi copiado para estes documentos.
- Na auditoria original, nada no código, na estrutura da BD ou nos dados foi alterado; foram criados apenas ficheiros em `docs/auditoria/`. As correções seguintes fizeram-se depois, em commits próprios.

## Índice

| Ficheiro | Conteúdo |
|---|---|
| [funcionalidades.md](funcionalidades.md) | Inventário funcional por módulo e interface, permissões, fluxos, estados, testes |
| [arquitetura.md](arquitetura.md) | Stack, middleware, permissões, 77 models (75 depois de `a33b12b`), diagramas Mermaid, problemas e propostas P1–P10 |
| [base-de-dados.md](base-de-dados.md) | Dicionário de tabelas e campos, histórico de esquema, consultas de diagnóstico |
| [utilizacao-base-de-dados.csv](utilizacao-base-de-dados.csv) | Tabela, campo, leituras, escritas, classificação, evidências, observações |
| [problemas.md](problemas.md) | SEG-001…018 e LOG-001…018 com evidência, impacto, correção e validação; bugs encontrados pelos testes de cobertura |
| [otimizacoes.md](otimizacoes.md) | Base de dados, backend, front-end, plano de medição |
| [limpeza.md](limpeza.md) | Candidatos a remoção com risco e verificações |
| [plano-de-acao.md](plano-de-acao.md) | Plano faseado e priorizado; o que falta por decisão, migration, dados de produção ou pronto para fazer; notas de deploy |
| [fluxo-lead-entrega.md](fluxo-lead-entrega.md) | Teste do percurso lead→entrega em MySQL real: estados, etapas, falhas e concorrência (FLX-001…023), guião manual e parecer |

## Histórico de revisões

| Data | Revisão |
|---|---|
| 2026-10-31 | Auditoria original (revisão estática), commit `f9f85fd` |
| 2026-10-31 | Revisão intermédia (`7450832`): estado de cada SEG/LOG, plano de ação, limpeza, otimizações (com as medições), funcionalidades e arquitetura. Fonte: `git log` de `00eb765` a `3bb96ac`. Suite: 872 passam, 1 falha conhecida. Itens "em curso" por atualizar no fim |
| 2026-10-31 | **Revisão final** (esta): fecha os "em curso" com a fonte `68dbbe3`, `058e135`, `a33b12b`, `1bc16d9`, `2baf1d3` e `dd006d8`, confirmados no código. `problemas.md`: 17 corrigidos, 15 parciais, 4 abertos; LOG-016 recontado (111 para 6 `route()` reais) e secção "Bugs encontrados pelos testes de cobertura". `plano-de-acao.md`: tarefas concluídas e pendentes separadas por tipo de bloqueio, notas de deploy. `limpeza.md`: resumo de `a33b12b`. `funcionalidades.md` e `arquitetura.md`: marcas e mapa de testes atualizados. Docs de domínio (`viaturas.md`, `leads-e-clientes.md`) ajustados. Suite: `php artisan test` com 1052 passam e 1 falha conhecida (`ExampleTest`), 6855 asserções |

## Decisões e informação em falta (do Pedro)

1. **`PERMISSIONS_ENFORCE`** (SEG-001): valor real em produção, análise do log `would_block` e plano para ligar por fases.
2. **Saldo de abertura** (`MovementService::INITIAL_BALANCE` = 15 757,28 €): vale antes de 2025? Os saldos de cada ano passaram a somar tudo o que veio antes, por isso o valor de abertura do primeiro ano com movimentos tem de estar certo; decidir se passa para as `settings`.
3. **`mail.admin_address` e fallbacks `.pt`/`.com`:** a chave não existe em `config/mail.php`; `SendFollowupReminder`, `SendOverdueCommissionAlert`, `SendAnnualReport` e `ReportsController` caem em `env('MAIL_FROM_ADDRESS', 'geral@izzycar.com')` (domínio `.com`, enquanto o resto usa `.pt`). Definir o endereço real e remover o `env()` fora de `config/`.
4. **`whereMonth` sem ano** que ficaram: `ProposalV2Controller.php:151` ("cotações do mês") e `ClientV2Controller.php:44` ("convertidos este mês") somam o mesmo mês de todos os anos; corrigir como no dashboard (`8da6d89`) se o pretendido é "este mês".
5. **Alteração por commitar no rodapé:** `resources/views/frontend/partials/frontend-footer-desktop.blade.php` tem uma linha modificada (o gradiente do `.cta-card` passa de `var(--accent-color)` para `var(--yellow-color)`, igual ao de `import.blade.php`). Não é uma mensagem de texto: confirmar se se quer commitar. A **mensagem de sucesso da subscrição** da newsletter (double opt-in) é outra coisa, já commitada: "Enviámos-lhe um email para confirmar a subscrição." (`NewsletterController.php:128`); confirmar o texto e o email `emails/newsletter-confirmation`.
6. **Simulador de custos público:** o cálculo (`CostSimulatorController`, linha 177) soma 300 EUR de "outros serviços" e a página de resultado (linha 245) soma 200 EUR; decidir o valor (ou passar para as `settings`). Também tem `Setting::...->first()->value` sem guarda: rebenta se faltar uma definição.
7. **`SHOW CREATE TABLE` de `suppliers`, `testimonials` e `sales`** (e as consultas de diagnóstico de `base-de-dados.md`): o esquema das migrations (`type` ENUM `NOT NULL`, `contact_name`/`email` `NOT NULL`, `testimonials.comment` `NOT NULL`, `sales.client_id`/`payment_method` `NOT NULL`) não bate com a validação dos controllers e dá 500; só com o esquema real se decide entre corrigir a validação ou fazer migration.
8. **Reescrever ou não o histórico git** por causa de `users/*.yaml` (o ficheiro já saiu do índice, mas o hash continua em commits antigos) e trocar a password associada.
9. **Valores do `.env` de produção** por confirmar: `PERMISSIONS_ENFORCE`, `APP_TIMEZONE` (LOG-002; sem ele os agendamentos e o "hoje" correm em UTC), `APP_DEBUG`, **`WHATSAPP_VERIFY_TOKEN`** (sem ele a verificação do webhook é sempre recusada) e **`WHATSAPP_APP_SECRET`** (sem ele o webhook recusa tudo com 403), `MAIL_BCC_ADDRESS`, drivers de fila, sessão e cache (a deduplicação por cache dos lembretes precisa de uma cache que persista entre execuções; `onOneServer` precisa de cache partilhada).
10. Política de retenção de `audit_logs` e dos logs (RGPD, `LOG_DAILY_DAYS`); confirmação sobre as pastas de notas na raiz antes de qualquer remoção; se se quer manter a regra de `final_payment` nas Definições (sem efeito desde LOG-015); CAPTCHA nos formulários públicos (opcional).

## Notas de deploy

1. `composer install --no-dev` e limpar a cache de bootstrap (`bootstrap/cache/*.php`, depois `php artisan config:clear`): saíram pacotes (`laravel-snappy`, `browsershot`, `legacy-factories`) e o script `pre-update-cmd`.
2. **Ordem:** correr `php artisan files:move-to-private` (simulação) e depois `php artisan files:move-to-private --apply` **antes** de publicar o código dos ficheiros privados (`68dbbe3`, sem fallback para o disco público); senão os ficheiros antigos deixam de abrir pela rota `admin.v2.files.show`.
3. Definir `WHATSAPP_VERIFY_TOKEN` (e confirmar `WHATSAPP_APP_SECRET`) no `.env` de produção.
4. Confirmar que o GD do servidor suporta WebP (upload otimizado das fotos de viaturas, `5d2a3be`).
5. Copiar à mão os ficheiros de `public/` alterados (a pasta pública do domínio é uma cópia separada).
6. Sem migrations novas: não há `php artisan migrate` pendente por causa das correções.

## O que ficou por verificar

- Tudo o que depende de produção: `.env`, esquema real das tabelas, FKs e órfãos, cache e fila, GD/WebP, o estado de `files:move-to-private`.
- A recontagem de LOG-016 foi feita por `grep` dos `route('nome')` literais: não apanha nomes construídos dinamicamente nem `route()` em JS.
- As contagens do dicionário da BD (88 tabelas, 1090 campos) vêm do parser estático da auditoria e não foram regeneradas depois da remoção dos models V1.
- O site continua sem teste em navegador; a verificação desta revisão foi por `git show`, leitura do código e `php artisan test`.
