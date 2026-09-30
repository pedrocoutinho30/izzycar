<?php

namespace App\Permissions;

/**
 * O que é preciso para aceder a uma rota do backoffice.
 */
final class RouteRule
{
    public const SHARED = 'shared';
    public const ADMIN_ONLY = 'admin_only';
    public const RESOURCE = 'resource';
    public const UNMAPPED = 'unmapped';

    private function __construct(
        public readonly string $type,
        public readonly ?string $resource = null,
        public readonly ?string $action = null,
        public readonly ?string $requiredScope = null,
    ) {
    }

    public static function shared(): self
    {
        return new self(self::SHARED);
    }

    public static function adminOnly(): self
    {
        return new self(self::ADMIN_ONLY);
    }

    /** Rota sem regra: tratada como só admin (fecha por defeito). */
    public static function unmapped(): self
    {
        return new self(self::UNMAPPED);
    }

    public static function resource(string $resource, string $action, ?string $requiredScope): self
    {
        return new self(self::RESOURCE, $resource, $action, $requiredScope);
    }

    public function describe(): string
    {
        return match ($this->type) {
            self::RESOURCE => PermissionRegistry::permissionName($this->resource, $this->action, $this->requiredScope),
            default => $this->type,
        };
    }
}
