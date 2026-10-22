<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Client;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ExportXlsxAuditTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_leads_export_writes_one_audit_record(): void
    {
        foreach (range(1, 3) as $i) {
            Client::create(['name' => "Lead {$i}", 'email' => "lead{$i}@example.com", 'is_lead' => true, 'lead_status' => 'fria']);
        }
        Client::create(['name' => 'Outra', 'is_lead' => true, 'lead_status' => 'nova']);
        $user = $this->backofficeUser();

        $this->actingAs($user)
            ->get(route('admin.v2.leads.export', ['fields' => ['email', 'name'], 'lead_status' => 'fria']))
            ->assertOk()->streamedContent();

        $this->assertSame(1, AuditLog::where('action', 'exported')->count());
        $log = AuditLog::where('action', 'exported')->first();
        $this->assertSame('exported', $log->action);
        $this->assertSame($user->id, $log->user_id);
        $this->assertNotNull($log->created_at);
        $this->assertSame('leads', $log->new_values['module']);
        $this->assertSame(['email', 'name'], $log->new_values['fields']);
        $this->assertSame('fria', $log->new_values['filters']['lead_status']);
        $this->assertSame(3, $log->new_values['rows']);
    }

    public function test_clients_export_records_module_and_count(): void
    {
        Client::create(['name' => 'Cli A', 'is_lead' => false]);
        Client::create(['name' => 'Lead', 'is_lead' => true]);

        $this->actingAs($this->backofficeUser())->get(route('admin.v2.clients.export'))->streamedContent();

        $log = AuditLog::where('action', 'exported')->sole();
        $this->assertSame('clients', $log->new_values['module']);
        $this->assertSame(1, $log->new_values['rows']);
        $this->assertNotEmpty($log->new_values['fields']);
    }

    public function test_audit_record_has_no_personal_data(): void
    {
        Client::create(['name' => 'Maria Segredo', 'email' => 'maria.segredo@example.com', 'vat_number' => '123456789', 'is_lead' => false]);

        $this->actingAs($this->backofficeUser())
            ->get(route('admin.v2.clients.export', ['fields' => ['name', 'email', 'vat_number'], 'search' => 'maria.segredo@example.com']))
            ->streamedContent();

        $log = AuditLog::where('action', 'exported')->sole();
        $dump = json_encode($log->getAttributes());
        foreach (['Maria Segredo', 'maria.segredo@example.com', '123456789'] as $secret) {
            $this->assertStringNotContainsString($secret, $dump);
        }
        $this->assertSame(['name', 'email', 'vat_number'], $log->new_values['fields']);
        $this->assertSame('[omitido]', $log->new_values['filters']['search']);
        $this->assertSame(1, $log->new_values['rows']);
    }

    public function test_no_audit_record_when_validation_fails(): void
    {
        $user = $this->backofficeUser();

        $this->actingAs($user)->get(route('admin.v2.leads.export', ['fields' => ['name', 'password']]))
            ->assertSessionHasErrors('fields.1');
        $this->actingAs($user)->get(route('admin.v2.clients.export', ['fields' => ['name', 'name']]))
            ->assertSessionHasErrors();

        $this->assertSame(0, AuditLog::where('action', 'exported')->count());
    }

    public function test_no_audit_record_for_guests(): void
    {
        $this->get(route('admin.v2.leads.export'))->assertRedirect();

        $this->assertSame(0, AuditLog::where('action', 'exported')->count());
    }
}
