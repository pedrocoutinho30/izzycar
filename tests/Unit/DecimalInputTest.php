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

    public function test_um_ponto_seguido_de_tres_digitos_e_milhares(): void
    {
        $casos = ['20.000' => '20000', '1.500' => '1500', '999.999' => '999999', '12.345' => '12345'];
        foreach ($casos as $entrada => $esperado) {
            $this->assertSame($esperado, DecimalInput::normalize($entrada), $entrada);
        }
    }

    public function test_um_ponto_com_outro_numero_de_digitos_continua_decimal(): void
    {
        $casos = ['1.5' => '1.5', '20000.00' => '20000.00', '1200.50' => '1200.50', '0.125' => '0.125', '1234.567' => '1234.567', '1.25' => '1.25'];
        foreach ($casos as $entrada => $esperado) {
            $this->assertSame($esperado, DecimalInput::normalize($entrada), $entrada);
        }
    }

    public function test_valores_da_bd_com_virgula_decimal_voltam_exactos(): void
    {
        // O pré-preenchimento do formulário troca '.' por ',': nunca gera "N.NNN".
        foreach (['0.5', '1.5', '1.123', '1500', '20000', '1200.50', '1999.99', '20000.00'] as $bd) {
            $mostrado = str_replace('.', ',', $bd);
            $this->assertSame((float) $bd, (float) DecimalInput::normalize($mostrado), $bd);
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
