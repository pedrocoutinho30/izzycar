<?php

namespace Tests\Feature;

use App\Enums\OpportunityStatus;
use App\Mail\ProposalSentMail;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Models\ProposalAttributeValue;
use App\Models\VehicleAttribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Caracterização das rotas de cotações do desktop (ProposalV2Controller):
 * fixa o comportamento antes e depois de a lógica passar para ProposalService.
 */
class ProposalServiceTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private Client $lead;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();

        $this->lead = Client::create(['name' => 'Ana', 'email' => 'ana@example.com', 'is_lead' => true, 'lead_status' => 'em_contacto']);
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'client_id' => $this->lead->id,
            'brand' => 'BMW',
            'model' => 'Série 3',
            'version' => '320d',
            'year' => 2021,
            'fuel' => 'Diesel',
            'transport_cost' => 800,
            'commission_cost' => 615,
            'proposed_car_value' => 20000,
            'status' => 'Pendente',
        ];
    }

    private function store(array $payload)
    {
        return $this->actingAs($this->backofficeUser())->post(route('admin.v2.proposals.store'), $payload);
    }

    private function existing(array $attributes = []): Proposal
    {
        return Proposal::create($attributes + [
            'client_id' => $this->lead->id, 'brand' => 'Volvo', 'model' => 'XC40', 'transport_cost' => 1000,
            'commission_cost' => 500, 'proposal_code' => 'VXABCDEFGH12', 'status' => 'Pendente',
        ]);
    }

    public function test_store_cria_pendente_com_codigo_e_regista_atividade(): void
    {
        $this->store($this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.v2.proposals.index'))
            ->assertSessionHas('success', 'Proposta criada com sucesso!');

        $proposal = Proposal::sole();
        $this->assertSame('Pendente', $proposal->status);
        // Inicial da marca + inicial do modelo + 8 aleatórios, em maiúsculas: 10
        // caracteres (docs/cotacoes.md diz 12, mas o código sempre gerou 10).
        $this->assertMatchesRegularExpression('/^BS[A-Z0-9]{8}$/', $proposal->proposal_code);
        $this->assertSame($this->lead->id, $proposal->client_id);
        $this->assertSame(2021, (int) $proposal->year);
        $this->assertTrue(LeadActivity::where('client_id', $this->lead->id)->where('title', "Cotação #{$proposal->id} criada — BMW Série 3 320d")->exists());
        Mail::assertNothingSent();
    }

    public function test_store_valida_transport_cost_combustivel_e_estado(): void
    {
        $this->store(collect($this->payload())->except('transport_cost')->all())->assertSessionHasErrors('transport_cost');
        $this->store($this->payload(['fuel' => 'eletrico']))->assertSessionHasErrors('fuel');
        $this->store($this->payload(['status' => 'Enviada']))->assertSessionHasErrors('status');
        $this->store($this->payload(['client_id' => 999]))->assertSessionHasErrors('client_id');

        $this->assertSame(0, Proposal::count());
    }

    public function test_store_com_estado_enviado_passa_pelo_servico(): void
    {
        $this->store($this->payload(['status' => 'Enviado']))->assertSessionHasNoErrors();

        $proposal = Proposal::sole();
        $this->assertSame('Enviado', $proposal->status);
        Mail::assertSent(ProposalSentMail::class, fn ($mail) => $mail->hasTo('ana@example.com'));
        $this->assertNotNull($this->lead->fresh()->next_followup_at);
        $this->assertTrue(now()->addDays(2)->isSameDay($this->lead->fresh()->next_followup_at));
    }

    public function test_store_com_estado_aprovada_cria_a_convertida(): void
    {
        $this->store($this->payload(['status' => 'Aprovada']))->assertSessionHasNoErrors()->assertRedirect(route('admin.v2.proposals.index'));

        $proposal = Proposal::sole();
        $this->assertSame('Aprovada', $proposal->status);
        $this->assertSame(1, ConvertedProposal::where('proposal_id', $proposal->id)->count());
        $this->assertFalse((bool) $this->lead->fresh()->is_lead);
    }

    public function test_store_liga_pedido_novo_e_passa_a_em_analise(): void
    {
        $novo = FormProposal::create(['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678', 'client_id' => $this->lead->id, 'status' => 'novo']);
        $rejeitado = FormProposal::create(['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678', 'client_id' => $this->lead->id, 'status' => 'rejeitado']);

        $this->store($this->payload(['form_proposal_id' => $novo->id]))->assertSessionHasNoErrors();
        $first = Proposal::latest('id')->first();
        $this->assertSame('em_analise', $novo->fresh()->status);
        $this->assertSame($first->id, $novo->fresh()->proposal_id);

        $this->store($this->payload(['form_proposal_id' => $rejeitado->id]))->assertSessionHasNoErrors();
        $this->assertSame('rejeitado', $rejeitado->fresh()->status);
        $this->assertSame(Proposal::max('id'), $rejeitado->fresh()->proposal_id);
    }

    public function test_store_grava_atributos_e_imagem(): void
    {
        Storage::fake('public');
        $attribute = VehicleAttribute::create(['name' => 'Teto', 'key' => 'teto', 'type' => 'text', 'order' => 1]);

        $this->store($this->payload([
            'image' => UploadedFile::fake()->image('carro.jpg', 800, 600),
            'attributes' => [$attribute->id => 'Panorâmico', 9999 => ''],
            'other_links' => ['https://exemplo.pt/a'],
        ]))->assertSessionHasNoErrors();

        $proposal = Proposal::sole();
        $images = $proposal->images;
        $this->assertNotEmpty($images);
        Storage::disk('public')->assertExists(is_array($images) ? $images[0] : $images);
        $this->assertSame(['Panorâmico'], ProposalAttributeValue::where('proposal_id', $proposal->id)->pluck('value')->all());
        $this->assertSame(['https://exemplo.pt/a'], json_decode($proposal->other_links, true));
    }

    public function test_update_atualiza_recria_atributos_e_regista_mudanca_de_estado(): void
    {
        $attribute = VehicleAttribute::create(['name' => 'Teto', 'key' => 'teto', 'type' => 'text', 'order' => 1]);
        $proposal = $this->existing();
        ProposalAttributeValue::create(['proposal_id' => $proposal->id, 'attribute_id' => $attribute->id, 'value' => 'Antigo']);

        $this->actingAs($this->backofficeUser())
            ->put(route('admin.v2.proposals.update', $proposal->id), $this->payload(['status' => 'Enviado', 'transport_cost' => 900]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.v2.proposals.index'))
            ->assertSessionHas('success', 'Proposta atualizada com sucesso!');

        $proposal->refresh();
        $this->assertSame('BMW', $proposal->brand);
        $this->assertEquals(900, $proposal->transport_cost);
        $this->assertSame('Enviado', $proposal->status);
        $this->assertSame('VXABCDEFGH12', $proposal->proposal_code);
        $this->assertSame(0, ProposalAttributeValue::where('proposal_id', $proposal->id)->count());
        $this->assertTrue(LeadActivity::where('title', "Cotação #{$proposal->id}: estado alterado para Enviado")->exists());
        Mail::assertSent(ProposalSentMail::class);
    }

    public function test_update_recusa_mudar_estado_de_cotacao_aceite(): void
    {
        $proposal = $this->existing();
        $this->actingAs($this->backofficeUser())->postJson(route('admin.v2.proposals.accept', $proposal->id))->assertOk();

        $this->actingAs($this->backofficeUser())
            ->from(route('admin.v2.proposals.edit', $proposal->id))
            ->put(route('admin.v2.proposals.update', $proposal->id), $this->payload(['status' => 'Reprovada', 'brand' => 'Audi']))
            ->assertRedirect(route('admin.v2.proposals.edit', $proposal->id))
            ->assertSessionHasErrors(['status' => 'Esta cotação já foi aceite e não pode mudar de estado — para a anular, cancele a cotação convertida.']);

        $this->assertSame('Aprovada', $proposal->fresh()->status);
        $this->assertSame('Volvo', $proposal->fresh()->brand);

        // Com "Aprovada" grava os restantes dados.
        $this->actingAs($this->backofficeUser())
            ->put(route('admin.v2.proposals.update', $proposal->id), $this->payload(['status' => 'Aprovada', 'brand' => 'Audi']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.v2.proposals.index'));
        $this->assertSame('Audi', $proposal->fresh()->brand);
        $this->assertSame(1, ConvertedProposal::count());
    }

    public function test_update_status_responde_json_e_recusa_cotacao_aceite(): void
    {
        $proposal = $this->existing();

        $this->actingAs($this->backofficeUser())
            ->patchJson(route('admin.v2.proposals.updateStatus', $proposal->id), ['status' => 'Enviado'])
            ->assertOk()
            ->assertExactJson(['success' => true, 'status' => 'Enviado', 'notified' => true]);

        $this->actingAs($this->backofficeUser())
            ->patchJson(route('admin.v2.proposals.updateStatus', $proposal->id), ['status' => 'Enviada'])
            ->assertUnprocessable();

        $this->actingAs($this->backofficeUser())
            ->patchJson(route('admin.v2.proposals.updateStatus', $proposal->id), ['status' => 'Aprovada'])
            ->assertOk()->assertJson(['success' => true, 'status' => 'Aprovada', 'notified' => false]);

        $this->actingAs($this->backofficeUser())
            ->patchJson(route('admin.v2.proposals.updateStatus', $proposal->id), ['status' => 'Pendente'])
            ->assertStatus(422)
            ->assertJson(['success' => false, 'message' => "A cotação #{$proposal->id} já foi aceite e não pode mudar de estado — para a anular, cancele a cotação convertida."]);
        $this->assertSame('Aprovada', $proposal->fresh()->status);
    }

    public function test_accept_cria_convertida_converte_lead_e_pedido(): void
    {
        $proposal = $this->existing();
        $request = FormProposal::create(['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678', 'client_id' => $this->lead->id, 'status' => 'em_analise', 'proposal_id' => $proposal->id]);

        $response = $this->actingAs($this->backofficeUser())->postJson(route('admin.v2.proposals.accept', $proposal->id))->assertOk();
        $converted = ConvertedProposal::sole();

        $response->assertExactJson([
            'success' => true,
            'status' => 'Aprovada',
            'converted_proposal_id' => $converted->id,
            'redirect' => route('admin.v2.converted-proposals.edit', $converted->id),
        ]);
        $this->assertFalse((bool) $this->lead->fresh()->is_lead);
        $this->assertSame('convertido', $request->fresh()->status);
        Mail::assertNothingSent();

        $this->actingAs($this->backofficeUser())->postJson(route('admin.v2.proposals.accept', $proposal->id))
            ->assertStatus(422)->assertJson(['success' => false, 'message' => 'Esta cotação já foi aceite.']);
        $this->assertSame(1, ConvertedProposal::count());
    }

    public function test_reprovar_rejeita_as_oportunidades_ligadas(): void
    {
        $proposal = $this->existing();
        $request = FormProposal::create(['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678', 'client_id' => $this->lead->id]);
        $opportunity = $request->opportunities()->create(['brand' => 'Volvo', 'model' => 'XC40', 'proposal_id' => $proposal->id, 'status' => OpportunityStatus::Selected]);

        $this->actingAs($this->backofficeUser())
            ->patchJson(route('admin.v2.proposals.updateStatus', $proposal->id), ['status' => 'Reprovada'])
            ->assertOk();

        $this->assertSame(OpportunityStatus::Rejected, $opportunity->fresh()->status);
    }
}
