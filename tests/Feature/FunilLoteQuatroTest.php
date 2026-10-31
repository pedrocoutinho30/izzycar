<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ConsignmentEvaluation;
use App\Models\Legalization;
use App\Models\LeadActivity;
use App\Models\Sale;
use App\Models\Task;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Vendas encaminhadas para o V3, tarefa do Modelo 9 idempotente, leads de consignação/retoma
 * e badges do menu calculados uma vez.
 */
class FunilLoteQuatroTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    // ---- Vendas ----

    public function test_registar_venda_encaminha_para_o_fluxo_v3(): void
    {
        $this->actingAs($this->backofficeUser())
            ->get(route('admin.v2.sales.create', ['client_id' => 1]))
            ->assertRedirect(route('admin.v3.vehicles.index', ['status' => 'em_stock']))
            ->assertSessionHas('success');
    }

    public function test_formulario_v1_continua_acessivel_com_legacy(): void
    {
        $this->actingAs($this->backofficeUser())
            ->get(route('admin.v2.sales.create', ['legacy' => 1]))
            ->assertOk();
    }

    public function test_editar_venda_v3_abre_o_separador_venda_da_viatura(): void
    {
        $vehicle = V3Vehicle::create(['brand' => 'Audi', 'model' => 'A3', 'year' => 2020, 'fuel' => 'Diesel', 'status' => 'vendido', 'reference' => 'V3-2026-001']);
        $client  = Client::create(['name' => 'Comprador']);
        $sale    = Sale::create(['v3_vehicle_id' => $vehicle->id, 'client_id' => $client->id, 'sale_date' => '2026-10-01', 'sale_price' => 10000, 'vat_rate' => 23, 'payment_method' => 'Transferência']);

        $this->actingAs($this->backofficeUser())
            ->get(route('admin.v2.sales.edit', $sale->id))
            ->assertRedirect(route('admin.v3.vehicles.edit', $vehicle->id))
            ->assertSessionHas('return_tab', 'sale');
    }

    public function test_ficha_do_cliente_aponta_para_o_fluxo_de_venda(): void
    {
        $client = Client::create(['name' => 'Cliente Venda']);

        $this->actingAs($this->backofficeUser())
            ->get(route('admin.v2.clients.show', $client->id))
            ->assertOk()
            ->assertSee('Registar Venda');
    }

    // ---- Legalização: tarefa do Modelo 9 ----

    private function legalization(): Legalization
    {
        return Legalization::create(['marca' => 'Tesla', 'modelo' => 'Y', 'combustivel' => 'Elétrico', 'steps_completed' => [1, 2, 3, 4]]);
    }

    private function toggle5(Legalization $l)
    {
        return $this->actingAs($this->backofficeUser())
            ->postJson(route('admin.legalizations.toggle-step', $l), ['step' => 5]);
    }

    public function test_passo_5_cria_a_tarefa_uma_so_vez_ao_desmarcar_e_marcar(): void
    {
        $l = $this->legalization();

        $this->toggle5($l)->assertOk()->assertJson(['task_created' => true]);   // marca
        $this->toggle5($l)->assertOk()->assertJson(['task_created' => false]);  // desmarca
        $this->toggle5($l)->assertOk()->assertJson(['task_created' => false]);  // marca outra vez

        $this->assertSame(1, Task::where('title', 'like', 'Entregar Modelo 9 IMT%')->count());
    }

    public function test_conclui_o_passo_de_novo_se_a_tarefa_anterior_ja_estiver_concluida(): void
    {
        $l = $this->legalization();
        $this->toggle5($l);
        Task::query()->update(['status' => 'concluida']);

        $this->toggle5($l);                       // desmarca
        $this->toggle5($l)->assertJson(['task_created' => true]);

        $this->assertSame(2, Task::count());
    }

    public function test_mover_no_kanban_tambem_nao_duplica_a_tarefa(): void
    {
        $l = $this->legalization();
        $admin = $this->backofficeUser();

        $this->actingAs($admin)->postJson(route('admin.legalizations.move', $l), ['column' => '6'])->assertJson(['task_created' => true]);
        $this->actingAs($admin)->postJson(route('admin.legalizations.move', $l), ['column' => '3']);
        $this->actingAs($admin)->postJson(route('admin.legalizations.move', $l), ['column' => '6'])->assertJson(['task_created' => false]);

        $this->assertSame(1, Task::count());
    }

    public function test_tarefas_de_processos_diferentes_nao_se_confundem(): void
    {
        $service = app(\App\Services\LegalizationTaskService::class);
        $a = $this->legalization();
        $b = $this->legalization();

        $this->assertNotNull($service->ensureModelo9Task($a));
        $this->assertNotNull($service->ensureModelo9Task($b));
        $this->assertNull($service->ensureModelo9Task($a));
        $this->assertSame(2, Task::count());
    }

    // ---- Consignação e retoma ----

    private function consignacao(array $extra = [])
    {
        return $this->post('/consignacao/avaliacao', $extra + [
            'brand' => 'Opel', 'model' => 'Astra', 'year' => 2020, 'kilometers' => 1000,
            'plate' => 'AA-00-BB', 'fuel' => 'Gasolina', 'gearbox' => 'Manual', 'condition' => 'Bom',
            'name' => 'Rita Consigna', 'phone' => '912000111', 'email' => 'rita@example.com',
            'privacy_consent' => '1', '_form_started' => time() - 10,
        ]);
    }

    public function test_consignacao_cria_lead_e_atividade_e_envia_email(): void
    {
        Mail::fake();

        $this->consignacao()->assertOk()->assertJson(['status' => 'success']);

        $client = Client::where('email', 'rita@example.com')->firstOrFail();
        $this->assertTrue((bool) $client->is_lead);
        $this->assertSame('consignacao', $client->lead_source);
        $this->assertSame(1, ConsignmentEvaluation::count());
        $this->assertTrue(LeadActivity::where('client_id', $client->id)->where('title', 'like', 'Pedido de avaliação para consignação%')->exists());
    }

    public function test_consignacao_reaproveita_o_cliente_existente(): void
    {
        Mail::fake();
        $existente = Client::create(['name' => 'Rita', 'email' => 'rita@example.com', 'phone' => '912000111', 'is_lead' => false]);

        $this->consignacao()->assertOk();

        $this->assertSame(1, Client::count());
        $this->assertTrue(LeadActivity::where('client_id', $existente->id)->exists());
    }

    public function test_consignacao_guarda_pedido_e_lead_mesmo_que_o_email_falhe(): void
    {
        Mail::shouldReceive('raw')->andThrow(new \RuntimeException('SMTP em baixo'));

        $this->consignacao()->assertOk()->assertJson(['status' => 'success']);

        $this->assertSame(1, ConsignmentEvaluation::count());
        $this->assertSame(1, Client::where('email', 'rita@example.com')->count());
    }

    public function test_retoma_cria_lead_atividade_e_sobrevive_a_falha_de_email(): void
    {
        Mail::shouldReceive('raw')->andThrow(new \RuntimeException('SMTP em baixo'));

        $this->post('/retomas', [
            'name' => 'Rui Retoma', 'phone' => '913000222', 'email' => 'rui@example.com',
            'brand' => 'Seat', 'model' => 'Leon', 'year' => 2019, 'fuel' => 'Diesel',
            'body_type' => 'Hatchback', 'km' => 80000, 'sale_type' => 'Particular', '_form_started' => time() - 10,
        ])->assertOk()->assertJson(['status' => 'success']);

        $client = Client::where('email', 'rui@example.com')->firstOrFail();
        $this->assertSame('retoma', $client->lead_source);
        $this->assertTrue(LeadActivity::where('client_id', $client->id)->where('title', 'Pedido de retoma submetido')->exists());
    }

    // ---- Badges do menu ----

    public function test_badges_do_menu_calculam_cada_contagem_uma_so_vez(): void
    {
        Client::create(['name' => 'Lead 1', 'is_lead' => true, 'lead_status' => 'nova']);
        Client::create(['name' => 'Lead 2', 'is_lead' => true, 'lead_status' => 'perdida']);

        $admin = $this->backofficeUser();
        \DB::enableQueryLog();
        $this->actingAs($admin)->get(route('admin.v2.manual'))->assertOk();
        $queries = collect(\DB::getQueryLog())->pluck('query');

        $porLeads = $queries->filter(fn ($q) => str_contains($q, 'from "clients"') && str_contains($q, '"is_lead"') && str_contains($q, 'not in') && str_contains($q, 'count(*)'));
        $this->assertLessThanOrEqual(1, $porLeads->count());
        $this->assertLessThanOrEqual(1, $queries->filter(fn ($q) => str_contains($q, 'from "proposals"') && str_contains($q, '"status" = ?') && str_contains($q, 'count(*)'))->count());
        $this->assertLessThanOrEqual(1, $queries->filter(fn ($q) => str_contains($q, 'from "cost_simulators"') && str_contains($q, 'count(*)'))->count());
    }
}
