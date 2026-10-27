<?php

namespace App\Services;

use App\Models\Proposal;

/**
 * A cotação foi gravada, mas o estado pedido não pôde ser aplicado
 * (ProposalAcceptanceService::changeStatus recusou). Leva a cotação para o
 * controller poder abrir a edição com o erro, como sempre fez.
 */
class ProposalStatusNotAppliedException extends ProposalAcceptanceException
{
    public function __construct(public readonly Proposal $proposal, ProposalAcceptanceException $previous)
    {
        parent::__construct($previous->getMessage(), 0, $previous);
    }
}
