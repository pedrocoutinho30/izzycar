<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProposalAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $convertedProposal;
    public $pdfContent;
    public $data;
    public bool $forAngariador;

    public function __construct($convertedProposal, $pdfContent, $data, bool $forAngariador = false)
    {
        $this->convertedProposal = $convertedProposal;
        $this->pdfContent = $pdfContent;
        $this->data = $data;
        $this->forAngariador = $forAngariador;
    }

    public function build()
    {
        $mail = $this->subject($this->forAngariador ? 'Cliente Aceitou a Proposta - Izzycar' : 'Proposta Aceite - Izzycar')
                    ->view('emails.proposal_accepted', $this->data);

        // O contrato é um documento entre a Izzycar e o cliente — não é
        // anexado na cópia enviada ao angariador, que recebe apenas o link
        // de acompanhamento do processo.
        if (!$this->forAngariador) {
            $mail->attachData($this->pdfContent, 'Contrato-Izzycar.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
