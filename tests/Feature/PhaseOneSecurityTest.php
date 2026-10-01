<?php

namespace Tests\Feature;

use App\Mail\ProposalStatusUpdatedMail;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class PhaseOneSecurityTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();

        // O rodapé das páginas públicas lê estas configurações.
        foreach (['email' => 'geral@izzycar.pt', 'phone' => '928459346', 'facebook' => 'https://facebook.com/izzycar', 'insta' => 'https://instagram.com/izzycar'] as $label => $value) {
            \App\Models\Setting::create(['title' => ucfirst($label), 'label' => $label, 'type' => 'text', 'value' => $value]);
        }

        $this->client = Client::create([
            'name' => 'Maria Silva', 'email' => 'maria@example.com', 'phone' => '912345678',
            'is_lead' => true, 'lead_status' => 'nova',
        ]);
    }

    private function proposal(array $attributes = []): Proposal
    {
        return Proposal::create($attributes + [
            'client_id' => $this->client->id, 'brand' => 'BMW', 'model' => 'i4',
            'transport_cost' => 1350, 'commission_cost' => 615, 'proposed_car_value' => 30000,
            'proposal_code' => 'BIC' . random_int(10000, 99999), 'status' => 'Pendente',
        ]);
    }

    // ── Aceitação pública ────────────────────────────────────────────

    public function test_accept_by_code_creates_one_converted_proposal(): void
    {
        $proposal = $this->proposal();

        $this->post(route('proposals.accept', $proposal->proposal_code), ['address' => 'Rua Nova 1'])
            ->assertSessionHas('success');

        $converted = ConvertedProposal::where('proposal_id', $proposal->id)->sole();
        $this->assertSame('Iniciada', $converted->status);
        $this->assertSame('Aprovada', $proposal->fresh()->status);
        $this->assertFalse($this->client->fresh()->is_lead);
        $this->assertSame('Rua Nova 1', $this->client->fresh()->address);
        $this->assertEquals(982.5, (float) $converted->valor_primeira_tranche);
    }

    public function test_the_old_id_route_no_longer_accepts(): void
    {
        $proposal = $this->proposal();

        $this->post('/proposals/' . $proposal->id . '/accept')->assertNotFound();
        $this->assertSame(0, ConvertedProposal::count());
    }

    public function test_existing_client_data_is_never_overwritten(): void
    {
        $proposal = $this->proposal();

        $this->post(route('proposals.accept', $proposal->proposal_code), [
            'email' => 'atacante@example.com',
            'phone' => '999999999',
            'vat_number' => '123456789',
        ])->assertSessionHas('success');

        $client = $this->client->fresh();
        $this->assertSame('maria@example.com', $client->email);
        $this->assertSame('912345678', $client->phone);
        $this->assertSame('123456789', $client->vat_number); // estava vazio: preenche
    }

    public function test_second_acceptance_changes_nothing(): void
    {
        $proposal = $this->proposal();
        $this->post(route('proposals.accept', $proposal->proposal_code));

        $proposal->update(['status' => 'Reprovada']);
        $this->client->update(['address' => null]);

        $this->post(route('proposals.accept', $proposal->proposal_code), ['address' => 'Rua X'])
            ->assertSessionHas('error');

        $this->assertSame(1, ConvertedProposal::count());
        $this->assertSame('Reprovada', $proposal->fresh()->status);
        $this->assertNull($this->client->fresh()->address);
    }

    public function test_rejected_and_expired_proposals_cannot_be_accepted_by_the_client(): void
    {
        $rejected = $this->proposal(['status' => 'Reprovada']);
        $this->post(route('proposals.accept', $rejected->proposal_code))->assertSessionHas('error');

        $expired = $this->proposal();
        $expired->forceFill(['created_at' => now()->subDays(Proposal::VALIDITY_DAYS + 1)])->saveQuietly();
        $this->post(route('proposals.accept', $expired->proposal_code))->assertSessionHas('error');

        $this->assertSame(0, ConvertedProposal::count());
        $this->assertTrue($this->client->fresh()->is_lead);
    }

    public function test_backoffice_can_still_accept_an_expired_proposal(): void
    {
        $proposal = $this->proposal();
        $proposal->forceFill(['created_at' => now()->subDays(40)])->saveQuietly();

        $this->actingAs($this->backofficeUser('admin'))
            ->postJson(route('admin.v2.proposals.accept', $proposal->id))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($this->backofficeUser('admin'))
            ->postJson(route('admin.v2.proposals.accept', $proposal->id))
            ->assertUnprocessable();

        $this->assertSame(1, ConvertedProposal::count());
    }

    public function test_rejected_proposal_page_hides_the_accept_button(): void
    {
        $proposal = $this->proposal(['status' => 'Reprovada']);

        $this->get(route('proposals.detail', $proposal->proposal_code))
            ->assertOk()
            ->assertDontSee('id="acceptForm"', false);
    }

    // ── Anti-spam ────────────────────────────────────────────────────

    public function test_honeypot_and_too_fast_submissions_are_dropped_silently(): void
    {
        $this->postJson(route('newsletter.subscribe'), ['email' => 'robo@example.com', 'company_website' => 'http://spam'])
            ->assertOk()->assertJsonPath('success', true);

        $this->postJson(route('newsletter.subscribe'), ['email' => 'rapido@example.com', '_form_started' => time()])
            ->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseMissing('clients', ['email' => 'robo@example.com']);
        $this->assertDatabaseMissing('clients', ['email' => 'rapido@example.com']);

        $this->postJson(route('newsletter.subscribe'), ['email' => 'pessoa@example.com', '_form_started' => time() - 10, 'company_website' => '']);
        $this->assertDatabaseHas('clients', ['email' => 'pessoa@example.com']);
    }

    public function test_public_forms_include_the_honeypot(): void
    {
        $this->get(route('frontend.form-import'))->assertOk()->assertSee('name="company_website"', false);
        $this->get(route('frontend.cost-simulator'))->assertOk()->assertSee('name="company_website"', false);
    }

    // ── Cotações convertidas ─────────────────────────────────────────

    private function converted(array $attributes = []): ConvertedProposal
    {
        return ConvertedProposal::create($attributes + ['client_id' => $this->client->id, 'status' => 'Iniciada', 'brand' => 'BMW', 'modelCar' => 'i4']);
    }

    public function test_status_change_only_emails_when_it_really_changes(): void
    {
        $admin = $this->backofficeUser('admin');
        $converted = $this->converted();

        $this->actingAs($admin)->patchJson(route('converted-proposals.updateStatus', $converted->id), ['status' => 'Iniciada'])->assertOk();
        Mail::assertNothingSent();

        $this->actingAs($admin)->patchJson(route('converted-proposals.updateStatus', $converted->id), ['status' => 'Inventado'])->assertUnprocessable();

        $this->actingAs($admin)->patchJson(route('converted-proposals.updateStatus', $converted->id), ['status' => 'Transporte'])->assertOk();
        Mail::assertSent(ProposalStatusUpdatedMail::class, 1);

        $this->client->update(['email' => null]);
        $this->actingAs($admin)->patchJson(route('converted-proposals.updateStatus', $converted->id), ['status' => 'IPO'])->assertOk();
        Mail::assertSent(ProposalStatusUpdatedMail::class, 1);
        $this->assertSame('IPO', $converted->fresh()->status);
    }

    public function test_cancellation_email_has_a_message_for_both_spellings(): void
    {
        $converted = $this->converted();

        foreach (['Cancelada', 'Cancelado'] as $status) {
            $html = (new ProposalStatusUpdatedMail($converted, 'Transporte', $status, 'Maria', null))->render();
            $this->assertStringContainsString('O processo foi cancelado', $html, $status);
        }
    }

    public function test_timeline_shows_the_model_and_works_without_a_proposal(): void
    {
        $converted = $this->converted(['status' => 'Cancelado', 'version' => 'eDrive40']);
        $converted->statusHistories()->create(['old_status' => 'Iniciada', 'new_status' => 'Cancelado']);

        $this->get(route('converted-proposals.timeline', ['bmw', 'i4', 'edrive40', $converted->id]))
            ->assertOk()
            ->assertSee('BMW i4')
            ->assertSee('Processo Cancelado')
            ->assertSee('Maria Silva');
    }

    public function test_paid_flags_can_be_unticked(): void
    {
        $admin = $this->backofficeUser('admin');
        $converted = $this->converted(['primeira_tranche_pago' => true, 'carro_pago' => true]);

        $this->actingAs($admin)->put(route('admin.v2.converted-proposals.update', $converted->id), [
            'client_id' => $this->client->id,
            'primeira_tranche_pago' => '0',
            'carro_pago' => '1',
        ])->assertSessionHasNoErrors();

        $converted->refresh();
        $this->assertFalse((bool) $converted->primeira_tranche_pago);
        $this->assertTrue((bool) $converted->carro_pago);
    }

    public function test_commission_toggle_is_not_nested_inside_the_main_form(): void
    {
        $angariador = $this->backofficeUser('angariador', ['commission_fixed_value' => 100]);
        $converted = $this->converted(['owner_id' => $angariador->id]);

        $html = $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v2.converted-proposals.edit', $converted->id))
            ->assertOk()
            ->assertSee('form="commissionToggleForm"', false)
            ->getContent();

        $start = strpos($html, '<form action="' . route('admin.v2.converted-proposals.update', $converted->id) . '"');
        $this->assertNotFalse($start);
        $mainForm = substr($html, $start + 5, strpos($html, '</form>', $start) - $start - 5);
        $this->assertStringNotContainsString('<form', $mainForm, 'Há um <form> dentro do formulário principal.');
        $this->assertStringContainsString('id="commissionToggleForm"', $html);

        $this->actingAs($this->backofficeUser('admin'))->post(route('admin.v2.angariadores.toggle-paid', $converted->id));
        $this->assertTrue((bool) $converted->fresh()->comissao_paga);
    }
}
