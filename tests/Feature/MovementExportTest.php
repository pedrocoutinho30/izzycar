<?php

namespace Tests\Feature;

use App\Exports\MovementExport;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Partner;
use App\Models\V3Vehicle;
use App\Services\ExportService;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MovementExportTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-20 10:00:00');
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        // A listagem usa YEAR() (só MySQL) em availableYears.
        DB::connection()->getPdo()->sqliteCreateFunction('YEAR', fn ($d) => $d ? (int) substr($d, 0, 4) : null);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function mov(string $title, string $date = '2026-05-10', array $extra = []): Expense
    {
        return Expense::create($extra + [
            'movement_type' => 'expense',
            'category' => 'repair',
            'title' => $title,
            'amount' => 100,
            'amount_gross' => 100,
            'expense_date' => $date,
            'status' => 'paid',
        ]);
    }

    private function rows(?array $keys, array $filters = []): array
    {
        $definition = new MovementExport();
        $service = new ExportService();
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $service->write($definition, $service->resolveFields($definition, $keys), $definition->normalizeFilters($filters), $path);

        return $this->read($path);
    }

    private function read(string $path): array
    {
        $reader = new Reader();
        $reader->open($path);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->getCells();
            }
        }
        $reader->close();
        unlink($path);

        return $rows;
    }

    private function httpRows($response): array
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $response->streamedContent());

        return $this->read($path);
    }

    /** Valores da 1.ª coluna (descrição) das linhas de dados. */
    private function titles(array $rows): array
    {
        return array_map(fn ($r) => $r[0]->getValue(), array_slice($rows, 1));
    }

    public function test_route_returns_xlsx_with_dated_filename(): void
    {
        $this->mov('A');

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.v2.movements.export'))->assertOk();

        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertMatchesRegularExpression('/filename=movimentos_\d{8}_\d{6}\.xlsx$/', $response->headers->get('Content-Disposition'));
        $this->assertCount(2, $this->httpRows($response));
    }

    public function test_guests_cannot_export(): void
    {
        $this->get(route('admin.v2.movements.export'))->assertRedirect();
    }

    public function test_export_requires_the_view_action_of_movements(): void
    {
        $rule = app(\App\Permissions\PermissionRegistry::class)->ruleForRoute('admin.v2.movements.export', 'GET');

        $this->assertSame('movements', $rule->resource);
        $this->assertSame('view', $rule->action);
    }

    public function test_invalid_fields_and_filters_are_rejected(): void
    {
        $user = $this->backofficeUser();
        $get = fn (array $q) => $this->actingAs($user)->get(route('admin.v2.movements.export', $q));

        $get(['fields' => ['title', 'attachment_path']])->assertSessionHasErrors('fields.1');
        $get(['fields' => ['source_type']])->assertSessionHasErrors('fields.0');
        $get(['fields' => ['balance']])->assertSessionHasErrors('fields.0');
        $get(['movement_type' => 'xpto'])->assertSessionHasErrors('movement_type');
        $get(['category' => 'xpto'])->assertSessionHasErrors('category');
        $get(['status' => 'xpto'])->assertSessionHasErrors('status');
        $get(['year' => '20x6'])->assertSessionHasErrors('year');
        $get(['v3_vehicle_id' => 'abc'])->assertSessionHasErrors('v3_vehicle_id');
        $get(['date_from' => '2026-05-10', 'date_to' => '2026-05-01'])->assertSessionHasErrors('date_to');
    }

    public function test_catalog_has_no_balance_in_out_or_excluded_columns(): void
    {
        $keys = (new MovementExport())->keys();

        foreach (['balance', 'saldo', 'income', 'expense_amount', 'attachment_path', 'source_type', 'source_id', 'vehicle_id', 'expense_category'] as $excluded) {
            $this->assertNotContains($excluded, $keys);
        }
        $labels = array_map(fn ($f) => $f->label, (new MovementExport())->fields());
        $this->assertNotContains('Entrada', $labels);
        $this->assertNotContains('Saída', $labels);
        $this->assertNotContains('Saldo', $labels);
        $this->assertCount(20, $keys);
    }

    public function test_default_columns_and_header(): void
    {
        $this->mov('Pastilhas');

        $rows = $this->rows(null);

        $this->assertSame(
            ['Data', 'Tipo', 'Categoria', 'Descrição', 'Valor (c/ IVA)', 'Estado', 'Viatura (referência)'],
            array_map(fn ($c) => $c->getValue(), $rows[0]),
        );
    }

    public function test_amount_is_always_positive_with_type_apart(): void
    {
        $this->mov('Despesa X', '2026-05-10', ['movement_type' => 'expense', 'amount' => 80, 'amount_gross' => 80]);
        $this->mov('Receita Y', '2026-05-09', ['movement_type' => 'income', 'amount' => 500, 'amount_gross' => null]);

        $rows = $this->rows(['title', 'movement_type', 'amount']);

        $this->assertSame(['Despesa X', 'Despesa', 80.0], [$rows[1][0]->getValue(), $rows[1][1]->getValue(), (float) $rows[1][2]->getValue()]);
        // amount_gross nulo: usa amount
        $this->assertSame(['Receita Y', 'Receita', 500.0], [$rows[2][0]->getValue(), $rows[2][1]->getValue(), (float) $rows[2][2]->getValue()]);
    }

    public function test_vat_empty_is_not_zero_and_dates_are_native(): void
    {
        $this->mov('Sem IVA registado', '2026-05-10', ['vat_rate' => null, 'vat_amount' => null]);
        $this->mov('IVA zero', '2026-05-09', ['vat_rate' => 0, 'vat_amount' => 0, 'amount_net' => 100]);
        $this->mov('IVA 23', '2026-05-08', ['vat_rate' => 23, 'vat_amount' => 18.7, 'amount_net' => 81.3]);

        $rows = $this->rows(['title', 'vat_rate', 'vat_amount', 'amount_net', 'expense_date', 'created_at']);

        $this->assertInstanceOf(\OpenSpout\Common\Entity\Cell\EmptyCell::class, $rows[1][1]);
        $this->assertInstanceOf(\OpenSpout\Common\Entity\Cell\EmptyCell::class, $rows[1][2]);
        $this->assertEquals(0, $rows[2][1]->getValue());
        $this->assertNotInstanceOf(\OpenSpout\Common\Entity\Cell\EmptyCell::class, $rows[2][1]);
        $this->assertEquals(0.23, $rows[3][1]->getValue()); // percentagem nativa (23%)
        $this->assertEquals(18.7, $rows[3][2]->getValue());
        $this->assertInstanceOf(DateTimeInterface::class, $rows[1][4]->getValue());
        $this->assertSame('2026-05-10', $rows[1][4]->getValue()->format('Y-m-d'));
        $this->assertInstanceOf(DateTimeInterface::class, $rows[1][5]->getValue());
    }

    public function test_origin_attachment_payment_and_booleans(): void
    {
        $this->mov('Manual', '2026-05-10', ['payment_method' => 'mbway', 'attachment_path' => 'movements/attachments/secreto.pdf']);
        $this->mov('Auto', '2026-05-09', ['source_type' => V3Vehicle::class, 'source_id' => 7, 'payment_method' => 'metodo_novo']);

        $rows = $this->rows(['title', 'origin', 'has_attachment', 'payment_method']);

        $this->assertSame(['Manual', 'Manual', 'Sim', 'MBWay'], array_map(fn ($c) => $c->getValue(), $rows[1]));
        $this->assertSame(['Auto', 'Automático', 'Não', 'metodo_novo'], array_map(fn ($c) => $c->getValue(), $rows[2]));
        $this->assertStringNotContainsString('secreto', json_encode($this->rows(array_map(fn ($f) => $f->key, (new MovementExport())->fields()))));
    }

    public function test_relations_are_exported(): void
    {
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4', 'registration' => 'AA-11-BB']);
        $client = Client::create(['name' => 'Cliente Teste', 'is_lead' => false]);
        $partner = Partner::create(['name' => 'Oficina Boa']);
        $this->mov('Com tudo', '2026-05-10', ['v3_vehicle_id' => $vehicle->id, 'client_id' => $client->id, 'partner_id' => $partner->id, 'legalization_id' => null]);

        $rows = $this->rows(['vehicle_reference', 'vehicle_name', 'vehicle_registration', 'client', 'partner']);

        $this->assertSame([$vehicle->reference, 'BMW i4', 'AA-11-BB', 'Cliente Teste', 'Oficina Boa'], array_map(fn ($c) => $c->getValue(), $rows[1]));
    }

    public function test_order_is_the_listing_order(): void
    {
        $this->mov('Antigo', '2026-01-01');
        $a = $this->mov('Mesmo dia, criado antes', '2026-05-10');
        $b = $this->mov('Mesmo dia, criado depois', '2026-05-10');
        DB::table('expenses')->where('id', $a->id)->update(['created_at' => '2026-05-10 08:00:00']);
        DB::table('expenses')->where('id', $b->id)->update(['created_at' => '2026-05-10 09:00:00']);
        $this->mov('Recente', '2026-09-01');

        $this->assertSame(
            ['Recente', 'Mesmo dia, criado depois', 'Mesmo dia, criado antes', 'Antigo'],
            $this->titles($this->rows(['title'])),
        );
    }

    public function test_default_is_current_year_and_year_all_has_no_limit(): void
    {
        $this->mov('2026', '2026-03-01');
        $this->mov('2025', '2025-03-01');
        $this->mov('2024', '2024-03-01');

        $this->assertSame(['2026'], $this->titles($this->rows(['title'])));
        $this->assertSame(['2025'], $this->titles($this->rows(['title'], ['year' => '2025'])));
        $this->assertSame(['2026', '2025', '2024'], $this->titles($this->rows(['title'], ['year' => 'all'])));

        $user = $this->backofficeUser();
        $this->assertSame(['2026'], $this->titles($this->httpRows($this->actingAs($user)->get(route('admin.v2.movements.export', ['fields' => ['title']])))));
        $this->assertCount(4, $this->httpRows($this->actingAs($user)->get(route('admin.v2.movements.export', ['fields' => ['title'], 'year' => 'all']))));
    }

    public function test_dates_override_the_year(): void
    {
        $this->mov('2026', '2026-03-01');
        $this->mov('2025 dentro', '2025-03-05');
        $this->mov('2025 fora', '2025-06-01');

        $titles = $this->titles($this->rows(['title'], ['date_from' => '2025-03-01', 'date_to' => '2025-03-31', 'year' => '2026']));

        $this->assertSame(['2025 dentro'], $titles);
        $this->assertSame(['2026', '2025 fora', '2025 dentro'], $this->titles($this->rows(['title'], ['date_from' => '2025-01-01'])));
    }

    public function test_filters_apply_to_all_pages(): void
    {
        foreach (range(1, 120) as $i) {
            $this->mov("Pneu {$i}", '2026-05-10', ['category' => 'repair']);
        }
        $this->mov('Outra', '2026-05-10', ['category' => 'fuel', 'movement_type' => 'income', 'status' => 'pending', 'observations' => 'nota pneu']);
        $user = $this->backofficeUser();

        // 121 linhas + cabeçalho: mais do que a página da listagem (50)
        $this->assertCount(122, $this->httpRows($this->actingAs($user)->get(route('admin.v2.movements.export', ['fields' => ['title']]))));
        $this->assertSame(['Outra'], $this->titles($this->httpRows($this->actingAs($user)->get(route('admin.v2.movements.export', ['category' => 'fuel', 'fields' => ['title']])))));
        $this->assertSame(['Outra'], $this->titles($this->httpRows($this->actingAs($user)->get(route('admin.v2.movements.export', ['movement_type' => 'income', 'fields' => ['title']])))));
        $this->assertSame(['Outra'], $this->titles($this->httpRows($this->actingAs($user)->get(route('admin.v2.movements.export', ['status' => 'pending', 'fields' => ['title']])))));
        // pesquisa em título e observações: 120 títulos + 1 observação, + cabeçalho
        $this->assertCount(122, $this->httpRows($this->actingAs($user)->get(route('admin.v2.movements.export', ['search' => 'pneu', 'fields' => ['title']]))));
    }

    public function test_vehicle_and_client_filters(): void
    {
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4']);
        $client = Client::create(['name' => 'C', 'is_lead' => false]);
        $this->mov('Da viatura', '2026-05-10', ['v3_vehicle_id' => $vehicle->id]);
        $this->mov('Do cliente', '2026-05-10', ['client_id' => $client->id]);
        $this->mov('Solta');

        $this->assertSame(['Da viatura'], $this->titles($this->rows(['title'], ['v3_vehicle_id' => $vehicle->id])));
        $this->assertSame(['Do cliente'], $this->titles($this->rows(['title'], ['client_id' => $client->id])));
    }

    public function test_batches_beyond_the_chunk_size_keep_order_and_count(): void
    {
        $rows = [];
        foreach (range(1, 1100) as $i) {
            $rows[] = ['movement_type' => 'expense', 'title' => "M{$i}", 'amount' => 1, 'expense_date' => '2026-05-10', 'created_at' => '2026-05-10 10:00:00', 'updated_at' => '2026-05-10 10:00:00'];
        }
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('expenses')->insert($chunk);
        }

        $titles = $this->titles($this->rows(['title']));

        $this->assertCount(1100, $titles);
        $this->assertCount(1100, array_unique($titles));
        $this->assertSame('M1100', $titles[0]); // empate de data/criação desfeito pelo id desc
    }

    public function test_query_count_is_constant(): void
    {
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4']);
        $client = Client::create(['name' => 'C', 'is_lead' => false]);
        $partner = Partner::create(['name' => 'P']);
        foreach (range(1, 40) as $i) {
            $this->mov("M{$i}", '2026-05-10', ['v3_vehicle_id' => $vehicle->id, 'client_id' => $client->id, 'partner_id' => $partner->id]);
        }

        DB::enableQueryLog();
        $rows = $this->rows(array_map(fn ($f) => $f->key, (new MovementExport())->fields()));
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertCount(41, $rows);
        $this->assertLessThanOrEqual(6, $queries);
    }

    public function test_only_chosen_relations_are_loaded(): void
    {
        $this->mov('A');

        DB::enableQueryLog();
        $this->rows(['title', 'amount']);
        $sql = implode(' ', array_column(DB::getQueryLog(), 'query'));
        DB::disableQueryLog();

        $this->assertStringNotContainsString('v3_vehicles', $sql);
        $this->assertStringNotContainsString('"clients"', $sql);
        $this->assertStringNotContainsString('partners', $sql);
    }

    public function test_audit_log_is_written_without_personal_data(): void
    {
        $client = Client::create(['name' => 'Maria Segredo', 'is_lead' => false]);
        $this->mov('Reparação confidencial', '2026-05-10', ['client_id' => $client->id, 'observations' => 'NIF 123456789']);
        $user = $this->backofficeUser();

        $this->actingAs($user)->get(route('admin.v2.movements.export', ['fields' => ['title', 'client'], 'search' => 'confidencial', 'category' => 'repair']))->streamedContent();

        $log = AuditLog::where('action', 'exported')->sole();
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('movements', $log->new_values['module']);
        $this->assertSame(['title', 'client'], $log->new_values['fields']);
        $this->assertSame('[omitido]', $log->new_values['filters']['search']);
        $this->assertSame('repair', $log->new_values['filters']['category']);
        $this->assertSame('2026', $log->new_values['filters']['year']); // ano efetivo (omisso)
        $this->assertSame(1, $log->new_values['rows']);
        $dump = json_encode($log->getAttributes());
        foreach (['Maria Segredo', 'confidencial', '123456789'] as $secret) {
            $this->assertStringNotContainsString($secret, $dump);
        }
    }

    public function test_no_audit_log_when_validation_fails(): void
    {
        $this->actingAs($this->backofficeUser())->get(route('admin.v2.movements.export', ['fields' => ['nope']]));

        $this->assertSame(0, AuditLog::where('action', 'exported')->count());
    }

    // ── Listagem ────────────────────────────────────────────────────────────

    public function test_listing_shows_export_button_with_active_filters_and_year(): void
    {
        $this->mov('A');

        $this->actingAs($this->backofficeUser())->get(route('admin.v2.movements.index', ['category' => 'repair', 'year' => 'all']))
            ->assertOk()
            ->assertSee('Exportar Excel')
            ->assertSee(route('admin.v2.movements.export'), false)
            ->assertSee('name="category" value="repair"', false)
            ->assertSee('name="year" value="all"', false);
    }

    public function test_listing_filters_and_totals_are_unchanged(): void
    {
        $this->mov('Compra A', '2026-05-10', ['amount' => 100, 'amount_gross' => 100, 'category' => 'repair']);
        $this->mov('Venda B', '2026-05-11', ['movement_type' => 'income', 'category' => 'vehicle_sale', 'amount' => 1000, 'amount_gross' => 1000]);
        $this->mov('Antiga', '2025-05-11', ['amount' => 7, 'amount_gross' => 7]);
        $user = $this->backofficeUser();

        // sem filtros: ano corrente
        $r = $this->actingAs($user)->get(route('admin.v2.movements.index'))->assertOk();
        $this->assertSame(['Venda B', 'Compra A'], $r->viewData('movements')->pluck('title')->all());
        $this->assertSame('100,00€', $r->viewData('stats')[0]['value']);   // saídas
        $this->assertSame('1.000,00€', $r->viewData('stats')[1]['value']); // entradas
        $this->assertSame(15757.28 + 1000 - 100, $r->viewData('currentSaldo'));

        // year=all inclui 2025 nos totais e na lista
        $r = $this->actingAs($user)->get(route('admin.v2.movements.index', ['year' => 'all']))->assertOk();
        $this->assertCount(3, $r->viewData('movements'));
        $this->assertSame('107,00€', $r->viewData('stats')[0]['value']);

        // com filtros
        $r = $this->actingAs($user)->get(route('admin.v2.movements.index', ['movement_type' => 'income', 'year' => 'all']))->assertOk();
        $this->assertSame(['Venda B'], $r->viewData('movements')->pluck('title')->all());
        $r = $this->actingAs($user)->get(route('admin.v2.movements.index', ['search' => 'Antiga', 'date_from' => '2025-01-01', 'date_to' => '2025-12-31']))->assertOk();
        $this->assertSame(['Antiga'], $r->viewData('movements')->pluck('title')->all());
        // totais ignoram os filtros além do ano: 100 de saídas em 2026 mesmo com filtro de categoria
        $r = $this->actingAs($user)->get(route('admin.v2.movements.index', ['category' => 'vehicle_sale']))->assertOk();
        $this->assertSame('100,00€', $r->viewData('stats')[0]['value']);
    }

    public function test_listing_and_export_return_the_same_movements(): void
    {
        foreach (['2026-05-10', '2026-05-11', '2025-05-12'] as $i => $date) {
            $this->mov("M{$i}", $date, ['status' => $i === 1 ? 'pending' : 'paid']);
        }
        $user = $this->backofficeUser();

        foreach ([[], ['year' => 'all'], ['status' => 'paid', 'year' => 'all'], ['date_from' => '2025-01-01', 'date_to' => '2025-12-31']] as $query) {
            $list = $this->actingAs($user)->get(route('admin.v2.movements.index', $query))->viewData('movements')->pluck('title')->all();
            $export = $this->titles($this->httpRows($this->actingAs($user)->get(route('admin.v2.movements.export', $query + ['fields' => ['title']]))));
            $this->assertSame($list, $export);
        }
    }

    public function test_empty_listing_message_depends_on_filters(): void
    {
        $user = $this->backofficeUser();

        $this->actingAs($user)->get(route('admin.v2.movements.index', ['year' => 'all']))->assertOk()
            ->assertSee('Ainda não existem movimentos registados.');

        $this->mov('Antiga', '2024-01-01');
        $this->actingAs($user)->get(route('admin.v2.movements.index'))->assertOk()
            ->assertSee('Sem resultados para os filtros aplicados')
            ->assertDontSee('Ainda não existem movimentos registados.');
        $this->actingAs($user)->get(route('admin.v2.movements.index', ['year' => 'all', 'search' => 'zzz']))->assertOk()
            ->assertSee('Sem resultados para os filtros aplicados');
    }
}
