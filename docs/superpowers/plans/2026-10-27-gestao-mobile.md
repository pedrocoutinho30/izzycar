# Gestão mobile (iPhone) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Área mobile da gestão em `/gestao/mobile` (leads, clientes, formulários, cotações, convertidas), com os mesmos dados, services e permissões do desktop.

**Architecture:** Controllers finos em `Admin/Mobile` + views próprias com layout `layouts.admin-mobile`. A lógica hoje inline nos controllers V2 é extraída para services partilhados (desktop e mobile passam a usá-los). Acesso por coluna `users.access_mobile` + middleware `mobile.access`.

**Tech Stack:** Laravel 12, PHP 8.2, Blade, Bootstrap 5 (CDN, como o BO V2), Spatie Permission, PHPUnit em sqlite.

Spec: `docs/superpowers/specs/2026-10-27-gestao-mobile-design.md`.

## Global Constraints

- Interface e comentários em português de Portugal; nomes de domínio em português quando já o são.
- Nunca correr `migrate`, seeders ou `tinker` com escrita contra o `.env` (MySQL remoto). Verificar só com `php artisan test` (sqlite em memória).
- Migrations: nomes explícitos e curtos; nunca editar migrations aplicadas. No fim dizer que falta `php artisan migrate` em produção.
- Cada rota nova de `/gestao` tem de ficar coberta por `config/permissions.php` (`PermissionCatalogTest`).
- Não alterar o comportamento do desktop: cada extração para service tem de manter verdes os testes existentes.
- Não adicionar dependências nem serviços pagos. Não reativar código V1.
- `Tests\ExampleTest` já falhava antes; ignorar mas não esconder.
- Commits em português `tipo(âmbito): resumo`, terminados com `Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>`.
- Testes com BD usam `Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations`; páginas do backoffice precisam de `Cache::forever('frontend_menus', collect()); Cache::forever('site_logo', '');` e `$this->backofficeUser('admin', [...])`.

## File Structure

Criar:
- `database/migrations/2026_10_27_200000_add_access_mobile_to_users_table.php`
- `app/Http/Middleware/EnsureMobileAccess.php`
- `app/Http/Controllers/Admin/Mobile/{DashboardController,LeadController,ClientController,FormProposalController,ProposalController,ConvertedProposalController,SearchController}.php`
- `app/Services/{LeadService,ClientService,ProposalService}.php`
- `resources/views/layouts/admin-mobile.blade.php`, `resources/views/admin/mobile/**`
- `public/mobile-manifest.json`, ícones `public/img/mobile/*`
- `tests/Feature/Mobile/{MobileAccessTest,MobileUserPermissionTest,MobileLeadsTest,MobileClientsTest,MobileProposalsTest}.php`

Modificar: `app/Models/User.php`, `app/Http/Kernel.php`, `routes/web.php`, `config/permissions.php`, `app/Http/Controllers/Admin/UserV2Controller.php`, `resources/views/admin/v2/users/form.blade.php`, `LeadV2Controller`, `ClientV2Controller`, `ProposalV2Controller` (só para delegar nos services).

---

### Task 1: Coluna `access_mobile` e modelo

**Files:**
- Create: `database/migrations/2026_10_27_200000_add_access_mobile_to_users_table.php`
- Modify: `app/Models/User.php` (casts em `casts()`, **não** no `$fillable`)
- Test: `tests/Feature/Mobile/MobileAccessTest.php`

**Interfaces:**
- Produces: `User->access_mobile` (bool, default false).

- [ ] **Step 1: Teste falhado**

```php
<?php

namespace Tests\Feature\Mobile;

use App\Models\User;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MobileAccessTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_novos_utilizadores_ficam_sem_acesso_mobile_e_o_campo_nao_e_atribuivel_em_massa(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->refresh()->access_mobile);

        $user->fill(['access_mobile' => true]);
        $this->assertFalse((bool) $user->access_mobile);
    }
}
```

- [ ] **Step 2:** `php artisan test --filter=MobileAccessTest` → FAIL (coluna inexistente).

- [ ] **Step 3: Migration e cast**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('access_mobile')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('access_mobile');
        });
    }
};
```

Em `User::casts()` acrescentar `'access_mobile' => 'boolean',`.

- [ ] **Step 4:** `php artisan test --filter=MobileAccessTest` → PASS.
- [ ] **Step 5:** `git add database/migrations app/Models/User.php tests/Feature/Mobile && git commit -m "feat(mobile): coluna access_mobile nos utilizadores"`

---

### Task 2: Middleware `mobile.access`, rota base e redirecionamento

**Files:**
- Create: `app/Http/Middleware/EnsureMobileAccess.php`, `app/Http/Controllers/Admin/Mobile/DashboardController.php`, `resources/views/layouts/admin-mobile.blade.php`, `resources/views/admin/mobile/dashboard.blade.php`
- Modify: `app/Http/Kernel.php` (alias `'mobile.access'`), `routes/web.php`, `config/permissions.php`
- Test: `tests/Feature/Mobile/MobileAccessTest.php`

**Interfaces:**
- Produces: rota `admin.mobile.dashboard` (`GET /gestao/mobile`); grupo `Route::prefix('mobile')->name('admin.mobile.')->middleware('mobile.access')` dentro do grupo `gestao`; layout com `@yield('title')`, `@yield('content')`, `@yield('header_actions')`.

- [ ] **Step 1: Testes falhados** (acrescentar à classe):

```php
use Illuminate\Support\Facades\Cache;

