<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleInspectionCategory extends Model
{
    protected $fillable = [
        'vehicle_inspection_template_id',
        'name',
        'slug',
        'icon',
        'sort_order',
        'applies_to_fuels',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'applies_to_fuels' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(VehicleInspectionTemplate::class, 'vehicle_inspection_template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VehicleInspectionItem::class, 'vehicle_inspection_category_id')->orderBy('sort_order');
    }
}
