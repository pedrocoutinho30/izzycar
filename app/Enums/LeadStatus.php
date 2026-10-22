<?php

namespace App\Enums;

/**
 * Estado de uma lead (coluna clients.lead_status). A coluna não tem constraint
 * na BD, por isso quem escreve diretamente pode gravar outros valores:
 * labelOf() devolve-os tal como estão. Hoje só a exportação XLSX usa este
 * enum; as views antigas ainda têm arrays inline.
 */
enum LeadStatus: string
{
    use HasOptions;

    case Nova = 'nova';
    case EmContacto = 'em_contacto';
    /** Já está a receber cotações. */
    case Qualificada = 'qualificada';
    case Fria = 'fria';
    case Perdida = 'perdida';

    public function label(): string
    {
        return match ($this) {
            self::Nova => 'Nova',
            self::EmContacto => 'Em contacto',
            self::Qualificada => 'Qualificada',
            self::Fria => 'Fria',
            self::Perdida => 'Perdida',
        };
    }

    /** Label do valor gravado; null/vazio conta como "Nova" (como no kanban). */
    public static function labelOf(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return self::Nova->label();
        }

        return self::tryFrom($value)?->label() ?? $value;
    }
}
