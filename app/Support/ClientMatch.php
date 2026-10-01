<?php

namespace App\Support;

use App\Models\Client;

/**
 * Cliente/lead já existente que corresponde a um email ou telefone.
 */
final class ClientMatch
{
    public function __construct(
        public readonly Client $client,
        /** 'email', 'phone' ou 'email+phone' */
        public readonly string $by,
        /** Outro cliente que coincide pelo outro critério (email e telefone de pessoas diferentes). */
        public readonly ?Client $conflict = null,
    ) {
    }

    public function byLabel(): string
    {
        return match ($this->by) {
            'email' => 'email',
            'phone' => 'telefone',
            default => 'email e telefone',
        };
    }
}
