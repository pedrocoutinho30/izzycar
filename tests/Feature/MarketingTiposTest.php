<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Ecrã de escolha do tipo de publicação: só "Viatura" exige uma viatura. */
class MarketingTiposTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    public function test_escolha_mostra_os_seis_tipos(): void
    {
        $r = $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.create'))->assertOk();
        foreach (['Viatura', 'Recomendação', 'Educativo', 'Serviço', 'Importação', 'Testemunho'] as $tipo) {
            $r->assertSee($tipo);
        }
        $r->assertSee(route('admin.marketing.vehicles.create'), false)
            ->assertSee(route('admin.marketing.content.create', 'educativo'), false);
    }

    public function test_formulario_de_viatura_continua_a_funcionar(): void
    {
        $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.vehicles.create'))
            ->assertOk()->assertSee('Viatura');
    }

    public function test_tipo_desconhecido_da_404(): void
    {
        $this->actingAs($this->backofficeUser('admin'))->get('/gestao/marketing/criar/inexistente')->assertNotFound();
    }

    public function test_angariador_nao_acede(): void
    {
        $u = $this->backofficeUser('angariador');
        $this->actingAs($u)->get(route('admin.marketing.create'))->assertForbidden();
        $this->actingAs($u)->get(route('admin.marketing.vehicles.create'))->assertForbidden();
        $this->actingAs($u)->get('/gestao/marketing/criar/educativo')->assertForbidden();
    }
}
