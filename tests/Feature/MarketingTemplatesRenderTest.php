<?php

namespace Tests\Feature;

use App\Enums\MarketingPostType;
use App\Marketing\PostRenderer;
use App\Marketing\TemplateRegistry;
use App\Models\MarketingPost;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Cada template desenha-se em todos os formatos que suporta, com e sem fotos/preço. */
class MarketingTemplatesRenderTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private function novoPost(MarketingPostType $type, string $template, string $format, bool $completo): MarketingPost
    {
        $data = ['brand' => 'Audi', 'model' => 'A3', 'status' => 'em_stock'];
        if ($completo) {
            $data += [
                'version' => '35 TFSI', 'year' => 2021, 'kilometers' => 42000, 'fuel' => 'Gasolina', 'power' => 150,
                'price' => 27500.0, 'savings' => 3100.0, 'transmission' => 'Manual',
                'equipment' => ['Bluetooth', 'Teto panorâmico', 'Câmara traseira', 'Cruise Control', 'LED'],
                'photos' => array_map(fn ($i) => ['id' => $i, 'path' => "marketing-posts/1/{$i}.jpg", 'focal_x' => null, 'focal_y' => null], range(1, 7)),
            ];
        } else {
            $data['photos'] = [];
        }

        return MarketingPost::create([
            'type' => $type, 'template' => $template, 'format' => $format, 'status' => 'rascunho',
            'data' => $data, 'config' => [],
        ]);
    }

    public function test_todos_os_templates_desenham_em_todos_os_formatos_suportados(): void
    {
        foreach (TemplateRegistry::all() as $key => $tpl) {
            $this->assertTrue(view()->exists($tpl->view), "falta a view de {$key}");
            foreach (array_filter($tpl->types, fn ($t) => in_array($t, [MarketingPostType::Veiculo, MarketingPostType::Recomendacao], true)) as $type) {
                foreach ($tpl->formats as $format) {
                    foreach ([true, false] as $completo) {
                        $r = PostRenderer::render($this->novoPost($type, $key, $format->value, $completo));
                        $ctx = "{$key}/{$type->value}/{$format->value}/" . ($completo ? 'completo' : 'minimo');

                        $this->assertSame($format->height(), $r['height'], $ctx);
                        $this->assertStringContainsString("width:{$format->width()}px;height:{$format->height()}px", $r['html'], $ctx);
                        $this->assertStringContainsString('Audi A3', $r['html'], $ctx);
                        $this->assertStringNotContainsString('null', strtolower($r['html']), $ctx);
                        $this->assertGreaterThanOrEqual($tpl->minSlides, $r['slides'], $ctx);
                        $this->assertLessThanOrEqual($tpl->maxSlides, $r['slides'], $ctx);
                        $this->assertSame($r['slides'], substr_count($r['html'], '<section class="mk-slide'), $ctx);
                        if (! $completo) {
                            $this->assertStringNotContainsString('€', $r['html'], $ctx);
                        }
                    }
                }
            }
        }
    }

    public function test_preco_e_poupanca_aparecem_quando_existem(): void
    {
        $html = PostRenderer::render($this->novoPost(MarketingPostType::Recomendacao, 'recomendacao', 'feed_vertical', true))['html'];

        $this->assertStringContainsString('27.500 €', $html);
        $this->assertStringContainsString('3.100 €', $html);
        $this->assertStringContainsString('Poupança', $html);
    }

    public function test_sem_poupanca_nao_ha_bloco_de_poupanca(): void
    {
        $post = $this->novoPost(MarketingPostType::Recomendacao, 'recomendacao', 'feed_vertical', true);
        $data = $post->data;
        unset($data['savings']);
        $post->update(['data' => $data]);

        $this->assertStringNotContainsString('Poupança', PostRenderer::render($post->fresh())['html']);
    }

    public function test_carrossel_cria_capa_fotos_equipamento_caracteristicas_e_cta(): void
    {
        $r = PostRenderer::render($this->novoPost(MarketingPostType::Veiculo, 'carrossel', 'feed_vertical', true));

        $this->assertStringContainsString('Equipamento', $r['html']);
        $this->assertStringContainsString('Características', $r['html']);
        $this->assertStringContainsString('izzycar.pt', $r['html']);
        $this->assertLessThanOrEqual(8, $r['slides']);
    }

    public function test_foto_so_aparece_se_for_escolhida(): void
    {
        $post = $this->novoPost(MarketingPostType::Veiculo, 'premium', 'feed_vertical', true);
        $post->update(['config' => ['photo_order' => [2]]]);

        $html = PostRenderer::render($post->fresh())['html'];

        $this->assertStringContainsString('/fotos/3', $html);
        $this->assertStringNotContainsString('/fotos/1', str_replace('/fotos/10', '', $html));
    }
}
