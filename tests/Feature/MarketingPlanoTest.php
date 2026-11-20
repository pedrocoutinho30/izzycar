<?php

namespace Tests\Feature;

use App\Marketing\ThirtyDayPlan;
use App\Models\MarketingPost;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Plano de 30 dias: rascunhos no calendário; conteúdo real por selecionar, nada inventado. */
class MarketingPlanoTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function admin()
    {
        return $this->actingAs($this->backofficeUser('admin'));
    }

    public function test_plano_tem_30_entradas_em_dias_consecutivos(): void
    {
        $e = ThirtyDayPlan::entries(CarbonImmutable::parse('2026-12-01'));

        $this->assertCount(30, $e);
        foreach ($e as $i => $x) {
            $this->assertSame(CarbonImmutable::parse('2026-12-01')->addDays($i)->toDateString(), $x['date']->toDateString());
            $this->assertSame('12:00', $x['date']->format('H:i'));
        }
    }

    public function test_temas_vem_da_biblioteca_e_nao_se_repetem(): void
    {
        $e = collect(ThirtyDayPlan::entries(CarbonImmutable::parse('2026-12-01')));
        $keys = collect(config('marketing.topics'))->keyBy('key');

        $comTema = $e->whereNotNull('topic');
        $this->assertNotEmpty($comTema);
        foreach ($comTema as $x) {
            $this->assertTrue($keys->has($x['topic']));
            $this->assertSame($keys[$x['topic']]['type'], $x['type']);
            $this->assertSame($keys[$x['topic']]['legal_check'], $x['legal_check']);
        }
        $this->assertSame($comTema->count(), $comTema->pluck('topic')->unique()->count());
    }

    public function test_tipos_que_precisam_de_fonte_ficam_pendentes(): void
    {
        foreach (ThirtyDayPlan::entries(CarbonImmutable::parse('2026-12-01')) as $x) {
            if (in_array($x['type'], ['veiculo', 'testemunho', 'importacao', 'recomendacao'], true)) {
                $this->assertTrue($x['pending'], $x['type']);
                $this->assertNull($x['topic']);
            } else {
                $this->assertFalse($x['pending']);
            }
        }
    }

    public function test_gerar_cria_30_rascunhos_agendados_sem_legenda(): void
    {
        $this->admin()->post(route('admin.marketing.plan.generate'), ['start_date' => '2026-12-01'])
            ->assertRedirect(route('admin.marketing.calendar', ['date' => '2026-12-01', 'view' => 'month']))
            ->assertSessionHas('success');

        $this->assertSame(30, MarketingPost::count());
        foreach (MarketingPost::all() as $p) {
            $this->assertSame('rascunho', $p->status->value);
            $this->assertNotNull($p->scheduled_for);
            $this->assertSame('ambos', $p->channel);
            $this->assertNull($p->caption);
            $this->assertNull($p->published_at);
            $this->assertNull($p->source_type);
            $this->assertSame('2026-12-01', $p->data['plan_start']);
        }
        $this->assertTrue(MarketingPost::all()->contains(fn ($p) => ($p->data['legal_check'] ?? false) === true));
    }

    public function test_gerar_duas_vezes_o_mesmo_plano_e_recusado(): void
    {
        $this->admin()->post(route('admin.marketing.plan.generate'), ['start_date' => '2026-12-01']);
        $this->admin()->post(route('admin.marketing.plan.generate'), ['start_date' => '2026-12-01'])->assertSessionHas('error');

        $this->assertSame(30, MarketingPost::count());
    }

    public function test_data_de_inicio_obrigatoria_e_valida(): void
    {
        $this->admin()->post(route('admin.marketing.plan.generate'), [])->assertSessionHasErrors('start_date');
        $this->admin()->post(route('admin.marketing.plan.generate'), ['start_date' => 'xx'])->assertSessionHasErrors('start_date');
        $this->assertSame(0, MarketingPost::count());
    }

    public function test_marcador_pendente_nao_exporta_nem_gera_legenda(): void
    {
        $this->admin()->post(route('admin.marketing.plan.generate'), ['start_date' => '2026-12-01']);
        $pendente = MarketingPost::all()->first(fn ($p) => $p->data['pending_selection'] ?? false);

        $this->admin()->get(route('admin.marketing.editor', $pendente))->assertOk()->assertSee('ainda não tem conteúdo real');
        $this->admin()->post(route('admin.marketing.duplicate', $pendente))->assertSessionHas('error');
    }

    public function test_marcador_pendente_nao_aprova_nao_publica_e_nao_gera_legenda_no_servidor(): void
    {
        $this->admin()->post(route('admin.marketing.plan.generate'), ['start_date' => '2026-12-01']);
        $pendente = MarketingPost::all()->first(fn ($p) => $p->data['pending_selection'] ?? false);

        $this->admin()->putJson(route('admin.marketing.update', $pendente), ['template' => $pendente->template, 'format' => $pendente->format->value, 'status' => 'aprovado'])->assertStatus(422);
        $this->admin()->post(route('admin.marketing.mark-published', $pendente))->assertSessionHas('error');
        $this->admin()->postJson(route('admin.marketing.caption', $pendente), ['tone' => 'profissional'])->assertStatus(422);
        $this->assertSame('rascunho', $pendente->fresh()->status->value);
    }

    public function test_duplicar_nao_copia_a_pertenca_ao_plano(): void
    {
        $this->admin()->post(route('admin.marketing.plan.generate'), ['start_date' => '2026-12-01']);
        $real = MarketingPost::all()->first(fn ($p) => ! ($p->data['pending_selection'] ?? false));

        $this->admin()->post(route('admin.marketing.duplicate', $real));

        $copia = MarketingPost::latest('id')->first();
        $this->assertArrayNotHasKey('plan_start', $copia->data);
        $this->assertArrayNotHasKey('plan_day', $copia->data);
    }

    public function test_calendario_mostra_o_plano_e_o_aviso_de_pendentes(): void
    {
        $this->admin()->post(route('admin.marketing.plan.generate'), ['start_date' => '2026-12-01']);

        $this->admin()->get(route('admin.marketing.calendar', ['date' => '2026-12-10']))->assertOk()->assertSee('por selecionar');
    }

    public function test_angariador_nao_gera(): void
    {
        $this->actingAs($this->backofficeUser('angariador'))->post(route('admin.marketing.plan.generate'), ['start_date' => '2026-12-01'])->assertForbidden();
        $this->assertSame(0, MarketingPost::count());
    }
}
