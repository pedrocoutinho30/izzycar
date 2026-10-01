<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\Expense;
use App\Models\FinancialMovement;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\PreLead;
use App\Models\Proposal;
use App\Models\User;
use App\Models\V3Vehicle;
use App\Services\ClientMatcher;
use App\Services\ClientMergeService;
use App\Services\PhoneNumberService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class PhaseThreeFunnelTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();
        Notification::fake();
        foreach (['email' => 'geral@izzycar.pt', 'phone' => '928459346', 'facebook' => 'https://facebook.com/izzycar', 'insta' => 'https://instagram.com/izzycar'] as $label => $value) {
            \App\Models\Setting::create(['title' => ucfirst($label), 'label' => $label, 'type' => 'text', 'value' => $value]);
        }
    }

    private function client(array $attributes = []): Client
    {
        return Client::create($attributes + ['name' => 'Maria Silva', 'is_lead' => false, 'lead_status' => 'nova']);
    }

    private function importPayload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Maria Silva', 'phone' => '912 345 678', 'email' => 'maria@example.com',
            'payment_type' => 'pronto_pagamento', 'estimated_purchase_date' => 'imediato',
            'data_processing_consent' => '1', 'brand' => 'BMW', 'model' => 'i4',
        ];
    }

    // ── Regra única de duplicados ────────────────────────────────────

    public function test_matcher_normalizes_email_and_phone(): void
    {
        $matcher = app(ClientMatcher::class);
        $client = $this->client(['email' => '  Maria@Example.COM ', 'phone' => '+351 912 345 678']);

        $this->assertSame('maria@example.com', $client->email_normalized);
        $this->assertSame('912345678', $client->phone_key);

        $this->assertSame('email', $matcher->find('MARIA@example.com', null)->by);
        $this->assertSame('phone', $matcher->find(null, '912-345-678')->by);
        $this->assertSame('phone', $matcher->find('outro@example.com', '00351912345678')->by);
        $this->assertSame('email+phone', $matcher->find('maria@example.com', '912345678')->by);
        $this->assertNull($matcher->find('nada@example.com', '123'), 'menos de 9 dígitos não identifica ninguém');
        $this->assertNull($matcher->find(null, null));
        $this->assertNull($matcher->find('maria@example.com', null, ignoreId: $client->id));
    }

    public function test_matcher_reports_email_and_phone_of_different_people(): void
    {
        $a = $this->client(['name' => 'Ana', 'email' => 'ana@example.com']);
        $b = $this->client(['name' => 'Bruno', 'phone' => '913333333']);

        $match = app(ClientMatcher::class)->find('ana@example.com', '913333333');

        $this->assertTrue($match->client->is($a));
        $this->assertTrue($match->conflict->is($b));
    }

    public function test_import_form_reuses_the_client_found_by_phone_or_email(): void
    {
        $client = $this->client(['email' => 'maria@example.com', 'phone' => '912345678', 'newsletter_consent' => true]);

        // Mesmo telefone, outro email e outro nome (ex. familiar que partilha o número).
        $this->post(route('frontend.import-submit'), $this->importPayload(['name' => 'João Silva', 'email' => 'joao@example.com']))
            ->assertSuccessful();

        $this->assertSame(1, Client::count());
        $request = FormProposal::sole();
        $this->assertSame((string) $client->id, (string) $request->client_id);
        $this->assertSame('João Silva', $request->name, 'o pedido guarda quem o submeteu');

        $client->refresh();
        $this->assertSame('Maria Silva', $client->name);
        $this->assertSame('maria@example.com', $client->email);
        $this->assertTrue((bool) $client->newsletter_consent, 'não perde o consentimento por não marcar a caixa');
        $this->assertSame(1, LeadActivity::where('client_id', $client->id)->where('title', 'like', '%outro nome%')->count());
    }

    public function test_import_form_creates_a_lead_when_nobody_matches(): void
    {
        $this->post(route('frontend.import-submit'), $this->importPayload())->assertSuccessful();

        $lead = Client::sole();
        $this->assertTrue($lead->is_lead);
        $this->assertSame('importacao', $lead->lead_source);
        $this->assertSame('912345678', $lead->phone_key);
    }

    public function test_import_form_works_without_a_submodel(): void
    {
        $this->post(route('frontend.import-submit'), $this->importPayload())->assertSuccessful();

        $this->assertNull(FormProposal::sole()->version);
    }

    public function test_newsletter_matches_email_without_case(): void
    {
        $client = $this->client(['email' => 'maria@example.com']);

        $this->postJson(route('newsletter.subscribe'), ['email' => 'MARIA@Example.com'])->assertOk();

        $this->assertSame(1, Client::count());
        $this->assertTrue((bool) $client->fresh()->newsletter_consent);
    }

    public function test_backoffice_warns_about_an_existing_contact_and_allows_creating_anyway(): void
    {
        $admin = $this->backofficeUser('admin');
        $existing = $this->client(['name' => 'Maria Silva', 'phone' => '912345678']);

        $this->actingAs($admin)
            ->post(route('admin.v2.leads.store'), ['name' => 'Marido da Maria', 'phone' => '+351 912 345 678'])
            ->assertSessionHasErrors('duplicate')
            ->assertSessionHas('duplicate_url', route('admin.v2.clients.show', $existing->id));
        $this->assertSame(1, Client::count());

        $this->actingAs($admin)
            ->post(route('admin.v2.leads.store'), ['name' => 'Marido da Maria', 'phone' => '+351 912 345 678', 'confirm_duplicate' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertSame(2, Client::count());

        $this->actingAs($admin)
            ->post(route('admin.v2.clients.store'), ['name' => 'Outra', 'phone' => '912345678'])
            ->assertSessionHasErrors('duplicate');
    }

    public function test_angariador_never_sees_who_already_owns_a_contact(): void
    {
        $this->client(['name' => 'Cliente Secreto', 'email' => 'segredo@example.com']);
        $angariador = $this->backofficeUser('angariador', ['referral_code' => 'ANG1']);

        $response = $this->actingAs($angariador)
            ->post(route('admin.angariador.leads.store'), ['name' => 'Lead', 'email' => 'SEGREDO@example.com']);

        $response->assertSessionHasErrors('email');
        $this->assertStringNotContainsString('Secreto', implode(' ', session('errors')->all()));
        $this->assertSame(1, Client::count());
    }

    public function test_pre_lead_joins_an_existing_client_instead_of_duplicating(): void
    {
        $existing = $this->client(['phone' => '912345678']);
        $preLead = PreLead::create(['phone' => '+351912345678', 'name' => 'Maria', 'message' => 'Olá, quero um carro', 'status' => 'pendente']);

        $this->actingAs($this->backofficeUser('admin'))
            ->post(route('admin.v2.pre-leads.approve', $preLead->id), ['name' => 'Maria'])
            ->assertRedirect(route('admin.v2.clients.show', $existing->id));

        $this->assertSame(1, Client::count());
        $this->assertModelMissing($preLead);
        $this->assertSame(1, LeadActivity::where('client_id', $existing->id)->where('title', 'Mensagem de WhatsApp recebida')->count());
    }

    public function test_whatsapp_lookup_uses_the_indexed_phone_key(): void
    {
        $client = $this->client(['phone' => '912 345 678']);

        $this->assertTrue(app(PhoneNumberService::class)->findExistingClient('+351912345678')->is($client));
        $this->assertNull(app(PhoneNumberService::class)->findExistingClient('+351966666666'));
    }

    // ── Junção de duplicados existentes ──────────────────────────────

    public function test_merge_moves_everything_to_the_kept_client(): void
    {
        $keep = $this->client(['name' => 'Maria Silva', 'email' => 'maria@example.com', 'is_lead' => true, 'newsletter_consent' => false]);
        $dup = $this->client(['name' => 'Maria Silva', 'phone' => '912345678', 'city' => 'Porto', 'newsletter_consent' => true, 'is_lead' => false, 'observation' => 'VIP']);
        Proposal::create(['client_id' => $dup->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0]);
        FormProposal::create(['name' => 'Maria', 'email' => 'm@x.pt', 'phone' => '912345678', 'client_id' => $dup->id]);
        LeadActivity::log($dup->id, 'Nota', '', 'bi-circle', 'secondary');
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4', 'client_id' => $dup->id]);

        $merger = app(ClientMergeService::class);

        $preview = $merger->merge($keep, collect([$dup]), apply: false);
        $this->assertSame(1, $preview['moved']['proposals']);
        $this->assertSame(1, $preview['moved']['form_proposals']);
        $this->assertModelExists($dup);
        $this->assertSame(0, Proposal::where('client_id', $keep->id)->count(), 'a simulação não altera nada');

        $merger->merge($keep, collect([$dup]), apply: true);

        $keep->refresh();
        $this->assertModelMissing($dup);
        $this->assertSame(1, Proposal::where('client_id', $keep->id)->count());
        $this->assertSame(1, FormProposal::where('client_id', $keep->id)->count());
        $this->assertSame($keep->id, $vehicle->fresh()->client_id);
        $this->assertSame('912345678', $keep->phone);
        $this->assertSame('912345678', $keep->phone_key);
        $this->assertSame('Porto', $keep->city);
        $this->assertTrue((bool) $keep->newsletter_consent);
        $this->assertFalse($keep->is_lead, 'um cliente continua cliente');
        $this->assertStringContainsString('VIP', $keep->observation);
        $this->assertSame(1, AuditLog::where('action', 'client_merged')->count());
        $this->assertSame(1, LeadActivity::where('client_id', $keep->id)->where('title', 'Registo duplicado unificado')->count());
    }

    public function test_merge_command_simulates_by_default_and_lists_groups(): void
    {
        $a = $this->client(['name' => 'Ana', 'email' => 'ana@example.com']);
        $b = $this->client(['name' => 'Ana', 'email' => 'ANA@example.com']);
        Proposal::create(['client_id' => $b->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0]);

        $this->artisan('clients:duplicates')->expectsOutputToContain('1 grupo(s)')->assertSuccessful();

        $this->artisan('clients:merge', ['keep' => $a->id, 'remove' => [$b->id]])
            ->expectsOutputToContain('Simulação')
            ->assertSuccessful();
        $this->assertModelExists($b);

        $this->artisan('clients:merge', ['keep' => $a->id, 'remove' => [$b->id], '--apply' => true])
            ->expectsConfirmation('Isto elimina os registos duplicados e não se desfaz. Continuar?', 'yes')
            ->assertSuccessful();
        $this->assertModelMissing($b);
        $this->assertSame(1, Proposal::where('client_id', $a->id)->count());
    }

    // ── Movimentos financeiros ───────────────────────────────────────

    private function converted(array $attributes = []): ConvertedProposal
    {
        return ConvertedProposal::create($attributes + [
            'client_id' => $this->client()->id, 'status' => 'Iniciada', 'brand' => 'BMW', 'modelCar' => 'i4',
            'valor_primeira_tranche' => 982.5, 'valor_segunda_tranche' => 982.5,
        ]);
    }

    public function test_received_tranches_become_income_in_the_ledger(): void
    {
        $converted = $this->converted();
        $this->assertSame(0, Expense::count());

        $converted->update(['primeira_tranche_pago' => true]);

        $expense = Expense::sole();
        $this->assertSame('income', $expense->movement_type);
        $this->assertSame('import_service', $expense->category);
        $this->assertEquals(982.5, $expense->amount_gross);
        $this->assertNull($expense->v3_vehicle_id);
        $this->assertSame(now()->toDateString(), $converted->fresh()->primeira_tranche_paga_em->toDateString());

        $movement = FinancialMovement::sole();
        $this->assertSame('income', $movement->type);
        $this->assertSame('Serviço de Importação', $movement->category);
        $this->assertEquals(982.5, $movement->amount_net);

        // Segunda tranche: segundo lançamento; guardar de novo não duplica.
        $converted->update(['segunda_tranche_pago' => true]);
        $converted->update(['observacoes' => 'x']);
        $this->assertSame(2, Expense::count());
        $this->assertSame(2, FinancialMovement::count());

        // Desmarcar apaga o lançamento e a data.
        $converted->update(['primeira_tranche_pago' => false]);
        $this->assertSame(1, Expense::count());
        $this->assertSame(1, FinancialMovement::count());
        $this->assertNull($converted->fresh()->primeira_tranche_paga_em);
    }

    public function test_paid_import_costs_become_expenses_with_their_own_category_but_not_the_car(): void
    {
        $converted = $this->converted([
            'valor_carro' => 30000, 'custo_inspecao_origem' => 350, 'custo_transporte' => 1350, 'custo_ipo' => 100,
            'isv' => 2500, 'custo_imt' => 45, 'custo_matricula' => 20, 'custo_registo_automovel' => 65,
        ]);

        $converted->update([
            'carro_pago' => true, 'inspecao_origem_pago' => true, 'transporte_pago' => true, 'ipo_pago' => true,
            'isv_pago' => true, 'imt_pago' => true, 'matricula_pago_impressa' => true, 'registo_pago' => true,
        ]);

        $byCategory = Expense::all()->groupBy('category')->map(fn ($rows) => $rows->sum('amount_gross'));
        $this->assertEquals([
            'inspection' => 350.0,
            'transport' => 1350.0,
            'legalization' => 230.0, // IPO 100 + IMT 45 + matrícula 20 + registo 65
            'tax' => 2500.0,
        ], $byCategory->all());
        $this->assertSame(7, Expense::count());
        $this->assertSame(['expense'], Expense::pluck('movement_type')->unique()->values()->all());
        $this->assertEquals(0, Expense::where('amount_gross', 30000)->count(), 'o valor do carro não é lançado');
        $this->assertSame(7, FinancialMovement::where('type', 'expense')->count());
        $this->assertStringContainsString('transporte', Expense::where('category', 'transport')->value('title'));

        // Desmarcar um custo retira-o dos movimentos; os outros ficam.
        $converted->update(['transporte_pago' => false]);
        $this->assertSame(6, Expense::count());
        $this->assertSame(0, Expense::where('category', 'transport')->count());
    }

    public function test_a_cost_keeps_its_date_while_it_stays_paid_and_follows_its_amount(): void
    {
        $converted = $this->converted(['custo_transporte' => 1350]);

        \Illuminate\Support\Carbon::setTestNow('2026-10-05 10:00:00');
        $converted->update(['transporte_pago' => true]);
        $this->assertSame('2026-10-05', Expense::sole()->expense_date->toDateString());

        // Guardar de novo noutro dia não mexe na data, mas o valor acompanha.
        \Illuminate\Support\Carbon::setTestNow('2026-10-20 10:00:00');
        $converted->update(['custo_transporte' => 1400, 'observacoes' => 'x']);
        $expense = Expense::sole();
        $this->assertSame('2026-10-05', $expense->expense_date->toDateString());
        $this->assertEquals(1400, $expense->amount_gross);

        // Desmarcar e voltar a marcar: data nova.
        $converted->update(['transporte_pago' => false]);
        $converted->update(['transporte_pago' => true]);
        $this->assertSame('2026-10-20', Expense::sole()->expense_date->toDateString());

        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_saving_the_converted_proposal_form_books_the_costs(): void
    {
        $admin = $this->backofficeUser('admin');
        $converted = $this->converted(['custo_ipo' => 100, 'custo_imt' => 45]);

        $this->actingAs($admin)->put(route('admin.v2.converted-proposals.update', $converted->id), [
            'client_id' => $converted->client_id,
            'custo_ipo' => 100, 'ipo_pago' => '1',
            'custo_imt' => 45, 'imt_pago' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertSame(['IPO'], Expense::all()->map(fn ($e) => explode('— ', $e->title)[1] ?? '')->map(fn ($t) => explode(' (', $t)[0])->all());
    }

    public function test_paid_angariador_commission_becomes_an_expense(): void
    {
        $angariador = $this->backofficeUser('angariador', ['commission_fixed_value' => 100]);
        $converted = $this->converted(['owner_id' => $angariador->id]);

        $converted->update(['comissao_paga' => true, 'comissao_paga_em' => '2026-10-05']);

        $expense = Expense::sole();
        $this->assertSame('expense', $expense->movement_type);
        $this->assertSame('commission', $expense->category);
        $this->assertEquals(100, $expense->amount_gross);
        $this->assertSame('2026-10-05', $expense->expense_date->toDateString());
        $this->assertSame('expense', FinancialMovement::sole()->type);

        $converted->update(['comissao_paga' => false]);
        $this->assertSame(0, Expense::count());
        $this->assertSame(0, FinancialMovement::count());
    }

    public function test_deleting_the_converted_proposal_removes_its_ledger_entries(): void
    {
        $converted = $this->converted(['primeira_tranche_pago' => true]);
        $this->assertSame(1, FinancialMovement::count());

        $converted->delete();

        $this->assertSame(0, Expense::count());
        $this->assertSame(0, FinancialMovement::count());
    }

    public function test_ledger_entries_do_not_count_as_vehicle_costs_and_are_read_only(): void
    {
        $converted = $this->converted();
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4', 'client_id' => $converted->client_id]);
        $converted->update(['v3_vehicle_id' => $vehicle->id, 'primeira_tranche_pago' => true]);

        $this->assertSame(0.0, $vehicle->fresh()->load('expenses')->expenses_total);

        // Lançamento automático: não se edita na página de movimentos.
        $entry = Expense::sole();
        $this->assertNotEmpty($entry->source_type);
        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v2.movements.edit', $entry->id))
            ->assertRedirect(route('admin.v2.movements.index'))
            ->assertSessionHas('error');
    }

    // ── Rotas V1 desligadas ──────────────────────────────────────────

    public function test_duplicated_v1_routes_are_gone_and_the_unique_ones_stay(): void
    {
        foreach (['users.index', 'roles.index', 'settings.index', 'permissions.index', 'clients.index', 'suppliers.index', 'partners.index',
                  'vehicle-attributes.index', 'expenses.index', 'sales.index', 'attribute-groups.index', 'proposals.index',
                  'form_proposals.index', 'converted-proposals.index', 'converted-proposals.updateStatus', 'admin.v2.proposals.matchAttributesAi'] as $name) {
            $this->assertFalse(Route::has($name), "{$name} devia ter sido removida");
        }

        foreach (['brands.index', 'pages.index', 'page-types.index', 'menus.index', 'ad-searches.index', 'car-analysis.index', 'calculator.profit',
                  'profile', 'isv.calcular', 'clients.contractService', 'proposals.detail', 'proposals.accept', 'converted-proposals.timeline',
                  'admin.v2.converted-proposals.update-status'] as $name) {
            $this->assertTrue(Route::has($name), "{$name} devia continuar a existir");
        }

        $this->actingAs($this->backofficeUser('admin'))->get('/gestao/users')->assertNotFound();
        $this->actingAs($this->backofficeUser('admin'))->get('/gestao/clients')->assertNotFound();
    }
}
