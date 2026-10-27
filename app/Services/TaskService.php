<?php

namespace App\Services;

use App\Models\Task;
use App\Notifications\TaskAssignedNotification;

/**
 * Regras das tarefas, partilhadas pelo desktop (Admin\TaskController) e pelo
 * mobile (Admin\Mobile\TaskController): validação, criar, atualizar e mudar
 * o estado. Não há recorrência; só a atribuição envia notificação.
 */
class TaskService
{
    public const STATUS_ABERTOS = ['pendente', 'em_progresso'];

    /**
     * @param  bool  $strictIds  ids só inteiros (mobile): impede user_id[]=1, que o "exists" aceitaria.
     * @return array<string, string>
     */
    public function rules(bool $strictIds = false): array
    {
        $id = $strictIds ? 'integer|' : '';

        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'reminder_date' => 'nullable|date|before_or_equal:due_date',
            'user_id' => 'nullable|' . $id . 'exists:users,id',
            'user_name' => 'nullable|string|max:255',
            'v3_vehicle_id' => 'nullable|' . $id . 'exists:v3_vehicles,id',
            'status' => 'required|in:pendente,em_progresso,concluida,cancelada',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'due_date.required' => 'A data limite é obrigatória.',
            'reminder_date.before_or_equal' => 'A data do lembrete deve ser anterior ou igual à data limite.',
            'status.required' => 'O estado é obrigatório.',
        ];
    }

    /** Cria a tarefa e notifica quem foi atribuído (não a si próprio, se se auto-atribui). */
    public function create(array $data, $actorId): Task
    {
        $task = Task::create($data);

        if ($task->user_id && $task->user_id !== $actorId) {
            $task->user?->notify(new TaskAssignedNotification($task));
        }

        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task;
    }

    public function setStatus(Task $task, string $status): Task
    {
        $task->update(['status' => $status]);

        return $task;
    }

    /** Conclui a tarefa: no desktop é só mudar o estado (sem recorrência nem avisos). */
    public function complete(Task $task): Task
    {
        return $this->setStatus($task, 'concluida');
    }
}
