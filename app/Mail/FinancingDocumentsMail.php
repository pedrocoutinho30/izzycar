<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\FormProposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FinancingDocumentsMail extends Mailable
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
            subject: 'Documentação para o Processo de Financiamento — Izzycar',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.financing_documents',
        );
    }

    public function attachments(): array
    {
        $files = [
            storage_path('app/documents/financiamento/IzzyCar_Documentacao_Credito_Pessoal.pdf'),
            storage_path('app/documents/financiamento/RGPD.pdf'),
        ];

        $attachments = [];
        foreach ($files as $path) {
            if (file_exists($path)) {
                $attachments[] = Attachment::fromPath($path)->withMime('application/pdf');
            } else {
                report(new \RuntimeException("FinancingDocumentsMail: ficheiro em falta — {$path}"));
            }
        }

        return $attachments;
    }
}
