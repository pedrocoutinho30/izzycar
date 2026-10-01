<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\ImportOpportunity;
use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso à equipa: o cliente carregou em "Pedir cotação" numa das outras
 * opções mostradas na sua cotação pública.
 */
class AlternativeQuoteRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Proposal $proposal,
        public ImportOpportunity $alternative,
        public ?Client $client,
    ) {
    }

    public function build()
    {
        $car = trim(implode(' ', array_filter([$this->alternative->brand, $this->alternative->model, $this->alternative->version])));

        return $this->subject("Pedido de cotação — {$car}" . ($this->client ? " ({$this->client->name})" : ''))
            ->text('emails.alternative-quote-requested', ['car' => $car]);
    }
}
