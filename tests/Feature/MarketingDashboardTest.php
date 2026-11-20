<?php

namespace Tests\Feature;

use App\Models\MarketingPost;
use App\Services\MarketingDashboardStats;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Dashboard do Marketing Studio: só contagens reais, nada fictício. */
class MarketingDashboardTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function novo(string $type, string $status, array $extra = []): MarketingPost
    {
        return MarketingPost::create($extra + [
            'type' => $type, 'template' => 'premium', 'format' => 'feed_square', 'status' => $status,
            'title' => "{$type} {$status}", 'data' => [], 'config' => [],
        ]);
    }

    public function test_vazio_mostra_zeros_e_estado_vazio(): void
    {
        $s = (new MarketingDashboardStats)->build();

        $this->assertSame(0, $s['total']);
        $this->assertSame(0, $s['by_status']['rascunho']);
        $this->assertSame(0, $s['by_type']['veiculo']);
        $this->assertSame(0, $s['published_this_month']);
        $this->assertCount(0, $s['recent']);

        $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.dashboard'))
            ->assertOk()->assertSee('Ainda não há publicações');
    }

    public function test_conta_por_estado_e_por_tipo(): void
    {
        $this->novo('veiculo', 'rascunho');
        $this->novo('veiculo', 'rascunho');
        $this->novo('educativo', 'aprovado');
        $this->novo('testemunho', 'publicado', ['published_at' => now()]);
        $this->novo('servico', 'publicado', ['published_at' => now()->subMonths(2)]);

        $s = (new MarketingDashboardStats)->build();

        $this->assertSame(5, $s['total']);
        $this->assertSame(2, $s['by_status']['rascunho']);
        $this->assertSame(1, $s['by_status']['aprovado']);
        $this->assertSame(2, $s['by_status']['publicado']);
        $this->assertSame(0, $s['by_status']['agendado']);
        $this->assertSame(2, $s['by_type']['veiculo']);
        $this->assertSame(1, $s['by_type']['educativo']);
        $this->assertSame(1, $s['published_this_month']);
    }

    public function test_prontas_a_publicar_so_aprovadas_ou_agendadas_e_ordenadas(): void
    {
        $this->novo('veiculo', 'rascunho', ['title' => 'Rascunho X']);
        $this->novo('veiculo', 'aprovado', ['title' => 'Aprovado sem data']);
        $this->novo('veiculo', 'agendado', ['title' => 'Agendado tarde', 'scheduled_for' => now()->addDays(5)]);
        $this->novo('veiculo', 'agendado', ['title' => 'Agendado cedo', 'scheduled_for' => now()->addDay()]);

        $titles = (new MarketingDashboardStats)->build()['ready']->pluck('title')->all();

        $this->assertSame(['Agendado cedo', 'Agendado tarde', 'Aprovado sem data'], $titles);
    }

    public function test_recentes_limitadas_e_ordenadas(): void
    {
        foreach (range(1, 8) as $i) {
            $p = $this->novo('veiculo', 'rascunho', ['title' => "P{$i}"]);
            \DB::table('marketing_posts')->where('id', $p->id)->update(['updated_at' => now()->subMinutes(10 - $i)]);
        }

        $recent = (new MarketingDashboardStats)->build()['recent'];

        $this->assertCount(6, $recent);
        $this->assertSame('P8', $recent->first()->title);
    }

    public function test_pagina_mostra_numeros_e_links(): void
    {
        $this->novo('educativo', 'em_revisao', ['title' => 'Em revisão A']);

        $this->actingAs($this->backofficeUser('admin'))->get(route('admin.marketing.dashboard'))
            ->assertOk()->assertSee('Em revisão A')->assertSee('Em revisão')->assertSee('Educativo');
    }

    public function test_angariador_nao_acede(): void
    {
        $this->actingAs($this->backofficeUser('angariador'))->get(route('admin.marketing.dashboard'))->assertForbidden();
    }
}
