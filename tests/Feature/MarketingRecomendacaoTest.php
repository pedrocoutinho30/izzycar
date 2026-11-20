<?php

namespace Tests\Feature;

use App\Enums\MarketingFormat;
use App\Enums\MarketingPostStatus;
use App\Enums\MarketingPostType;
use App\Marketing\RecommendationPostData;
use App\Models\MarketingPost;
use App\Models\RecommendationPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Posts de recomendação (carro de fora) no Marketing Studio e importação dos posts antigos. */
class MarketingRecomendacaoTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Storage::fake('public');
    }

    private function dados(array $extra = []): array
    {
        return $extra + [
            'brand' => 'Mercedes-Benz', 'model' => 'C 220 d', 'version' => 'AMG Line', 'year' => 2022,
            'mileage' => 35000, 'power' => 194, 'fuel' => 'Diesel', 'price' => 38900, 'savings' => 6200,
            'equipment_raw' => "Pack AMG\nTeto panorâmico\nCâmara 360\nBanco aquecido\nQuinto equipamento",
            'url' => 'https://exemplo.test/anuncio/1', 'template' => 'recomendacao', 'format' => 'feed_square',
            'image' => UploadedFile::fake()->image('principal.jpg', 1600, 1200),
            'gallery_photos' => [UploadedFile::fake()->image('g1.jpg', 1600, 1200), UploadedFile::fake()->image('g2.jpg', 1600, 1200)],
        ];
    }

    private function enviar(array $dados)
    {
        return $this->actingAs($this->backofficeUser('admin'))->post(route('admin.marketing.recommendations.store'), $dados);
    }

    public function test_pagina_de_criar_recomendacao_abre(): void
    {
        $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.recommendations.create'))->assertOk()->assertSee('Recomendação');
    }

    public function test_store_recomendacao_guarda_fotos_e_instantaneo(): void
    {
        $this->enviar($this->dados())->assertRedirect();

        $post = MarketingPost::firstOrFail();
        $this->assertSame(MarketingPostType::Recomendacao, $post->type);
        $this->assertSame(MarketingPostStatus::Rascunho, $post->status);
        $this->assertSame(MarketingFormat::FeedSquare, $post->format);
        $this->assertSame('Mercedes-Benz', $post->data['brand']);
        $this->assertSame(38900.0, (float) $post->data['price']);
        $this->assertSame(6200.0, (float) $post->data['savings']);
        $this->assertCount(3, $post->data['photos']);
        foreach ($post->data['photos'] as $f) {
            $this->assertStringStartsWith("marketing-posts/{$post->id}/", $f['path']);
            Storage::disk('public')->assertExists($f['path']);
        }
    }

    public function test_rejeita_ficheiro_que_nao_e_imagem(): void
    {
        $this->enviar($this->dados(['image' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')]))
            ->assertSessionHasErrors('image');
        $this->assertSame(0, MarketingPost::count());
    }

    public function test_rejeita_mais_de_12_fotos(): void
    {
        $fotos = array_map(fn ($i) => UploadedFile::fake()->image("g{$i}.jpg", 400, 300), range(1, 13));

        $this->enviar($this->dados(['gallery_photos' => $fotos]))->assertSessionHasErrors('gallery_photos');
    }

    public function test_equipamento_limitado_a_4_linhas(): void
    {
        $this->enviar($this->dados());

        $this->assertSame(['Pack AMG', 'Teto panorâmico', 'Câmara 360', 'Banco aquecido'], MarketingPost::first()->data['equipment']);
    }

    public function test_sem_poupanca_nao_aparece_no_instantaneo(): void
    {
        $this->enviar($this->dados(['savings' => null, 'price' => null]));

        $data = MarketingPost::firstOrFail()->data;
        $this->assertArrayNotHasKey('savings', $data);
        $this->assertArrayNotHasKey('price', $data);
    }

    public function test_marca_e_modelo_obrigatorios(): void
    {
        $this->enviar($this->dados(['brand' => '', 'model' => '']))->assertSessionHasErrors(['brand', 'model']);
    }

    public function test_foto_de_post_devolve_imagem_e_fora_do_indice_da_404(): void
    {
        $this->enviar($this->dados());
        $post = MarketingPost::firstOrFail();
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->get(route('admin.marketing.post-photos.show', [$post, 0]))->assertOk();
        $this->actingAs($admin)->get(route('admin.marketing.post-photos.show', [$post, 9]))->assertNotFound();
    }

    public function test_foto_de_post_nao_serve_caminhos_fora_da_pasta_do_studio(): void
    {
        Storage::disk('public')->put('private/segredo.jpg', 'x');
        $post = MarketingPost::create([
            'type' => 'recomendacao', 'template' => 'recomendacao', 'format' => 'feed_square', 'status' => 'rascunho',
            'data' => ['photos' => [['path' => 'private/segredo.jpg']]], 'config' => [],
        ]);

        $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.post-photos.show', [$post, 0]))->assertNotFound();
    }

    private function jpeg(): string
    {
        ob_start();
        imagejpeg(imagecreatetruecolor(800, 600));

        return ob_get_clean();
    }

    private function recomendacaoAntiga(): RecommendationPost
    {
        Storage::disk('public')->put('recommendation-posts/main.jpg', $this->jpeg());
        Storage::disk('public')->put('recommendation-posts/gallery/g1.jpg', $this->jpeg());

        return RecommendationPost::create([
            'brand' => 'BMW', 'model' => '320d', 'year' => 2021, 'price' => 29900, 'savings' => 4100,
            'equipment' => ['Pack M', 'HUD'], 'image' => 'recommendation-posts/main.jpg',
            'gallery_photos' => ['recommendation-posts/gallery/g1.jpg'], 'gallery_layouts' => ['1' => 'grid'],
            'gallery_photos_per_slide' => 3,
        ]);
    }

    public function test_importacao_copia_sem_alterar_antigos(): void
    {
        $antigo = $this->recomendacaoAntiga();
        $antes = $antigo->fresh()->toArray();

        $this->actingAs($this->backofficeUser('admin'))->post(route('admin.marketing.import-recommendations'))->assertRedirect();

        $post = MarketingPost::firstOrFail();
        $this->assertSame(MarketingPostType::Recomendacao, $post->type);
        $this->assertSame(MarketingPostStatus::Rascunho, $post->status);
        $this->assertSame('BMW', $post->data['brand']);
        $this->assertSame(29900.0, (float) $post->data['price']);
        $this->assertSame(['Pack M', 'HUD'], $post->data['equipment']);
        $this->assertSame(3, $post->config['gallery_photos_per_slide']);
        $this->assertCount(2, $post->data['photos']);
        $this->assertStringStartsWith("marketing-posts/{$post->id}/", $post->data['photos'][0]['path']);
        Storage::disk('public')->assertExists($post->data['photos'][0]['path']);
        // originais intactos
        $this->assertSame($antes, $antigo->fresh()->toArray());
        Storage::disk('public')->assertExists('recommendation-posts/main.jpg');
        Storage::disk('public')->assertExists('recommendation-posts/gallery/g1.jpg');
    }

    public function test_importacao_e_idempotente(): void
    {
        $this->recomendacaoAntiga();
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->post(route('admin.marketing.import-recommendations'));
        $this->actingAs($admin)->post(route('admin.marketing.import-recommendations'));

        $this->assertSame(1, MarketingPost::count());
    }

    public function test_angariador_nao_importa(): void
    {
        $this->recomendacaoAntiga();

        $this->actingAs($this->backofficeUser('angariador'))->post(route('admin.marketing.import-recommendations'))->assertForbidden();
        $this->assertSame(0, MarketingPost::count());
    }
}
