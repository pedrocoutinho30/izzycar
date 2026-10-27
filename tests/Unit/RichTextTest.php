<?php

namespace Tests\Unit;

use App\Support\RichText;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RichTextTest extends TestCase
{
    private const GOLD = 'var(--accent-color, #92642D)';

    public static function reds(): array
    {
        return [
            'hex 6 dígitos bordô' => ['#6e0707'],
            'hex 6 dígitos' => ['#990000'],
            'hex 3 dígitos' => ['#900'],
            'maiúsculas' => ['#CF1C1C'],
            'vermelho claro' => ['#ff8080'],
            'bootstrap danger' => ['#dc3545'],
            'rgb com vírgulas' => ['rgb(153, 0, 0)'],
            'rgb sem espaços' => ['rgb(220,53,69)'],
            'rgba' => ['rgba(255, 0, 0, 0.8)'],
            'rgb moderno' => ['rgb(153 0 0)'],
            'rgb em percentagem' => ['rgb(60%, 0%, 0%)'],
            'hsl' => ['hsl(0, 100%, 30%)'],
            'nome red' => ['red'],
            'nome darkred' => ['DarkRed'],
            'nome crimson' => ['crimson'],
        ];
    }

    public static function notReds(): array
    {
        return [
            'preto' => ['#000'],
            'branco' => ['#ffffff'],
            'cinza' => ['#666666'],
            'dourado da marca' => ['#B59151'],
            'dourado escuro' => ['#92642D'],
            'laranja' => ['#ff6b35'],
            'rosa' => ['#e91e63'],
            'verde' => ['rgb(40, 167, 69)'],
            'azul' => ['blue'],
            'castanho acinzentado' => ['#7a5c5c'],
            'variável css' => ['var(--accent-color)'],
            'herdar' => ['inherit'],
            'vazio' => [''],
        ];
    }

    #[DataProvider('reds')]
    public function test_cores_vermelhas_do_texto_passam_a_dourado(string $color): void
    {
        $html = '<p>Olá <span style="color: ' . $color . ';">mundo</span></p>';

        $this->assertSame('<p>Olá <span style="color: ' . self::GOLD . ';">mundo</span></p>', RichText::display($html));
    }

    #[DataProvider('notReds')]
    public function test_outras_cores_ficam_como_estao(string $color): void
    {
        $html = '<span style="color: ' . $color . ';">x</span>';

        $this->assertSame($html, RichText::display($html));
    }

    public function test_so_a_cor_do_texto_e_trocada_e_o_resto_do_estilo_fica(): void
    {
        $html = '<span style="font-size:14px; color:#990000; background-color:#990000; border-color: red; text-decoration-color:#900">x</span>';

        $this->assertSame(
            '<span style="font-size:14px; color:' . self::GOLD . '; background-color:#990000; border-color: red; text-decoration-color:#900">x</span>',
            RichText::display($html)
        );
    }

    public function test_important_e_espacos_sao_preservados(): void
    {
        $this->assertSame(
            '<b style="color : ' . self::GOLD . ' !important">x</b>',
            RichText::display('<b style="color : #990000 !important">x</b>')
        );
        $this->assertSame(
            '<b style="COLOR:' . self::GOLD . '">x</b>',
            RichText::display('<b style="COLOR:#990000">x</b>')
        );
    }

    public function test_aspas_simples_no_atributo_style(): void
    {
        $this->assertSame(
            "<span style='color:" . self::GOLD . "'>x</span>",
            RichText::display("<span style='color:#990000'>x</span>")
        );
    }

    public function test_tag_font_usa_hex_literal_porque_o_atributo_nao_aceita_var(): void
    {
        $this->assertSame('<font color="#92642D">x</font>', RichText::display('<font color="#990000">x</font>'));
        $this->assertSame("<font face='Arial' color='#92642D'>x</font>", RichText::display("<font face='Arial' color='red'>x</font>"));
        $this->assertSame('<font color="green">x</font>', RichText::display('<font color="green">x</font>'));
    }

    public function test_varios_vermelhos_e_outras_cores_no_mesmo_texto(): void
    {
        $html = '<p style="color:#900">a</p><p style="color:#2e7d32">b</p><span style="color: rgb(207, 28, 28)">c</span>';

        $this->assertSame(
            '<p style="color:' . self::GOLD . '">a</p><p style="color:#2e7d32">b</p><span style="color: ' . self::GOLD . '">c</span>',
            RichText::display($html)
        );
    }

    public function test_texto_visivel_que_menciona_uma_cor_sem_ser_atributo_fica_igual(): void
    {
        $html = '<p>Use color: #990000 para vermelho</p>';

        $this->assertSame($html, RichText::display($html));
    }

    public function test_vazio_e_nulo(): void
    {
        $this->assertSame('', RichText::display(null));
        $this->assertSame('', RichText::display(''));
        $this->assertSame('<p>sem cor</p>', RichText::display('<p>sem cor</p>'));
    }
}
