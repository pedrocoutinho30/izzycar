<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProposalStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $convertedProposal;
    public $oldStatus;
    public $newStatus;
    public $clientName;
    public $matricula;

    public function __construct($convertedProposal, $oldStatus, $newStatus, $clientName, $matricula)
    {
        $this->convertedProposal = $convertedProposal;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->clientName = $clientName;    
        $this->matricula = $convertedProposal->matricula_destino;
    }

    public function build()
    {
        return $this->subject('Atualização do estado da sua proposta - Izzycar')
                    ->view('emails.proposal_status_updated', [
                        'client_name' => $this->clientName,
                        'oldStatus' => $this->oldStatus,
                        'newStatus' => $this->newStatus,
                        'convertedProposal' => $this->convertedProposal,
                        'matricula' => $this->matricula,
                    ]);
    }
}
