<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Services\MovementService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** LOG-008 (saldos e pendentes) e LOG-017 (anexos em transação). */
class MovementServiceSummaryTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        DB::connection()->getPdo()->sqliteCreateFunction('YEAR', fn ($d) => $d ? (int) substr($d, 0, 4) : null);
    }

    private function mov(array $attributes = []): Expense
    {
        return Expense::create($attributes + [
            'movement_type' => 'expense', 'category' => 'repair', 'title' => 'Manual',
            'amount' => 100, 'amount_gross' => 100, 'expense_date' => '2026-05-10', 'status' => 'paid',
        ]);
    }

    public function test_so_contam_movimentos_desde_a_data_de_inicio(): void
    {
        $this->mov(['movement_type' => 'income', 'amount_gross' => 1000, 'expense_date' => '2025-03-01']);
        $this->mov(['movement_type' => 'expense', 'amount_gross' => 300, 'expense_date' => '2025-12-31']);
        $this->mov(['movement_type' => 'income', 'amount_gross' => 500, 'expense_date' => '2026-01-01']);
        $this->mov(['movement_type' => 'expense', 'amount_gross' => 40, 'expense_date' => '2026-05-01', 'status' => 'pending']);

        $s = (new MovementService)->summary('2026', false);

        // Anterior a 01/01/2026 fica de fora e o saldo parte do saldo de abertura.
        $this->assertSame(15757.28, $s['openingBalance']);
        $this->assertSame(500.0, $s['totalIncome']);
        $this->assertSame(40.0, $s['totalExpenses']);
        $this->assertSame(16217.28, $s['saldo']);
        $this->assertSame(16257.28, $s['saldoReal']);

        $this->assertSame(15757.28, (new MovementService)->summary('2025', false)['saldo']);
        $this->assertSame(16217.28, (new MovementService)->summary('all', false)['saldo']);
    }

    public function test_pendente_separa_receitas_de_despesas(): void
    {
        $this->mov(['movement_type' => 'income', 'amount_gross' => 1000, 'status' => 'pending']);
        $this->mov(['movement_type' => 'expense', 'amount_gross' => 1000, 'status' => 'pending']);
        $this->mov(['movement_type' => 'expense', 'amount_gross' => 250.5, 'status' => 'pending']);

        $s = (new MovementService)->summary('2026', false);

        $this->assertSame(1000.0, $s['pendingIncome']);
        $this->assertSame(1250.5, $s['pendingExpenses']);
        $this->assertSame(-250.5, $s['pending']);
    }

    public function test_somas_em_centimos_nao_acumulam_erro(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->mov(['movement_type' => 'income', 'amount_gross' => 0.1]);
        }

        $s = (new MovementService)->summary('2026', false);

        $this->assertSame(1.0, $s['totalIncome']);
        $this->assertSame(15758.28, $s['saldo']);
    }

    public function test_update_troca_o_anexo_e_so_apaga_o_antigo_depois_de_gravar(): void
    {
        Storage::fake('local');
        $service = new MovementService;
        $m = $service->create(['movement_type' => 'expense', 'title' => 'x', 'amount' => 10, 'expense_date' => '2026-05-01'], UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'));
        $old = $m->attachment_path;
        Storage::disk('local')->assertExists($old);

        $service->update($m, ['title' => 'y', 'amount' => 10, 'expense_date' => '2026-05-01'], UploadedFile::fake()->create('b.pdf', 10, 'application/pdf'));

        $new = $m->fresh()->attachment_path;
        $this->assertNotSame($old, $new);
        Storage::disk('local')->assertExists($new);
        Storage::disk('local')->assertMissing($old);
    }

    public function test_falha_na_gravacao_mantem_o_anexo_antigo_e_remove_o_novo(): void
    {
        Storage::fake('local');
        $service = new MovementService;
        $m = $service->create(['movement_type' => 'expense', 'title' => 'x', 'amount' => 10, 'expense_date' => '2026-05-01'], UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'));
        $old = $m->attachment_path;

        Expense::updating(fn () => throw new \RuntimeException('falha injetada'));

        try {
            $service->update($m, ['title' => 'y', 'amount' => 10, 'expense_date' => '2026-05-01'], UploadedFile::fake()->create('b.pdf', 10, 'application/pdf'));
            $this->fail('Devia ter lançado.');
        } catch (\RuntimeException $e) {
            $this->assertSame('falha injetada', $e->getMessage());
        }

        $this->assertSame($old, $m->fresh()->attachment_path);
        Storage::disk('local')->assertExists($old);
        $this->assertCount(1, Storage::disk('local')->allFiles());
    }

    public function test_falha_ao_remover_anexo_mantem_o_ficheiro(): void
    {
        Storage::fake('local');
        $service = new MovementService;
        $m = $service->create(['movement_type' => 'expense', 'title' => 'x', 'amount' => 10, 'expense_date' => '2026-05-01'], UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'));
        $old = $m->attachment_path;

        Expense::updating(fn () => throw new \RuntimeException('falha injetada'));

        $this->assertThrows(fn () => $service->update($m, ['title' => 'y', 'amount' => 10, 'expense_date' => '2026-05-01'], null, true), \RuntimeException::class);

        Storage::disk('local')->assertExists($old);
        $this->assertSame($old, $m->fresh()->attachment_path);
    }

    public function test_remover_anexo_apaga_o_ficheiro_depois_do_commit(): void
    {
        Storage::fake('local');
        $service = new MovementService;
        $m = $service->create(['movement_type' => 'expense', 'title' => 'x', 'amount' => 10, 'expense_date' => '2026-05-01'], UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'));
        $old = $m->attachment_path;

        $service->update($m, ['title' => 'x', 'amount' => 10, 'expense_date' => '2026-05-01'], null, true);

        $this->assertNull($m->fresh()->attachment_path);
        Storage::disk('local')->assertMissing($old);
    }
}
