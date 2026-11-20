<?php

namespace Tests\Feature;

use App\Enums\MarketingPostType;
use App\Marketing\PostRenderer;
use App\Marketing\TemplateRegistry;
use App\Models\MarketingPost;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Posts educativos e de serviços: sem fonte, o texto é escrito pelo utilizador. */
class MarketingEducativosTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private const TEMPLATES_TEXTO = ['dica-rapida', 'mito-verdade', 'pergunta-frequente', 'passo-a-passo', 'erros-comuns', 'educativo-carrossel', 'servico'];

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

    private function novoPost(string $template = 'passo-a-passo', array $data = [], string $type = 'educativo', string $format = 'feed_vertical'): MarketingPost
    {
        return MarketingPost::create([
            'type' => $type, 'template' => $template, 'format' => $format, 'status' => 'rascunho',
            'title' => 'T', 'data' => $data + ['title' => 'Como importar', 'items' => [], 'legal_check' => false], 'config' => [],
        ]);
    }

    public function test_biblioteca_de_temas_nao_traz_texto_fiscal(): void
    {
        $topics = config('marketing.topics');
        $this->assertNotEmpty($topics);
        foreach ($topics as $t) {
            $this->assertSame(['hint', 'key', 'legal_check', 'template', 'title', 'type'], collect($t)->keys()->sort()->values()->all(), $t['key'] ?? '?');
            $this->assertLessThanOrEqual(160, mb_strlen($t['hint']), $t['key']);
            $this->assertContains($t['type'], ['educativo', 'servico']);
            $this->assertContains($t['template'], self::TEMPLATES_TEXTO);
        }
    }

    public function test_temas_fiscais_marcam_legal_check(): void
    {
        $byKey = collect(config('marketing.topics'))->keyBy('key');
        foreach (['isv', 'dav', 'isencao-transferencia-residencia'] as $k) {
            $this->assertTrue($byKey[$k]['legal_check'], $k);
        }
    }

    public function test_store_cria_post_sem_fonte(): void
    {
        $this->admin()->postJson(route('admin.marketing.content.store', 'educativo'), [
            'topic' => 'isv', 'template' => 'dica-rapida', 'format' => 'feed_square',
        ])->assertCreated();

        $post = MarketingPost::latest('id')->firstOrFail();
        $this->assertSame(MarketingPostType::Educativo, $post->type);
        $this->assertNull($post->source_type);
        $this->assertNull($post->source_id);
        $this->assertTrue($post->data['legal_check']);
        $this->assertSame('isv', $post->data['topic']);
        $this->assertSame([], $post->data['items']);
        $this->assertNotSame('', $post->title);
    }

    public function test_store_sem_tema_exige_titulo(): void
    {
        $this->admin()->postJson(route('admin.marketing.content.store', 'educativo'), [
            'template' => 'dica-rapida', 'format' => 'feed_square',
        ])->assertUnprocessable()->assertJsonValidationErrors('title');
    }

    public function test_tipos_de_texto_so_aceitam_templates_de_texto(): void
    {
        $this->admin()->postJson(route('admin.marketing.content.store', 'educativo'), [
            'title' => 'X', 'template' => 'premium', 'format' => 'feed_square',
        ])->assertUnprocessable()->assertJsonValidationErrors('template');

        $this->admin()->postJson(route('admin.marketing.content.store', 'servico'), [
            'title' => 'X', 'template' => 'dica-rapida', 'format' => 'feed_square',
        ])->assertCreated();
    }

    public function test_post_vazio_nao_gera_blocos_nem_null(): void
    {
        foreach (self::TEMPLATES_TEXTO as $key) {
            $tpl = TemplateRegistry::get($key);
            foreach ($tpl->formats as $f) {
                $r = PostRenderer::render($this->novoPost($key, [], $key === 'servico' ? 'servico' : 'educativo', $f->value));
                $this->assertStringNotContainsString('null', strtolower($r['html']), "{$key}/{$f->value}");
                $this->assertStringNotContainsString('mk-item', $r['html'], "{$key}/{$f->value}");
                $this->assertStringContainsString('Como importar', $r['html'], "{$key}/{$f->value}");
            }
        }
    }

    public function test_preview_com_itens_mostra_o_texto_escapado(): void
    {
        $post = $this->novoPost('passo-a-passo');

        $r = $this->admin()->postJson(route('admin.marketing.preview', $post), [
            'overrides' => ['items' => [['a' => 'Escolher <b>carro</b>', 'b' => 'Com calma'], ['a' => '', 'b' => '']]],
        ])->assertOk();

        $html = $r->json('html');
        $this->assertStringContainsString('Escolher &lt;b&gt;carro&lt;/b&gt;', $html);
        $this->assertStringContainsString('Com calma', $html);
        $this->assertSame(1, substr_count($html, 'class="mk-item'));
    }

    public function test_carrossel_educativo_cria_um_slide_por_item(): void
    {
        $items = array_map(fn ($i) => ['a' => "Ponto {$i}", 'b' => "Detalhe {$i}"], range(1, 3));
        foreach (['educativo-carrossel', 'mito-verdade'] as $key) {
            $r = PostRenderer::render($this->novoPost($key, ['items' => $items]));
            $this->assertSame(5, $r['slides'], $key); // capa + 3 + CTA
            $this->assertSame(5, substr_count($r['html'], '<section class="mk-slide'), $key);
        }
    }

    public function test_itens_vazios_sao_descartados_e_limitados(): void
    {
        $out = \App\Marketing\TextPostData::fromInput([
            'title' => ' Olá ', 'items' => array_merge([['a' => '', 'b' => ' ']], array_map(fn ($i) => ['a' => "A{$i}", 'b' => ''], range(1, 12))),
        ]);
        $this->assertSame('Olá', $out['title']);
        $this->assertCount(8, $out['items']);
        $this->assertFalse($out['legal_check']);
    }

    public function test_legal_check_aparece_no_editor(): void
    {
        $fiscal = $this->novoPost('dica-rapida', ['legal_check' => true, 'topic' => 'isv']);
        $this->admin()->get(route('admin.marketing.editor', $fiscal))
            ->assertOk()->assertSee('Verificar a legislação atual antes de publicar');

        $neutro = $this->novoPost('dica-rapida');
        $this->admin()->get(route('admin.marketing.editor', $neutro))
            ->assertOk()->assertDontSee('Verificar a legislação atual antes de publicar');
    }

    public function test_guardar_itens_nas_edicoes_nao_altera_o_instantaneo(): void
    {
        $post = $this->novoPost('passo-a-passo');
        $this->admin()->putJson(route('admin.marketing.update', $post), [
            'template' => 'passo-a-passo', 'format' => 'feed_vertical',
            'overrides' => ['items' => [['a' => 'Passo 1', 'b' => 'Detalhe']]],
        ])->assertOk();

        $post->refresh();
        $this->assertSame([], $post->data['items']);
        $this->assertSame([['a' => 'Passo 1', 'b' => 'Detalhe']], $post->config['overrides']['items']);
    }

    public function test_cada_template_de_texto_desenha_em_todos_os_formatos_com_conteudo(): void
    {
        $items = array_map(fn ($i) => ['a' => "Ponto {$i}", 'b' => "Detalhe {$i}"], range(1, 8));
        foreach (self::TEMPLATES_TEXTO as $key) {
            $tpl = TemplateRegistry::get($key);
            $type = $key === 'servico' ? 'servico' : 'educativo';
            foreach ($tpl->formats as $f) {
                $r = PostRenderer::render($this->novoPost($key, ['items' => $items, 'subtitle' => 'Sub'], $type, $f->value));
                $this->assertStringContainsString("width:{$f->width()}px;height:{$f->height()}px", $r['html'], "{$key}/{$f->value}");
                $this->assertStringContainsString('Ponto 1', $r['html'], "{$key}/{$f->value}");
                $this->assertGreaterThanOrEqual($tpl->minSlides, $r['slides'], $key);
                $this->assertLessThanOrEqual($tpl->maxSlides, $r['slides'], $key);
                $this->assertSame($r['slides'], substr_count($r['html'], '<section class="mk-slide'), $key);
            }
        }
    }

    public function test_mito_verdade_so_desenha_o_lado_preenchido(): void
    {
        $r = PostRenderer::render($this->novoPost('mito-verdade', ['items' => [['a' => 'Só mito', 'b' => '']]]));
        $this->assertStringContainsString('Só mito', $r['html']);
        $this->assertStringNotContainsString('Verdade', $r['html']);
    }

    public function test_vertical_com_muitos_itens_usa_modo_compacto(): void
    {
        $items = array_map(fn ($i) => ['a' => "P{$i}", 'b' => 'd'], range(1, 6));
        $this->assertStringContainsString('mk-dense', PostRenderer::render($this->novoPost('passo-a-passo', ['items' => $items], 'educativo', 'feed_vertical'))['html']);
    }
}
