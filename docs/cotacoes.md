# Cotações

No código chamam-se `Proposal` ("proposta"/"cotação"). Cotação aceite gera uma `ConvertedProposal` (cotação convertida), que é o processo de importação em curso. Estado verificado no código em 2026-10; **(por confirmar)** marca o que não foi verificado.

## Cotação (`app/Models/Proposal.php`)

- **Conteúdo**: cliente, viatura (brand, model, version, year, mileage, `fuel`…), custos (`transport_cost` obrigatório, `ipo_cost`, `imt_cost`, `registration_cost`, `isv_cost`, `license_plate_cost`, `inspection_commission_cost`, `commission_cost`, `iuc_cost`), `proposed_car_*`, `images`, `notes` (internas), `url`, `other_links`, atributos (`ProposalAttributeValue`).
- `proposal_code`: 12 caracteres únicos (inicial da marca + inicial do modelo + 8 aleatórios). É o identificador **público**: nunca expor o id sequencial.
- `fuel`: string (era ENUM; migration `2026_10_06_120000`). Valores aceites nos controllers: Gasolina, Diesel, Gasolina (HEV), Diesel (HEV), Híbrido Plug-in/Gasolina, Híbrido Plug-in/Diesel, Elétrico. A lista repete-se em `ProposalV2Controller` (`store`, `update`) e `ProposalController`. Ao acrescentar um valor, mudar todos, as views de formulário e o enum `VehicleFuel` (`proposalLabel()` converte oportunidade→cotação).
- `images`: o V2 grava uma string (caminho canónico), apesar do cast para array.
- **Validade**: `VALIDITY_DAYS = 15`, contados desde `created_at` (editar ou reenviar não renova). `isExpired()`.
- **Estados** (strings): `Pendente`, `Enviado`, `Aprovada`, `Reprovada`, `Sem resposta`. A migration original define `Enviada`; o código usa `Enviado` **(por confirmar o que a BD real tem)**.
- **Mudar de estado**: usar sempre `ProposalAcceptanceService::changeStatus`. Qualquer estado pode ir a qualquer outro, exceto: uma cotação já aceite só admite `Aprovada` (para anular, cancelar a convertida). `Aprovada` passa sempre por `accept()`; `Reprovada` marca as oportunidades ligadas como `rejeitado`. `ProposalController::updateStatus` (V1) não cria convertida; updates em massa ignoram o observer.
- `ProposalObserver`: `created`/`updated` gravam `AuditLog`; passar a `Enviado` dispara `notifyProposalSent` (`ProposalSentMail` ao cliente e, ao dono, versão `forAngariador`; agenda follow-up a +2 dias se vazio); passar a `Aprovada` converte a lead. Os mails saem de forma síncrona dentro do observer. `deleting` repõe oportunidades `selecionado` a `em_analise`.

## Criar e editar

- **V2 (em uso)**: `Admin/ProposalV2Controller`, rotas `admin.v2.proposals.*`: `create`, `createFromForm/{formProposalId}`, `createFromOpportunity/{opportunity}`, `importFromListing`, `store`, `edit`, `update`, `destroy`, `updateStatus`, `accept`, `bulkReject`, `bulkUpdateStatus`.
- **V1** (`ProposalController`): só `detail`, `accept` e `requestAlternativeQuote` têm rota. Os outros métodos existem sem rota; não os reativar.
- `store`: nasce sempre `Pendente`; o estado pedido é aplicado no fim por `changeStatus`. Com `import_opportunity_id` liga a oportunidade (`linkProposal`, passa a `selecionado`) e usa a foto dela se não houver upload. Com `form_proposal_id` liga o pedido e passa-o de `novo` a `em_analise`.
- `update` recusa mudar o estado de uma cotação já aceite e recria todos os atributos.
- Não há ação "enviar": basta o estado ser `Enviado`.

## Página pública

`GET proposta/{proposal_code}` (`proposals.detail`, `ProposalController::detail`): cotação expirada mostra `proposals.expired`; uma reprovada continua visível mas não se pode aceitar. Mostra "outras opções" (oportunidades do mesmo pedido, exceto rejeitadas); `POST …/opcoes/{opportunity}/pedir-cotacao` envia `AlternativeQuoteRequestedMail` ao admin e não reenvia.

## Aceitação

