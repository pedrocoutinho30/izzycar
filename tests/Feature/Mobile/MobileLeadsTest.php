<?php

namespace Tests\Feature\Mobile;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\CostSimulator;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MobileLeadsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    public function test_lista_mostra_cartoes_com_pesquisa_e_filtro_de_estado(): void
    {
        Client::create(['name' => 'Ana Nova', 'phone' => '912345678', 'email' => 'ana@example.com', 'is_lead' => true, 'lead_status' => 'nova']);
        Client::create(['name' => 'Rui Frio', 'is_lead' => true, 'lead_status' => 'fria']);
        Client::create(['name' => 'Ana Cliente', 'is_lead' => false, 'lead_status' => 'nova']);

        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.leads.index', ['search' => 'Ana']))
            ->assertOk()->assertSee('Ana Nova')->assertDontSee('Rui Frio')->assertDontSee('Ana Cliente')
            ->assertSee('href="tel:912345678"', false)
            ->assertSee('href="https://wa.me/351912345678"', false)
            ->assertSee('href="mailto:ana@example.com"', false)
            ->assertDontSee('<table', false);

        $this->actingAs($user)->get(route('admin.mobile.leads.index', ['lead_status' => 'fria']))
            ->assertOk()->assertSee('Rui Frio')->assertDontSee('Ana Nova');
    }

    public function test_lista_tem_atalho_na_navegacao_inferior(): void
    {
        $this->actingAs($this->mobileUser())->get(route('admin.mobile.dashboard'))
            ->assertOk()->assertSee('href="' . route('admin.mobile.leads.index') . '"', false);
    }

    public function test_ficha_mostra_formularios_cotacoes_e_convertidas_da_lead(): void
    {
        $lead = Client::create(['name' => 'Ana', 'phone' => '+351 912 345 678', 'is_lead' => true, 'lead_status' => 'nova']);
        $outra = Client::create(['name' => 'Outra', 'is_lead' => true, 'lead_status' => 'nova']);

        FormProposal::create(['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678', 'client_id' => $lead->id, 'brand' => 'Tesla', 'model' => 'Model 3']);
        FormProposal::create(['name' => 'Outra', 'email' => 'outra@example.com', 'phone' => '913000000', 'client_id' => $outra->id, 'brand' => 'Fiat', 'model' => 'Panda']);
        $aceite = Proposal::create(['client_id' => $lead->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0, 'status' => 'Aprovada']);
        Proposal::create(['client_id' => $lead->id, 'brand' => 'Audi', 'model' => 'A3', 'transport_cost' => 0, 'status' => 'Pendente']);
        ConvertedProposal::create(['client_id' => $lead->id, 'proposal_id' => $aceite->id, 'status' => 'Transporte', 'brand' => 'BMW', 'modelCar' => 'i4']);
        CostSimulator::create(['client_id' => $lead->id, 'brand' => 'Peugeot', 'model' => '208', 'token' => 'tok-1'] + array_fill_keys(['car_value', 'commission_cost', 'inspection_commission_cost', 'transport', 'ipo_cost', 'imt_cost', 'registration_cost', 'plates_cost', 'total_cost'], 1000));
        LeadActivity::log($lead->id, 'Lead criado manualmente');

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.leads.show', $lead->id))
            ->assertOk()
            ->assertSee('Tesla Model 3')->assertDontSee('Fiat Panda')
            ->assertSee('BMW i4')->assertSee('Audi A3')->assertSee('Aprovada')->assertSee('Pendente')
            // A cotação aceite liga à convertida no mobile.
            ->assertSee('href="' . route('admin.mobile.converted-proposals.show', ConvertedProposal::sole()->id) . '" class="badge bg-dark', false)->assertSee('Transporte')
            ->assertSee('Peugeot')
            ->assertSee('Lead criado manualmente')
            ->assertSee('href="tel:+351912345678"', false)
            ->assertSee('href="https://wa.me/351912345678"', false)
            ->assertSee('Marcar como Em contacto')
            ->assertDontSee('<table', false);
    }

    public function test_mudar_estado_regista_atividade_igual_ao_desktop(): void
    {
        $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova']);

        $this->actingAs($this->mobileUser())->post(route('admin.mobile.leads.status', $lead->id), ['status' => 'em_contacto'])
            ->assertRedirect()->assertSessionHas('success', 'Estado da lead atualizado.');

        $this->assertSame('em_contacto', $lead->refresh()->lead_status);
        $this->assertDatabaseHas('lead_activities', ['client_id' => $lead->id, 'title' => 'Estado alterado: Nova → Em Contacto']);

        $this->actingAs($this->mobileUser())->post(route('admin.mobile.leads.status', $lead->id), ['status' => 'xpto'])
            ->assertSessionHasErrors('status');
        $this->assertSame('em_contacto', $lead->refresh()->lead_status);
    }

    public function test_cliente_da_um_404_na_ficha_de_lead(): void
    {
        $cliente = Client::create(['name' => 'Cliente', 'is_lead' => false, 'lead_status' => 'nova']);

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.leads.show', $cliente->id))->assertNotFound();
        $this->actingAs($this->mobileUser())->post(route('admin.mobile.leads.status', $cliente->id), ['status' => 'fria'])->assertNotFound();
    }

    public function test_angariador_com_permissao_so_ve_os_seus_leads(): void
    {
        // Com as regras ligadas: as rotas de leads mobile filtram pelo dono, por isso aceitam "próprios".
        config(['permissions.enforce' => true]);

        // Só angariador fica preso à sua área (restrictAngariador): junta-se outro perfil sem leads.
        $joao = $this->backofficeUser('angariador');
        $joao->assignRole('cms');
        $joao->forceFill(['access_mobile' => true])->save();
        $pedro = $this->backofficeUser('angariador');

        $minha = Client::create(['name' => 'Lead do João', 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $joao->id]);
        $outra = Client::create(['name' => 'Lead do Pedro', 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $pedro->id]);

        $this->actingAs($joao)->get(route('admin.mobile.leads.index'))
            ->assertOk()->assertSee('Lead do João')->assertDontSee('Lead do Pedro');
        $this->actingAs($joao)->get(route('admin.mobile.leads.show', $minha->id))->assertOk();
        $this->actingAs($joao)->get(route('admin.mobile.leads.show', $outra->id))->assertForbidden();

        $this->actingAs($joao)->post(route('admin.mobile.leads.status', $outra->id), ['status' => 'fria'])->assertForbidden();
        $this->assertSame('nova', $outra->refresh()->lead_status);
        $this->actingAs($joao)->post(route('admin.mobile.leads.status', $minha->id), ['status' => 'em_contacto'])->assertRedirect();
        $this->assertSame('em_contacto', $minha->refresh()->lead_status);

        // "Qualificada" só a equipa atribui (como no portal do angariador).
        $this->actingAs($joao)->get(route('admin.mobile.leads.show', $minha->id))->assertDontSee('Marcar como Qualificada');
        $this->actingAs($joao)->post(route('admin.mobile.leads.status', $minha->id), ['status' => 'qualificada'])->assertSessionHasErrors('status');
        $this->assertSame('em_contacto', $minha->refresh()->lead_status);
    }

    /** Utilizador não admin, com acesso mobile, com apenas as permissões dadas. */
    private function userWith(array $permissions): User
    {
        $role = Role::findOrCreate('vendas-' . md5(implode(',', $permissions)), 'web');
        foreach ($permissions as $nome) {
            $role->givePermissionTo(Permission::findOrCreate($nome, 'web'));
        }
        $user = $this->backofficeUser($role->name);
        $user->forceFill(['access_mobile' => true])->save();

        return $user;
    }

    public function test_criar_lead_valida_e_regista_atividade(): void
    {
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.leads.create'))
            ->assertOk()->assertSee('type="tel"', false)->assertSee('type="email"', false)->assertSee('inputmode="tel"', false);

        $this->actingAs($user)->post(route('admin.mobile.leads.store'), ['name' => ''])->assertSessionHasErrors('name');

        $resposta = $this->actingAs($user)->post(route('admin.mobile.leads.store'), [
            'name' => 'Maria', 'email' => 'maria@example.com', 'phone' => '911222333', 'observation' => 'Quer um Golf',
        ]);

        $lead = Client::where('email', 'maria@example.com')->sole();
        $resposta->assertRedirect(route('admin.mobile.leads.show', $lead->id))->assertSessionHas('success', 'Lead criado com sucesso.');
        $this->assertTrue((bool) $lead->is_lead);
        $this->assertSame('manual', $lead->lead_source);
        $this->assertSame('nova', $lead->lead_status);
        $this->assertDatabaseHas('lead_activities', ['client_id' => $lead->id, 'title' => 'Lead criado manualmente']);
    }

    public function test_criar_lead_com_email_repetido_nao_se_pode_forcar(): void
    {
        Client::create(['name' => 'Existente', 'email' => 'dup@example.com', 'phone' => '911000111', 'is_lead' => true, 'lead_status' => 'nova']);
        $user = $this->mobileUser();

        // Com e sem confirm_duplicate (POST forjado), e com maiúsculas: erro, sem 500 nem nova linha.
        foreach ([[], ['confirm_duplicate' => '1']] as $extra) {
            $this->actingAs($user)->post(route('admin.mobile.leads.store'), ['name' => 'Outra', 'email' => 'DUP@example.com'] + $extra)
                ->assertRedirect()
                ->assertSessionHasErrors('duplicate')
                ->assertSessionHas('duplicate_email', true);
        }
        $this->assertSame(1, Client::count());

        // O formulário reaparece sem a opção "Criar mesmo assim".
        $this->actingAs($user)->from(route('admin.mobile.leads.create'))
            ->post(route('admin.mobile.leads.store'), ['name' => 'Outra', 'email' => 'dup@example.com']);
        $this->actingAs($user)->get(route('admin.mobile.leads.create'))
            ->assertOk()->assertSee('mesmo email')->assertDontSee('confirm_duplicate');
    }

    public function test_criar_lead_com_telefone_repetido_pede_confirmacao(): void
    {
        Client::create(['name' => 'Existente', 'email' => 'dup@example.com', 'phone' => '911000111', 'is_lead' => true, 'lead_status' => 'nova']);
        $user = $this->mobileUser();

        $this->actingAs($user)->from(route('admin.mobile.leads.create'))
            ->post(route('admin.mobile.leads.store'), ['name' => 'Outra', 'phone' => '911000111'])
            ->assertSessionHasErrors('duplicate')
            ->assertSessionMissing('duplicate_email');
        $this->assertSame(1, Client::count());

        $this->actingAs($user)->get(route('admin.mobile.leads.create'))
            ->assertOk()->assertSee('name="confirm_duplicate"', false);

        $this->actingAs($user)->post(route('admin.mobile.leads.store'), ['name' => 'Outra', 'phone' => '911000111', 'confirm_duplicate' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertSame(2, Client::count());
    }

    public function test_converter_lead_em_cliente(): void
    {
        $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova']);

        $this->actingAs($this->mobileUser())->post(route('admin.mobile.leads.convert', $lead->id))
            ->assertRedirect(route('admin.mobile.clients.show', $lead->id));

        $this->assertFalse((bool) $lead->refresh()->is_lead);
        $this->assertDatabaseHas('lead_activities', ['client_id' => $lead->id, 'title' => 'Lead convertido em cliente']);
    }

    public function test_followup_exige_data_futura(): void
    {
        $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova']);
        $user = $this->mobileUser();

        $this->actingAs($user)->post(route('admin.mobile.leads.followup', $lead->id), ['next_followup_at' => now()->subDay()->format('Y-m-d H:i')])
            ->assertSessionHasErrors('next_followup_at');
        $this->assertNull($lead->refresh()->next_followup_at);

        $this->actingAs($user)->post(route('admin.mobile.leads.followup', $lead->id), ['next_followup_at' => now()->addDay()->format('Y-m-d H:i'), 'followup_note' => 'Ligar'])
            ->assertSessionHas('success', 'Follow-up agendado.');
        $this->assertNotNull($lead->refresh()->next_followup_at);
    }

    public function test_atividade_rejeita_tipo_invalido(): void
    {
        $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova']);
        $user = $this->mobileUser();

        $this->actingAs($user)->post(route('admin.mobile.leads.activity', $lead->id), ['type' => 'system', 'title' => 'x'])
            ->assertSessionHasErrors('type');

        $this->actingAs($user)->post(route('admin.mobile.leads.activity', $lead->id), ['type' => 'call', 'title' => 'Liguei'])
            ->assertSessionHas('success', 'Actividade registada.');
        $this->assertDatabaseHas('lead_activities', ['client_id' => $lead->id, 'title' => 'Liguei']);
    }

    public function test_dono_so_aceita_utilizador_existente(): void
    {
        $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova']);
        $user = $this->mobileUser();

        $this->actingAs($user)->post(route('admin.mobile.leads.owner', $lead->id), ['owner_id' => 99999])->assertSessionHasErrors('owner_id');
        $this->assertNull($lead->refresh()->owner_id);

        $this->actingAs($user)->post(route('admin.mobile.leads.owner', $lead->id), ['owner_id' => $user->id])
            ->assertSessionHas('success', 'Proprietário da lead actualizado.');
        $this->assertSame($user->id, $lead->refresh()->owner_id);
    }

    public function test_eliminar_lead(): void
    {
        $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova']);

        $this->actingAs($this->mobileUser())->delete(route('admin.mobile.leads.destroy', $lead->id))
            ->assertRedirect(route('admin.mobile.leads.index'));
        $this->assertNull(Client::find($lead->id));
    }

    public function test_cliente_da_404_em_converter_e_eliminar_mas_aceita_followup_e_atividade(): void
    {
        $cliente = Client::create(['name' => 'Cliente', 'is_lead' => false, 'lead_status' => 'nova']);
        $user = $this->mobileUser();

        $this->actingAs($user)->post(route('admin.mobile.leads.convert', $cliente->id))->assertNotFound();
        $this->actingAs($user)->delete(route('admin.mobile.leads.destroy', $cliente->id))->assertNotFound();
        $this->assertNotNull(Client::find($cliente->id));

        $this->actingAs($user)->post(route('admin.mobile.leads.activity', $cliente->id), ['type' => 'note', 'title' => 'Nota'])->assertSessionHas('success');
    }

    public function test_so_ver_proprias_nao_cria_converte_elimina_nem_atribui_dono(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['leads.view.own']);
        $lead = Client::create(['name' => 'Minha', 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $user->id]);

        $this->actingAs($user)->get(route('admin.mobile.leads.show', $lead->id))->assertOk()
            ->assertDontSee('Converter em cliente')->assertDontSee('Marcar como')->assertDontSee('Eliminar lead')
            ->assertDontSee(route('admin.mobile.leads.followup', $lead->id), false)
            ->assertDontSee(route('admin.mobile.leads.activity', $lead->id), false)
            ->assertDontSee(route('admin.mobile.leads.owner', $lead->id), false);
        $this->actingAs($user)->get(route('admin.mobile.leads.index'))->assertOk()->assertDontSee('Nova lead');

        $this->actingAs($user)->get(route('admin.mobile.leads.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.leads.store'), ['name' => 'X'])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.leads.status', $lead->id), ['status' => 'fria'])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.leads.convert', $lead->id))->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.leads.followup', $lead->id), ['next_followup_at' => now()->addDay()->format('Y-m-d H:i')])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.leads.activity', $lead->id), ['type' => 'note', 'title' => 'x'])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.leads.owner', $lead->id), ['owner_id' => $user->id])->assertForbidden();
        $this->actingAs($user)->delete(route('admin.mobile.leads.destroy', $lead->id))->assertForbidden();

        $this->assertTrue((bool) $lead->refresh()->is_lead);
        $this->assertSame('nova', $lead->lead_status);
        $this->assertSame(1, Client::count());
    }

    public function test_editar_proprias_so_atua_nas_suas_leads_e_nao_atribui_dono(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['leads.view.own', 'leads.update.own']);
        $minha = Client::create(['name' => 'Minha', 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $user->id]);
        $alheia = Client::create(['name' => 'Alheia', 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $this->backofficeUser('cms')->id]);
        $futuro = now()->addDay()->format('Y-m-d H:i');

        $this->actingAs($user)->get(route('admin.mobile.leads.show', $minha->id))->assertOk()
            ->assertSee(route('admin.mobile.leads.followup', $minha->id), false)
            ->assertSee('Converter em cliente')
            ->assertDontSee(route('admin.mobile.leads.owner', $minha->id), false);

        $this->actingAs($user)->post(route('admin.mobile.leads.followup', $minha->id), ['next_followup_at' => $futuro])->assertSessionHas('success');
        $this->actingAs($user)->post(route('admin.mobile.leads.activity', $minha->id), ['type' => 'note', 'title' => 'x'])->assertSessionHas('success');

        $this->actingAs($user)->post(route('admin.mobile.leads.followup', $alheia->id), ['next_followup_at' => $futuro])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.leads.activity', $alheia->id), ['type' => 'note', 'title' => 'x'])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.leads.convert', $alheia->id))->assertForbidden();
        $this->assertTrue((bool) $alheia->refresh()->is_lead);

        // Dono só com update.all; eliminar e criar continuam fechados.
        $this->actingAs($user)->post(route('admin.mobile.leads.owner', $minha->id), ['owner_id' => $user->id])->assertForbidden();
        $this->actingAs($user)->delete(route('admin.mobile.leads.destroy', $minha->id))->assertForbidden();

        $this->actingAs($user)->post(route('admin.mobile.leads.convert', $minha->id))->assertRedirect();
        $this->assertFalse((bool) $minha->refresh()->is_lead);
    }

    public function test_pesquisa_nao_alarga_o_ambito_das_leads_proprias(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['leads.view.own']);
        Client::create(['name' => 'Ana minha', 'email' => 'a@example.com', 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $user->id]);
        Client::create(['name' => 'Ana alheia', 'email' => 'ana@alheia.com', 'is_lead' => true, 'lead_status' => 'nova']);

        $this->actingAs($user)->get(route('admin.mobile.leads.index', ['search' => 'ana', 'lead_status' => 'nova']))
            ->assertOk()->assertSee('Ana minha')->assertDontSee('Ana alheia');
    }

    public function test_dono_com_update_all_ve_e_usa_o_seletor(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['leads.view.all', 'leads.update.all', 'leads.create', 'leads.delete']);
        $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova']);

        $this->actingAs($user)->get(route('admin.mobile.leads.show', $lead->id))->assertOk()
            ->assertSee(route('admin.mobile.leads.owner', $lead->id), false)->assertSee('Eliminar lead');
        $this->actingAs($user)->post(route('admin.mobile.leads.owner', $lead->id), ['owner_id' => $user->id])->assertSessionHas('success');
        $this->actingAs($user)->post(route('admin.mobile.leads.store'), ['name' => 'Nova'])->assertSessionHasNoErrors();
        $this->actingAs($user)->delete(route('admin.mobile.leads.destroy', $lead->id))->assertRedirect();
    }
}
