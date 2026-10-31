<?php

namespace Tests\Feature;

use App\Mail\NewsletterConfirmation;
use App\Mail\NewsletterUnsubscribeNotification;
use App\Models\Client;
use App\Models\Newsletter;
use App\Support\NewsletterLinks;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class NewsletterSecurityTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();
        config(['mail.from.address' => 'geral@izzycar.pt']);
    }

    private function client(bool $consent = true, string $email = 'maria@example.com'): Client
    {
        return Client::create(['name' => 'Maria', 'email' => $email, 'is_lead' => false, 'newsletter_consent' => $consent]);
    }

    public function test_unsubscribe_sem_assinatura_e_403_e_nao_altera_nada(): void
    {
        $client = $this->client();

        $this->get(route('newsletter.unsubscribe', ['email' => 'maria@example.com']))
            ->assertForbidden()->assertSee('Link inválido');

        $this->assertTrue((bool) $client->fresh()->newsletter_consent);
        Mail::assertNothingSent();
    }

    public function test_unsubscribe_com_assinatura_valida_retira_consentimento_e_avisa_uma_vez(): void
    {
        $client = $this->client();
        $url = NewsletterLinks::unsubscribe('maria@example.com');

        $this->get($url)->assertOk();
        $this->assertFalse((bool) $client->fresh()->newsletter_consent);
        Mail::assertSent(NewsletterUnsubscribeNotification::class, 1);

        // Repetir o link não volta a gerar emails ao administrador.
        $this->get($url)->assertOk();
        Mail::assertSent(NewsletterUnsubscribeNotification::class, 1);
    }

    public function test_assinatura_nao_serve_para_outro_email(): void
    {
        $outro = $this->client(true, 'outro@example.com');
        $url = str_replace('maria%40example.com', 'outro%40example.com', NewsletterLinks::unsubscribe('maria@example.com'));

        $this->get($url)->assertForbidden();
        $this->assertTrue((bool) $outro->fresh()->newsletter_consent);
    }

    public function test_subscribe_nao_liga_consentimento_de_cliente_existente(): void
    {
        $client = $this->client(false);

        $this->postJson(route('newsletter.subscribe'), ['email' => 'maria@example.com', '_form_started' => time() - 10])
            ->assertOk()->assertJsonPath('success', true);

        $this->assertFalse((bool) $client->fresh()->newsletter_consent);
        Mail::assertSent(NewsletterConfirmation::class, fn ($m) => $m->hasTo('maria@example.com'));
    }

    public function test_subscribe_novo_contacto_so_fica_com_consentimento_apos_confirmar(): void
    {
        $this->postJson(route('newsletter.subscribe'), ['email' => 'novo@example.com', '_form_started' => time() - 10])->assertOk();

        $client = Client::where('email', 'novo@example.com')->sole();
        $this->assertFalse((bool) $client->newsletter_consent);

        $this->get(route('newsletter.confirm', $client->id))->assertForbidden();
        $this->assertFalse((bool) $client->fresh()->newsletter_consent);

        $this->get(NewsletterLinks::confirm($client->id))->assertOk()->assertSee('confirmada');
        $this->assertTrue((bool) $client->fresh()->newsletter_consent);
    }

    public function test_do_send_ignora_clientes_sem_consentimento(): void
    {
        $user = $this->backofficeUser('admin');
        $com = $this->client(true, 'com@example.com');
        $sem = $this->client(false, 'sem@example.com');
        $newsletter = Newsletter::create(['title' => 'N', 'template' => 'custos-importacao']);

        $this->actingAs($user)->post(route('admin.v2.newsletter-management.doSend', $newsletter->id), ['client_ids' => [$com->id, $sem->id]]);

        Mail::assertSent(\App\Mail\NewsletterPreview::class, 1);
        Mail::assertSent(\App\Mail\NewsletterPreview::class, fn ($m) => $m->hasTo('com@example.com'));
        Mail::assertNotSent(\App\Mail\NewsletterPreview::class, fn ($m) => $m->hasTo('sem@example.com'));
    }
}
