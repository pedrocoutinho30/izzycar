<?php

namespace App\Mail;
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

    public function __construct($convertedProposal, $pdfContent, $data)
    {
        $this->convertedProposal = $convertedProposal;
        $this->pdfContent = $pdfContent;
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Proposta Aceite - Izzycar')
                    ->view('emails.proposal_accepted', $this->data)
                    ->attachData($this->pdfContent, 'Contrato-Izzycar.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
