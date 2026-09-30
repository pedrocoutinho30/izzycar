<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\User;
use App\Permissions\PermissionService;
use App\Permissions\PermissionSynchronizer;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ProfileAccessTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        config(['permissions.enforce' => true]);
    }

    private function lead(?User $owner, string $name = 'Lead'): Client
    {
        return Client::create(['name' => $name, 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $owner?->id]);
    }

    public function test_migration_applied_the_profiles(): void
    {
        $this->assertNull(Role::where('name', 'gestor')->first());
        $this->assertTrue(Role::findByName('Importador')->hasPermissionTo('leads.view.all'));
        $this->assertTrue(Role::findByName('angariador')->hasPermissionTo('leads.view.own'));
        $this->assertFalse(Role::findByName('angariador')->hasPermissionTo('leads.delete'));
        $this->assertTrue(Role::findByName('cms')->hasPermissionTo('news.create'));
    }

    public function test_admin_has_access_to_everything_including_future_permissions(): void
    {
        $admin = $this->backofficeUser('admin');

        foreach (['admin.v2.users.index', 'admin.v2.roles.index', 'admin.v2.sales.index', 'admin.v2.settings.index', 'admin.v2.audit-log'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }

        $this->assertTrue($admin->can('leads.view.all'));
        $this->assertTrue($admin->can('something-created-tomorrow.view'));
    }

    public function test_importador_accesses_only_its_modules(): void
    {
        $user = $this->backofficeUser('Importador');

        foreach (['admin.v2.leads.index', 'admin.v2.clients.index', 'admin.v2.form-proposals.index', 'admin.v2.proposals.index', 'admin.tasks.index', 'admin.v2.radar.index', 'admin.v2.reports.index', 'admin.v2.sellers.index'] as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }

        foreach (['admin.v2.sales.index', 'admin.v3.inspections.index', 'admin.v2.users.index', 'admin.v2.roles.index', 'admin.v2.permissions.index', 'admin.news.index', 'admin.v2.settings.index', 'admin.v2.suppliers.index'] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
        }

        $this->actingAs($user)->getJson(route('admin.v2.sellers.search', ['q' => 'x']))->assertOk();
    }

    public function test_cms_accesses_only_site_content(): void
    {
        $user = $this->backofficeUser('cms');

        $this->actingAs($user)->get(route('admin.news.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.testimonials.index'))->assertOk();

        foreach (['admin.v2.leads.index', 'admin.v2.clients.index', 'admin.v2.users.index', 'admin.v3.vehicles.index'] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
        }
    }

    public function test_angariador_sees_only_own_leads(): void
    {
        $joao = $this->backofficeUser('angariador');
        $pedro = $this->backofficeUser('angariador');
        $mine = $this->lead($joao, 'Lead do João');
        $other = $this->lead($pedro, 'Lead do Pedro');

        $this->actingAs($joao)->get(route('admin.angariador.leads'))->assertOk()->assertSee('Lead do João')->assertDontSee('Lead do Pedro');
        $this->actingAs($joao)->get(route('admin.angariador.leads.show', $mine))->assertOk();
        $this->actingAs($joao)->get(route('admin.angariador.leads.show', $other))->assertForbidden();
        $this->actingAs($joao)->post(route('admin.angariador.leads.status', $other), ['lead_status' => 'fria'])->assertForbidden();

        // Os ecrãs V2 mostram todos os registos: exigem "Ver → Todos".
        $this->actingAs($joao)->get(route('admin.v2.leads.index'))->assertForbidden();
        $this->actingAs($joao)->get(route('admin.v2.leads.show', $other))->assertForbidden();
        $this->actingAs($joao)->get(route('admin.v2.export.leads'))->assertForbidden();

        $this->assertTrue($joao->can('leads.view'));
        $this->assertFalse($joao->can('leads.view.all'));
        $this->assertTrue($joao->can('leads.update', $mine));
        $this->assertFalse($joao->can('leads.update', $other));
        $this->assertFalse($joao->can('leads.delete'));
    }

    public function test_scope_filters_queries_by_owner(): void
    {
        $joao = $this->backofficeUser('angariador');
        $mine = $this->lead($joao, 'Minha');
        $this->lead(null, 'Sem dono');
        Proposal::create(['client_id' => $mine->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0]);
        Proposal::create(['client_id' => $this->lead(null)->id, 'brand' => 'Tesla', 'model' => 'Model 3', 'transport_cost' => 0]);

        $permissions = app(PermissionService::class);

        $this->assertSame(['Minha'], $permissions->applyScope(Client::query(), $joao, 'leads')->pluck('name')->all());
        $this->assertSame(['BMW'], $permissions->applyScope(Proposal::query(), $joao, 'proposals')->pluck('brand')->all());
        // Sem permissão nenhuma: nada.
        $this->assertSame(0, $permissions->applyScope(Client::query(), $joao, 'clients')->count());
        // Todos: sem filtro.
        $this->assertSame(3, $permissions->applyScope(Client::query(), $this->backofficeUser('Importador'), 'leads')->count());
    }

    public function test_multiple_profiles_sum_permissions_and_widest_scope_wins(): void
    {
        $user = $this->backofficeUser('angariador');
        $user->assignRole('cms');

        $this->assertFalse($user->isAngariadorOnly());
        $this->actingAs($user)->get(route('admin.news.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.angariador.leads'))->assertOk();
        $this->actingAs($user)->get(route('admin.v2.leads.index'))->assertForbidden();

        app(PermissionSynchronizer::class)->syncRolePermissions(Role::findOrCreate('Supervisor', 'web'), ['leads.view.all']);
        $user->assignRole('Supervisor');
        app()->forgetInstance(PermissionService::class);

        $this->assertSame('all', app(PermissionService::class)->scope($user->fresh(), 'leads', 'view'));
        $this->actingAs($user->fresh())->get(route('admin.v2.leads.index'))->assertOk();
    }

    public function test_shadow_mode_only_logs(): void
    {
        config(['permissions.enforce' => false]);
        $log = storage_path('logs/permissions-test.log');
        @unlink($log);
        config(['logging.channels.permissions' => ['driver' => 'single', 'path' => $log, 'formatter' => \Monolog\Formatter\JsonFormatter::class]]);

        $user = $this->backofficeUser('Importador');
        $this->actingAs($user)->get(route('admin.v2.sales.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.v2.leads.index'))->assertOk();

        $entries = array_map(fn ($line) => json_decode($line, true), file($log, FILE_IGNORE_NEW_LINES));
        $this->assertCount(1, $entries);
        $this->assertSame('would_block', $entries[0]['message']);
        $this->assertSame('admin.v2.sales.index', $entries[0]['context']['route']);
        $this->assertSame('sales.view', $entries[0]['context']['requires']);
        @unlink($log);
    }

    public function test_menu_shows_only_accessible_items(): void
    {
        $response = $this->actingAs($this->backofficeUser('Importador'))->get(route('admin.v2.leads.index'))->assertOk();

        $response->assertSee(route('admin.v2.clients.index'), false)
            ->assertSee(route('admin.v2.radar.index'), false)
            ->assertDontSee(route('admin.v2.sales.index'), false)
            ->assertDontSee(route('admin.v2.users.index'), false)
            ->assertDontSee('Conteúdo do Site')
            ->assertDontSee('>Sistema<', false);
    }

    public function test_search_only_returns_sections_the_user_can_open(): void
    {
        $this->lead(null, 'Zacarias Pesquisa');

        $groups = fn (User $user) => collect($this->actingAs($user)->getJson(route('admin.v2.search', ['q' => 'Zacarias']))->assertOk()->json('groups'))->pluck('label')->all();

        $this->assertContains('Leads', $groups($this->backofficeUser('Importador')));
        $this->assertNotContains('Leads', $groups($this->backofficeUser('cms')));
    }
}
