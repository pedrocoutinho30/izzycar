<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnualReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array  $data,
        public readonly string $pdfPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Relatório Anual Izzycar — {$this->data['year']}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.annual-report',
            with: ['data' => $this->data],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as("Relatorio_Anual_{$this->data['year']}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
