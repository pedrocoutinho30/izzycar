# Viaturas

`V3Vehicle` (tabela `v3_vehicles`) é a **única fonte de verdade** do stock. O legado (`Vehicle`, `VehicleController`, `VehicleV2Controller`, rotas `v2/vehicles`) está desligado: as rotas V2 redirecionam para V3. Não estender código legado.

Estado verificado no código em 2026-10. O que não foi confirmado está marcado com **(por confirmar)**.

## Modelo (`app/Models/V3Vehicle.php`)

- **Identificação**: `reference` (`V3-AAAA-NNN`, gerada por `generateReference()`), brand, model, sub_model, version, year/month/day, vin, registration.
- **Técnico**: fuel, kilometers, power, cylinder_capacity, color, notes, ad_text.
- **Estado e site**: `status` (`em_stock`, `reservado`, `vendido`; string sem enum, validada em `saveGeneral`), `show_online`, `home_featured`, `home_featured_order`, `is_imported`.
- **Ligações**: client_id, supplier_id, seller_id, seller_contact_id, generated_from_inspection_id.
- **Compra e preço**: purchase_price (sem IVA), purchase_date, purchase_type (`Geral`, `Margem`, `Sem Iva`), purchase_vat_rate, purchase_vat_paid (valor absoluto), asking_price.
- **Relações**: `photos` (por `order_position`), `documents`, `legalization` (hasOne), `expenses` (por `v3_vehicle_id`), `legalizationExpenses` (movimentos da legalização que não estão ligados diretamente à viatura), `sales`, `client`, `supplier`, `seller`, `sellerContact`, `attributeValues`, `generatedFromInspection`.
- **Calculados**: `expenses_total` (despesas `movement_type=expense` sem `vehicle_purchase`), `total_cost` (compra + `expenses_total`), `cover_photo`, `year_label`.
- `fuelOptions()`: Gasolina, Diesel, Elétrico, Gasolina (HEV), Diesel (HEV), Híbrido Plug-In, Híbrido, GPL, Hidrogénio. É a lista usada nos selects de viaturas e legalizações.

## Backoffice (`Admin/V3VehicleController`, rotas `admin.v3.vehicles.*`)

Ecrã de edição com separadores (`resources/views/admin/v3/vehicles/edit.blade.php`): geral, compra, despesas, documentos, venda, fotos, equipamento, legalização. O separador ativo vem de `session('return_tab')`; as ações com `back()` definem-no.

- **Auto-save AJAX**: `save-general`, `save-purchase`, `save-equipment`.
- `store()` cria só marca, modelo, ano e combustível, com `status=em_stock` e referência gerada (não define `show_online`).
- `saveGeneral` propaga `client_id` para a legalização se esta ainda não tiver cliente; sem mês, o dia é anulado.
- `savePurchase` valida o tipo de compra e que o contacto pertence ao vendedor, e chama `Expense::syncFromV3Vehicle`.
- O separador **Legalização só aparece se `is_imported`**.
- Despesas: separador "Despesas" lista e cria movimentos com `v3_vehicle_id`; mostra também os da legalização da viatura (só leitura).
- Fotos: capa, ponto focal, recorte, restaurar, reordenar. Anúncio: `generate-ad` / `save-ad` (OpenAI). **(por confirmar o detalhe de fotos, equipamento e anúncio)**.

## Origem das viaturas

1. **Manual** no BO.
2. **Cotação convertida** (`ConvertedProposalV2Controller::createVehicle` → `ConvertedProposalService::createVehicleAndLegalization`): exige a permissão `vehicles.create`, é idempotente (transação com `lockForUpdate`), cria a viatura com `is_imported=true`, `status=reservado`, `show_online=false`, copia dados da cotação/oportunidade e cria a legalização (`firstOrCreate`).
3. **Inspeção** (`VehicleInspectionService::createV3Vehicle`): copia dados e media, `status=em_stock`, `show_online=false`, marca a inspeção como `converted`.

## Legalização (`app/Models/Legalization.php`)

- Uma por viatura (`v3_vehicle_id`); `createLegalization` copia cliente, marca, modelo, combustível (por omissão Gasolina) e matrícula, e é idempotente.
- **7 passos** (`PASSOS`): homologação IMT (CHNAC), Inspeção B/Modelo 112, DAV nas Finanças, pagamento do ISV, chapas e seguro, entregar Modelo 9, registo na Conservatória.
- `toggleLegalizationStep`: só marca o passo N se o N-1 está concluído (422 caso contrário); desmarcar não verifica os seguintes. Devolve o progresso (concluídos/7).
- **Documentos**: 10 tipos em `DOCUMENTOS`; o `regime_especial_isv` acrescenta CRESI, DVIDQ, CSTRI e MD1460. Upload pdf/jpg/png/webp, 10 MB, um documento por tipo (substitui). O upload do separador V3 só aceita `DOCUMENTOS`, **não** os do regime especial; esses editam-se em `Admin/LegalizationController` e `admin/v2/legalizations`.
- Token público de 32 caracteres e `trackingUrl()` (`frontend.legalization.status`) para o cliente acompanhar. Comando `legalizations:send-tracking-emails` às 15:00.
- A página da legalização mostra os movimentos com `legalization_id`.

