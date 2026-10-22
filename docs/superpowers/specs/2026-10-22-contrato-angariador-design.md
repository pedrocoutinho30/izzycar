# Contrato de angariador — desenho

Data: 2026-10-22. Estado: desenho aprovado, por implementar.

## Objetivo

O admin descarrega, na ficha de um angariador, um contrato em PDF com o mesmo layout do contrato de cotação convertida, preenchido com os dados da IzzyCar, os dados do angariador e as cláusulas de angariação e comissão.

## Decisões

- O PDF é gerado na hora e não é guardado (como o "Ver contrato" das cotações convertidas).
- A comissão é devida **após a entrega da viatura ao cliente** e paga **até 48 horas depois**, por transferência para o IBAN do angariador.
- O valor da comissão é o `commission_fixed_value` do perfil. Sem valor, o PDF deixa uma linha em branco. O mesmo vale para NIF ou IBAN em falta; nunca bloqueia a geração.
- Pré-aviso de cessação: 15 dias (por omissão, a confirmar).

## Abordagem

Vista nova `pdf/contract_angariador` e serviço `AngariadorContractService`, ao lado do `ContractService`. A CSS de `pdf/contract_service` passou para o partial `pdf/_contract-styles`, partilhado pelos dois contratos, para o layout ficar igual por construção. O cabeçalho e o rodapé ficam em cada vista, porque o texto muda. O teste `ConvertedProposalContractTest` tem de continuar a passar.

## Componentes

**Rota.** `GET /gestao/v2/angariadores/{user}/contrato`, nome `admin.v2.angariadores.contract`, no grupo `gestao` dos angariadores. Entra em `config/permissions.php` ao lado das permissões de ver angariadores (o `PermissionCatalogTest` falha caso contrário). Utilizador sem o papel `angariador` dá 404, como na ficha.

**Controller.** Método fino em `AngariadorAdminController` que valida o papel e devolve o PDF como descarga.

**Serviço.** `AngariadorContractService::generatePdf(User $angariador)` reúne os dados e usa o dompdf.

- Prestador: definições (`name`, `vat_number`, `phone`, `email`), morada fixa Oliveira de Azeméis e `assinatura_prestador`, como no contrato atual.
- Angariador: nome completo, `nif`, `location`, `phone`, `email`, `iban`.
- Comissão: `commission_fixed_value`.
- Data de emissão e logo.

**Botões.** "Descarregar contrato" no cabeçalho da ficha (`admin/v2/angariadores/show.blade.php`, via `extraActions`, visível sem scroll) e um ícone de PDF em cada linha da listagem (`index.blade.php`). Se faltarem NIF, IBAN, morada ou comissão no perfil, a ficha mostra um aviso (`AngariadorContractService::camposEmFalta`); o PDF gera na mesma, com linhas em branco.

## Cláusulas

1. Identificação das partes.
2. Objeto: angariação de clientes para o serviço de importação, através do link e código pessoais.
3. Natureza: prestação de serviços autónoma, sem vínculo laboral, sem exclusividade e sem poderes de representação. O angariador não assume compromissos nem recebe pagamentos em nome da IzzyCar.
4. Obrigações do angariador: usar sempre o seu link, dar informação verdadeira, não prometer preços ou prazos fora da cotação e não prejudicar a imagem da IzzyCar.
5. Obrigações da IzzyCar: informar do estado das leads e pagar a comissão.
6. Comissão:
   - valor do perfil, devido por cada cotação convertida atribuída ao angariador;
   - devida após a entrega da viatura ao cliente, paga até 48 horas depois, por transferência para o IBAN indicado;
   - não é devida se o cliente cancelar ou não houver entrega;
   - o valor fica fixado quando o angariador é associado à cotação (`angariador_commission`);
   - recibo ou fatura quando legalmente aplicável.
7. Atribuição de leads: só contam as leads com o código do angariador. Em caso de duplicado, prevalece a primeira associação.
8. Confidencialidade e proteção de dados (RGPD): os dados dos leads só podem ser usados para este fim.
9. Duração e cessação: tempo indeterminado, com denúncia por escrito e pré-aviso de 15 dias. As comissões já devidas mantêm-se.
10. Lei aplicável e resolução de litígios.
11. Assinaturas (angariador e IzzyCar, com a assinatura digital do prestador).

## Testes

- Admin descarrega o PDF de um angariador (resposta `application/pdf`, começa por `%PDF`).
- Utilizador sem o papel `angariador` dá 404.
- Sem comissão, NIF ou IBAN, o PDF gera na mesma.
- `ConvertedProposalContractTest` continua a passar depois de extrair o partial.
- `PermissionCatalogTest` cobre a rota nova.

## Fora de âmbito

Guardar o PDF, enviar por email, assinatura digital do angariador e versões do contrato por data.

## Risco jurídico

O texto deve ser revisto por um jurista antes de ser assinado. A relação pode enquadrar-se como contrato de agência (Decreto-Lei 178/86), que prevê indemnização de clientela na cessação, e o recibo tem implicações fiscais. O PDF não deve prometer mais do que as cláusulas acima.
