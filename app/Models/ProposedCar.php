<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposedCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_request_id',
        'transport_cost',
        'ipo_cost',
        'imt_cost',
        'registration_cost',
        'isv_cost',
        'license_plate_cost',
        'inspection_commission_cost',
        'commission_cost',
        'proposed_car_mileage',
        'proposed_car_year_month',
        'proposed_car_value',
        'proposed_car_notes',
        'proposed_car_features',
        'images',
        'url',
    ];

    public function clientRequest()
    {
        return $this->belongsTo(ClientRequest::class);
    }
}