<?php

namespace Tests\Feature;

use App\Http\Middleware\BlockSpamSubmissions;
use App\Models\NewsArticle;
use App\Models\PageContent;
use App\Models\Setting;
use App\Models\User;
use App\Models\V3Vehicle;
use App\Support\IsvTable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** SEG-006, SEG-012, SEG-017 e vazamentos públicos (rascunhos e viaturas fora do site). */
class ExposicaoPublicaCorrecoesTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        foreach (['address', 'email', 'facebook', 'insta', 'phone', 'vat_number'] as $label) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => 'x']);
        }
        DB::connection()->getPdo()->sqliteCreateFunction('FIELD', function ($value, ...$list) {
            $i = array_search($value, $list, true);

            return $i === false ? 0 : $i + 1;
        });
    }

    // ---- SEG-012: anti-spam ----

    public function test_newsletter_without_form_started_is_silently_blocked(): void
    {
        $this->postJson(route('newsletter.subscribe'), ['email' => 'sem-hora@example.com'])
            ->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseMissing('clients', ['email' => 'sem-hora@example.com']);
    }

    public function test_invalid_or_future_form_started_is_blocked(): void
    {
        foreach (['abc', '0', (string) (time() + 3600)] as $i => $valor) {
            $this->postJson(route('newsletter.subscribe'), ['email' => "x{$i}@example.com", '_form_started' => $valor])->assertOk();
            $this->assertDatabaseMissing('clients', ['email' => "x{$i}@example.com"]);
        }
    }

    public function test_same_email_is_limited_per_hour(): void
    {
        $resultados = [];
        for ($i = 0; $i < 6; $i++) {
            // Os limites do throttle por IP (5/min) não interferem: limpa-se entre pedidos.
            $this->app['cache']->forget('throttle');
            $this->postJson(route('newsletter.subscribe'), ['email' => 'mesmo@example.com', '_form_started' => time() - 10]);
            $resultados[] = \Illuminate\Support\Facades\RateLimiter::attempts('antispam:email:newsletter.subscribe:' . sha1('mesmo@example.com'));
        }

        // Depois de 5 tentativas deixa de contar (ficou bloqueado).
        $this->assertSame(5, end($resultados));
    }

    public function test_angariador_registration_requires_antispam_fields_and_does_not_reveal_existing_emails(): void
    {
        Storage::fake('local');
        Role::findOrCreate('angariador', 'web');
        $this->backofficeUser('admin', ['email' => 'existe@example.com']);

        $dados = fn (array $extra = []) => $extra + [
            'name' => 'Rita', 'last_name' => 'Costa', 'email' => 'nova@example.com', 'phone' => '912345678',
            'documento_frente' => UploadedFile::fake()->image('f.jpg'),
            'documento_verso' => UploadedFile::fake()->create('v.pdf', 100, 'application/pdf'),
        ];

        // Sem _form_started: bloqueado em silêncio.
        $this->post(route('public.angariador.register.store'), $dados())->assertSessionHas('success');
        $this->assertSame(0, User::where('email', 'nova@example.com')->count());

        // Honeypot preenchido: idem.
        $this->post(route('public.angariador.register.store'), $dados(['_form_started' => time() - 10, BlockSpamSubmissions::FIELD => 'x']))
            ->assertSessionHas('success');
        $this->assertSame(0, User::where('email', 'nova@example.com')->count());

        // Email já registado: mesma resposta de sucesso, sem erro de validação e sem novo utilizador.
        $this->post(route('public.angariador.register.store'), $dados(['_form_started' => time() - 10, 'email' => 'existe@example.com']))
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame(1, User::where('email', 'existe@example.com')->count());

        // Pedido legítimo.
        $this->post(route('public.angariador.register.store'), $dados(['_form_started' => time() - 10]))->assertSessionHasNoErrors();
        $this->assertSame(1, User::where('email', 'nova@example.com')->count());
    }

    public function test_angariador_register_form_includes_the_honeypot(): void
    {
        $this->get(route('public.angariador.register'))->assertOk()
            ->assertSee('name="company_website"', false)->assertSee('name="_form_started"', false);
    }

    // ---- Vazamentos públicos ----

    public function test_draft_news_is_404_for_the_public_and_published_opens(): void
    {
        NewsArticle::create(['title' => 'Rascunho', 'slug' => 'rascunho', 'content' => 'x', 'status' => 'Rascunho']);
        NewsArticle::create(['title' => 'Publicada', 'slug' => 'publicada', 'content' => 'x', 'status' => 'Publicado', 'published_at' => now()]);

        $this->get(route('frontend.news-details', 'rascunho'))->assertNotFound();
        $this->get(route('frontend.news-details', 'publicada'))->assertOk();
    }

    public function test_vehicle_offline_is_404_by_reference(): void
    {
        $offline = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Audi', 'model' => 'A4', 'show_online' => false]);
        $online = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Audi', 'model' => 'A4', 'show_online' => true]);

        $this->get(route('vehicles.details', ['brand' => 'audi', 'model' => 'a4', 'id' => $offline->reference]))->assertNotFound();
        $this->get(route('vehicles.details', ['brand' => 'audi', 'model' => 'a4', 'id' => $online->reference]))->assertOk();
    }

    // ---- SEG-006 restante ----

    public function test_page_content_ignores_fields_outside_the_whitelist(): void
    {
        DB::table('page_types')->insert(['id' => 1, 'name' => 'T', 'created_at' => now(), 'updated_at' => now()]);
        $page = DB::table('pages')->insertGetId(['title' => 'P', 'slug' => 'p', 'page_type_id' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $user = $this->backofficeUser('admin');

        $conteudo = PageContent::create(['page_id' => $page, 'field_name' => 'a', 'field_value' => 'b']);

        $this->actingAs($user)->putJson(route('page-contents.update', $conteudo), [
            'field_value' => 'novo', 'page_id' => 9999, 'id' => 77,
        ])->assertOk();

        $conteudo->refresh();
        $this->assertSame('novo', $conteudo->field_value);
        $this->assertSame($page, (int) $conteudo->page_id);
        $this->assertNotSame(77, $conteudo->id);

        $this->actingAs($user)->postJson(route('page-contents.store'), ['page_id' => $page])->assertStatus(422);
    }

    public function test_isv_table_is_sanitized_keeping_the_layout(): void
    {
        $html = "<table class='table table-bordered'><tr><th colspan='2' onclick='x()'>Total</th><td style='x'>1 €<script>alert(1)</script><img src=x onerror=alert(1)></td></tr></table>";

        $limpo = IsvTable::sanitize($html);

        $this->assertStringContainsString('<table class="table table-bordered">', $limpo);
        $this->assertStringContainsString('<th colspan="2">Total</th>', $limpo);
        $this->assertStringNotContainsString('onclick', $limpo);
        $this->assertStringNotContainsString('onerror', $limpo);
        $this->assertStringNotContainsString('<img', $limpo);
        $this->assertStringNotContainsString('<script', $limpo);
        $this->assertStringContainsString('1 €', $limpo);
    }

    public function test_manual_faq_renders_answers_through_rich_text(): void
    {
        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.angariador.faq'))
            ->assertOk()
            ->assertSee('<li><b>Preço da Viatura</b>', false);
    }
}
