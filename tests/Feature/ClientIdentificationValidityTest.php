<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ClientIdentificationValidityTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_client_form_saves_and_shows_document_validity(): void
    {
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $admin = $this->backofficeUser('admin');
        $client = Client::create(['name' => 'Maria Silva', 'is_lead' => false, 'lead_status' => 'nova']);

        $this->actingAs($admin)
            ->put(route('admin.v2.clients.update', $client->id), [
                'name' => 'Maria Silva',
                'identification_number' => '12345678',
                'validate_identification_number' => '2031-03-15',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('2031-03-15', $client->fresh()->validate_identification_number->format('Y-m-d'));

        $this->actingAs($admin)
            ->get(route('admin.v2.clients.edit', $client->id))
            ->assertOk()
            ->assertSee('name="validate_identification_number"', false)
            ->assertSee('value="2031-03-15"', false);

        $this->actingAs($admin)
            ->put(route('admin.v2.clients.update', $client->id), ['name' => 'Maria Silva', 'validate_identification_number' => 'ontem'])
            ->assertSessionHasErrors('validate_identification_number');
    }
}
