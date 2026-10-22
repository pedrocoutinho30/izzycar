<?php

namespace Tests\Feature;

use App\Exports\VehicleExport;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Legalization;
use App\Models\Seller;
use App\Models\Supplier;
use App\Models\V3Vehicle;
use App\Models\V3VehicleDocument;
use App\Models\V3VehiclePhoto;
use App\Services\ExportService;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Cell\EmptyCell;
use OpenSpout\Reader\XLSX\Reader;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class VehicleExportTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-20 10:00:00');
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function vehicle(array $attrs = []): V3Vehicle
    {
        $this->seq++;

        $vehicle = V3Vehicle::create($attrs + [
            'reference' => 'V3-2026-' . str_pad((string) $this->seq, 3, '0', STR_PAD_LEFT),
            'brand' => 'BMW',
            'model' => 'i4',
            'status' => 'em_stock',
        ]);
        // created_at distinto por viatura: a listagem não desempata, e o teste não pode depender disso
        DB::table('v3_vehicles')->where('id', $vehicle->id)->update(['created_at' => Carbon::now()->addSeconds($this->seq)->toDateTimeString()]);

        return $vehicle->refresh();
    }

    private function legalization(V3Vehicle $v, array $steps): Legalization
    {
        return Legalization::create(['v3_vehicle_id' => $v->id, 'marca' => 'BMW', 'modelo' => 'i4', 'combustivel' => 'Gasolina', 'matricula' => 'AA-00-AA', 'steps_completed' => $steps]);
    }

    private function rows(?array $keys, array $filters = []): array
    {
        $definition = new VehicleExport();
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

    private function values(array $row): array
    {
        return array_map(fn ($c) => $c->getValue(), $row);
    }

    /** Valores da 1.ª coluna das linhas de dados. */
    private function firsts(array $rows): array
    {
        return array_map(fn ($r) => $r[0]->getValue(), array_slice($rows, 1));
    }

    private function export(array $query, $user = null): array
    {
        return $this->httpRows($this->actingAs($user ?? $this->backofficeUser())->get(route('admin.v3.vehicles.export', $query)));
    }

    public function test_route_returns_xlsx_with_dated_filename(): void
    {
        $this->vehicle();

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.v3.vehicles.export'))->assertOk();

        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertMatchesRegularExpression('/filename=viaturas_\d{8}_\d{6}\.xlsx$/', $response->headers->get('Content-Disposition'));
        $this->assertCount(2, $this->httpRows($response));
    }

    public function test_guests_cannot_export(): void
    {
        $this->get(route('admin.v3.vehicles.export'))->assertRedirect();
    }

    public function test_export_route_is_not_swallowed_by_id_routes(): void
    {
        $this->assertSame('/gestao/v3/vehicles/export', route('admin.v3.vehicles.export', [], false));
        $this->assertSame('admin.v3.vehicles.export', app('router')->getRoutes()->match(\Illuminate\Http\Request::create('/gestao/v3/vehicles/export'))->getName());
    }

    public function test_export_requires_the_view_action_of_vehicles(): void
    {
        $rule = app(\App\Permissions\PermissionRegistry::class)->ruleForRoute('admin.v3.vehicles.export', 'GET');

        $this->assertSame('vehicles', $rule->resource);
        $this->assertSame('view', $rule->action);
    }

    public function test_invalid_fields_are_rejected(): void
    {
        $user = $this->backofficeUser();
        $get = fn (array $q) => $this->actingAs($user)->get(route('admin.v3.vehicles.export', $q));

        $get(['fields' => ['brand', 'purchase_price']])->assertSessionHasErrors('fields.1');
        $get(['fields' => ['notes']])->assertSessionHasErrors('fields.0');
        $get(['fields' => ['ad_text']])->assertSessionHasErrors('fields.0');
        $get(['fields' => ['total_cost']])->assertSessionHasErrors('fields.0');
        $get(['fields' => ['brand', 'brand']])->assertSessionHasErrors('fields.1');
    }

    public function test_catalog_has_no_cost_margin_purchase_or_sale_fields(): void
    {
        $fields = (new VehicleExport())->fields();
        $blacklist = '/cost|custo|margem|margin|purchase|compra|sale|venda|expense|despesa|movement|movimento|lucro|profit|nif|notes|notas|ad_text|home_featured|path|inspection|_id$/i';

        foreach ($fields as $field) {
            $this->assertDoesNotMatchRegularExpression($blacklist, $field->key, "chave {$field->key}");
            // Única exceção legítima: data de disponibilidade, não um valor de venda
            if ($field->key !== 'available_to_sell_date') {
                $this->assertDoesNotMatchRegularExpression($blacklist, $field->label, "rótulo {$field->label}");
            }
        }
        $this->assertCount(27, $fields);
        $this->assertContains('asking_price', (new VehicleExport())->keys());
    }

    public function test_default_columns_and_header(): void
    {
        $this->vehicle();

        $this->assertSame(
            ['Referência', 'Marca', 'Modelo', 'Versão', 'Ano', 'Matrícula', 'Combustível', 'Quilómetros', 'Estado', 'Preço de anúncio', 'Cliente (dono)'],
            $this->values($this->rows(null)[0]),
        );
    }

    public function test_text_identifiers_keep_leading_zeros_and_formulas_stay_text(): void
    {
        $this->vehicle(['registration' => '00-AA-11', 'vin' => '000123456789ABCDE', 'version' => '=1+1', 'color' => '+SOMA(A1)']);

        $rows = $this->rows(['registration', 'vin', 'version', 'color']);

        $this->assertSame(['00-AA-11', '000123456789ABCDE', '=1+1', '+SOMA(A1)'], $this->values($rows[1]));

        // O leitor converte "=..." em fórmula; o que conta é o XML escrito (texto em linha, sem <f>).
        $definition = new VehicleExport();
        $service = new ExportService();
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $service->write($definition, $service->resolveFields($definition, ['registration', 'vin', 'version']), [], $path);
        $zip = new \ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        unlink($path);
        $this->assertStringNotContainsString('<f>', $xml);
        $this->assertStringContainsString('=1+1', $xml);
        $this->assertStringContainsString('000123456789ABCDE', $xml);
    }

    public function test_empty_is_not_zero_for_kilometers_and_price(): void
    {
        $this->vehicle(['kilometers' => null, 'asking_price' => null]);
        $this->vehicle(['kilometers' => 0, 'asking_price' => 0]);
        $this->vehicle(['kilometers' => 85000, 'asking_price' => 23990.5]);

        $rows = $this->rows(['reference', 'kilometers', 'asking_price']);

        // ordem: mais recente primeiro
        $this->assertSame([85000, 23990.5], [$rows[1][1]->getValue(), (float) $rows[1][2]->getValue()]);
        $this->assertNotInstanceOf(EmptyCell::class, $rows[2][1]);
        $this->assertEquals(0, $rows[2][1]->getValue());
        $this->assertNotInstanceOf(EmptyCell::class, $rows[2][2]);
        $this->assertEquals(0, $rows[2][2]->getValue());
        $this->assertInstanceOf(EmptyCell::class, $rows[3][1]);
        $this->assertInstanceOf(EmptyCell::class, $rows[3][2]);
    }

    public function test_dates_are_native_booleans_are_sim_nao_and_year_label(): void
    {
        $this->vehicle([
            'register_date' => '2020-03-15', 'manufacture_date' => '2019-12-01', 'available_to_sell_date' => '2026-09-30',
            'show_online' => true, 'is_imported' => false, 'year' => 2020, 'month' => 3, 'day' => 15,
        ]);
        $this->vehicle(['year' => 2021, 'month' => 6]);
        $this->vehicle(['year' => 2022]);
        $this->vehicle([]);

        $rows = $this->rows(['year_label', 'register_date', 'manufacture_date', 'available_to_sell_date', 'show_online', 'is_imported', 'created_at']);

        $this->assertInstanceOf(EmptyCell::class, $rows[1][0]);          // sem ano: vazio, não "—"
        $this->assertSame('2022', $rows[2][0]->getValue());
        $this->assertSame('06/2021', $rows[3][0]->getValue());
        $this->assertSame('15/03/2020', $rows[4][0]->getValue());
        foreach ([1, 2, 3] as $i) {
            $this->assertInstanceOf(DateTimeInterface::class, $rows[4][$i]->getValue());
        }
        $this->assertSame('2020-03-15', $rows[4][1]->getValue()->format('Y-m-d'));
        $this->assertSame(['Sim', 'Não'], [$rows[4][4]->getValue(), $rows[4][5]->getValue()]);
        $this->assertInstanceOf(DateTimeInterface::class, $rows[4][6]->getValue());
    }

    public function test_unknown_status_is_exported_as_is(): void
    {
        $this->vehicle(['status' => 'em_transito']);
        $this->vehicle(['status' => 'reservado']);

        $this->assertSame(['Reservado', 'em_transito'], $this->firsts($this->rows(['status'])));
    }

    public function test_relations_and_counts_are_exported(): void
    {
        $client = Client::create(['name' => 'Cliente Dono', 'is_lead' => false]);
        $supplier = Supplier::create(['type' => 'Empresa', 'contact_name' => 'X', 'email' => 'f@example.test', 'company_name' => 'Stand Fornecedor']);
        $seller = Seller::create(['name' => 'Vendedor Alemão']);
        $v = $this->vehicle(['client_id' => $client->id, 'supplier_id' => $supplier->id, 'seller_id' => $seller->id]);
        foreach ([1, 2, 3] as $p) {
            V3VehiclePhoto::create(['v3_vehicle_id' => $v->id, 'path' => "p{$p}.jpg", 'order_position' => $p]);
        }
        V3VehicleDocument::create(['v3_vehicle_id' => $v->id, 'caminho' => 'd.pdf', 'nome_original' => 'd.pdf']);
        $this->vehicle(); // sem ligações

        $rows = $this->rows(['client', 'supplier', 'seller', 'photos_count', 'documents_count']);

        $this->assertInstanceOf(EmptyCell::class, $rows[1][0]);
        $this->assertSame(0, $rows[1][3]->getValue());                 // sem fotos: 0 (contagem real)
        $this->assertSame(['Cliente Dono', 'Stand Fornecedor', 'Vendedor Alemão', 3, 1], $this->values($rows[2]));
        // sem caminhos de ficheiros no output
        $this->assertStringNotContainsString('p1.jpg', json_encode($this->rows(array_map(fn ($f) => $f->key, (new VehicleExport())->fields()))));
    }

    public function test_legalization_step_and_multiple_legalizations(): void
    {
        $none = $this->vehicle();
        $partial = $this->vehicle();
        $done = $this->vehicle();
        $multi = $this->vehicle();
        $this->legalization($partial, [1, 2]);
        $this->legalization($done, array_keys(Legalization::PASSOS));
        // várias: usa-se a mais antiga (menor id), mesmo que a mais recente esteja noutro passo
        $this->legalization($multi, []);
        $this->legalization($multi, array_keys(Legalization::PASSOS));

        $rows = $this->rows(['reference', 'legalization_step']);
        $byRef = [];
        foreach (array_slice($rows, 1) as $r) {
            $byRef[$r[0]->getValue()] = $r[1];
        }

        $this->assertInstanceOf(EmptyCell::class, $byRef[$none->reference]);
        $this->assertSame(Legalization::PASSOS_CURTOS[3], $byRef[$partial->reference]->getValue());
        $this->assertSame('Concluída', $byRef[$done->reference]->getValue());
        $this->assertSame(Legalization::PASSOS_CURTOS[1], $byRef[$multi->reference]->getValue());
    }

    public function test_order_is_the_listing_order_with_id_tiebreak(): void
    {
        $a = $this->vehicle(['brand' => 'A']);
        $b = $this->vehicle(['brand' => 'B']);
        $c = $this->vehicle(['brand' => 'C']);
        $d = $this->vehicle(['brand' => 'D']);
        DB::table('v3_vehicles')->where('id', $a->id)->update(['created_at' => '2026-01-01 10:00:00']);
        DB::table('v3_vehicles')->where('id', $b->id)->update(['created_at' => '2026-05-01 10:00:00']);
        DB::table('v3_vehicles')->where('id', $c->id)->update(['created_at' => '2026-05-01 10:00:00']);
        DB::table('v3_vehicles')->where('id', $d->id)->update(['created_at' => '2026-09-01 10:00:00']);

        $this->assertSame(['D', 'C', 'B', 'A'], $this->firsts($this->rows(['brand'])));
    }

    public function test_filters_apply_to_all_pages(): void
    {
        foreach (range(1, 45) as $i) {
            $this->vehicle(['brand' => 'Audi', 'model' => "A{$i}", 'fuel' => 'Diesel', 'registration' => "AA-{$i}-ZZ"]);
        }
        $this->vehicle(['brand' => 'Tesla', 'model' => 'Model 3', 'fuel' => 'Elétrico', 'status' => 'vendido', 'registration' => '00-TT-00']);
        $user = $this->backofficeUser();

        // 46 linhas + cabeçalho: mais do que a página da listagem (20)
        $this->assertCount(47, $this->export(['fields' => ['brand']], $user));
        $this->assertSame(['Tesla'], $this->firsts($this->export(['fields' => ['brand'], 'fuel' => 'Elétrico'], $user)));
        $this->assertSame(['Tesla'], $this->firsts($this->export(['fields' => ['brand'], 'status' => 'vendido'], $user)));
        $this->assertSame(['Tesla'], $this->firsts($this->export(['fields' => ['brand'], 'search' => '00-TT'], $user)));
        $this->assertSame(['Tesla'], $this->firsts($this->export(['fields' => ['brand'], 'search' => 'model 3'], $user)));
        $this->assertCount(47, $this->export(['fields' => ['brand'], 'search' => 'V3-2026'], $user));
        $this->assertCount(1, $this->export(['fields' => ['brand'], 'status' => 'nao_existe'], $user));
    }

    public function test_empty_query_params_do_not_filter(): void
    {
        $this->vehicle();
        $this->vehicle();

        $this->assertCount(3, $this->export(['fields' => ['brand'], 'search' => '', 'status' => '', 'fuel' => '']));
    }

    public function test_batches_beyond_the_chunk_size_keep_order_and_count(): void
    {
        $rows = [];
        foreach (range(1, 1100) as $i) {
            $rows[] = ['reference' => "R{$i}", 'brand' => 'X', 'status' => 'em_stock', 'created_at' => '2026-05-10 10:00:00', 'updated_at' => '2026-05-10 10:00:00'];
        }
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('v3_vehicles')->insert($chunk);
        }

        $refs = $this->firsts($this->rows(['reference']));

        $this->assertCount(1100, $refs);
        $this->assertCount(1100, array_unique($refs));
        $this->assertSame('R1100', $refs[0]);
    }

    public function test_query_count_is_constant_with_all_fields(): void
    {
        $client = Client::create(['name' => 'C', 'is_lead' => false]);
        $supplier = Supplier::create(['type' => 'Empresa', 'contact_name' => 'X', 'email' => 'f@example.test', 'company_name' => 'S']);
        $seller = Seller::create(['name' => 'V']);
        foreach (range(1, 40) as $i) {
            $v = $this->vehicle(['client_id' => $client->id, 'supplier_id' => $supplier->id, 'seller_id' => $seller->id]);
            $this->legalization($v, [1]);
            V3VehiclePhoto::create(['v3_vehicle_id' => $v->id, 'path' => 'p.jpg', 'order_position' => 1]);
        }

        DB::enableQueryLog();
        $rows = $this->rows(array_map(fn ($f) => $f->key, (new VehicleExport())->fields()));
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertCount(41, $rows);
        $this->assertLessThanOrEqual(6, $queries);
    }

    public function test_only_chosen_relations_are_loaded(): void
    {
        $this->vehicle();

        DB::enableQueryLog();
        $this->rows(['brand', 'asking_price']);
        $sql = implode(' ', array_column(DB::getQueryLog(), 'query'));
        DB::disableQueryLog();

        foreach (['"clients"', 'suppliers', 'sellers', 'legalizations', 'v3_vehicle_photos', 'v3_vehicle_documents'] as $table) {
            $this->assertStringNotContainsString($table, $sql);
        }
    }

    public function test_audit_log_is_written_without_personal_data(): void
    {
        $client = Client::create(['name' => 'Maria Segredo', 'is_lead' => false]);
        $this->vehicle(['registration' => 'SE-CR-ET', 'client_id' => $client->id, 'fuel' => 'Diesel']);
        $user = $this->backofficeUser();

        $this->actingAs($user)->get(route('admin.v3.vehicles.export', ['fields' => ['brand', 'client'], 'search' => 'SE-CR-ET', 'fuel' => 'Diesel']))->streamedContent();

        $log = AuditLog::where('action', 'exported')->sole();
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('vehicles', $log->new_values['module']);
        $this->assertSame(['brand', 'client'], $log->new_values['fields']);
        $this->assertSame('[omitido]', $log->new_values['filters']['search']);
        $this->assertSame('Diesel', $log->new_values['filters']['fuel']);
        $this->assertSame(1, $log->new_values['rows']);
        $dump = json_encode($log->getAttributes());
        foreach (['Maria Segredo', 'SE-CR-ET'] as $secret) {
            $this->assertStringNotContainsString($secret, $dump);
        }
    }

    public function test_no_audit_log_when_validation_fails(): void
    {
        $this->actingAs($this->backofficeUser())->get(route('admin.v3.vehicles.export', ['fields' => ['nope']]));

        $this->assertSame(0, AuditLog::where('action', 'exported')->count());
    }

    public function test_other_modules_still_validate_status_against_movements(): void
    {
        $this->actingAs($this->backofficeUser())->get(route('admin.v2.movements.export', ['status' => 'em_stock']))->assertSessionHasErrors('status');
    }

    // ── Listagem ────────────────────────────────────────────────────────────

    public function test_listing_shows_export_button_with_active_filters(): void
    {
        $this->vehicle(['fuel' => 'Diesel']);

        $this->actingAs($this->backofficeUser())->get(route('admin.v3.vehicles.index', ['fuel' => 'Diesel', 'status' => '']))
            ->assertOk()
            ->assertSee('Exportar Excel')
            ->assertSee(route('admin.v3.vehicles.export'), false)
            ->assertSee('name="fuel" value="Diesel"', false)
            ->assertDontSee('name="status" value=', false);
    }

    public function test_listing_filters_order_and_pagination_are_unchanged(): void
    {
        foreach (range(1, 25) as $i) {
            $this->vehicle(['brand' => 'Audi', 'model' => "A{$i}", 'fuel' => 'Diesel', 'registration' => "AB-{$i}-CD"]);
        }
        $this->vehicle(['brand' => 'Tesla', 'model' => 'Model 3', 'fuel' => 'Elétrico', 'status' => 'vendido']);
        $user = $this->backofficeUser();
        $list = fn (array $q) => $this->actingAs($user)->get(route('admin.v3.vehicles.index', $q))->assertOk()->viewData('vehicles');

        $all = $list([]);
        $this->assertSame(26, $all->total());
        $this->assertCount(20, $all);
        $this->assertSame('Tesla', $all->first()->brand);                  // latest(): mais recente primeiro
        $this->assertCount(6, $list(['page' => 2]));

        // parâmetros vazios não filtram
        $this->assertSame(26, $list(['search' => '', 'status' => '', 'fuel' => ''])->total());

        $this->assertSame(['Tesla'], $list(['status' => 'vendido'])->pluck('brand')->all());
        $this->assertSame(['Tesla'], $list(['fuel' => 'Elétrico'])->pluck('brand')->all());
        $this->assertSame(['Tesla'], $list(['search' => 'model 3'])->pluck('brand')->all());
        $this->assertSame(1, $list(['search' => 'AB-7-CD'])->total());
        $this->assertSame(0, $list(['status' => 'xpto'])->total());
        $this->assertSame(25, $list(['search' => 'Audi', 'fuel' => 'Diesel', 'status' => 'em_stock'])->total());
    }

    public function test_listing_and_export_return_the_same_vehicles(): void
    {
        foreach (range(1, 30) as $i) {
            $this->vehicle(['model' => "M{$i}", 'fuel' => $i % 2 ? 'Diesel' : 'Gasolina', 'status' => $i % 3 ? 'em_stock' : 'reservado']);
        }
        $user = $this->backofficeUser();

        foreach ([[], ['fuel' => 'Diesel'], ['status' => 'reservado'], ['search' => 'M1'], ['status' => 'em_stock', 'fuel' => 'Gasolina']] as $query) {
            $list = collect();
            foreach ([1, 2] as $page) {
                $list = $list->merge($this->actingAs($user)->get(route('admin.v3.vehicles.index', $query + ['page' => $page]))->viewData('vehicles')->pluck('reference'));
            }
            $export = $this->firsts($this->export($query + ['fields' => ['reference']], $user));
            $this->assertSame($list->all(), $export);
        }
    }

    public function test_empty_listing_message_depends_on_filters(): void
    {
        $user = $this->backofficeUser();

        $this->actingAs($user)->get(route('admin.v3.vehicles.index'))->assertOk()
            ->assertSee('Nenhum veículo encontrado.')
            ->assertDontSee('Sem resultados para os filtros aplicados');

        $this->vehicle();
        $this->actingAs($user)->get(route('admin.v3.vehicles.index', ['search' => 'zzz']))->assertOk()
            ->assertSee('Sem resultados para os filtros aplicados')
            ->assertSee('Limpar filtros')
            ->assertDontSee('Nenhum veículo encontrado.');
        // parâmetro vazio não conta como filtro
        $this->actingAs($user)->get(route('admin.v3.vehicles.index', ['status' => '']))->assertOk()
            ->assertDontSee('Sem resultados para os filtros aplicados');
    }
}