protected function setUp(): void
{
    parent::setUp();
    Cache::forever('frontend_menus', collect());
    Cache::forever('site_logo', '');
}

public function test_utilizador_com_permissao_acede_ao_mobile(): void
{
    $user = $this->backofficeUser('admin', ['access_mobile' => true]);
    $this->actingAs($user)->get(route('admin.mobile.dashboard'))->assertOk();
}

public function test_utilizador_sem_permissao_vai_para_o_desktop_com_mensagem_e_sem_ciclo(): void
{
    $user = $this->backofficeUser('admin');
    $this->actingAs($user)->get('/gestao/mobile')
        ->assertRedirect(route('admin.v2.dashboard'))
        ->assertSessionHas('error', 'Não tem acesso à versão mobile.');

    $this->actingAs($user)->get(route('admin.v2.dashboard'))->assertOk();
}

public function test_visitante_vai_para_login(): void
{
    $this->get('/gestao/mobile')->assertRedirect('/login');
}
```

Nota: `access_mobile` fora do `$fillable` — usar `User::factory()->create()` e depois `forceFill(['access_mobile' => true])->save()` num helper `mobileUser()` da classe em vez de passar em `$attributes` (o factory usa `forceFill`? confirmar no passo 2; se não, usar o helper).

- [ ] **Step 2:** correr → FAIL (rota inexistente).

- [ ] **Step 3: Middleware**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * A área /gestao/mobile só é acessível a quem tem access_mobile. Sem acesso
 * volta ao desktop (rota fora do grupo mobile, logo sem ciclo de redirects).
 */
class EnsureMobileAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->access_mobile) {
            return redirect()->route('admin.v2.dashboard')
                ->with('error', 'Não tem acesso à versão mobile.');
        }

        return $next($request);
    }
}
```

Kernel: `'mobile.access' => \App\Http\Middleware\EnsureMobileAccess::class,`.

Rotas (dentro do grupo `gestao`, depois do bloco de converted-proposals):

```php
Route::prefix('mobile')->name('admin.mobile.')->middleware('mobile.access')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\Mobile\DashboardController::class, 'index'])->name('dashboard');
});
```

Um angariador puro: `restrictAngariador` já bloqueia (403) rotas fora de `admin.angariador.*`; manter assim.

`config/permissions.php`: junto das `routes` de `leads` acrescentar `'admin.mobile.dashboard'`, `'admin.mobile.leads.*'`; `clients`: `'admin.mobile.clients.*'`; `form-proposals`: `'admin.mobile.form-proposals.*'`; `proposals`: `'admin.mobile.proposals.*'`; e `converted-proposals` (ver o objeto existente no ficheiro): `'admin.mobile.converted-proposals.*'`. Pesquisa: `'admin.mobile.search'` em `leads`. Cada fase só regista as rotas que cria; o dashboard entra já.

`DashboardController::index` devolve `view('admin.mobile.dashboard', ['leadsNovas' => Client::where('is_lead', true)->where('lead_status', 'nova')->count(), 'followupsHoje' => Client::where('is_lead', true)->whereDate('next_followup_at', today())->count()])` — aplicar `PermissionService::applyScope` com `->applyScope($query, $user, 'leads')` (assinatura em `app/Permissions/PermissionService.php:93`).

Layout `layouts.admin-mobile`: `<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">`, `--admin-primary: #6e0707` e restantes variáveis copiadas de `layouts/admin-v2.blade.php`, Bootstrap 5.3.0 e Bootstrap Icons por CDN (iguais ao V2), `padding: env(safe-area-inset-*)`, topbar com título, mensagens flash `success`/`error`/`errors`, e barra inferior fixa com 4 itens (Leads, Clientes, Pesquisa, Mais) com `min-height:44px`. Item ativo por `request()->routeIs('admin.mobile.leads.*')` etc. Barra "Mais": link para o desktop (`admin.v2.dashboard`) e logout.

- [ ] **Step 4:** testes PASS e `php artisan test --filter=PermissionCatalogTest` PASS.
- [ ] **Step 5:** commit `feat(mobile): middleware de acesso, rota base e layout mobile`.

---

### Task 3: Checkbox no perfil de utilizador com proteção de permissão

