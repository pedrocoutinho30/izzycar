<?php

namespace App\Enums;

/**
 * Tipo de cada registo no histórico de contactos de uma Oportunidade.
 */
enum ContactType: string
{
    use HasOptions;

    case Sent = 'enviado';
    case Received = 'recebido';
    case Call = 'chamada';
    case Note = 'nota';

    public function label(): string
    {
        return match ($this) {
            self::Sent => 'Mensagem enviada',
            self::Received => 'Resposta recebida',
            self::Call => 'Chamada',
            self::Note => 'Nota interna',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Sent => 'bi-arrow-up-right',
            self::Received => 'bi-arrow-down-left',
            self::Call => 'bi-telephone',
            self::Note => 'bi-journal-text',
        };
    }

    /** Conta como contacto efetivo com o vendedor (atualiza "último contacto"). */
    public function isOutreach(): bool
    {
        return $this !== self::Note;
    }
}
