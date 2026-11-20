<?php

namespace Tests\Feature;

use App\Marketing\PostRenderer;
use App\Models\Client;
use App\Models\MarketingPost;
use App\Models\V3Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Importações realizadas: exigem autorização, nunca mostram cliente, matrícula nem VIN. */
class MarketingImportacoesTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function admin()
    {
        return $this->actingAs($this->backofficeUser('admin'));
    }

    private function viaturaImportada(bool $importada = true): V3Vehicle
    {
        $cliente = Client::create(['name' => 'Joaquim Cliente Secreto', 'is_lead' => false, 'lead_status' => 'nova']);

        return V3Vehicle::create([
            'reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'X3', 'version' => '20d', 'year' => 2020,
            'status' => 'reservado', 'is_imported' => $importada, 'client_id' => $cliente->id,
            'registration' => 'AA-11-BB', 'vin' => 'WBAVIN1234567890X',
        ]);
    }

    private function criarDeViatura(V3Vehicle $v, array $extra = [])
    {
        return $this->admin()->postJson(route('admin.marketing.content.store', 'importacao'), array_merge([
            'vehicle_id' => $v->id, 'template' => 'importacao', 'format' => 'feed_vertical', 'authorized' => '1',
        ], $extra));
    }

    public function test_sem_autorizacao_da_422(): void
    {
        $v = $this->viaturaImportada();
        $this->criarDeViatura($v, ['authorized' => '0'])->assertUnprocessable()->assertJsonValidationErrors('authorized');

        $this->admin()->postJson(route('admin.marketing.content.store', 'importacao'), [
            'vehicle_id' => $v->id, 'template' => 'importacao', 'format' => 'feed_vertical',
        ])->assertUnprocessable()->assertJsonValidationErrors('authorized');

        $this->admin()->postJson(route('admin.marketing.content.store', 'importacao'), [
            'brand' => 'Audi', 'model' => 'A4', 'template' => 'importacao', 'format' => 'feed_vertical',
        ])->assertUnprocessable()->assertJsonValidationErrors('authorized');
        $this->assertSame(0, MarketingPost::count());
    }

    public function test_instantaneo_nao_tem_cliente_matricula_nem_vin(): void
    {
        $this->criarDeViatura($this->viaturaImportada(), ['origin_country' => 'Alemanha'])->assertCreated();

        $post = MarketingPost::firstOrFail();
        $json = json_encode($post->data) . json_encode($post->getAttributes());
        foreach (['Joaquim', 'Secreto', 'AA-11-BB', 'WBAVIN', 'client', 'registration', 'vin'] as $proibido) {
            $this->assertStringNotContainsStringIgnoringCase($proibido, $json, $proibido);
        }
        $html = PostRenderer::render($post)['html'];
        foreach (['Joaquim', 'AA-11-BB', 'WBAVIN'] as $proibido) {
            $this->assertStringNotContainsString($proibido, $html);
        }
        $this->assertTrue($post->data['authorized']);
        $this->assertNull($post->source_type);
    }

    public function test_titulo_por_omissao_usa_a_marca(): void
    {
        $this->criarDeViatura($this->viaturaImportada(), ['origin_country' => 'Alemanha'])->assertCreated();

        $html = PostRenderer::render(MarketingPost::firstOrFail())['html'];
        $this->assertStringContainsString('Mais um BMW entregue em Portugal!', $html);
        $this->assertStringContainsString('Importado de Alemanha', $html);
    }

    public function test_sem_pais_nao_ha_subtitulo_de_origem(): void
    {
        $this->criarDeViatura($this->viaturaImportada())->assertCreated();

        $html = PostRenderer::render(MarketingPost::firstOrFail())['html'];
        $this->assertStringNotContainsString('Importado de', $html);
        $this->assertStringNotContainsString('null', strtolower($html));
        $this->assertStringNotContainsString('€', $html);
    }

    public function test_so_aceita_viaturas_importadas(): void
    {
        $this->criarDeViatura($this->viaturaImportada(false))->assertUnprocessable()->assertJsonValidationErrors('vehicle_id');
    }

    public function test_manual_guarda_fotos_validadas(): void
    {
        Storage::fake('public');

        $this->admin()->post(route('admin.marketing.content.store', 'importacao'), [
            'brand' => 'Audi', 'model' => 'A4', 'origin_country' => 'França', 'savings' => '2500', 'authorized' => '1',
            'template' => 'importacao', 'format' => 'feed_square',
            'photos' => [UploadedFile::fake()->image('a.jpg', 1600, 1000), UploadedFile::fake()->image('b.jpg', 1600, 1000)],
        ])->assertRedirect();

        $post = MarketingPost::firstOrFail();
        $this->assertCount(2, $post->data['photos']);
        foreach ($post->data['photos'] as $f) {
            Storage::disk('public')->assertExists($f['path']);
            $this->assertStringStartsWith("marketing-posts/{$post->id}/", $f['path']);
        }
        $this->assertEquals(2500, $post->data['savings']);

        $this->admin()->post(route('admin.marketing.content.store', 'importacao'), [
            'brand' => 'Audi', 'model' => 'A4', 'authorized' => '1', 'template' => 'importacao', 'format' => 'feed_square',
            'photos' => [UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')],
        ])->assertSessionHasErrors('photos.0');
        $this->assertSame(1, MarketingPost::count());
    }

    public function test_manual_exige_marca_e_modelo(): void
    {
        $this->admin()->postJson(route('admin.marketing.content.store', 'importacao'), [
            'authorized' => '1', 'template' => 'importacao', 'format' => 'feed_square',
        ])->assertUnprocessable();
    }

    public function test_pesquisa_filtra_importadas(): void
    {
        $this->viaturaImportada();
        V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Fiat', 'model' => 'Panda', 'status' => 'em_stock']);

        $res = $this->admin()->getJson(route('admin.marketing.vehicles.search', ['imported' => 1]))->assertOk();
        $this->assertCount(1, $res->json());
        $this->assertStringContainsString('BMW', $res->json('0.label'));
    }

    public function test_recomendacao_continua_a_guardar_fotos(): void
    {
        Storage::fake('public');

        $this->admin()->post(route('admin.marketing.recommendations.store'), [
            'brand' => 'Seat', 'model' => 'Leon', 'template' => 'recomendacao', 'format' => 'feed_square',
            'image' => UploadedFile::fake()->image('p.jpg', 1600, 1000),
            'gallery_photos' => [UploadedFile::fake()->image('g.jpg', 1600, 1000)],
        ])->assertRedirect();

        $post = MarketingPost::firstOrFail();
        $this->assertCount(2, $post->data['photos']);
        Storage::disk('public')->assertExists($post->data['photos'][0]['path']);
    }

    public function test_atualizar_dados_nao_se_aplica_a_importacoes(): void
    {
        $this->criarDeViatura($this->viaturaImportada())->assertCreated();
        $post = MarketingPost::firstOrFail();

        $this->admin()->get(route('admin.marketing.editor', $post))->assertOk()->assertDontSee('Joaquim');
        $this->admin()->post(route('admin.marketing.refresh', $post))->assertSessionHas('error');
    }
}
