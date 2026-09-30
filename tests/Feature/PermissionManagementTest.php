<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Permissions\PermissionSynchronizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        config(['permissions.enforce' => true]);
        Mail::fake();

        $this->admin = $this->backofficeUser('admin');
    }

    public function test_profile_page_shows_matrix_and_catalog_page_renders(): void
    {
        $importador = Role::findByName('Importador');

        $this->actingAs($this->admin)
            ->get(route('admin.v2.roles.edit', $importador->id))
            ->assertOk()
            ->assertSee('Funil de vendas')
            ->assertSee('name="grants[leads][view]"', false)
            ->assertSee('<option value="all" selected>Todos</option>', false)
            ->assertSee('name="grants[reports][view]"', false)
            ->assertDontSee('name="grants[reports][create]"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.v2.roles.edit', Role::findOrCreate('admin', 'web')->id))
            ->assertOk()
            ->assertSee('acesso total')
            ->assertDontSee('name="grants[leads][view]"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.v2.permissions.index'))
            ->assertOk()
            ->assertSee('Leads')
            ->assertSee('Importador · Todos')
            ->assertSee('angariador · Próprios');
    }

    public function test_updating_a_profile_syncs_permissions_keeps_legacy_ones_and_is_audited(): void
    {
        $role = Role::findByName('cms');
        Permission::findOrCreate('gerir noticias', 'web');
        $role->givePermissionTo('gerir noticias');

        $this->actingAs($this->admin)
            ->put(route('admin.v2.roles.update', $role->id), [
                'name' => 'cms',
                'grants' => [
                    'news' => ['view' => '1', 'create' => '1', 'update' => '', 'delete' => ''],
                    'leads' => ['view' => 'own', 'create' => '', 'update' => '', 'delete' => ''],
                ],
            ])
            ->assertRedirect(route('admin.v2.roles.index'));

        $permissions = $role->fresh()->permissions->pluck('name')->sort()->values()->all();
        $this->assertSame(['gerir noticias', 'leads.view.own', 'news.create', 'news.view'], $permissions);

        $log = AuditLog::where('action', 'role_permissions_updated')->sole();
        $this->assertSame($this->admin->id, $log->user_id);
        $this->assertContains('leads.view.own', $log->new_values['added']);
        $this->assertContains('news.delete', $log->new_values['removed']);
        $this->assertContains('news.delete', $log->old_values['permissions']);

        // O efeito é imediato (cache limpa).
        $cmsUser = $this->backofficeUser('cms');
        $this->actingAs($cmsUser)->get(route('admin.angariador.leads'))->assertOk();
        $this->actingAs($cmsUser)->get(route('admin.testimonials.index'))->assertForbidden();
    }

    public function test_protected_profiles_cannot_be_renamed_or_deleted(): void
    {
        $angariador = Role::findByName('angariador');

        $this->actingAs($this->admin)->put(route('admin.v2.roles.update', $angariador->id), ['name' => 'outro', 'grants' => []]);
        $this->assertSame('angariador', $angariador->fresh()->name);

        $this->actingAs($this->admin)->delete(route('admin.v2.roles.destroy', $angariador->id))->assertSessionHas('error');
        $this->assertModelExists($angariador);

        $admin = Role::findByName('admin');
        $this->actingAs($this->admin)->put(route('admin.v2.roles.update', $admin->id), ['name' => 'admin', 'grants' => ['leads' => ['view' => 'own']]]);
        $this->assertSame(0, $admin->fresh()->permissions()->count());
    }

    public function test_creating_a_profile_from_the_matrix(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.v2.roles.store'), ['name' => 'Contabilidade', 'grants' => ['movements' => ['view' => '1'], 'reports' => ['view' => '1']]])
            ->assertRedirect(route('admin.v2.roles.index'));

        $this->assertEqualsCanonicalizing(['movements.view', 'reports.view'], Role::findByName('Contabilidade')->permissions->pluck('name')->all());
        $this->assertSame(1, AuditLog::where('action', 'role_created')->count());
    }

    public function test_users_can_have_several_profiles_and_changes_are_audited(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.v2.users.store'), [
                'name' => 'João', 'last_name' => 'Silva', 'email' => 'joao@izzycar.test',
                'roles' => ['Importador', 'cms'],
            ])
            ->assertSessionHasNoErrors();

        $joao = User::where('email', 'joao@izzycar.test')->sole();
        $this->assertEqualsCanonicalizing(['Importador', 'cms'], $joao->getRoleNames()->all());

        $this->actingAs($this->admin)
            ->put(route('admin.v2.users.update', $joao->id), [
                'name' => 'João', 'last_name' => 'Silva', 'email' => 'joao@izzycar.test',
                'roles' => ['Importador'],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(['Importador'], $joao->fresh()->getRoleNames()->all());
        $log = AuditLog::where('action', 'user_roles_updated')->latest('id')->first();
        $this->assertSame(['cms'], $log->new_values['removed']);
        $this->assertSame($joao->id, $log->auditable_id);

        $this->actingAs($this->admin)
            ->put(route('admin.v2.users.update', $joao->id), ['name' => 'João', 'last_name' => 'Silva', 'email' => 'joao@izzycar.test', 'roles' => []])
            ->assertSessionHasErrors('roles');
    }

    public function test_only_admins_can_grant_the_admin_profile(): void
    {
        app(PermissionSynchronizer::class)->syncRolePermissions(Role::findOrCreate('RH', 'web'), ['users.view', 'users.create', 'users.update']);
        $rh = $this->backofficeUser('RH');

        $this->actingAs($rh)
            ->put(route('admin.v2.users.update', $rh->id), ['name' => 'RH', 'last_name' => 'Teste', 'email' => $rh->email, 'roles' => ['RH', 'admin']])
            ->assertSessionHasErrors('roles');

        $this->assertFalse($rh->fresh()->hasRole('admin'));
    }
}
