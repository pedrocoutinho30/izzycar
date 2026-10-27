<?php

namespace Tests\Feature\Mobile;

use App\Models\Client;
use App\Models\FormProposal;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MobileSearchTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function userWith(array $permissions): User
    {
        $role = Role::findOrCreate('pesquisa-' . md5(implode(',', $permissions)), 'web');
        foreach ($permissions as $nome) {
            $role->givePermissionTo(Permission::findOrCreate($nome, 'web'));
        }
        $user = $this->backofficeUser($role->name);
        $user->forceFill(['access_mobile' => true])->save();

        return $user;
    }

    private function pesquisar(User $user, string $termo)
    {
        return $this->actingAs($user)->get(route('admin.mobile.search', ['q' => $termo]))->assertOk();
    }

    public function test_navegacao_inferior_liga_a_pesquisa(): void
    {
        $this->actingAs($this->mobileUser())->get(route('admin.mobile.dashboard'))
            ->assertOk()->assertSee('href="' . route('admin.mobile.search') . '"', false)->assertDontSee('href="#"', false);
    }

    public function test_formulario_com_campo_unico_de_pesquisa(): void
    {
        $this->actingAs($this->mobileUser())->get(route('admin.mobile.search'))
            ->assertOk()->assertSee('type="search" name="q"', false)->assertDontSee('<table', false);
    }

    public function test_resultados_agrupados_ligam_as_paginas_mobile(): void
    {
        $lead = Client::create(['name' => 'Zeferino Lead', 'email' => 'zl@example.com', 'is_lead' => true]);
        $cliente = Client::create(['name' => 'Zeferino Cliente', 'phone' => '912000000', 'is_lead' => false]);
        $cotacao = Proposal::create(['client_id' => $cliente->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0, 'status' => 'Pendente']);
        $pedido = FormProposal::create(['name' => 'Zeferino Pedido', 'email' => 'zp@example.com', 'phone' => '913000000', 'brand' => 'Fiat', 'model' => 'Panda']);

        $this->pesquisar($this->mobileUser(), 'Zeferino')
            ->assertSeeInOrder(['Leads', 'Zeferino Lead', 'Clientes', 'Zeferino Cliente', 'Cotações', 'BMW i4', 'Pedidos de formulário', 'Zeferino Pedido'])
            ->assertSee('href="' . route('admin.mobile.leads.show', $lead->id) . '"', false)
            ->assertSee('href="' . route('admin.mobile.clients.show', $cliente->id) . '"', false)
            ->assertSee('href="' . route('admin.mobile.proposals.show', $cotacao->id) . '"', false)
            ->assertSee('href="' . route('admin.mobile.form-proposals.show', $pedido->id) . '"', false)
            ->assertDontSee(route('admin.v2.leads.show', $lead->id), false)
            ->assertDontSee(route('admin.v2.proposals.edit', $cotacao->id), false)
            ->assertDontSee('<table', false);
    }

    public function test_cotacoes_por_codigo_e_por_marca_e_modelo_em_campos_separados(): void
    {
        $cliente = Client::create(['name' => 'Ana', 'is_lead' => false]);
        $cotacao = Proposal::create(['client_id' => $cliente->id, 'brand' => 'Mercedes', 'model' => 'CLA 200', 'transport_cost' => 0, 'status' => 'Pendente']);
        $cotacao->forceFill(['proposal_code' => 'COT-777'])->save();
        Proposal::create(['client_id' => $cliente->id, 'brand' => 'Fiat', 'model' => 'Panda', 'transport_cost' => 0, 'status' => 'Pendente']);
        $admin = $this->mobileUser();

        $this->pesquisar($admin, 'COT-777')->assertSee('Mercedes CLA 200')->assertDontSee('Fiat Panda');
        $this->pesquisar($admin, 'Mercedes CLA')->assertSee('Mercedes CLA 200')->assertDontSee('Fiat Panda');
    }

    public function test_curingas_do_like_sao_texto(): void
    {
        Client::create(['name' => 'Ana Lead', 'is_lead' => true]);
        Client::create(['name' => '100% Lead', 'is_lead' => true]);
        Client::create(['name' => 'Rui_Lead', 'is_lead' => true]);
        $admin = $this->mobileUser();

        $this->pesquisar($admin, 'A_a')->assertDontSee('Ana Lead');
        $this->pesquisar($admin, '%%')->assertDontSee('Ana Lead')->assertDontSee('100% Lead');
        $this->pesquisar($admin, '0%')->assertSee('100% Lead')->assertDontSee('Ana Lead');
        $this->pesquisar($admin, 'i_L')->assertSee('Rui_Lead')->assertDontSee('Ana Lead');
        $this->pesquisar($admin, '!%')->assertSee('Nenhum resultado');
    }

    public function test_termo_curto_nao_pesquisa(): void
    {
        Client::create(['name' => 'Z', 'is_lead' => true]);

        $this->pesquisar($this->mobileUser(), 'Z')
            ->assertSee('Escreva pelo menos 2 caracteres.')
            ->assertDontSee('href="' . route('admin.mobile.leads.show', Client::sole()->id) . '"', false);
    }

    /** Com as regras ligadas e desligadas: o filtro é do controller, não do middleware. */
    public static function enforce(): array
    {
        return ['regras ligadas' => [true], 'regras desligadas' => [false]];
    }

    #[DataProvider('enforce')]
    public function test_ver_proprios_so_encontra_os_seus_registos(bool $enforce): void
    {
        config(['permissions.enforce' => $enforce]);
        $user = $this->userWith(['leads.view.own', 'clients.view.own', 'proposals.view.own', 'form-proposals.view.own']);
        $outro = $this->backofficeUser('cms');

        $minhaLead = Client::create(['name' => 'Zeferino Minha Lead', 'is_lead' => true, 'owner_id' => $user->id]);
        Client::create(['name' => 'Zeferino Lead Alheia', 'is_lead' => true, 'owner_id' => $outro->id]);
        Client::create(['name' => 'Zeferino Lead Sem Dono', 'is_lead' => true]);
        $meuCliente = Client::create(['name' => 'Zeferino Meu Cliente', 'is_lead' => false, 'owner_id' => $user->id]);
        $clienteAlheio = Client::create(['name' => 'Zeferino Cliente Alheio', 'is_lead' => false, 'owner_id' => $outro->id]);
        $minhaCotacao = Proposal::create(['client_id' => $meuCliente->id, 'brand' => 'Zeferino', 'model' => 'Minha', 'transport_cost' => 0, 'status' => 'Pendente']);
        Proposal::create(['client_id' => $clienteAlheio->id, 'brand' => 'Zeferino', 'model' => 'Alheia', 'transport_cost' => 0, 'status' => 'Pendente']);
        $meuPedido = FormProposal::create(['name' => 'Zeferino Meu Pedido', 'email' => 'a@example.com', 'phone' => '911', 'client_id' => $meuCliente->id]);
        FormProposal::create(['name' => 'Zeferino Pedido Alheio', 'email' => 'b@example.com', 'phone' => '922', 'client_id' => $clienteAlheio->id]);
        FormProposal::create(['name' => 'Zeferino Pedido Sem Cliente', 'email' => 'c@example.com', 'phone' => '933']);

        $this->pesquisar($user, 'Zeferino')
            ->assertSee('Zeferino Minha Lead')->assertSee('Zeferino Meu Cliente')->assertSee('Zeferino Minha')->assertSee('Zeferino Meu Pedido')
            ->assertSee('href="' . route('admin.mobile.leads.show', $minhaLead->id) . '"', false)
            ->assertSee('href="' . route('admin.mobile.proposals.show', $minhaCotacao->id) . '"', false)
            ->assertSee('href="' . route('admin.mobile.form-proposals.show', $meuPedido->id) . '"', false)
            ->assertDontSee('Alheia')->assertDontSee('Alheio')->assertDontSee('Sem Dono')->assertDontSee('Sem Cliente');
    }

    #[DataProvider('enforce')]
    public function test_so_entram_as_seccoes_que_o_utilizador_pode_ver(bool $enforce): void
    {
        config(['permissions.enforce' => $enforce]);
        Client::create(['name' => 'Zeferino Lead', 'is_lead' => true]);
        $cliente = Client::create(['name' => 'Zeferino Cliente', 'is_lead' => false]);
        Proposal::create(['client_id' => $cliente->id, 'brand' => 'Zeferino', 'model' => 'X', 'transport_cost' => 0, 'status' => 'Pendente']);

        $this->pesquisar($this->userWith(['clients.view.all']), 'Zeferino')
            ->assertSee('Zeferino Cliente')->assertDontSee('Zeferino Lead')->assertDontSee('Zeferino X')->assertDontSee('Cotações');
    }

    #[DataProvider('enforce')]
    public function test_sem_permissoes_de_ver_nao_encontra_nada(bool $enforce): void
    {
        // Como no desktop: a rota de pesquisa é partilhada e devolve zero secções.
        config(['permissions.enforce' => $enforce]);
        $lead = Client::create(['name' => 'Zeferino Lead', 'is_lead' => true]);
        $user = $this->userWith([]);

        $this->pesquisar($user, 'Zeferino')
            ->assertSee('Nenhum resultado')->assertDontSee('Zeferino Lead')
            ->assertDontSee(route('admin.mobile.leads.show', $lead->id), false);
    }

    public function test_sem_acesso_mobile_vai_para_o_desktop_e_angariador_puro_fica_barrado(): void
    {
        $this->actingAs($this->mobileUser(false))->get(route('admin.mobile.search', ['q' => 'Zeferino']))
            ->assertRedirect(route('admin.v2.dashboard'));

        $angariador = $this->backofficeUser('angariador');
        $angariador->forceFill(['access_mobile' => true])->save();
        $this->actingAs($angariador)->get(route('admin.mobile.search', ['q' => 'Zeferino']))->assertForbidden();
    }

    public function test_q_em_array_nao_rebenta(): void
    {
        $this->actingAs($this->mobileUser())->get(route('admin.mobile.search') . '?q[]=x')->assertOk();
    }
}
