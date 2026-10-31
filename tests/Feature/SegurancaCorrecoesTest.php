<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\Setting;
use App\Models\User;
use App\Services\VehicleListingImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Correções da auditoria de segurança (SEG-003/004/005/007/008) e rotas públicas mortas. */
class SegurancaCorrecoesTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    // ── Rotas públicas ───────────────────────────────────────────────

    public function test_contact_and_cookies_redirect_to_existing_pages(): void
    {
        $this->get('/contactos')->assertRedirect('/formulario-importacao');
        $this->get('/cookies')->assertRedirect('/politica-privacidade');
        $this->assertSame(url('/contactos'), route('frontend.contact'));
    }

    public function test_dead_pages_are_not_found_instead_of_500(): void
    {
        foreach (['/sobre-nos', '/servicos', '/parceiros'] as $uri) {
            $this->get($uri)->assertNotFound();
        }
    }

    // ── SEG-003 ──────────────────────────────────────────────────────

    public function test_whatsapp_webhook_refuses_when_secret_is_empty(): void
    {
        config(['services.whatsapp.app_secret' => '']);
        $body = json_encode(['entry' => []]);

        $this->call('POST', '/api/whatsapp/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-Hub-Signature-256' => 'sha256=' . hash_hmac('sha256', $body, ''),
        ], $body)->assertForbidden();
    }

    public function test_whatsapp_webhook_accepts_valid_signature(): void
    {
        config(['services.whatsapp.app_secret' => 'segredo-de-teste']);
        $body = json_encode(['entry' => []]);

        $response = $this->call('POST', '/api/whatsapp/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-Hub-Signature-256' => 'sha256=' . hash_hmac('sha256', $body, 'segredo-de-teste'),
        ], $body);

        $this->assertNotSame(403, $response->getStatusCode());
    }

    // ── SEG-004 ──────────────────────────────────────────────────────

    public function test_listing_import_rejects_lookalike_hosts_and_insecure_urls(): void
    {
        $service = new VehicleListingImportService();

        foreach ([
            'https://autoscout24.evil.test/x',
            'https://www.autoscout24.pt.evil.test/x',
            'http://www.autoscout24.pt/x',
            'https://127.0.0.1/x',
            'https://evil.test/?u=www.autoscout24.pt',
        ] as $url) {
            $result = $service->import($url);
            $this->assertFalse($result['success'], $url);
            $this->assertFalse($result['supported'], $url);
        }
    }

    public function test_listing_import_host_matcher(): void
    {
        $m = new \ReflectionMethod(VehicleListingImportService::class, 'isAutoScout24Host');
        $m->setAccessible(true);
        $service = new VehicleListingImportService();

        $this->assertTrue($m->invoke($service, 'www.autoscout24.pt'));
        $this->assertTrue($m->invoke($service, 'www.autoscout24.de'));
        $this->assertTrue($m->invoke($service, 'autoscout24.com'));
        $this->assertFalse($m->invoke($service, 'autoscout24.evil.test'));
        $this->assertFalse($m->invoke($service, 'evilautoscout24.pt'));
    }

    // ── SEG-005 ──────────────────────────────────────────────────────

    public function test_push_subscription_endpoint_must_be_https_url(): void
    {
        $user = $this->backofficeUser();
        $keys = ['p256dh' => 'a', 'auth' => 'b'];

        foreach (['http://localhost/x', 'ftp://x.test/y', 'nao-e-url'] as $endpoint) {
            $this->actingAs($user)->postJson(route('admin.v2.push-subscriptions.store'), ['endpoint' => $endpoint, 'keys' => $keys])
                ->assertStatus(422)->assertJsonValidationErrors('endpoint');
        }
    }

    // ── SEG-007 ──────────────────────────────────────────────────────

    public function test_settings_image_upload_rejects_svg_and_uses_generated_name(): void
    {
        Storage::fake('public');
        $admin = $this->backofficeUser();

        $svg = UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $this->actingAs($admin)->post(route('admin.v2.settings.store'), [
            'titulo' => 'Imagem', 'label' => 'img_svg', 'tipo' => 'image', 'valor' => $svg,
        ])->assertSessionHasErrors('valor');
        $this->assertDatabaseMissing('settings', ['label' => 'img_svg']);

        $png = UploadedFile::fake()->image('../../evil.png', 20, 20);
        $this->actingAs($admin)->post(route('admin.v2.settings.store'), [
            'titulo' => 'Imagem', 'label' => 'img_ok', 'tipo' => 'image', 'valor' => $png,
        ])->assertSessionHasNoErrors();

        $value = Setting::where('label', 'img_ok')->value('value');
        $this->assertStringStartsWith('settings/', $value);
        $this->assertStringNotContainsString('img_ok', $value);
        $this->assertStringNotContainsString('evil', $value);
        Storage::disk('public')->assertExists($value);
    }

    // ── SEG-008 ──────────────────────────────────────────────────────

    public function test_filemanager_is_closed_to_angariadores_and_open_to_admin(): void
    {
        $this->actingAs($this->backofficeUser('angariador'))->get('/laravel-filemanager')->assertForbidden();
        $this->actingAs($this->backofficeUser('admin'))->get('/laravel-filemanager')->assertSuccessful();
        auth()->logout();
        $this->get('/laravel-filemanager')->assertRedirect();
    }

    // ── Galeria de notícias ──────────────────────────────────────────

    public function test_delete_gallery_image_only_removes_own_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('news/gallery/a.jpg', 'x');
        Storage::disk('public')->put('settings/logo.png', 'x');

        $article = NewsArticle::create([
            'title' => 'T', 'slug' => 't', 'content' => 'conteudo longo', 'status' => 'Rascunho',
            'gallery' => ['news/gallery/a.jpg'],
        ]);
        $admin = $this->backofficeUser();

        $this->actingAs($admin)->deleteJson(route('admin.news.gallery-image.delete', $article->id), ['path' => 'settings/logo.png'])->assertStatus(422);
        $this->actingAs($admin)->deleteJson(route('admin.news.gallery-image.delete', $article->id), ['path' => 'news/../settings/logo.png'])->assertStatus(422);
        Storage::disk('public')->assertExists('settings/logo.png');

        $this->actingAs($admin)->deleteJson(route('admin.news.gallery-image.delete', $article->id), ['path' => 'news/gallery/a.jpg'])->assertOk();
        Storage::disk('public')->assertMissing('news/gallery/a.jpg');
        $this->assertSame([], $article->fresh()->gallery);
    }

    // ── Utilizadores ─────────────────────────────────────────────────

    public function test_non_admin_cannot_edit_or_delete_administrators(): void
    {
        $manager = $this->backofficeUser('Importador');
        $admin = $this->backofficeUser('admin');

        $this->actingAs($manager)->delete(route('admin.v2.users.destroy', $admin->id))->assertForbidden();
        $this->actingAs($manager)->put(route('admin.v2.users.update', $admin->id), [
            'name' => 'X', 'last_name' => 'Y', 'email' => 'novo@x.test', 'roles' => ['admin'],
        ])->assertForbidden();
        $this->assertNotNull(User::find($admin->id));
        $this->assertNotSame('novo@x.test', $admin->fresh()->email);
    }

    public function test_admin_can_still_delete_another_user(): void
    {
        $other = $this->backofficeUser('Importador');

        $this->actingAs($this->backofficeUser('admin'))->delete(route('admin.v2.users.destroy', $other->id))->assertRedirect();
        $this->assertNull(User::find($other->id));
    }
}
