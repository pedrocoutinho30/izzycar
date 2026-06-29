<?php

namespace App\Mail;

use App\Models\FormProposal;
use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ImportFormConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public FormProposal $proposal;
    public Client $client;

    public function __construct(FormProposal $proposal, Client $client)
    {
        $this->proposal = $proposal;
        $this->client   = $client;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('geral@izzycar.pt', 'Izzycar - Importação Automóvel'),
            subject: 'Recebemos o seu pedido de importação — Izzycar',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.import_form_confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
