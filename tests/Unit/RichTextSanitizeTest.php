<?php

namespace Tests\Unit;

use App\Support\RichText;
use PHPUnit\Framework\TestCase;

class RichTextSanitizeTest extends TestCase
{
    public function test_remove_script_e_handlers(): void
    {
        $out = RichText::display('<p onclick="x()">Olá</p><script>alert(1)</script><img src="https://x.pt/a.jpg" onerror="alert(1)" alt="a">');

        $this->assertStringNotContainsString('script', $out);
        $this->assertStringNotContainsString('alert', $out);
        $this->assertStringNotContainsString('onerror', $out);
        $this->assertStringNotContainsString('onclick', $out);
        $this->assertStringContainsString('<p>Olá</p>', $out);
        $this->assertStringContainsString('src="https://x.pt/a.jpg"', $out);
    }

    public function test_remove_javascript_uri_e_iframe(): void
    {
        $out = RichText::display('<a href="javascript:alert(1)">x</a><iframe src="https://evil.test"></iframe><a href="data:text/html;base64,AAA">y</a>');

        $this->assertStringNotContainsString('javascript:', $out);
        $this->assertStringNotContainsString('iframe', $out);
        $this->assertStringNotContainsString('data:', $out);
    }

    public function test_mantem_formatacao_normal(): void
    {
        $out = RichText::display('<h2>Título</h2><p><strong>negrito</strong> <em>it</em></p><ul><li>a</li></ul><blockquote>q</blockquote><a href="https://izzycar.pt" target="_blank" title="t">l</a>');

        foreach (['<h2>Título</h2>', '<strong>negrito</strong>', '<em>it</em>', '<li>a</li>', '<blockquote>q</blockquote>', 'href="https://izzycar.pt"', 'target="_blank"'] as $esperado) {
            $this->assertStringContainsString($esperado, $out);
        }
        $this->assertStringContainsString('noopener', $out);
    }

    public function test_cores_vermelhas_continuam_a_passar_a_dourado_depois_de_sanitizar(): void
    {
        $out = RichText::display('<p>Olá <span style="color: #990000; background-color: #eeeeee;">mundo</span></p>');

        $this->assertStringContainsString('var(--accent-color, #92642D)', $out);
        $this->assertStringContainsString('background-color', $out);
    }

    public function test_estilos_perigosos_sao_removidos(): void
    {
        $out = RichText::display('<span style="color:red;position:fixed;top:0;background:url(javascript:x)">x</span>');

        $this->assertStringNotContainsString('position', $out);
        $this->assertStringNotContainsString('url(', $out);
    }
}
