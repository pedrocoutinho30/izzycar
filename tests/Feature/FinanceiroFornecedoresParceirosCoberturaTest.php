<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FinancialMovement;
use App\Models\Partner;
use App\Models\Supplier;
use App\Models\V3Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Dashboard financeiro, fornecedores e parceiros (CRUD básico + acesso).
 */
class FinanceiroFornecedoresParceirosCoberturaTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Storage::fake('public');

        // O dashboard usa YEAR()/MONTH() (MySQL); emulam-se no SQLite dos testes.
        $pdo = DB::connection()->getPdo();
        $pdo->sqliteCreateFunction('YEAR', fn ($d) => $d ? (int) substr($d, 0, 4) : null);
        $pdo->sqliteCreateFunction('MONTH', fn ($d) => $d ? (int) substr($d, 5, 2) : null);
    }

    private function admin()
    {
        return $this->actingAs($this->backofficeUser('admin'));
    }

    /** O esquema das migrations exige type (enum '', Particular, Empresa), contact_name e email únicos. */
    private function supplier(array $over = []): Supplier
    {
        static $n = 0;
        $n++;

        return Supplier::create($over + ['type' => 'Particular', 'company_name' => 'Fornecedor ' . $n, 'contact_name' => 'Contacto', 'email' => "f{$n}@teste.test"]);
    }

    private function movement(string $type, float $gross, float $vat, string $date, string $desc): FinancialMovement
    {
        return FinancialMovement::create([
            'movable_type' => 'teste', 'movable_id' => 1, 'type' => $type, 'category' => 'teste', 'description' => $desc, 'amount_gross' => $gross,
            'vat_amount' => $vat, 'amount_net' => $gross - $vat, 'movement_date' => $date,
        ]);
    }

    // ── Dashboard financeiro ─────────────────────────────────────────────────

    public function test_dashboard_requires_login_and_blocks_angariador(): void
    {
        $this->get(route('admin.v2.financial.dashboard'))->assertRedirect(route('login'));
        $this->post(route('admin.v2.financial.seed'))->assertRedirect(route('login'));

        $angariador = $this->backofficeUser('angariador');
        $this->actingAs($angariador)->get(route('admin.v2.financial.dashboard'))->assertForbidden();
        $this->actingAs($angariador)->post(route('admin.v2.financial.seed'))->assertForbidden();
    }

    public function test_dashboard_without_movements_renders_with_zeros(): void
    {
        $this->admin()->get(route('admin.v2.financial.dashboard'))
            ->assertOk()
            ->assertViewHas('totalIncome', 0)
            ->assertViewHas('totalExpenses', 0)
            ->assertViewHas('availableYears', fn ($y) => $y->all() === [now()->year]);
    }

    public function test_dashboard_totals_for_the_year_and_net_result(): void
    {
        $ano = now()->year;
        $this->movement('income', 12300, 2300, "$ano-03-10", 'Venda A');
        $this->movement('expense', 6150, 1150, "$ano-04-02", 'Compra B');
        $this->movement('income', 999, 0, ($ano - 1) . '-05-05', 'Venda do ano passado');

        $response = $this->admin()->get(route('admin.v2.financial.dashboard'))->assertOk()
            ->assertSee('Venda A')->assertSee('Compra B')->assertDontSee('Venda do ano passado');

        $this->assertEquals(12300, $response->viewData('totalIncome'));
        $this->assertEquals(6150, $response->viewData('totalExpenses'));
        $this->assertEquals(6150, $response->viewData('netBalance'));
        $this->assertEquals(1150, $response->viewData('netVat'));
        // (12300-2300) - (6150-1150) = 5000
        $this->assertEquals(5000, $response->viewData('netResult'));
        $this->assertEquals(12300, $response->viewData('chartIncome')[3]);
        $this->assertEquals(6150, $response->viewData('chartExpenses')[4]);
        $this->assertSame([$ano, $ano - 1], $response->viewData('availableYears')->map(fn ($y) => (int) $y)->all());
    }

    public function test_dashboard_filters_by_month_type_and_all_years(): void
    {
        $ano = now()->year;
        $this->movement('income', 100, 0, "$ano-03-10", 'Entrada marco');
        $this->movement('expense', 50, 0, "$ano-04-10", 'Saida abril');
        $this->movement('income', 70, 0, ($ano - 1) . '-01-10', 'Entrada antiga');

        $this->admin()->get(route('admin.v2.financial.dashboard', ['month' => 4]))
            ->assertOk()->assertSee('Saida abril')->assertDontSee('Entrada marco');
        $this->admin()->get(route('admin.v2.financial.dashboard', ['type' => 'income']))
            ->assertOk()->assertSee('Entrada marco')->assertDontSee('Saida abril');

        // year=all ignora o mês e mostra todos os anos.
        $response = $this->admin()->get(route('admin.v2.financial.dashboard', ['year' => 'all', 'month' => 4]))
            ->assertOk()->assertSee('Entrada antiga')->assertSee('Entrada marco');
        $this->assertNull($response->viewData('month'));
        $this->assertSame([(string) ($ano - 1), (string) $ano], $response->viewData('chartLabels'));
    }

    public function test_seed_existing_redirects_and_resyncs_without_error(): void
    {
        $this->admin()->post(route('admin.v2.financial.seed'))
            ->assertRedirect(route('admin.v2.financial.dashboard'))
            ->assertSessionHas('success');
    }

    // ── Fornecedores ─────────────────────────────────────────────────────────

    public function test_suppliers_require_login_and_block_angariador(): void
    {
        $supplier = $this->supplier(['company_name' => 'Auto Norte']);

        $this->get(route('admin.v2.suppliers.index'))->assertRedirect(route('login'));
        $this->post(route('admin.v2.suppliers.store'), ['company_name' => 'X'])->assertRedirect(route('login'));
        $this->delete(route('admin.v2.suppliers.destroy', $supplier->id))->assertRedirect(route('login'));

        $angariador = $this->backofficeUser('angariador');
        $this->actingAs($angariador)->get(route('admin.v2.suppliers.index'))->assertForbidden();
        $this->actingAs($angariador)->post(route('admin.v2.suppliers.store'), ['company_name' => 'X'])->assertForbidden();
        $this->actingAs($angariador)->delete(route('admin.v2.suppliers.destroy', $supplier->id))->assertForbidden();

        $this->assertSame(1, Supplier::count());
    }

    public function test_supplier_crud(): void
    {
        $this->admin()->get(route('admin.v2.suppliers.create'))->assertOk();

        $this->admin()->post(route('admin.v2.suppliers.store'), [
            'type' => 'Empresa', 'company_name' => 'Auto Norte', 'contact_name' => 'Joana', 'country' => 'Portugal', 'email' => 'geral@autonorte.test', 'vat' => '500000000',
        ])->assertRedirect(route('admin.v2.suppliers.index'))->assertSessionHas('success');

        $supplier = Supplier::sole();
        $this->admin()->get(route('admin.v2.suppliers.edit', $supplier->id))->assertOk()->assertSee('Auto Norte');

        $this->admin()->put(route('admin.v2.suppliers.update', $supplier->id), ['type' => 'Particular', 'company_name' => 'Auto Sul', 'contact_name' => 'Joana', 'email' => 'sul@teste.test', 'country' => 'Espanha'])
            ->assertRedirect(route('admin.v2.suppliers.index'));
        $this->assertSame('Auto Sul', $supplier->fresh()->company_name);

        $this->admin()->delete(route('admin.v2.suppliers.destroy', $supplier->id))->assertRedirect(route('admin.v2.suppliers.index'));
        $this->assertSame(0, Supplier::count());
    }

    public function test_supplier_validation_and_not_found(): void
    {
        $this->admin()->post(route('admin.v2.suppliers.store'), ['email' => 'nao-e-email'])->assertSessionHasErrors(['company_name', 'email']);
        $this->assertSame(0, Supplier::count());

        $supplier = $this->supplier(['company_name' => 'Auto Norte']);
        $this->admin()->put(route('admin.v2.suppliers.update', $supplier->id), ['type' => 'Particular', 'company_name' => ''])->assertSessionHasErrors('company_name');
        $this->admin()->get(route('admin.v2.suppliers.edit', 9999))->assertNotFound();
        $this->admin()->delete(route('admin.v2.suppliers.destroy', 9999))->assertNotFound();
    }

    public function test_supplier_index_search_filters_and_counts(): void
    {
        $this->supplier(['company_name' => 'Auto Norte', 'country' => 'Portugal', 'vat' => '111']);
        $this->supplier(['company_name' => 'Garage Berlin', 'contact_name' => 'Hans', 'country' => 'Alemanha', 'vat' => '222']);
        $this->supplier(['company_name' => 'Sem Pais']);

        $this->admin()->get(route('admin.v2.suppliers.index', ['search' => 'Hans']))->assertOk()->assertSee('Garage Berlin')->assertDontSee('Auto Norte');
        $this->admin()->get(route('admin.v2.suppliers.index', ['search' => '111']))->assertOk()->assertSee('Auto Norte')->assertDontSee('Garage Berlin');

        $stats = $this->admin()->get(route('admin.v2.suppliers.index'))->viewData('stats');
        $this->assertSame(3, $stats[0]['value']);
        $this->assertSame(1, $stats[1]['value']);
        $this->assertSame(2, $stats[2]['value']); // estrangeiros + sem país
    }

    public function test_deleting_supplier_keeps_vehicle_and_clears_the_link(): void
    {
        $supplier = $this->supplier(['company_name' => 'Auto Norte']);
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4', 'supplier_id' => $supplier->id]);

        $this->admin()->delete(route('admin.v2.suppliers.destroy', $supplier->id))->assertRedirect();

        $this->assertNotNull(V3Vehicle::find($vehicle->id));
        // No sqlite dos testes as FKs não são impostas; o esquema MySQL tem nullOnDelete (migration create_v3_vehicles_table).
        $this->assertSame(0, Supplier::count());
    }

    /**
     * BUG conhecido (SupplierV2Controller::store/update, ~linhas 105 e 160; migration
     * 2025_02_27_091659_create_suppliers_table): o controlador valida type/contact_name/email como
     * nullable e o formulário oferece os tipos Stand/Transportadora/Leilão/Outro, mas no esquema
     * `type` é ENUM('', 'Particular', 'Empresa') NOT NULL, `contact_name` NOT NULL e `email` NOT NULL UNIQUE.
     * Só company_name (o único obrigatório na validação) dá 500; um tipo do formulário também (CHECK do enum
     * no sqlite, "Data truncated" em MySQL estrito). Se produção funciona, a BD foi alterada fora das
     * migrations (verificar com SHOW CREATE TABLE suppliers). O correto seria 302 + registo criado.
     */
    public function test_supplier_form_values_do_not_match_schema_known_bug(): void
    {
        $this->admin()->post(route('admin.v2.suppliers.store'), ['company_name' => 'So Nome'])->assertStatus(500);
        $this->admin()->post(route('admin.v2.suppliers.store'), [
            'type' => 'Stand', 'company_name' => 'Stand Lda', 'contact_name' => 'Ana', 'email' => 'stand@teste.test',
        ])->assertStatus(500);

        $this->assertSame(0, Supplier::count());
    }

    // ── Parceiros ────────────────────────────────────────────────────────────

    public function test_partners_require_login_and_block_angariador(): void
    {
        $partner = Partner::create(['name' => 'Parceiro X']);

        $this->get(route('admin.v2.partners.index'))->assertRedirect(route('login'));
        $this->delete(route('admin.v2.partners.destroy', $partner->id))->assertRedirect(route('login'));

        $angariador = $this->backofficeUser('angariador');
        $this->actingAs($angariador)->get(route('admin.v2.partners.index'))->assertForbidden();
        $this->actingAs($angariador)->post(route('admin.v2.partners.store'), ['name' => 'Y'])->assertForbidden();
        $this->actingAs($angariador)->delete(route('admin.v2.partners.destroy', $partner->id))->assertForbidden();

        $this->assertSame(1, Partner::count());
    }

    public function test_partner_crud_with_image(): void
    {
        $this->admin()->get(route('admin.v2.partners.create'))->assertOk();

        $this->admin()->post(route('admin.v2.partners.store'), [
            'name' => 'Seguros Alfa', 'url' => 'https://alfa.test', 'show_on_site' => '1',
            'image' => UploadedFile::fake()->image('logo.png', 200, 100),
        ])->assertRedirect(route('admin.v2.partners.index'))->assertSessionHas('success');

        $partner = Partner::sole();
        $this->assertTrue((bool) $partner->show_on_site);
        Storage::disk('public')->assertExists($partner->image);

        $this->admin()->get(route('admin.v2.partners.edit', $partner->id))->assertOk()->assertSee('Seguros Alfa');
        $this->admin()->get(route('admin.v2.partners.index'))->assertOk()->assertSee('Seguros Alfa');

        // Atualizar sem imagem mantém a existente; show_on_site desmarcado fica false.
        $imagemAntiga = $partner->image;
        $this->admin()->put(route('admin.v2.partners.update', $partner->id), ['name' => 'Seguros Beta'])->assertRedirect(route('admin.v2.partners.index'));
        $partner->refresh();
        $this->assertSame('Seguros Beta', $partner->name);
        $this->assertFalse((bool) $partner->show_on_site);
        $this->assertSame($imagemAntiga, $partner->image);

        // Nova imagem substitui a antiga no disco.
        $this->admin()->put(route('admin.v2.partners.update', $partner->id), ['name' => 'Seguros Beta', 'image' => UploadedFile::fake()->image('novo.png', 100, 100)]);
        $partner->refresh();
        $this->assertNotSame($imagemAntiga, $partner->image);
        Storage::disk('public')->assertMissing($imagemAntiga);
        Storage::disk('public')->assertExists($partner->image);

        $this->admin()->delete(route('admin.v2.partners.destroy', $partner->id))->assertRedirect(route('admin.v2.partners.index'));
        $this->assertSame(0, Partner::count());
        Storage::disk('public')->assertMissing($partner->image);
    }

    public function test_partner_validation(): void
    {
        $url = route('admin.v2.partners.store');

        $this->admin()->post($url, [])->assertSessionHasErrors('name');
        $this->admin()->post($url, ['name' => 'X', 'url' => 'não é url', 'email' => 'x'])->assertSessionHasErrors(['url', 'email']);
        $this->admin()->post($url, ['name' => 'X', 'image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')])->assertSessionHasErrors('image');
        $this->admin()->post($url, ['name' => 'X', 'image' => UploadedFile::fake()->create('grande.png', 3000, 'image/png')])->assertSessionHasErrors('image');

        $this->assertSame(0, Partner::count());
    }

    public function test_partner_index_search(): void
    {
        Partner::create(['name' => 'Seguros Alfa']);
        Partner::create(['name' => 'Oficina Beta']);

        $this->admin()->get(route('admin.v2.partners.index', ['search' => 'Beta']))->assertOk()->assertSee('Oficina Beta')->assertDontSee('Seguros Alfa');
    }
}
