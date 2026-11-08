<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Vehicle;
use App\Services\MovementService;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MovimentosSaldoContabilizaveisTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private function mov(string $tipo, float $valor, array $extra = []): Expense
    {
        return Expense::create($extra + [
            'movement_type' => $tipo, 'title' => 't', 'amount' => $valor, 'amount_gross' => $valor,
            'expense_date' => now()->toDateString(), 'status' => 'paid',
        ]);
    }

    public function test_saldo_ignora_lancamentos_do_legado_e_cancelados(): void
    {
        $this->mov('income', 1000);
        $this->mov('expense', 300);
        $this->mov('expense', 26500, ['source_type' => Vehicle::class, 'source_id' => 2]); // duplicado legado
        $this->mov('expense', 500, ['status' => 'cancelled']);
        $this->mov('expense', 65, ['status' => 'pending']);

        $s = app(MovementService::class)->summary((string) now()->year, false);

        $this->assertSame(300.0 + 65.0, $s['totalExpenses']);
        $this->assertSame(1000.0, $s['totalIncome']);
        $this->assertEqualsWithDelta(MovementService::INITIAL_BALANCE + 1000 - 300 - 65, $s['saldo'], 0.001);
        $this->assertEqualsWithDelta(MovementService::INITIAL_BALANCE + 1000 - 300, $s['saldoReal'], 0.001);
    }

    public function test_listagem_esconde_o_legado_mas_mostra_cancelados(): void
    {
        $this->mov('expense', 10, ['source_type' => Vehicle::class, 'source_id' => 2]);
        $c = $this->mov('expense', 20, ['status' => 'cancelled']);

        $ids = Expense::filtrar(['year' => 'all'])->pluck('id')->all();

        $this->assertSame([$c->id], $ids);
    }
}
