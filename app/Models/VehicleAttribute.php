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
        'field_name_autoscout',
        'field_name_mobile',
    ];

    protected $casts = [
        'options' => 'array',
    ];
}
