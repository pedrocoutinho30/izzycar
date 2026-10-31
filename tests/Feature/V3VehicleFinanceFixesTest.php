<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** LOG-003 (IVA das despesas), LOG-004 (venda) e LOG-005 (referência). */
class V3VehicleFinanceFixesTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private V3Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $this->vehicle = V3Vehicle::create([
            'reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4',
            'purchase_price' => 10000, 'status' => 'em_stock',
        ]);
    }

    private function postExpense(float $amount, float $rate)
    {
        return $this->actingAs($this->backofficeUser('admin'))
            ->post(route('admin.v3.vehicles.expenses.store', $this->vehicle->id), [
                'title' => 'Teste', 'movement_type' => 'expense', 'amount' => $amount,
                'vat_rate' => $rate, 'expense_date' => '2026-10-01',
            ]);
    }

    public function test_expense_vat_is_extracted_from_gross(): void
    {
        $this->postExpense(100, 23)->assertSessionHasNoErrors();

        $e = Expense::where('v3_vehicle_id', $this->vehicle->id)->firstOrFail();
        $this->assertEquals(18.70, (float) $e->vat_amount);
        $this->assertEquals(81.30, (float) $e->amount_net);
        $this->assertEquals(100.00, (float) $e->amount_gross);
    }

    public function test_expense_vat_matches_movement_service_for_all_rates(): void
    {
        foreach ([0, 6, 13, 23] as $rate) {
            Expense::query()->delete();
            $this->postExpense(123, $rate);
            $e = Expense::firstOrFail();
            $data = ['amount' => 123, 'vat_rate' => $rate];
            app(\App\Services\MovementService::class)->computeVatFields($data);
            $this->assertEquals($data['vat_amount'], (float) $e->vat_amount, "IVA {$rate}%");
            $this->assertEquals($data['amount_net'], (float) $e->amount_net, "líquido {$rate}%");
        }
    }

    private function postSale(array $over = [])
    {
        // sales.client_id é NOT NULL no esquema sqlite dos testes
        $client = \App\Models\Client::first()
            ?? \App\Models\Client::create(['name' => 'Maria', 'is_lead' => false, 'lead_status' => 'nova']);

        return $this->actingAs($this->backofficeUser('admin'))
            ->post(route('admin.v3.vehicles.sale.store', $this->vehicle->id), $over + [
                'client_id' => $client->id, 'payment_method' => 'transferencia', 'sale_date' => '2026-10-10', 'sale_price' => 15000, 'vat_type' => 'geral_23',
            ]);
    }

    public function test_sale_is_registered_once_and_vehicle_marked_sold(): void
    {
        $this->postSale()->assertSessionHas('success');
        $this->assertSame('vendido', $this->vehicle->fresh()->status);

        // segundo pedido (duplo clique): rejeitado, sem segunda venda
        $this->postSale()->assertSessionHas('error');
        $this->assertSame(1, Sale::where('v3_vehicle_id', $this->vehicle->id)->count());
    }

    public function test_sale_rejects_unknown_vat_type(): void
    {
        $this->postSale(['vat_type' => 'qualquer'])->assertSessionHasErrors('vat_type');
        $this->assertSame(0, Sale::count());
        $this->assertSame('em_stock', $this->vehicle->fresh()->status);
    }

    public function test_sale_accepts_every_closed_vat_type(): void
    {
        foreach (['margem', 'geral_23', 'geral_13', 'geral_6', 'isento'] as $tipo) {
            Sale::query()->delete();
            $this->postSale(['vat_type' => $tipo])->assertSessionHasNoErrors();
            $this->assertSame(1, Sale::count(), $tipo);
        }
    }

    public function test_sale_rolls_back_when_status_update_fails(): void
    {
        V3Vehicle::updating(fn () => throw new \RuntimeException('falha simulada'));

        try {
            $this->withoutExceptionHandling()->postSale();
            $this->fail('Devia ter lançado a exceção.');
        } catch (\RuntimeException) {
            $this->assertSame(0, Sale::count());
        } finally {
            V3Vehicle::flushEventListeners();
        }
    }

    public function test_reference_survives_deleting_a_middle_vehicle(): void
    {
        V3Vehicle::query()->delete();
        $ano = now()->year;
        foreach ([1, 2, 3] as $n) {
            V3Vehicle::create(['reference' => "V3-{$ano}-00{$n}", 'brand' => 'A', 'model' => 'B']);
        }
        V3Vehicle::where('reference', "V3-{$ano}-002")->delete();

        $this->assertSame("V3-{$ano}-004", V3Vehicle::generateReference());
    }

    public function test_reference_starts_at_001_and_skips_taken_one(): void
    {
        V3Vehicle::query()->delete();
        $ano = now()->year;
        $this->assertSame("V3-{$ano}-001", V3Vehicle::generateReference());

        V3Vehicle::create(['reference' => "V3-{$ano}-001", 'brand' => 'A', 'model' => 'B']);
        $this->assertSame("V3-{$ano}-002", V3Vehicle::generateReference());
    }
}
