<?php

namespace Tests\Unit;

use App\Support\DecimalInput;
use PHPUnit\Framework\TestCase;

class DecimalInputTest extends TestCase
{
    public function test_normaliza_formatos_portugueses(): void
    {
        $casos = [
            '800,50' => '800.50', '800.50' => '800.50', '1.200,5' => '1200.5', '1.200.500,25' => '1200500.25',
            ' 2 300,25 ' => '2300.25', "1\u{00A0}200,5" => '1200.5', '800' => '800', '1.200.500' => '1200500', ',5' => '.5',
        ];
        foreach ($casos as $entrada => $esperado) {
            $this->assertSame($esperado, DecimalInput::normalize($entrada), (string) $entrada);
        }
    }

    public function test_vazio_fica_nulo_e_texto_invalido_fica_como_esta(): void
    {
        $this->assertNull(DecimalInput::normalize(null));
        $this->assertNull(DecimalInput::normalize('  '));
        foreach (['abc', '1,2,3', '12€'] as $invalido) {
            $this->assertSame($invalido, DecimalInput::normalize($invalido));
        }
    }
}
