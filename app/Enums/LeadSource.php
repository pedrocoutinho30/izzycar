<?php

namespace App\Enums;

/**
 * Origem de uma lead/cliente (coluna clients.lead_source, texto livre na BD).
 * labelOf() devolve os valores desconhecidos tal como estão. Hoje só a
 * exportação XLSX usa este enum; as views antigas ainda têm arrays inline.
 */
enum LeadSource: string
{
    use HasOptions;

    case Simulador = 'simulador';
    case Importacao = 'importacao';
    case Retoma = 'retoma';
    case Manual = 'manual';
    case WhatsApp = 'whatsapp';
    case Newsletter = 'newsletter_footer';
    case Contacto = 'contacto';
    case Recomendador = 'recomendador';

    public function label(): string
    {
        return match ($this) {
            self::Simulador => 'Simulador de Custos',
            self::Importacao => 'Formulário de Importação',
            self::Retoma => 'Retoma',
            self::Manual => 'Manual',
            self::WhatsApp => 'WhatsApp',
            self::Newsletter => 'Newsletter',
            self::Contacto => 'Contacto no site',
            self::Recomendador => 'Recomendador de viaturas',
        };
    }

    public static function labelOf(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value)?->label() ?? $value;
    }
}
