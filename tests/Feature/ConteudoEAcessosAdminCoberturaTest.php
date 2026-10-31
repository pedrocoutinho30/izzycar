<?php

namespace Tests\Feature;

use App\Models\CarCandidate;
use App\Models\Client;
use App\Models\NewsArticle;
use App\Models\RecommendationPost;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Carros candidatos, push subscriptions, impersonation, testemunhos, notícias (admin) e posts sociais.
 */
class ConteudoEAcessosAdminCoberturaTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Storage::fake('public');
    }

    private function admin()
    {
        return $this->actingAs($this->backofficeUser('admin'));
    }

    private function client(string $name = 'Cliente Teste'): Client
    {
        return Client::create(['name' => $name, 'is_lead' => true, 'lead_status' => 'nova']);
    }

    // ── Carros candidatos ────────────────────────────────────────────────────

    public function test_car_candidates_require_login_and_block_angariador(): void
    {
        $client = $this->client();
        $candidate = CarCandidate::create(['client_id' => $client->id, 'title' => 'Golf', 'status' => 'em_conversacao']);

        $this->post(route('admin.v2.car-candidates.store', $client), ['title' => 'X', 'status' => 'descartado'])->assertRedirect(route('login'));
        $this->delete(route('admin.v2.car-candidates.destroy', [$client, $candidate]))->assertRedirect(route('login'));

        $angariador = $this->backofficeUser('angariador');
        $this->actingAs($angariador)->post(route('admin.v2.car-candidates.store', $client), ['title' => 'X', 'status' => 'descartado'])->assertForbidden();
        $this->actingAs($angariador)->delete(route('admin.v2.car-candidates.destroy', [$client, $candidate]))->assertForbidden();

        $this->assertSame(1, CarCandidate::count());
    }

    public function test_car_candidate_store_appends_at_the_end_and_validates(): void
    {
        $client = $this->client();

        $this->admin()->post(route('admin.v2.car-candidates.store', $client), ['title' => 'Golf', 'status' => 'primeiro_contacto', 'price' => 12000.5, 'link' => 'https://exemplo.test/anuncio/1'])
            ->assertRedirect()->assertSessionHas('success');
        $this->admin()->post(route('admin.v2.car-candidates.store', $client), ['title' => 'Polo', 'status' => 'sem_resposta']);

        $this->assertSame([0, 1], $client->carCandidates()->orderBy('order_position')->pluck('order_position')->all());
        $this->assertEquals(12000.5, CarCandidate::where('title', 'Golf')->value('price'));

        $this->admin()->post(route('admin.v2.car-candidates.store', $client), ['title' => '', 'status' => 'inventado', 'price' => -1, 'link' => 'nao-e-url'])
            ->assertSessionHasErrors(['title', 'status', 'price', 'link']);
        $this->assertSame(2, CarCandidate::count());
    }

    public function test_car_candidate_update_and_destroy_are_scoped_to_the_client(): void
    {
        $client = $this->client();
        $outro = $this->client('Outro');
        $candidate = CarCandidate::create(['client_id' => $client->id, 'title' => 'Golf', 'status' => 'primeiro_contacto']);

        $this->admin()->put(route('admin.v2.car-candidates.update', [$client, $candidate]), ['title' => 'Golf GTI', 'status' => 'descartado'])->assertRedirect();
        $this->assertSame('Golf GTI', $candidate->fresh()->title);
        $this->assertSame('descartado', $candidate->fresh()->status);

        // Com o cliente errado na URL: 404 e nada muda.
        $this->admin()->put(route('admin.v2.car-candidates.update', [$outro, $candidate]), ['title' => 'Hack', 'status' => 'descartado'])->assertNotFound();
        $this->admin()->delete(route('admin.v2.car-candidates.destroy', [$outro, $candidate]))->assertNotFound();
        $this->assertSame('Golf GTI', $candidate->fresh()->title);

        $this->admin()->delete(route('admin.v2.car-candidates.destroy', [$client, $candidate]))->assertRedirect()->assertSessionHas('success');
        $this->assertSame(0, CarCandidate::count());
    }

    public function test_car_candidates_reorder_only_touches_own_candidates(): void
    {
        $client = $this->client();
        $outro = $this->client('Outro');
        $a = CarCandidate::create(['client_id' => $client->id, 'title' => 'A', 'status' => 'descartado', 'order_position' => 0]);
        $b = CarCandidate::create(['client_id' => $client->id, 'title' => 'B', 'status' => 'descartado', 'order_position' => 1]);
        $alheio = CarCandidate::create(['client_id' => $outro->id, 'title' => 'C', 'status' => 'descartado', 'order_position' => 9]);

        $this->admin()->postJson(route('admin.v2.car-candidates.reorder', $client), ['order' => [$b->id, $a->id, $alheio->id]])
            ->assertOk()->assertJson(['success' => true]);

        $this->assertSame(0, $b->fresh()->order_position);
        $this->assertSame(1, $a->fresh()->order_position);
        $this->assertSame(9, $alheio->fresh()->order_position);

        $this->admin()->postJson(route('admin.v2.car-candidates.reorder', $client), [])->assertStatus(422);
    }

    // ── Push subscriptions ───────────────────────────────────────────────────

    public function test_push_subscription_store_and_destroy(): void
    {
        $user = $this->backofficeUser('admin');
        $payload = ['endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123', 'keys' => ['p256dh' => 'chave-publica', 'auth' => 'segredo']];

        $this->actingAs($user)->postJson(route('admin.v2.push-subscriptions.store'), $payload)->assertOk()->assertJson(['success' => true]);
        $this->assertSame(1, $user->pushSubscriptions()->count());

        // Repetir o mesmo endpoint atualiza em vez de duplicar.
        $this->actingAs($user)->postJson(route('admin.v2.push-subscriptions.store'), $payload)->assertOk();
        $this->assertSame(1, $user->pushSubscriptions()->count());

        $this->actingAs($user)->deleteJson(route('admin.v2.push-subscriptions.destroy'), ['endpoint' => $payload['endpoint']])->assertOk();
        $this->assertSame(0, $user->pushSubscriptions()->count());
    }

    public function test_push_subscription_validates_payload_and_rejects_non_https_endpoint(): void
    {
        $user = $this->backofficeUser('admin');

        $this->actingAs($user)->postJson(route('admin.v2.push-subscriptions.store'), [])->assertStatus(422)->assertJsonValidationErrors(['endpoint', 'keys.p256dh', 'keys.auth']);
        $this->actingAs($user)->postJson(route('admin.v2.push-subscriptions.store'), [
            'endpoint' => 'http://localhost/interno', 'keys' => ['p256dh' => 'a', 'auth' => 'b'],
        ])->assertStatus(422)->assertJsonValidationErrors('endpoint');
        $this->actingAs($user)->deleteJson(route('admin.v2.push-subscriptions.destroy'), [])->assertStatus(422);

        $this->assertSame(0, $user->pushSubscriptions()->count());
    }

    public function test_push_subscription_requires_login_and_does_not_remove_other_users_subscription(): void
    {
        $this->postJson(route('admin.v2.push-subscriptions.store'), [])->assertUnauthorized();
        $this->deleteJson(route('admin.v2.push-subscriptions.destroy'), ['endpoint' => 'https://x.test'])->assertUnauthorized();

        $dono = $this->backofficeUser('admin');
        $intruso = $this->backofficeUser('admin');
        $endpoint = 'https://fcm.googleapis.com/fcm/send/dono';
        $dono->updatePushSubscription($endpoint, 'k', 'a');

        $this->actingAs($intruso)->deleteJson(route('admin.v2.push-subscriptions.destroy'), ['endpoint' => $endpoint])->assertOk();

        $this->assertSame(1, $dono->pushSubscriptions()->count());
    }

    // ── Impersonation ────────────────────────────────────────────────────────

    private function angariador(): User
    {
        return $this->backofficeUser('angariador', ['status' => 'aprovado']);
    }

    public function test_admin_can_impersonate_an_angariador_and_stop(): void
    {
        $admin = $this->backofficeUser('admin');
        $angariador = $this->angariador();

        $this->actingAs($admin)->post(route('admin.v2.angariadores.impersonate', $angariador))
            ->assertRedirect(route('admin.angariador.dashboard'))
            ->assertSessionHas('impersonator_id', $admin->id);
        $this->assertAuthenticatedAs($angariador);

        // Como angariador (puro), a área de admin fica bloqueada; a de angariador abre.
        $this->get(route('admin.v2.suppliers.index'))->assertForbidden();

        $this->post(route('admin.angariador.stop-impersonating'))
            ->assertRedirect(route('admin.v2.angariadores.index'));
        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session('impersonator_id'));
    }

    public function test_impersonation_target_must_be_an_angariador(): void
    {
        $admin = $this->backofficeUser('admin');
        $outroAdmin = $this->backofficeUser('admin');

        $this->actingAs($admin)->post(route('admin.v2.angariadores.impersonate', $outroAdmin))->assertNotFound();
        $this->assertAuthenticatedAs($admin);
    }

    public function test_stop_impersonating_without_impersonation_is_forbidden(): void
    {
        $this->actingAs($this->angariador())->post(route('admin.angariador.stop-impersonating'))->assertForbidden();
        $this->actingAs($this->backofficeUser('admin'))->post(route('admin.angariador.stop-impersonating'))->assertForbidden();
    }

    public function test_guest_and_angariador_cannot_start_impersonation(): void
    {
        $alvo = $this->angariador();

        $this->post(route('admin.v2.angariadores.impersonate', $alvo))->assertRedirect(route('login'));
        $this->post(route('admin.angariador.stop-impersonating'))->assertRedirect(route('login'));

        $outro = $this->angariador();
        $this->actingAs($outro)->post(route('admin.v2.angariadores.impersonate', $alvo))->assertForbidden();
        $this->assertAuthenticatedAs($outro);
    }

    public function test_only_admin_can_impersonate_when_permissions_are_enforced(): void
    {
        config(['permissions.enforce' => true]);
        $alvo = $this->angariador();
        $cms = $this->backofficeUser('cms');

        $this->actingAs($cms)->post(route('admin.v2.angariadores.impersonate', $alvo))->assertForbidden();
        $this->assertAuthenticatedAs($cms);
    }

    /**
     * BUG conhecido (config/permissions.php: PERMISSIONS_ENFORCE desligado por omissão, SEG-001 em
     * docs/auditoria/problemas.md): com a imposição desligada, qualquer perfil não-angariador (aqui "cms")
     * consegue iniciar impersonation, apesar de a rota estar em admin_only. O correto seria 403.
     */
    public function test_non_admin_can_impersonate_when_enforcement_is_off_known_bug(): void
    {
        config(['permissions.enforce' => false]);
        $alvo = $this->angariador();
        $cms = $this->backofficeUser('cms');

        $this->actingAs($cms)->post(route('admin.v2.angariadores.impersonate', $alvo))->assertRedirect(route('admin.angariador.dashboard'));
        $this->assertAuthenticatedAs($alvo);
    }

    // ── Testemunhos ──────────────────────────────────────────────────────────

    public function test_testimonials_require_login_and_block_angariador(): void
    {
        $t = Testimonial::create(['name' => 'Ana', 'rating' => 5, 'origin' => 'google', 'published' => true, 'comment' => 'ok']);

        $this->get(route('admin.testimonials.index'))->assertRedirect(route('login'));
        $this->delete(route('admin.testimonials.destroy', $t))->assertRedirect(route('login'));

        $angariador = $this->backofficeUser('angariador');
        $this->actingAs($angariador)->get(route('admin.testimonials.index'))->assertForbidden();
        $this->actingAs($angariador)->delete(route('admin.testimonials.destroy', $t))->assertForbidden();
        $this->actingAs($angariador)->post(route('admin.testimonials.toggle', $t))->assertForbidden();

        $this->assertTrue($t->fresh()->published);
    }

    public function test_testimonial_crud_toggle_and_stats(): void
    {
        $this->admin()->get(route('admin.testimonials.create'))->assertOk();

        $this->admin()->post(route('admin.testimonials.store'), ['name' => 'Ana Silva', 'rating' => 4.5, 'origin' => 'google', 'published' => '1', 'comment' => 'Excelente serviço'])
            ->assertRedirect(route('admin.testimonials.index'))->assertSessionHas('success');
        $this->admin()->post(route('admin.testimonials.store'), ['name' => 'Rui', 'rating' => 3, 'origin' => 'email', 'comment' => 'Bom']);

        $ana = Testimonial::where('name', 'Ana Silva')->firstOrFail();
        $rui = Testimonial::where('name', 'Rui')->firstOrFail();
        $this->assertTrue($ana->published);
        $this->assertFalse($rui->published);

        $response = $this->admin()->get(route('admin.testimonials.index'))->assertOk()->assertSee('Ana Silva')->assertSee('Rui');
        $stats = $response->viewData('stats');
        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['publicados']);
        $this->assertSame(1, $stats['google']);
        $this->assertEquals(3.8, $stats['media']);

        $this->admin()->get(route('admin.testimonials.edit', $ana))->assertOk()->assertSee('Ana Silva');
        $this->admin()->put(route('admin.testimonials.update', $ana), ['name' => 'Ana S.', 'rating' => 5, 'origin' => 'facebook', 'comment' => 'ok'])->assertRedirect(route('admin.testimonials.index'));
        $ana->refresh();
        $this->assertSame('Ana S.', $ana->name);
        $this->assertFalse($ana->published); // sem o checkbox, passa a não publicado

        $this->admin()->postJson(route('admin.testimonials.toggle', $rui))->assertOk()->assertJson(['published' => true]);
        $this->assertTrue($rui->fresh()->published);

        $this->admin()->delete(route('admin.testimonials.destroy', $rui))->assertRedirect(route('admin.testimonials.index'));
        $this->assertNull(Testimonial::find($rui->id));
    }

    public function test_testimonial_validation_boundaries(): void
    {
        $url = route('admin.testimonials.store');
        $comment = ['comment' => 'ok'];

        $this->admin()->post($url, [])->assertSessionHasErrors(['name', 'rating', 'origin']);
        $this->admin()->post($url, ['name' => 'X', 'rating' => 5.5, 'origin' => 'google'] + $comment)->assertSessionHasErrors('rating');
        $this->admin()->post($url, ['name' => 'X', 'rating' => 3.3, 'origin' => 'google'] + $comment)->assertSessionHasErrors('rating');
        $this->admin()->post($url, ['name' => 'X', 'rating' => 3, 'origin' => 'tiktok'] + $comment)->assertSessionHasErrors('origin');
        $this->admin()->post($url, ['name' => 'X', 'rating' => 0, 'origin' => 'outro'] + $comment)->assertSessionHasNoErrors();

        $this->assertSame(1, Testimonial::count());
    }

    /**
     * BUG conhecido (TestimonialController::store/update, ~linhas 33 e 57; migration
     * 2026_04_28_090528_create_testimonials_table): `comment` é validado como nullable (e o textarea do
     * formulário não é obrigatório), mas `testimonials.comment` é TEXT NOT NULL. Um testemunho só com
     * estrelas dá 500. O correto seria aceitar (tornar a coluna nullable) ou exigir o comentário (422).
     */
    public function test_testimonial_without_comment_fails_with_500_known_bug(): void
    {
        $this->admin()->post(route('admin.testimonials.store'), ['name' => 'Sem comentario', 'rating' => 5, 'origin' => 'google'])->assertStatus(500);

        $this->assertSame(0, Testimonial::count());
    }

    // ── Notícias (admin) ─────────────────────────────────────────────────────

    private function news(array $over = []): NewsArticle
    {
        return NewsArticle::create($over + [
            'title' => 'Guia ISV', 'slug' => 'guia-isv-' . uniqid(), 'content' => 'Conteúdo da notícia com mais de dez caracteres.', 'status' => 'Publicado', 'published_at' => now(),
        ]);
    }

    public function test_news_admin_requires_login_and_blocks_angariador(): void
    {
        $article = $this->news();

        $this->get(route('admin.news.index'))->assertRedirect(route('login'));
        $this->delete(route('admin.news.destroy', $article->id))->assertRedirect(route('login'));

        $angariador = $this->backofficeUser('angariador');
        $this->actingAs($angariador)->get(route('admin.news.index'))->assertForbidden();
        $this->actingAs($angariador)->post(route('admin.news.store'), [])->assertForbidden();
        $this->actingAs($angariador)->delete(route('admin.news.destroy', $article->id))->assertForbidden();

        $this->assertNotNull(NewsArticle::find($article->id));
    }

    public function test_news_index_filters_and_counts(): void
    {
        $this->news(['title' => 'Publicada sobre ISV']);
        $this->news(['title' => 'Rascunho sobre seguros', 'status' => 'Rascunho']);

        $response = $this->admin()->get(route('admin.news.index'))->assertOk()->assertSee('Publicada sobre ISV')->assertSee('Rascunho sobre seguros');
        $this->assertSame(2, $response->viewData('total'));
        $this->assertSame(1, $response->viewData('published'));
        $this->assertSame(1, $response->viewData('drafts'));

        $this->admin()->get(route('admin.news.index', ['status' => 'Rascunho']))->assertOk()->assertSee('Rascunho sobre seguros')->assertDontSee('Publicada sobre ISV');
        $this->admin()->get(route('admin.news.index', ['search' => 'ISV']))->assertOk()->assertSee('Publicada sobre ISV')->assertDontSee('Rascunho sobre seguros');
    }

    public function test_news_store_generates_unique_slug_and_stores_images(): void
    {
        $dados = fn (array $extra = []) => $extra + ['title' => 'Novo Artigo', 'content' => 'Conteúdo suficientemente longo.', 'status' => 'Rascunho'];

        $this->admin()->get(route('admin.news.create'))->assertOk();
        $this->admin()->post(route('admin.news.store'), $dados([
            'cover_image' => UploadedFile::fake()->image('capa.jpg'), 'gallery' => [UploadedFile::fake()->image('g1.jpg')],
        ]))->assertRedirect();
        $this->admin()->post(route('admin.news.store'), $dados());

        $primeiro = NewsArticle::orderBy('id')->first();
        $segundo = NewsArticle::orderByDesc('id')->first();
        $this->assertSame('novo-artigo', $primeiro->slug);
        $this->assertNotSame($primeiro->slug, $segundo->slug);
        Storage::disk('public')->assertExists($primeiro->cover_image);
        $this->assertCount(1, $primeiro->gallery);
        Storage::disk('public')->assertExists($primeiro->gallery[0]);
    }

    public function test_news_store_validation(): void
    {
        $this->admin()->post(route('admin.news.store'), ['title' => '', 'content' => 'curto', 'status' => 'Arquivado'])
            ->assertSessionHasErrors(['title', 'content', 'status']);
        $this->admin()->post(route('admin.news.store'), ['title' => 'T', 'content' => 'Conteúdo suficientemente longo.', 'status' => 'Rascunho', 'cover_image' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('cover_image');

        $this->assertSame(0, NewsArticle::count());
    }

    public function test_news_update_replaces_cover_and_removes_it_on_request(): void
    {
        $this->admin()->post(route('admin.news.store'), ['title' => 'Artigo', 'content' => 'Conteúdo suficientemente longo.', 'status' => 'Publicado', 'cover_image' => UploadedFile::fake()->image('a.jpg')]);
        $article = NewsArticle::firstOrFail();
        $capaAntiga = $article->cover_image;

        $base = ['title' => 'Artigo editado', 'content' => 'Conteúdo suficientemente longo.', 'status' => 'Publicado'];
        $this->admin()->put(route('admin.news.update', $article->id), $base + ['cover_image' => UploadedFile::fake()->image('b.jpg')])->assertRedirect(route('admin.news.edit', $article->id));
        $article->refresh();
        $this->assertSame('Artigo editado', $article->title);
        $this->assertNotSame($capaAntiga, $article->cover_image);
        Storage::disk('public')->assertMissing($capaAntiga);

        $capa = $article->cover_image;
        $this->admin()->put(route('admin.news.update', $article->id), $base + ['remove_cover' => '1']);
        $this->assertNull($article->fresh()->cover_image);
        Storage::disk('public')->assertMissing($capa);

        // Sem alterar o slug mantém o atual; alterar o slug gera outro único.
        $slug = $article->slug;
        $this->admin()->put(route('admin.news.update', $article->id), $base + ['slug' => 'slug-novo'])->assertRedirect();
        $this->assertSame('slug-novo', $article->fresh()->slug);
        $this->assertNotSame($slug, $article->fresh()->slug);
    }

    public function test_news_toggle_status_and_destroy_removes_files(): void
    {
        $this->admin()->post(route('admin.news.store'), [
            'title' => 'Artigo', 'content' => 'Conteúdo suficientemente longo.', 'status' => 'Publicado',
            'cover_image' => UploadedFile::fake()->image('a.jpg'), 'gallery' => [UploadedFile::fake()->image('g.jpg')],
        ]);
        $article = NewsArticle::firstOrFail();

        $this->admin()->patchJson(route('admin.news.toggle-status', $article->id))->assertOk()->assertJson(['status' => 'Rascunho']);
        $this->admin()->patchJson(route('admin.news.toggle-status', $article->id))->assertOk()->assertJson(['status' => 'Publicado']);

        $this->admin()->delete(route('admin.news.destroy', $article->id))->assertRedirect(route('admin.news.index'));
        $this->assertNull(NewsArticle::find($article->id));
        Storage::disk('public')->assertMissing($article->cover_image);
        Storage::disk('public')->assertMissing($article->gallery[0]);
    }

    public function test_news_gallery_image_delete_only_accepts_own_gallery_paths(): void
    {
        $this->admin()->post(route('admin.news.store'), [
            'title' => 'Artigo', 'content' => 'Conteúdo suficientemente longo.', 'status' => 'Publicado', 'gallery' => [UploadedFile::fake()->image('g.jpg')],
        ]);
        $article = NewsArticle::firstOrFail();
        $propria = $article->gallery[0];
        Storage::disk('public')->put('news/outra.jpg', 'x');

        $this->admin()->deleteJson(route('admin.news.gallery-image.delete', $article->id), ['path' => 'news/outra.jpg'])->assertStatus(422);
        $this->admin()->deleteJson(route('admin.news.gallery-image.delete', $article->id), ['path' => '../.env'])->assertStatus(422);
        Storage::disk('public')->assertExists('news/outra.jpg');

        $this->admin()->deleteJson(route('admin.news.gallery-image.delete', $article->id), ['path' => $propria])->assertOk()->assertJson(['ok' => true]);
        Storage::disk('public')->assertMissing($propria);
        $this->assertSame([], $article->fresh()->gallery);
    }

    // ── Posts sociais ────────────────────────────────────────────────────────

    public function test_social_posts_require_login_and_block_angariador(): void
    {
        $post = RecommendationPost::create(['brand' => 'BMW', 'model' => 'i4']);

        $this->get(route('admin.v2.social-posts.index'))->assertRedirect(route('login'));
        $this->post(route('admin.v2.social-posts.store'), [])->assertRedirect(route('login'));
        $this->delete(route('admin.v2.social-posts.destroy', $post))->assertRedirect(route('login'));

        $angariador = $this->backofficeUser('angariador');
        $this->actingAs($angariador)->get(route('admin.v2.social-posts.index'))->assertForbidden();
        $this->actingAs($angariador)->delete(route('admin.v2.social-posts.destroy', $post))->assertForbidden();

        $this->assertNotNull(RecommendationPost::find($post->id));
    }

    public function test_social_post_create_edit_and_list(): void
    {
        $this->admin()->get(route('admin.v2.social-posts.recommendation'))->assertOk();

        $this->admin()->post(route('admin.v2.social-posts.store'), [
            'brand' => 'Audi', 'model' => 'A3', 'price' => 19900, 'equipment_raw' => "Navegação\n\nJantes 18\nTeto\nCâmara\nQuinto item",
            'image' => UploadedFile::fake()->image('carro.jpg'),
        ])->assertRedirect(route('admin.v2.social-posts.index'))->assertSessionHas('success', 'Post guardado com sucesso!');

        $post = RecommendationPost::sole();
        $this->assertSame(['Navegação', 'Jantes 18', 'Teto', 'Câmara'], $post->equipment); // linhas vazias ignoradas, máximo 4
        Storage::disk('public')->assertExists($post->image);

        $this->admin()->get(route('admin.v2.social-posts.index'))->assertOk()->assertSee('Audi');
        $this->admin()->get(route('admin.v2.social-posts.edit', $post))->assertOk();

        $this->admin()->post(route('admin.v2.social-posts.store'), ['id' => $post->id, 'brand' => 'Audi', 'model' => 'A4'])
            ->assertSessionHas('success', 'Post atualizado com sucesso!');
        $this->assertSame(1, RecommendationPost::count());
        $this->assertSame('A4', $post->fresh()->model);
        $this->assertSame($post->image, $post->fresh()->image); // sem nova imagem mantém a atual
    }

    public function test_social_post_validation_and_foreign_gallery_paths_are_ignored(): void
    {
        $this->admin()->post(route('admin.v2.social-posts.store'), ['brand' => '', 'url' => 'x', 'year' => 1900])
            ->assertSessionHasErrors(['brand', 'model', 'url', 'year']);
        $this->assertSame(0, RecommendationPost::count());

        Storage::disk('public')->put('settings/logo.png', 'x');
        $post = RecommendationPost::create(['brand' => 'BMW', 'model' => 'i4']);
        $this->admin()->post(route('admin.v2.social-posts.store'), [
            'id' => $post->id, 'brand' => 'BMW', 'model' => 'i4',
            'gallery_order' => json_encode(['existing:settings/logo.png']),
            'remove_gallery_photos' => json_encode(['settings/logo.png']),
        ])->assertRedirect();

        // Caminhos fora de recommendation-posts/ (ou que não são do post) nunca são associados nem apagados.
        $this->assertSame([], $post->fresh()->gallery_photos);
        Storage::disk('public')->assertExists('settings/logo.png');
    }

    public function test_social_post_destroy_removes_own_files_only(): void
    {
        Storage::disk('public')->put('recommendation-posts/a.jpg', 'x');
        Storage::disk('public')->put('recommendation-posts/gallery/b.jpg', 'x');
        Storage::disk('public')->put('settings/nao-tocar.png', 'x');
        $post = RecommendationPost::create([
            'brand' => 'BMW', 'model' => 'i4', 'image' => 'recommendation-posts/a.jpg',
            'gallery_photos' => ['recommendation-posts/gallery/b.jpg', 'settings/nao-tocar.png'],
        ]);

        $this->admin()->delete(route('admin.v2.social-posts.destroy', $post))->assertRedirect(route('admin.v2.social-posts.index'));

        $this->assertNull(RecommendationPost::find($post->id));
        Storage::disk('public')->assertMissing('recommendation-posts/a.jpg');
        Storage::disk('public')->assertMissing('recommendation-posts/gallery/b.jpg');
        Storage::disk('public')->assertExists('settings/nao-tocar.png');
    }
}