**Files:**
- Modify: `app/Http/Controllers/Admin/UserV2Controller.php` (`store`, `update`), `resources/views/admin/v2/users/form.blade.php`
- Test: `tests/Feature/Mobile/MobileUserPermissionTest.php`

**Interfaces:**
- Consumes: `PermissionService::allows(User $user, string $resource, string $action, ?Model $record = null, ?string $requiredScope = null): bool`.
- Produces: `UserV2Controller::canManageMobileAccess(User $target): bool` (privado).

Regra: pode alterar quem `allows($actor, 'users', 'update')` e `$actor->id !== $target->id` ou `PermissionService::isAdmin($actor)`. Caso contrário o campo é ignorado e a checkbox não é mostrada.

- [ ] **Step 1: Testes falhados**

```php
class MobileUserPermissionTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();
    }

    private function payload(User $u, array $extra = []): array
    {
        return $extra + ['name' => $u->name, 'last_name' => 'Teste', 'email' => $u->email, 'roles' => ['admin']];
    }

    public function test_admin_atribui_acesso_mobile_a_outro_utilizador(): void
    {
        $admin = $this->backofficeUser('admin');
        $alvo = $this->backofficeUser('admin');

        $this->actingAs($admin)->put(route('admin.v2.users.update', $alvo->id), $this->payload($alvo, ['access_mobile' => '1']))->assertRedirect();
        $this->assertTrue($alvo->refresh()->access_mobile);

        $this->actingAs($admin)->put(route('admin.v2.users.update', $alvo->id), $this->payload($alvo))->assertRedirect();
        $this->assertFalse($alvo->refresh()->access_mobile);
    }

    public function test_utilizador_sem_autorizacao_nao_se_atribui_acesso_mesmo_enviando_o_campo(): void
    {
        // Perfil sem users.update. Confirmar o nome de um perfil existente em
        // PermissionSynchronizer / seeders; criar um perfil de teste sem grants.
        $role = \Spatie\Permission\Models\Role::findOrCreate('semusers', 'web');
        $user = User::factory()->create(['password' => 'secret', 'last_name' => 'Teste']);
        $user->assignRole($role);

        $this->actingAs($user)->put(route('admin.v2.users.update', $user->id), $this->payload($user, ['roles' => ['semusers'], 'access_mobile' => '1']));
        $this->assertFalse($user->refresh()->access_mobile);
    }

    public function test_checkbox_aparece_so_a_quem_pode_geri-la(): void
    {
        $admin = $this->backofficeUser('admin');
        $alvo = $this->backofficeUser('admin');
        $this->actingAs($admin)->get(route('admin.v2.users.edit', $alvo->id))
            ->assertOk()->assertSee('name="access_mobile"', false)->assertSee('Acesso à versão mobile');
    }
}
```

(O nome do método de teste não pode ter hífen: usar `test_checkbox_aparece_so_a_quem_pode_geri_la`.)

- [ ] **Step 2:** correr → FAIL.

- [ ] **Step 3: Implementação**

Injetar `PermissionService` por `app(PermissionService::class)` em `UserV2Controller` e acrescentar:

```php
/** Só quem gere utilizadores altera o acesso mobile, e nunca o próprio (exceto admin). */
private function canManageMobileAccess(User $target): bool
{
    $actor = auth()->user();
    $permissions = app(\App\Permissions\PermissionService::class);

    if (!$actor || !$permissions->allows($actor, 'users', 'update')) {
        return false;
    }

    return $actor->id !== $target->id || $permissions->isAdmin($actor);
}
```

Em `update`, depois de `$user->save()`: 

```php
if ($this->canManageMobileAccess($user)) {
    $user->forceFill(['access_mobile' => $request->boolean('access_mobile')])->save();
}
```

Em `store`, depois de `User::create(...)`: mesmo bloco (o alvo é novo, logo nunca é o próprio). Em `AuditLog::record('user_mobile_access_updated', ...)` quando o valor muda (registar antigo/novo como em `user_roles_updated`).

View `users/form.blade.php`, a seguir ao bloco de perfis (linha ~134):

```blade
@if($canManageMobile)
<div class="form-check mt-3">
    <input type="hidden" name="access_mobile" value="0">
    <input class="form-check-input" type="checkbox" name="access_mobile" value="1" id="access_mobile"
           @checked(old('access_mobile', isset($user) ? $user->access_mobile : false))>
    <label class="form-check-label" for="access_mobile">Acesso à versão mobile</label>
</div>
@endif
```

`edit`/`create` passam `$canManageMobile` (`create` → `true` se `allows($actor,'users','update')`).

- [ ] **Step 4:** testes PASS; correr também `--filter=PermissionManagementTest` e `--filter=ProfileAccessTest`.
- [ ] **Step 5:** commit `feat(mobile): checkbox Acesso à versão mobile no perfil de utilizador`.

---

