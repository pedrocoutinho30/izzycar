<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FormProposal;
use App\Models\Legalization;
use App\Models\Proposal;
use App\Models\Sale;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Fixa o JSON da pesquisa global do desktop antes de as consultas passarem
 * para o GlobalSearchService (partilhado com a pesquisa mobile).
 */
class GlobalSearchCharacterizationTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function pesquisar(string $term): array
    {
        return $this->actingAs($this->backofficeUser('admin'))
            ->getJson(route('admin.v2.search', ['q' => $term]))
            ->assertOk()->json('groups');
    }

    public function test_termo_curto_devolve_grupos_vazios(): void
    {
        Client::create(['name' => 'X', 'is_lead' => true]);

        $this->assertSame([], $this->pesquisar('X'));
    }

    public function test_devolve_todas_as_seccoes_com_o_formato_atual(): void
    {
        $lead = Client::create(['name' => 'Zeferino Lead', 'email' => 'zl@example.com', 'phone' => '911', 'is_lead' => true]);
        $cliente = Client::create(['name' => 'Zeferino Cliente', 'phone' => '922', 'is_lead' => false]);
        $proposta = Proposal::create(['client_id' => $cliente->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0, 'status' => 'Pendente']);
        $form = FormProposal::create(['name' => 'Zeferino Form', 'email' => 'zf@example.com', 'phone' => '933', 'brand' => 'Fiat', 'model' => 'Panda']);
        $leg = Legalization::create(['client_id' => $cliente->id, 'marca' => 'Audi', 'modelo' => 'A3', 'matricula' => 'AA-00-ZZ', 'combustivel' => 'Gasolina', 'steps_completed' => []]);
        $viatura = V3Vehicle::create(['reference' => 'REF-1', 'brand' => 'Zeferino', 'model' => 'Car']);
        $venda = Sale::create(['client_id' => $cliente->id, 'v3_vehicle_id' => $viatura->id, 'sale_date' => now()->toDateString(), 'sale_price' => 1000, 'vat_rate' => 'Sem IVA', 'payment_method' => 'Transferência']);

        $this->assertSame([
            ['label' => 'Leads', 'icon' => 'bi-funnel', 'color' => 'primary', 'count' => 1, 'items' => [
                ['title' => 'Zeferino Lead', 'subtitle' => 'zl@example.com · 911', 'url' => route('admin.v2.leads.show', $lead->id)],
            ]],
            ['label' => 'Clientes', 'icon' => 'bi-people', 'color' => 'info', 'count' => 1, 'items' => [
                ['title' => 'Zeferino Cliente', 'subtitle' => '922', 'url' => route('admin.v2.clients.show', $cliente->id)],
            ]],
            ['label' => 'Cotações', 'icon' => 'bi-file-earmark-text', 'color' => 'warning', 'count' => 1, 'items' => [
                ['title' => 'BMW i4', 'subtitle' => 'Zeferino Cliente', 'url' => route('admin.v2.proposals.edit', $proposta->id)],
            ]],
            ['label' => 'Formulários', 'icon' => 'bi-envelope-open', 'color' => 'secondary', 'count' => 1, 'items' => [
                ['title' => 'Zeferino Form', 'subtitle' => 'zf@example.com · 933', 'url' => route('admin.v2.form-proposals.show', $form->id)],
            ]],
            ['label' => 'Legalizações', 'icon' => 'bi-file-earmark-check', 'color' => 'dark', 'count' => 1, 'items' => [
                ['title' => 'Audi A3', 'subtitle' => 'AA-00-ZZ · Zeferino Cliente', 'url' => route('admin.legalizations.show', $leg->id)],
            ]],
            ['label' => 'Viaturas', 'icon' => 'bi-car-front-fill', 'color' => 'success', 'count' => 1, 'items' => [
                ['title' => 'Zeferino Car', 'subtitle' => 'REF-1', 'url' => route('admin.v3.vehicles.edit', $viatura->id)],
            ]],
            ['label' => 'Vendas', 'icon' => 'bi-cash-coin', 'color' => 'danger', 'count' => 1, 'items' => [
                ['title' => 'Zeferino Car', 'subtitle' => 'Zeferino Cliente', 'url' => route('admin.v2.sales.edit', $venda->id)],
            ]],
        ], $this->pesquisar('Zeferino'));
    }

    public function test_marca_e_modelo_em_campos_separados_e_codigo_da_cotacao(): void
    {
        $cliente = Client::create(['name' => 'Ana', 'is_lead' => false]);
        $proposta = Proposal::create(['client_id' => $cliente->id, 'brand' => 'Mercedes', 'model' => 'CLA 200', 'transport_cost' => 0, 'status' => 'Pendente']);
        $proposta->forceFill(['proposal_code' => 'COT-777'])->save();
        Proposal::create(['client_id' => $cliente->id, 'brand' => 'Fiat', 'model' => 'Panda', 'transport_cost' => 0, 'status' => 'Pendente']);

        $titulos = fn (string $termo) => collect($this->pesquisar($termo))->firstWhere('label', 'Cotações')['items'] ?? [];

        $this->assertSame(['Mercedes CLA 200'], array_column($titulos('Mercedes CLA'), 'title'));
        $this->assertSame(['Mercedes CLA 200'], array_column($titulos('CLA Mercedes'), 'title'));
        $this->assertSame(['Mercedes CLA 200'], array_column($titulos('COT-777'), 'title'));
    }

    public function test_curingas_do_like_continuam_ativos_no_desktop(): void
    {
        // Comportamento atual (não alterar): o desktop não escapa % e _.
        Client::create(['name' => 'Ana Lead', 'is_lead' => true]);

        $this->assertSame(['Ana Lead'], array_column(collect($this->pesquisar('A_a'))->firstWhere('label', 'Leads')['items'], 'title'));
    }

    public function test_resultados_completos_mostram_a_pagina(): void
    {
        Client::create(['name' => 'Zeferino Lead', 'is_lead' => true]);

        $this->actingAs($this->backofficeUser('admin'))->get(route('admin.v2.search.results', ['q' => 'Zeferino']))
            ->assertOk()->assertSee('Zeferino Lead');
    }
}
