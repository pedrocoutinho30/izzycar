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
        'gallery_photos',
        'gallery_layouts',
        'gallery_photos_per_slide',
    ];

    protected $casts = [
        'equipment' => 'array',
        'price' => 'decimal:2',
        'savings' => 'decimal:2',
        'gallery_photos' => 'array',
        'gallery_layouts' => 'array',
        'gallery_photos_per_slide' => 'integer',
    ];
}
