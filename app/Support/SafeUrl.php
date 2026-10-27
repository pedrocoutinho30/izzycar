<?php

namespace App\Support;

/**
 * Valida valores submetidos por terceiros antes de os usar num href: só
 * http:// e https:// viram ligação (evita javascript:, data:, etc.).
 */
final class SafeUrl
{
    public static function isHttp(?string $value): bool
    {
        $value = trim((string) $value);

        if ($value === '' || preg_match('/[\s\x00-\x1F]/', $value)) {
            return false;
        }

        return (bool) preg_match('#^https?://[^/?\#]+#i', $value);
    }
}
