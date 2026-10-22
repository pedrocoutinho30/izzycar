<?php

namespace Tests\Feature;

use App\Exports\LegalizationExport;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Legalization;
use App\Models\LegalizationDocument;
use App\Models\V3Vehicle;
use App\Services\ExportService;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class LegalizationExportTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function leg(string $modelo, ?array $steps = [], ?string $createdAt = null, array $extra = []): Legalization
    {
        $leg = Legalization::create($extra + ['marca' => 'Tesla', 'modelo' => $modelo, 'combustivel' => 'Elétrico', 'steps_completed' => $steps]);
        if ($createdAt) {
            DB::table('legalizations')->where('id', $leg->id)->update(['created_at' => $createdAt]);
        }

        return $leg->fresh();
    }

    /** Linhas (células) do ficheiro gerado pelo serviço. */
    private function rows(?array $keys, array $filters = []): array
    {
        $definition = new LegalizationExport();
        $service = new ExportService();
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $service->write($definition, $service->resolveFields($definition, $keys), $filters, $path);

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

    private function models(array $rows): array
    {
        return array_map(fn ($r) => $r[0]->getValue(), array_slice($rows, 1));
    }

    private function httpRows($response): array
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $response->streamedContent());

        return $this->read($path);
    }

    public function test_route_returns_xlsx_with_dated_filename(): void
    {
        $this->leg('A');

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.legalizations.export'))->assertOk();

        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertMatchesRegularExpression('/filename=legalizacoes_\d{8}_\d{6}\.xlsx$/', $response->headers->get('Content-Disposition'));
        $this->assertCount(2, $this->httpRows($response));
    }

    public function test_guests_cannot_export(): void
    {
        $this->get(route('admin.legalizations.export'))->assertRedirect();
    }

    public function test_export_requires_the_view_action_of_legalizations(): void
    {
        $rule = app(\App\Permissions\PermissionRegistry::class)->ruleForRoute('admin.legalizations.export', 'GET');

        $this->assertSame('legalizations', $rule->resource);
        $this->assertSame('view', $rule->action);
    }

    public function test_invalid_fields_and_filters_are_rejected(): void
    {
        $user = $this->backofficeUser();

        $this->actingAs($user)->get(route('admin.legalizations.export', ['fields' => ['marca', 'token']]))->assertSessionHasErrors('fields.1');
        $this->actingAs($user)->get(route('admin.legalizations.export', ['fields' => ['invoice_path']]))->assertSessionHasErrors('fields.0');
        $this->actingAs($user)->get(route('admin.legalizations.export', ['estado' => 'xpto']))->assertSessionHasErrors('estado');
        $this->actingAs($user)->get(route('admin.legalizations.export', ['date_from' => 'ontem']))->assertSessionHasErrors('date_from');
        $this->actingAs($user)->get(route('admin.legalizations.export', ['date_from' => '2026-05-10', 'date_to' => '2026-05-01']))->assertSessionHasErrors('date_to');
    }

    public function test_catalog_has_no_costs_or_sensitive_internals(): void
    {
        $keys = (new LegalizationExport())->keys();

        foreach (['token', 'invoice_path', 'modelo9_dados', 'modelo1460_dados', 'email_enviado', 'steps_completed', 'vehicle_id', 'v3_vehicle_id'] as $forbidden) {
            $this->assertNotContains($forbidden, $keys);
        }
        foreach ($keys as $key) {
            $this->assertDoesNotMatchRegularExpression('/cost|custo|margem|margin|expense|despesa|total/i', $key);
        }
    }

    public function test_date_and_state_filters_apply_to_the_whole_collection(): void
    {
        foreach (range(1, 30) as $i) {
            $this->leg(sprintf('Antiga %02d', $i), [1, 2], '2026-01-15 10:00:00');
        }
        $this->leg('NovaA', [], '2026-03-10 00:00:00');
        $this->leg('NovaB', null, '2026-03-31 23:59:59');
        $this->leg('Curso', [1, 2, 3], '2026-03-20 12:00:00');
        $this->leg('Feita', [1, 2, 3, 4, 5, 6, 7], '2026-03-25 12:00:00');
        $this->leg('Fora', [], '2026-04-01 00:00:00');

        $this->assertCount(30 + 5, $this->models($this->rows(['modelo'])));

        // intervalo inclusivo nos dois extremos (fim do dia incluído)
        $m = $this->models($this->rows(['modelo'], ['date_from' => '2026-03-10', 'date_to' => '2026-03-31']));
        $this->assertEqualsCanonicalizing(['NovaA', 'NovaB', 'Curso', 'Feita'], $m);

        $emCurso = $this->models($this->rows(['modelo'], ['estado' => 'em_curso']));
        $this->assertCount(31, $emCurso);   // as 30 antigas (2 passos) + Curso
        $this->assertContains('Curso', $emCurso);
        $this->assertEqualsCanonicalizing(['NovaA', 'NovaB', 'Fora'], $this->models($this->rows(['modelo'], ['estado' => 'nova'])));
        $this->assertSame(['Feita'], $this->models($this->rows(['modelo'], ['estado' => 'concluida'])));
        $this->assertEqualsCanonicalizing(['NovaA', 'NovaB'], $this->models($this->rows(['modelo'], ['estado' => 'nova', 'date_from' => '2026-03-01', 'date_to' => '2026-03-31'])));
        $this->assertSame([], $this->models($this->rows(['modelo'], ['date_from' => '2027-01-01'])));
    }

    public function test_step_columns_progress_and_current_step(): void
    {
        $this->leg('Parcial', [1, 3]);
        $this->leg('Feita', [1, 2, 3, 4, 5, 6, 7]);
        $this->leg('Zero', null);

        $rows = $this->rows(['modelo', 'current_step', 'progress', 'steps_done', 'step_1', 'step_2', 'step_3', 'step_4', 'step_5', 'step_6', 'step_7']);
        $this->assertSame(['Modelo', 'Passo atual', 'Progresso', 'Passos concluídos', 'Homologação IMT', 'Inspeção B', 'DAV', 'Pagar ISV', 'Chapas e seguro', 'Modelo 9', 'Registo'], array_map(fn ($c) => $c->getValue(), $rows[0]));

        $by = [];
        foreach (array_slice($rows, 1) as $r) {
            $by[$r[0]->getValue()] = array_map(fn ($c) => $c->getValue(), array_slice($r, 1));
        }

        $this->assertSame(['Inspeção B', 0.29, '2/7', 'Sim', 'Não', 'Sim', 'Não', 'Não', 'Não', 'Não'], $by['Parcial']);
        $this->assertEquals(['Concluída', 1.0, '7/7', 'Sim', 'Sim', 'Sim', 'Sim', 'Sim', 'Sim', 'Sim'], $by['Feita']);
        // zero é zero (não vazio) e a primeira etapa é o passo atual
        $this->assertEquals(['Homologação IMT', 0.0, '0/7', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não', 'Não'], $by['Zero']);
    }

    public function test_progress_is_a_percent_cell_that_excel_shows_correctly(): void
    {
        $this->leg('Feita', [1, 2, 3, 4, 5, 6, 7]);
        $service = new ExportService();
        $definition = new LegalizationExport();
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $service->write($definition, $service->resolveFields($definition, ['progress']), [], $path);
        $zip = new \ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $styles = $zip->getFromName('xl/styles.xml');
        $zip->close();
        unlink($path);

        $this->assertStringContainsString('<v>1</v>', $xml);   // 100 % guardado como 1
        $this->assertStringContainsString('numFmtId="10"', $styles);
    }

    public function test_text_identifiers_dates_and_boolean_cells(): void
    {
        $client = Client::create(['name' => 'Maria', 'is_lead' => false]);
        $this->leg('X', [], '2026-03-05 14:30:00', [
            'client_id' => $client->id, 'matricula' => '00-AB-12', 'num_homologacao' => '0012345', 'num_processo_imt' => '007/2026', 'regime_especial_isv' => true, 'notas' => '=1+1',
        ]);

        $rows = $this->rows(['id', 'created_at', 'client', 'matricula', 'num_homologacao', 'num_processo_imt', 'regime_especial_isv', 'notas', 'v3_vin']);
        $r = $rows[1];

        $this->assertIsInt($r[0]->getValue());
        $this->assertInstanceOf(DateTimeInterface::class, $r[1]->getValue());
        $this->assertSame('2026-03-05 00:00', $r[1]->getValue()->format('Y-m-d H:i'));
        $this->assertSame('Maria', $r[2]->getValue());
        $this->assertSame('00-AB-12', $r[3]->getValue());
        $this->assertSame('0012345', $r[4]->getValue());
        $this->assertSame('007/2026', $r[5]->getValue());
        $this->assertSame('Sim', $r[6]->getValue());
        $this->assertSame('=1+1', $r[7]->getValue());
        $this->assertInstanceOf(\OpenSpout\Common\Entity\Cell\EmptyCell::class, $r[8]);   // sem viatura V3: vazio
    }

    public function test_v3_vehicle_documents_count_and_missing_documents(): void
    {
        $v3 = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Tesla', 'model' => 'S', 'vin' => 'VIN0123456789']);
        $leg = $this->leg('Com V3', [], null, ['v3_vehicle_id' => $v3->id]);
        foreach (['CMATR', 'CCEUR'] as $tipo) {
            LegalizationDocument::create(['legalization_id' => $leg->id, 'tipo' => $tipo, 'nome_original' => 'x.pdf', 'caminho' => 'x']);
        }
        $this->leg('Sem docs', [], null, ['regime_especial_isv' => true]);

        $this->assertSame($v3->id, $leg->v3Vehicle->id);

        $rows = $this->rows(['modelo', 'v3_reference', 'v3_vin', 'documents_count', 'missing_documents']);
        $by = [];
        foreach (array_slice($rows, 1) as $r) {
            $by[$r[0]->getValue()] = $r;
        }

        $this->assertSame($v3->reference, $by['Com V3'][1]->getValue());
        $this->assertSame('VIN0123456789', $by['Com V3'][2]->getValue());
        $this->assertSame(2, $by['Com V3'][3]->getValue());
        $this->assertStringNotContainsString('CMATR', $by['Com V3'][4]->getValue());
        $this->assertStringContainsString('DHTEC', $by['Com V3'][4]->getValue());
        $this->assertStringNotContainsString('MD1460', $by['Com V3'][4]->getValue());   // regime normal

        $this->assertSame(0, $by['Sem docs'][3]->getValue());
        $this->assertStringContainsString('MD1460', $by['Sem docs'][4]->getValue());   // regime especial
    }

    public function test_query_count_does_not_grow_with_rows(): void
    {
        $v3 = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Tesla', 'model' => 'S']);
        $client = Client::create(['name' => 'C', 'is_lead' => false]);
        foreach (range(1, 40) as $i) {
            $this->leg("M{$i}", [1], null, ['client_id' => $client->id, 'v3_vehicle_id' => $v3->id]);
        }

        DB::enableQueryLog();
        $keys = (new LegalizationExport())->keys();
        $rows = $this->rows($keys);
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertCount(41, $rows);
        $this->assertLessThanOrEqual(8, $queries);
    }

    public function test_audit_log_is_written_without_personal_data(): void
    {
        $client = Client::create(['name' => 'Maria Segredo', 'is_lead' => false]);
        $this->leg('A', [], null, ['client_id' => $client->id, 'matricula' => 'ZZ-99-ZZ']);
        $this->leg('B', [1, 2, 3, 4, 5, 6, 7]);
        $user = $this->backofficeUser();

        $this->actingAs($user)->get(route('admin.legalizations.export', ['fields' => ['client', 'matricula'], 'estado' => 'nova']))->streamedContent();

        $log = AuditLog::where('action', 'exported')->sole();
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('legalizations', $log->new_values['module']);
        $this->assertSame(['client', 'matricula'], $log->new_values['fields']);
        $this->assertSame('nova', $log->new_values['filters']['estado']);
        $this->assertSame(1, $log->new_values['rows']);
        $dump = json_encode($log->getAttributes());
        $this->assertStringNotContainsString('Maria Segredo', $dump);
        $this->assertStringNotContainsString('ZZ-99-ZZ', $dump);
    }

    public function test_index_without_filters_lists_everything_with_stats(): void
    {
        $this->leg('A', []);
        $this->leg('B', [1, 2]);
        $this->leg('C', [1, 2, 3, 4, 5, 6, 7]);

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.legalizations.index'))->assertOk()
            ->assertSee('Exportar Excel')->assertSee(route('admin.legalizations.export'), false)->assertSee('Criada desde');

        $this->assertCount(3, $response->viewData('legalizations'));
        $this->assertSame(['total' => 3, 'concluidas' => 1, 'em_curso' => 1, 'novas' => 1], $response->viewData('stats'));
    }

    public function test_index_filters_list_and_stats_follow_dates_but_not_state(): void
    {
        $this->leg('Velha', [1], '2026-01-10 10:00:00');
        $this->leg('NovaMar', [], '2026-03-10 10:00:00');
        $this->leg('FeitaMar', [1, 2, 3, 4, 5, 6, 7], '2026-03-12 10:00:00');
        $user = $this->backofficeUser();

        $response = $this->actingAs($user)->get(route('admin.legalizations.index', ['date_from' => '2026-03-01', 'date_to' => '2026-03-31']))->assertOk();
        $this->assertEqualsCanonicalizing(['NovaMar', 'FeitaMar'], $response->viewData('legalizations')->pluck('modelo')->all());
        $this->assertSame(['total' => 2, 'concluidas' => 1, 'em_curso' => 0, 'novas' => 1], $response->viewData('stats'));

        $response = $this->actingAs($user)->get(route('admin.legalizations.index', ['date_from' => '2026-03-01', 'estado' => 'concluida']))->assertOk();
        $this->assertSame(['FeitaMar'], $response->viewData('legalizations')->pluck('modelo')->all());
        $this->assertSame(['total' => 2, 'concluidas' => 1, 'em_curso' => 0, 'novas' => 1], $response->viewData('stats'));
        // o botão de exportar leva os filtros ativos
        $response->assertSee('name="estado" value="concluida"', false)->assertSee('name="date_from" value="2026-03-01"', false);
    }

    public function test_index_ignores_invalid_filters(): void
    {
        $this->leg('A', []);

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.legalizations.index', ['estado' => 'xpto', 'date_from' => 'lixo']))->assertOk();

        $this->assertCount(1, $response->viewData('legalizations'));
        $this->assertSame([], $response->viewData('filters'));
    }

    public function test_kanban_still_works(): void
    {
        $this->leg('A', [1]);

        $this->actingAs($this->backofficeUser())->get(route('admin.legalizations.kanban'))->assertOk();
    }
}
