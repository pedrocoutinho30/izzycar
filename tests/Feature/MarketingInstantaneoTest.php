<?php

namespace Tests\Feature;

use App\Enums\MarketingFormat;
use App\Enums\MarketingPostStatus;
use App\Enums\MarketingPostType;
use App\Models\MarketingPost;
use App\Models\MarketingPostSlide;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Modelo de dados do Marketing Studio: instantâneo em JSON e enums. */
class MarketingInstantaneoTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_post_guarda_instantaneo_e_casts(): void
    {
        $post = MarketingPost::create([
            'type' => MarketingPostType::Veiculo,
            'template' => 'premium',
            'format' => MarketingFormat::FeedSquare,
            'status' => MarketingPostStatus::Rascunho,
            'title' => 'Teste',
            'data' => ['brand' => 'BMW'],
            'config' => [],
        ])->fresh();

        $this->assertSame('BMW', $post->data['brand']);
        $this->assertSame(MarketingFormat::FeedSquare, $post->format);
        $this->assertSame(MarketingPostType::Veiculo, $post->type);
        $this->assertSame(1920, MarketingFormat::Story->height());
        $this->assertSame(1080, MarketingFormat::FeedVertical->width());
        $this->assertSame(1350, MarketingFormat::FeedVertical->height());
    }

    public function test_slides_ficam_ordenados_por_posicao(): void
    {
        $post = MarketingPost::create([
            'type' => MarketingPostType::Recomendacao, 'template' => 'carrossel',
            'format' => MarketingFormat::FeedVertical, 'status' => MarketingPostStatus::Rascunho,
            'title' => 'X', 'data' => [], 'config' => [],
        ]);
        MarketingPostSlide::create(['post_id' => $post->id, 'position' => 2, 'content' => ['t' => 'b']]);
        MarketingPostSlide::create(['post_id' => $post->id, 'position' => 1, 'content' => ['t' => 'a']]);

        $this->assertSame(['a', 'b'], $post->slides->map(fn ($s) => $s->content['t'])->all());
    }
}
