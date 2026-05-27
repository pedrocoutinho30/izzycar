<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleInspectionItem extends Model
{
    protected $fillable = [
        'vehicle_inspection_category_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'applies_to_fuels',
        'is_critical',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'applies_to_fuels' => 'array',
        'is_critical' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleInspectionCategory::class, 'vehicle_inspection_category_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(VehicleInspectionEntry::class, 'vehicle_inspection_item_id');
    }

    public function appliesToFuel(?string $fuel): bool
    {
        if (empty($this->applies_to_fuels)) {
            return true;
        }

        return in_array($fuel, $this->applies_to_fuels, true);
    }
}
