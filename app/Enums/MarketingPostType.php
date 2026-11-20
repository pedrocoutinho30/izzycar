<?php

namespace App\Enums;

/** Tipo de conteúdo de um post do Marketing Studio. */
enum MarketingPostType: string
{
    use HasOptions;

    case Veiculo = 'veiculo';
    case Recomendacao = 'recomendacao';
    case Servico = 'servico';
    case Importacao = 'importacao';
    case Educativo = 'educativo';
    case Testemunho = 'testemunho';

    public function label(): string
    {
        return match ($this) {
            self::Veiculo => 'Veículo',
            self::Recomendacao => 'Recomendação',
            self::Servico => 'Serviço',
            self::Importacao => 'Importação realizada',
            self::Educativo => 'Educativo',
            self::Testemunho => 'Testemunho',
        };
    }
}
