<?php

namespace Tests\Feature;

use App\Models\ConvertedProposal;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class DashboardConvertedProposalsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_cancelled_converted_proposals_are_not_counted(): void
    {
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        // Em sqlite o status é o enum da migration (só "Cancelado"); em
        // produção é varchar e o V2 grava "Cancelada" — ver o último assert.
        foreach (['Iniciada', 'Transporte', 'Cancelado'] as $status) {
            ConvertedProposal::create(['status' => $status]);
        }

        $month = now()->format('Y-m-d');
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)
            ->getJson(route('admin.v2.dashboard.chart-data', ['start_date' => $month, 'end_date' => $month]))
            ->assertOk()
            ->assertJsonPath('datasets.0.convertedProposals', [2]);

        $this->actingAs($admin)
            ->getJson(route('admin.v2.dashboard.chart-data', [
                'mode' => 'compare',
                'start_date' => $month, 'end_date' => $month,
                'compare_start_date' => $month, 'compare_end_date' => $month,
            ]))
            ->assertOk()
            ->assertJsonPath('datasets.0.convertedProposals', [2])
            ->assertJsonPath('datasets.1.convertedProposals', [2]);

        $this->assertEqualsCanonicalizing(['Cancelada', 'Cancelado'], ConvertedProposal::notCancelled()->getBindings());
    }
}
