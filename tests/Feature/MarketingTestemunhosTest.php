<?php

namespace Tests\Feature;

use App\Marketing\PostRenderer;
use App\Models\MarketingPost;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Testemunhos no Marketing Studio: só com autorização, texto nunca reescrito, primeiro nome por omissão. */
class MarketingTestemunhosTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private const COMENTARIO = 'Excelente serviço,  tudo tratado com rapidez. Recomendo a toda a gente!';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function admin()
    {
        return $this->actingAs($this->backofficeUser('admin'));
    }

    private function testemunho(bool $consent = true, string $comment = self::COMENTARIO, string $name = 'Maria João Silva'): Testimonial
    {
        return Testimonial::create([
            'name' => $name, 'rating' => 4.5, 'comment' => $comment, 'origin' => 'google', 'marketing_consent' => $consent,
        ]);
    }

    private function criar(Testimonial $t, string $format = 'feed_square')
    {
        return $this->admin()->postJson(route('admin.marketing.content.store', 'testemunho'), [
            'testimonial_id' => $t->id, 'template' => 'testemunho', 'format' => $format,
        ]);
    }

    public function test_sem_consentimento_da_422(): void
    {
        $this->criar($this->testemunho(false))->assertUnprocessable()->assertJsonValidationErrors('testimonial_id');
        $this->assertSame(0, MarketingPost::count());
    }

    public function test_sem_comentario_da_422(): void
    {
        $this->criar($this->testemunho(true, ''))->assertUnprocessable()->assertJsonValidationErrors('testimonial_id');
        $this->criar($this->testemunho(true, '   '))->assertUnprocessable();
    }

    public function test_com_consentimento_cria_post_com_primeiro_nome(): void
    {
        $t = $this->testemunho();
        $this->criar($t)->assertCreated();

        $post = MarketingPost::firstOrFail();
        $this->assertSame(Testimonial::class, $post->source_type);
        $this->assertSame($t->id, $post->source_id);
        $this->assertSame('Maria', $post->data['first_name']);

        $html = PostRenderer::render($post)['html'];
        $this->assertStringContainsString('Maria', $html);
        $this->assertStringNotContainsString('Silva', $html);
        $this->assertStringNotContainsString('null', strtolower($html));
    }

    public function test_nome_completo_so_por_opcao_explicita(): void
    {
        $this->criar($this->testemunho())->assertCreated();
        $post = MarketingPost::firstOrFail();

        $r = $this->admin()->postJson(route('admin.marketing.preview', $post), ['overrides' => ['show_full_name' => true]])->assertOk();
        $this->assertStringContainsString('Maria João Silva', $r->json('html'));
    }

    public function test_comentario_nao_e_reescrito(): void
    {
        $this->criar($this->testemunho())->assertCreated();

        $this->assertSame(self::COMENTARIO, MarketingPost::firstOrFail()->data['comment']);
    }

    public function test_excerto_inexistente_da_422(): void
    {
        $this->criar($this->testemunho())->assertCreated();
        $post = MarketingPost::firstOrFail();

        $this->admin()->postJson(route('admin.marketing.preview', $post), ['overrides' => ['quote' => 'Uma frase que o cliente nunca disse']])
            ->assertUnprocessable()->assertJsonValidationErrors('overrides.quote');
        $this->admin()->putJson(route('admin.marketing.update', $post), [
            'template' => 'testemunho', 'format' => 'feed_square', 'overrides' => ['quote' => 'Recomendo a toda a gente! Sempre']])
            ->assertUnprocessable();
    }

    public function test_excerto_valido_aparece_na_imagem(): void
    {
        $this->criar($this->testemunho())->assertCreated();
        $post = MarketingPost::firstOrFail();

        $r = $this->admin()->postJson(route('admin.marketing.preview', $post), ['overrides' => ['quote' => 'tudo tratado com RAPIDEZ.']])->assertOk();
        $this->assertStringContainsString('tudo tratado com RAPIDEZ.', $r->json('html'));
        $this->assertStringNotContainsString('Excelente', $r->json('html'));

        $this->admin()->putJson(route('admin.marketing.update', $post), [
            'template' => 'testemunho', 'format' => 'feed_square', 'overrides' => ['quote' => 'tudo tratado com RAPIDEZ.']])->assertOk();
        $this->assertSame('tudo tratado com RAPIDEZ.', $post->fresh()->config['overrides']['quote']);
    }

    public function test_comentario_longo_exige_escolher_excerto(): void
    {
        $longo = str_repeat('Muito bom serviço. ', 20);
        $this->criar($this->testemunho(true, $longo))->assertCreated();
        $post = MarketingPost::firstOrFail();

        $html = PostRenderer::render($post)['html'];
        $this->assertStringNotContainsString('Muito bom', $html);
        $this->assertStringNotContainsString('null', strtolower($html));
    }

    public function test_pesquisa_so_lista_testemunhos_autorizados(): void
    {
        $this->testemunho(true, 'Gostei muito', 'Ana Costa');
        $this->testemunho(false, 'Não autorizado', 'Rui Pires');

        $res = $this->admin()->getJson(route('admin.marketing.testimonials.search'))->assertOk();
        $this->assertCount(1, $res->json());
        $this->assertStringContainsString('Ana', $res->json('0.label'));
        $this->assertStringNotContainsString('Rui', $res->getContent());
    }

    public function test_formulario_edicao_nao_repoe_o_consentimento_apos_erro(): void
    {
        $t = $this->testemunho();
        $this->admin()->from(route('admin.testimonials.edit', $t))->put(route('admin.testimonials.update', $t), ['name' => '', 'rating' => 5, 'origin' => 'google']);
        $html = $this->admin()->get(route('admin.testimonials.edit', $t))->getContent();
        $this->assertDoesNotMatchRegularExpression('/name="marketing_consent"[^>]*checked/s', $html);
    }

    public function test_formulario_guarda_consentimento(): void
    {
        $dados = ['name' => 'Joana Reis', 'rating' => 5, 'comment' => 'Ótimo', 'origin' => 'google'];

        $this->admin()->post(route('admin.testimonials.store'), $dados + ['marketing_consent' => 1])->assertRedirect();
        $t = Testimonial::where('name', 'Joana Reis')->firstOrFail();
        $this->assertTrue($t->marketing_consent);

        $this->admin()->put(route('admin.testimonials.update', $t), $dados)->assertRedirect();
        $this->assertFalse($t->fresh()->marketing_consent);
    }

    public function test_novo_testemunho_nao_e_elegivel_por_omissao(): void
    {
        $t = Testimonial::create(['name' => 'X Y', 'rating' => 5, 'comment' => 'Bom', 'origin' => 'google']);
        $this->assertFalse($t->fresh()->marketing_consent);
    }

    public function test_atualizar_dados_nao_apanha_testemunho_que_perdeu_consentimento(): void
    {
        $t = $this->testemunho();
        $this->criar($t)->assertCreated();
        $post = MarketingPost::firstOrFail();

        $t->update(['marketing_consent' => false, 'comment' => 'Texto novo']);

        $this->admin()->post(route('admin.marketing.refresh', $post))->assertSessionHas('error');
        $this->assertSame(self::COMENTARIO, $post->fresh()->data['comment']);
    }

    public function test_atualizar_dados_descarta_excerto_que_ja_nao_existe(): void
    {
        $t = $this->testemunho();
        $this->criar($t)->assertCreated();
        $post = MarketingPost::firstOrFail();
        $post->update(['config' => ['overrides' => ['quote' => 'tudo tratado com rapidez.']]]);

        $t->update(['comment' => 'Comentário completamente diferente.']);
        $this->admin()->post(route('admin.marketing.refresh', $post))->assertSessionHas('success');

        $post->refresh();
        $this->assertSame('Comentário completamente diferente.', $post->data['comment']);
        $this->assertArrayNotHasKey('quote', $post->config['overrides'] ?? []);
    }

    public function test_duplicar_testemunho_sem_consentimento_e_recusado(): void
    {
        $t = $this->testemunho();
        $this->criar($t)->assertCreated();
        $post = MarketingPost::firstOrFail();
        $t->update(['marketing_consent' => false]);

        $this->admin()->post(route('admin.marketing.duplicate', $post))->assertSessionHas('error');
        $this->assertSame(1, MarketingPost::count());
        $this->admin()->get(route('admin.marketing.editor', $post))->assertOk()->assertSee('já não tem autorização');
    }

    public function test_excerto_nao_corta_palavras_nem_inverte_o_sentido(): void
    {
        $this->criar($this->testemunho(true, 'Não recomendo a ninguém. Foi impecável.'))->assertCreated();
        $post = MarketingPost::firstOrFail();

        foreach (['pecável', 'recomendo a ninguém', 'Não recomendo a ning'] as $q) {
            $r = $this->admin()->postJson(route('admin.marketing.preview', $post), ['overrides' => ['quote' => $q]]);
            if ($q === 'recomendo a ninguém') {
                $r->assertOk(); // começa numa palavra inteira: permitido, mas com reticências
                $this->assertStringContainsString('…', $r->json('html'));
            } else {
                $r->assertUnprocessable();
            }
        }
    }

    public function test_excerto_acima_de_280_caracteres_da_422(): void
    {
        $longo = str_repeat('Muito bom. ', 30);
        $this->criar($this->testemunho(true, $longo))->assertCreated();
        $this->admin()->postJson(route('admin.marketing.preview', MarketingPost::firstOrFail()), ['overrides' => ['quote' => trim($longo)]])
            ->assertUnprocessable();
    }
}
