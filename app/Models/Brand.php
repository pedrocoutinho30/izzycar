<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = ['name'];

    public function models(): HasMany
    {
        return $this->hasMany(ModelCar::class);
    }

    public function modelCars(): HasMany
    {
        return $this->hasMany(ModelCar::class);
    }
}
