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

        foreach (['Nova', 'Em Contacto', 'Fria', 'Perdida'] as $label) {
            $response->assertSee($label);
        }
        $response->assertSee('Ana Nova')->assertSee('912345678')->assertSee('Rui Perdido')->assertSee('rui@example.com');
        // Um <style> aberto dentro de outro faz o navegador ignorar o CSS e empilha as colunas.
        $this->assertStringNotContainsString("<style>\n<style>", $response->getContent());
        $this->assertSame(substr_count($response->getContent(), '<style'), substr_count($response->getContent(), '</style>'));
        $this->assertSame(4, substr_count($response->getContent(), 'class="kanban-col" data-status'));
    }
}
