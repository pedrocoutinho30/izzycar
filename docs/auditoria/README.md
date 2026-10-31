# Auditoria IzzyCar

Data: 2026-10-31. Plataforma Laravel 12 (PHP ≥ 8.2, MySQL): site público, backoffice V2/V3, versão mobile e portal de angariadores.

## Resumo

- **Estado geral:** a aplicação tem uma base funcional ampla e uma suite de testes razoável (765 testes; 764 passam, 1 falha: `ExampleTest`, já conhecido). Os riscos principais estão na autorização, na integridade dos dados e no legado V1 que ainda convive com o V3.
- **Mais urgente:**
  1. `PERMISSIONS_ENFORCE` desligado por omissão: o catálogo de permissões só regista (SEG-001).
  2. Ficheiro versionado com hash de password (SEG-002).
  3. Apagar clientes ou viaturas deixa dados órfãos ou apaga em cascata (arquitetura P1/P2).
  4. Erros de cálculo (IVA nas despesas V3) e operações sem transação (LOG-003, LOG-004).
  5. Tudo síncrono: fila `sync`, sem `app/Jobs`.
- **Contagens:** 36 problemas registados (18 de segurança: 2 altos, 8 médios, 8 baixos; 18 de lógica) mais os achados funcionais por módulo; 77 models, 88 tabelas e 1090 campos catalogados.

## Âmbito

Código PHP/Blade/JS do repositório, rotas, middleware, models, migrations, comandos agendados, integrações, assets e dependências. Interfaces: site público, backoffice desktop, mobile e portal angariador.

## Método e limitações (leitura obrigatória)

- **Revisão estática.** Leitura de código, `route:list` e `composer audit`/`npm audit --offline`. O que foi executado: `php artisan test` em sqlite em memória (fila `sync`, email `array`), `route:list` em sqlite em memória e os audits de dependências. Nada mais foi corrido e o site não foi testado em navegador.
- **Sem acesso à BD real.** O `.env` local aponta para produção e não foi usado. Logo:
  - o dicionário de BD é reconstruído a partir das 178 migrations por um parser estático **não validado contra um sqlite real** (a tentativa de `migrate` em sqlite foi recusada pelo sistema de permissões e não foi contornada);
  - existe *drift* conhecido entre repositório e produção (`clients.owner_id`, `users.commission_fixed_value`, colunas de comissão em `converted_proposals`);
  - contagens de registos, tamanhos, índices reais e `EXPLAIN` **não existem**; `base-de-dados.md` traz consultas de leitura para os obter;
  - «sem referências» na classificação **não** significa «nunca usado» (a confiança da classificação é média; nomes genéricos como `name`/`status` inflacionam contagens).
- **Benefícios de otimização são estimativas**, não medições. Plano de medição em `otimizacoes.md`.
- **Não revisto em profundidade:** valores do `.env` de produção, histórico git completo, scraper Python (só a interface com a BD), geração de PDF, `LeadService`/`ClientService`/`SaleV2Controller` por inteiro, serviços de relatórios, taxonomias, vistas Blade uma a uma. Cada documento tem o seu registo de cobertura.
- Nenhuma credencial, token ou dado pessoal foi copiado para estes documentos.
- Nada no código, na estrutura da BD ou nos dados foi alterado. Foram criados apenas ficheiros em `docs/auditoria/`.

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

## Decisões e informação em falta (do Pedro)

1. Valores de produção: `PERMISSIONS_ENFORCE`, `APP_DEBUG`, `APP_TIMEZONE`, drivers de fila/sessão/cache, `WHATSAPP_APP_SECRET`.
2. Resultado das consultas de diagnóstico da BD.
3. Política de retenção de `audit_logs` (RGPD).
4. Confirmação sobre as pastas de notas na raiz antes de qualquer remoção.
5. Reescrever ou não o histórico git por causa de `users/*.yaml`.
