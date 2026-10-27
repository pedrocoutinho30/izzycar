<?php

namespace Tests\Feature\Mobile;

use App\Models\Client;
use App\Models\CostSimulator;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MobileCostSimulatorsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function client(array $attributes = []): Client
    {
        return Client::create($attributes + ['name' => 'Ana Silva', 'email' => 'ana@example.com', 'phone' => '912345678', 'is_lead' => false, 'lead_status' => 'nova']);
    }

    private function simulation(?Client $client, array $attributes = []): CostSimulator
    {
        return CostSimulator::create($attributes + [
            'client_id' => $client?->id ?? 999999, 'brand' => 'Tesla', 'model' => 'Model 3',
            'car_value' => 20000, 'isv_cost' => 1500.5, 'commission_cost' => 1000, 'inspection_commission_cost' => 150,
            'transport' => 600, 'ipo_cost' => 40, 'imt_cost' => 65, 'registration_cost' => 55, 'plates_cost' => 30,
            'total_cost' => 23440.5, 'read' => false,
        ]);
    }

    /** Utilizador não admin, com acesso mobile, com apenas as permissões dadas. */
    private function userWith(array $permissions): User
    {
        $role = Role::findOrCreate('mobile-' . md5(implode(',', $permissions)), 'web');
        foreach ($permissions as $nome) {
            $role->givePermissionTo(Permission::findOrCreate($nome, 'web'));
        }
        $user = $this->backofficeUser($role->name);
        $user->forceFill(['access_mobile' => true])->save();

        return $user;
    }

    public function test_lista_mostra_cartoes_com_cliente_viatura_e_resultado(): void
    {
        $ana = $this->client();
        $this->simulation($ana);

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.cost-simulators.index'))
            ->assertOk()
            ->assertSee('Ana Silva')->assertSee('Tesla Model 3')
            ->assertSee('20.000,00 €')->assertSee('1.500,50 €')->assertSee('23.440,50 €')
            ->assertSee('ana@example.com')
            ->assertSee('href="tel:', false)
            ->assertSee('Nova')
            ->assertSee('href="' . route('admin.mobile.cost-simulators.show', 1) . '"', false)
            ->assertDontSee('<table', false);
    }

    public function test_lista_nao_marca_como_lida(): void
    {
        $sim = $this->simulation($this->client());

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.cost-simulators.index'))->assertOk();

        $this->assertFalse($sim->fresh()->read);
    }

    public function test_pesquisa_por_cliente_viatura_e_trata_curingas_como_texto(): void
    {
        $ana = $this->client();
        $rui = $this->client(['name' => 'Rui Costa', 'email' => 'rui@example.com', 'phone' => '913000111', 'vat_number' => '123456789']);
        $this->simulation($ana, ['brand' => 'Tesla', 'model' => 'Model 3']);
        $this->simulation($rui, ['brand' => 'Audi', 'model' => 'A4']);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.cost-simulators.index', ['search' => 'Rui']))
            ->assertOk()->assertSee('Rui Costa')->assertDontSee('Ana Silva');
        $this->actingAs($user)->get(route('admin.mobile.cost-simulators.index', ['search' => 'Model 3']))
            ->assertOk()->assertSee('Ana Silva')->assertDontSee('Rui Costa');
        $this->actingAs($user)->get(route('admin.mobile.cost-simulators.index', ['search' => '123456789']))
            ->assertOk()->assertSee('Rui Costa')->assertDontSee('Ana Silva');
        $this->actingAs($user)->get(route('admin.mobile.cost-simulators.index', ['search' => 'rui@example']))
            ->assertOk()->assertSee('Rui Costa')->assertDontSee('Ana Silva');
        $this->actingAs($user)->get(route('admin.mobile.cost-simulators.index', ['search' => '%']))
            ->assertOk()->assertDontSee('Rui Costa')->assertDontSee('Ana Silva')->assertSee('Nenhuma simulação encontrada');
    }

    public function test_filtro_por_cliente_e_chip_para_remover(): void
    {
        $ana = $this->client();
        $rui = $this->client(['name' => 'Rui Costa', 'email' => 'rui@example.com']);
        $this->simulation($ana, ['model' => 'ModeloAna']);
        $this->simulation($rui, ['model' => 'ModeloRui']);

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.cost-simulators.index', ['client_id' => $rui->id]))
            ->assertOk()->assertSee('ModeloRui')->assertDontSee('ModeloAna')
            ->assertSee('name="client_id" value="' . $rui->id . '"', false)
            ->assertSee('Remover filtro');
    }

    public function test_lista_pagina_com_carregar_mais(): void
    {
        $ana = $this->client();
        foreach (range(1, 16) as $i) {
            $this->simulation($ana);
        }
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.cost-simulators.index'))
            ->assertOk()->assertSee('id="m-load-more"', false)->assertSee('page=2', false);
        $this->actingAs($user)->get(route('admin.mobile.cost-simulators.index', ['page' => 2]))
            ->assertOk()->assertDontSee('id="m-load-more"', false);
    }

    public function test_ficha_mostra_dados_submetidos_e_custos_como_no_desktop(): void
    {
        $sim = $this->simulation($this->client(), [
            'year' => 2021, 'fuel' => 'eletrico', 'cc' => 1998, 'co2' => 120, 'tipo_veiculo' => 'ligeiro_passageiros',
            'pais_matricula' => 'uniao-europeia', 'autonomia' => 'igual_superior', 'emissao_particulas' => '+0.0001',
        ]);

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.cost-simulators.show', $sim->id))
            ->assertOk()
            ->assertSee('Simulação #' . $sim->id)
            ->assertSee('Tesla')->assertSee('Model 3')->assertSee('2021')->assertSee('Eletrico')
            ->assertSee('1998 cc')->assertSee('120 g/km')->assertSee('Ligeiro passageiros')
            ->assertSee('União Europeia')->assertSee('Igual ou superior a 50 km')->assertSee('&gt; 0.0001 g/km', false)
            ->assertSee('Valor do carro')->assertSee('20.000,00 €')
            ->assertSee('ISV (Imposto sobre Veículos)')->assertSee('1.500,50 €')
            ->assertSee('Comissão')->assertSee('1.000,00 €')
            ->assertSee('Inspeção na origem')->assertSee('150,00 €')
            ->assertSee('Transporte')->assertSee('600,00 €')
            ->assertSee('IPO')->assertSee('IMT')->assertSee('Registo automóvel')->assertSee('Matrículas')
            ->assertSee('CUSTO TOTAL')->assertSee('23.440,50 €')
            ->assertSee('Abrir no desktop')
            ->assertSee('href="' . route('admin.v2.cost-simulators.show', $sim->id) . '"', false)
            ->assertDontSee('<table', false)
            ->assertDontSee('Eliminar');
    }

    public function test_ficha_esconde_o_que_o_desktop_esconde(): void
    {
        $sim = $this->simulation($this->client());

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.cost-simulators.show', $sim->id))
            ->assertOk()
            ->assertDontSee('IUC (Imposto Único de Circulação)')
            ->assertDontSee('Cilindrada')->assertDontSee('Autonomia da bateria')->assertDontSee('Emissão de partículas');
    }

    public function test_ficha_escapa_html_e_nao_rebenta_sem_cliente(): void
    {
        $sim = $this->simulation(null, ['brand' => '<script>alert(1)</script>']);

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.cost-simulators.show', $sim->id))
            ->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('Cliente removido');
        $this->actingAs($this->mobileUser())->get(route('admin.mobile.cost-simulators.index'))
            ->assertOk()->assertSee('Cliente removido');
    }

    public function test_ficha_inexistente_da_404(): void
    {
        $this->actingAs($this->mobileUser())->get(route('admin.mobile.cost-simulators.show', 999999))->assertNotFound();
    }

    public function test_ficha_liga_a_lead_ou_ao_cliente_se_o_utilizador_o_pode_ver(): void
    {
        $lead = $this->client(['name' => 'Lead Teste', 'email' => 'lead@example.com', 'is_lead' => true]);
        $cliente = $this->client();
        $simLead = $this->simulation($lead);
        $simCliente = $this->simulation($cliente);
        $admin = $this->mobileUser();

        $this->actingAs($admin)->get(route('admin.mobile.cost-simulators.show', $simLead->id))
            ->assertSee('href="' . route('admin.mobile.leads.show', $lead->id) . '"', false)->assertSee('Ver lead');
        $this->actingAs($admin)->get(route('admin.mobile.cost-simulators.show', $simCliente->id))
            ->assertSee('href="' . route('admin.mobile.clients.show', $cliente->id) . '"', false)->assertSee('Ver cliente');

        // Só vê simulações: sem permissão de leads/clientes, sem ligação à ficha mobile.
        $leitor = $this->userWith(['cost-simulators.view']);
        $this->actingAs($leitor)->get(route('admin.mobile.cost-simulators.show', $simLead->id))
            ->assertOk()->assertSee('Lead Teste')
            ->assertDontSee(route('admin.mobile.leads.show', $lead->id), false)->assertDontSee('Ver lead');
        $this->actingAs($leitor)->get(route('admin.mobile.cost-simulators.show', $simCliente->id))
            ->assertOk()->assertDontSee(route('admin.mobile.clients.show', $cliente->id), false);
    }

    public function test_fichas_de_lead_e_cliente_ligam_as_simulacoes(): void
    {
        $lead = $this->client(['name' => 'Lead Teste', 'email' => 'lead@example.com', 'is_lead' => true]);
        $cliente = $this->client();
        $simLead = $this->simulation($lead);
        $simCliente = $this->simulation($cliente);
        $admin = $this->mobileUser();

        $this->actingAs($admin)->get(route('admin.mobile.leads.show', $lead->id))
            ->assertOk()->assertSee('href="' . route('admin.mobile.cost-simulators.show', $simLead->id) . '"', false);
        $this->actingAs($admin)->get(route('admin.mobile.clients.show', $cliente->id))
            ->assertOk()->assertSee('Simuladores')
            ->assertSee('href="' . route('admin.mobile.cost-simulators.show', $simCliente->id) . '"', false);

        // Quem vê a lead/cliente mas não o simulador: sem ligações (e sem secção no cliente).
        $semSimulador = $this->userWith(['leads.view.all', 'clients.view.all']);
        $this->actingAs($semSimulador)->get(route('admin.mobile.leads.show', $lead->id))
            ->assertOk()->assertDontSee(route('admin.mobile.cost-simulators.show', $simLead->id), false);
        $this->actingAs($semSimulador)->get(route('admin.mobile.clients.show', $cliente->id))
            ->assertOk()->assertDontSee('Simuladores')->assertDontSee(route('admin.mobile.cost-simulators.show', $simCliente->id), false);
    }

    public function test_entrada_mais_so_para_quem_pode_ver(): void
    {
        $this->simulation($this->client());
        $link = 'href="' . route('admin.mobile.cost-simulators.index') . '"';

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.leads.index'))->assertSee($link, false)->assertSee('Simulador de custos');
        $this->actingAs($this->userWith(['cost-simulators.view', 'leads.view.all']))->get(route('admin.mobile.leads.index'))->assertSee($link, false);
        $this->actingAs($this->userWith(['leads.view.all']))->get(route('admin.mobile.leads.index'))->assertOk()->assertDontSee($link, false);
    }

    public function test_enforce_ligado_so_quem_tem_ver_entra_e_so_ve_o_simulador(): void
    {
        config(['permissions.enforce' => true]);
        $cliente = $this->client();
        $sim = $this->simulation($cliente);

        $leitor = $this->userWith(['cost-simulators.view']);
        $this->actingAs($leitor)->get(route('admin.mobile.cost-simulators.index'))->assertOk()->assertSee('Ana Silva');
        $this->actingAs($leitor)->get(route('admin.mobile.cost-simulators.show', $sim->id))->assertOk();

        // Sem o objeto (mesmo com leads/clientes "próprios"): 403, nunca 500.
        $semAcesso = $this->userWith(['leads.view.own', 'clients.view.own']);
        $this->actingAs($semAcesso)->get(route('admin.mobile.cost-simulators.index'))->assertForbidden();
        $this->actingAs($semAcesso)->get(route('admin.mobile.cost-simulators.show', $sim->id))->assertForbidden();
    }

    public function test_sem_enforce_o_controller_bloqueia_quem_nao_pode(): void
    {
        $sim = $this->simulation($this->client());
        $semAcesso = $this->userWith(['leads.view.own', 'clients.view.own']);

        $this->actingAs($semAcesso)->get(route('admin.mobile.cost-simulators.index'))->assertForbidden();
        $this->actingAs($semAcesso)->get(route('admin.mobile.cost-simulators.show', $sim->id))->assertForbidden();
    }

    public function test_rotas_mobile_estao_cobertas_e_a_eliminacao_continua_so_no_desktop(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.mobile.cost-simulators.destroy'));
        $this->assertSame('/gestao/mobile/simulador-custos', route('admin.mobile.cost-simulators.index', [], false));
    }
}