### Task 4: PWA (manifest e ícones)

**Files:**
- Create: `public/mobile-manifest.json`, `public/img/mobile/icon-180.png`, `icon-192.png`, `icon-512.png` (gerados a partir de `public/img/logo-arredondado.png`, que o layout V2 já usa como apple-touch-icon; se faltarem os tamanhos, redimensionar com `sips -z 180 180` no macOS)
- Modify: `resources/views/layouts/admin-mobile.blade.php`
- Test: `tests/Feature/Mobile/MobileAccessTest.php`

- [ ] **Step 1: Teste**: `->assertSee('rel="manifest" href="' . asset('mobile-manifest.json') . '"', false)->assertSee('apple-mobile-web-app-title" content="IzzyCar Gestão"', false)` na página `admin.mobile.dashboard`.
- [ ] **Step 2:** FAIL. **Step 3:** manifest

```json
{
  "name": "IzzyCar Gestão",
  "short_name": "IzzyCar",
  "start_url": "/gestao/mobile",
  "scope": "/gestao/mobile",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#6e0707",
  "icons": [
    {"src": "/img/mobile/icon-192.png", "sizes": "192x192", "type": "image/png"},
    {"src": "/img/mobile/icon-512.png", "sizes": "512x512", "type": "image/png"}
  ]
}
```

e no `<head>` do layout: `theme-color`, `apple-mobile-web-app-capable`, `apple-mobile-web-app-status-bar-style=black-translucent`, `apple-mobile-web-app-title="IzzyCar Gestão"`, `<link rel="apple-touch-icon" href="{{ asset('img/mobile/icon-180.png') }}">`, `<link rel="manifest" href="{{ asset('mobile-manifest.json') }}">`. Sem service worker.
- [ ] **Step 4:** PASS. **Step 5:** commit `feat(mobile): manifest e ícones para o ecrã principal do iPhone`.

---

### Task 5: `LeadService` (extração) e leads mobile: lista, ficha, estado

**Files:**
- Create: `app/Services/LeadService.php`, `app/Http/Controllers/Admin/Mobile/LeadController.php`, `resources/views/admin/mobile/leads/{index,show,_card}.blade.php`
- Modify: `LeadV2Controller` (delegar), `routes/web.php`, `config/permissions.php` (já coberto)
- Test: `tests/Feature/Mobile/MobileLeadsTest.php`

**Interfaces:**
- Produces (`App\Services\LeadService`):
  - `rules(): array` — as regras de `LeadV2Controller::store` (linhas 28–37).
  - `create(array $data, ?User $by): Client` — corpo de `store` a partir de `Client::create([...])` incluindo `LeadActivity::log('Lead criado manualmente', ...)`.
  - `changeStatus(Client $lead, string $status): void` — corpo de `updateStatus` (labels/cores incluídos; usar `App\Enums\LeadStatus::labelOf`).
  - `convert(Client $lead, ?User $by): void` — `convertToClient()` + `LeadActivity::log('Lead convertido em cliente', ...)`.
  - `scheduleFollowup(Client $lead, string $at, ?string $note): void`; `logActivity(Client $lead, string $type, string $title, ?string $body): void`; `assignOwner(Client $lead, ?int $ownerId): void`.
- Rotas mobile (nome → URI): `leads.index` GET `/leads`, `leads.create` GET `/leads/nova`, `leads.store` POST `/leads`, `leads.show` GET `/leads/{id}`, `leads.status` POST `/leads/{id}/estado`, `leads.convert` POST `/leads/{id}/converter`, `leads.followup` POST `/leads/{id}/followup`, `leads.activity` POST `/leads/{id}/atividade`, `leads.owner` POST `/leads/{id}/dono`, `leads.destroy` DELETE `/leads/{id}`. `/leads/nova` declarada antes de `/leads/{id}`.

Procedimento por método de `LeadService` (TDD, um de cada vez): (a) o teste de caracterização já existente do desktop (`LeadKanbanTest::test_admin_can_qualify_a_lead_and_it_stays_active` para `changeStatus`) tem de continuar verde; para os restantes escrever primeiro um teste Feature desktop (`tests/Feature/LeadServiceTest.php`) que exerce a rota V2 e verifica o `LeadActivity`; (b) mover o corpo do método do controller para o service **sem mudar o texto das mensagens/atividades**; (c) o controller V2 passa a chamar o service; (d) testes verdes; (e) commit por método ou por grupo.

- [ ] **Step 1: Testes mobile falhados** (`MobileLeadsTest`, com `mobileUser()` = `backofficeUser('admin')` + `forceFill(['access_mobile'=>true])->save()`):

