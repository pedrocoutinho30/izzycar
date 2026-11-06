<?php

namespace App\QuickEntry;

use App\Models\User;

/**
 * Interpretador de um módulo (movimentos, clientes…). Só lê: nunca cria nem
 * altera registos de negócio. Para acrescentar um módulo, implementa isto e
 * regista-o no QuickEntryDispatcher.
 */
interface ModuleInterpreter
{
    /** Chave "módulo.ação" que este interpretador trata (ex. "movimento.criar"). */
    public function command(): string;

    /** $cursor já vem sem o comando inicial. */
    public function interpret(Proposal $proposal, TextCursor $cursor, User $user): void;
}
