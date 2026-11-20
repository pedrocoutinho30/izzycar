<?php

namespace Tests\Feature;

use App\Models\MarketingPost;
use App\Models\RecommendationPost;
use App\Models\V3Vehicle;
use App\Models\V3VehiclePhoto;
use App\Services\MarketingRecommendationImporter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Regressões apanhadas na revisão final da Fase 1. */
class MarketingRevisaoTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function viatura(int $fotos, string $status = 'em_stock'): V3Vehicle
    {
        $v = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Audi', 'model' => 'A3', 'status' => $status, 'asking_price' => 20000]);
        for ($i = 1; $i <= $fotos; $i++) {
            V3VehiclePhoto::create(['v3_vehicle_id' => $v->id, 'path' => "f{$i}.jpg", 'order_position' => $i]);
        }

        return $v;
    }

    private function postDe(V3Vehicle $v, string $template = 'premium'): MarketingPost
    {
        $this->actingAs($this->backofficeUser('admin'))->postJson(route('admin.marketing.store'), [
            'type' => 'veiculo', 'vehicle_id' => $v->id, 'template' => $template, 'format' => 'feed_vertical',
        ])->assertCreated();

        return MarketingPost::latest('id')->firstOrFail();
    }

    public function test_editor_aceita_ordem_inicial_com_mais_de_12_fotos(): void
    {
        $post = $this->postDe($this->viatura(15));

        $res = $this->actingAs($this->backofficeUser('admin'))->postJson(route('admin.marketing.preview', $post), ['photo_order' => range(0, 14)]);

        $res->assertOk();
        $this->assertLessThanOrEqual(12, substr_count($res->json('html'), 'background-image'));
    }

    public function test_refresh_nao_deixa_template_incompativel_com_novo_estado(): void
    {
        $v = $this->viatura(1);
        $post = $this->postDe($v, 'novo-stock');
        $v->update(['status' => 'vendido']);

        $this->actingAs($this->backofficeUser('admin'))->post(route('admin.marketing.refresh', $post))->assertSessionHas('error');

        $this->assertSame('em_stock', $post->fresh()->data['status']);
    }

    public function test_refresh_remapeia_ordem_e_focais_das_fotos(): void
    {
        $v = $this->viatura(3);
        $post = $this->postDe($v);
        $idB = $post->data['photos'][1]['id'];
        $post->update(['config' => ['photo_order' => [1], 'focal' => [1 => [20, 30]]]]);
        // Nova capa passa à frente: a foto B muda de posição.
        $nova = V3VehiclePhoto::create(['v3_vehicle_id' => $v->id, 'path' => 'capa.jpg', 'order_position' => 0, 'is_cover' => true]);
        V3VehiclePhoto::where('v3_vehicle_id', $v->id)->where('id', '!=', $nova->id)->update(['is_cover' => false]);

        $this->actingAs($this->backofficeUser('admin'))->post(route('admin.marketing.refresh', $post));

        $post->refresh();
        $novoIdx = array_search($idB, array_column($post->data['photos'], 'id'));
        $this->assertNotSame(1, $novoIdx);
        $this->assertSame([$novoIdx], $post->config['photo_order']);
        $this->assertSame([20, 30], $post->config['focal'][$novoIdx]);
    }

    public function test_refresh_descarta_fotos_que_ja_nao_existem(): void
    {
        $v = $this->viatura(2);
        $post = $this->postDe($v);
        $post->update(['config' => ['photo_order' => [0, 1]]]);
        $removida = $post->data['photos'][1]['id'];
        V3VehiclePhoto::find($removida)->delete();

        $this->actingAs($this->backofficeUser('admin'))->post(route('admin.marketing.refresh', $post));

        $this->assertCount(1, $post->fresh()->config['photo_order']);
    }

    public function test_titulos_longos_ficam_dentro_de_255(): void
    {
        Storage::fake('public');
        $admin = $this->backofficeUser('admin');
        $this->actingAs($admin)->post(route('admin.marketing.recommendations.store'), [
            'brand' => str_repeat('a', 255), 'model' => str_repeat('b', 255), 'template' => 'recomendacao', 'format' => 'feed_square',
        ])->assertRedirect();
        $this->assertLessThanOrEqual(255, mb_strlen(MarketingPost::firstOrFail()->title));

        $longo = MarketingPost::create(['type' => 'veiculo', 'template' => 'premium', 'format' => 'feed_square', 'status' => 'rascunho',
            'title' => str_repeat('c', 250), 'data' => [], 'config' => []]);
        $this->actingAs($admin)->post(route('admin.marketing.duplicate', $longo));
        $this->assertLessThanOrEqual(255, MarketingPost::where('id', '!=', $longo->id)->orderByDesc('id')->first()->title ? mb_strlen(MarketingPost::orderByDesc('id')->first()->title) : 0);
    }

    public function test_importacao_falhada_nao_deixa_posts_vazios(): void
    {
        RecommendationPost::create(['brand' => 'BMW', 'model' => '320d']);
        $falha = new class extends MarketingRecommendationImporter {
            protected function copiar(?string $path, int $postId): ?string
            {
                throw new \RuntimeException('disco cheio');
            }
        };
        Storage::fake('public');
        Storage::disk('public')->put('recommendation-posts/a.jpg', 'x');
        RecommendationPost::query()->update(['image' => 'recommendation-posts/a.jpg']);

        try {
            $falha->import();
            $this->fail('devia ter lançado');
        } catch (\RuntimeException) {
        }

        $this->assertSame(0, MarketingPost::count());
        $this->assertSame(1, app(MarketingRecommendationImporter::class)->import()['imported']);
    }
}
