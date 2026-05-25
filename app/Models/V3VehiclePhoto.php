<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class V3VehiclePhoto extends Model
{
    protected $table = 'v3_vehicle_photos';

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
