<?php

namespace App\Mail;

use App\Support\NewsletterLinks;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Pedido de confirmação (double opt-in) da subscrição da newsletter feita no rodapé. */
class NewsletterConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public string $confirmUrl;

    public function __construct(public string $recipientName, int $clientId)
    {
        $this->confirmUrl = NewsletterLinks::confirm($clientId);
    }

    public function build(): self
    {
        return $this->subject('Confirme a subscrição da newsletter Izzycar')
            ->view('emails.newsletter-confirmation');
    }
}
