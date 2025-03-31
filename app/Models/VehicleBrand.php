<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleBrand extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'reference'];

    public function models()
    {
        return $this->hasMany(VehicleModel::class);
    }
}