## Venda

- `Sale` (V3: `v3_vehicle_id`; `vehicle_id` é legado): uma venda por viatura. Registo no separador Venda (`storeSale`): calcula com `SaleCalculator::compute`, cria a `Sale` e passa a viatura a `vendido`. `destroySale` apaga a venda e repõe `em_stock` (mesmo que estivesse reservada).
- `SaleCalculator` (`app/Services/SaleCalculator.php`) é a fonte única das margens: `geral_23|13|6` (base = preço/(1+taxa)), `margem` (IVA = max(0,(venda−compra)×23/123)), `isento`. Não duplicar estes cálculos.
- `SaleV2Controller` (`v2/sales`) só cria vendas de `Vehicle` V1; as V3 passam pelo separador Venda.
- `SaleObserver` cria a receita `vehicle_sale` (movimento) ao guardar.

## Movimentos financeiros

`Expense` (tabela `expenses`) é o movimento (receita/despesa), com `v3_vehicle_id`, `legalization_id`, `client_id`, `source_type/source_id`. 16 categorias (`Expense::categories()`), estados `paid|pending|partially_paid|cancelled`. `ExpenseObserver` sincroniza o livro `FinancialMovement` (polimórfico).
- `syncFromV3Vehicle`: despesa `vehicle_purchase` = preço + IVA pago; apaga-a se o preço de compra ficar vazio.
- `syncFromConvertedProposal` lança tranches e custos de importação **sem** `v3_vehicle_id` de propósito (o carro é do cliente).
- Movimentos criados em `v2/movements`; as rotas `v2/expenses` redirecionam para aí.

## Site público (`Frontend/VehiclesController`, `routes/frontend.php`)

- `/viaturas` (`vehicles.list`), `/viaturas-filtradas` (JSON dos filtros), `/viaturas/{brand}/{model}/{id}` (`vehicles.details`, por `reference`; 404 se os slugs de marca e modelo não coincidem).
- **Duas queries**: `vehicles()` e `filteredVehicles()`; a listagem na página é alimentada pelo endpoint JSON. Mantê-las com a mesma ordenação (estado: em_stock, reservado, vendido; depois data). Em 2026-10 `vehicles()` ordena por `created_at` desc e `filteredVehicles()` ainda por `purchase_date`: confirmar com o Pedro qual é a intenção.
- Mostra todas as viaturas com `show_online=true`, incluindo vendidas (o filtro `status != vendido` está comentado).
- **Cache** de 10 min: chaves `vehicles_*`, `filtered_vehicles_*`, home (`v3vehicles`, `v3vehicles_count`, `v3last_vehicles`). `clearVehicleCache()` do `V3VehicleController` só limpa as da home: a listagem pode ficar desatualizada até 10 min (ou `cache:clear`).
- Views: `frontend/vehicles-list` (+ `-desktop`/`-mobile`) e `vehicles-detail` (+ `-desktop`/`-mobile`). Há ficheiros `.bak`, `copy` e `___` que são lixo; não editar.
- **JSON-LD**: só se emite `Car` com `offers` se `status=em_stock` e `asking_price>0` (exigência do Google); reservada, vendida e sem preço ficam sem bloco de produto. `BreadcrumbList` em JSON-LD; sem microdata duplicado. O `seller` refere o `AutoDealer` do layout (`@id` `https://izzycar.pt/#autodealer`).
- `VehicleCatalog` (marca → modelos) usa-se nas oportunidades e formulários; no `V3VehicleController` marca e modelo são texto livre.

## Armadilhas

- `generateReference()` usa `count`+1 por ano: apagar viaturas pode gerar referências repetidas **(por confirmar índice único)**.
- `expenses_total` conta movimentos sincronizados (`source_type` não nulo), mas o cálculo da venda exclui-os (`whereNull('source_type')`): os dois totais podem diferir. Movimentos só da legalização não entram em `expenses_total`.
- Viaturas importadas ficam `reservado` e `show_online=false`: ao pôr `show_online`, publicam-se como reservadas.
- `destroy` de viatura não avisa de vendas, despesas ou legalização **(por confirmar cascades nas migrations)**.
- Compra: `purchase_price` é sem IVA; o bruto é preço + `purchase_vat_paid`.
- O `.env` local usa a BD remota, e os ficheiros enviados (fotos, assinatura) vivem no disco de cada servidor.
