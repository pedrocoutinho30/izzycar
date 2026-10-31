<?php

namespace Tests\Unit;

use App\Support\CsvSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CsvSanitizerTest extends TestCase
{
    public static function perigosos(): array
    {
        return [
            'igual' => ['=HYPERLINK("http://x.pt")'],
            'mais' => ['+SUM(A1)'],
            'menos' => ['-2+3'],
            'arroba' => ['@SUM(1)'],
            'tab' => ["\t=1"],
            'cr' => ["\r=1"],
            'espaço antes' => [' =1+1'],
        ];
    }

    #[DataProvider('perigosos')]
    public function test_prefixa_apostrofo(string $valor): void
    {
        $this->assertSame("'" . $valor, CsvSanitizer::cell($valor));
    }

    public function test_deixa_numeros_telefones_e_texto_normal(): void
    {
        foreach (['-1', '-12,50', '+3.5', '+351 912 345 678', '912345678', 'Maria Silva', 'a=b', '', null, 12, 1.5] as $v) {
            $this->assertSame($v, CsvSanitizer::cell($v));
        }
    }

    public function test_row_aplica_a_cada_celula(): void
    {
        $this->assertSame(["'=1", 'ok', 5], CsvSanitizer::row(['=1', 'ok', 5]));
    }
}
