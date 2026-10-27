# Funil comercial

Do primeiro contacto à venda ou legalização. Detalhes em [leads-e-clientes.md](leads-e-clientes.md), [cotacoes.md](cotacoes.md) e [viaturas.md](viaturas.md).

```
Entrada (site, WhatsApp, BO, angariador)
   │  ClientMatcher: mesmo email OU telefone → reaproveita o registo
   ▼
LEAD (Client, is_lead=true)   lead_status: nova → em_contacto → qualificada → fria | perdida
   │  pedido de importação
   ▼
PEDIDO DE FORMULÁRIO (FormProposal)   novo → em_analise → … → convertido
   │  1..n
   ▼
OPORTUNIDADES (ImportOpportunity)  carros/anúncios em análise, com checklist e contactos
   │  uma cotação por oportunidade ("selecionado")
   ▼
COTAÇÃO (Proposal)   Pendente → Enviado → Aprovada | Reprovada | Sem resposta
   │  aceite pelo cliente (página pública, proposal_code) ou pelo BO
   ▼
COTAÇÃO CONVERTIDA (ConvertedProposal)   Iniciada → … → Concluido | Cancelada
   │  cliente passa a is_lead=false; contrato enviado; movimentos financeiros automáticos
   ▼
VIATURA V3 (is_imported, reservado, show_online=false) + LEGALIZAÇÃO (7 passos)
   ▼
Entrega ao cliente; comissão do angariador
```

Viaturas **de stock** (venda a clientes) seguem outro ramo: nascem em stock (manual ou inspeção), são publicadas no site (`show_online`) e fecham com uma `Sale` e `status=vendido`.

## Regras transversais

- **Quem é o cliente**: um só registo ao longo do funil. A lead é convertida automaticamente quando a cotação é aceite (ou `Aprovada`).
- **Estados ligados**: aceitar a cotação põe o pedido de formulário em `convertido`; reprovar a cotação rejeita as oportunidades ligadas; apagar a cotação repõe as oportunidades `selecionado`.
- **Quem manda no dinheiro**: a cotação convertida gera movimentos (receitas das tranches, despesas pagas) sem `v3_vehicle_id`; a venda e a compra de stock geram movimentos com `v3_vehicle_id`. Margens: só `SaleCalculator`.
- **Identificadores públicos**: cotação por `proposal_code`; legalização por token; timeline da convertida só por id (enumerável).
- **Angariadores**: `owner_id`/`angariador_code` no cliente; só vêm a sua área (`admin.angariador.*`); a comissão da convertida é um instantâneo.
- **Produção**: o funil escreve em muitas tabelas; testar sempre em sqlite, nunca contra o `.env` remoto.

## Versão mobile

Backoffice simplificado para iPhone em `/gestao/mobile` (rotas `admin.mobile.*`, layout `layouts.admin-mobile`, instalável como PWA). Cobre o funil até à cotação convertida: leads, clientes, pedidos de formulário e oportunidades, cotações, convertidas (consulta e estado), movimentos financeiros e uma pesquisa (`/gestao/mobile/pesquisa`) por leads, clientes, cotações e pedidos.

- **Acesso**: só quem tem `users.access_mobile = true`; sem ele, qualquer rota mobile volta ao dashboard desktop com aviso (middleware `mobile.access`, depois de `auth` e `restrictAngariador` — o angariador puro continua barrado). O campo altera-se na ficha do utilizador por quem tem `users.update`, nunca no próprio (exceto admin) nem pelo perfil; cada mudança fica no log de auditoria. **Falta `php artisan migrate` em produção (coluna `users.access_mobile`).**
- **Mesma lógica do desktop**: os controllers de `Admin\Mobile` usam `LeadService`, `ClientService`, `FormProposalService`, `ProposalService`, `ConvertedProposalService`, `MovementService` e `GlobalSearchService` (consultas da pesquisa global). Uma regra de negócio nova vai para o service, não para o controller mobile.
- **Permissões**: as rotas mobile estão em `config/permissions.php` com nomes explícitos; as que filtram registo a registo (`applyScope`/`allows`) estão em `own_routes` e aceitam "próprios". O filtro é feito no controller, por isso vale mesmo com `PERMISSIONS_ENFORCE` desligado. A pesquisa é partilhada e só mostra as secções que o utilizador pode abrir, no seu âmbito (no mobile, `%` e `_` contam como texto).
- **Convertidas**: quem tem permissão de convertidas vê todas; quem só vê cotações vê as dos seus clientes (`ConvertedProposalService::viewScope`). Mudar o estado exige editar convertidas.
- **Simulador de custos**: consulta das simulações feitas no site (`/gestao/mobile/simulador-custos`, lista com pesquisa e `?client_id=`, e ficha com dados e custos), ligada às fichas de lead e cliente. O objeto `cost-simulators` não tem âmbito "próprios": quem pode ver, vê todas; eliminar fica no desktop. A lista mobile não marca as simulações como lidas.
- **Movimentos** (`/gestao/mobile/movimentos`, rotas `admin.mobile.movements.*`): lista em cartões com os filtros do desktop (pesquisa, tipo, categoria, estado, viatura, ano, datas e `?client_id=`), os mesmos totais e saldos dos cartões do desktop (entradas, saídas, saldo com pendentes e saldo real, do ano escolhido; com datas, de sempre) e o total dos resultados filtrados; "Carregar mais"; ficha; criar, editar e eliminar com a lógica do desktop (`MovementService`, partilhado com o `MovementV2Controller`; o `ExpenseObserver` sincroniza `FinancialMovement`). Os valores aceitam vírgula (`DecimalInput`); a pesquisa é literal. Os movimentos automáticos (`source_type`: venda, compra de viatura, cotação convertida) são só de consulta — editar/eliminar dá 403 mesmo num pedido forjado — e ligam à convertida mobile (se o utilizador a pode ver) ou à origem no desktop. A ficha da convertida liga aos movimentos dela (`?convertida=`) para quem vê movimentos. O objeto `movements` não tem âmbito "próprios": cada ação (ver/criar/editar/eliminar) verifica-se no controller. **Fica no desktop**: exportação XLSX, enviar/remover o comprovativo (no mobile só se abre o existente), gráficos/resultados e as rotas antigas `v2/expenses`.
- **Clientes criados no mobile ficam sem dono** (`owner_id` nulo), mesmo por quem só vê os "próprios".
- **Fica no desktop**: kanban, exportações XLSX, importar cotação a partir do link do anúncio, contrato em PDF, uploads de documentos e fotos, edição de pagamentos da convertida, criar/editar candidatos (carros) e editar atributos da cotação.
