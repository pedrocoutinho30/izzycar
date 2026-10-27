<?php

namespace Tests\Feature\Mobile;

use App\Models\CarCandidate;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MobileClientsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
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

    private function cliente(array $attributes = []): Client
    {
        return Client::create($attributes + ['name' => 'Maria', 'is_lead' => false]);
    }

    public function test_lista_so_clientes_com_pesquisa_e_contactos(): void
    {
        $this->cliente(['name' => 'Ana Cliente', 'phone' => '912345678', 'email' => 'ana@example.com']);
        $this->cliente(['name' => 'Rui Cliente', 'client_type' => 'Empresa']);
        Client::create(['name' => 'Ana Lead', 'is_lead' => true, 'lead_status' => 'nova']);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.clients.index', ['search' => 'Ana']))
            ->assertOk()->assertSee('Ana Cliente')->assertDontSee('Rui Cliente')->assertDontSee('Ana Lead')
            ->assertSee('href="tel:912345678"', false)
            ->assertSee('href="https://wa.me/351912345678"', false)
            ->assertSee('href="mailto:ana@example.com"', false)
            ->assertSee('href="' . route('admin.mobile.clients.create') . '"', false)
            ->assertDontSee('<table', false);

        $this->actingAs($user)->get(route('admin.mobile.clients.index', ['client_type' => 'empresa']))
            ->assertOk()->assertSee('Rui Cliente')->assertDontSee('Ana Cliente');
    }

    public function test_lista_pagina_com_carregar_mais(): void
    {
        foreach (range(1, 16) as $i) {
            $this->cliente(['name' => "Cliente {$i}"]);
        }

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.clients.index'))
            ->assertOk()->assertSee('id="m-load-more"', false)->assertSee('Carregar mais');
    }

    public function test_navegacao_inferior_tem_clientes(): void
    {
        $this->actingAs($this->mobileUser())->get(route('admin.mobile.dashboard'))
            ->assertOk()->assertSee('href="' . route('admin.mobile.clients.index') . '"', false);
    }

    public function test_criar_cliente_guarda_campos_e_nao_e_lead(): void
    {
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.clients.create'))
            ->assertOk()->assertSee('type="tel"', false)->assertSee('type="email"', false)->assertSee('inputmode="tel"', false)
            ->assertSee('name="validate_identification_number"', false);

        $this->actingAs($user)->post(route('admin.mobile.clients.store'), ['name' => ''])->assertSessionHasErrors('name');

        $resposta = $this->actingAs($user)->post(route('admin.mobile.clients.store'), [
            'name' => 'Maria Silva', 'email' => 'maria@example.com', 'phone' => '+351 912 345 678', 'language' => 'pt',
            'identification_number' => '12345678', 'validate_identification_number' => '2031-03-15',
            'is_lead' => '1', 'owner_id' => $user->id, 'lead_status' => 'perdida',
        ]);

        $client = Client::sole();
        $resposta->assertRedirect(route('admin.mobile.clients.show', $client->id))->assertSessionHas('success', 'Cliente criado com sucesso!');
        $this->assertFalse((bool) $client->is_lead);
        $this->assertNull($client->owner_id);
        $this->assertNotSame('perdida', $client->lead_status);
        // O telefone grava-se como escrito (como no desktop); a chave de duplicados fica com os 9 dígitos.
        $this->assertSame('+351 912 345 678', $client->phone);
        $this->assertSame('912345678', $client->phone_key);
        $this->assertSame('2031-03-15', $client->validate_identification_number->format('Y-m-d'));
        $this->assertTrue((bool) $client->data_processing_consent);
        $this->assertFalse((bool) $client->newsletter_consent);
    }

    public function test_criar_com_telefone_repetido_pede_confirmacao(): void
    {
        $this->cliente(['name' => 'Existente', 'phone' => '911000111']);
        $user = $this->mobileUser();

        $this->actingAs($user)->from(route('admin.mobile.clients.create'))
            ->post(route('admin.mobile.clients.store'), ['name' => 'Outra', 'phone' => '+351911000111'])
            ->assertSessionHasErrors(['duplicate' => 'Já existe «Existente» com o mesmo telefone.']);
        $this->assertSame(1, Client::count());

        $this->actingAs($user)->get(route('admin.mobile.clients.create'))
            ->assertOk()->assertSee('name="confirm_duplicate"', false);

        $this->actingAs($user)->post(route('admin.mobile.clients.store'), ['name' => 'Outra', 'phone' => '911000111', 'confirm_duplicate' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertSame(2, Client::count());
    }

    public function test_email_repetido_e_erro_de_validacao_ao_criar_e_editar(): void
    {
        $this->cliente(['name' => 'Existente', 'email' => 'dup@example.com']);
        $outra = $this->cliente(['name' => 'Outra', 'email' => 'outra@example.com']);
        $user = $this->mobileUser();

        // Nem com confirm_duplicate (POST forjado), e sem distinguir maiúsculas: erro, sem 500 nem nova linha.
        foreach ([[], ['confirm_duplicate' => '1']] as $extra) {
            $this->actingAs($user)->post(route('admin.mobile.clients.store'), ['name' => 'Nova', 'email' => 'DUP@example.com'] + $extra)
                ->assertRedirect()->assertSessionHasErrors(['email' => 'Já existe «Existente» com o mesmo email.']);
        }
        $this->assertSame(2, Client::count());

        $this->actingAs($user)->put(route('admin.mobile.clients.update', $outra->id), ['name' => 'Outra', 'email' => 'dup@example.com'])
            ->assertSessionHasErrors('email');
        $this->assertSame('outra@example.com', $outra->refresh()->email);

        // O próprio email não conta como repetido.
        $this->actingAs($user)->put(route('admin.mobile.clients.update', $outra->id), ['name' => 'Outra Editada', 'email' => 'OUTRA@example.com'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Outra Editada', $outra->refresh()->name);
    }

    public function test_editar_guarda_campos_sem_mudar_lead_nem_dono(): void
    {
        $dono = $this->backofficeUser('cms');
        $client = $this->cliente(['name' => 'Maria', 'owner_id' => $dono->id, 'client_type' => 'Particular', 'origin' => 'Manual BO']);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.clients.edit', $client->id))
            ->assertOk()->assertSee('value="Maria"', false)
            ->assertSee('action="' . route('admin.mobile.clients.update', $client->id) . '"', false)
            ->assertSee('name="_method" value="PUT"', false)
            // Valores antigos fora das opções não se perdem.
            ->assertSee('<option value="particular" selected', false)
            ->assertSee('<option value="Manual BO" selected>', false);

        $this->actingAs($user)->put(route('admin.mobile.clients.update', $client->id), [
            'name' => 'Maria Silva', 'phone' => '913000000', 'vat_number' => '123456789',
            'identification_number' => 'AB123', 'validate_identification_number' => '2030-01-01',
            'newsletter_consent' => '1', 'is_lead' => '1', 'owner_id' => $user->id,
        ])->assertRedirect(route('admin.mobile.clients.show', $client->id))->assertSessionHas('success', 'Cliente atualizado com sucesso!');

        $client->refresh();
        $this->assertSame('Maria Silva', $client->name);
        $this->assertSame('AB123', $client->identification_number);
        $this->assertTrue((bool) $client->newsletter_consent);
        $this->assertFalse((bool) $client->is_lead);
        $this->assertSame($dono->id, $client->owner_id);
    }

    public function test_ficha_mostra_dados_pedidos_cotacoes_convertidas_candidatos_e_timeline(): void
    {
        $client = $this->cliente(['name' => 'Ana', 'phone' => '+351 912 345 678', 'vat_number' => '987654321', 'identification_number' => 'CC-1', 'validate_identification_number' => '2020-01-01']);
        $outro = $this->cliente(['name' => 'Outro']);

        FormProposal::create(['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678', 'client_id' => $client->id, 'brand' => 'Tesla', 'model' => 'Model 3']);
        FormProposal::create(['name' => 'Outro', 'email' => 'outro@example.com', 'phone' => '913000000', 'client_id' => $outro->id, 'brand' => 'Fiat', 'model' => 'Panda']);
        $aceite = Proposal::create(['client_id' => $client->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0, 'status' => 'Aprovada']);
        Proposal::create(['client_id' => $outro->id, 'brand' => 'Audi', 'model' => 'A3', 'transport_cost' => 0, 'status' => 'Pendente']);
        $convertida = ConvertedProposal::create(['client_id' => $client->id, 'proposal_id' => $aceite->id, 'status' => 'Transporte', 'brand' => 'BMW', 'modelCar' => 'i4']);
        CarCandidate::create(['client_id' => $client->id, 'title' => 'Golf GTI 2021', 'link' => 'https://example.com/anuncio', 'price' => 25000, 'status' => 'em_conversacao', 'order_position' => 0]);
        CarCandidate::create(['client_id' => $client->id, 'title' => 'Polo perigoso', 'link' => 'javascript:alert(1)', 'status' => 'descartado', 'order_position' => 1]);
        CarCandidate::create(['client_id' => $outro->id, 'title' => 'Clio do outro', 'status' => 'descartado', 'order_position' => 0]);
        LeadActivity::log($client->id, 'Lead convertido em cliente');
        LeadActivity::log($outro->id, 'Atividade do outro');

        $formProposal = FormProposal::where('client_id', $client->id)->sole();

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.clients.show', $client->id))
            ->assertOk()
            ->assertSee('987654321')->assertSee('CC-1')->assertSee('Caducado')
            ->assertSee('Tesla Model 3')->assertDontSee('Fiat Panda')
            ->assertSee('href="' . route('admin.mobile.form-proposals.show', $formProposal->id) . '"', false)
            ->assertSee('BMW i4')->assertDontSee('Audi A3')
            ->assertSee('href="' . route('admin.mobile.proposals.show', $aceite->id) . '"', false)
            ->assertSee('href="' . route('admin.mobile.converted-proposals.show', $convertida->id) . '"', false)->assertSee('Transporte')
            ->assertSee('Golf GTI 2021')->assertSee('href="https://example.com/anuncio"', false)
            ->assertSee('Polo perigoso')->assertDontSee('javascript:alert', false)->assertDontSee('Clio do outro')
            ->assertSee('Lead convertido em cliente')->assertDontSee('Atividade do outro')
            ->assertSee('href="tel:+351912345678"', false)
            ->assertSee('href="https://wa.me/351912345678"', false)
            ->assertSee(route('admin.mobile.clients.followup', $client->id), false)
            ->assertSee(route('admin.mobile.clients.activity', $client->id), false)
            ->assertSee('href="' . route('admin.mobile.clients.edit', $client->id) . '"', false)
            ->assertDontSee('<table', false);
    }

    public function test_lead_aberta_pelas_rotas_de_cliente_vai_para_a_ficha_de_lead(): void
    {
        $lead = Client::create(['name' => 'Lead', 'is_lead' => true, 'lead_status' => 'nova']);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.clients.show', $lead->id))->assertRedirect(route('admin.mobile.leads.show', $lead->id));
        $this->actingAs($user)->get(route('admin.mobile.clients.edit', $lead->id))->assertRedirect(route('admin.mobile.leads.show', $lead->id));

        // Editar, follow-up e atividade funcionam como no desktop, sem converter a lead.
        $this->actingAs($user)->put(route('admin.mobile.clients.update', $lead->id), ['name' => 'Lead Editada'])->assertSessionHasNoErrors();
        $this->actingAs($user)->post(route('admin.mobile.clients.activity', $lead->id), ['type' => 'note', 'title' => 'Nota'])->assertSessionHas('success');
        $this->assertSame('Lead Editada', $lead->refresh()->name);
        $this->assertTrue((bool) $lead->is_lead);
    }

    public function test_followup_e_atividade(): void
    {
        $client = $this->cliente();
        $user = $this->mobileUser();

        $this->actingAs($user)->post(route('admin.mobile.clients.followup', $client->id), ['next_followup_at' => now()->subDay()->format('Y-m-d H:i')])
            ->assertSessionHasErrors('next_followup_at');
        $this->assertNull($client->refresh()->next_followup_at);

        $data = now()->addDay()->setTime(9, 15);
        $this->actingAs($user)->from(route('admin.mobile.clients.show', $client->id))
            ->post(route('admin.mobile.clients.followup', $client->id), ['next_followup_at' => $data->format('Y-m-d H:i'), 'followup_note' => 'Ligar'])
            ->assertRedirect(route('admin.mobile.clients.show', $client->id))
            ->assertSessionHas('success', 'Follow-up agendado.');
        $this->assertDatabaseHas('lead_activities', ['client_id' => $client->id, 'title' => 'Follow-up agendado para ' . $data->format('d/m/Y H:i')]);

        $this->actingAs($user)->post(route('admin.mobile.clients.activity', $client->id), ['type' => 'system', 'title' => 'x'])
            ->assertSessionHasErrors('type');
        $this->actingAs($user)->post(route('admin.mobile.clients.activity', $client->id), ['type' => 'call', 'title' => 'Liguei'])
            ->assertSessionHas('success', 'Atividade registada.');
        $this->assertDatabaseHas('lead_activities', ['client_id' => $client->id, 'type' => 'call', 'title' => 'Liguei', 'user_id' => $user->id]);
    }

    public function test_converter_lead_segue_para_a_ficha_de_cliente(): void
    {
        $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova']);
        $user = $this->mobileUser();

        $this->actingAs($user)->post(route('admin.mobile.leads.convert', $lead->id))
            ->assertRedirect(route('admin.mobile.clients.show', $lead->id));
        $this->actingAs($user)->get(route('admin.mobile.clients.show', $lead->id))->assertOk()->assertSee('Ana');
    }

    public function test_converter_sem_acesso_a_clientes_volta_as_leads(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['leads.view.own', 'leads.update.own']);
        $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $user->id]);

        $this->actingAs($user)->post(route('admin.mobile.leads.convert', $lead->id))
            ->assertRedirect(route('admin.mobile.leads.index'))->assertSessionHas('success');
        $this->assertFalse((bool) $lead->refresh()->is_lead);
    }

    public function test_ver_proprios_so_ve_os_seus_e_nao_cria_nem_edita(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['clients.view.own']);
        $meu = $this->cliente(['name' => 'Cliente meu', 'owner_id' => $user->id, 'identification_number' => 'CC-SEGREDO']);
        $alheio = $this->cliente(['name' => 'Cliente alheio', 'owner_id' => $this->backofficeUser('cms')->id]);
        $semDono = $this->cliente(['name' => 'Cliente sem dono']);

        $this->actingAs($user)->get(route('admin.mobile.clients.index', ['search' => 'Cliente']))
            ->assertOk()->assertSee('Cliente meu')->assertDontSee('Cliente alheio')->assertDontSee('Cliente sem dono')
            ->assertDontSee('href="' . route('admin.mobile.clients.create') . '"', false);

        $this->actingAs($user)->get(route('admin.mobile.clients.show', $meu->id))->assertOk()
            ->assertDontSee(route('admin.mobile.clients.edit', $meu->id), false)
            ->assertDontSee(route('admin.mobile.clients.followup', $meu->id), false)
            ->assertDontSee(route('admin.mobile.clients.activity', $meu->id), false)
            ->assertDontSee('CC-SEGREDO');
        $this->actingAs($user)->get(route('admin.mobile.clients.show', $alheio->id))->assertForbidden();
        $this->actingAs($user)->get(route('admin.mobile.clients.show', $semDono->id))->assertForbidden();

        $this->actingAs($user)->get(route('admin.mobile.clients.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.clients.store'), ['name' => 'X'])->assertForbidden();
        $this->actingAs($user)->get(route('admin.mobile.clients.edit', $meu->id))->assertForbidden();
        $this->actingAs($user)->put(route('admin.mobile.clients.update', $meu->id), ['name' => 'X'])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.clients.followup', $meu->id), ['next_followup_at' => now()->addDay()->format('Y-m-d H:i')])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.clients.activity', $meu->id), ['type' => 'note', 'title' => 'x'])->assertForbidden();

        $this->assertSame('Cliente meu', $meu->refresh()->name);
        $this->assertSame(3, Client::count());
    }

    public function test_editar_proprios_so_atua_nos_seus(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['clients.view.own', 'clients.update.own']);
        $meu = $this->cliente(['name' => 'Meu', 'owner_id' => $user->id]);
        $alheio = $this->cliente(['name' => 'Alheio', 'owner_id' => $this->backofficeUser('cms')->id]);
        $futuro = now()->addDay()->format('Y-m-d H:i');

        $this->actingAs($user)->get(route('admin.mobile.clients.edit', $meu->id))->assertOk();
        $this->actingAs($user)->put(route('admin.mobile.clients.update', $meu->id), ['name' => 'Meu Editado'])->assertSessionHasNoErrors();
        $this->actingAs($user)->post(route('admin.mobile.clients.followup', $meu->id), ['next_followup_at' => $futuro])->assertSessionHas('success');
        $this->actingAs($user)->post(route('admin.mobile.clients.activity', $meu->id), ['type' => 'note', 'title' => 'x'])->assertSessionHas('success');
        $this->assertSame('Meu Editado', $meu->refresh()->name);

        $this->actingAs($user)->get(route('admin.mobile.clients.edit', $alheio->id))->assertForbidden();
        $this->actingAs($user)->put(route('admin.mobile.clients.update', $alheio->id), ['name' => 'Roubado'])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.clients.followup', $alheio->id), ['next_followup_at' => $futuro])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.clients.activity', $alheio->id), ['type' => 'note', 'title' => 'x'])->assertForbidden();
        $this->assertSame('Alheio', $alheio->refresh()->name);
        $this->assertSame(0, LeadActivity::where('client_id', $alheio->id)->count());
    }

    public function test_proprios_tambem_sem_regras_ligadas(): void
    {
        // Com PERMISSIONS_ENFORCE desligado, o controller continua a filtrar registo a registo.
        config(['permissions.enforce' => false]);
        $user = $this->userWith(['clients.view.own', 'clients.update.own']);
        $alheio = $this->cliente(['name' => 'Alheio']);

        $this->actingAs($user)->get(route('admin.mobile.clients.show', $alheio->id))->assertForbidden();
        $this->actingAs($user)->put(route('admin.mobile.clients.update', $alheio->id), ['name' => 'Roubado'])->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.clients.store'), ['name' => 'X'])->assertForbidden();
        $this->actingAs($user)->get(route('admin.mobile.clients.index'))->assertOk()->assertDontSee('Alheio');
    }

    public function test_criar_com_ver_proprios_fica_dono_do_cliente(): void
    {
        config(['permissions.enforce' => true]);
        $user = $this->userWith(['clients.view.own', 'clients.create']);

        $this->actingAs($user)->get(route('admin.mobile.clients.index'))->assertOk()
            ->assertSee('href="' . route('admin.mobile.clients.create') . '"', false);

        $this->actingAs($user)->post(route('admin.mobile.clients.store'), ['name' => 'Novo', 'owner_id' => $this->backofficeUser('cms')->id])
            ->assertSessionHasNoErrors();

        $client = Client::sole();
        $this->assertSame($user->id, $client->owner_id);
        $this->actingAs($user)->get(route('admin.mobile.clients.show', $client->id))->assertOk();
    }

    public function test_nomes_de_cliente_nas_cotacoes_ligam_a_ficha_mobile(): void
    {
        $client = $this->cliente(['name' => 'Ana Ligada']);
        $proposal = Proposal::create(['client_id' => $client->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0, 'status' => 'Pendente', 'proposal_code' => 'VX00000001']);
        $converted = ConvertedProposal::create(['client_id' => $client->id, 'status' => 'Transporte', 'brand' => 'BMW', 'modelCar' => 'i4']);
        $formProposal = FormProposal::create(['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678', 'client_id' => $client->id, 'brand' => 'Tesla', 'model' => 'Model 3']);
        $user = $this->mobileUser();
        $link = 'href="' . route('admin.mobile.clients.show', $client->id) . '"';

        $this->actingAs($user)->get(route('admin.mobile.proposals.show', $proposal->id))->assertOk()->assertSee($link, false);
        $this->actingAs($user)->get(route('admin.mobile.converted-proposals.show', $converted->id))->assertOk()->assertSee($link, false);
        $this->actingAs($user)->get(route('admin.mobile.form-proposals.show', $formProposal->id))->assertOk()->assertSee($link, false);
    }
}
