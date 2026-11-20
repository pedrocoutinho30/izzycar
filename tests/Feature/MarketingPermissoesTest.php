<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Acesso ao Marketing Studio: admin entra, angariador e utilizadores sem perfil não. */
class MarketingPermissoesTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    public function test_admin_ve_o_indice(): void
    {
        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.marketing.index'))
            ->assertOk()
            ->assertSee('Marketing Studio');
    }

    public function test_angariador_nao_acede(): void
    {
        $this->actingAs($this->backofficeUser('angariador'))
            ->get(route('admin.marketing.index'))
            ->assertForbidden();
    }

    public function test_utilizador_sem_perfil_nao_acede(): void
    {
        $user = \App\Models\User::factory()->create(['password' => 'secret', 'last_name' => 'Teste']);

        $this->actingAs($user)->get(route('admin.marketing.index'))->assertForbidden();
    }

    public function test_visitante_vai_para_o_login(): void
    {
        $this->get('/gestao/marketing')->assertRedirect();
    }
}
