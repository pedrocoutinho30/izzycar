<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonthlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Carbon $period,
        public readonly array  $data,
        public readonly string $pdfPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Relatório Mensal Izzycar — ' . $this->period->locale('pt_PT')->translatedFormat('F Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.monthly-report',
            with: [
                'period' => $this->period,
                'data'   => $this->data,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as('Relatorio_Mensal_' . $this->period->format('Y_m') . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
