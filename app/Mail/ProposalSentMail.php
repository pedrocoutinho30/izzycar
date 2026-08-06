<?php

namespace App\Mail;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Enviado quando uma cotação passa para o estado "Enviado" — para o cliente
 * (com o link de acesso) e, se a lead tiver um angariador, uma versão com
 * instruções extra para esse angariador (ver $forAngariador).
 */
class ProposalSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public Proposal $proposal;
    public string $url;
    public bool $forAngariador;
    public int $followupDays;

    public function __construct(Proposal $proposal, string $url, bool $forAngariador = false, int $followupDays = 2)
    {
        $this->proposal = $proposal;
        $this->url = $url;
        $this->forAngariador = $forAngariador;
        $this->followupDays = $followupDays;
    }

    public function build()
    {
        return $this->subject(
            $this->forAngariador
                ? 'Cotação enviada ao seu cliente — ' . trim(($this->proposal->brand ?? '') . ' ' . ($this->proposal->model ?? ''))
                : 'A sua cotação Izzycar está pronta'
        )->view('emails.proposal_sent', [
            'proposal' => $this->proposal,
            'url' => $this->url,
            'forAngariador' => $this->forAngariador,
            'followupDays' => $this->followupDays,
        ]);
    }
}
