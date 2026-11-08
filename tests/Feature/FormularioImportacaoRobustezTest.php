<?php

namespace Tests\Feature;

use App\Models\FormProposal;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class FormularioImportacaoRobustezTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Notification::fake();
        foreach (['email' => 'geral@izzycar.pt', 'phone' => '928459346', 'facebook' => 'x', 'insta' => 'x'] as $label => $value) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => $value]);
        }
    }

    private function payload(array $o = []): array
    {
        return $o + [
            'name' => 'Maria Silva', 'phone' => '912 345 678', 'email' => 'maria@example.com',
            '_form_started' => time() - 10, 'buyer_type' => 'particular', 'payment_type' => 'financiamento',
            'estimated_purchase_date' => 'imediato', 'data_processing_consent' => '1', 'brand' => 'BMW', 'model' => 'i4',
        ];
    }

    public function test_falha_de_email_depois_de_gravar_nao_devolve_erro(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP em baixo'));
        Mail::shouldReceive('raw')->andThrow(new \RuntimeException('SMTP em baixo'));

        $this->postJson(route('frontend.import-submit'), $this->payload())
            ->assertOk()->assertJsonPath('status', 'success');

        $this->assertSame(1, FormProposal::where('email', 'maria@example.com')->count());
    }

    public function test_bloqueio_de_robo_responde_com_o_formato_que_o_formulario_espera(): void
    {
        $this->postJson(route('frontend.import-submit'), $this->payload(['company_website' => 'http://spam']))
            ->assertOk()->assertJsonPath('status', 'success');

        $this->assertSame(0, FormProposal::count());
    }

    public function test_limite_por_email_avisa_a_pessoa_com_429_e_mensagem(): void
    {
        Mail::fake();
        $chave = 'antispam:email:frontend.import-submit:' . sha1('maria@example.com');
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($chave, 3600);
        }

        $this->postJson(route('frontend.import-submit'), $this->payload())
            ->assertStatus(429)->assertJsonPath('status', 'error')
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'Aguarde'));

        $this->assertSame(0, FormProposal::count());
    }
}
