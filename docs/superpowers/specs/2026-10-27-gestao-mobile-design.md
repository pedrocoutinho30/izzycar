# Gestão mobile (iPhone) — desenho

Data: 2026-10-27. Estado: aprovado pelo Pedro.

## Objetivo

Área mobile da gestão em `/gestao/mobile`, integrada no projeto: mesmos dados, serviços e permissões do desktop, apresentação própria para iPhone. Uma cotação criada no mobile a partir de uma lead aparece no desktop associada a essa lead.

## 1. Acesso (`access_mobile`)

- Migration: coluna `users.access_mobile` boolean, default `false`. Fora do `User::$fillable`; só é gravada explicitamente.
- Checkbox "Acesso à versão mobile" no formulário de utilizadores (`admin/v2/users/form`).
- Só altera o campo quem tem `users.update` (via `PermissionService::allows`, verificação explícita porque `PERMISSIONS_ENFORCE` está desligado) e que não seja o próprio utilizador. Admin pode atribuir-se a si próprio. Sem autorização a checkbox não aparece e o campo enviado à mão é ignorado. O `ProfileController@update` nunca grava o campo.
- Middleware `mobile.access`: sem `access_mobile`, redireciona para `admin.v2.dashboard` (fora do grupo mobile, logo sem ciclo) com a mensagem "Não tem acesso à versão mobile.".
- Grupo de rotas: `gestao/mobile` com `auth`, `restrictAngariador`, `mobile.access`, `authorizeResource`. Rotas `admin.mobile.*`.
- Dados: continuam governados por `leads.*`, `clients.*`, `form-proposals.*`, `proposals.*` e pelo âmbito "próprios" (`applyScope`), tal como no desktop.

## 2. Estrutura

- Controllers finos em `app/Http/Controllers/Admin/Mobile`; views em `resources/views/admin/mobile`; layout `layouts.admin-mobile` (identidade IzzyCar, `viewport-fit=cover`, safe-areas, barra inferior Leads / Clientes / Pesquisa / Mais, alvos ≥ 44px, cartões em vez de tabelas).
- Lógica hoje inline nos controllers V2 (validação, `LeadActivity`, conversão, criação de cotação) é extraída para services (`LeadService`, `ClientService`, `ProposalService`) usados por desktop e mobile. Reutilizam-se `ProposalAcceptanceService`, `ClientMatcher` e FormRequests existentes.
- Rotas mobile entram em `config/permissions.php` (padrões dos objetos leads, clients, form-proposals, proposals) para o `PermissionCatalogTest` passar.

## 3. Âmbito (fases)

1. Acesso, layout, PWA, início mobile.
2. Leads: lista com pesquisa/filtros, ficha com ligar/WhatsApp/email, estado, converter, follow-up, dono, atividades/timeline, criar, eliminar, formulários e simuladores.
3. Pedidos de formulário e oportunidades (estado, checklist, contactos, notas; criar pedido manual).
4. Cotações: criar a partir de lead/pedido/oportunidade com pré-preenchimento, editar, estado, aceitar pelo BO, ver convertidas da lead.
5. Clientes: lista, ficha (formulários, cotações, convertidas, carros candidatos, timeline), criar, editar, follow-up, atividades.
6. Cotações convertidas: consulta e mudança de estado do pipeline.

Fora de âmbito (a assinalar no resumo): kanban, exportações XLSX, importar cotação por link de anúncio, contrato PDF, uploads de documentos/fotos, financeiro.

## 4. PWA

Manifest próprio (nome "IzzyCar Gestão", `display: standalone`, `scope`/`start_url` `/gestao/mobile`), ícones e meta tags `apple-mobile-web-app-*`. Sem service worker novo nem dependências.

## 5. Testes

PHPUnit em sqlite: acesso com/sem permissão, redirecionamento sem ciclo, auto-atribuição impedida, âmbito "próprios", cotação criada no mobile associada à lead e visível no desktop; suite completa para garantir que o desktop não regride. Verificação num iPhone real fica por fazer pelo Pedro.

## 6. Base de dados

Só se entrega a migration; não se corre `migrate` contra o MySQL remoto. Falta correr `php artisan migrate` em produção.