`ProposalAcceptanceService::accept($proposal, $clientData, $byClient)` (transação com `lockForUpdate`):
- `byClient=true` (público): recusa expirada, reprovada ou já aceite. Backoffice (`byClient=false`) aceita qualquer uma (não já aceite).
- Só preenche campos **vazios** do cliente (`CLIENT_FIELDS`), converte a lead, põe `Aprovada`, cria a `ConvertedProposal` e o histórico `Iniciada`, passa os pedidos ligados a `convertido` e regista atividade. O índice único `converted_proposals.proposal_id` impede duplicados.
- `convertedData`: copia dados e custos (mapa em `ProposalAcceptanceService`); tranches = 50% + 50% de transporte, inspeção, IPO, IMT, registo, matrícula e comissão (**ISV, valor do carro e IUC ficam de fora**); `owner_id` copiado do cliente.
- **Só a aceitação pelo cliente** (`ProposalController::accept`, `POST proposta/{code}/aceitar`, `throttle:5,1`) gera e envia o contrato e os mails (`sendAcceptanceEmail`: PDF do `ContractService`, guardado em `converted-proposals/{id}/` (disco `local`) como `ConvertedProposalDocument` `gerado`; `ProposalAcceptedMail` ao cliente com cc/bcc internos). Se o envio falha, a aceitação mantém-se. A aceitação pelo backoffice não envia nem gera nada; o contrato vê-se depois em "Ver contrato".

## Contrato

`ContractService::generateContractPdf($client, $convertedProposal)` + `resources/views/pdf/contract_service.blade.php` (dompdf): logo, prestador (nome, NIF, telefone, email; morada fixa Oliveira de Azeméis), cliente, veículo e cotação, prazos, pagamentos (`init_payment`, `final_payment`, IBAN), cláusulas e assinatura do prestador (`assinatura_prestador`, disco `public`). Botão "Ver contrato" na cotação convertida (`admin.v2.converted-proposals.contract`) gera-o na hora. O template `clients/contract_service.blade.php` é antigo e não é o enviado. Ver o agent `pdf-documents`.

## Cotação convertida (`ConvertedProposal`)

- **Campos**: cliente, dono, viatura (brand, `modelCar`, version, year, km, matrículas origem e destino), custos e flags de pagamento (inspeção, transporte, IPO, ISV, IMT, matrícula, registo, valor do carro), tranches e datas, comissão (`valor_comissao`, `angariador_commission`, `comissao_paga`), `v3_vehicle_id`, `comprovativo_pagamento`, `contactos_stand`, `observacoes`.
- **Pipeline** (`PIPELINE_STATUSES`): Iniciada, Negociação Carro, Pagamento do Carro, Transporte, IPO, DAV, ISV, Matriculação, IMT, Entrega, Registo automóvel, Concluido. Cancelada: `Cancelada` (V2) e `Cancelado` (V1); `scopeNotCancelled` exclui os dois.
- Hooks do modelo: marcar tranche paga fixa a data (desmarcar limpa); mudar `owner_id` refixa `angariador_commission` a partir do valor do utilizador (instantâneo); `saved`/`deleted` sincronizam movimentos; `updated` com mudança de `status` cria `StatusProposalHistory`.
- `isCommissionOverdue()`: >48h desde a entrega, comissão por pagar, não cancelada (comando `commissions:overdue-alert`).
- Controllers: `Admin/ConvertedProposalV2Controller` (lista, editar, documentos, `contract`, `createVehicle`) e `ConvertedProposalController` (V1, mas `updateStatus` é a rota usada pelo pipeline V2: valida o estado, grava e envia `ProposalStatusUpdatedMail` com try/catch). Timeline pública `converted-proposals.timeline` (usa só o id; marca/modelo/versão do URL são decorativos).
- **Criar viatura** (`createVehicle`, exige `vehicles.create`): ver [viaturas.md](viaturas.md).

## Financeiro

`Expense::syncFromConvertedProposal` corre em cada `saved`: **receitas** (`import_service`) das tranches pagas; **despesas** de comissão (usa `angariador_commission`, não `valor_comissao`), inspeção, transporte, IPO, ISV, IMT, matrícula e registo, só enquanto estão pagas (desmarcar apaga). O valor do carro fica de fora. Sem `v3_vehicle_id` de propósito (a viatura é do cliente). Os movimentos com `source_type` não se editam em `v2/movements`, só na convertida. `ExpenseObserver` sincroniza `FinancialMovement`.

## Armadilhas

1. `ConvertedProposalV2Controller::index`: os cartões "Em Processo" e "Concluídas" filtram valores que não existem nos estados (`Em Processo`, `Concluída`); tendem a 0.
2. `ConvertedProposalV2Controller::update` envia o mail de estado sem proteção e sem verificar se há email.
3. `ConvertedProposalV2Controller::store` não cria o histórico inicial `Iniciada`.
4. A timeline pública usa o id sequencial (enumerável); a cotação usa `proposal_code`.
5. Nomes de estado inconsistentes (`Enviado`/`Enviada`, `Cancelada`/`Cancelado`) e ENUMs antigos nas migrations: **(por confirmar o que a BD real tem)**.
6. Eliminar a convertida apaga os movimentos automáticos.
7. **(por confirmar)** `ProposalAcceptedMail::build`: se o angariador recebe o contrato.
