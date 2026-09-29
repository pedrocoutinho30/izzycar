<?php

namespace App\Enums;

/**
 * Estado de uma Oportunidade (carro/anúncio em análise para um Pedido de
 * Importação).
 */
enum OpportunityStatus: string
{
    use HasOptions;

    case ToContact = 'por_contactar';
    case Contacted = 'contactado';
    case AwaitingReply = 'aguardar_resposta';
    case Negotiating = 'em_negociacao';
    case InAnalysis = 'em_analise';
    case Selected = 'selecionado';
    case Rejected = 'rejeitado';

    public function label(): string
    {
        return match ($this) {
            self::ToContact => 'Por contactar',
            self::Contacted => 'Contactado',
            self::AwaitingReply => 'A aguardar resposta',
            self::Negotiating => 'Em negociação',
            self::InAnalysis => 'Em análise',
            self::Selected => 'Selecionado',
            self::Rejected => 'Rejeitado',
        };
    }

    /** Cor Bootstrap usada nos badges/chips. */
    public function color(): string
    {
        return match ($this) {
            self::ToContact => 'secondary',
            self::Contacted => 'info',
            self::AwaitingReply => 'warning',
            self::Negotiating => 'primary',
            self::InAnalysis => 'dark',
            self::Selected => 'success',
            self::Rejected => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ToContact => 'bi-circle',
            self::Contacted => 'bi-send',
            self::AwaitingReply => 'bi-hourglass-split',
            self::Negotiating => 'bi-currency-euro',
            self::InAnalysis => 'bi-search',
            self::Selected => 'bi-check-circle-fill',
            self::Rejected => 'bi-x-circle',
        };
    }
}
