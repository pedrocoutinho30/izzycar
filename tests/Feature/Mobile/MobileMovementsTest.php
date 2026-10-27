<?php

namespace Tests\Feature\Mobile;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\Expense;
use App\Models\FinancialMovement;
use App\Models\Legalization;
use App\Models\Partner;
use App\Models\Sale;
use App\Models\User;
use App\Models\V3Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Movimentos na versão mobile. Sem registar YEAR() no sqlite: a listagem
 * mobile tem de usar SQL portátil.
 */
class MobileMovementsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

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

    private function mov(array $attributes = []): Expense
    {
        return Expense::create($attributes + [
            'movement_type' => 'expense', 'category' => 'repair', 'title' => 'Manual',
            'amount' => 100, 'amount_gross' => 100, 'expense_date' => '2026-05-10', 'status' => 'paid',
        ]);
    }

    private function payload(array $override = []): array
    {
        return $override + [
            'movement_type' => 'expense',
            'category' => 'repair',
            'title' => 'Pneus novos',
            'amount' => '123,00',
            'vat_rate' => '23',
            'expense_date' => '2026-10-10',
            'status' => 'paid',
            'payment_method' => 'card',
        ];
    }

    /** Utilizador não admin, com acesso mobile, com apenas as permissões dadas. */
    private function userWith(array $permissions): User
    {
        $role = Role::findOrCreate('mobile-' . md5(implode(',', $permissions)), 'web');
        foreach ($permissions as $nome) {
            $role->givePermissionTo(Permission::findOrCreate($nome, 'web'));
        }
        $user = $this->backofficeUser($role->name);
        $user->forceFill(['access_mobile' => true])->save();

        return $user;
    }

    private function convertedWithPaidTranche(): ConvertedProposal
    {
        $client = Client::create(['name' => 'Ana Silva', 'email' => 'ana@example.com', 'phone' => '912345678', 'is_lead' => false]);

        return ConvertedProposal::create([
            'client_id' => $client->id, 'status' => 'Iniciada', 'brand' => 'BMW', 'modelCar' => 'i4',
            'valor_primeira_tranche' => 982.5, 'primeira_tranche_pago' => true, 'primeira_tranche_paga_em' => '2026-10-05',
        ]);
    }

    // ── Lista ───────────────────────────────────────────────────────────

    public function test_lista_mostra_cartoes_totais_e_saldos_como_o_desktop(): void
    {
        $vehicle = V3Vehicle::create(['reference' => 'REF-1', 'brand' => 'BMW', 'model' => 'i4']);
        $this->mov(['title' => 'Venda carro', 'movement_type' => 'income', 'category' => 'vehicle_sale', 'amount' => 1000, 'amount_gross' => 1000, 'expense_date' => '2026-03-01', 'v3_vehicle_id' => $vehicle->id]);
        $this->mov(['title' => 'Reparação', 'amount' => 200, 'amount_gross' => 200, 'expense_date' => '2026-04-01']);
        $this->mov(['title' => 'Pendente', 'amount' => 50, 'amount_gross' => 50, 'expense_date' => '2026-04-02', 'status' => 'pending']);
        $this->mov(['title' => 'Ano passado', 'movement_type' => 'income', 'amount' => 7000, 'amount_gross' => 7000, 'expense_date' => '2025-06-01']);

        $r = $this->actingAs($this->mobileUser())->get(route('admin.mobile.movements.index'))
            ->assertOk()
            ->assertSee('Venda carro')->assertSee('BMW i4 (REF-1)')->assertSee('Reparação')->assertSee('Pendente')
            ->assertDontSee('Ano passado')
            ->assertSee('Totais de 2026')
            ->assertSee('1.000,00 €')     // entradas
            ->assertSee('250,00 €')       // saídas
            ->assertSee('16.507,28 €')    // saldo c/ pendentes
            ->assertSee('16.557,28 €')    // saldo real
            ->assertSee('3 movimentos com estes filtros')
            ->assertDontSee('<table', false);

        $this->assertSame(['2026', '2025'], collect($r->viewData('availableYears'))->map(fn ($y) => (string) $y)->all());

        // Desde o início.
        $this->get(route('admin.mobile.movements.index', ['year' => 'all']))
            ->assertOk()->assertSee('Ano passado')->assertSee('Totais desde o início')->assertSee('8.000,00 €');
    }

    public function test_filtros_do_desktop(): void
    {
        $vehicle = V3Vehicle::create(['reference' => 'REF-1', 'brand' => 'BMW', 'model' => 'i4']);
        $client = Client::create(['name' => 'Rui Costa', 'email' => 'rui@example.com', 'phone' => '913333333', 'is_lead' => false]);
        $this->mov(['title' => 'AlfaReceita', 'movement_type' => 'income', 'category' => 'import_service']);
        $this->mov(['title' => 'BetaPendente', 'status' => 'pending', 'observations' => 'nota secreta']);
        $this->mov(['title' => 'GamaViatura', 'v3_vehicle_id' => $vehicle->id]);
        $this->mov(['title' => 'DeltaCliente', 'client_id' => $client->id]);
        $this->mov(['title' => 'EpsilonAbril', 'expense_date' => '2026-04-15']);
        $this->mov(['title' => 'ZetaAntigo', 'expense_date' => '2024-01-01']);
        $user = $this->mobileUser();

        $titulos = fn (array $q) => $this->actingAs($user)->get(route('admin.mobile.movements.index', $q))->assertOk()
            ->viewData('movements')->pluck('title')->sort()->values()->all();

        $this->assertSame(['AlfaReceita'], $titulos(['movement_type' => 'income']));
        $this->assertSame(['AlfaReceita'], $titulos(['category' => 'import_service']));
        $this->assertSame(['BetaPendente'], $titulos(['status' => 'pending']));
        $this->assertSame(['GamaViatura'], $titulos(['v3_vehicle_id' => $vehicle->id]));
        $this->assertSame(['DeltaCliente'], $titulos(['client_id' => $client->id]));
        $this->assertSame(['BetaPendente'], $titulos(['search' => 'secreta']));
        $this->assertSame(['EpsilonAbril'], $titulos(['date_from' => '2026-04-01', 'date_to' => '2026-04-30']));
        $this->assertSame(['ZetaAntigo'], $titulos(['year' => '2024']));
        $this->assertCount(6, $titulos(['year' => 'all']));
        // Valores desconhecidos não filtram (nem dão erro).
        $this->assertCount(5, $titulos(['movement_type' => 'transfer', 'category' => 'xyz', 'year' => 'abc', 'date_from' => '2026-13-45']));

        $this->actingAs($user)->get(route('admin.mobile.movements.index', ['client_id' => $client->id]))->assertSee('Rui Costa');
    }

    public function test_pesquisa_trata_curingas_como_texto(): void
    {
        $this->mov(['title' => 'Desconto 50% total']);
        $this->mov(['title' => 'Desconto 500 total']);
        $this->mov(['title' => 'cod_a']);
        $this->mov(['title' => 'codXa']);
        $user = $this->mobileUser();

        $titulos = fn (string $s) => $this->actingAs($user)->get(route('admin.mobile.movements.index', ['search' => $s]))->assertOk()
            ->viewData('movements')->pluck('title')->all();

        $this->assertSame(['Desconto 50% total'], $titulos('50%'));
        $this->assertSame(['cod_a'], $titulos('cod_a'));
        $this->assertSame([], $titulos('!'));
    }

    public function test_parametros_em_formato_de_lista_nao_dao_erro(): void
    {
        $this->mov(['title' => 'MovUnico']);
        $user = $this->mobileUser();

        foreach (['search', 'page', 'year', 'movement_type', 'category', 'status', 'v3_vehicle_id', 'client_id', 'date_from', 'date_to', 'convertida'] as $chave) {
            $this->actingAs($user)->get(route('admin.mobile.movements.index', [$chave => ['x']]))
                ->assertOk()->assertSee('MovUnico');
        }

        // No formulário, um campo em lista é um erro de validação, não um 500.
        $this->actingAs($user)->post(route('admin.mobile.movements.store'), $this->payload(['title' => ['x'], 'amount' => ['1']]))
            ->assertSessionHasErrors(['title', 'amount']);
    }

    public function test_lista_pagina_com_carregar_mais(): void
    {
        foreach (range(1, 17) as $i) {
            $this->mov(['title' => "Mov {$i}", 'expense_date' => sprintf('2026-01-%02d', $i)]);
        }

        $r = $this->actingAs($this->mobileUser())->get(route('admin.mobile.movements.index', ['status' => 'paid']))->assertOk()
            ->assertSee('id="m-load-more"', false)->assertSee('Mov 17')->assertDontSee('Mov 2<', false);
        $this->assertCount(15, $r->viewData('movements'));
        $this->assertStringContainsString('status=paid', $r->viewData('movements')->nextPageUrl());

        $this->get(route('admin.mobile.movements.index', ['status' => 'paid', 'page' => 2]))->assertOk()
            ->assertSee('Mov 1')->assertSee('Mov 2')->assertDontSee('id="m-load-more"', false);
    }

    // ── Ficha ───────────────────────────────────────────────────────────

    public function test_ficha_mostra_valores_ligacoes_e_acoes(): void
    {
        $vehicle = V3Vehicle::create(['reference' => 'REF-9', 'brand' => 'Audi', 'model' => 'A3']);
        $client = Client::create(['name' => 'Rui Costa', 'email' => 'rui@example.com', 'phone' => '913333333', 'is_lead' => false]);
        $partner = Partner::create(['name' => 'Oficina X']);
        $leg = Legalization::create(['marca' => 'Seat', 'modelo' => 'Leon', 'matricula' => 'AA-00-BB', 'combustivel' => 'Gasolina', 'steps_completed' => []]);
        $m = $this->mov([
            'legalization_id' => $leg->id,
            'title' => 'Revisão <b>grande</b>', 'amount' => 123, 'amount_gross' => 123, 'vat_rate' => 23, 'vat_amount' => 23, 'amount_net' => 100,
            'v3_vehicle_id' => $vehicle->id, 'client_id' => $client->id, 'partner_id' => $partner->id,
            'observations' => '<script>alert(1)</script>', 'payment_method' => 'mbway',
        ]);

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.movements.show', $m->id))
            ->assertOk()
            ->assertSee('Revisão &lt;b&gt;grande&lt;/b&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('−123,00 €')->assertSee('100,00 €')->assertSee('23%')->assertSee('MBWay')
            ->assertSee('REF-9 – Audi A3')->assertSee('Oficina X')->assertSee('#' . $leg->id . ' — Seat Leon — AA-00-BB')
            ->assertSee(route('admin.mobile.clients.show', $client->id), false)
            ->assertSee(route('admin.mobile.movements.edit', $m->id), false)
            ->assertSee(route('admin.mobile.movements.destroy', $m->id), false)
            ->assertSee("confirm('Eliminar este movimento?')", false)
            ->assertSee(route('admin.v2.movements.edit', $m->id), false);
    }

    public function test_ficha_inexistente_da_404(): void
    {
        $this->actingAs($this->mobileUser())->get(route('admin.mobile.movements.show', 999999))->assertNotFound();
    }

    // ── Criar, editar, eliminar ─────────────────────────────────────────

    public function test_criar_com_virgula_sincroniza_como_o_desktop_e_aparece_no_desktop(): void
    {
        $user = $this->mobileUser();
        $dono = Client::create(['name' => 'Dono Leg', 'email' => 'leg@example.com', 'phone' => '914444444', 'is_lead' => false]);
        $leg = Legalization::create(['client_id' => $dono->id, 'marca' => 'Seat', 'modelo' => 'Leon', 'matricula' => 'AA-00-BB', 'combustivel' => 'Gasolina', 'steps_completed' => []]);

        $this->actingAs($user)->get(route('admin.mobile.movements.create'))->assertOk()
            ->assertSee('Novo movimento')->assertSee('inputmode="decimal"', false)->assertSee('name="_token"', false)
            ->assertSee('#' . $leg->id . ' — Seat Leon — AA-00-BB (Dono Leg)')
            ->assertSee('value="2026-10-20"', false)->assertSee('<option value="23" selected', false);

        $r = $this->actingAs($user)->post(route('admin.mobile.movements.store'), $this->payload(['amount' => '1.200,50']));

        $m = Expense::sole();
        $r->assertRedirect(route('admin.mobile.movements.show', $m->id))->assertSessionHas('success', 'Movimento criado com sucesso!');
        $this->assertEquals(1200.50, $m->amount);
        $this->assertEquals(1200.50, $m->amount_gross);
        $this->assertEquals(224.48, $m->vat_amount);
        $this->assertEquals(976.02, $m->amount_net);
        $this->assertNull($m->source_type);

        // Mesmos efeitos do desktop: o lançamento financeiro.
        $fm = FinancialMovement::sole();
        $this->assertSame(Expense::class, $fm->movable_type);
        $this->assertSame($m->id, $fm->movable_id);
        $this->assertSame('expense', $fm->type);
        $this->assertSame('Reparação', $fm->category);
        $this->assertSame('Pneus novos', $fm->description);
        $this->assertEquals(1200.50, $fm->amount_gross);
        $this->assertEquals(224.48, $fm->vat_amount);
        $this->assertEquals(976.02, $fm->amount_net);
        $this->assertSame('2026-10-10', $fm->movement_date->toDateString());

        // Ida e volta: aparece na lista do desktop.
        $this->actingAs($user)->get(route('admin.v2.movements.index'))->assertOk()->assertSee('Pneus novos');

        // Vírgula simples.
        $this->actingAs($user)->post(route('admin.mobile.movements.store'), $this->payload(['title' => 'Outro', 'amount' => '800,5', 'vat_rate' => '0']));
        $this->assertEquals(800.5, Expense::where('title', 'Outro')->sole()->amount_net);
    }

    public function test_criar_invalido_e_campos_fora_do_formulario_ignorados(): void
    {
        $user = $this->mobileUser();

        $this->actingAs($user)->from(route('admin.mobile.movements.create'))
            ->post(route('admin.mobile.movements.store'), $this->payload(['title' => '', 'amount' => 'abc', 'expense_date' => '']))
            ->assertRedirect(route('admin.mobile.movements.create'))
            ->assertSessionHasErrors(['title', 'amount', 'expense_date']);
        $this->assertSame(0, Expense::count());

        $this->actingAs($user)->post(route('admin.mobile.movements.store'), $this->payload([
            'source_type' => Sale::class, 'source_id' => 3, 'vehicle_id' => 4, 'attachment_path' => 'movements/attachments/x.pdf',
            'amount_gross' => 999, 'amount_net' => 999,
        ]));
        $m = Expense::sole();
        $this->assertNull($m->source_type);
        $this->assertNull($m->source_id);
        $this->assertNull($m->vehicle_id);
        $this->assertNull($m->attachment_path);
        $this->assertEquals(100.0, $m->amount_net);
    }

    public function test_editar_e_eliminar(): void
    {
        $m = $this->mov(['attachment_path' => 'movements/attachments/recibo.pdf']);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.movements.edit', $m->id))->assertOk()
            ->assertSee('Editar movimento')->assertSee('value="100"', false)->assertSee('name="_method" value="PUT"', false);

        $this->actingAs($user)->put(route('admin.mobile.movements.update', $m->id), $this->payload(['title' => 'Revisão', 'amount' => '246,00', 'movement_type' => 'income']))
            ->assertRedirect(route('admin.mobile.movements.show', $m->id))
            ->assertSessionHas('success', 'Movimento atualizado com sucesso!');

        $m->refresh();
        $this->assertSame('Revisão', $m->title);
        $this->assertEquals(200.0, $m->amount_net);
        // O comprovativo mantém-se (gere-se no desktop).
        $this->assertSame('movements/attachments/recibo.pdf', $m->attachment_path);
        $this->assertSame('income', FinancialMovement::sole()->type);

        $this->actingAs($user)->delete(route('admin.mobile.movements.destroy', $m->id))
            ->assertRedirect(route('admin.mobile.movements.index'))
            ->assertSessionHas('success', 'Movimento eliminado com sucesso!');
        $this->assertSame(0, Expense::count());
        $this->assertSame(0, FinancialMovement::count());
    }

    // ── Automáticos ─────────────────────────────────────────────────────

    public function test_movimento_automatico_e_so_de_consulta_e_liga_a_convertida(): void
    {
        $converted = $this->convertedWithPaidTranche();
        $auto = Expense::sole();
        $this->assertNotEmpty($auto->source_type);
        $user = $this->mobileUser();

        $this->actingAs($user)->get(route('admin.mobile.movements.show', $auto->id))
            ->assertOk()
            ->assertSee('Gerado automaticamente')
            ->assertSee(route('admin.mobile.converted-proposals.show', $converted->id), false)
            ->assertDontSee(route('admin.mobile.movements.edit', $auto->id), false)
            ->assertDontSee('Eliminar este movimento');

        $this->actingAs($user)->get(route('admin.mobile.movements.edit', $auto->id))
            ->assertRedirect(route('admin.mobile.movements.show', $auto->id))
            ->assertSessionHas('error');
        $this->actingAs($user)->put(route('admin.mobile.movements.update', $auto->id), $this->payload(['title' => 'Mudado']))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.mobile.movements.destroy', $auto->id))->assertForbidden();

        $this->assertSame(1, Expense::count());
        $this->assertNotSame('Mudado', $auto->fresh()->title);

        // Na lista aparece marcado como automático.
        $this->actingAs($user)->get(route('admin.mobile.movements.index'))->assertSee('Auto');
    }

    public function test_automatico_sem_acesso_a_convertida_nao_tem_link(): void
    {
        $converted = $this->convertedWithPaidTranche();
        $auto = Expense::sole();

        $this->actingAs($this->userWith(['movements.view']))->get(route('admin.mobile.movements.show', $auto->id))
            ->assertOk()->assertSee('Gerado automaticamente')
            ->assertDontSee(route('admin.mobile.converted-proposals.show', $converted->id), false);
    }

    public function test_convertida_liga_aos_seus_movimentos(): void
    {
        $converted = $this->convertedWithPaidTranche();
        $this->mov(['title' => 'Alheio']);
        $link = route('admin.mobile.movements.index', ['convertida' => $converted->id, 'year' => 'all']);

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.converted-proposals.show', $converted->id))
            ->assertOk()->assertSee(e($link), false)->assertSee('Movimentos desta importação (1)');

        $this->get($link)->assertOk()->assertSee('1.ª tranche')->assertDontSee('Alheio')->assertSee('Convertida #' . $converted->id);

        // Sem permissão de movimentos: sem link.
        $this->actingAs($this->userWith(['converted-proposals.view']))->get(route('admin.mobile.converted-proposals.show', $converted->id))
            ->assertOk()->assertDontSee('Movimentos desta importação');
    }

    // ── Permissões ──────────────────────────────────────────────────────

    private function allRoutes(Expense $m): array
    {
        return [
            ['get', route('admin.mobile.movements.index')],
            ['get', route('admin.mobile.movements.show', $m->id)],
            ['get', route('admin.mobile.movements.create')],
            ['post', route('admin.mobile.movements.store')],
            ['get', route('admin.mobile.movements.edit', $m->id)],
            ['put', route('admin.mobile.movements.update', $m->id)],
            ['delete', route('admin.mobile.movements.destroy', $m->id)],
        ];
    }

    public function test_com_enforce_sem_ver_movimentos_da_403_em_todas(): void
    {
        config(['permissions.enforce' => true]);
        $m = $this->mov();
        $user = $this->userWith(['leads.view.all', 'clients.view.all']);

        foreach ($this->allRoutes($m) as [$method, $url]) {
            $this->actingAs($user)->{$method}($url, $this->payload())->assertForbidden();
        }
        $this->assertSame(1, Expense::count());
    }

    public function test_sem_enforce_o_controller_bloqueia_quem_nao_ve(): void
    {
        $m = $this->mov();
        $user = $this->userWith(['leads.view.all']);

        foreach ($this->allRoutes($m) as [$method, $url]) {
            $this->actingAs($user)->{$method}($url, $this->payload())->assertForbidden();
        }
        $this->assertSame('Manual', $m->fresh()->title);
    }

    #[DataProvider('enforceProvider')]
    public function test_so_ver_nao_cria_edita_nem_elimina(bool $enforce): void
    {
        config(['permissions.enforce' => $enforce]);
        $m = $this->mov();
        $leitor = $this->userWith(['movements.view']);

        $this->actingAs($leitor)->get(route('admin.mobile.movements.index'))->assertOk()->assertSee('Manual')
            ->assertDontSee(route('admin.mobile.movements.create'), false);
        $this->actingAs($leitor)->get(route('admin.mobile.movements.show', $m->id))->assertOk()
            ->assertDontSee(route('admin.mobile.movements.edit', $m->id), false)
            ->assertDontSee('Eliminar este movimento');

        $this->actingAs($leitor)->get(route('admin.mobile.movements.create'))->assertForbidden();
        $this->actingAs($leitor)->post(route('admin.mobile.movements.store'), $this->payload())->assertForbidden();
        $this->actingAs($leitor)->get(route('admin.mobile.movements.edit', $m->id))->assertForbidden();
        $this->actingAs($leitor)->put(route('admin.mobile.movements.update', $m->id), $this->payload(['title' => 'Mudado']))->assertForbidden();
        $this->actingAs($leitor)->delete(route('admin.mobile.movements.destroy', $m->id))->assertForbidden();

        $this->assertSame(1, Expense::count());
        $this->assertSame('Manual', $m->fresh()->title);

        // Cada ação com a sua permissão.
        $criador = $this->userWith(['movements.view', 'movements.create']);
        $this->actingAs($criador)->post(route('admin.mobile.movements.store'), $this->payload())->assertRedirect();
        $this->actingAs($criador)->delete(route('admin.mobile.movements.destroy', $m->id))->assertForbidden();

        $editor = $this->userWith(['movements.view', 'movements.update']);
        $this->actingAs($editor)->put(route('admin.mobile.movements.update', $m->id), $this->payload(['title' => 'Mudado']))->assertRedirect();
        $this->assertSame('Mudado', $m->fresh()->title);

        $eliminador = $this->userWith(['movements.view', 'movements.delete']);
        $this->actingAs($eliminador)->delete(route('admin.mobile.movements.destroy', $m->id))->assertRedirect(route('admin.mobile.movements.index'));
        $this->assertNull($m->fresh());
    }

    public static function enforceProvider(): array
    {
        return ['enforce ligado' => [true], 'enforce desligado' => [false]];
    }

    public function test_entrada_mais_so_para_quem_pode_ver(): void
    {
        $link = 'href="' . route('admin.mobile.movements.index') . '"';

        $this->actingAs($this->mobileUser())->get(route('admin.mobile.leads.index'))->assertSee($link, false)->assertSee('Movimentos');
        $this->actingAs($this->userWith(['movements.view', 'leads.view.all']))->get(route('admin.mobile.leads.index'))->assertSee($link, false);
        $this->actingAs($this->userWith(['leads.view.all']))->get(route('admin.mobile.leads.index'))->assertOk()->assertDontSee($link, false);
    }

    // ── Correções da revisão ────────────────────────────────────────────

    public function test_chip_do_cliente_so_mostra_o_nome_a_quem_pode_ver_a_ficha(): void
    {
        $cliente = Client::create(['name' => 'Segredo Reservado', 'email' => 's@example.com', 'phone' => '911111111', 'is_lead' => false]);
        // Sem movimentos do cliente, para o nome não vir dos cartões da lista.
        $url = route('admin.mobile.movements.index', ['client_id' => $cliente->id]);

        $soMovimentos = $this->userWith(['movements.view']);
        $this->actingAs($soMovimentos)->get($url)->assertOk()
            ->assertDontSee('Segredo Reservado')->assertSee('Cliente #' . $cliente->id);

        $comClientes = $this->userWith(['movements.view', 'clients.view.all']);
        $this->actingAs($comClientes)->get($url)->assertOk()->assertSee('Segredo Reservado');
    }

    public function test_editar_movimento_antigo_sem_tocar_nos_campos_mantem_os_valores(): void
    {
        $user = $this->userWith(['movements.view', 'movements.update']);
        $antigo = $this->mov([
            'category' => 'categoria_antiga', 'payment_method' => 'cheque', 'status' => 'estado_antigo',
            'vat_rate' => 21, 'amount' => 123, 'amount_gross' => 123, 'vat_amount' => 21.35, 'amount_net' => 101.65,
        ]);

        $html = $this->actingAs($user)->get(route('admin.mobile.movements.edit', $antigo->id))->assertOk()->getContent();
        $this->assertStringContainsString('21% (atual)', $html);
        $this->assertStringContainsString('categoria_antiga (atual)', $html);
        $this->assertStringContainsString('estado_antigo (atual)', $html);
        $this->assertStringContainsString('cheque (atual)', $html);

        // O que o browser enviaria sem mexer em nada.
        $this->actingAs($user)->put(route('admin.mobile.movements.update', $antigo->id), [
            'movement_type' => 'expense', 'category' => 'categoria_antiga', 'title' => 'Manual', 'amount' => '123',
            'vat_rate' => '21', 'expense_date' => '2026-05-10', 'status' => 'estado_antigo', 'payment_method' => 'cheque',
        ])->assertRedirect();

        $f = $antigo->fresh();
        $this->assertSame('categoria_antiga', $f->category);
        $this->assertSame('estado_antigo', $f->status);
        $this->assertSame('cheque', $f->payment_method);
        $this->assertEquals(21, $f->vat_rate);
        $this->assertEquals(21.35, $f->vat_amount);
    }

    public function test_editar_movimento_antigo_sem_taxa_de_iva_mantem_taxa_e_valores(): void
    {
        $user = $this->userWith(['movements.view', 'movements.update']);
        $antigo = $this->mov(['vat_rate' => null, 'amount' => 80, 'amount_gross' => 80, 'vat_amount' => null, 'amount_net' => null]);

        $html = $this->actingAs($user)->get(route('admin.mobile.movements.edit', $antigo->id))->assertOk()->getContent();
        $this->assertStringContainsString('Não definido', $html);
        $this->assertDoesNotMatchRegularExpression('/<option value="23" selected/', $html);

        $this->actingAs($user)->put(route('admin.mobile.movements.update', $antigo->id), [
            'movement_type' => 'expense', 'category' => 'repair', 'title' => 'Manual', 'amount' => '80',
            'vat_rate' => '', 'expense_date' => '2026-05-10', 'status' => 'paid',
        ])->assertRedirect();

        $f = $antigo->fresh();
        $this->assertNull($f->vat_rate);
        $this->assertNull($f->vat_amount);
        $this->assertNull($f->amount_net);
        $this->assertEquals(80, $f->amount_gross);
    }

    public function test_totais_filtrados_usam_o_montante_quando_nao_ha_bruto(): void
    {
        $this->mov(['movement_type' => 'income', 'amount' => 500, 'amount_gross' => 500]);
        $this->mov(['movement_type' => 'income', 'amount' => 250, 'amount_gross' => null]);
        $this->mov(['movement_type' => 'expense', 'amount' => 40, 'amount_gross' => null]);
        $this->mov(['movement_type' => 'expense', 'amount' => 60, 'amount_gross' => 60]);

        $r = $this->actingAs($this->userWith(['movements.view']))->get(route('admin.mobile.movements.index', ['year' => '2026']))->assertOk();
        $this->assertEquals(750.0, $r->viewData('filteredIncome'));
        $this->assertEquals(100.0, $r->viewData('filteredExpenses'));
    }

    public function test_carregar_mais_navega_quando_o_pedido_falha(): void
    {
        foreach (glob(resource_path('views/admin/mobile/*/index.blade.php')) as $vista) {
            $codigo = file_get_contents($vista);
            if (str_contains($codigo, "getElementById('m-load-more')")) {
                $this->assertStringContainsString('if (!resposta.ok) throw', $codigo, basename(dirname($vista)));
            }
        }
    }

    public function test_rotas_mobile_cobertas_pelo_catalogo_com_a_acao_certa(): void
    {
        $registry = app(\App\Permissions\PermissionRegistry::class);
        $esperado = [
            'index' => 'movements.view', 'show' => 'movements.view',
            'create' => 'movements.create', 'store' => 'movements.create',
            'edit' => 'movements.update', 'update' => 'movements.update',
            'destroy' => 'movements.delete',
        ];

        foreach ($esperado as $acao => $regra) {
            $route = Route::getRoutes()->getByName("admin.mobile.movements.{$acao}");
            $this->assertNotNull($route, $acao);
            $this->assertSame($regra, $registry->ruleForRoute($route->getName(), $route->methods()[0])->describe());
        }

        $this->assertSame('/gestao/mobile/movimentos/novo', route('admin.mobile.movements.create', [], false));
    }
}
