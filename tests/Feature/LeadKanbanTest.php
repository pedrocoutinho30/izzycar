<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class LeadKanbanTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_kanban_renders_every_status_column_with_compact_cards(): void
    {
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        Client::create(['name' => 'Ana Nova', 'phone' => '912345678', 'is_lead' => true, 'lead_status' => 'nova']);
        Client::create(['name' => 'Rui Perdido', 'email' => 'rui@example.com', 'is_lead' => true, 'lead_status' => 'perdida']);

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.v2.leads.kanban'))->assertOk();

        foreach (['Nova', 'Em Contacto', 'Qualificada', 'Fria', 'Perdida'] as $label) {
            $response->assertSee($label);
        }
        $response->assertSee('Ana Nova')->assertSee('912345678')->assertSee('Rui Perdido')->assertSee('rui@example.com');
        // Um <style> aberto dentro de outro faz o navegador ignorar o CSS e empilha as colunas.
        $this->assertStringNotContainsString("<style>\n<style>", $response->getContent());
        $this->assertSame(substr_count($response->getContent(), '<style'), substr_count($response->getContent(), '</style>'));
        $this->assertSame(5, substr_count($response->getContent(), 'class="kanban-col" data-status'));
    }

    public function test_admin_can_qualify_a_lead_and_it_stays_active(): void
    {
        $lead = Client::create(['name' => 'Ana', 'is_lead' => true, 'lead_status' => 'em_contacto']);

        $this->actingAs($this->backofficeUser())
            ->postJson(route('admin.v2.leads.status', $lead->id), ['status' => 'qualificada'])
            ->assertOk();

        $this->assertSame('qualificada', $lead->refresh()->lead_status);
        $this->assertDatabaseHas('lead_activities', ['client_id' => $lead->id, 'title' => 'Estado alterado: Em Contacto → Qualificada']);
        $this->assertSame('Qualificada', \App\Enums\LeadStatus::labelOf('qualificada'));
    }

    public function test_angariador_cannot_set_a_lead_as_qualified_but_sees_the_column(): void
    {
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $angariador = $this->backofficeUser('angariador');
        $lead = Client::create(['name' => 'Rui', 'is_lead' => true, 'lead_status' => 'nova', 'owner_id' => $angariador->id]);

        $this->actingAs($angariador)
            ->postJson(route('admin.angariador.leads.status', $lead->id), ['status' => 'qualificada'])
            ->assertStatus(422);
        $this->assertSame('nova', $lead->refresh()->lead_status);

        $this->actingAs($angariador)->get(route('admin.angariador.leads.kanban'))
            ->assertOk()->assertSee('Qualificada');
    }
}