```php
public function test_lista_mostra_cartoes_com_pesquisa_e_filtro_de_estado(): void
{
    Client::create(['name' => 'Ana Nova', 'phone' => '912345678', 'is_lead' => true, 'lead_status' => 'nova']);
    Client::create(['name' => 'Rui Frio', 'is_lead' => true, 'lead_status' => 'fria']);

    $this->actingAs($this->mobileUser())->get(route('admin.mobile.leads.index', ['search' => 'Ana']))
        ->assertOk()->assertSee('Ana Nova')->assertDontSee('Rui Frio')
        ->assertSee('href="tel:912345678"', false);
}

public function test_ficha_mostra_formularios_cotacoes_e_convertidas_da_lead(): void { /* cria FormProposal, Proposal, ConvertedProposal do client e assertSee dos títulos */ }

public function test_mudar_estado_regista_atividade_igual_ao_desktop(): void
{
    $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova']);
    $this->actingAs($this->mobileUser())->post(route('admin.mobile.leads.status', $lead->id), ['status' => 'em_contacto'])->assertRedirect();
    $this->assertSame('em_contacto', $lead->refresh()->lead_status);
    $this->assertDatabaseHas('lead_activities', ['client_id' => $lead->id, 'title' => 'Estado alterado: Nova → Em Contacto']);
}

public function test_cliente_da_um_404_na_ficha_de_lead(): void { /* is_lead=false → 404 */ }

public function test_angariador_com_permissao_so_ve_os_seus_leads(): void { /* ver Task 9 */ }
```

(O corpo do `test_ficha…` escreve-se com os campos obrigatórios de `FormProposal`/`Proposal`/`ConvertedProposal` lidos nos respetivos modelos/factories.)

- [ ] **Step 2:** FAIL. **Step 3:** implementar service, controller e views.
  - `LeadController::index`: `Client::where('is_lead', true)->orderByRaw("lead_status = 'perdida' ASC")->orderBy('created_at','desc')->filtrarLeads($request->only(['search','lead_source','lead_status','owner_id']))`, `applyScope($q, $user, 'leads')`, `->with('owner')->paginate(15)->withQueryString()`. **Atenção ao sqlite:** `orderByRaw("lead_status = 'perdida' ASC")` funciona em sqlite e MySQL.
  - View `index`: pesquisa fixa no topo, chips de estado (Todas, Nova, Em contacto, Qualificada, Fria, Perdida) com `LeadStatus`, cartões `_card` (nome, estado em badge, origem, data, botões `tel:` e `https://wa.me/351…` e email `mailto:`), paginação simples "Carregar mais" (`$leads->nextPageUrl()`), botão flutuante "+" para `leads.create`.
  - `show`: reproduzir os dados carregados em `LeadV2Controller::show` (`proposals`, `convertedProposals`, `owner`, `FormProposal withCount('opportunities')`, `CostSimulator`, `LeadActivity with('user')`) e organizar em secções recolhíveis (accordion Bootstrap): Contacto, Estado/Follow-up, Formulários, Cotações (com badge de estado e marca "Convertida" → link para convertida), Convertidas, Simuladores, Timeline + formulário de nota rápida. Cada ação é um formulário POST com botão ≥ 44px.
  - Autorização por registo: `abort_unless(app(PermissionService::class)->allows($user, 'leads', 'view', $lead), 403)` (mesmo padrão dos controllers do angariador).
- [ ] **Step 4:** `php artisan test --filter="MobileLeadsTest|LeadKanbanTest|PermissionCatalogTest"` PASS.
- [ ] **Step 5:** commit `feat(mobile): leads — lista, ficha e estado (LeadService partilhado)`.

---

### Task 6: Leads mobile: criar, converter, follow-up, atividades, dono, eliminar

**Files:** mesmos da Task 5 + `resources/views/admin/mobile/leads/create.blade.php`.
**Interfaces:** consome `LeadService::create/convert/scheduleFollowup/logActivity/assignOwner`; `ClientMatcher::duplicateWarning(?string $email, ?string $phone, bool $confirm)` (retorna `['message','url']` ou null).

- [ ] **Step 1: Testes falhados** em `MobileLeadsTest`: criar lead válida (cria `Client` com `is_lead`, `lead_source=manual`, atividade "Lead criado manualmente"); criar com email duplicado devolve erro `duplicate` e não cria; converter põe `is_lead=false` e redireciona para `admin.mobile.clients.show`; follow-up exige data futura; atividade rejeita tipo inválido; eliminar remove; dono só aceita utilizador existente.
- [ ] **Step 2–4:** implementar (validação via `LeadService::rules()`; duplicado igual a `LeadV2Controller::store`, com `confirm_duplicate`). Formulário de criar: inputs `type="tel"`, `type="email"`, `inputmode`, `autocomplete`, `font-size:16px` (evita zoom do iOS), `<select>` nativos.
- [ ] **Step 5:** commit `feat(mobile): leads — criar, converter, follow-up, atividades, dono e eliminar`.

---

### Task 7: Pedidos de formulário e oportunidades (mobile)

