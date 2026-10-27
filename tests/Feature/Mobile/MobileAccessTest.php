<?php

namespace Tests\Feature\Mobile;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MobileAccessTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_novos_utilizadores_ficam_sem_acesso_mobile_e_o_campo_nao_e_atribuivel_em_massa(): void
    {
        // A factory não define last_name (NOT NULL em sqlite) e o hash por omissão é bcrypt custo 10,
        // mas o phpunit usa BCRYPT_ROUNDS=4: definir os dois como os restantes testes (ver tests/TestCase.php).
        $user = User::factory()->create(['password' => 'secret', 'last_name' => 'Teste']);
        $this->assertFalse($user->refresh()->access_mobile);

        $user->fill(['access_mobile' => true]);
        $this->assertFalse((bool) $user->access_mobile);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    /** access_mobile fora do $fillable: define-se com forceFill. */
    private function mobileUser(bool $acesso = true): User
    {
        $user = $this->backofficeUser('admin');
        $user->forceFill(['access_mobile' => $acesso])->save();

        return $user;
    }

    public function test_utilizador_com_permissao_acede_ao_mobile(): void
    {
        $this->actingAs($this->mobileUser())->get(route('admin.mobile.dashboard'))->assertOk();
    }

    public function test_o_ecra_mobile_declara_manifest_e_metadados_pwa(): void
    {
        $this->actingAs($this->mobileUser())->get(route('admin.mobile.dashboard'))
            ->assertOk()
            ->assertSee('rel="manifest" href="' . asset('mobile-manifest.json') . '"', false)
            ->assertSee('apple-mobile-web-app-title" content="IzzyCar Gestão"', false)
            ->assertSee('rel="apple-touch-icon" href="' . asset('img/mobile/icon-180.png') . '"', false);
    }

    public function test_utilizador_sem_permissao_vai_para_o_desktop_com_mensagem_e_sem_ciclo(): void
    {
        $user = $this->mobileUser(false);
        $this->actingAs($user)->get('/gestao/mobile')
            ->assertRedirect(route('admin.v2.dashboard'))
            ->assertSessionHas('error', 'Não tem acesso à versão mobile.');

        $this->actingAs($user)->get(route('admin.v2.dashboard'))->assertOk();
    }

    public function test_visitante_vai_para_login(): void
    {
        $this->get('/gestao/mobile')->assertRedirect('/login');
    }
}
