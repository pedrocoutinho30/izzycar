<?php

namespace App\Enums;

/**
 * Estado do contacto com o vendedor (independente do estado da Oportunidade).
 */
enum ContactStatus: string
{
    use HasOptions;

    case NotContacted = 'nao_contactado';
    case Contacted = 'contactado';
    case Replied = 'respondeu';
    case Negotiating = 'negociacao_iniciada';

    public function label(): string
    {
        return match ($this) {
            self::NotContacted => 'Ainda não contactado',
            self::Contacted => 'Contactado',
            self::Replied => 'Respondeu',
            self::Negotiating => 'Negociação iniciada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NotContacted => 'secondary',
            self::Contacted => 'info',
            self::Replied => 'success',
            self::Negotiating => 'primary',
        };
    }
}
