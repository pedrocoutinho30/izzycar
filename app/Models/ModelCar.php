<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModelCar extends Model
{
    protected $fillable = ['name', 'brand_id', 'submodel'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function years(): HasMany
    {
        return $this->hasMany(ModelYear::class);
    }

    public function modelYears(): HasMany
    {
        return $this->hasMany(ModelYear::class);
    }
}
