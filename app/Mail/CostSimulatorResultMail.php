<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CostSimulatorResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public $valorCarro;
    public $isv;
    public $servicos;
    public $custoTotal;

    /**
     * Create a new message instance.
     */
    public function __construct($valorCarro, $isv, $servicos, $custoTotal)
    {
        $this->valorCarro = $valorCarro;
        $this->isv = $isv;
        $this->servicos = $servicos;
        $this->custoTotal = $custoTotal;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('Resultado do Simulador de Custos')
            ->markdown('emails.cost_simulator_result');
    }
}
