<?php

namespace App\Notifications;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewLeadNotification extends Notification
{
    use Queueable;

    public function __construct(private Client $lead)
    {
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Nova Lead — ' . $this->lead->name)
            ->icon('/img/logo-arredondado.png')
            ->body('Origem: ' . ($this->lead->lead_source ?? 'manual'))
            ->tag('lead-' . $this->lead->id)
            ->data(['url' => route('admin.v2.leads.show', $this->lead->id)]);
    }
}
