<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Mail\ProposalStatusUpdatedMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
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

    private function updateStatus(Client $client, string $status)
    {
        $converted = ConvertedProposal::forceCreate(['client_id' => $client->id, 'brand' => 'Audi', 'modelCar' => 'A4', 'status' => 'Iniciada']);

        $this->actingAs($this->backofficeUser('admin'))
            ->put(route('admin.v2.converted-proposals.update', $converted->id), ['client_id' => $client->id, 'status' => $status])
            ->assertRedirect(route('admin.v2.converted-proposals.index'));

        return $converted->fresh();
    }

    public function test_status_change_without_client_email_still_saves_and_sends_nothing(): void
    {
        Mail::fake();

        $this->assertSame('Transporte', $this->updateStatus($this->client, 'Transporte')->status);

        Mail::assertNothingSent();
    }

    public function test_status_change_sends_mail_when_client_has_email(): void
    {
        Mail::fake();
        $client = Client::create(['name' => 'Rui', 'email' => 'rui@example.com', 'is_lead' => false, 'lead_status' => 'nova']);

        $this->assertSame('Transporte', $this->updateStatus($client, 'Transporte')->status);

        Mail::assertSent(ProposalStatusUpdatedMail::class);
    }
}
