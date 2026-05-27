<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleInspection extends Model
{
    protected $fillable = [
        'vehicle_inspection_template_id',
        'v3_vehicle_id',
        'parent_inspection_id',
        'status',
        'brand',
        'model',
        'sub_model',
        'version',
        'year',
        'kilometers',
        'vin',
        'registration',
        'color',
        'fuel',
        'power',
        'transmission',
        'traction',
        'notes',
        'recommendations',
        'inspection_result',
        'total_points',
        'max_points',
        'verified_items',
        'ok_items',
        'attention_items',
        'problem_items',
        'unverified_items',
        'critical_issues',
        'completed_at',
        'converted_at',
    ];

    protected $casts = [
        'year'             => 'integer',
        'kilometers'       => 'integer',
        'power'            => 'integer',
        'total_points'     => 'float',
        'max_points'       => 'float',
        'verified_items'    => 'integer',
        'ok_items'         => 'integer',
        'attention_items'  => 'integer',
        'problem_items'    => 'integer',
        'unverified_items' => 'integer',
        'critical_issues'  => 'integer',
        'completed_at'     => 'datetime',
        'converted_at'     => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'parent_inspection_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(VehicleInspection::class, 'parent_inspection_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(VehicleInspectionTemplate::class, 'vehicle_inspection_template_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(V3Vehicle::class, 'v3_vehicle_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(VehicleInspectionEntry::class, 'vehicle_inspection_id');
    }

    public function media(): HasMany
    {
        return $this->hasManyThrough(VehicleInspectionMedia::class, VehicleInspectionEntry::class, 'vehicle_inspection_id', 'vehicle_inspection_entry_id');
    }

    public function generalMedia(): HasMany
    {
        return $this->hasMany(VehicleInspectionMedia::class, 'vehicle_inspection_id')
            ->whereNull('vehicle_inspection_entry_id')
            ->orderBy('sort_order');
    }
}
