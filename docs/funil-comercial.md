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
