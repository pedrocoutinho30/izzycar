<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostSimulator extends Model
{
    
    public $fillable = [
        'client_id',
        'car_value',
        'commission_cost',
        'inspection_commission_cost',
        'transport',
        'ipo_cost',
        'imt_cost',
        'registration_cost',
        'plates_cost',
        'total_cost',
        'isv_cost',
    ];
}
