<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Legalization;
use App\Models\QuickEntryInterpretation;
use App\Models\User;
use App\QuickEntry\Proposal;
use App\QuickEntry\QuickEntryDispatcher;
use App\Services\QuickEntryService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Inserção rápida: interpretação por regras de uma frase, proposta editável e
 * gravação só após confirmação. Usa apenas a base de dados de teste (sqlite).
 */
class QuickEntryTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private User $admin;

    private Client $fabio;

    private Legalization $fabioLegalization;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-20 23:30:00'); // já é dia 21 em Lisboa (WEST, UTC+1)
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $this->admin = $this->backofficeUser('admin');
        $this->fabio = $this->client('Fábio Oliveira');
        $this->fabioLegalization = $this->legalization($this->fabio, 'BMW', 'i4', '12-AB-34');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function client(string $name, array $extra = []): Client
    {
        return Client::create($extra + ['name' => $name, 'email' => str()->slug($name) . rand(1, 99999) . '@example.com', 'phone' => '91' . rand(1000000, 9999999), 'is_lead' => false]);
    }

    private function legalization(?Client $client, string $marca, string $modelo, ?string $matricula = null): Legalization
    {
        return Legalization::create([
            'client_id' => $client?->id, 'marca' => $marca, 'modelo' => $modelo, 'combustivel' => 'Gasolina',
            'matricula' => $matricula, 'steps_completed' => [],
        ]);
    }

    private function interpret(string $text, ?User $user = null): Proposal
    {
        return app(QuickEntryDispatcher::class)->interpret($text, $user ?? $this->admin);
    }

    private function limitedUser(array $permissions): User
    {
        $role = Role::findOrCreate('qe-' . md5(implode(',', $permissions)), 'web');
        foreach ($permissions as $name) {
            $role->givePermissionTo(Permission::findOrCreate($name, 'web'));
        }

        return $this->backofficeUser($role->name);
    }

    // ── Exemplos obrigatórios ───────────────────────────────────────────────

    public function test_exemplo_a_despesa_com_categoria_outros_e_legalizacao(): void
    {
        $p = $this->interpret('criar movimento despesa categoria outros 10€ legalização fabio oliveira pago em dinheiro');

        $this->assertSame(Proposal::READY, $p->status);
        $this->assertSame('expense', $p->value('movement_type'));
        $this->assertSame('other', $p->value('category'));
        $this->assertSame('10.00', $p->value('amount'));
        $this->assertSame('10,00 €', $p->fields['amount']['label']);
        $this->assertSame($this->fabioLegalization->id, $p->value('legalization_id'));
        $this->assertSame($this->fabio->id, $p->value('client_id'), 'o cliente vem da relação da legalização');
        $this->assertSame('relation', $p->fields['client_id']['source']);
        $this->assertSame('paid', $p->value('status'));
        $this->assertSame('cash', $p->value('payment_method'));
        $this->assertSame([], $p->unresolved);
        $this->assertNull($p->leftover);
    }

    public function test_exemplo_b_receita_com_cliente_sem_legalizacao_implicita(): void
    {
        $p = $this->interpret('Criar movimento receita cliente Fabio oliveira 2000€ pago transferencia bancaria');

        $this->assertSame('income', $p->value('movement_type'));
        $this->assertSame($this->fabio->id, $p->value('client_id'));
        $this->assertSame('Fábio Oliveira', $p->fields['client_id']['label'], 'mostra o nome real, com acentos');
        $this->assertSame('2000.00', $p->value('amount'));
        $this->assertSame('2.000,00 €', $p->fields['amount']['label']);
        $this->assertSame('paid', $p->value('status'));
        $this->assertSame('bank_transfer', $p->value('payment_method'));
        $this->assertNull($p->value('category'), 'categoria não é inferida');
        $this->assertNull($p->value('legalization_id'), 'a legalização não é escolhida só pelo cliente');
        $this->assertSame([], $p->unresolved);
    }

    public function test_exemplo_c_transporte_infere_despesa_e_categoria(): void
    {
        $p = $this->interpret('Criar movimento transporte 922,5€ legalização fabio oliveira pago por transferencia');

        $this->assertSame('expense', $p->value('movement_type'));
        $this->assertSame('inferred', $p->fields['movement_type']['source']);
        $this->assertSame('transport', $p->value('category'));
        $this->assertSame('inferred', $p->fields['category']['source']);
        $this->assertSame('922.50', $p->value('amount'));
        $this->assertSame($this->fabioLegalization->id, $p->value('legalization_id'));
        $this->assertSame('paid', $p->value('status'));
        $this->assertSame('bank_transfer', $p->value('payment_method'));
    }

    // ── Acentos, maiúsculas, valores, nomes ─────────────────────────────────

    public function test_ignora_maiusculas_acentos_e_espacos(): void
    {
        $p = $this->interpret("  CRIAR   MOVIMENTO  RECEITA\tCLIENTE FÁBIO   OLIVEIRA 50 EUR PAGO ");

        $this->assertSame('income', $p->value('movement_type'));
        $this->assertSame($this->fabio->id, $p->value('client_id'));
        $this->assertSame('50.00', $p->value('amount'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('valoresPortugueses')]
    public function test_valores_em_formato_portugues(string $texto, string $esperado): void
    {
        $p = $this->interpret("criar movimento despesa {$texto} pago");

        $this->assertSame($esperado, $p->value('amount'));
    }

    public static function valoresPortugueses(): array
    {
        return [
            '10€' => ['10€', '10.00'],
            '10 €' => ['10 €', '10.00'],
            '10 euros' => ['10 euros', '10.00'],
            '2000€' => ['2000€', '2000.00'],
            '922,5€' => ['922,5€', '922.50'],
            '922,50 €' => ['922,50 €', '922.50'],
            '1.250,50 €' => ['1.250,50 €', '1250.50'],
            '€ 15' => ['€ 15', '15.00'],
            '0,05€' => ['0,05€', '0.05'],
        ];
    }

    public function test_valores_invalidos_ou_ambiguos_sao_sinalizados(): void
    {
        $varios = $this->interpret('criar movimento despesa 10€ e 20€ pago');
        $this->assertNull($varios->value('amount'));
        $this->assertCount(2, $varios->options['amount']);
        $this->assertContains('amount', $varios->unresolved);

        $nulo = $this->interpret('criar movimento despesa 0€ pago');
        $this->assertNull($nulo->value('amount'));

        $semMoeda = $this->interpret('criar movimento despesa 50 pago');
        $this->assertNull($semMoeda->value('amount'));
        $this->assertStringContainsString('sem moeda', collect($semMoeda->warnings)->pluck('message')->implode(' '));

        $invalido = $this->interpret('criar movimento despesa 10,555€ pago');
        $this->assertNull($invalido->value('amount'));
        $this->assertStringContainsString('Formato de valor inválido', collect($invalido->warnings)->pluck('message')->implode(' '));

        $negativo = $this->interpret('criar movimento despesa -10€ pago');
        $this->assertNull($negativo->value('amount'));
    }

    public function test_data_nao_se_confunde_com_valor_nem_numero_em_nome(): void
    {
        $p = $this->interpret('criar movimento despesa 15/10/2026 50€ pago');
        $this->assertSame('2026-10-15', $p->value('expense_date'));
        $this->assertSame('50.00', $p->value('amount'));

        $semValor = $this->interpret('criar movimento despesa 15/10/2026 pago');
        $this->assertNull($semValor->value('amount'));
        $this->assertSame('2026-10-15', $semValor->value('expense_date'));

        $this->client('Rua 25 Lda');
        $nome = $this->interpret('criar movimento despesa cliente Rua 25 Lda 30€ pago');
        $this->assertSame('30.00', $nome->value('amount'));
        $this->assertNotNull($nome->value('client_id'));
    }

    public function test_nomes_com_varias_palavras_terminam_no_proximo_campo(): void
    {
        $maria = $this->client('Maria da Conceição Silva Santos');

        $p = $this->interpret('criar movimento receita cliente maria da conceicao silva santos 300 euros pago em dinheiro');

        $this->assertSame($maria->id, $p->value('client_id'));
        $this->assertSame('300.00', $p->value('amount'));
        $this->assertSame('cash', $p->value('payment_method'));
        $this->assertNull($p->leftover);
    }

    public function test_cliente_no_meio_da_frase_nao_muda_de_modulo(): void
    {
        $p = $this->interpret('criar movimento receita 100€ cliente Fabio Oliveira');

        $this->assertSame('movimento', $p->module);
        $this->assertSame('criar', $p->action);
        $this->assertSame($this->fabio->id, $p->value('client_id'));
    }

    // ── Ambiguidades ────────────────────────────────────────────────────────

    public function test_clientes_homonimos_apresentam_opcoes_sem_escolher(): void
    {
        $outro = $this->client('Fabio Oliveira');

        $p = $this->interpret('criar movimento receita cliente fabio oliveira 100€ pago');

        $this->assertNull($p->value('client_id'));
        $this->assertContains('client_id', $p->unresolved);
        $this->assertEqualsCanonicalizing([$this->fabio->id, $outro->id], array_column($p->options['client_id'], 'id'));
    }

    public function test_cliente_inexistente_pede_selecao_manual_e_nao_cria_cliente(): void
    {
        $antes = Client::count();

        $p = $this->interpret('criar movimento receita cliente Zacarias Inexistente 100€ pago');

        $this->assertNull($p->value('client_id'));
        $this->assertContains('client_id', $p->unresolved);
        $this->assertSame($antes, Client::count());
    }

    public function test_nome_parcial_nao_e_escolhido_por_aproximacao(): void
    {
        $p = $this->interpret('criar movimento receita cliente fabio 100€ pago');

        $this->assertNull($p->value('client_id'));
        $this->assertSame([$this->fabio->id], array_column($p->options['client_id'], 'id'), 'só como sugestão');
    }

    public function test_varias_legalizacoes_do_cliente_apresentam_opcoes_distintas(): void
    {
        $segunda = $this->legalization($this->fabio, 'Audi', 'A3', '99-ZZ-11');

        $p = $this->interpret('criar movimento despesa 10€ legalização fabio oliveira pago');

        $this->assertNull($p->value('legalization_id'));
        $this->assertContains('legalization_id', $p->unresolved);
        $labels = array_column($p->options['legalization_id'], 'label', 'id');
        $this->assertEqualsCanonicalizing([$this->fabioLegalization->id, $segunda->id], array_keys($labels));
        $this->assertStringContainsString('BMW i4', $labels[$this->fabioLegalization->id]);
        $this->assertStringContainsString('99-ZZ-11', $labels[$segunda->id]);
    }

    public function test_legalizacao_por_matricula(): void
    {
        $p = $this->interpret('criar movimento despesa 10€ legalização 12-ab-34 pago');

        $this->assertSame($this->fabioLegalization->id, $p->value('legalization_id'));
    }

    public function test_cliente_e_legalizacao_contraditorios_bloqueiam_a_confirmacao(): void
    {
        $this->client('Ana Costa');

        $text = 'criar movimento despesa 10€ cliente Ana Costa legalização fabio oliveira pago';
        $p = $this->interpret($text);

        $this->assertContains('client_id', $p->unresolved);
        $this->assertStringContainsString('Contradição', collect($p->warnings)->pluck('message')->implode(' '));

        $interpretation = app(QuickEntryService::class)->interpret($this->admin, $text);
        $this->actingAs($this->admin)
            ->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $this->formFrom($interpretation))
            ->assertSessionHasErrors();
        $this->assertSame(0, Expense::count());
    }

    public function test_tipos_e_metodos_incompativeis_pedem_correcao(): void
    {
        $tipos = $this->interpret('criar movimento despesa receita 10€ pago');
        $this->assertNull($tipos->value('movement_type'));
        $this->assertContains('movement_type', $tipos->unresolved);

        $metodos = $this->interpret('criar movimento despesa 10€ pago em dinheiro por transferencia');
        $this->assertNull($metodos->value('payment_method'));
        $this->assertContains('payment_method', $metodos->unresolved);
    }

    public function test_sem_tipo_e_sem_regra_pede_escolha(): void
    {
        $p = $this->interpret('criar movimento 10€ pago');

        $this->assertNull($p->value('movement_type'));
        $this->assertContains('movement_type', $p->unresolved);
    }

    // ── Estado ──────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\DataProvider('estados')]
    public function test_estado(string $expressao, string $esperado): void
    {
        $p = $this->interpret("criar movimento despesa 10€ {$expressao}");

        $this->assertSame($esperado, $p->value('status'));
    }

    public static function estados(): array
    {
        return [
            'pago' => ['pago', 'paid'],
            'paga' => ['paga', 'paid'],
            'recebido' => ['recebido', 'paid'],
            'recebida' => ['recebida', 'paid'],
            'não pago' => ['não pago', 'pending'],
            'nao paga' => ['nao paga', 'pending'],
            'por pagar' => ['por pagar', 'pending'],
            'não recebido' => ['não recebido', 'pending'],
            'por receber' => ['por receber', 'pending'],
            'pendente' => ['pendente', 'pending'],
        ];
    }

    public function test_por_pagar_nunca_e_pago_e_metodo_sozinho_nao_significa_pago(): void
    {
        $naoPago = $this->interpret('criar movimento despesa 10€ por pagar em dinheiro');
        $this->assertSame('pending', $naoPago->value('status'));
        $this->assertSame('cash', $naoPago->value('payment_method'));

        $semEstado = $this->interpret('criar movimento despesa 10€ em dinheiro');
        $this->assertSame('default', $semEstado->fields['status']['source'], 'o estado vem do valor por defeito do formulário, assinalado como tal');

        $contraditorio = $this->interpret('criar movimento despesa 10€ pago por pagar');
        $this->assertNull($contraditorio->value('status'));
        $this->assertContains('status', $contraditorio->unresolved);
    }

    // ── Prioridades ─────────────────────────────────────────────────────────

    public function test_categoria_e_tipo_explicitos_prevalecem_sobre_transporte(): void
    {
        $categoria = $this->interpret('criar movimento despesa categoria marketing transporte 10€ pago');
        $this->assertSame('marketing', $categoria->value('category'));
        $this->assertSame('extracted', $categoria->fields['category']['source']);

        $tipo = $this->interpret('criar movimento receita transporte 10€ pago');
        $this->assertSame('income', $tipo->value('movement_type'));
        $this->assertSame('extracted', $tipo->fields['movement_type']['source']);
        $this->assertSame('transport', $tipo->value('category'));
    }

    public function test_categoria_inexistente_nao_e_criada(): void
    {
        $p = $this->interpret('criar movimento despesa categoria astrologia 10€ pago');

        $this->assertNull($p->value('category'));
        $this->assertContains('category', $p->unresolved);
    }

    public function test_categoria_com_varias_palavras(): void
    {
        $p = $this->interpret('criar movimento despesa categoria compra de veículo 10€ pago');

        $this->assertSame('vehicle_purchase', $p->value('category'));
    }

    // ── Datas ───────────────────────────────────────────────────────────────

    public function test_datas_relativas_usam_europe_lisbon(): void
    {
        // 2026-10-20 23:30 UTC já é dia 21 em Lisboa (WEST, UTC+1).
        $this->assertSame('2026-10-21', $this->interpret('criar movimento despesa 10€ hoje pago')->value('expense_date'));
        $this->assertSame('2026-10-20', $this->interpret('criar movimento despesa 10€ ontem pago')->value('expense_date'));
        $this->assertSame('2026-02-05', $this->interpret('criar movimento despesa 10€ 05/02/2026 pago')->value('expense_date'));
    }

    public function test_sem_data_aplica_o_valor_por_defeito_do_formulario_e_mostra_o(): void
    {
        $p = $this->interpret('criar movimento despesa 10€ pago');

        $this->assertSame('2026-10-21', $p->value('expense_date'));
        $this->assertSame('default', $p->fields['expense_date']['source']);
        $this->assertSame(23, $p->value('vat_rate'));
        $this->assertSame('default', $p->fields['vat_rate']['source']);
    }

    public function test_data_invalida_ou_contraditoria_pede_correcao(): void
    {
        $invalida = $this->interpret('criar movimento despesa 10€ 31/02/2026 pago');
        $this->assertNull($invalida->value('expense_date'));
        $this->assertContains('expense_date', $invalida->unresolved);

        $varias = $this->interpret('criar movimento despesa 10€ hoje ontem pago');
        $this->assertNull($varias->value('expense_date'));
    }

    // ── Texto não interpretado ──────────────────────────────────────────────

    public function test_texto_nao_interpretado_e_avisado_e_aproveitado_no_titulo(): void
    {
        $p = $this->interpret('criar movimento despesa 10€ pago pneus novos do carro');

        $this->assertSame('pneus novos carro', $p->leftover);
        $this->assertSame('pneus novos carro', $p->value('title'));
        $this->assertStringContainsString('Texto não interpretado', collect($p->warnings)->pluck('message')->implode(' '));
        $this->assertSame('criar movimento despesa 10€ pago pneus novos do carro', $p->value('observations'), 'preserva o texto original');
    }

    // ── Comandos ────────────────────────────────────────────────────────────

    public function test_comandos_reconhecidos_mas_nao_implementados_nao_alteram_nada(): void
    {
        $clientes = Client::count();

        foreach (['editar cliente Fábio Oliveira telefone 910000000', 'Editar cliente'] as $text) {
            $p = $this->interpret($text);
            $this->assertSame(Proposal::NOT_IMPLEMENTED, $p->status);
            $this->assertNull($p->confirmLabel);
        }

        $this->assertSame($clientes, Client::count());

        $interpretation = app(QuickEntryService::class)->interpret($this->admin, 'editar cliente João Silva');
        $this->actingAs($this->admin)
            ->post(route('admin.v2.quick-entry.confirm', $interpretation->id), ['title' => 'x'])
            ->assertSessionHasErrors('interpretation');
        $this->assertSame($clientes, Client::count());
        $this->assertSame(0, Expense::count());
    }

    public function test_comando_desconhecido_e_so_identificado_no_inicio(): void
    {
        $this->assertSame(Proposal::UNRECOGNIZED, $this->interpret('cliente fabio oliveira 10€')->status);
        $this->assertSame(Proposal::UNRECOGNIZED, $this->interpret('apagar movimento 10€')->status);
        $this->assertSame(Proposal::UNRECOGNIZED, $this->interpret('hoje criar movimento despesa 10€')->status);
    }

    // ── HTTP: interpretar não grava, confirmar grava uma vez ────────────────

    public function test_interpretar_nao_cria_movimentos_nem_altera_registos(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.v2.quick-entry.interpret'), ['text' => 'criar movimento despesa categoria outros 10€ legalização fabio oliveira pago em dinheiro'])
            ->assertRedirect();

        $this->assertSame(0, Expense::count());
        $this->assertSame(1, Client::count());
        $this->assertSame(1, QuickEntryInterpretation::count());

        $interpretation = QuickEntryInterpretation::first();
        $this->assertSame(QuickEntryInterpretation::INTERPRETED, $interpretation->status);
        $this->assertSame($this->admin->id, $interpretation->user_id);
        $this->assertSame('movimento', $interpretation->module);
        $this->assertSame(QuickEntryDispatcher::VERSION, $interpretation->interpreter_version);
        $this->assertSame('10.00', $interpretation->proposal['fields']['amount']['value']);

        $this->actingAs($this->admin)
            ->get(route('admin.v2.quick-entry.show', $interpretation->id))
            ->assertOk()
            ->assertSee('Criar movimento')
            ->assertSee('Fábio Oliveira');
    }

    /** Formulário tal como o browser o enviaria sem edições. */
    private function formFrom(QuickEntryInterpretation $interpretation, array $override = []): array
    {
        $form = [];
        foreach ($interpretation->proposal['fields'] as $field => $data) {
            $form[$field] = $data['value'];
        }

        return $override + $form;
    }

    public function test_confirmar_cria_o_movimento_com_as_regras_normais_e_regista_o_historico(): void
    {
        $interpretation = app(QuickEntryService::class)->interpret($this->admin, 'criar movimento despesa categoria outros 10€ legalização fabio oliveira pago em dinheiro');

        $this->actingAs($this->admin)
            ->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $this->formFrom($interpretation, ['title' => 'Taxa balcão']))
            ->assertRedirect(route('admin.v2.movements.index'))
            ->assertSessionHas('success');

        $movement = Expense::sole();
        $this->assertSame('expense', $movement->movement_type);
        $this->assertSame('other', $movement->category);
        $this->assertEquals(10.0, $movement->amount);
        $this->assertSame('paid', $movement->status);
        $this->assertSame('cash', $movement->payment_method);
        $this->assertSame($this->fabio->id, $movement->client_id);
        $this->assertSame($this->fabioLegalization->id, $movement->legalization_id);
        $this->assertSame('Taxa balcão', $movement->title);
        $this->assertEquals(1.87, $movement->vat_amount, 'IVA a 23% do formulário (por defeito)');
        $this->assertSame('2026-10-21', $movement->expense_date->toDateString());

        $interpretation->refresh();
        $this->assertSame(QuickEntryInterpretation::CONFIRMED, $interpretation->status);
        $this->assertSame($movement->id, $interpretation->record_id);
        $this->assertSame(Expense::class, $interpretation->record_type);
        $this->assertNotNull($interpretation->confirmed_at);
        $this->assertSame('Taxa balcão', $interpretation->final_data['title']);
        $this->assertNotSame('Taxa balcão', $interpretation->proposal['fields']['title']['value'], 'a proposta original fica preservada');
    }

    public function test_confirmacao_repetida_nao_cria_um_segundo_movimento(): void
    {
        $interpretation = app(QuickEntryService::class)->interpret($this->admin, 'criar movimento receita cliente Fabio oliveira 2000€ pago transferencia bancaria');
        $form = $this->formFrom($interpretation);

        $this->actingAs($this->admin)->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $form)->assertSessionHas('success');
        $this->actingAs($this->admin)->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $form)->assertSessionHas('success');

        $this->assertSame(1, Expense::count());
        $this->assertEquals(2000.0, Expense::first()->amount);
    }

    public function test_confirmar_revalida_no_servidor_e_exige_escolhas_pendentes(): void
    {
        $this->client('Fabio Oliveira');
        $interpretation = app(QuickEntryService::class)->interpret($this->admin, 'criar movimento receita cliente fabio oliveira 100€ pago');
        $form = $this->formFrom($interpretation, ['client_id' => '']);

        $this->actingAs($this->admin)
            ->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $form)
            ->assertSessionHasErrors('client_id');
        $this->assertSame(0, Expense::count());

        // Deixar em branco de propósito é aceite; um id inventado não.
        $this->actingAs($this->admin)
            ->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $form + ['client_id' => '9999'])
            ->assertSessionHasErrors('client_id');

        $this->actingAs($this->admin)
            ->post(route('admin.v2.quick-entry.confirm', $interpretation->id), ['client_id' => QuickEntryService::NONE] + $form)
            ->assertSessionHasNoErrors();
        $this->assertNull(Expense::sole()->client_id);
    }

    public function test_confirmar_recusa_valores_fora_das_regras(): void
    {
        $interpretation = app(QuickEntryService::class)->interpret($this->admin, 'criar movimento despesa 10€ pago');
        $url = route('admin.v2.quick-entry.confirm', $interpretation->id);
        $form = $this->formFrom($interpretation, ['title' => 'Teste']);

        foreach ([
            ['amount' => '0'], ['amount' => '10,555'], ['amount' => ''], ['movement_type' => 'transfer'],
            ['status' => 'inventado'], ['category' => 'inventada'], ['payment_method' => 'bitcoin'],
            ['expense_date' => '2026-02-31'], ['title' => ''], ['vat_rate' => '99'],
        ] as $mudanca) {
            $this->actingAs($this->admin)->post($url, $mudanca + $form)->assertSessionHasErrors();
        }
        $this->assertSame(0, Expense::count());

        $this->actingAs($this->admin)->post($url, ['amount' => '1.250,50'] + $form)->assertSessionHasNoErrors();
        $this->assertEquals(1250.5, Expense::sole()->amount);
    }

    public function test_cancelar_nao_grava_e_impede_confirmar(): void
    {
        $interpretation = app(QuickEntryService::class)->interpret($this->admin, 'criar movimento despesa 10€ pago');

        $this->actingAs($this->admin)->post(route('admin.v2.quick-entry.cancel', $interpretation->id))->assertRedirect();
        $this->assertSame(QuickEntryInterpretation::CANCELLED, $interpretation->fresh()->status);

        $this->actingAs($this->admin)
            ->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $this->formFrom($interpretation, ['title' => 'x']))
            ->assertSessionHasErrors('interpretation');
        $this->assertSame(0, Expense::count());
    }

    // ── Permissões e âmbito ─────────────────────────────────────────────────

    public function test_sem_permissao_de_criar_movimentos_nao_entra_nem_interpreta_nem_confirma(): void
    {
        $semCriar = $this->limitedUser(['movements.view']);
        $interpretation = app(QuickEntryService::class)->interpret($semCriar, 'criar movimento despesa 10€ pago');

        $this->actingAs($semCriar)->get(route('admin.v2.quick-entry.index'))->assertForbidden();
        $this->actingAs($semCriar)->post(route('admin.v2.quick-entry.interpret'), ['text' => 'criar movimento despesa 10€'])->assertForbidden();
        $this->actingAs($semCriar)->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $this->formFrom($interpretation, ['title' => 'x']))->assertForbidden();
        $this->assertSame(0, Expense::count());
    }

    public function test_so_o_autor_abre_e_confirma_a_sua_interpretacao(): void
    {
        $outro = $this->limitedUser(['movements.create']);
        $interpretation = app(QuickEntryService::class)->interpret($this->admin, 'criar movimento despesa 10€ pago');

        $this->actingAs($outro)->get(route('admin.v2.quick-entry.show', $interpretation->id))->assertNotFound();
        $this->actingAs($outro)->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $this->formFrom($interpretation, ['title' => 'x']))->assertNotFound();
        $this->assertSame(0, Expense::count());
    }

    public function test_pesquisas_respeitam_o_ambito_do_utilizador(): void
    {
        $dono = $this->limitedUser(['movements.create', 'clients.view.own', 'legalizations.view']);
        $meu = $this->client('Rita Meiga', ['owner_id' => $dono->id]);
        $alheio = $this->client('Rui Alheio', ['owner_id' => $this->admin->id]);

        $ok = $this->interpret('criar movimento receita cliente Rita Meiga 10€ pago', $dono);
        $this->assertSame($meu->id, $ok->value('client_id'));

        $barrado = $this->interpret('criar movimento receita cliente Rui Alheio 10€ pago', $dono);
        $this->assertNull($barrado->value('client_id'));
        $this->assertContains('client_id', $barrado->unresolved);

        // E a gravação revalida: um id fora do âmbito não passa.
        $interpretation = app(QuickEntryService::class)->interpret($dono, 'criar movimento receita 10€ pago');
        $this->actingAs($dono)
            ->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $this->formFrom($interpretation, ['title' => 'x', 'client_id' => $alheio->id]))
            ->assertSessionHasErrors('client_id');
        $this->assertSame(0, Expense::count());

        $semClientes = $this->limitedUser(['movements.create']);
        $this->assertNull($this->interpret('criar movimento receita cliente Rita Meiga 10€ pago', $semClientes)->value('client_id'));
    }

    public function test_historico_so_com_permissao_propria(): void
    {
        app(QuickEntryService::class)->interpret($this->admin, 'criar movimento despesa 10€ pago');

        $this->actingAs($this->limitedUser(['movements.create']))->get(route('admin.v2.quick-entry-history.index'))->assertForbidden();
        $this->actingAs($this->limitedUser(['quick-entry-history.view']))->get(route('admin.v2.quick-entry-history.index'))->assertOk()->assertSee('criar movimento despesa 10€ pago');
        $this->actingAs($this->admin)->get(route('admin.v2.quick-entry-history.index'))->assertOk();
    }

    // ── Mobile ──────────────────────────────────────────────────────────────

    public function test_mobile_interpreta_e_confirma_com_as_mesmas_regras(): void
    {
        $mobile = $this->mobileUser();

        $this->actingAs($mobile)->get(route('admin.mobile.quick-entry.index'))->assertOk()->assertSee('Inserção rápida');

        $this->actingAs($mobile)->post(route('admin.mobile.quick-entry.interpret'), ['text' => 'criar movimento receita cliente Fabio oliveira 2000€ pago transferencia bancaria'])->assertRedirect();
        $interpretation = QuickEntryInterpretation::sole();
        $this->assertSame(0, Expense::count());

        $this->actingAs($mobile)->get(route('admin.mobile.quick-entry.show', $interpretation->id))->assertOk()->assertSee('Criar movimento');

        $response = $this->actingAs($mobile)->post(route('admin.mobile.quick-entry.confirm', $interpretation->id), $this->formFrom($interpretation, ['title' => 'Sinal']));
        $response->assertRedirect(route('admin.mobile.movements.show', Expense::sole()->id));
    }

    public function test_paginas_mostram_ambiguidades_e_comandos_nao_implementados(): void
    {
        $this->client('Fabio Oliveira');

        $ambigua = app(QuickEntryService::class)->interpret($this->admin, 'criar movimento receita cliente fabio oliveira 100€ pago');
        $this->actingAs($this->admin)->get(route('admin.v2.quick-entry.show', $ambigua->id))
            ->assertOk()->assertSee('Sugestões da frase', false)->assertSee('Há 2 clientes')->assertSee('Criar movimento');

        $futuro = app(QuickEntryService::class)->interpret($this->admin, 'editar cliente João Silva');
        $this->actingAs($this->admin)->get(route('admin.v2.quick-entry.show', $futuro->id))
            ->assertOk()->assertSee('ainda não está disponível')->assertDontSee('id="qe-confirm"', false);
    }

    // ── Criar cliente ───────────────────────────────────────────────────────

    public function test_criar_cliente_extrai_nome_telefone_email_e_nif(): void
    {
        $p = $this->interpret('Criar cliente joão  silva telefone 912 345 678 email Joao@Exemplo.pt nif 123456789');

        $this->assertSame(Proposal::READY, $p->status);
        $this->assertSame('cliente', $p->module);
        $this->assertSame('Criar cliente', $p->confirmLabel);
        $this->assertSame('João Silva', $p->value('name'));
        $this->assertSame('912345678', $p->value('phone'));
        $this->assertSame('joao@exemplo.pt', $p->value('email'));
        $this->assertSame('123456789', $p->value('vat_number'));
        $this->assertSame('pt', $p->value('language'));
        $this->assertSame('default', $p->fields['language']['source']);
        $this->assertNull($p->leftover);
    }

    public function test_criar_cliente_telefone_sem_palavra_e_inferido_e_ordem_livre(): void
    {
        $p = $this->interpret('criar cliente telefone 931 111 222 Maria da Conceição Silva');
        $this->assertSame('Maria da Conceição Silva', $p->value('name'));
        $this->assertSame('931111222', $p->value('phone'));

        $inferido = $this->interpret('criar cliente Rui Costa 961234567');
        $this->assertSame('961234567', $inferido->value('phone'));
        $this->assertSame('inferred', $inferido->fields['phone']['source']);
    }

    public function test_criar_cliente_sinaliza_falta_de_nome_conflitos_e_texto_extra(): void
    {
        $semNome = $this->interpret('criar cliente telefone 912345678');
        $this->assertNull($semNome->value('name'));

        $dois = $this->interpret('criar cliente Ana telefone 912345678 telefone 933333333');
        $this->assertNull($dois->value('phone'));

        $extra = $this->interpret('criar cliente Ana Reis email ana@x.pt veio da feira');
        $this->assertSame('Ana Reis', $extra->value('name'));
        $this->assertSame('veio feira', $extra->value('observation'));
    }

    public function test_criar_cliente_avisa_duplicados_e_email_repetido(): void
    {
        $this->fabio->update(['phone' => '912345678', 'email' => 'fabio@x.pt']);

        $p = $this->interpret('criar cliente Outro Nome telefone 912345678 email fabio@x.pt');

        $texto = collect($p->warnings)->pluck('message')->implode(' ');
        $this->assertStringContainsString('mesmo email', $texto);
        $this->assertStringContainsString('mesmo telefone', $texto);
    }

    public function test_interpretar_cliente_nao_cria_e_confirmar_cria_uma_so_vez(): void
    {
        $antes = Client::count();
        $this->actingAs($this->admin)->post(route('admin.v2.quick-entry.interpret'), ['text' => 'criar cliente Rui Costa telefone 961234567 email rui@x.pt'])->assertRedirect();
        $this->assertSame($antes, Client::count());

        $interpretation = QuickEntryInterpretation::latest('id')->first();
        $this->actingAs($this->admin)->get(route('admin.v2.quick-entry.show', $interpretation->id))->assertOk()->assertSee('Novo cliente')->assertSee('Rui Costa');

        $form = $this->formFrom($interpretation);
        $this->actingAs($this->admin)->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $form)->assertRedirect(route('admin.v2.clients.index'));
        $this->actingAs($this->admin)->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $form)->assertSessionHas('success');

        $this->assertSame($antes + 1, Client::count());
        $client = Client::where('name', 'Rui Costa')->sole();
        $this->assertSame('961234567', $client->phone);
        $this->assertSame('rui@x.pt', $client->email);
        $this->assertSame(Client::class, $interpretation->fresh()->record_type);
        $this->assertSame($client->id, $interpretation->fresh()->record_id);
    }

    public function test_confirmar_cliente_revalida_email_telefone_e_dados(): void
    {
        $this->fabio->update(['phone' => '912345678', 'email' => 'fabio@x.pt']);
        $interpretation = app(QuickEntryService::class)->interpret($this->admin, 'criar cliente Novo Nome');
        $url = route('admin.v2.quick-entry.confirm', $interpretation->id);
        $form = $this->formFrom($interpretation);
        $antes = Client::count();

        $this->actingAs($this->admin)->post($url, ['email' => 'fabio@x.pt'] + $form)->assertSessionHasErrors('email');
        $this->actingAs($this->admin)->post($url, ['phone' => '912 345 678'] + $form)->assertSessionHasErrors('confirm_duplicate');
        $this->actingAs($this->admin)->post($url, ['name' => ''] + $form)->assertSessionHasErrors('name');
        $this->actingAs($this->admin)->post($url, ['phone' => '12'] + $form)->assertSessionHasErrors('phone');
        $this->actingAs($this->admin)->post($url, ['vat_number' => '123'] + $form)->assertSessionHasErrors('vat_number');
        $this->assertSame($antes, Client::count());

        $this->actingAs($this->admin)->post($url, ['phone' => '912 345 678', 'confirm_duplicate' => '1'] + $form)->assertSessionHasNoErrors();
        $this->assertSame($antes + 1, Client::count());
    }

    public function test_permissoes_por_comando_movimentos_e_clientes(): void
    {
        $soMovimentos = $this->limitedUser(['movements.create']);
        $soClientes = $this->limitedUser(['clients.create']);
        $nenhuma = $this->limitedUser(['movements.view']);

        $this->actingAs($nenhuma)->get(route('admin.v2.quick-entry.index'))->assertForbidden();

        $this->actingAs($soMovimentos)->post(route('admin.v2.quick-entry.interpret'), ['text' => 'criar cliente Ana'])->assertForbidden();
        $this->actingAs($soMovimentos)->post(route('admin.v2.quick-entry.interpret'), ['text' => 'criar movimento despesa 10€'])->assertRedirect();

        $this->actingAs($soClientes)->post(route('admin.v2.quick-entry.interpret'), ['text' => 'criar movimento despesa 10€'])->assertForbidden();
        $this->actingAs($soClientes)->post(route('admin.v2.quick-entry.interpret'), ['text' => 'criar cliente Ana'])->assertRedirect();

        $interpretation = QuickEntryInterpretation::where('user_id', $soClientes->id)->sole();
        $this->actingAs($soClientes)->post(route('admin.v2.quick-entry.confirm', $interpretation->id), $this->formFrom($interpretation))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('clients', ['name' => 'Ana']);
    }

    public function test_menu_mobile_mostra_a_insercao_rapida_na_barra_inferior(): void
    {
        $this->actingAs($this->mobileUser())->get(route('admin.mobile.dashboard'))
            ->assertOk()->assertSee(route('admin.mobile.quick-entry.index'), false)->assertSee('Rápida');

        $semAcesso = $this->limitedUser(['movements.view']);
        $semAcesso->forceFill(['access_mobile' => true])->save();
        $this->actingAs($semAcesso)->get(route('admin.mobile.dashboard'))->assertOk()->assertDontSee(route('admin.mobile.quick-entry.index'), false);
    }
}
