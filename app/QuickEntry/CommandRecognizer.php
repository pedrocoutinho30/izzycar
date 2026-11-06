<?php

namespace App\QuickEntry;

/**
 * Identifica o módulo e a ação pelo início da frase ("criar movimento", "criar
 * cliente", "editar cliente"), sem distinguir maiúsculas, acentos nem espaços.
 * Só o início conta: "cliente" mais adiante numa frase de movimento é uma
 * associação, não um módulo.
 */
final class CommandRecognizer
{
    /** @var array<string, array{module: string, action: string, label: string}> */
    public const COMMANDS = [
        'criar movimento' => ['module' => 'movimento', 'action' => 'criar', 'label' => 'Criar movimento'],
        'criar cliente' => ['module' => 'cliente', 'action' => 'criar', 'label' => 'Criar cliente'],
        'editar cliente' => ['module' => 'cliente', 'action' => 'editar', 'label' => 'Editar cliente'],
    ];

    /**
     * @return array{key: string, module: string, action: string, label: string, length: int}|null
     *         length = número de caracteres do original ocupados pelo comando (e espaços iniciais)
     */
    public function recognize(string $text): ?array
    {
        $normalized = TextNormalizer::normalize($text);

        foreach (self::COMMANDS as $phrase => $command) {
            [$verb, $noun] = explode(' ', $phrase);
            if (preg_match('/^\s*' . $verb . '\s+' . $noun . '(?![a-z0-9])/', $normalized, $m) === 1) {
                return $command + ['key' => $command['module'] . '.' . $command['action'], 'length' => strlen($m[0])];
            }
        }

        return null;
    }
}
