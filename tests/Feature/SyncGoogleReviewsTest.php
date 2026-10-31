<?php

namespace Tests\Feature;

use App\Console\Commands\SyncGoogleReviews;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Log;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** LOG-014: falhas do comando de reviews ficam no log. */
class SyncGoogleReviewsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_sem_credenciais_falha_e_regista(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null, 'services.google.refresh_token' => null]);
        Log::spy();

        $this->artisan('google:sync-reviews')->assertFailed();

        Log::shouldHaveReceived('warning')->withArgs(fn ($msg) => str_contains($msg, 'Google Reviews') && str_contains($msg, 'credenciais'))->once();
    }

    public function test_guardar_reviews_nao_duplica(): void
    {
        $command = new SyncGoogleReviews;
        $save = new \ReflectionMethod($command, 'saveReviews');
        $save->setAccessible(true);
        $reviews = [
            ['reviewId' => 'r1', 'starRating' => 'FIVE', 'comment' => 'Ótimo', 'reviewer' => ['displayName' => 'Ana'], 'createTime' => '2026-05-01T10:00:00Z'],
            ['reviewId' => 'r2', 'starRating' => 'FOUR'],
            ['semId' => true],
        ];

        $this->assertSame([2, 0], $save->invoke($command, $reviews));
        $this->assertSame([0, 2], $save->invoke($command, $reviews));
        $this->assertSame(2, Testimonial::where('origin', 'google')->count());
        $this->assertSame(5.0, (float) Testimonial::where('google_review_id', 'r1')->value('rating'));
    }
}
