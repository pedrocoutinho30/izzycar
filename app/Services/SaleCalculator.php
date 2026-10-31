<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Single source of truth for sale margin calculations.
 * Used by both SaleV2Controller and V3VehicleController.
 */
class SaleCalculator
{
    /**
     * Compute all sale metrics.
     *
     * @param  float    $salePrice       Gross sale price (with VAT)
     * @param  string   $vatType         'geral_23'|'geral_13'|'geral_6'|'margem'|'isento'
     * @param  float    $purchasePrice   Net purchase price (sem IVA)
     * @param  float    $purchaseVatPaid VAT paid on purchase
     * @param  iterable $expenses        Expense objects with amount_gross (or amount) and vat_rate
     * @param  float    $marginVatRate   Taxa usada para extrair o IVA no regime de margem (23 por omissão)
     * @return array
     *
     * @throws InvalidArgumentException se $vatType não for um dos valores conhecidos (antes era tratado como isento)
     */
    public static function compute(
        float    $salePrice,
        string   $vatType,
        float    $purchasePrice,
        float    $purchaseVatPaid,
        iterable $expenses,
        float    $marginVatRate = 23.0
    ): array {
        $vatRateMap = ['geral_23' => 23.0, 'geral_13' => 13.0, 'geral_6' => 6.0];

        if ($vatType !== 'margem' && $vatType !== 'isento' && ! isset($vatRateMap[$vatType])) {
            throw new InvalidArgumentException("Tipo de IVA desconhecido: «{$vatType}».");
        }

        $vatRateVal  = $vatRateMap[$vatType] ?? 0.0;
        $vatRateSale = $vatRateVal / 100;

        // ── VENDA ────────────────────────────────────────────────────────
        // Valores monetários arredondados a cêntimos em cada passo, para as
        // parcelas somarem sempre o total.
        if ($vatType === 'margem') {
            $sellVat  = round(max(0.0, ($salePrice - $purchasePrice) * $marginVatRate / (100 + $marginVatRate)), 2);
            $sellBase = round($salePrice - $sellVat, 2);
        } else {
            $sellBase = $vatRateSale > 0 ? round($salePrice / (1 + $vatRateSale), 2) : round($salePrice, 2);
            $sellVat  = round($salePrice - $sellBase, 2);
        }

        // ── DESPESAS ─────────────────────────────────────────────────────
        $expenseBase        = 0.0;
        $expenseVat         = 0.0;
        $totalExpensesGross = 0.0;

        foreach ($expenses as $expense) {
            $rate  = ((float) str_replace('%', '', $expense->vat_rate ?? '0')) / 100;
            $gross = (float) ($expense->amount_gross ?? $expense->amount ?? 0);
            $base  = $rate > 0 ? round($gross / (1 + $rate), 2) : round($gross, 2);
            $vat   = round($gross - $base, 2);
            $expenseBase        += $base;
            $expenseVat         += $vat;
            $totalExpensesGross += $gross;
        }

        // ── MARGENS ──────────────────────────────────────────────────────
        // net_margin  = lucro real (sem IVA)
        $net_margin   = $sellBase - $purchasePrice - $expenseBase;
        // gross_margin = lucro cash (com IVA incluído)
        $gross_margin = $salePrice - ($purchasePrice + $purchaseVatPaid) - $totalExpensesGross;

        // ── IVA ──────────────────────────────────────────────────────────
        $vat_paid        = $sellVat - $purchaseVatPaid - $expenseVat;
        $vat_settle_sale = $sellVat;

        // ── CUSTOS ───────────────────────────────────────────────────────
        $totalCost = $purchasePrice + $totalExpensesGross;

        // ── RENTABILIDADE ─────────────────────────────────────────────────
        $net_profitability   = $salePrice > 0 ? ($net_margin   / $salePrice) * 100 : 0;
        $gross_profitability = $salePrice > 0 ? ($gross_margin / $salePrice) * 100 : 0;

        return [
            'vat_rate'            => $vatRateVal > 0 ? (string) (int) $vatRateVal : '0',
            'net_revenue'         => round($sellBase, 2),
            'vat_settle_sale'     => round($vat_settle_sale, 2),
            'vat_paid'            => round($vat_paid, 2),
            'vat_recoverable'     => round($purchaseVatPaid, 2),
            'total_cost'          => round($totalCost, 2),
            'expenses_total'      => round($totalExpensesGross, 2),
            'gross_margin'        => round($gross_margin, 2),
            'net_margin'          => round($net_margin, 2),
            'gross_profitability' => round($gross_profitability, 2),
            'net_profitability'   => round($net_profitability, 2),
        ];
    }

    /**
     * Normalise V2's vat_rate string value (e.g. "23", "Sem IVA") to a vatType key.
     */
    public static function normaliseVatType(?string $vatRate): string
    {
        $normalised = match ((string) $vatRate) {
            '23'     => 'geral_23',
            '13'     => 'geral_13',
            '6'      => 'geral_6',
            'Margem' => 'margem',
            '', '0', 'Sem IVA', 'Isento', 'isento' => 'isento',
            default  => null,
        };

        // Valor que não se reconhece: mantém-se "isento" (as chamadas existentes
        // não rebentam), mas fica registado para se detetar um IVA mal escrito.
        if ($normalised === null) {
            Log::warning('SaleCalculator: taxa de IVA desconhecida tratada como isento', ['vat_rate' => (string) $vatRate]);

            return 'isento';
        }

        return $normalised;
    }
}
