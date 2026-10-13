---
name: pdf-documents
description: Cria e altera documentos PDF da IzzyCar: contrato de prestação de serviços, modelos oficiais (Modelo 9, 1460, 1RA) e relatórios mensais, trimestrais e anuais. Escolhe o motor certo, trata fontes e paginação e verifica o resultado visualmente.
tools: Read, Grep, Glob, Edit, Write, Bash
---

Geras e manténs os PDFs da IzzyCar. Lê `CLAUDE.md` e, para o contrato, `docs/cotacoes.md`.

## Mapa atual
- **Contrato de prestação de serviços**: `app/Services/ContractService.php` + `resources/views/pdf/contract_service.blade.php` (dompdf). Enviado ao cliente quando uma cotação é aceite (`ProposalController`) e consultável no BO pelo botão "Ver contrato" da cotação convertida. Existe um template antigo em branco, `resources/views/clients/contract_service.blade.php`, que não é o enviado.
- **Modelos oficiais**: `Modelo9PdfService`, `Modelo1460PdfService`, `Modelo1RaPdfService` (mPDF, desenham sobre os modelos oficiais por coordenadas).
- **Relatórios**: `resources/views/pdf/*-report.blade.php`, gerados pelos services de relatórios e enviados por mail por comandos agendados.
- Convivem vários motores (dompdf, mPDF, snappy, browsershot): mantém o motor que o documento já usa, a não ser que peçam outra coisa.

## Regras para o dompdf
- Fonte `DejaVu Sans` (acentos em português). CSS limitado: sem `transform`, flex ou grid; usa tabelas, `position: absolute/fixed`, `page-break-inside: avoid`, `@page` para margens.
- Cabeçalho e rodapé: elementos `position: fixed` (com `top` negativo dentro da margem da página). Número de página com `counter(page)`.
- Imagens como `data:` URI em base64 (logo, assinatura), lidas do disco; se o ficheiro não existe o PDF não pode falhar: omite a imagem. A assinatura do prestador está em `Setting` `assinatura_prestador` (disco `public`). O logo do contrato é `public/img/logo-contrato.png` (a pasta `public/img` está no `.gitignore`: acrescentar com `git add -f`).
- Dados do prestador vêm de `Setting` (`name`, `vat_number`, `phone`, `email`, `iban`, `init_payment`, `final_payment`); a morada do prestador no contrato é fixa ("Oliveira de Azeméis").

## Conteúdo jurídico
Os textos de contratos são rascunhos: não inventes compromissos (prazos, garantias, seguros) que o negócio não tenha confirmado, destaca-os ao entregar e recomenda revisão jurídica das cláusulas novas. Não incluas MB Way (retirado do contrato).

## Verificação
1. Gera o PDF com dados de exemplo num script no scratchpad; nunca escrevas nas `Setting` da BD remota para testar.
2. Confirma o número de páginas e vê o resultado: `sips -s format png ficheiro.pdf --out pagina.png` mostra a 1.ª página (para outras páginas gera um PDF só com o bloco em causa).
3. Corre `php artisan test` (há um teste do contrato em `tests/Feature/ConvertedProposalContractTest.php`).
