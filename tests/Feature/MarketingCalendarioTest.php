<?php

namespace Tests\Feature;

use App\Models\MarketingPost;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Agendamento interno e calendário editorial (sem publicação automática). */
class MarketingCalendarioTest extends TestCase
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

    private function novo(string $title, ?string $when, string $status = 'rascunho', ?string $channel = 'instagram'): MarketingPost
    {
        return MarketingPost::create([
            'type' => 'educativo', 'template' => 'dica-rapida', 'format' => 'feed_square', 'status' => $status, 'title' => $title,
            'data' => ['title' => $title, 'items' => []], 'config' => [], 'scheduled_for' => $when, 'channel' => $channel,
        ]);
    }

    private function guardar(MarketingPost $p, array $extra)
    {
        return $this->admin()->putJson(route('admin.marketing.update', $p), array_merge(['template' => $p->template, 'format' => $p->format->value], $extra));
    }

    public function test_agendar_guarda_data_e_rede(): void
    {
        $p = $this->novo('A', null);

        $this->guardar($p, ['scheduled_for' => '2026-12-10T18:30', 'channel' => 'facebook'])->assertOk();

        $p->refresh();
        $this->assertSame('2026-12-10 18:30', $p->scheduled_for->format('Y-m-d H:i'));
        $this->assertSame('facebook', $p->channel);
        $this->assertSame('rascunho', $p->status->value); // só aprovado passa a agendado
    }

    public function test_aprovado_com_data_passa_a_agendado_e_sem_data_volta(): void
    {
        $p = $this->novo('A', null, 'aprovado');

        $this->guardar($p, ['scheduled_for' => '2026-12-10T10:00'])->assertOk()->assertJson(['status' => 'agendado']);
        $this->assertSame('agendado', $p->fresh()->status->value);

        $this->guardar($p, ['scheduled_for' => null])->assertOk()->assertJson(['status' => 'aprovado']);
        $p->refresh();
        $this->assertSame('aprovado', $p->status->value);
        $this->assertNull($p->scheduled_for);
    }

    public function test_agendar_nunca_marca_como_publicado(): void
    {
        $p = $this->novo('A', null, 'aprovado');
        $this->guardar($p, ['scheduled_for' => '2020-01-01T10:00'])->assertOk();

        $p->refresh();
        $this->assertSame('agendado', $p->status->value);
        $this->assertNull($p->published_at);
    }

    public function test_rede_e_data_invalidas_dao_422(): void
    {
        $p = $this->novo('A', null);
        $this->guardar($p, ['channel' => 'tiktok'])->assertUnprocessable()->assertJsonValidationErrors('channel');
        $this->guardar($p, ['scheduled_for' => 'amanhã'])->assertUnprocessable()->assertJsonValidationErrors('scheduled_for');
    }

    public function test_calendario_mensal_mostra_so_o_mes(): void
    {
        $this->novo('Dez-10', '2026-12-10 12:00');
        $this->novo('Jan-05', '2027-01-05 12:00');
        $this->novo('Sem data', null);

        $this->admin()->get(route('admin.marketing.calendar', ['view' => 'month', 'date' => '2026-12-15']))
            ->assertOk()->assertSee('Dez-10')->assertDontSee('Jan-05')->assertDontSee('Sem data')->assertSee('Dezembro 2026');
    }

    public function test_calendario_semanal_mostra_so_a_semana(): void
    {
        $this->novo('Seg', '2026-12-14 12:00'); // semana de 14 a 20
        $this->novo('Dom-seguinte', '2026-12-21 12:00');
        $this->novo('Sab-anterior', '2026-12-12 12:00');

        $this->admin()->get(route('admin.marketing.calendar', ['view' => 'week', 'date' => '2026-12-17']))
            ->assertOk()->assertSee('Seg')->assertDontSee('Dom-seguinte')->assertDontSee('Sab-anterior');
    }

    public function test_filtro_por_rede_inclui_ambos(): void
    {
        $this->novo('Só insta', '2026-12-10 12:00', 'rascunho', 'instagram');
        $this->novo('Só face', '2026-12-11 12:00', 'rascunho', 'facebook');
        $this->novo('Os dois', '2026-12-12 12:00', 'rascunho', 'ambos');

        $this->admin()->get(route('admin.marketing.calendar', ['date' => '2026-12-15', 'channel' => 'instagram']))
            ->assertOk()->assertSee('Só insta')->assertSee('Os dois')->assertDontSee('Só face');
    }

    public function test_data_invalida_usa_hoje_sem_erro(): void
    {
        $this->admin()->get(route('admin.marketing.calendar', ['date' => 'lixo', 'view' => 'x']))->assertOk();
    }

    public function test_angariador_nao_acede(): void
    {
        $this->actingAs($this->backofficeUser('angariador'))->get(route('admin.marketing.calendar'))->assertForbidden();
    }

    public function test_editor_mostra_agendamento(): void
    {
        $p = $this->novo('A', '2026-12-10 18:30');
        $this->admin()->get(route('admin.marketing.editor', $p))->assertOk()->assertSee('2026-12-10T18:30', false)->assertSee('Agendamento');
    }

    public function test_navegacao_de_mes_nao_salta_em_dia_31(): void
    {
        $r = $this->admin()->get(route('admin.marketing.calendar', ['date' => '2026-03-31']));
        $this->assertStringContainsString('date=2026-02-28', urldecode($r->getContent()));

        $r = $this->admin()->get(route('admin.marketing.calendar', ['date' => '2026-01-31']));
        $this->assertStringContainsString('date=2026-02-28', urldecode($r->getContent()));
    }
}
