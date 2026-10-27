<?php

namespace App\Enums;

/**
 * Estado de uma lead (coluna clients.lead_status). A coluna não tem constraint
 * na BD, por isso quem escreve diretamente pode gravar outros valores:
 * labelOf() devolve-os tal como estão. Usado pela exportação XLSX e pelo
 * mobile; as views desktop ainda têm arrays inline e a timeline escreve
 * "Em Contacto" (ver LeadService).
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

    /** Cor Bootstrap (as mesmas do kanban e da ficha desktop). */
    public function color(): string
    {
        return match ($this) {
            self::Nova => 'success',
            self::EmContacto => 'info',
            self::Qualificada => 'primary',
            self::Fria => 'secondary',
            self::Perdida => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Nova => 'bi-circle-fill',
            self::EmContacto => 'bi-telephone-fill',
            self::Qualificada => 'bi-patch-check-fill',
            self::Fria => 'bi-snow',
            self::Perdida => 'bi-x-circle-fill',
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
