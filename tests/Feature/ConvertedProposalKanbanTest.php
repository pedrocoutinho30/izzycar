<?php

namespace Tests\Feature;

use App\Mail\ProposalStatusUpdatedMail;
use App\Models\Client;
use App\Models\ConvertedProposal;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ConvertedProposalKanbanTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();

        $this->client = Client::create(['name' => 'Maria', 'email' => 'maria@example.com', 'is_lead' => false, 'lead_status' => 'nova']);
    }

    private function converted(string $model, ?string $status, ?Client $client = null): ConvertedProposal
    {
        return ConvertedProposal::forceCreate(['client_id' => ($client ?? $this->client)->id, 'brand' => 'Audi', 'modelCar' => $model, 'status' => $status]);
    }

    private function changeStatus(ConvertedProposal $converted, array $data)
    {
        return $this->actingAs($this->backofficeUser())->patchJson(route('admin.v2.converted-proposals.update-status', $converted->id), $data);
    }

    public function test_board_column_groups_statuses(): void
    {
        $this->assertSame('Iniciada', (new ConvertedProposal(['status' => null]))->boardColumn());  // a coluna é NOT NULL, mas o modelo aguenta-o
        $this->assertSame('Iniciada', $this->converted('B', 'Em Processo')->boardColumn());  // estado desconhecido
        $this->assertSame('IPO', $this->converted('C', 'IPO')->boardColumn());
        $this->assertSame('done', $this->converted('D', 'Concluido')->boardColumn());
        $this->assertSame('cancelled', $this->converted('E', 'Cancelada')->boardColumn());
        $this->assertSame('cancelled', $this->converted('F', 'Cancelado')->boardColumn());
    }

    public function test_kanban_shows_in_progress_cards_and_only_counts_done_and_cancelled(): void
    {
        $this->converted('ModeloIpo', 'IPO');
        $this->converted('ModeloFeito', 'Concluido');
        $this->converted('ModeloCancelado', 'Cancelada');

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.v2.converted-proposals.kanban'))->assertOk();

        $this->assertCount(11, $response->viewData('columns'));
        $this->assertNotContains('Concluido', $response->viewData('columns'));
        $this->assertSame(1, $response->viewData('doneCount'));
        $this->assertSame(1, $response->viewData('cancelledCount'));
        $response->assertSee('ModeloIpo')->assertDontSee('ModeloFeito')->assertDontSee('ModeloCancelado')
            ->assertSee('Largue aqui para concluir')->assertSee('Largue aqui para cancelar');
    }

    public function test_list_and_detail_link_to_the_kanban_and_the_confirmation_modal(): void
    {
        $converted = $this->converted('Detalhe', 'Iniciada');
        $admin = $this->backofficeUser();

        $this->actingAs($admin)->get(route('admin.v2.converted-proposals.index'))->assertOk()
            ->assertSee(route('admin.v2.converted-proposals.kanban'));
        $this->get(route('admin.v2.converted-proposals.edit', $converted->id))->assertOk()
            ->assertSee('statusChangeModal')->assertSee('Enviar email ao cliente');
    }

    public function test_status_change_can_skip_the_email(): void
    {
        $converted = $this->converted('Silencioso', 'Iniciada');

        $this->changeStatus($converted, ['status' => 'DAV', 'notify' => false])->assertOk()->assertJson(['notified' => false]);

        $this->assertSame('DAV', $converted->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_status_change_sends_the_email_when_asked_or_when_not_specified(): void
    {
        $converted = $this->converted('Avisado', 'Iniciada');

        $this->changeStatus($converted, ['status' => 'Transporte', 'notify' => true])->assertOk()->assertJson(['notified' => true]);
        Mail::assertSent(ProposalStatusUpdatedMail::class, 1);

        // Sem o campo, mantém-se o comportamento antigo (avisar).
        $this->changeStatus($converted, ['status' => 'IMT'])->assertOk()->assertJson(['notified' => true]);
        Mail::assertSent(ProposalStatusUpdatedMail::class, 2);
    }

    public function test_notify_true_without_client_email_sends_nothing(): void
    {
        $noEmail = Client::create(['name' => 'Sem Email', 'is_lead' => false, 'lead_status' => 'nova']);
        $converted = $this->converted('SemEmail', 'Iniciada', $noEmail);

        $this->changeStatus($converted, ['status' => 'Transporte', 'notify' => true])->assertOk()->assertJson(['notified' => false]);
        Mail::assertNothingSent();
    }

    public function test_dropping_in_the_zones_sets_done_and_cancelled(): void
    {
        $done = $this->converted('Fim', 'Registo automóvel');
        $cancelled = $this->converted('Cancela', 'IPO');

        $this->changeStatus($done, ['status' => 'Concluido', 'notify' => false])->assertOk();
        $this->changeStatus($cancelled, ['status' => 'Cancelada', 'notify' => false])->assertOk();

        $this->assertSame('Concluido', $done->fresh()->status);
        $this->assertTrue($cancelled->fresh()->isCancelled());
        $this->changeStatus($done, ['status' => 'Inventado', 'notify' => false])->assertUnprocessable();
    }
}
