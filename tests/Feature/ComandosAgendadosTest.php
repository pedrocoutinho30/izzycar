<?php

namespace Tests\Feature;

use App\Mail\FollowupReminderMail;
use App\Mail\OverdueCommissionMail;
use App\Mail\ProposalStatusUpdatedMail;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\RadarListing;
use App\Models\RadarSearch;
use App\Models\User;
use App\Services\ConvertedProposalService;
use App\Services\RadarTrimPriceAiService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * LOG-001, LOG-009, LOG-011, LOG-018 e alerta diário de comissões.
 */
class ComandosAgendadosTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function lead(Carbon $quando, array $extra = []): Client
    {
        return Client::create($extra + [
            'name' => 'Lead Teste',
            'is_lead' => true,
            'next_followup_at' => $quando,
        ]);
    }

    public function test_lembrete_sai_no_minuto_exato_e_nao_uma_hora_antes(): void
    {
        $this->lead(Carbon::parse('2026-03-10 10:00:00'));

        Carbon::setTestNow('2026-03-10 09:00:00');
        $this->artisan('leads:followup-reminder')->assertSuccessful();
        Mail::assertNothingSent();

        Carbon::setTestNow('2026-03-10 10:00:20');
        $this->artisan('leads:followup-reminder')->assertSuccessful();
        Mail::assertSent(FollowupReminderMail::class, 1);
    }

    public function test_follow_up_a_hh_mm_00_nao_e_duplicado_entre_minutos_consecutivos(): void
    {
        $this->lead(Carbon::parse('2026-03-10 10:00:00'));

        Carbon::setTestNow('2026-03-10 09:59:00');
        $this->artisan('leads:followup-reminder');
        Carbon::setTestNow('2026-03-10 10:00:00');
        $this->artisan('leads:followup-reminder');
        Carbon::setTestNow('2026-03-10 10:01:00');
        $this->artisan('leads:followup-reminder');

        Mail::assertSent(FollowupReminderMail::class, 1);
    }

    public function test_correr_duas_vezes_no_mesmo_minuto_envia_um_so_email(): void
    {
        $this->lead(Carbon::parse('2026-03-10 10:00:30'));

        Carbon::setTestNow('2026-03-10 10:00:05');
        $this->artisan('leads:followup-reminder');
        $this->artisan('leads:followup-reminder');

        Mail::assertSent(FollowupReminderMail::class, 1);
    }

    public function test_follow_up_reagendado_volta_a_avisar(): void
    {
        $lead = $this->lead(Carbon::parse('2026-03-10 10:00:00'));

        Carbon::setTestNow('2026-03-10 10:00:00');
        $this->artisan('leads:followup-reminder');

        $lead->update(['next_followup_at' => Carbon::parse('2026-03-10 10:30:00')]);
        Carbon::setTestNow('2026-03-10 10:30:00');
        $this->artisan('leads:followup-reminder');

        Mail::assertSent(FollowupReminderMail::class, 2);
    }

    public function test_angariador_recebe_o_seu_email_mesmo_se_o_do_admin_falhar(): void
    {
        $owner = $this->backofficeUser('angariador', ['email' => 'angariador@example.com']);
        $this->lead(Carbon::parse('2026-03-10 10:00:00'), ['owner_id' => $owner->id]);

        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('smtp em baixo'));
        Mail::shouldReceive('to')->with('angariador@example.com')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once();

        Carbon::setTestNow('2026-03-10 10:00:00');
        $this->artisan('leads:followup-reminder')->assertSuccessful();
    }

    public function test_classify_trims_nao_salta_lotes(): void
    {
        $search = RadarSearch::create(['name' => 'Teste', 'make' => 'BMW', 'model' => 'i4', 'base_url' => 'https://example.com']);
        for ($i = 1; $i <= 60; $i++) {
            RadarListing::create([
                'external_id' => "ext-$i", 'source' => 'autoscout24',
                'radar_search_id' => $search->id, 'make' => 'BMW', 'model' => 'i4',
                'price_eur' => 30000, 'first_seen_at' => now(), 'last_seen_at' => now(), 'url' => "https://example.com/$i",
            ]);
        }

        $this->mock(RadarTrimPriceAiService::class, function ($mock) {
            $mock->shouldReceive('classifyBatch')->andReturnUsing(fn ($make, $model, $batch) => [
                'success' => true,
                'results' => $batch->mapWithKeys(fn ($l) => [$l->id => 1.1])->all(),
            ]);
        });

        $this->artisan('radar:classify-trims')->assertSuccessful();

        $this->assertSame(60, RadarListing::whereNotNull('trim_classified_at')->count());
    }

    public function test_alerta_de_comissoes_nao_repete_a_mesma_comissao_todos_os_dias(): void
    {
        $owner = $this->backofficeUser('angariador');
        $client = Client::create(['name' => 'Cliente']);
        $conv = ConvertedProposal::create([
            'client_id' => $client->id, 'owner_id' => $owner->id, 'status' => 'Entrega',
            'brand' => 'BMW', 'modelCar' => 'i4', 'comissao_paga' => false,
        ]);
        $historico = $conv->statusHistories()->create(['old_status' => 'IPO', 'new_status' => 'Entrega']);
        $historico->forceFill(['created_at' => Carbon::parse('2026-03-01 09:00:00')])->save();

        Carbon::setTestNow('2026-03-10 09:00:00');
        $this->artisan('commissions:overdue-alert')->assertSuccessful();
        Mail::assertSent(OverdueCommissionMail::class, 1);

        Carbon::setTestNow('2026-03-11 09:00:00');
        $this->artisan('commissions:overdue-alert');
        Mail::assertSent(OverdueCommissionMail::class, 1);

        Carbon::setTestNow('2026-03-18 09:00:01');
        $this->artisan('commissions:overdue-alert');
        Mail::assertSent(OverdueCommissionMail::class, 2);
    }

    public function test_agendamentos_nao_se_sobrepoem_e_registam_falhas(): void
    {
        $eventos = collect(app(Schedule::class)->events());
        $this->assertGreaterThan(0, $eventos->count());

        foreach ($eventos as $evento) {
            $this->assertTrue($evento->withoutOverlapping, "Sem withoutOverlapping: {$evento->command}");
            // onFailure() regista o callback em afterCallbacks (protegido).
            $callbacks = (fn () => $this->afterCallbacks)->call($evento);
            $this->assertNotEmpty($callbacks, "Sem onFailure: {$evento->command}");
        }
    }

    public function test_bcc_do_estado_da_cotacao_vem_da_config(): void
    {
        config(['mail.bcc_address' => 'bcc@example.com']);
        $client = Client::create(['name' => 'Cliente', 'email' => 'cliente@example.com']);
        $conv = ConvertedProposal::create([
            'client_id' => $client->id, 'status' => 'Iniciada', 'brand' => 'BMW', 'modelCar' => 'i4',
        ]);

        app(ConvertedProposalService::class)->changeStatus($conv, 'Transporte', true);

        Mail::assertSent(ProposalStatusUpdatedMail::class, fn ($m) => $m->hasBcc('bcc@example.com'));
    }
}
