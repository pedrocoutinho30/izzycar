<?php

namespace Tests\Feature;

use App\Mail\ProposalStatusUpdatedMail;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\StatusProposalHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Comportamento da rota do desktop admin.v2.converted-proposals.update-status
 * (pipeline V2), fixado antes de extrair a lógica para ConvertedProposalService.
 */
class ConvertedProposalStatusCharacterizationTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $this->client = Client::create(['name' => 'Maria', 'email' => 'maria@example.com', 'is_lead' => false, 'lead_status' => 'nova']);
    }

    private function converted(?string $status = 'Iniciada', ?Client $client = null): ConvertedProposal
    {
        return ConvertedProposal::create(['client_id' => ($client ?? $this->client)->id, 'status' => $status, 'brand' => 'BMW', 'modelCar' => 'i4']);
    }

    private function patchStatus(ConvertedProposal $converted, array $data)
    {
        return $this->actingAs($this->backofficeUser())
            ->patchJson(route('admin.v2.converted-proposals.update-status', $converted->id), $data);
    }

    public function test_mudar_estado_grava_historico_e_devolve_o_json_do_desktop(): void
    {
        Mail::fake();
        $converted = $this->converted('Iniciada');

        $this->patchStatus($converted, ['status' => 'Transporte'])
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Estado atualizado com sucesso!',
                'notified' => true,
                'oldStatus' => 'Iniciada',
                'newStatus' => 'Transporte',
            ]);

        $this->assertSame('Transporte', $converted->fresh()->status);
        $history = StatusProposalHistory::where('converted_proposal_id', $converted->id)->sole();
        $this->assertSame('Iniciada', $history->old_status);
        $this->assertSame('Transporte', $history->new_status);

        Mail::assertSent(ProposalStatusUpdatedMail::class, fn ($mail) => $mail->hasTo('maria@example.com')
            && $mail->hasBcc('izzycarpt@gmail.com')
            && $mail->oldStatus === 'Iniciada' && $mail->newStatus === 'Transporte' && $mail->clientName === 'Maria');
    }

    public function test_mesmo_estado_nao_grava_nem_envia(): void
    {
        Mail::fake();
        $converted = $this->converted('IPO');

        $this->patchStatus($converted, ['status' => 'IPO'])
            ->assertOk()
            ->assertExactJson(['success' => true, 'message' => 'O estado já era este.', 'oldStatus' => 'IPO', 'newStatus' => 'IPO']);

        $this->assertSame(0, StatusProposalHistory::count());
        Mail::assertNothingSent();
    }

    public function test_cliente_sem_email_muda_o_estado_sem_enviar(): void
    {
        Mail::fake();
        $semEmail = Client::create(['name' => 'Sem Email', 'is_lead' => false, 'lead_status' => 'nova']);
        $converted = $this->converted('Iniciada', $semEmail);

        $this->patchStatus($converted, ['status' => 'DAV'])->assertOk()->assertJson(['notified' => false]);

        $this->assertSame('DAV', $converted->fresh()->status);
        $this->assertSame(1, StatusProposalHistory::count());
        Mail::assertNothingSent();
    }

    public function test_falha_no_envio_nao_impede_a_mudanca_e_e_reportada(): void
    {
        Exceptions::fake();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP em baixo'));
        $converted = $this->converted('Iniciada');

        $this->patchStatus($converted, ['status' => 'Transporte'])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Estado atualizado com sucesso!', 'notified' => false]);

        $this->assertSame('Transporte', $converted->fresh()->status);
        $this->assertSame(1, StatusProposalHistory::count());
        Exceptions::assertReported(fn (\RuntimeException $e) => $e->getMessage() === 'SMTP em baixo');
    }

    public function test_estado_invalido_e_rejeitado_com_a_mensagem_do_desktop(): void
    {
        Mail::fake();
        $converted = $this->converted('Iniciada');

        $this->patchStatus($converted, ['status' => 'Inventado'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status' => 'Estado inválido.']);
        $this->patchStatus($converted, ['status' => ''])->assertUnprocessable()->assertJsonValidationErrors('status');
        // "Em Processo" existe nos dados antigos mas não é uma etapa do pipeline.
        $this->patchStatus($converted, ['status' => 'Em Processo'])->assertUnprocessable();

        $this->assertSame('Iniciada', $converted->fresh()->status);
        $this->assertSame(0, StatusProposalHistory::count());
        Mail::assertNothingSent();
    }

    public function test_aceita_todas_as_etapas_e_as_duas_grafias_de_cancelado(): void
    {
        Mail::fake();
        $converted = $this->converted('Iniciada');

        foreach ([...ConvertedProposal::PIPELINE_STATUSES, 'Cancelada', 'Cancelado', 'Iniciada'] as $status) {
            $this->patchStatus($converted, ['status' => $status, 'notify' => false])->assertOk();
            $this->assertSame($status, $converted->fresh()->status);
        }

        // "Cancelado" (V1) e "Cancelada" (V2) contam ambos como cancelada.
        $this->assertTrue($this->converted('Cancelado')->isCancelled());
        $this->assertSame(0, ConvertedProposal::notCancelled()->where('status', 'Cancelado')->count());
        Mail::assertNothingSent();
    }

    public function test_notify_falso_nao_envia(): void
    {
        Mail::fake();
        $converted = $this->converted('Iniciada');

        $this->patchStatus($converted, ['status' => 'IPO', 'notify' => false])->assertOk()->assertJson(['notified' => false]);
        $this->patchStatus($converted, ['status' => 'DAV', 'notify' => 'talvez'])->assertUnprocessable()->assertJsonValidationErrors('notify');

        $this->assertSame('IPO', $converted->fresh()->status);
        Mail::assertNothingSent();
    }
}
