<?php

namespace App\Models;

use App\Support\VehicleCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class V3VehiclePhoto extends Model
{
    protected $table = 'v3_vehicle_photos';

    protected static function booted(): void
    {
        // A capa e as fotos aparecem na listagem pública em cache.
        static::saved(fn () => VehicleCache::flush());
        static::deleted(fn () => VehicleCache::flush());
    }

    protected $fillable = [
        'v3_vehicle_id',
        'path',
        'order_position',
        'is_cover',
        'focal_x',
        'focal_y',
    ];

    protected $casts = [
        'is_cover'       => 'boolean',
        'order_position' => 'integer',
        'focal_x'        => 'float',
        'focal_y'        => 'float',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(V3Vehicle::class, 'v3_vehicle_id');
    }
}
