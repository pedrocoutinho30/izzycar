<?php

namespace Tests\Feature;

use App\Models\MarketingPost;
use App\Models\V3Vehicle;
use App\Models\V3VehiclePhoto;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Editor: pré-visualização com o mesmo HTML da exportação e edições guardadas à parte do instantâneo. */
class MarketingEditorTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function novoPost(string $status = 'em_stock', ?float $preco = 30000, string $template = 'premium'): MarketingPost
    {
        $v = V3Vehicle::create([
            'reference' => V3Vehicle::generateReference(), 'brand' => 'Audi', 'model' => 'A3', 'version' => '35 TFSI',
            'year' => 2021, 'kilometers' => 42000, 'fuel' => 'Gasolina', 'status' => $status, 'asking_price' => $preco,
        ]);
        V3VehiclePhoto::create(['v3_vehicle_id' => $v->id, 'path' => 'a.jpg', 'order_position' => 1, 'is_cover' => true]);

        $this->actingAs($this->backofficeUser('admin'))->postJson(route('admin.marketing.store'), [
            'type' => 'veiculo', 'vehicle_id' => $v->id, 'template' => $template, 'format' => 'feed_vertical',
        ])->assertCreated();

        return MarketingPost::latest('id')->firstOrFail();
    }

    private function admin()
    {
        return $this->actingAs($this->backofficeUser('admin'));
    }

    public function test_editor_abre_com_titulo_e_preview(): void
    {
        $post = $this->novoPost();

        $this->admin()->get(route('admin.marketing.editor', $post))
            ->assertOk()
            ->assertSee('Audi A3')
            ->assertSee('mk-slide', false);
    }

    public function test_preview_devolve_html_com_dados_reais(): void
    {
        $post = $this->novoPost();

        $res = $this->admin()->postJson(route('admin.marketing.preview', $post), ['template' => 'comercial', 'format' => 'feed_square']);

        $res->assertOk();
        $html = $res->json('html');
        $this->assertStringContainsString('Audi A3', $html);
        $this->assertStringContainsString('30.000 €', $html);
        $this->assertStringContainsString('width:1080px;height:1080px', str_replace(' ', '', $html));
    }

    public function test_preview_omite_preco_quando_viatura_nao_tem(): void
    {
        $post = $this->novoPost('em_stock', null);

        $html = $this->admin()->postJson(route('admin.marketing.preview', $post), [])->json('html');

        $this->assertStringNotContainsString('€', $html);
    }

    public function test_preview_escapa_html_do_utilizador(): void
    {
        $post = $this->novoPost();

        $html = $this->admin()->postJson(route('admin.marketing.preview', $post), ['overrides' => ['title' => '<script>alert(1)</script>']])->json('html');

        $this->assertStringNotContainsString('<script>alert(1)', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_update_guarda_overrides_sem_alterar_instantaneo(): void
    {
        $post = $this->novoPost();
        $antes = $post->data;

        $this->admin()->putJson(route('admin.marketing.update', $post), [
            'title' => 'Novo título', 'caption' => 'Legenda', 'hashtags' => '#izzycar', 'cta' => 'Fala connosco',
            'template' => 'comercial', 'format' => 'feed_square',
            'overrides' => ['title' => 'Audi A3 Sportback', 'price' => 29500, 'highlights' => ['Teto panorâmico']],
            'photo_order' => [0],
        ])->assertOk();

        $post->refresh();
        $this->assertSame($antes, $post->data);
        $this->assertSame('comercial', $post->template);
        $this->assertSame('Novo título', $post->title);
        $this->assertSame('Audi A3 Sportback', $post->config['overrides']['title']);
        $this->assertEquals(29500, $post->config['overrides']['price']);
        $this->assertSame(['Teto panorâmico'], $post->config['overrides']['highlights']);
    }

    public function test_preco_alterado_fica_assinalado_no_editor(): void
    {
        $post = $this->novoPost();
        $this->admin()->putJson(route('admin.marketing.update', $post), [
            'template' => 'premium', 'format' => 'feed_vertical', 'overrides' => ['price' => 25000],
        ])->assertOk();

        $this->admin()->get(route('admin.marketing.editor', $post))->assertSee('difere do catálogo');
    }

    public function test_update_rejeita_template_vendido_para_viatura_em_stock(): void
    {
        $post = $this->novoPost('em_stock');

        $this->admin()->putJson(route('admin.marketing.update', $post), ['template' => 'vendido', 'format' => 'feed_vertical'])->assertStatus(422);
        $this->assertSame('premium', $post->fresh()->template);
    }

    public function test_update_rejeita_formato_nao_suportado(): void
    {
        $post = $this->novoPost('vendido', 30000, 'vendido');

        $this->admin()->putJson(route('admin.marketing.update', $post), ['template' => 'vendido', 'format' => 'story'])->assertStatus(422);
    }

    public function test_angariador_nao_edita(): void
    {
        $post = $this->novoPost();

        $this->actingAs($this->backofficeUser('angariador'))
            ->putJson(route('admin.marketing.update', $post), ['template' => 'premium', 'format' => 'feed_vertical'])->assertForbidden();
    }

    public function test_update_pode_aprovar_mas_nunca_marcar_publicado(): void
    {
        $post = $this->novoPost();
        $base = ['template' => 'premium', 'format' => 'feed_vertical'];

        $this->admin()->putJson(route('admin.marketing.update', $post), $base + ['status' => 'aprovado'])->assertOk();
        $this->assertSame('aprovado', $post->fresh()->status->value);

        $this->admin()->putJson(route('admin.marketing.update', $post), $base + ['status' => 'publicado'])->assertStatus(422);
        $this->assertSame('aprovado', $post->fresh()->status->value);
        $this->assertNull($post->fresh()->published_at);
    }
}
