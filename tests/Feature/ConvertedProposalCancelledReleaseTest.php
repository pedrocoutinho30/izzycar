<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\Proposal;
use App\Services\ConvertedProposalService;
use App\Services\ProposalAcceptanceException;
use App\Services\ProposalAcceptanceService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** LOG-007: cancelar a cotação convertida liberta a cotação. */
class ConvertedProposalCancelledReleaseTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();
    }

    private function proposal(): Proposal
    {
        $client = Client::create(['name' => 'Maria', 'email' => 'maria@example.com', 'is_lead' => false, 'lead_status' => 'nova']);

        return Proposal::create(['client_id' => $client->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0, 'proposal_code' => 'BI' . $client->id . 'REL', 'status' => 'Enviado']);
    }

    public function test_aceite_e_com_convertida_ativa_nao_se_volta_a_aceitar(): void
    {
        $proposal = $this->proposal();
        $service = app(ProposalAcceptanceService::class);
        $service->accept($proposal, byClient: false);

        $this->assertTrue($proposal->fresh()->isAccepted());
        $this->expectException(ProposalAcceptanceException::class);
        $service->accept($proposal->fresh(), byClient: false);
    }

    public function test_convertida_cancelada_liberta_a_cotacao(): void
    {
        $proposal = $this->proposal();
        $service = app(ProposalAcceptanceService::class);
        $converted = $service->accept($proposal, byClient: false);

        app(ConvertedProposalService::class)->changeStatus($converted, 'Cancelado', false);

        $this->assertFalse($proposal->fresh()->isAccepted());
        // Já pode mudar de estado e voltar a ser aceite.
        $service->changeStatus($proposal->fresh(), 'Reprovada');
        $this->assertSame('Reprovada', $proposal->fresh()->status);
    }

    public function test_reaceitar_cria_nova_convertida_e_mantem_a_cancelada_sem_ligacao(): void
    {
        $proposal = $this->proposal();
        $service = app(ProposalAcceptanceService::class);
        $first = $service->accept($proposal, byClient: false);
        app(ConvertedProposalService::class)->changeStatus($first, 'Cancelado', false);

        $second = $service->accept($proposal->fresh(), byClient: false);

        $this->assertNotSame($first->id, $second->id);
        $this->assertTrue($proposal->fresh()->isAccepted());
        $this->assertNull($first->fresh()->proposal_id);
        $this->assertSame('Cancelado', $first->fresh()->status);
        $this->assertSame($proposal->id, $second->fresh()->proposal_id);
        $this->assertSame(2, ConvertedProposal::where('client_id', $proposal->client_id)->count());
    }

    public function test_as_duas_grafias_de_cancelada_sao_a_mesma_coisa_na_leitura(): void
    {
        $this->assertSame('Cancelada', ConvertedProposal::normalizeStatus('Cancelado'));
        $this->assertSame('Cancelada', ConvertedProposal::normalizeStatus('Cancelada'));
        $this->assertSame('Transporte', ConvertedProposal::normalizeStatus('Transporte'));
        $this->assertNull(ConvertedProposal::normalizeStatus(null));
        $this->assertTrue(ConvertedProposal::isCancelledStatus('Cancelado'));
        $this->assertFalse(ConvertedProposal::isCancelledStatus('Concluido'));
    }
}