**Files:**
- Create: `app/Http/Controllers/Admin/Mobile/FormProposalController.php`, `resources/views/admin/mobile/form-proposals/{index,show,create}.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Mobile/MobileFormProposalsTest.php`

**Procedimento:** ler `FormProposalV2Controller` (197 linhas), `ImportOpportunityController`, `ImportOpportunityContactController`. Extrair para `app/Services/FormProposalService.php` apenas o que o mobile reutiliza (criar pedido manual: validar `client_id`, marca/modelo contra `VehicleCatalog`, nasce `em_analise`; mudar estado), mantendo o desktop a delegar. As ações das oportunidades (estado, checklist, notas, contactos) **chamam os controllers existentes** `ImportOpportunityController` via rotas desktop com redirect de volta (`back()`), pois já são ações POST/PATCH/DELETE sem vista própria, **ou** — se devolverem JSON/redirect para vistas desktop — são envolvidas por métodos do `FormProposalService`. Decidir ao ler o código e documentar no commit.

Rotas: `form-proposals.show` GET `/formularios/{id}`, `form-proposals.create` GET `/formularios/novo?client_id=`, `form-proposals.store` POST `/formularios`, `form-proposals.status` PATCH `/formularios/{id}/estado`, `form-proposals.opportunities.show` GET `/formularios/{formProposal}/oportunidades/{opportunity}` (`scopeBindings`).

- [ ] **Step 1: Testes falhados**: ver pedido `site` com todos os campos submetidos (nome, marca, modelo, orçamento, etc.; lista exata lida em `form-proposals/_request-fields.blade.php`); criar pedido manual a partir da lead; mudar estado; oportunidade mostra checklist e contactos; ver oportunidade de outro pedido dá 404.
- [ ] **Step 2–4:** implementar; `show` reutiliza `_request-fields` como dados e apresenta-os como lista de pares rótulo/valor mobile (partial `admin/mobile/form-proposals/_fields.blade.php`).
- [ ] **Step 5:** commit `feat(mobile): pedidos de formulário e oportunidades`.

---

### Task 8: `ProposalService` e cotações mobile

**Files:**
- Create: `app/Services/ProposalService.php`, `app/Http/Controllers/Admin/Mobile/ProposalController.php`, `resources/views/admin/mobile/proposals/{index,show,form}.blade.php`
- Modify: `ProposalV2Controller` (delegar `store`/`update`/`updateStatus`/`accept` no service), `routes/web.php`
- Test: `tests/Feature/Mobile/MobileProposalsTest.php`, `tests/Feature/ProposalServiceTest.php` (caracterização desktop)

**Interfaces (a ler em `ProposalV2Controller::store/update/updateStatus/accept`, linhas indicadas por `grep -n "public function" app/Http/Controllers/Admin/ProposalV2Controller.php`):**
- `ProposalService::rules(): array` — regras de validação de `store` (incluindo lista de combustíveis, `transport_cost` obrigatório).
- `ProposalService::create(array $data, ?UploadedFile $image, ?int $formProposalId, ?int $opportunityId): Proposal` — corpo de `store`: nasce `Pendente`, gera `proposal_code`, liga oportunidade (`linkProposal`), liga pedido (passa `novo`→`em_analise`), aplica o estado pedido no fim com `ProposalAcceptanceService::changeStatus`.
- `ProposalService::update(Proposal $p, array $data, ?UploadedFile $image): Proposal` — recusa mudar o estado de cotação já aceite; recria atributos.
- `ProposalService::prefillFor(Client $client, ?FormProposal $form, ?ImportOpportunity $opp): array` — valores iniciais (cliente, marca, modelo, versão, ano, km, combustível, url) que `create`, `createFromForm` e `createFromOpportunity` do desktop já calculam; mobile e desktop partilham.
- Estado e aceitação: **sempre** `ProposalAcceptanceService::changeStatus` e `::accept($proposal, $clientData, false)`.

Rotas: `proposals.index` GET `/cotacoes`, `proposals.create` GET `/cotacoes/nova?client_id=&form_proposal_id=&opportunity_id=`, `proposals.store` POST `/cotacoes`, `proposals.show` GET `/cotacoes/{id}`, `proposals.edit` GET `/cotacoes/{id}/editar`, `proposals.update` PUT `/cotacoes/{id}`, `proposals.status` PATCH `/cotacoes/{id}/estado`, `proposals.accept` POST `/cotacoes/{id}/aceitar`.

- [ ] **Step 1: Testes falhados** (`MobileProposalsTest`):

