<?php

namespace App\Mail;

use App\Models\Legalization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class LegalizationTrackingMail extends Mailable
{
    use Queueable, SerializesModels;

    public Legalization $legalization;
    public string $clientName;
    public string $trackingUrl;

    public function __construct(Legalization $legalization, string $clientName, string $trackingUrl)
    {
        $this->legalization = $legalization;
        $this->clientName    = $clientName;
        $this->trackingUrl   = $trackingUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('geral@izzycar.pt', 'Izzycar - Importação Automóvel'),
            subject: __('legalization.email_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.legalization_tracking',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
