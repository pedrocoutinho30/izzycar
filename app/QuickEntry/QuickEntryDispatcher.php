<?php

namespace App\QuickEntry;

use App\Models\User;
use App\QuickEntry\Interpreters\ClientInterpreter;
use App\QuickEntry\Interpreters\MovementInterpreter;

/**
 * Normaliza, identifica o comando e entrega o resto da frase ao interpretador
 * do módulo. Um comando reconhecido sem interpretador devolve uma proposta
 * "não implementada", sem qualquer alteração.
 */
class QuickEntryDispatcher
{
    /** Versão das regras: guardada no histórico para comparar propostas ao longo do tempo. */
    public const VERSION = '1.1.0';

    /** @var array<string, ModuleInterpreter> */
    private array $interpreters = [];

    public function __construct(private CommandRecognizer $recognizer, MovementInterpreter $movements, ClientInterpreter $clients)
    {
        $this->register($movements);
        $this->register($clients);
    }

    public function register(ModuleInterpreter $interpreter): void
    {
        $this->interpreters[$interpreter->command()] = $interpreter;
    }

    public function interpret(string $text, User $user): Proposal
    {
        $command = $this->recognizer->recognize($text);

        if ($command === null) {
            return new Proposal(
                $text,
                Proposal::UNRECOGNIZED,
                message: 'Não reconheci o comando. A frase tem de começar por «criar movimento», «criar cliente» ou «editar cliente».',
            );
        }

        $proposal = new Proposal(
            $text,
            Proposal::READY,
            $command['module'],
            $command['action'],
            $command['label'],
            $command['label'],
        );

        $interpreter = $this->interpreters[$command['key']] ?? null;
        if ($interpreter === null) {
            $proposal->status = Proposal::NOT_IMPLEMENTED;
            $proposal->confirmLabel = null;
            $proposal->message = "O comando «{$command['label']}» foi reconhecido, mas ainda não está disponível na inserção rápida. Nada foi alterado.";

            return $proposal;
        }

        $cursor = new TextCursor($text);
        $cursor->consume(0, $command['length']);
        $interpreter->interpret($proposal, $cursor, $user);

        return $proposal;
    }
}
