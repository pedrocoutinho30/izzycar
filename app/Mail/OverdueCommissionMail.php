<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OverdueCommissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $convertedProposals;

    public function __construct(Collection $convertedProposals)
    {
        $this->convertedProposals = $convertedProposals;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comissões de angariadores em atraso (' . $this->convertedProposals->count() . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.overdue-commission-alert',
        );
    }
}
