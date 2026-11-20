<?php

namespace App\Services;

use App\Enums\MarketingPostStatus;
use App\Marketing\TextPostData;
use App\Marketing\ThirtyDayPlan;
use App\Models\MarketingPost;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/** Cria os 30 rascunhos do plano editorial no calendário. Não publica nem escreve legendas. */
class MarketingPlanGenerator
{
    /** @throws \DomainException se já existe um plano com esta data de início */
    public function generate(CarbonImmutable $start, int $userId): int
    {
        // Lock para duplo clique / dois utilizadores em simultâneo.
        $lock = \Illuminate\Support\Facades\Cache::lock('marketing-plan-generate', 30);
        if (! $lock->get()) {
            throw new \DomainException('Já está a ser gerado um plano. Tenta novamente daqui a pouco.');
        }
        try {
            return $this->doGenerate($start, $userId);
        } finally {
            $lock->release();
        }
    }

    private function doGenerate(CarbonImmutable $start, int $userId): int
    {
        $startKey = $start->toDateString();
        if (MarketingPost::where('data->plan_start', $startKey)->exists()) {
            throw new \DomainException("Já existe um plano de 30 dias a começar em {$start->format('d/m/Y')}.");
        }

        return DB::transaction(function () use ($start, $startKey, $userId) {
            foreach (ThirtyDayPlan::entries($start) as $day => $e) {
                $data = $e['pending']
                    ? ['title' => $e['title'], 'pending_selection' => true, 'legal_check' => false]
                    : TextPostData::fromInput(['title' => $e['title'], 'topic' => $e['topic'], 'legal_check' => $e['legal_check']]);

                MarketingPost::create([
                    'type' => $e['type'],
                    'template' => $e['template'],
                    'format' => $e['format'],
                    'status' => MarketingPostStatus::Rascunho,
                    'title' => $e['title'],
                    'data' => $data + ['plan_start' => $startKey, 'plan_day' => $day + 1],
                    'config' => [],
                    'scheduled_for' => $e['date'],
                    'channel' => 'ambos',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            return ThirtyDayPlan::DAYS;
        });
    }
}
