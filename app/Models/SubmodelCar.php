<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmodelCar extends Model
{
    protected $fillable = ['name', 'brand_id', 'model_id'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function model(): HasMany
    {
        return $this->hasMany(ModelCar::class);
    }
}
