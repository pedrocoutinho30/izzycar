<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

/**
 * SendTaskReminders
 * 
 * Comando para enviar lembretes de tarefas
 * Executa diariamente às 00:00
 * Envia email para tarefas cuja data de lembrete é hoje
 */
class SendTaskReminders extends Command
{
    /**
     * Nome e assinatura do comando
     *
     * @var string
     */
    protected $signature = 'tasks:send-reminders';

    /**
     * Descrição do comando
     *
     * @var string
     */
    protected $description = 'Envia lembretes por email para tarefas agendadas para hoje';

    /**
     * Executa o comando
     */
    public function handle()
    {
        $this->info('Iniciando envio de lembretes de tarefas...');

        // Buscar tarefas que precisam de lembrete hoje
        $tasks = Task::needsReminderToday()
            ->with(['user', 'vehicle'])
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('Nenhuma tarefa com lembrete para hoje.');
            return 0;
        }

        $this->info("Encontradas {$tasks->count()} tarefa(s) com lembrete para hoje.");

        $sentCount = 0;
        $errorCount = 0;

        // Processar cada tarefa
        foreach ($tasks as $task) {
            try {
                // Determinar destinatário do email
                $recipientEmail = $task->user?->email ?? config('mail.from.address');
                $recipientName = $task->user?->name ?? $task->user_name ?? 'Equipe';

                // Enviar email
                Mail::send('emails.task-reminder', ['task' => $task], function ($message) use ($recipientEmail, $recipientName, $task) {
                    $message->to($recipientEmail, $recipientName)
                            ->subject("Lembrete: {$task->title}");
                });

                // Marcar lembrete como enviado
                $task->update(['reminder_sent' => true]);

                $this->info("✓ Lembrete enviado para: {$recipientName} ({$recipientEmail})");
                $sentCount++;

            } catch (\Exception $e) {
                $this->error("✗ Erro ao enviar lembrete da tarefa #{$task->id}: {$e->getMessage()}");
                $errorCount++;
            }
        }

        // Resumo final
        $this->info("\n" . str_repeat('=', 50));
        $this->info("Resumo do envio de lembretes:");
        $this->info("Total de tarefas: {$tasks->count()}");
        $this->info("Enviados com sucesso: {$sentCount}");
        $this->info("Erros: {$errorCount}");
        $this->info(str_repeat('=', 50));

        return 0;
    }
}
