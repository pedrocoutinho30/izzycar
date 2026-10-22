<?php

namespace Tests\Feature;

use App\Enums\OpportunityStatus;
use App\Mail\ProposalSentMail;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\LeadActivity;
use App\Models\Proposal;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ProposalKanbanTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private Client $client;

    private int $codes = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();

        $this->client = Client::create(['name' => 'Maria', 'email' => 'maria@example.com', 'is_lead' => true, 'lead_status' => 'nova']);
    }

    private function proposal(string $model, string $status = 'Pendente'): Proposal
    {
        return Proposal::create([
            'client_id' => $this->client->id, 'brand' => 'Volvo', 'model' => $model, 'transport_cost' => 0,
            'proposal_code' => 'VX' . str_pad((string) ++$this->codes, 10, '0', STR_PAD_LEFT), 'status' => $status,
        ]);
    }

    private function changeStatus(Proposal $proposal, array $data)
    {
        return $this->actingAs($this->backofficeUser())->patchJson(route('admin.v2.proposals.updateStatus', $proposal->id), $data);
    }

    public function test_kanban_shows_every_status_and_limits_the_closed_ones(): void
    {
        $this->proposal('ModeloPendente');
        $this->proposal('ModeloEnviado', 'Enviado');
        $this->proposal('ModeloReprovado', 'Reprovada');
        for ($i = 0; $i < 32; $i++) {
            $this->proposal('Muitas' . $i, 'Sem resposta');
        }

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.v2.proposals.kanban'))->assertOk();
        $columns = $response->viewData('columns');

        $this->assertSame(['Pendente', 'Enviado', 'Aprovada', 'Reprovada', 'Sem resposta'], array_keys($columns));
        $this->assertSame(32, $columns['Sem resposta']['total']);
        $this->assertCount(30, $columns['Sem resposta']['items']);
        $response->assertSee('ModeloPendente')->assertSee('ModeloEnviado')->assertSee('ModeloReprovado')->assertSee('+ 2 mais na');
    }

    public function test_list_links_to_the_kanban(): void
    {
        $this->actingAs($this->backofficeUser())->get(route('admin.v2.proposals.index'))->assertOk()
            ->assertSee(route('admin.v2.proposals.kanban'));
    }

    public function test_sending_with_email_notifies_the_client_and_without_it_does_not(): void
    {
        $withEmail = $this->proposal('ComEmail');
        $this->changeStatus($withEmail, ['status' => 'Enviado', 'notify' => true])->assertOk()->assertJson(['notified' => true]);
        Mail::assertSent(ProposalSentMail::class, fn ($mail) => $mail->hasTo('maria@example.com'));

        Mail::fake();
        $without = $this->proposal('SemEmail');
        $this->changeStatus($without, ['status' => 'Enviado', 'notify' => false])->assertOk()->assertJson(['notified' => false]);

        Mail::assertNothingSent();
        $this->assertSame('Enviado', $without->fresh()->status);
        // O follow-up e o registo na lead mantêm-se, a dizer que foi sem email.
        $this->assertNotNull($this->client->fresh()->next_followup_at);
        $this->assertTrue(LeadActivity::where('client_id', $this->client->id)->where('body', 'like', '%sem email%')->exists());
    }

    public function test_sending_without_the_field_keeps_the_old_behaviour(): void
    {
        $this->changeStatus($this->proposal('Antigo'), ['status' => 'Enviado'])->assertOk()->assertJson(['notified' => true]);

        Mail::assertSent(ProposalSentMail::class);
    }

    public function test_notify_is_ignored_for_other_statuses(): void
    {
        $proposal = $this->proposal('Outros', 'Enviado');
        Mail::fake(); // criar já "Enviado" envia o email; interessa só a mudança seguinte

        $this->changeStatus($proposal, ['status' => 'Sem resposta', 'notify' => true])->assertOk()->assertJson(['notified' => false]);

        Mail::assertNothingSent();
    }

    public function test_rejecting_marks_the_linked_opportunities_as_rejected(): void
    {
        $proposal = $this->proposal('Reprovar');
        $form = FormProposal::create(['name' => 'Maria', 'email' => 'maria@example.com', 'phone' => '912345678', 'client_id' => $this->client->id, 'brand' => 'Volvo', 'model' => 'XC40']);
        $opportunity = $form->opportunities()->create(['brand' => 'Volvo', 'model' => 'XC40', 'proposal_id' => $proposal->id, 'status' => OpportunityStatus::Selected->value]);

        $this->changeStatus($proposal, ['status' => 'Reprovada'])->assertOk();

        $this->assertSame(OpportunityStatus::Rejected, $opportunity->fresh()->status);
    }

    public function test_approving_creates_the_converted_proposal_and_an_accepted_one_cannot_move(): void
    {
        $proposal = $this->proposal('Aceitar');

        $this->changeStatus($proposal, ['status' => 'Aprovada'])->assertOk();
        $this->assertSame(1, ConvertedProposal::where('proposal_id', $proposal->id)->count());

        $this->changeStatus($proposal, ['status' => 'Pendente'])->assertStatus(422)->assertJsonPath('success', false);
        $this->assertSame('Aprovada', $proposal->fresh()->status);

        $page = $this->actingAs($this->backofficeUser())->get(route('admin.v2.proposals.kanban'))->assertOk();
        $this->assertStringContainsString('já aceite', $page->getContent());
    }
}
