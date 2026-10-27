<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\LeadActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Caracterização das rotas V2 de clientes: o ClientService partilhado com o
 * mobile não pode mudar o comportamento nem as mensagens do desktop.
 */
class ClientServiceTest extends TestCase
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

    /** Todos os campos do formulário do desktop. */
    private function dados(array $extra = []): array
    {
        return $extra + [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'phone' => '912 345 678',
            'language' => 'en',
            'vat_number' => '123456789',
            'birth_date' => '1980-05-02',
            'gender' => 'feminino',
            'identification_number' => '12345678 9ZZ0',
            'validate_identification_number' => '2031-03-15',
            'address' => 'Rua A, 1',
            'postal_code' => '4000-001',
            'city' => 'Porto',
            'client_type' => 'particular',
            'origin' => 'Google',
            'observation' => 'Quer um elétrico',
        ];
    }

    public function test_criar_guarda_todos_os_campos_e_aplica_consentimentos_por_omissao(): void
    {
        $this->actingAs($this->admin)->post(route('admin.v2.clients.store'), $this->dados())
            ->assertRedirect(route('admin.v2.clients.index'))
            ->assertSessionHas('success', 'Cliente criado com sucesso!');

        $client = Client::sole();
        $this->assertFalse((bool) $client->is_lead);
        $this->assertSame('912 345 678', $client->phone);
        $this->assertSame('912345678', $client->phone_key);
        $this->assertSame('en', $client->language);
        $this->assertSame('feminino', $client->gender);
        $this->assertSame('12345678 9ZZ0', $client->identification_number);
        $this->assertSame('2031-03-15', $client->validate_identification_number->format('Y-m-d'));
        $this->assertSame('1980-05-02', $client->birth_date->format('Y-m-d'));
        $this->assertSame('particular', $client->client_type);
        $this->assertSame('Google', $client->origin);
        $this->assertTrue((bool) $client->data_processing_consent);
        $this->assertFalse((bool) $client->newsletter_consent);
        $this->assertNull($client->owner_id);
    }

    public function test_criar_respeita_consentimentos_escolhidos_e_ignora_campos_fora_das_regras(): void
    {
        $this->actingAs($this->admin)->post(route('admin.v2.clients.store'), $this->dados([
            'data_processing_consent' => '0',
            'newsletter_consent' => '1',
            'is_lead' => '1',
            'owner_id' => $this->admin->id,
        ]))->assertSessionHasNoErrors();

        $client = Client::sole();
        $this->assertFalse((bool) $client->data_processing_consent);
        $this->assertTrue((bool) $client->newsletter_consent);
        $this->assertFalse((bool) $client->is_lead);
        $this->assertNull($client->owner_id);
    }

    public function test_criar_valida_campos(): void
    {
        $this->actingAs($this->admin)->post(route('admin.v2.clients.store'), $this->dados(['name' => '', 'email' => 'x', 'language' => 'fr', 'validate_identification_number' => 'ontem']))
            ->assertSessionHasErrors(['name', 'email', 'language', 'validate_identification_number']);
        $this->assertSame(0, Client::count());
    }

    public function test_criar_com_contacto_repetido_avisa_e_pode_confirmar(): void
    {
        $existente = Client::create(['name' => 'Ana', 'phone' => '+351912345678', 'is_lead' => true, 'lead_status' => 'nova']);

        $this->actingAs($this->admin)->post(route('admin.v2.clients.store'), $this->dados(['email' => null]))
            ->assertSessionHasErrors(['duplicate' => 'Já existe «Ana» com o mesmo telefone.'])
            ->assertSessionHas('duplicate_url', route('admin.v2.leads.show', $existente->id));
        $this->assertSame(1, Client::count());

        $this->actingAs($this->admin)->post(route('admin.v2.clients.store'), $this->dados(['email' => null, 'confirm_duplicate' => '1']))
            ->assertRedirect(route('admin.v2.clients.index'));
        $this->assertSame(2, Client::count());
    }

    public function test_atualizar_guarda_campos_sem_verificar_duplicados(): void
    {
        Client::create(['name' => 'Outra', 'phone' => '912345678', 'is_lead' => false]);
        $client = Client::create(['name' => 'Maria', 'is_lead' => false, 'data_processing_consent' => true]);

        $this->actingAs($this->admin)->put(route('admin.v2.clients.update', $client->id), $this->dados(['data_processing_consent' => '0', 'newsletter_consent' => '1', 'is_lead' => '1']))
            ->assertRedirect(route('admin.v2.clients.show', $client->id))
            ->assertSessionHas('success', 'Cliente atualizado com sucesso!');

        $client->refresh();
        $this->assertSame('Maria Silva', $client->name);
        $this->assertSame('912345678', $client->phone_key);
        $this->assertSame('12345678 9ZZ0', $client->identification_number);
        $this->assertSame('2031-03-15', $client->validate_identification_number->format('Y-m-d'));
        $this->assertFalse((bool) $client->data_processing_consent);
        $this->assertTrue((bool) $client->newsletter_consent);
        $this->assertFalse((bool) $client->is_lead);
    }

    public function test_atualizar_abre_tambem_leads_sem_as_converter(): void
    {
        $lead = Client::create(['name' => 'Lead', 'is_lead' => true, 'lead_status' => 'nova']);

        $this->actingAs($this->admin)->get(route('admin.v2.clients.show', $lead->id))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.v2.clients.edit', $lead->id))->assertOk();
        $this->actingAs($this->admin)->put(route('admin.v2.clients.update', $lead->id), ['name' => 'Lead Editada'])
            ->assertRedirect(route('admin.v2.clients.show', $lead->id));

        $this->assertSame('Lead Editada', $lead->refresh()->name);
        $this->assertTrue((bool) $lead->is_lead);
    }

    public function test_followup_valida_data_futura_e_regista_atividade(): void
    {
        $client = Client::create(['name' => 'Maria', 'is_lead' => false]);

        $this->actingAs($this->admin)->post(route('admin.v2.clients.followup', $client->id), ['next_followup_at' => now()->subHour()->format('Y-m-d H:i')])
            ->assertSessionHasErrors('next_followup_at');

        $data = now()->addDay()->setTime(10, 30);
        $this->actingAs($this->admin)->post(route('admin.v2.clients.followup', $client->id), ['next_followup_at' => $data->format('Y-m-d H:i'), 'followup_note' => 'Ligar'])
            ->assertSessionHas('success', 'Follow-up agendado.');

        $client->refresh();
        $this->assertSame($data->format('Y-m-d H:i'), $client->next_followup_at->format('Y-m-d H:i'));
        $this->assertSame('Ligar', $client->followup_note);
        $atividade = LeadActivity::where('client_id', $client->id)->sole();
        $this->assertSame('Follow-up agendado para ' . Carbon::parse($data)->format('d/m/Y H:i'), $atividade->title);
        $this->assertSame('Ligar', $atividade->body);
        $this->assertSame('warning', $atividade->color);
    }

    public function test_atividade_valida_tipo_e_regista_nota_do_utilizador(): void
    {
        $client = Client::create(['name' => 'Maria', 'is_lead' => false]);

        $this->actingAs($this->admin)->post(route('admin.v2.clients.activity', $client->id), ['type' => 'system', 'title' => 'x'])
            ->assertSessionHasErrors('type');

        $this->actingAs($this->admin)->post(route('admin.v2.clients.activity', $client->id), ['type' => 'call', 'title' => 'Liguei', 'body' => 'Não atendeu'])
            ->assertSessionHas('success', 'Atividade registada.');

        $this->assertDatabaseHas('lead_activities', ['client_id' => $client->id, 'type' => 'call', 'title' => 'Liguei', 'body' => 'Não atendeu', 'user_id' => $this->admin->id]);
    }
}
