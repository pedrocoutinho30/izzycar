<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'v3_vehicle_id',
        'attribute_id',
        'value',
    ];

    /**
     * A que veículo este valor pertence.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Qual é o atributo (ex: cor, nº portas, etc).
     */
    public function attribute()
    {
        return $this->belongsTo(VehicleAttribute::class, 'attribute_id');
    }

    public function v3Vehicle()
    {
        return $this->belongsTo(V3Vehicle::class, 'v3_vehicle_id');
    }
}
