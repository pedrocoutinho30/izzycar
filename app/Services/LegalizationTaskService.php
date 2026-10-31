<?php

namespace App\Services;

use App\Models\Legalization;
use App\Models\Task;

/**
 * Tarefa automática do passo 5 da legalização (entregar o Modelo 9 no IMT).
 *
 * Fonte única para qualquer ponto que conclua o passo 5 (kanban/lista de legalizações,
 * separador Legalização da viatura V3). É idempotente: não cria nova tarefa se já existir
 * uma em aberto (pendente ou em progresso) para o mesmo processo, por isso desmarcar e voltar
 * a marcar o passo não duplica.
 */
class LegalizationTaskService
{
    private const ABERTAS = ['pendente', 'em_progresso'];

    /** Devolve a tarefa criada, ou null se já existia uma em aberto. */
    public function ensureModelo9Task(Legalization $legalization): ?Task
    {
        if ($this->openModelo9Task($legalization)) {
            return null;
        }

        $legalization->loadMissing('client');

        $clienteNome = $legalization->client?->name ?? 'Cliente sem nome';
        $matricula   = $legalization->matricula ?: 'matrícula não registada';
        $veiculo     = trim($legalization->marca . ' ' . $legalization->modelo);

        return Task::create([
            'title'         => 'Entregar Modelo 9 IMT — ' . $veiculo,
            'description'   => self::marcador($legalization) . " do veículo {$veiculo} ({$matricula}), do cliente {$clienteNome}.",
            'due_date'      => now()->addDays(30)->toDateString(),
            'reminder_date' => now()->addDays(15)->toDateString(),
            'v3_vehicle_id' => $legalization->v3_vehicle_id,
            'status'        => 'pendente',
        ]);
    }

    public function openModelo9Task(Legalization $legalization): ?Task
    {
        return Task::whereIn('status', self::ABERTAS)
            ->where('title', 'like', 'Entregar Modelo 9 IMT%')
            ->where('description', 'like', '%' . self::marcador($legalization) . ' %')
            ->first();
    }

    /** Texto fixo que liga a tarefa ao processo (o espaço final evita confundir #12 com #123). */
    private static function marcador(Legalization $legalization): string
    {
        return "Entregar o Modelo 9 no IMT referente ao processo de legalização #{$legalization->id}";
    }
}
