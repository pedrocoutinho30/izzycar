<?php

namespace Tests\Feature;

use App\Models\MarketingPost;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Legendas com IA: chave só no backend, fallback sem bloquear, limite diário. */
class MarketingLegendasTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private const CHAVE = 'sk-test-SEGREDO-123';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        config(['services.openai.key' => self::CHAVE, 'marketing.ai_daily_limit' => 30, 'marketing.ai_model' => 'gpt-4o-mini']);
    }

    private function novoPost(): MarketingPost
    {
        return MarketingPost::create([
            'type' => 'veiculo', 'template' => 'premium', 'format' => 'feed_square', 'status' => 'rascunho', 'title' => 'Audi A3',
            'data' => [
                'vehicle_id' => 7, 'reference' => 'REF-1', 'status' => 'em_stock', 'brand' => 'Audi', 'model' => 'A3', 'version' => '35 TFSI',
                'year' => 2021, 'kilometers' => 42000, 'fuel' => 'Gasolina', 'price' => 27500.0, 'equipment' => ['Bluetooth', 'LED'],
                'client_name' => 'Joaquim Secreto', 'registration' => 'AA-11-BB', 'vin' => 'WBAVIN123', 'photos' => [],
            ],
            'config' => [],
        ]);
    }

    private function gerar(MarketingPost $post, string $tone = 'profissional', $user = null)
    {
        return ($user ? $this->actingAs($user) : $this->actingAs($this->backofficeUser('admin')))
            ->postJson(route('admin.marketing.caption', $post), ['tone' => $tone]);
    }

    private function respostaOpenAi(array $conteudo, int $status = 200)
    {
        return Http::response(['choices' => [['message' => ['content' => json_encode($conteudo)]]], 'usage' => ['total_tokens' => 321]], $status);
    }

    public function test_sem_chave_usa_fallback_com_dados_reais(): void
    {
        config(['services.openai.key' => null]);
        Http::fake();

        $r = $this->gerar($this->novoPost())->assertOk();

        $this->assertFalse($r->json('ai'));
        $this->assertNotEmpty($r->json('warning'));
        $this->assertStringContainsString('Audi A3', $r->json('caption'));
        $this->assertStringContainsString('#izzycar', $r->json('hashtags'));
        $this->assertNotSame('', $r->json('cta'));
        $this->assertStringNotContainsString('AA-11-BB', $r->getContent());
        Http::assertNothingSent();
    }

    public function test_resposta_valida_preenche_legenda_hashtags_e_cta(): void
    {
        Http::fake(['api.openai.com/*' => $this->respostaOpenAi(['caption' => 'Um Audi A3 pronto a rolar.', 'hashtags' => ['#izzycar', 'audi', '#a3'], 'cta' => 'Fala connosco'])]);

        $r = $this->gerar($this->novoPost(), 'premium')->assertOk();

        $this->assertTrue($r->json('ai'));
        $this->assertNull($r->json('warning'));
        $this->assertSame('Um Audi A3 pronto a rolar.', $r->json('caption'));
        $this->assertSame('#izzycar #audi #a3', $r->json('hashtags'));
        $this->assertSame('Fala connosco', $r->json('cta'));

        Http::assertSent(function (Request $req) {
            $b = $req->data();

            return $req->hasHeader('Authorization', 'Bearer ' . self::CHAVE)
                && $b['model'] === 'gpt-4o-mini' && $b['max_tokens'] === 700
                && $b['response_format']['type'] === 'json_object'
                && str_contains($b['messages'][1]['content'], 'premium');
        });
    }

    public function test_erro_http_usa_fallback_e_devolve_200(): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['error' => 'boom'], 500)]);

        $r = $this->gerar($this->novoPost())->assertOk();
        $this->assertFalse($r->json('ai'));
        $this->assertNotEmpty($r->json('warning'));
        $this->assertStringContainsString('Audi A3', $r->json('caption'));
    }

    public function test_timeout_usa_fallback(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $r = $this->gerar($this->novoPost())->assertOk();
        $this->assertFalse($r->json('ai'));
    }

    public function test_json_invalido_ou_incompleto_usa_fallback(): void
    {
        foreach ([
            Http::response(['choices' => [['message' => ['content' => 'isto não é json']]]], 200),
            $this->respostaOpenAi(['hashtags' => '#x']),
            $this->respostaOpenAi(['caption' => '   ']),
            Http::response(['sem' => 'choices'], 200),
        ] as $resp) {
            Http::fake(['api.openai.com/*' => $resp]);
            $r = $this->gerar($this->novoPost())->assertOk();
            $this->assertFalse($r->json('ai'));
            $this->assertStringContainsString('Audi A3', $r->json('caption'));
        }
    }

    public function test_prompt_so_tem_dados_do_instantaneo(): void
    {
        Http::fake(['api.openai.com/*' => $this->respostaOpenAi(['caption' => 'x', 'hashtags' => '#a', 'cta' => 'y'])]);
        $user = $this->backofficeUser('admin');

        $this->gerar($this->novoPost(), 'comercial', $user)->assertOk();

        Http::assertSent(function (Request $req) use ($user) {
            $corpo = json_encode($req->data());
            $utilizador = $req->data()['messages'][1]['content'];

            return str_contains($utilizador, 'Audi')
                && ! preg_match('/client|registration|vin|secreto|AA-11|REF-1/i', $utilizador)
                && ! str_contains($corpo, $user->email)
                && ! str_contains($corpo, self::CHAVE);
        });
    }

    public function test_limite_diario_da_429_sem_chamar_a_openai(): void
    {
        config(['marketing.ai_daily_limit' => 0]);
        Http::fake();

        $this->gerar($this->novoPost())->assertStatus(429)->assertJsonStructure(['message']);
        Http::assertNothingSent();
    }

    public function test_limite_conta_por_utilizador_e_por_dia(): void
    {
        config(['marketing.ai_daily_limit' => 2]);
        Http::fake(['api.openai.com/*' => $this->respostaOpenAi(['caption' => 'x', 'hashtags' => '#a', 'cta' => 'y'])]);
        $post = $this->novoPost();
        $a = $this->backofficeUser('admin');

        $this->gerar($post, 'profissional', $a)->assertOk();
        $this->gerar($post, 'profissional', $a)->assertOk();
        $this->gerar($post, 'profissional', $a)->assertStatus(429);
        Http::assertSentCount(2);

        // outro utilizador tem o seu próprio limite
        $this->gerar($post, 'profissional', $this->backofficeUser('admin'))->assertOk();
    }

    public function test_tom_invalido_da_422(): void
    {
        Http::fake();
        $this->gerar($this->novoPost(), 'sarcastico')->assertUnprocessable()->assertJsonValidationErrors('tone');
        $this->actingAs($this->backofficeUser('admin'))->postJson(route('admin.marketing.caption', $this->novoPost()), [])->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_chave_nunca_aparece_no_html_do_editor(): void
    {
        $html = $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.editor', $this->novoPost()))->assertOk()->getContent();

        $this->assertStringNotContainsString(self::CHAVE, $html);
        $this->assertStringContainsString('Gerar legenda', $html);
    }

    public function test_angariador_nao_gera(): void
    {
        Http::fake();
        $this->gerar($this->novoPost(), 'profissional', $this->backofficeUser('angariador'))->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_registo_nao_inclui_o_texto_gerado(): void
    {
        Log::spy();
        Http::fake(['api.openai.com/*' => $this->respostaOpenAi(['caption' => 'TEXTO SECRETO', 'hashtags' => '#a', 'cta' => 'y'])]);

        $this->gerar($this->novoPost())->assertOk();

        Log::shouldHaveReceived('info')->withArgs(fn ($msg, $ctx = []) => $msg === 'marketing.caption'
            && $ctx['ai'] === true && $ctx['tone'] === 'profissional' && $ctx['tokens'] === 321
            && ! str_contains(json_encode($ctx), 'TEXTO SECRETO'))->once();
    }

    public function test_a_legenda_nao_e_gravada_sozinha(): void
    {
        Http::fake(['api.openai.com/*' => $this->respostaOpenAi(['caption' => 'Nova', 'hashtags' => '#a', 'cta' => 'y'])]);
        $post = $this->novoPost();

        $this->gerar($post)->assertOk();

        $this->assertNull($post->fresh()->caption);
    }

    public function test_resposta_com_tipos_errados_usa_fallback(): void
    {
        foreach ([['caption' => ['text' => 'x']], ['caption' => 'ok', 'hashtags' => [['#a']]], ['caption' => 'ok', 'cta' => ['x']]] as $c) {
            Http::fake(['api.openai.com/*' => $this->respostaOpenAi($c)]);
            $r = $this->gerar($this->novoPost())->assertOk();
            $this->assertFalse($r->json('ai'));
        }
    }
}
