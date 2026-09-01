<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecommendationPost extends Model
{
    protected $fillable = [
        'brand',
        'model',
        'version',
        'mileage',
        'power',
        'fuel',
        'year',
        'equipment',
        'price',
        'savings',
        'url',
        'image',
    ];

    protected $casts = [
        'equipment' => 'array',
        'price' => 'decimal:2',
        'savings' => 'decimal:2',
    ];
}
