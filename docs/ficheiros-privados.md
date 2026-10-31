# Ficheiros privados

Os documentos sensíveis de clientes e do negócio ficam no disco privado (`local`, `storage/app`), fora de `/storage`. Só são servidos a utilizadores autenticados do backoffice. Quem adivinhar o URL não os consegue abrir.

## Pastas privadas

| Tipo (URL) | Pasta no disco | O que é | Quem guarda |
|---|---|---|---|
| `retomas` | `retoma/` | Fotos de retoma enviadas no formulário de importação | `Frontend\ImportController` |
| `consignacao` | `consignment-evaluations/` | Fotos enviadas no pedido de avaliação para consignação | `Frontend\ConsignmentController` |
| `comprovativos` | `comissoes/comprovativos/` | Comprovativos de transferência das comissões | `Admin\AngariadorAdminController::uploadReceipt` |
| `despesas` | `expenses/attachments/` | Anexos de despesas | `Admin\ExpenseV2Controller` |
| `movimentos` | `movements/attachments/` | Anexos de movimentos | `Admin\MovementV2Controller` |

A lista está em `App\Services\PrivateFiles::TIPOS`. Para acrescentar uma pasta, juntar uma linha aí (a rota, o comando e os testes acompanham).

Ficam **públicas** de propósito: fotos de viaturas à venda, notícias, logos, marcas, parceiros, newsletter, settings, recommendation-posts, `files/` (CMS) e `proposals/` (imagem da cotação, usada na página pública da cotação e no email). As fotos de inspeção (`inspections/`) também ficam públicas, porque ao converter uma inspeção em viatura são copiadas para as fotos da viatura no site.

## Como funciona

- Guardar: `PrivateFiles::guardar($ficheiro, 'pasta/...')` grava no disco `local` e devolve o caminho relativo (o que vai para a BD, igual ao que já se guardava).
- Mostrar: `PrivateFiles::url($caminho)` devolve o URL da rota `admin.v2.files.show` (`/gestao/ficheiros/{tipo}/{caminho}`). Nas views usa-se `\App\Services\PrivateFiles::url($modelo->campo)`.
- A rota exige login (`auth`) e está na lista `shared` de `config/permissions.php`. O angariador puro não a vê (`restrictAngariador`); para o seu próprio comprovativo existe `admin.angariador.comissoes.comprovativo`, que só serve cotações onde `owner_id` é ele.
- Segurança da rota: os tipos são limitados por `whereIn`; caminhos com `..`, `.`, `\`, segmentos vazios ou barra inicial dão 404; o ficheiro resolvido (`realpath`) tem de ficar dentro da pasta do tipo. Resposta com `X-Content-Type-Options: nosniff` e `Cache-Control: private`. Imagens e PDF abrem no browser (`inline`); qualquer outro tipo é descarregado.
- Apagar: `PrivateFiles::apagar($caminho)` remove do disco privado e do público.
- Anexos de email: `PrivateFiles::caminhoAbsoluto($caminho)` (usado no pedido de consignação).

## Ficheiros antigos (sem fallback)

Já não há fallback de leitura para o disco `public`: um ficheiro que ainda esteja lá dá 404 na rota autenticada e `PrivateFiles::caminhoAbsoluto` devolve `null` (o anexo do email de consignação é omitido). **Antes de publicar este código, correr o passo 2 e 3 do Deploy**, senão os ficheiros antigos deixam de abrir. O comando `files:move-to-private` e `PrivateFiles::apagar` continuam a tocar no disco público.

## Quem pode abrir

A rota continua em `shared` (qualquer perfil de backoffice autenticado), mas o `PrivateFileController` exige a habilidade do objeto dono (`PrivateFiles::HABILIDADES`): retomas e consignação `form-proposals.view.all`, comprovativos `commissions.view.all`, despesas e movimentos `movements.view`. Como o resto do catálogo, com `PERMISSIONS_ENFORCE` desligado só regista no canal `permissions` (`would_block`); ligado, responde 403. Não se verifica o dono de cada ficheiro (retomas e consignação não têm um registo-dono simples), só o acesso ao objeto.

## Deploy

1. Publicar o código.
2. `php artisan files:move-to-private` (dry-run: só lista o que moveria, não altera nada).
3. Se a lista estiver certa: `php artisan files:move-to-private --apply`.

O comando só mexe em ficheiros (não usa a base de dados). Copia em stream, confirma que a cópia existe com o mesmo tamanho e só então apaga o original do disco público. É idempotente: repetir não faz mal, e se o ficheiro já existir no privado com o mesmo tamanho apenas limpa o público. Se alguma cópia falhar, o original fica e o comando termina com erro.

Depois do passo 3, `/storage/retoma/...`, `/storage/comissoes/comprovativos/...` etc. deixam de responder (404).

## Notas

- As fotos de retoma e de consignação só são vistas no backoffice autenticado (a consignação em `consignment-evaluations/show`; as retomas não têm ecrã de fotos, só a contagem).
- Pré-existente, não alterado: `ConsignmentController::submitEvaluation` lê `power`, `displacement`, `price_expectation`, `version`, `color` e `location` sem `??`; um pedido sem essas chaves dá erro 500 (o formulário real envia-as sempre).
- Testes: `tests/Feature/FicheirosPrivadosTest.php`.
