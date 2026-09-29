<?php

namespace App\Enums;

/**
 * Helpers partilhados pelos enums com label()/color() — evita repetir a
 * mesma lista de estados nas views, validações e JS.
 */
trait HasOptions
{
    /** @return array<string, string> value => label */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
