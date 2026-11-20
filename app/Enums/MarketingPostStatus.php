<?php

namespace App\Enums;

/** Estado editorial de um post. "Publicado" só se atribui por confirmação manual. */
enum MarketingPostStatus: string
{
    use HasOptions;

    case Rascunho = 'rascunho';
    case EmRevisao = 'em_revisao';
    case Aprovado = 'aprovado';
    case Agendado = 'agendado';
    case Publicado = 'publicado';

    public function label(): string
    {
        return match ($this) {
            self::Rascunho => 'Rascunho',
            self::EmRevisao => 'Em revisão',
            self::Aprovado => 'Aprovado',
            self::Agendado => 'Agendado',
            self::Publicado => 'Publicado',
        };
    }

    /** Cor Bootstrap usada nos badges. */
    public function color(): string
    {
        return match ($this) {
            self::Rascunho => 'secondary',
            self::EmRevisao => 'warning',
            self::Aprovado => 'info',
            self::Agendado => 'primary',
            self::Publicado => 'success',
        };
    }
}
