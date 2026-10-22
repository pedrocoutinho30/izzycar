<?php

namespace App\Exports;

use Closure;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Uma coluna exportável: chave estável (vai no URL), rótulo do cabeçalho,
 * grupo (para o modal), tipo da célula e como ler o valor do modelo.
 *
 * Tipos: text, int, decimal, money, percent, date, datetime, bool.
 * "percent" lê-se como 0-100 (12.5 = 12,5%).
 */
final class ExportField
{
    public const TYPES = ['text', 'int', 'decimal', 'money', 'percent', 'date', 'datetime', 'bool'];

    /**
     * @param  Closure(Model): mixed  $reader
     * @param  list<string>  $with  relações a carregar quando o campo é escolhido
     * @param  list<string>  $counts  relações a contar (withCount) quando o campo é escolhido
     */
    private function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $group,
        public readonly string $type,
        public readonly bool $default,
        public readonly bool $sensitive,
        private readonly Closure $reader,
        public readonly array $with,
        public readonly array $counts,
        public readonly ?float $width,
    ) {
    }

    /**
     * @param  Closure(Model): mixed|string|null  $reader  closure, caminho para data_get ("owner.name") ou null (= a chave)
     */
    public static function make(
        string $key,
        string $label,
        string $group,
        string $type = 'text',
        Closure|string|null $reader = null,
        bool $default = false,
        bool $sensitive = false,
        array $with = [],
        array $counts = [],
        ?float $width = null,
    ): self {
        if (!in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException("Tipo de campo de exportação desconhecido: {$type}");
        }

        $path = is_string($reader) ? $reader : $key;
        $closure = $reader instanceof Closure ? $reader : fn (Model $model) => data_get($model, $path);

        return new self($key, $label, $group, $type, $default, $sensitive, $closure, $with, $counts, $width);
    }

    public function read(Model $model): mixed
    {
        return ($this->reader)($model);
    }
}
