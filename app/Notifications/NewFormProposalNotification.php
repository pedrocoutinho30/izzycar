<?php

namespace App\Notifications;

use App\Models\FormProposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewFormProposalNotification extends Notification
{
    use Queueable;

    public function __construct(private FormProposal $formProposal)
    {
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $vehicle = trim(($this->formProposal->brand ?? '') . ' ' . ($this->formProposal->model ?? ''));

        return (new WebPushMessage())
            ->title('Novo Formulário — ' . $this->formProposal->name)
            ->icon('/img/logo-arredondado.png')
            ->body($vehicle !== '' ? $vehicle : 'Pedido de importação')
            ->tag('form-proposal-' . $this->formProposal->id)
            ->data(['url' => route('admin.v2.form-proposals.show', $this->formProposal->id)]);
    }
}