```php
public function test_cotacao_criada_no_mobile_fica_associada_a_lead_e_visivel_no_desktop(): void
{
    $lead = Client::create(['name' => 'Ana', 'email' => 'ana@example.com', 'is_lead' => true, 'lead_status' => 'em_contacto']);
    $user = $this->mobileUser();

    $this->actingAs($user)->get(route('admin.mobile.proposals.create', ['client_id' => $lead->id]))
        ->assertOk()->assertSee('Ana');

    $this->actingAs($user)->post(route('admin.mobile.proposals.store'), [
        'client_id' => $lead->id, 'brand' => 'BMW', 'model' => 'Série 3', 'version' => '320d',
        'year' => 2021, 'fuel' => 'Diesel', 'transport_cost' => 800, 'status' => 'Pendente',
    ])->assertRedirect();

    $p = Proposal::where('client_id', $lead->id)->firstOrFail();
    $this->assertSame('Pendente', $p->status);
    $this->assertSame(12, strlen($p->proposal_code));

    // Visível no desktop, dentro da ficha da lead e na lista de cotações.
    $this->actingAs($user)->get(route('admin.v2.leads.show', $lead->id))->assertOk()->assertSee('BMW');
    $this->actingAs($user)->get(route('admin.v2.proposals.index'))->assertOk()->assertSee('BMW');
}

public function test_cotacao_a_partir_de_oportunidade_liga_e_marca_selecionado(): void { /* store com import_opportunity_id → opportunity.status = selecionado */ }
public function test_estado_enviado_passa_por_changeStatus(): void { /* Mail::fake(); status Enviado → follow-up +2 dias se vazio */ }
public function test_aceitar_pelo_backoffice_converte_lead_e_cria_convertida(): void { /* is_lead=false, ConvertedProposal existe, form → convertido */ }
public function test_cotacao_aceite_nao_muda_de_estado(): void { /* só admite Aprovada */ }
public function test_transport_cost_e_obrigatorio(): void
```

Os valores obrigatórios exatos de `fuel`, `status` e restantes campos lêem-se em `ProposalV2Controller::store` antes de escrever o teste; ajustar o payload em conformidade.

- [ ] **Step 2:** FAIL.
- [ ] **Step 3:** extrair o service **primeiro** com os testes desktop de caracterização (`tests/Feature/ProposalServiceTest.php`: POST em `admin.v2.proposals.store` verifica cotação, `proposal_code`, ligação a oportunidade e a pedido; o `ProposalFromOpportunityTest` existente tem de continuar verde), depois o controller mobile. Formulário mobile em passos (accordion/secções): Viatura → Custos (campos numéricos `inputmode="decimal"`) → Notas/links → Estado. Cliente e dados de viatura já vêm pré-preenchidos de `prefillFor`. Ficha mostra totais, validade (`isExpired`), link público `route('proposals.detail', $p->proposal_code)` com botão "Copiar/partilhar" (`navigator.share`, com fallback para copiar), estado e, se aceite, link para a convertida.
- [ ] **Step 4:** `php artisan test --filter="MobileProposalsTest|ProposalServiceTest|ProposalFromOpportunityTest|ProposalKanbanTest|PermissionCatalogTest"` PASS.
- [ ] **Step 5:** commit `feat(mobile): cotações — criar, editar, estado e aceitar (ProposalService partilhado)`.

---

### Task 9: Cotações convertidas (mobile, consulta e pipeline)

**Files:**
- Create: `app/Http/Controllers/Admin/Mobile/ConvertedProposalController.php`, `resources/views/admin/mobile/converted-proposals/{index,show}.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Mobile/MobileProposalsTest.php`

Consulta (lista com filtro por etapa do pipeline `ConvertedProposal::PIPELINE_STATUSES`, ficha com viatura, custos/pagamentos, histórico `StatusProposalHistory`). Mudança de estado: **reutilizar** a lógica de `Admin\ConvertedProposalController::updateStatus` (V1, usada pelo pipeline V2: valida o estado, grava, envia `ProposalStatusUpdatedMail` com try/catch) — extrair para `ConvertedProposalService::changeStatus(ConvertedProposal $c, string $status)` e delegar nos dois sítios, sem alterar o comportamento. Rotas: `converted-proposals.index` GET `/convertidas`, `.show` GET `/convertidas/{id}`, `.status` PATCH `/convertidas/{id}/estado`. Edição de pagamentos/documentos/contrato: fora de âmbito (ligação "Abrir no desktop").

- [ ] **Step 1: Testes**: lista só mostra as do utilizador no âmbito "próprios"; ficha mostra pipeline; mudar estado grava `StatusProposalHistory`; estado inválido rejeitado; Mail fake e falha de email não impede a atualização.
- [ ] **Step 2–4:** implementar. **Step 5:** commit `feat(mobile): cotações convertidas — consulta e pipeline`.

---

### Task 10: Clientes (mobile)

**Files:**
- Create: `app/Services/ClientService.php`, `app/Http/Controllers/Admin/Mobile/ClientController.php`, `resources/views/admin/mobile/clients/{index,show,form}.blade.php`
- Modify: `ClientV2Controller` (delegar `store`/`update`/`saveFollowup`/`storeActivity`), `routes/web.php`
- Test: `tests/Feature/Mobile/MobileClientsTest.php`, `tests/Feature/ClientServiceTest.php`

