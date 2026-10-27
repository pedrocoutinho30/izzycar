<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Comportamento do desktop dos pedidos de formulário e das oportunidades que
 * não estava coberto noutros testes. Fixado antes de passar a lógica para o
 * FormProposalService (partilhado com o mobile): não pode mudar.
 */
class FormProposalDesktopCharacterizationTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $this->user = $this->backofficeUser();
        $this->vehicleCatalog(['Volvo' => ['XC40']]);
    }

    private function request(array $attributes = []): FormProposal
    {
        return FormProposal::create($attributes + ['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678']);
    }

    public function test_lista_filtra_por_estado_e_pesquisa(): void
    {
        $this->request(['name' => 'Ana Novo', 'status' => 'novo', 'brand' => 'Volvo']);
        $this->request(['name' => 'Rui Analise', 'status' => 'em_analise', 'email' => 'rui@example.com', 'brand' => 'Fiat']);

        $this->actingAs($this->user)->get(route('admin.v2.form-proposals.index', ['status' => 'em_analise']))
            ->assertOk()->assertSee('Rui Analise')->assertDontSee('Ana Novo');

        $this->actingAs($this->user)->get(route('admin.v2.form-proposals.index', ['search' => 'Volvo']))
            ->assertOk()->assertSee('Ana Novo')->assertDontSee('Rui Analise');

        $this->actingAs($this->user)->get(route('admin.v2.form-proposals.index', ['search' => 'rui@example']))
            ->assertOk()->assertSee('Rui Analise')->assertDontSee('Ana Novo');
    }

    public function test_mudar_estado_volta_atras_com_mensagem(): void
    {
        $request = $this->request(['status' => 'novo']);

        $this->actingAs($this->user)
            ->from(route('admin.v2.form-proposals.show', $request->id))
            ->patch(route('admin.v2.form-proposals.update-status', $request->id), ['status' => 'arquivado'])
            ->assertRedirect(route('admin.v2.form-proposals.show', $request->id))
            ->assertSessionHas('success', 'Estado atualizado!');

        $this->assertSame('arquivado', $request->fresh()->status);

        $this->actingAs($this->user)
            ->patch(route('admin.v2.form-proposals.update-status', $request->id), ['status' => 'convertido'])
            ->assertSessionHasErrors(['status' => '"Convertido" é automático — fica assim quando o cliente aceita uma cotação do pedido.']);
    }

    public function test_criar_pedido_manual_mensagem_e_dados_do_cliente(): void
    {
        $client = Client::create(['name' => 'Maria', 'email' => 'maria@example.com', 'phone' => '913000000', 'is_lead' => true, 'lead_status' => 'nova', 'angariador_code' => 'ANG1']);

        $this->actingAs($this->user)
            ->post(route('admin.v2.form-proposals.store'), ['client_id' => $client->id, 'brand' => 'Volvo', 'model' => 'XC40', 'km_max' => 50000])
            ->assertSessionHas('success', 'Pedido criado. Já pode adicionar oportunidades.');

        $request = FormProposal::sole();
        $this->assertSame('ANG1', $request->angariador_code);
        $this->assertSame('913000000', $request->phone);
        $this->assertSame(50000, (int) $request->km_max);
        $this->assertDatabaseHas('lead_activities', [
            'client_id' => $client->id,
            'title' => 'Pedido de importação criado',
            'body' => 'Pedido "Volvo XC40" criado manualmente no backoffice.',
            'icon' => 'bi-envelope-plus',
            'color' => 'primary',
            'user_id' => null,
        ]);

        $this->actingAs($this->user)
            ->post(route('admin.v2.form-proposals.store'), ['client_id' => $client->id, 'brand' => 'Volvo', 'model' => 'Qualquer'])
            ->assertSessionHasErrors(['model' => 'Escolha um modelo da lista para a marca escolhida.']);
        $this->actingAs($this->user)
            ->post(route('admin.v2.form-proposals.store'), ['client_id' => $client->id, 'km_max' => 1.5])
            ->assertSessionHasErrors(['km_max' => 'Os quilómetros têm de ser um número inteiro.']);
    }

    public function test_editar_pedido_mantem_valor_antigo_fora_do_catalogo(): void
    {
        $request = $this->request(['brand' => 'Lynk & Co', 'model' => '01']);

        $this->actingAs($this->user)
            ->put(route('admin.v2.form-proposals.update', $request->id), ['brand' => 'Lynk & Co', 'model' => '01', 'budget' => 30000])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Pedido atualizado.');

        $this->assertSame(30000, (int) $request->fresh()->budget);
    }

    public function test_mensagens_de_validacao_das_oportunidades(): void
    {
        $request = $this->request();
        $opportunity = $request->opportunities()->create(['brand' => 'Volvo', 'model' => 'XC40']);
        $route = fn (string $name) => route("admin.v2.form-proposals.opportunities.{$name}", [$request->id, $opportunity->id]);

        $this->actingAs($this->user)->patchJson($route('update-status'), [])
            ->assertUnprocessable()->assertJsonValidationErrors(['status' => 'Escolha um estado.']);
        $this->actingAs($this->user)->patchJson($route('update-status'), ['status' => 'xpto'])
            ->assertJsonValidationErrors(['status' => 'Estado inválido.']);
        $this->actingAs($this->user)->patchJson($route('update-notes'), ['notes' => str_repeat('a', 20001)])
            ->assertJsonValidationErrors(['notes' => 'As notas são demasiado longas.']);
        $this->actingAs($this->user)->patchJson($route('update-checklist'), ['item_id' => 1, 'status' => 'xpto'])
            ->assertJsonValidationErrors(['status' => 'Estado inválido.']);

        $this->actingAs($this->user)->patchJson($route('update-notes'), ['notes' => null])->assertOk();
        $this->assertNull(ImportOpportunity::find($opportunity->id)->notes);
    }
}
