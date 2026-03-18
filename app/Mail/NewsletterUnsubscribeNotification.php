<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterUnsubscribeNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $clientEmail;
    public $clientName;
    public $unsubscribeDate;

    /**
     * Create a new message instance.
     */
    public function __construct($clientEmail, $clientName)
    {
        $this->clientEmail = $clientEmail;
        $this->clientName = $clientName;
        $this->unsubscribeDate = now()->format('d/m/Y H:i:s');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Cancelamento de Newsletter - ' . $this->clientEmail)
            ->view('emails.newsletter-unsubscribe-notification');
    }
}
