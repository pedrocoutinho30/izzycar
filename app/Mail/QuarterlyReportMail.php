<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuarterlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array  $data,
        public readonly string $pdfPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Relatório Trimestral Izzycar — {$this->data['label']}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quarterly-report',
            with: ['data' => $this->data],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as("Relatorio_Trimestral_{$this->data['year']}_Q{$this->data['quarter']}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
