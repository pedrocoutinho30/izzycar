<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Cache do site público de viaturas (listagem, filtros e home). As chaves
 * levam uma versão: incrementá-la invalida todas de uma vez, o que o
 * driver "file" não permite fazer com um padrão (vehicles_*).
 */
class VehicleCache
{
    private const VERSION_KEY = 'public_vehicles_cache_version';

    public static function key(string $name): string
    {
        return $name . '_v' . Cache::get(self::VERSION_KEY, 1);
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, (int) Cache::get(self::VERSION_KEY, 1) + 1);
    }
}
