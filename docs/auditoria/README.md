# Auditoria IzzyCar

Data da auditoria: 2026-10-31; última revisão (estado após as correções): 2026-10-31. Plataforma Laravel 12 (PHP ≥ 8.2, MySQL): site público, backoffice V2/V3, versão mobile e portal de angariadores.

## Resumo

- **Estado geral:** a aplicação tem uma base funcional ampla e uma suite de testes razoável. À data da auditoria eram 765 testes (764 a passar); **depois das correções são 873 testes, 872 passam e 1 falha: `ExampleTest`, a falha conhecida de sempre, não escondida**. Os riscos principais estão na autorização, na integridade dos dados e no legado V1 que ainda convive com o V3.
- **Mais urgente (estado actual entre parênteses):**
  1. `PERMISSIONS_ENFORCE` desligado por omissão: o catálogo de permissões só regista (SEG-001). **Aberto.**
  2. Ficheiro versionado com hash de password (SEG-002). **Parcial:** fora do índice e ignorado; o hash continua no histórico git.
  3. Apagar clientes ou viaturas deixa dados órfãos ou apaga em cascata (arquitetura P1/P2). **Aberto.**
  4. Erros de cálculo (IVA nas despesas V3) e operações sem transação (LOG-003, LOG-004). **Corrigido** (LOG-003) e **parcial** (LOG-004: falta índice único em `sales.v3_vehicle_id`).
  5. Tudo síncrono: fila `sync`, sem `app/Jobs`. **Aberto** (os emails do observer já não rebentam a ação, mas continuam síncronos).
- **Contagens:** 36 problemas registados (18 de segurança: 2 altos, 8 médios, 8 baixos; 18 de lógica) mais os achados funcionais por módulo; 77 models (em `HEAD`), 88 tabelas, 178 migrations e 1090 campos catalogados. Estado dos 36 problemas: **15 corrigidos, 13 parciais, 3 em curso, 5 abertos** (quadro completo em [problemas.md](problemas.md)). A árvore de trabalho tem entretanto menos ficheiros (por exemplo 75 models) por causa da limpeza V1 em curso, ainda não commitada; as contagens acima são as do `HEAD`.

## Estado das correções

Commits feitos a partir da auditoria (todos de 2026-10-31): `00eb765` (webhook, SSRF, uploads, filemanager, admins), `73f68de` (site público), `8da6d89` (dashboard), `ee9af9b` (IVA, venda, referência), `9616cc9` (clientes, observer, importação), `7e991a7` (agendador), `73df400` (`symfony/mime`, `users/`), `86550ca` (CSV, newsletter, HTML), `5d2a3be` (desempenho), `1e52c53` (limpeza), `3bb96ac` (financeiro e integrações). Os sete primeiros foram feitos antes do commit dos documentos (`f9f85fd`), que por isso os descrevia como por corrigir.

| Estado | Itens |
|---|---|
| Corrigido | SEG-003, 004, 009, 010, 011, 013; LOG-001, 003, 005, 006, 008, 010, 011, 015, 017 |
| Parcial | SEG-002, 005, 006, 007, 008, 018; LOG-004, 007, 009, 012, 013, 014, 018 |
| Em curso (outros trabalhos em paralelo; **não** dados como feitos) | SEG-012, SEG-015, SEG-017; vazamento de rascunhos de notícias; vendas V3 no menu; Modelo 9 duplicado; simulador de custos; consignação; queries do layout; limpeza V1; testes novos |
| Aberto | SEG-001, 014, 016; LOG-002, 016; fila real, índices, FKs e integridade de eliminação, retenção de dados, front-end |

Sem alterações ao esquema: nenhuma migration foi criada, por isso não há `php artisan migrate` pendente por causa destas correções.

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
| [arquitetura.md](arquitetura.md) | Stack, middleware, permissões, 77 models, diagramas Mermaid, problemas e propostas P1–P10 |
| [base-de-dados.md](base-de-dados.md) | Dicionário de tabelas e campos, histórico de esquema, consultas de diagnóstico |
| [utilizacao-base-de-dados.csv](utilizacao-base-de-dados.csv) | Tabela, campo, leituras, escritas, classificação, evidências, observações |
| [problemas.md](problemas.md) | SEG-001…018 e LOG-001…018 com evidência, impacto, correção e validação |
| [otimizacoes.md](otimizacoes.md) | Base de dados, backend, front-end, plano de medição |
| [limpeza.md](limpeza.md) | Candidatos a remoção com risco e verificações |
| [plano-de-acao.md](plano-de-acao.md) | Plano faseado e priorizado |

## Histórico de revisões

| Data | Revisão |
|---|---|
| 2026-10-31 | Auditoria original (revisão estática), commit `f9f85fd` |
| 2026-10-31 | Atualização após as correções: estado de cada SEG/LOG, plano de ação, limpeza, otimizações (com as medições), funcionalidades e arquitetura (marcas "Corrigido" e secção "Alterações após a auditoria"), nota em `base-de-dados.md`. Fonte: `git log` de `00eb765` a `3bb96ac` e leitura do código. Suite: 872 passam, 1 falha conhecida (`ExampleTest`). Itens "em curso" por atualizar no fim |

## Decisões e informação em falta (do Pedro)

1. Valores de produção: `PERMISSIONS_ENFORCE`, `APP_DEBUG`, `APP_TIMEZONE`, drivers de fila/sessão/cache, `WHATSAPP_APP_SECRET`.
2. Resultado das consultas de diagnóstico da BD.
3. Política de retenção de `audit_logs` (RGPD).
4. Confirmação sobre as pastas de notas na raiz antes de qualquer remoção.
5. Reescrever ou não o histórico git por causa de `users/*.yaml` (o ficheiro já saiu do índice, mas o hash continua em commits antigos) e trocar a password associada.
6. **Saldo de abertura** (`MovementService::INITIAL_BALANCE` = 15 757,28 €): vale antes de 2025? Os saldos de cada ano passaram a somar tudo o que veio antes, por isso o valor de abertura do primeiro ano com movimentos tem de estar certo; decidir se passa para as `settings`.
7. **`mail.admin_address` e fallbacks `.pt`/`.com`:** a chave não existe em `config/mail.php`; `SendFollowupReminder` e `SendOverdueCommissionAlert` caem em `env('MAIL_FROM_ADDRESS', 'geral@izzycar.com')` (domínio `.com`, enquanto o resto usa `.pt`). Definir o endereço real e remover o `env()` fora de `config/`.
8. **`whereMonth` sem ano** que ficaram: `ProposalV2Controller.php:151` ("cotações do mês") e `ClientV2Controller.php:44` ("convertidos este mês") somam o mesmo mês de todos os anos; corrigir como no dashboard (`8da6d89`) se o pretendido é "este mês".
9. **Mensagem do rodapé** da subscrição da newsletter: passou a "Enviámos-lhe um email para confirmar a subscrição." (double opt-in); confirmar o texto e se o email de confirmação (`emails/newsletter-confirmation`) está como se quer.
10. **Valores do `.env` de produção** que continuam por confirmar (ver ponto 1): `PERMISSIONS_ENFORCE`, `APP_TIMEZONE` (LOG-002), `APP_DEBUG`, `WHATSAPP_APP_SECRET` (sem ele o webhook agora recusa tudo com 403), `MAIL_BCC_ADDRESS`, drivers de fila/sessão/cache (a deduplicação por cache dos lembretes precisa de uma cache que persista entre execuções).
11. Se se quer manter a regra de `final_payment` nas Definições, agora sem efeito (a 2.ª tranche é sempre 100 menos a 1.ª, LOG-015).
