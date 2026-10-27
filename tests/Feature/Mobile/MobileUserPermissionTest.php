<?php

namespace Tests\Feature\Mobile;

use App\Models\AuditLog;
use App\Models\User;
use App\Permissions\PermissionSynchronizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

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
        $this->assertSame(1, AuditLog::where('action', 'user_mobile_access_updated')->count());

        $this->actingAs($admin)->put(route('admin.v2.users.update', $alvo->id), $this->payload($alvo))->assertRedirect();
        $this->assertFalse($alvo->refresh()->access_mobile);
        $this->assertSame(2, AuditLog::where('action', 'user_mobile_access_updated')->count());

        // Sem alteração de valor, não regista auditoria.
        $this->actingAs($admin)->put(route('admin.v2.users.update', $alvo->id), $this->payload($alvo))->assertRedirect();
        $this->assertSame(2, AuditLog::where('action', 'user_mobile_access_updated')->count());
    }

    public function test_admin_cria_utilizador_com_acesso_mobile(): void
    {
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->post(route('admin.v2.users.store'), [
            'name' => 'Novo', 'last_name' => 'Teste', 'email' => 'novo@example.com', 'roles' => ['admin'], 'access_mobile' => '1',
        ])->assertRedirect();

        $this->assertTrue(User::where('email', 'novo@example.com')->firstOrFail()->access_mobile);
    }

    public function test_utilizador_sem_autorizacao_nao_se_atribui_acesso_mesmo_enviando_o_campo(): void
    {
        $role = Role::findOrCreate('semusers', 'web');
        $user = User::factory()->create(['password' => 'secret', 'last_name' => 'Teste']);
        $user->assignRole($role);

        $this->actingAs($user)->put(route('admin.v2.users.update', $user->id), $this->payload($user, ['roles' => ['semusers'], 'access_mobile' => '1']));
        $this->assertFalse($user->refresh()->access_mobile);
    }

    public function test_quem_gere_utilizadores_altera_o_acesso_de_outros_mas_nao_o_proprio(): void
    {
        $rh = Role::findOrCreate('RH', 'web');
        app(PermissionSynchronizer::class)->syncRolePermissions($rh, ['users.view', 'users.update']);
        $ator = $this->backofficeUser('RH');
        $alvo = $this->backofficeUser('RH');

        $this->actingAs($ator)->put(route('admin.v2.users.update', $alvo->id), $this->payload($alvo, ['roles' => ['RH'], 'access_mobile' => '1']))->assertRedirect();
        $this->assertTrue($alvo->refresh()->access_mobile);

        $this->actingAs($ator)->put(route('admin.v2.users.update', $ator->id), $this->payload($ator, ['roles' => ['RH'], 'access_mobile' => '1']))->assertRedirect();
        $this->assertFalse($ator->refresh()->access_mobile);
    }

    public function test_checkbox_aparece_so_a_quem_pode_geri_la(): void
    {
        $admin = $this->backofficeUser('admin');
        $alvo = $this->backofficeUser('admin');
        $this->actingAs($admin)->get(route('admin.v2.users.edit', $alvo->id))
            ->assertOk()->assertSee('name="access_mobile"', false)->assertSee('Acesso à versão mobile');
        $this->actingAs($admin)->get(route('admin.v2.users.create'))
            ->assertOk()->assertSee('name="access_mobile"', false);

        $semUsers = $this->backofficeUser('semusers');
        $this->actingAs($semUsers)->get(route('admin.v2.users.edit', $alvo->id))
            ->assertOk()->assertDontSee('name="access_mobile"', false);
    }

    public function test_perfil_proprio_nunca_escreve_access_mobile(): void
    {
        $user = $this->backofficeUser('semusers');

        $this->actingAs($user)->put(route('profile.update'), ['name' => $user->name, 'last_name' => 'Teste', 'email' => $user->email, 'access_mobile' => '1']);
        $this->assertFalse($user->refresh()->access_mobile);
    }
}
