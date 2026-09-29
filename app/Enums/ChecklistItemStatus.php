<?php

namespace App\Enums;

/**
 * Estado de cada item da checklist de informação/documentação. Três estados
 * (não um booleano): "Não tem / N/A" deixa de estar pendente mas não conta
 * como confirmado.
 */
enum ChecklistItemStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case NotApplicable = 'not_applicable';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Por confirmar',
            self::Confirmed => 'Confirmado',
            self::NotApplicable => 'Não tem / N/A',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'bi-square',
            self::Confirmed => 'bi-check-square-fill',
            self::NotApplicable => 'bi-x-square',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::Confirmed => 'success',
            self::NotApplicable => 'dark',
        };
    }
}
