<?php

namespace Tests\Feature;

use App\Mail\ProposalStatusUpdatedMail;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\Proposal;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ConvertedProposalTranchesTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();

        foreach (['email' => 'geral@izzycar.pt', 'phone' => '928459346', 'facebook' => 'x', 'insta' => 'x', 'init_payment' => '60%', 'final_payment' => '40%'] as $label => $value) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => $value]);
        }

        $this->client = Client::create(['name' => 'Maria', 'email' => 'maria@example.com', 'phone' => '912345678', 'is_lead' => true, 'lead_status' => 'nova']);
    }

    private function converted(array $extra = []): ConvertedProposal
    {
        return ConvertedProposal::forceCreate($extra + [
            'client_id' => $this->client->id, 'brand' => 'Opel', 'modelCar' => 'Astra', 'status' => 'Iniciada',
            'custo_transporte' => 700, 'custo_ipo' => 50, 'custo_imt' => 100, 'custo_matricula' => 40, 'custo_registo_automovel' => 60,
            'valor_comissao' => 1050, 'isv' => 3500, 'valor_carro' => 12000,
            'valor_primeira_tranche' => 1272.5, 'valor_segunda_tranche' => 1272.5,
        ]);
    }

    public function test_acceptance_splits_the_service_by_the_settings_percentages_without_isv(): void
    {
        $proposal = Proposal::create([
            'client_id' => $this->client->id, 'brand' => 'Opel', 'model' => 'Astra',
            'transport_cost' => 1350, 'commission_cost' => 650, 'isv_cost' => 3500, 'proposed_car_value' => 12000,
            'proposal_code' => 'TR' . random_int(100000, 999999), 'status' => 'Pendente',
        ]);

        $this->post(route('proposals.accept', $proposal->proposal_code), ['address' => 'Rua Nova 1'])->assertSessionHas('success');

        $converted = ConvertedProposal::where('proposal_id', $proposal->id)->sole();
        // Serviço = 1.350 + 650 = 2.000 €; 60% = 1.200 €, 40% = 800 €. O ISV não entra.
        $this->assertEquals(1200.0, (float) $converted->valor_primeira_tranche);
        $this->assertEquals(800.0, (float) $converted->valor_segunda_tranche);
    }

    public function test_command_only_shows_the_difference_without_apply(): void
    {
        $converted = $this->converted();

        $this->artisan('converted-proposals:recalculate-tranches', ['id' => $converted->id])
            ->expectsTable(
                ['Id', 'Veículo', 'Tranches atuais', 'Tranches novas', 'Estado'],
                [[$converted->id, 'Opel Astra', '1.272,50 / 1.272,50', '1.200,00 / 800,00', 'a atualizar']]
            )
            ->expectsOutputToContain('Nada foi gravado')
            ->assertSuccessful();

        $this->assertEquals(1272.5, (float) $converted->refresh()->valor_primeira_tranche);
    }

    public function test_command_with_apply_saves_the_new_tranches(): void
    {
        $converted = $this->converted();

        $this->artisan('converted-proposals:recalculate-tranches', ['id' => $converted->id, '--apply' => true])->assertSuccessful();

        $converted->refresh();
        $this->assertEquals(1200.0, (float) $converted->valor_primeira_tranche);
        $this->assertEquals(800.0, (float) $converted->valor_segunda_tranche);
    }

    public function test_command_skips_converted_quotes_with_a_paid_tranche(): void
    {
        $converted = $this->converted(['primeira_tranche_pago' => true]);

        $this->artisan('converted-proposals:recalculate-tranches', ['id' => $converted->id, '--apply' => true])
            ->expectsTable(
                ['Id', 'Veículo', 'Tranches atuais', 'Tranches novas', 'Estado'],
                [[$converted->id, 'Opel Astra', '1.272,50 / 1.272,50', '1.200,00 / 800,00', 'ignorada (tranche já paga)']]
            )
            ->assertSuccessful();

        $this->assertEquals(1272.5, (float) $converted->refresh()->valor_primeira_tranche);
    }

    public function test_command_reports_nothing_to_do_when_the_tranches_are_already_right(): void
    {
        $this->converted(['valor_primeira_tranche' => 1200, 'valor_segunda_tranche' => 800]);

        $this->artisan('converted-proposals:recalculate-tranches')
            ->expectsOutputToContain('Nada a alterar')
            ->assertSuccessful();
    }
}
