<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelYear extends Model
{
    protected $fillable = ['year', 'model_car_id'];

    public function modelCar(): BelongsTo
    {
        return $this->belongsTo(ModelCar::class);
    }
}
