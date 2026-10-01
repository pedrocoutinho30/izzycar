<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Proposal;
use App\Services\Modelo9PdfService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ProposalAcceptanceIdentificationTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();
        Notification::fake();
    }

    private function proposal(array $client = []): Proposal
    {
        $client = Client::create($client + ['name' => 'Maria Silva', 'is_lead' => false, 'lead_status' => 'nova', 'email' => 'maria@example.com']);

        return Proposal::create(['client_id' => $client->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0, 'proposal_code' => 'BI' . $client->id . 'TEST', 'status' => 'Pendente']);
    }

    public function test_acceptance_saves_the_document_validity(): void
    {
        $proposal = $this->proposal();

        $this->post(route('proposals.accept', $proposal->proposal_code), [
            'identification_number' => '12345678 9 ZZ1',
            'validate_identification_number' => '2031-03-15',
        ])->assertSessionHasNoErrors();

        $client = $proposal->client->fresh();
        $this->assertSame('12345678 9 ZZ1', $client->identification_number);
        $this->assertSame('2031-03-15', $client->validate_identification_number->format('Y-m-d'));
    }

    public function test_acceptance_rejects_an_invalid_validity(): void
    {
        $proposal = $this->proposal();

        $this->post(route('proposals.accept', $proposal->proposal_code), ['validate_identification_number' => 'amanhã'])
            ->assertSessionHasErrors('validate_identification_number');

        $this->assertNull($proposal->client->fresh()->validate_identification_number);
    }

    public function test_modal_asks_for_validity_only_when_missing(): void
    {
        $missing = $this->proposal();
        $this->assertStringContainsString('name="validate_identification_number"', $this->acceptanceHtml($missing));

        $complete = $this->proposal(['email' => 'outro@example.com', 'validate_identification_number' => '2031-03-15']);
        $this->assertStringNotContainsString('name="validate_identification_number"', $this->acceptanceHtml($complete));
    }

    public function test_modelo9_document_number_uses_the_first_nine_characters(): void
    {
        $this->assertSame('123456789', Modelo9PdfService::documentNumber('12345678 9 ZZ1'));
        $this->assertSame('12345678', Modelo9PdfService::documentNumber('12345678'));
        $this->assertSame('CB1234567', Modelo9PdfService::documentNumber('CB-1234567'));
        $this->assertSame('', Modelo9PdfService::documentNumber(null));
    }

    private function acceptanceHtml(Proposal $proposal): string
    {
        $proposal->update(['proposal_code' => 'TEST' . $proposal->id]);

        return $this->get(route('proposals.detail', $proposal->fresh()->proposal_code))->assertOk()->getContent();
    }
}
