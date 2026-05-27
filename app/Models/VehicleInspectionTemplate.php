<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleInspectionTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(VehicleInspectionCategory::class, 'vehicle_inspection_template_id')->orderBy('sort_order');
    }
}
