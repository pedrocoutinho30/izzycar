<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private Task $task)
    {
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Nova Tarefa — ' . $this->task->title)
            ->icon('/img/logo-arredondado.png')
            ->body($this->task->due_date ? 'Prazo: ' . $this->task->due_date->format('d/m/Y') : 'Sem prazo definido')
            ->tag('task-' . $this->task->id)
            ->data(['url' => route('admin.tasks.show', $this->task->id)]);
    }
}
