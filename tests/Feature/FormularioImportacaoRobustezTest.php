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

    public function test_formulario_de_importacao_nao_tem_limites_nem_antispam(): void
    {
        Mail::fake();
        $chave = 'antispam:email:frontend.import-submit:' . sha1('maria@example.com');
        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit($chave, 3600);
        }

        // Sem _form_started, com o campo-isco preenchido e depois de muitos pedidos: grava na mesma.
        $this->postJson(route('frontend.import-submit'), $this->payload(['company_website' => 'x']) + [])
            ->assertOk()->assertJsonPath('status', 'success');
        $this->assertSame(1, FormProposal::where('email', 'maria@example.com')->count());

        for ($i = 0; $i < 8; $i++) {
            $p = $this->payload(['email' => "pessoa{$i}@example.com", 'phone' => '91234567' . $i]);
            unset($p['_form_started']);
            $this->postJson(route('frontend.import-submit'), $p)->assertOk();
        }
        $this->assertSame(9, FormProposal::count());
    }
}
