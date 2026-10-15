<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    /** Chave única da cache com todas as definições (site público). */
    public const CACHE_KEY = 'settings_all';

    protected $fillable = ['title', 'label', 'type', 'value'];

    protected static function booted(): void
    {
        // Qualquer escrita via modelo invalida a cache (e o logótipo, que deriva da definição "logo").
        $flush = static fn () => static::flushCache();

        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * Todas as definições, em cache durante 1 hora. Devolve a mesma Collection de
     * modelos que Setting::all(), por isso `->where('label', ...)->first()` continua a servir.
     */
    public static function cached(): Collection
    {
        return Cache::remember(self::CACHE_KEY, 3600, fn () => static::all());
    }

    /** Valor de uma definição a partir da cache (ou $default se não existir). */
    public static function cachedValue(string $label, mixed $default = null): mixed
    {
        return static::cached()->firstWhere('label', $label)?->value ?? $default;
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('site_logo');
        Cache::forget('site_logo_small');
    }
}
