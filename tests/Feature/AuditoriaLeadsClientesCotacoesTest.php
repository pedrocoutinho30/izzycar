<?php

namespace Tests\Feature;

use App\Mail\ProposalSentMail;
use App\Models\Client;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Models\Setting;
use App\Services\ProposalService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Correções da auditoria: email repetido forçado no desktop (leads/clientes),
 * LOG-006 (emails do observer), LOG-010 (transação em create) e SEG-009
 * (formulário público de importação).
 */
class AuditoriaLeadsClientesCotacoesTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();
        Notification::fake();
        foreach (['email' => 'geral@izzycar.pt', 'phone' => '928459346', 'facebook' => 'x', 'insta' => 'x'] as $label => $value) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => $value]);
        }
    }

    // ---- Email repetido com "criar mesmo assim" ----

    public function test_lead_com_email_repetido_e_confirmar_duplicado_da_erro_de_validacao_e_nao_500(): void
    {
        Client::create(['name' => 'Existente', 'email' => 'dup@example.com', 'phone' => '911111111']);

        $this->actingAs($this->backofficeUser())
            ->post(route('admin.v2.leads.store'), [
                'name' => 'Outra', 'email' => 'DUP@example.com', 'phone' => '922222222', 'confirm_duplicate' => '1',
            ])
            ->assertSessionHasErrors('duplicate')
            ->assertSessionHas('duplicate_email', true);

        $this->assertSame(1, Client::count());
    }

    public function test_cliente_com_email_repetido_e_confirmar_duplicado_da_erro_de_validacao_e_nao_500(): void
    {
        Client::create(['name' => 'Existente', 'email' => 'dup@example.com']);

        $this->actingAs($this->backofficeUser())
            ->post(route('admin.v2.clients.store'), [
                'name' => 'Outro', 'email' => 'dup@example.com', 'confirm_duplicate' => '1',
            ])
            ->assertSessionHasErrors('duplicate');

        $this->assertSame(1, Client::count());
    }

    public function test_editar_cliente_para_email_de_outro_da_erro_de_validacao(): void
    {
        Client::create(['name' => 'A', 'email' => 'a@example.com']);
        $b = Client::create(['name' => 'B', 'email' => 'b@example.com']);

        $this->actingAs($this->backofficeUser())
            ->put(route('admin.v2.clients.update', $b->id), ['name' => 'B', 'email' => 'a@example.com'])
            ->assertSessionHasErrors('email');

        $this->assertSame('b@example.com', $b->fresh()->email);
    }

    public function test_telefone_repetido_continua_a_poder_ser_confirmado(): void
    {
        Client::create(['name' => 'Existente', 'email' => 'x@example.com', 'phone' => '911111111']);

        $this->actingAs($this->backofficeUser())
            ->post(route('admin.v2.leads.store'), ['name' => 'Outra', 'email' => 'y@example.com', 'phone' => '911111111'])
            ->assertSessionHasErrors('duplicate');

        $this->actingAs($this->backofficeUser())
            ->post(route('admin.v2.leads.store'), ['name' => 'Outra', 'email' => 'y@example.com', 'phone' => '911111111', 'confirm_duplicate' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Client::count());
    }

    // ---- LOG-006 ----

    public function test_falha_de_email_ao_enviar_cotacao_nao_deixa_a_cotacao_a_meio(): void
    {
        $lead = Client::create(['name' => 'Ana', 'email' => 'ana@example.com', 'is_lead' => true, 'lead_status' => 'em_contacto']);
        $proposal = Proposal::create([
            'client_id' => $lead->id, 'brand' => 'Volvo', 'model' => 'XC40', 'transport_cost' => 1000,
            'commission_cost' => 500, 'proposal_code' => 'VXABCDEFGH12', 'status' => 'Pendente',
        ]);

        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP em baixo para ana@example.com'));
        Log::spy();

        $proposal->update(['status' => 'Enviado']);

        $this->assertSame('Enviado', $proposal->fresh()->status);
        $this->assertTrue(LeadActivity::where('client_id', $lead->id)->where('title', "Cotação #{$proposal->id} enviada ao cliente")->exists());
        $this->assertNotNull($lead->fresh()->next_followup_at);
        $this->assertTrue(LeadActivity::where('client_id', $lead->id)->where('title', "Falha ao enviar o email da cotação #{$proposal->id}")->exists());

        // Log sem dados pessoais: nem o email nem a mensagem da exceção.
        Log::shouldHaveReceived('error')->withArgs(function ($mensagem, $contexto) {
            return !str_contains(json_encode($contexto), 'ana@example.com')
                && ($contexto['exception'] ?? null) === \RuntimeException::class;
        })->once();
    }

    public function test_envio_normal_continua_a_enviar_o_email_ao_cliente(): void
    {
        $lead = Client::create(['name' => 'Ana', 'email' => 'ana@example.com', 'is_lead' => true, 'lead_status' => 'em_contacto']);
        Proposal::create([
            'client_id' => $lead->id, 'brand' => 'Volvo', 'model' => 'XC40', 'transport_cost' => 1000,
            'commission_cost' => 500, 'proposal_code' => 'VXABCDEFGH12', 'status' => 'Enviado',
        ]);

        Mail::assertSent(ProposalSentMail::class, fn ($m) => $m->hasTo('ana@example.com'));
    }

    // ---- LOG-010 ----

    public function test_falha_a_meio_do_create_faz_rollback_da_cotacao(): void
    {
        $lead = Client::create(['name' => 'Ana', 'email' => 'ana@example.com', 'is_lead' => true]);
        $pedido = FormProposal::create(['name' => 'Ana', 'phone' => '912345678', 'email' => 'ana@example.com', 'status' => 'novo']);

        // Falha ao atualizar o pedido de origem (último passo antes da timeline).
        FormProposal::updating(fn () => throw new \RuntimeException('falha injetada'));

        try {
            app(ProposalService::class)->create([
                'client_id' => $lead->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 800, 'commission_cost' => 600,
            ], null, $pedido->id, null);
            $this->fail('Devia ter lançado a exceção injetada.');
        } catch (\RuntimeException $e) {
            $this->assertSame('falha injetada', $e->getMessage());
        }

        $this->assertSame(0, Proposal::count());
        $this->assertNull($pedido->fresh()->proposal_id);
        $this->assertSame(0, LeadActivity::where('client_id', $lead->id)->count());
    }

    // ---- SEG-009 ----

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Maria Silva', 'phone' => '912 345 678', 'email' => 'maria@example.com',
            '_form_started' => time() - 10, // anti-spam: o formulário real envia sempre a hora de abertura
            'buyer_type' => 'particular', 'payment_type' => 'pronto_pagamento', 'estimated_purchase_date' => 'imediato',
            'data_processing_consent' => '1', 'ad_option' => 'nao_sei', 'brand' => 'BMW', 'model' => 'i4',
            'submodel' => 'M50', 'year_min' => 2020, 'km_max' => '+200000', 'gearbox' => 'automatica',
        ];
    }

    public function test_formulario_de_importacao_ignora_campos_que_nao_sao_do_formulario(): void
    {
        $this->post(route('frontend.import-submit'), $this->payload([
            'created_by' => 1, 'proposal_id' => 99, 'origin' => 'manual', 'title' => 'Hack', 'status' => 'concluido', 'client_id' => 555,
        ]))->assertSuccessful();

        $pedido = FormProposal::where('email', 'maria@example.com')->firstOrFail();
        $this->assertNull($pedido->created_by);
        $this->assertNull($pedido->proposal_id);
        $this->assertSame('site', $pedido->origin); // omissão da BD, não o valor do pedido
        $this->assertNull($pedido->title);
        $this->assertSame('novo', $pedido->status);
        $this->assertSame(Client::where('email', 'maria@example.com')->value('id'), (int) $pedido->client_id);
        // Campos legítimos continuam a ser gravados.
        $this->assertSame('BMW', $pedido->brand);
        $this->assertSame('M50', $pedido->version);
        $this->assertSame('automatica', $pedido->gearbox);
        $this->assertSame(200000, (int) $pedido->km_max);
    }

    public function test_formulario_de_importacao_valida_os_campos_de_preferencias(): void
    {
        $this->postJson(route('frontend.import-submit'), $this->payload(['gearbox' => 'turbo']))->assertStatus(422)->assertJsonValidationErrors('gearbox');
        $this->postJson(route('frontend.import-submit'), $this->payload(['km_max' => 'muitos']))->assertStatus(422)->assertJsonValidationErrors('km_max');
        $this->assertSame(0, FormProposal::count());
    }

    public function test_codigo_de_angariador_inexistente_e_ignorado_e_o_valido_atribui_dono(): void
    {
        $this->post(route('frontend.import-submit'), $this->payload(['angariador' => 'naoexiste']))->assertSuccessful();
        $cliente = Client::where('email', 'maria@example.com')->firstOrFail();
        $this->assertNull($cliente->angariador_code);
        $this->assertNull($cliente->owner_id);
        $this->assertNull(FormProposal::firstOrFail()->angariador_code);

        $angariador = $this->backofficeUser('angariador', ['referral_code' => 'ANG1']);
        $this->post(route('frontend.import-submit'), $this->payload(['email' => 'joao@example.com', 'phone' => '913456789', 'name' => 'Joao', 'angariador' => 'ANG1']))->assertSuccessful();
        $cliente = Client::where('email', 'joao@example.com')->firstOrFail();
        $this->assertSame('ANG1', $cliente->angariador_code);
        $this->assertSame($angariador->id, $cliente->owner_id);
    }
}
