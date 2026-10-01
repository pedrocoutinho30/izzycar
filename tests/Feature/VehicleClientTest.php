<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Legalization;
use App\Models\User;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class VehicleClientTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private User $admin;

    private Client $client;

    private V3Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $this->admin = $this->backofficeUser('admin');
        $this->client = Client::create(['name' => 'Maria Importação', 'is_lead' => false, 'lead_status' => 'nova']);
        $this->vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4', 'is_imported' => true]);
    }

    private function saveGeneral(array $data)
    {
        return $this->actingAs($this->admin)->postJson(route('admin.v3.vehicles.save-general', $this->vehicle->id), $data + [
            'brand' => 'BMW', 'model' => 'i4', 'is_imported' => 1,
        ]);
    }

    public function test_imported_vehicle_can_be_linked_to_a_client_without_a_sale(): void
    {
        $this->saveGeneral(['client_id' => $this->client->id])->assertOk()->assertJsonPath('success', true);

        $this->vehicle->refresh();
        $this->assertTrue($this->vehicle->client->is($this->client));
        $this->assertCount(0, $this->vehicle->sales);
        $this->assertTrue($this->client->vehicles->first()->is($this->vehicle));

        $this->saveGeneral(['client_id' => ''])->assertOk();
        $this->assertNull($this->vehicle->fresh()->client_id);

        $this->saveGeneral(['client_id' => 999999])->assertUnprocessable()->assertJsonValidationErrors('client_id');
    }

    public function test_legalization_inherits_the_vehicle_client(): void
    {
        $this->vehicle->update(['client_id' => $this->client->id]);

        $this->actingAs($this->admin)->post(route('admin.v3.vehicles.legalization.create', $this->vehicle->id));

        $this->assertSame($this->client->id, Legalization::where('v3_vehicle_id', $this->vehicle->id)->sole()->client_id);
    }

    public function test_existing_legalization_without_client_gets_it_but_others_are_kept(): void
    {
        $legalization = Legalization::create(['v3_vehicle_id' => $this->vehicle->id, 'marca' => 'BMW', 'modelo' => 'i4', 'combustivel' => 'Gasolina', 'steps_completed' => []]);

        $this->saveGeneral(['client_id' => $this->client->id])->assertOk();
        $this->assertSame($this->client->id, $legalization->fresh()->client_id);

        $other = Client::create(['name' => 'Outro', 'is_lead' => false, 'lead_status' => 'nova']);
        $this->saveGeneral(['client_id' => $other->id])->assertOk();
        $this->assertSame($this->client->id, $legalization->fresh()->client_id);
    }

    public function test_pages_show_the_link(): void
    {
        $this->vehicle->update(['client_id' => $this->client->id]);

        $this->actingAs($this->admin)->get(route('admin.v3.vehicles.edit', $this->vehicle->id))
            ->assertOk()
            ->assertSee('id="v3ClientSelect"', false)
            ->assertSee('<option value="' . $this->client->id . '" selected', false);

        $this->actingAs($this->admin)->get(route('admin.v2.clients.show', $this->client->id))
            ->assertOk()
            ->assertSee(route('admin.v3.vehicles.edit', $this->vehicle->id), false)
            ->assertSee('Importação');

        $this->actingAs($this->admin)->get(route('admin.v3.vehicles.index'))
            ->assertOk()
            ->assertSee('Maria Importação');
    }
}
