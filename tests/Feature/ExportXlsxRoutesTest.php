<?php

namespace Tests\Feature;

use App\Models\Client;
use OpenSpout\Reader\XLSX\Reader;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ExportXlsxRoutesTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private function names($response): array
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $response->streamedContent());

        $reader = new Reader();
        $reader->open($path);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
        }
        $reader->close();
        unlink($path);

        return array_map(fn ($r) => $r[0], array_slice($rows, 1));
    }

    public function test_leads_export_is_xlsx_with_dated_filename(): void
    {
        Client::create(['name' => 'Lead A', 'is_lead' => true]);

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.v2.leads.export'))->assertOk();

        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertMatchesRegularExpression('/^attachment; filename=leads_\d{8}_\d{6}\.xlsx$/', $response->headers->get('Content-Disposition'));
        $this->assertSame(['Lead A'], $this->names($response));
    }

    public function test_clients_export_filename_and_content_type(): void
    {
        Client::create(['name' => 'Cli A', 'is_lead' => false]);

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.v2.clients.export'))->assertOk();

        $this->assertStringContainsString('clientes_', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertSame(['Cli A'], $this->names($response));
    }

    public function test_leads_and_clients_do_not_mix(): void
    {
        Client::create(['name' => 'Lead A', 'is_lead' => true]);
        Client::create(['name' => 'Cliente A', 'is_lead' => false]);
        $user = $this->backofficeUser();

        $this->assertSame(['Lead A'], $this->names($this->actingAs($user)->get(route('admin.v2.leads.export'))));
        $this->assertSame(['Cliente A'], $this->names($this->actingAs($user)->get(route('admin.v2.clients.export'))));
    }

    public function test_filters_apply_to_all_results_not_only_one_page(): void
    {
        foreach (range(1, 30) as $i) {
            Client::create(['name' => sprintf('Lead %02d', $i), 'is_lead' => true, 'lead_status' => 'fria', 'lead_source' => 'retoma']);
        }
        Client::create(['name' => 'Outra', 'is_lead' => true, 'lead_status' => 'nova', 'lead_source' => 'manual']);

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.v2.leads.export', ['lead_status' => 'fria', 'lead_source' => 'retoma']));
        $this->assertCount(30, $this->names($response));

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.v2.leads.export', ['search' => 'Outra']));
        $this->assertSame(['Outra'], $this->names($response));
    }

    public function test_clients_filter_by_type_ignores_case(): void
    {
        Client::create(['name' => 'Emp', 'is_lead' => false, 'client_type' => 'empresa']);
        Client::create(['name' => 'Par', 'is_lead' => false, 'client_type' => 'Particular']);

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.v2.clients.export', ['client_type' => 'Empresa']));
        $this->assertSame(['Emp'], $this->names($response));
    }

    public function test_requested_fields_define_the_columns(): void
    {
        Client::create(['name' => 'Lead A', 'email' => 'a@example.com', 'is_lead' => true]);

        $response = $this->actingAs($this->backofficeUser())
            ->get(route('admin.v2.leads.export', ['fields' => ['email', 'name']]))->assertOk();

        $this->assertSame(['a@example.com'], $this->names($response));
    }

    public function test_unknown_or_duplicate_fields_are_rejected(): void
    {
        $user = $this->backofficeUser();

        $this->actingAs($user)->get(route('admin.v2.leads.export', ['fields' => ['name', 'password']]))
            ->assertSessionHasErrors('fields.1');
        $this->actingAs($user)->get(route('admin.v2.clients.export', ['fields' => ['name', 'name']]))
            ->assertSessionHasErrors();
        // um campo de clientes que não existe em leads
        $this->actingAs($user)->get(route('admin.v2.leads.export', ['fields' => ['vat_number']]))
            ->assertSessionHasErrors('fields.0');
    }

    public function test_guests_cannot_export(): void
    {
        $this->get(route('admin.v2.leads.export'))->assertRedirect();
        $this->get(route('admin.v2.clients.export'))->assertRedirect();
    }

    public function test_export_routes_require_the_view_action(): void
    {
        $registry = app(\App\Permissions\PermissionRegistry::class);

        foreach (['admin.v2.leads.export' => 'leads', 'admin.v2.clients.export' => 'clients'] as $route => $resource) {
            $rule = $registry->ruleForRoute($route, 'GET');
            $this->assertSame($resource, $rule->resource);
            $this->assertSame('view', $rule->action);
        }
    }

    public function test_list_pages_show_the_export_button(): void
    {
        \Illuminate\Support\Facades\Cache::forever('frontend_menus', collect());
        \Illuminate\Support\Facades\Cache::forever('site_logo', '');
        $user = $this->backofficeUser();

        $this->actingAs($user)->get(route('admin.v2.leads.index', ['lead_status' => 'fria']))
            ->assertOk()->assertSee('Exportar Excel')->assertSee(route('admin.v2.leads.export'), false)
            ->assertSee('name="lead_status" value="fria"', false);
        $this->actingAs($user)->get(route('admin.v2.clients.index'))
            ->assertOk()->assertSee('Exportar Excel')->assertSee('Consentimento newsletter');
    }
}
