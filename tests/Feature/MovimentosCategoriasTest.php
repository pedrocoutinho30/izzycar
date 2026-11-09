<?php

namespace Tests\Feature;

use App\Models\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Lista de categorias de movimentos: agrupada, com os passos de uma importação. */
class MovimentosCategoriasTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-20 10:00:00');
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        DB::connection()->getPdo()->sqliteCreateFunction('YEAR', fn ($d) => $d ? (int) substr($d, 0, 4) : null);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_listas_por_tipo(): void
    {
        $despesas = Expense::categoriesFor('expense');
        $receitas = Expense::categoriesFor('income');

        foreach (['import_inspection_pre' => 'Inspeção pré-compra', 'vehicle_purchase' => 'Compra do carro', 'transport' => 'Transporte',
                  'import_isv' => 'ISV', 'import_ipo' => 'IPO', 'vehicle_washing' => 'Lavagem', 'import_plates' => 'Matrícula',
                  'import_imt' => 'IMT', 'import_registration' => 'Registo automóvel', 'maintenance_scheduled' => 'Manutenção programada',
                  'repair' => 'Manutenção/Reparação', 'fees' => 'Honorários', 'marketing' => 'Marketing', 'other' => 'Outros',
                  'insurance' => 'Seguro', 'fuel' => 'Combustível', 'salary' => 'Salário', 'rent' => 'Renda', 'commission' => 'Comissão'] as $chave => $nome) {
            $this->assertSame($nome, $despesas[$chave] ?? null, "despesa {$chave}");
        }
        $this->assertArrayHasKey('general_expenses', $despesas);

        $this->assertSame(['import_service', 'legalization', 'fees', 'vehicle_sale', 'commission', 'other'], array_keys($receitas));
        $this->assertSame('Serviço de legalização', $receitas['legalization']);
        $this->assertArrayNotHasKey('vehicle_purchase', $receitas);
        $this->assertArrayNotHasKey('import_service', $despesas);
    }

    public function test_antigas_continuam_conhecidas_e_com_nome_certo_por_tipo(): void
    {
        foreach (['legalization', 'tax', 'inspection', 'office', 'import_service', 'vehicle_sale'] as $chave) {
            $this->assertArrayHasKey($chave, Expense::categories());
        }
        $this->assertFalse(Expense::categoryAllowed('tax', 'expense'));
        $this->assertSame('Legalização', Expense::categoryLabelFor('legalization', 'expense'));
        $this->assertSame('Serviço de legalização', Expense::categoryLabelFor('legalization', 'income'));
        $this->assertSame('Imposto', Expense::categoryLabelFor('tax', 'expense'));
    }

    public function test_formularios_filtram_pelo_tipo_e_o_filtro_lista_tudo(): void
    {
        $user = $this->backofficeUser();

        $this->actingAs($user)->get(route('admin.v2.movements.create'))->assertOk()
            ->assertSee('<optgroup label="Passos da importação" data-type="expense">', false)
            ->assertSee('<optgroup label="Serviços" data-type="income">', false);

        $this->actingAs($user)->get(route('admin.v2.movements.index'))->assertOk()
            ->assertSee('Despesas · Passos da importação')
            ->assertSee('value="import_inspection_pre"', false);
    }

    public function test_servidor_recusa_categoria_de_outro_tipo(): void
    {
        $user = $this->backofficeUser();
        $base = ['title' => 'X', 'amount' => '10', 'expense_date' => '2026-10-10', 'status' => 'paid'];

        $this->actingAs($user)->post(route('admin.v2.movements.store'), array_merge($base, ['movement_type' => 'income', 'category' => 'import_ipo']))
            ->assertSessionHasErrors('category');
        $this->actingAs($user)->post(route('admin.v2.movements.store'), array_merge($base, ['movement_type' => 'expense', 'category' => 'legalization']))
            ->assertSessionHasErrors('category');
        $this->assertSame(0, Expense::count());

        $this->actingAs($user)->post(route('admin.v2.movements.store'), array_merge($base, ['title' => 'Inspeção BMW', 'amount' => '150', 'movement_type' => 'expense', 'category' => 'import_inspection_pre']))
            ->assertRedirect(route('admin.v2.movements.index'));
        $this->actingAs($user)->post(route('admin.v2.movements.store'), array_merge($base, ['title' => 'Honorários', 'movement_type' => 'income', 'category' => 'fees']))
            ->assertRedirect(route('admin.v2.movements.index'));

        $this->assertSame('Inspeção pré-compra', Expense::where('title', 'Inspeção BMW')->firstOrFail()->category_label);
        $this->actingAs($user)->get(route('admin.v2.movements.index', ['category' => 'import_inspection_pre']))->assertOk()->assertSee('Inspeção BMW');
    }

    public function test_movimento_antigo_mantem_a_categoria_ao_editar(): void
    {
        $user = $this->backofficeUser();
        $antigo = Expense::create([
            'movement_type' => 'expense', 'category' => 'tax', 'title' => 'Imposto antigo', 'amount' => 20, 'amount_gross' => 20,
            'expense_date' => '2026-03-01', 'status' => 'paid',
        ]);
        $dados = ['movement_type' => 'expense', 'title' => 'Imposto antigo', 'amount' => '25', 'expense_date' => '2026-03-01', 'status' => 'paid'];

        // Edita sem mexer na categoria: aceita.
        $this->actingAs($user)->put(route('admin.v2.movements.update', $antigo->id), array_merge($dados, ['category' => 'tax']))
            ->assertSessionHasNoErrors();
        $this->assertSame('tax', $antigo->fresh()->category);

        // Mudar para uma categoria que não é do tipo: recusa. Para uma válida: aceita.
        $this->actingAs($user)->put(route('admin.v2.movements.update', $antigo->id), array_merge($dados, ['category' => 'import_ipo', 'movement_type' => 'income']))
            ->assertSessionHasErrors('category');
        $this->actingAs($user)->put(route('admin.v2.movements.update', $antigo->id), array_merge($dados, ['category' => 'import_isv']))
            ->assertSessionHasNoErrors();
        $this->assertSame('import_isv', $antigo->fresh()->category);
    }

    public function test_formulario_tem_legalizacao_sempre_visivel_e_valores_no_cartao_de_pagamento(): void
    {
        $html = $this->actingAs($this->backofficeUser())->get(route('admin.v2.movements.create'))->assertOk()->getContent();

        // Legalização como veículo e cliente: select próprio, sem estar escondido por categoria.
        $this->assertStringContainsString('name="legalization_id" id="legalization_id"', $html);
        $this->assertStringNotContainsString('legalization_field', $html);
        $this->assertStringContainsString('name="v3_vehicle_id" id="v3_vehicle_id"', $html); // Tom Select precisa dos ids
        $this->assertStringContainsString('name="client_id" id="client_id"', $html);
        $this->assertStringContainsString('name="partner_id" id="partner_id"', $html);

        // Cartão Pagamento: método, estado, montante bruto, taxa de IVA e resumo, por esta ordem.
        $pagamento = substr($html, strpos($html, 'bi-credit-card'));
        $ordem = ['name="payment_method"', 'name="status"', 'name="amount"', 'name="vat_rate"', 'id="preview_gross"'];
        $ultimo = -1;
        foreach ($ordem as $campo) {
            $pos = strpos($pagamento, $campo);
            $this->assertNotFalse($pos, $campo);
            $this->assertGreaterThan($ultimo, $pos, "ordem: {$campo}");
            $ultimo = $pos;
        }
        $this->assertStringNotContainsString('bi-currency-euro me-2', $html); // o cartão "Valores" desapareceu
    }

    public function test_movimento_guarda_e_edita_a_legalizacao_com_qualquer_categoria(): void
    {
        $user = $this->backofficeUser();
        $leg = \App\Models\Legalization::create(['marca' => 'BMW', 'modelo' => 'i4', 'matricula' => 'AA-00-AA']);
        $dados = ['movement_type' => 'expense', 'category' => 'import_ipo', 'title' => 'IPO do i4', 'amount' => '60',
                  'expense_date' => '2026-10-10', 'status' => 'paid', 'legalization_id' => $leg->id];

        $this->actingAs($user)->post(route('admin.v2.movements.store'), $dados)->assertSessionHasNoErrors();
        $mov = Expense::where('title', 'IPO do i4')->firstOrFail();
        $this->assertSame($leg->id, (int) $mov->legalization_id);

        $this->actingAs($user)->get(route('admin.v2.movements.edit', $mov->id))->assertOk()
            ->assertSee('#' . $leg->id . ' — BMW i4 — AA-00-AA', false);

        $this->actingAs($user)->put(route('admin.v2.movements.update', $mov->id), array_merge($dados, ['legalization_id' => '']))->assertSessionHasNoErrors();
        $this->assertNull($mov->fresh()->legalization_id);
    }
}
