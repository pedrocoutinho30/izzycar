<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ConvertedProposal;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ConvertedProposalListTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $this->client = Client::create(['name' => 'Maria', 'is_lead' => false, 'lead_status' => 'nova']);
        foreach (['Iniciada', 'Transporte', 'Concluido', 'Cancelada', 'Cancelado'] as $status) {
            ConvertedProposal::forceCreate(['client_id' => $this->client->id, 'brand' => 'Audi', 'modelCar' => 'A4', 'status' => $status]);
        }
    }

    public function test_stats_count_pipeline_statuses(): void
    {
        $response = $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v2.converted-proposals.index'))
            ->assertOk();

        $values = collect($response->viewData('stats'))->pluck('value', 'title');

        $this->assertSame(5, $values['Total Convertidas']);
        $this->assertSame(2, $values['Em Processo']);
        $this->assertSame(1, $values['Concluídas']);
        $this->assertSame(2, $values['Canceladas']);
    }

    public function test_status_filter_accepts_pipeline_statuses_and_both_cancelled_spellings(): void
    {
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->get(route('admin.v2.converted-proposals.index', ['status' => 'Transporte']))
            ->assertOk()->assertViewHas('convertedProposals', fn ($page) => $page->total() === 1);

        $this->actingAs($admin)->get(route('admin.v2.converted-proposals.index', ['status' => 'Cancelada']))
            ->assertOk()->assertViewHas('convertedProposals', fn ($page) => $page->total() === 2);
    }
}
