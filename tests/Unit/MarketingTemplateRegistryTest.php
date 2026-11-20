<?php

namespace Tests\Unit;

use App\Enums\MarketingFormat;
use App\Enums\MarketingPostType;
use App\Marketing\TemplateRegistry;
use PHPUnit\Framework\TestCase;

class MarketingTemplateRegistryTest extends TestCase
{
    public function test_tem_as_chaves_do_plano(): void
    {
        $this->assertEqualsCanonicalizing(
            ['premium', 'comercial', 'minimalista', 'equipamento', 'carrossel', 'novo-stock', 'vendido', 'recomendacao', 'dica-rapida', 'mito-verdade', 'pergunta-frequente', 'passo-a-passo', 'erros-comuns', 'educativo-carrossel', 'servico', 'testemunho', 'importacao'],
            array_keys(TemplateRegistry::all())
        );
    }

    public function test_get_devolve_definicao_e_falha_se_nao_existe(): void
    {
        $this->assertSame('premium', TemplateRegistry::get('premium')->key);
        $this->expectException(\InvalidArgumentException::class);
        TemplateRegistry::get('nao-existe');
    }

    public function test_for_type_filtra(): void
    {
        $veiculo = TemplateRegistry::forType(MarketingPostType::Veiculo);
        $this->assertArrayHasKey('vendido', $veiculo);
        $this->assertArrayNotHasKey('recomendacao', $veiculo);

        $rec = TemplateRegistry::forType(MarketingPostType::Recomendacao);
        $this->assertArrayHasKey('recomendacao', $rec);
        $this->assertArrayNotHasKey('vendido', $rec);
        $this->assertArrayNotHasKey('novo-stock', $rec);
    }

    public function test_cada_template_tem_formatos_e_view(): void
    {
        foreach (TemplateRegistry::all() as $key => $t) {
            $this->assertNotEmpty($t->formats, $key);
            $this->assertContainsOnlyInstancesOf(MarketingFormat::class, $t->formats);
            $this->assertSame('admin.marketing.templates.' . $key, $t->view);
        }
        $this->assertTrue(TemplateRegistry::get('carrossel')->minSlides >= 2);
    }

    public function test_formato_suportado(): void
    {
        $this->assertTrue(TemplateRegistry::get('premium')->supports(MarketingFormat::Story));
        $this->assertFalse(TemplateRegistry::get('vendido')->supports(MarketingFormat::Story));
    }
}
