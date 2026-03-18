<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $headline;
    public string $intro;
    public array $offers;
    public array $client;

    /**
     * Create a new message instance.
     */
    public function __construct(string $headline, string $intro, array $offers = [], array $client = [])
    {
        $this->headline = $headline;
        $this->intro = $intro;
        $this->offers = $offers;
        $this->client = $client;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject($this->headline)
            ->view('emails.newsletter', [
                'headline' => $this->headline,
                'intro' => $this->intro,
                'offers' => $this->offers,
                'client' => $this->client,
                'email' => $this->client['email'] ?? '',
                'name' => $this->client['name'] ?? '',
            ]);
    }
}
