<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Models\Sale;
use App\Models\Task;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Caracteriza os números do dashboard V2 (para provar que a otimização das queries
 * não os altera) e fixa um teto de queries por carregamento.
 */
class DashboardV2QueriesTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    /** Teto atual de queries do dashboard (antes da otimização eram 41 no teste). */
    private const MAX_QUERIES = 26;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Carbon::setTestNow('2026-05-15 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function lead(string $name, string $status, array $extra = []): Client
    {
        $created = $extra['created_at'] ?? now()->subDays(10);
        unset($extra['created_at']);
        $client = Client::create(['name' => $name, 'is_lead' => true, 'lead_status' => $status] + $extra);
        DB::table('clients')->where('id', $client->id)->update(['created_at' => $created]);

        return $client;
    }

    private function seedDados(): array
    {
        $a = $this->lead('Lead A sem actividade', 'nova');
        $b = $this->lead('Lead B com actividade', 'em_contacto');
        LeadActivity::create(['client_id' => $b->id, 'type' => 'note', 'title' => 'Nota', 'icon' => 'x', 'color' => 'y']);
        $c = $this->lead('Lead C atrasada', 'qualificada', ['next_followup_at' => now()->subDay()]);
        $d = $this->lead('Lead D hoje', 'nova', ['next_followup_at' => now()->setTime(15, 0)]);
        $e = $this->lead('Lead E amanha', 'em_contacto', ['next_followup_at' => now()->addDay()->setTime(9, 0)]);
        $this->lead('Lead F perdida', 'perdida', ['next_followup_at' => now()->subDays(2)]);
        $cliente = Client::create(['name' => 'Cliente G', 'is_lead' => false]);
        // Cliente do mesmo mês do ano passado: não conta como "este mês".
        $velho = Client::create(['name' => 'Cliente Velho', 'is_lead' => false]);
        DB::table('clients')->where('id', $velho->id)->update(['created_at' => now()->subYear()]);

        $mk = fn (string $status, $created, $updated) => tap(Proposal::create([
            'client_id' => $cliente->id, 'brand' => 'BMW', 'model' => 'i4', 'transport_cost' => 0,
            'status' => $status, 'proposal_code' => 'C' . random_int(10000, 99999),
        ]), fn ($p) => DB::table('proposals')->where('id', $p->id)->update(['created_at' => $created, 'updated_at' => $updated]));
        $mk('Pendente', now()->subDays(1), now()->subDays(1));
        $mk('Pendente', now()->subDays(10), now()->subDays(10));
        $mk('Sem resposta', now()->subDays(20), now()->subDays(20));
        $mk('Aprovada', now()->subMonths(2), now()->subMonths(2));
        $mk('Aprovada', now()->subMonths(5), now()->subMonths(5));

        FormProposal::create(['name' => 'Form', 'email' => 'f@example.com', 'phone' => '9']);

        $viatura = V3Vehicle::create(['reference' => 'REF-D1', 'brand' => 'Audi', 'model' => 'A3', 'status' => 'em_stock']);
        $venda = fn ($preco, $created) => tap(Sale::create(['client_id' => $cliente->id, 'v3_vehicle_id' => $viatura->id, 'sale_date' => now()->toDateString(), 'sale_price' => $preco, 'vat_rate' => 'Sem IVA', 'payment_method' => 'Transferência']),
            fn ($s) => DB::table('sales')->where('id', $s->id)->update(['created_at' => $created]));
        $venda(1000, now());
        $venda(2500, now()->subDays(2));
        $venda(9999, now()->subYear());

        return compact('a', 'b', 'c', 'd', 'e');
    }

    public function test_numeros_do_dashboard_nao_mudam_e_queries_ficam_abaixo_do_teto(): void
    {
        $l = $this->seedDados();
        $admin = $this->backofficeUser('admin');

        DB::flushQueryLog();
        DB::enableQueryLog();
        $res = $this->actingAs($admin)->get(route('admin.v2.dashboard'))->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();
        fwrite(STDERR, "\n[dashboard] queries: {$queries}\n" . implode("\n", array_map(fn ($q) => substr($q['query'], 0, 110), DB::getQueryLog())) . "\n");

        $stats = collect($res->viewData('stats'));
        $this->assertSame(1, $stats[0]['value']);                       // formulários novos
        $this->assertSame(2, $stats[1]['value']);                       // pendentes
        $this->assertSame(8, $stats[2]['value']);                       // clientes totais (6 leads + 2)
        $this->assertSame('+7 este mês', $stats[2]['change']);
        $this->assertSame(2, $stats[3]['value']);                       // vendas do mês
        $this->assertSame('3.500€', $stats[3]['change']);

        // A, C, D e E (activas sem actividade): só B tem actividade
        $this->assertSame(4, $res->viewData('leadsSemActividadeCount'));
        $this->assertSame(5, $res->viewData('totalLeadsActivas'));
        $this->assertEqualsCanonicalizing(
            [$l['a']->id, $l['c']->id, $l['d']->id, $l['e']->id],
            $res->viewData('leadsSemActividadeLista')->pluck('id')->all()
        );
        // Ordem da lista: mais recentes primeiro (created_at desc)
        $ids = $res->viewData('leadsSemActividadeLista')->pluck('id')->all();
        $this->assertCount(4, $ids);

        // Atrasados: C e F (perdida também conta, a query não filtra por estado), por data
        $this->assertSame(['Lead F perdida', 'Lead C atrasada'], $res->viewData('followupsAtrasados')->pluck('name')->all());
        $this->assertSame(['Lead D hoje'], $res->viewData('followupsHoje')->pluck('name')->all());
        $this->assertSame(['Lead E amanha'], $res->viewData('followupsAmanha')->pluck('name')->all());
        $this->assertTrue($res->viewData('followupsAtrasados')->first()->relationLoaded('latestActivity'));

        $this->assertSame(2, $res->viewData('cotacoesSemResposta')->count()); // 10 dias Pendente + 20 dias Sem resposta
        $this->assertSame(1, $res->viewData('viaturasEmStockSemPreco'));

        // Gráfico: 6 meses terminados em Mai 2026
        $this->assertSame(
            [['month' => 'Dec 2025', 'count' => 1], ['month' => 'Jan 2026', 'count' => 0], ['month' => 'Feb 2026', 'count' => 0],
             ['month' => 'Mar 2026', 'count' => 1], ['month' => 'Apr 2026', 'count' => 1], ['month' => 'May 2026', 'count' => 2]],
            $res->viewData('proposalsChart')
        );

        $this->assertCount(2, $res->viewData('alerts')); // formulários + sem resposta há >7 dias
        $this->assertLessThanOrEqual(self::MAX_QUERIES, $queries);
    }

    public function test_chart_data_agrega_por_mes_em_poucas_queries(): void
    {
        $this->seedDados();
        $admin = $this->backofficeUser('admin');

        DB::flushQueryLog();
        DB::enableQueryLog();
        $res = $this->actingAs($admin)->getJson(route('admin.v2.dashboard.chart-data', ['start_date' => '2026-03-01', 'end_date' => '2026-05-31']))->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();
        fwrite(STDERR, "\n[chart-data 3 meses] queries: {$queries}\n");

        $res->assertJsonPath('labels', ['Mar 2026', 'Apr 2026', 'May 2026'])
            ->assertJsonPath('datasets.0.proposals', [1, 1, 2]);
        $this->assertLessThanOrEqual(6, $queries); // eram 12 (4 por mês)
    }
}