Ler `ClientV2Controller` (203 linhas) e extrair: `rules()`, `create(array $data): Client` (usar `ClientMatcher`, ver `docs/leads-e-clientes.md`: `update` **não** verifica duplicados, manter), `update`, e reutilizar `LeadService::scheduleFollowup/logActivity` (o `Client` é o mesmo modelo). Ficha: contacto (tel/WhatsApp/email), dados pessoais e documentos de identificação (dados já mostrados em `clients/show`), pedidos de formulário, cotações, convertidas, viaturas V3 do cliente (se o desktop as mostra), carros candidatos (`CarCandidate`, consulta; criar/editar através de `CarCandidateController` existente), timeline e nota rápida. Rotas: `clients.index` GET `/clientes`, `.create`, `.store`, `.show` GET `/clientes/{id}`, `.edit`, `.update`, `.followup`, `.activity`. `show/edit/update` abrem também leads (comportamento igual ao desktop; leads redirecionam para a ficha de lead mobile).

- [ ] **Step 1: Testes**: lista só `is_lead=false` com pesquisa; criar cliente; editar não altera `is_lead`; ficha lista formulários, cotações e convertidas do cliente; âmbito "próprios" (angariador com `clients.view.own` só vê os seus: criar utilizador angariador com `access_mobile` via `forceFill` e permissão via `givePermissionTo('clients.view.own')`; ler `PermissionManagementTest` para o padrão exato).
- [ ] **Step 2–4:** implementar. **Step 5:** commit `feat(mobile): clientes — lista, ficha, criar, editar e atividades`.

---

### Task 11: Pesquisa, permissões de dados e verificação final

**Files:**
- Create: `app/Http/Controllers/Admin/Mobile/SearchController.php`, `resources/views/admin/mobile/search.blade.php`
- Test: `tests/Feature/Mobile/MobilePermissionsTest.php`
- Modify: `docs/` (nova secção em `docs/leads-e-clientes.md` ou `docs/funil-comercial.md`: "Versão mobile")

- [ ] **Step 1: Testes**: pesquisa `admin.mobile.search?q=` devolve leads, clientes e cotações (por `proposal_code`/nome) respeitando `applyScope`; utilizador com `access_mobile` mas sem `leads.view` (com `permissions.enforce=true`) recebe 403 em `admin.mobile.leads.index`; com `enforce=false` passa e regista no log (igual ao desktop); angariador puro com `access_mobile` continua bloqueado (403) fora de `admin.angariador.*`; sem `access_mobile` nunca entra, nem como admin.
- [ ] **Step 2–4:** implementar. Reutilizar `GlobalSearchController` se já devolver estes resultados (ler antes; extrair a consulta para service se necessário).
- [ ] **Step 5: Verificação final**
  - `php artisan test` (toda a suite; registar o resultado exato e confirmar que só `ExampleTest` falha, como antes).
  - `php artisan route:list --path=gestao/mobile` mostra todas as rotas com os middlewares `mobile.access`.
  - Abrir o HTML de cada página mobile em sqlite (testes) e confirmar: viewport com `viewport-fit=cover`, inputs com `font-size:16px`, botões ≥ 44px, sem `<table>` largas (`assertDontSee('<table')`).
  - Se houver servidor local, `php artisan serve` com BD sqlite descartável e inspeção em modo responsivo iPhone (390×844); caso contrário registar que a verificação visual no iPhone fica para o Pedro.
- [ ] **Step 6:** commit `feat(mobile): pesquisa, testes de permissões e documentação`.

---

## Self-review

- **Cobertura do spec:** §1 → Tasks 1–3 e 11; §2 → Tasks 2, 5, 8, 10 (services) e `config/permissions.php` em 2; §3 fases 1–6 → Tasks 2/4, 5–6, 7, 8, 10, 9; §4 PWA → Task 4; §5 testes → cada task + 11; §6 BD → Task 1 (só migration; avisar `php artisan migrate`).
- **Pontos a confirmar durante a execução (não adivinhar):** se `User::factory()` aceita `access_mobile` em `$attributes` (por isso o helper `forceFill`); campos obrigatórios exatos de `ProposalV2Controller::store`; nome do objeto de permissões das convertidas em `config/permissions.php`; nome de perfil de teste sem `users.update`.
- **Risco principal:** extrações para service (`LeadService`, `ProposalService`, `ClientService`, `ConvertedProposalService`) tocam código do desktop; cada uma começa com testes de caracterização e só avança com a suite verde.
- **Fora de âmbito (assinalar no resumo final):** kanban, exportações XLSX, importar cotação por link, contrato PDF, uploads de documentos/fotos, edição de pagamentos da convertida, financeiro.
