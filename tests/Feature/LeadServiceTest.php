<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Caracterização das rotas V2 de leads: o LeadService partilhado com o mobile
 * não pode mudar o comportamento nem o texto das atividades do desktop.
 */
class LeadServiceTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        $this->admin = $this->backofficeUser('admin');
    }

    private function lead(array $attributes = []): Client
    {
        return Client::create($attributes + ['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova']);
    }

    public function test_criar_lead_manual_aplica_omissoes_e_regista_atividade(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.v2.leads.store'), ['name' => 'Rui Manual', 'phone' => '912000111']);

        $lead = Client::where('name', 'Rui Manual')->firstOrFail();
        $response->assertRedirect(route('admin.v2.leads.show', $lead->id))->assertSessionHas('success', 'Lead criado com sucesso.');

        $this->assertTrue((bool) $lead->is_lead);
        $this->assertSame('manual', $lead->lead_source);
        $this->assertSame('nova', $lead->lead_status);
        $this->assertSame('Manual BO', $lead->origin);
        $this->assertDatabaseHas('lead_activities', [
            'client_id' => $lead->id,
            'title' => 'Lead criado manualmente',
            'body' => 'Criado por ' . $this->admin->name . '.',
            'icon' => 'bi-plus-circle-fill',
            'color' => 'success',
            'type' => 'system',
        ]);
    }

    public function test_criar_lead_valida_e_avisa_duplicados(): void
    {
        $this->actingAs($this->admin)->post(route('admin.v2.leads.store'), ['name' => '', 'lead_status' => 'xpto'])
            ->assertSessionHasErrors(['name', 'lead_status']);

        $this->lead(['phone' => '912000111']);
        $this->actingAs($this->admin)->post(route('admin.v2.leads.store'), ['name' => 'Outra', 'phone' => '912000111'])
            ->assertSessionHasErrors('duplicate');
        $this->assertSame(1, Client::count());
    }

    public function test_mudar_estado_por_formulario_volta_atras_com_mensagem(): void
    {
        $lead = $this->lead();

        $this->actingAs($this->admin)->from(route('admin.v2.leads.show', $lead->id))
            ->post(route('admin.v2.leads.status', $lead->id), ['status' => 'perdida'])
            ->assertRedirect(route('admin.v2.leads.show', $lead->id))
            ->assertSessionHas('success', 'Estado da lead atualizado.');

        $this->assertSame('perdida', $lead->refresh()->lead_status);
        $this->assertDatabaseHas('lead_activities', [
            'client_id' => $lead->id, 'title' => 'Estado alterado: Nova → Perdida', 'icon' => 'bi-arrow-repeat', 'color' => 'danger',
        ]);

        // Um valor fora da lista (escrito diretamente na BD) aparece tal como está.
        $estranho = $this->lead(['lead_status' => 'antigo']);
        $this->actingAs($this->admin)->post(route('admin.v2.leads.status', $estranho->id), ['status' => 'em_contacto']);
        $this->assertDatabaseHas('lead_activities', ['client_id' => $estranho->id, 'title' => 'Estado alterado: antigo → Em Contacto', 'color' => 'info']);

        $this->actingAs($this->admin)->post(route('admin.v2.leads.status', $lead->id), ['status' => 'xpto'])->assertSessionHasErrors('status');
    }

    public function test_converter_lead_em_cliente(): void
    {
        $lead = $this->lead();

        $this->actingAs($this->admin)->post(route('admin.v2.leads.convert', $lead->id))
            ->assertRedirect(route('admin.v2.clients.edit', $lead->id))
            ->assertSessionHas('success', 'Lead convertido em cliente! Complete os dados em falta.');

        $lead->refresh();
        $this->assertFalse((bool) $lead->is_lead);
        $this->assertNotNull($lead->converted_at);
        $this->assertDatabaseHas('lead_activities', [
            'client_id' => $lead->id,
            'title' => 'Lead convertido em cliente',
            'body' => 'Convertido manualmente pelo utilizador ' . $this->admin->name . '.',
            'icon' => 'bi-person-check-fill',
            'color' => 'success',
        ]);
    }

    public function test_agendar_followup(): void
    {
        $lead = $this->lead();
        $quando = now()->addDays(2)->setTime(10, 30);

        $this->actingAs($this->admin)->post(route('admin.v2.leads.followup', $lead->id), [
            'next_followup_at' => $quando->format('Y-m-d H:i'),
            'followup_note' => 'Confirmar interesse',
        ])->assertSessionHas('success', 'Follow-up agendado.');

        $lead->refresh();
        $this->assertSame($quando->format('Y-m-d H:i'), $lead->next_followup_at->format('Y-m-d H:i'));
        $this->assertSame('Confirmar interesse', $lead->followup_note);
        $this->assertDatabaseHas('lead_activities', [
            'client_id' => $lead->id,
            'title' => 'Follow-up agendado para ' . $quando->format('d/m/Y H:i'),
            'body' => 'Confirmar interesse',
            'icon' => 'bi-alarm-fill',
            'color' => 'warning',
        ]);

        $this->actingAs($this->admin)->post(route('admin.v2.leads.followup', $lead->id), ['next_followup_at' => now()->subDay()->format('Y-m-d H:i')])
            ->assertSessionHasErrors('next_followup_at');
    }

    public function test_registar_atividade_manual(): void
    {
        $lead = $this->lead();

        $this->actingAs($this->admin)->post(route('admin.v2.leads.activity', $lead->id), ['type' => 'call', 'title' => 'Liguei, não atendeu'])
            ->assertSessionHas('success', 'Actividade registada.');

        $this->assertDatabaseHas('lead_activities', [
            'client_id' => $lead->id, 'user_id' => $this->admin->id, 'type' => 'call', 'title' => 'Liguei, não atendeu', 'body' => '',
            'icon' => LeadActivity::TYPES['call']['icon'], 'color' => LeadActivity::TYPES['call']['color'],
        ]);

        $this->actingAs($this->admin)->post(route('admin.v2.leads.activity', $lead->id), ['type' => 'system', 'title' => 'x'])
            ->assertSessionHasErrors('type');
    }

    public function test_atribuir_e_retirar_proprietario(): void
    {
        $lead = $this->lead();
        $angariador = $this->backofficeUser('angariador', ['name' => 'Joana']);

        $this->actingAs($this->admin)->post(route('admin.v2.leads.assign-owner', $lead->id), ['owner_id' => $angariador->id])
            ->assertSessionHas('success', 'Proprietário da lead actualizado.');
        $this->assertSame($angariador->id, (int) $lead->refresh()->owner_id);
        $this->assertDatabaseHas('lead_activities', [
            'client_id' => $lead->id, 'title' => 'Proprietário da lead alterado para: Joana', 'body' => '', 'icon' => 'bi-person-badge', 'color' => 'info',
        ]);

        $this->actingAs($this->admin)->post(route('admin.v2.leads.assign-owner', $lead->id), ['owner_id' => '']);
        $this->assertNull($lead->refresh()->owner_id);
        $this->assertDatabaseHas('lead_activities', ['client_id' => $lead->id, 'title' => 'Proprietário da lead alterado para: sem proprietário']);
    }
}
