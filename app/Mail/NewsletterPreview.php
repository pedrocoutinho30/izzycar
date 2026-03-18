<?php

namespace App\Mail;

use App\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class NewsletterPreview extends Mailable
{
    use Queueable, SerializesModels;

    public $newsletter;
    public $previewClient;

    /**
     * Create a new message instance.
     */
    public function __construct(Newsletter $newsletter, array $previewClient = [])
    {
        $this->newsletter = $newsletter;
        $this->previewClient = $previewClient ?: [
            'email' => 'preview@izzycar.pt',
            'name' => 'Preview User'
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('geral@izzycar.pt', 'Izzycar - Importação automóvel'),
            subject: 'Recomendações de importação',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter-preview',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
