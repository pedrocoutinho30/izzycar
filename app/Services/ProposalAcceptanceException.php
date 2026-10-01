<?php

namespace App\Services;

use RuntimeException;

/** Cotação que não pode ser aceite (já aceite, expirada, reprovada, sem cliente). */
class ProposalAcceptanceException extends RuntimeException
{
}
