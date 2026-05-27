<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleInspectionEntry extends Model
{
    protected $fillable = [
        'vehicle_inspection_id',
        'vehicle_inspection_item_id',
        'status',
        'priority',
        'notes',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'vehicle_inspection_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(VehicleInspectionItem::class, 'vehicle_inspection_item_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(VehicleInspectionMedia::class, 'vehicle_inspection_entry_id')->orderBy('sort_order');
    }

    public function score(): float
    {
        return match ($this->status) {
            'ok' => 1.0,
            'atencao' => 0.5,
            default => 0.0,
        };
    }
}
