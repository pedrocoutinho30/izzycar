<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleInspectionMedia extends Model
{
    protected $fillable = [
        'vehicle_inspection_id',
        'vehicle_inspection_entry_id',
        'type',
        'path',
        'original_name',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'vehicle_inspection_id');
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(VehicleInspectionEntry::class, 'vehicle_inspection_entry_id');
    }
}
