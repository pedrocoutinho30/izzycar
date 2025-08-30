<?php

// app/Models/VehicleAttribute.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleAttribute extends Model
{
    protected $fillable = [
        'name',
        'key',
        'type',
        'options',
        'order',
        'attribute_group',
    ];

    protected $casts = [
        'options' => 'array',
    ];
    
}
