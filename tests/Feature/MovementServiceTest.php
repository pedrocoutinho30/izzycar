<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\FinancialMovement;
use App\Models\Partner;
use App\Models\Sale;
use App\Models\V3Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Caracterização das rotas de movimentos do desktop (admin.v2.movements.*):
 * fixa o comportamento atual antes de a lógica passar para o MovementService,
 * partilhado com a versão mobile.
 */
class MovementServiceTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-20 10:00:00');
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        // Para o caso de a listagem usar YEAR() (só MySQL).
        DB::connection()->getPdo()->sqliteCreateFunction('YEAR', fn ($d) => $d ? (int) substr($d, 0, 4) : null);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function payload(array $override = []): array
    {
        return $override + [
            'movement_type' => 'expense',
            'category' => 'repair',
            'title' => 'Pneus novos',
            'amount' => '123',
            'vat_rate' => '23',
            'expense_date' => '2026-10-10',
            'observations' => 'Oficina X',
            'payment_method' => 'card',
            'status' => 'paid',
        ];
    }

    private function mov(array $attributes = []): Expense
    {
        return Expense::create($attributes + [
            'movement_type' => 'expense', 'category' => 'repair', 'title' => 'Manual',
            'amount' => 100, 'amount_gross' => 100, 'expense_date' => '2026-05-10', 'status' => 'paid',
        ]);
    }

    public function test_store_calcula_iva_cria_o_movimento_financeiro_e_volta_a_lista(): void
    {
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4']);
        $client = Client::create(['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678', 'is_lead' => false]);
        $partner = Partner::create(['name' => 'Oficina X']);

        $this->actingAs($this->backofficeUser())
            ->post(route('admin.v2.movements.store'), $this->payload([
                'v3_vehicle_id' => $vehicle->id, 'client_id' => $client->id, 'partner_id' => $partner->id,
            ]))
            ->assertRedirect(route('admin.v2.movements.index'))
            ->assertSessionHas('success', 'Movimento criado com sucesso!');

        $m = Expense::sole();
        $this->assertSame('expense', $m->movement_type);
        $this->assertSame('repair', $m->category);
        $this->assertSame('Pneus novos', $m->title);
        $this->assertEquals(123, $m->amount);
        $this->assertEquals(123, $m->amount_gross);
        $this->assertEquals(23, $m->vat_rate);
        $this->assertEquals(23.0, $m->vat_amount);
        $this->assertEquals(100.0, $m->amount_net);
        $this->assertSame('2026-10-10', $m->expense_date->toDateString());
        $this->assertSame($vehicle->id, $m->v3_vehicle_id);
        $this->assertSame($client->id, $m->client_id);
        $this->assertSame($partner->id, $m->partner_id);
        $this->assertNull($m->source_type);

        $fm = FinancialMovement::sole();
        $this->assertSame(Expense::class, $fm->movable_type);
        $this->assertSame($m->id, $fm->movable_id);
        $this->assertSame('expense', $fm->type);
        $this->assertSame('Reparação', $fm->category);
        $this->assertSame('Pneus novos', $fm->description);
        $this->assertEquals(123, $fm->amount_gross);
        $this->assertEquals(23, $fm->vat_rate);
        $this->assertEquals(23.0, $fm->vat_amount);
        $this->assertEquals(100.0, $fm->amount_net);
        $this->assertSame('2026-10-10', $fm->movement_date->toDateString());
    }

    public function test_store_sem_iva_e_receita(): void
    {
        $this->actingAs($this->backofficeUser())
            ->post(route('admin.v2.movements.store'), $this->payload(['movement_type' => 'income', 'category' => 'import_service', 'amount' => '500.5', 'vat_rate' => '0']))
            ->assertRedirect(route('admin.v2.movements.index'));

        $m = Expense::sole();
        $this->assertEquals(500.5, $m->amount_gross);
        $this->assertEquals(0.0, $m->vat_amount);
        $this->assertEquals(500.5, $m->amount_net);
        $this->assertSame('income', FinancialMovement::sole()->type);
        $this->assertNull(FinancialMovement::sole()->vat_rate);
    }

    public function test_store_ignora_campos_de_origem_e_valores_calculados_do_pedido(): void
    {
        $this->actingAs($this->backofficeUser())
            ->post(route('admin.v2.movements.store'), $this->payload([
                'source_type' => Sale::class, 'source_id' => 5, 'vehicle_id' => 7,
                'amount_gross' => 999, 'vat_amount' => 999, 'amount_net' => 999, 'attachment_path' => 'x/y.pdf',
            ]))
            ->assertRedirect(route('admin.v2.movements.index'));

        $m = Expense::sole();
        $this->assertNull($m->source_type);
        $this->assertNull($m->source_id);
        $this->assertNull($m->vehicle_id);
        $this->assertNull($m->attachment_path);
        $this->assertEquals(123, $m->amount_gross);
        $this->assertEquals(100.0, $m->amount_net);
    }

    public function test_store_valida_os_campos(): void
    {
        $this->actingAs($this->backofficeUser())
            ->from(route('admin.v2.movements.create'))
            ->post(route('admin.v2.movements.store'), $this->payload(['title' => '', 'amount' => '-1', 'movement_type' => 'transfer', 'expense_date' => 'ontem', 'v3_vehicle_id' => 999999]))
            ->assertRedirect(route('admin.v2.movements.create'))
            ->assertSessionHasErrors(['title', 'amount', 'movement_type', 'expense_date', 'v3_vehicle_id']);

        $this->assertSame(0, Expense::count());
    }

    public function test_update_recalcula_e_sincroniza(): void
    {
        $m = $this->mov();
        $this->assertSame(1, FinancialMovement::count());

        $this->actingAs($this->backofficeUser())
            ->put(route('admin.v2.movements.update', $m->id), $this->payload(['title' => 'Revisão', 'amount' => '246', 'movement_type' => 'income']))
            ->assertRedirect(route('admin.v2.movements.index'))
            ->assertSessionHas('success', 'Movimento atualizado com sucesso!');

        $m->refresh();
        $this->assertSame('Revisão', $m->title);
        $this->assertEquals(246, $m->amount_gross);
        $this->assertEquals(46.0, $m->vat_amount);
        $this->assertEquals(200.0, $m->amount_net);

        $fm = FinancialMovement::sole();
        $this->assertSame('income', $fm->type);
        $this->assertSame('Revisão', $fm->description);
        $this->assertEquals(200.0, $fm->amount_net);
    }

    public function test_destroy_apaga_o_movimento_e_o_lancamento(): void
    {
        $m = $this->mov();

        $this->actingAs($this->backofficeUser())
            ->delete(route('admin.v2.movements.destroy', $m->id))
            ->assertRedirect(route('admin.v2.movements.index'))
            ->assertSessionHas('success', 'Movimento eliminado com sucesso!');

        $this->assertSame(0, Expense::count());
        $this->assertSame(0, FinancialMovement::count());
    }

    public function test_movimento_automatico_nao_se_edita_nem_elimina(): void
    {
        $auto = $this->mov(['title' => 'Venda automática', 'source_type' => Sale::class, 'source_id' => 1]);
        $user = $this->backofficeUser();

        $this->actingAs($user)->get(route('admin.v2.movements.edit', $auto->id))
            ->assertRedirect(route('admin.v2.movements.index'))
            ->assertSessionHas('error', 'Este movimento foi gerado automaticamente e não pode ser editado aqui. Edite a venda, o veículo ou a cotação convertida correspondente.');

        $this->actingAs($user)->put(route('admin.v2.movements.update', $auto->id), $this->payload(['title' => 'Mudado']))
            ->assertRedirect(route('admin.v2.movements.index'))
            ->assertSessionHas('error', 'Este movimento foi gerado automaticamente e não pode ser editado aqui.');

        $this->actingAs($user)->delete(route('admin.v2.movements.destroy', $auto->id))
            ->assertRedirect(route('admin.v2.movements.index'))
            ->assertSessionHas('error', 'Este movimento foi gerado automaticamente e não pode ser eliminado aqui. Elimine a venda ou o veículo correspondente.');

        $this->assertSame('Venda automática', $auto->fresh()->title);
    }

    public function test_rotas_antigas_de_despesas_usam_o_mesmo_controller(): void
    {
        $this->actingAs($this->backofficeUser())
            ->post(route('admin.v2.expenses.store'), $this->payload())
            ->assertRedirect(route('admin.v2.movements.index'))
            ->assertSessionHas('success', 'Movimento criado com sucesso!');

        $this->assertSame(1, Expense::count());
    }

    public function test_listagem_calcula_totais_e_saldos_como_hoje(): void
    {
        $this->mov(['movement_type' => 'income', 'amount' => 1000, 'amount_gross' => 1000, 'expense_date' => '2026-03-01']);
        $this->mov(['movement_type' => 'expense', 'amount' => 200, 'amount_gross' => 200, 'expense_date' => '2026-04-01']);
        $this->mov(['movement_type' => 'expense', 'amount' => 50, 'amount_gross' => 50, 'expense_date' => '2026-04-02', 'status' => 'pending']);
        // Outro ano: fora dos totais do ano corrente.
        $this->mov(['movement_type' => 'income', 'amount' => 7000, 'amount_gross' => 7000, 'expense_date' => '2025-06-01']);

        $user = $this->backofficeUser();
        $r = $this->actingAs($user)->get(route('admin.v2.movements.index'))->assertOk();

        $this->assertSame('2026', $r->viewData('year'));
        $stats = collect($r->viewData('stats'))->pluck('value', 'title')->all();
        $this->assertSame([
            'Total Saídas' => '250,00€',
            'Total Entradas' => '1.000,00€',
            // 15757.28 + 1000 - 200 - 50
            'Saldo (c/ Pendentes)' => '16.507,28€',
            // Só pagos: sem os 50 pendentes.
            'Saldo Real (só Pagos)' => '16.557,28€',
        ], $stats);
        $this->assertEqualsWithDelta(16507.28, $r->viewData('currentSaldo'), 0.001);
        $this->assertEqualsWithDelta(16557.28, $r->viewData('currentSaldoReal'), 0.001);
        $this->assertSame(15757.28, $r->viewData('initialBalance'));
        $this->assertSame([2026, 2025], collect($r->viewData('availableYears'))->map(fn ($y) => (int) $y)->all());
        $this->assertCount(3, $r->viewData('movements'));

        // Desde o início: tudo.
        $r = $this->actingAs($user)->get(route('admin.v2.movements.index', ['year' => 'all']))->assertOk();
        $stats = collect($r->viewData('stats'))->pluck('value', 'title')->all();
        $this->assertSame('8.000,00€', $stats['Total Entradas']);
        $this->assertSame('23.507,28€', $stats['Saldo (c/ Pendentes)']);

        // Com datas, os totais ignoram o ano (e os outros filtros), como hoje.
        $r = $this->actingAs($user)->get(route('admin.v2.movements.index', ['date_from' => '2026-04-01', 'date_to' => '2026-04-30', 'movement_type' => 'expense']))->assertOk();
        $stats = collect($r->viewData('stats'))->pluck('value', 'title')->all();
        $this->assertSame('8.000,00€', $stats['Total Entradas']);
        $this->assertCount(2, $r->viewData('movements'));
    }

    public function test_listagem_sem_movimentos_mostra_o_ano_corrente(): void
    {
        $r = $this->actingAs($this->backofficeUser())->get(route('admin.v2.movements.index'))->assertOk();

        $this->assertSame([2026], collect($r->viewData('availableYears'))->map(fn ($y) => (int) $y)->all());
        $this->assertSame('15.757,28€', collect($r->viewData('stats'))->pluck('value', 'title')['Saldo (c/ Pendentes)']);
    }

    public function test_formularios_do_desktop_abrem(): void
    {
        $m = $this->mov();
        $user = $this->backofficeUser();

        $this->actingAs($user)->get(route('admin.v2.movements.create'))->assertOk()->assertSee('Novo Movimento');
        $this->actingAs($user)->get(route('admin.v2.movements.edit', $m->id))->assertOk()->assertSee('Editar Movimento')->assertSee('Manual');
    }
}
