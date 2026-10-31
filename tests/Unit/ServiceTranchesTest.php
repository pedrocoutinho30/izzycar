<?php

namespace Tests\Unit;

use App\Services\ServiceTranches;
use PHPUnit\Framework\TestCase;

class ServiceTranchesTest extends TestCase
{
    public function test_por_omissao_50_50(): void
    {
        $this->assertSame(
            ['tranche1' => 500.0, 'tranche2' => 500.0, 'pct1' => 50.0, 'pct2' => 50.0],
            ServiceTranches::split(1000.0, [])
        );
    }

    public function test_pct2_e_sempre_o_resto_de_pct1(): void
    {
        // final_payment incoerente (50) não pode mostrar 60/50 (LOG-015).
        $r = ServiceTranches::split(100.0, ['init_payment' => '60', 'final_payment' => '50']);

        $this->assertSame(60.0, $r['tranche1']);
        $this->assertSame(40.0, $r['tranche2']);
        $this->assertSame(60.0, $r['pct1']);
        $this->assertSame(40.0, $r['pct2']);
    }

    public function test_aceita_percentagem_com_simbolo_e_virgula(): void
    {
        $r = ServiceTranches::split(333.33, ['init_payment' => '33,5%']);

        $this->assertSame(33.5, $r['pct1']);
        $this->assertSame(66.5, $r['pct2']);
        $this->assertSame(round(333.33, 2), round($r['tranche1'] + $r['tranche2'], 2));
    }

    public function test_valor_invalido_volta_aos_50(): void
    {
        foreach (['0', '150', 'abc', ''] as $valor) {
            $r = ServiceTranches::split(200.0, ['init_payment' => $valor]);
            $this->assertSame([50.0, 50.0], [$r['pct1'], $r['pct2']], "init_payment={$valor}");
        }
    }

    public function test_100_por_cento_na_primeira(): void
    {
        $r = ServiceTranches::split(80.0, ['init_payment' => '100']);

        $this->assertSame([80.0, 0.0, 100.0, 0.0], [$r['tranche1'], $r['tranche2'], $r['pct1'], $r['pct2']]);
    }
}
