<?php

namespace App\Notifications;

use App\Models\CostSimulator;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewCostSimulatorNotification extends Notification
{
    use Queueable;

    public function __construct(private CostSimulator $simulator)
    {
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $vehicle = trim(($this->simulator->brand ?? '') . ' ' . ($this->simulator->model ?? ''));

        return (new WebPushMessage())
            ->title('Nova Simulação de Custos')
            ->icon('/img/logo-arredondado.png')
            ->body($vehicle !== '' ? $vehicle : 'Simulação de importação')
            ->tag('cost-simulator-' . $this->simulator->id)
            ->data(['url' => route('admin.v2.cost-simulators.show', $this->simulator->id)]);
    }
}
