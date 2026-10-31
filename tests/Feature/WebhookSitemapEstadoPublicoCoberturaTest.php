<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Legalization;
use App\Models\LegalizationDocument;
use App\Models\NewsArticle;
use App\Models\PreLead;
use App\Models\Setting;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Webhook do WhatsApp, comando sitemap:generate e páginas públicas de estado de legalização.
 */
class WebhookSitemapEstadoPublicoCoberturaTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private string $publicDir;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Storage::fake('local');

        foreach (['address', 'email', 'facebook', 'insta', 'phone', 'vat_number'] as $label) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => 'x']);
        }

        DB::connection()->getPdo()->sqliteCreateFunction('FIELD', function ($value, ...$list) {
            $i = array_search($value, $list, true);

            return $i === false ? 0 : $i + 1;
        });

        // O comando grava em public_path('sitemap.xml'): desviar para uma pasta temporária
        // para nunca tocar no sitemap real do repositório.
        $this->publicDir = sys_get_temp_dir() . '/izzycar-sitemap-' . uniqid();
        mkdir($this->publicDir);
    }

    protected function tearDown(): void
    {
        @unlink($this->publicDir . '/sitemap.xml');
        @rmdir($this->publicDir);

        parent::tearDown();
    }

    // ── Webhook WhatsApp ─────────────────────────────────────────────────────

    private function signed(array $payload, string $secret = 'segredo-teste'): array
    {
        $body = json_encode($payload);

        return [$body, ['HTTP_X-Hub-Signature-256' => 'sha256=' . hash_hmac('sha256', $body, $secret), 'CONTENT_TYPE' => 'application/json']];
    }

    private function textMessage(string $from = '351912345678', string $text = 'Olá, quero importar um carro', ?string $name = 'João Silva'): array
    {
        return ['entry' => [['changes' => [['value' => [
            'contacts' => [['wa_id' => $from, 'profile' => ['name' => $name]]],
            'messages' => [['from' => $from, 'id' => 'wamid.1', 'type' => 'text', 'text' => ['body' => $text]]],
        ]]]]]];
    }

    private function hook(array $payload, ?string $secret = 'segredo-teste', ?array $server = null)
    {
        [$body, $headers] = $this->signed($payload, $secret ?? 'x');

        return $this->call('POST', route('api.whatsapp.handle'), [], [], [], $server ?? $headers, $body);
    }

    public function test_valid_signature_creates_pending_prelead(): void
    {
        config(['services.whatsapp.app_secret' => 'segredo-teste']);

        $this->hook($this->textMessage())->assertOk()->assertJson(['status' => 'ok']);

        $lead = PreLead::sole();
        $this->assertSame('+351912345678', $lead->phone);
        $this->assertSame('João Silva', $lead->name);
        $this->assertSame('Olá, quero importar um carro', $lead->message);
        $this->assertSame('pendente', $lead->status);
    }

    public function test_invalid_or_missing_signature_is_rejected_and_nothing_is_created(): void
    {
        config(['services.whatsapp.app_secret' => 'segredo-teste']);

        $this->hook($this->textMessage(), 'segredo-errado')->assertForbidden()->assertJson(['error' => 'invalid signature']);
        $this->hook($this->textMessage(), 'segredo-teste', ['CONTENT_TYPE' => 'application/json'])->assertForbidden();
        $this->hook($this->textMessage(), 'segredo-teste', ['HTTP_X-Hub-Signature-256' => 'sha256=', 'CONTENT_TYPE' => 'application/json'])->assertForbidden();

        $this->assertSame(0, PreLead::count());
    }

    public function test_signature_over_a_different_body_is_rejected(): void
    {
        config(['services.whatsapp.app_secret' => 'segredo-teste']);
        [, $headers] = $this->signed($this->textMessage(text: 'original'));

        $adulterado = json_encode($this->textMessage(text: 'adulterado'));
        $this->call('POST', route('api.whatsapp.handle'), [], [], [], $headers, $adulterado)->assertForbidden();

        $this->assertSame(0, PreLead::count());
    }

    public function test_empty_app_secret_always_refuses_even_with_hmac_of_empty_key(): void
    {
        foreach ([null, ''] as $vazio) {
            config(['services.whatsapp.app_secret' => $vazio]);
            $payload = $this->textMessage();
            $body = json_encode($payload);
            $assinaturaComChaveVazia = hash_hmac('sha256', $body, '');

            $this->call('POST', route('api.whatsapp.handle'), [], [], [], ['HTTP_X-Hub-Signature-256' => 'sha256=' . $assinaturaComChaveVazia, 'CONTENT_TYPE' => 'application/json'], $body)
                ->assertForbidden()->assertJson(['error' => 'webhook not configured']);
        }

        $this->assertSame(0, PreLead::count());
    }

    public function test_signature_without_sha256_prefix_is_also_accepted(): void
    {
        config(['services.whatsapp.app_secret' => 'segredo-teste']);
        $body = json_encode($this->textMessage());

        $this->call('POST', route('api.whatsapp.handle'), [], [], [], ['HTTP_X-Hub-Signature-256' => hash_hmac('sha256', $body, 'segredo-teste'), 'CONTENT_TYPE' => 'application/json'], $body)->assertOk();

        $this->assertSame(1, PreLead::count());
    }

    public function test_webhook_ignores_non_text_status_events_and_existing_clients_and_repeats(): void
    {
        config(['services.whatsapp.app_secret' => 'segredo-teste']);

        // Evento de estado (entregue/lido): sem "messages".
        $this->hook(['entry' => [['changes' => [['value' => ['statuses' => [['id' => 'x', 'status' => 'read']]]]]]]])->assertOk();
        // Imagem: ignorada.
        $this->hook(['entry' => [['changes' => [['value' => ['messages' => [['from' => '351911111111', 'type' => 'image']]]]]]]])->assertOk();
        // Corpo vazio/estrutura desconhecida: continua a responder 200.
        $this->hook(['foo' => 'bar'])->assertOk();
        $this->assertSame(0, PreLead::count());

        // Número que já é cliente: não cria pré-lead.
        Client::create(['name' => 'Cliente Antigo', 'phone' => '+351933333333', 'is_lead' => false, 'lead_status' => 'nova']);
        $this->hook($this->textMessage('351933333333'))->assertOk();
        $this->assertSame(0, PreLead::count());

        // Mesma pessoa a escrever duas vezes: um único pré-lead (o primeiro texto fica).
        $this->hook($this->textMessage('351944444444', 'primeira'))->assertOk();
        $this->hook($this->textMessage('351944444444', 'segunda'))->assertOk();
        $this->assertSame(1, PreLead::count());
        $this->assertSame('primeira', PreLead::sole()->message);
    }

    public function test_verify_handshake(): void
    {
        config(['services.whatsapp.verify_token' => 'token-certo']);

        $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=token-certo&hub_challenge=12345')->assertOk()->assertSee('12345');
        $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=errado&hub_challenge=12345')->assertForbidden();
        $this->get('/api/whatsapp/webhook?hub_mode=unsubscribe&hub_verify_token=token-certo&hub_challenge=12345')->assertForbidden();
    }

    public function test_verify_rejects_request_without_token_when_none_configured(): void
    {
        foreach ([null, ''] as $vazio) {
            config(['services.whatsapp.verify_token' => $vazio]);

            $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_challenge=999')->assertForbidden();
            $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=&hub_challenge=999')->assertForbidden();
        }
    }

    // ── Sitemap ──────────────────────────────────────────────────────────────

    private function sitemap(): string
    {
        // Só aqui: noutros testes o layout público lê css/js de public_path().
        $this->app->usePublicPath($this->publicDir);

        $this->artisan('sitemap:generate')->expectsOutput('Sitemap gerado com sucesso!')->assertExitCode(0);

        $this->assertFileExists($this->publicDir . '/sitemap.xml');

        return file_get_contents($this->publicDir . '/sitemap.xml');
    }

    private function news(string $title, string $slug, string $status): NewsArticle
    {
        return NewsArticle::create(['title' => $title, 'slug' => $slug, 'content' => 'Conteúdo da notícia longo.', 'status' => $status, 'published_at' => now()]);
    }

    public function test_sitemap_has_static_pages_and_only_published_news(): void
    {
        $this->news('Pública', 'noticia-publica', 'Publicado');
        $this->news('Rascunho', 'noticia-rascunho', 'Rascunho');

        $xml = $this->sitemap();

        foreach (['/viaturas', '/importacao', '/legalizacao', '/noticias', '/simulador-custos'] as $path) {
            $this->assertStringContainsString($path . '</loc>', $xml);
        }
        $this->assertStringContainsString('/noticias/noticia-publica</loc>', $xml);
        $this->assertStringNotContainsString('noticia-rascunho', $xml);
    }

    public function test_sitemap_includes_only_vehicles_shown_online_with_brand_model_and_reference(): void
    {
        $publica = V3Vehicle::create(['reference' => 'V3-2026-001', 'brand' => 'Citroën', 'model' => 'C4 Cactus', 'show_online' => true]);
        V3Vehicle::create(['reference' => 'V3-2026-002', 'brand' => 'Audi', 'model' => 'A3', 'show_online' => false]);
        V3Vehicle::create(['reference' => 'V3-2026-003', 'brand' => null, 'model' => 'Sem marca', 'show_online' => true]);

        $xml = $this->sitemap();

        $this->assertStringContainsString('/viaturas/citroen/c4-cactus/V3-2026-001</loc>', $xml);
        $this->assertStringNotContainsString('V3-2026-002', $xml);
        $this->assertStringNotContainsString('V3-2026-003', $xml);
        $this->assertSame(1, substr_count($xml, '/viaturas/citroen/'));
        $this->assertNotNull($publica);
    }

    /**
     * Caracterização: o sitemap lista viaturas com show_online=true mesmo que estejam vendidas
     * (o comando não olha para o estado). Se o site público as esconde, convém alinhar.
     */
    public function test_sitemap_includes_sold_vehicle_if_still_online(): void
    {
        V3Vehicle::create(['reference' => 'V3-2026-009', 'brand' => 'Fiat', 'model' => 'Panda', 'show_online' => true, 'status' => 'vendido']);

        $this->assertStringContainsString('V3-2026-009', $this->sitemap());
    }

    public function test_sitemap_command_does_not_touch_the_real_public_sitemap(): void
    {
        $real = base_path('public/sitemap.xml');
        $antes = file_exists($real) ? md5_file($real) : null;

        $this->sitemap();

        $this->assertSame($antes, file_exists($real) ? md5_file($real) : null);
    }

    // ── Páginas públicas de estado de legalização ────────────────────────────

    private function legalization(array $over = []): Legalization
    {
        return Legalization::create($over + [
            'marca' => 'Tesla', 'modelo' => 'Model 3', 'combustivel' => 'Elétrico', 'matricula' => 'AA-00-AA', 'steps_completed' => [1, 2],
        ]);
    }

    public function test_status_page_shows_vehicle_progress_and_steps_without_login(): void
    {
        $l = $this->legalization();

        $this->get(route('frontend.legalization.status', $l->token))
            ->assertOk()
            ->assertSee('Tesla Model 3')
            ->assertSee('AA-00-AA')
            ->assertSee('Obter número de homologação nacional no IMT')
            ->assertSee('29%'); // 2 de 7 passos
    }

    public function test_status_page_with_unknown_or_empty_token_is_404(): void
    {
        $this->legalization();

        $this->get(route('frontend.legalization.status', 'token-inexistente'))->assertNotFound();
        $this->get(route('frontend.legalization.status.invoice', 'token-inexistente'))->assertNotFound();
    }

    public function test_status_page_tokens_are_unique_random_and_do_not_leak_other_processes(): void
    {
        $a = $this->legalization(['marca' => 'MarcaA', 'modelo' => 'ModeloA']);
        $b = $this->legalization(['marca' => 'MarcaB', 'modelo' => 'ModeloB']);

        $this->assertSame(32, strlen($a->token));
        $this->assertNotSame($a->token, $b->token);

        $this->get(route('frontend.legalization.status', $a->token))->assertOk()->assertSee('MarcaA')->assertDontSee('MarcaB');
        // O id numérico não serve como token.
        $this->get(route('frontend.legalization.status', (string) $a->id))->assertNotFound();
    }

    public function test_status_page_does_not_expose_internal_notes_or_client_contacts(): void
    {
        $client = Client::create(['name' => 'Nome Privado Cliente', 'email' => 'privado@cliente.test', 'phone' => '+351900000001', 'is_lead' => false, 'lead_status' => 'nova']);
        $l = $this->legalization(['client_id' => $client->id, 'notas' => 'NOTA-INTERNA-CONFIDENCIAL', 'num_processo_imt' => 'IMT-SECRETO-123']);

        $this->get(route('frontend.legalization.status', $l->token))
            ->assertOk()
            ->assertDontSee('NOTA-INTERNA-CONFIDENCIAL')
            ->assertDontSee('IMT-SECRETO-123')
            ->assertDontSee('privado@cliente.test')
            ->assertDontSee('+351900000001');
    }

    public function test_status_page_uses_client_language_and_lists_received_documents(): void
    {
        $client = Client::create(['name' => 'Tom', 'language' => 'en', 'is_lead' => false, 'lead_status' => 'nova']);
        $l = $this->legalization(['client_id' => $client->id]);
        LegalizationDocument::create(['legalization_id' => $l->id, 'tipo' => array_key_first(Legalization::DOCUMENTOS), 'nome_original' => 'a.pdf', 'caminho' => 'x/a.pdf']);

        $this->get(route('frontend.legalization.status', $l->token))
            ->assertOk()
            ->assertSee('Process tracking')
            ->assertSee('License plate')
            ->assertDontSee('Passos do Processo');
    }

    public function test_invoice_download_requires_existing_file_and_valid_token(): void
    {
        $l = $this->legalization();

        // Sem fatura: 404.
        $this->get(route('frontend.legalization.status.invoice', $l->token))->assertNotFound();

        // Com caminho mas sem ficheiro no disco: 404.
        $l->update(['invoice_path' => 'legalizations/1/fatura.pdf']);
        $this->get(route('frontend.legalization.status.invoice', $l->token))->assertNotFound();

        Storage::disk('local')->put('legalizations/1/fatura.pdf', 'PDF');
        $this->get(route('frontend.legalization.status.invoice', $l->token))->assertOk()->assertDownload('fatura_' . $l->id . '.pdf');

        // Outro token não acede à fatura deste processo.
        $outro = $this->legalization();
        $this->get(route('frontend.legalization.status.invoice', $outro->token))->assertNotFound();
    }

    public function test_status_page_shows_invoice_link_only_when_available(): void
    {
        $sem = $this->legalization();
        $com = $this->legalization(['invoice_path' => 'legalizations/9/fatura.pdf']);
        Storage::disk('local')->put('legalizations/9/fatura.pdf', 'PDF');

        $this->get(route('frontend.legalization.status', $sem->token))->assertOk()->assertDontSee(route('frontend.legalization.status.invoice', $sem->token));
        $this->get(route('frontend.legalization.status', $com->token))->assertOk()->assertSee(route('frontend.legalization.status.invoice', $com->token));
    }
}
