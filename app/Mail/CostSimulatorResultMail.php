<?php

namespace App\Mail;

use App\Models\CostSimulator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class CostSimulatorResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public CostSimulator $simulation;
    public string $clientName;
    public string $resultUrl;

    public function __construct(CostSimulator $simulation, string $clientName, string $resultUrl)
    {
        $this->simulation = $simulation;
        $this->clientName = $clientName;
        $this->resultUrl  = $resultUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('geral@izzycar.pt', 'Izzycar - Importação Automóvel'),
            subject: 'A sua Simulação de Custos de Importação',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cost_simulator_result',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
