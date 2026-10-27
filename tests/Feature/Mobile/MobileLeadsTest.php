<?php

namespace Tests\Feature\Mobile;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\CostSimulator;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\Proposal;
use Illuminate\Support\Facades\Cache;
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
            ->assertSee('<span class="badge bg-dark">Convertida</span>', false)->assertSee('Transporte')
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
}
