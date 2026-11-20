<?php

namespace Tests\Feature;

use App\Enums\MarketingFormat;
use App\Enums\MarketingPostStatus;
use App\Enums\MarketingPostType;
use App\Models\MarketingPost;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Criar, duplicar, atualizar dados e confirmar publicação de posts de viaturas. */
class MarketingFluxoTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function viatura(string $status = 'em_stock', ?float $preco = 30000): V3Vehicle
    {
        return V3Vehicle::create([
            'reference' => V3Vehicle::generateReference(), 'brand' => 'Audi', 'model' => 'A3',
            'status' => $status, 'asking_price' => $preco, 'year' => 2021,
        ]);
    }

    private function criar(V3Vehicle $v, string $template = 'premium', string $format = 'feed_square')
    {
        return $this->actingAs($this->backofficeUser('admin'))->postJson(route('admin.marketing.store'), [
            'type' => 'veiculo', 'vehicle_id' => $v->id, 'template' => $template, 'format' => $format,
        ]);
    }

    public function test_pagina_de_criar_abre(): void
    {
        $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.vehicles.create'))->assertOk()->assertSee('Publicação de viatura');
    }

    public function test_pesquisa_de_viaturas_devolve_json(): void
    {
        $this->viatura();
        V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Fiat', 'model' => 'Panda']);

        $res = $this->actingAs($this->backofficeUser('admin'))->getJson(route('admin.marketing.vehicles.search', ['q' => 'audi']));

        $res->assertOk()->assertJsonCount(1)->assertJsonPath('0.status', 'em_stock');
        $this->assertStringContainsString('Audi A3', $res->json('0.label'));
    }

    public function test_store_cria_post_com_instantaneo(): void
    {
        $v = $this->viatura();

        $res = $this->criar($v);

        $post = MarketingPost::firstOrFail();
        $res->assertCreated()->assertJsonPath('redirect', route('admin.marketing.editor', $post));
        $this->assertSame(MarketingPostType::Veiculo, $post->type);
        $this->assertSame(MarketingPostStatus::Rascunho, $post->status);
        $this->assertSame(MarketingFormat::FeedSquare, $post->format);
        $this->assertSame('Audi', $post->data['brand']);
        $this->assertSame(V3Vehicle::class, $post->source_type);
        $this->assertSame($v->id, $post->source_id);
        $this->assertNotNull($post->created_by);
    }

    public function test_rejeita_vendido_para_viatura_em_stock(): void
    {
        $this->criar($this->viatura('em_stock'), 'vendido')->assertStatus(422);
        $this->assertSame(0, MarketingPost::count());
    }

    public function test_rejeita_novo_stock_para_viatura_vendida(): void
    {
        $this->criar($this->viatura('vendido'), 'novo-stock')->assertStatus(422);
    }

    public function test_rejeita_formato_nao_suportado_pelo_template(): void
    {
        $this->criar($this->viatura('vendido'), 'vendido', 'story')->assertStatus(422);
    }

    public function test_alterar_viatura_nao_muda_post_antigo_e_refresh_so_quando_pedido(): void
    {
        $v = $this->viatura('em_stock', 30000);
        $this->criar($v);
        $post = MarketingPost::firstOrFail();

        $v->update(['asking_price' => 25000]);
        $this->assertSame(30000.0, (float) $post->fresh()->data['price']);

        $this->actingAs($this->backofficeUser('admin'))->post(route('admin.marketing.refresh', $post))->assertRedirect();
        $this->assertSame(25000.0, (float) $post->fresh()->data['price']);
    }

    public function test_refresh_preserva_edicoes_do_utilizador(): void
    {
        $v = $this->viatura();
        $this->criar($v);
        $post = MarketingPost::firstOrFail();
        $post->update(['title' => 'O meu título', 'caption' => 'Legenda minha']);

        $this->actingAs($this->backofficeUser('admin'))->post(route('admin.marketing.refresh', $post));

        $this->assertSame('O meu título', $post->fresh()->title);
        $this->assertSame('Legenda minha', $post->fresh()->caption);
    }

    public function test_duplicate_volta_a_rascunho(): void
    {
        $this->criar($this->viatura());
        $post = MarketingPost::firstOrFail();
        $post->update(['title' => 'Original', 'status' => MarketingPostStatus::Aprovado, 'published_at' => now()]);

        $this->actingAs($this->backofficeUser('admin'))->post(route('admin.marketing.duplicate', $post))->assertRedirect();

        $copia = MarketingPost::where('id', '!=', $post->id)->firstOrFail();
        $this->assertSame(MarketingPostStatus::Rascunho, $copia->status);
        $this->assertSame('Original (cópia)', $copia->title);
        $this->assertNull($copia->published_at);
        $this->assertSame($post->data, $copia->data);
    }

    public function test_publicado_so_por_acao_explicita(): void
    {
        $this->criar($this->viatura());
        $post = MarketingPost::firstOrFail();
        $this->assertNull($post->published_at);

        $this->actingAs($this->backofficeUser('admin'))->post(route('admin.marketing.mark-published', $post))->assertRedirect();

        $post->refresh();
        $this->assertSame(MarketingPostStatus::Publicado, $post->status);
        $this->assertNotNull($post->published_at);
    }

    public function test_destroy_remove_post_e_slides(): void
    {
        $this->criar($this->viatura());
        $post = MarketingPost::firstOrFail();
        $post->slides()->create(['position' => 1, 'content' => []]);

        $this->actingAs($this->backofficeUser('admin'))->delete(route('admin.marketing.destroy', $post))->assertRedirect(route('admin.marketing.index'));

        $this->assertSame(0, MarketingPost::count());
        $this->assertDatabaseCount('marketing_post_slides', 0);
    }

    public function test_destroy_apaga_as_fotos_carregadas_do_post(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->criar($this->viatura());
        $post = MarketingPost::firstOrFail();
        \Illuminate\Support\Facades\Storage::disk('public')->put("marketing-posts/{$post->id}/a.jpg", 'x');
        \Illuminate\Support\Facades\Storage::disk('public')->put('v3-vehicles/1/photos/b.jpg', 'y');

        $this->actingAs($this->backofficeUser('admin'))->delete(route('admin.marketing.destroy', $post));

        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing("marketing-posts/{$post->id}/a.jpg");
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists('v3-vehicles/1/photos/b.jpg');
    }
}
