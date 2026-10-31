<?php

namespace Tests\Unit;

use App\Services\SaleCalculator;
use InvalidArgumentException;
use Tests\TestCase;

class SaleCalculatorTest extends TestCase
{
    private function exp(float $gross, string $rate): object
    {
        return (object) ['amount_gross' => $gross, 'amount' => $gross, 'vat_rate' => $rate];
    }

    public function test_geral_23(): void
    {
        $m = SaleCalculator::compute(12300.0, 'geral_23', 8000.0, 0.0, []);

        $this->assertSame('23', $m['vat_rate']);
        $this->assertSame(10000.0, $m['net_revenue']);
        $this->assertSame(2300.0, $m['vat_settle_sale']);
        $this->assertSame(2300.0, $m['vat_paid']);
        $this->assertSame(2000.0, $m['net_margin']);
        $this->assertSame(4300.0, $m['gross_margin']);
        $this->assertSame(8000.0, $m['total_cost']);
    }

    public function test_taxas_13_e_6(): void
    {
        $this->assertSame(1000.0, SaleCalculator::compute(1130.0, 'geral_13', 0.0, 0.0, [])['net_revenue']);
        $this->assertSame(130.0, SaleCalculator::compute(1130.0, 'geral_13', 0.0, 0.0, [])['vat_settle_sale']);
        $this->assertSame(60.0, SaleCalculator::compute(1060.0, 'geral_6', 0.0, 0.0, [])['vat_settle_sale']);
    }

    public function test_isento_nao_tem_iva_na_venda(): void
    {
        $m = SaleCalculator::compute(5000.0, 'isento', 4000.0, 0.0, []);

        $this->assertSame('0', $m['vat_rate']);
        $this->assertSame(5000.0, $m['net_revenue']);
        $this->assertSame(0.0, $m['vat_settle_sale']);
        $this->assertSame(1000.0, $m['net_margin']);
    }

    public function test_margem_extrai_o_iva_so_da_margem(): void
    {
        $m = SaleCalculator::compute(12300.0, 'margem', 10000.0, 0.0, []);

        // (12300 - 10000) * 23 / 123 = 430,08
        $this->assertSame(430.08, $m['vat_settle_sale']);
        $this->assertSame(11869.92, $m['net_revenue']);
    }

    public function test_margem_com_taxa_parametrizada(): void
    {
        $m = SaleCalculator::compute(1060.0, 'margem', 1000.0, 0.0, [], 6.0);

        $this->assertSame(3.4, $m['vat_settle_sale']); // 60 * 6 / 106 = 3,396
    }

    public function test_margem_com_venda_abaixo_do_custo_nao_tem_iva_negativo(): void
    {
        $m = SaleCalculator::compute(8000.0, 'margem', 10000.0, 0.0, []);

        $this->assertSame(0.0, $m['vat_settle_sale']);
        $this->assertSame(8000.0, $m['net_revenue']);
        $this->assertSame(-2000.0, $m['net_margin']);
        $this->assertSame(-2000.0, $m['gross_margin']);
    }

    public function test_venda_abaixo_do_custo_com_iva_geral_da_margem_negativa(): void
    {
        $m = SaleCalculator::compute(6150.0, 'geral_23', 8000.0, 0.0, [$this->exp(123.0, '23')]);

        $this->assertSame(-3100.0, $m['net_margin']);
        $this->assertSame(-1973.0, $m['gross_margin']);
        $this->assertSame(-50.41, $m['net_profitability']); // -3100 / 6150
    }

    public function test_despesas_a_23_e_6_somam_as_parcelas_ao_cêntimo(): void
    {
        $expenses = [];
        for ($i = 0; $i < 7; $i++) {
            $expenses[] = $this->exp(10.01, '23');
        }
        $expenses[] = $this->exp(33.33, '6%');

        $m = SaleCalculator::compute(20000.0, 'geral_23', 10000.0, 500.0, $expenses);

        $gross = round(7 * 10.01 + 33.33, 2);
        $this->assertSame($gross, $m['expenses_total']);
        $this->assertSame(round(10000.0 + $gross, 2), $m['total_cost']);
        // Cada linha arredondada: base 8,14 + IVA 1,87 = 10,01 (x7); base 31,44 + IVA 1,89 = 33,33.
        $this->assertSame(round(16260.16 - 10000.0 - (7 * 8.14 + 31.44), 2), $m['net_margin']);
    }

    public function test_despesa_sem_taxa_ou_com_amount_em_falta(): void
    {
        $m = SaleCalculator::compute(1000.0, 'isento', 0.0, 0.0, [(object) ['amount' => 50.0]]);

        $this->assertSame(50.0, $m['expenses_total']);
        $this->assertSame(950.0, $m['net_margin']);
    }

    public function test_tipo_de_iva_desconhecido_e_rejeitado(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SaleCalculator::compute(1000.0, 'geral_25', 0.0, 0.0, []);
    }

    public function test_normalise_conhece_os_valores_do_v2_e_nao_rebenta_com_o_resto(): void
    {
        $this->assertSame('geral_23', SaleCalculator::normaliseVatType('23'));
        $this->assertSame('geral_13', SaleCalculator::normaliseVatType('13'));
        $this->assertSame('geral_6', SaleCalculator::normaliseVatType('6'));
        $this->assertSame('margem', SaleCalculator::normaliseVatType('Margem'));
        $this->assertSame('isento', SaleCalculator::normaliseVatType('Sem IVA'));
        $this->assertSame('isento', SaleCalculator::normaliseVatType(null));

        \Illuminate\Support\Facades\Log::spy();
        $this->assertSame('isento', SaleCalculator::normaliseVatType('25'));
        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')->once();
    }
}
