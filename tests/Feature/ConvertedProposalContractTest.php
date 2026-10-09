<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\Setting;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ConvertedProposalContractTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_backoffice_can_view_the_contract_as_pdf(): void
    {
        foreach (['name' => 'Izzycar', 'vat_number' => '999999999', 'address' => 'Lisboa', 'iban' => 'PT50', 'phone' => '928459346'] as $label => $value) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => $value]);
        }
        $client = Client::create(['name' => 'Maria', 'is_lead' => false, 'lead_status' => 'nova']);
        $converted = ConvertedProposal::forceCreate(['client_id' => $client->id, 'brand' => 'Audi', 'modelCar' => 'A4']);

        $response = $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v2.converted-proposals.contract', $converted->id))
            ->assertOk();

        $this->assertStringStartsWith('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
