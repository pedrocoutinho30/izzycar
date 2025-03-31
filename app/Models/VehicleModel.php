<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class VehicleModel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'reference', 'vehicle_brand_id'];

    public function brand()
    {
        return $this->belongsTo(VehicleBrand::class, 'vehicle_brand_id');
    }
}
