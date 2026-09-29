<?php

namespace App\Enums;

/**
 * Canal usado para falar com o vendedor de uma Oportunidade. Partilhado entre
 * o método principal da oportunidade e cada registo do histórico.
 */
enum ContactMethod: string
{
    use HasOptions;

    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Phone = 'telefone';
    case MobileDe = 'mobile_de';
    case AutoScout24 = 'autoscout24';
    case Other = 'outro';

    public function label(): string
    {
        return match ($this) {
            self::WhatsApp => 'WhatsApp',
            self::Email => 'Email',
            self::Phone => 'Telefone',
            self::MobileDe => 'Mobile.de',
            self::AutoScout24 => 'AutoScout24',
            self::Other => 'Outro',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WhatsApp => 'bi-whatsapp',
            self::Email => 'bi-envelope',
            self::Phone => 'bi-telephone',
            self::MobileDe, self::AutoScout24 => 'bi-globe',
            self::Other => 'bi-chat-dots',
        };
    }
}
