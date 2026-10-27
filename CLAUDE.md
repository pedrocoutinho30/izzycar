# IzzyCar

Plataforma Laravel 12 (PHP 8.2, MySQL) para o mercado português: site público (viaturas, importação, simuladores, notícias) e backoffice de gestão do negócio de importação, legalização e venda de viaturas. Toda a interface e a documentação estão em português de Portugal.

## Documentação de domínio

Ler antes de mexer nestas áreas:

- [docs/funil-comercial.md](docs/funil-comercial.md) — o fluxo ponta a ponta, da lead à venda; inclui a versão mobile (`/gestao/mobile`, `access_mobile`).
- [docs/leads-e-clientes.md](docs/leads-e-clientes.md) — leads, clientes, duplicados, pedidos de formulário e oportunidades.
- [docs/cotacoes.md](docs/cotacoes.md) — cotações, aceitação, contrato e cotações convertidas.
- [docs/viaturas.md](docs/viaturas.md) — viaturas V3, legalização, venda e site público.

Há ainda `BACKOFFICE-V2-README.md` (componentes e layout do BO V2) e `TASKS_MODULE_README.md` (tarefas).

## Convenções

**Backoffice**
- Rotas em `routes/web.php`, dentro do grupo `gestao` (`auth`, `restrictAngariador`, `authorizeResource`). Nomes `admin.v2.*`, `admin.v3.*`, `admin.*`.
- Controllers em `app/Http/Controllers/Admin`, finos. A lógica de negócio vai para `app/Services`; validação não trivial para `app/Http/Requests` (`authorize()` devolve `true`, o acesso é do grupo de rotas).
- Views Blade em `resources/views/admin/v2|v3`, com o layout `layouts.admin-v2` e os componentes `components/admin/*`. Partials locais começam por `_`.
- Valores fechados (estados, combustíveis…) em enums de `app/Enums` com o trait `HasOptions`.
- Cada rota nova de `/gestao` tem de ficar coberta por `config/permissions.php` (o `PermissionCatalogTest` falha caso contrário). `PERMISSIONS_ENFORCE` está desligado por omissão: as regras só registam no log, não bloqueiam.

**Versões**
- `V3Vehicle` é a fonte de verdade das viaturas. `Vehicle`/`VehicleV2Controller`/rotas `v2/vehicles` são legado e redirecionam para V3. `Expense` está a ser substituído por movimentos (`v2/movements`).
- Não reativar nem estender código V1.

**Base de dados — cuidado**
- O `.env` local aponta para o MySQL remoto (Hostinger), muito provavelmente produção. **Nunca** correr `migrate`, seeders, `tinker` com escrita ou qualquer comando que altere dados sem autorização explícita do Pedro. Para verificar, usar sqlite em memória (os testes) ou uma base descartável.
- Migrations: dar nomes explícitos e curtos a FKs e índices (MySQL limita a 64 caracteres, o sqlite não avisa). Cuidado com `ENUM` (alterar exige migration). Nunca editar migrations já aplicadas.
- Ao entregar uma migration, dizer que falta correr `php artisan migrate` em produção.

**Testes**
- `php artisan test` (sqlite em memória). Classes com BD usam `Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations`; migrations só de MySQL estão listadas no trait.
- O `Tests\Feature\ExampleTest` já falhava antes de qualquer alteração recente; ignorar, mas não o esconder.
- SQL só de MySQL (`FIELD()`, `ORDER BY ... IS NULL`) precisa de emulação nos testes.

**Geral**
- Estilo: seguir o código ao lado (comentários em português, nomes de domínio em português quando já o são: `cotação`, `viatura`).
- Commits em português, no formato `tipo(âmbito): resumo` (`feat`, `fix`). Só fazer commit ou push quando pedido.
- Não guardar segredos nem dados de clientes em ficheiros do repositório.

## Agents (`.claude/agents/`)

| Agent | Trabalho |
|---|---|
| `backoffice-dev` | Features do backoffice |
| `migration-author` | Migrations seguras |
| `test-writer` | Testes |
| `code-reviewer` | Revisão de diffs (só leitura) |
| `bug-investigator` | Diagnóstico de causa raiz (só leitura) |
| `public-site-dev` | Site público, SEO |
| `pdf-documents` | Contratos, modelos oficiais, relatórios em PDF |
| `integrations-dev` | Radar, scrapers, OpenAI, comandos agendados, mails |
