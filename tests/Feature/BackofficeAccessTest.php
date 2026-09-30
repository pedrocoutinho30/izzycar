<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class BackofficeAccessTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    public function test_public_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'X', 'last_name' => 'Y', 'email' => 'x@y.test',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'x@y.test']);
    }

    public function test_users_without_role_cannot_use_the_backoffice(): void
    {
        $user = User::factory()->create(['password' => 'secret', 'last_name' => 'Teste']);

        foreach (['admin.v2.users.index', 'admin.v2.roles.index', 'admin.v2.permissions.index', 'admin.v2.clients.index', 'admin.v2.settings.index'] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
        }
    }

    public function test_users_without_role_cannot_log_in(): void
    {
        User::factory()->create(['email' => 'semperfil@x.test', 'password' => 'secret123', 'last_name' => 'Teste']);

        $this->post('/login', ['email' => 'semperfil@x.test', 'password' => 'secret123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_financial_seed_is_admin_only_and_not_a_get(): void
    {
        $this->actingAs($this->backofficeUser('admin'))->get('/gestao/v2/financial/seed')->assertMethodNotAllowed();
        $this->actingAs($this->backofficeUser('Importador'))->post(route('admin.v2.financial.seed'))->assertForbidden();
    }

    public function test_migration_gives_importador_to_users_without_role(): void
    {
        $withoutRole = User::factory()->create(['password' => 'secret', 'last_name' => 'Teste']);
        $admin = $this->backofficeUser('admin');

        $migration = require base_path('database/migrations/2026_09_30_130000_assign_importador_to_users_without_role.php');
        $migration->up();

        $this->assertSame(['Importador'], $withoutRole->fresh()->getRoleNames()->all());
        $this->assertSame(['admin'], $admin->fresh()->getRoleNames()->all());
        $this->assertSame(1, DB::table('roles')->where('name', 'Importador')->count());
    }
}
