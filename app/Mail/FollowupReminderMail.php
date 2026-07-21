<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class FollowupReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $followups;
    public string $data;

    public function __construct(Collection $followups, string $data)
    {
        $this->followups = $followups;
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lembrete de follow-up — ' . $this->data,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.followup-reminder',
        );
    }
}
