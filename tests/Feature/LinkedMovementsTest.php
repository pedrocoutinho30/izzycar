<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Legalization;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class LinkedMovementsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private V3Vehicle $vehicle;

    private Legalization $legalization;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $this->vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4']);
        $this->legalization = Legalization::create([
            'v3_vehicle_id' => $this->vehicle->id, 'marca' => 'BMW', 'modelo' => 'i4', 'combustivel' => 'Gasolina', 'steps_completed' => [],
        ]);
    }

    private function movement(array $attributes): Expense
    {
        return Expense::create($attributes + [
            'movement_type' => 'expense', 'category' => 'legalization', 'amount' => 100, 'amount_gross' => 100,
            'expense_date' => '2026-10-01', 'status' => 'paid',
        ]);
    }

    public function test_legalization_page_lists_only_its_movements(): void
    {
        $this->movement(['title' => 'Taxa IMT', 'legalization_id' => $this->legalization->id]);
        $this->movement(['title' => 'Movimento alheio']);

        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.legalizations.show', $this->legalization))
            ->assertOk()
            ->assertSee('Taxa IMT')
            ->assertDontSee('Movimento alheio');
    }

    public function test_vehicle_lists_legalization_movements_without_duplicating_its_own(): void
    {
        $this->movement(['title' => 'Taxa IMT', 'legalization_id' => $this->legalization->id]);
        $this->movement(['title' => 'Pneus novos', 'v3_vehicle_id' => $this->vehicle->id, 'legalization_id' => $this->legalization->id]);

        $linked = $this->vehicle->legalizationExpenses()->pluck('title')->all();

        $this->assertSame(['Taxa IMT'], $linked);

        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v3.vehicles.edit', $this->vehicle->id))
            ->assertOk()
            ->assertSee('Movimentos da legalização')
            ->assertSee('Taxa IMT');
    }
}
