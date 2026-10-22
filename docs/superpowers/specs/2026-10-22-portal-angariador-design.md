# Portal do angariador alinhado com o backoffice — desenho

Data: 2026-10-22. Estado: aprovado e implementado.

## Objetivo

O angariador vê, para as leads/clientes **dele**, quase o mesmo que o cliente vê nas cotações e nas cotações convertidas, com a terminologia do backoffice normal.

## Alterações

- **Menu:** Painel · Leads · Cotações · Cotações convertidas · Comissões · Manual · FAQ. "Propostas" passou a "Cotações" (`admin.angariador.cotacoes`); "Formulários" desapareceu (os pedidos de importação estão na ficha da lead). `/propostas` e `/formularios` redirecionam.
- **Ficha da lead (angariador):** pedidos de importação (só leitura, sem oportunidades nem análise de mercado, que levam margens internas), cotações e cotações convertidas dessa lead.
- **Cotações:** lista com o valor total (`Proposal::totalCost()`, a mesma soma que o cliente vê).
- **Cotações convertidas:** lista (`admin.angariador.convertidas`) e detalhe (`.show`). Mostra veículo, cliente, estado, etapas com datas, valor final e links para o que o cliente vê. Não mostra custos individuais, tranches, pagamentos, contactos do stand, observações internas nem documentos. A comissão fica na página Comissões.
- **Acesso:** só registos com `clients.owner_id` igual ao angariador autenticado; o resto dá 404. As rotas das cotações convertidas estão em `shared` em `config/permissions.php` (o filtro está no controller).
- **Estado de lead `qualificada`:** nova etapa entre `em_contacto` e `fria`/`perdida`, para leads que já recebem cotações. Conta como ativa. Só a equipa a atribui; o angariador vê a coluna no kanban mas não pode largar leads nela. Manual (não há promoção automática).

## Fora de âmbito

- Promoção automática para `qualificada` quando a 1.ª cotação é enviada.
- A página de acompanhamento do cliente abre com o id sequencial, sem login (exposição já existente).
